<?php
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/email_utils.php";

// Creates a new member record with a default hashed password and returns the new ID
function add_member($member)
{
    global $connGym;

    $name = trim($member['name'] ?? '');
    $gender = $member['gender'] ?? 'Male';
    $email = $member['email'] ?? '';
    $phone = $member['phone'] ?? '';
    $joinDate = !empty($member['joinDate']) ? $member['joinDate'] : date('Y-m-d');
    $password = password_hash("member123", PASSWORD_DEFAULT);
    $role = 'member';

    $stmt = $connGym->prepare("INSERT INTO members (name, gender, email, phone, joinDate, password, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $name, $gender, $email, $phone, $joinDate, $password, $role);
    $stmt->execute();
    $new_id = $connGym->insert_id;
    $stmt->close();

    return $new_id;
}

// Finds a plan ID by either its name or existing ID
function get_plan_id($plan)
{
    global $connGym;
    $stmt = $connGym->prepare("SELECT id FROM plans WHERE name = ? OR id = ?");
    $stmt->bind_param("ss", $plan, $plan);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $result ? $result['id'] : null;
}

// Fetches plan details (ID, name, base price) by plan ID
function get_plan_info($plan_id)
{
    global $connGym;
    $stmt = $connGym->prepare("SELECT id, name, price FROM plans WHERE id = ?");
    $stmt->bind_param("i", $plan_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $result;
}

function find_member_by_contact($email, $phone)
{
    global $connGym;
    if (!empty($email)) {
        $stmt = $connGym->prepare("SELECT id FROM members WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($result) return $result['id'];
    }
    if (!empty($phone)) {
        $stmt = $connGym->prepare("SELECT id FROM members WHERE phone = ?");
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($result) return $result['id'];
    }
    return null;
}

function handle_new_member_signup($form, $plan_id, $duration_id, $durationMonths, $discount_pct, $price_paid)
{
    $details = json_encode([
        'form' => $form, // Save the full form to create the member later
        'plan_id' => $plan_id,
        'duration_id' => $duration_id,
        'durationMonths' => $durationMonths,
        'discount_pct' => $discount_pct,
        'price_paid' => $price_paid
    ]);
    // Insert with member_id = NULL
    $success = insert_member_request(null, 'membership', $price_paid, $details, 'New member signup request');
    if ($success) {
        $toEmail = $form['email'] ?? '';
        $toName = $form['name'] ?? 'New Member';
        if (!empty($toEmail)) {
            send_membership_pending_email($toEmail, $toName);
        }
        return 'pending_approval';
    }
    return false;
}

function get_furthest_end_date($member_id)
{
    global $connGym;
    $today = date('Y-m-d');
    $stmt = $connGym->prepare("
        SELECT MAX(end_date) AS furthest_end 
        FROM subscription 
        WHERE member_id = ? AND status IN ('active', 'pending') AND end_date >= ?
    ");
    $stmt->bind_param("is", $member_id, $today);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row['furthest_end'] ?? null;
}

function calculate_subscription_dates($member_id, $durationMonths)
{
    $today = date('Y-m-d');
    $furthestEnd = get_furthest_end_date($member_id);

    if (!empty($furthestEnd)) {
        // Queue this new subscription right after the furthest subscription finishes!
        $start_date = date('Y-m-d', strtotime('+1 day', strtotime($furthestEnd)));
        $end_date   = date('Y-m-d', strtotime("+$durationMonths months", strtotime($start_date)));
        $status     = 'pending';
    } else {
        // No current active subscription: starts today as active
        $start_date = $today;
        $end_date   = date('Y-m-d', strtotime("+$durationMonths months", strtotime($today)));
        $status     = 'active';
    }

    return ['start_date' => $start_date, 'end_date' => $end_date, 'status' => $status];
}

function recalculate_subscription_queue($member_id) {
    global $connGym;
    // We fetch all active and pending subscriptions ordered by start_date ASC
    $stmt = $connGym->prepare("SELECT id, start_date, durationMonths, status FROM subscription WHERE member_id = ? AND status IN ('active', 'pending') ORDER BY start_date ASC, id ASC");
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $subs = [];
    while ($row = $result->fetch_assoc()) {
        $subs[] = $row;
    }
    $stmt->close();

    $today = date('Y-m-d');
    $current_end_date = null;

    foreach ($subs as $sub) {
        $start_date = $sub['start_date'];
        $status = $sub['status'];
        
        if ($status === 'active') {
            // We do not change an active subscription's start date.
            $end_date = date('Y-m-d', strtotime("+" . $sub['durationMonths'] . " months", strtotime($start_date)));
            $current_end_date = $end_date;
            
            $uStmt = $connGym->prepare("UPDATE subscription SET end_date = ? WHERE id = ?");
            $uStmt->bind_param("si", $end_date, $sub['id']);
            $uStmt->execute();
            $uStmt->close();
        } 
        else if ($status === 'pending') {
            // Pending subscriptions start the day after the previous one finishes.
            if ($current_end_date && $current_end_date >= $today) {
                $start_date = date('Y-m-d', strtotime('+1 day', strtotime($current_end_date)));
                $end_date = date('Y-m-d', strtotime("+" . $sub['durationMonths'] . " months", strtotime($start_date)));
                $current_end_date = $end_date;
                
                $uStmt = $connGym->prepare("UPDATE subscription SET start_date = ?, end_date = ? WHERE id = ?");
                $uStmt->bind_param("ssi", $start_date, $end_date, $sub['id']);
                $uStmt->execute();
                $uStmt->close();
            } else {
                // It should start today and become active!
                $start_date = $today;
                $end_date = date('Y-m-d', strtotime("+" . $sub['durationMonths'] . " months", strtotime($start_date)));
                $current_end_date = $end_date;
                $new_status = 'active';
                
                $uStmt = $connGym->prepare("UPDATE subscription SET start_date = ?, end_date = ?, status = ? WHERE id = ?");
                $uStmt->bind_param("sssi", $start_date, $end_date, $new_status, $sub['id']);
                $uStmt->execute();
                $uStmt->close();
            }
        }
    }
}

function edit_specific_subscription($member_id, $sub_id, $plan_name, $durationMonths, $price_diff) {
    global $connGym;
    $planStmt = $connGym->prepare("SELECT id FROM plans WHERE name = ? LIMIT 1");
    $planStmt->bind_param("s", $plan_name);
    $planStmt->execute();
    $plan = $planStmt->get_result()->fetch_assoc();
    $planStmt->close();

    if (!$plan) return false;
    $plan_id = intval($plan['id']);

    // Fetch the old subscription
    $sStmt = $connGym->prepare("SELECT * FROM subscription WHERE id = ? AND member_id = ? AND status IN ('active', 'pending')");
    $sStmt->bind_param("ii", $sub_id, $member_id);
    $sStmt->execute();
    $oldSub = $sStmt->get_result()->fetch_assoc();
    $sStmt->close();

    if (!$oldSub) return false;

    // Mark old as replaced
    $uStmt = $connGym->prepare("UPDATE subscription SET status = 'replaced' WHERE id = ?");
    $uStmt->bind_param("i", $sub_id);
    $uStmt->execute();
    $uStmt->close();

    // Create new copy
    $new_price_paid = intval($oldSub['price_paid']) + intval($price_diff);
    $status = $oldSub['status'];
    $start_date = $oldSub['start_date'];
    $end_date = date('Y-m-d', strtotime("+$durationMonths months", strtotime($start_date)));

    $duration_id = $oldSub['duration_id'];
    $discount_pct = $oldSub['discount_pct'];

    $iStmt = $connGym->prepare("INSERT INTO subscription (member_id, plan_id, price_paid, start_date, end_date, durationMonths, duration_id, discount_pct, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $iStmt->bind_param("iiissidds", $member_id, $plan_id, $new_price_paid, $start_date, $end_date, $durationMonths, $duration_id, $discount_pct, $status);
    $iStmt->execute();
    $iStmt->close();

    // Cascade updates
    recalculate_subscription_queue($member_id);

    return true;
}

// Subscribes a member to a plan with the chosen duration and calculates the final price
function add_subscription($form)
{
    global $connGym;

    $email = trim($form['email'] ?? '');
    $phone = trim($form['phone'] ?? '');
    $plan_id = get_plan_id(trim($form['plan'] ?? ''));
    $plan = get_plan_info($plan_id);
    $member_id = !empty($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;

    if (!$member_id) {
        $member_id = find_member_by_contact($email, $phone);
    }

    if ($member_id) {
        // Prevent bypassing approval: Check if they are already waiting for membership approval
        $reqStmt = $connGym->prepare("SELECT id FROM requests WHERE member_id = ? AND request_type = 'membership' AND status = 'pending'");
        $reqStmt->bind_param("i", $member_id);
        $reqStmt->execute();
        $pendingReq = $reqStmt->get_result()->fetch_assoc();
        $reqStmt->close();

        if ($pendingReq) {
            return false;
        }
    }

    $is_new_member = !$member_id;

    if (!$plan) {
        return false;
    }

    $duration_id  = intval($form['duration_id'] ?? 0);
    $duration_row = null;

    if ($duration_id) {
        $dStmt = $connGym->prepare("SELECT months, discount_pct FROM plan_durations WHERE id = ? LIMIT 1");
        $dStmt->bind_param("i", $duration_id);
        $dStmt->execute();
        $duration_row = $dStmt->get_result()->fetch_assoc();
        $dStmt->close();
    }

    $durationMonths = intval($duration_row['months'] ?? 1);
    $discount_pct   = floatval($duration_row['discount_pct'] ?? 0);
    $plan_id        = intval($plan['id']);
    $base_price     = intval($plan['price']);
    $price_paid     = intval($form['price_paid'] ?? round($base_price * $durationMonths * (1 - $discount_pct / 100)));

    if ($is_new_member) {
        return handle_new_member_signup($form, $plan_id, $duration_id, $durationMonths, $discount_pct, $price_paid);
    }

    // Sync member subscriptions first (auto-expire past ones, activate eligible pending ones)
    sync_member_subscriptions($member_id);

    $dates = calculate_subscription_dates($member_id, $durationMonths);

    $stmt = $connGym->prepare("INSERT INTO subscription (member_id, plan_id, price_paid, start_date, end_date, durationMonths, duration_id, discount_pct, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiissidds", $member_id, $plan_id, $price_paid, $dates['start_date'], $dates['end_date'], $durationMonths, $duration_id, $discount_pct, $dates['status']);
    $success = $stmt->execute();
    $stmt->close();

    return $success ? $dates['status'] : false;
}

// Updates existing member profile info and syncs their subscription
function update_member($data)
{
    global $connGym;
    if (!$connGym)
        return false;

    $id = intval($data['id'] ?? 0);
    $name = trim($data['name'] ?? '');
    $gender = $data['gender'] ?? 'Male';
    $email = trim($data['email'] ?? '');
    $phone = trim($data['phone'] ?? '');

    $stmt = $connGym->prepare("UPDATE members SET name = ?, gender = ?, email = ?, phone = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $name, $gender, $email, $phone, $id);
    $stmt->execute();
    $stmt->close();

    $subscription_id = trim($data['subscription_id'] ?? '');
    $plan_name = trim($data['plan'] ?? '');
    
    if ($id && !empty($plan_name)) {
        if ($subscription_id === 'new' || empty($subscription_id)) {
            $durationMonths = intval($data['durationMonths'] ?? 1);
            $price_diff = intval($data['price_diff'] ?? 0);
            
            $planStmt = $connGym->prepare("SELECT id, price FROM plans WHERE name = ? LIMIT 1");
            $planStmt->bind_param("s", $plan_name);
            $planStmt->execute();
            $plan = $planStmt->get_result()->fetch_assoc();
            $planStmt->close();
            
            if ($plan) {
                $plan_id = intval($plan['id']);
                $price_paid = (intval($plan['price']) * $durationMonths) + $price_diff;
                $dates = calculate_subscription_dates($id, $durationMonths);
                
                $insertSub = $connGym->prepare("INSERT INTO subscription (member_id, plan_id, price_paid, start_date, end_date, durationMonths, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $insertSub->bind_param("iiissis", $id, $plan_id, $price_paid, $dates['start_date'], $dates['end_date'], $durationMonths, $dates['status']);
                $insertSub->execute();
                $insertSub->close();
            }
        } else {
            $sub_id = intval($subscription_id);
            $durationMonths = intval($data['durationMonths'] ?? 1);
            $price_diff = intval($data['price_diff'] ?? 0);
            edit_specific_subscription($id, $sub_id, $plan_name, $durationMonths, $price_diff);
        }
    }

    return true;
}

// Updates member profile (name, phone, gender, optional password) from member profile page
function update_member_profile($user_id, $name, $phone, $gender, $password = '')
{
    global $connGym;
    if (!$connGym || empty($user_id))
        return false;

    $user_id = intval($user_id);
    $name    = trim($name);
    $phone   = trim($phone);

    if (!empty($password)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $connGym->prepare("UPDATE members SET name = ?, phone = ?, gender = ?, password = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $name, $phone, $gender, $hashed, $user_id);
    } else {
        $stmt = $connGym->prepare("UPDATE members SET name = ?, phone = ?, gender = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $phone, $gender, $user_id);
    }

    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

// Retrieves member (or staff) profile data by user ID
function get_member_profile($user_id)
{
    global $connGym;
    if (!$connGym || empty($user_id))
        return null;

    $stmt = $connGym->prepare("SELECT id, name, email, phone, gender, joinDate, role FROM members WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $member = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$member) {
        $stmt = $connGym->prepare("SELECT id, name, email, phone, gender, joinDate, role FROM staff WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $member = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }

    return $member;
}

// Automatically expires past subscriptions and activates pending ones when their start date arrives
function sync_member_subscriptions($member_id)
{
    global $connGym;
    if (!$connGym || empty($member_id))
        return;

    $today = date('Y-m-d');

    // 1. Mark active subscriptions that have passed their end_date as 'expired'
    $expStmt = $connGym->prepare("
        UPDATE subscription 
        SET status = 'expired' 
        WHERE member_id = ? AND status = 'active' AND end_date < ?
    ");
    $expStmt->bind_param("is", $member_id, $today);
    $expStmt->execute();
    $expStmt->close();

    // 2. Check if there is currently any 'active' subscription
    $activeStmt = $connGym->prepare("
        SELECT id FROM subscription 
        WHERE member_id = ? AND status = 'active' AND end_date >= ?
        LIMIT 1
    ");
    $activeStmt->bind_param("is", $member_id, $today);
    $activeStmt->execute();
    $hasActive = $activeStmt->get_result()->fetch_assoc();
    $activeStmt->close();

    // 3. If no active subscription exists, activate the earliest pending subscription whose start_date has arrived
    if (!$hasActive) {
        $actStmt = $connGym->prepare("
            UPDATE subscription 
            SET status = 'active' 
            WHERE member_id = ? AND status = 'pending' AND start_date <= ?
            ORDER BY start_date ASC 
            LIMIT 1
        ");
        $actStmt->bind_param("is", $member_id, $today);
        $actStmt->execute();
        $actStmt->close();
    }
}

// Fetches the active subscription and any queued pending subscription for a member
function get_member_subscription($member_id)
{
    global $connGym;
    if (!$connGym || empty($member_id))
        return null;

    sync_member_subscriptions($member_id);

    // 1. Fetch active subscription
    $stmt = $connGym->prepare("
        SELECT s.*, p.name AS plan_name, p.price AS plan_price
        FROM subscription s
        LEFT JOIN plans p ON s.plan_id = p.id
        WHERE s.member_id = ? AND s.status = 'active'
        ORDER BY s.id DESC
        LIMIT 1
    ");
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $sub = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // 2. If no active subscription, fetch the most recent subscription (e.g. pending or expired)
    if (!$sub) {
        $stmt = $connGym->prepare("
            SELECT s.*, p.name AS plan_name, p.price AS plan_price
            FROM subscription s
            LEFT JOIN plans p ON s.plan_id = p.id
            WHERE s.member_id = ?
            ORDER BY s.id DESC
            LIMIT 1
        ");
        $stmt->bind_param("i", $member_id);
        $stmt->execute();
        $sub = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }

    if (!$sub) {
        return null;
    }

    // 3. If active, also fetch any queued/pending subscription
    $sub['pending_sub'] = null;
    if ($sub['status'] === 'active') {
        $qStmt = $connGym->prepare("
            SELECT s.*, p.name AS plan_name, p.price AS plan_price
            FROM subscription s
            LEFT JOIN plans p ON s.plan_id = p.id
            WHERE s.member_id = ? AND s.status = 'pending' AND s.id != ?
            ORDER BY s.start_date ASC
            LIMIT 1
        ");
        $subId = intval($sub['id']);
        $qStmt->bind_param("ii", $member_id, $subId);
        $qStmt->execute();
        $pending = $qStmt->get_result()->fetch_assoc();
        $qStmt->close();

        if ($pending) {
            $sub['pending_sub'] = $pending;
        }
    }

    // Query furthest end date across all active and pending subscriptions
    $furthestEnd = get_furthest_end_date($member_id);
    $sub['furthest_end_date'] = $furthestEnd ?? $sub['end_date'];

    return $sub;
}

// Retrieves all available duration tiers (1, 3, 6, 12 months) and their discount %
function get_plan_durations()
{
    global $connGym;
    if (!$connGym)
        return [];
    $result = $connGym->query("SELECT * FROM plan_durations ORDER BY months ASC");
    if (!$result)
        return [];
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    return $rows;
}

// Checks if today is within the first 3 days of the current 30-day billing cycle
function can_change_plan($sub)
{
    if (!$sub || empty($sub['start_date']))
        return false;

    $start     = strtotime($sub['start_date']);
    $today     = strtotime(date('Y-m-d'));
    $days_since = (int) floor(($today - $start) / 86400);

    $month_number  = (int) floor($days_since / 30);
    $month_start   = $start + ($month_number * 30 * 86400);
    $grace_end     = $month_start + (3 * 86400);

    return $today <= $grace_end;
}

// Calculates prorated upgrade cost or refund amount when changing plans mid-subscription
function calculate_change_cost($sub, $new_plan)
{
    $original_months   = intval($sub['durationMonths'] ?? 1);
    $discount_pct      = floatval($sub['discount_pct'] ?? 0);
    $price_paid        = intval($sub['price_paid']);
    $new_base_price    = intval($new_plan['price']);

    $start      = strtotime($sub['start_date']);
    $today      = strtotime(date('Y-m-d'));
    $days_since = (int) floor(($today - $start) / 86400);
    $month_number      = (int) floor($days_since / 30);
    $months_remaining  = $original_months - $month_number;

    if ($months_remaining <= 0)
        $months_remaining = 0;

    $new_plan_cost  = round($new_base_price * $original_months * (1 - $discount_pct / 100));
    $refund_amount  = $original_months > 0 ? round(($price_paid / $original_months) * $months_remaining) : 0;
    $amount         = $new_plan_cost - $refund_amount;

    return [
        'amount'           => $amount,
        'is_refund'        => $amount < 0,
        'months_remaining' => $months_remaining,
        'new_end_date'     => date('Y-m-d', strtotime(date('Y-m-d') . ' +' . ($months_remaining * 30) . ' days')),
    ];
}

// Updates an active subscription to a new plan with the updated price and end date
function change_plan($sub_id, $new_plan_id, $new_price_paid, $new_end_date)
{
    global $connGym;
    if (!$connGym)
        return false;

    $sub_id       = intval($sub_id);
    $new_plan_id  = intval($new_plan_id);
    $new_price_paid = intval($new_price_paid);

    $stmt = $connGym->prepare("UPDATE subscription SET plan_id = ?, price_paid = ?, end_date = ? WHERE id = ?");
    $stmt->bind_param("iisi", $new_plan_id, $new_price_paid, $new_end_date, $sub_id);
    $ok = $stmt->execute();
    $stmt->close();

    return $ok;
}

// Records a general member request (refund, email_change, etc.) in the requests table
function insert_member_request($member_id, $request_type, $amount = null, $new_value = null, $reason = '')
{
    global $connGym;
    if (!$connGym)
        return false;

    $member_id    = $member_id !== null ? intval($member_id) : null;
    $request_type = trim($request_type);
    $new_value    = $new_value !== null ? trim($new_value) : null;
    $reason       = trim($reason);

    if ($request_type === 'email_change') {
        $existing = get_pending_email_request($member_id);
        if ($existing) {
            // Update the pending request if email changed, without creating duplicate rows
            if (strtolower($existing['new_value']) !== strtolower($new_value)) {
                $uStmt = $connGym->prepare("UPDATE requests SET new_value = ?, created_at = NOW() WHERE id = ?");
                $uStmt->bind_param("si", $new_value, $existing['id']);
                $uStmt->execute();
                $uStmt->close();
            }
            return true;
        }
    }

    if ($amount !== null) {
        $amount = intval(abs($amount));
        $stmt = $connGym->prepare("
            INSERT INTO requests (member_id, request_type, amount, new_value, reason, status) 
            VALUES (?, ?, ?, ?, ?, 'pending')
        ");
        $stmt->bind_param("isiss", $member_id, $request_type, $amount, $new_value, $reason);
    } else {
        $stmt = $connGym->prepare("
            INSERT INTO requests (member_id, request_type, amount, new_value, reason, status) 
            VALUES (?, ?, NULL, ?, ?, 'pending')
        ");
        $stmt->bind_param("isss", $member_id, $request_type, $new_value, $reason);
    }

    $ok = $stmt->execute();
    $stmt->close();
    return $ok;
}

// Backward-compatible wrapper for refund requests
function insert_refund_request($member_id, $amount, $reason = '')
{
    return insert_member_request($member_id, 'refund', $amount, null, $reason);
}

// Checks if a member has a pending email change request
function get_pending_email_request($member_id)
{
    global $connGym;
    if (!$connGym)
        return null;

    $member_id = intval($member_id);
    $stmt = $connGym->prepare("
        SELECT * FROM requests 
        WHERE member_id = ? AND request_type = 'email_change' AND status = 'pending' 
        ORDER BY id DESC LIMIT 1
    ");
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $req = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $req;
}
