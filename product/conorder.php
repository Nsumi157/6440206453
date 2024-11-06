<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'dbonline_bag');
$conn->set_charset('utf8mb4');

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("การเชื่อมต่อล้มเหลว: " . $conn->connect_error);
}

// ตั้งค่าโซนเวลา
date_default_timezone_set('Asia/Bangkok');

// รับค่า Receipt_id จากแบบฟอร์ม
$Receipt_id = isset($_POST['Receipt_id']) ? $_POST['Receipt_id'] : '';
$Order_id = isset($_POST['Order_id']) ? $_POST['Order_id'] : '';
if ($Order_id) {
    // ตรวจสอบว่า Order_id นั้นมีอยู่ในฐานข้อมูลหรือไม่ และดึง Receipt_id ที่ตรงกัน
    $sql_check = "SELECT o.`Receipt_id` FROM `orders` o WHERE o.`Order_id` = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $Order_id);
    $stmt_check->execute();
    $stmt_check->store_result();
    $stmt_check->bind_result($Receipt_id);
    $stmt_check->fetch();

    // รับวันที่และเวลา
    $Date_img = date('Y-m-d');
    $Time_img = date('H:i:s');

    if ($stmt_check->num_rows > 0 && $Receipt_id) {
        // ตรวจสอบว่ามีการอัปโหลดไฟล์ภาพหรือไม่
        if (isset($_FILES['Receipt_img']) && is_uploaded_file($_FILES['Receipt_img']['tmp_name'])) {
            // ใช้ชื่อไฟล์ต้นฉบับแต่เพิ่ม 'b_' ด้านหน้า
            $original_file_name = basename($_FILES['Receipt_img']['name']);
            $new_image_name = 'b_' . $original_file_name;
            $image_upload_path = "../pay/" . $new_image_name;

            // ย้ายไฟล์ไปยังโฟลเดอร์ที่กำหนด
            if (move_uploaded_file($_FILES['Receipt_img']['tmp_name'], $image_upload_path)) {
                // อัปเดตข้อมูลในฐานข้อมูลตาม Receipt_id ที่ได้จาก Order_id
                $sql = "UPDATE `receipt` r INNER JOIN `orders` o ON r.`Receipt_id` = o.`Receipt_id` 
                        SET r.`Receipt_img` = ?, r.`Date_img` = ?, r.`Time_img` = ?, o.`Status_id` = 2 
                        WHERE o.`Order_id` = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssss", $new_image_name, $Date_img, $Time_img, $Order_id);
                $result = $stmt->execute();

                if ($result) {
                    echo "<script>alert('ทำการสั่งซื้อสินค้าสำเร็จ!'); window.location = 'history.php';</script>";
                } else {
                    echo "<script>alert('ไม่สามารถบันทึกข้อมูลได้'); window.location = 'history.php';</script>";
                }
                $stmt->close();
            } else {
                echo "<script>alert('ไม่สามารถอัปโหลดไฟล์ได้'); window.location = 'history.php';</script>";
            }
        } else {
            echo "<script>alert('ไม่มีไฟล์ถูกเลือก'); window.location = 'history.php';</script>";
        }
    } else {
        echo "<script>alert('ไม่พบ Order ID นี้ในระบบ หรือไม่มี Receipt ID ที่ตรงกัน'); window.location = 'history.php';</script>";
    }
    $stmt_check->close();
} else {
    echo "<script>alert('ไม่ได้รับค่า Order ID'); window.location = 'history.php';</script>";
}

$conn->close();
?>