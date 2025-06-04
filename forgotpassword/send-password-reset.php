<?php
require_once "../config/config.php";
require_once "mailer.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["email"])) {
    $email = trim($_POST["email"]);
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows == 1) {
        $user = $result->fetch_assoc();
        $token = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

        // Insert token into password_resets table
        $stmt_insert = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
        $stmt_insert->bind_param("sss", $email, $token, $expires);
        $stmt_insert->execute();

        $reset_link = "http://" . $_SERVER["HTTP_HOST"] . dirname($_SERVER["PHP_SELF"]) . "/reset-password.php?token=$token";
        $subject = "Password Reset Request";
        $message = "Click the link below to reset your password:<br><a href=\"$reset_link\">Reset Password</a><br>This link will expire in 1 hour.";

        sendMail($email, $subject, $message);
    }
    // Always show the same message for security
    header("Location: ../index.php?reset=sent");
    exit();
} else {
    header("Location: ../index.php");
    exit();
}
?>