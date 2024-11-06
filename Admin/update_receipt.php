 <!-- <?php
session_start();


$conn = new mysqli('localhost', 'root', '', 'dbonline_bag');
$conn->set_charset('utf8mb4');

if ($conn->connect_error) {
    die("การเชื่อมต่อล้มเหลว: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
 
    $receipt_id = $_POST['receipt_id'];
    $admin_id = $_POST['admin_id'];

   
    $conn = new mysqli($servername, $username, $password, $dbname);

    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

   
    $sql = "UPDATE receipt SET admin_id = ? WHERE receipt_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $admin_id, $receipt_id);

    if ($stmt->execute()) {
        echo "อัพเดตสำเร็จ";
    } else {
        echo "เกิดข้อผิดพลาด: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
 ?>-->
