<?php
$subdistrict_id = $_GET['subdistrict_id'];

$conn = new mysqli('localhost', 'root', '', 'dbonline_bag');
$conn->set_charset('utf8mb4');

$sql = "SELECT Postcode FROM subdistrict WHERE Subdistrict_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $subdistrict_id);
$stmt->execute();
$result = $stmt->get_result();

$postcode = $result->fetch_assoc();

echo json_encode($postcode);
?>
