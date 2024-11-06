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
    $Order_id = isset($_POST['Order_id']) ? $_POST['Order_id'] : '';
    $Receipt_id = isset($_POST['Receipt_id']) ? $_POST['Receipt_id'] : '';
    $Title_name = isset($_POST['Title_name']) ? $_POST['Title_name'] : '';
    $First_name = isset($_POST['First_name']) ? $_POST['First_name'] : '';
    $Last_name = isset($_POST['Last_name']) ? $_POST['Last_name'] : '';
    $H_number = isset($_POST['H_number']) ? $_POST['H_number'] : '';
    $Road = isset($_POST['Road']) ? $_POST['Road'] : '';
    $Alley = isset($_POST['Alley']) ? $_POST['Alley'] : '';
    $Subdistrict_name = isset($_POST['Subdistrict_name']) ? $_POST['Subdistrict_name'] : '';
    $District_name = isset($_POST['District_name']) ? $_POST['District_name'] : '';
    $province_name = isset($_POST['province_name']) ? $_POST['province_name'] : '';
    $Postcode = isset($_POST['Postcode']) ? $_POST['Postcode'] : '';
    $price = isset($_POST['price']) ? $_POST['price'] : 0;
    $Quantity = isset($_POST['Quantity']) ? $_POST['Quantity'] : 0;

    $Price_Order = isset($_POST['Price_Order']) ? $_POST['Price_Order'] : 0;
    $Quantity_totol = isset($_POST['Quantity_totol']) ? $_POST['Quantity_totol'] : 0;
    $subdistrict_id = isset($_POST['Subdistrict_id']) ? $_POST['Subdistrict_id'] : '';
    $Phone_number = isset($_POST['Phone_number']) ? $_POST['Phone_number'] : '';

    // กำหนดค่าให้ตัวแปร $NamRec และ $Phone_num
    $NamRec = $First_name;
    $Phone_num = $Phone_number;

    // ตรวจสอบว่าค่า NamRec และ Phone_num ไม่เป็น NULL
    if (empty($NamRec) || empty($Phone_num)) {
        die("กรุณากรอกข้อมูลผู้รับและเบอร์โทรศัพท์ให้ครบถ้วน");
    }
   
    // ตรวจสอบว่าค่า Subdistrict_id ไม่เป็น NULL
    if (empty($subdistrict_id)) {
        die("ไม่พบ Subdistrict_id สำหรับสมาชิกนี้.");
    }

    $items = isset($_POST['items']) ? $_POST['items'] : [];

foreach ($items as $item) {
    $Quantity = isset($item['Quantity']) ? $item['Quantity'] : 0;
    if (empty($Quantity)) {
        die("ไม่พบ Quantity สำหรับสินค้า");
    }
    // ตรวจสอบค่าว่าถูกต้องหรือไม่
    echo "Quantity: " . htmlspecialchars($Quantity, ENT_QUOTES, 'UTF-8');
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
    $Receipt_date = date('Y-m-d');
    // INSERT ข้อมูลลงในตาราง receipt
    $stmt = $conn->prepare("INSERT INTO `receipt` (`Receipt_id`, `Receipt_date`, `Sent_date`, `Receipt_img`, `Date_img`, `Time_img`, `Tracking`, `NamRec`, `Phone_num`, `H_number`, `Road`, `Alley`, `Subdistrict_id`, `Admin_id`) 
                            VALUES (?, ?, NULL, NULL, NULL, NULL, NULL, ?, ?,?,?,?, ?, NULL)");
    $stmt->bind_param("ssssssss", $Receipt_id, $Receipt_date,$NamRec, $Phone_num,$H_number,$Road, $Alley, $subdistrict_id);

    if ($stmt->execute()) {
        echo "บันทึกข้อมูลใบเสร็จสำเร็จ";
    } else {
        echo "เกิดข้อผิดพลาดในการบันทึกข้อมูลใบเสร็จ: " . $stmt->error;
    }
    $stmt->close();

    

// ตรวจสอบว่า items มีข้อมูลหรือไม่
// ตรวจสอบว่า items มีข้อมูลหรือไม่
// if (isset($_POST['items']) && is_array($_POST['items'])) {
//     $items = $_POST['items'];

//     foreach ($items as $item) {
//         $bag_id = isset($item['bag_id']) ? trim($item['bag_id']) : null;
//         $Colors_code = isset($item['Colors_code']) ? trim($item['Colors_code']) : null;
//         $Quantity = isset($item['Quantity']) ? $item['Quantity'] : 0;

//         // แปลง Price_Order เป็นตัวเลขและตัดจุดทศนิยมออก
//         $Price_Order = isset($item['Price_Order']) ? floatval(str_replace(',', '', $item['Price_Order'])) : 0;
//         $Price_Order = floor($Price_Order);

//         if ($bag_id && $Colors_code) {
//             // ตรวจสอบว่า Bag_id และ Colors_code มีอยู่ในตาราง bag_color หรือไม่
//             $stmt = $conn->prepare("SELECT COUNT(*) FROM bag_color WHERE Bag_id = ? AND Colors_code = ?");
//             if ($stmt === false) {
//                 die("Prepare failed: " . $conn->error);
//             }
//             $stmt->bind_param("ss", $bag_id, $Colors_code);
//             $stmt->execute();
//             $stmt->bind_result($count);
//             $stmt->fetch();
//             $stmt->close();

//             if ($count == 0) {
//                 die("Bag_id และ Colors_code ที่ระบุไม่พบในฐานข้อมูล");
//             }

//             // กำหนดวันที่และเวลา
//             $Order_date = date('Y-m-d');
//             $Order_time = date('H:i:s');

//             // ต้องกำหนดค่า $Order_id, $Receipt_id, $member_id ให้ถูกต้องก่อนการ INSERT
//             $Order_id = uniqid('ORD'); // ตัวอย่างการกำหนดค่า
//             $Receipt_id = uniqid('REC'); // ตัวอย่างการกำหนดค่า
//             $member_id = 'MEM001'; // ตัวอย่างการกำหนดค่า

//             // INSERT ข้อมูลลงในตาราง orders
//             $stmt = $conn->prepare("INSERT INTO `orders` (`Order_id`, `Order_date`, `Order_time`, `Quantity`, `Price_Order`, `Review`, `Point`, `W_cancel`, `Status_id`, `Bag_id`, `Admin_id`, `Receipt_id`, `Member_id`, `Colors_code`) 
//                                     VALUES (?, ?, ?, ?, ?, NULL, NULL, NULL, 1, ?, NULL, ?, ?, ?)");
//             if ($stmt === false) {
//                 die("Prepare failed: " . $conn->error);
//             }
//             $stmt->bind_param("sssisssss", $Order_id, $Order_date, $Order_time, $Quantity, $Price_Order, $bag_id, $Receipt_id, $member_id, $Colors_code);

//             if ($stmt->execute()) {
//                 echo "บันทึกข้อมูลการสั่งซื้อสำเร็จ";
//             } else {
//                 echo "เกิดข้อผิดพลาดในการบันทึกข้อมูลการสั่งซื้อ: " . $stmt->error;
//             }
//             $stmt->close();

//             // UPDATE จำนวนสินค้าในตาราง bag_color
//             $stmt = $conn->prepare("UPDATE `bag_color` SET `Total`= `Total` - ? WHERE `Colors_code`= ? AND `Bag_id`= ?");
//             if ($stmt === false) {
//                 die("Prepare failed: " . $conn->error);
//             }
//             $stmt->bind_param("iss", $Quantity, $Colors_code, $bag_id);

//             if ($stmt->execute()) {
//                 echo "อัพเดทจำนวนสินค้าในตาราง bag_color สำเร็จ";
//             } else {
//                 echo "เกิดข้อผิดพลาดในการอัพเดทจำนวนสินค้า: " . $stmt->error;
//             }
//             $stmt->close();
//         } else {
//             echo "ข้อมูล bag_id หรือ Colors_code ไม่ครบถ้วน";
//         }

//         echo "Bag ID: " . $bag_id . ", Colors Code: " . $Colors_code;
//     }
// } else {
//     die("ไม่มีข้อมูลสินค้าในคำสั่งซื้อ");
// }

if (isset($_POST['items']) && is_array($_POST['items'])) {
    $items = $_POST['items'];

    foreach ($items as $item) {
        $bag_id = isset($item['bag_id']) ? trim($item['bag_id']) : null;
        $Colors_code = isset($item['Colors_code']) ? trim($item['Colors_code']) : null;
        $Quantity = isset($item['Quantity']) ? $item['Quantity'] : 0;

        // แปลง Price_Order เป็นตัวเลขและตัดจุดทศนิยมออก
        $Price_Order = isset($item['Price_Order']) ? floatval(str_replace(',', '', $item['Price_Order'])) : 0;
        $Price_Order = floor($Price_Order);

        if ($bag_id && $Colors_code) {
            // ตรวจสอบว่า Bag_id และ Colors_code มีอยู่ในตาราง bag_color หรือไม่
            $stmt = $conn->prepare("SELECT COUNT(*) FROM bag_color WHERE Bag_id = ? AND Colors_code = ?");
            $stmt->bind_param("ss", $bag_id, $Colors_code);
            $stmt->execute();
            $stmt->bind_result($count);
            $stmt->fetch();
            $stmt->close();

            if ($count == 0) {
                die("Bag_id และ Colors_code ที่ระบุไม่พบในฐานข้อมูล");
            }

            // กำหนดวันที่และเวลา
            $Order_date = date('Y-m-d');
            $Order_time = date('H:i:s');

            // INSERT ข้อมูลลงในตาราง orders
            $stmt = $conn->prepare("INSERT INTO `orders` (`Order_id`, `Order_date`, `Order_time`, `Quantity`, `Price_Order`, `Review`, `Point`, `W_cancel`, `Status_id`, `Bag_id`, `Admin_id`, `Receipt_id`, `Member_id`, `Colors_code`) 
                                  VALUES (?, ?, ?, ?, ?, NULL, NULL, NULL, 1, ?, NULL, ?, ?, ?)");
            if ($stmt === false) {
                die("Prepare failed: " . $conn->error);
            }
            $stmt->bind_param("sssisssss", $Order_id, $Order_date, $Order_time, $Quantity, $Price_Order, $bag_id, $Receipt_id, $member_id, $Colors_code);

            if ($stmt->execute()) {
                echo "บันทึกข้อมูลการสั่งซื้อสำเร็จ";
            } else {
                echo "เกิดข้อผิดพลาดในการบันทึกข้อมูลการสั่งซื้อ: " . $stmt->error;
            }
            $stmt->close();

            // UPDATE จำนวนสินค้าในตาราง bag_color
            $stmt = $conn->prepare("UPDATE `bag_color` SET `Total`= `Total` - ? WHERE `Colors_code`= ? AND `Bag_id`= ?");
            if ($stmt === false) {
                die("Prepare failed: " . $conn->error);
            }
            $stmt->bind_param("iss", $Quantity, $Colors_code, $bag_id);

            if ($stmt->execute()) {
                echo "อัพเดทจำนวนสินค้าในตาราง bag_color สำเร็จ";
            } else {
                echo "เกิดข้อผิดพลาดในการอัพเดทจำนวนสินค้า: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "ข้อมูล bag_id หรือ Colors_code ไม่ครบถ้วน";
        }

        echo "Bag ID: " . $bag_id . ", Colors Code: " . $Colors_code;
    }
} else {
    die("ไม่มีข้อมูลสินค้าในคำสั่งซื้อ");
}
//             $stmt = $conn->prepare("INSERT INTO `orders` (`Order_id`, `Order_date`, `Order_time`, `Quantity`, `Price_Order`, `Review`, `Point`, `W_cancel`, `Status_id`, `Bag_id`, `Admin_id`, `Receipt_id`, `Member_id`, `Colors_code`) 
//                                     VALUES (?, ?, ?, ?, ?, NULL, NULL, NULL, 1, ?, NULL, ?, ?, ?)");
//             $stmt->bind_param("sssisssss", $Order_id, $Order_date, $Order_time, $Quantity, $Price_Order, $bag_id, $Receipt_id, $member_id, $Colors_code);

//             $stmt = $conn->prepare("UPDATE `bag_color` SET `Total`= `Total` - ? WHERE `Colors_code`= ? AND `Bag_id`= ?");
//             $stmt->bind_param("iss", $Quantity, $Colors_code, $bag_id);

//             if ($stmt->execute()) {
//                 echo "บันทึกข้อมูลการสั่งซื้อสำเร็จ";
//             } else {
//                 echo "เกิดข้อผิดพลาดในการบันทึกข้อมูลการสั่งซื้อ: " . $stmt->error;
//             }
//             $stmt->close();
//         } else {
//             echo "ข้อมูล bag_id หรือ Colors_code ไม่ครบถ้วน";
//         }

//         echo "Bag ID: " . $bag_id . ", Colors Code: " . $Colors_code;
//     }
// } else {
//     die("ไม่มีข้อมูลสินค้าในคำสั่งซื้อ");
// }

$_SESSION['order_data'] = [
    'member_id' => $member_id,
    'Order_id' => $Order_id,
    'Receipt_id' => $Receipt_id,
    'Title_name' => $Title_name,
    'First_name' => $First_name,
    'Last_name' => $Last_name,
    'H_number' => $H_number,
    'Road' => $Road,
    'Alley' => $Alley,
    'Subdistrict_name' => $Subdistrict_name,
    'District_name' => $District_name,
    'province_name' => $province_name,
    'Postcode' => $Postcode,
    'price' => $price,
    'Quantity' => $Quantity,
    'Price_Order' => $Price_Order,
    'Quantity_totol' => $Quantity_totol,
    'subdistrict_id' => $subdistrict_id,
    'Phone_number' => $Phone_number,
    'Status_id' => 1,
    
];

unset($_SESSION['cart']);
$_SESSION['cartItemCount'] = 0;
    // หลังจากบันทึกข้อมูลเสร็จแล้ว ทำการ Redirect ไปยังหน้าที่ต้องการ
    header("Location: Qrcode.php");
    exit();
} else {
    die("วิธีการส่งข้อมูลไม่ถูกต้อง");
}


?>
