<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_login();
redirect(dashboard_path(current_user()['role']));
