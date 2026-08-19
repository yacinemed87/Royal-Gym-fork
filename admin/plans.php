<!doctype html>
<html lang="en">
	<head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<title>Membership Plans | Royal Gym</title>
		<link rel="stylesheet" href="../css/admin/admin.css" />
		<link rel="icon" type="image/png" href="../assets/images/logo.png" />
	</head>

	<body>
		<!-- Sidebar -->
		<aside class="sidebar">
			<a href="../index.php" class="sidebar-brand">
				Royal Gym
				<img src="../assets/images/logo.png" alt="Royal Gym Logo" />
			</a>
			<ul class="sidebar-nav">
				<li><a href="./dashboard.php">Dashboard</a></li>
				<li><a href="./plans.php" class="active">Plans</a></li>
				<li><a href="./members.php">Members</a></li>
				<div class="nav-divider"></div>
				<li><a href="../index.php">Return to Home</a></li>
			</ul>
		</aside>

		<!-- Main Content -->
		<div class="main-content">
			<div class="page-header">
				<h1>Membership Plans</h1>
				<p>Manage your gym's membership plans and offerings</p>
			</div>

			<!-- Current Plans Table -->
			<div class="card">
				<h2 class="card-title">Current Plans</h2>

				<!-- Toolbar: search + add button -->
				<div class="toolbar">
					<input
						type="text"
						id="plan-search"
						placeholder="Search by name or feature..."
					/>
					<button class="btn btn-gold" onclick="openAddModal()">
						Add Plan
					</button>
					<span class="count"
						>Showing <strong id="plan-count">0</strong> plans</span
					>
				</div>

				<table>
					<thead>
						<tr>
							<th>Plan Name</th>
							<th>Price</th>
							<th>Duration</th>
							<th>Features</th>
							<th>Actions</th>
						</tr>
					</thead>
					<tbody id="plans-tbody">
						<!-- Rendered by plans.js -->
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

		<!-- ========== ADD / EDIT PLAN MODAL ========== -->
		<div id="planModal" class="modal-overlay hidden">
			<div class="modal">
				<div class="modal-header">
					<h2 id="modalTitle">Add Plan</h2>
					<button class="modal-close" onclick="closeModal()">
						✕
					</button>
				</div>

				<div class="form-group">
					<label class="label">Plan Name</label>
					<input
						type="text"
						id="inputName"
						class="input"
						placeholder="e.g. Platinum"
					/>
				</div>
				<div class="form-group">
					<label class="label">Price (DA)</label>
					<input
						type="number"
						id="inputPrice"
						class="input"
						placeholder="e.g. 20000"
					/>
				</div>
				<div class="form-group">
					<label class="label">Duration</label>
					<select id="inputDuration" class="input select">
						<option value="">Select duration</option>
						<option value="1 month">1 Month</option>
						<option value="3 months">3 Months</option>
						<option value="6 months">6 Months</option>
						<option value="1 year">1 Year</option>
					</select>
				</div>
				<div class="form-group">
					<label class="label">Features</label>
					<textarea
						id="inputFeatures"
						class="input"
						placeholder="List features separated by commas, e.g. Gym access, Sauna, Meal plan"
						style="min-height: 80px; resize: vertical"
					></textarea>
				</div>

				<p class="error-msg hidden" id="errorMsg">
					⚠ A plan with this name already exists.
				</p>

				<div class="modal-footer">
					<button class="btn btn-secondary" onclick="closeModal()">
						Cancel
					</button>
					<button class="btn btn-primary" onclick="savePlan()">
						Save
					</button>
				</div>
			</div>
		</div>

		<!-- ========== DELETE CONFIRMATION MODAL ========== -->
		<div id="deleteModal" class="modal-overlay hidden">
			<div class="modal">
				<h2>Delete Plan?</h2>
				<p style="color: var(--text-light); margin: 0.5rem 0 1.5rem">
					This action cannot be undone. Members on this plan will need
					to be reassigned.
				</p>
				<div class="modal-footer">
					<button
						class="btn btn-secondary"
						onclick="closeDeleteModal()"
					>
						Cancel
					</button>
					<button class="btn btn-danger" onclick="confirmDelete()">
						Delete
					</button>
				</div>
			</div>
		</div>

		<!-- Scripts -->
		<script type="module" src="../js/admin/plans.js"></script>
	</body>
</html>
