<?php
// เชื่อมต่อฐานข้อมูล
session_start();
include '../connetDB/con_db.php';


if (isset($_GET['Member_id'])) {
    $member_id = $_GET['Member_id'];
  
    // ดึงข้อมูลสมาชิก
    $sql = "SELECT Title_name, First_name, Last_name, H_number, Road, Alley, Subdistrict_name, Postcode, District_name, province_name, Phone_number,
     dt.District_id ,pv.Province_id,st.Subdistrict_id
            FROM member mb
            JOIN subdistrict st ON mb.Subdistrict_id = st.Subdistrict_id
            JOIN district dt ON st.district_id = dt.district_id
            JOIN province pv ON dt.Province_id = pv.Province_id
            JOIN telephone tp ON mb.Member_id = tp.Member_id
            WHERE mb.Member_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $member_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
    } else {
        echo "ไม่พบข้อมูลที่อยู่";
        exit;
    }
    $stmt->close();
} else {
    echo "ไม่พบ ID สมาชิก";
    exit;
}
$provinces = $conn->query("SELECT * FROM province");
$districts = $conn->query("SELECT * FROM district");
$subdistricts = $conn->query("SELECT * FROM subdistrict");

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
    <link rel="stylesheet" href="profilee.css">
    <style>
    .color-button.selected {
        border: 2px solid #000;

    }

    .bag-heading {
        font-size: 45px;
        margin-right: 57%;
    }
    </style>


</head>

<body>
    <nav class="navbar">
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
            <h1 class="history">แก้ไขที่อยู่</h1>
        </header>
        <br>



        <form method="POST" action="editaddress_db.php">
            <div class="profile-row">
                <label class="profile-label">คำนำหน้า:</label>
                <select name="Title_name" class="select-box">
                    <option value="">เลือกคำนำหน้า</option>
                    <option value="นาย" <?php echo ($row['Title_name'] === 'นาย') ? 'selected' : ''; ?>>นาย</option>
                    <option value="นางสาว" <?php echo ($row['Title_name'] === 'นางสาว') ? 'selected' : ''; ?>>นางสาว
                    </option>
                    <option value="นาง" <?php echo ($row['Title_name'] === 'นาง') ? 'selected' : ''; ?>>นาง</option>
                </select>

                <label class="profile-label">ชื่อ:</label>
                <input class="profile-value" type="text" name="First_name"
                    value="<?php echo htmlspecialchars($row['First_name']); ?>">
                <label class="profile-label">นามสกุล:</label>
                <input class="profile-value" type="text" name="Last_name"
                    value="<?php echo htmlspecialchars($row['Last_name']); ?>">
            </div>
            <div class="profile-row">
            <label class="profile-label">เบอร์โทรศัพท์:</label>
            <input class="profile-value" type="text" name="Phone_number"
                value="<?php echo htmlspecialchars($row['Phone_number']); ?>">

            <label class="profile-label">บ้านเลขที่:</label>
            <input class="profile-value" type="text" name="H_number"
                value="<?php echo htmlspecialchars($row['H_number']); ?>">
                </div>
            <label class="profile-label">ถนน:</label>
            <input class="profile-value" type="text" name="Road" value="<?php echo htmlspecialchars($row['Road']); ?>">
           

            <label class="profile-label">ซอย:</label>
            <input class="profile-value" type="text" name="Alley"
                value="<?php echo htmlspecialchars($row['Alley']); ?>">


            <label class="profile-label">จังหวัด:</label>
            <select name="Province_name" required onchange="loadDistricts(this.value)">
                <option value="">เลือกจังหวัด</option>
                <?php while ($province = $provinces->fetch_assoc()): ?>
                <option value="<?php echo $province['Province_id']; ?>"
                    <?php echo ($row['Province_id'] == $province['Province_id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($province['Province_name'], ENT_QUOTES, 'UTF-8'); ?>
                </option>
                <?php endwhile; ?>
            </select>
            <div class="profile-row">
            <label class="profile-label">อำเภอ:</label>
            <select id="district" name="District_name" required onchange="loadSubdistricts(this.value)">
                <option value="">เลือกอำเภอ</option>
                <?php while ($district = $districts->fetch_assoc()): ?>
                <option value="<?php echo $district['District_id']; ?>"
                    <?php echo ($row['District_id'] == $district['District_id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($district['District_name'], ENT_QUOTES, 'UTF-8'); ?>
                </option>
                <?php endwhile; ?>
            </select>

            <label class="profile-label">ตำบล:</label>
            <select id="subdistrict" name="Subdistrict_name" required onchange="loadPostcode(this.value)">
                <option value="">เลือกตำบล</option>
                <?php while ($subdistrict = $subdistricts->fetch_assoc()): ?>
                <option value="<?php echo $subdistrict['Subdistrict_id']; ?>"
                    <?php echo ($row['Subdistrict_id'] == $subdistrict['Subdistrict_id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($subdistrict['Subdistrict_name'], ENT_QUOTES, 'UTF-8'); ?>
                </option>
                <?php endwhile; ?>
            </select>

            <div class="profile-row">
                <label class="profile-label">รหัสไปรษณีย์:</label>
                <input class="profile-value" type="text" name="Postcode" id="postcode"
                    value="<?php echo htmlspecialchars($row['Postcode']); ?>" readonly>
            </div>
            </div>
            <button type="submit" class="btn-submit">บันทึก</button>
    </div>
    </form>


    <script>
    function loadDistricts(province_id) {
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "get_districts.php?province_id=" + province_id, true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                document.getElementById("district").innerHTML = xhr.responseText;
                document.getElementById("subdistrict").innerHTML = '<option value="">เลือกตำบล</option>';
            }
        };
        xhr.send();
    }

    function loadSubdistricts(district_id) {
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "get_subdistricts.php?district_id=" + district_id, true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                document.getElementById("subdistrict").innerHTML = xhr.responseText;
            }
        };
        xhr.send();
    }

    function loadPostcode(subdistrict_id) {
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "get_postcode.php?subdistrict_id=" + subdistrict_id, true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);
                document.getElementById("postcode").value = response.Postcode;
            }
        };
        xhr.send();
    }
    </script>
    <style>
    .form-container {
        max-width: 600px;
        margin: 20px auto;
        padding: 20px;
        border: 1px solid #ddd;
        border-radius: 8px;
        background-color: #f9f9f9;
    }

    .input-box {
        margin-bottom: 15px;
    }

    .input-box label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }

    .input-box input,
    .input-box select {
        width: 100%;
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    .select-box {
        padding: 8px;
        background-color: #fff;
    }

    .required-mark {
        color: red;
    }

    .btn-submit {
        display: inline-block;
        padding: 10px 20px;
        color: #fff;
        background-color: #007bff;
        border: none;
        border-radius: 5px;
        text-align: center;
        cursor: pointer;
        transition: background-color 0.3s, transform 0.2s;
        text-decoration: none;
    }

    .btn-submit:hover {
        background-color: #0056b3;
        transform: scale(1.05);
    }

    .btn-submit:active {
        background-color: #004494;
    }
    </style>