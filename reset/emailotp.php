<?php
session_start();
include '../connetDB/con_db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    // ตรวจสอบว่าอีเมลนั้นมีอยู่ในระบบหรือไม่ (เชื่อมต่อกับฐานข้อมูลเพื่อดู)
    // สมมติว่าได้ตรวจสอบและยืนยันว่ามีอีเมลนั้นอยู่ในระบบแล้ว

    // สร้างรหัส OTP หรือรหัสชั่วคราว
    $otp = rand(100000, 999999); // ตัวอย่าง: OTP 6 หลัก

    // ส่งอีเมล
    $subject = "Reset Password OTP";
    $message = "Your OTP code to reset your password is: " . $otp;
    $headers = "From: no-reply@yourdomain.com";

    if (mail($email, $subject, $message, $headers)) {
        // เก็บ OTP ในเซสชั่นหรือฐานข้อมูลชั่วคราวเพื่อเปรียบเทียบในขั้นตอนถัดไป
        session_start();
        $_SESSION['otp'] = $otp;
        $_SESSION['email'] = $email;

        // เปลี่ยนเส้นทางไปยังหน้า OTP
        header("Location: otp.html");
    } else {
        echo "Error sending email.";
    }
}
?>
