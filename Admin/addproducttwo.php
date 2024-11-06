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
    <link rel="stylesheet" href="manage.css">
    <link rel="stylesheet" href="addproductt.css">

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
        window.location.href = "logout.php";
    }
}
</script>

    <div class="container">
        <div id="nav-placeholder" class="nav-placeholder"></div>
        <h2 class="manage-add" style="margin-top: 0px;">เพิ่มข้อมูลสินค้า</h2><br>
        <div class="box">
            <form id="product-form" method="POST" enctype="multipart/form-data">
                <div class="form-container">

                    <div class="form-row">
                        <label for="bag-id" style="margin-right: 10px;">รหัสกระเป๋า:</label>
                        <input type="text" id="bag_id" name="bag_id" value="<?php echo $bagId; ?>" readonly>
                    </div>

                    <?php
                    $sql = "SELECT type_id, type_name FROM bag_type";
                    $result = $conn->query($sql);
                    ?>
                    <div class="form-row">
                        <label for="bag-type">ประเภท:</label>
                        <select id="bag-type" name="bag-type">
                            <option value=""> </option>
                            <?php
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<option value='" . htmlspecialchars($row['type_id'], ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars($row['type_name'], ENT_QUOTES, 'UTF-8') . "</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <?php
                    $sql = "SELECT brand_id, brand_name FROM bag_brand";
                    $result = $conn->query($sql);
                    ?>
                    <div class="form-row">
                        <label for="brand-type">ยี่ห้อ:</label>
                        <select id="brand-type" name="brand-type">
                            <option value=""> </option>
                            <?php
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<option value='" . htmlspecialchars($row['brand_id'], ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars($row['brand_name'], ENT_QUOTES, 'UTF-8') . "</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <?php
                    $sql = "SELECT Material_id, Material_name FROM material";
                    $result = $conn->query($sql);
                    ?>
                    <div class="form-row">
                        <label for="material-type">วัสดุ:</label>
                        <select id="material-type" name="material-type">
                            <option value=""> </option>
                            <?php
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    echo "<option value='" . htmlspecialchars($row['Material_id'], ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars($row['Material_name'], ENT_QUOTES, 'UTF-8') . "</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <?php
                    $sql = "SELECT `Colors_code`, `Colors_name` FROM `colors`";
                    $result = $conn->query($sql);
                    ?>
                    <div class="colors-container">
                        <label for="material-type">สี:</label>
                        <div class="color-buttons">
                            <?php
                            if ($result->num_rows > 0) {
                                $count = 0;
                                while ($row = $result->fetch_assoc()) {
                                    echo "<div style='display: inline-block; margin: 5px;'>";
                                    echo "<button type='button' onclick=\"selectColor(this, '" . htmlspecialchars($row['Colors_code'], ENT_QUOTES, 'UTF-8') . "', '" . htmlspecialchars($row['Colors_name'], ENT_QUOTES, 'UTF-8') . "')\" style='background-color: " . htmlspecialchars($row['Colors_code'], ENT_QUOTES, 'UTF-8') . "; color: black; border: none; padding: 10px; margin: 5px; border-radius: 5px;'>" . htmlspecialchars($row['Colors_name'], ENT_QUOTES, 'UTF-8') . "</button>";
                                    echo "<input type='number' value='0' min='0' class='color-count' style='width: 50px; margin-left: 5px; display: none;' onchange='updateCount(this)'/>";
                                    echo "</div>";
                                    $count++;
                                    if ($count % 6 == 0) {
                                        echo "<br>";
                                    }
                                }
                            }
                            ?>
                        </div>
                    </div>

                    <script>
                    let total = 0;

                    function selectColor(button, colorCode, colorName) {
                        let input = button.nextElementSibling;
                        if (input.style.display === "none") {
                            input.style.display = "inline-block";
                        } else {
                            input.style.display = "none";
                            total -= parseInt(input.value);
                            input.value = 0;
                            updateTotal();
                        }
                    }

                    function updateCount(input) {
                        let currentValue = parseInt(input.value);
                        if (isNaN(currentValue) || currentValue < 0) {
                            currentValue = 0;
                        }
                        total += currentValue;
                        updateTotal();
                    }

                    function updateTotal() {
                        console.log("Total quantity selected: " + total);
                    }
                    </script>


<div class="form-row">
    <label for="cost_price">ราคาต้นทุน:</label>
    <input type="number" id="cost_price" name="cost-_rice" step="0.01" required>
</div>

<div class="form-row">
    <label for="price">ราคาขาย:</label>
    <input type="number" id="price" name="price" step="0.01" required>
</div>







<div class="form-row">
        <label for="image-upload">อัปโหลดรูปภาพ:</label>
        <input type="file" id="image-upload" name="images[]" accept="image/*" multiple onchange="validateFiles(this)" required>
        <small>(สูงสุด 10 รูป)</small>
    </div>
    <div id="image-preview" class="image-preview"></div>
  

<script>
function validateFiles(input) {
    const preview = document.getElementById('image-preview');
    const currentImages = preview.getElementsByTagName('img').length; // นับจำนวนรูปภาพที่แสดงอยู่แล้ว
    const files = input.files;

    // ตรวจสอบว่าจำนวนรูปภาพที่มีอยู่แล้วรวมกับรูปภาพใหม่เกิน 10 หรือไม่
    if (currentImages + files.length > 10) {
        alert('สามารถอัปโหลดรูปภาพได้สูงสุด 10 รูป');
        input.value = ''; // Reset input
        return;
    }

    // แสดงตัวอย่างรูปภาพใหม่ที่เลือก
    for (const file of files) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.style.width = '100px'; // Set the width of the image preview
            img.style.margin = '5px'; // Add some margin
            preview.appendChild(img);
        };
        reader.readAsDataURL(file);
    }
}
</script>



                </div>
            </form     
                button type="submit" name="add-product" class="add-product-btn">เพิ่มสินค้า</button>
           
        </div>
    </div>
</body>

</html>
