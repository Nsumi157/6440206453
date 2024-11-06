<?php
session_start();
include '../connetDB/con_db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ตรวจสอบว่าผู้ใช้ได้กรอกวัสดุใหม่หรือไม่
    if (isset($_POST['new-bag-material']) && !empty($_POST['new-bag-material'])) {
        $newBagMaterial = $_POST['new-bag-material'];

        // เริ่มต้นรหัสวัสดุที่ "A1"
        $prefix = 'A';
        $suffix = '1';
        $newMaterialId = $prefix . $suffix;

        // วนลูปเพื่อตรวจสอบว่ารหัสซ้ำหรือไม่
        do {
            $sqlCheckDuplicate = "SELECT * FROM `material` WHERE Material_id = '$newMaterialId'";
            $checkResult = $conn->query($sqlCheckDuplicate);

            if ($checkResult->num_rows > 0) {
                // หากซ้ำ ให้เพิ่มตัวอักษรภาษาอังกฤษถัดไป
                $prefix = chr(ord($prefix) + 1); // ใช้ฟังก์ชัน chr() และ ord() ในการเพิ่มตัวอักษร
                $newMaterialId = $prefix . $suffix;
            } else {
                break; // ออกจากลูปเมื่อเจอรหัสที่ไม่ซ้ำ
            }
        } while ($prefix <= 'Z'); // ตรวจสอบถึงตัวอักษร Z

        // ตรวจสอบว่ามีรหัสที่ไม่ซ้ำแล้วหรือไม่
        if ($prefix > 'Z') {
            echo "ไม่สามารถสร้างรหัสวัสดุใหม่ได้";
        } else {
            // เพิ่มวัสดุใหม่ในฐานข้อมูล พร้อมรหัสที่เจนใหม่
            $sqlInsert = "INSERT INTO material (`Material_id`, `Material_name`) VALUES ('$newMaterialId', '$newBagMaterial')";
            if ($conn->query($sqlInsert) === TRUE) {
                echo "<script>
                alert('เพิ่มวัสดุใหม่สำเร็จ');
                window.location.href = 'addproduct.php';
              </script>";
                $bagMaterialId = $newMaterialId;
            } else {
                echo "เกิดข้อผิดพลาด: " . $conn->error;
            }
        }
    } else {
        // ใช้วัสดุที่มีอยู่แล้ว
        $bagMaterialId = $_POST['bag-material'];
    }
}







?>

<!-- popup_content.php -->
<div>
    <h3>เพิ่มวัสดุใหม่</h3>
    <form id="new-bag-material-form" method="POST" action="process_add_material.php">
        <input type="text" name="new-bag-material" placeholder="กรอกวัสดุใหม่" required>
        <button type="submit">เพิ่มวัสดุใหม่</button>
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



