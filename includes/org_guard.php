<?php
// Organization pages: must have an org record; shows pending banner via $orgVerified
require_role('ORGANIZATION');
$org = my_org();
if (!$org) { flash('error', 'Organization profile not found.'); redirect('auth/logout.php'); }
$orgId = (int)$org['org_id'];
$orgVerified = $org['verification_status'] === 'VERIFIED';
function org_notice() {
    global $org;
    if ($org['verification_status'] === 'PENDING') echo '<div class="alert alert-warn">⏳ Your organization is <b>awaiting verification</b>. You can set up projects and events now; they become visible to donors and volunteers once an administrator verifies you.</div>';
    elseif ($org['verification_status'] === 'REJECTED') echo '<div class="alert alert-error">Your organization was not verified. Please update your details in <a href="' . url('profile.php') . '">My Profile</a> or contact the administrator.</div>';
}
