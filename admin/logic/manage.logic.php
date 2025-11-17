<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['status'] != 1) {
    header('Location: ../login.php');
    exit;
}
require_once '../config.php';
$success = null;
$error = null;

// ตั้งค่า pagination
$items_per_page = 10;
$student_page = isset($_GET['student_page']) ? (int)$_GET['student_page'] : 1;
$teacher_page = isset($_GET['teacher_page']) ? (int)$_GET['teacher_page'] : 1;

if (isset($_GET['delete_student'])) {
    $student_id = $_GET['delete_student'];
    $stmt = $conn->prepare('DELETE FROM student WHERE id = ?');
    $stmt->bind_param('i', $student_id);
    if ($stmt->execute()) {
        $success = "ลบข้อมูลนักเรียนสำเร็จ";
    } else {
        $error = "ลบข้อมูลนักเรียนไม่สำเร็จ";
    }
    $stmt->close();
}
if (isset($_GET['delete_teacher'])) {
    $teacher_id = $_GET['delete_teacher'];
    $stmt = $conn->prepare('DELETE FROM teacher WHERE id = ?');
    $stmt->bind_param('i', $teacher_id);
    if ($stmt->execute()) {
        $success = "ลบข้อมูลอาจารย์สำเร็จ";
    } else {
        $error = "ลบข้อมูลอาจารย์ไม่สำเร็จ";
    }
    $stmt->close();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_student'])) {
    $student_id = $_POST['student_id'];
    $year = $_POST['year'];
    $title = $_POST['title'];
    $name = $_POST['name'];
    $surname = $_POST['surname'];
    $number = $_POST['number'];
    $classroom = $_POST['classroom'];
    $stmt = $conn->prepare('UPDATE student SET year = ?, title = ?, name = ?, surname = ?, number = ?, classroom = ? WHERE id = ?');
    $stmt->bind_param('isssisi', $year, $title, $name, $surname, $number, $classroom, $student_id);
    if ($stmt->execute()) {
        $success = "แก้ไขข้อมูลนักเรียนสำเร็จ";
    } else {
        $error = "แก้ไขข้อมูลนักเรียนไม่สำเร็จ";
    }
    $stmt->close();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_teacher'])) {
    $teacher_id = $_POST['teacher_id'];
    $year = $_POST['year'];
    $title = $_POST['title'];
    $name = $_POST['name'];
    $surname = $_POST['surname'];
    $classroom = $_POST['classroom'];
    $subject_group = $_POST['subject_group'];
    $stmt = $conn->prepare('UPDATE teacher SET year = ?, title = ?, name = ?, surname = ?, classroom = ?, subject_group = ? WHERE id = ?');
    $stmt->bind_param('isssssi', $year, $title, $name, $surname, $classroom, $subject_group, $teacher_id);
    if ($stmt->execute()) {
        $success = "แก้ไขข้อมูลอาจารย์สำเร็จ";
    } else {
        $error = "แก้ไขข้อมูลอาจารย์ไม่สำเร็จ";
    }
    $stmt->close();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_attendance'])) {
    $date = $_POST['date'];
    $classroom = $_POST['classroom'];
    $user_id = $_SESSION['user_id']; // เก็บ user_id ไว้ก่อน

    // ดึง teacher.id จาก user_id
    $stmt = $conn->prepare("SELECT id FROM teacher WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($teacher_id);
    $stmt->fetch();
    $stmt->close();

    $stmt = $conn->prepare('DELETE FROM attendance WHERE date = ? AND student_id IN (SELECT id FROM student WHERE classroom = ?)');
    $stmt->bind_param('ss', $date, $classroom);
    $stmt->execute();
    $stmt->close();
    if (isset($_POST['attendance'])) {
        $stmt = $conn->prepare('INSERT INTO attendance (student_id, date, status, teacher_id) VALUES (?, ?, ?, ?)');
        foreach ($_POST['attendance'] as $student_id => $status) {
            $stmt->bind_param('isis', $student_id, $date, $status, $teacher_id);
            $stmt->execute();
        }
        $stmt->close();
        $success = "บันทึกการเช็คชื่อสำเร็จ";
    }
}

// ดึงข้อมูลนักเรียนพร้อม pagination
$students = [];
$total_students = 0;

// นับจำนวนนักเรียนทั้งหมด
$stmt = $conn->prepare('SELECT COUNT(*) as total FROM student');
$stmt->execute();
$result = $stmt->get_result();
$total_students = $result->fetch_assoc()['total'];
$stmt->close();

// คำนวณ pagination สำหรับนักเรียน
$total_student_pages = ceil($total_students / $items_per_page);
$student_offset = ($student_page - 1) * $items_per_page;

// ดึงข้อมูลนักเรียนตามหน้า
$stmt = $conn->prepare('SELECT s.*, u.userId FROM student s JOIN user u ON s.user_id = u.id ORDER BY s.classroom, s.number LIMIT ? OFFSET ?');
$stmt->bind_param('ii', $items_per_page, $student_offset);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}
$stmt->close();

// ดึงข้อมูลอาจารย์พร้อม pagination
$teachers = [];
$total_teachers = 0;

// นับจำนวนอาจารย์ทั้งหมด
$stmt = $conn->prepare('SELECT COUNT(*) as total FROM teacher');
$stmt->execute();
$result = $stmt->get_result();
$total_teachers = $result->fetch_assoc()['total'];
$stmt->close();

// คำนวณ pagination สำหรับอาจารย์
$total_teacher_pages = ceil($total_teachers / $items_per_page);
$teacher_offset = ($teacher_page - 1) * $items_per_page;

// ดึงข้อมูลอาจารย์ตามหน้า
$stmt = $conn->prepare('SELECT t.*, u.userId FROM teacher t JOIN user u ON t.user_id = u.id ORDER BY t.name LIMIT ? OFFSET ?');
$stmt->bind_param('ii', $items_per_page, $teacher_offset);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $teachers[] = $row;
}
$stmt->close();

$classrooms = [];
$stmt = $conn->prepare('SELECT DISTINCT classroom FROM student ORDER BY classroom');
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $classrooms[] = $row['classroom'];
}
$stmt->close();
$attendance_data = [];
if (isset($_GET['classroom']) && isset($_GET['date'])) {
    $classroom = $_GET['classroom'];
    $date = $_GET['date'];
    $stmt = $conn->prepare('SELECT s.id, s.name, s.surname, s.number, a.status FROM student s LEFT JOIN attendance a ON s.id = a.student_id AND a.date = ? WHERE s.classroom = ? ORDER BY s.number');
    $stmt->bind_param('ss', $date, $classroom);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $attendance_data[] = $row;
    }
    $stmt->close();
} 