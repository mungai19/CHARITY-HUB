<?php
// Loaded by every page. Sets up session, database, helpers.
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}
date_default_timezone_set('Africa/Nairobi');

define('ROOT_PATH', realpath(__DIR__ . '/..'));

// Work out the web address of the project folder automatically,
// so it works at http://localhost/CHARITY-HUB/ or any other folder name.
$__docroot = rtrim(str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: ''), '/');
$__root    = str_replace('\\', '/', ROOT_PATH);
$__base    = ($__docroot !== '' && strpos($__root, $__docroot) === 0) ? substr($__root, strlen($__docroot)) : '';
define('BASE_URL', rtrim($__base, '/'));

require_once ROOT_PATH . '/config/db.php';

/* ---------- small helpers ---------- */
function e($v) { return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
function url($path = '') { return BASE_URL . '/' . ltrim($path, '/'); }
function asset($path) { return url('assets/' . ltrim($path, '/')); }
function redirect($path) {
    if (!preg_match('#^https?://#', $path)) $path = url($path);
    header("Location: " . $path);
    exit();
}
function money($n) { return 'KES ' . number_format((float)$n, 2); }
function fdate($d, $fmt = 'd M Y') { return $d ? date($fmt, strtotime($d)) : '—'; }

function flash($type, $msg) { $_SESSION['flash'][] = ['type' => $type, 'msg' => $msg]; }
function get_flash() { $f = $_SESSION['flash'] ?? []; unset($_SESSION['flash']); return $f; }

function csrf_token() {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_field() { return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">'; }
function csrf_check() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $ok = isset($_POST['csrf'], $_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $_POST['csrf']);
        if (!$ok) { http_response_code(419); exit('Session expired. Go back, refresh the page and try again.'); }
    }
}

/* ---------- database helpers (always prepared statements) ---------- */
function db_run($sql, $params = []) {
    global $conn;
    $stmt = $conn->prepare($sql);
    if (!$stmt) { error_log("SQL error: " . $conn->error . " | " . $sql); return false; }
    if ($params) {
        $types = '';
        foreach ($params as $p) $types .= is_int($p) ? 'i' : (is_float($p) ? 'd' : 's');
        $stmt->bind_param($types, ...$params);
    }
    if (!$stmt->execute()) { error_log("SQL exec error: " . $stmt->error); $stmt->close(); return false; }
    return $stmt;
}
function db_all($sql, $params = []) {
    $s = db_run($sql, $params); if (!$s) return [];
    $r = $s->get_result(); $rows = $r ? $r->fetch_all(MYSQLI_ASSOC) : []; $s->close(); return $rows;
}
function db_one($sql, $params = []) { $rows = db_all($sql, $params); return $rows[0] ?? null; }
function db_val($sql, $params = [], $default = 0) {
    $row = db_one($sql, $params); if (!$row) return $default;
    $v = array_values($row)[0]; return $v ?? $default;
}
function db_exec($sql, $params = []) {
    global $conn; $s = db_run($sql, $params); if (!$s) return false;
    $id = $conn->insert_id; $aff = $s->affected_rows; $s->close(); return $id ?: ($aff >= 0 ? true : false);
}

/* ---------- auth helpers ---------- */
function dashboard_path($role) {
    return [
        'ADMIN' => 'admin/dashboard.php', 'ORGANIZATION' => 'organization/dashboard.php',
        'DONOR' => 'donor/dashboard.php', 'VOLUNTEER' => 'volunteer/dashboard.php',
        'BENEFICIARY' => 'beneficiary/dashboard.php',
    ][$role] ?? 'auth/login.php';
}
function current_user() { return $_SESSION['user'] ?? null; }
function require_login() {
    if (empty($_SESSION['user'])) { flash('error', 'Please log in to continue.'); redirect('auth/login.php'); }
}
function require_role($roles) {
    require_login();
    $roles = (array)$roles;
    if (!in_array($_SESSION['user']['role'], $roles, true)) {
        flash('error', 'You do not have access to that page.');
        redirect(dashboard_path($_SESSION['user']['role']));
    }
}
function login_user($u) {
    session_regenerate_id(true);
    $_SESSION['user'] = ['id' => (int)$u['user_id'], 'name' => $u['name'], 'email' => $u['email'], 'role' => $u['role']];
}
// Organization record for the logged-in ORGANIZATION user
function my_org() {
    static $org = false;
    if ($org === false) $org = db_one("SELECT * FROM organizations WHERE user_id = ?", [current_user()['id']]);
    return $org;
}
function status_badge($s) {
    $map = ['ACTIVE'=>'ok','VERIFIED'=>'ok','COMPLETED'=>'ok','APPROVED'=>'ok','FULFILLED'=>'ok','ONGOING'=>'info','OPEN'=>'info',
            'PENDING'=>'warn','SUBMITTED'=>'warn','PLANNED'=>'warn','SUSPENDED'=>'bad','REJECTED'=>'bad','DECLINED'=>'bad','CANCELLED'=>'bad','ON_HOLD'=>'warn','CLOSED'=>'muted'];
    $c = $map[strtoupper($s)] ?? 'muted';
    return '<span class="badge badge-' . $c . '">' . e(ucwords(strtolower(str_replace('_', ' ', $s)))) . '</span>';
}
function post($k, $d = '') { return trim($_POST[$k] ?? $d); }
