<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'dbonline_bag');
$conn->set_charset('utf8mb4');

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("การเชื่อมต่อล้มเหลว: " . $conn->connect_error);
}
date_default_timezone_set('Asia/Bangkok');
// ตรวจสอบว่ามีการส่งข้อมูลมาหรือไม่
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_SESSION['Member_id'])) {
        die("กรุณาเข้าสู่ระบบก่อนทำการสั่งซื้อ");
    }

    // ข้อมูลที่ได้รับจากฟอร์ม
    $member_id = $_SESSION['Member_id'];
    $Title_name = $_POST['Title_name'];
    $First_name = $_POST['First_name'];
    $Last_name = $_POST['Last_name'];
    $H_number = $_POST['H_number'];
    $Road = $_POST['Road'];
    $Alley = $_POST['Alley'];
    $Subdistrict_name = $_POST['Subdistrict_name'];
    $District_name = $_POST['District_name'];
    $province_name = $_POST['province_name'];
    $Postcode = $_POST['Postcode'];
    $type_name = $_POST['type_name'];
    $brand_name = $_POST['brand_name'];

    $material_name = $_POST['material_name'];
    $Colors_name = $_POST['Colors_name'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];


    $Price_Order = $_POST['Price_Order'];
    $Quantity = $_POST['Quantity'];
    $Orderid = $_SESSION['Order_id'];
    $Receipt_id = $_SESSION['Receipt_id'];
    $subdistrict_id = $_POST['Subdistrict_id'];

    $Colors_code = isset($_POST['Colors_code']) ? $_POST['Colors_code'] : ''; // ตรวจสอบว่ามีการส่งค่าหรือไม่
    $bag_id = isset($_POST['bag_id']) ? $_POST['bag_id'] : ''; // ตรวจสอบว่ามีการส่งค่าหรือไม่

    // รับค่าจากฟอร์ม
    $first_name = $_POST['First_name'];
    $phone_number = $_POST['Phone_number'];

    // กำหนดค่าให้ตัวแปร $NamRec และ $Phone_num
    $NamRec = $first_name;
    $Phone_num = $phone_number;

    // ตรวจสอบว่าค่า NamRec และ Phone_num ไม่เป็น NULL
    if (empty($NamRec) || empty($Phone_num)) {
        die("กรุณากรอกข้อมูลผู้รับและเบอร์โทรศัพท์ให้ครบถ้วน");
    }

    // ตรวจสอบว่าค่า Subdistrict_id ไม่เป็น NULL
    if (empty($subdistrict_id)) {
        die("ไม่พบ Subdistrict_id สำหรับสมาชิกนี้.");
    }

    // ตรวจสอบว่าค่า Subdistrict_id มีอยู่ในฐานข้อมูลหรือไม่
    $stmt = $conn->prepare("SELECT COUNT(*) FROM subdistrict WHERE Subdistrict_id = ?");
    $stmt->bind_param("s", $subdistrict_id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count == 0) {
        die("Subdistrict_id ที่ระบุไม่พบในฐานข้อมูล");
    }

    // INSERT ข้อมูลลงในตาราง receipt
    $stmt = $conn->prepare("INSERT INTO `receipt` (`Receipt_id`, `Receipt_date`, `Sent_date`, `Receipt_img`, `Date_img`, `Time_img`, `Tracking`, `NamRec`, `Phone_num`, `Subdistrict_id`, `Admin_id`) 
                            VALUES (?, NULL, NULL, NULL, NULL, NULL, NULL, ?, ?, ?, NULL)");
    $stmt->bind_param("ssss", $Receipt_id, $NamRec, $Phone_num, $subdistrict_id);

    if ($stmt->execute()) {
        echo "บันทึกข้อมูลใบเสร็จสำเร็จ";
    } else {
        echo "เกิดข้อผิดพลาดในการบันทึกข้อมูลใบเสร็จ: " . $stmt->error;
    }
    $stmt->close();



     // ใช้ session เพื่อเก็บข้อมูลที่ต้องการแสดงใน Qrcode.php
$_SESSION['order_data'] = [
    'Orderid' => $Orderid,
    'Receipt_id' => $Receipt_id,

    // ข้อมูลผู้รับและที่อยู่
    'Title_name' => $Title_name,
    'Last_name' => $Last_name,
    'H_number' => $H_number,
    'Road' => $Road,
    'Alley' => $Alley,
    'District_name' => $District_name,
    'province_name' => $province_name,
    'Postcode' => $Postcode,
    'NamRec' => $NamRec,
    'Phone_num' => $Phone_num,
    'Subdistrict_id' => $subdistrict_id,
    'Subdistrict_name' => $Subdistrict_name,

    // ข้อมูลสินค้า
    'Colors_code' => $Colors_code,
    'bag_id' => $bag_id,
    'Price_Order' => $Price_Order,
    'Quantity' => $Quantity,
    'type_name' => $type_name,
    'brand_name' => $brand_name,
    'material_name' => $material_name,
    'Colors_name' => $Colors_name,
    'price' => $price,
    'quantity' => $quantity,
];

// SELECT `B_img`
//                   FROM `pictures` p
//                   JOIN `bag` b ON p.Bag_id = b.Bag_id
//                   WHERE p.Bag_id = ?";


    // ตรวจสอบว่า items มีข้อมูลหรือไม่
    // if (isset($_POST['items']) && is_array($_POST['items'])) {
    //     $items = $_POST['items'];
    //     foreach ($items as $item) {
    //         $bag_id = isset($item['bag_id']) ? $item['bag_id'] : null;
    //         $Colors_code = isset($item['Colors_code']) ? $item['Colors_code'] : null;

    //         if ($bag_id && $Colors_code) {
    //             // กำหนดวันที่และเวลา
    //             $Order_date = date('Y-m-d');
    //             $Order_time = date('H:i:s');

    //             // INSERT ข้อมูลลงในตาราง orders
    //             $stmt = $conn->prepare("INSERT INTO `orders` (`Order_id`, `Order_date`, `Order_time`, `Quantity`, `Price_Order`, `Review`, `Point`, `W_cancel`, `Status_id`, `Bag_id`, `Admin_id`, `Receipt_id`, `Member_id`, `Colors_code`) 
    //                                     VALUES (?, ?, ?, ?, ?, NULL, NULL, NULL, 1, ?, NULL, ?, ?, ?)");
    //             $stmt->bind_param("ssddsiss", $Orderid, $Order_date, $Order_time, $Quantity, $Price_Order, $bag_id, $Receipt_id, $member_id, $Colors_code);

    //             if ($stmt->execute()) {
    //                 echo "บันทึกข้อมูลการสั่งซื้อสำเร็จ";
    //             } else {
    //                 echo "เกิดข้อผิดพลาดในการบันทึกข้อมูลการสั่งซื้อ: " . $stmt->error;
    //             }
    //             $stmt->close();
    //         } else {
    //             echo "ข้อมูล bag_id หรือ Colors_code ไม่ครบถ้วน";
    //         }
    //     }
    // } else {
    //     die("ไม่มีข้อมูลสินค้าในคำสั่งซื้อ");
    // }

    // หลังจากบันทึกข้อมูลเสร็จแล้ว ทำการ Redirect ไปยังหน้าที่ต้องการ
    header("Location: Qrcode.php"); // Redirect ไปยังหน้าที่ต้องการ เช่นหน้าประสบความสำเร็จ
    exit();
} else {
    die("วิธีการส่งข้อมูลไม่ถูกต้อง");
}
?>
