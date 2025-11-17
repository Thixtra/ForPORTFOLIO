<?php
session_start();

// ตรวจสอบโหมด Mock
if (defined('MOCK_MODE') && MOCK_MODE === true) {
    require_once '../config_mock.php';
} else {
    require_once '../config.php';
}

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['user_id']) || $_SESSION['status'] != 2) {
    header("Location: ../../login.php");
    exit;
}

// ถ้าเป็นโหมด Mock
if (defined('MOCK_MODE') && MOCK_MODE === true) {
    $teacher_id = $_SESSION['user_id'];
    $teacher = MockData::getTeacherByUserId($teacher_id);
    
    if (!$teacher) {
        header("Location: ../../login.php");
        exit;
    }
    
    // ดึงข้อมูลนักเรียนในห้องเรียน
    $students = MockData::getStudentsByClassroom($teacher['classroom']);
    
    // ดึงข้อมูลการเช็คชื่อวันนี้
    $today = date('Y-m-d');
    $today_attendance = MockData::getAttendanceByDateAndTeacher($today, $teacher['id']);
    
    // สร้าง array สำหรับการแสดงผล
    $attendance_status = [];
    foreach ($today_attendance as $att) {
        $attendance_status[$att['student_id']] = $att['status'];
    }
    
    // นับสถิติการมาเรียนวันนี้
    $stats = [
        'มา' => 0,
        'ขาด' => 0,
        'ลา' => 0,
        'สาย' => 0
    ];
    
    foreach ($attendance_status as $status) {
        switch ($status) {
            case 1: $stats['มา']++; break;
            case 2: $stats['ขาด']++; break;
            case 3: $stats['ลา']++; break;
            case 4: $stats['สาย']++; break;
        }
    }
    
    // คำนวณวันจันทร์ของสัปดาห์ปัจจุบัน
    $monday = date('Y-m-d', strtotime('monday this week'));
    $weekly_stats = MockData::getWeeklyStats($teacher['id'], $monday);
} else {
    // โหมดปกติ
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

    // ดึงข้อมูลการเช็คชื่อวันนี้
    $today = date('Y-m-d');
    $stmt = $conn->prepare("SELECT a.*, s.name, s.surname, s.number 
                           FROM attendance a 
                           JOIN student s ON a.student_id = s.id 
                           WHERE a.date = ? AND a.teacher_id = ? 
                           ORDER BY s.number");
    $stmt->bind_param("si", $today, $teacher['id']);
    $stmt->execute();
    $today_attendance = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // สร้าง array สำหรับการแสดงผล
    $attendance_status = [];
    foreach ($today_attendance as $att) {
        $attendance_status[$att['student_id']] = $att['status'];
    }

    // นับสถิติการมาเรียนวันนี้
    $stats = [
        'มา' => 0,
        'ขาด' => 0,
        'ลา' => 0,
        'สาย' => 0
    ];

    foreach ($attendance_status as $status) {
        switch ($status) {
            case 1: $stats['มา']++; break;
            case 2: $stats['ขาด']++; break;
            case 3: $stats['ลา']++; break;
            case 4: $stats['สาย']++; break;
        }
    }

    // คำนวณวันจันทร์ของสัปดาห์ปัจจุบัน (เริ่มต้นสัปดาห์)
    $monday = date('Y-m-d', strtotime('monday this week'));

    // ดึงข้อมูลการเช็คชื่อตั้งแต่วันจันทร์ของสัปดาห์ปัจจุบัน
    $stmt = $conn->prepare("SELECT DATE(date) as date, 
                           SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as present,
                           SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) as absent,
                           SUM(CASE WHEN status = 3 THEN 1 ELSE 0 END) as leave_status,
                           SUM(CASE WHEN status = 4 THEN 1 ELSE 0 END) as late
                           FROM attendance 
                           WHERE teacher_id = ? AND date >= ? AND WEEKDAY(date) < 5
                           GROUP BY DATE(date) 
                           ORDER BY date");
    $stmt->bind_param("is", $teacher['id'], $monday);
    $stmt->execute();
    $weekly_stats = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?> 