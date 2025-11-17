<?php
session_start();
require_once '../config.php';

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['user_id']) || $_SESSION['status'] != 2) {
    header("Location: ../../login.php");
    exit;
}

$success = null;
$error = null;

// จัดการการส่งการแจ้งเตือน
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_notification'])) {
    $title = trim($_POST['notification_title']);
    $message = trim($_POST['notification_message']);
    $type = $_POST['notification_type'];
    
    if (empty($title) || empty($message)) {
        $error = "กรุณากรอกหัวข้อและข้อความ";
    } else {
        // ดึงข้อมูลอาจารย์
        $teacher_id = $_SESSION['user_id'];
        $stmt = $conn->prepare("SELECT id FROM teacher WHERE user_id = ?");
        $stmt->bind_param("i", $teacher_id);
        $stmt->execute();
        $teacher = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if ($teacher) {
            // บันทึกการแจ้งเตือน
            $stmt = $conn->prepare("INSERT INTO notifications (teacher_id, title, message, type) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $teacher['id'], $title, $message, $type);
            
            if ($stmt->execute()) {
                $success = "ส่งการแจ้งเตือนสำเร็จ";
            } else {
                $error = "เกิดข้อผิดพลาดในการส่งการแจ้งเตือน";
            }
            $stmt->close();
        } else {
            $error = "ไม่พบข้อมูลอาจารย์";
        }
    }
}

// ดึงข้อมูลอาจารย์
$teacher_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT t.*, u.username FROM teacher t 
                       JOIN user u ON t.user_id = u.id 
                       WHERE t.user_id = ?");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$teacher = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$teacher) {
    header("Location: ../../login.php");
    exit;
}

// ดึงข้อมูลนักเรียนในห้องเรียน
$stmt = $conn->prepare("SELECT s.*, u.username FROM student s 
                       JOIN user u ON s.user_id = u.id 
                       WHERE s.classroom = ? 
                       ORDER BY s.number");
$stmt->bind_param("s", $teacher['classroom']);
$stmt->execute();
$students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// คำนวณวันจันทร์ของสัปดาห์ปัจจุบัน
$monday = date('Y-m-d', strtotime('monday this week'));

// ดึงข้อมูลสถิติการมาเรียนของนักเรียนแต่ละคน (สัปดาห์นี้)
$stmt = $conn->prepare("SELECT s.id,
                       SUM(CASE WHEN a.status = 1 THEN 1 ELSE 0 END) as present,
                       SUM(CASE WHEN a.status = 2 THEN 1 ELSE 0 END) as absent,
                       SUM(CASE WHEN a.status = 3 THEN 1 ELSE 0 END) as leave_status,
                       SUM(CASE WHEN a.status = 4 THEN 1 ELSE 0 END) as late,
                       COUNT(a.id) as total_days
                       FROM student s
                       LEFT JOIN attendance a ON s.id = a.student_id AND a.date >= ? AND a.date <= CURDATE() AND a.teacher_id = ? AND WEEKDAY(a.date) < 5
                       WHERE s.classroom = ?
                       GROUP BY s.id");
$stmt->bind_param("sis", $monday, $teacher['id'], $teacher['classroom']);
$stmt->execute();
$student_stats = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// สร้าง array สำหรับการแสดงผล
$stats_by_student = [];
foreach ($student_stats as $stat) {
    $stats_by_student[$stat['id']] = $stat;
}

// นับจำนวนนักเรียนทั้งหมด
$total_students = count($students);
?> 