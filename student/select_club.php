<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['club_type'])) {
    $_SESSION['club_type'] = $_POST['club_type'];
    $_SESSION['club_name'] = $_POST['club_name'];
    header("Location: eventsubmission.php");
    exit;
}

$clubs = [
    "SD" => [
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
    "CL" => [
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

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Select Your Club</title>
</head>
<body>
    <h2>Select Your Club Type</h2>
    <form method="POST">
        <label for="club_type">Choose Club Type:</label>
        <select name="club_type" id="club_type" required onchange="updateClubOptions()">
            <option value="">Select</option>
            <option value="SD">Student Development Club (SD)</option>
            <option value="CL">Campus Lifestyle Club (CL)</option>
        </select>
        
        <br><br>

        <label for="club_name">Choose Your Club:</label>
        <select name="club_name" id="club_name" required>
            <option value="">Select Club</option>
        </select>

        <br><br>

        <button type="submit">Continue to Event Submission</button>
    </form>

    <script>
        const clubs = <?php echo json_encode($clubs); ?>;
        
        function updateClubOptions() {
            let clubType = document.getElementById("club_type").value;
            let clubDropdown = document.getElementById("club_name");

            clubDropdown.innerHTML = "<option value=''>Select Club</option>"; 

            if (clubType in clubs) {
                clubs[clubType].forEach(function(club) {
                    let option = document.createElement("option");
                    option.value = club;
                    option.textContent = club;
                    clubDropdown.appendChild(option);
                });
            }
        }
    </script>
</body>
</html>
