 
 
 
 
 <?php

// if (isset($_FILES['images'])) {
//     $totalFiles = count($_FILES['images']['name']);
    
//     // ตรวจสอบจำนวนไฟล์ไม่เกิน 10
//     if ($totalFiles > 10) {
//         echo "สามารถอัพโหลดได้สูงสุด 10 ภาพเท่านั้น";
//     } else {
//         for ($i = 0; $i < $totalFiles; $i++) {
//             $fileName = $_FILES['images']['name'][$i];
//             $fileTmpName = $_FILES['images']['tmp_name'][$i];
//             $fileError = $_FILES['images']['error'][$i];

//             // ตรวจสอบว่ามีข้อผิดพลาดในการอัปโหลดไฟล์
//             if ($fileError === 0) {
//                 // กำหนดตำแหน่งที่จะเก็บไฟล์
//                 $destination = 'uploads/' . $fileName;
                
//                 // อัปโหลดไฟล์
//                 if (move_uploaded_file($fileTmpName, $destination)) {
//                     // เพิ่มข้อมูลรูปภาพลงในฐานข้อมูล
//                     $bagId = $bagId; // ค่า Bag_id ที่คุณต้องการใส่
//                     $stmt = $conn->prepare("INSERT INTO `pictures`(`B_img`, `Bag_id`) VALUES (?, ?)");
//                     $stmt->bind_param("si", $fileName, $bagId); // ประเภทข้อมูล: "s" = string, "i" = integer

//                     if ($stmt->execute()) {
//                         echo "อัปโหลดและบันทึกรูปภาพเรียบร้อยแล้ว: " . htmlspecialchars($fileName);
//                     } else {
//                         echo "เกิดข้อผิดพลาดในการบันทึกข้อมูลรูปภาพ: " . htmlspecialchars($fileName);
//                     }
//                     $stmt->close();
//                 }
//             } else {
//                 echo "มีข้อผิดพลาดในการอัปโหลดไฟล์ที่ " . htmlspecialchars($fileName);
//             }
//         }
//     }
// }















            //    $sql = "SELECT brand_id, brand_name FROM bag_brand";
            //    $result = $conn->query($sql);
            //    ?>
            //     <div class="form-row">
            //         <label for="brand">ยี่ห้อ:</label>
            //         <select id="brand" name="brand">
            //         <option value=""> </option>
            //             <?php
            //             if ($result->num_rows > 0) {
            //                 while ($row = $result->fetch_assoc()) {
            //                     echo "<option value='" . htmlspecialchars($row['brand_id'], ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars($row['brand_name'], ENT_QUOTES, 'UTF-8') . "</option>";
            //                 }
            //             }
            //             ?>
            //         </select>
            //     </div>


                <?php
               $sql = "SELECT Material_id, Material_name FROM material";
               $result = $conn->query($sql);
               ?>

                <div class="form-row">
                    <label for="material">วัสดุ:</label>
                    <select id="material" name="material">
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
                

                <div class="color-options">
                    <label for="color">สี:</label>
                    <div class="container" style="display: grid; grid-template-columns: repeat(4, 1fr);margin-top: -23px;">
                       
                            <div class="card" style=" margin-left:110px;">
                                <div class="color-option" style="background-color: black; border: 2px solid #ADADAD;" data-color="black"></div>
                                <span class="card-text">สีดำ</span>
                            </div>
                            <div class="card" style="margin-left: 5px;">
                                <div class="color-option" style="background-color: white; border: 2px solid #ADADAD;" data-color="white"></div>
                                <span class="card-text">สีขาว</span>
                            </div>
                            <div class="card"style="margin-left: 5px;">
                                <div class="color-option" style="background-color: Yellow; border: 2px solid #ADADAD;" data-color="black"></div>
                                <span class="card-text">สีเหลือง</span>
                            </div>
                            <div class="card" style="margin-left: 5px;">
                                <div class="color-option" style="background-color: #7E5C19; border: 2px solid #ADADAD;" data-color="white"></div>
                                <span class="card-text">สีน้ำตาล</span>
                            </div>
                            <div class="card" style=" margin-left: 110px;">
                                <div class="color-option" style="background-color: #D1FEFE; border: 2px solid #ADADAD;" data-color="black"></div>
                                <span class="card-text">สีฟ้า</span>
                            </div>
                            <div class="card" style="margin-left: 5px;">
                                <div class="color-option" style="background-color: #EBD6FF; border: 2px solid #ADADAD;" data-color="white"></div>
                                <span class="card-text">สีม่วง</span>
                            </div>
                            <div class="card"style="margin-left: 5px;">
                                <div class="color-option" style="background-color: #E6BDBD; border: 2px solid #ADADAD;" data-color="Yellow"></div>
                                <span class="card-text">สีชมพูม่วง</span>
                            </div>
                            <div class="card" style="margin-left: 5px;">
                                <div class="color-option" style="background-color: #ADADAD; border: 2px solid #ADADAD;" data-color="Brown"></div>
                                <span class="card-text">สีเทา</span>
                            </div>
                    </div>
                    </div>
                    
                </div><br>
                <div class="form-row">
                    <label for="bag-id">จำนวน:</label>
                    <input type="text" id="bag-id" name="bag-id" >
                    <span class="unit">ชิ้น</span>
                </div>


                <div class="form-row">
                    <label for="bag-id">ราคาต้นทุน:</label>
                    <input type="text" id="bag-id" name="bag-id" >
                    <span class="unit">บาท</span>
                </div>



                <div class="form-row">
                    <label for="bag-id">ราคาขาย:</label>
                    <input type="text" id="bag-id" name="bag-id" >
                    <span class="unit">บาท</span>
                </div>



                <div class="form-row">
                    <label for="material">สถานะสินค้า:</label>
                    <select id="material" name="material">
                        <option value="01">   </option>
                        <option value="01">มี</option>
                        <option value="02">ไม่มี</option>
                    </select>
                </div>
              <div class="form-row">
    <label for="file-upload" class="label-with-icon">
        ไฟล์รูปภาพ:
        <box-icon type='solid' name='file-plus'></box-icon>
    </label>
</div>


            </div>
        </div>
    </div>




























































































<div class="payment-status">
                    <?php if (!empty($items)): ?>
                        <p class="status_name"><?php echo htmlspecialchars($item['status_name']); ?></p>
                        <p><span id="countdown">1:00</span></p>
                    <?php endif; ?>
                </div>









                <script>
        // ตั้งเวลานับถอยหลัง 1 นาที
        var timeleft = 60;
        var countdownTimer = setInterval(function() {
            timeleft--;
            var minutes = Math.floor(timeleft / 60);
            var seconds = timeleft % 60;
            document.getElementById("countdown").textContent = minutes + ":" + (seconds < 10 ? "0" : "") + seconds;

            if (timeleft <= 0) {
                clearInterval(countdownTimer);
                document.getElementById("countdown").textContent = "หมดเวลา";
                
                // ส่งค่าไปยัง server เพื่อตั้งค่า ostatus_id เป็น 7
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "mem_pay.php", true);
                xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                xhr.send("update_status=true&receipt_id=" + "<?php echo htmlspecialchars($_GET['receipt_id']); ?>");
            }
        }, 1000);
    </script>





if (isset($_SESSION['target_time']) && $current_time > $_SESSION['target_time']) {
    // อัปเดต Status_id เป็น 'c'
    if (isset($_SESSION['order_data'])) {
        $order_id = $_SESSION['order_data']['Order_id'];
        $stmt = $conn->prepare("UPDATE orders SET Status_id = 'c' WHERE Order_id = ?");
        $stmt->bind_param("s", $order_id);
        $stmt->execute();
        $stmt->close();
        
        // ลบ target_time เพื่อไม่ให้ทำการอัปเดตสถานะซ้ำ
        unset($_SESSION['target_time']);
        unset($_SESSION['order_id']);
    }
}