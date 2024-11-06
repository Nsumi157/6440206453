<?php
session_start();
include '../connetDB/con_db.php';

// กำหนดจำนวนรายการต่อหน้า
$items_per_page = 25;

// กำหนดหมายเลขหน้าปัจจุบัน
$current_page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($current_page - 1) * $items_per_page;

// คำสั่ง SQL เพื่อดึงข้อมูลทั้งหมดก่อนที่จะทำการแบ่งหน้า
$sql_count = "SELECT COUNT(*) AS total_orders FROM orders";
$result_count = $conn->query($sql_count);
$row_count = $result_count->fetch_assoc();
$total_orders = $row_count['total_orders'];

// คำนวณจำนวนหน้าทั้งหมด
$total_pages = ceil($total_orders / $items_per_page);


?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
        referrerpolicy="no-referrer" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jaro:opsz@6..72&display=swap" rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=K2D:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="report.css">
    <link rel="stylesheet" href="orderr.css">
    <link rel="stylesheet" href="receipt.css">
</head>

<body>
    <div class="sidebar">
        <div class="user-profile">
            <i class="fas fa-user-circle"></i>
            <?php if (isset($_SESSION['First_name'])) : ?>
            <div class="nav-btn" style="font-size: 18px;">
                <p class="user-name">
                    <strong><?php echo htmlspecialchars($_SESSION['First_name'], ENT_QUOTES, 'UTF-8'); ?></strong></p>
            </div>
            <?php endif; ?>
        </div>



        <div class="nav-links">
            <a href="manage.php">จัดการข้อมูลสินค้า</a>
            <a href="order.php">รายการคำสั่งซื้อ</a>
            <a href="reportform.php">รายงานยอดการสั่งซื้อ</a>
        </div>

        <div class="logout-container">
            <button class="logout-btn">
                <a href="#" style="border: none; text-decoration: none;" onclick="confirmLogout()">ออกจากระบบ</a>
                <i class="fas fa-sign-out-alt"></i>
            </button>
        </div>
    </div>
    <script>
    function confirmLogout() {
        // แสดงกล่องยืนยัน
        var confirmation = confirm("ต้องการออกจากระบบใช่หรือไม่?");
        if (confirmation) {
            window.location.href = "logout.php";
        }
    }
    </script>


    <div class="container">
        <h2 class="manage">จัดการคำสั่งซื้อ</h2>

        <form action="" method="GET" class="search-form">
            <div class="form-row" style="margin-left: 120%;">
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
        </form>


        <table class="cart-table">
            <thead>
                <tr>
                    <th>เลขที่สั่งซื้อ</th>
                    <th>วันที่สั่งซื้อ</th>
                    <th>ประเภท/ยี่ห้อ/วัสดุ</th>
                    <th>หลักฐานการชำระ</th>
                    <th>ผู้ซื้อ</th>
                    <th>สถานะการสั่งซื้อ</th>
                    <th>เลขพัสดุ</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // คำสั่ง SQL เพื่อเลือกข้อมูลจากฐานข้อมูล
             
                $sql = "SELECT 
                    o.Order_id, 
                    o.Order_date, 
                    s.Status_id,
                    o.Order_time, r.Tracking,
                    GROUP_CONCAT(DISTINCT CONCAT(bt.type_name, ' ', bb.brand_name, ' ', m.Material_name, ' ', c.Colors_name) SEPARATOR '|') AS Product_Details,
                    GROUP_CONCAT(DISTINCT CONCAT(o.Quantity) SEPARATOR ', ') AS Total_Quantity,
                    GROUP_CONCAT(DISTINCT CONCAT(o.Price_Order) SEPARATOR ', ') AS Price_Order,
                    GROUP_CONCAT(DISTINCT r.Receipt_img SEPARATOR ', ') AS Receipt_Images,
                    GROUP_CONCAT(DISTINCT r.NamRec SEPARATOR ', ') AS Receipt_Names,
                    s.Status_name
                FROM orders o 
                INNER JOIN receipt r ON o.Receipt_id = r.Receipt_id 
                INNER JOIN status s ON o.Status_id = s.Status_id 
                INNER JOIN bag b ON o.bag_id = b.bag_id 
                INNER JOIN bag_color bc ON o.Colors_code = bc.Colors_code
                INNER JOIN colors c ON bc.Colors_code = c.Colors_code
                INNER JOIN pictures p ON p.Bag_id = b.Bag_id
                INNER JOIN bag_type bt ON b.type_id = bt.type_id
                INNER JOIN bag_brand bb ON b.brand_id = bb.brand_id
                INNER JOIN material m ON b.Material_id = m.Material_id";

// เพิ่มเงื่อนไข WHERE สำหรับ status_id
            if (isset($_GET['status']) && $_GET['status'] != "") {
                $status_id = intval($_GET['status']); // แปลงค่าที่ได้รับเป็น integer เพื่อความปลอดภัย
                        $sql .= " WHERE o.Status_id = $status_id";
            }

                $sql .= " GROUP BY o.Order_id
                ORDER BY o.Order_date DESC, o.Order_time DESC
                  LIMIT $offset, $items_per_page";

                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $hasImages = !empty($row['Receipt_Images']);
                        
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['Order_id'], ENT_QUOTES, 'UTF-8') . "</td>";
                        echo "<td>" . htmlspecialchars($row['Order_date'], ENT_QUOTES, 'UTF-8') . "<br>" . htmlspecialchars($row['Order_time'], ENT_QUOTES, 'UTF-8') . "</td>";
                        
                        $productDetails = str_replace('|', '<br>', htmlspecialchars($row['Product_Details'], ENT_QUOTES, 'UTF-8'));
                        // && $row['Status_id'] != 5
                        $buttonClass = ($hasImages && $row['Status_id'] != 4 ) ? "" : "disabled-button"; // กำหนดคลาสตามการมีรูปภาพและสถานะ
echo "<td>" . $productDetails . "<br><br>
    <button class='edit-btn $buttonClass' onclick=\"openModal('" . htmlspecialchars($row['Order_id'], ENT_QUOTES, 'UTF-8') . "')\">รายละเอียด</button>";

// if ($hasImages && $row['Status_id'] == 5) {
//     // แสดงลิงก์ใบเสร็จเฉพาะเมื่อมีรูปภาพและ Status_id = 5
//     echo "<a href='receipt.php?order_id=" . htmlspecialchars($row['Order_id'], ENT_QUOTES, 'UTF-8') . "' class='receipt-link' style='margin-left: 10px;'>ใบเสร็จ</a>";
// }

echo "</td>";

                        
                        
                        
                        $receipt_images = explode(', ', $row['Receipt_Images']);
                        $images_html = '';
                        foreach ($receipt_images as $image) {
                            $images_html .= "<img src='../pay/" . htmlspecialchars($image, ENT_QUOTES, 'UTF-8') . "' style='width: 100px; height: auto; margin-right: 5px;'>";
                        }
                        echo "<td>" . $images_html . "</td>";
                        echo "<td>" . htmlspecialchars($row['Receipt_Names'], ENT_QUOTES, 'UTF-8') . "</td>";
                        echo "<td>" . htmlspecialchars($row['Status_name'], ENT_QUOTES, 'UTF-8') . "</td>";
                        echo "<td>" . htmlspecialchars($row['Tracking'], ENT_QUOTES, 'UTF-8') . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>ไม่มีข้อมูลคำสั่งซื้อ</td></tr>";
                }
    
                $conn->close();
                ?>
            </tbody>
        </table>
        <div class="pagination">
            <?php
           

            for ($page = 1; $page <= $total_pages; $page++) {
                if ($page == $current_page) {
                    echo "<span class='current-page'>$page</span>";
                } else {
                    echo "<a href='order.php?page=$page'>$page</a>";
                }
            }

           
            ?>
            <br><br><br>
        </div>
    </div>

    <!-- ป๊อปอัพ -->
    <div id="myModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>

            <div id="modal-body">
                <!-- ข้อมูลจะถูกโหลดที่นี่โดย JavaScript -->
            </div>
        </div>
    </div>

    <script>
    function openModal(orderId) {
        // ใช้ AJAX เพื่อโหลดข้อมูลเพิ่มเติม
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'order_details.php?order_id=' + orderId, true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                document.getElementById('modal-body').innerHTML = xhr.responseText;
                document.getElementById('myModal').style.display = "block";
            }
        };
        xhr.send();
    }

    // function openReceiptModal(orderId) {
    //     // ใช้ AJAX เพื่อโหลดข้อมูลเพิ่มเติม
    //     var xhr = new XMLHttpRequest();
    //     xhr.open('GET', 'receipt.php?order_id=' + orderId, true);
    //     xhr.onload = function() {
    //         if (xhr.status === 200) {
    //             document.getElementById('modal-body').innerHTML = xhr.responseText;
    //             document.getElementById('myModal').style.display = "block";
    //         }
    //     };
    //     xhr.send();
    // }

    // ปิดป๊อปอัพเมื่อคลิกที่ (x)
    document.querySelector('.close').onclick = function() {
        document.getElementById('myModal').style.display = "none";
    }

    // // ปิดป๊อปอัพเมื่อคลิกที่พื้นที่นอกป๊อปอัพ
    // window.onclick = function(event) {
    //     if (event.target == document.getElementById('myModal')) {
    //         document.getElementById('myModal').style.display = "none";
    //     }
    // }
    </script>
    <style>
    .disabled-button {
        pointer-events: none;
        opacity: 0.5;
    }
    .pagination {
    text-align: center;
    margin-top: 20px;
    margin-left:70%;
}

.pagination a {
    display: inline-block;
    padding: 8px 16px;
    text-decoration: none;
    color: #000;
    border: 1px solid #ddd;
    margin: 0 4px;
}

.pagination a:hover {
    background-color: #ddd;
}

.current-page {
    display: inline-block;
    padding: 8px 16px;
    color: white;
    background-color: black;
    border: 1px solid black;
    margin: 0 4px;
}
    </style>
</body>

</html>