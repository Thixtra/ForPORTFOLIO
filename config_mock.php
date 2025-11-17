<?php
/**
 * Mock Configuration - สำหรับ Portfolio Demo
 * ไม่เชื่อมต่อฐานข้อมูลจริง ใช้ MockData แทน
 */

// กำหนดโหมด Mock
define('MOCK_MODE', true);

// Load MockData class
require_once __DIR__ . '/MockData.php';

// สร้าง Mock Connection Object เพื่อให้โค้ดเดิมทำงานได้
class MockConnection {
    public function prepare($sql) {
        return new MockStatement($sql);
    }
    
    public function query($sql) {
        return new MockResult($sql);
    }
    
    public function set_charset($charset) {
        return true;
    }
    
    public function connect_error() {
        return null;
    }
}

class MockStatement {
    private $sql;
    private $params = [];
    private $param_types = '';
    
    public function __construct($sql) {
        $this->sql = $sql;
    }
    
    public function bind_param($types, ...$params) {
        $this->param_types = $types;
        $this->params = $params;
        return true;
    }
    
    public function execute() {
        // จำลองการ execute
        return true;
    }
    
    public function get_result() {
        return new MockResult($this->sql, $this->params);
    }
    
    public function store_result() {
        return true;
    }
    
    public function num_rows() {
        // จำลองจำนวนแถว
        return 0;
    }
    
    public function bind_result(...$vars) {
        // จำลอง bind_result
        return true;
    }
    
    public function fetch() {
        // จำลอง fetch
        return false;
    }
    
    public function close() {
        return true;
    }
    
    public function insert_id() {
        return rand(100, 999);
    }
    
    public function error() {
        return '';
    }
}

class MockResult {
    private $sql;
    private $params;
    private $data = [];
    private $current_index = 0;
    
    public function __construct($sql, $params = []) {
        $this->sql = $sql;
        $this->params = $params;
        $this->loadData();
    }
    
    private function loadData() {
        // วิเคราะห์ SQL และโหลดข้อมูลจาก MockData
        $sql = strtolower($this->sql);
        
        // SELECT user WHERE username = ?
        if (strpos($sql, 'select') !== false && strpos($sql, 'user') !== false && strpos($sql, 'username') !== false) {
            if (!empty($this->params)) {
                $user = MockData::getUserByUsername($this->params[0]);
                if ($user) {
                    $this->data = [$user];
                }
            }
        }
        // SELECT student WHERE user_id = ?
        elseif (strpos($sql, 'select') !== false && strpos($sql, 'student') !== false && strpos($sql, 'user_id') !== false) {
            if (!empty($this->params)) {
                $student = MockData::getStudentByUserId($this->params[0]);
                if ($student) {
                    $this->data = [$student];
                }
            }
        }
        // SELECT teacher WHERE user_id = ?
        elseif (strpos($sql, 'select') !== false && strpos($sql, 'teacher') !== false && strpos($sql, 'user_id') !== false) {
            if (!empty($this->params)) {
                $teacher = MockData::getTeacherByUserId($this->params[0]);
                if ($teacher) {
                    $this->data = [$teacher];
                }
            }
        }
        // SELECT student WHERE classroom = ?
        elseif (strpos($sql, 'select') !== false && strpos($sql, 'student') !== false && strpos($sql, 'classroom') !== false) {
            if (!empty($this->params)) {
                $students = MockData::getStudentsByClassroom($this->params[0]);
                $this->data = $students;
            }
        }
        // SELECT attendance WHERE date = ? AND teacher_id = ?
        elseif (strpos($sql, 'select') !== false && strpos($sql, 'attendance') !== false && strpos($sql, 'date') !== false) {
            if (count($this->params) >= 2) {
                $attendance = MockData::getAttendanceByDateAndTeacher($this->params[0], $this->params[1]);
                $this->data = $attendance;
            }
        }
        // SELECT attendance WHERE student_id = ?
        elseif (strpos($sql, 'select') !== false && strpos($sql, 'attendance') !== false && strpos($sql, 'student_id') !== false) {
            if (!empty($this->params)) {
                $attendance = MockData::getAttendanceByStudentId($this->params[0]);
                $this->data = $attendance;
            }
        }
        // SELECT COUNT(*) หรือ SELECT status, COUNT(*)
        elseif (strpos($sql, 'count') !== false) {
            // สำหรับ stats
            if (strpos($sql, 'attendance') !== false && strpos($sql, 'status') !== false) {
                $start_date = isset($this->params[0]) ? $this->params[0] : date('Y-m-01');
                $end_date = isset($this->params[1]) ? $this->params[1] : date('Y-m-t');
                $stats = MockData::getAttendanceStats($start_date, $end_date);
                // แปลงเป็นรูปแบบที่ query คาดหวัง
                $this->data = [
                    ['status' => 1, 'count' => $stats['มา']],
                    ['status' => 2, 'count' => $stats['ขาด']],
                    ['status' => 3, 'count' => $stats['ลา']],
                    ['status' => 4, 'count' => $stats['สาย']]
                ];
            }
        }
    }
    
    public function fetch_assoc() {
        if ($this->current_index < count($this->data)) {
            return $this->data[$this->current_index++];
        }
        return null;
    }
    
    public function fetch_all($mode = MYSQLI_ASSOC) {
        return $this->data;
    }
    
    public function num_rows() {
        return count($this->data);
    }
}

// สร้าง connection object
$conn = new MockConnection();
?>

