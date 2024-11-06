<div class="order-info">
                    <p>คะแนน </p>
                    <?php
                    // Query เพื่อคำนวณคะแนนเฉลี่ยของสินค้าแต่ละตัวตาม Jeans_id
                    $sql = "SELECT SUM(Point) AS total_points, COUNT(Point) AS total_reviewers
                    FROM orders
                    WHERE Point IS NOT NULL AND Jeans_id = ?";
                    $stmt_review = $conn->prepare($sql);
                    $stmt_review->bind_param('i', $Jeans_id);
                    $stmt_review->execute();
                    $result_review = $stmt_review->get_result();

                    if ($result_review->num_rows > 0) {
                        $row = $result_review->fetch_assoc();
                        $total_points = $row['total_points'];
                        $total_reviewers = $row['total_reviewers'];

                        // คำนวณคะแนนเฉลี่ย
                        $average_point = $total_reviewers > 0 ? $total_points / $total_reviewers : 0;

                        // ตรวจสอบให้คะแนนไม่เกิน 5.0
                        if ($average_point > 5) {
                            $average_point = 5.0;
                        }

                        // แสดงดาว
                        $full_stars = floor($average_point); // ดาวเต็ม
                        $half_star = ($average_point - $full_stars) >= 0.5 ? 1 : 0; // ดาวครึ่ง
                        $empty_stars = 5 - $full_stars - $half_star; // ดาวว่าง
            
                        // สร้างการแสดงดาว
                        for ($i = 0; $i < $full_stars; $i++) {
                            echo '<i class="bi bi-star-fill" style="color: gold;"></i>'; // ดาวเต็ม
                        }
                        if ($half_star) {
                            echo '<i class="bi bi-star-half" style="color: gold;"></i>'; // ดาวครึ่ง
                        }
                        for ($i = 0; $i < $empty_stars; $i++) {
                            echo '<i class="bi bi-star" style="color: gold;"></i>'; // ดาวว่าง
                        }

                        // แสดงคะแนนเฉลี่ย
                        echo number_format($average_point, 1); // แสดง 1 ตำแหน่งทศนิยม
            
                    } else {
                        echo "ไม่มีข้อมูล";
                    }
                    ?>

                </div>

                <div class="reviews">
                    <h3>รีวิว</h3>
                    <?php if ($result_reviews->num_rows > 0): ?>
                        <ul>
                            <?php while ($row = $result_reviews->fetch_assoc()): ?>
                                <?php if (!empty($row['Review'])): // ตรวจสอบว่ามีการรีวิวหรือไม่ ?>
                                    <li>
                                        <strong><?php echo htmlspecialchars($row['First_name'] . ' ' . $row['Last_name']); ?></strong>
                                        <p><?php echo htmlspecialchars($row['Review']); ?></p>
                                    </li>
                                <?php endif; ?>
                            <?php endwhile; ?>
                        </ul>
                    <?php else: ?>
                        <p>ไม่มีรีวิวสำหรับสินค้านี้</p>
                    <?php endif; ?>
                </div>
        </body>
        <script>
คะแนนกับรีวิว



/// Query เพื่อคำนวณคะแนนเฉลี่ยของสินค้าแต่ละตัวตาม Jeans_id
$sql_reviews = "
        SELECT orders.Review, member.First_name, member.Last_name 
        FROM orders 
        JOIN member ON orders.Email_member = member.Email_member 
        WHERE orders.Jeans_id = ?
        ORDER BY orders.Order_date DESC, orders.Order_time DESC";
        $stmt_reviews = $conn->prepare($sql_reviews);
        $stmt_reviews->bind_param('i', $Jeans_id);
        $stmt_reviews->execute();
        $result_reviews = $stmt_reviews->get_result();
ของรีวิว