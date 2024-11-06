<?php
session_start();
include '../connetDB/con_db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ตรวจสอบว่าผู้ใช้ได้กรอกยี่ห้อใหม่หรือไม่
    if (isset($_POST['new-bag-brand']) && !empty($_POST['new-bag-brand'])) {
        $newBagBrand = $_POST['new-bag-brand'];

        // เริ่มต้นรหัสยี่ห้อที่ "A1"
        $prefix = 'A';
        $suffix = '1';
        $newBrandId = $prefix . $suffix;

        // วนลูปเพื่อตรวจสอบว่ารหัสซ้ำหรือไม่
        do {
            $sqlCheckDuplicate = "SELECT * FROM `bag_brand` WHERE brand_id = '$newBrandId'";
            $checkResult = $conn->query($sqlCheckDuplicate);

            if ($checkResult->num_rows > 0) {
                // หากซ้ำ ให้เพิ่มตัวอักษรภาษาอังกฤษถัดไป
                $prefix = chr(ord($prefix) + 1); // ใช้ฟังก์ชัน chr() และ ord() ในการเพิ่มตัวอักษร
                $newBrandId = $prefix . $suffix;
            } else {
                break; // ออกจากลูปเมื่อเจอรหัสที่ไม่ซ้ำ
            }
        } while ($prefix <= 'Z'); // ตรวจสอบถึงตัวอักษร Z

        // ตรวจสอบว่ามีรหัสที่ไม่ซ้ำแล้วหรือไม่
        if ($prefix > 'Z') {
            echo "ไม่สามารถสร้างรหัสยี่ห้อใหม่ได้";
        } else {
            // เพิ่มยี่ห้อใหม่ในฐานข้อมูล พร้อมรหัสที่เจนใหม่
            $sqlInsert = "INSERT INTO bag_brand (brand_id, brand_name) VALUES ('$newBrandId', '$newBagBrand')";
            if ($conn->query($sqlInsert) === TRUE) {
                echo "<script>
                alert('เพิ่มยี่ห้อใหม่สำเร็จ');
                window.location.href = 'addproduct.php';
              </script>";
                $bagBrandId = $newBrandId;
            } else {
                echo "เกิดข้อผิดพลาด: " . $conn->error;
            }
        }
    } else {
        // ใช้ยี่ห้อที่มีอยู่แล้ว
        $bagBrandId = $_POST['bag_brand'];
    }
}
?>

<!-- popup_content.php -->
<div>
    <h3>เพิ่มยี่ห้อใหม่</h3>
    <form id="new-bag-brand-form" method="POST" action="process_add_brand.php">
        <input type="text" name="new-bag-brand" placeholder="กรอกยี่ห้อใหม่" required>
        <button type="submit">เพิ่มยี่ห้อใหม่</button>
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
