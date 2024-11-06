<?php
session_start();
include '../connetDB/con_db.php';

$currentMonth = date('m'); // เดือนปัจจุบันในรูปแบบ 01, 02, ..., 12
$currentYear = date('Y');  // ปีปัจจุบันในรูปแบบ 4 หลัก

$salesData = [];
$labels = [];
$salesAmounts = [];

// ตรวจสอบว่ามีการกดปุ่ม "ค้นหา" หรือไม่
if (isset($_POST['search'])) {
    $selectedMonth = $_POST['month'];
    $selectedYear = $_POST['year'];
} else {
    // ใช้เดือนและปีปัจจุบันถ้าไม่ได้กดปุ่ม "ค้นหา"
    $selectedMonth = $currentMonth;
    $selectedYear = $currentYear;
}

// คำสั่ง SQL เพื่อกรองตามเดือนและปีที่เลือกหรือปัจจุบัน
$sql = "SELECT bb.brand_name, c.Colors_name, SUM(o.Quantity) AS total_sales, SUM(o.Price_Order) AS total_revenue
    FROM bag b
    JOIN orders o ON o.Bag_id = b.Bag_id
    JOIN bag_color bc ON o.Bag_id = bc.Bag_id AND o.Colors_code = bc.Colors_code
    JOIN colors c ON bc.Colors_code = c.Colors_code
    JOIN status s ON s.Status_id = o.Status_id
    JOIN bag_brand bb ON b.brand_id = bb.brand_id
    WHERE MONTH(o.Order_date) = ?
    AND YEAR(o.Order_date) = ?
    AND (s.Status_id = '5' OR s.Status_id = '3')
    GROUP BY bb.brand_name, c.Colors_name
    ORDER BY total_sales DESC
    LIMIT 4";

// เตรียมและผูกค่าพารามิเตอร์
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $selectedMonth, $selectedYear);
$stmt->execute();
$result = $stmt->get_result();

// เก็บข้อมูลสำหรับแสดงในตาราง
$tableData = []; // ใช้เก็บข้อมูลสำหรับตาราง
while ($row = $result->fetch_assoc()) {
    $tableData[] = $row;
    $tableLabels[] = $row['brand_name'] . ' (' . $row['Colors_name'] . ')';
    $tableSalesAmounts[] = $row['total_sales'];
}

// คำสั่ง SQL สำหรับกราฟ
$sqlForGraph = "SELECT bb.brand_name, SUM(o.Price_Order) AS total_revenue,SUM(o.Quantity) AS Quantity
    FROM bag b
    JOIN orders o ON o.Bag_id = b.Bag_id
    JOIN status s ON s.Status_id = o.Status_id
    JOIN bag_brand bb ON b.brand_id = bb.brand_id
    WHERE MONTH(o.Order_date) = ?
    AND YEAR(o.Order_date) = ?
    AND (s.Status_id = '5' OR s.Status_id = '3')
    GROUP BY bb.brand_name
    ORDER BY total_revenue DESC";

// เตรียมและผูกค่าพารามิเตอร์สำหรับกราฟ
$stmtForGraph = $conn->prepare($sqlForGraph);
$stmtForGraph->bind_param("ss", $selectedMonth, $selectedYear);
$stmtForGraph->execute();
$resultForGraph = $stmtForGraph->get_result();

// เก็บข้อมูลสำหรับกราฟ
$graphLabels = []; // ใช้เก็บข้อมูลสำหรับกราฟ
$revenueAmounts = [];
while ($rowForGraph = $resultForGraph->fetch_assoc()) {
    $graphLabels[] = $rowForGraph['brand_name'];
    $revenueAmounts[] = $rowForGraph['total_revenue'];
}

// คำนวณรายได้รวมทั้งหมด
$totalRevenue = array_sum($revenueAmounts);
?>


<!DOCTYPE html>
<html lang="th">

<head>
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- ไลบรารี Chart.js -->
</head>

<body>
    <div class="sidebar">
        <div class="user-profile">
            <i class="fas fa-user-circle"></i>
            <?php if (isset($_SESSION['First_name'])) : ?>
            <div class="nav-btn">
                <p class="user-name" style="font-size: 18px;">
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
                <a href="#" style="border: none; text-decoration: none;" onclick="confirmLogout()">ออกจากระบบ</a>
                <i class="fas fa-sign-out-alt"></i>
            </button>
        </div>

    </div>
    <script>
    function confirmLogout() {
        var confirmation = confirm("ต้องการออกจากระบบใช่หรือไม่?");
        if (confirmation) {
            window.location.href = "logout.php";
        }
    }
    </script>

    <div class="container">
        <h2 class="report">รายงานประจำเดือน</h2>

        <div class="search-container">
            <h3 class="bag">กระเป๋าขายดีประจำเดือน</h3>
            <form action="" method="POST" class="search-form">
                <select name="month" id="month">
                    <option value="01" <?php if (isset($selectedMonth) && $selectedMonth == '01') echo 'selected'; ?>>
                        มกราคม</option>
                    <option value="02" <?php if (isset($selectedMonth) && $selectedMonth == '02') echo 'selected'; ?>>
                        กุมภาพันธ์</option>
                    <option value="03" <?php if (isset($selectedMonth) && $selectedMonth == '03') echo 'selected'; ?>>
                        มีนาคม</option>
                    <option value="04" <?php if (isset($selectedMonth) && $selectedMonth == '04') echo 'selected'; ?>>
                        เมษายน</option>
                    <option value="05" <?php if (isset($selectedMonth) && $selectedMonth == '05') echo 'selected'; ?>>
                        พฤษภาคม</option>
                    <option value="06" <?php if (isset($selectedMonth) && $selectedMonth == '06') echo 'selected'; ?>>
                        มิถุนายน</option>
                    <option value="07" <?php if (isset($selectedMonth) && $selectedMonth == '07') echo 'selected'; ?>>
                        กรกฎาคม</option>
                    <option value="08" <?php if (isset($selectedMonth) && $selectedMonth == '08') echo 'selected'; ?>>
                        สิงหาคม</option>
                    <option value="09" <?php if (isset($selectedMonth) && $selectedMonth == '09') echo 'selected'; ?>>
                        กันยายน</option>
                    <option value="10" <?php if (isset($selectedMonth) && $selectedMonth == '10') echo 'selected'; ?>>
                        ตุลาคม</option>
                    <option value="11" <?php if (isset($selectedMonth) && $selectedMonth == '11') echo 'selected'; ?>>
                        พฤศจิกายน</option>
                    <option value="12" <?php if (isset($selectedMonth) && $selectedMonth == '12') echo 'selected'; ?>>
                        ธันวาคม</option>
                </select>

                <select name="year" id="year">
                    <?php
    $currentYear = date("Y") + 543; // ปีปัจจุบันในแบบพ.ศ.
    for ($i = $currentYear; $i >= ($currentYear - 10); $i--) {
        $selected = (isset($selectedYear) && ($selectedYear == ($i - 543))) ? 'selected' : ''; 
        echo "<option value='" . ($i - 543) . "' $selected>$i</option>";
    }
    ?>
                </select>

                <button type="submit" name="search">ค้นหา</button>
            </form>
            
        </div>

        <h2 style=" color: #333;margin-left:70%; color:#19CC56;">รายงานกระเป๋าขายดี</2>
        <h2 style=" color: #333; margin-left:70%;">เดือน:
                <?php 
    if (isset($selectedMonth) && isset($selectedYear)) {
        $months = [
            "01" => "มกราคม", "02" => "กุมภาพันธ์", "03" => "มีนาคม", "04" => "เมษายน", 
            "05" => "พฤษภาคม", "06" => "มิถุนายน", "07" => "กรกฎาคม", "08" => "สิงหาคม", 
            "09" => "กันยายน", "10" => "ตุลาคม", "11" => "พฤศจิกายน", "12" => "ธันวาคม"
        ];
        echo $months[$selectedMonth] . " " . ($selectedYear + 543); // เพิ่ม 543 เพื่อให้เป็นปี พ.ศ.
    }
?>
            </h2>
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>ลำดับ</th>
                        <th>ยี่ห้อ</th>
                        <th>สี</th>
                        <th>ยอดการขาย(จำนวน)</th>
                        <th>ยอดขายรวม(บาท)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
        if (isset($tableData) && count($tableData) > 0) {
            $index = 1;
            foreach ($tableData as $row) {
                echo "<tr>";
                echo "<td>" . $index++ . "</td>";
                echo "<td>" . htmlspecialchars($row['brand_name'], ENT_QUOTES, 'UTF-8') . "</td>";
                echo "<td>" . htmlspecialchars($row['Colors_name'], ENT_QUOTES, 'UTF-8') . "</td>";
                echo "<td>" . htmlspecialchars($row['total_sales'], ENT_QUOTES, 'UTF-8') . "</td>";
                echo "<td>" . number_format($row['total_revenue']) . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='5'>ไม่มีข้อมูลสินค้า</td></tr>";
        }
        ?>
                </tbody>
            </table>

            <br>
            <h3 class="bag">ยอดขายประจำเดือน</h3><br>
            <h2 style=" margin-left:73%; color:#19CC56;">ยอดขายของเดือน</h2>
           

            <table class="cart-table">
    <thead>
        <tr>
            <th>ลำดับ</th>
            <th>ยี่ห้อ</th>
            <th>ยอดขาย(จำนวน)</th>
            <th>ยอดขาย(บาท)</th>
        </tr>
    </thead>
    <tbody>
        <?php
        // คำสั่ง SQL สำหรับกราฟ
        $sqlForGraph = "SELECT bb.brand_name, SUM(o.Price_Order) AS total_revenue, SUM(o.Quantity) AS total_quantity
            FROM bag b
            JOIN orders o ON o.Bag_id = b.Bag_id
            JOIN status s ON s.Status_id = o.Status_id
            JOIN bag_brand bb ON b.brand_id = bb.brand_id
            WHERE MONTH(o.Order_date) = ?
            AND YEAR(o.Order_date) = ?
            AND (s.Status_id = '5' OR s.Status_id = '3')
            GROUP BY bb.brand_name
            ORDER BY total_revenue DESC";

        // เตรียมและผูกค่าพารามิเตอร์สำหรับกราฟ
        $stmtForGraph = $conn->prepare($sqlForGraph);
        $stmtForGraph->bind_param("ss", $selectedMonth, $selectedYear);
        $stmtForGraph->execute();
        $resultForGraph = $stmtForGraph->get_result();

        // เก็บข้อมูลสำหรับกราฟและตาราง
        $graphLabels = []; // ใช้เก็บข้อมูลสำหรับกราฟ
        $revenueAmounts = [];
        $tableData = []; // ใช้เก็บข้อมูลสำหรับตาราง
        $index = 1;
        $totalQuantity = 0; // ตัวแปรสำหรับเก็บจำนวนรวม
        $totalRevenue = 0; // ตัวแปรสำหรับเก็บราคาสรวม

        while ($rowForGraph = $resultForGraph->fetch_assoc()) {
            $graphLabels[] = $rowForGraph['brand_name'];
            $revenueAmounts[] = $rowForGraph['total_revenue'];
            $tableData[] = [
                'index' => $index++, // เก็บลำดับ
                'brand_name' => htmlspecialchars($rowForGraph['brand_name'], ENT_QUOTES, 'UTF-8'),
                'total_quantity' => $rowForGraph['total_quantity'], // ยอดขาย (จำนวน)
                'total_revenue' => number_format($rowForGraph['total_revenue']) // ยอดขาย (บาท)
            ];
            
            // รวมจำนวนและราคาของแต่ละยี่ห้อ
            $totalQuantity += $rowForGraph['total_quantity'];
            $totalRevenue += $rowForGraph['total_revenue'];
        }

        // แสดงข้อมูลในตาราง
        if (count($tableData) > 0) {
            foreach ($tableData as $row) {
                echo "<tr>";
                echo "<td>" . $row['index'] . "</td>";
                echo "<td>" . $row['brand_name'] . "</td>";
                echo "<td>" . $row['total_quantity'] . "</td>";
                echo "<td>" . $row['total_revenue'] . "</td>";
                echo "</tr>";
            }
            
            // แสดงบรรทัดรวม
            echo "<tr>";
            echo "<td colspan='2' style='text-align: center;'><strong>รวม</strong></td>";
            echo "<td><strong>" . $totalQuantity . "</strong></td>";
            echo "<td><strong>" . number_format($totalRevenue) . "</strong></td>";
            echo "</tr>";
        } else {
            echo "<tr><td colspan='4'>ไม่มีข้อมูลยอดขาย</td></tr>";
        }
        ?>
    </tbody>
</table>





            <div class="sales-summary"
                style=" margin-top: -10%; margin-left: 85%;width: 35%;  padding: 15px 20px; border-radius: 8px;    ">


                <!-- <div style="font-size: 18px; margin: 0; color: #28a745;padding: 20px 0;">
    <h4 style="color: #333; margin: 0; font-weight: normal;">
        ยอดขายทั้งหมด: <strong
            style="color: #28a745; font-size:20px;"><?php echo number_format($totalRevenue); ?> บาท     border: 1px solid #ccc;background-color: #f9f9f9; box-shadow: 0 2px 5px rgba(0,0,0,0.1); display: flex; flex-direction: column; align-items: flex-start;</strong>
    </h4>
</div> -->

                <!-- จำนวนขายทั้งหมด: <strong><?php echo number_format($total_sales); ?> ใบ</strong> -->


                <!-- <p style="font-size: 14px; margin-top: 5px; color: #666;">(ข้อมูลจากคำสั่งซื้อทั้งหมด)</p> -->
            </div>
            <br><br>
            <br>


<!-- ส่วนของกราฟ -->
<div class="chart-container" style="display: flex; align-items: flex-start;">
    <canvas id="revenueChart" width="600" height="400" style="margin: auto;"></canvas>
</div>

<?php
// คำสั่ง SQL สำหรับกราฟยอดขาย
$sqlForRevenueGraph = "SELECT DATE(o.Order_date) AS order_date, SUM(o.Price_Order) AS total_revenue
    FROM orders o
    JOIN status s ON s.Status_id = o.Status_id
    WHERE MONTH(o.Order_date) = ?
    AND YEAR(o.Order_date) = ?
    AND (s.Status_id = '5' OR s.Status_id = '3')
    GROUP BY order_date
    ORDER BY order_date";

// เตรียมและผูกค่าพารามิเตอร์สำหรับกราฟ
$stmtForRevenueGraph = $conn->prepare($sqlForRevenueGraph);
$stmtForRevenueGraph->bind_param("ss", $selectedMonth, $selectedYear);
$stmtForRevenueGraph->execute();
$resultForRevenueGraph = $stmtForRevenueGraph->get_result();

// เก็บข้อมูลสำหรับกราฟยอดขาย
$revenueGraphLabels = []; // ใช้เก็บวันที่
$revenueAmounts = []; // ใช้เก็บยอดขาย (บาท)

while ($rowForRevenueGraph = $resultForRevenueGraph->fetch_assoc()) {
    $revenueGraphLabels[] = date('d-m-Y', strtotime($rowForRevenueGraph['order_date'])); // วันที่
    $revenueAmounts[] = $rowForRevenueGraph['total_revenue']; // ยอดขาย
}
?>

<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
<script>
var ctxRevenue = document.getElementById('revenueChart').getContext('2d');
var revenueChart = new Chart(ctxRevenue, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($revenueGraphLabels); ?>,
        datasets: [{
            label: 'ยอดขาย (บาท)',
            data: <?php echo json_encode($revenueAmounts); ?>,
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1,
            datalabels: {
                anchor: 'end',
                align: 'end',
                formatter: (value) => {
                    return value.toLocaleString();
                }
            }
        }]
    },
    options: {
        scales: {
        y: {
            beginAtZero: true,
            title: {
                display: true,
                text: 'บาท', // ชื่อแกน y
                position: 'left', // ตำแหน่งชื่อแกน y
                padding: { top: 10 },
                font: {
                    size: 14,
                    weight: 'bold'
                }
            },
            ticks: {
                callback: function(value) {
                    return value.toLocaleString();
                }
            }
        }
    },
        plugins: {
            datalabels: {
                color: 'black',
                font: {
                    weight: 'bold'
                }
            }
        }
    },
    plugins: [ChartDataLabels]
});
</script>

<br><br><br><br><br>

<!-- กราฟจำนวนสินค้า -->
<div class="chart-container" style="display: flex; align-items: flex-start;">
    <canvas id="quantityChart" width="600" height="400" style="margin: auto;"></canvas>
</div>

<?php
// คำสั่ง SQL สำหรับกราฟจำนวนสินค้า
$sqlForQuantityGraph = "SELECT DATE(o.Order_date) AS order_date, SUM(o.Quantity) AS total_quantity
    FROM orders o
    JOIN status s ON s.Status_id = o.Status_id
    WHERE MONTH(o.Order_date) = ?
    AND YEAR(o.Order_date) = ?
    AND (s.Status_id = '5' OR s.Status_id = '3')
    GROUP BY order_date
    ORDER BY order_date";

// เตรียมและผูกค่าพารามิเตอร์สำหรับกราฟ
$stmtForQuantityGraph = $conn->prepare($sqlForQuantityGraph);
$stmtForQuantityGraph->bind_param("ss", $selectedMonth, $selectedYear);
$stmtForQuantityGraph->execute();
$resultForQuantityGraph = $stmtForQuantityGraph->get_result();

// เก็บข้อมูลสำหรับกราฟจำนวนสินค้า
$quantityGraphLabels = []; // ใช้เก็บวันที่
$quantityAmounts = []; // ใช้เก็บยอดขายจำนวน

while ($rowForQuantityGraph = $resultForQuantityGraph->fetch_assoc()) {
    $quantityGraphLabels[] = date('d-m-Y', strtotime($rowForQuantityGraph['order_date'])); // วันที่
    $quantityAmounts[] = $rowForQuantityGraph['total_quantity']; // ยอดขายจำนวน
}
?>

<script>
var ctxQuantity = document.getElementById('quantityChart').getContext('2d');
var quantityChart = new Chart(ctxQuantity, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($quantityGraphLabels); ?>,
        datasets: [{
            label: 'ยอดขาย (จำนวน)',
            data: <?php echo json_encode($quantityAmounts); ?>,
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 1,
            datalabels: {
                anchor: 'end',
                align: 'end',
                formatter: (value) => {
                    return value.toLocaleString();
                }
            }
        }]
    },
    options: {
        scales: {
        y: {
            beginAtZero: true,
            title: {
                display: true,
                text: 'ใบ', // ชื่อแกน y
                position: 'left', // ตำแหน่งชื่อแกน y
                padding: { top: 10 },
                font: {
                    size: 14,
                    weight: 'bold'
                }
            },
            ticks: {
                callback: function(value) {
                    return value.toLocaleString();
                }
            }
        }
    },
        plugins: {
            datalabels: {
                color: 'black',
                font: {
                    weight: 'bold'
                }
            }
        }
    },
    plugins: [ChartDataLabels]
});
</script>

<br><br><br>

<style>
.bag {
    display: inline-block;
    padding: 10px 20px;
    background-color: #f0f0f0;
    border: 2px solid #333;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    font-weight: bold;
    color: #333;
}
</style>


</body>

</html>