<?php
include '../connetDB/con_db.php';

if (isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];

    $sql = "SELECT 
        o.Order_id, 
        o.Order_date, 
        o.Order_time, 
        mb.Member_id,  -- เพิ่ม Member_id ในการเลือกข้อมูล
        mb.Title_name, 
        mb.First_name, 
        mb.Last_name, 
        mb.H_number, 
        mb.Road, 
        mb.Alley,
        sd.Subdistrict_name,
        sd.Postcode,
        dt.District_name,
        pv.Province_name,
        tl.Phone_number,
        p.B_img, 
        o.Quantity, 
        o.Price_Order, 
        bt.type_name, 
        bb.brand_name, 
        m.Material_name, 
        c.Colors_name, 
        b.Price,
        GROUP_CONCAT(DISTINCT r.Receipt_img SEPARATOR ', ') AS Receipt_Images,
        GROUP_CONCAT(DISTINCT p.B_img ORDER BY p.B_img ASC SEPARATOR ', ') AS B_img,
        GROUP_CONCAT(DISTINCT r.NamRec SEPARATOR ', ') AS Receipt_Names,
        s.Status_name,
        s.Status_id,
        b.bag_id,
        r.Receipt_id,
        r.Receipt_date,
        o.Colors_code,
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
    INNER JOIN member mb ON o.Member_id = mb.Member_id
    INNER JOIN subdistrict sd ON mb.Subdistrict_id = sd.Subdistrict_id
    INNER JOIN district dt ON sd.District_id = dt.District_id
    INNER JOIN province pv ON dt.Province_id = pv.Province_id
    INNER JOIN telephone tl ON mb.Member_id = tl.Member_id
    WHERE o.Order_id = ?
    GROUP BY o.Order_id, b.bag_id, o.Colors_code, o.Quantity, o.Price_Order";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();


    
    // เริ่มต้นรวมทั้งหมด
    $grand_total_quantity = 0;
    $grand_total_price = 0;
    

    echo "<h1 class='receipt' style='text-align: center;'>ใบเสร็จ</h1>";
echo "<br>";

if ($row = $result->fetch_assoc()) {
    // แสดงข้อมูลสมาชิก
    echo "<div style='display: flex; justify-content: space-between; margin-left: 5%; margin-right: 5%;'>";
    echo "<div style='flex: 1;'>";
    // echo "<p>อีเมล: " . htmlspecialchars($row['Member_id'], ENT_QUOTES, 'UTF-8') . "</p>";
    echo "<p>เลขที่คำสั่งซื้อ: " . htmlspecialchars($row['Order_id'], ENT_QUOTES, 'UTF-8') . "</p>";
  
    echo "<p>ชื่อ: " . htmlspecialchars($row['Title_name'], ENT_QUOTES, 'UTF-8') . " " . 
         htmlspecialchars($row['First_name'], ENT_QUOTES, 'UTF-8') . " " . 
         htmlspecialchars($row['Last_name'], ENT_QUOTES, 'UTF-8') . "</p>";
    echo "<p>ที่อยู่: " . htmlspecialchars($row['H_number'], ENT_QUOTES, 'UTF-8') . " " . 
         htmlspecialchars($row['Road'], ENT_QUOTES, 'UTF-8') . " " . 
         htmlspecialchars($row['Alley'], ENT_QUOTES, 'UTF-8') . " " . 
         htmlspecialchars($row['Subdistrict_name'], ENT_QUOTES, 'UTF-8') . " " . 
         htmlspecialchars($row['District_name'], ENT_QUOTES, 'UTF-8') . " " . 
         htmlspecialchars($row['Province_name'], ENT_QUOTES, 'UTF-8') . " " . 
         htmlspecialchars($row['Postcode'], ENT_QUOTES, 'UTF-8') . "</p>";
    echo "<p>เบอร์โทรศัพท์: " . htmlspecialchars($row['Phone_number'], ENT_QUOTES, 'UTF-8') . "</p>";
    echo "</div>";
    
    echo "<div style='text-align: right;'>";
    echo "<p style='text-align: right;'>เลขที่ใบเสร็จ: " . htmlspecialchars($row['Receipt_id'], ENT_QUOTES, 'UTF-8') . "<br>";
    echo "วันที่ออกใบเสร็จ: " . htmlspecialchars($row['Receipt_date'], ENT_QUOTES, 'UTF-8') . "</p>";
    echo "</div>";
    echo "</div>";
}

 

    // แสดงตารางสินค้า
echo "<table class='cart-table' style='width: 100%; border-collapse: collapse;'>
<thead>
    <tr style='background-color: #f4f4f4;'>
        <th style='padding: 10px; border: 1px solid #ddd;'>รายละเอียด</th>
        <th style='padding: 10px; border: 1px solid #ddd;'>ราคา/ชิ้น</th>
        <th style='padding: 10px; border: 1px solid #ddd;'>จำนวน</th>
        <th style='padding: 10px; border: 1px solid #ddd;'>จำนวนเงิน</th>
    </tr>
</thead>
<tbody>";


      // ประมวลผลแต่ละแถวและรวมยอดรวม
do {
    $grand_total_quantity += $row['Quantity'];
    $grand_total_price += $row['Price_Order'];

    echo "<tr>
          <td>" . htmlspecialchars($row['type_name'], ENT_QUOTES, 'UTF-8') . "/" .
          htmlspecialchars($row['brand_name'], ENT_QUOTES, 'UTF-8') . "/" .
          htmlspecialchars($row['Material_name'], ENT_QUOTES, 'UTF-8') . "/" .
          htmlspecialchars($row['Colors_name'], ENT_QUOTES, 'UTF-8') . "</td>
          <td style='text-align: center;'>" . number_format($row['Price'], 2) . " </td>
          <td style='text-align: center;'>" . htmlspecialchars($row['Quantity'], ENT_QUOTES, 'UTF-8') . "</td>
          <td style='text-align: center;'>" . number_format($row['Price_Order'], 2) . " </td>
          </tr>";
} while ($row = $result->fetch_assoc());

echo "</tbody></table>";
 // แสดงผลค่ารวม
echo "<p style='margin-left: 75%'>จำนวนทั้งหมด: " . htmlspecialchars($grand_total_quantity, ENT_QUOTES, 'UTF-8') . " ใบ</p>";
echo "<p style='margin-left: 75%'>ยอดรวมทั้งหมด: " . number_format($grand_total_price, 2) . " บาท</p>";
echo "<br>";
echo "<br>";


echo "<div style='text-align: center; margin-top: 20px;'>
    <button onclick='window.print()' style='padding: 10px 20px; background-color: #4CAF50; color: white; border: none; border-radius: 5px; cursor: pointer;'>พิมพ์ใบเสร็จ</button>
</div>";



            
    //         <input type='hidden' name='Order_id' value='" . htmlspecialchars($row['Order_id'], ENT_QUOTES, 'UTF-8') . "'>
    //         <button type='submit' class='submit-btn'style=' padding: 8px;margin-left: -25%;'>ยืนยัน</button>
    //     </form>";

    //     // echo "<script>
    //     //     document.getElementById('Status_id').addEventListener('change', function() {
    //     //         var trackingField = document.getElementById('tracking');
    //     //         if (this.value === '3') {
    //     //             trackingField.style.display = 'block';
    //     //         } else {
    //     //             trackingField.style.display = 'none';
    //     //         }
    //     //     });
    //     // </script>";
    // } else {
    //     echo "ไม่พบคำสั่งซื้อที่ระบุ";
    // }

    $stmt->close();
} else {
    echo "ไม่ได้ระบุ Order ID";
}
?>
