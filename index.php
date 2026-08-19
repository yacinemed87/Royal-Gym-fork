<?php
$current_page = 'home';
$active_gym   = 'royal-gym';
require_once __DIR__ . "/backend/gyms.php";
$gym = current_gym();
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="./css/index.css">

	<title><?php echo htmlspecialchars($gym["name"]); ?></title>
	<link rel="icon" type="image/png" href="assets/images/<?php echo htmlspecialchars($gym["logo"]); ?>">
</head>

<body>
	<?php
	include __DIR__ . "/client/includes/header.php"
	?>
	<main>
		<section class="hero">
			<h1><?php echo htmlspecialchars($gym["name"]); ?></h1>
			<p class="tagline"><?php echo htmlspecialchars($gym["tagline"]); ?></p>
			<a href="./client/membership.php">Sign Up Today</a>
		</section>
		<section class="facilities">
			<h2>Facilities</h2>
			<div class="facilities-container">
				<article class="facility-card">
					<h3>Cardio Room</h3>
					<p>
						A fully equipped space with treadmills, bikes, and
						ellipticals for endurance training
					</p>
					<img src="./assets/images/cardio-room.png" alt="Royal Gym Cardio Rooms Picture">
				</article>
				<article class="facility-card">
					<h3>Weights Area</h3>
					<p>
						Free weights and machines for strength and muscle
						building
					</p>
					<img src="./assets/images/weights-area.png" alt="Royal Gym Weights Area Picture">
				</article>
				<article class="facility-card">
					<h3>Locker Rooms</h3>
					<p>
						Secure, modern changing rooms with showers and
						lockers
					</p>
					<img src="./assets/images/locker-rooms.png" alt="Royal Gym Locker Rooms Picture">
				</article>
				<article class="facility-card">
					<h3>Personal Training Zone</h3>
					<p>
						A private area for one-on-one sessions with
						certified trainers
					</p>
					<img src="./assets/images/personal-training-zone.png"
						alt="Royal Gym Personal Training Zone Picture">
				</article>
				<article class="facility-card">
					<h3>Sauna & Steam</h3>
					<p>
						A luxurious space to relax, recover, and detox in
						our premium sauna and steam rooms
					</p>
					<img src="./assets/images/sauna-steam.png" alt="Royal Gym Sauna and Steam Room Picture">
				</article>
				<article class="facility-card">
					<h3>Recovery & Massage</h3>
					<p>
						Professional therapists offering massages and tailored
						recovery sessions in a peaceful environment
					</p>
					<img src="./assets/images/recovery.png" alt="Royal Gym Recovery and Massage Room Picture">
				</article>
			</div>
		</section>
		<section class="membership">
			<h2>Membership Plans</h2>
			<div class="membership-cards-container" id="plans-preview">
				<!-- Rendered by data.js -->
			</div>
		</section>
		<section class="opening-hours">
			<h2>Opening Hours</h2>
			<table>
				<tr>
					<th>Day</th>
					<th>Opening Time</th>
					<th>Closing Time</th>
				</tr>
				<tr>
					<td>Sunday to Thursday</td>
					<td>6:00 AM</td>
					<td>10:00 PM</td>
				</tr>
				<tr>
					<td>Friday</td>
					<td>2:30 PM</td>
					<td>12:00 PM</td>
				</tr>
				<tr>
					<td>Saturday</td>
					<td>8:00 AM</td>
					<td>10:00 PM</td>
				</tr>
			</table>
		</section>
	</main>
	<?php
	include __DIR__ . "/client/includes/footer.php"
	?>
	<script>
		document.querySelector('.menu-toggle').addEventListener('click', function() {
			document.querySelector('header nav').classList.toggle('open');
		});
	</script>
	<script type="module">
		import {
			membershipPlans
		} from './js/data.js';

		function getPlans() {
			const stored = localStorage.getItem('plans');
			if (stored) {
				try {
					const parsed = JSON.parse(stored);
					if (Array.isArray(parsed) && parsed.length > 0) return parsed;
				} catch (_) {}
			}
			return membershipPlans;
		}

		const container = document.getElementById('plans-preview');
		const highlightIndex = 1; // Recommended plan index
		const plans = getPlans();

		container.innerHTML = plans.map((plan, i) => {
			const cardClass = i === highlightIndex ? 'membership-card elite' : 'membership-card';
			const features = plan.features.map(f => `<dd>${f}</dd>`).join('');
			const recommended = i === highlightIndex ?
				`<p class="price">Recommended</p>` :
				'';
			return `
				<article class="${cardClass}">
					<h3>${plan.name}</h3>
					<p class="price">${Number(plan.price).toLocaleString()} DA / ${plan.duration}</p>
					<dl>
						<dt>Features</dt>
						${features}
					</dl>
					${recommended}
				</article>`;
		}).join('');
	</script>
</body>

</html>
