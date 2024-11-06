<?php
include('../connetDB/con_db.php'); // รวมการเชื่อมต่อฐานข้อมูล

if (isset($_GET['color'])) {
    $color = $_GET['color'];
    
    $query = "SELECT * FROM products WHERE Colors_name = '$color'";
    $query_run = mysqli_query($conn, $query);

    if (mysqli_num_rows($query_run) > 0) {
        while ($product = mysqli_fetch_assoc($query_run)) {
            echo "<div class='product'>";
            echo "<img src='" . $product['B_img'] . "' alt='" . $product['brand_name'] . "'>";
            echo "<p>Brand: " . $product['brand_name'] . "</p>";
            echo "<p>Price: " . $product['Price'] . "</p>";
            echo "</div>";
        }
    } else {
        echo "No products found for the selected color.";
    }
} else {
    echo "Color not specified.";
}
?>
