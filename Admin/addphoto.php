<?php
session_start();

// เชื่อมต่อกับฐานข้อมูล
$conn = new mysqli('localhost', 'root', '', 'dbonline_bag');
$conn->set_charset('utf8mb4');

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("การเชื่อมต่อล้มเหลว: " . $conn->connect_error);
}



if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ตรวจสอบการเข้าสู่ระบบ
    if (!isset($_SESSION['Admin_id'])) {
        die("กรุณาเข้าสู่ระบบก่อนทำการสั่งซื้อ");
    }

    $admin_id = $_SESSION['Admin_id']; // รับค่า Admin_id
    $bag_id = $_POST['bag_id'];
    $type_name = $_POST['type_name'];
    $brand_name = $_POST['brand_name'];
    $Material_name = $_POST['Material_name'];
    $Cost_price = $_POST['Cost_price'];
    $Price = $_POST['Price'];

    // ตรวจสอบและแปลง Colors_code เป็นอาเรย์
    $colors_codes = isset($_POST['Total']) ? array_keys($_POST['Total']) : []; // ดึงเฉพาะ Colors_code ที่มีการกรอก Total
    $total_array = $_POST['Total']; // รับจำนวนทั้งหมดในรูปแบบอาร์เรย์

    // เริ่ม transaction
    $conn->begin_transaction();

    try {
        // Insert ข้อมูลกระเป๋าลงตาราง bag
        $sql = "INSERT INTO bag (bag_id, type_id, brand_id, Material_id, Cost_price, Price, Admin_id) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssdds", $bag_id, $type_name, $brand_name, $Material_name, $Cost_price, $Price, $admin_id);
        $stmt->execute();

// ตั้งค่าที่อยู่ในการจัดเก็บไฟล์
$uploadDir = '../../img/';

// ตรวจสอบว่าไดเรกทอรีมีอยู่หรือไม่
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true); // สร้างไดเรกทอรีถ้ายังไม่มี
}

// ตรวจสอบว่ามีไฟล์ถูกอัปโหลด
if (!empty($_FILES['images']['name'][0])) {
    $uploadedFilesCount = 0; // ตัวนับไฟล์ที่ถูกอัปโหลด

    // รับค่ารายการไฟล์ที่ถูกลบจาก input hidden
    $deletedImages = isset($_POST['deleted_images']) ? explode(',', rtrim($_POST['deleted_images'], ',')) : [];

    foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
        $fileName = $_FILES['images']['name'][$key];

        // ตรวจสอบว่าไฟล์นี้อยู่ในรายการที่ถูกลบหรือไม่
        if (in_array($fileName, $deletedImages)) {
            continue; // ข้ามไฟล์นี้ถ้าอยู่ในรายการที่ถูกลบ
        }

        if ($uploadedFilesCount >= 10) {
            break; // หยุดหากจำนวนไฟล์ถึง 10
        }

        $fileTmp = $_FILES['images']['tmp_name'][$key];

        // ตั้งค่าที่อยู่ในการจัดเก็บไฟล์
        $uploadFile = $uploadDir . basename($fileName);

        // ตรวจสอบว่าไฟล์สามารถถูกย้ายได้หรือไม่
        if (move_uploaded_file($fileTmp, $uploadFile)) {
            // Insert ข้อมูลรูปภาพลงตาราง pictures
            $sqlImage = "INSERT INTO pictures (bag_id, B_img) VALUES (?, ?)";
            $stmtImage = $conn->prepare($sqlImage);
            $stmtImage->bind_param("ss", $bag_id, $uploadFile);
            $stmtImage->execute();
            $stmtImage->close(); // ปิด statement หลังจากใช้เสร็จ
            
            $uploadedFilesCount++; // เพิ่มตัวนับ
        } else {
            // แจ้งข้อผิดพลาดถ้าย้ายไฟล์ไม่ได้
            throw new Exception("ไม่สามารถย้ายไฟล์ $fileName ไปยัง $uploadDir ได้");
        }
    }

    // แจ้งจำนวนไฟล์ที่ถูกอัปโหลด
    echo "$uploadedFilesCount รูปภาพถูกอัปโหลดเรียบร้อยแล้ว";
} else {
    echo "กรุณาเลือกไฟล์ที่จะอัปโหลด";
}




        // ตรวจสอบค่าที่รับมาว่าถูกต้องหรือไม่
        foreach ($colors_codes as $index => $colors_code) {
            $total = isset($total_array[$colors_code]) ? $total_array[$colors_code] : 0;
            if ($total <= 0) {
                continue; // ข้ามสีที่ไม่ได้กรอกจำนวนหรือจำนวนเท่ากับ 0
            }

            // ตรวจสอบว่ามี Colors_code นี้อยู่ในฐานข้อมูลหรือไม่
            $sqlCheckColor = "SELECT COUNT(*) FROM colors WHERE Colors_code = ?";
            $stmtCheckColor = $conn->prepare($sqlCheckColor);
            $stmtCheckColor->bind_param("s", $colors_code);
            $stmtCheckColor->execute();
            $stmtCheckColor->bind_result($count);
            $stmtCheckColor->fetch();
            $stmtCheckColor->close();

            // ถ้ามีสีนี้ในฐานข้อมูลให้ทำการ INSERT ลงใน bag_color
            if ($count > 0) {
                // Insert สีและจำนวนลงตาราง bag_colors
                $sqlColor = "INSERT INTO bag_color (bag_id, Colors_code, Total) VALUES (?, ?, ?)";
                $stmtColor = $conn->prepare($sqlColor);
                $stmtColor->bind_param("ssi", $bag_id, $colors_code, $total);
                $stmtColor->execute();
                $stmtColor->close(); // ปิด statement หลังจากใช้เสร็จ
            } else {
                throw new Exception("ข้อมูลสีไม่ถูกต้อง: $colors_code");
            }
        }

        // ยืนยันการบันทึกข้อมูล
        $conn->commit();
        echo "<script>alert('เพิ่มสินค้าใหม่สำเร็จ!'); window.location = 'addproduct.php';</script>";

    } catch (Exception $e) {
        // หากเกิดข้อผิดพลาด ย้อนกลับการทำงานทั้งหมด
        $conn->rollback();
        echo "เกิดข้อผิดพลาด: " . $e->getMessage();
    } finally {
        // ปิด statement
        if (isset($stmt)) {
            $stmt->close();
        }
        $conn->close(); // ปิดการเชื่อมต่อ
    }
}
?>
