<?php
session_start();

$conn = new mysqli('localhost', 'root', '', 'dbonline_bag');
$conn->set_charset('utf8mb4');

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("การเชื่อมต่อล้มเหลว: " . $conn->connect_error);
}




$status_filter = '';
if (isset($_GET['status']) && !empty($_GET['status'])) {
    $status_id = intval($_GET['status']); // รับค่าจากฟอร์มและแปลงเป็นจำนวนเต็ม
    $status_filter = " AND o.Status_id = $status_id";
}

// ตรวจสอบว่าผู้ใช้ได้เข้าสู่ระบบหรือไม่
if (isset($_SESSION['Member_id'])) {
    $member_id = $_SESSION['Member_id'];

    // ดึงข้อมูล order ที่ตรงกับ member_id
    $sql_orders = "SELECT o.Status_id, s.Status_name, o.Order_date, o.Order_id, o.Order_time, o.Quantity, o.Price_Order, o.Bag_id, b.Price, c.Colors_name,
    SUBSTRING_INDEX(GROUP_CONCAT(p.B_img ORDER BY p.B_img ASC), ',', 1) AS B_img, -- ดึงรูปแรกของสินค้า
    m.Material_name, bt.type_name, bb.brand_name, r.Tracking, o.Review, o.Point,
     SUM(o.Quantity) AS Total_Quantity,
    SUM(o.Price_Order) AS Total_Price
    FROM orders o
    INNER JOIN receipt r ON o.Receipt_id = r.Receipt_id
    INNER JOIN status s ON o.Status_id = s.Status_id
    INNER JOIN bag b ON o.bag_id = b.bag_id
    INNER JOIN bag_color bc ON o.Colors_code = bc.Colors_code
    INNER JOIN colors c ON bc.Colors_code = c.Colors_code
    INNER JOIN pictures p ON p.Bag_id = b.Bag_id
    INNER JOIN bag_type bt ON b.type_id = bt.type_id
    INNER JOIN bag_brand bb ON b.brand_id = bb.brand_id
    INNER JOIN material m ON b.Material_id = m.Material_id
    WHERE o.member_id = ? $status_filter
    GROUP BY o.Order_id, o.Bag_id, c.Colors_name -- จัดกลุ่มตาม Order_id, Bag_id และ Colors_name
    ORDER BY o.Order_date DESC, o.Order_time DESC";

    $stmt = $conn->prepare($sql_orders);
    $stmt->bind_param("s", $member_id);
    $stmt->execute();
    $result_orders = $stmt->get_result(); 

      // เริ่มต้นรวมทั้งหมด
      $grand_total_quantity = 0;
      $grand_total_price = 0;

} else {
    echo "กรุณาเข้าสู่ระบบเพื่อดูประวัติการสั่งซื้อ";
    exit;
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ประวัติการสั่งซื้อ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
        referrerpolicy="no-referrer" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jaro:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=K2D:wght@400;600&display=swap" rel="stylesheet">
    <link href='https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="history.css">
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
            <h1 class="history">ประวัติการสั่งซื้อ</h1>
        </header>

        <br><br>

        <!-- <form action="" method="GET" class="search-form">
            <div class="form-row">
                <select id="status" name="status" onchange="this.form.submit()">
                    <option value="">เลือกสถานะ</option>
                    <?php
                    // ดึงสถานะการสั่งซื้อ
                    $sql = "SELECT Status_id, Status_name FROM status";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value='" . htmlspecialchars($row['Status_id'], ENT_QUOTES, 'UTF-8') . "'"
                                . (isset($_GET['status']) && $_GET['status'] == $row['Status_id'] ? " selected" : "")
                                . ">" . htmlspecialchars($row['Status_name'], ENT_QUOTES, 'UTF-8') . "</option>";
                        }
                    }
                    ?>
                </select>
            </div>
        </form> -->

      <!-- แสดงข้อมูลคำสั่งซื้อ -->
      <?php if ($result_orders->num_rows > 0): ?>
<div class="order-container">
    <?php 
    $current_order_id = null; // ตัวแปรเก็บ Order_id ปัจจุบัน
    // คำนวณรวมทั้งหมด
    while ($row = $result_orders->fetch_assoc()): 
        // ถ้า Order_id ไม่เหมือนกับ Order_id ปัจจุบัน ให้เริ่มแสดงรายละเอียดคำสั่งซื้อใหม่
        if ($row['Order_id'] !== $current_order_id): 
            if ($current_order_id !== null): // ถ้ามี Order_id ปัจจุบัน ให้ปิดป้ายคำสั่งซื้อก่อนหน้า
                echo '<div class="order-total" style=" margin-top: 20px; ">';
                echo '<p  >รวมทั้งหมดจำนวน: ' . htmlspecialchars($grand_total_quantity) . ' ใบ</p>';
                echo '<p>รวมทั้งหมดราคา: ' . number_format($grand_total_price, 2) . ' บาท</p>';
                echo '</div>'; // ปิด div ของ order-total
                echo '</div>'; // ปิด div ของ order-products
                echo '</div>'; // ปิด div ของ order-item
            endif;
            $current_order_id = $row['Order_id'];
            $grand_total_quantity = 0; // รีเซ็ตค่าเมื่อเริ่มคำสั่งซื้อใหม่
            $grand_total_price = 0; // รีเซ็ตค่าเมื่อเริ่มคำสั่งซื้อใหม่
        ?>
            <div class="order-item">
                <div class="order-header">
                    <p class="order-status">
                        เลขคำสั่งซื้อ: <?php echo htmlspecialchars($row['Order_id']); ?>
                    </p>
                    <div class="order-status-tracking">
                        <span
                            class="status <?php echo ($row['Status_id'] == 4 || $row['Status_id'] == 'c') ? 'red-status' : ($row['Status_id'] == 5 ? 'green-status' : ''); ?>">
                            <?php echo htmlspecialchars($row['Status_name']); ?>
                        </span><br>
                        <span class="Tracking"><?php echo htmlspecialchars($row['Tracking']); ?></span><br>
                        <span class="Review"
                            style="color: <?php echo (!empty($row['Review']) || !empty($row['Point'])) ? 'red' : 'black'; ?>;">
                            <?php echo (!empty($row['Review']) || !empty($row['Point'])) ? 'รีวิวแล้ว' : htmlspecialchars($row['Review']); ?>
                        </span>


                    </div>

                </div>

                <p class="order_day" style="margin-top: -3%">วันที่สั่งซื้อ:
                    <?php echo htmlspecialchars($row['Order_date']); ?></p>
                <p>เวลาสั่งซื้อ: <?php echo htmlspecialchars($row['Order_time']); ?></p>



                <div class="order-products">
                    <!-- เริ่มแสดงสินค้าของคำสั่งซื้อปัจจุบัน -->
                    <?php endif; ?>

                    <div class="product-item">
                        <!-- แสดงสินค้าทั้งหมดในคำสั่งซื้อ -->
                        <div class="product-image">
                            <?php if (!empty($row['B_img'])): ?>
                            <img src="../<?php echo htmlspecialchars($row['B_img']); ?>" alt="Product Image">
                            <?php else: ?>
                            <img src="../default-image.jpg" alt="Default Image"> <!-- รูปภาพเริ่มต้นหากไม่มีรูป -->
                            <?php endif; ?>
                        </div>
                        <?php 
                     // ตัวแปรสำหรับเก็บสถานะของการแสดงปุ่ม
                        static $hasButtonDisplayed = false;

                         // ตรวจสอบว่า Order_id เปลี่ยนหรือไม่ เพื่อรีเซ็ตปุ่ม
                        if ($row['Order_id'] !== $current_order_id) {
                             $hasButtonDisplayed = false; // รีเซ็ตสถานะ
                             
                         }
                            ?>

                        <div class="product-details">
                            <?php echo htmlspecialchars($row['type_name']); ?> /
                            <?php echo htmlspecialchars($row['brand_name']); ?> /
                            <?php echo htmlspecialchars($row['Material_name']); ?>
                            <p>สี: <?php echo htmlspecialchars($row['Colors_name']); ?></p>
                            <p>ราคา: <?php echo number_format(htmlspecialchars($row['Price']), 2); ?> บาท</p>
                            <p>จำนวน: <?php echo htmlspecialchars($row['Quantity']); ?> ใบ</p>
                            <p>รวมทั้งสิ้น: <?php echo number_format(htmlspecialchars($row['Price_Order']), 2); ?> บาท </p>
                       
                       
                       
                       
                       <?php
                            // อัพเดท grand_total_quantity และ grand_total_price สำหรับคำสั่งซื้อปัจจุบัน
                            $grand_total_quantity += $row['Quantity'];
                            $grand_total_price += $row['Price_Order'];
                            ?>
                        

                        </div>

                        <div class="order-actions">

    <?php
    // ตรวจสอบเฉพาะเมื่อ Status_id = 5 และยังไม่แสดงปุ่มรีวิวสำหรับ Order_id นี้
    static $reviewButtonDisplayed = []; // สร้าง array เพื่อเก็บสถานะการแสดงปุ่มของแต่ละ Order_id

    if ($row['Review'] === null && $row['Status_id'] == 5 && !in_array($row['Order_id'], $reviewButtonDisplayed)): ?>
        <!-- ปุ่มรีวิว -->
        <button class="review-button"
            onclick="window.location.href='review.php?Order_id=<?php echo $row['Order_id']; ?>'">รีวิว</button>
        <?php
        // อัปเดตสถานะให้แสดงปุ่มแล้วสำหรับ Order_id นี้
        $reviewButtonDisplayed[] = $row['Order_id'];
    endif;

    // ตัวแปรสำหรับเก็บสถานะของการแสดงปุ่มชำระเงิน
    static $payButtonDisplayed = [];

    // เพิ่มเงื่อนไขสำหรับ Status_id = 1 เพื่อแสดงปุ่มชำระเงิน
    if ($row['Status_id'] == 1 && !in_array($row['Order_id'], $payButtonDisplayed)): ?>
        <!-- ปุ่มชำระเงิน -->
        <button class="pay-button"
            onclick="window.location.href='Qrcode.php?Order_id=<?php echo $row['Order_id']; ?>'">ชำระเงิน</button>

        

        <?php
        // อัปเดตสถานะให้แสดงปุ่มแล้วสำหรับ Order_id นี้
        $payButtonDisplayed[] = $row['Order_id'];
    endif; 
    ?>
</div>


                    </div> <!-- ปิด div ของ order-item -->
                    
                    <?php 
                    endwhile; 
                    if ($current_order_id !== null): 
                 echo '</div>'; // ปิด div ของ order-products
                        echo '</div>'; // ปิด div ของ order-item
                     endif; 
                         ?>
                </div>
            </div>
            <?php else: ?>
            <p>ไม่มีประวัติการสั่งซื้อ</p>
            <?php endif; ?>



        </div>

    </div>


    <style>
    .pay-button {
        margin-left: 150%;
       
        width: 100px;
        color: #fff;
        border-radius: 5px;
        border: none;
        padding: 5px 15px;
        background-color: green;
        /* สีพื้นหลังของ order-item */
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
    .bag-heading{
    font-size: 40px;
    margin-right: 72.5%;
    color: #fff;
     font-family: 'Jaro', sans-serif; /* กำหนดฟอนต์ */
    
}
    </style>

</body>

</html>