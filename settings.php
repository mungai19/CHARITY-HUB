
<?php
$pageTitle = "Settings";
include 'includes/header.php';
?>

<div class="card">

    <h2>Organization Information</h2>
    <br>

    <form id="settingsForm">

        <div class="form-group">
            <label>Organization Name</label>
            <input type="text" id="orgName"
                   value="CHARITY HUB" required>
        </div>

        <div class="form-group">
            <label>Organization Email</label>
            <input type="email" id="orgEmail"
                   placeholder="info@charityhub.org" required>
        </div>

        <div class="form-group">
            <label>Phone Number</label>
            <input type="tel" id="orgPhone"
                   placeholder="Enter phone number">
        </div>

        <div class="form-group">
            <label>Organization Address</label>
            <textarea id="orgAddress"
                      placeholder="Enter organization address"></textarea>
        </div>

        <button type="submit">Save Settings</button>

    </form>

    <p id="settingsMessage" aria-live="polite"></p>

</div>

<?php include 'includes/footer.php'; ?>
