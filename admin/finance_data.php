<?php
header('Content-Type: application/json');
session_start();
include __DIR__ . '/../config/config.php';

if (!isset($_SESSION['admin_name'])) {
    http_response_code(403);
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : null;
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : null;

$where = "WHERE proposal_status = 'Approved' AND approved_budget > 0";
$params = [];
if ($start_date && $end_date) {
    $where .= " AND event_date >= ? AND event_date <= ?";
    $params[] = $start_date;
    $params[] = $end_date;
}

$sql = "SELECT club_name, SUM(approved_budget) AS total_approved FROM proposals $where GROUP BY club_name";
$stmt = $conn->prepare($sql);
if (count($params) === 2) {
    $stmt->bind_param('ss', $params[0], $params[1]);
}
$stmt->execute();
$result = $stmt->get_result();
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        'club_name' => $row['club_name'],
        'total_approved' => (float)$row['total_approved']
    ];
}
echo json_encode($data);
$stmt->close();
$conn->close();
?>