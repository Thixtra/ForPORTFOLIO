<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['status'] != 1) {
    header('Location: ../login.php');
    exit;
}

// ตรวจสอบโหมด Mock
if (defined('MOCK_MODE') && MOCK_MODE === true) {
    require_once '../config_mock.php';
} else {
    require_once '../config.php';
}

$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-t');

// ถ้าเป็นโหมด Mock
if (defined('MOCK_MODE') && MOCK_MODE === true) {
    $stats = MockData::getAttendanceStats($start_date, $end_date);
    $teacher_status = MockData::getTeacherStatus(date('Y-m-d'));
} else {
    // โหมดปกติ
    $stats = ['มา' => 0, 'ขาด' => 0, 'ลา' => 0, 'สาย' => 0];
    $sql = "SELECT status, COUNT(*) as count FROM attendance WHERE date BETWEEN '$start_date' AND '$end_date' GROUP BY status";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        switch ($row['status']) {
            case 1: $stats['มา'] = $row['count']; break;
            case 0: $stats['ขาด'] = $row['count']; break;
            case 2: $stats['ลา'] = $row['count']; break;
            case 3: $stats['สาย'] = $row['count']; break;
        }
    }
    $teacher_status = [];
    $sql = "SELECT t.name, t.surname, t.classroom, (SELECT COUNT(*) FROM attendance a WHERE a.teacher_id = t.id AND a.date = CURDATE()) as checked FROM teacher t";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $teacher_status[] = $row;
    }
} 