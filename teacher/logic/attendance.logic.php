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

$success_message = null;
$error_message = null;

// จัดการการบันทึกการเช็คชื่อ
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'];
    $student_ids = $_POST['student_id'];
    $statuses = $_POST['status'];
    
    // ตรวจสอบว่าข้อมูลถูกต้อง
    if (!is_array($student_ids) || !is_array($statuses) || count($student_ids) !== count($statuses)) {
        $error_message = "ข้อมูลไม่ถูกต้อง กรุณาลองใหม่อีกครั้ง";
    } else {
        $success_count = 0;
        $error_count = 0;
    
        // วนลูปผ่านนักเรียนแต่ละคน
        for ($i = 0; $i < count($student_ids); $i++) {
            $student_id = intval($student_ids[$i]);
            $status = intval($statuses[$i]);
            
            // ตรวจสอบความถูกต้องของข้อมูล
            if ($student_id <= 0 || $status < 0 || $status > 4) {
                $error_count++;
                continue;
            }
            
            // ข้ามถ้าสถานะเป็น 0 (ยังไม่เช็ค)
            if ($status == 0) {
                continue;
            }
            
            // ตรวจสอบว่านักเรียนมีอยู่ในฐานข้อมูลหรือไม่
            $stmt = $conn->prepare("SELECT id FROM student WHERE id = ?");
            $stmt->bind_param("i", $student_id);
            $stmt->execute();
            $student_exists = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            
            if (!$student_exists) {
                $error_count++;
                continue;
            }
            
            // ตรวจสอบว่ามีการเช็คชื่อแล้วหรือไม่
            $stmt = $conn->prepare("SELECT id FROM attendance WHERE student_id = ? AND date = ? AND teacher_id = ?");
            $stmt->bind_param("isi", $student_id, $date, $teacher['id']);
            $stmt->execute();
            $existing = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            
            if ($existing) {
                // อัปเดตข้อมูลที่มีอยู่
                $stmt = $conn->prepare("UPDATE attendance SET status = ?, timestamp = NOW() WHERE id = ?");
                $stmt->bind_param("ii", $status, $existing['id']);
            } else {
                // เพิ่มข้อมูลใหม่
                $stmt = $conn->prepare("INSERT INTO attendance (student_id, date, status, teacher_id) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("isii", $student_id, $date, $status, $teacher['id']);
            }
            
            if ($stmt->execute()) {
                $success_count++;
            } else {
                $error_count++;
                // Log the error for debugging
                error_log("Attendance insert/update failed for student_id: $student_id, status: $status, date: $date, teacher_id: " . $teacher['id']);
            }
            $stmt->close();
        }
        
        if ($success_count > 0) {
            $success_message = "บันทึกการเช็คชื่อเรียบร้อยแล้ว " . $success_count . " รายการ";
        }
        if ($error_count > 0) {
            $error_message = "เกิดข้อผิดพลาดในการบันทึกข้อมูล " . $error_count . " รายการ (อาจเป็นเพราะข้อมูลนักเรียนไม่ถูกต้อง)";
        }
    }
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

// วันที่เลือก (เริ่มต้นเป็นวันนี้)
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');

// ดึงข้อมูลการเช็คชื่อของวันที่เลือก
$stmt = $conn->prepare("SELECT a.*, s.name, s.surname, s.number 
                       FROM attendance a 
                       JOIN student s ON a.student_id = s.id 
                       WHERE a.date = ? AND a.teacher_id = ? 
                       ORDER BY s.number");
$stmt->bind_param("si", $selected_date, $teacher['id']);
$stmt->execute();
$attendance_data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// สร้าง array สำหรับการแสดงผล
$attendance_status = [];
foreach ($attendance_data as $att) {
    $attendance_status[$att['student_id']] = $att['status'];
}
?> 