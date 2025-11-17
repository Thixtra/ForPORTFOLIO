<?php
/**
 * MockData Class - สำหรับ Portfolio Demo
 * ข้อมูลตัวอย่างที่ปลอดภัย ไม่เชื่อมต่อฐานข้อมูลจริง
 */
class MockData {
    private static $users = [];
    private static $students = [];
    private static $teachers = [];
    private static $admins = [];
    private static $attendance = [];
    private static $notifications = [];
    
    public static function init() {
        // ข้อมูลผู้ใช้ (User)
        self::$users = [
            ['id' => 1, 'userId' => 'admin', 'username' => 'admin', 'password' => '$2y$10$f.Zx2Cv6EO8pbTrLKaXYDOP5tf5GpU0Muh3oYv7pGV9VHrcMOoa7G', 'status' => 1],
            ['id' => 2, 'userId' => 'teacher01', 'username' => 'teacher01', 'password' => '$2y$10$ZmUzu1z.a7xK2WfqlidxCe4RISS8xnmlIw.Q7FkcT/pO1sKz/QjiW', 'status' => 2],
            ['id' => 3, 'userId' => '64010001', 'username' => '64010001', 'password' => '$2y$10$LDMhGK1kwayd7DYPFlFTeOhX8rB/Vj.g8mSTAWJSrdeCdHWJc4o0.', 'status' => 3],
            ['id' => 4, 'userId' => '64010002', 'username' => '64010002', 'password' => '$2y$10$LDMhGK1kwayd7DYPFlFTeOhX8rB/Vj.g8mSTAWJSrdeCdHWJc4o0.', 'status' => 3],
            ['id' => 5, 'userId' => '64010003', 'username' => '64010003', 'password' => '$2y$10$LDMhGK1kwayd7DYPFlFTeOhX8rB/Vj.g8mSTAWJSrdeCdHWJc4o0.', 'status' => 3],
        ];
        
        // ข้อมูล Admin
        self::$admins = [
            ['id' => 1, 'user_id' => 1, 'year' => 2024, 'title' => 'นาย', 'name' => 'ผู้ดูแล', 'surname' => 'ระบบ']
        ];
        
        // ข้อมูล Teacher
        self::$teachers = [
            ['id' => 1, 'user_id' => 2, 'year' => 2024, 'title' => 'อาจารย์', 'name' => 'สมชาย', 'surname' => 'ใจดี', 'classroom' => 'ม.4/1', 'subject_group' => 'คณิตศาสตร์']
        ];
        
        // ข้อมูล Student
        self::$students = [
            ['id' => 1, 'user_id' => 3, 'year' => 2567, 'title' => 'นาย', 'name' => 'สมชาย', 'surname' => 'เรียนดี', 'number' => 1, 'classroom' => 'ม.4/1'],
            ['id' => 2, 'user_id' => 4, 'year' => 2567, 'title' => 'นางสาว', 'name' => 'สมหญิง', 'surname' => 'ขยันเรียน', 'number' => 2, 'classroom' => 'ม.4/1'],
            ['id' => 3, 'user_id' => 5, 'year' => 2567, 'title' => 'นาย', 'name' => 'สมศักดิ์', 'surname' => 'ตั้งใจเรียน', 'number' => 3, 'classroom' => 'ม.4/1'],
        ];
        
        // ข้อมูล Attendance (30 วันย้อนหลัง)
        $today = new DateTime();
        self::$attendance = [];
        $attendance_id = 1;
        
        for ($i = 0; $i < 30; $i++) {
            $date = clone $today;
            $date->modify("-{$i} days");
            $date_str = $date->format('Y-m-d');
            $day_of_week = $date->format('w'); // 0 = Sunday, 6 = Saturday
            
            // ข้ามวันหยุด
            if ($day_of_week == 0 || $day_of_week == 6) {
                continue;
            }
            
            // สร้างข้อมูลการเช็คชื่อสำหรับนักเรียนแต่ละคน
            foreach (self::$students as $student) {
                $status = rand(1, 4); // 1=มา, 2=ขาด, 3=ลา, 4=สาย
                // ให้โอกาสมาเรียนมากกว่า
                if (rand(1, 10) <= 7) {
                    $status = 1; // มา
                }
                
                $hour = rand(7, 8);
                $minute = rand(0, 59);
                $timestamp = $date_str . ' ' . sprintf('%02d:%02d:00', $hour, $minute);
                
                self::$attendance[] = [
                    'id' => $attendance_id++,
                    'student_id' => $student['id'],
                    'date' => $date_str,
                    'status' => $status,
                    'teacher_id' => 1,
                    'timestamp' => $timestamp
                ];
            }
        }
        
        // ข้อมูล Notifications
        self::$notifications = [
            ['id' => 1, 'teacher_id' => 1, 'title' => 'แจ้งเตือน', 'message' => 'มีนักเรียนขาดเรียนติดต่อกัน 3 วัน', 'type' => 'warning', 'status' => 'unread', 'created_at' => date('Y-m-d H:i:s')]
        ];
    }
    
    // Methods สำหรับ query ข้อมูล
    public static function getUserByUsername($username) {
        foreach (self::$users as $user) {
            if ($user['username'] === $username) {
                return $user;
            }
        }
        return null;
    }
    
    public static function getStudentByUserId($user_id) {
        foreach (self::$students as $student) {
            if ($student['user_id'] == $user_id) {
                $user = self::getUserById($student['user_id']);
                return array_merge($student, ['username' => $user['username']]);
            }
        }
        return null;
    }
    
    public static function getTeacherByUserId($user_id) {
        foreach (self::$teachers as $teacher) {
            if ($teacher['user_id'] == $user_id) {
                $user = self::getUserById($teacher['user_id']);
                return array_merge($teacher, ['username' => $user['username']]);
            }
        }
        return null;
    }
    
    public static function getUserById($id) {
        foreach (self::$users as $user) {
            if ($user['id'] == $id) {
                return $user;
            }
        }
        return null;
    }
    
    public static function getStudentsByClassroom($classroom) {
        $result = [];
        foreach (self::$students as $student) {
            if ($student['classroom'] === $classroom) {
                $user = self::getUserById($student['user_id']);
                $result[] = array_merge($student, ['username' => $user['username']]);
            }
        }
        // เรียงตามเลขที่
        usort($result, function($a, $b) {
            return $a['number'] - $b['number'];
        });
        return $result;
    }
    
    public static function getAttendanceByDateAndTeacher($date, $teacher_id) {
        $result = [];
        foreach (self::$attendance as $att) {
            if ($att['date'] === $date && $att['teacher_id'] == $teacher_id) {
                $student = self::getStudentById($att['student_id']);
                $result[] = array_merge($att, [
                    'name' => $student['name'],
                    'surname' => $student['surname'],
                    'number' => $student['number']
                ]);
            }
        }
        // เรียงตามเลขที่
        usort($result, function($a, $b) {
            return $a['number'] - $b['number'];
        });
        return $result;
    }
    
    public static function getAttendanceByStudentId($student_id, $limit = 30) {
        $result = [];
        foreach (self::$attendance as $att) {
            if ($att['student_id'] == $student_id) {
                $teacher = self::getTeacherById($att['teacher_id']);
                $result[] = array_merge($att, [
                    'teacher_name' => $teacher ? $teacher['name'] : '',
                    'teacher_surname' => $teacher ? $teacher['surname'] : ''
                ]);
            }
        }
        // เรียงตามวันที่ล่าสุด
        usort($result, function($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });
        return array_slice($result, 0, $limit);
    }
    
    public static function getAttendanceStats($start_date, $end_date) {
        $stats = ['มา' => 0, 'ขาด' => 0, 'ลา' => 0, 'สาย' => 0];
        foreach (self::$attendance as $att) {
            if ($att['date'] >= $start_date && $att['date'] <= $end_date) {
                switch ($att['status']) {
                    case 1: $stats['มา']++; break;
                    case 2: $stats['ขาด']++; break;
                    case 3: $stats['ลา']++; break;
                    case 4: $stats['สาย']++; break;
                }
            }
        }
        return $stats;
    }
    
    public static function getTeacherStatus($date = null) {
        if ($date === null) {
            $date = date('Y-m-d');
        }
        $result = [];
        foreach (self::$teachers as $teacher) {
            $checked = 0;
            foreach (self::$attendance as $att) {
                if ($att['teacher_id'] == $teacher['id'] && $att['date'] === $date) {
                    $checked++;
                }
            }
            $result[] = [
                'name' => $teacher['name'],
                'surname' => $teacher['surname'],
                'classroom' => $teacher['classroom'],
                'checked' => $checked
            ];
        }
        return $result;
    }
    
    public static function getWeeklyStats($teacher_id, $monday) {
        $result = [];
        $current_date = new DateTime($monday);
        
        for ($i = 0; $i < 5; $i++) {
            $date = clone $current_date;
            $date->modify("+{$i} days");
            $date_str = $date->format('Y-m-d');
            
            $present = 0;
            $absent = 0;
            $leave_status = 0;
            $late = 0;
            
            foreach (self::$attendance as $att) {
                if ($att['date'] === $date_str && $att['teacher_id'] == $teacher_id) {
                    switch ($att['status']) {
                        case 1: $present++; break;
                        case 2: $absent++; break;
                        case 3: $leave_status++; break;
                        case 4: $late++; break;
                    }
                }
            }
            
            $result[] = [
                'date' => $date_str,
                'present' => $present,
                'absent' => $absent,
                'leave_status' => $leave_status,
                'late' => $late
            ];
        }
        
        return $result;
    }
    
    public static function getStudentById($id) {
        foreach (self::$students as $student) {
            if ($student['id'] == $id) {
                return $student;
            }
        }
        return null;
    }
    
    public static function getTeacherById($id) {
        foreach (self::$teachers as $teacher) {
            if ($teacher['id'] == $id) {
                return $teacher;
            }
        }
        return null;
    }
    
    public static function getAllStudents($limit = null, $offset = 0) {
        $result = self::$students;
        if ($limit !== null) {
            $result = array_slice($result, $offset, $limit);
        }
        foreach ($result as &$student) {
            $user = self::getUserById($student['user_id']);
            $student['userId'] = $user['userId'];
        }
        return $result;
    }
    
    public static function getAllTeachers($limit = null, $offset = 0) {
        $result = self::$teachers;
        if ($limit !== null) {
            $result = array_slice($result, $offset, $limit);
        }
        foreach ($result as &$teacher) {
            $user = self::getUserById($teacher['user_id']);
            $teacher['userId'] = $user['userId'];
        }
        return $result;
    }
    
    public static function getClassrooms() {
        $classrooms = [];
        foreach (self::$students as $student) {
            if (!in_array($student['classroom'], $classrooms)) {
                $classrooms[] = $student['classroom'];
            }
        }
        sort($classrooms);
        return $classrooms;
    }
}

// Initialize mock data
MockData::init();
?>

