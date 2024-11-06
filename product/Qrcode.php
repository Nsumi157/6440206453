<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'dbonline_bag');
$conn->set_charset('utf8mb4');

if ($conn->connect_error) {
    die("การเชื่อมต่อล้มเหลว: " . $conn->connect_error);
}

// ตรวจสอบว่ามีข้อมูลการสั่งซื้อในเซสชันหรือไม่
if (!isset($_SESSION['order_data'])) {
    // ถ้าไม่มีข้อมูลการสั่งซื้อให้รีไดเรกต์ไปที่หน้าประวัติการสั่งซื้อ
    header("Location: history.php");
    exit();
}

$current_time = time();  // เวลาปัจจุบันในหน่วยวินาที
$countdown_duration = 60 * 60; // ตั้งค่าเวลาถอยหลัง 60 นาที

$current_order_id = $_SESSION['order_data']['Order_id'];

// ตรวจสอบว่าเป็นคำสั่งซื้อใหม่หรือไม่
if (!isset($_SESSION['previous_order_ids'])) {
    $_SESSION['previous_order_ids'] = []; // สร้าง array เพื่อเก็บ order_id ที่ผ่านมา
}

if (!in_array($current_order_id, $_SESSION['previous_order_ids'])) {
    // ถ้าเป็นคำสั่งซื้อใหม่ ให้รีเซ็ตเวลานับถอยหลัง
    $_SESSION['target_times'][$current_order_id] = $current_time + $countdown_duration; // กำหนดเวลาเป้าหมายสำหรับ order_id ปัจจุบัน
    $_SESSION['previous_order_ids'][] = $current_order_id;  // เพิ่ม order_id นี้ลงใน array
}

// คำนวณเวลาถอยหลังที่เหลือในหน่วยวินาที
$remaining_time = isset($_SESSION['target_times'][$current_order_id]) ? $_SESSION['target_times'][$current_order_id] - $current_time : 0;

// ถ้าเวลาหมดแล้ว ตั้งให้หมดเวลาเลย
if ($remaining_time <= 0) {
    $remaining_time = 0;
}

// ตรวจสอบว่าต้องอัปเดตสถานะหรือไม่
if ($current_time > $_SESSION['target_times'][$current_order_id]) {
    // อัปเดต Status_id เป็น 'c' (ยกเลิก)
    if (isset($_SESSION['order_data'])) {
        $order_id = $_SESSION['order_data']['Order_id'];

        // เริ่มต้นการทำงานของ Transaction
        $conn->begin_transaction();

        try {
            // อัปเดตสถานะคำสั่งซื้อเป็น 'c' (ยกเลิก)
            $stmt = $conn->prepare("UPDATE orders SET Status_id = 'c', W_cancel = 'ระบบ' WHERE Order_id = ?");
            $stmt->bind_param("s", $order_id);
            $stmt->execute();
            $stmt->close();

            // คืนสต็อกสินค้า
            $stmt = $conn->prepare("SELECT Bag_id, Quantity FROM orders WHERE Order_id = ?");
            $stmt->bind_param("s", $order_id);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                $bag_id = $row['Bag_id'];
                $quantity = $row['Quantity'];

                // เพิ่มจำนวนสต็อกที่ถูกคืน
                $stmt_update_stock = $conn->prepare("UPDATE bag_color SET Total = Total + ? WHERE Bag_id = ?");
                $stmt_update_stock->bind_param("is", $quantity, $bag_id);
                $stmt_update_stock->execute();
                $stmt_update_stock->close();
            }

            // ยืนยันการทำธุรกรรม
            $conn->commit();
        } catch (Exception $e) {
            // ยกเลิกการทำธุรกรรมหากเกิดข้อผิดพลาด
            $conn->rollback();
            echo "เกิดข้อผิดพลาด: " . $e->getMessage();
        }

        // ลบ target_time สำหรับ order_id นี้ เพื่อไม่ให้ทำการอัปเดตสถานะซ้ำ
        unset($_SESSION['target_times'][$current_order_id]);

        header("Location: history.php");
        exit();
    }
}

// ส่งค่าเวลาถอยหลังไปที่ JavaScript (ในหน่วยวินาที)
$target_time = isset($_SESSION['target_times'][$current_order_id]) ? $_SESSION['target_times'][$current_order_id] : $current_time;

?>



<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ยืนยันคำสั่งซื้อ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
        referrerpolicy="no-referrer" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jaro:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=K2D:wght@400;600&display=swap" rel="stylesheet">
    <link href='https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="Qrs.css">

    <style>
    .bag-heading {
        font-size: 45px;
        margin-right: 70%;
        color: white;
        font-family: 'Jaro', sans-serif;
    }

    header {
        background-color: #343a40;
        color: #ffffff;
        padding: 0.02rem;
        text-align: center;
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
</head>

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
        window.location.href = "login/logout.php";
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
    <br>

    <div class="container">

        <header>
            <h1 class="Orders">ยืนยันคำสั่งซื้อ</h1>
        </header>

        <div class="order-details">

            <?php
            // รับ Order_id จาก URL และเก็บในเซสชัน
if (isset($_GET['Order_id'])) {
    $_SESSION['order_data']['Order_id'] = $_GET['Order_id'];
}

// ตอนนี้ $order_data จะถูกใช้เพื่อดึงข้อมูลคำสั่งซื้อตาม Order_id
$current_order_id = $_SESSION['order_data']['Order_id'];
        if (isset($_SESSION['order_data'])) {
            $order_data = $_SESSION['order_data'];
            $order_id = $order_data['Order_id'];




            

            // Query หา Status_id จาก orders ที่ตรงกับ Order_id
            $stmt = $conn->prepare("SELECT  o.Status_id, s.Status_name, o.Quantity, o.Price_Order, o.Bag_id, b.Price, c.Colors_name, p.B_img, m.Material_name, bt.type_name, bb.brand_name
            FROM orders o 
            INNER JOIN status s ON o.Status_id = s.Status_id 
            INNER JOIN bag b ON o.bag_id = b.bag_id 
            INNER JOIN bag_color bc ON o.Colors_code = bc.Colors_code
            INNER JOIN colors c ON bc.Colors_code = c.Colors_code
            INNER JOIN pictures p ON p.Bag_id = b.Bag_id
            INNER JOIN bag_type bt ON b.type_id = bt.type_id
            INNER JOIN bag_brand bb ON b.brand_id = bb.brand_id
            INNER JOIN material m ON b.Material_id = m.Material_id
            WHERE o.Order_id = ?");
            $stmt->bind_param("s", $order_id);
            $stmt->execute();
            $result = $stmt->get_result();

            // แสดงข้อมูลคำสั่งซื้อ
            if ($row = $result->fetch_assoc()) {
                echo '<p class="Order">เลขที่สั่งซื้อ <span class="Order-id">' . htmlspecialchars($order_data['Order_id'], ENT_QUOTES, 'UTF-8') . '</span>'.
                ' <span class="Status-name">' . htmlspecialchars($row['Status_name'], ENT_QUOTES, 'UTF-8') . '</span></p>';

                echo '<p style="font-size: 18px; margin-bottom: 8px;">' . 
                htmlspecialchars($order_data['Title_name'], ENT_QUOTES, 'UTF-8') . ' ' . 
                htmlspecialchars($order_data['First_name'], ENT_QUOTES, 'UTF-8') . ' ' . 
                htmlspecialchars($order_data['Last_name'], ENT_QUOTES, 'UTF-8') . '</p>';
                echo '<p style="font-size: 16px; margin-bottom: 8px;">' . 
                htmlspecialchars($order_data['H_number'], ENT_QUOTES, 'UTF-8') . ' ' . 
                htmlspecialchars($order_data['Road'], ENT_QUOTES, 'UTF-8') . ' ' . 
                htmlspecialchars($order_data['Alley'], ENT_QUOTES, 'UTF-8') . ' ' .
                htmlspecialchars($order_data['Subdistrict_name'], ENT_QUOTES, 'UTF-8') . ', ' . 
                htmlspecialchars($order_data['District_name'], ENT_QUOTES, 'UTF-8') . ', ' . 
                htmlspecialchars($order_data['province_name'], ENT_QUOTES, 'UTF-8') . ', ' . 
                htmlspecialchars($order_data['Postcode'], ENT_QUOTES, 'UTF-8') . '</p>';
                echo '<p style="font-size: 16px; margin-bottom: 8px;">Phone: ' . 
                htmlspecialchars($order_data['Phone_number'], ENT_QUOTES, 'UTF-8') . '</p>';

               
        
                // แทรกรูปภาพที่ต้องการแสดง
              
              
                
             // ใช้ array เพื่อเก็บข้อมูลของ bag_id และ Colors_name ที่แสดงแล้ว
$displayed_bag_ids_colors = array();
$total_price = 0;

echo '<table class="cart-table">
<thead>
    <br>   <br>
    <tr>
        <th>รูปภาพ</th>
        <th>ประเภท/ยี่ห้อ/วัสดุ</th>
        <th>สี</th>
        <th>จำนวน</th>
        <th>ราคา</th>
    </tr>
</thead>
<tbody>';

do {
    // สร้างคีย์ที่เป็นการรวมของ bag_id และ Colors_name
    $bag_color_key = $row['Bag_id'] . '-' . $row['Colors_name'];

    if (!in_array($bag_color_key, $displayed_bag_ids_colors)) {
        // เก็บ bag_id และ Colors_name ที่แสดงแล้ว
        $displayed_bag_ids_colors[] = $bag_color_key;

        echo '<tr>';
        echo '<td>';
        if (!empty($row['B_img'])) {
            $img_path = '../' . htmlspecialchars($row['B_img'], ENT_QUOTES, 'UTF-8');
            echo '<img src="' . $img_path . '" alt="Order Image" class="order-image">';
        } else {
            echo 'ไม่มีภาพสำหรับกระเป๋านี้';
        }
        echo '</td>';
        echo '<td>' . htmlspecialchars($row['type_name'], ENT_QUOTES, 'UTF-8') . ' /' .
            htmlspecialchars($row['brand_name'], ENT_QUOTES, 'UTF-8') . ' /' .
            htmlspecialchars($row['Material_name'], ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td>' . htmlspecialchars($row['Colors_name'], ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td>' . htmlspecialchars($row['Quantity'], ENT_QUOTES, 'UTF-8') . '</td>';
        echo '<td>' . number_format($row['Price'], 2) . '</td>';
        echo '</tr>';

        // คำนวณราคารวม
        $total_price += $row['Price'] * $row['Quantity'];
    }
} while ($row = $result->fetch_assoc());
echo '<img src="../img/0000.jpg" alt="Order Image" class="order-image">';

echo '</tbody></table>';
                
                // แสดงราคารวม
                echo '<p class="total-price" style="font-size: 18px; margin-top: 25px;  margin-left: 47%;">ราคารวมทั้งหมด: ฿' . number_format($total_price, 2) . ' บาท</p><br><br>';
                // echo '<p class="order-time">โปรดชำระเงินภายในเวลา 60 นาที</p>';
                echo '<p></p>';
              
                echo '<p class="order-time" style=" font-size: 16px;
                color: red;
                 margin-top: -5%;
                 margin-left: 35%;">โปรดชำระเงินภายในเวลา <span id="countdown-timer"></span> </p>';
 
              
                

            } else {
                echo "<p>ไม่พบข้อมูลการสั่งซื้อ</p>";
            }

            $stmt->close();
        } else {
            echo "<p>ไม่พบข้อมูลการสั่งซื้อ</p>";
        }
        ?>


<form action="conorder.php" method="POST" enctype="multipart/form-data" onsubmit="return validateForm()">
    <p class="total-image">
        หลักฐานการชำระเงิน
        <label for="receipt-upload" class="file-upload-button">เลือกไฟล์รูปภาพ</label>
        <input type="file" id="receipt-upload" name="Receipt_img" accept="image/*" class="file-upload" onchange="displayFileName()">
        <span id="file-name" class="file-name">ยังไม่มีไฟล์เลือก</span>
    </p>
    <input type="hidden" name="Order_id" value="<?php echo htmlspecialchars($order_data['Order_id'], ENT_QUOTES, 'UTF-8'); ?>">
    <input type="hidden" name="Receipt_id" value="<?php echo htmlspecialchars($order_data['Receipt_id'], ENT_QUOTES, 'UTF-8'); ?>">
    <button type="submit" id="submit-button" class="submit-button">ยืนยันการสั่งซื้อ</button>
</form>

<script>
function displayFileName() {
    var input = document.getElementById('receipt-upload');
    var fileName = input.files[0].name;
    document.getElementById('file-name').textContent = fileName;
}

// ฟังก์ชันตรวจสอบการอัปโหลดไฟล์รูปภาพ
function validateForm() {
    var input = document.getElementById('receipt-upload');
    
    if (input.files.length === 0) {
        alert("กรุณาอัปโหลดไฟล์หลักฐานการชำระเงินก่อนยืนยันการสั่งซื้อ");
        return false; // หยุดการส่งฟอร์ม
    }
    return true; // อนุญาตให้ส่งฟอร์ม
}


    // เวลานับถอยหลังที่ได้จาก PHP (ms)
    var targetTime = <?php echo $_SESSION['target_times'][$current_order_id] * 1000; ?>;
        var countdownElement = document.getElementById('countdown-timer');
        var hasRefreshed = false;  // ใช้เพื่อป้องกันการรีเฟรชซ้ำ

        function updateCountdown() {
            var now = new Date().getTime();
            var distance = targetTime - now;

            if (distance < 0) {
                countdownElement.innerHTML = "หมดเวลา";
                if (!hasRefreshed) {  // ตรวจสอบว่ารีเฟรชแล้วหรือยัง
                    hasRefreshed = true;
                    alert("หมดเวลาในการชำระเงินแล้ว!");
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);  // หน่วงเวลา 1 วินาทีเพื่อให้การรีเฟรชดำเนินการได้อย่างราบรื่น
                }
                return;
            }

            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((distance % (1000 * 60)) / 1000);
            countdownElement.innerHTML = minutes + "น." + seconds + "ว.";
        }

        setInterval(updateCountdown, 1000); // เรียกใช้ฟังก์ชันทุก ๆ วินาที
    </script>

    </div>





    <!-- 
    <script>
    const member_id = <?php echo json_encode($order_data['member_id']); ?>;
    console.log("Member ID: ", member_id);

    const subdistrict_id = <?php echo json_encode($order_data['subdistrict_id']); ?>;
    console.log("subdistrict_id: ", subdistrict_id);



    const Receipt_id = <?php echo json_encode($order_data['Receipt_id']); ?>;
    console.log("Receipt_id: ", Receipt_id);;
    </script> -->

</body>

</html>