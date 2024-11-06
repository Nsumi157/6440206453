


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
<link rel="stylesheet" href="loginm.css">

<script src="login.js"></script>

<br>
<br>
</head>
<body>
<?php if (isset($_SESSION['First_name'])) : ?>
    <nav class="navbar">
        <div class="navbar-icons">
            <div class="nav-btn">
                <p class="user-name"><strong><?php echo $_SESSION['First_name']; ?></strong></p>
            </div>
            <a class="nav-btn" href="profile.php"><i class="user-circle fas fa-user-circle"></i></a>
            <a class="nav-btn" href="cart.php"><i class="fas fa-shopping-cart"></i></a>
        </div>
    </nav>
<?php else: ?>
    <div class="container-fluid">
        <button type="button" class="btn-login" onclick="location.href='../login/login.php'">เข้าสู่ระบบ</button>
        <button type="button" class="btn-register" onclick="location.href='../register/registerr.php'">สมัครสมาชิก</button>
    </div>
<?php endif; ?>
        <br><br><br>
    <h2 class="member">เข้าสู่ระบบ</h2>
</div>
<br>
<div class="login">
<form action="login_db.php" method="POST">
<?php if (isset($_SESSION['error'])) : ?>
            <div class="error">
                <h3>
                    <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                </h3>
            </div>
        <?php endif ?>


        <label for="Member_id" class="emaill" style="margin-right: 250px;">Email<span class="required-mark" style=" color: red;">*</span></label><br>
            <div class="input-container">
                <i class="fas fa-envelope" style="position: absolute; left: 10px;top: 8%; transform: translateY(-50%); "></i>
                <input type="text"  name="Member_id" placeholder="@gmail.com" style="padding-left: 30px;">
        <label for="Password" class="pass" style="margin-right: 227px;">รหัสผ่าน<span class="required-mark" style=" color: red;">*</span></label><br>
        <div style="position: relative;">
        <input type="password" id="login-pass" name="Password" placeholder="รหัสผ่านความยาว 8-16 " required maxlength="16">
            <i id="login-eye" class="fas fa-eye-slash" style="position: absolute; right: 10px; top: 40%; transform: translateY(-50%); cursor: pointer;"></i>
        </div>
        
        
        

        <!-- <div style="font-size: smaller; display: flex; justify-content: space-between;">
            <a href="../reset/reset.php" style="color: black;">ลืมรหัสผ่านใช่หรือไม่</a>
            <a href="../reset/otp.php" style="color: red;">ลืมรหัสผ่าน รับ OTP แล้ว</a>
        </div> -->
        <br><br> 
            
       
        <input type="submit" value="ยืนยัน">
      </form> 
    </div>

       
        
    </div>
</div>
<style>
    
</style>

</body>
</html>
