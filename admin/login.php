<?php
session_start();
include __DIR__ . '/../config/config.php'; // Adjust the path if necessary

if (isset($_POST['login'])) {
    $admin_name = $_POST['admin_name'];
    $password = $_POST['password'];

    // Check if the admin_name exists in the admin table
    $query = "SELECT * FROM admin WHERE admin_name = '$admin_name'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        // Directly compare the entered password with the stored password
        if ($admin['password'] === $password) {
            // Password is correct, store session data
            $_SESSION['admin_name'] = $admin_name;
            $_SESSION['is_admin'] = true;
            
            // Redirect to admin dashboard after successful login
            header("Location: dashboard.php"); // Make sure to create this page
            exit();
        } else {
            // Incorrect password
            $error = "Invalid admin credentials!";
        }
    } else {
        // Admin name doesn't exist
        $error = "Invalid admin credentials!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
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
    </style>
</head>
<body>
    <div class="container">
        <div class="left-panel">
            <img src="../assets/adminlog.png" alt="Admin Login Illustration" class="illustration">
        </div>
        <div class="right-panel">
            <div class="brand">UNIKL MIIT</div>
            <div class="login-container">
                <div class="login-title">SRC Login</div>
                <?php if (isset($error)) echo "<div class='message error'>$error</div>"; ?>
                <form method="POST" action="" autocomplete="off" class="login-form">
                    <div class="form-group">
                        <label class="form-label" for="admin_name">Admin Name</label>
                        <input class="form-input" type="text" name="admin_name" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="password">Password</label>
                        <input class="form-input" type="password" name="password" required>
                    </div>
                    <button type="submit" name="login" class="login-button">LOGIN</button>
                </form>
            </div>
            </div>
        </div>
    </div>
</body>
</html>