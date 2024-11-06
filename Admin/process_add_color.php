<?php
session_start();
include '../connetDB/con_db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ตรวจสอบว่าผู้ใช้ได้กรอกสีใหม่หรือไม่
    if (isset($_POST['new-bag-color']) && !empty($_POST['new-bag-color'])) {
        $newBagColor = $_POST['new-bag-color'];

        // เริ่มต้นรหัสสีใหม่ที่ "01"
        $newColorIdNum = 1;

        // วนลูปเพื่อตรวจสอบว่ารหัสซ้ำหรือไม่
        do {
            $newColorId = str_pad($newColorIdNum, 2, '0', STR_PAD_LEFT); // เติมเลข 0 ด้านหน้าให้ครบ 2 หลัก
            $sqlCheckDuplicate = "SELECT Colors_code FROM colors WHERE Colors_code = '$newColorId'";
            $checkResult = $conn->query($sqlCheckDuplicate);

            if ($checkResult->num_rows > 0) {
                // หากซ้ำ ให้เพิ่มเลข 1
                $newColorIdNum++;
            } else {
                break; // ออกจากลูปเมื่อเจอรหัสที่ไม่ซ้ำ
            }
        } while ($newColorIdNum <= 99); // ตรวจสอบถึง 99

        // ตรวจสอบว่ามีรหัสที่ไม่ซ้ำแล้วหรือไม่
        if ($newColorIdNum > 99) {
            echo "<script>alert('ไม่สามารถสร้างรหัสสีใหม่ได้');</script>";
        } else {
            // เพิ่มสีใหม่ในฐานข้อมูล พร้อมรหัสที่เจนใหม่
            $sqlInsert = "INSERT INTO colors (Colors_code, Colors_name) VALUES ('$newColorId', '$newBagColor')";
            if ($conn->query($sqlInsert) === TRUE) {
                echo "<script>
                alert('เพิ่มสีใหม่สำเร็จ');
                window.location.href = 'addproduct.php';
                </script>";
            } else {
                echo "<script>alert('เกิดข้อผิดพลาด: " . $conn->error . "');</script>";
            }
        }
    }
}
?>



<!-- ฟอร์มเพิ่มสีใหม่ -->
<div>
    <h3>เพิ่มสีใหม่</h3>
    <form id="new-bag-color-form" method="POST" action="process_add_color.php">
        <input type="text" name="new-bag-color" placeholder="กรอกสีใหม่" required>
        <button type="submit">เพิ่มสีใหม่</button>
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
