<?php
session_start();
include '../connetDB/con_db.php';

// Fetch provinces
$sql_province = "SELECT `Province_id`, `Province_name` FROM `province`";
$result_province = $conn->query($sql_province);
if ($result_province === false) {
    die("Error fetching province data: " . $conn->error);
}

$provinces = [];
while ($row = $result_province->fetch_assoc()) {
    $provinces[] = $row;
}

// Fetch districts
$sql_district = "SELECT `District_id`, `District_name`, `Province_id` FROM `district`";
$result_district = $conn->query($sql_district);
if ($result_district === false) {
    die("Error fetching district data: " . $conn->error);
}

$districts = [];
while ($row = $result_district->fetch_assoc()) {
    $districts[] = $row;
}

// Fetch subdistricts
$sql_subdistrict = "SELECT `Subdistrict_id`, `Subdistrict_name`, `Postcode`, `District_id` FROM `subdistrict`";
$result_subdistrict = $conn->query($sql_subdistrict);
if ($result_subdistrict === false) {
    die("Error fetching subdistrict data: " . $conn->error);
}

$subdistricts = [];
while ($row = $result_subdistrict->fetch_assoc()) {
    $subdistricts[] = $row;
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LOGIN FORM MEMBER</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" referrerpolicy="no-referrer" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jaro:opsz@6..72&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=K2D:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="register.css">
    <script src="register.js"></script>
    
</head>
<body>
<?php include('../styCSS/navv.php'); ?>
<div>
    <br>
    <h2 class="member">สมัครสมาชิก</h2>
</div>
<br>
<div class="wrapper">
    <div class="form-box">
        <form action="register_db.php" method="post" class="form">
            <div class="input-box">
                <label>Email<span class="required-mark">*</span></label>
                <div>
                    <i id="login-email" class="fas fa-envelope"></i>
                    <input type="email" name="Member_id" placeholder=" @gmail.com" required maxlength="50">
                </div>
            </div>
            <div class="column">
                <div class="input-box">
                    <label>คำนำหน้าชื่อ<span class="required-mark">*</span></label>
                    <select name="Title_name" class="select-box">
                        <option value=""></option>
                        <option value="นาย">นาย</option>
                        <option value="นางสาว">นางสาว</option>
                        <option value="นาง">นาง</option>
                    </select>
                </div>
                <div class="input-box">
                    <label>ชื่อ<span class="required-mark">*</span></label>
                    <input type="text" name="First_name" required>
                </div>
                <div class="input-box">
                    <label>นามสกุล<span class="required-mark">*</span></label>
                    <input type="text" name="Last_name" required>
                </div>
            </div>
            <div class="column">
                

                <div class="input-box">
                    <label>เบอร์โทรศัพท์<span class="required-mark">*</span></label>
                    <input type="text" name="Phone_number[]" placeholder=" +66" required>
                </div>

                <div class="input-box">
                    <label>เบอร์โทรศัพท์ (สำรอง)</span></label>
                    <input type="text" name="Phone_number[]" placeholder=" +66" >
                </div>
                
                <div class="input-box">
                    <label>รหัสผ่าน<span class="required-mark">*</span></label>
                    <div>
                        <input type="password" id="login-pass" name="Password" placeholder="รหัสผ่านความยาว 8-16 ตัวอักษร" required maxlength="16">
                        <i id="login-eye" class="fas fa-eye-slash"></i>
                    </div>
                </div>
            </div>
            


            <div class="column">
                <div class="input-box">
                    <label>เลขที่บ้าน<span class="required-mark">*</span></label>
                    <input type="text" name="H_number" required>
                    
                </div>
                <div class="input-box">
                    <label>ถนน</label>
                    <input type="text" name="Road">
                </div>
                <div class="input-box">
                    <label>ซอย</label>
                    <input type="text" name="Alley">
                </div>
            </div>
            <div class="column">
                <div class="input-box">
                    <label for="Province_id">จังหวัด<span class="required-mark">*</span></label>
                    <select id="Province_id" name="Province_id" class="select-box" required>
                        <option value="">กรุณาเลือก</option>
                        <?php
                        foreach ($provinces as $province) {
                            echo "<option value='" . $province['Province_id'] . "'>" . $province['Province_name'] . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="input-box">
                    <label for="District_id">อำเภอ<span class="required-mark">*</span></label>
                    <select id="District_id" name="District_id" class="select-box" required>
                        <option value="">กรุณาเลือก</option>
                    </select>
                </div>
                <div class="input-box">
                    <label for="Subdistrict_id">ตำบล<span class="required-mark">*</span></label>
                    <select id="Subdistrict_id" name="Subdistrict_id" class="select-box" required>
                        <option value="">กรุณาเลือก</option>
                    </select>
                </div>
                <div class="input-box">
                    <label for="Postcode">รหัสไปรษณีย์<span class="required-mark">*</span></label>
                    <select id="Postcode" name="Postcode" class="select-box" required>
                        <option value="">กรุณาเลือก</option>
                    </select>
                </div>
            </div><br>
            <div class="btn-container">
                <button type="submit" class="btn">สมัครสมาชิก</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const districts = <?php echo json_encode($districts); ?>;
    const subdistricts = <?php echo json_encode($subdistricts); ?>;

    const provinceSelect = document.getElementById('Province_id');
    const districtSelect = document.getElementById('District_id');
    const subdistrictSelect = document.getElementById('Subdistrict_id');
    const postcodeSelect = document.getElementById('Postcode');

    provinceSelect.addEventListener('change', function() {
        const provinceId = this.value;
        districtSelect.innerHTML = '<option value="">กรุณาเลือก</option>';
        subdistrictSelect.innerHTML = '<option value="">กรุณาเลือก</option>';
        postcodeSelect.innerHTML = '<option value="">กรุณาเลือก</option>';

        const filteredDistricts = districts.filter(d => d.Province_id == provinceId);
        filteredDistricts.forEach(d => {
            const option = document.createElement('option');
            option.value = d.District_id;
            option.textContent = d.District_name;
            districtSelect.appendChild(option);
        });
    });

    districtSelect.addEventListener('change', function() {
        const districtId = this.value;
        subdistrictSelect.innerHTML = '<option value="">กรุณาเลือก</option>';
        postcodeSelect.innerHTML = '<option value="">กรุณาเลือก</option>';

        const filteredSubdistricts = subdistricts.filter(s => s.District_id == districtId);
        filteredSubdistricts.forEach(s => {
            const option = document.createElement('option');
            option.value = s.Subdistrict_id;
            option.textContent = s.Subdistrict_name;
            subdistrictSelect.appendChild(option);
        });
    });

    subdistrictSelect.addEventListener('change', function() {
        const subdistrictId = this.value;
        const selectedSubdistrict = subdistricts.find(s => s.Subdistrict_id == subdistrictId);

        if (selectedSubdistrict) {
            const option = document.createElement('option');
            option.value = selectedSubdistrict.Postcode;
            option.textContent = selectedSubdistrict.Postcode;
            postcodeSelect.innerHTML = '';
            postcodeSelect.appendChild(option);
        } else {
            postcodeSelect.innerHTML = '<option value="">ไม่พบข้อมูล</option>';
        }
    });
});
</script>

</body>
</html>
