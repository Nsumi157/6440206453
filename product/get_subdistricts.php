<?php
$district_id = $_GET['district_id'];

$conn = new mysqli('localhost', 'root', '', 'dbonline_bag');
$conn->set_charset('utf8mb4');

$sql = "SELECT Subdistrict_id, Subdistrict_name FROM subdistrict WHERE District_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $district_id);
$stmt->execute();
$result = $stmt->get_result();

echo '<option value="">เลือกตำบล</option>';
while ($row = $result->fetch_assoc()) {
    echo '<option value="' . $row['Subdistrict_id'] . '">' . htmlspecialchars($row['Subdistrict_name'], ENT_QUOTES, 'UTF-8') . '</option>';
}
?>
