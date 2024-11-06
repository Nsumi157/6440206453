<?php
session_start();
include '../connetDB/con_db.php';

$First_name = ""; // กำหนดค่าเริ่มต้นให้กับ $First_name

// ตรวจสอบว่ามีการส่งข้อมูล POST หรือไม่
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $Admin_id = $_POST['Admin_id'];
    $Password = $_POST['Password'];

    // เตรียมคำสั่ง SQL
    $query = "SELECT `Admin_id`, `First_name`, `Last_name`, `Password` FROM `admin` WHERE `Admin_id` = ? AND `Password` = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $Admin_id, $Password);
    $stmt->execute();
    
    // รับผลลัพธ์
    $result = $stmt->get_result();

    // ตรวจสอบผลลัพธ์
    if ($result->num_rows == 1) {
        // ข้อมูลถูกต้อง
        $row = $result->fetch_assoc();
        $First_name = $row['First_name']; 
        $Last_name = $row['Last_name']; 

        // เก็บค่า $First_name และ $Last_name ใน session
        $_SESSION['First_name'] = $First_name;
        $_SESSION['Last_name'] = $Last_name;
        $_SESSION['Admin_id'] = $Admin_id; 

        // อัปเดตค่า s_admin เป็น 'Y' ในฐานข้อมูล
        $update_query = "UPDATE `admin` SET `s_admin` = 'Y' WHERE `Admin_id` = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("s", $Admin_id);
        $update_stmt->execute();
        $update_stmt->close();

        // แสดงข้อความและเปลี่ยนหน้า
        echo "<script>
                alert('เข้าสู่ระบบสำเร็จ');
                window.location='reportform.php';
              </script>";
        exit();
    } else {
        // ข้อมูลไม่ถูกต้อง
        $_SESSION['error'] = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
        echo "<script>
                alert('ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง');
                window.location='login.php';
              </script>";
        exit();
    }

    $stmt->close();
}
?>
