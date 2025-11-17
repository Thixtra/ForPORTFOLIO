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
    <title>แอดมิน - SKR Attendance</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography,aspect-ratio"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navbar -->
    <nav class="bg-gradient-to-r from-blue-700 to-blue-600 text-white px-4 py-3 shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row items-center justify-between">
            <div class="flex items-center justify-between w-full md:w-auto">
                <div class="text-xl font-bold flex items-center">
                    <i class="fas fa-chalkboard-teacher mr-2"></i>
                    <span>SKR Admin</span>
                </div>
                <button id="mobile-menu-button" class="md:hidden focus:outline-none">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>
            
            <ul id="mobile-menu" class="hidden md:flex space-x-1 md:space-x-2 lg:space-x-4 text-sm md:text-base mt-4 md:mt-0 flex-col md:flex-row w-full md:w-auto bg-blue-700 md:bg-transparent p-2 md:p-0 rounded-lg">
                <li>
                    <a href="Main.php" class="flex items-center px-3 py-2 rounded hover:bg-blue-800 md:hover:bg-blue-500/20 transition">
                        <i class="fas fa-home mr-2 w-5 text-center"></i>
                        <span>หน้าหลัก</span>
                    </a>
                </li>
                <li>
                    <a href="add_student.php" class="flex items-center px-3 py-2 rounded hover:bg-blue-800 md:hover:bg-blue-500/20 transition">
                        <i class="fas fa-user-plus mr-2 w-5 text-center"></i>
                        <span>เพิ่มนักเรียน</span>
                    </a>
                </li>
                <li>
                    <a href="add_teacher.php" class="flex items-center px-3 py-2 rounded hover:bg-blue-800 md:hover:bg-blue-500/20 transition">
                        <i class="fas fa-chalkboard-user mr-2 w-5 text-center"></i>
                        <span>เพิ่มอาจารย์</span>
                    </a>
                </li>
                <li>
                    <a href="manage.php" class="flex items-center px-3 py-2 rounded hover:bg-blue-800 md:hover:bg-blue-500/20 transition">
                        <i class="fas fa-tasks mr-2 w-5 text-center"></i>
                        <span>จัดการข้อมูล</span>
                    </a>
                </li>
                <li>
                    <a href="attendance_report.php" class="flex items-center px-3 py-2 rounded hover:bg-blue-800 md:hover:bg-blue-500/20 transition">
                        <i class="fas fa-file-alt mr-2 w-5 text-center"></i>
                        <span>รายงาน</span>
                    </a>
                </li>
                <li>
                    <a href="notify.php" class="flex items-center px-3 py-2 rounded hover:bg-blue-800 md:hover:bg-blue-500/20 transition">
                        <i class="fas fa-bell mr-2 w-5 text-center"></i>
                        <span>แจ้งเตือน</span>
                    </a>
                </li>
                <li class="mt-2 md:mt-0 border-t md:border-t-0 pt-2 md:pt-0 border-blue-600/50">
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
        <div class="bg-white rounded-xl shadow-md overflow-hidden p-6 mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">ยินดีต้อนรับ, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
                    <p class="text-gray-600 mt-1">นี่คือหน้าหลักสำหรับผู้ดูแลระบบ</p>
                </div>
                <div class="mt-4 md:mt-0">
                    <span class="inline-block bg-blue-100 text-blue-800 text-sm px-3 py-1 rounded-full">
                        <i class="fas fa-calendar-day mr-1"></i>
                        <?php echo date('d/m/Y'); ?>
                    </span>
                </div>
            </div>

            <!-- ฟอร์มเลือกช่วงวันที่ -->
            <form method="get" class="bg-gray-50 p-4 rounded-lg mb-8">
                <h3 class="text-lg font-semibold text-gray-700 mb-3 flex items-center">
                    <i class="fas fa-filter mr-2 text-blue-600"></i>
                    กรองข้อมูลตามช่วงเวลา
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">วันที่เริ่มต้น</label>
                        <div class="relative">
                            <input type="date" id="start_date" name="start_date" 
                                   value="<?php echo isset($_GET['start_date']) ? htmlspecialchars($_GET['start_date']) : date('Y-m-01'); ?>" 
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 px-3 border">
                        </div>
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">วันที่สิ้นสุด</label>
                        <div class="relative">
                            <input type="date" id="end_date" name="end_date" 
                                   value="<?php echo isset($_GET['end_date']) ? htmlspecialchars($_GET['end_date']) : date('Y-m-t'); ?>" 
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2 px-3 border">
                        </div>
                    </div>
                    <div>
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition duration-150 ease-in-out flex items-center justify-center">
                            <i class="fas fa-chart-bar mr-2"></i>
                            แสดงสถิติ
                        </button>
                    </div>
                </div>
            </form>
            
            <!-- สถิติการมาเรียน -->
            <div class="mb-10">
                <h3 class="text-xl font-semibold mb-4 flex items-center text-gray-700">
                    <i class="fas fa-chart-pie mr-2 text-blue-600"></i>
                    สถิติการมาเรียน (<?php echo htmlspecialchars($start_date); ?> ถึง <?php echo htmlspecialchars($end_date); ?>)
                </h3>
                
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
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
                
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <canvas id="attendanceChart" height="250"></canvas>
                </div>
            </div>
            
            <!-- สถานะการเช็คชื่อของอาจารย์ -->
            <div>
                <h3 class="text-xl font-semibold mb-4 flex items-center text-gray-700">
                    <i class="fas fa-clipboard-list mr-2 text-blue-600"></i>
                    สถานะการเช็คชื่อของอาจารย์ (วันนี้)
                </h3>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 rounded-lg overflow-hidden">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">ชื่อ-นามสกุล</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">ห้อง</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">สถานะ</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">รายละเอียด</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($teacher_status as $t): ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <div class="font-medium text-gray-900"><?php echo htmlspecialchars($t['name']); ?> <?php echo htmlspecialchars($t['surname']); ?></div>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                                        <?php echo htmlspecialchars($t['classroom']); ?>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <?php if ($t['checked'] > 0): ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                เช็คชื่อแล้ว
                                            </span>
                                        <?php else: ?>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                <i class="fas fa-times-circle mr-1"></i>
                                                ยังไม่เช็คชื่อ
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <button class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                            <i class="fas fa-eye mr-1"></i>
                                            ดูรายละเอียด
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        // ข้อมูลสำหรับกราฟ
        var stats_ma = <?php echo (int)$stats['มา']; ?>;
        var stats_khad = <?php echo (int)$stats['ขาด']; ?>;
        var stats_la = <?php echo (int)$stats['ลา']; ?>;
        var stats_sai = <?php echo (int)$stats['สาย']; ?>;
    </script>
    
    <!-- แยกไฟล์ JavaScript -->
    <script src="js/Main.js"></script>
    
    <!-- Mobile Menu Toggle -->
    <script>
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        });
    </script>
    <footer class="bg-gradient-to-r from-blue-700 to-blue-600 text-white py-4 text-center text-sm mt-10 w-full" style="position:relative; bottom:0; left:0;">
        <div class="container mx-auto px-4">
            <p>จัดทำโดย นายธีรภัทร เสนาคำ</p>
            <p>&copy; 2025 โรงเรียนสกลราชวิทยานุกูล. สงวนลิขสิทธิ์ทุกประการ.</p>
        </div>
    </footer>
</body>
</html>