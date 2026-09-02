<?php
require_once __DIR__ . "/config.php";

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

function get_plan_info($plan_id)
{
    global $connGym;
    $stmt = $connGym->prepare("SELECT id, name, price, duration FROM plans WHERE id = ?");
    $stmt->bind_param("s", $plan_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return $result;
}

function add_subscription($form)
{
    global $connGym;

    $email = trim($form['email'] ?? '');
    $phone = trim($form['phone'] ?? '');
    $plan_id = get_plan_id(trim($form['plan'] ?? ''));
    $plan = get_plan_info($plan_id);
    $member_id = null;

    if (!empty($email)) {
        $stmt = $connGym->prepare("SELECT id FROM members WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($result) {
            $member_id = $result['id'];
        }
    }

    if (!$member_id && !empty($phone)) {
        $stmt = $connGym->prepare("SELECT id FROM members WHERE phone = ?");
        $stmt->bind_param("s", $phone);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($result) {
            $member_id = $result['id'];
        }
    }

    if (!$member_id) {
        $member_id = add_member($form);
        if (!$member_id) {
            return false;
        }
    }

    if (!$plan) {
        return false;
    }

    $start_date = date('Y-m-d');
    $durationMonths = intval($plan['duration'] ?: 1);
    $plan_id = intval($plan['id']);
    $end_date = date('Y-m-d', strtotime("+$durationMonths months"));
    $price_paid = intval($form['price_paid'] ?? $plan['price']);
    $status = $form['status'] ?? 'active';

    $stmt = $connGym->prepare("INSERT INTO subscription (member_id, plan_id, price_paid, start_date, end_date, durationMonths, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiissis", $member_id, $plan_id, $price_paid, $start_date, $end_date, $durationMonths, $status);
    $success = $stmt->execute();
    $stmt->close();

    return $success;
}

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

    $plan_name = trim($data['plan'] ?? '');
    if ($id && !empty($plan_name)) {
        $planStmt = $connGym->prepare("SELECT id, price, duration FROM plans WHERE name = ? LIMIT 1");
        $planStmt->bind_param("s", $plan_name);
        $planStmt->execute();
        $plan = $planStmt->get_result()->fetch_assoc();
        $planStmt->close();

        if ($plan) {
            $plan_id = intval($plan['id']);
            $durationMonths = intval($plan['duration'] ?: 1);
            $price_paid = intval($plan['price']);

            $checkSub = $connGym->prepare("SELECT id FROM subscription WHERE member_id = ? ORDER BY id DESC LIMIT 1");
            $checkSub->bind_param("i", $id);
            $checkSub->execute();
            $subRes = $checkSub->get_result()->fetch_assoc();
            $checkSub->close();

            if ($subRes) {
                $subId = $subRes['id'];
                $updateSub = $connGym->prepare("UPDATE subscription SET plan_id = ?, price_paid = ? WHERE id = ?");
                $updateSub->bind_param("iii", $plan_id, $price_paid, $subId);
                $updateSub->execute();
                $updateSub->close();
            } else {
                $start_date = date('Y-m-d');
                $end_date = date('Y-m-d', strtotime("+$durationMonths months"));
                $status = 'active';
                $insertSub = $connGym->prepare("INSERT INTO subscription (member_id, plan_id, price_paid, start_date, end_date, durationMonths, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $insertSub->bind_param("iiissis", $id, $plan_id, $price_paid, $start_date, $end_date, $durationMonths, $status);
                $insertSub->execute();
                $insertSub->close();
            }
        }
    }

    return true;
}

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

function get_member_subscription($member_id)
{
    global $connGym;
    if (!$connGym || empty($member_id))
        return null;

    $stmt = $connGym->prepare("
        SELECT s.*, p.name AS plan_name, p.price AS plan_price, p.duration AS plan_duration
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

    return $sub;
}
