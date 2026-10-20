
<?php
$pageTitle = "User Management";
include 'includes/header.php';
?>

<div class="card">

    <h2>Add System User</h2>
    <br>

    <form class="record-form" data-key="charity_users">

        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="name" required>
        </div>

        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" required>
        </div>

        <div class="form-group">
            <label>User Role</label>
            <select name="role" required>
                <option value="">Select role</option>
                <option>Administrator</option>
                <option>Staff</option>
                <option>Volunteer</option>
            </select>
        </div>

        <button type="submit">Add User</button>

    </form>

</div>

<div class="card">

    <h2>System Users</h2>
    <br>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody id="charity_users">
                <tr>
                    <td colspan="4">No users added yet.</td>
                </tr>
            </tbody>
        </table>
    </div>

</div>

<?php include 'includes/footer.php'; ?>
