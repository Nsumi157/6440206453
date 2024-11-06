<?php
session_start();
include '../connetDB/con_db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // รับค่าจากฟอร์ม
    $Member_id = $_POST['Member_id'];
    $Title_name = $_POST['Title_name'];
    $First_name = $_POST['First_name'];
    $Last_name = $_POST['Last_name'];
    $H_number = $_POST['H_number'];
    $Road = $_POST['Road'];
    $Alley = $_POST['Alley'];
    $Phone_number = $_POST['Phone_number']; 
  
    $Subdistrict_id = $_POST['Subdistrict_name'];
    $Postcode = $_POST['Postcode'];


    $NamRec = $First_name;
    $Phone_num = $Phone_number;


    
    echo "<script>alert('อัปเดตข้อมูลสำเร็จ'); window.location.href='edit_address.php';</script>";
} else {
    echo "<script>alert('เกิดข้อผิดพลาด'); window.location.href='edit_address.php';</script>";
}
?> 