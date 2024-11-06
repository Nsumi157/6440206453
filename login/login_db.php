<?php
session_start();
include '../connetDB/con_db.php';



$First_name = ""; // กำหนดค่าเริ่มต้นให้กับ $First_name

// ตรวจสอบว่ามีการส่งข้อมูล POST หรือไม่
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $Member_id = $_POST['Member_id'];
    $Password = $_POST['Password'];


    // เตรียมคำสั่ง SQL
    $query = "SELECT `Member_id`, `First_name`, `Last_name`, `Password` FROM `member` WHERE `Member_id` = ? AND `Password` = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $Member_id, $Password);
    $stmt->execute();
    
    // รับผลลัพธ์
    $result = $stmt->get_result();

    // ตรวจสอบผลลัพธ์
    if ($result->num_rows == 1) {
        // ข้อมูลถูกต้อง
        $row = $result->fetch_assoc();
        $First_name = $row['First_name']; // กำหนดค่า $First_name จากผลลัพธ์ที่ได้จากการค้นหาในฐานข้อมูล
        $Last_name = $row['Last_name']; // กำหนดค่า $Last_name จากผลลัพธ์ที่ได้จากการค้นหาในฐานข้อมูล

        // เก็บค่า $First_name และ $Last_name ใน session
        $_SESSION['First_name'] = $First_name;
        $_SESSION['Last_name'] = $Last_name;
        $_SESSION['Member_id'] = $Member_id; // เก็บชื่อผู้ใช้ใน session สำหรับการใช้งานอื่นๆ ต่อไป
        echo "<script>
                alert('เข้าสู่ระบบสำเร็จ');
                window.location='../index.php';
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
