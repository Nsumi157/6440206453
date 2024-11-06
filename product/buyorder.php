<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'dbonline_bag');
$conn->set_charset('utf8mb4');

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("การเชื่อมต่อล้มเหลว: " . $conn->connect_error);
}


// ฟังก์ชันสำหรับการสร้างรหัสใบเสร็จ
function generateOrderId($conn) {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $letterPart = '';

    // สร้างตัวอักษร 2 ตัว
    for ($i = 0; $i < 2; $i++) {
        $letterPart .= $characters[mt_rand(0, strlen($characters) - 1)];
    }

    // ดึงวันที่ปัจจุบัน
    $datePart = date('Ymd');  // รูปแบบ: YYYYMMDD

    // รวมตัวอักษรกับวันที่
    $Orderid = $letterPart . $datePart;

    // ตรวจสอบว่ารหัสใบเสร็จไม่ซ้ำกันในฐานข้อมูล
    $stmt = $conn->prepare("SELECT COUNT(*) FROM orders WHERE Order_id = ?");
    $stmt->bind_param("s", $Orderid);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        return generateOrderId($conn);
    }

    return $Orderid;
}


// ฟังก์ชันสำหรับการสร้างรหัสใบเสร็จ
function generateReceiptId($conn) {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $length = 10;
    $receipt_id = '';

    // สร้างรหัส 10 ตัว
    for ($i = 0; $i < $length; $i++) {
        $receipt_id .= $characters[mt_rand(0, strlen($characters) - 1)];
    }

    // ตรวจสอบว่ารหัสใบเสร็จไม่ซ้ำกันในฐานข้อมูล
    $stmt = $conn->prepare("SELECT COUNT(*) FROM receipt WHERE Receipt_id = ?");
    $stmt->bind_param("s", $receipt_id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        return generateReceiptId($conn);
    }

    return $receipt_id;
}



// ตรวจสอบว่ามีการส่งข้อมูลมาหรือไม่
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_SESSION['Member_id'])) {
        die("กรุณาเข้าสู่ระบบก่อนทำการสั่งซื้อ");
    }

    $member_id = $_SESSION['Member_id'];
    $Price_Order = $_POST['Price_Order'];
    $Quantity_totol = $_POST['Quantity_totol'];
    $items = $_POST['items'];

    // ดึง Subdistrict_id จากฐานข้อมูล
    $stmt = $conn->prepare("SELECT Subdistrict_id FROM member WHERE Member_id = ?");
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $stmt->bind_result($subdistrict_id);
    $stmt->fetch();
    $stmt->close();

    if (!$subdistrict_id) {
        die("ไม่พบ Subdistrict_id สำหรับสมาชิกนี้.");
    }

    // เก็บข้อมูลที่จำเป็นใน session
    $_SESSION['order_data'] = [
        'Price_Order' => $Price_Order,
        'Quantity_totol' => $Quantity_totol,
        'items' => $items
    ];

    // เจนรหัสใบเสร็จ
    $Orderid = generateOrderId($conn);

    // เก็บรหัสใบเสร็จใน session
    $_SESSION['Order_id'] = $Orderid;


    // เจนรหัสใบเสร็จ
    $Receipt_id = generateReceiptId($conn);

    // เก็บรหัสใบเสร็จใน session หรือเก็บในฐานข้อมูลตามต้องการ
    $_SESSION['Receipt_id'] = $Receipt_id;

    // Redirect ไปยังหน้าผลลัพธ์
    header("Location: buyorder.php");
    exit();
}

// เก็บข้อมูลการสั่งซื้อในตัวแปรเพื่อใช้งานใน HTML ด้านล่าง
$order_data = $_SESSION['order_data'];
$Orderid = $_SESSION['Order_id'] ?? 'ไม่พบรหัสใบเสร็จ';
$Receipt_id = $_SESSION['Receipt_id'] ?? 'ไม่พบรหัสใบเสร็จ';
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สั่งซื้อสินค้า</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
        referrerpolicy="no-referrer" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jaro:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=K2D:wght@400;600&display=swap" rel="stylesheet">
    <link href='https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="buyorder.css">
</head>
<style>
         .bag-heading{
        font-size: 45px;
        margin-right: 70.5%;
        color: white;  
        font-family: 'Jaro', sans-serif;
    }
    .nav-back {
        position: absolute;
        left: 0;
        top: 2;
        padding: 10px;
        cursor: pointer;
    }

    .nav-back i {
        font-size: 30px;
        color: #fff;

    }
</style>

<body>

    <nav class="navbar">
        
    <div class="nav-back" onclick="location.href='../index.php'" style="cursor: pointer;">
        <i class="fa fa-home"></i>
    </div>

    <h2 class="bag-heading" onclick="location.href='../index.php'" style="cursor: pointer;">Bag Collective</h2>
        <div class="navbar-icons">
            <?php if (isset($_SESSION['First_name']) && isset($_SESSION['Member_id'])): ?>
            <div class="nav-btn">
                <p class="user-name">
                    <strong><?php echo htmlspecialchars($_SESSION['First_name']); ?></strong>
                </p>
            </div>
            <div class="nav-btn user-dropdown">
            <i class="user-circle fas fa-user-circle" onclick="toggleDropdown()"></i>
            <div class="dropdown-menu" id="dropdownMenu">
                <a href="profile.php">บัญชีผู้ใช้</a>
                <a href="history.php">ประวัติการสั่งซื้อ</a>
                <a href="#" onclick="confirmLogout()">ออกจากระบบ</a>
            </div>
        </div>
            <?php endif; ?>
        </div>
    </nav>
    <script>
        function confirmLogout() {
    // แสดงกล่องยืนยัน
    var confirmation = confirm("ต้องการออกจากระบบใช่หรือไม่?");
    if (confirmation) {
        // ถ้าผู้ใช้กด OK ให้เปลี่ยนเส้นทางไปยังสคริปต์ logout.php
        window.location.href = "../login/logout.php";
    }
}

    function toggleDropdown() {
        var dropdownMenu = document.getElementById('dropdownMenu');
        dropdownMenu.style.display = dropdownMenu.style.display === 'block' ? 'none' : 'block';
    }

    // Close the dropdown if the user clicks outside of it
    window.onclick = function(event) {
        if (!event.target.matches('.fa-user-circle')) {
            var dropdowns = document.getElementsByClassName('dropdown-menu');
            for (var i = 0; i < dropdowns.length; i++) {
                var openDropdown = dropdowns[i];
                if (openDropdown.style.display === 'block') {
                    openDropdown.style.display = 'none';
                }
            }
        }
    }





</script>

    <br><br><br><br>

    <div class="container">
        <header>
            <h1 class="buyorder">สั่งซื้อสินค้า</h1>
        </header>
        <h3 class="addrese" style="margin-left: 60px; font-size: 24px; color: #333;">ที่อยู่จัดส่ง</h3>
        <div class="address-box"
            style="border: 1px solid #ddd; padding: 20px; margin-left: 60px; background-color: #f9f9f9; border-radius: 8px; width: 80%;">
            <?php
if (isset($_SESSION['Member_id'])) {
    $member_id = $_SESSION['Member_id'];
    $sql = "SELECT mb.Subdistrict_id, Title_name, First_name, Last_name, H_number, Road, Alley, Subdistrict_name, Postcode, District_name, province_name, Phone_number 
            FROM member mb
            JOIN subdistrict st ON mb.Subdistrict_id = st.Subdistrict_id
            JOIN district dt ON st.district_id = dt.district_id
            JOIN province pv ON dt.Province_id = pv.Province_id
            JOIN telephone tp ON mb.Member_id = tp.Member_id
            WHERE mb.Member_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $member_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // เก็บค่า Subdistrict_id ในตัวแปร
        $subdistrict_id = $row['Subdistrict_id'];
        $first_name = $row['First_name'];
        $phone_number = $row['Phone_number'];



        
        // แสดงข้อมูลสมาชิก
        echo '<p style="font-size: 18px; margin-bottom: 8px;">' . 
                htmlspecialchars($row['Title_name'], ENT_QUOTES, 'UTF-8') . ' ' . 
                htmlspecialchars($row['First_name'], ENT_QUOTES, 'UTF-8') . ' ' . 
                htmlspecialchars($row['Last_name'], ENT_QUOTES, 'UTF-8') . '</p>';
        echo '<p style="font-size: 16px; margin-bottom: 8px;">' . 
                htmlspecialchars($row['H_number'], ENT_QUOTES, 'UTF-8') . ' ' . 
                htmlspecialchars($row['Road'], ENT_QUOTES, 'UTF-8') . ' ' . 
                htmlspecialchars($row['Alley'], ENT_QUOTES, 'UTF-8') . ' ' .
                htmlspecialchars($row['Subdistrict_name'], ENT_QUOTES, 'UTF-8') . ', ' . 
                 htmlspecialchars($row['District_name'], ENT_QUOTES, 'UTF-8') . ', ' . 
                htmlspecialchars($row['province_name'], ENT_QUOTES, 'UTF-8') . ', ' . 
                htmlspecialchars($row['Postcode'], ENT_QUOTES, 'UTF-8') . '</p>';
        echo '<p style="font-size: 16px; margin-bottom: 8px;">Phone: ' . 
                htmlspecialchars($row['Phone_number'], ENT_QUOTES, 'UTF-8') . '</p>';
        // echo '<div class="btn-container" style="text-align: right;">' . 
        //         '<a href="edit_address.php?Member_id=' . urlencode($member_id) . '" class="btn-edit" style="background-color: #ccc; color: red; padding: 10px 20px; text-decoration: none; border-radius: 5px;">แก้ไข</a>' . 
        //     '</div>';
    } else {
        echo "<p style='color: red;'>ไม่พบที่อยู่สำหรับสมาชิกที่ล็อกอิน</p>";
    }
    $stmt->close();
} else {
    echo "<p style='color: red;'>ไม่พบ ID สมาชิกใน session</p>";
}
?>

        </div>

        <div>
            <br><br><br>
            <main>
            <form action="insertorder.php" method="POST">
            <script>
                        const orderId = <?php echo json_encode($Orderid); ?>;
                        console.log("เลขที่สั่งซื้อ: " + orderId);

                        const receiptId = <?php echo json_encode($Receipt_id); ?>;
                        console.log("เลขที่ใบเสร็จ: " + receiptId);

                        const subdistrictId = <?php echo json_encode($subdistrict_id); ?>;
                        console.log("Subdistrict ID: " + subdistrictId);

                        const firstName = <?php echo json_encode($first_name); ?>;
                        console.log("ชื่อสมาชิก: " + firstName);

                        const phoneNumber = <?php echo json_encode($phone_number); ?>;
                        console.log("เบอร์โทร: " + phoneNumber);

                        const items = <?php echo json_encode($order_data['items']); ?>;
                        console.log("รายการสินค้า: ", items);
                        </script>

    <div class="receipt" style="text-align: center; margin-right: 40px;">
        <input type="hidden" name="Order_id" value="<?php echo htmlspecialchars($Orderid, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="Receipt_id" value="<?php echo htmlspecialchars($Receipt_id, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="Subdistrict_id" value="<?php echo htmlspecialchars($subdistrict_id, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="First_name" value="<?php echo htmlspecialchars($first_name, ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="Phone_number" value="<?php echo htmlspecialchars($phone_number, ENT_QUOTES, 'UTF-8'); ?>">

        <input type="hidden" name="H_number" value="<?php echo htmlspecialchars($row['H_number'], ENT_QUOTES, 'UTF-8'); ?>">
    <input type="hidden" name="Road" value="<?php echo htmlspecialchars($row['Road'], ENT_QUOTES, 'UTF-8'); ?>">
    <input type="hidden" name="Alley" value="<?php echo htmlspecialchars($row['Alley'], ENT_QUOTES, 'UTF-8'); ?>">

    <input type="hidden" name="Last_name" value="<?php echo htmlspecialchars($row['Last_name'], ENT_QUOTES, 'UTF-8'); ?>">
    <input type="hidden" name="Subdistrict_name" value="<?php echo htmlspecialchars($row['Subdistrict_name'], ENT_QUOTES, 'UTF-8'); ?>">
    <input type="hidden" name="District_name" value="<?php echo htmlspecialchars($row['District_name'], ENT_QUOTES, 'UTF-8'); ?>">

    
    <input type="hidden" name="province_name" value="<?php echo htmlspecialchars($row['province_name'], ENT_QUOTES, 'UTF-8'); ?>">
    <input type="hidden" name="Postcode" value="<?php echo htmlspecialchars($row['Postcode'], ENT_QUOTES, 'UTF-8'); ?>">
    <input type="hidden" name="Title_name" value="<?php echo htmlspecialchars($row['Title_name'], ENT_QUOTES, 'UTF-8'); ?>">
   
    </div>

    <?php foreach ($order_data['items'] as $index => $item): ?>
    <div class="bag-container">
        <div class="bag-image">
            <?php
                $images = explode(',', $item['first_image']);
                $first_image = $images[0];
            ?>
            <img src="../<?php echo htmlspecialchars($first_image); ?>" class="small-image">
            <input type="hidden" name="items[<?php echo $index; ?>][first_image]" value="<?php echo htmlspecialchars($first_image, ENT_QUOTES, 'UTF-8'); ?>">
        </div>
        <div class="bag-details">
            <p><?php echo htmlspecialchars($item['type_name'], ENT_QUOTES, 'UTF-8') . ' / ' . htmlspecialchars($item['brand_name'], ENT_QUOTES, 'UTF-8') . ' / ' . htmlspecialchars($item['material_name'], ENT_QUOTES, 'UTF-8'); ?></p>
            <input type="hidden" name="items[<?php echo $index; ?>][type_name]" value="<?php echo htmlspecialchars($item['type_name'], ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="items[<?php echo $index; ?>][brand_name]" value="<?php echo htmlspecialchars($item['brand_name'], ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="items[<?php echo $index; ?>][material_name]" value="<?php echo htmlspecialchars($item['material_name'], ENT_QUOTES, 'UTF-8'); ?>">
            <p>สี: <?php echo htmlspecialchars($item['Colors_name'], ENT_QUOTES, 'UTF-8'); ?></p>
            <input type="hidden" name="items[<?php echo $index; ?>][Colors_name]" value="<?php echo htmlspecialchars($item['Colors_name'], ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="items[<?php echo $index; ?>][bag_id]" value="<?php echo htmlspecialchars($item['bag_id'], ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="items[<?php echo $index; ?>][Colors_code]" value="<?php echo htmlspecialchars($item['Colors_code'], ENT_QUOTES, 'UTF-8'); ?>">
            <p>ราคา: <?php echo htmlspecialchars($item['price'], ENT_QUOTES, 'UTF-8'); ?></p>
            <input type="hidden" name="items[<?php echo $index; ?>][price]" value="<?php echo htmlspecialchars($item['price'], ENT_QUOTES, 'UTF-8'); ?>">
            <div class="totalprice">
                <p>จำนวน: <?php echo htmlspecialchars($item['Quantity'], ENT_QUOTES, 'UTF-8'); ?></p>
                <input type="hidden" name="items[<?php echo $index; ?>][Quantity]" value="<?php echo htmlspecialchars($item['Quantity'], ENT_QUOTES, 'UTF-8'); ?>">
                <p>รวม: <?php echo htmlspecialchars($item['Price_Order'], ENT_QUOTES, 'UTF-8'); ?></p>
                <input type="hidden" name="items[<?php echo $index; ?>][Price_Order]" value="<?php echo htmlspecialchars($item['Price_Order'], ENT_QUOTES, 'UTF-8'); ?>">
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <div class="total-order">
        <h2>รวมทั้งหมด</h2>
        <p>จำนวนสินค้า: <?php echo htmlspecialchars($order_data['Quantity_totol'], ENT_QUOTES, 'UTF-8'); ?> ใบ</p>
        <input type="hidden" name="Quantity_totol" value="<?php echo htmlspecialchars($order_data['Quantity_totol'], ENT_QUOTES, 'UTF-8'); ?>">
        <p>ราคารวมสินค้า: <?php echo htmlspecialchars($order_data['Price_Order'], ENT_QUOTES, 'UTF-8'); ?> บาท</p>
        <input type="hidden" name="Price_Order" value="<?php echo htmlspecialchars($order_data['Price_Order'], ENT_QUOTES, 'UTF-8'); ?>">
    </div>

    <br>
    <button type="submit" class="continue-shopping">สั่งซื้อสินค้า</button>
</form>

            </main>
            <br>
        </div>
    </div>

    <style>
 .continue-shopping {
    display: block;
    margin-top: 15px;
    text-align: center;
    text-decoration: none;
    padding: 10px 20px; /* เพิ่ม padding ให้ใหญ่ขึ้น */
    background-color: #28a745;
    color: #fff;
    border-radius: 5px;
    font-size: 16px; /* เพิ่มขนาดตัวอักษร */
    width: 150px; /* เพิ่มความกว้างของปุ่ม */
    margin-left: 78%;
}

    </style>
</body>

</html>