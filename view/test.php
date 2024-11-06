<?php
session_start();
include '../connetDB/con_db.php';

// ฟังก์ชันสร้างรหัสกระเป๋าใหม่
function generateBagId($conn) {
    $sql = "SELECT bag_id FROM bag ORDER BY bag_id DESC LIMIT 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $lastId = $row['bag_id'];
        $num = intval(substr($lastId, 3)) + 1; 
        $newId = 'BAG' . str_pad($num, 2, '0', STR_PAD_LEFT); 
    } else {
        $newId = 'BAG01'; 
    }

    return $newId;
}

$bagId = generateBagId($conn);
?>


<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
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
    <link rel="stylesheet" href="manage.css">
    <link rel="stylesheet" href="addproductt.css">

</head>

<body>



    <div class="sidebar">
        <div class="user-profile">
            <i class="fas fa-user-circle"></i>
            <?php if (isset($_SESSION['First_name'])) : ?>
            <div class="nav-btn">
                <p class="user-name">
                   <p class="user-name"><strong><?php echo $_SESSION['First_name']; ?></strong></p>
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
                <a href="login.php">ออกจากระบบ</a>
                <i class="fas fa-sign-out-alt"></i>
            </button>
        </div>
    </div>


    <body>
        <div class="container">
            <div id="nav-placeholder" class="nav-placeholder"></div>
            <h2 class="manage-add" style="margin-top: 0px;">เพิ่มข้อมูลสินค้า</h2><br>
            <div class="box">
            <form method="POST" action="addphoto.php" enctype="multipart/form-data">
                <div class="form-container">
                  
                        <div class="form-row">

                            <label for="bag-id" style="margin-right: 10px;">รหัสกระเป๋า:</label>
                            <input type="text" id="bag_id" name="bag_id" value="<?php echo $bagId; ?>" readonly>
                        </div>



                        <!-- ช่องประเภท -->
                        <?php
$sql = "SELECT type_id, type_name FROM bag_type";
$result = $conn->query($sql);
?>
                        <div class="form-row" style="display: flex; align-items: center;">
                            <label for="type_id" style="margin-right: 10px;">ประเภท:</label>
                            <div style="display: flex; align-items: center; width: 54.6%;">
                                <select id="type_id" name="type_name" style="flex-grow: 1; margin-right: 10px;">
                                    <option value=""> </option>
                                    <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='" . htmlspecialchars($row['type_id'], ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars($row['type_name'], ENT_QUOTES, 'UTF-8') . "</option>";
                }
            }
            ?>
                                </select>
                                <button type="button" id="add-new-type"
                                    style="margin-left: 10px;">เพิ่มประเภทใหม่</button>
                            </div>
                        </div>
                        <!-- การเพิ่มประเภทใหม่ -->
                        <form method="POST" action="process_add_product.php"></form>

                        <!-- ป๊อปอัพ -->
                        <div id="popup"
                            style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%); background:#fff; border:1px solid #ccc; padding:20px; z-index:1000;">
                        </div>
                        <div id="overlay"
                            style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:999;">
                        </div>

                        <script>
                        document.getElementById('add-new-type').addEventListener('click', function() {
                            fetch('process_add_product.php')
                                .then(response => response.text())
                                .then(data => {
                                    document.getElementById('popup').innerHTML = data;
                                    document.getElementById('overlay').style.display = 'block';
                                    document.getElementById('popup').style.display = 'block';
                                });
                        });

                        document.getElementById('popup').addEventListener('click', function(e) {
                            if (e.target.id === 'close-popup-btn' || e.target.id === 'overlay') {
                                document.getElementById('overlay').style.display = 'none';
                                document.getElementById('popup').style.display = 'none';
                            }
                        });
                        </script>

                        <style>
                        #popup {
                            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
                            border-radius: 5px;
                        }
                        </style>





                        <!-- ช่องยี่ห้อ -->
                        <?php
    $sql = "SELECT brand_id, brand_name FROM bag_brand";
    $result = $conn->query($sql);
    ?>
                        <div class="form-row" style="display: flex; align-items: center;">
                            <label for="brand_id" style="margin-right: 10px;">ยี่ห้อ:</label>
                            <div style="display: flex; align-items: center; width: 52.8%;">
                                <select id="brand_id" name="brand_name" style="flex-grow: 1; margin-right: 10px;">
                                    <option value=""> </option>
                                    <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='" . htmlspecialchars($row['brand_id'], ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars($row['brand_name'], ENT_QUOTES, 'UTF-8') . "</option>";
                }
            }
            ?>

                                </select>
                                <button type="button" id="add-new-brand"
                                    style="margin-left: 10px;">เพิ่มยี่ห้อใหม่</button>
                            </div>
                        </div>

                        <form method="POST" action="process_add_brand.php"></form>
                        <div id="popup"
                            style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%); background:#fff; border:1px solid #ccc; padding:20px; z-index:1000;">
                        </div>
                        <div id="overlay"
                            style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:999;">
                        </div>

                        <script>
                        document.getElementById('add-new-brand').addEventListener('click', function() {
                            fetch('process_add_brand.php')
                                .then(response => response.text())
                                .then(data => {
                                    document.getElementById('popup').innerHTML = data;
                                    document.getElementById('overlay').style.display = 'block';
                                    document.getElementById('popup').style.display = 'block';
                                });
                        });

                        document.getElementById('popup').addEventListener('click', function(e) {
                            if (e.target.id === 'close-popup-btn' || e.target.id === 'overlay') {
                                document.getElementById('overlay').style.display = 'none';
                                document.getElementById('popup').style.display = 'none';
                            }
                        });
                        </script>

                        <style>
                        #popup {
                            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
                            border-radius: 5px;
                        }
                        </style>



                        <!-- ช่องวัสดุ -->
                        <?php
    $sql = "SELECT Material_id, Material_name FROM material"; 
    $result = $conn->query($sql);
    ?>
                        <div class="form-row" style="display: flex; align-items: center;">
                            <label for="Material_id" style="margin-right: 10px;">วัสดุ:</label>
                            <div style="display: flex; align-items: center; width: 52.7%;">
                            <select id="Material_id" name="Material_name" style="flex-grow: 1; margin-right: 10px;">
                                <option value=""> </option>
                                <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='" . htmlspecialchars($row['Material_id'], ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars($row['Material_name'], ENT_QUOTES, 'UTF-8') . "</option>";
                }
            }
            ?>
                                <!-- <option value="add-new">เพิ่มวัสดุใหม่</option> -->
                            </select>
                            <button type="button" id="add-new-material" style="margin-left: 10px;">เพิ่มวัสดุใหม่</button>
                        </div>
                </div>
                <form method="POST" action="process_add_material.php"></form>

                <!-- ป๊อปอัพ -->
<div id="popup"
    style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%); background:#fff; border:1px solid #ccc; padding:20px; z-index:1000;">
</div>
<div id="overlay"
    style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:999;">
</div>

<script>
document.getElementById('add-new-material').addEventListener('click', function() {
    fetch('process_add_material.php')
        .then(response => response.text())
        .then(data => {
            document.getElementById('popup').innerHTML = data;
            document.getElementById('overlay').style.display = 'block';
            document.getElementById('popup').style.display = 'block';
        });
});

document.getElementById('popup').addEventListener('click', function(e) {
    if (e.target.id === 'close-popup-btn' || e.target.id === 'overlay') {
        document.getElementById('overlay').style.display = 'none';
        document.getElementById('popup').style.display = 'none';
    }
});
</script>

<style>
#popup {
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
    border-radius: 5px;
}
</style>






                        <?php
$sql = "SELECT `Colors_code`, `Colors_name` FROM `colors`";
$result = $conn->query($sql);
?>

<div class="colors-container">
    <label for="material-type">สี:</label>
    <div class="color-buttons">
        <?php
        if ($result->num_rows > 0) {
            $count = 0; // ตัวนับสำหรับปุ่มสี
            while ($row = $result->fetch_assoc()) {
                // สร้างปุ่มสำหรับแต่ละสี
                echo "<div style='display: inline-block; margin: 5px;'>";
                echo "<button type='button' class='color-button' id='color-btn-" . htmlspecialchars($row['Colors_code'], ENT_QUOTES, 'UTF-8') . "' onclick=\"selectColor(this, '" . htmlspecialchars($row['Colors_code'], ENT_QUOTES, 'UTF-8') . "', '" . htmlspecialchars($row['Colors_name'], ENT_QUOTES, 'UTF-8') . "')\" style='background-color: " . htmlspecialchars($row['Colors_code'], ENT_QUOTES, 'UTF-8') . "; color: black; border: 2px solid transparent; padding: 10px; margin: 5px; border-radius: 5px;'>" . htmlspecialchars($row['Colors_name'], ENT_QUOTES, 'UTF-8') . "</button>";
                
                // ช่องแสดงจำนวนสำหรับแต่ละสี (แสดงอยู่ตั้งแต่เริ่มต้น)
                echo "<input type='number' id='color-count-" . htmlspecialchars($row['Colors_code'], ENT_QUOTES, 'UTF-8') . "' name='Total[" . htmlspecialchars($row['Colors_code'], ENT_QUOTES, 'UTF-8') . "]' value='0' min='0' class='color-count' style='width: 50px; margin-left: 5px;' onchange='updateCount(this, \"" . htmlspecialchars($row['Colors_name'], ENT_QUOTES, 'UTF-8') . "\")'/>"; 

                echo "</div>";
                $count++; // เพิ่มตัวนับ

                // หากตัวนับถึง 6 ให้เริ่มแถวใหม่
                if ($count % 6 == 0) {
                    echo "<br>"; // เพิ่มการตัดบรรทัดเพื่อเริ่มแถวใหม่
                }
            }
        }
        ?>
    </div>
</div>

<script>
// ฟังก์ชันเมื่อคลิกเลือกสี
function selectColor(button, colorCode, colorName) {
    // ยกเลิกการเลือกสีจากปุ่มอื่นๆ
    var allButtons = document.querySelectorAll('.color-button');
    allButtons.forEach(function(btn) {
        btn.style.border = '2px solid transparent'; // เอากรอบออก
    });

    // ใส่เส้นกรอบสีดำให้กับปุ่มที่เลือก
    button.style.border = '2px solid black';

    // ทำให้ปุ่มนี้ถูกทำเครื่องหมายว่าเลือกแล้ว
    button.classList.add('selected');
    console.log("เลือกสี: " + colorName);
}

// ฟังก์ชันสำหรับอัปเดตจำนวนเมื่อมีการเปลี่ยนแปลงใน input
function updateCount(input, colorName) {
    console.log("จำนวนสำหรับสี " + colorName + ": " + input.value);
}
</script>








<!-- ฟอร์มซ่อนสำหรับส่งข้อมูล -->
<form id="colorForm" method="post" action="addphoto.php" style="display: none;">
<input type="hidden" name="bag_id" id="bag_id">
    <input type="hidden" name="type_name" id="type_id">


    <input type="hidden" id="brand_id" name="brand_name">
    <input type="hidden" id="Material_id" name="Material_name">

 
    <input type="hidden" name="Colors_code" id="Colors_code">
    <input type="hidden" name="Total" id="Total">
</form>





                <div class="form-row">
                    <label for="cost_price">ราคาต้นทุน:</label>
                    <input type="number" id="Cost_price" name="Cost_price" step="1" >
                </div>

                <div class="form-row">
                    <label for="price">ราคาขาย:</label>
                    <input type="number" id="Price" name="Price" step="1" >
                </div>





                <div class="form-row">
                    <label for="image-upload">อัปโหลดรูปภาพ:</label>
                    <input type="file" id="image-upload" name="images[]" accept="image/*" multiple
                        onchange="validateFiles(this)" >
                    <small>(สูงสุด 10 รูป)</small>
                </div>
                <div id="image-preview" class="image-preview"></div>
                <button type="submit">เพิ่มสินค้า</button>
                

                <script>
                function validateFiles(input) {
                    const preview = document.getElementById('image-preview');
                    const currentImages = preview.getElementsByTagName('img')
                        .length; // นับจำนวนรูปภาพที่แสดงอยู่แล้ว
                    const files = input.files;

                    // ตรวจสอบว่าจำนวนรูปภาพที่มีอยู่แล้วรวมกับรูปภาพใหม่เกิน 10 หรือไม่
                    if (currentImages + files.length > 10) {
                        alert('สามารถอัปโหลดรูปภาพได้สูงสุด 10 รูป');
                        input.value = ''; // Reset input
                        return;
                    }

                    // แสดงตัวอย่างรูปภาพใหม่ที่เลือก
                    for (const file of files) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const img = document.createElement('img');
                            img.src = e.target.result;
                            img.style.width = '100px'; // Set the width of the image preview
                            img.style.margin = '5px'; // Add some margin
                            preview.appendChild(img);
                        };
                        reader.readAsDataURL(file);
                    }
                }
                </script>

                <style>
                .image-preview {
                    display: flex;
                    flex-wrap: wrap;
                }

                .image-preview img {
                    border: 1px solid #ccc;
                    border-radius: 4px;
                }
                </style>




            </div>
            </form >
        </div>
        </div>
    </body>

</html>