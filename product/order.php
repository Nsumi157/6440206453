
<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'dbonline_bag');
$conn->set_charset('utf8mb4');

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("การเชื่อมต่อล้มเหลว: " . $conn->connect_error);
}



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $receipt_code = htmlspecialchars($_POST['receipt_code'], ENT_QUOTES, 'UTF-8');
    $items = $_POST['items'];
    $total_quantity = htmlspecialchars($_POST['total_quantity'], ENT_QUOTES, 'UTF-8');
    $total_price = htmlspecialchars($_POST['total_price'], ENT_QUOTES, 'UTF-8');
    ?>

    <div class="receipt" style="text-align: center; margin-right: 40px;">
        <p>รหัสใบเสร็จ: <?php echo $receipt_code; ?></p>
    </div>

    <?php foreach ($items as $item): ?>
    <div class="bag-container">
        <div class="bag-image">
            <img src="../<?php echo htmlspecialchars($item['first_image'], ENT_QUOTES, 'UTF-8'); ?>" class="small-image">
        </div>
        <!-- <div class="bag-details">
            <p><?php echo htmlspecialchars($item['type_name'], ENT_QUOTES, 'UTF-8') . ' / ' .
                    htmlspecialchars($item['brand_name'], ENT_QUOTES, 'UTF-8') . ' / ' .
                    htmlspecialchars($item['material_name'], ENT_QUOTES, 'UTF-8'); ?></p>
            <p>สี: <?php echo htmlspecialchars($item['selected_color'], ENT_QUOTES, 'UTF-8'); ?></p>
            <p>ราคา: <?php echo htmlspecialchars($item['price'], ENT_QUOTES, 'UTF-8'); ?></p>
            <div class="totalprice">
                <p>จำนวน: <?php echo htmlspecialchars($item['quantity'], ENT_QUOTES, 'UTF-8'); ?></p>
                <p>รวม: <?php echo htmlspecialchars($item['totalPrice'], ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
        </div> -->
    </div>
    <?php endforeach; ?>

    <div class="total-order">
        <h2>รวมทั้งหมด</h2>
        <p>จำนวนสินค้า : <?php echo $total_quantity; ?> ชิ้น</p>
        <p>ราคารวมทั้งหมด: <?php echo $total_price; ?></p>
    </div>

    <?php
} else {
    echo "No order data received.";
}
?>
