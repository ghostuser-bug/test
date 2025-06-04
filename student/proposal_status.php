<?php
session_start();
require_once "../config/config.php";

$student_id = $_SESSION['student_id'];

$sql = "SELECT 
            id AS proposal_id,
            event_title,
            proposal_status,
            admin_feedback,
            proposal_file,
            reviewed_proposal
        FROM proposals
        WHERE student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Proposal Status</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:700,400&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Montserrat', Arial, sans-serif;
            background: linear-gradient(120deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background: #fff;
            border-radius: 32px;
            box-shadow: 0 12px 40px rgba(0,0,0,0.18);
            padding: 60px 60px 40px 60px;
            max-width: 1200px;
            width: 100%;
            margin: 60px 0;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        h2 {
            text-align: center;
            font-size: 2.6rem;
            font-weight: 700;
            color: #232323;
            margin-bottom: 40px;
            letter-spacing: 1.5px;
        }
        .table-wrapper {
            width: 100%;
            overflow-x: auto;
        }
        table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
            background: #f9fafc;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            margin-bottom: 30px;
        }
        th, td {
            padding: 22px 18px;
            text-align: center;
            font-size: 1.15rem;
        }
        th {
            background: #ff9800;
            color: #232323;
            font-weight: 700;
            border-bottom: 2px solid #f5f5f5;
        }
        tr:nth-child(even) td {
            background: #f5f7fa;
        }
        tr:nth-child(odd) td {
            background: #fff;
        }
        td {
            color: #333;
        }
        .status-approved {
            color: #43a047;
            font-weight: bold;
        }
        .status-rejected {
            color: #e53935;
            font-weight: bold;
        }
        .status-pending {
            color: #ff9800;
            font-weight: bold;
        }
        .btn {
            display: inline-block;
            padding: 12px 32px;
            border-radius: 22px;
            font-size: 1.08rem;
            font-weight: 600;
            text-decoration: none;
            border: none;
            outline: none;
            cursor: pointer;
            transition: background 0.2s, color 0.2s, box-shadow 0.2s;
            margin: 0 2px;
        }
        .btn-view {
            background: #1976d2;
            color: #fff;
            box-shadow: 0 2px 8px rgba(25, 118, 210, 0.08);
        }
        .btn-view:hover {
            background: #0d47a1;
        }
        .btn-back {
            background: #ff9800;
            color: #232323;
            margin-top: 28px;
            font-weight: 700;
            box-shadow: 0 2px 8px rgba(255, 152, 0, 0.08);
        }
        .btn-back:hover {
            background: #f57c00;
            color: #fff;
        }
        /* Back Button Navigation Styles */
        .back-btn-animated {
            position: fixed;
            top: 24px;
            left: 24px;
            z-index: 1000;
            display: flex;
            align-items: center;
            background: #1976d2;
            color: #fff;
            padding: 14px 36px 14px 28px;
            border: none;
            border-radius: 40px;
            font-size: 22px;
            font-family: inherit;
            font-weight: 500;
            box-shadow: 0 2px 12px rgba(25, 118, 210, 0.12);
            cursor: pointer;
            text-decoration: none;
            transition: background 0.2s, box-shadow 0.2s, transform 0.2s;
            opacity: 0;
            animation: slideInLeft 0.7s 0.1s forwards;
        }
        .back-btn-animated:hover {
            background: #1565c0;
            box-shadow: 0 4px 24px rgba(25, 118, 210, 0.18);
            transform: translateY(-2px) scale(1.04);
            text-decoration: none;
        }
        .back-btn-animated svg {
            margin-right: 12px;
            width: 28px;
            height: 28px;
            fill: none;
            stroke: #fff;
            stroke-width: 3;
        }
        @keyframes slideInLeft {
            from { opacity: 0; transform: translateX(-60px); }
            to { opacity: 1; transform: translateX(0); }
        }
        @media (max-width: 1300px) {
            .container {
                max-width: 99vw;
                padding: 30px 2vw 20px 2vw;
            }
            th, td {
                padding: 14px 6px;
                font-size: 1rem;
            }
        }
        @media (max-width: 900px) {
            .container {
                padding: 18px 2vw 10px 2vw;
            }
            h2 {
                font-size: 1.5rem;
            }
            th, td {
                font-size: 0.95rem;
            }
        }
        @media (max-width: 600px) {
            .container {
                padding: 8px 1vw 8px 1vw;
            }
            h2 {
                font-size: 1.1rem;
            }
            th, td {
                font-size: 0.85rem;
                padding: 8px 2px;
            }
            .back-btn-animated {
                top: 10px;
                left: 10px;
                padding: 10px 22px 10px 16px;
                font-size: 16px;
            }
            .back-btn-animated svg {
                width: 20px;
                height: 20px;
                margin-right: 8px;
            }
        }
    </style>
</head>
<body>
    <a href="eventsubmission.php" class="back-btn-animated">
        <svg viewBox="0 0 32 32"><line x1="24" y1="16" x2="8" y2="16"/><polyline points="14 10 8 16 14 22"/></svg>
        Back
    </a>
    <div class="container">
        <h2>Your Proposal Status</h2>
        <div class="table-wrapper">
        <table>
            <tr>
                <th>Event Title</th>
                <th>Status</th>
                <th>Remarks/Comments</th>
                <th>Original Proposal</th>
                <th>Reviewed Proposal (by SRC)</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['event_title']); ?></td>
                <td>
                    <?php
                        $status = strtolower($row['proposal_status']);
                        if ($status == 'approved') {
                            echo '<span class="status-approved">Approved</span>';
                        } elseif ($status == 'rejected') {
                            echo '<span class="status-rejected">Rejected</span>';
                        } else {
                            echo '<span class="status-pending">'.htmlspecialchars($row['proposal_status']).'</span>';
                        }
                    ?>
                </td>
                <td><?php echo htmlspecialchars($row['admin_feedback']); ?></td>
                <td>
                    <?php if (!empty($row['proposal_file'])): ?>
                        <a href="../uploads/<?php echo htmlspecialchars($row['proposal_file']); ?>" target="_blank" class="btn btn-view">View</a>
                    <?php else: ?>
                        <span style="color:#aaa;">Not available</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if (!empty($row['reviewed_proposal'])): ?>
                        <a href="../reviewed_proposals/<?php echo htmlspecialchars($row['reviewed_proposal']); ?>" target="_blank" class="btn btn-view">View</a>
                    <?php else: ?>
                        <span style="color:#aaa;">Not available</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
        </div>
        <div style="text-align:center;">
            <a href="eventsubmission.php" class="btn btn-back">Back to Submission</a>
        </div>
    </div>
</body>
</html>