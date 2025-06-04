<?php
include __DIR__ . '/config/config.php';
session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit;
}

// Check if a proposal submission is being made
if (isset($_POST['submit'])) {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $event_date = $_POST['event_date'];
    $registration_link = $_POST['registration_link'];
    $club_name = $_POST['club_name'];
    $event_location = $_POST['event_location'];
    $person_in_charge = $_POST['person_in_charge'];
    $phone_number = $_POST['phone_number'];
    $budget = $_POST['budget']; // Make sure you collect this data from the form
    $estimated_participants = $_POST['estimated_participants']; // Likewise

    // Upload files
    $poster = $_FILES['poster']['name'];
    $proposal = $_FILES['proposal']['name'];
    move_uploaded_file($_FILES['poster']['tmp_name'], "uploads/" . $poster);
    move_uploaded_file($_FILES['proposal']['tmp_name'], "uploads/" . $proposal);

    // Insert into proposals table first
    $student_id = $_SESSION['student_id'];
    $query = "INSERT INTO proposals (student_id, event_title, event_description, event_date, registration_link, club_name, event_location, project_manager, phone_number, budget, estimated_participants, poster, proposal_file)
              VALUES ('$student_id', '$title', '$description', '$event_date', '$registration_link', '$club_name', '$event_location', '$person_in_charge', '$phone_number', '$budget', '$estimated_participants', '$poster', '$proposal')";

    if ($conn->query($query)) {
        $proposal_id = $conn->insert_id; // Get the ID of the inserted proposal
        
        // Insert into events table
        $event_query = "INSERT INTO events (proposal_id, registration_link) VALUES ('$proposal_id', '$registration_link')";
        
        if ($conn->query($event_query)) {
            echo "Event submitted successfully!";
        } else {
            echo "Error inserting event details: " . $conn->error;
        }
    } else {
        echo "Error submitting event proposal: " . $conn->error;
    }
}

// View Proposal Status
if (isset($_POST['view_status'])) {
    $student_id = $_SESSION['student_id'];
    $query = "SELECT proposal_status FROM proposals WHERE student_id = '$student_id' ORDER BY created_at DESC LIMIT 1"; // Get latest proposal status

    $result = $conn->query($query);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo "Your proposal status is: " . $row['proposal_status'];
    } else {
        echo "No proposals found or an error occurred.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Submit Event</title>
</head>
<body>
    <h1>Submit Event</h1>
    <form method="POST" enctype="multipart/form-data">
        <label for="title">Event Title:</label>
        <input type="text" name="title" required><br>

        <label for="description">Event Description:</label>
        <textarea name="description" required></textarea><br>

        <label for="event_date">Event Date:</label>
        <input type="date" name="event_date" required><br>

        <label for="registration_link">Registration Link:</label>
        <input type="url" name="registration_link"><br>

        <label for="club_name">Club Name:</label>
        <input type="text" name="club_name" required><br>

        <label for="event_location">Event Location:</label>
        <input type="text" name="event_location" required><br>

        <label for="person_in_charge">Project Manager:</label>
        <input type="text" name="person_in_charge" required><br>

        <label for="phone_number">Phone Number:</label>
        <input type="text" name="phone_number" required><br>

        <label for="budget">Event Budget:</label>
        <input type="number" name="budget" required><br>

        <label for="estimated_participants">Estimated Participants:</label>
        <input type="number" name="estimated_participants" required><br>

        <label for="poster">Upload Poster:</label>
        <input type="file" name="poster" required><br>

        <label for="proposal">Upload Proposal:</label>
        <input type="file" name="proposal" required><br>

        <button type="submit" name="submit">Submit Event</button>
    </form>

    <!-- Button to View Proposal Status -->
    <form method="POST">
        <button type="submit" name="view_status">View Proposal Status</button>
    </form>
</body>
</html>
