<?php
require_once 'logic/attendance_report.logic.php';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายงานสถิติการเช็คชื่อ - SKR Attendance</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography,aspect-ratio"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
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
                    <a href="Main.php" class="flex items-center px-3 py-2 rounded hover:bg-green-800 md:hover:bg-green-500/20 transition">
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
                    <a href="attendance_report.php" class="flex items-center px-3 py-2 rounded bg-green-800 md:bg-green-500/20 transition">
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
        <!-- Header -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden p-6 mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">รายงานสถิติการเช็คชื่อ</h2>
                    <p class="text-gray-600 mt-1">ห้อง <?php echo htmlspecialchars($teacher['classroom']); ?> | <?php echo htmlspecialchars($teacher['title'] . $teacher['name'] . ' ' . $teacher['surname']); ?></p>
                </div>
                <div class="mt-4 md:mt-0 flex gap-2">
                    <a href="attendance.php" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition duration-200">
                        <i class="fas fa-arrow-left mr-2"></i>
                        กลับไปเช็คชื่อ
                    </a>
                    <button onclick="downloadReport()" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition duration-200">
                        <i class="fas fa-download mr-2"></i>
                        โหลดรายงาน
                    </button>
                </div>
            </div>
        </div>

        <!-- Date Selection -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-6">
            <form method="get" class="flex flex-col md:flex-row items-center gap-4">
                <div class="flex-1">
                    <label for="date" class="block text-sm font-medium text-gray-700 mb-2">เลือกวันที่</label>
                    <input type="date" id="date" name="date" value="<?php echo htmlspecialchars($selected_date); ?>" 
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 py-2 px-3 border">
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg transition duration-200">
                        <i class="fas fa-search mr-2"></i>
                        ดูรายงาน
                    </button>
                    <a href="attendance_report.php" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg transition duration-200">
                        <i class="fas fa-calendar-day mr-2"></i>
                        วันนี้
                    </a>
                </div>
            </form>
        </div>

        <!-- Report Container -->
        <div id="reportContainer" class="report-container">
            <!-- School Header -->
            <div class="school-header">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center mr-4 overflow-hidden">
                        <img src="../icon/SKR-logo-T.png" alt="SKR Logo" class="w-full h-full object-contain">
                    </div>
                    <div>
                        <h1 class="text-xl font-bold">โรงเรียนสกลราชวิทยานุกูล</h1>
                        <p class="text-sm opacity-90">Sakon Ratchawittayanukul School</p>
                    </div>
                </div>
            </div>

            <!-- Report Title -->
            <div class="text-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-2">สถิติการมาเรียนของนักเรียน</h2>
                <p class="text-lg text-gray-600">วันที่ <?php echo date('d/m/Y', strtotime($selected_date)); ?></p>
                <p class="text-lg text-gray-600">ระดับชั้น <?php echo htmlspecialchars($teacher['classroom']); ?></p>
            </div>

            <!-- Chart -->
            <div class="chart-container">
                <canvas id="attendanceChart"></canvas>
            </div>

            <!-- Statistics Table -->
            <div class="overflow-x-auto">
                <table class="stats-table">
                    <thead>
                        <tr>
                            <th>รายการ</th>
                            <th>ชาย</th>
                            <th>หญิง</th>
                            <th>รวม</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>มาเข้า</td>
                            <td><?php echo $stats['male']['present']; ?></td>
                            <td><?php echo $stats['female']['present']; ?></td>
                            <td><?php echo $total_present; ?></td>
                        </tr>
                        <tr>
                            <td>ขาดเรียน</td>
                            <td><?php echo $stats['male']['absent']; ?></td>
                            <td><?php echo $stats['female']['absent']; ?></td>
                            <td><?php echo $total_absent; ?></td>
                        </tr>
                        <tr>
                            <td>ลากิจ/ลาป่วย</td>
                            <td><?php echo $stats['male']['leave']; ?></td>
                            <td><?php echo $stats['female']['leave']; ?></td>
                            <td><?php echo $total_leave; ?></td>
                        </tr>
                        <tr>
                            <td>มาสาย</td>
                            <td><?php echo $stats['male']['late']; ?></td>
                            <td><?php echo $stats['female']['late']; ?></td>
                            <td><?php echo $total_late; ?></td>
                        </tr>
                        <tr style="background-color: #f8f9fa; font-weight: 600;">
                            <td>รวม</td>
                            <td><?php echo $total_male; ?></td>
                            <td><?php echo $total_female; ?></td>
                            <td><?php echo $grand_total; ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Student Lists -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
                <!-- Absent Students -->
                <div class="student-list absent-list">
                    <div class="list-header">รายชื่อนักเรียนขาดเรียน</div>
                    <?php if (empty($absent_students)): ?>
                        <div class="no-data">-</div>
                    <?php else: ?>
                        <?php foreach ($absent_students as $student): ?>
                            <div class="student-item"><?php echo htmlspecialchars($student); ?></div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Leave Students -->
                <div class="student-list leave-list">
                    <div class="list-header">รายชื่อนักเรียนลา</div>
                    <?php if (empty($leave_students)): ?>
                        <div class="no-data">-</div>
                    <?php else: ?>
                        <?php foreach ($leave_students as $student): ?>
                            <div class="student-item"><?php echo htmlspecialchars($student); ?></div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Late Students -->
                <div class="student-list late-list">
                    <div class="list-header">รายชื่อนักเรียนสาย</div>
                    <?php if (empty($late_students)): ?>
                        <div class="no-data">-</div>
                    <?php else: ?>
                        <?php foreach ($late_students as $student): ?>
                            <div class="student-item"><?php echo htmlspecialchars($student); ?></div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-gradient-to-r from-green-700 to-green-600 text-white py-4 text-center text-sm mt-10 w-full" style="position:relative; bottom:0; left:0;">
        <div class="container mx-auto px-4">
            <p>จัดทำโดย นายธีรภัทร เสนาคำ</p>
            <p>&copy; 2025 โรงเรียนสกลราชวิทยานุกูล. สงวนลิขสิทธิ์ทุกประการ.</p>
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            document.getElementById('mobile-menu').classList.toggle('hidden');
        });

        // Chart configuration
        const ctx = document.getElementById('attendanceChart').getContext('2d');
        const attendanceChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['มา', 'ขาด', 'ลา', 'สาย'],
                datasets: [
                    {
                        label: 'ชาย',
                        data: [
                            <?php echo $stats['male']['present']; ?>,
                            <?php echo $stats['male']['absent']; ?>,
                            <?php echo $stats['male']['leave']; ?>,
                            <?php echo $stats['male']['late']; ?>
                        ],
                        backgroundColor: '#dc2626',
                        borderColor: '#b91c1c',
                        borderWidth: 1
                    },
                    {
                        label: 'หญิง',
                        data: [
                            <?php echo $stats['female']['present']; ?>,
                            <?php echo $stats['female']['absent']; ?>,
                            <?php echo $stats['female']['leave']; ?>,
                            <?php echo $stats['female']['late']; ?>
                        ],
                        backgroundColor: '#ea580c',
                        borderColor: '#c2410c',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: <?php echo max($total_male, $total_female) + 2; ?>,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 20
                        }
                    }
                }
            }
        });

        // Download report function
        function downloadReport() {
            const reportContainer = document.getElementById('reportContainer');
            
            // Show loading
            const downloadBtn = event.target;
            const originalText = downloadBtn.innerHTML;
            downloadBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>กำลังสร้างรายงาน...';
            downloadBtn.disabled = true;

            html2canvas(reportContainer, {
                scale: 2,
                useCORS: true,
                allowTaint: true,
                backgroundColor: '#ffffff'
            }).then(canvas => {
                // Create download link
                const link = document.createElement('a');
                link.download = 'รายงานการเช็คชื่อ_<?php echo date('Y-m-d', strtotime($selected_date)); ?>_<?php echo htmlspecialchars($teacher['classroom']); ?>.png';
                link.href = canvas.toDataURL();
                link.click();

                // Reset button
                downloadBtn.innerHTML = originalText;
                downloadBtn.disabled = false;
            }).catch(error => {
                console.error('Error generating report:', error);
                alert('เกิดข้อผิดพลาดในการสร้างรายงาน กรุณาลองใหม่อีกครั้ง');
                downloadBtn.innerHTML = originalText;
                downloadBtn.disabled = false;
            });
        }
    </script>
</body>
</html> 