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

if (!isset($_SESSION['Member_id'])) {
    echo "<script> alert('กรุณาล็อกอินเข้าสู่ระบบ'); window.location='../login/login.php'; </script>";
    exit();
}

$Member_id = $_SESSION['Member_id'];

// SQL query to fetch user details
$sql = "SELECT m.Member_id, m.Title_name, m.First_name, m.Last_name, m.H_number, m.Road, m.Alley, 
               s.Subdistrict_name, s.Postcode, d.District_name, p.Province_name,
               GROUP_CONCAT(tp.Phone_number SEPARATOR ', ') AS Phone_numbers
        FROM member m
        INNER JOIN subdistrict s ON m.Subdistrict_id = s.Subdistrict_id
        INNER JOIN district d ON s.District_id = d.District_id
        INNER JOIN province p ON d.Province_id = p.Province_id
        LEFT JOIN telephone tp ON m.Member_id = tp.Member_id
        WHERE m.Member_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $Member_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
} else {
    echo "<script> alert('ไม่พบข้อมูลสมาชิก'); window.location='../login/login.php'; </script>";
    exit();
}

$stmt->close();
$conn->close();
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
    <link rel="stylesheet" href="prof.css">
    <style>
    .color-button.selected {
        border: 2px solid #000;

    }

    .bag-heading {
        font-size: 45px;
        margin-right: 57%;
    }
    .nav-back {
        position: absolute;
        left: 0;
        top: 2;
        padding: 10px;
        cursor: pointer;
    }

    .nav-back i {
        font-size: 40px;
        color: #fff;

    }
    </style>


</head>

<body>
    <nav class="navbar">
    <div class="nav-back" onclick="location.href='../index.php'" style="cursor: pointer;">
        <i class="fas fa-arrow-left"></i>
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
    <div class="container">
        <header>
            <h1 class="history">ข้อมูลส่วนตัว</h1>
        </header>
        <br><br>

        <div class="profile-container">
    <div class="profile-row">
        <label class="profile-label">Email:</label>
        <div class="profile-field">
            <span class="profile-value"><?php echo htmlspecialchars($row['Member_id']); ?></span>
        </div>
    </div>

    <div class="profile-row">
        <label class="profile-label">ชื่อ:</label>
        <div class="profile-field">
            <span class="profile-value"><?php echo htmlspecialchars($row['Title_name']); ?></span>
            <span class="profile-value"><?php echo htmlspecialchars($row['First_name']); ?></span>
            <span class="profile-value"><?php echo htmlspecialchars($row['Last_name']); ?></span>
        </div>
    </div>

    <div class="profile-row">
        <label class="profile-label">เบอร์โทรศัพท์:</label>
        <div class="profile-field">
            <?php 
                $phone_numbers = explode(', ', $row['Phone_numbers']);
                foreach ($phone_numbers as $phone) {
                    echo '<span class="profile-value">' . htmlspecialchars($phone) . '</span><br>';
                }
            ?>
        </div>
    </div>

    <div class="profile-row">
        <label class="profile-label">ที่อยู่:</label>
        <div class="profile-field">
            <span class="profile-value">บ้านเลขที่ <?php echo htmlspecialchars($row['H_number']); ?></span>
            <span class="profile-value"><?php echo htmlspecialchars($row['Road']); ?></span>
            <span class="profile-value"><?php echo htmlspecialchars($row['Alley']); ?></span>
          
           
            <span class="profile-value">ตำบล <?php echo htmlspecialchars($row['Subdistrict_name']); ?></span>
            <span class="profile-value">อำเภอ <?php echo htmlspecialchars($row['District_name']); ?></span>
            <span class="profile-value">จังหวัด <?php echo htmlspecialchars($row['Province_name']); ?></span>
            <span class="profile-value">รหัสไปรษณีย์ <?php echo htmlspecialchars($row['Postcode']); ?></span>
        </div>
    </div>
</div>
<br><br>
    <div class="profile-row">
        <button id="edit-button" type="button" onclick="window.location.href='editprofile.php?Member_id=<?php echo urlencode($row['Member_id']); ?>'" class="edit-button">แก้ไข</button>
    </div>
</div>



            <style>
            .profile-container {
        width: 80%;
        max-width: 600px;
        margin: auto;
    }

  

 
    .profile-field {
        background-color: #e0e0e0; /* สีเทาครอบข้อมูล */
        padding: 10px;
        border-radius: 5px;
        display: inline-block;
        width: 70%; /* ช่องข้อมูลกว้างเต็ม */
    }


    .edit-button {
    width: 50%;
    background-color: #4CAF50; /* สีพื้นหลัง */
    color: white; /* สีตัวอักษร */
    padding: 10px 20px; /* ช่องว่างภายใน */
    border: none; /* ไม่มีขอบ */
    border-radius: 5px; /* มุมโค้ง */
    cursor: pointer; /* แสดงตัวชี้ */
    font-size: 16px; /* ขนาดตัวอักษร */
    transition: background-color 0.3s; /* การเปลี่ยนสีพื้นหลังเมื่อ hover */
    display: block; /* ทำให้ปุ่มเป็น block element */
    margin: 20px auto; /* จัดปุ่มให้อยู่ตรงกลาง */
}

.edit-button:hover {
    background-color: #45a049; /* สีพื้นหลังเมื่อ hover */
}

.edit-button:focus {
    outline: none; /* ไม่มีเส้นขอบเมื่อ focus */
}
.edit-button{
    margin-left: 85%;
    background-color: #4CAF50;
    color: white;
    border: none;
    padding: 5px 10px;
    font-size: 16px;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.profile-row {
    display: flex; /* เพิ่ม flexbox เพื่อให้ align items */
    align-items: center;
  
}

.profile-label {
  
    font-weight: bold;
    margin-right: 25px; /* เว้นระยะห่างระหว่าง label และ input */
}
.profile-row {
    display: flex;
    align-items: center;
    
}

.profile-label {
    width: 20%; /* กำหนดความกว้างของ label เพื่อให้เท่ากัน */
    font-weight: bold;
    margin-right: 3px;
}

.profile-field {
    width: 70%; /* กำหนดความกว้างของช่องข้อมูล */
    background-color: #e0e0e0;
    padding: 10px;
    border-radius: 5px;
}



            </style>
</body>

</html>