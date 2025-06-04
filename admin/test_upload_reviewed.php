<?php
require_once "../config/config.php";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proposal_id']) && isset($_FILES['reviewed_proposal'])) {
    $proposal_id = intval($_POST['proposal_id']);
    $file = $_FILES['reviewed_proposal'];
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($file_ext === 'pdf') {
        $target_dir = "../reviewed_proposals/";
        $filename = uniqid('reviewed_') . '_' . basename($file['name']);
        $target_file = $target_dir . $filename;
        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            $stmt = $conn->prepare("UPDATE proposals SET reviewed_proposal = ? WHERE id = ?");
            $stmt->bind_param("si", $filename, $proposal_id);
            $stmt->execute();
            $stmt->close();
            echo "Upload successful!";
        } else {
            echo "File upload failed!";
        }
    } else {
        echo "Only PDF files allowed!";
    }
}
?>
<form method="post" enctype="multipart/form-data">
    Proposal ID: <input type="text" name="proposal_id" required><br>
    Reviewed Proposal (PDF): <input type="file" name="reviewed_proposal" required><br>
    <button type="submit">Upload</button>
</form>