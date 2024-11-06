<?php
session_start();
include '../connetDB/con_db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $order_id = $_POST['order_id'];

    // สมมติว่า admin_id ถูกเก็บไว้ใน session หลังจากที่ผู้ดูแลระบบล็อกอิน
    if (isset($_SESSION['admin_id'])) {
        $admin_id = $_SESSION['admin_id'];

        // ค้นหา Receipt_id จาก order_id
        $sql = "SELECT Receipt_id FROM orders WHERE Order_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $order_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $receipt_id = $row['Receipt_id'];

            // อัปเดต admin_id ในตาราง receipt
            $updateSql = "UPDATE receipt SET admin_id = ? WHERE Receipt_id = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param('ii', $admin_id, $receipt_id);

            if ($updateStmt->execute()) {
                echo "success";
            } else {
                echo "error";
            }
        }
    }
}
?>
