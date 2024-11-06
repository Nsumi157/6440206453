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
    $Phone_numbers = $_POST['Phone_numbers']; // รับค่าเบอร์โทรศัพท์เป็นอาร์เรย์
  
    $Subdistrict_id = $_POST['Subdistrict_name'];
    $Postcode = $_POST['Postcode'];

    // อัปเดตข้อมูลในตาราง member
    $sql = "UPDATE member 
            SET Title_name = ?, First_name = ?, Last_name = ?, H_number = ?, Road = ?, Alley = ?, 
             Subdistrict_id = ? 
            WHERE Member_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssss", $Title_name, $First_name, $Last_name, $H_number, $Road, $Alley, $Subdistrict_id, $Member_id);
    $stmt->execute();
    $stmt->close();

    // ลบข้อมูลโทรศัพท์เก่า
    $sqlDeletePhone = "DELETE FROM telephone WHERE Member_id = ?";
    $stmtDelete = $conn->prepare($sqlDeletePhone);
    $stmtDelete->bind_param("s", $Member_id);
    $stmtDelete->execute();
    $stmtDelete->close();

    // เพิ่มเบอร์โทรศัพท์ใหม่
    $sqlInsertPhone = "INSERT INTO telephone (Member_id, Phone_number) VALUES (?, ?)";
    $stmtInsert = $conn->prepare($sqlInsertPhone);
    foreach ($Phone_numbers as $phone) {
        $stmtInsert->bind_param("ss", $Member_id, $phone);
        $stmtInsert->execute();
    }
    $stmtInsert->close();

    echo "<script>alert('อัปเดตข้อมูลสำเร็จ'); window.location.href='profile.php';</script>";
} else {
    echo "<script>alert('เกิดข้อผิดพลาด'); window.location.href='editprofile.php';</script>";
}
?>
