<?php
require_once '../config.php';
require_once 'logic/attendance_report.logic.php';

// Set default timezone
date_default_timezone_set('Asia/Bangkok');

// Device detection (optional)
$isMobile = preg_match("/(android|webos|iphone|ipad|ipod|blackberry|windows phone)/i", $_SERVER['HTTP_USER_AGENT']);
$isTablet = preg_match("/(ipad|tablet|playbook|silk)|(android(?!.*mobile))/i", $_SERVER['HTTP_USER_AGENT']);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายงานการเช็คชื่อ - SKR Attendance</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography,aspect-ratio"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
        }
        
        /* Responsive table */
        .responsive-table {
            display: block;
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        
        /* Print styles */
        @media print {
            nav, button, .no-print {
                display: none !important;
            }
            body, .container {
                margin: 0 !important;
                padding: 0 !important;
                font-size: 12pt !important;
            }
            table {
                page-break-inside: auto;
                font-size: 10pt !important;
            }
            tr {
                page-break-inside: avoid;
            }
        }
        
        /* Mobile specific */
        @media (max-width: 640px) {
            .mobile-stack {
                flex-direction: column !important;
            }
            .mobile-text-center {
                text-align: center !important;
            }
            .mobile-p-2 {
                padding: 0.5rem !important;
            }
        }
        
        /* Tablet specific */
        @media (min-width: 641px) and (max-width: 1024px) {
            .tablet-w-full {
                width: 100% !important;
            }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navbar (copied from Main.php) -->
    <nav class="bg-gradient-to-r from-blue-700 to-blue-600 text-white px-4 py-3 shadow-lg sticky top-0 z-50 print:hidden">
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
                    <a href="attendance_report.php" class="flex items-center px-3 py-2 rounded bg-blue-800 md:bg-blue-500/20 font-bold transition">
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
    <main class="container mx-auto px-4 py-6 print:py-2">
        <!-- Header -->
        <header class="mb-6 print:mb-2">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800 flex items-center">
                <i class="fas fa-file-alt text-blue-600 mr-2"></i>
                รายงานการเช็คชื่อ
            </h1>
            <?php if (!empty($summary)): ?>
            <p class="text-sm text-gray-500 mt-1">
                ข้อมูลวันที่ <?php echo date('d/m/Y', strtotime($_GET['start_date'])); ?> ถึง <?php echo date('d/m/Y', strtotime($_GET['end_date'])); ?>
            </p>
            <?php endif; ?>
        </header>

        <!-- Filter Section -->
        <section class="bg-white rounded-lg shadow-md p-4 mb-6 print:shadow-none print:border">
            <h2 class="text-lg font-semibold mb-3 flex items-center">
                <i class="fas fa-filter text-blue-600 mr-2"></i>
                กรองข้อมูล
            </h2>
            
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Classroom Select -->
                <div>
                    <label for="classroom" class="block text-sm font-medium text-gray-700 mb-1">ห้องเรียน</label>
                    <select id="classroom" name="classroom" class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">เลือกห้องเรียน</option>
                        <?php foreach ($classrooms as $classroom): ?>
                        <option value="<?php echo htmlspecialchars($classroom); ?>" <?php echo (isset($_GET['classroom']) && $_GET['classroom'] == $classroom) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($classroom); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <!-- Start Date -->
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">วันที่เริ่มต้น</label>
                    <input type="date" id="start_date" name="start_date" 
                           value="<?php echo isset($_GET['start_date']) ? htmlspecialchars($_GET['start_date']) : date('Y-m-01'); ?>" 
                           class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <!-- End Date -->
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">วันที่สิ้นสุด</label>
                    <input type="date" id="end_date" name="end_date" 
                           value="<?php echo isset($_GET['end_date']) ? htmlspecialchars($_GET['end_date']) : date('Y-m-d'); ?>" 
                           class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <!-- Submit Button -->
                <div class="flex items-end">
                    <button type="submit" 
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition">
                        <i class="fas fa-search mr-2"></i> ดำเนินการ
                    </button>
                </div>
            </form>
        </section>

        <?php if (!empty($summary)): ?>
        <!-- Statistics Summary -->
        <section class="bg-white rounded-xl shadow-md overflow-hidden p-6 mb-6">
            <header class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold flex items-center">
                    <i class="fas fa-chart-bar text-blue-600 mr-2"></i>
                    สรุปสถิติ
                </h2>
                <!-- Summary Stats (color matched to Main.php) -->
                <div class="flex flex-wrap gap-2 text-sm">
                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded">
                        มา: <?php echo array_sum(array_column($summary, 'present')); ?>
                    </span>
                    <span class="bg-orange-100 text-orange-800 px-2 py-1 rounded">
                        สาย: <?php echo array_sum(array_column($summary, 'late')); ?>
                    </span>
                    <span class="bg-red-100 text-red-800 px-2 py-1 rounded">
                        ขาด: <?php echo array_sum(array_column($summary, 'absent')); ?>
                    </span>
                    <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded">
                        ลา: <?php echo array_sum(array_column($summary, 'leave_count')); ?>
                    </span>
                </div>
            </header>
            <!-- Chart (static canvas, responsive) -->
            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 mb-6">
                <canvas id="attendanceChart" style="width:100%;max-width:600px;height:250px;"></canvas>
            </div>
            <!-- Responsive Table Container -->
            <div class="responsive-table">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">เลขที่</th>
                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ชื่อ-นามสกุล</th>
                            <th scope="col" class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">มา</th>
                            <th scope="col" class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">สาย</th>
                            <th scope="col" class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">ขาด</th>
                            <th scope="col" class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">ลา</th>
                            <th scope="col" class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">รวม</th>
                            <th scope="col" class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">%</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($summary as $student): ?>
                        <?php 
                        $total_days = $student['total_days'];
                        $present_days = $student['present'] + $student['late'];
                        $attendance_rate = $total_days > 0 ? round(($present_days / $total_days) * 100, 1) : 0;
                        ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($student['number']); ?></td>
                            <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($student['name'] . ' ' . $student['surname']); ?></td>
                            <td class="px-3 py-4 whitespace-nowrap text-sm text-center text-green-700 font-bold"><?php echo htmlspecialchars($student['present']); ?></td>
                            <td class="px-3 py-4 whitespace-nowrap text-sm text-center text-orange-700 font-bold"><?php echo htmlspecialchars($student['late']); ?></td>
                            <td class="px-3 py-4 whitespace-nowrap text-sm text-center text-red-700 font-bold"><?php echo htmlspecialchars($student['absent']); ?></td>
                            <td class="px-3 py-4 whitespace-nowrap text-sm text-center text-yellow-700 font-bold"><?php echo htmlspecialchars($student['leave_count']); ?></td>
                            <td class="px-3 py-4 whitespace-nowrap text-sm text-center font-medium"><?php echo htmlspecialchars($total_days); ?></td>
                            <td class="px-3 py-4 whitespace-nowrap text-sm text-center font-medium <?php echo $attendance_rate >= 80 ? 'text-green-600' : ($attendance_rate >= 60 ? 'text-yellow-600' : 'text-red-600'); ?>">
                                <?php echo htmlspecialchars($attendance_rate); ?>%
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Detailed Records -->
        <section class="bg-white rounded-lg shadow-md p-4 mb-6 print:shadow-none print:border">
            <header class="mb-4">
                <h2 class="text-lg font-semibold flex items-center">
                    <i class="fas fa-list text-blue-600 mr-2"></i>
                    รายละเอียดการเช็คชื่อ
                </h2>
            </header>
            
            <div class="responsive-table">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">เลขที่</th>
                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ชื่อ-นามสกุล</th>
                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">วันที่</th>
                            <th scope="col" class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">สถานะ</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($report_data as $record): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($record['number']); ?></td>
                            <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($record['name'] . ' ' . $record['surname']); ?></td>
                            <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo date('d/m/Y', strtotime($record['date'])); ?></td>
                            <td class="px-3 py-4 whitespace-nowrap text-sm text-center">
                                <?php 
                                $status_class = '';
                                switch($record['status']) {
                                    case 1: $status_class = 'bg-green-100 text-green-800'; break;
                                    case 2: $status_class = 'bg-yellow-100 text-yellow-800'; break;
                                    case 3: $status_class = 'bg-red-100 text-red-800'; break;
                                    case 4: $status_class = 'bg-blue-100 text-blue-800'; break;
                                    default: $status_class = 'bg-gray-100 text-gray-800';
                                }
                                ?>
                                <span class="px-2 py-1 rounded-full text-xs font-medium <?php echo $status_class; ?>">
                                    <?php echo htmlspecialchars($record['status_text']); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-3 justify-center no-print">
            <button onclick="window.print()" 
                    class="flex items-center justify-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md transition focus:outline-none focus:ring-2 focus:ring-green-500">
                <i class="fas fa-print mr-2"></i> พิมพ์รายงาน
            </button>
            
            <button id="exportExcel" 
                    class="flex items-center justify-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition focus:outline-none focus:ring-2 focus:ring-blue-500">
                <i class="fas fa-file-excel mr-2"></i> ส่งออก Excel
            </button>
            
            <button id="shareReport" 
                    class="flex items-center justify-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-md transition focus:outline-none focus:ring-2 focus:ring-purple-500">
                <i class="fas fa-share-alt mr-2"></i> แชร์
            </button>
        </div>
        <?php endif; ?>
    </main>

    <footer class="bg-gradient-to-r from-blue-700 to-blue-600 text-white py-4 text-center text-sm mt-10 w-full" style="position:relative; bottom:0; left:0;">
        <div class="container mx-auto px-4">
            <p>จัดทำโดย นายธีรภัทร เสนาคำ</p>
            <p>&copy; 2025 โรงเรียนสกลราชวิทยานุกูล. สงวนลิขสิทธิ์ทุกประการ.</p>
        </div>
    </footer>

    <script>
        // Mobile Menu Toggle (match Main.php)
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            const menu = document.getElementById('mobile-menu');
            menu.classList.toggle('hidden');
        });
        // Chart.js (static canvas, responsive)
        <?php if (!empty($summary)): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('attendanceChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['มา', 'สาย', 'ขาด', 'ลา'],
                    datasets: [{
                        data: [
                            <?php echo array_sum(array_column($summary, 'present')); ?>,
                            <?php echo array_sum(array_column($summary, 'late')); ?>,
                            <?php echo array_sum(array_column($summary, 'absent')); ?>,
                            <?php echo array_sum(array_column($summary, 'leave_count')); ?>
                        ],
                        backgroundColor: [
                            'rgba(34, 197, 94, 0.7)',    // มา (เขียว)
                            'rgba(251, 146, 60, 0.7)',    // สาย (ส้ม)
                            'rgba(239, 68, 68, 0.7)',     // ขาด (แดง)
                            'rgba(251, 191, 36, 0.7)'     // ลา (เหลือง)
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    },
                    layout: {
                        padding: 10
                    }
                }
            });
        });
        <?php endif; ?>

        // Export to Excel
        document.getElementById('exportExcel')?.addEventListener('click', function() {
            // Create workbook
            const wb = XLSX.utils.book_new();
            
            // Add summary sheet
            const summaryData = [
                ['เลขที่', 'ชื่อ-นามสกุล', 'มา', 'สาย', 'ขาด', 'ลา', 'รวมวัน', '% การมาเรียน']
            ];
            
            <?php foreach ($summary as $student): ?>
                <?php 
                $total_days = $student['total_days'];
                $present_days = $student['present'] + $student['late'];
                $attendance_rate = $total_days > 0 ? round(($present_days / $total_days) * 100, 1) : 0;
                ?>
                summaryData.push([
                    '<?php echo $student['number']; ?>',
                    '<?php echo $student['name'] . ' ' . $student['surname']; ?>',
                    <?php echo $student['present']; ?>,
                    <?php echo $student['late']; ?>,
                    <?php echo $student['absent']; ?>,
                    <?php echo $student['leave_count']; ?>,
                    <?php echo $total_days; ?>,
                    <?php echo $attendance_rate; ?>
                ]);
            <?php endforeach; ?>
            
            const summaryWS = XLSX.utils.aoa_to_sheet(summaryData);
            XLSX.utils.book_append_sheet(wb, summaryWS, "สรุปสถิติ");
            
            // Add details sheet
            const detailsData = [
                ['เลขที่', 'ชื่อ-นามสกุล', 'วันที่', 'สถานะ']
            ];
            
            <?php foreach ($report_data as $record): ?>
                detailsData.push([
                    '<?php echo $record['number']; ?>',
                    '<?php echo $record['name'] . ' ' . $record['surname']; ?>',
                    '<?php echo date('d/m/Y', strtotime($record['date'])); ?>',
                    '<?php echo $record['status_text']; ?>'
                ]);
            <?php endforeach; ?>
            
            const detailsWS = XLSX.utils.aoa_to_sheet(detailsData);
            XLSX.utils.book_append_sheet(wb, detailsWS, "รายละเอียด");
            
            // Generate and download file
            const fileName = `รายงานการเช็คชื่อ_<?php echo isset($_GET['classroom']) ? $_GET['classroom'] : ''; ?>_<?php echo date('Ymd'); ?>.xlsx`;
            XLSX.writeFile(wb, fileName);
        });

        // Share functionality
        document.getElementById('shareReport')?.addEventListener('click', function() {
            if (navigator.share) {
                navigator.share({
                    title: 'รายงานการเช็คชื่อ',
                    text: 'รายงานการเช็คชื่อนักเรียน <?php echo isset($_GET['classroom']) ? $_GET['classroom'] : ''; ?>',
                    url: window.location.href
                }).catch(err => {
                    console.log('Error sharing:', err);
                });
            } else {
                // Fallback for browsers that don't support Web Share API
                alert('ฟังก์ชันแชร์ไม่รองรับในเบราว์เซอร์นี้ กรุณาคัดลอกลิงก์ด้านบน');
            }
        });
    </script>
</body>
</html>