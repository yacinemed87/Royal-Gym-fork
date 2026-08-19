<?php
$current_page = 'classes';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gym Classes | Royal Gym</title>
  <link rel="stylesheet" href="../css/classes.css">
  <link rel="icon" type="image/png" href="../assets/images/logo.png">
</head>

<body>
  <?php
  include __DIR__ . "/includes/header.php"
  ?>
  <main>
    <section class="hero">
      <h1>Our Fitness Classes</h1>
      <p class="tagline">Find the perfect class to reach your fitness goals.</p>
    </section>

    <section id="filters" class="filters-section">
    </section>

    <section class="schedule-container">
      <div class="table-wrapper">
        <table>
          <caption>Weekly Class Schedule</caption>
          <thead>
            <tr>
              <th>Class Name</th>
              <th>Trainer</th>
              <th>Day</th>
              <th>Time</th>
              <th>Duration</th>
              <th>Level</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="classes-body">
          </tbody>
        </table>
      </div>
    </section>
  </main>

  <?php
  include __DIR__ . "/includes/footer.php"
  ?>
  <script type="module" src="../js/classes.js"></script>
  <script>
    document.querySelector('.menu-toggle').addEventListener('click', function() {
      document.querySelector('header nav').classList.toggle('open');
    });
  </script>
</body>

</html>
