<?php
include '../connetDB/con_db.php';
session_start(); // เริ่มต้นเซสชัน

date_default_timezone_set('Asia/Bangkok');

// ตรวจสอบว่ามีการล็อกอินของแอดมินหรือไม่
if (!isset($_SESSION['Admin_id'])) {
    echo "<p>กรุณาล็อกอินเข้าสู่ระบบก่อน</p>";
    exit();
}

$admin_id = $_SESSION['Admin_id']; // ดึง Admin_id จากเซสชัน

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['Order_id'], $_POST['Status_id'])) {
        $order_id = $_POST['Order_id'];
        $status_id = $_POST['Status_id'];
        $tracking = isset($_POST['Tracking']) ? $_POST['Tracking'] : null;
        $sent_date = date('Y-m-d');
        
        // เตรียมคำสั่ง SQL สำหรับการอัพเดตสถานะคำสั่งซื้อ
        $updateOrderSql = "UPDATE orders SET Status_id = ?, Admin_id = ? WHERE Order_id = ?";
        
        // เตรียมคำสั่ง SQL สำหรับการอัพเดตเลขพัสดุและวันที่จัดส่ง
        $updateReceiptSql = "UPDATE receipt SET Tracking = ?, Sent_date = ? WHERE Receipt_id IN (SELECT Receipt_id FROM orders WHERE Order_id = ?)";
        
        // คำสั่ง SQL สำหรับการอัพเดต W_cancel เป็น 'admin'
        $updateCancelSql = "UPDATE orders SET W_cancel = 'admin', Admin_id = ? WHERE Order_id = ?";
        
        // คำสั่ง SQL สำหรับการดึงข้อมูลจากคำสั่งซื้อ
        $selectOrderItemsSql = "SELECT Bag_id, Quantity FROM orders WHERE Order_id = ?";
        
        // คำสั่ง SQL สำหรับการอัพเดตสต็อก
        $updateStockSql = "UPDATE bag_color SET Total = Total + ? WHERE Bag_id = ?";

        $conn->begin_transaction();
        try {
            // อัพเดตสถานะคำสั่งซื้อ
            $stmt = $conn->prepare($updateOrderSql);
            $stmt->bind_param("sss", $status_id, $admin_id, $order_id);
            $stmt->execute();
            $stmt->close();
            
            // อัพเดต W_cancel เป็น 'admin' ถ้า Status_id เป็น 4
            if ($status_id == 4) {
                // ดึงข้อมูลคำสั่งซื้อ
                $stmt = $conn->prepare($selectOrderItemsSql);
                $stmt->bind_param("s", $order_id);
                $stmt->execute();
                $result = $stmt->get_result();

                while ($row = $result->fetch_assoc()) {
                    $bag_id = $row['Bag_id'];
                    $quantity = $row['Quantity'];

                    // เพิ่มจำนวนสต็อกที่ถูกคืน
                    $stmt_update_stock = $conn->prepare($updateStockSql);
                    $stmt_update_stock->bind_param("is", $quantity, $bag_id);
                    $stmt_update_stock->execute();
                    $stmt_update_stock->close();
                }
                $stmt->close();

                // อัพเดต W_cancel
                $stmt = $conn->prepare($updateCancelSql);
                $stmt->bind_param("ss", $admin_id, $order_id);
                $stmt->execute();
                $stmt->close();
            }

            // อัพเดตเลขพัสดุและวันที่จัดส่งถ้ามีการป้อนข้อมูล
            if (!empty($tracking)) {
                $stmt = $conn->prepare($updateReceiptSql);
                $stmt->bind_param("sss", $tracking, $sent_date, $order_id);
                $stmt->execute();
                $stmt->close();
            }

            $conn->commit();

            echo "<script>
                    alert('อัพเดตสถานะคำสั่งซื้อเรียบร้อยแล้ว');
                    window.location.href = 'order.php';
                  </script>";
        } catch (Exception $e) {
            // ทำการ rollback ถ้ามีข้อผิดพลาด
            $conn->rollback();
            echo "<p>เกิดข้อผิดพลาดในการอัพเดตข้อมูล: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</p>";
        }

        $conn->close();
    } else {
        echo "<p>ข้อมูลที่ต้องการไม่ครบถ้วน</p>";
    }
} else {
    echo "<p>การร้องขอไม่ถูกต้อง</p>";
}
?>
