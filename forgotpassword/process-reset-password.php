<?php
require_once "../config/config.php";
$conn->query("SET time_zone = '+00:00'"); // Make sure to set the timezone

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["token"], $_POST["password"], $_POST["confirm_password"])) {
    $token = $_POST["token"];
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        // Check if token is valid and not expired
        $stmt = $conn->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result && $result->num_rows == 1) {
            // Token is valid, proceed to reset password
            $row = $result->fetch_assoc();
            $email = $row['email'];
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Update user's password
            $stmt_update = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt_update->bind_param("ss", $hashed_password, $email);
            $stmt_update->execute();

            // Remove the token so it can't be reused
            $stmt_delete = $conn->prepare("DELETE FROM password_resets WHERE token = ?");
            $stmt_delete->bind_param("s", $token);
            $stmt_delete->execute();

            $success = true;
        } else {
            // Invalid or expired token
            $error = "Invalid or expired token.";
        }
    }
} else {
    $error = "Invalid request.";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Password Reset Result</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; }
        .container { max-width: 400px; margin: 80px auto; background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 8px #ccc; }
        .error { color: #c62828; margin-bottom: 1rem; }
        .success { color: #2e7d32; margin-bottom: 1rem; }
        a { color: #3498db; }
    </style>
</head>
<body>
    <div class="container">
        <?php if (isset($success) && $success): ?>
            <div class="success">Your password has been reset successfully. <a href="../index.php">Login</a></div>
        <?php else: ?>
            <div class="error"><?php echo $error ?? "An error occurred."; ?></div>
            <a href="../index.php">Back to Login</a>
        <?php endif; ?>
    </div>
</body>
</html>