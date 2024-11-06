<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LOGIN FORM MEMBER</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"  referrerpolicy="no-referrer" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jaro:opsz@6..72&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=K2D:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="resett.css">
<script src='https://kit.fontawesome.com/a076d05399.js' crossorigin='anonymous'></script>
<script src="repassword.js"></script>

<br>
<br>
</head>
<body>
    <div id="nav-placeholder"></div>
    <script>
        fetch('http://localhost/ProBag/styCSS/navv.php') // เปลี่ยนเส้นทางตามที่เซิร์ฟเวอร์ของคุณให้บริการ
            .then(response => response.text())
            .then(data => {
                document.getElementById('nav-placeholder').innerHTML = data;
            })
            .catch(error => console.error('Error fetching nav:', error));
    </script>
    <div>
        <br><br><br>
    <h2 class="member">เปลี่ยนรหัสผ่าน</h2>
</div>
<br>
<div class="login">
    <form action="../login/login.html">
        <label for="login-pass">กรอกรหัสผ่านใหม่</label><br>
        <div style="position: relative;">
            <input type="password" id="login-pass1" name="password1" placeholder="รหัสผ่านความยาว 8-16 ตัวอักษร" style="padding-right: 40px;">
            <i class="fas fa-eye-slash" style="position: absolute; right: 10px; top: 40%; transform: translateY(-50%); cursor: pointer;"></i>
        </div>
        <label for="login-pass">ยืนยันรหัสผ่านใหม่</label><br>
        <div style="position: relative;">
            <input type="password" id="login-pass2" name="password2" placeholder="รหัสผ่านความยาว 8-16 ตัวอักษร" style="padding-right: 40px;">
            <i class="fas fa-eye-slash" style="position: absolute; right: 10px; top: 40%; transform: translateY(-50%); cursor: pointer;"></i>
        </div>
        
        <br>
    
        <input type="submit" value="ยืนยัน">
    </form>
    </div>

       
        
    </div>
</div>


</body>
</html>