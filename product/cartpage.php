<?php
session_start();

// การเชื่อมต่อฐานข้อมูล
$conn = new mysqli('localhost', 'root', '', 'dbonline_bag');
$conn->set_charset('utf8mb4');

if ($conn->connect_error) {
    die("การเชื่อมต่อล้มเหลว: " . $conn->connect_error);
}

// ตรวจสอบว่าตะกร้าสินค้าว่างหรือไม่
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo '<div style="text-align: center; margin-top: 50px;">';
    echo '<p style="font-size: 24px; font-weight: bold;">ตะกร้าสินค้าของคุณว่างเปล่า.</p>';
    echo '<form action="../index.php" method="get">';
    echo '<button type="submit" class="quantity-btn">เลือกสินค้าใหม่</button>';
    echo '</form>';
    echo '</div>';
    exit();
}

// ฟังก์ชันคำนวณราคารวมและจำนวนรวม
function calculateTotals($cart) {
    $totals = ['Price_Order' => 0, 'Quantity_totol' => 0];
    foreach ($cart as $item) {
        $totals['Price_Order'] += $item['price'] * $item['Quantity'];
        $totals['Quantity_totol'] += $item['Quantity'];
    }
    return $totals;
}

$totals = calculateTotals($_SESSION['cart']);


?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตะกร้าสินค้า</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" referrerpolicy="no-referrer" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jaro:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=K2D:wght@400;600&display=swap" rel="stylesheet">
    <link href='https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="cartpagetwo.css">
</head>
<body>

<nav class="navbar">

<div class="nav-back" onclick="location.href='../index.php'" style="cursor: pointer;">
        <i class="fa fa-home"></i>
    </div>

<h2 class="bag-heading" onclick="location.href='../index.php'" style="cursor: pointer;  ">Bag Collective</h2>

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
                <?php echo isset($_SESSION['cartItemCount']) ? htmlspecialchars($_SESSION['cartItemCount']) : 0; ?>
            </span>
        </a>
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
        <h1>ตะกร้าสินค้า</h1>
        <?php if (isset($_SESSION['cart_message'])): ?>
            <div class="cart-message">
                <?php
                echo htmlspecialchars($_SESSION['cart_message']);
                unset($_SESSION['cart_message']);
                ?>
            </div>
        <?php endif; ?>
    </header>

    <main>
        <table class="cart-table">
            <thead>
                <tr>
                    <th>รูปภาพ</th>
                    <th>ประเภท/ยี่ห้อ/วัสดุ</th>
                    <th>สี</th>
                    <th>จำนวน</th>
                    <th>ราคา</th>
                    <th>ราคารวม</th>
                    <th>จัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($_SESSION['cart'] as $index => $item): ?>
                    <tr>
                        <td>
                            <?php
                            $images = explode(',', $item['B_imgs']);
                            $first_image = htmlspecialchars($images[0]);
                            ?>
                            <img src="../<?php echo $first_image; ?>" class="small-image">
                        </td>
                        <td><?php echo htmlspecialchars($item['type_name'], ENT_QUOTES, 'UTF-8'); ?><br>
                        <?php echo htmlspecialchars($item['brand_name'], ENT_QUOTES, 'UTF-8'); ?><br>
                        <?php echo htmlspecialchars($item['material_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?php echo htmlspecialchars($item['Colors_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td class="quantity-container">
                            <div class="quantity-wrapper">
                                <form action="cart_action.php" method="post" class="cart-form">
                                    <input type="hidden" name="action" value="decrease">
                                    <input type="hidden" name="bag_id" value="<?php echo htmlspecialchars($item['bag_id']); ?>">
                                    <input type="hidden" name="Colors_name" value="<?php echo htmlspecialchars($item['Colors_name']); ?>">
                                    <button type="submit" class="quantity-btn">-</button>
                                </form>
                                <input type="number" name="Quantity" value="<?php echo htmlspecialchars($item['Quantity']); ?>" min="1" readonly class="quantity-input">
                                <form action="cart_action.php" method="post" class="cart-form">
                                    <input type="hidden" name="action" value="increase">
                                    <input type="hidden" name="bag_id" value="<?php echo htmlspecialchars($item['bag_id']); ?>">
                                    <input type="hidden" name="Colors_name" value="<?php echo htmlspecialchars($item['Colors_name']); ?>">
                                    <button type="submit" class="quantity-btn">+</button>
                                </form>
                            </div>
                        </td>
                        <td><?php echo number_format($item['price'], 2); ?></td>
                        <td><?php echo number_format($item['price'] * $item['Quantity'], 2); ?></td>
                        <td><a href="cart_action.php?remove=<?php echo $index; ?>" class="remove-link">ลบ</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5">จำนวนทั้งหมด:</td>
                    <td><?php echo htmlspecialchars($totals['Quantity_totol']); ?></td>
                    <td colspan="1">ใบ</td>
                </tr>
                <tr>
                    <td colspan="5">ราคารวมทั้งหมด:</td>
                    <td><?php echo number_format($totals['Price_Order'], 2); ?></td>
                    <td colspan="1">บาท</td>
                </tr>
            </tfoot>
        </table>
        <br>
        <div class="buy-now-container">
    <form action="buyorder.php" method="post">
        <input type="hidden" name="Price_Order" value="<?php echo number_format($totals['Price_Order'], 2); ?>">
        <input type="hidden" name="Quantity_totol" value="<?php echo $totals['Quantity_totol']; ?>">
        <?php foreach ($_SESSION['cart'] as $index => $item): ?>
            <input type="hidden" name="items[<?php echo $index; ?>][bag_id]" value="<?php echo htmlspecialchars($item['bag_id']); ?>">
            <input type="hidden" name="items[<?php echo $index; ?>][Colors_code]" value="<?php echo htmlspecialchars($item['Colors_code']); ?>">
            <input type="hidden" name="items[<?php echo $index; ?>][first_image]" value="<?php echo htmlspecialchars($item['B_imgs']); ?>">
            <input type="hidden" name="items[<?php echo $index; ?>][type_name]" value="<?php echo htmlspecialchars($item['type_name']); ?>">
            <input type="hidden" name="items[<?php echo $index; ?>][brand_name]" value="<?php echo htmlspecialchars($item['brand_name']); ?>">
            <input type="hidden" name="items[<?php echo $index; ?>][material_name]" value="<?php echo htmlspecialchars($item['material_name']); ?>">
            <input type="hidden" name="items[<?php echo $index; ?>][Colors_name]" value="<?php echo htmlspecialchars($item['Colors_name']); ?>">
            <input type="hidden" name="items[<?php echo $index; ?>][Quantity]" value="<?php echo htmlspecialchars($item['Quantity']); ?>">
            <input type="hidden" name="items[<?php echo $index; ?>][price]" value="<?php echo number_format($item['price'], 2); ?>">
            <input type="hidden" name="items[<?php echo $index; ?>][Price_Order]" value="<?php echo number_format($item['price'] * $item['Quantity'], 2); ?>">
        <?php endforeach; ?>
        <button type="submit" class="btn-buy-now">ซื้อเลย  <?php echo number_format($totals['Price_Order'], 2); ?></button>
    </form>
</div>

    </main>

    <footer>
        <a href="../index.php" class="continue-shopping">สินค้าเพิ่มเติม</a>
    </footer>
</div>

<style>
    
       .bag-heading{
        font-size: 45px;
        margin-right: 68%;
        color: white;  
        font-family: 'Jaro', sans-serif;
    }
    .buy-now-container {
        text-align: right;
        margin-top: 20px;
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
    .btn-buy-now {
        background-color: #28a745;
        color: #fff;
        border: none;
        padding: 10px 20px;
        font-size: 16px;
        cursor: pointer;
        text-align: center;
        display: inline-block;
        margin: 10px;
    }

    .btn-buy-now:hover {
        background-color: #209E3C;
    }

    .small-image {
        width: 100px; /* ปรับขนาดตามต้องการ */
        height: auto;
    }

    .quantity-wrapper {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .cart-form {
        display: flex;
        align-items: center;
    }

    .quantity-btn {
        background-color: #f8f9fa;
        border: 1px solid #ced4da;
        color: #212529;
        padding: 5px 10px;
        font-size: 18px;
        cursor: pointer;
        transition: background-color 0.3s ease;
        margin: 0 5px;
        border-radius: 4px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .quantity-btn:hover {
        background-color: #e2e6ea;
    }

    .quantity-input {
        width: 50px;
        text-align: center;
        border: 1px solid #ced4da;
        padding: 5px;
        font-size: 16px;
        border-radius: 4px;
    }
</style>
</body>
</html>
