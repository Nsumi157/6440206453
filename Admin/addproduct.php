<?php
session_start();
include '../connetDB/con_db.php';

// ฟังก์ชันสร้างรหัสกระเป๋าใหม่
function generateBagId($conn) {
    // เริ่มต้นรหัสใหม่เป็น NULL
    $newId = null;

    // สร้างรหัสกระเป๋าใหม่
    $num = 1; // เริ่มต้นจาก 1

    // วนลูปจนกว่าจะได้รหัสที่ไม่ซ้ำ
    while (true) {
        // สร้างรหัสใหม่ โดยเติม 0 ด้านหน้าให้มี 4 หลัก
        $newId = 'B' . str_pad($num, 4, '0', STR_PAD_LEFT);

        // ตรวจสอบว่ารหัสนี้มีอยู่ในฐานข้อมูลหรือไม่
        $checkSql = "SELECT bag_id FROM bag WHERE bag_id = '$newId'";
        $checkResult = $conn->query($checkSql);

        // ถ้าไม่มีรหัสซ้ำให้ break ออกจากลูป
        if ($checkResult->num_rows === 0) {
            break;
        }

        // เพิ่มหมายเลขเพื่อสร้างรหัสใหม่
        $num++;
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
    <link rel="stylesheet" href="product.css">

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
            <a href="#" onclick="confirmLogout()">ออกจากระบบ</a>
                <i class="fas fa-sign-out-alt"></i>
            </button>
        </div>
    </div>
    <script>
       
function confirmLogout() {
    // แสดงกล่องยืนยัน
    var confirmation = confirm("ต้องการออกจากระบบใช่หรือไม่?");
    if (confirmation) {
        // ถ้าผู้ใช้กด OK ให้เปลี่ยนเส้นทางไปยังสคริปต์ logout.php
        window.location.href = "logout.php";
    }
}
</script>

    <body>
        <div class="container">
            <div id="nav-placeholder" class="nav-placeholder"></div>
            <h2 class="manage-add" style="margin-top: 0px;">เพิ่มข้อมูลสินค้า</h2><br>
            <div class="box">
                <form method="POST" action="addphoto.php" enctype="multipart/form-data">
                    <input type="hidden" name="bag_id" id="bag_id">
                    <input type="hidden" name="type_name" id="type_id">
                    <input type="hidden" id="brand_id" name="brand_name">
                    <input type="hidden" id="Material_id" name="Material_name">
                    <!-- <input type="hidden" id="image-upload" name="images[]"> -->


                    <div class="form-container">

                        <div class="form-row">

                            <label for="bag-id" style="margin-right: 10px;">รหัสกระเป๋า:</label>
                            <input type="text" id="bag_id" name="bag_id" value="<?php echo $bagId; ?>" readonly>
                        </div>


                        <?php
                            $sql = "SELECT type_id, type_name FROM bag_type";
                            $result = $conn->query($sql);
                        ?>
                        <div class="form-row" style="display: flex; align-items: center;">
                            <label for="type_id" style="margin-right: 10px;">ประเภท:</label>
                            <div style="display: flex; align-items: center; width: 42.5%;">
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
                            </div>

                        </div>


                        <!-- ช่องยี่ห้อ -->
                        <?php
                                    $sql = "SELECT brand_id, brand_name FROM bag_brand";
                                    $result = $conn->query($sql);
                                 ?>
                        <div class="form-row" style="display: flex; align-items: center;">
                            <label for="brand_id" style="margin-right: 10px;">ยี่ห้อ:</label>
                            <div style="display: flex; align-items: center; width: 42.5%;">
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
                            </div>
                        </div>



                        <!-- ช่องวัสดุ -->
                        <?php
                                    $sql = "SELECT Material_id, Material_name FROM material"; 
                                $result = $conn->query($sql);
                                ?>
                        <div class="form-row" style="display: flex; align-items: center;">
                            <label for="Material_id" style="margin-right: 10px;">วัสดุ:</label>
                            <div style="display: flex; align-items: center; width: 42.5%;">
                                <select id="Material_id" name="Material_name" style="flex-grow: 1; margin-right: 10px;">
                                    <option value=""> </option>
                                    <?php
                                 if ($result->num_rows > 0) {
                                 while ($row = $result->fetch_assoc()) {
                                         echo "<option value='" . htmlspecialchars($row['Material_id'], ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars($row['Material_name'], ENT_QUOTES, 'UTF-8') . "</option>";
                                         }
                                     }
                                    ?>

                                </select>
                            </div>
                        </div>


                        <?php
$sql = "SELECT Colors_code, Colors_name FROM colors";
$result = $conn->query($sql);
?>

                        <div class="colors-container">
                            <label for="material-type">สี: </label>
                            <label style="color: red;">เลือกสี ก่อนใส่จำนวน</label>
                        </div>

                        <div class="color-buttons">
                            <?php
            if ($result->num_rows > 0) {
                $count = 0;
                while ($row = $result->fetch_assoc()) {
                    $colorCode = htmlspecialchars($row['Colors_code'], ENT_QUOTES, 'UTF-8');
                    $colorName = htmlspecialchars($row['Colors_name'], ENT_QUOTES, 'UTF-8');
                    
                    // ปุ่มเลือกสี
                    echo "<div style='display: inline-block; margin: 5px;'>";
                    echo "<button type='button' class='color-button' id='color-btn-$colorCode' onclick=\"toggleColor('$colorCode')\" style='background-color: $colorCode; color: black; border: 2px solid transparent; padding: 10px; margin: 5px; border-radius: 5px;'>$colorName</button>";

                    // ช่องกรอกจำนวนสำหรับสี
                    echo "<input type='number' id='color-count-$colorCode' name='Total[$colorCode]' value='0' min='0' class='color-count' style='width: 50px; margin-left: 5px;' disabled onchange=\"logSelection('$colorCode', this.value)\" />";
                    echo "</div>";
                    
                    $count++;
                    if ($count % 4 == 0) {
                        echo "<br>";
                    }
                }
            }
            ?>
                        </div>
                    </div>



                    <script>
                    function toggleColor(colorCode) {
                        var selectedButton = document.getElementById('color-btn-' + colorCode);
                        var colorCountInput = document.getElementById('color-count-' + colorCode);

                        // เช็คว่าปุ่มถูกเลือกอยู่แล้วหรือไม่
                        if (selectedButton.style.border === '2px solid black') {
                            // ถ้าถูกเลือกแล้ว ให้ยกเลิกการเลือก
                            selectedButton.style.border = '2px solid transparent';
                            colorCountInput.disabled = true;
                            colorCountInput.value = 0; // รีเซ็ตค่าเมื่อยกเลิกการเลือก
                        } else {
                            // ถ้ายังไม่ถูกเลือก ให้เลือกและเปิดการใช้งานช่องกรอกจำนวน
                            selectedButton.style.border = '2px solid black';
                            colorCountInput.disabled = false;
                        }

                        // แสดงสีที่เลือกใน console
                        console.log("เลือกสี: " + colorCode);
                    }

                    function logSelection(colorCode, value) {
                        if (value > 0) {
                            console.log("สี " + colorCode + " มีจำนวน: " + value);
                        } else {
                            console.log("ยกเลิกการเลือกสี " + colorCode);
                        }
                    }
                    </script>












                    <div class="form-row">

                        <label for="cost_price">ราคาต้นทุน:</label>
                        <input type="number" id="Cost_price" name="Cost_price" step="1"
                            style="display: flex; align-items: center; width: 42.5%;  height: 24px;">
                    </div>

                    <div class="form-row">
                        <label for="price">ราคาขาย:</label>
                        <input type="number" id="Price" name="Price" step="1"
                            style="display: flex; align-items: center; width: 42.5%;height: 24px;">
                    </div>







                    <div class="form-row">
                        <label for="image-upload">อัปโหลดรูปภาพ:</label>
                        <input type="file" id="image-upload" name="images[]" accept="image/*" multiple
                            onchange="validateFiles(this)">
                        <small>(สูงสุด 10 รูป)</small>
                    </div><br><br><br>
                    <div id="image-preview" class="image-preview"></div>

                    <!-- input hidden เพื่อเก็บชื่อไฟล์ที่ถูกลบ -->
                    <input type="hidden" id="deleted-images" name="deleted_images" value="">

                    <button type="submit"
                        style="background-color: #45a049;background-color: #4CAF50; color: white; border: none; padding: 10px 10px; font-size: 16px; width: 20%; margin-left: 80%; border-radius: 5px;">เพิ่มสินค้า</button>

                    <script>
                    function validateFiles(input) {
                        const preview = document.getElementById('image-preview');
                        const files = Array.from(input.files); // แปลงเป็นอาร์เรย์
                        const deletedImagesInput = document.getElementById('deleted-images');

                        // เก็บภาพที่แสดงอยู่แล้วในตัวอย่าง
                        const currentImages = preview.getElementsByTagName('img').length;

                        // ตรวจสอบว่าจำนวนรูปภาพทั้งหมดเกิน 10 หรือไม่
                        if (currentImages + files.length > 10) {
                            alert('สามารถอัปโหลดรูปภาพได้สูงสุด 10 รูป');
                            input.value = ''; // Reset input
                            return;
                        }

                        // เคลียร์ตัวอย่างรูปภาพเดิมก่อน เพื่อให้แสดงภาพที่เรียงตามลำดับ
                        preview.innerHTML = '';

                        // วนลูปผ่านไฟล์ทั้งหมดที่เลือก และแสดงเรียงลำดับ
                        files.forEach(file => {
                            const reader = new FileReader();
                            reader.onload = function(e) {
                                const imgWrapper = document.createElement(
                                'div'); // Container สำหรับรูปภาพและปุ่มลบ
                                imgWrapper.style.position =
                                'relative'; // กำหนดตำแหน่งแบบ relative เพื่อวางปุ่มลบ
                                imgWrapper.style.display = 'inline-block'; // จัดเรียงแบบ inline
                                imgWrapper.style.margin = '5px'; // เพิ่มระยะห่าง

                                const img = document.createElement('img');
                                img.src = e.target.result;
                                img.style.width = '100px'; // กำหนดความกว้างของตัวอย่างภาพ
                                img.style.border = '1px solid #ccc';
                                img.style.borderRadius = '4px';

                                const deleteButton = document.createElement('button'); // ปุ่มลบ
                                deleteButton.innerHTML = 'ลบ';
                                deleteButton.style.position = 'absolute';
                                deleteButton.style.top = '5px';
                                deleteButton.style.right = '5px';
                                deleteButton.style.backgroundColor = 'red';
                                deleteButton.style.color = 'white';
                                deleteButton.style.border = 'none';
                                deleteButton.style.borderRadius = '50%';
                                deleteButton.style.padding = '5px 8px';
                                deleteButton.style.cursor = 'pointer';

                                // กำหนดฟังก์ชันสำหรับการลบรูป
                                deleteButton.onclick = function() {
                                    preview.removeChild(imgWrapper);

                                    // เก็บชื่อไฟล์ที่ถูกลบลงใน input hidden
                                    const fileName = file.name;
                                    deletedImagesInput.value += fileName + ',';
                                };

                                imgWrapper.appendChild(img);
                                imgWrapper.appendChild(deleteButton);
                                preview.appendChild(imgWrapper);
                            };
                            reader.readAsDataURL(file);
                        });
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
            </form>

        </div>

        </div>

        <div style="display: flex; flex-direction: column;margin-left: -110px; margin-top:5%; ">
            <div>
                <button type="button" id="add-new-type" style="margin: 10px 0;background-color: #45a049;background-color: #0C8FC7;
    color: white;
    border: none;
    padding: 10px 10px;
    font-size: 14px;  border-radius: 5px;">เพิ่มประเภทใหม่</button>
                <form method="POST" action="process_add_product.php"></form>
            </div>

            <div>
                <button type="button" id="add-new-brand" style="margin: 10px 0;background-color: #45a049;background-color: #0C8FC7;
    color: white;
    border: none;
    padding: 10px 10px;
    font-size: 14px;  border-radius: 5px;">เพิ่มยี่ห้อใหม่</button>
                <form method="POST" action="process_add_brand.php"></form>
            </div>

            <div>
                <button type="button" id="add-new-material" style="margin: 10px 0;background-color: #45a049;background-color: #0C8FC7;
    color: white;
    border: none;
    padding: 10px 10px;
    font-size: 14px;  border-radius: 5px;">เพิ่มวัสดุใหม่</button>
                <form method="POST" action="process_add_material.php"></form>
            </div>


            <div>
                <button type="button" id="add-new-color" style="margin: 10px 0;background-color: #45a049;background-color: #0C8FC7;
    color: white;
    border: none;
    padding: 10px 10px;
    font-size: 14px;  border-radius: 5px;">เพิ่มสีใหม่</button>
                <form method="POST" action="process_add_color.php"></form>
            </div>


        </div>





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


        document.getElementById('add-new-color').addEventListener('click', function() {
            fetch('process_add_color.php')
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
    </body>

</html>