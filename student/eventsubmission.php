<?php
include __DIR__ . '/../config/config.php';
session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: ../index.php");
    exit;
}

$student_id = $_SESSION['student_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Event Submission</title>
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400&family=Inter:wght@400&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f7fafd;
            color: #18344a;
        }
        .sidebar {
            position: fixed;
            left: 0;
            top: 60px;
            width: 220px;
            height: calc(100vh - 60px);
            background: #18344a;
            color: #fff;
            padding-top: 0;
            z-index: 100;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            transition: transform 0.3s;
            transform: translateX(-100%);
        }
        .sidebar.active {
            transform: translateX(0);
        }
        .topbar {
            position: fixed;
            left: 0;
            top: 0;
            right: 0;
            height: 60px;
            background: #ffd600;
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 101;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }
        #toggleSidebarBtn {
            position: fixed;
            top: 0;
            left: 0;
            z-index: 102;
            background: none;
            border: none;
            border-radius: 0;
            padding: 0 16px;
            height: 60px;
            cursor: pointer;
            box-shadow: none;
            display: flex;
            align-items: center;
        }
        .topbar .title {
            font-family: 'Hanken Grotesk', Arial, sans-serif;
            font-size: 22px;
            font-weight: 400;
            margin-left: 60px;
            color: #18344a;
            letter-spacing: 1px;
        }
        .topbar .welcome {
            font-family: 'Inter', Arial, sans-serif;
            margin-right: 30px;
            color: #18344a;
            font-size: 24px;
            font-weight: 400;
        }
        .topbar .logout {
            font-family: 'Inter', Arial, sans-serif;
            margin-right: 30px;
            background: #00e676;
            border: none;
            color: #18344a;
            font-size: 16px;
            font-weight: 400;
            padding: 8px 18px;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .main-content {
            margin-left: 0;
            margin-top: 60px;
            padding: 0;
            min-height: calc(100vh - 60px);
            background: #f7fafd;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
        }
        .event-announcement-container {
            background: #fff;
            border-radius: 18px;
            padding: 28px 22px 18px 22px;
            margin-top: 32px;
            box-shadow: 0 6px 32px 0 rgba(31,38,135,0.10), 0 1.5px 6px 0 rgba(0,0,0,0.07);
            width: 650px;
            min-height: 420px;
            max-width: 98vw;
            border: 1.5px solid #e3e8f0;
            transition: box-shadow 0.2s, border-color 0.2s;
        }
        .event-announcement-container:hover {
            box-shadow: 0 12px 40px 0 rgba(31,38,135,0.14), 0 2px 8px 0 rgba(0,0,0,0.09);
            border-color: #ffd600;
        }
        .event-announcement-title {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 18px;
            text-align: center;
            color: #18344a;
            letter-spacing: 1.5px;
            text-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }
        .event-list {
            max-height: 420px;
            overflow-y: auto;
            padding-right: 8px;
            scrollbar-width: thin;
            scrollbar-color: #ffd600 #e0e7ef;
        }
        .event-card {
            background: #f9fafc;
            border-radius: 10px;
            margin-bottom: 10px;
            padding: 10px 12px 8px 12px;
            box-shadow: 0 1px 6px rgba(31,38,135,0.06);
            color: #18344a;
            border: 1px solid #e3e8f0;
            border-left: 7px solid #ffd600;
            transition: transform 0.13s, box-shadow 0.13s, border-color 0.13s;
            position: relative;
            overflow: hidden;
        }
        .event-card:nth-child(4n+1) { border-left-color: #ffd600; background: #fffbe7; }
        .event-card:nth-child(4n+2) { border-left-color: #00e676; background: #eafff3; }
        .event-card:nth-child(4n+3) { border-left-color: #1086ff; background: #e7f3ff; }
        .event-card:nth-child(4n)   { border-left-color: #ff6f91; background: #fff0f5; }
        .event-card:hover {
            transform: translateY(-1px) scale(1.01);
            box-shadow: 0 3px 12px rgba(31,38,135,0.10);
            border-color: #ffd600;
        }
        .event-card .event-date {
            font-weight: bold;
            color: #ff9100;
            margin-bottom: 2px;
            font-size: 13px;
            letter-spacing: 0.5px;
            text-shadow: none;
        }
        .event-card .event-name {
            font-size: 15px;
            font-weight: 700;
            margin-bottom: 3px;
            color: #1086ff;
            text-shadow: none;
        }
        .event-card .event-desc {
            font-size: 12px;
            margin-bottom: 6px;
            color: #00bfae;
        }
        .join-btn {
            margin-top: 6px;
            background: #1086ff;
            color: #fff !important;
            padding: 7px 18px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none !important;
            text-align: center;
            box-shadow: 0 1px 4px rgba(16,134,255,0.08);
            cursor: pointer;
            transition: background 0.18s, transform 0.18s, box-shadow 0.18s;
            outline: none;
            display: inline-block;
        }
        .join-btn:hover, .join-btn:focus {
            background: #006be6;
            color: #fff !important;
            transform: translateY(-1px) scale(1.03);
            box-shadow: 0 2px 8px rgba(16,134,255,0.12);
        }
        .sidebar .nav a {
            display: flex;
            align-items: center;
            color: #fff;
            text-decoration: none;
            padding: 14px 30px;
            font-size: 16px;
            font-weight: 500;
            transition: background 0.2s, color 0.2s;
            border-left: 4px solid transparent;
            gap: 10px;
        }
        .sidebar .nav a.active, .sidebar .nav a:hover {
            background: #ffeb3b;
            color: #18344a;
            border-left: 4px solid #ff9100;
            text-decoration: none;
        }
        .sidebar .nav a:visited,
        .sidebar .nav a:active,
        .sidebar .nav a:focus {
            color: #fff;
            text-decoration: none;
        }
        .sidebar {
            transform: translateX(-100%);
            transition: transform 0.3s cubic-bezier(.4,2,.6,1);
            box-shadow: 2px 0 16px rgba(24,52,74,0.08);
        }
        .sidebar.active {
            transform: translateX(0);
        }
        #toggleSidebarBtn {
            display: block;
        }
        @media (min-width: 900px) {
            .sidebar {
            }
        }
    </style>
</head>
<body>
    <!-- Toggle Navigation Button at the very edge -->
    <button id="toggleSidebarBtn" style="position:fixed;top:0;left:0;z-index:1001;background:none;border:none;border-radius:0;padding:0 16px;height:60px;cursor:pointer;box-shadow:none;display:flex;align-items:center;">
        <i class="fa fa-bars" style="font-size:22px;color:#18344a;"></i>
    </button>
    <div class="topbar">
        <span class="title">Event Portal</span>
        <span class="welcome">Welcome Back! <?php echo htmlspecialchars($_SESSION['student_name'] ?? ''); ?></span>
        <form action="../logout.php" method="post" style="margin:0;display:inline;">
            <button type="submit" class="logout" title="Sign Out">
                <i class="fa-solid fa-right-from-bracket"></i> SIGN OUT
            </button>
        </form>
    </div>
    <div class="sidebar" id="sidebar">
        <div class="nav">
            <a href="form.php" class="nav-link">
                <i class="fa-solid fa-file-circle-plus"></i> Submit Proposal
            </a>
            <a href="proposal_status.php" id="view-status-link" class="nav-link">
                <i class="fa-solid fa-list-check"></i> View Status
            </a>
            <a href="../assets/event_guidelines.zip" download class="nav-link">
                <i class="fa-solid fa-file-lines"></i> Event Guidelines
            </a>
            <a href="https://linktr.ee/srcmiit2024?utm_source=linktree_profile_share&ltsid=b5e4754e-8d9d-427a-ad1f-20fd3078bbf9" class="nav-link" target="_blank" rel="noopener">
                <i class="fa-solid fa-headset"></i> Hotline
            </a>
        </div>
    </div>
    <div class="main-content">
        <div class="event-announcement-container">
            <div class="event-announcement-title">Event Announcements</div>
            <!-- Filter Form Start -->
            <div style="display:flex;gap:12px;justify-content:center;margin-bottom:12px;">
                <input type="text" id="filterEventName" placeholder="Filter by Event Name" style="padding:6px 10px;border-radius:4px;border:1px solid #b0bec5;font-size:14px;">
                <input type="date" id="filterEventDate" style="padding:6px 10px;border-radius:4px;border:1px solid #b0bec5;font-size:14px;">
                <button type="button" onclick="clearFilters()" style="padding:6px 14px;border-radius:4px;border:none;background:#ffd600;color:#18344a;font-weight:bold;cursor:pointer;">Clear</button>
            </div>
            <!-- Filter Form End -->
            <div class="event-list" id="eventList">
                <?php
                $sql = "SELECT event_title, event_description, event_date, registration_link FROM proposals WHERE proposal_status = 'Approved' ORDER BY event_date DESC";
                $result = $conn->query($sql);
                if ($result && $result->num_rows > 0):
                    while ($row = $result->fetch_assoc()):
                ?>
                <div class="event-card">
                    <div class="event-date">DATE <?php echo htmlspecialchars($row['event_date']); ?></div>
                    <div class="event-name"><?php echo htmlspecialchars($row['event_title']); ?></div>
                    <div class="event-desc">[<?php echo htmlspecialchars($row['event_description']); ?>]</div>
                    <?php if (!empty($row['registration_link'])): ?>
                        <a href="<?php echo htmlspecialchars($row['registration_link']); ?>" target="_blank" class="join-btn">JOIN</a>
                    <?php endif; ?>
                </div>
                <?php
                    endwhile;
                else:
                ?>
                <div style="color:#ffd180;text-align:center;">No approved events yet.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script>
        const sidebar = document.getElementById('sidebar');
        const toggleBtn = document.getElementById('toggleSidebarBtn');
        toggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });
        document.addEventListener('click', function(e) {
            if (
                sidebar.classList.contains('active') &&
                !sidebar.contains(e.target) &&
                e.target !== toggleBtn &&
                !toggleBtn.contains(e.target)
            ) {
                sidebar.classList.remove('active');
            }
        });
        document.addEventListener('DOMContentLoaded', function() {
            const eventNameInput = document.getElementById('filterEventName');
            const eventDateInput = document.getElementById('filterEventDate');
            const eventList = document.getElementById('eventList');
            const allCards = Array.from(eventList.children);
            function filterEvents() {
                const nameVal = eventNameInput.value.trim().toLowerCase();
                const dateVal = eventDateInput.value;
                allCards.forEach(card => {
                    let show = true;
                    if (nameVal) {
                        const eventName = card.querySelector('.event-name');
                        if (!eventName || !eventName.textContent.toLowerCase().includes(nameVal)) show = false;
                    }
                    if (dateVal) {
                        const eventDate = card.querySelector('.event-date');
                        if (!eventDate || !eventDate.textContent.includes(dateVal)) show = false;
                    }
                    card.style.display = show ? '' : 'none';
                });
            }
            eventNameInput.addEventListener('input', filterEvents);
            eventDateInput.addEventListener('change', filterEvents);
            window.clearFilters = function() {
                eventNameInput.value = '';
                eventDateInput.value = '';
                filterEvents();
            };
        });
    </script>
</body>
</html>