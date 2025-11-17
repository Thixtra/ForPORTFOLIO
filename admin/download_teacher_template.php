<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['status'] != 1) {
    header('Location: ../login.php');
    exit;
}

// สร้างโฟลเดอร์ถ้ายังไม่มี
if (!is_dir('uploads')) {
    mkdir('uploads', 0777, true);
}

$filename = 'teacher_template_' . date('Y-m-d_H-i-s') . '.csv';
$filepath = 'uploads/' . $filename;

// สร้าง template CSV สำหรับอาจารย์
$template = "รหัสอาจารย์,ปีการศึกษา,คำนำหน้า,ชื่อ,นามสกุล,ห้องเรียน,กลุ่มสาระ/กลุ่มวิชา,เลขบัตรประชาชน\n";
$template .= "1001,2567,นาย,สมชาย,ใจดี,ม.4/1,คณิตศาสตร์,1234567890123\n";
$template .= "1002,2567,นางสาว,สมหญิง,ใจงาม,ม.4/2,วิทยาศาสตร์,1234567890124\n";
$template .= "1003,2567,นาย,สมศักดิ์,รักดี,ม.5/1,ภาษาไทย,1234567890125\n";
$template .= "1004,2567,นาง,สมศรี,ใจเย็น,ม.5/2,สังคมศึกษา,1234567890126\n";

// เพิ่ม BOM สำหรับ UTF-8
file_put_contents($filepath, "\xEF\xBB\xBF" . $template);

// ส่งไฟล์ให้ดาวน์โหลด
if (file_exists($filepath)) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($filepath));
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    
    readfile($filepath);
    unlink($filepath); // ลบไฟล์หลังจากดาวน์โหลด
    exit;
} else {
    echo "เกิดข้อผิดพลาดในการสร้างไฟล์ template";
}
?> 