<?php
session_start();
include '../connetDB/con_db.php';

// รับค่าตัวแปรมาจากฟอร์ม register
$Member_id = $_POST['Member_id'];
$Title_name = $_POST['Title_name'];
$First_name = $_POST['First_name'];
$Last_name = $_POST['Last_name'];
$Password = $_POST['Password'];
$H_number = $_POST['H_number'];
$Phone_numbers = $_POST['Phone_number']; // Array ของเบอร์โทรศัพท์
$Road = isset($_POST['Road']) ? $_POST['Road'] : '';
$Alley = isset($_POST['Alley']) ? $_POST['Alley'] : '';
$Subdistrict_id = $_POST['Subdistrict_id'];

// เช็ค format ของ email
if (!filter_var($Member_id, FILTER_VALIDATE_EMAIL) || !preg_match('/@gmail\.com$/', $Member_id)) {
    echo "<script> alert('อีเมลต้องเป็น @gmail.com เท่านั้น'); </script>";
    echo "<script> window.history.back(); </script>";
    exit();
}

try {
    // เริ่มต้น transaction
    $conn->begin_transaction();

    // คำสั่งเพิ่มข้อมูลลงตาราง member
    $sql_member = "INSERT INTO `member` (`Member_id`, `Title_name`, `First_name`, `Last_name`, `Password`, `H_number`, `Road`, `Alley`, `Subdistrict_id`) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_member = $conn->prepare($sql_member);
    $stmt_member->bind_param("sssssssss", $Member_id, $Title_name, $First_name, $Last_name, $Password, $H_number, $Road, $Alley, $Subdistrict_id);

    if (!$stmt_member->execute()) {
        throw new Exception("Error: " . $stmt_member->error);
    }

// คำสั่งเพิ่มข้อมูลเบอร์โทรศัพท์ลงตาราง telephone โดยใช้ลูป
$sql_telephone = "INSERT INTO `telephone` (`Phone_number`, `member_id`) VALUES (?, ?)";
$stmt_telephone = $conn->prepare($sql_telephone);
foreach ($Phone_numbers as $Phone_number) {
    if (!empty($Phone_number)) { // เช็คว่าเบอร์โทรไม่ได้เป็นค่าว่าง
        $stmt_telephone->bind_param("ss", $Phone_number, $Member_id);
        if (!$stmt_telephone->execute()) {
            throw new Exception("Error: " . $stmt_telephone->error);
        }
    }
}


    // commit transaction
    $conn->commit();

    echo "<script> alert('บันทึกข้อมูลเรียบร้อย'); window.location='../login/login.php'; </script>";

} catch (Exception $e) {
    // rollback transaction if something failed
    $conn->rollback();
    echo "<script> alert('บันทึกข้อมูลไม่สำเร็จ'); window.location='registerr.php'; </script>";
    echo "Error: " . $e->getMessage();
}

$stmt_member->close();
$stmt_telephone->close();
$conn->close();

?>
