<?php
session_start();
session_unset(); // ล้างข้อมูล session ทั้งหมด
session_destroy(); // ทำลาย session

// เปลี่ยนเส้นทางกลับไปที่หน้าเข้าสู่ระบบหรือตำแหน่งอื่น ๆ ตามต้องการ
header("Location: login.php");
exit();
?>
