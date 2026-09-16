<?php
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/db_connect.php";
require_once __DIR__ . "/handling_members.php";

// ── Plans ──────────────────────────────────────────────────────────────────────

function get_plans()
{
    global $connGym;
    if (!$connGym)
        return [];

    $result = $connGym->query("SELECT * FROM plans ORDER BY price ASC");
    if (!$result)
        return [];

    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    return $rows;
}

function get_plan_by_id($id)
{
    global $connGym;
    if (!$connGym)
        return null;

    $stmt = $connGym->prepare("SELECT * FROM plans WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row;
}

function get_plan_by_name($name)
{
    global $connGym;
    if (!$connGym)
        return null;

    $stmt = $connGym->prepare("SELECT id, price FROM plans WHERE name = ? LIMIT 1");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row;
}


// ── Plan Durations ─────────────────────────────────────────────────────────────

function get_durations()
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

function get_duration_by_id($id)
{
    global $connGym;
    if (!$connGym)
        return null;

    $stmt = $connGym->prepare("SELECT * FROM plan_durations WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $row;
}

// ── Members ────────────────────────────────────────────────────────────────────

function get_all_members()
{
    global $connGym;
    if (!$connGym)
        return [];

    $result = $connGym->query("SELECT * FROM members ORDER BY id DESC");
    if (!$result)
        return [];

    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    return $rows;
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


// ── Requests ───────────────────────────────────────────────────────────────────

function get_pending_requests()
{
    global $connGym;
    if (!$connGym)
        return [];

    $result = $connGym->query("SELECT * FROM requests WHERE status = 'pending' ORDER BY id DESC");
    if (!$result)
        return [];

    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    return $rows;
}

function get_all_requests()
{
    global $connGym;
    if (!$connGym)
        return [];

    $result = $connGym->query("SELECT * FROM requests ORDER BY id DESC");
    if (!$result)
        return [];

    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    return $rows;
}

// ── Subscriptions ──────────────────────────────────────────────────────────────

function get_subscriptions_by_member($member_id)
{
    global $connGym;
    if (!$connGym)
        return [];

    $stmt = $connGym->prepare("
        SELECT s.*, p.name AS plan_name, p.price AS plan_price
        FROM subscription s
        LEFT JOIN plans p ON s.plan_id = p.id
        WHERE s.member_id = ?
        ORDER BY s.start_date ASC
    ");
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    $stmt->close();
    return $rows;
}

function get_active_subscriptions()
{
    global $connGym;
    if (!$connGym)
        return [];

    $result = $connGym->query("
        SELECT s.*, p.name AS plan_name, m.name AS member_name
        FROM subscription s
        LEFT JOIN plans p ON s.plan_id = p.id
        LEFT JOIN members m ON s.member_id = m.id
        WHERE s.status = 'active'
        ORDER BY s.end_date ASC
    ");
    if (!$result)
        return [];

    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $rows[] = $row;
    }
    return $rows;
}

// Fetches current status of a member
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

// Calculates total days remaining including any pending subscriptions
function calculate_subscription_days_left($sub)
{
    if (empty($sub)) {
        return 0;
    }

    $todayTs = strtotime(date('Y-m-d'));
    
    $endDate = $sub['end_date'] ?? null;
    $hasPending = !empty($sub['pending_sub']);
    $pending = $sub['pending_sub'] ?? null;
    
    // get_member_subscription() already attaches furthest_end_date
    $furthestEndDate = $sub['furthest_end_date'] ?? ($hasPending ? $pending['end_date'] : $endDate);
    
    if (!$furthestEndDate) {
        return 0;
    }
    
    $furthestEndTs = strtotime($furthestEndDate);
    return (int) max(0, ceil(($furthestEndTs - $todayTs) / 86400));
}
