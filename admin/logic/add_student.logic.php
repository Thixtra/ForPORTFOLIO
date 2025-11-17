<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['status'] != 1) {
    header('Location: ../login.php');
    exit;
}
require_once '../config.php';

// ตั้งค่า charset เป็น UTF-8
$conn->set_charset("utf8mb4");

$success = null;
$error = null;
$excel_success = null;
$excel_error = null;

// จัดการการเพิ่มนักเรียนแบบรายบุคคล
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['multi_student'])) {
    $success = [];
    $error = [];
    $count = count($_POST['student_id']);
    for ($i = 0; $i < $count; $i++) {
        $student_id = trim($_POST['student_id'][$i]);
        $student_name = trim($_POST['student_name'][$i]);
        $student_surname = trim($_POST['student_surname'][$i]);
        $student_title = trim($_POST['student_title'][$i]);
        $student_year = trim($_POST['student_year'][$i]);
        $student_number = trim($_POST['student_number'][$i]);
        $student_classroom = trim($_POST['student_classroom'][$i]);
        $student_citizen_id = trim($_POST['student_citizen_id'][$i]);
        $username = $student_id;
        $password = password_hash($student_citizen_id, PASSWORD_DEFAULT);
        $status = 3;
        $stmt = $conn->prepare('SELECT id FROM user WHERE userId = ?');
        $stmt->bind_param('s', $student_id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error[] = "รหัสนักเรียน $student_id มีอยู่แล้ว";
        } else {
            $stmt = $conn->prepare('INSERT INTO user (userId, username, password, status) VALUES (?, ?, ?, ?)');
            $stmt->bind_param('sssi', $student_id, $username, $password, $status);
            if ($stmt->execute()) {
                $user_id = $stmt->insert_id;
                $stmt2 = $conn->prepare('INSERT INTO student (user_id, year, title, name, surname, number, classroom) VALUES (?, ?, ?, ?, ?, ?, ?)');
                $stmt2->bind_param('iisssis', $user_id, $student_year, $student_title, $student_name, $student_surname, $student_number, $student_classroom);
                if ($stmt2->execute()) {
                    $success[] = "เพิ่ม $student_id สำเร็จ";
                } else {
                    $error[] = "เพิ่ม $student_id ไม่สำเร็จ: " . $stmt2->error;
                }
                $stmt2->close();
            } else {
                $error[] = "เพิ่ม $student_id ไม่สำเร็จ: " . $stmt->error;
            }
        }
        $stmt->close();
    }
}

// จัดการการอัปโหลด Excel
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excel_upload'])) {
    $excel_success = [];
    $excel_error = [];
    
    // ตรวจสอบไฟล์ที่อัปโหลด
    if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
        $excel_error[] = "เกิดข้อผิดพลาดในการอัปโหลดไฟล์";
    } else {
        $file = $_FILES['excel_file'];
        $fileName = $file['name'];
        $fileTmpName = $file['tmp_name'];
        $fileSize = $file['size'];
        $fileError = $file['error'];
        $fileType = $file['type'];
        
        // ตรวจสอบนามสกุลไฟล์
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = array('xlsx', 'xls', 'csv');
        
        if (!in_array($fileExt, $allowed)) {
            $excel_error[] = "ไฟล์ที่อัปโหลดต้องเป็น .xlsx, .xls หรือ .csv เท่านั้น";
        } else {
            // ตรวจสอบขนาดไฟล์ (ไม่เกิน 5MB)
            if ($fileSize > 5 * 1024 * 1024) {
                $excel_error[] = "ขนาดไฟล์ต้องไม่เกิน 5MB";
            } else {
                try {
                    // อ่านไฟล์ Excel
                    $data = readExcelFile($fileTmpName, $fileExt);
                    
                    if (empty($data)) {
                        $excel_error[] = "ไม่พบข้อมูลในไฟล์ Excel";
                    } else {
                        // ประมวลผลข้อมูล
                        $result = processExcelData($data, $conn);
                        $excel_success = $result['success'];
                        $excel_error = array_merge($excel_error, $result['error']);
                    }
                } catch (Exception $e) {
                    $excel_error[] = "เกิดข้อผิดพลาดในการอ่านไฟล์: " . $e->getMessage();
                }
            }
        }
    }
}

/**
 * อ่านไฟล์ Excel และแปลงเป็น array
 */
function readExcelFile($filePath, $fileExt) {
    $data = [];
    
    if ($fileExt === 'csv') {
        // อ่านไฟล์ CSV
        $handle = fopen($filePath, 'r');
        if ($handle !== false) {
            // ตั้งค่า encoding เป็น UTF-8
            stream_filter_append($handle, 'convert.iconv.UTF-8/UTF-8');
            
            $row = 0;
            while (($rowData = fgetcsv($handle)) !== false) {
                if ($row === 0) {
                    // ข้าม header
                    $row++;
                    continue;
                }
                
                // แปลงข้อมูลเป็น UTF-8 และจัดการรูปแบบวิทยาศาสตร์
                $rowData = array_map(function($cell) {
                    $cell = mb_convert_encoding(trim($cell), 'UTF-8', 'auto');
                    // แปลงเลขบัตรประชาชนจากรูปแบบวิทยาศาสตร์ (คอลัมน์ที่ 8)
                    return $cell;
                }, $rowData);
                
                $data[] = $rowData;
                $row++;
            }
            fclose($handle);
        }
    } else {
        // สำหรับไฟล์ .xlsx และ .xls ใช้ SimpleXLSX หรือ PhpSpreadsheet
        // ในที่นี้จะใช้วิธีง่ายๆ โดยแปลงเป็น CSV ก่อน
        if (class_exists('SimpleXLSX')) {
            $xlsx = SimpleXLSX::parse($filePath);
            if ($xlsx) {
                $rows = $xlsx->rows();
                // ข้าม header
                for ($i = 1; $i < count($rows); $i++) {
                    $rowData = array_map(function($cell) {
                        return mb_convert_encoding(trim($cell), 'UTF-8', 'auto');
                    }, $rows[$i]);
                    $data[] = $rowData;
                }
            }
        } else {
            // ถ้าไม่มี library ให้ใช้วิธีแปลงไฟล์
            $data = convertExcelToArray($filePath, $fileExt);
        }
    }
    
    return $data;
}

/**
 * แปลงไฟล์ Excel เป็น array (fallback method)
 */
function convertExcelToArray($filePath, $fileExt) {
    $data = [];
    
    // ใช้ command line tools ถ้ามี
    if ($fileExt === 'xlsx' && function_exists('shell_exec')) {
        $output = shell_exec("python -c \"
import pandas as pd
import sys
# อ่านไฟล์ Excel โดยไม่แปลงคอลัมน์เป็นตัวเลข
df = pd.read_excel('$filePath', engine='openpyxl', dtype=str)
df.to_csv(sys.stdout, index=False, encoding='utf-8')
\"");
        
        if ($output) {
            $lines = explode("\n", trim($output));
            for ($i = 1; $i < count($lines); $i++) {
                if (!empty($lines[$i])) {
                    $rowData = str_getcsv($lines[$i]);
                    $data[] = array_map(function($cell) {
                        return mb_convert_encoding(trim($cell), 'UTF-8', 'auto');
                    }, $rowData);
                }
            }
        }
    }
    
    return $data;
}

/**
 * ประมวลผลข้อมูลจาก Excel
 */
function processExcelData($data, $conn) {
    $success = [];
    $error = [];
    
    foreach ($data as $rowIndex => $row) {
        $rowNumber = $rowIndex + 2; // +2 เพราะข้าม header และ array เริ่มที่ 0
        
        // ตรวจสอบจำนวนคอลัมน์
        if (count($row) < 8) {
            $error[] = "แถวที่ $rowNumber: ข้อมูลไม่ครบถ้วน (ต้องการ 8 คอลัมน์)";
            continue;
        }
        
        // แปลงข้อมูล
        $student_id = trim($row[0]);
        $student_title = trim($row[1]);
        $student_name = trim($row[2]);
        $student_surname = trim($row[3]);
        $student_year = trim($row[4]);
        $student_number = trim($row[5]);
        $student_classroom = trim($row[6]);
        $student_citizen_id = trim($row[7]);
        
        // แปลงเลขบัตรประชาชนจากรูปแบบวิทยาศาสตร์ (ถ้ามี)
        if (!empty($student_citizen_id)) {
            $student_citizen_id = convertScientificNotation($student_citizen_id);
        }
        
        // ตรวจสอบข้อมูลที่จำเป็น
        if (empty($student_id) || empty($student_name) || empty($student_surname)) {
            $error[] = "แถวที่ $rowNumber: รหัสนักเรียน, ชื่อ หรือนามสกุลไม่สามารถเป็นค่าว่างได้";
            continue;
        }
        
        // ตรวจสอบรูปแบบเลขบัตรประชาชน
        if (!empty($student_citizen_id)) {
            // ตรวจสอบว่าเป็นตัวเลข 13 หลักหรือไม่
            if (!preg_match('/^[0-9]{13}$/', $student_citizen_id)) {
                $error[] = "แถวที่ $rowNumber: เลขบัตรประชาชนต้องเป็นตัวเลข 13 หลัก (ได้รับ: $student_citizen_id)";
                continue;
            }
        }
        
        // ตรวจสอบว่ารหัสนักเรียนซ้ำหรือไม่
        $stmt = $conn->prepare('SELECT id FROM user WHERE userId = ?');
        $stmt->bind_param('s', $student_id);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $error[] = "แถวที่ $rowNumber: รหัสนักเรียน $student_id มีอยู่แล้ว";
            $stmt->close();
            continue;
        }
        $stmt->close();
        
        // เพิ่มข้อมูลลงฐานข้อมูล
        $username = $student_id;
        $password = password_hash($student_citizen_id ?: $student_id, PASSWORD_DEFAULT);
        $status = 3;
        
        // เพิ่มข้อมูลในตาราง user
        $stmt = $conn->prepare('INSERT INTO user (userId, username, password, status) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('sssi', $student_id, $username, $password, $status);
        
        if ($stmt->execute()) {
            $user_id = $stmt->insert_id;
            
            // เพิ่มข้อมูลในตาราง student
            $stmt2 = $conn->prepare('INSERT INTO student (user_id, year, title, name, surname, number, classroom) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt2->bind_param('iisssis', $user_id, $student_year, $student_title, $student_name, $student_surname, $student_number, $student_classroom);
            
            if ($stmt2->execute()) {
                $success[] = "แถวที่ $rowNumber: เพิ่ม $student_id สำเร็จ";
            } else {
                $error[] = "แถวที่ $rowNumber: เพิ่ม $student_id ไม่สำเร็จ: " . $stmt2->error;
            }
            $stmt2->close();
        } else {
            $error[] = "แถวที่ $rowNumber: เพิ่ม $student_id ไม่สำเร็จ: " . $stmt->error;
        }
        $stmt->close();
    }
    
    return ['success' => $success, 'error' => $error];
}

/**
 * แปลงรูปแบบวิทยาศาสตร์กลับเป็นตัวเลขปกติ
 */
function convertScientificNotation($value) {
    $value = trim($value);

    // ถ้าเป็นรูปแบบวิทยาศาสตร์ (เช่น 1.23457E+12, 1.23457e+12)
    if (stripos($value, 'e+') !== false) {
        $value = sprintf('%.0f', $value);
    }

    // ถ้าเป็นตัวเลขที่มีจุดทศนิยม (เช่น 1234567890123.0)
    if (is_numeric($value) && strpos($value, '.') !== false) {
        $value = sprintf('%.0f', $value);
    }

    // เติม 0 ข้างหน้าจนครบ 13 หลัก (กรณี Excel ตัด 0 ข้างหน้าออก)
    $value = str_pad($value, 13, '0', STR_PAD_LEFT);

    return $value;
}

/**
 * สร้างไฟล์ Excel template สำหรับดาวน์โหลด
 */
function generateExcelTemplate() {
    $filename = 'student_template_' . date('Y-m-d_H-i-s') . '.csv';
    $filepath = '../uploads/' . $filename;
    
    // สร้างโฟลเดอร์ถ้ายังไม่มี
    if (!is_dir('../uploads')) {
        mkdir('../uploads', 0777, true);
    }
    
    $template = "รหัสนักเรียน,คำนำหน้า,ชื่อ,นามสกุล,ปีการศึกษา,เลขที่,ห้องเรียน,เลขบัตรประชาชน\n";
    $template .= "64010001,นาย,สมชาย,ใจดี,2567,1,ม.4/1,1234567890123\n";
    $template .= "64010002,นางสาว,สมหญิง,ใจงาม,2567,2,ม.4/1,1234567890124\n";
    
    file_put_contents($filepath, "\xEF\xBB\xBF" . $template); // เพิ่ม BOM สำหรับ UTF-8
    
    return $filename;
}
?> 