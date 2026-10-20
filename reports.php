
<?php
$pageTitle = "Reports";
include 'includes/header.php';
?>

<div class="cards">

    <div class="card">
        <h3>Donation Report</h3>
        <p>View donations received by the organization.</p>
    </div>

    <div class="card">
        <h3>Beneficiary Report</h3>
        <p>View registered beneficiaries.</p>
    </div>

    <div class="card">
        <h3>Project Report</h3>
        <p>View ongoing and completed projects.</p>
    </div>

</div>

<div class="card">
    <h2>Report Summary</h2>
    <br>

    <table>
        <thead>
            <tr>
                <th>Report Type</th>
                <th>Description</th>
                <th>Action</th>
            </tr>
        </thead>

        <tbody>
            <tr>
                <td>Donations</td>
                <td>Donation records and amounts</td>
                <td><button onclick="window.print()">Print Report</button></td>
            </tr>

            <tr>
                <td>Beneficiaries</td>
                <td>Beneficiary registration records</td>
                <td><button onclick="window.print()">Print Report</button></td>
            </tr>

            <tr>
                <td>Projects</td>
                <td>Project status and budgets</td>
                <td><button onclick="window.print()">Print Report</button></td>
            </tr>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>
