<?php
require_once __DIR__ . "/../config.php";
require_once PROJECT_ROOT . "/GymsManager/backend/require_admin.php";
require_once PROJECT_ROOT . "/GymsManager/backend/adminBack.php";
require_once PROJECT_ROOT . "/GymsManager/backend/membershipBack.php";
require_once PROJECT_ROOT . "/GymsManager/backend/gyms.php";

$gym = get_gym_info();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$action = $_POST['action'] ?? '';

	if ($action === 'add') {
		add_subscription($_POST);
	} elseif ($action === 'edit') {
		update_member($_POST);
	} elseif ($action === 'delete') {
		if (($_SESSION['role'] ?? '') === 'super_admin') {
				$delete_id = intval($_POST['id'] ?? 0);
			$stmt1 = $connGym->prepare("DELETE FROM subscription WHERE member_id = ?");
			$stmt1->bind_param("i", $delete_id);
			$stmt1->execute();
			$stmt1->close();
			$stmt2 = $connGym->prepare("DELETE FROM members WHERE id = ?");
			$stmt2->bind_param("i", $delete_id);
			$stmt2->execute();
			$stmt2->close();
		}
	}

	// Refresh page to see updated list
	header("Location: members.php");
	exit;
}


?>


<!doctype html>
<html lang="en">

<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Members Management | <?= htmlspecialchars($gym['name'] ?? 'Gym'); ?></title>
	<link rel="stylesheet" href="../css/admin/admin.css" />
	<link rel="icon" type="image/png" href="../assets/images/<?= htmlspecialchars($gym['logo'] ?? 'logo.png'); ?>" />
</head>

<body>
	<?php write_admin_sidebar('members'); ?>

	<!-- Main Content -->
	<div class="main-content">
		<div class="page-header">
			<h1>Members</h1>
			<p>Manage gym members — add, edit, search, and remove</p>
		</div>

		<!-- Members Table -->
		<div class="card">
			<h2 class="card-title">All Members</h2>

			<!-- Toolbar: search + filter -->
			<div class="toolbar">
				<input type="text" id="member-search" placeholder="Search by name..." />
				<select id="plan-filter">
					<option value="All">All Plans</option>
					<?php write_plan_options(); ?>
				</select>
				<button class="btn btn-gold" onclick="openAddModal()">
					Add Member
				</button>
				<span class="count">Total: 
					<strong id="member-count"><?= $connGym ? ($connGym->query("SELECT COUNT(*) as cnt FROM members")->fetch_assoc()['cnt'] ?? 0) : 0; ?></strong> members</span>
			</div>

			<table>
				<thead>
					<tr>
						<th>Name</th>
						<th>Gender</th>
						<th>Email</th>
						<th>Phone</th>
						<th>Plan</th>
						<th>Join Date</th>
						<th>Actions</th>
					</tr>
				</thead>
				<tbody>
					<?php write_members_table(); ?>
				</tbody>

			</table>
		</div>

		<footer class="admin-footer">
			<p>
				&copy; 2026 Royal Gym Management System. All rights
				reserved.
			</p>
		</footer>
	</div>

	<div id="memberModal" class="modal-overlay hidden">
		<div class="modal">
			<div class="modal-header">
				<h2 id="modalTitle">Add Member</h2>
				<button type="button" class="modal-close" onclick="closeModal()">
					✕
				</button>
			</div>

			<form action="members.php" method="POST" id="memberForm">
				<input type="hidden" name="action" id="formAction" value="add">
				<input type="hidden" name="id" id="inputId" value="">

				<div class="form-group">
					<label class="label">Full Name</label>
					<input type="text" name="name" id="inputName" class="input" placeholder="e.g. Sara Ahmed" required />
				</div>
				<div class="form-group">
					<label class="label">Gender</label>
					<select name="gender" id="inputGender" class="input select">
						<option value="Male">Male</option>
						<option value="Female">Female</option>
					</select>
				</div>
				<div class="form-group">
					<label class="label">Email</label>
					<input type="email" name="email" id="inputEmail" class="input" placeholder="e.g. sara@email.com" required />
				</div>
				<div class="form-group">
					<label class="label">Phone</label>
					<input type="text" name="phone" id="inputPhone" class="input" placeholder="e.g. +213555050550" required />
				</div>
				<div class="form-group" id="groupSubscription" style="display: none;">
					<label class="label">Target Subscription (Edit Only)</label>
					<select name="subscription_id" id="inputSubscriptionId" class="input select">
					</select>
				</div>
				<div class="form-group">
					<label class="label">Plan</label>
					<select name="plan" id="inputPlan" class="input select">
						<?php write_plan_options(); ?>
					</select>
				</div>
				<div class="form-group">
					<label class="label">Duration (Months)</label>
					<input type="number" name="durationMonths" id="inputDuration" class="input" value="1" min="1" required />
				</div>
				<div class="form-group" id="groupPriceDiff" style="display: none;">
					<label class="label">Price Adjustment (DA) <small>(Paid extra = positive, Refunded = negative)</small></label>
					<input type="number" name="price_diff" id="inputPriceDiff" class="input" value="0" />
				</div>

				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" onclick="closeModal()">
						Cancel
					</button>
					<button type="submit" class="btn btn-primary">
						Save
					</button>
				</div>
			</form>
		</div>
	</div>

	<!-- Scripts -->
	<script src="../js/admin/members.js?v=<?= filemtime(__DIR__ . '/../js/admin/members.js'); ?>"></script>
</body>

</html>