<?php
session_start();
include __DIR__ . '/../config/config.php';

// Redirect if admin is not logged in
if (!isset($_SESSION['admin_name'])) {
    header("Location: login.php");
    exit();
}

// Reviewed proposals filter handling
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$club_filter = isset($_GET['club']) ? $_GET['club'] : '';
$date_filter = isset($_GET['event_date']) ? $_GET['event_date'] : '';

$where_clauses = ["proposal_status IN ('Approved', 'Rejected')"];
if (!empty($status_filter)) {
    $where_clauses[] = "proposal_status = '" . $conn->real_escape_string($status_filter) . "'";
}
if (!empty($club_filter)) {
    $where_clauses[] = "club_name = '" . $conn->real_escape_string($club_filter) . "'";
}
if (!empty($date_filter)) {
    $where_clauses[] = "event_date = '" . $conn->real_escape_string($date_filter) . "'";
}
$where_sql = implode(' AND ', $where_clauses);

$reviewed_query = "SELECT * FROM proposals WHERE $where_sql ORDER BY created_at DESC";
$reviewed_result = $conn->query($reviewed_query);

$clubs_result = $conn->query("SELECT DISTINCT club_name FROM proposals");

// Pagination logic for reviewed proposals
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 10;
$reviewed_rows = [];
if ($reviewed_result && $reviewed_result->num_rows > 0) {
    $total_reviewed = $reviewed_result->num_rows;
    $total_pages = ceil($total_reviewed / $per_page);
    $reviewed_result->data_seek(0);
    $i = 0;
    $start = ($page - 1) * $per_page;
    $end = $start + $per_page;
    while ($row = $reviewed_result->fetch_assoc()) {
        if ($i >= $start && $i < $end) {
            $reviewed_rows[] = $row;
        }
        $i++;
    }
} else {
    $total_pages = 1;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reviewed Proposals</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:700,400&display=swap" rel="stylesheet">
    <style>
        :root {
            --main-bg: #f7f8fa;
            --sidebar-bg: #fff;
            --sidebar-active: #6c63ff;
            --sidebar-hover: #ecebff;
            --sidebar-text: #222;
            --header-bg: #fff;
            --header-text: #222;
            --card-bg: #fff;
            --card-shadow: 0 4px 24px 0 rgba(108,99,255,0.08);
            --border-radius: 18px;
            --accent: #6c63ff;
            --accent-light: #ecebff;
            --success: #7ed957;
            --danger: #e53935;
            --pending: #fbbd4c;
            --gray: #bdbdbd;
        }
        body {
            margin: 0;
            font-family: 'Montserrat', Arial, sans-serif;
            background: var(--main-bg);
            min-height: 100vh;
        }
        .main-content {
            max-width: 1100px;
            margin: 40px auto;
            padding: 36px 36px 36px 36px;
            background: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
        }
        .section h2 {
            margin-top: 0;
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--accent);
            margin-bottom: 18px;
        }
        .filter-form {
            margin-bottom: 18px;
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
        }
        .filter-form label {
            font-weight: 600;
            color: #555;
            margin-right: 4px;
        }
        .filter-form select, .filter-form input[type="date"] {
            padding: 7px 12px;
            border-radius: 8px;
            border: 1px solid #ecebff;
            background: #fafbfc;
            font-size: 1rem;
        }
        .button, input[type="submit"] {
            display: inline-block;
            padding: 10px 24px;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 700;
            text-decoration: none;
            border: none;
            outline: none;
            cursor: pointer;
            margin: 0 2px;
            background: var(--accent-light);
            color: var(--accent);
            transition: background 0.2s, color 0.2s, box-shadow 0.2s, transform 0.12s;
        }
        .table-wrapper {
            overflow-x: auto;
        }
        table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: none;
            margin-bottom: 20px;
        }
        th, td {
            padding: 14px 10px;
            text-align: center;
            font-size: 1rem;
        }
        th {
            background: var(--accent-light);
            color: var(--accent);
            font-weight: 700;
            border-bottom: 2px solid #f0f0f0;
        }
        tr:nth-child(even) td {
            background: #fafbfc;
        }
        tr:nth-child(odd) td {
            background: #fff;
        }
        .badge {
            display: inline-block;
            padding: 6px 18px;
            border-radius: 12px;
            font-size: 0.98rem;
            font-weight: 600;
            background: #ecebff;
            color: var(--accent);
        }
        .badge.approved { background: var(--success); color: #fff; }
        .badge.rejected { background: var(--danger); color: #fff; }
        .badge.pending { background: var(--pending); color: #fff; }
        @media (max-width: 900px) {
            .main-content { padding: 18px 4vw; }
        }
        @media (max-width: 600px) {
            .main-content { padding: 8px 2vw; }
            .section { padding: 12px 2vw 10px 2vw; }
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="section">
            <h2>Reviewed Proposals (Approved & Rejected)</h2>
            <form method="GET" class="filter-form">
                <label>Status:</label>
                <select name="status">
                    <option value="">All</option>
                    <option value="Approved" <?php if($status_filter=='Approved') echo 'selected'; ?>>Approved</option>
                    <option value="Rejected" <?php if($status_filter=='Rejected') echo 'selected'; ?>>Rejected</option>
                </select>
                <label>Club:</label>
                <select name="club">
                    <option value="">All</option>
                    <?php
                    // Re-query clubs for filter dropdown (since $clubs_result was exhausted)
                    $clubs_result2 = $conn->query("SELECT DISTINCT club_name FROM proposals");
                    while($club = $clubs_result2->fetch_assoc()) {
                        $selected = $club_filter == $club['club_name'] ? 'selected' : '';
                        echo "<option value='" . htmlspecialchars($club['club_name']) . "' $selected>" . htmlspecialchars($club['club_name']) . "</option>";
                    }
                    ?>
                </select>
                <label>Date:</label>
                <input type="date" name="event_date" value="<?php echo htmlspecialchars($date_filter); ?>">
                <input type="submit" value="Filter" class="button">
            </form>
            <div class="table-wrapper">
                <table>
                    <tr>
                        <th>ID</th>
                        <th>Club Name</th>
                        <th>Event Title</th>
                        <th>Status</th>
                        <th>Date Submit</th>
                        <th>Reviewed By (Position)</th>
                        <th>Budget Approved (RM)</th>
                    </tr>
                    <?php 
                    if (count($reviewed_rows) > 0) {
                        foreach ($reviewed_rows as $row) {
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['club_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['event_title']); ?></td>
                            <td>
                                <?php if ($row['proposal_status'] == 'Approved'): ?>
                                    <span class="badge approved">Approved</span>
                                <?php elseif ($row['proposal_status'] == 'Rejected'): ?>
                                    <span class="badge rejected">Rejected</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                            <td><?php echo htmlspecialchars($row['reviewed_by_position']); ?></td>
                            <td>
                                <?php if ($row['proposal_status'] == 'Approved') {
                                    echo isset($row['approved_budget']) ? htmlspecialchars($row['approved_budget']) : '-';
                                } else {
                                    echo '-';
                                } ?>
                            </td>
                        </tr>
                    <?php 
                        }
                    } else {
                    ?>
                        <tr><td colspan="7">No reviewed proposals</td></tr>
                    <?php 
                    }
                    ?>
                </table>
            </div>
            <div style="display:flex;justify-content:center;align-items:center;gap:8px;margin:18px 0;">
                <span>Page <?php echo $page; ?> of <?php echo $total_pages; ?></span>
                <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                    <a href="?<?php
                        $params = $_GET;
                        $params['page'] = $p;
                        echo http_build_query($params);
                    ?>"
                    style="display:inline-block;padding:8px 14px;margin:0 2px;border-radius:6px;
                        background:<?php echo $p == $page ? '#6c63ff' : '#f7f8fa'; ?>;
                        color:<?php echo $p == $page ? '#fff' : '#222'; ?>;
                        font-weight:700;text-decoration:none;">
                        <?php echo $p; ?>
                    </a>
                <?php endfor; ?>
            </div>
        </div>
    </div>
</body>
</html>