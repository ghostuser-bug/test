<?php
require_once "../config/config.php";
$conn->query("SET time_zone = '+00:00'"); // or your local timezone, e.g. '+08:00'

$token = $_GET['token'] ?? '';

// Debug code start
echo "PHP time: " . date('Y-m-d H:i:s') . "<br>";
$res = $conn->query("SELECT NOW() as mysql_now");
$row = $res->fetch_assoc();
echo "MySQL NOW(): " . $row['mysql_now'] . "<br>";
echo "Token from URL: " . htmlspecialchars($token) . "<br>";

$stmt = $conn->prepare("SELECT * FROM password_resets WHERE token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $result->num_rows == 1) {
    $row = $result->fetch_assoc();
    echo "Token found in DB. Expires at: " . $row['expires_at'] . "<br>";
} else {
    echo "Token not found in DB.<br>";
}
// Debug code end

$stmt = $conn->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows == 1) {
    // Token is valid, show the reset password form
    $error = "";
} else {
    // Invalid or expired token
    echo '<div style="max-width:500px;margin:40px auto;padding:40px 30px;background:#fff;border-radius:12px;box-shadow:0 2px 12px #0001;">
            <h2 style="font-size:2rem;font-weight:700;margin-bottom:18px;">Reset Password</h2>
            <div style="color:#d32f2f;font-size:1.2rem;">Invalid or expired token.</div>
          </div>';
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .container { max-width: 400px; margin: 80px auto; background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 8px #ccc; }
        .form-group { margin-bottom: 1.5rem; }
        .form-label { font-weight: bold; }
        .form-input { width: 100%; padding: 0.8rem; border-radius: 4px; border: 1px solid #ccc; }
        .login-button { width: 100%; padding: 1rem; background: #4CAF50; color: #fff; border: none; border-radius: 50px; font-weight: bold; cursor: pointer; }
        .error { color: #c62828; margin-bottom: 1rem; }
        .success { color: #2e7d32; margin-bottom: 1rem; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Reset Password</h2>
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php else: ?>
            <form method="POST" action="process-reset-password.php">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                <div class="form-group">
                    <label class="form-label" for="password">New Password</label>
                    <input class="form-input" type="password" name="password" id="password" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="confirm_password">Confirm Password</label>
                    <input class="form-input" type="password" name="confirm_password" id="confirm_password" required>
                </div>
                <button type="submit" class="login-button">Reset Password</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>