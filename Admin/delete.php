<?php
session_start();
include '../connetDB/con_db.php';

if (isset($_GET['Bag_id']) && isset($_GET['Colors_code'])) {
    $bag_id = $_GET['Bag_id'];
    $colors_code = $_GET['Colors_code'];

    // คำสั่ง SQL เพื่อลบข้อมูล
    $sql = "DELETE FROM bag_color WHERE Bag_id = ? AND Colors_code = ?";
    
    // เตรียมและดำเนินการคำสั่ง SQL
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $bag_id, $colors_code);
    
    if ($stmt->execute()) {
        
        echo "<script>
                   
                    window.location='manage.php';
                  </script>";
    } else {
        echo "เกิดข้อผิดพลาดในการลบข้อมูล";
    }

    $stmt->close();
}
?>
