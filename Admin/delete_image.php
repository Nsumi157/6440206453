<?php
session_start();
include '../connetDB/con_db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $imageName = $_POST['image_name'];
    $bagId = $_POST['bag_id'];

    // ลบไฟล์รูปภาพจากเซิร์ฟเวอร์
    $imagePath = '../uploads/' . $imageName;
    if (file_exists($imagePath)) {
        unlink($imagePath); // ลบไฟล์
    }

    // ลบข้อมูลรูปภาพจากฐานข้อมูล
    $deleteSql = "DELETE FROM pictures WHERE B_img = ? AND Bag_id = ?";
    $deleteStmt = $conn->prepare($deleteSql);
    $deleteStmt->bind_param("ss", $imageName, $bagId);
    
    if ($deleteStmt->execute()) {
        echo "<script>
        
        window.location='editproduct.php?status=deleted';
      </script>";
       
    } else {
        // ลบไม่สำเร็จ
        echo "เกิดข้อผิดพลาดในการลบรูปภาพ";
    }
}
?>
