<?php
require 'db.php';

// Get Institute Context (Default to 1 if not set)
$inst_id = isset($_GET['inst_id']) ? intval($_GET['inst_id']) : 1;

// Fetch Institute Details
$inst_res = $conn->query("SELECT * FROM institutes WHERE id = $inst_id");
if ($inst_res->num_rows == 0) die("Institute not found. Please check configuration.");
$inst_data = $inst_res->fetch_assoc();

// Fetch Active Services for THIS Institute
$sql = "SELECT * FROM services WHERE institute_id = $inst_id AND status='active'";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $inst_data['name']; ?> - Kiosk</title>
    <!-- We need basic styles but not necessarily all DataTables JS here, but lucide is key -->
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>

<div class="app-container" style="max-width: 900px; margin-top: 40px;">
    
    <header class="header-bar glass-panel">
        <div class="brand">
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5f/Emblem_of_Sri_Lanka.svg/1200px-Emblem_of_Sri_Lanka.svg.png" alt="Emblem">
            <div>
                <h1><?php echo $inst_data['name']; ?></h1>
                <p>Welcome! Please select a service to proceed.</p>
            </div>
        </div>
        <div style="font-size: 0.9rem; font-weight: 600; color: var(--primary);">
            <?php echo date("l, F j, Y"); ?>
        </div>
    </header>

    <div class="glass-panel">
        <!-- Progress Steps -->
        <form action="generate_token.php" method="POST">
            <input type="hidden" name="inst_id" value="<?php echo $inst_id; ?>">
            
            <h3 class="mb-4"><i data-lucide="grid"></i> Select Service</h3>
            
            <div class="cards-grid">
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        $icon = !empty($row['icon']) ? $row['icon'] : 'file-text';
                        echo '<label class="selection-card">';
                        echo '<input type="radio" name="service_id" value="'.$row["id"].'" required onchange="selectCard(this)">';
                        echo '<i data-lucide="'.$icon.'" style="width: 40px; height: 40px; color: var(--text-muted); margin-bottom: 10px;"></i>';
                        echo '<h4>' . $row["name"] . '</h4>';
                        echo '</label>';
                    }
                } else {
                    echo "<p>No services defined for this institute yet.</p>";
                }
                ?>
            </div>

            <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid var(--border-light);">
                <h3 class="mb-4"><i data-lucide="user"></i> Your Details</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: 600;">National ID (NIC) *</label>
                        <input type="text" name="nic" placeholder="e.g. 199012345678" required>
                    </div>
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: 600;">Mobile Number</label>
                        <input type="text" name="phone" placeholder="077xxxxxxx">
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-full btn-lg" style="margin-top: 30px;">
                Print Token <i data-lucide="printer"></i>
            </button>

        </form>
    </div>

    <footer class="text-center" style="margin-top: 40px; color: var(--text-muted);">
        &copy; <?php echo date("Y"); ?> Smart Citizen Services.
    </footer>
</div>

<script>
    lucide.createIcons();
    function selectCard(input) {
        document.querySelectorAll('.selection-card').forEach(c => c.classList.remove('active'));
        input.parentElement.classList.add('active');
        input.parentElement.querySelector('svg').style.color = 'var(--primary)';
    }
</script>

</body>
</html>
