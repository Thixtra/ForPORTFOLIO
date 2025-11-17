<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['status'] != 1) {
    header('Location: ../login.php');
    exit;
}
require_once '../config.php';
$success = null;
$error = null;
$classrooms = [];
$stmt = $conn->prepare('SELECT DISTINCT classroom FROM student ORDER BY classroom');
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $classrooms[] = $row['classroom'];
}
$stmt->close();
$report_data = [];
$summary = [];
if (isset($_GET['classroom']) && isset($_GET['start_date']) && isset($_GET['end_date'])) {
    $classroom = $_GET['classroom'];
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];
    $stmt = $conn->prepare('SELECT s.id, s.name, s.surname, s.number, a.date, a.status, CASE WHEN a.status = 1 THEN "มา" WHEN a.status = 2 THEN "สาย" WHEN a.status = 3 THEN "ขาด" WHEN a.status = 4 THEN "ลา" ELSE "ไม่ระบุ" END as status_text FROM student s LEFT JOIN attendance a ON s.id = a.student_id AND a.date BETWEEN ? AND ? WHERE s.classroom = ? ORDER BY s.number, a.date');
    $stmt->bind_param('sss', $start_date, $end_date, $classroom);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $report_data[] = $row;
    }
    $stmt->close();
    $stmt = $conn->prepare('SELECT s.id, s.name, s.surname, s.number, COUNT(CASE WHEN a.status = 1 THEN 1 END) as present, COUNT(CASE WHEN a.status = 2 THEN 1 END) as late, COUNT(CASE WHEN a.status = 3 THEN 1 END) as absent, COUNT(CASE WHEN a.status = 4 THEN 1 END) as leave_count, COUNT(a.id) as total_days FROM student s LEFT JOIN attendance a ON s.id = a.student_id AND a.date BETWEEN ? AND ? WHERE s.classroom = ? GROUP BY s.id, s.name, s.surname, s.number ORDER BY s.number');
    $stmt->bind_param('sss', $start_date, $end_date, $classroom);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $summary[] = $row;
    }
    $stmt->close();
} 