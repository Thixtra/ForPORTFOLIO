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

$filename = 'student_template_' . date('Y-m-d_H-i-s') . '.csv';
$filepath = 'uploads/' . $filename;

// สร้าง template CSV
$template = "รหัสนักเรียน,คำนำหน้า,ชื่อ,นามสกุล,ปีการศึกษา,เลขที่,ห้องเรียน,เลขบัตรประชาชน\n";
$template .= "64010001,นาย,สมชาย,ใจดี,2567,1,ม.4/1,1234567890123\n";
$template .= "64010002,นางสาว,สมหญิง,ใจงาม,2567,2,ม.4/1,1234567890124\n";
$template .= "64010003,นาย,สมศักดิ์,รักดี,2567,3,ม.4/1,1234567890125\n";

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