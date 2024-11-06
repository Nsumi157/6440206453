<?php
session_start();
include '../connetDB/con_db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new-bag-type'])) {
    // ประมวลผลการเพิ่มประเภทใหม่
    $newBagType = trim($_POST['new-bag-type']);
    
    // ตรวจสอบว่ามีการกรอกประเภทใหม่หรือไม่
    if (!empty($newBagType)) {
        // ป้องกัน SQL Injection
        $newBagType = $conn->real_escape_string($newBagType);
        
        // เริ่มต้นรหัสประเภทที่ "A1"
        $prefix = 'A';
        $suffix = '1';
        $newTypeId = $prefix . $suffix;

        // วนลูปเพื่อตรวจสอบว่ารหัสซ้ำหรือไม่
        do {
            $sqlCheckDuplicate = "SELECT * FROM bag_type WHERE type_id = '$newTypeId'";
            $checkResult = $conn->query($sqlCheckDuplicate);

            if ($checkResult->num_rows > 0) {
                $prefix = chr(ord($prefix) + 1);
                $newTypeId = $prefix . $suffix;
            } else {
                break;
            }
        } while ($prefix <= 'Z');

        // ตรวจสอบว่ามีรหัสที่ไม่ซ้ำแล้วหรือไม่
        if ($prefix > 'Z') {
            echo "ไม่สามารถสร้างรหัสประเภทใหม่ได้";
        } else {
            // เพิ่มประเภทใหม่ในฐานข้อมูล พร้อมรหัสที่เจนใหม่
            $sqlInsert = "INSERT INTO bag_type (type_id, type_name) VALUES ('$newTypeId', '$newBagType')";
            if ($conn->query($sqlInsert) === TRUE) {
                echo "<script>
                alert('เพิ่มประเภทใหม่สำเร็จ');
                window.location.href = 'addproduct.php'; // เปลี่ยนเป็นชื่อหน้าหลักของคุณ
                </script>";
            } else {
                echo "เกิดข้อผิดพลาด: " . $conn->error;
            }
        }
    } else {
        echo "กรุณากรอกประเภทใหม่";
    }
}
?>

<!-- popup_content.php -->
<div>
    <h3>เพิ่มประเภทใหม่</h3>
    <form id="new-bag-type-form" method="POST" action="process_add_product.php">
        <input type="text" name="new-bag-type" placeholder="กรอกประเภทใหม่" required>
        <button type="submit">เพิ่มประเภทใหม่</button>
    </form>
    <button id="close-popup-btn" style="background: none; border: none; font-size: 24px; position: absolute; top: 10px; right: 10px;">&times;</button>
</div>

<script>
    document.getElementById('close-popup-btn').addEventListener('click', function() {
        document.getElementById('overlay').style.display = 'none';
        document.getElementById('popup').style.display = 'none';
    });
</script>

<style>
    #popup {
        position: relative;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        border-radius: 5px;
        padding: 20px;
    }
</style>
