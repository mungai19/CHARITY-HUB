<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role('ORGANIZATION');
$reportOrg = (int)my_org()['org_id'];
require __DIR__ . '/../includes/report_view.php';
