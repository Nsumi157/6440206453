<?php
session_start();

// ตรวจสอบการเชื่อมต่อฐานข้อมูล
$conn = new mysqli('localhost', 'root', '', 'dbonline_bag');
$conn->set_charset('utf8mb4');

if ($conn->connect_error) {
    die("การเชื่อมต่อล้มเหลว: " . $conn->connect_error);
}

// ตรวจสอบการส่งข้อมูลรีวิว
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // รับค่าจากฟอร์ม
    $order_id = htmlspecialchars($_POST['Order_id']);
    $rating = htmlspecialchars($_POST['rating']);
    $review = htmlspecialchars($_POST['review']);

    // ตรวจสอบสถานะของคำสั่งซื้อ (ต้องเป็น Status_id = 5)
    $sql_check_status = "SELECT Status_id FROM orders WHERE Order_id = ?";
    $stmt = $conn->prepare($sql_check_status);
    $stmt->bind_param("s", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // ตรวจสอบว่าคำสั่งซื้อนี้มีสถานะเป็น 5 หรือไม่
        if ($row['Status_id'] == 5) {
            // ทำการอัปเดตคะแนนและรีวิว
            $sql_update_review = "UPDATE orders SET Review = ?, Point = ? WHERE Order_id = ?";
            $stmt_update = $conn->prepare($sql_update_review);
            $stmt_update->bind_param("sis", $review, $rating, $order_id);

            if ($stmt_update->execute()) {
                echo "<script>alert('รีวิวสินค้าสำเร็จ'); window.location.href='history.php';</script>";
            } else {
                echo "<script>alert('เกิดข้อผิดพลาดในการบันทึกรีวิว'); window.location.href='history.php';</script>";
            }
        } else {
            // ถ้าสถานะไม่ใช่ 5 ไม่ให้บันทึกรีวิว
            echo "<script>alert('ไม่สามารถให้คะแนนได้ เนื่องจากสถานะของคำสั่งซื้อไม่ถูกต้อง'); window.location.href='history.php';</script>";
        }
    } else {
        echo "<script>alert('ไม่พบคำสั่งซื้อที่คุณเลือก'); window.location.href='history.php';</script>";
    }

    // ปิดการเชื่อมต่อฐานข้อมูล
    $stmt->close();
    $conn->close();
}
?>
