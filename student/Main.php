<?php
session_start();

// ตรวจสอบโหมด Mock
if (defined('MOCK_MODE') && MOCK_MODE === true) {
    require_once '../config_mock.php';
} else {
    require_once '../config.php';
}

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['user_id']) || $_SESSION['status'] != 3) {
    header("Location: ../login.php");
    exit;
}

// ถ้าเป็นโหมด Mock
if (defined('MOCK_MODE') && MOCK_MODE === true) {
    $user_id = $_SESSION['user_id'];
    $student = MockData::getStudentByUserId($user_id);
    
    if (!$student) {
        header("Location: ../login.php");
        exit;
    }
    
    // ดึงประวัติการมาเรียน (30 วันล่าสุด)
    $attendance_records = MockData::getAttendanceByStudentId($student['id'], 30);
} else {
    // โหมดปกติ
    // ดึงข้อมูลนักเรียน
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT s.*, u.username FROM student s 
                           JOIN user u ON s.user_id = u.id 
                           WHERE s.user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();

    if (!$student) {
        header("Location: ../login.php");
        exit;
    }

    // ดึงประวัติการมาเรียน (30 วันล่าสุด)
    $stmt = $conn->prepare("SELECT a.*, t.name as teacher_name, t.surname as teacher_surname 
                           FROM attendance a 
                           LEFT JOIN teacher t ON a.teacher_id = t.id 
                           WHERE a.student_id = ? 
                           ORDER BY a.date DESC 
                           LIMIT 30");
    $stmt->bind_param("i", $student['id']);
    $stmt->execute();
    $attendance_result = $stmt->get_result();
    $attendance_records = [];
    while ($row = $attendance_result->fetch_assoc()) {
        $attendance_records[] = $row;
    }
}

// นับสถิติการมาเรียน
$total_days = count($attendance_records);
$present_days = 0;
$absent_days = 0;
$late_days = 0;

foreach ($attendance_records as $record) {
    switch ($record['status']) {
        case 1: // มาเรียน
            $present_days++;
            break;
        case 2: // สาย
            $late_days++;
            break;
        case 3: // ขาด
            $absent_days++;
            break;
    }
}

$attendance_rate = $total_days > 0 ? round(($present_days / $total_days) * 100, 1) : 0;

// จัดการการออกจากระบบ
if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: ../login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="format-detection" content="telephone=no">
    <title>หน้าหลักนักเรียน - SKR Attendance</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Custom styles for better mobile experience */
        @media (max-width: 640px) {
            .container {
                padding-left: 1rem;
                padding-right: 1rem;
            }
        }
        
        /* Prevent text selection on mobile */
        * {
            -webkit-touch-callout: none;
            -webkit-user-select: none;
            -khtml-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
        
        /* Allow text selection in input fields */
        input, textarea {
            -webkit-user-select: text;
            -khtml-user-select: text;
            -moz-user-select: text;
            -ms-user-select: text;
            user-select: text;
        }
        
        /* Smooth scrolling */
        html {
            scroll-behavior: smooth;
        }
        
        /* Better touch targets for mobile */
        button, a {
            min-height: 44px;
            min-width: 44px;
        }
        
        /* Custom gradient animation for header */
        .bg-gradient-to-r {
            background-size: 200% 200%;
            animation: gradientShift 3s ease infinite;
        }
        
        @keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        
        /* Glow effect for logo */
        .drop-shadow-lg {
            filter: drop-shadow(0 10px 8px rgb(0 0 0 / 0.04)) drop-shadow(0 4px 3px rgb(0 0 0 / 0.1));
        }
        
        /* Hover effects for cards */
        .bg-red-50:hover, .bg-green-50:hover, .bg-purple-50:hover, .bg-yellow-50:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #dc2626;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #b91c1c;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-gradient-to-r from-red-600 to-red-700 text-white shadow-lg border-b-4 border-red-800">
        <div class="container mx-auto px-4 py-4">
            <div class="flex flex-col lg:flex-row justify-between items-center space-y-4 lg:space-y-0">
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <img src="../icon/SKR-logo-T.png" alt="SKR Logo" class="h-10 w-10 lg:h-12 lg:w-12 drop-shadow-lg">
                        
                    </div>
                    <div>
                        <h1 class="text-xl lg:text-2xl font-bold text-white drop-shadow-md">SKR Attendance</h1>
                        <p class="text-red-100 text-sm lg:text-base font-medium">ระบบจัดการการเข้าเรียน</p>
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row items-center space-y-2 sm:space-y-0 sm:space-x-4">
                    <div class="bg-white/10 backdrop-blur-sm rounded-lg px-3 py-2 border border-white/20">
                        <span class="text-white text-sm lg:text-base text-center sm:text-left font-medium">
                            <i class="fas fa-user-circle mr-2 text-red-200"></i>
                            สวัสดี, <?php echo $student['title'] . $student['name'] . ' ' . $student['surname']; ?>
                        </span>
                    </div>
                    <form method="post" class="inline">
                        <button type="submit" name="logout" class="bg-white text-red-600 hover:bg-red-50 px-4 py-2 lg:px-5 lg:py-2.5 rounded-lg transition-all duration-200 text-sm lg:text-base w-full sm:w-auto font-semibold shadow-md hover:shadow-lg transform hover:-translate-y-0.5 border-2 border-white/20">
                            <i class="fas fa-sign-out-alt mr-2"></i>ออกจากระบบ
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <div class="container mx-auto px-4 py-4 lg:py-8">
        <!-- ข้อมูลนักเรียน -->
        <div class="bg-white rounded-lg shadow-md p-4 lg:p-6 mb-6 lg:mb-8 border-l-4 border-red-600">
            <h2 class="text-xl lg:text-2xl font-bold text-gray-800 mb-4 lg:mb-6 flex items-center">
                <i class="fas fa-user-graduate mr-2 lg:mr-3 text-red-600"></i>
                ข้อมูลนักเรียน
            </h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-6">
                <div class="bg-red-50 p-3 lg:p-4 rounded-lg border border-red-200">
                    <div class="flex items-center">
                        <i class="fas fa-id-card text-red-600 mr-2 lg:mr-3 text-lg lg:text-xl"></i>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs lg:text-sm text-gray-600">รหัสนักเรียน</p>
                            <p class="font-semibold text-gray-800 text-sm lg:text-base truncate"><?php echo $student['username']; ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-green-50 p-3 lg:p-4 rounded-lg border border-green-200">
                    <div class="flex items-center">
                        <i class="fas fa-user text-green-600 mr-2 lg:mr-3 text-lg lg:text-xl"></i>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs lg:text-sm text-gray-600">ชื่อ-นามสกุล</p>
                            <p class="font-semibold text-gray-800 text-sm lg:text-base truncate"><?php echo $student['title'] . $student['name'] . ' ' . $student['surname']; ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-purple-50 p-3 lg:p-4 rounded-lg border border-purple-200">
                    <div class="flex items-center">
                        <i class="fas fa-graduation-cap text-purple-600 mr-2 lg:mr-3 text-lg lg:text-xl"></i>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs lg:text-sm text-gray-600">ชั้นเรียน</p>
                            <p class="font-semibold text-gray-800 text-sm lg:text-base truncate"><?php echo $student['classroom']; ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-yellow-50 p-3 lg:p-4 rounded-lg border border-yellow-200">
                    <div class="flex items-center">
                        <i class="fas fa-hashtag text-yellow-600 mr-2 lg:mr-3 text-lg lg:text-xl"></i>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs lg:text-sm text-gray-600">เลขที่</p>
                            <p class="font-semibold text-gray-800 text-sm lg:text-base truncate"><?php echo $student['number']; ?></p>
                        </div>
                    </div>
                </div>
                <div class="bg-red-50 p-3 lg:p-4 rounded-lg border border-red-200">
                    <div class="flex items-center">
                        <i class="fas fa-calendar text-red-600 mr-2 lg:mr-3 text-lg lg:text-xl"></i>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs lg:text-sm text-gray-600">ปีที่เข้ามาเป็นนักเรียน</p>
                            <p class="font-semibold text-gray-800 text-sm lg:text-base truncate"><?php echo $student['year']; ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- สถิติการมาเรียน -->
        <div class="bg-white rounded-lg shadow-md p-4 lg:p-6 mb-6 lg:mb-8 border-l-4 border-red-600">
            <h2 class="text-xl lg:text-2xl font-bold text-gray-800 mb-4 lg:mb-6 flex items-center">
                <i class="fas fa-chart-bar mr-2 lg:mr-3 text-red-600"></i>
                สถิติการมาเรียน (30 วันล่าสุด)
            </h2>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-6">
                <div class="bg-green-100 p-3 lg:p-4 rounded-lg text-center">
                    <div class="text-2xl lg:text-3xl font-bold text-green-600"><?php echo $present_days; ?></div>
                    <div class="text-xs lg:text-sm text-gray-600">มาเรียน</div>
                </div>
                <div class="bg-yellow-100 p-3 lg:p-4 rounded-lg text-center">
                    <div class="text-2xl lg:text-3xl font-bold text-yellow-600"><?php echo $late_days; ?></div>
                    <div class="text-xs lg:text-sm text-gray-600">มาสาย</div>
                </div>
                <div class="bg-red-100 p-3 lg:p-4 rounded-lg text-center">
                    <div class="text-2xl lg:text-3xl font-bold text-red-600"><?php echo $absent_days; ?></div>
                    <div class="text-xs lg:text-sm text-gray-600">ขาดเรียน</div>
                </div>
                <div class="bg-red-100 p-3 lg:p-4 rounded-lg text-center border border-red-200">
                    <div class="text-2xl lg:text-3xl font-bold text-red-600"><?php echo $attendance_rate; ?>%</div>
                    <div class="text-xs lg:text-sm text-gray-600">อัตราการมาเรียน</div>
                </div>
            </div>
        </div>

        <!-- ประวัติการมาเรียน -->
        <div class="bg-white rounded-lg shadow-md p-4 lg:p-6 border-l-4 border-red-600">
            <h2 class="text-xl lg:text-2xl font-bold text-gray-800 mb-4 lg:mb-6 flex items-center">
                <i class="fas fa-history mr-2 lg:mr-3 text-red-600"></i>
                ประวัติการมาเรียน
            </h2>
            <?php if (empty($attendance_records)): ?>
                <div class="text-center py-6 lg:py-8">
                    <i class="fas fa-calendar-times text-gray-400 text-4xl lg:text-6xl mb-3 lg:mb-4"></i>
                    <p class="text-gray-500 text-base lg:text-lg">ยังไม่มีประวัติการมาเรียน</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full table-auto">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-2 py-2 lg:px-4 lg:py-3 text-left text-xs lg:text-sm font-medium text-gray-700">วันที่</th>
                                <th class="px-2 py-2 lg:px-4 lg:py-3 text-left text-xs lg:text-sm font-medium text-gray-700">สถานะ</th>
                                <th class="px-2 py-2 lg:px-4 lg:py-3 text-left text-xs lg:text-sm font-medium text-gray-700 hidden sm:table-cell">ครูผู้สอน</th>
                                <th class="px-2 py-2 lg:px-4 lg:py-3 text-left text-xs lg:text-sm font-medium text-gray-700">เวลา</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($attendance_records as $record): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-2 py-2 lg:px-4 lg:py-3 text-xs lg:text-sm text-gray-900">
                                        <?php 
                                        $date = new DateTime($record['date']);
                                        echo $date->format('d/m/Y');
                                        ?>
                                    </td>
                                    <td class="px-2 py-2 lg:px-4 lg:py-3">
                                        <?php
                                        $status_class = '';
                                        $status_text = '';
                                        $status_icon = '';
                                        
                                        switch ($record['status']) {
                                            case 1:
                                                $status_class = 'bg-green-100 text-green-800';
                                                $status_text = 'มาเรียน';
                                                $status_icon = 'fas fa-check';
                                                break;
                                            case 2:
                                                $status_class = 'bg-yellow-100 text-yellow-800';
                                                $status_text = 'มาสาย';
                                                $status_icon = 'fas fa-clock';
                                                break;
                                            case 3:
                                                $status_class = 'bg-red-100 text-red-800';
                                                $status_text = 'ขาดเรียน';
                                                $status_icon = 'fas fa-times';
                                                break;
                                        }
                                        ?>
                                        <span class="inline-flex items-center px-1.5 py-0.5 lg:px-2.5 lg:py-0.5 rounded-full text-xs font-medium <?php echo $status_class; ?>">
                                            <i class="<?php echo $status_icon; ?> mr-1"></i>
                                            <span class="hidden sm:inline"><?php echo $status_text; ?></span>
                                            <span class="sm:hidden"><?php echo substr($status_text, 0, 1); ?></span>
                                        </span>
                                    </td>
                                    <td class="px-2 py-2 lg:px-4 lg:py-3 text-xs lg:text-sm text-gray-900 hidden sm:table-cell">
                                        <?php 
                                        if ($record['teacher_name']) {
                                            echo $record['teacher_name'] . ' ' . $record['teacher_surname'];
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </td>
                                    <td class="px-2 py-2 lg:px-4 lg:py-3 text-xs lg:text-sm text-gray-500">
                                        <?php 
                                        $timestamp = new DateTime($record['timestamp']);
                                        echo $timestamp->format('H:i');
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // เพิ่ม JavaScript สำหรับการโต้ตอบ
        document.addEventListener('DOMContentLoaded', function() {
            // เพิ่ม animation เมื่อโหลดหน้า
            const cards = document.querySelectorAll('.bg-white');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
    <footer class="bg-gradient-to-r from-red-600 to-red-700 text-white py-4 text-center text-sm mt-10 w-full" style="position:relative; bottom:0; left:0;">
        <div class="container mx-auto px-4">
            <p>จัดทำโดย นายธีรภัทร เสนาคำ</p>
            <p>&copy; 2025 โรงเรียนสกลราชวิทยานุกูล. สงวนลิขสิทธิ์ทุกประการ.</p>
        </div>
    </footer>
</body>
</html>
