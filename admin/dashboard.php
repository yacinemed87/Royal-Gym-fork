<!doctype html>
<html lang="en">
	<head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<title>Admin Dashboard | Royal Gym</title>
		<link rel="stylesheet" href="../css/admin/admin.css" />
		<link rel="stylesheet" href="../css/admin/dashboard.css" />
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
				<li><a href="./dashboard.php" class="active">Dashboard</a></li>
				<li><a href="./plans.php">Plans</a></li>
				<li><a href="./members.php">Members</a></li>
				<div class="nav-divider"></div>
				<li><a href="../index.php">Return to Home</a></li>
			</ul>
		</aside>

		<!-- Main Content -->
		<div class="main-content">
			<div class="page-header">
				<h1>Dashboard</h1>
				<p>Overview of your gym's performance and membership stats</p>
			</div>

			<!-- Stat Cards -->
			<div class="stats-grid">
				<div class="stat-card">
					<p class="stat-label">Total Members</p>
					<p class="stat-value" id="total-members">0</p>
				</div>
				<div class="stat-card">
					<p class="stat-label">Active Subscriptions</p>
					<p class="stat-value" id="active-subs">0</p>
				</div>
				<div class="stat-card">
					<p class="stat-label">Classes Today</p>
					<p class="stat-value" id="classes-today">0</p>
				</div>
				<div class="stat-card">
					<p class="stat-label">Most Popular Plan</p>
					<p class="stat-value" id="popular-plan">—</p>
				</div>
			</div>

			<!-- Two column: Recent Members + Chart -->
			<div class="two-col">
				<!-- Recent Registrations -->
				<div class="card">
					<h2 class="card-title">Recent Registrations</h2>
					<table>
						<thead>
							<tr>
								<th>Name</th>
								<th>Join Date</th>
								<th>Plan</th>
								<th>Email</th>
							</tr>
						</thead>
						<tbody id="recent-tbody">
							<!-- Rendered by dashboard.js -->
						</tbody>
					</table>
				</div>

				<!-- Plan Distribution Chart -->
				<div class="card">
					<h2 class="card-title">Plan Distribution</h2>
					<div class="chart-container">
						<div class="chart-row">
							<span class="chart-label">Starter</span>
							<div class="chart-track">
								<div
									class="chart-bar"
									id="bar-starter"
									style="width: 0%"
								></div>
							</div>
							<span class="chart-count" id="label-starter"
								>0</span
							>
						</div>
						<div class="chart-row">
							<span class="chart-label">Elite</span>
							<div class="chart-track">
								<div
									class="chart-bar"
									id="bar-elite"
									style="width: 0%"
								></div>
							</div>
							<span class="chart-count" id="label-elite">0</span>
						</div>
						<div class="chart-row">
							<span class="chart-label">Royal</span>
							<div class="chart-track">
								<div
									class="chart-bar"
									id="bar-royal"
									style="width: 0%"
								></div>
							</div>
							<span class="chart-count" id="label-royal">0</span>
						</div>
					</div>
				</div>
			</div>

			<footer class="admin-footer">
				<p>
					&copy; 2026 Royal Gym Management System. All rights
					reserved.
				</p>
			</footer>
		</div>

		<script type="module" src="../js/admin/dashboard.js"></script>
	</body>
</html>
