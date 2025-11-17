<?php
// ตรวจสอบโหมด Mock
if (defined('MOCK_MODE') && MOCK_MODE === true) {
    require_once '../config_mock.php';
} else {
    require_once '../config.php';
}
require_once 'logic/Main.logic.php';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>อาจารย์ - SKR Attendance</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography,aspect-ratio"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navbar -->
    <nav class="bg-gradient-to-r from-green-700 to-green-600 text-white px-4 py-3 shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between">
            <div class="flex items-center justify-between w-full md:w-auto">
                <div class="text-xl font-bold flex items-center">
                    <i class="fas fa-chalkboard-teacher mr-2"></i>
                    <span>SKR Teacher</span>
                </div>
                <button id="mobile-menu-button" class="md:hidden focus:outline-none">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>
            
            <ul id="mobile-menu" class="hidden md:flex space-x-1 md:space-x-2 lg:space-x-4 text-sm md:text-base mt-4 md:mt-0 flex-col md:flex-row w-full md:w-auto bg-green-700 md:bg-transparent p-2 md:p-0 rounded-lg">
                <li>
                    <a href="Main.php" class="flex items-center px-3 py-2 rounded bg-green-800 md:bg-green-500/20 transition">
                        <i class="fas fa-home mr-2 w-5 text-center"></i>
                        <span>หน้าหลัก</span>
                    </a>
                </li>
                <li>
                    <a href="attendance.php" class="flex items-center px-3 py-2 rounded hover:bg-green-800 md:hover:bg-green-500/20 transition">
                        <i class="fas fa-clipboard-check mr-2 w-5 text-center"></i>
                        <span>เช็คชื่อ</span>
                    </a>
                </li>
                <li>
                    <a href="attendance_report.php" class="flex items-center px-3 py-2 rounded hover:bg-green-800 md:hover:bg-green-500/20 transition">
                        <i class="fas fa-chart-bar mr-2 w-5 text-center"></i>
                        <span>รายงานสถิติ</span>
                    </a>
                </li>
                <li>
                    <a href="progress.php" class="flex items-center px-3 py-2 rounded hover:bg-green-800 md:hover:bg-green-500/20 transition">
                        <i class="fas fa-users mr-2 w-5 text-center"></i>
                        <span>ข้อมูลนักเรียนในห้อง</span>
                    </a>
                </li>
                <li class="mt-2 md:mt-0 border-t md:border-t-0 pt-2 md:pt-0 border-green-600/50">
                    <a href="../login.php?logout=1" class="flex items-center px-3 py-2 rounded hover:bg-red-600/20 transition text-red-200">
                        <i class="fas fa-sign-out-alt mr-2 w-5 text-center"></i>
                        <span>ออกจากระบบ</span>
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 py-6 sm:px-6 lg:px-8">
        <!-- Welcome Section -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden p-6 mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">ยินดีต้อนรับ, <?php echo htmlspecialchars($teacher['title'] . $teacher['name'] . ' ' . $teacher['surname']); ?>!</h2>
                    <p class="text-gray-600 mt-1">ห้อง <?php echo htmlspecialchars($teacher['classroom']); ?> | กลุ่มสาระ <?php echo htmlspecialchars($teacher['subject_group']); ?></p>
                </div>
                <div class="mt-4 md:mt-0 flex flex-col items-end">
                    <span class="inline-block bg-green-100 text-green-800 text-sm px-3 py-1 rounded-full mb-2">
                        <i class="fas fa-calendar-day mr-1"></i>
                        <?php echo date('d/m/Y'); ?>
                    </span>
                    <span class="text-sm text-gray-500">
                        <i class="fas fa-clock mr-1"></i>
                        <?php echo date('H:i'); ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-gradient-to-br from-green-100 to-green-50 border border-green-200 rounded-lg p-4 shadow-sm">
                <div class="text-green-800 text-sm font-medium mb-1 flex items-center">
                    <i class="fas fa-check-circle mr-1"></i> มา
                </div>
                <div class="text-2xl font-bold text-green-700"><?php echo $stats['มา']; ?></div>
                <div class="text-xs text-green-600 mt-1">นักเรียน</div>
            </div>
            <div class="bg-gradient-to-br from-red-100 to-red-50 border border-red-200 rounded-lg p-4 shadow-sm">
                <div class="text-red-800 text-sm font-medium mb-1 flex items-center">
                    <i class="fas fa-times-circle mr-1"></i> ขาด
                </div>
                <div class="text-2xl font-bold text-red-700"><?php echo $stats['ขาด']; ?></div>
                <div class="text-xs text-red-600 mt-1">นักเรียน</div>
            </div>
            <div class="bg-gradient-to-br from-yellow-100 to-yellow-50 border border-yellow-200 rounded-lg p-4 shadow-sm">
                <div class="text-yellow-800 text-sm font-medium mb-1 flex items-center">
                    <i class="fas fa-bed mr-1"></i> ลา
                </div>
                <div class="text-2xl font-bold text-yellow-700"><?php echo $stats['ลา']; ?></div>
                <div class="text-xs text-yellow-600 mt-1">นักเรียน</div>
            </div>
            <div class="bg-gradient-to-br from-orange-100 to-orange-50 border border-orange-200 rounded-lg p-4 shadow-sm">
                <div class="text-orange-800 text-sm font-medium mb-1 flex items-center">
                    <i class="fas fa-clock mr-1"></i> สาย
                </div>
                <div class="text-2xl font-bold text-orange-700"><?php echo $stats['สาย']; ?></div>
                <div class="text-xs text-orange-600 mt-1">นักเรียน</div>
            </div>
        </div>



        <!-- Today's Attendance Status -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-clipboard-list mr-2 text-green-600"></i>
                    สถานะการเช็คชื่อวันนี้ (<?php echo date('d/m/Y'); ?>)
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">เลขที่</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ชื่อ-นามสกุล</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">สถานะ</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">เวลา</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($students)): ?>
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                    ไม่มีนักเรียนในห้องเรียนนี้
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($students as $student): ?>
                                <?php 
                                $status = isset($attendance_status[$student['id']]) ? $attendance_status[$student['id']] : 0;
                                $status_text = '';
                                $status_color = '';
                                $status_icon = '';
                                
                                switch ($status) {
                                    case 1:
                                        $status_text = 'มา';
                                        $status_color = 'bg-green-100 text-green-800';
                                        $status_icon = 'fas fa-check-circle';
                                        break;
                                    case 2:
                                        $status_text = 'ขาด';
                                        $status_color = 'bg-red-100 text-red-800';
                                        $status_icon = 'fas fa-times-circle';
                                        break;
                                    case 3:
                                        $status_text = 'ลา';
                                        $status_color = 'bg-yellow-100 text-yellow-800';
                                        $status_icon = 'fas fa-bed';
                                        break;
                                    case 4:
                                        $status_text = 'สาย';
                                        $status_color = 'bg-orange-100 text-orange-800';
                                        $status_icon = 'fas fa-clock';
                                        break;
                                    default:
                                        $status_text = 'ยังไม่เช็ค';
                                        $status_color = 'bg-gray-100 text-gray-800';
                                        $status_icon = 'fas fa-minus';
                                        break;
                                }
                                
                                // หาเวลาเช็คชื่อ
                                $check_time = '';
                                if ($status > 0) {
                                    foreach ($today_attendance as $att) {
                                        if ($att['student_id'] == $student['id']) {
                                            $check_time = date('H:i', strtotime($att['timestamp']));
                                            break;
                                        }
                                    }
                                }
                                ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($student['number']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($student['title'] . $student['name'] . ' ' . $student['surname']); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $status_color; ?>">
                                            <i class="<?php echo $status_icon; ?> mr-1"></i>
                                            <?php echo $status_text; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo $check_time ? $check_time : '-'; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Weekly Chart -->
        <?php if (!empty($weekly_stats)): ?>
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-chart-line mr-2 text-green-600"></i>
                    สถิติการมาเรียนสัปดาห์นี้ (ตั้งแต่วันจันทร์)
                </h3>
            </div>
            <div class="p-6">
                <canvas id="weeklyChart" height="100"></canvas>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <footer class="bg-gradient-to-r from-green-700 to-green-600 text-white py-4 text-center text-sm mt-10 w-full" style="position:relative; bottom:0; left:0;">
        <div class="container mx-auto px-4">
            <p>จัดทำโดย นายธีรภัทร เสนาคำ</p>
            <p>&copy; 2025 โรงเรียนสกลราชวิทยานุกูล. สงวนลิขสิทธิ์ทุกประการ.</p>
        </div>
    </footer>
    <script src="js/teacher.js"></script>
    <script>
        // Initialize weekly chart with data
        <?php if (!empty($weekly_stats)): ?>
        const weeklyStats = <?php echo json_encode($weekly_stats); ?>;
        initializeWeeklyChart(weeklyStats);
        <?php endif; ?>
    </script>
</body>
</html>
