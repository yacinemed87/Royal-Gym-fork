<?php
$current_page = 'contact';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact | Gym Management System</title>
    <link rel="stylesheet" href="../css/contact.css">
    <link rel="icon" type="image/png" href="../assets/images/logo.png">
</head>

<body>
    <?php
    include __DIR__ . "/includes/header.php"
    ?>
    <main>
        <section>
            <h2>Send Us a Message</h2>
            <form action="#" method="post">
                <div>
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" required>
                    <span id="nameError" class="error-msg"></span>
                </div>

                <div>
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                    <span id="emailError" class="error-msg"></span>
                </div>

                <div>
                    <label for="subject">Subject</label>
                    <input type="text" id="subject" name="subject" required>
                    <span id="subjectError" class="error-msg"></span>
                </div>

                <div>
                    <label for="message">Message</label>
                    <textarea id="message" name="message" required></textarea>
                    <span id="messageError" class="error-msg"></span>
                </div>

                <button type="submit">Send Message</button>
            </form>
        </section>

        <section>
            <h2>Find Us on the Map</h2>
            <div class="map-placeholder">
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d23861.838587157177!2d6.565886483108256!3d36.26457091570315!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x12f1650023de1a59%3A0x40290e29d3b3ef66!2sPower%20fitness%20Constantine!5e0!3m2!1sen!2sdz!4v1777970682128!5m2!1sen!2sdz"
                    width="100%" height="300px" style="border:0;" allowfullscreen="" loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"></iframe>
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
    include __DIR__ . "/includes/footer.php"
    ?>

    <script src="../js/contact.js"></script>
    <script>
        document.querySelector('.menu-toggle').addEventListener('click', function() {
            document.querySelector('header nav').classList.toggle('open');
        });
    </script>
</body>

</html>
