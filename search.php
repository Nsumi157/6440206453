<?php
session_start();

$conn = new mysqli('localhost', 'root', '', 'dbonline_bag');
$conn->set_charset('utf8mb4');

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("การเชื่อมต่อล้มเหลว: " . $conn->connect_error);
}
// รับค่าจาก URL และตรวจสอบว่ามีการตั้งค่า
$price = isset($_GET['price']) ? intval($_GET['price']) : 1000;

// ตรวจสอบว่าราคามีค่าอย่างน้อย 1000
if ($price < 1000) {
    $price = 1000; // ตั้งค่าราคาเริ่มต้น
}

// ค้นหา bag_id ที่มีราคาต่ำสุด 1000 และไม่เกิน $price
$sql = "SELECT * FROM bag WHERE price >= 1000 AND price <= ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $price);
$stmt->execute();
$result = $stmt->get_result();

// แสดงผลลัพธ์การค้นหา
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // ตรวจสอบว่า key 'bag_id' และ 'price' มีอยู่ใน $row
        $bagId = isset($row['bag_id']) ? $row['bag_id'] : 'ไม่มีข้อมูล';
        $bagPrice = isset($row['price']) ? $row['price'] : 'ไม่มีข้อมูล';

        echo "<div>Bag ID: " . htmlspecialchars($bagId) . " ราคา: " . htmlspecialchars($bagPrice) . " บาท</div>";
    }
} else {
    echo "ไม่พบข้อมูลที่ตรงกับช่วงราคาที่เลือก";
}

$stmt->close();
$conn->close();
?>
