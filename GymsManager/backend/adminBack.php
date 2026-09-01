<?php
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/gyms.php";
require_once __DIR__ . "/handling_members.php";

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

    echo "        <div class='nav-divider'></div>
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
                   p.name AS plan, s.status AS subscription_status
            FROM members m
            LEFT JOIN (
                SELECT s1.* 
                FROM subscription s1
                INNER JOIN (
                    SELECT member_id, MAX(id) AS max_id
                    FROM subscription
                    GROUP BY member_id
                ) s2 ON s1.id = s2.max_id
            ) s ON m.id = s.member_id
            LEFT JOIN plans p ON s.plan_id = p.id
            ORDER BY m.id DESC";

    $result = $connGym->query($sql);
    if (!$result)
        return;

    while ($row = $result->fetch_assoc()) {
        $plan = htmlspecialchars($row['plan'] ?? 'No Plan');
        $name = htmlspecialchars($row['name']);
        $email = htmlspecialchars($row['email']);
        $phone = htmlspecialchars($row['phone']);
        $gender = htmlspecialchars($row['gender']);
        $joinDate = htmlspecialchars($row['joinDate']);
        $can_delete = ($_SESSION['role'] ?? '') === 'super_admin';
        $delete_form = "";
        if ($can_delete) {
            $delete_form = "
            <form method='POST' action='members.php' style='display:inline;' onsubmit='return confirm(\"Are you sure you want to delete {$name}?\");'>
                <input type='hidden' name='action' value='delete'>
                <input type='hidden' name='id' value='{$row['id']}'>
                <button type='submit' class='btn btn-delete'>Delete</button>
            </form>";
        }

        echo "
        <tr data-name='{$name}' data-plan='{$plan}'>
            <td><strong>{$name}</strong></td>
            <td>{$gender}</td>
            <td>{$email}</td>
            <td>{$phone}</td>
            <td><span class='badge'>{$plan}</span></td>
            <td>{$joinDate}</td>
            <td>
                <button type='button' class='btn btn-edit' data-id='{$row['id']}' data-name='{$name}' data-gender='{$gender}' data-email='{$email}' data-phone='{$phone}' data-plan='{$plan}' onclick='openEditModal(this)'>Edit</button>
                {$delete_form}
            </td>
        </tr>
        ";
    }
}

function write_plan_options()
{
    global $connGym;
    if (!$connGym)
        return;

    $result = $connGym->query("SELECT id, name FROM plans ORDER BY id ASC");
    if (!$result)
        return;

    while ($row = $result->fetch_assoc()) {
        $name = htmlspecialchars($row['name']);
        echo "<option value='{$name}'>{$name}</option>\n";
    }
}
