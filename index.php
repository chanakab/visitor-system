<?php
require 'db.php';

// Fetch Active Services
$sql = "SELECT * FROM services";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart Visitor Kiosk - DS Office</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>

<div class="kiosk-container">
    <header class="kiosk-header">
        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5f/Emblem_of_Sri_Lanka.svg/1200px-Emblem_of_Sri_Lanka.svg.png" alt="Emblem" class="emblem">
        <div class="titles">
            <h1>Divisional Secretariat</h1>
            <h2>Smart Visitor Management System</h2>
            <p>Welcome! Please select a service to proceed.</p>
        </div>
    </header>

    <div class="content-wrapper">
        <div class="step-indicator">
            <span class="active">1. Select Service</span>
            <span>2. Enter Details</span>
            <span>3. Get Token</span>
        </div>

        <form action="generate_token.php" method="POST" class="service-form" id="tokenForm">
            <div class="service-grid">
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo '<label class="service-card">';
                        echo '<input type="radio" name="service_id" value="'.$row["id"].'" required>';
                        echo '<div class="card-content">';
                        echo '<span class="icon">🏛️</span>'; // You can replace with specific icons
                        echo '<h3>' . $row["name"] . '</h3>';
                        echo '</div>';
                        echo '</label>';
                    }
                } else {
                    echo "<p>No services available currently.</p>";
                }
                ?>
            </div>

            <div class="input-section">
                <h3>Your Details</h3>
                <div class="input-group">
                    <label for="nic">National ID (NIC) *</label>
                    <input type="text" id="nic" name="nic" placeholder="e.g., 199012345678" required>
                </div>
                <div class="input-group">
                    <label for="phone">Mobile Number (Optional)</label>
                    <input type="text" id="phone" name="phone" placeholder="e.g., 0771234567">
                    <small>For SMS notifications</small>
                </div>
            </div>

            <button type="submit" class="btn-generate">Print Token</button>
        </form>
    </div>

    <footer class="kiosk-footer">
        &copy; <?php echo date("Y"); ?> Divisional Secretariat. Smart Service Initiative.
    </footer>
</div>

<script>
    // Simple script to add active class to selected card
    const cards = document.querySelectorAll('.service-card');
    cards.forEach(card => {
        card.addEventListener('click', () => {
             cards.forEach(c => c.classList.remove('selected'));
             card.classList.add('selected');
        });
    });
</script>

</body>
</html>
