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
<script src="login.js"></script>

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
    <h2 class="member">ลืมรหัสผ่าน</h2>
</div>
<br>
<div class="login">
    <form action="repassword.html">
        <label for="fname">Email</label><br>
            <div class="input-container">
                <i class="fas fa-envelope" style="position: absolute; left: 10px;top: 10%; transform: translateY(-50%); "></i>
                <input type="text" id="fname" name="fname" placeholder="@gmail.com" style="padding-left: 30px;">
        <label for="login-pass">รหัสOTP</label><br>
        <div style="position: relative;">
            <input type="password" id="login-pass" name="password" placeholder="กรอกรหัสOTP" style="padding-right: 40px;">
           
        </div>
        
        
        
      <br>
            
       
        <input type="submit" value="ยืนยัน">
      </form> 
    </div>

       
        
    </div>
</div>


</body>
</html>
