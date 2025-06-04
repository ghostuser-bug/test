<?php
session_start();
include __DIR__ . '/../config/config.php';

// Redirect if admin is not logged in
if (!isset($_SESSION['admin_name'])) {
    header("Location: manage_proposals.php");
    exit();
}

// Fetch proposal statistics
$total_proposals = $conn->query("SELECT COUNT(*) AS total FROM proposals")->fetch_assoc()['total'];
$total_approved = $conn->query("SELECT COUNT(*) AS total FROM proposals WHERE proposal_status = 'Approved'")->fetch_assoc()['total'];
$total_rejected = $conn->query("SELECT COUNT(*) AS total FROM proposals WHERE proposal_status = 'Rejected'")->fetch_assoc()['total'];
$total_pending = $conn->query("SELECT COUNT(*) AS total FROM proposals WHERE proposal_status = 'Pending'")->fetch_assoc()['total'];

// Fetch submission counts per club
$club_reports = $conn->query("SELECT club_name, COUNT(*) AS count FROM proposals GROUP BY club_name");

$pending_query = "SELECT * FROM proposals WHERE proposal_status = 'Pending'";
$pending_result = $conn->query($pending_query);

$clubs_result = $conn->query("SELECT DISTINCT club_name FROM proposals");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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
        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 220px;
            background: var(--sidebar-bg);
            box-shadow: 2px 0 16px 0 rgba(108,99,255,0.06);
            display: flex;
            flex-direction: column;
            padding: 32px 0 0 0;
        }
        .sidebar .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--accent);
            text-align: center;
            margin-bottom: 36px;
            letter-spacing: 2px;
        }
        .sidebar nav {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .sidebar a {
            text-decoration: none;
            color: var(--sidebar-text);
            padding: 12px 32px;
            border-radius: var(--border-radius) 0 0 var(--border-radius);
            font-weight: 500;
            transition: background 0.15s, color 0.15s;
            margin-right: 12px;
        }
        .sidebar a.active, .sidebar a:hover {
            background: var(--sidebar-active);
            color: #fff;
        }
        .sidebar .logout {
            margin-top: auto;
            margin-bottom: 32px;
            color: var(--danger);
            background: none;
            border: none;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            padding: 12px 32px;
            border-radius: var(--border-radius) 0 0 var(--border-radius);
            transition: background 0.15s;
        }
        .sidebar .logout:hover {
            background: var(--danger);
            color: #fff;
        }
        .main-content {
            flex: 1;
            padding: 36px 36px 36px 36px;
        }
        .header {
            background: var(--header-bg);
            color: var(--header-text);
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            padding: 28px 36px 18px 36px;
            margin-bottom: 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .header .welcome {
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: 1px;
        }
        .header .subtitle {
            font-size: 1.05rem;
            color: #888;
            margin-left: 18px;
        }
        .stats-row {
            display: flex;
            gap: 24px;
            margin-bottom: 32px;
            flex-wrap: wrap;
        }
        .stat-card {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            flex: 1 1 180px;
            min-width: 180px;
            padding: 28px 0 18px 0;
            text-align: center;
            margin-bottom: 0;
        }
        .stat-card h3 {
            font-size: 1.08rem;
            font-weight: 600;
            color: #888;
            margin: 0 0 10px 0;
        }
        .stat-card .stat-value {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 0;
        }
        .stat-card .approved { color: var(--success);}
        .stat-card .rejected { color: var(--danger);}
        .stat-card .pending { color: var(--pending);}
        .section {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            margin-bottom: 32px;
            padding: 32px 24px 24px 24px;
        }
        .section h2 {
            margin-top: 0;
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--accent);
            margin-bottom: 18px;
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
        .button.approve { background: var(--success); color: #fff; }
        .button.reject { background: var(--danger); color: #fff; }
        .button.gray { background: var(--gray); color: #fff; }
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
        @media (max-width: 900px) {
            .dashboard-container { flex-direction: column; }
            .sidebar { width: 100vw; flex-direction: row; padding: 0; }
            .sidebar nav { flex-direction: row; }
            .main-content { padding: 18px 4vw; }
            .stats-row { flex-direction: column; gap: 12px; }
        }
        @media (max-width: 600px) {
            .main-content { padding: 8px 2vw; }
            .section { padding: 12px 2vw 10px 2vw; }
            .header { flex-direction: column; align-items: flex-start; padding: 18px 2vw 10px 2vw; }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="logo">SRC Admin</div>
            <nav>
                <a href="#" class="active">Dashboard</a>
                <a href="manage_proposals.php" class="pending-link">Pending Proposals</a>
                <a href="reviewed.php" class="reviewed-link">Reviewed Proposal</a>
            </nav>
            <form action="logout1.php" method="post" style="margin-top:auto;">
                <button type="submit" class="logout">Logout</button>
            </form>
        </aside>
        <main class="main-content">
            <div class="header">
                <div>
                    <span class="welcome">Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
                    <span class="subtitle">Student Representative Committee Dashboard</span>
                </div>
            </div>
            <div class="stats-row">
                <div class="stat-card stat-total">
                    <h3>Total Proposals</h3>
                    <div class="stat-value"><?php echo $total_proposals; ?></div>
                </div>
                <div class="stat-card stat-approved">
                    <h3>Approved</h3>
                    <div class="stat-value"><?php echo $total_approved; ?></div>
                </div>
                <div class="stat-card stat-rejected">
                    <h3>Rejected</h3>
                    <div class="stat-value"><?php echo $total_rejected; ?></div>
                </div>
                <div class="stat-card stat-pending">
                    <h3>Pending</h3>
                    <div class="stat-value"><?php echo $total_pending; ?></div>
                </div>
            </div>
            <div class="section">
                <h2>Proposals Submitted by Each Club</h2>
                <div class="table-wrapper">
                <table>
                    <tr><th>Club Name</th><th>Number of Proposals</th></tr>
                    <?php 
                    $club_names = [];
                    $club_counts = [];
                    if ($club_reports->num_rows > 0) {
                        $club_reports->data_seek(0);
                        while ($row = $club_reports->fetch_assoc()) {
                            echo "<tr><td>".htmlspecialchars($row['club_name'])."</td><td>{$row['count']}</td></tr>";
                            $club_names[] = htmlspecialchars($row['club_name']);
                            $club_counts[] = $row['count'];
                        }
                    } else {
                        echo "<tr><td colspan='2'>No data available</td></tr>";
                    }
                    ?>
                </table>
                </div>
                <!-- Add the horizontal bar chart below the table -->
                <div style="background:#fff; border-radius:12px; box-shadow:0 4px 24px 0 rgba(108,99,255,0.08); padding:24px; margin-top:24px;">
                    <canvas id="clubBarChart" style="max-width:100%;"></canvas>
                </div>
                <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                <script>
                    const clubNames = <?php echo json_encode($club_names); ?>;
                    const clubCounts = <?php echo json_encode($club_counts); ?>;
                    const palette = [
                        "#6c63ff", "#27A2DA", "#F1E532", "#D81B81", "#5E2D85"
                    ];
                    const barColors = clubNames.map((_, i) => palette[i % palette.length]);
                    new Chart(document.getElementById('clubBarChart').getContext('2d'), {
                        type: 'bar',
                        data: {
                            labels: clubNames,
                            datasets: [{
                                label: 'Number of Proposals',
                                data: clubCounts,
                                backgroundColor: barColors,
                                borderRadius: 8,
                                borderWidth: 2,
                                borderColor: '#fff',
                                hoverBackgroundColor: barColors.map(c => c + 'cc')
                            }]
                        },
                        options: {
                            indexAxis: 'y',
                            responsive: true,
                            plugins: {
                                legend: { display: false },
                                title: { display: false }
                            },
                            scales: {
                                x: {
                                    beginAtZero: true,
                                    ticks: { color: '#888', font: { weight: 'bold' } },
                                    grid: { color: '#ecebff' }
                                },
                                y: {
                                    ticks: { color: '#222', font: { weight: 'bold' } },
                                    grid: { display: false }
                                }
                            }
                        }
                    });
                </script>
            </div>
            <!-- Remove ONLY the Pending Proposals section (from <div class="section"> with <h2>Pending Proposals</h2> up to its closing </div>), leaving all other code unchanged.
            <div class="section">
                <h2>Pending Proposals</h2>
                <div class="table-wrapper">
                <table>
                    <tr>
                        <th>Proposal ID</th>
                        <th>Club Name</th>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                    <?php 
                    if ($pending_result->num_rows > 0) {
                        while ($row = $pending_result->fetch_assoc()) {
                            echo "<tr>
                                    <td>{$row['id']}</td>
                                    <td>".htmlspecialchars($row['club_name'])."</td>
                                    <td>".htmlspecialchars($row['event_title'])."</td>
                                    <td><span class='badge pending'>{$row['proposal_status']}</span></td>
                                    <td>
                                        <a href='manage_proposals.php?id={$row['id']}&action=approve' class='button approve'>Approve</a> 
                                        <a href='manage_proposals.php?id={$row['id']}&action=reject' class='button reject'>Reject</a>
                                    </td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>No pending proposals</td></tr>";
                    }
                    ?>
                </table>
                </div>
            </div>
            <!-- Finance Analysis Section START -->
            <div class="section">
                <h2>Finance Analysis</h2>
                <form id="finance-filter-form" class="filter-form" onsubmit="return false;">
                    <label for="start-date">Start Date:</label>
                    <input type="date" id="start-date" name="start-date" required>
                    <label for="end-date">End Date:</label>
                    <input type="date" id="end-date" name="end-date" required>
                    <button type="button" class="button" onclick="updateFinanceChart()">Filter</button>
                </form>
                <div style="background:#fff; border-radius:12px; box-shadow:0 4px 24px 0 rgba(108,99,255,0.08); padding:24px; margin-top:24px; text-align:center;">
                    <canvas id="financePieChart" width="400" height="400" style="max-width:100%;"></canvas>
                </div>
                <script>
                let financeChart;
                function updateFinanceChart() {
                    const startDate = document.getElementById('start-date').value;
                    const endDate = document.getElementById('end-date').value;
                    if (!startDate || !endDate) return;
                    fetch(`finance_data.php?start_date=${startDate}&end_date=${endDate}`)
                        .then(response => response.json())
                        .then(data => {
                            const labels = data.map(item => item.club_name);
                            const budgets = data.map(item => parseFloat(item.total_approved)); // <-- update this line
                            const palette = ["#6c63ff", "#27A2DA", "#F1E532", "#D81B81", "#5E2D85", "#7ed957", "#e53935", "#fbbd4c"];
                            const colors = labels.map((_, i) => palette[i % palette.length]);
                            if (financeChart) financeChart.destroy();
                            financeChart = new Chart(document.getElementById('financePieChart').getContext('2d'), {
                                type: 'pie',
                                data: {
                                    labels: labels,
                                    datasets: [{
                                        label: 'Approved Budget (RM)',
                                        data: budgets,
                                        backgroundColor: colors,
                                        borderColor: '#fff',
                                        borderWidth: 2
                                    }]
                                },
                                options: {
                                    responsive: false, // <-- set this to false
                                    plugins: {
                                        legend: { position: 'bottom' },
                                        title: { display: true, text: 'Approved Budget by Club (RM)' }
                                    }
                                }
                            });
                        });
                }
                // Set default date range to this year
                window.addEventListener('DOMContentLoaded', () => {
                    const today = new Date();
                    const yearStart = new Date(today.getFullYear(), 0, 1);
                    document.getElementById('start-date').value = yearStart.toISOString().split('T')[0];
                    document.getElementById('end-date').value = today.toISOString().split('T')[0];
                    updateFinanceChart();
                });
                </script>
            </div>
            <!-- Finance Analysis Section END -->
        </main>
    </div>
</body>
</html>
<style>
    .stat-card.stat-total {
        background: #0D6FB0; /* Sea Blue */
        color: #fff;
    }
    .stat-card.stat-approved {
        background: #7ed957; /* Success green or you can use #27A2DA (Turquoise) */
        color: #fff;
    }
    .stat-card.stat-rejected {
        background: var(--danger);
        color: #fff;
    }
    .stat-card.stat-pending {
        background: #F1E532; /* Yellow */
        color: #222;
    }
    .stat-card .stat-value {
        font-size: 2.2rem;
        font-weight: 700;
        margin-bottom: 0;
        /* Optionally add a text-shadow for better contrast */
        text-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    /* Optional: Make headings a bit lighter for contrast */
    .stat-card h3 {
        color: rgba(255,255,255,0.85);
    }
</style>
