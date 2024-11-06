<?php
include '../connetDB/con_db.php';

if (isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];

    $sql = "SELECT 
    o.Order_id, 
    o.Order_date, 
    o.Order_time, 
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

      // ดึงผลลัพธ์ก่อนที่จะแสดงผล
    if ($row = $result->fetch_assoc()) {
        $current_status_id = $row['Status_id'];
        echo "<h3>ที่อยู่จัดส่ง</h3>" . 
             "<p>" . htmlspecialchars($row['Title_name'], ENT_QUOTES, 'UTF-8') . " " . 
             htmlspecialchars($row['First_name'], ENT_QUOTES, 'UTF-8') . " " . 
             htmlspecialchars($row['Last_name'], ENT_QUOTES, 'UTF-8') . "<br>" . 
             htmlspecialchars($row['H_number'], ENT_QUOTES, 'UTF-8') . " " . 
             htmlspecialchars($row['Road'], ENT_QUOTES, 'UTF-8') . " " . 
             htmlspecialchars($row['Alley'], ENT_QUOTES, 'UTF-8') . "" . 
             htmlspecialchars($row['Subdistrict_name'], ENT_QUOTES, 'UTF-8') . " " . 
             htmlspecialchars($row['District_name'], ENT_QUOTES, 'UTF-8') . " " . 
             htmlspecialchars($row['Province_name'], ENT_QUOTES, 'UTF-8') . " " . 
             htmlspecialchars($row['Postcode'], ENT_QUOTES, 'UTF-8') . "<br>" . 
             htmlspecialchars($row['Phone_number'], ENT_QUOTES, 'UTF-8') . "</p>";

       // แสดงข้อมูลสินค้า
        echo "<h3>ข้อมูลสินค้า</h3>";
        echo "<table class='cart-table' style='width: 100%; border-collapse: collapse;'>
                <thead>
                    <tr style='background-color: #f4f4f4;'>
                        <th style='padding: 10px; border: 1px solid #ddd;'>สินค้า</th>
                        <th style='padding: 10px; border: 1px solid #ddd;'>รายละเอียด</th>
                        <th style='padding: 10px; border: 1px solid #ddd;'>ราคา</th>
                        <th style='padding: 10px; border: 1px solid #ddd;'>จำนวน</th>
                        <th style='padding: 10px; border: 1px solid #ddd;'>ราคารวม</th>
                    </tr>
                </thead>
                <tbody>";

       // ประมวลผลแต่ละแถวและรวมยอดรวม
        do {
            // คำนวณรวมทั้งหมด
            $grand_total_quantity += $row['Quantity'];
            $grand_total_price += $row['Price_Order'];

            echo "<tr>
                    <td style='text-align: center;'>";
            
                    if (!empty($row['B_img'])) {
                        // แยกรูปภาพออกจากกันและเลือกเฉพาะรูปแรก
                        $images = explode(',', $row['B_img']);
                        $first_image = $images[0];
                        
                        // แสดงรูปภาพแรกของสินค้า
                        echo "<img src='../" . htmlspecialchars($first_image, ENT_QUOTES, 'UTF-8') . "' alt='Product Image' style='width: 100px; height: auto;'>";
                    } else {
                        echo "No Image";
                    }
                    
            
            echo "</td>
                  <td>" . htmlspecialchars($row['type_name'], ENT_QUOTES, 'UTF-8') . "<br>" .
                  htmlspecialchars($row['brand_name'], ENT_QUOTES, 'UTF-8') . "<br>" .
                  htmlspecialchars($row['Material_name'], ENT_QUOTES, 'UTF-8') . "<br>" .
                  htmlspecialchars($row['Colors_name'], ENT_QUOTES, 'UTF-8') . "</td>
                  <td style='text-align: center;'>" . number_format($row['Price'], 2) . " บาท</td>
                  <td style='text-align: center;'>" . htmlspecialchars($row['Quantity'], ENT_QUOTES, 'UTF-8') . "</td>
                  <td style='text-align: center;'>" . number_format($row['Price_Order'], 2) . " บาท</td>
                  </tr>";
        } while ($row = $result->fetch_assoc());

        echo "</tbody></table>";
        
        // Display grand totals
        // echo "<p style='margin-top: 2%; margin-left: 30%'>จำนวนทั้งหมด: " . htmlspecialchars($grand_total_quantity, ENT_QUOTES, 'UTF-8') . "</p>";
        // echo "<p style='margin-top: 2%; margin-left: 30%'>ยอดรวมทั้งหมด: " . number_format($grand_total_price, 2) . " บาท</p>";

        // Reset the pointer to the beginning of the result set
        $result->data_seek(0);

        // Display order id and order date below the product details
        if ($row = $result->fetch_assoc()) {
            echo "<h3 style=' margin-left: 20%'>เลขที่คำสั่งซื้อ: " . htmlspecialchars($row['Order_id'], ENT_QUOTES, 'UTF-8') . "</h3>";
            echo "<p style=' margin-left: 20%'>วันที่สั่งซื้อ: " . htmlspecialchars($row['Order_date'], ENT_QUOTES, 'UTF-8') . " เวลา: " . htmlspecialchars($row['Order_time'], ENT_QUOTES, 'UTF-8') . "</p>";
            echo "<p style=' margin-left: 20%'>จำนวนทั้งหมด: " . htmlspecialchars($grand_total_quantity, ENT_QUOTES, 'UTF-8') . " ใบ</p>";
            echo "<p style=' margin-left: 20%'>ยอดรวมทั้งหมด: " . number_format($grand_total_price, 2) . " บาท</p>";
            echo "<p style=' margin-left: 20%'>สถานะคำสั่งซื้อ: " . htmlspecialchars($row['Status_name'], ENT_QUOTES, 'UTF-8') . "</p>";
            echo "<br >";
             echo "<br >";
        }

        if (!empty($row['Receipt_Images'])) {
            $receipt_images = explode(', ', $row['Receipt_Images']);
            foreach ($receipt_images as $image) {
                echo "<img src='../pay/" . htmlspecialchars($image, ENT_QUOTES, 'UTF-8') . "' alt='Receipt Image' style='width: 25%; height: auto; margin-top: -15%;margin-left: 70%;'>";
                echo "<br><br>";
            }
        } else {
            echo "<p>No Receipt Images</p>";
        }

        echo "<form action='order_check.php' method='POST' class='search-form' style=' padding: 15px; width: 300px;margin-left: -5%'>
            <div class='form-row' style='margin-bottom: 10px;'>
                <label for='Status_id' style='font-weight: bold; display: block; margin-bottom: 5px;margin-left: 50%;margin-top: -50%;'>สถานะการจัดส่ง</label>
                <select name='Status_id' id='Status_id' class='form-select' style=' padding: 8px;margin-left: 50%'>
                 <option value=''></option>
                    <option value=''></option>
                <option value='3'" . ($current_status_id == '3' ? ' selected' : '') . ">คำสั่งซื้อสำเร็จ รอการจัดส่ง</option>
                <option value='4'" . ($current_status_id == '4' ? ' selected' : '') . ">ยกเลิกคำสั่งซื้อ เนื่องจากการชำระเงินไม่ถูกต้อง</option>
                <option value='5'" . ($current_status_id == '5' ? ' selected' : '') . ">จัดส่งแล้ว</option>
                </select>
            </div>
            
            <div class='form-row' style='margin-bottom: 10px;margin-left: -60.4%;margin-top: -50%;' id='tracking' >
                <label for='Tracking' style='font-weight: bold; display: block; margin-bottom: 5px;'>Tracking<span class='required-mark' style='color:red;'>*</span></label>
                <input type='text' name='Tracking' id='Tracking' class='form-input' placeholder='กรอกเลขพัสดุ' style='padding: 8px;width: 325px;'>
            </div><br><br>
            
            <input type='hidden' name='Order_id' value='" . htmlspecialchars($row['Order_id'], ENT_QUOTES, 'UTF-8') . "'>
            <button type='submit' class='submit-btn'style=' padding: 8px;margin-left: -25%;'>ยืนยัน</button>
        </form>";

        // echo "<script>
        //     document.getElementById('Status_id').addEventListener('change', function() {
        //         var trackingField = document.getElementById('tracking');
        //         if (this.value === '3') {
        //             trackingField.style.display = 'block';
        //         } else {
        //             trackingField.style.display = 'none';
        //         }
        //     });
        // </script>";
    } else {
        echo "ไม่พบคำสั่งซื้อที่ระบุ";
    }

    $stmt->close();
} else {
    echo "ไม่ได้ระบุ Order ID";
}


?>
