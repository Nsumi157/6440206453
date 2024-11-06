<?php
session_start();
include '../connetDB/con_db.php';


// ฟังก์ชันสร้างรหัสกระเป๋าใหม่
function generateBagId($conn) {
    $sql = "SELECT bag_id FROM bag ORDER BY bag_id DESC LIMIT 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $lastId = $row['bag_id'];
        $num = intval(substr($lastId, 3)) + 1;
        $newId = 'BAG' . str_pad($num, 2, '0', STR_PAD_LEFT);
    } else {
        $newId = 'BAG01';
    }
    return $newId;
}

$bagId = generateBagId($conn);

// รับข้อมูลจากฟอร์ม
$type_id = $_POST['type_id'];
$brand_id = $_POST['brand'];
$material_id = $_POST['material'];
$cost_price = $_POST['cost-price'];
$sell_price = $_POST['Price'];

// Insert ข้อมูลสินค้าไปยังตาราง `bag`
$sql_bag = "INSERT INTO bag (Bag_id, Cost_price, Price, type_id, brand_id, Material_id) 
            VALUES ('$bagId', '$cost_price', '$sell_price', '$type_id', '$brand_id', '$material_id')";

if ($conn->query($sql_bag) === TRUE) {
    echo "เพิ่มข้อมูลสินค้าสำเร็จ";
} else {
    echo "Error: " . $sql_bag . "<br>" . $conn->error;
}

// ส่วนของการเพิ่มรูปภาพไปยังตาราง `pictures`
if (isset($_FILES['images'])) {
    $total_images = count($_FILES['images']['name']);
    
    for ($i = 0; $i < $total_images; $i++) {
        $image_name = $_FILES['images']['name'][$i];
        $image_tmp_name = $_FILES['images']['tmp_name'][$i];

        // กำหนดตำแหน่งที่ต้องการเก็บไฟล์
        $upload_dir = 'uploads/';
        $image_path = $upload_dir . basename($image_name);
        
        // ย้ายไฟล์จากตำแหน่งชั่วคราวไปยังโฟลเดอร์ uploads
        if (move_uploaded_file($image_tmp_name, $image_path)) {
            // Insert ภาพไปยังตาราง `pictures`
            $sql_picture = "INSERT INTO pictures (B_img, Bag_id) VALUES ('$image_path', '$bagId')";
            $conn->query($sql_picture);
        } else {
            echo "Error uploading file: " . $image_name;
        }
    }
}

// ส่วนของการเพิ่มจำนวนสีไปยังตาราง `bag_color`
if (isset($_POST['Colors_code'])) {
    $color_code = $_POST['Colors_code'];
    $total_color = $_POST['color-total-' . $color_code]; // รับจำนวนสีจากฟอร์ม

    $sql_color = "INSERT INTO bag_color (Total, Colors_code, Bag_id) 
                  VALUES ('$total_color', '$color_code', '$bagId')";
    
    if ($conn->query($sql_color) === TRUE) {
        echo "เพิ่มข้อมูลสีสำเร็จ";
    } else {
        echo "Error: " . $sql_color . "<br>" . $conn->error;
    }
}

$conn->close();
?>
