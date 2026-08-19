<?php
$current_page = 'trainers';
?>


<!doctype html>
<html lang="en">

<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Trainers | Gym Management System</title>

	<link
		href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@300;400;600&family=Roboto:wght@700&display=swap"
		rel="stylesheet" />
	<link rel="stylesheet" href="../css/trainers.css" />
	<link rel="icon" type="image/png" href="../assets/images/logo.png">
</head>

<body>
	<?php
	include  __DIR__ . "/includes/header.php"
	?>

	<main>
		<section>
			<h2>Meet Our Trainers</h2>
			<p>
				Our gym has experienced trainers who specialize in different
				fitness areas to help you achieve your goals.
			</p>

			<div class="trainer-container">
				<article>
					<figure>
						<img src="../assets/images/Alikhodja-Yacine.jpg" />
						<figcaption>Alikhodja Yacine</figcaption>
					</figure>
					<h3>Specialty: Strength Training</h3>
					<p><strong>Years of Experience:</strong> 8 years</p>
					<p>
						Yacine helps members build muscle, improve strength,
						and follow proper workout techniques.
					</p>
					<a href="#">View Full Profile</a>
				</article>

				<article>
					<figure>
						<img src="../assets/images/Galileo-Paul.jpg" />
						<figcaption>Galileo Paul</figcaption>
					</figure>
					<h3>Specialty: crossfit</h3>
					<p><strong>Years of Experience:</strong> 6 years</p>
					<p>
						paul focuses on flexibility, balance, and functional
						movements.
					</p>
					<a href="#">View Full Profile</a>
				</article>

				<article>
					<figure>
						<img src="../assets/images/Achour-Iskander.png" />
						<figcaption>Achour Iskander</figcaption>
					</figure>
					<h3>Specialty: Cardio Fitness</h3>
					<p><strong>Years of Experience:</strong> 5 years</p>
					<p>
						Iskander l designs effective cardio programs for
						weight loss, endurance, and heart health
						improvement.
					</p>
					<a href="#">View Full Profile</a>
				</article>

				<article>
					<figure>
						<img src="../assets/images/Mark.jpg" alt="Photo of trainer Emily Davis" />
						<figcaption>Mark</figcaption>
					</figure>
					<h3>Specialty: Personal Training</h3>
					<p><strong>Years of Experience:</strong> 7 years</p>
					<p>
						Djaber works one-on-one with clients to create
						custom fitness plans based on personal goals.
					</p>
					<br />
					<a href="#">View Full Profile</a>
				</article>
			</div>
		</section>

		<section class="work-with-us">
			<h2>Work With Us</h2>
			<p>
				Are you a certified fitness trainer looking to join our
				team? We are always searching for passionate professionals.
			</p>
			<a href="/client/contact.php">Contact Us to Apply</a>
		</section>

		<section>
			<h2>Trainer Contact Information</h2>
			<address>
				Gym Management System<br />
				123 Fitness Street<br />
				Ali Mendjeli , n(15)part 18<br />
				Phone: +213 781292716<br />
				Email: djeberboudjerda@gmail.com
			</address>
		</section>
	</main>

	<?php
	include __DIR__ . "/includes/footer.php"
	?>

	<script src="../js/trainers.js"></script>
	<script>
		document.querySelector('.menu-toggle').addEventListener('click', function() {
			document.querySelector('header nav').classList.toggle('open');
		});
	</script>
</body>

</html>
