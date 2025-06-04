<?php
session_start();
require_once "config/config.php";

// Login logic
$error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $student_id = trim($_POST["student_id"]);
    $password = $_POST["password"];
    $stmt = $conn->prepare("SELECT * FROM users WHERE student_id = ?");
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user["password"])) {
            $_SESSION["student_id"] = $user["student_id"];
            $_SESSION["name"] = $user["name"];
            header("Location: student/eventsubmission.php");
            exit();
        } else {
            $error = "Invalid Student ID or Password.";
        }
    } else {
        $error = "Invalid Student ID or Password.";
    }
}

// Password recovery logic
$recovery_message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['recover_password'])) {
    $email = trim($_POST['recovery_email']);
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows == 1) {
        // Generate a temporary password
        $temp_password = bin2hex(random_bytes(4));
        $hashed_password = password_hash($temp_password, PASSWORD_DEFAULT);
        $stmt_update = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $stmt_update->bind_param("ss", $hashed_password, $email);
        $stmt_update->execute();
        // mail($email, "Password Recovery", "Your temporary password is: $temp_password");
        $recovery_message = "If this email is registered, a temporary password has been sent.";
    } else {
        $recovery_message = "If this email is registered, a temporary password has been sent.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>UNIKL MIIT Student Portal</title>
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
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
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
    align-items: center; /* centers vertically */
    justify-content: center; /* centers horizontally */
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
        
        .forgot-password {
            text-align: center;
            margin-top: 1.5rem;
        }
        
        .forgot-link {
            color: var(--orange);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }
        
        .forgot-link:hover {
            color: #e67e22;
            text-decoration: underline;
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
            color: #2980b9;
            text-decoration: underline;
        }
        
        .footer-info {
            position: absolute;
            bottom: 2rem;
            left: 2rem;
            color: var(--white);
            font-size: 0.9rem;
            line-height: 1.6;
        }
        
        .info-item {
            margin-bottom: 0.8rem;
        }
        
        .info-title {
            font-weight: 600;
            margin-bottom: 0.3rem;
        }
        
        .recovery-form {
            display: none;
            margin-top: 1.5rem;
            width: 100%;
        }
        
        .message {
            margin-bottom: 1.5rem;
            padding: 1rem;
            border-radius: 4px;
            text-align: center;
            font-weight: 500;
        }
        
        .error {
            background-color: #ffebee;
            color: #c62828;
        }
        
        .success {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        
        /* Responsive Design */
        @media (max-width: 992px) {
            .container {
                flex-direction: column;
                overflow-y: auto;
            }
            
            .left-panel, .right-panel {
                flex: none;
                width: 100%;
                height: 50vh;
                min-width: unset;
            }
            
            .footer-info {
                position: relative;
                left: 0;
                bottom: 0;
                padding: 1rem 2rem;
                text-align: center;
            }
            
            .brand {
                top: 1rem;
                right: 1rem;
            }
        }
        
        @media (max-width: 576px) {
            .login-container {
                padding: 0 2rem;
            }
            
            .login-title {
                font-size: 1.8rem;
            }
            
            .illustration {
                max-width: 90%;
            }
        }
    </style>
    <script>
        function toggleRecoveryForm() {
            var form = document.getElementById('recoveryForm');
            form.style.display = form.style.display === 'block' ? 'none' : 'block';
        }
    </script>
</head>
<body>
    <div class="container">
        <!-- Left Panel with Illustration -->
        <div class="left-panel">
            <img src="assets/poster.png" alt="Online Meeting Illustration" class="illustration">
        </div>
        
        <!-- Right Panel with Login Form -->
        <div class="right-panel">
            <div class="brand">UNIKL MIIT</div>
            
            <div class="login-container">
                <div class="login-form">
                    <form method="POST" action="" autocomplete="off" class="login-form">
                        <div class="login-title welcome-back-small">Student Event Portal</div>
                        <?php if ($error): ?>
                            <div class="message error"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <div class="form-group">
                            <label class="form-label" for="student_id">Student ID</label>
                            <input class="form-input" type="text" id="student_id" name="student_id" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="password">Password</label>
                            <input class="form-input" type="password" id="password" name="password" required>
                        </div>
                        <button type="submit" class="login-button" name="login">Login</button>
                    </form>
                    
                    <div class="forgot-password">
                        <a href="javascript:void(0);" class="forgot-link" onclick="toggleRecoveryForm()">Forgot Password?</a>
                    </div>
                    
                    <form id="recoveryForm" class="recovery-form" method="POST" action="forgotpassword/send-password-reset.php">
                        <div class="form-group">
                            <label for="recovery_email" class="form-label">Enter your registered email:</label>
                            <input type="email" id="recovery_email" name="email" class="form-input" required>
                        </div>
                        <button type="submit" name="recover_password" class="login-button">Send Reset Link</button>
                    </form>
                    
                    <div class="signup-prompt">
                        Don't have an account? <a href="register.php" class="signup-link">Sign up</a>
                    </div>
                    <div class="social-buttons-row">
                        <a href="https://www.instagram.com/srcuniklmiit?igsh=amQ0ejF4NTV4MzF2" target="_blank" class="circle-btn instagram-btn" title="Instagram">
                            <img src="assets/instagram.png" alt="Instagram" class="circle-icon-img">
                        </a>
                        <a href="https://linktr.ee/srcmiit2024?utm_source=linktree_profile_share&ltsid=b5e4754e-8d9d-427a-ad1f-20fd3078bbf9" target="_blank" class="circle-btn linktree-btn" title="Linktree">
                            <img src="assets/linktree.png" alt="Linktree" class="circle-icon-img">
                        </a>
                    </div>
                    <style>
                        .social-buttons-row {
                            display: flex;
                            justify-content: center;
                            gap: 24px;
                            margin-top: 24px;
                        }
                        .circle-btn {
                            display: flex;
                            align-items: center;
                            justify-content: center; 
                            width: 60px;
                            height: 60px;
                            border-radius: 50%;
                            background: var(--orange); /* Same as Forgot Password? */
                            box-shadow: 0 2px 8px rgba(24,54,81,0.10);
                            transition: background 0.2s, box-shadow 0.2s, transform 0.2s;
                            border: none;
                            outline: none;
                            text-decoration: none;
                            padding: 0;
                        }
                        .circle-btn:hover {
                            background: #ffa726;
                            box-shadow: 0 4px 16px rgba(41,182,246,0.13);
                            transform: translateY(-2px) scale(1.07);
                        }
                        .circle-icon-img {
                            width: 72px;
                            height: 72px;
                            object-fit: contain;
                            display: block;
                            margin: 0;
                            padding: 0;
                        }
                    </style>
                </div>
            </div>
        </div>
    </div>
</body>
</html>