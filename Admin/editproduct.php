<?php
session_start();
include '../connetDB/con_db.php';



$bag_id = isset($_GET['Bag_id']) ? $_GET['Bag_id'] : null;
$colors_code = isset($_GET['Colors_code']) ? $_GET['Colors_code'] : null;
$total = isset($_GET['Total']) ? $_GET['Total'] : null;
$colors_name = isset($_GET['Colors_name']) ? $_GET['Colors_name'] : null;

                    // // ตรวจสอบค่าที่ได้รับ
                    // echo "Bag ID: " . htmlspecialchars($bag_id, ENT_QUOTES, 'UTF-8') . "<br>";
                    // echo "Colors Code: " . htmlspecialchars($colors_code, ENT_QUOTES, 'UTF-8') . "<br>";
                    // echo "Total: " . htmlspecialchars($total, ENT_QUOTES, 'UTF-8') . "<br>";
                    // echo "Colors Name: " . htmlspecialchars($colors_name, ENT_QUOTES, 'UTF-8') . "<br>";

// ตรวจสอบว่ามี Bag_id หรือไม่
if (isset($_GET['Bag_id']) && isset($_GET['Colors_code'])) {
    $bag_id = $_GET['Bag_id'];
    $Colors_code = $_GET['Colors_code'];
    // ใช้ prepared statement
    $stmt = $conn->prepare("SELECT b.Bag_id, b.type_id, b.brand_id, b.Material_id, b.Price, b.Cost_price, c.colors_name,
                                bc.Total, bc.Colors_code
                            FROM bag b
                            JOIN bag_color bc ON b.Bag_id = bc.Bag_id
                             JOIN colors c ON bc.Colors_code = c.Colors_code
                            WHERE b.Bag_id = ?
                            LIMIT 1");
    $stmt->bind_param("s", $bag_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
    } else {
        echo "ไม่พบข้อมูล";
        exit;
    }
} else {
    header("Location: manage.php");
    exit;
}

// ดึงข้อมูลประเภท, ยี่ห้อ, วัสดุ และสีทั้งหมดจากฐานข้อมูล
$types = $conn->query("SELECT * FROM bag_type");
$brands = $conn->query("SELECT * FROM bag_brand");
$materials = $conn->query("SELECT * FROM material");
$colors = $conn->query("SELECT * FROM colors");
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
        referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="report.css">
    <link rel="stylesheet" href="edit.css">
    <title>แก้ไขข้อมูลสินค้า</title>
</head>

<body>
    <div class="sidebar">
        <div class="user-profile">
            <i class="fas fa-user-circle"></i>
            <?php if (isset($_SESSION['First_name'])) : ?>
            <div class="nav-btn">
                <p class="user-name">
                    <strong><?php echo htmlspecialchars($_SESSION['First_name'], ENT_QUOTES, 'UTF-8'); ?></strong>
                </p>
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
                <a href="#" onclick="confirmLogout()">ออกจากระบบ</a>
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
        <h2>แก้ไขข้อมูลกระเป๋า</h2>
        <div class="box">
            <div class="form-container">
                <form action="updateproduct.php" method="POST" enctype="multipart/form-data">
                    <div class="form-row">
                        <input type="hidden" name="Bag_id"
                            value="<?php echo htmlspecialchars($row['Bag_id'], ENT_QUOTES, 'UTF-8'); ?>">
                        <label for="Bag_id">รหัสกระเป๋า:</label>
                        <input type="text" name="Bag_id"
                            value="<?php echo htmlspecialchars($row['Bag_id'], ENT_QUOTES, 'UTF-8'); ?>" readonly
                            class="gray-input">
                    </div>

                    <div class="form-row">
                        <label for="type_name">ประเภท:</label>
                        <select name="type_name" required>
                            <option value="">เลือกประเภท</option>
                            <?php while ($type = $types->fetch_assoc()): ?>
                            <option value="<?php echo $type['type_id']; ?>"
                                <?php echo ($type['type_id'] == $row['type_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($type['type_name'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-row">
                        <label for="brand_name">ยี่ห้อ:</label>
                        <select name="brand_name" required>
                            <option value="">เลือกยี่ห้อ</option>
                            <?php while ($brand = $brands->fetch_assoc()): ?>
                            <option value="<?php echo $brand['brand_id']; ?>"
                                <?php echo ($brand['brand_id'] == $row['brand_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($brand['brand_name'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-row">
                        <label for="Material_name">วัสดุ:</label>
                        <select name="Material_name" required>
                            <option value="">เลือกวัสดุ</option>
                            <?php while ($material = $materials->fetch_assoc()): ?>
                            <option value="<?php echo $material['Material_id']; ?>"
                                <?php echo ($material['Material_id'] == $row['Material_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($material['Material_name'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>


                    <div class="form-row">
                        <label for="colors_name">สี:</label>
                        <input type="text" name="colors_name"
                            value="<?php echo htmlspecialchars($colors_name, ENT_QUOTES, 'UTF-8'); ?>"
                            class="gray-input">
                        <input type="hidden" name="Colors_code"
                            value="<?php echo htmlspecialchars($colors_code, ENT_QUOTES, 'UTF-8'); ?>">
                    </div>


                    <div class="form-row">
                        <label for="Total">จำนวน:</label>
                        <input type="number" name="Total"
                            value="<?php echo htmlspecialchars($total, ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>


                    <div class="form-row">
                        <label for="Price">ราคาขาย:</label>
                        <input type="number" name="Price"
                            value="<?php echo htmlspecialchars($row['Price'], ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>

                    <div class="form-row">
                        <label for="Cost_price">ราคาทุน:</label>
                        <input type="number" name="Cost_price"
                            value="<?php echo htmlspecialchars($row['Cost_price'], ENT_QUOTES, 'UTF-8'); ?>" required>
                    </div>

                    <div class="form-row">
                        <label for="B_img">อัปโหลดรูปภาพ:</label>
                        <input type="file" name="B_img[]" id="B_img" multiple onchange="previewImages()">
                    </div>

                    <div id="imagePreview" class="image-preview"></div>

                    <script>
                    function previewImages() {
                        var preview = document.getElementById('imagePreview');
                        preview.innerHTML = ''; // ลบตัวอย่างเก่าออกก่อน
                        var files = document.getElementById('B_img').files;

                        if (files) {
                            for (let i = 0; i < files.length; i++) {
                                var file = files[i];
                                var reader = new FileReader();

                                reader.onload = function(e) {
                                    var img = document.createElement('img');
                                    img.src = e.target.result;
                                    img.style.width = '150px'; // ตั้งขนาดรูปภาพ
                                    img.style.margin = '10px';
                                    preview.appendChild(img);
                                }

                                reader.readAsDataURL(file);
                            }
                        }
                    }
                    </script>

                    <style>
                    .image-preview img {
                        border: 2px solid #ddd;
                        border-radius: 5px;
                        padding: 5px;
                        margin-top: 10px;
                    }
                    </style>


                    <button type="submit">บันทึกการเปลี่ยนแปลง</button>
                    <button type="button" onclick="location.href='manage.php'">ยกเลิก</button>
                </form>

                <!-- แสดงรูปภาพที่มีอยู่ -->
                <h3>รูปภาพที่มีอยู่:</h3>
                <div class="image-gallery">
                    <?php
    // ดึงรูปภาพที่เกี่ยวข้องกับ Bag_id จากฐานข้อมูล
    $imageSql = "SELECT B_img FROM pictures WHERE Bag_id = ?";
    $imageStmt = $conn->prepare($imageSql);
    $imageStmt->bind_param("s", $bag_id);
    $imageStmt->execute();
    $imageResult = $imageStmt->get_result();

    if ($imageResult->num_rows > 0) {
        while ($imageRow = $imageResult->fetch_assoc()) {
            $imagePath = '../uploads/' . htmlspecialchars($imageRow['B_img'], ENT_QUOTES, 'UTF-8');
            echo '<div style="display: inline-block; text-align: center;">';
            echo '<img src="' . $imagePath . '" alt="Image" style="width:100px; height:auto; margin:5px;">';
            echo '<form method="POST" action="delete_image.php">';
            echo '<input type="hidden" name="image_name" value="' . htmlspecialchars($imageRow['B_img'], ENT_QUOTES, 'UTF-8') . '">';
            echo '<input type="hidden" name="bag_id" value="' . htmlspecialchars($bag_id, ENT_QUOTES, 'UTF-8') . '">';
            echo '<button type="submit" style="background-color: #ccc; color: white; border: none; font-size: 12px; padding: 2px 6px; cursor: pointer; border-radius: 3px; margin-top: 5px;">ลบ</button>';

            echo '</form>';
            echo '</div>';
        }
    } else {
        echo "<p>ไม่มีรูปภาพ</p>";
    }
    ?>
                </div>

            </div>
        </div>
    </div>
</body>

</html>