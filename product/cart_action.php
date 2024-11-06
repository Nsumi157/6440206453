<?php
session_start();

// เชื่อมต่อกับฐานข้อมูล
$conn = new mysqli('localhost', 'root', '', 'dbonline_bag');
$conn->set_charset('utf8mb4');

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("การเชื่อมต่อล้มเหลว: " . $conn->connect_error);
}


// จัดการการลบสินค้าจากตะกร้า
if (isset($_GET['remove'])) {
    $removeId = intval($_GET['remove']);
    if (isset($_SESSION['cart'][$removeId])) {
        unset($_SESSION['cart'][$removeId]);
    }
    // อัปเดตจำนวนสินค้าทั้งหมดในตะกร้า
    $_SESSION['cartItemCount'] = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'Quantity')) : 0;
    header("Location: cartpage.php");
    exit();
}

// จัดการการส่งฟอร์มเพื่อเพิ่มสินค้าในตะกร้า
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['bag_id']) && isset($_POST['Quantity']) && isset($_POST['Colors_name']) && isset($_POST['Colors_code'])) {
    $bag_id = $_POST['bag_id'];
    $Quantity = intval($_POST['Quantity']);
    $Colors_name = $_POST['Colors_name'];
    $Colors_code = $_POST['Colors_code']; // รับค่า Colors_code
    $type_name = $_POST['type_name'];
    $brand_name = $_POST['brand_name'];
    $material_name = $_POST['material_name'];
    $price = floatval($_POST['price']);
    $main_image = $_POST['main_image'];
    $B_imgs = $_POST['B_imgs'];

    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
        $_SESSION['cart'] = array();
    }

    $item_exists = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['bag_id'] == $bag_id && $item['Colors_name'] == $Colors_name) {
            $item['Quantity'] += $Quantity; // อัปเดตจำนวนหากมีสินค้าอยู่แล้ว
            $item_exists = true;
            break;
        }
    }

    

    if (!$item_exists) {
        // เพิ่มสินค้าชิ้นใหม่ลงในตะกร้า
        $_SESSION['cart'][] = array(
            'bag_id' => $bag_id,
            'Quantity' => $Quantity,
            'Colors_name' => $Colors_name,
            'Colors_code' => $Colors_code, // บันทึกค่า Colors_code
            'type_name' => $type_name,
            'brand_name' => $brand_name,
            'material_name' => $material_name,
            'price' => $price,
            'main_image' => $main_image,
            'B_imgs' => $B_imgs
        );
    }

    // อัปเดตจำนวนสินค้าทั้งหมดในตะกร้า
    $_SESSION['cartItemCount'] = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'Quantity')) : 0;

    // ตั้งค่าข้อความสำเร็จ
    $_SESSION['cart_message'] = 'เพิ่มสินค้าลงในตะกร้าแล้ว';

    // เปลี่ยนเส้นทางกลับไปยังหน้าผลิตภัณฑ์
    header('Location: product.php?id=' . $bag_id);
    exit();
}

// จัดการการเพิ่มและลดจำนวนสินค้าในตะกร้า
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && isset($_POST['bag_id']) && isset($_POST['Colors_name'])) {
    $bag_id = $_POST['bag_id'];
    $Colors_name = $_POST['Colors_name'];
    $Colors_code = $_POST['Colors_code']; // รับค่า Colors_code
    $action = $_POST['action'];

    // ดึงจำนวนสต็อกจากฐานข้อมูล
    $stmt = $conn->prepare("SELECT bc.Total FROM bag_color AS bc INNER JOIN colors AS c ON bc.Colors_code = c.Colors_code WHERE bc.bag_id = ? AND c.Colors_name = ?");
    $stmt->bind_param('ss', $bag_id, $Colors_name);
    $stmt->execute();
    $stmt->bind_result($Total);
    $stmt->fetch();
    $stmt->close();

    // ค้นหาสินค้าในตะกร้า
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['bag_id'] == $bag_id && $item['Colors_name'] == $Colors_name) {
            if ($action == 'increase') {
                if ($item['Quantity'] < $Total) {
                    $item['Quantity'] += 1; // เพิ่มจำนวนถ้าไม่เกินจำนวนสต็อก
                } else {
                    // แจ้งเตือนว่าเกินจำนวนสต็อก
                    $_SESSION['cart_message'] = 'จำนวนที่สั่งซื้อเกินจำนวนในสต็อก';
                }
            } elseif ($action == 'decrease') {
                $item['Quantity'] -= 1; // ลดจำนวน
                if ($item['Quantity'] < 1) {
                    $item['Quantity'] = 1; // ห้ามลดจำนวนต่ำกว่า 1
                }
            }
            break;
        }
    }

    // อัปเดตจำนวนสินค้าทั้งหมดในตะกร้า
    $_SESSION['cartItemCount'] = isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'Quantity')) : 0;

    // เปลี่ยนเส้นทางกลับไปยังหน้าตะกร้า
    header("Location: cartpage.php");
    exit();
}
?>
