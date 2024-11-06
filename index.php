<?php
session_start();
include 'connetDB/con_db.php';

$selected_types = isset($_GET['bag_type']) ? $_GET['bag_type'] : [];
$selected_brands = isset($_GET['bag_brand']) ? $_GET['bag_brand'] : [];
$selected_materials = isset($_GET['material']) ? $_GET['material'] : [];
$selected_colors = isset($_GET['colors']) ? $_GET['colors'] : [];
$min_price = isset($_GET['min_price']) ? $_GET['min_price'] : 1000; // ค่าราคาขั้นต่ำ
$max_price = isset($_GET['max_price']) ? $_GET['max_price'] : 5000; // ค่าราคาขั้นสูง

// เริ่มต้นสร้างคำสั่ง SQL
$sql = "SELECT b.Bag_id, b.Price, bt.type_name, bb.brand_name, m.material_name, 
               GROUP_CONCAT(DISTINCT p.B_img SEPARATOR ', ') AS B_img, 
               GROUP_CONCAT(DISTINCT co.colors_name SEPARATOR ', ') AS colors_name
        FROM bag b
        JOIN bag_type bt ON b.type_id = bt.type_id
        JOIN material m ON b.Material_id = m.Material_id
        JOIN bag_brand bb ON b.brand_id = bb.brand_id
        JOIN pictures p ON b.Bag_id = p.Bag_id
        JOIN bag_color bc ON b.Bag_id = bc.Bag_id
        JOIN colors co ON bc.Colors_code = co.Colors_code";

$conditions = [];
$params = [];
$types = '';

// เช็คประเภทที่ถูกเลือก
if (!empty($selected_types)) {
    $types_placeholders = implode(',', array_fill(0, count($selected_types), '?'));
    $conditions[] = "bt.type_name IN ($types_placeholders)";
    $params = array_merge($params, $selected_types);
    $types .= str_repeat('s', count($selected_types));
}

// เช็คยี่ห้อที่ถูกเลือก
if (!empty($selected_brands)) {
    $brands_placeholders = implode(',', array_fill(0, count($selected_brands), '?'));
    $conditions[] = "bb.brand_name IN ($brands_placeholders)";
    $params = array_merge($params, $selected_brands);
    $types .= str_repeat('s', count($selected_brands));
}

// เช็ควัสดุที่ถูกเลือก
if (!empty($selected_materials)) {
    $materials_placeholders = implode(',', array_fill(0, count($selected_materials), '?'));
    $conditions[] = "m.material_name IN ($materials_placeholders)";
    $params = array_merge($params, $selected_materials);
    $types .= str_repeat('s', count($selected_materials));
}

// เช็คสีที่ถูกเลือก
if (!empty($selected_colors)) {
    $colors_placeholders = implode(',', array_fill(0, count($selected_colors), '?'));
    $conditions[] = "co.colors_name IN ($colors_placeholders)";
    $params = array_merge($params, $selected_colors);
    $types .= str_repeat('s', count($selected_colors));
}

// กรองราคาตามช่วงที่กำหนด
$conditions[] = "b.Price BETWEEN ? AND ?";
$params[] = $min_price;
$params[] = $max_price;
$types .= 'ii'; // ใช้ 'ii' สำหรับข้อมูลชนิดตัวเลข (int)

// เพิ่ม WHERE clause หากมีเงื่อนไข
if (!empty($conditions)) {
    $sql .= ' WHERE ' . implode(' AND ', $conditions);
}

$sql .= " GROUP BY b.Bag_id, b.Price, bt.type_name, bb.brand_name, m.material_name";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    die("Error: " . mysqli_error($conn));
}

// เก็บผลลัพธ์
$bag_data = [];
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $bag_data[] = $row;
    }
}
?>





<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>index</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
        referrerpolicy="no-referrer" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jaro:opsz@6..72&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=K2D:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="cssindexx.css">

</head>

<body>
    <nav class="navbar">
        <div class="navbar-icons">
            <?php if (isset($_SESSION['First_name'])) : ?>
            <div class="nav-btn">
                <p class="user-name"><strong><?php echo $_SESSION['First_name']; ?></strong></p>
            </div>
            <div class="nav-btn user-dropdown">
                <i class="user-circle fas fa-user-circle" onclick="toggleDropdown()"></i>
                <div class="dropdown-menu" id="dropdownMenu">
                    <a href="product/profile.php">บัญชีผู้ใช้</a>
                    <a href="product/history.php">ประวัติการสั่งซื้อ</a>
                    <a href="#" onclick="confirmLogout()">ออกจากระบบ</a>
                </div>
            </div>
            <a class="nav-btn" href="product/cartpage.php">
                <i class="fas fa-shopping-cart"></i>
                <span id="cart-item-count" class="cart-item-count">
                    <?php echo isset($_SESSION['cartItemCount']) ? $_SESSION['cartItemCount'] : 0; ?>
                </span>
            </a>
            <?php else: ?>
            <div class="container-fluid">
                <button type="button" class="btn-login" onclick="location.href='./login/login.php'">เข้าสู่ระบบ</button>
                <button type="button" class="btn-register"
                    onclick="location.href='./register/registerr.php'">สมัครสมาชิก</button>
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
        if (dropdownMenu.style.display === 'block') {
            dropdownMenu.style.display = 'none';
        } else {
            dropdownMenu.style.display = 'block';
        }
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
    <br>
    <h2 class="bag-heading" onclick="location.href='index.php'">Bag Collective</h2>

    <hr noshade width="100%">

    <div class="main-content">
        <div class="custom-form">
            <form class="from-serch" action="" method="GET">
                <div class="checkbox-group">
                    <legend class="bag-type" style="font-size: 20px; margin-left: 15px;">ประเภทกระเป๋า</legend>
                    <?php
    $query = "SELECT DISTINCT bt.type_name FROM bag_type bt";
    $query_run = mysqli_query($conn, $query);

    if (mysqli_num_rows($query_run) > 0) {
        while ($type = mysqli_fetch_assoc($query_run)) {
            ?>
                    <div>
                        <input type="checkbox" name="bag_type[]" value="<?= $type['type_name']; ?>"
                            <?php if (in_array($type['type_name'], $selected_types)) { echo "checked"; } ?>
                            onchange="this.form.submit();" />
                        <?= $type['type_name']; ?>
                    </div>
                    <?php
        }
    } else {
        echo "ไม่พบประเภทกระเป๋า";
    }
    ?>
                </div>


                <div class="checkbox-group">
                    <legend class="bag-type" style="font-size: 20px; margin-left: 15px;">ยี่ห้อ</legend>
                    <?php
                        $query = "SELECT DISTINCT bb.brand_name FROM bag_brand bb";
                        $query_run = mysqli_query($conn, $query);

                        if (mysqli_num_rows($query_run) > 0) {
                            while ($brand = mysqli_fetch_assoc($query_run)) {
                    ?>
                    <div>
                        <input type="checkbox" name="bag_brand[]" value="<?= $brand['brand_name']; ?>"
                            <?php if (in_array($brand['brand_name'], $selected_brands)) { echo "checked"; } ?>
                            onchange="this.form.submit();" />

                        <?= $brand['brand_name']; ?>
                    </div>
                    <?php
                            }
                        } else {
                            echo "No Brands Found";
                        }
                    ?>
                </div>

                <div class="checkbox-group">
                    <legend class="bag-type" style="font-size: 20px; margin-left: 15px;">วัสดุ</legend>
                    <?php
                        $query = "SELECT DISTINCT m.material_name FROM material m";
                        $query_run = mysqli_query($conn, $query);

                        if (mysqli_num_rows($query_run) > 0) {
                            while ($material = mysqli_fetch_assoc($query_run)) {
                    ?>
                    <div>

                        <input type="checkbox" name="material[]" value="<?= $material['material_name']; ?>"
                            <?php if (in_array($material['material_name'], $selected_materials)) { echo "checked"; } ?>
                            onchange="this.form.submit();" />
                        <?= $material['material_name']; ?>
                    </div>
                    <?php
                            }
                        } else {
                            echo "No Materials Found";
                        }
                    ?>
                </div>
                

                <div class="price-range">
    <legend class="legend-custom" style="font-size: 20px; margin-left: 15px;">ราคา</legend>
    <div class="range-wrapper">
        <input type="range" id="Price" name="Price" min="1000" max="5000" step="100"
        oninput="updatePriceRange();" onchange="this.form.submit();" 
        value="<?php echo $max_price; ?>"> <!-- กำหนดค่าเริ่มต้นจาก PHP -->
    </div>
    <div class="range-values" style="margin-left: 55px;">
        <span id="current-value"><?php echo number_format($min_price); ?></span> <!-- ค่าเริ่มต้น -->
        <span id="current">5,000</span>
    </div>
</div>

<form method="GET" action="">
    <input type="hidden" id="MinPrice" name="min_price" value="<?php echo $min_price; ?>"> <!-- ซ่อนค่าส่ง min_price -->
    <input type="hidden" id="MaxPrice" name="max_price" value="<?php echo $max_price; ?>"> <!-- ซ่อนค่าส่ง max_price -->
</form>

<script>
function updatePriceRange() {
    var priceSlider = document.getElementById('Price');
    var currentValue = document.getElementById('current-value');
    var selectedValue = priceSlider.value;
    currentValue.textContent = parseInt(selectedValue).toLocaleString();

    // อัปเดตค่า max ที่ซ่อนไว้
    document.getElementById('MaxPrice').value = selectedValue;
}

// ตั้งค่าค่าเริ่มต้นของแถบเลื่อนและตัวแสดงตามค่าที่ค้นหาล่าสุด
window.onload = function() {
    var priceSlider = document.getElementById('Price');
    var currentValue = document.getElementById('current-value');
    var selectedValue = priceSlider.value;
    currentValue.textContent = parseInt(selectedValue).toLocaleString();
};
</script>



<div class="checkbox-group">
    <legend class="bag-type" style="font-size: 20px; margin-left: 15px;">สี</legend>
    <form method="GET" action="">
        <?php
            $query = "SELECT DISTINCT colors.Colors_name FROM colors";
            $query_run = mysqli_query($conn, $query);

            if (mysqli_num_rows($query_run) > 0) {
                echo '<div class="colors-container">';
                $index = 0;
                $total = mysqli_num_rows($query_run);
                $half = ceil($total / 2);

                while ($color = mysqli_fetch_assoc($query_run)) {
                    if ($index % $half == 0) {
                        if ($index > 0) {
                            echo '</div><div class="colors-column">';
                        } else {
                            echo '<div class="colors-column">';
                        }
                    }
        ?>
        <div class="colors-group">
            <input type="checkbox" name="colors[]" value="<?= $color['Colors_name']; ?>"
                <?php if (in_array($color['Colors_name'], $selected_colors)) { echo "checked"; } ?>
                onchange="this.form.submit();" />
            <?= $color['Colors_name']; ?>
        </div>
        <?php
                    $index++;
                }
                echo '</div></div>';
            } else {
                echo "ไม่พบสี";
            }
        ?>
    </form>
</div>







            </form>
        </div>


        <?php
// ตั้งค่าจำนวนสินค้าต่อหน้า
$items_per_page = 25;

// รับค่าหน้าปัจจุบันจาก URL (ค่าเริ่มต้นคือหน้า 1)
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$current_page = max(1, $current_page);

// คำนวณจำนวนสินค้าทั้งหมด
$total_items = count($bag_data);
$total_pages = ceil($total_items / $items_per_page);

// ตัดข้อมูลตามจำนวนสินค้าต่อหน้า
$offset = ($current_page - 1) * $items_per_page;
$current_items = array_slice($bag_data, $offset, $items_per_page);
?>
<div class="main-container">
        <div class="product-container">
            <?php if (!empty($current_items)) : ?>
            <?php foreach ($current_items as $bag) : ?>
            <div class="product-item" onclick="window.location.href='product/product.php?id=<?= $bag['Bag_id'] ?>'">
                <?php 
                $images = explode(', ', $bag["B_img"]); 
                echo '<img src="' . $images[0] . '" alt="Product Image">';
                ?>
                <div class="product-details">
                    <div class="product-name"><?= $bag["brand_name"] ?></div>
                    <div class="product-price">฿<?= number_format($bag["Price"]) ?></div>
                    <div class="product-color">
                        <?php 
                        $colors = explode(', ', $bag["colors_name"]);
                        foreach ($colors as $color) {
                        $color_code = '';
                        switch ($color) {
                            case 'Black':
                                $color_code = 'Black';
                                break;
                            case 'White':
                                $color_code = 'White';
                                break;
                            case 'Yellow':
                                $color_code = 'Yellow';
                                break;
                            case 'Brown':
                                $color_code = '#7E5C19';
                                break;
                            case 'Blue':
                                $color_code = '#D1FEFE';
                                break;
                            case 'Purple':
                                $color_code = '#EBD6FF';
                                break;
                            case 'Gray':
                                $color_code = '#ADADAD';
                                break;
                            case 'Apricot':
                                $color_code = '#F5EEA7';
                                break;
                            case 'Pink':
                                $color_code = 'pink';
                                break;
                            case 'Green':
                                    $color_code = 'Green';
                                    break;
                            case 'Red':
                                    $color_code = 'Red';
                                    break;
                            case 'Orange':
                                    $color_code = 'Orange';
                                    break;
                            
                        }
                        echo '<span class="color-circle" style="background-color: ' . $color_code . ';"></span> ';
                    }
                    ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php else : ?>
            <p class="no-products" style="margin-top: 20%;">ไม่พบสินค้า</p>
            <?php endif; ?>
        </div>

        <ul class="pagination">
        <?php for ($page = 1; $page <= $total_pages; $page++) : ?>
            <li><a href="?page=<?= $page ?>" <?= $page == $current_page ? 'class="active"' : '' ?>><?= $page ?></a></li>
        <?php endfor; ?>
    </ul>
</div>


</body>

</html>
