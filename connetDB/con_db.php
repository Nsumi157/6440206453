<?php
$servername = "localhost"; //ชื#อ Host ฐานข้อมูล
$username = "root"; //ชื#อผู้ใช้งานฐานข้อมูล
$password = ""; //รหัสผ่านเข้าฐานข้อมูล
$dbname = "dbonline_bag"; //ชื#อฐานข้อมูลเช่น data22565_รหัสนิสิต
// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
// echo "Connected successfully";
?>