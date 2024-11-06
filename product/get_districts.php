<?php
$province_id = $_GET['province_id'];

$conn = new mysqli('localhost', 'root', '', 'dbonline_bag');
$conn->set_charset('utf8mb4');

$sql = "SELECT District_id, District_name FROM district WHERE Province_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $province_id);
$stmt->execute();
$result = $stmt->get_result();

echo '<option value="">เลือกอำเภอ</option>';
while ($row = $result->fetch_assoc()) {
    echo '<option value="' . $row['District_id'] . '">' . htmlspecialchars($row['District_name'], ENT_QUOTES, 'UTF-8') . '</option>';
}
?>
