<?php
session_start();
include '../connetDB/con_db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // รับค่าจากฟอร์ม
    $bag_id = $_POST['Bag_id'];
    $type_id = $_POST['type_name'];
    $brand_id = $_POST['brand_name'];
    $material_id = $_POST['Material_name'];
    $total = $_POST['Total'];
    $price = $_POST['Price'];
    $cost_price = $_POST['Cost_price'];
    $Colors_code = $_POST['Colors_code'];
    // อัปเดตข้อมูลสินค้าในตาราง bag
    $sql = "UPDATE bag 
            SET type_id = ?, brand_id = ?, Material_id = ?, Price = ?, Cost_price = ? 
            WHERE Bag_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssss", $type_id, $brand_id, $material_id, $price, $cost_price, $bag_id);
    $stmt->execute();
    
    // อัปเดตข้อมูลจำนวนในตาราง bag_color
    $sqlColor = "UPDATE bag_color 
                 SET Total = ? 
                 WHERE Bag_id = ? and Colors_code = ?";
    $stmtColor = $conn->prepare($sqlColor);
    $stmtColor->bind_param("sss", $total, $bag_id, $Colors_code);
    $stmtColor->execute();

// ตรวจสอบว่าไฟล์รูปภาพใหม่ถูกอัปโหลดหรือไม่
if (!empty($_FILES['B_img']['name'][0])) {
    // ตั้งค่าที่อยู่ในการจัดเก็บไฟล์
    $uploadDir = '../../img/';

    // ตรวจสอบว่าไดเรกทอรีมีอยู่หรือไม่ ถ้าไม่มีให้สร้าง
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // **ไม่ลบรูปภาพเก่าที่เกี่ยวข้องกับ Bag_id**

    // อัปโหลดรูปภาพใหม่
    foreach ($_FILES['B_img']['tmp_name'] as $key => $tmp_name) {
        $fileName = $_FILES['B_img']['name'][$key];
        $fileTmp = $_FILES['B_img']['tmp_name'][$key];
        $uploadFile = $uploadDir . basename($fileName);

        if (move_uploaded_file($fileTmp, $uploadFile)) {
            // แทรกพาธของรูปภาพใหม่ลงในตาราง pictures
            $imagePath = $uploadDir . $fileName;
            $sqlImage = "INSERT INTO pictures (Bag_id, B_img) VALUES (?, ?)";
            $stmtImage = $conn->prepare($sqlImage);
            $stmtImage->bind_param("ss", $bag_id, $imagePath);
            $stmtImage->execute();
        } else {
            echo "ไม่สามารถอัปโหลดรูปภาพ $fileName ได้";
        }

        // ตรวจสอบว่าอัปโหลดรูปภาพเสร็จแล้ว
if ($stmtImage->affected_rows > 0) {
    // ดึงรูปภาพใหม่ที่ถูกอัปโหลด
    $sqlSelectImages = "SELECT B_img FROM pictures WHERE Bag_id = ?";
    $stmtSelectImages = $conn->prepare($sqlSelectImages);
    $stmtSelectImages->bind_param("s", $bag_id);
    $stmtSelectImages->execute();
    $resultImages = $stmtSelectImages->get_result();

    // แสดงรูปภาพ
    echo '<div class="uploaded-images">';
    while ($row = $resultImages->fetch_assoc()) {
        echo '<img src="' . htmlspecialchars($row['B_img']) . '" alt="Uploaded Image" style="width: 150px; height: auto; margin: 5px;">';
    }
    echo '</div>';
}

    }
}



    // ปิด statement หลังจากใช้งานเสร็จ
    $stmt->close();
    $stmtColor->close();
    
    // แจ้งเตือนและเปลี่ยนเส้นทางกลับไปยังหน้าจัดการข้อมูล
    echo "<script>alert('อัพเดตข้อมูลสำเร็จ'); window.location.href='editproduct.php';</script>";
} else {
    echo "<script>alert('เกิดข้อผิดพลาด'); window.location.href='editproduct.php';</script>";
}


?>
