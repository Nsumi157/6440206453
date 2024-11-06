<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "dbonline_bag";
$conn = new mysqli($servername, $username, $password, $dbname);
$conn->set_charset('utf8mb4');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$cart_message = isset($_SESSION['cart_message']) ? $_SESSION['cart_message'] : '';
unset($_SESSION['cart_message']); // Clear the message after displaying
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Details</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
        referrerpolicy="no-referrer" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jaro:opsz@6..72&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=K2D:wght@400;600&display=swap" rel="stylesheet">
    <link href='https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="productbag.css">
    <link rel="stylesheet" href="starr.css">
    <style>
    .color-button.selected {
        border: 2px solid #000;

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
    .bag-heading {
        font-size: 45px;
        margin-right: 58%;
    }
    .color-button.disabled-color {
    background-color: #ccc; /* สีเทา */
    cursor: not-allowed;
    color: #666; /* สีตัวอักษรเป็นสีเทา */
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
            <?php if (isset($_SESSION['First_name'])): ?>
            <div class="nav-btn">
                <p class="user-name"><strong><?php echo htmlspecialchars($_SESSION['First_name']); ?></strong></p>
            </div>
            <div class="nav-btn user-dropdown">
                <i class="user-circle fas fa-user-circle" onclick="toggleDropdown()"></i>
                <div class="dropdown-menu" id="dropdownMenu">
                    <a href="profile.php">บัญชีผู้ใช้</a>
                    <a href="history.php">ประวัติการสั่งซื้อ</a>
                    <a href="#" onclick="confirmLogout()">ออกจากระบบ</a>
                </div>
            </div>
            <a class="nav-btn" href="cartpage.php">
                <i class="fas fa-shopping-cart"></i>
                <span id="cart-item-count" class="cart-item-count">
                    <?php echo isset($_SESSION['cartItemCount']) ? $_SESSION['cartItemCount'] : 0; ?>
                </span>
            </a>
            <?php else: ?>
            <div class="container-fluid">
                <button type="button" class="btn-login"
                    onclick="location.href='../login/login.php'">เข้าสู่ระบบ</button>
                <button type="button" class="btn-register"
                    onclick="location.href='../register/registerr.php'">สมัครสมาชิก</button>
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
    <br><br><br><br>


    <?php
// Get the bag ID from the URL
$ids = $conn->real_escape_string($_GET['id']);

$sql = "SELECT b.Bag_id, Price, type_name, brand_name, material_name,
GROUP_CONCAT(DISTINCT B_img ORDER BY B_img SEPARATOR ', ') AS B_imgs,
GROUP_CONCAT(DISTINCT co.Colors_name ORDER BY bc.Colors_code SEPARATOR ', ') AS colors_names,
GROUP_CONCAT(DISTINCT bc.Total ORDER BY bc.Colors_code SEPARATOR ', ') AS color_totals,
GROUP_CONCAT(DISTINCT bc.Colors_code ORDER BY bc.Colors_code SEPARATOR ', ') AS color_codes
FROM bag b
JOIN bag_type bt ON b.type_id = bt.type_id
JOIN material m ON b.Material_id = m.Material_id
JOIN bag_brand bb ON b.brand_id = bb.brand_id
JOIN pictures p ON b.Bag_id = p.Bag_id
JOIN bag_color bc ON b.Bag_id = bc.Bag_id
JOIN colors co ON bc.Colors_code = co.Colors_code
WHERE b.Bag_id = '$ids'
GROUP BY b.Bag_id";


$result = $conn->query($sql);   
$bag = $result->fetch_assoc();
?>

    <div class="product-detail">
        <div class="product-images">
            <?php
    if (!empty($bag["B_imgs"])) {
        $images = explode(', ', $bag["B_imgs"]);
        foreach ($images as $index => $img) {
            if ($index == 0) {
                echo '<img id="mainImage" src="../' . htmlspecialchars($img) . '" alt="Product Image"><br>';
            } else {
                echo '<img src="../' . htmlspecialchars($img) . '" alt="Product Image" onclick="swapImage(this)">';
            }
        }
    }
    ?>
        </div>



        <div class="product-info">
            <h1><?php echo htmlspecialchars($bag["brand_name"]); ?></h1>


            <p>ประเภท: <?php echo htmlspecialchars($bag["type_name"]); ?></p>
            <p>วัสดุ: <?php echo htmlspecialchars($bag["material_name"]); ?></p>
            <p>ราคา: ฿<?php echo number_format($bag["Price"]); ?></p>
            <div class="product-colors">
    <p>สี:</p>
    <div class="container-button">
        <?php
        if (!empty($bag["colors_names"]) && !empty($bag["color_totals"])) {
            $colors = explode(', ', $bag["colors_names"]);
            $totals = explode(', ', $bag["color_totals"]);
            $color_codes = explode(', ', $bag["color_codes"]); // เพิ่มการจับคู่ด้วย Color Code

            foreach ($colors as $index => $color_name) {
                $total = isset($totals[$index]) ? $totals[$index] : 0;
                $color_code = isset($color_codes[$index]) ? $color_codes[$index] : '';
                ?>
                <button class="color-button <?= $total <= 0 ? 'disabled-color' : ''; ?>"
                    onclick="selectColor(this, '<?= htmlspecialchars($color_name); ?>', <?= intval($total); ?>)"
                    data-total="<?= intval($total); ?>" data-color-code="<?= htmlspecialchars($color_code); ?>"
                    <?= $total <= 0 ? 'disabled' : '' ?>>
                    <?= htmlspecialchars($color_name); ?>
                </button>
                <?php
            }
        }
        ?>
    </div>
</div>



            <script>
            function selectColor(button, color, total) {
                document.getElementById('Colors_name').value = color;
                document.getElementById('Colors_code').value = button.getAttribute('data-color-code'); // เพิ่มบรรทัดนี้
                document.getElementById('total-quantity').innerText = 'จำนวน: ' + total;

                var buttons = document.querySelectorAll('.color-button');
                buttons.forEach(btn => btn.classList.remove('selected'));
                button.classList.add('selected');
            }


            function changeQuantity(amount) {
                var quantityInput = document.getElementById('quantity-input');
                var currentQuantity = parseInt(quantityInput.value);
                var maxQuantity = parseInt(document.querySelector('.color-button.selected').dataset.total);

                var newQuantity = currentQuantity + amount;

                if (newQuantity < 1) {
                    newQuantity = 1;
                } else if (newQuantity > maxQuantity) {
                    alert('ไม่สามารถเลือกสินค้าเกินกว่านี้ได้');
                    newQuantity = maxQuantity;
                }

                quantityInput.value = newQuantity;
                document.getElementById('Quantity').value = newQuantity;
            }


            function isLoggedIn() {
                // Check if user is logged in by verifying if user data is in session
                return <?php echo isset($_SESSION['First_name']) ? 'true' : 'false'; ?>;
            }

            function submitForm(formId) {
                var selectedColor = document.getElementById('Colors_name').value;
                if (!selectedColor) {
                    alert("กรุณาเลือกสีของสินค้า");
                    return false;
                }

                if (!isLoggedIn()) {
                    alert("กรุณาล็อกอินก่อนทำรายการ");
                    return false;
                }

                var quantity = parseInt(document.getElementById('Quantity').value);
                var maxQuantity = parseInt(document.querySelector('.color-button.selected').dataset.total);

                if (quantity > maxQuantity) {
                    alert('ไม่สามารถเลือกสินค้าเกินกว่านี้ได้');
                    return false;
                }

                document.getElementById(formId).submit();
                return false;
            }
           
            // Show success message if present in the URL
            <?php if (isset($_GET['message']) && $_GET['message'] === 'added'): ?>
            alert("เพิ่มสินค้าในตะกร้าสำเร็จ");
            <?php endif; ?>
            </script>





            <p id="total-quantity">จำนวนสินค้า: 0</p>


            <div id="quantity-control">
                <button onclick="changeQuantity(-1)">-</button>
                <input type="number" id="quantity-input" value="1" min="1">
                <button onclick="changeQuantity(1)">+</button>
            </div>

            <div class="button-container">
                <form action="cart_action.php" method="POST" id="cartform" onsubmit="return submitForm('cartform')"
                    style="display: inline-block;">
                    <input type="hidden" name="bag_id" value="<?php echo htmlspecialchars($bag['Bag_id']); ?>">
                    <input type="hidden" name="Quantity" id="Quantity" value="1">
                    <input type="hidden" name="Colors_name" id="Colors_name" value="">
                    <input type="hidden" name="Colors_code" id="Colors_code" value=""> <!-- เพิ่มบรรทัดนี้ -->
                    <input type="hidden" name="type_name" value="<?php echo htmlspecialchars($bag['type_name']); ?>">
                    <input type="hidden" name="brand_name" value="<?php echo htmlspecialchars($bag['brand_name']); ?>">
                    <input type="hidden" name="material_name"
                        value="<?php echo htmlspecialchars($bag['material_name']); ?>">
                    <input type="hidden" name="price" value="<?php echo htmlspecialchars($bag['Price']); ?>">
                    <input type="hidden" name="main_image" id="main-image-url" value="">
                    <input type="hidden" name="B_imgs" value="<?php echo htmlspecialchars($bag['B_imgs']); ?>">

                    <button type="submit" class="btn_action" id="add-to-cart">เพิ่มลงในตะกร้า</button>
                </form>

                </form>


            </div>
        </div>


    </div>
    <br>
    <hr noshade width="100%">
    <script>
    function swapImage(imgElement) {
        document.getElementById('mainImage').src = imgElement.src;
    }
    </script>


    <br>


    <h3 class="review" style="margin-left: 30%;">ความคิดเห็น</h3>

<?php
// คำสั่ง SQL เพื่อดึงความคิดเห็นและคะแนนสำหรับ bag_id ที่กำหนด โดยแยกตาม Colors_name
$review_sql = "
SELECT o.Review, o.Point, m.First_name, c.Colors_name
FROM orders o
JOIN bag_color bc ON o.Colors_code = bc.Colors_code
JOIN colors c ON bc.Colors_code = c.Colors_code
JOIN member m ON o.Member_id = m.Member_id
WHERE o.bag_id = '$ids'
ORDER BY c.Colors_name"; // เรียงลำดับตาม Colors_name

$review_result = $conn->query($review_sql);
$points_per_color = []; // อาร์เรย์ที่ใช้สำหรับเก็บคะแนนแยกตามสี

// วนรอบครั้งแรกเพื่อรวบรวมความคิดเห็นสำหรับแต่ละสี
if ($review_result->num_rows > 0) {
    while ($review = $review_result->fetch_assoc()) {
        $color = $review["Colors_name"];
        $points_per_color[$color][] = [
            'Review' => $review["Review"],
            'Point' => $review["Point"],
            'First_name' => $review["First_name"],
        ];
    }
}

// เตรียม HTML สำหรับแสดงค่าเฉลี่ยของคะแนน
$average_points_html = ''; // เก็บ HTML ของคะแนนเฉลี่ย
foreach ($points_per_color as $color => $reviews) {
     // กำหนดตัวแปรสำหรับคำนวณค่าเฉลี่ย
    $total_points = 0;
    $total_reviews = 0;

    // ตรวจสอบความคิดเห็นเพื่อคำนวณค่าเฉลี่ย
    foreach ($reviews as $review) {
        if (!empty($review['Point'])) {
            $total_points += (int)$review['Point'];
            $total_reviews++; // เพิ่มจำนวนรีวิวสำหรับแต่ละคะแนน
        }
    }

// คำนวณค่าเฉลี่ยของคะแนน
$average_points = $total_reviews > 0 ? ($total_points / $total_reviews) : 0;
$average_points = number_format($average_points, 1); // ปัดเศษค่าเฉลี่ยเป็นทศนิยม 1 ตำแหน่ง
$full_stars = floor($average_points); // Full stars


  // สร้าง HTML สำหรับคะแนนเฉลี่ย
$average_points_html .= '<div style="display: flex; align-items: center; padding-bottom: 10px;">';
$average_points_html .= '<strong style="margin-right: 3px;">' . htmlspecialchars($color) . '</strong>';

for ($i = 1; $i <= 5; $i++) {
    if ($i <= $full_stars) {
        $average_points_html .= '<span class="star filled">&#9733;</span>'; // ดาวเต็ม
    
    } else {
        $average_points_html .= '<span class="star">&#9734;</span>'; //  ดาวว่าง
    }
}
$average_points_html .= '<span style="margin-left: 5px;">(' . $average_points . ')</span>'; // แสดงคะแนนทศนิยม
$average_points_html .= '</div>';



}

// แสดงคะแนนเฉลี่ยและความคิดเห็น โดยจัดให้อยู่ข้างกัน
echo '<div style="display: flex; justify-content: space-between; margin-left: 25%;">';
echo '<div style="width: 30%;">' . $average_points_html . '</div>'; //  คอนเทนเนอร์ของคะแนนเฉลี่ย
?>
<div class="reviews-container" style="margin-right: 30%; border: 1px solid black; padding: 10px; border-radius: 5px; width: 100%;">
    <?php
    foreach ($points_per_color as $color => $reviews) {
        $has_review = false;
        $has_point_only = false;

        $displayed_reviews = [];
        $displayed_points = []; // ปรับเพื่อเก็บชื่อผู้ใช้และคะแนน

        foreach ($reviews as $review) {
            if (!empty($review['Review'])) {
                $has_review = true;// ตรวจสอบว่ามีความคิดเห็นหรือไม่
            } elseif (!empty($review['Point'])) {
                $has_point_only = true;// ตรวจสอบว่ามีคะแนนเท่านั้นหรือไม่
            }
        }

        if ($has_review || $has_point_only) {
            echo '<div style="margin-left: 5%; border-bottom: 1px solid black; padding-bottom: 10px; margin-bottom: 10px;">';
            echo '<h4 style="background-color: #fff; padding: 5px; border-radius: 5px;">สี: ' . htmlspecialchars($color) . '</h4>';

            foreach ($reviews as $review) {
                $points = (int)$review['Point'];
                $user_point_key = $review["First_name"] . '-' . $points; // สร้างคีย์ที่ไม่ซ้ำสำหรับผู้ใช้แต่ละคนและคะแนน


                if (!in_array($user_point_key, $displayed_points) && $points > 0) {
                    echo '<p><em>คะแนน: </em>';
                    for ($i = 1; $i <= 5; $i++) {
                        if ($i <= $points) {
                            echo '<span class="star filled">&#9733;</span>';
                        } else {
                            echo '<span class="star">&#9734;</span>';
                        }
                    }
                    echo '</p>';
                    $displayed_points[] = $user_point_key; // เก็บคีย์ที่ไม่ซ้ำของผู้ใช้-คะแนน
                }

                if (!empty($review['Review'])) {
                    $review_key = $review["Review"] . '-' . $review["First_name"];// สร้างคีย์ที่ไม่ซ้ำสำหรับความคิดเห็น
                    if (!in_array($review_key, $displayed_reviews)) {
                        echo '<p>ผู้ใช้: ' . htmlspecialchars($review["First_name"]) . '</p>';
                        echo '<div class="review-item" style=" padding-bottom: 10px;">';
                        echo '<p>' . htmlspecialchars($review["Review"]) . '</p>';
                        echo '</div>';
                        $displayed_reviews[] = $review_key;// เก็บคีย์ที่ไม่ซ้ำของความคิดเห็น
                    }
                } elseif (!empty($review['Point']) && empty($review['Review'])) {
                    if (!in_array($review["First_name"], $displayed_reviews)) {
                        echo '<p>ผู้ใช้: ' . htmlspecialchars($review["First_name"]) . '</p>';
                        $displayed_reviews[] = $review["First_name"]; // เก็บชื่อผู้ใช้ที่ไม่ซ้ำ
                    }
                }
            }
            echo '</div>';
        }
    }

    if (empty($points_per_color)) {
        echo '<p>ยังไม่มีความคิดเห็นหรือคะแนนสำหรับสินค้า.</p>';// แสดงข้อความเมื่อไม่มีความคิดเห็นหรือคะแนน
    }
    ?>
</div>


<?php
echo '</div>'; 
?>





    <?php
$conn->close();
?>
</body>

</html>