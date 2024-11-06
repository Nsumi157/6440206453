<?php
session_start();

$conn = new mysqli('localhost', 'root', '', 'dbonline_bag');
$conn->set_charset('utf8mb4');

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("การเชื่อมต่อล้มเหลว: " . $conn->connect_error);
}
?>


<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ประวัติการสั่งซื้อ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
        referrerpolicy="no-referrer" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jaro:wght@400;600&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=K2D:wght@400;600&display=swap" rel="stylesheet">
    <link href='https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="history.css">
</head>

<body>
    <nav class="navbar">
    <div class="nav-back" onclick="location.href='../index.php'" style="cursor: pointer;">
        <i class="fa fa-home"></i>
    </div>
        <h2 class="bag-heading" onclick="location.href='../index.php'" style="cursor: pointer;">Bag Collective</h2>
        <div class="navbar-icons">
            <?php if (isset($_SESSION['First_name']) && isset($_SESSION['Member_id'])): ?>
            <div class="nav-btn">
                <p class="user-name">
                    <strong><?php echo htmlspecialchars($_SESSION['First_name']); ?></strong>
                </p>
            </div>
            <div class="nav-btn user-dropdown">
                <i class="user-circle fas fa-user-circle" onclick="toggleDropdown()"></i>
                <div class="dropdown-menu" id="dropdownMenu">
                    <a href="profile.php">บัญชีผู้ใช้</a>
                    <a href="history.php">ประวัติการสั่งซื้อ</a>
                    <a href="#" onclick="confirmLogout()">ออกจากระบบ</a>
                </div>
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
            <h1 class="history">ให้คะแนนสินค้า</h1>
        </header>

        <br><br>
        <?php
// ตรวจสอบว่ามีค่า Order_id ถูกส่งมาหรือไม่
if (isset($_GET['Order_id'])) {
    $order_id = htmlspecialchars($_GET['Order_id']);

    // คิวรีข้อมูลคำสั่งซื้อจากฐานข้อมูล
    $sql = "SELECT * FROM orders WHERE Order_id = '$order_id'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // แสดงรายละเอียดคำสั่งซื้อ
        $row = $result->fetch_assoc();

        // ส่งค่าต่าง ๆ ไปยัง Console ของเบราว์เซอร์
        echo "<script>
            console.log('Order ID: " . htmlspecialchars($row['Order_id']) . "');
            console.log('Member ID: " . htmlspecialchars($row['Member_id']) . "');
            console.log('สถานะ: " . htmlspecialchars($row['Status_id']) . "');
        </script>";

       // ฟอร์มให้คะแนนและเขียนรีวิว
echo '<form method="post" action="submit_review.php">';
echo '<input type="hidden" name="Order_id" value="' . htmlspecialchars($order_id) . '">';
echo '<label for="rating">ให้คะแนน:</label>';
echo '<div class="star-rating">';
for ($i = 1; $i <= 5; $i++) {
    echo '<label for="star' . $i . '" class="star" onclick="setRating(' . $i . ')">&#9733;</label>'; // ดาว
}
echo '</div>';
echo '<input type="hidden" name="rating" id="rating" required>';
echo '<br>';
echo '<label for="review">เขียนรีวิว:</label><br>';
echo '<textarea name="review" id="review" rows="4" ></textarea>';
echo '<br><br>';
echo '<button type="submit">ส่งรีวิว</button>';
echo '</form>';
    } else {
        echo "ไม่พบคำสั่งซื้อนี้.";
    }
} else {
    echo "ไม่พบ Order ID.";
}

// ปิดการเชื่อมต่อ
$conn->close();
?>

<script>
function setRating(rating) {
    // กำหนดค่าให้กับ input hidden
    document.getElementById('rating').value = rating;

    // เปลี่ยนสีของดาวที่เลือก
    var stars = document.querySelectorAll('.star');
    stars.forEach((star, index) => {
        star.style.color = index < rating ? '#f5a623' : '#ccc'; // สีเหลืองเมื่อเลือก, สีเทาเมื่อไม่เลือก
    });
}
</script>

<style>
    .bag-heading{
    font-size: 40px;
    margin-right:73%;
    color: #fff;
     font-family: 'Jaro', sans-serif; /* กำหนดฟอนต์ */
    
}
    .star-rating {
        display: flex;
       
        align-items: center;
        
    }

    .star {
        font-size: 2.5rem; /* เพิ่มขนาดดาว */
        color: #ddd; /* สีของดาวเมื่อไม่ได้เลือก */
        cursor: pointer;
     
        padding: 0 5px; /* เพิ่มช่องว่างระหว่างดาว */
    }

    .star:hover, .star:hover ~ .star {
        color: #ffca28; /* สีดาวเมื่อ hover */
        transform: scale(1.2); /* ขยายดาวเมื่อ hover */
    }

   
    /* ปรับแต่งกล่องรีวิว */
    textarea {
        width: 100%;
        padding: 10px;
        font-size: 1.2rem;
        border: 2px solid #ddd;
        border-radius: 8px;
        transition: border-color 0.3s;
    }

    textarea:focus {
        border-color: #f5a623; /* เปลี่ยนสีขอบเมื่อคลิก */
    }

    button[type="submit"] {
        background-color: #28a745;
        color: white;
        padding: 10px 20px;
        font-size: 1.2rem;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: background-color 0.3s;
    }

   
    /* เพิ่มพื้นที่รอบกล่องรีวิว */
    form {
        max-width: 600px;
        margin: 0 auto;
       
    }

    label {
        font-size: 1.2rem;
        font-weight: bold;
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
</style>

</body>
</html>