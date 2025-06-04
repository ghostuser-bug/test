<?php 
session_start();
require_once "../config/config.php";

ini_set('display_errors', 1);
error_reporting(E_ALL);

$show_security_form = false;
$selected_proposal_id = null;
$selected_action = null;
$remarks_value = '';
$security_error = '';
$pending_result = null;

// Handle security check after approve/reject
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['security_check'])
    && isset($_POST['proposal_id'])
    && isset($_POST['action'])
    && in_array($_POST['action'], ['approve', 'reject'])
) {
    $proposal_id = intval($_POST['proposal_id']);
    $student_id = trim($_POST['student_id']);
    $position = trim($_POST['position']);
    $remarks = isset($_POST['remarks']) ? $_POST['remarks'] : '';
    $status = $_POST['action'] === 'approve' ? 'Approved' : 'Rejected';
    $reviewed_filename = isset($_POST['reviewed_filename']) ? $_POST['reviewed_filename'] : '';
    $approved_budget = null;
    if ($status === 'Approved') {
        if (!isset($_POST['approved_budget']) || $_POST['approved_budget'] === '') {
            $security_error = "Budget Approve (RM) is required!";
            $show_security_form = true;
            $selected_proposal_id = $proposal_id;
            $selected_action = $_POST['action'];
            $remarks_value = $remarks;
        } else {
            $approved_budget = floatval($_POST['approved_budget']);
        }
    }
    // Security check
    if ($student_id === '' || $position === '' || ($status === 'Approved' && $approved_budget === null)) {
        $security_error = "Student ID, Position, and Budget Approve (RM) are required!";
        $show_security_form = true;
        $selected_proposal_id = $proposal_id;
        $selected_action = $_POST['action'];
        $remarks_value = $remarks;
    } else {
        // Update proposal status, remarks, reviewed file, and approved_budget if present
        if ($reviewed_filename) {
            if ($status === 'Approved') {
                $stmt = $conn->prepare("UPDATE proposals SET proposal_status = ?, admin_feedback = ?, reviewed_proposal = ?, reviewed_by_position = ?, approved_budget = ? WHERE id = ?");
                $stmt->bind_param("ssssdi", $status, $remarks, $reviewed_filename, $position, $approved_budget, $proposal_id);
            } else {
                $stmt = $conn->prepare("UPDATE proposals SET proposal_status = ?, admin_feedback = ?, reviewed_proposal = ?, reviewed_by_position = ? WHERE id = ?");
                $stmt->bind_param("ssssi", $status, $remarks, $reviewed_filename, $position, $proposal_id);
            }
        } else {
            if ($status === 'Approved') {
                $stmt = $conn->prepare("UPDATE proposals SET proposal_status = ?, admin_feedback = ?, reviewed_by_position = ?, approved_budget = ? WHERE id = ?");
                $stmt->bind_param("sssdi", $status, $remarks, $position, $approved_budget, $proposal_id);
            } else {
                $stmt = $conn->prepare("UPDATE proposals SET proposal_status = ?, admin_feedback = ?, reviewed_by_position = ? WHERE id = ?");
                $stmt->bind_param("sssi", $status, $remarks, $position, $proposal_id);
            }
        }
        $stmt->execute();
        $stmt->close();
        header("Location: manage_proposals.php");
        exit;
    }
}

// Handle approve/reject with upload (single form)
if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && isset($_POST['proposal_id'])
    && isset($_POST['action'])
    && in_array($_POST['action'], ['approve', 'reject'])
    && isset($_POST['remarks'])
) {
    $proposal_id = intval($_POST['proposal_id']);
    $remarks = $_POST['remarks'];
    $action = $_POST['action'];
    $reviewed_filename = '';
    $upload_error = '';

    // Handle file upload if present
    if (isset($_FILES['reviewed_proposal']) && $_FILES['reviewed_proposal']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['reviewed_proposal'];
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $upload_error = "File upload error: " . $file['error'];
        } elseif ($file_ext !== 'pdf') {
            $upload_error = "Only PDF files allowed!";
        } else {
            $target_dir = "../reviewed_proposals/";
            if (!is_dir($target_dir)) {
                if (!mkdir($target_dir, 0777, true)) {
                    $upload_error = "Failed to create directory: $target_dir";
                }
            }
            $filename = uniqid('reviewed_') . '_' . basename($file['name']);
            $target_file = $target_dir . $filename;
            if (move_uploaded_file($file['tmp_name'], $target_file)) {
                $reviewed_filename = $filename;
            } else {
                $upload_error = "File move failed!";
            }
        }
    }

    if ($upload_error) {
        $security_error = $upload_error;
        $show_security_form = true;
        $selected_proposal_id = $proposal_id;
        $selected_action = $action;
        $remarks_value = $remarks;
    } else {
        // Show security form before finalizing
        $show_security_form = true;
        $selected_proposal_id = $proposal_id;
        $selected_action = $action;
        $remarks_value = $remarks;
        $_POST['reviewed_filename'] = $reviewed_filename;
    }
}

// Fetch only pending proposals
$sql = "SELECT * FROM proposals WHERE proposal_status = 'Pending'";
$pending_result = $conn->query($sql);

if (!$pending_result) {
    die("Database query failed: " . $conn->error);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Proposals</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:700,400&display=swap" rel="stylesheet">
    <style>
        :root {
            --sky-blue: #85BFB4;
            --pale-blue: #CCD1DB;
            --yellow: #EDB240;
            --beige: #F5F2F0;
            --deep-green: #0F2A1D;
            --mid-green: #6B9071;
            --light-green: #AEC3B0;
            --mint: #E3EED4;
            --dark-blue: #334663;
            --accent: #8FB1BE;
            --white: #fff;
            --glass-bg: rgba(255,255,255,0.7);
            --glass-border: rgba(200,200,200,0.25);
            --shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.12);

            --premium-green: linear-gradient(90deg, #1e7f5c 0%, #43e97b 100%);
            --premium-green-solid: #1e7f5c;
            --premium-red: linear-gradient(90deg, #a83246 0%, #ff5858 100%);
            --premium-red-solid: #a83246;
            --premium-gold: linear-gradient(90deg, #bfa14a 0%, #edb240 100%);
            --button-glass: rgba(255,255,255,0.18);
        }
        body {
            margin: 0;
            padding: 0;
            font-family: 'Montserrat', Arial, sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, var(--pale-blue) 0%, var(--mint) 100%);
        }
        .dashboard-header {
            background: linear-gradient(90deg, var(--sky-blue) 60%, var(--pale-blue) 100%);
            color: var(--deep-green);
            padding: 38px 0 22px 0;
            text-align: center;
            border-radius: 0 0 36px 36px;
            box-shadow: var(--shadow);
            margin-bottom: 38px;
            position: relative;
        }
        .dashboard-header h1 {
            margin: 0;
            font-size: 2.3rem;
            font-weight: 700;
            letter-spacing: 2px;
            color: var(--deep-green);
            text-shadow: 0 2px 8px rgba(133,191,180,0.08);
        }
        .dashboard-header span {
            font-size: 1.1rem;
            font-weight: 400;
            color: var(--dark-blue);
            opacity: 0.8;
        }
        .section {
            background: var(--glass-bg);
            border-radius: 24px;
            box-shadow: var(--shadow);
            margin: 0 auto 38px auto;
            padding: 38px 36px 32px 36px;
            max-width: 98vw;
            border: 1.5px solid var(--glass-border);
            backdrop-filter: blur(8px);
        }
        .section h2 {
            margin-top: 0;
            font-size: 1.45rem;
            color: var(--deep-green);
            font-weight: 700;
            margin-bottom: 18px;
            letter-spacing: 1px;
        }
        .table-wrapper {
            width: 100%;
            overflow-x: auto;
        }
        table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
            min-width: 1600px;
            background: var(--white);
            border-radius: 18px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }
        th, td {
            padding: 16px 18px;
            text-align: left;
            font-size: 1.05rem;
        }
        th {
            background: var(--mint);
            color: var(--deep-green);
            font-weight: 700;
            border-bottom: 2px solid var(--glass-border);
        }
        tr:nth-child(even) td {
            background: var(--beige);
        }
        tr:hover td {
            background: var(--mint);
            transition: background 0.2s;
        }
        .badge {
            display: inline-block;
            padding: 6px 18px;
            border-radius: 18px;
            font-size: 1rem;
            font-weight: 600;
            letter-spacing: 1px;
            background: var(--glass-bg);
            box-shadow: 0 2px 8px 0 rgba(67,233,123,0.07);
            border: 1.5px solid var(--glass-border);
            color: var(--dark-blue);
        }
        .badge.approved {
            background: var(--premium-green);
            color: #fff;
            border: none;
            box-shadow: 0 2px 12px 0 rgba(67,233,123,0.18);
        }
        .badge.rejected {
            background: var(--premium-red);
            color: #fff;
            border: none;
            box-shadow: 0 2px 12px 0 rgba(255,88,88,0.18);
        }
        .badge.pending {
            background: var(--premium-gold);
            color: #fff;
            border: none;
            box-shadow: 0 2px 12px 0 rgba(237,178,64,0.18);
        }
        .button {
            display: inline-block;
            padding: 12px 32px;
            font-size: 1rem;
            font-weight: 700;
            border-radius: 24px;
            border: none;
            cursor: pointer;
            background: var(--button-glass);
            color: var(--deep-green);
            box-shadow: 0 2px 12px 0 rgba(133,191,180,0.10);
            transition: background 0.18s, color 0.18s, box-shadow 0.18s, transform 0.12s;
            margin: 0 6px;
            outline: none;
            position: relative;
            overflow: hidden;
        }
        .button:active {
            transform: scale(0.97);
        }
        .button.green, .button.approve {
            background: var(--premium-green);
            color: #fff;
            box-shadow: 0 4px 18px 0 rgba(67,233,123,0.18);
            border: none;
        }
        .button.green:hover, .button.approve:hover {
            background: linear-gradient(90deg, #43e97b 0%, #1e7f5c 100%);
            color: #fff;
            box-shadow: 0 6px 24px 0 rgba(67,233,123,0.28);
        }
        .button.red, .button.reject {
            background: var(--premium-red);
            color: #fff;
            box-shadow: 0 4px 18px 0 rgba(255,88,88,0.18);
            border: none;
        }
        .button.red:hover, .button.reject:hover {
            background: linear-gradient(90deg, #ff5858 0%, #a83246 100%);
            color: #fff;
            box-shadow: 0 6px 24px 0 rgba(255,88,88,0.28);
        }
        .button.gray {
            background: linear-gradient(90deg, #e0e0e0 0%, #bdbdbd 100%);
            color: #333;
        }
        .action-form textarea {
            width: 100%;
            min-height: 90px;
            border-radius: 12px;
            border: 2px solid var(--glass-border);
            padding: 14px 16px;
            font-size: 1.08rem;
            background: var(--white);
            margin-bottom: 12px;
            font-family: 'Montserrat', Arial, sans-serif;
            resize: vertical;
            box-sizing: border-box;
            box-shadow: 0 2px 8px 0 rgba(133,191,180,0.07);
            transition: border 0.2s;
        }
        .action-form textarea:focus {
            border: 2px solid var(--sky-blue);
            outline: none;
        }
        .action-form label {
            font-weight: 600;
            color: var(--deep-green);
            margin-right: 6px;
        }
        .action-form input[type="file"] {
            margin: 8px 0;
        }
        .security-form {
            background: var(--mint);
            border: 1.5px solid var(--premium-gold);
            padding: 18px;
            margin: 12px 0;
            border-radius: 16px;
            box-shadow: 0 2px 12px 0 rgba(237,178,64,0.08);
        }
        .security-form label {
            font-weight: 600;
            color: var(--deep-green);
        }
        .security-form input[type="text"] {
            border-radius: 8px;
            border: 1.5px solid var(--glass-border);
            padding: 8px 12px;
            margin-bottom: 8px;
            font-size: 1rem;
            background: var(--glass-bg);
        }
        .disabled { opacity: 0.5; pointer-events: none; }
        @media (max-width: 1400px) {
            table { min-width: 1200px; }
        }
        @media (max-width: 900px) {
            .section { padding: 18px 4px; }
            th, td { font-size: 0.95rem; padding: 10px 6px; }
            table { min-width: 900px; }
        }
        @media (max-width: 600px) {
            .dashboard-header { padding: 18px 0 10px 0; border-radius: 0 0 18px 18px; }
            .dashboard-header h1 { font-size: 1.1rem; }
            .section { padding: 8px 1vw 8px 1vw; }
            th, td { font-size: 0.85rem; padding: 6px 1px; }
            table { min-width: 600px; }
        }
    </style>
</head>
<body>
    <a href="dashboard.php" class="back-btn-animated">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        <span>Back</span>
    </a>
    <div class="dashboard-header">
        <h1>Manage Proposals</h1>
        <span>Admin Panel &mdash; Pending Only</span>
    </div>
    <div class="section">
        <h2>Pending Proposals</h2>
        <div class="table-wrapper">
        <table>
            <tr>
                <th>Proposal ID</th>
                <th>Club Name</th>
                <th>Event Title</th>
                <th>Event Date</th>
                <th>Budget (RM)</th>
                <th>Participants</th>
                <th>Proposal File</th>
                <th>Reviewed Proposal</th>
                <th>Approve/Reject & Upload</th>
            </tr>
            <?php while ($row = $pending_result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['id']); ?></td>
                <td><?php echo htmlspecialchars($row['club_name']); ?></td>
                <td><?php echo htmlspecialchars($row['event_title']); ?></td>
                <td><?php echo htmlspecialchars($row['event_date']); ?></td>
                <td><?php echo htmlspecialchars($row['budget']); ?></td>
                <td><?php echo htmlspecialchars($row['estimated_participants']); ?></td>
                <td>
                    <?php if (!empty($row['proposal_file'])): ?>
                        <a href="../uploads/<?php echo htmlspecialchars($row['proposal_file']); ?>" target="_blank" class="button gray" style="padding:6px 18px;font-size:0.95rem;">View</a>
                    <?php else: ?>
                        <span style="color:#aaa;">N/A</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if (!empty($row['reviewed_proposal'])): ?>
                        <a href="../reviewed_proposals/<?php echo htmlspecialchars($row['reviewed_proposal']); ?>" target="_blank" class="button gray" style="padding:6px 18px;font-size:0.95rem;">View</a>
                    <?php else: ?>
                        <span style="color:#aaa;">N/A</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($show_security_form && $selected_proposal_id == $row['id']): ?>
                        <div class="security-form">
                            <?php if ($security_error) echo "<div style='color:red;'>$security_error</div>"; ?>
                            <form action="manage_proposals.php" method="post">
                                <input type="hidden" name="security_check" value="1">
                                <input type="hidden" name="proposal_id" value="<?php echo $row['id']; ?>">
                                <input type="hidden" name="action" value="<?php echo htmlspecialchars($selected_action); ?>">
                                <input type="hidden" name="remarks" value="<?php echo htmlspecialchars($remarks_value); ?>">
                                <input type="hidden" name="reviewed_filename" value="<?php echo isset($_POST['reviewed_filename']) ? htmlspecialchars($_POST['reviewed_filename']) : ''; ?>">
                                <?php if ($selected_action === 'approve'): ?>
                                    <label>Budget Approve (RM):</label>
                                    <input type="number" name="approved_budget" min="0" step="0.01" required><br>
                                <?php endif; ?>
                                <label>Student ID:</label>
                                <input type="text" name="student_id" required>
                                <br>
                                <label>Position:</label>
                                <input type="text" name="position" required>
                                <br>
                                <button type="submit" class="button green" style="margin-top:8px;">Confirm</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <form class="action-form" action="manage_proposals.php" method="post" enctype="multipart/form-data" onsubmit="return confirm('Are you sure you want to proceed?');">
                            <input type="hidden" name="proposal_id" value="<?php echo $row['id']; ?>">
                            <input type="hidden" name="remarks" value="" class="remarks-hidden">
                            <textarea name="remarks_textarea" placeholder="Enter remarks..." required></textarea>
                            <br>
                            <label>Upload Reviewed Proposal (PDF):</label>
                            <input type="file" name="reviewed_proposal" accept="application/pdf" required>
                            <br>
                            <button type="submit" name="action" value="approve" class="button green approve">Approve</button>
                            <button type="submit" name="action" value="reject" class="button red reject">Reject</button>
                        </form>
                        <script>
                            // Sync remarks textarea to hidden input before submit
                            document.querySelectorAll('.action-form').forEach(function(form) {
                                form.addEventListener('submit', function(e) {
                                    var remarks = form.querySelector('textarea[name="remarks_textarea"]');
                                    var hidden = form.querySelector('input.remarks-hidden');
                                    if (remarks && hidden) {
                                        hidden.value = remarks.value;
                                    }
                                });
                            });
                        </script>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
        </div>
    </div>
</body>
</html>
