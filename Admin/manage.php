<?php
session_start();
include '../connetDB/con_db.php';

// กำหนดจำนวนแถวต่อหน้า
$rows_per_page = 20;

// ตรวจสอบหมายเลขหน้าจาก URL ถ้าไม่มีให้เริ่มจากหน้าแรก
if (isset($_GET['page']) && is_numeric($_GET['page'])) {
    $current_page = (int)$_GET['page'];
} else {
    $current_page = 1;
}

// คำนวณจุดเริ่มต้นของการแสดงผลข้อมูลในแต่ละหน้า
$offset = ($current_page - 1) * $rows_per_page;

// SQL query เพื่อดึงข้อมูลจำนวนทั้งหมด (สำหรับการคำนวณหน้าทั้งหมด)
$total_rows_query = "SELECT COUNT(DISTINCT b.Bag_id, bc.Colors_code) AS total FROM bag b
                    JOIN bag_color bc ON b.Bag_id = bc.Bag_id";
$total_rows_result = $conn->query($total_rows_query);
$total_rows = $total_rows_result->fetch_assoc()['total'];

// คำนวณจำนวนหน้าทั้งหมด
$total_pages = ceil($total_rows / $rows_per_page);
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" referrerpolicy="no-referrer" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jaro:opsz@6..72&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=K2D:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="report.css">
    <link rel="stylesheet" href="orderr.css">
    <link rel="stylesheet" href="managerr.css">
</head>

<body>
    <div class="sidebar">
        <div class="user-profile">
            <i class="fas fa-user-circle"></i>
            <?php if (isset($_SESSION['First_name'])) : ?>
                <div class="nav-btn" style="font-size: 18px;">
                    <p class="user-name"><strong><?php echo htmlspecialchars($_SESSION['First_name'], ENT_QUOTES, 'UTF-8'); ?></strong></p>
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
        // ถ้าผู้ใช้กด OK ให้เปลี่ยนเส้นทางไปยังสคริปต์ logout.php
        window.location.href = "logout.php";
    }
}
</script>



    <div class="container">
        <h2 class="manage">จัดการข้อมูลสินค้า</h2>
        <div class="btn-container">
            <button class="btn btn-add"  onclick="location.href='addproduct.php' ">เพิ่มข้อมูล +</button>
        </div>

        <table class="cart-table">
            <thead>
                <tr>
                    <!-- <th>ลำดับ</th> -->
                    <th>รหัสกระเป๋า</th>
                    <th>ไฟล์รูปภาพ</th>
                    <th>ประเภท</th>
                    <th>ยี่ห้อ</th>
                    <th>วัสดุ</th>
                    <th>สี</th>
                    <th>จำนวน</th>
                    <th>ราคาขาย</th>
                    <th>ราคาทุน</th>
                    <th>จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // SQL query to select data from database
                $sql = "SELECT b.Bag_id, p.B_img, bt.type_name, bb.brand_name, m.material_name, bc.Colors_code,
                               co.colors_name, bc.Total, b.Price, b.Cost_price
                        FROM bag b
                        JOIN bag_type bt ON b.type_id = bt.type_id
                        JOIN material m ON b.Material_id = m.Material_id
                        JOIN bag_brand bb ON b.brand_id = bb.brand_id
                        JOIN pictures p ON b.Bag_id = p.Bag_id
                        JOIN bag_color bc ON b.Bag_id = bc.Bag_id
                        JOIN colors co ON bc.Colors_code = co.Colors_code
                        GROUP BY b.Bag_id, co.colors_name
                        
                       LIMIT $rows_per_page OFFSET $offset";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    $index = 1; // ตัวแปรสำหรับนับลำดับ
                    // Output data of each row
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        // echo "<td>" . $index++ . "</td>"; // แสดงลำดับและเพิ่มค่าของ $index
                        echo "<td>" . $row['Bag_id'] . "</td>";
                        echo "<td>";
                        // ดึงรูปภาพทั้งหมดของ Bag_id
                        $bag_id = $row['Bag_id'];
                        $sql_images = "SELECT B_img FROM pictures WHERE Bag_id = '$bag_id'";
                        $result_images = $conn->query($sql_images);
                        $images = [];
                        while ($img_row = $result_images->fetch_assoc()) {
                            $images[] = htmlspecialchars($img_row['B_img'], ENT_QUOTES, 'UTF-8');
                        }
                        // เก็บรูปภาพใน data-images attribute
                        echo "<a href='#' class='image-link' data-images='" . json_encode($images) . "'>รูปภาพ</a>";
                        echo "</td>";
                
                        echo "<td>" . $row['type_name'] . "</td>";
                        echo "<td>" . $row['brand_name'] . "</td>";
                        echo "<td>" . $row['material_name'] . "</td>";
                        echo "<td>" . $row['colors_name'] . "</td>";
                        echo "<td>" . $row['Total'] . "</td>";
                        echo "<td>" . $row['Price'] . "</td>";
                        echo "<td>" . $row['Cost_price'] . "</td>";
                        echo "<td class='manage-buttons'>";
                       echo "<button class='edit-btn' onclick=\"location.href='editproduct.php?Bag_id=" . $row['Bag_id'] . "&Colors_code=" . urlencode($row['Colors_code']) . "&Total=" . urlencode($row['Total']) . "&Colors_name=" . urlencode($row['colors_name']) . "';\">แก้ไข</button>";

                        
                        echo "<button class='delete-btn' onclick=\"confirmDelete('" . $row['Bag_id'] . "', '" . $row['Colors_code'] . "')\">ลบ</button>";


                        
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='11'>ไม่มีข้อมูลสินค้า</td></tr>";
                }

                $conn->close();
                ?>
            </tbody>
            
        </table>
        <br> 
        <?php
        // แสดงหมายเลขหน้า
echo "<div class='pagination'>";


for ($page = 1; $page <= $total_pages; $page++) {
    if ($page == $current_page) {
        echo "<span class='current-page'>$page</span>"; // หน้าปัจจุบัน
    } else {
        echo "<a href='manage.php?page=$page'>$page</a>";
    }
}


echo "</div>";
        
        ?>
        <br>
        <br>
    </div>
    
    <!-- ป็อปอัพสำหรับแสดงรูปภาพ -->
<div id="imagePopup" class="popup">
    <span class="close-btn">&times;</span>
    <div class="popup-content">
        <div id="popup-images"></div>
    </div>
</div>
<script>
    function confirmDelete(bagId, colorsCode) {
        var confirmation = confirm("ต้องการลบสินค้าหรือไม่?");
        if (confirmation) {
            location.href = 'delete.php?Bag_id=' + bagId + '&Colors_code=' + colorsCode;
        }
    }
</script>

<script>
// ฟังก์ชันเปิดป็อปอัพ
function openPopup(images) {
    const popup = document.getElementById("imagePopup");
    const popupImagesContainer = document.getElementById("popup-images");
    popupImagesContainer.innerHTML = ''; // ล้างรูปภาพเก่า
    
    // สร้าง img tag สำหรับแต่ละรูปภาพ
    images.forEach(function (imgSrc) {
        const img = document.createElement("img");
        img.src = imgSrc;
        img.alt = "Bag Image";
        img.classList.add("popup-image"); // เพิ่ม class สำหรับสไตล์
        popupImagesContainer.appendChild(img);
    });
    
    popup.style.display = "block";
}

// ปิดป็อปอัพ
document.querySelector(".close-btn").onclick = function() {
    document.getElementById("imagePopup").style.display = "none";
}

// เมื่อคลิกลิงก์รูปภาพ
document.querySelectorAll(".image-link").forEach(function (link) {
    link.onclick = function (e) {
        e.preventDefault();
        const images = JSON.parse(this.getAttribute("data-images"));
        openPopup(images);
    };
});
</script>


<style>
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

    .popup {
    display: none;
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background-color: white;
    border: 1px solid #ccc;
    z-index: 1000;
    width: 80%;
    height: 80%;
    overflow: auto;
    padding: 10px;
}

.popup-content {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
}

.popup-image {
    max-width: 200px;
    margin: 10px;
}

.close-btn {
    position: absolute;
    top: 10px;
    right: 20px;
    font-size: 30px;
    cursor: pointer;
}

</style>
</body>

</html>
