<?php
include __DIR__ . '/../config/config.php';
session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: ../index.php");
    exit;
}

$student_id = $_SESSION['student_id'];
$error_message = '';
$success_message = '';
$show_form = true;

// Club list
$clubs = [
    "Student Development Club (SD)" => [
        "UNIKL MIIT ANGKATAN MAHASISWA ANTI RASUAH (AMAR)",
        "UNIKL MIIT LANGUAGE CLUB",
        "DEVELOPER STUDENT CLUBS UNIKL BY GOOGLE DEVELOPERS",
        "UNIKL MIIT PASUKAN ASKAR WATANIAH (PASKAW)",
        "AUDIO VISUAL CLUB",
        "SEKRETARIAT RAKAN MUDA UNIKL MIIT",
        "THE FILM SOCIETY",
        "SEKRETARIAT RUKUN NEGARA",
        "RAKAN SISWA YADIM",
        "YOUTH GUIDANCE COMMUNITY CLUB",
        "STUDENT ENTREPRENEURSHIP ASSOCIATION (SEA)",
        "UNIKL MIIT GANBATTE CLUB",
        "KOREAN CLUB",
        "UNIKL MIIT COMPUTER SYSTEM SECURITY CLUB",
        "SOFTWARE ENGINEERING CLUB",
        "UNIKL MIIT NETSYS CLUB",
        "UNIKL MIIT ANIMATION CLUB",
        "FICT STUDENT COMMITTEE",
        "POSTGRADUATE CLUB (PG CLUB)"
    ],
    "Campus Lifestyle Club (CL)" => [
        "UNIKL MIIT SILAT SENI GAYONG",
        "UNIKL MIIT BOWLING CLUB",
        "UNIKL MIIT BADMINTON CLUB",
        "UNIKL MIIT FOOTBALL CLUB",
        "BASKETBALL CLUB",
        "FUTSAL (FEMALE) CLUB",
        "FUTSAL (MALE) CLUB",
        "UNIKL MIIT TAEKWONDO CLUB",
        "NETBALL CLUB",
        "KELAB REKREASI UNIKL MIIT (KRUM)",
        "RUGBY CLUB",
        "UNIKL MIIT E-SPORT CLUB",
        "UNIKL MIIT DANCE CLUB",
        "UNIKL MIIT VOLLEYBALL CLUB",
        "FRISBEE MIIT",
        "PETANQUE CLUB",
        "PHANTOM SEPAK TAKRAW CLUB",
        "CHESS CLUB"
    ]
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_proposal'])) {
    $event_title = trim($_POST['event_title'] ?? '');
    $event_description = trim($_POST['event_description'] ?? '');
    $event_date = trim($_POST['event_date'] ?? '');
    $registration_link = trim($_POST['registration_link'] ?? '');
    $club_name = trim($_POST['club_name'] ?? '');
    $event_location = trim($_POST['event_location'] ?? '');
    $budget = trim($_POST['budget'] ?? '');
    $estimated_participants = trim($_POST['estimated_participants'] ?? '');

    // Validate required fields
    if (
        empty($event_title) || empty($event_description) || empty($event_date) ||
        empty($club_name) || empty($event_location) || empty($budget) || empty($estimated_participants)
    ) {
        $error_message = "Please fill in all required fields.";
    } elseif (!isset($_FILES['proposal_file'])) {
        $error_message = "Please upload the proposal file.";
    } else {
        // Handle file upload
        $uploads_dir = __DIR__ . '/../uploads/';
        if (!is_dir($uploads_dir)) {
            mkdir($uploads_dir, 0777, true);
        }
        $proposal_file = $_FILES['proposal_file'];
        $proposal_ext = strtolower(pathinfo($proposal_file['name'], PATHINFO_EXTENSION));
        $proposal_filename = uniqid('proposal_') . '.' . $proposal_ext;
        $proposal_target = $uploads_dir . $proposal_filename;

        // Add file type and size validation
        $allowed_exts = ['pdf', 'doc', 'docx'];
        $allowed_mimes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
        $max_size = 10 * 1024 * 1024; // 10MB

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $proposal_file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($proposal_ext, $allowed_exts) || !in_array($mime_type, $allowed_mimes)) {
            $error_message = "Proposal file must be a PDF or Microsoft Word document (.doc, .docx) only.";
        } elseif ($proposal_file['size'] > $max_size) {
            $error_message = "Proposal file must not exceed 10MB.";
        } elseif ($proposal_file['error'] !== UPLOAD_ERR_OK) {
            $error_message = "File upload error.";
        } elseif (!move_uploaded_file($proposal_file['tmp_name'], $proposal_target)) {
            $error_message = "Failed to upload proposal file.";
        } else {
            // Insert into proposals table
            $stmt = $conn->prepare("INSERT INTO proposals (student_id, event_title, event_description, event_date, registration_link, club_name, event_location, budget, estimated_participants, proposal_file, proposal_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')");
            $stmt->bind_param(
                "sssssssdss",
                $student_id,
                $event_title,
                $event_description,
                $event_date,
                $registration_link,
                $club_name,
                $event_location,
                $budget,
                $estimated_participants,
                $proposal_filename
            );
            if ($stmt->execute()) {
                $success_message = "Your proposal has been submitted successfully!";
                $show_form = false;
                // header("Location: ../admin/manage_proposals.php");
                // exit;
            } else {
                $error_message = "Database error: " . $conn->error;
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Submit Event Proposal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400&family=Inter:wght@400&display=swap" rel="stylesheet">
    <style>
        body { margin: 0; font-family: 'Segoe UI', Arial, sans-serif; background: #f7fafd; color: #18344a; }
        .form-popup { background: #fff; border-radius: 10px; padding: 32px 32px 24px 32px; box-shadow: 0 4px 24px rgba(0,0,0,0.12); width: 700px; max-width: 98vw; margin: 40px auto; position: relative; }
        .form-popup .close-btn { display: none; }
        .form-popup .section-title { font-size: 20px; font-weight: bold; margin-bottom: 18px; color: #ff9100; text-align: center; }
        .form-popup label { display: block; margin-top: 12px; margin-bottom: 4px; font-weight: 500; }
        .form-popup input[type="text"], .form-popup input[type="date"], .form-popup input[type="url"], .form-popup input[type="number"], .form-popup input[type="file"], .form-popup select, .form-popup textarea { width: 100%; padding: 8px 10px; border-radius: 4px; border: 1px solid #b0bec5; font-size: 15px; margin-bottom: 6px; }
        .form-popup textarea { min-height: 60px; resize: vertical; }
        .form-popup button[type="submit"] { margin-top: 18px; width: 100%; background: #1976d2; color: #fff; border: none; padding: 12px 0; border-radius: 4px; font-size: 16px; font-weight: bold; cursor: pointer; transition: background 0.2s; }
        .form-popup button[type="submit"]:hover { background: #1565c0; }
        .error-message { color: #d32f2f; background: #ffebee; border: 1px solid #d32f2f; border-radius: 4px; padding: 8px 12px; margin-bottom: 10px; text-align: center; }
        .success-message { color: #388e3c; background: #e8f5e9; border: 1px solid #388e3c; border-radius: 4px; padding: 8px 12px; margin-bottom: 10px; text-align: center; }
        @media (max-width: 800px) { .form-popup { width: 98vw; min-width: unset; padding: 16px 4vw; } }
        /* Back Button Styles */
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
        @media (max-width: 600px) {
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
    <div class="form-popup">
        <div class="section-title">Submit Your Event Proposal</div>
        <?php if ($error_message): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php elseif ($success_message): ?>
            <div class="success-message"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if ($show_form): ?>
        <form action="" method="POST" enctype="multipart/form-data" autocomplete="off">
            <label for="event_title">Event Title:</label>
            <input type="text" name="event_title" id="event_title" required>

            <label for="event_description">Event Description:</label>
            <textarea name="event_description" id="event_description" maxlength="30" required></textarea>

            <label for="event_date">Event Date:</label>
            <input type="date" name="event_date" id="event_date" required>

            <label for="registration_link">Registration Link:</label>
            <input type="url" name="registration_link" id="registration_link">

            <label for="club_name">Club Name:</label>
            <select name="club_name" id="club_name" required>
                <option value="">-- Select Club --</option>
                <optgroup label="Student Development Club (SD)">
<?php foreach ($clubs["Student Development Club (SD)"] as $club): ?>
<option value="<?php echo htmlspecialchars($club); ?>"><?php echo htmlspecialchars($club); ?></option>
<?php endforeach; ?>
                </optgroup>
                <optgroup label="Campus Lifestyle Club (CL)">
<?php foreach ($clubs["Campus Lifestyle Club (CL)"] as $club): ?>
<option value="<?php echo htmlspecialchars($club); ?>"><?php echo htmlspecialchars($club); ?></option>
<?php endforeach; ?>
                </optgroup>
            </select>
            <label for="event_location">Event Location:</label>
            <input type="text" name="event_location" id="event_location" required>

            <label for="budget">Budget Requested (RM):</label>
            <input type="number" name="budget" id="budget" min="0" required>

            <label for="estimated_participants">Estimated Participants:</label>
            <input type="number" name="estimated_participants" id="estimated_participants" min="1" required>

            <label for="proposal_file">Proposal File (PDF/DOC/DOCX, max 10MB):</label>
            <input type="file" name="proposal_file" id="proposal_file" accept=".pdf,.doc,.docx" required>

            <button type="submit" name="submit_proposal">Submit Proposal</button>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>