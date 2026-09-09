<?php
require_once __DIR__ . "/../config.php";
require_once PROJECT_ROOT . "/GymsManager/backend/require_admin.php";
require_once PROJECT_ROOT . "/GymsManager/backend/adminBack.php";

$gym = get_gym_info();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $request_id = intval($_POST['request_id'] ?? 0);
    $request_type = $_POST['request_type'] ?? '';

    if ($action === 'accept' && $request_id) {
        if ($request_type === 'membership') {
            accept_new_member_request($request_id);
        } elseif ($request_type === 'email_change') {
            accept_email_change_request($request_id);
        }
    } elseif ($action === 'reject' && $request_id) {
        reject_request($request_id);
    }
    
    // PRG pattern to prevent resubmission
    header("Location: requests.php");
    exit;
}
?>

<!doctype html>
<html lang="en">

<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Member Requests | <?= htmlspecialchars($gym['name'] ?? 'Gym'); ?></title>
	<link rel="stylesheet" href="../css/admin/admin.css" />
	<link rel="icon" type="image/png" href="../assets/images/<?= htmlspecialchars($gym['logo'] ?? 'logo.png'); ?>" />
</head>

<body>
	<?php write_admin_sidebar('requests'); ?>

	<!-- Main Content -->
	<div class="main-content">
		<div class="page-header">
			<h1>Member Requests</h1>
			<p>Manage and approve membership and email change requests.</p>
		</div>

		<!-- Placeholder for future requests table -->
		<div class="card">
			<h2 class="card-title">Pending Requests</h2>
			<table>
				<thead>
					<tr>
						<th>ID</th>
						<th>Member</th>
						<th>Request Type</th>
						<th>Details</th>
						<th>Date</th>
						<th>Actions</th>
					</tr>
				</thead>
				<tbody>
					<?php write_requests_table(); ?>
				</tbody>
			</table>
		</div>

	</div>
</body>

</html>