<?php
require_once 'logic/attendance.logic.php';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เช็คชื่อนักเรียน - SKR Attendance</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography,aspect-ratio"></script>
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
                    <a href="Main.php" class="flex items-center px-3 py-2 rounded hover:bg-green-800 md:hover:bg-green-500/20 transition">
                        <i class="fas fa-home mr-2 w-5 text-center"></i>
                        <span>หน้าหลัก</span>
                    </a>
                </li>
                <li>
                    <a href="attendance.php" class="flex items-center px-3 py-2 rounded bg-green-800 md:bg-green-500/20 transition">
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
        <!-- Header -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden p-6 mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">เช็คชื่อนักเรียน</h2>
                    <p class="text-gray-600 mt-1">ห้อง <?php echo htmlspecialchars($teacher['classroom']); ?> | <?php echo htmlspecialchars($teacher['title'] . $teacher['name'] . ' ' . $teacher['surname']); ?></p>
                </div>
                <div class="mt-4 md:mt-0 flex gap-2">
                    <a href="Main.php" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition duration-200">
                        <i class="fas fa-arrow-left mr-2"></i>
                        กลับหน้าหลัก
                    </a>
                    <a href="attendance_report.php" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition duration-200">
                        <i class="fas fa-chart-bar mr-2"></i>
                        ดูรายงานสถิติ
                    </a>
                </div>
            </div>
        </div>

        <!-- Messages -->
        <?php if (isset($success_message)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                <i class="fas fa-check-circle mr-2"></i>
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                <i class="fas fa-exclamation-circle mr-2"></i>
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

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
                        ดูข้อมูล
                    </button>
                    <a href="attendance.php" class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg transition duration-200">
                        <i class="fas fa-calendar-day mr-2"></i>
                        วันนี้
                    </a>
                </div>
            </form>
        </div>

        <!-- Attendance Form -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-clipboard-list mr-2 text-green-600"></i>
                    รายชื่อนักเรียน - <?php echo date('d/m/Y', strtotime($selected_date)); ?>
                </h3>
            </div>
            
            <?php if (empty($students)): ?>
                <div class="p-6 text-center text-gray-500">
                    <i class="fas fa-users text-4xl mb-4 text-gray-300"></i>
                    <p>ไม่มีนักเรียนในห้องเรียนนี้</p>
                </div>
            <?php else: ?>
                <form method="post" id="attendanceForm">
                    <input type="hidden" name="date" value="<?php echo htmlspecialchars($selected_date); ?>">
                    
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
                                <?php foreach ($students as $student): ?>
                                    <?php 
                                    $current_status = isset($attendance_status[$student['id']]) ? $attendance_status[$student['id']] : 0;
                                    $check_time = '';
                                    
                                    // หาเวลาเช็คชื่อ
                                    foreach ($attendance_data as $att) {
                                        if ($att['student_id'] == $student['id']) {
                                            $check_time = date('H:i', strtotime($att['timestamp']));
                                            break;
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
                                            <input type="hidden" name="student_id[]" value="<?php echo $student['id']; ?>">
                                            <select name="status[]" class="rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500 py-1 px-2 text-sm border" 
                                                    onchange="updateStatus(this, <?php echo $student['id']; ?>)">
                                                <option value="0" <?php echo $current_status == 0 ? 'selected' : ''; ?>>ยังไม่เช็ค</option>
                                                <option value="1" <?php echo $current_status == 1 ? 'selected' : ''; ?>>มา</option>
                                                <option value="2" <?php echo $current_status == 2 ? 'selected' : ''; ?>>ขาด</option>
                                                <option value="3" <?php echo $current_status == 3 ? 'selected' : ''; ?>>ลา</option>
                                                <option value="4" <?php echo $current_status == 4 ? 'selected' : ''; ?>>สาย</option>
                                            </select>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo $check_time ? $check_time : '-'; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                        <div class="flex flex-col sm:flex-row gap-3">
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg transition duration-200 flex items-center justify-center">
                                <i class="fas fa-save mr-2"></i>
                                บันทึกการเช็คชื่อ
                            </button>
                            <button type="button" onclick="selectAll('มา')" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-200">
                                เลือกทั้งหมด "มา"
                            </button>
                            <button type="button" onclick="selectAll('ขาด')" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition duration-200">
                                เลือกทั้งหมด "ขาด"
                            </button>
                            <button type="button" onclick="selectAll('ลา')" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg transition duration-200">
                                เลือกทั้งหมด "ลา"
                            </button>
                            <button type="button" onclick="clearAll()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition duration-200">
                                ล้างทั้งหมด
                            </button>
                        </div>
                    </div>
                </form>
            <?php endif; ?>
        </div>


    </div>

    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            document.getElementById('mobile-menu').classList.toggle('hidden');
        });

        // Update status function
        function updateStatus(select, studentId) {
            // สามารถเพิ่ม AJAX call เพื่อบันทึกแบบ real-time ได้ที่นี่
            console.log('Student ID:', studentId, 'Status:', select.value);
        }

        // Select all function
        function selectAll(status) {
            const selects = document.querySelectorAll('select[name="status[]"]');
            const statusValue = {
                'มา': '1',
                'ขาด': '2',
                'ลา': '3',
                'สาย': '4'
            };
            
            selects.forEach(select => {
                select.value = statusValue[status];
            });
        }

        // Clear all function
        function clearAll() {
            const selects = document.querySelectorAll('select[name="status[]"]');
            selects.forEach(select => {
                select.value = '0';
            });
        }

        // Auto-submit form when status changes (optional)
        document.getElementById('attendanceForm').addEventListener('change', function(e) {
            if (e.target.name === 'status[]') {
                // สามารถเปิดใช้งาน auto-save ได้ที่นี่
                // this.submit();
            }
        });
    </script>
    <footer class="bg-gradient-to-r from-green-700 to-green-600 text-white py-4 text-center text-sm mt-10 w-full" style="position:relative; bottom:0; left:0;">
        <div class="container mx-auto px-4">
            <p>จัดทำโดย นายธีรภัทร เสนาคำ</p>
            <p>&copy; 2025 โรงเรียนสกลราชวิทยานุกูล. สงวนลิขสิทธิ์ทุกประการ.</p>
        </div>
    </footer>
</body>
</html>
