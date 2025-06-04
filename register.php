<?php
session_start();
require_once "config/config.php";

$register_error = "";
$register_success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = trim($_POST['student_id']);
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if student_id or email already exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE student_id = ? OR email = ?");
    $stmt->bind_param("ss", $student_id, $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $register_error = "Student ID or Email already registered.";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (student_id, name, email, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $student_id, $name, $email, $password);
        if ($stmt->execute()) {
            $register_success = "Registration successful! <a href='index.php'>Log In</a>";
        } else {
            $register_error = "Error: " . $stmt->error;
        }
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --navy-dark: #18394b;
            --navy-darker: #142f3e;
            --green: #4CAF50;
            --green-hover: #388e3c;
            --blue-light: #b3d0f7;
            --white: #ffffff;
            --orange: #ff9800;
            --blue-link: #3498db;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Montserrat', sans-serif;
            height: 100vh;
            width: 100vw;
            overflow: hidden;
            margin: 0;
            padding: 0;
        }
        .container {
            display: flex;
            height: 100vh;
            width: 100vw;
        }
        .left-panel {
            flex: 1;
            background: linear-gradient(120deg, #a5c5fe 0%, #eaf4fe 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            min-width: 500px;
        }
        .right-panel {
            flex: 1;
            background-color: var(--navy-dark);
            display: flex;
            flex-direction: column;
            position: relative;
            min-width: 500px;
        }
        .illustration {
            width: 90%;
            height: auto;
            max-height: 90%;
            object-fit: contain;
        }
        .brand {
            position: absolute;
            top: 2rem;
            right: 2rem;
            color: var(--white);
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: 2px;
        }
        .login-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            width: 100%;
            padding: 0 4rem;
        }
        .login-form {
            width: 100%;
            max-width: 400px;
        }
        .login-title {
            color: var(--white);
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 2.5rem;
            text-align: center;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            margin-bottom: 1.5rem;
        }
        .form-label {
            color: var(--white);
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        .form-input {
            padding: 1rem;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            font-family: 'Montserrat', sans-serif;
            background-color: var(--white);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .login-button {
            width: 100%;
            padding: 1rem;
            background-color: var(--green);
            color: var(--white);
            border: none;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-family: 'Montserrat', sans-serif;
            margin-top: 1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .login-button:hover {
            background-color: var(--green-hover);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .signup-prompt {
            text-align: center;
            margin-top: 2rem;
            color: var(--white);
        }
        .signup-link {
            color: var(--blue-link);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }
        .signup-link:hover {
            color: #217dbb;
            text-decoration: underline;
        }
        .footer-info {
            position: absolute;
            left: 40px;
            bottom: 30px;
            color: #fff;
            font-size: 0.95rem;
            z-index: 3;
            line-height: 1.6;
        }
        .message.error {
            background: #fff0f0;
            color: #e53935;
            text-align: center;
            margin-bottom: 10px;
            font-size: 1.08rem;
            border-radius: 12px;
            padding: 12px 18px;
            margin-top: 10px;
            font-weight: 500;
            box-shadow: 0 2px 8px rgba(24,54,81,0.08);
        }
        .message.success {
            background: #eaffea;
            color: #388e3c;
            text-align: center;
            margin-bottom: 10px;
            font-size: 1.08rem;
            border-radius: 12px;
            padding: 12px 18px;
            margin-top: 10px;
            font-weight: 500;
            box-shadow: 0 2px 8px rgba(24,54,81,0.08);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="left-panel">
            <img src="assets/bts.png" alt="Register Illustration" class="illustration">
        </div>
        <div class="right-panel">
            <div class="brand">UNIKL MIIT</div>
            <div class="login-container">
                <div class="login-title">Events Start Here ! Take the first step</div>
                <?php if ($register_error): ?>
                    <div class="message error"><?php echo $register_error; ?></div>
                <?php endif; ?>
                <?php if ($register_success): ?>
                    <div class="message success"><?php echo $register_success; ?></div>
                <?php endif; ?>
                <form action="register.php" method="POST" autocomplete="off" class="login-form">
                    <div class="form-group">
                        <label class="form-label" for="student_id">Student ID</label>
                        <input class="form-input" type="text" id="student_id" name="student_id" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="name">Name</label>
                        <input class="form-input" type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="email">Student Email</label>
                        <input class="form-input" type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="password">Password</label>
                        <input class="form-input" type="password" id="password" name="password" required>
                    </div>
                    <button type="submit" class="login-button">Sign Up</button>
                </form>
                <div class="signup-prompt">
                    Already have an account? <a href="index.php" class="signup-link">Sign in</a>
                </div>
            </div>
            </div>
        </div>
    </div>
<?php if ($register_success): ?>
<div id="successModal" style="position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(0,0,0,0.35);display:flex;align-items:center;justify-content:center;z-index:9999;">
    <div style="background:#4CAF50;border-radius:18px;max-width:340px;width:90vw;padding:36px 24px 28px 24px;box-shadow:0 8px 32px #0002;text-align:center;">
        <div style="font-size:3rem;margin-bottom:12px;color:#fff;">&#10003;</div>
        <div style="color:#fff;font-size:1.2rem;font-weight:700;margin-bottom:8px;">SUCCESS</div>
        <div style="color:#fff;font-size:1rem;margin-bottom:24px;">Congratulations, your account has been successfully created.</div>
        <a href="index.php" style="display:inline-block;padding:12px 36px;background:#fff;color:#4CAF50;font-weight:600;border-radius:24px;text-decoration:none;font-size:1.1rem;box-shadow:0 2px 8px #0001;">Continue</a>
    </div>
</div>
<script>
    // Optionally, close modal after a few seconds
    // setTimeout(function(){ document.getElementById('successModal').style.display='none'; }, 4000);
</script>
<?php endif; ?>
</body>
</html>