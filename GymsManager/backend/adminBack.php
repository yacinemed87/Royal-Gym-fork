<?php
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/gyms.php";
require_once __DIR__ . "/handling_members.php";
require_once __DIR__ . "/gym_data.php";

function write_admin_sidebar($active_page = '')
{
    global $gym;
    if (!isset($gym) || $gym === null) {
        $gym = get_gym_info();
    }

    $gym_name = htmlspecialchars($gym['name'] ?? 'Gym');
    $gym_logo = htmlspecialchars($gym['logo'] ?? 'logo.png');

    $nav_items = [
        'dashboard' => ['label' => 'Dashboard', 'url' => './dashboard.php'],
        'plans' => ['label' => 'Plans', 'url' => './plans.php'],
        'members' => ['label' => 'Members', 'url' => './members.php'],
        'requests' => ['label' => 'Requests', 'url' => './requests.php'],
    ];

    echo "
    <aside class='sidebar'>
        <a href='../index.php' class='sidebar-brand'>
            {$gym_name}
            <img src='../assets/images/{$gym_logo}' alt='{$gym_name} Logo' />
        </a>
        <ul class='sidebar-nav'>
    ";

    foreach ($nav_items as $key => $item) {
        $active_class = ($active_page === $key) ? 'class="active"' : '';
        echo "        <li><a href='{$item['url']}' {$active_class}>{$item['label']}</a></li>\n";
    }

    echo "
            <div class='nav-divider'></div>
            <li><a href='../index.php'>Return to Home</a></li>
            <li><a href='../client/logout.php'>Logout</a></li>
        </ul>
        </aside>
    ";
}

function write_members_table()
{
    global $connGym;
    if (!$connGym)
        return;

    $sql = "SELECT m.id, m.name, m.gender, m.email, m.phone, m.joinDate,
                   s.id AS sub_id, s.status, s.start_date, s.end_date, s.price_paid, s.durationMonths,
                   p.name AS plan_name
            FROM members m
            LEFT JOIN subscription s ON m.id = s.member_id AND s.status IN ('active', 'pending')
            LEFT JOIN plans p ON s.plan_id = p.id
            ORDER BY m.id DESC, s.start_date ASC";

    $result = $connGym->query($sql);
    if (!$result)
        return;

    $members = [];
    while ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        if (!isset($members[$id])) {
            $members[$id] = [
                'id' => $id,
                'name' => $row['name'],
                'gender' => $row['gender'],
                'email' => $row['email'],
                'phone' => $row['phone'],
                'joinDate' => $row['joinDate'],
                'subs' => []
            ];
        }
        if ($row['sub_id']) {
            $members[$id]['subs'][] = [
                'id' => $row['sub_id'],
                'plan_name' => $row['plan_name'],
                'status' => $row['status'],
                'durationMonths' => $row['durationMonths'],
                'price_paid' => $row['price_paid'],
                'start_date' => $row['start_date'],
                'end_date' => $row['end_date']
            ];
        }
    }

    foreach ($members as $m) {
        $name = htmlspecialchars($m['name']);
        $email = htmlspecialchars($m['email']);
        $phone = htmlspecialchars($m['phone']);
        $gender = htmlspecialchars($m['gender']);
        $joinDate = htmlspecialchars($m['joinDate']);
        $subsJson = htmlspecialchars(json_encode($m['subs']), ENT_QUOTES, 'UTF-8');

        $planBadges = '';
        $planNames = [];
        foreach ($m['subs'] as $sub) {
            $class = $sub['status'] === 'active' ? 'badge-active' : 'badge-pending';
            $planBadges .= "<span class='badge {$class}'>" . htmlspecialchars($sub['plan_name']) . " (" . ucfirst(htmlspecialchars($sub['status'])) . ")</span><br>";
            $planNames[] = $sub['plan_name'];
        }
        if (empty($planBadges)) $planBadges = "<span class='badge'>No Plan</span>";
        $dataPlanStr = htmlspecialchars(implode(", ", $planNames));

        $can_delete = ($_SESSION['role'] ?? '') === 'super_admin';
        $delete_form = "";
        if ($can_delete) {
            $delete_form = "
            <form method='POST' action='members.php' style='display:inline;' onsubmit='return confirm(\"Are you sure you want to delete {$name}?\");'>
                <input type='hidden' name='action' value='delete'>
                <input type='hidden' name='id' value='{$m['id']}'>
                <button type='submit' class='btn btn-delete'>Delete</button>
            </form>";
        }

        echo "
        <tr data-name='{$name}' data-plan='{$dataPlanStr}'>
            <td><strong>{$name}</strong></td>
            <td>{$gender}</td>
            <td>{$email}</td>
            <td>{$phone}</td>
            <td>{$planBadges}</td>
            <td>{$joinDate}</td>
            <td>
                <button type='button' class='btn btn-edit' data-id='{$m['id']}' data-name='{$name}' data-gender='{$gender}' data-email='{$email}' data-phone='{$phone}' data-subs='{$subsJson}' onclick='openEditModal(this)'>Edit</button>
                {$delete_form}
            </td>
        </tr>
        ";
    }
}

function write_plan_options()
{
    $plans = get_plans();

    foreach ($plans as $row) {
        $name = htmlspecialchars($row['name']);
        echo "<option value='{$name}'>{$name}</option>\n";
    }
}

function write_plans_table()
{
    $plansList = get_plans();

    $thead = "
			<table>
			<thead>
				<tr>
					<th>Plan Name</th>
					<th>Price</th>
					<th>Features</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tbody id='plans-tbody'>
	";

    echo $thead;

    foreach ($plansList as $row) {
        echo "
			<tr>
				<td>{$row['name']}</td>
				<td>{$row['price']}DA</td>
				<td>{$row['features']}</td>
				<td>
					<button class='btn btn-edit' onclick='openEditModal({$row['id']})'>Edit</button>
					<button class='btn btn-delete' onclick='openDeleteModal({$row['id']})'>Delete</button>
				</td>
			</tr>
			";
    }

    echo "
	</body>
	</table>
	";
}

function write_durations_table()
{
    $durationsList = get_durations();

    $thead = "
			<table>
			<thead>
				<tr>
					<th>Duration</th>
					<th>Discount</th>
					<th>Actions</th>
				</tr>
			</thead>
			<tbody id='plans-tbody'>
	";

    echo $thead;

    foreach ($durationsList as $row) {
        echo "
		<tr>
			<td>{$row['label']}</td>
			<td>{$row['discount_pct']}DA</td>
			<td>
				<button class='btn btn-edit' onclick='openEditModal({$row['id']})'>Edit</button>
				<button class='btn btn-delete' onclick='openDeleteModal({$row['id']})'>Delete</button>
			</td>
		</tr>
	";
    }

    echo "
	</body>
	</table
	";
}

function write_requests_table()
{
    global $connGym;
    if (!$connGym)
        return;

    $sql = "SELECT r.id, r.request_type, r.new_value, r.amount, r.created_at, m.name AS member_name 
            FROM requests r 
            LEFT JOIN members m ON r.member_id = m.id 
            WHERE r.status = 'pending' 
            ORDER BY r.id ASC";

    $result = $connGym->query($sql);
    if (!$result)
        return;

    while ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $memberName = htmlspecialchars($row['member_name'] ?? '');
        $data = json_decode($row['new_value'], true); // Decode JSON early

        if (empty($memberName) && isset($data['form']['name'])) {
            $memberName = htmlspecialchars($data['form']['name']) . ' (New)';
        }
        $type = htmlspecialchars($row['request_type']);
        $date = date('d M Y - H:i', strtotime($row['created_at']));

        $details = '';
        if ($type === 'email_change') {
            $details = "Requested new email: <strong>" . htmlspecialchars($row['new_value']) . "</strong>";
        } elseif ($type === 'membership') {
            $plan_id = $data['plan_id'] ?? 0;
            $planInfo = get_plan_by_id($plan_id);
            $planName = $planInfo ? htmlspecialchars($planInfo['name']) : 'Unknown Plan';
            $durationMonths = $data['durationMonths'] ?? '';
            $email = $data['form']['email'] ?? '';
            $phone = $data['form']['phone'] ?? '';
            $gender = $data['form']['gender'] ?? '';

            $detailsList = [];
            $detailsList[] = "Requested Plan: <strong>{$planName}</strong>";
            if ($durationMonths)
                $detailsList[] = "Duration: <strong>{$durationMonths} Month(s)</strong>";
            if ($email)
                $detailsList[] = "Email: " . htmlspecialchars($email);
            if ($phone)
                $detailsList[] = "Phone: " . htmlspecialchars($phone);
            if ($gender)
                $detailsList[] = "Gender: " . htmlspecialchars($gender);
            $detailsList[] = "Paid: <strong>" . htmlspecialchars($row['amount']) . " DA</strong>";

            $details = implode("<br>", $detailsList);
        } else {
            $details = htmlspecialchars($row['new_value']);
        }

        echo "
        <tr>
            <td>{$id}</td>
            <td><strong>{$memberName}</strong></td>
            <td><span class='badge'>{$type}</span></td>
            <td>{$details}</td>
            <td>{$date}</td>
            <td>
                <form method='POST' action='requests.php' style='display:inline;'>
                    <input type='hidden' name='action' value='accept'>
                    <input type='hidden' name='request_id' value='{$id}'>
                    <input type='hidden' name='request_type' value='{$type}'>
                    <button type='submit' class='btn btn-edit'>Accept</button>
                </form>
                <form method='POST' action='requests.php' style='display:inline;' onsubmit='return confirm(\"Are you sure you want to reject this request?\");'>
                    <input type='hidden' name='action' value='reject'>
                    <input type='hidden' name='request_id' value='{$id}'>
                    <button type='submit' class='btn btn-delete'>Reject</button>
                </form>
            </td>
        </tr>
        ";
    }
}

function accept_email_change_request($request_id)
{
    global $connGym;
    $admin_id = $_SESSION['user_id'] ?? null;
    if ($admin_id === null)
        return false;
    if (!$connGym || empty($request_id))
        return false;

    $connGym->begin_transaction();
    try {
        $stmt = $connGym->prepare("SELECT member_id, new_value FROM requests WHERE id = ? AND request_type = 'email_change' AND status = 'pending'");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $req = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$req) {
            $connGym->rollback();
            return false;
        }

        $member_id = $req['member_id'];
        $new_email = $req['new_value'];

        $uStmt = $connGym->prepare("UPDATE members SET email = ? WHERE id = ?");
        $uStmt->bind_param("si", $new_email, $member_id);
        $uStmt->execute();
        $uStmt->close();

        // $admin_id is fetched at the top of the function
        $sStmt = $connGym->prepare("UPDATE requests SET status = 'approved', accepted_by = ? WHERE id = ?");
        $sStmt->bind_param("ii", $admin_id, $request_id);
        $sStmt->execute();
        $sStmt->close();

        $connGym->commit();
        return true;
    } catch (Exception $e) {
        $connGym->rollback();
        return false;
    }
}

function accept_new_member_request($request_id)
{
    global $connGym;
    $admin_id = $_SESSION['user_id'] ?? null;
    if ($admin_id === null)
        return false;
    if (!$connGym || empty($request_id))
        return false;

    $connGym->begin_transaction();
    try {
        $stmt = $connGym->prepare("SELECT * FROM requests WHERE id = ? AND request_type = 'membership' AND status = 'pending'");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $req = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$req) {
            $connGym->rollback();
            return false;
        }

        // Decode the JSON details we stored in handling_members.php
        $details = json_decode($req['new_value'], true);

        $member_id = $req['member_id'];
        if (!$member_id) {
            if (empty($details['form'])) {
                $connGym->rollback();
                return false;
            }
            $member_id = add_member($details['form']);
            if (!$member_id) {
                $connGym->rollback();
                return false;
            }
        }

        $plan_id = $details['plan_id'];
        $duration_id = $details['duration_id'];
        $durationMonths = $details['durationMonths'];
        $discount_pct = $details['discount_pct'];
        $price_paid = $details['price_paid'];

        $today = date('Y-m-d');
        $start_date = $today;
        $end_date = date('Y-m-d', strtotime("+$durationMonths months", strtotime($today)));
        $status = 'active'; // Admin approval instantly activates it

        $subStmt = $connGym->prepare("INSERT INTO subscription (member_id, plan_id, price_paid, start_date, end_date, durationMonths, duration_id, discount_pct, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $subStmt->bind_param("iiissidds", $member_id, $plan_id, $price_paid, $start_date, $end_date, $durationMonths, $duration_id, $discount_pct, $status);
        $subStmt->execute();
        $subStmt->close();

        // Update the request to approved, and also set the member_id so we have a record of who it was for
        $sStmt = $connGym->prepare("UPDATE requests SET status = 'approved', accepted_by = ?, member_id = ? WHERE id = ?");
        $sStmt->bind_param("iii", $admin_id, $member_id, $request_id);
        $sStmt->execute();
        $sStmt->close();

        $connGym->commit();

        // Send Approval Email
        $recipient_name = $details['form']['name'] ?? 'Member';
        $recipient_email = $details['form']['email'] ?? '';

        if (!empty($recipient_email)) {
            send_membership_approved_email($recipient_email, $recipient_name);
        }

        return true;
    } catch (Exception $e) {
        $connGym->rollback();
        return false;
    }
}

function reject_request($request_id)
{
    global $connGym;
    $admin_id = $_SESSION['user_id'] ?? null;
    if ($admin_id === null)
        return false;
    if (!$connGym || empty($request_id))
        return false;

    $connGym->begin_transaction();
    try {
        // Fetch the request details
        $stmt = $connGym->prepare("SELECT * FROM requests WHERE id = ? AND status = 'pending'");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $request = $result->fetch_assoc();
        $stmt->close();

        if (!$request) {
            $connGym->rollback();
            return false;
        }

        // Reject it
        $rStmt = $connGym->prepare("UPDATE requests SET status = 'rejected', accepted_by = ? WHERE id = ?");
        $rStmt->bind_param("ii", $admin_id, $request_id);
        $success = $rStmt->execute();
        $rStmt->close();

        if ($success) {
            $recipient_email = '';
            $recipient_name = 'Member';

            if ($request['request_type'] === 'membership') {
                $details = json_decode($request['new_value'], true);
                $recipient_email = $details['form']['email'] ?? '';
                $recipient_name = $details['form']['name'] ?? 'Member';
            } else {
                // Try to get member email if member_id is set
                if (!empty($request['member_id'])) {
                    $mStmt = $connGym->prepare("SELECT email, name FROM members WHERE id = ?");
                    $mStmt->bind_param("i", $request['member_id']);
                    $mStmt->execute();
                    $mResult = $mStmt->get_result();
                    if ($member = $mResult->fetch_assoc()) {
                        $recipient_email = $member['email'];
                        $recipient_name = $member['name'];
                    }
                    $mStmt->close();
                }
            }

            if (!empty($recipient_email)) {
                send_request_rejected_email($recipient_email, $recipient_name, $request['request_type']);
            }
            $connGym->commit();
            return true;
        }
    } catch (Exception $e) {
        $connGym->rollback();
        return false;
    }
}
