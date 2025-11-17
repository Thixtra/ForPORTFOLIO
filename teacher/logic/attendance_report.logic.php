<?php
session_start();
require_once '../config.php';

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['user_id']) || $_SESSION['status'] != 2) {
    header("Location: ../../login.php");
    exit;
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

// วันที่เลือก (เริ่มต้นเป็นวันนี้)
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// ฟังก์ชันตรวจสอบเพศจากคำนำหน้า
function getGender($title) {
    $male_titles = ['เด็กชาย', 'นาย'];
    $female_titles = ['เด็กหญิง', 'นางสาว', 'นาง'];
    
    if (in_array($title, $male_titles)) {
        return 'male';
    } elseif (in_array($title, $female_titles)) {
        return 'female';
    }
    return 'unknown';
}

// ดึงข้อมูลการเช็คชื่อของวันที่เลือก
$stmt = $conn->prepare("SELECT a.*, s.name, s.surname, s.title, s.number 
                       FROM attendance a 
                       JOIN student s ON a.student_id = s.id 
                       WHERE a.date = ? AND a.teacher_id = ? 
                       ORDER BY s.number");
$stmt->bind_param("si", $selected_date, $teacher['id']);
$stmt->execute();
$attendance_data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// คำนวณสถิติ
$stats = [
    'male' => ['present' => 0, 'absent' => 0, 'leave' => 0, 'late' => 0],
    'female' => ['present' => 0, 'absent' => 0, 'leave' => 0, 'late' => 0]
];

$absent_students = [];
$leave_students = [];
$late_students = [];

foreach ($attendance_data as $att) {
    $gender = getGender($att['title']);
    $student_name = $att['title'] . $att['name'] . ' ' . $att['surname'];
    
    switch ($att['status']) {
        case 1: // มา
            $stats[$gender]['present']++;
            break;
        case 2: // ขาด
            $stats[$gender]['absent']++;
            $absent_students[] = $student_name;
            break;
        case 3: // ลา
            $stats[$gender]['leave']++;
            $leave_students[] = $student_name;
            break;
        case 4: // สาย
            $stats[$gender]['late']++;
            $late_students[] = $student_name;
            break;
    }
}

// คำนวณยอดรวม
$total_male = array_sum($stats['male']);
$total_female = array_sum($stats['female']);
$total_present = $stats['male']['present'] + $stats['female']['present'];
$total_absent = $stats['male']['absent'] + $stats['female']['absent'];
$total_leave = $stats['male']['leave'] + $stats['female']['leave'];
$total_late = $stats['male']['late'] + $stats['female']['late'];
$grand_total = $total_present + $total_absent + $total_leave + $total_late;
?> 