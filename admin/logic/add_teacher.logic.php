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

// เพิ่มข้อมูลอาจารย์หลายคน
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['multi_teacher'])) {
    $success = [];
    $error = [];
    $count = count($_POST['teacher_id']);
    for ($i = 0; $i < $count; $i++) {
        $teacher_id = trim($_POST['teacher_id'][$i]);
        $year = trim($_POST['year'][$i]);
        $title = trim($_POST['title'][$i]);
        $name = trim($_POST['name'][$i]);
        $surname = trim($_POST['surname'][$i]);
        $classroom = trim($_POST['classroom'][$i]);
        $subject_group = trim($_POST['subject_group'][$i]);
        $teacher_citizen_id = trim($_POST['teacher_citizen_id'][$i]);
        
        // แปลงเลขบัตรประชาชนจากรูปแบบวิทยาศาสตร์ (ถ้ามี)
        if (!empty($teacher_citizen_id)) {
            $teacher_citizen_id = convertScientificNotation($teacher_citizen_id);
        }
        
        $username = $teacher_id;
        $password = password_hash($teacher_citizen_id, PASSWORD_DEFAULT);
        $status = 2;
        // ตรวจสอบรูปแบบเลขบัตรประชาชน
        if (!empty($teacher_citizen_id) && !preg_match('/^[0-9]{13}$/', $teacher_citizen_id)) {
            $error[] = "รหัสอาจารย์ $teacher_id: เลขบัตรประชาชนต้องเป็นตัวเลข 13 หลัก (ได้รับ: $teacher_citizen_id)";
        } else {
            $stmt = $conn->prepare('SELECT id FROM user WHERE userId = ?');
            $stmt->bind_param('s', $teacher_id);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $error[] = "รหัสอาจารย์ $teacher_id มีอยู่แล้ว";
            } else {
                $stmt = $conn->prepare('INSERT INTO user (userId, username, password, status) VALUES (?, ?, ?, ?)');
                $stmt->bind_param('sssi', $teacher_id, $username, $password, $status);
                if ($stmt->execute()) {
                    $user_id = $stmt->insert_id;
                    $stmt2 = $conn->prepare('INSERT INTO teacher (user_id, year, title, name, surname, classroom, subject_group) VALUES (?, ?, ?, ?, ?, ?, ?)');
                    $stmt2->bind_param('iisssss', $user_id, $year, $title, $name, $surname, $classroom, $subject_group);
                    if ($stmt2->execute()) {
                        $success[] = "เพิ่ม $teacher_id สำเร็จ";
                    } else {
                        $error[] = "เพิ่ม $teacher_id ไม่สำเร็จ: " . $stmt2->error;
                    }
                    $stmt2->close();
                } else {
                    $error[] = "เพิ่ม $teacher_id ไม่สำเร็จ: " . $stmt->error;
                }
            }
            $stmt->close();
        }
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
        $teacher_id = trim($row[0]);
        $year = trim($row[1]);
        $title = trim($row[2]);
        $name = trim($row[3]);
        $surname = trim($row[4]);
        $classroom = trim($row[5]);
        $subject_group = trim($row[6]);
        $teacher_citizen_id = trim($row[7]);
        
        // แปลงเลขบัตรประชาชนจากรูปแบบวิทยาศาสตร์ (ถ้ามี)
        if (!empty($teacher_citizen_id)) {
            $teacher_citizen_id = convertScientificNotation($teacher_citizen_id);
        }
        
        // ตรวจสอบข้อมูลที่จำเป็น
        if (empty($teacher_id) || empty($name) || empty($surname)) {
            $error[] = "แถวที่ $rowNumber: รหัสอาจารย์, ชื่อ หรือนามสกุลไม่สามารถเป็นค่าว่างได้";
            continue;
        }
        
        // ตรวจสอบรูปแบบเลขบัตรประชาชน
        if (!empty($teacher_citizen_id)) {
            // ตรวจสอบว่าเป็นตัวเลข 13 หลักหรือไม่
            if (!preg_match('/^[0-9]{13}$/', $teacher_citizen_id)) {
                $error[] = "แถวที่ $rowNumber: เลขบัตรประชาชนต้องเป็นตัวเลข 13 หลัก (ได้รับ: $teacher_citizen_id)";
                continue;
            }
        }
        
        // ตรวจสอบว่ารหัสอาจารย์ซ้ำหรือไม่
        $stmt = $conn->prepare('SELECT id FROM user WHERE userId = ?');
        $stmt->bind_param('s', $teacher_id);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $error[] = "แถวที่ $rowNumber: รหัสอาจารย์ $teacher_id มีอยู่แล้ว";
            $stmt->close();
            continue;
        }
        $stmt->close();
        
        // เพิ่มข้อมูลลงฐานข้อมูล
        $username = $teacher_id;
        $password = password_hash($teacher_citizen_id ?: $teacher_id, PASSWORD_DEFAULT);
        $status = 2;
        
        // เพิ่มข้อมูลในตาราง user
        $stmt = $conn->prepare('INSERT INTO user (userId, username, password, status) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('sssi', $teacher_id, $username, $password, $status);
        
        if ($stmt->execute()) {
            $user_id = $stmt->insert_id;
            
            // เพิ่มข้อมูลในตาราง teacher
            $stmt2 = $conn->prepare('INSERT INTO teacher (user_id, year, title, name, surname, classroom, subject_group) VALUES (?, ?, ?, ?, ?, ?, ?)');
            $stmt2->bind_param('iisssss', $user_id, $year, $title, $name, $surname, $classroom, $subject_group);
            
            if ($stmt2->execute()) {
                $success[] = "แถวที่ $rowNumber: เพิ่ม $teacher_id สำเร็จ";
            } else {
                $error[] = "แถวที่ $rowNumber: เพิ่ม $teacher_id ไม่สำเร็จ: " . $stmt2->error;
            }
            $stmt2->close();
        } else {
            $error[] = "แถวที่ $rowNumber: เพิ่ม $teacher_id ไม่สำเร็จ: " . $stmt->error;
        }
        $stmt->close();
    }
    
    return ['success' => $success, 'error' => $error];
}

/**
 * แปลงรูปแบบวิทยาศาสตร์กลับเป็นตัวเลขปกติ
 */
function convertScientificNotation($value) {
    // ลบช่องว่างและอักขระพิเศษ
    $value = trim($value);
    
    // ถ้าเป็นรูปแบบวิทยาศาสตร์ (เช่น 1.23457E+12, 1.23457e+12)
    if (preg_match('/^(\d+\.?\d*)E[+-](\d+)$/i', $value, $matches)) {
        $number = (float)$matches[1];
        $exponent = (int)$matches[2];
        
        // แปลงกลับเป็นตัวเลข
        $result = $number * pow(10, $exponent);
        
        // ถ้าเป็นเลขบัตรประชาชน (13 หลัก) ให้แปลงเป็น string โดยไม่มีจุดทศนิยม
        if ($result >= 1000000000000 && $result < 10000000000000) {
            return number_format($result, 0, '', '');
        }
        
        // สำหรับกรณีอื่นๆ ให้ส่งคืนค่าเดิม
        return $value;
    }
    
    // ถ้าเป็นตัวเลขที่มีจุดทศนิยม (เช่น 1234567890123.0)
    if (is_numeric($value) && strpos($value, '.') !== false) {
        $floatValue = (float)$value;
        
        // ถ้าเป็นเลขบัตรประชาชน (13 หลัก) ให้แปลงเป็น string โดยไม่มีจุดทศนิยม
        if ($floatValue >= 1000000000000 && $floatValue < 10000000000000) {
            return number_format($floatValue, 0, '', '');
        }
        
        // สำหรับกรณีอื่นๆ ให้ส่งคืนค่าเดิม
        return $value;
    }
    
    // ถ้าไม่ใช่รูปแบบวิทยาศาสตร์ ให้ส่งคืนค่าเดิม
    return $value;
}

/**
 * สร้างไฟล์ Excel template สำหรับดาวน์โหลด
 */
function generateTeacherExcelTemplate() {
    $filename = 'teacher_template_' . date('Y-m-d_H-i-s') . '.csv';
    $filepath = '../uploads/' . $filename;
    
    // สร้างโฟลเดอร์ถ้ายังไม่มี
    if (!is_dir('../uploads')) {
        mkdir('../uploads', 0777, true);
    }
    
    $template = "รหัสอาจารย์,ปีการศึกษา,คำนำหน้า,ชื่อ,นามสกุล,ห้องเรียน,กลุ่มสาระ/กลุ่มวิชา,เลขบัตรประชาชน\n";
    $template .= "1001,2567,นาย,สมชาย,ใจดี,ม.4/1,คณิตศาสตร์,1234567890123\n";
    $template .= "1002,2567,นางสาว,สมหญิง,ใจงาม,ม.4/2,วิทยาศาสตร์,1234567890124\n";
    
    file_put_contents($filepath, "\xEF\xBB\xBF" . $template); // เพิ่ม BOM สำหรับ UTF-8
    
    return $filename;
}
?> 