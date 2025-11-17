<?php
require_once 'logic/progress.logic.php';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ข้อมูลนักเรียนในห้อง - SKR Attendance</title>
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
                    <a href="progress.php" class="flex items-center px-3 py-2 rounded bg-green-800 md:bg-green-500/20 transition">
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
                    <h2 class="text-2xl font-bold text-gray-800">ข้อมูลนักเรียนในห้อง</h2>
                    <p class="text-gray-600 mt-1">ห้อง <?php echo htmlspecialchars($teacher['classroom']); ?> | <?php echo htmlspecialchars($teacher['title'] . $teacher['name'] . ' ' . $teacher['surname']); ?></p>
                </div>
                <div class="mt-4 md:mt-0 flex gap-2">
                    <button onclick="openNotificationModal()" class="inline-flex items-center px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg transition duration-200">
                        <i class="fas fa-bell mr-2"></i>
                        รายงานปัญหา
                    </button>
                    <a href="attendance.php" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition duration-200">
                        <i class="fas fa-clipboard-check mr-2"></i>
                        เช็คชื่อ
                    </a>
                    <a href="Main.php" class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition duration-200">
                        <i class="fas fa-arrow-left mr-2"></i>
                        กลับหน้าหลัก
                    </a>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6 flex items-center">
                <i class="fas fa-check-circle mr-2"></i>
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6 flex items-center">
                <i class="fas fa-times-circle mr-2"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- Summary Card -->
        <div class="bg-white rounded-xl shadow-md p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="text-center">
                    <div class="text-3xl font-bold text-green-600"><?php echo $total_students; ?></div>
                    <div class="text-sm text-gray-600">จำนวนนักเรียนทั้งหมด</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-blue-600"><?php echo date('d/m/Y', strtotime($monday)); ?></div>
                    <div class="text-sm text-gray-600">เริ่มต้นสัปดาห์</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-bold text-purple-600"><?php echo date('d'); ?></div>
                    <div class="text-sm text-gray-600">วันที่</div>
                </div>
            </div>
        </div>

        <!-- Students List -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <i class="fas fa-users mr-2 text-green-600"></i>
                    รายชื่อนักเรียนห้อง <?php echo htmlspecialchars($teacher['classroom']); ?> (สัปดาห์นี้)
                </h3>
            </div>
            
            <?php if (empty($students)): ?>
                <div class="p-6 text-center text-gray-500">
                    <i class="fas fa-users text-4xl mb-4 text-gray-300"></i>
                    <p>ไม่มีนักเรียนในห้องเรียนนี้</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">เลขที่</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ชื่อ-นามสกุล</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ปีที่เข้ามาเรียน</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ห้องเรียน</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">อัตราการมาเรียน</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($students as $student): ?>
                                <?php 
                                $stats = isset($stats_by_student[$student['id']]) ? $stats_by_student[$student['id']] : [
                                    'present' => 0,
                                    'absent' => 0,
                                    'leave_status' => 0,
                                    'late' => 0,
                                    'total_days' => 0
                                ];
                                
                                $total_days = $stats['total_days'];
                                $present_rate = $total_days > 0 ? round(($stats['present'] / $total_days) * 100, 1) : 0;
                                
                                // กำหนดสีตามอัตราการมาเรียน
                                $rate_color = '';
                                $rate_bg = '';
                                if ($present_rate >= 90) {
                                    $rate_color = 'text-green-600';
                                    $rate_bg = 'bg-green-100';
                                } elseif ($present_rate >= 80) {
                                    $rate_color = 'text-yellow-600';
                                    $rate_bg = 'bg-yellow-100';
                                } else {
                                    $rate_color = 'text-red-600';
                                    $rate_bg = 'bg-red-100';
                                }
                                ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($student['number']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                    <i class="fas fa-user text-gray-600"></i>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?php echo htmlspecialchars($student['title'] . $student['name'] . ' ' . $student['surname']); ?>
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    <?php echo htmlspecialchars($student['username']); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($student['year']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo htmlspecialchars($student['classroom']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-1">
                                                <div class="flex items-center justify-between mb-1">
                                                    <span class="text-sm font-medium <?php echo $rate_color; ?>">
                                                        <?php echo $present_rate; ?>%
                                                    </span>
                                                    <span class="text-xs text-gray-500">
                                                        <?php echo $stats['present']; ?>/<?php echo $total_days; ?>
                                                    </span>
                                                </div>
                                                <div class="w-full bg-gray-200 rounded-full h-2">
                                                    <div class="bg-green-600 h-2 rounded-full" style="width: <?php echo $present_rate; ?>%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Notification Modal -->
    <div id="notificationModal" class="modal">
        <div class="modal-content">
            <div class="bg-gradient-to-r from-orange-600 to-orange-500 text-white px-6 py-4 rounded-t-lg">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold">
                        <i class="fas fa-bell mr-2"></i>
                        รายงานปัญหา/แจ้งเตือน
                    </h3>
                    <button onclick="closeNotificationModal()" class="text-white hover:text-gray-200">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>
            <div class="p-6">
                <form method="POST" action="">
                    <div class="mb-4">
                        <label for="notification_type" class="block text-sm font-medium text-gray-700 mb-2">
                            ประเภทการแจ้งเตือน
                        </label>
                        <select name="notification_type" id="notification_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" required>
                            <option value="info">ข้อมูลทั่วไป</option>
                            <option value="warning">คำเตือน</option>
                            <option value="error">ปัญหา/ข้อผิดพลาด</option>
                            <option value="success">ความสำเร็จ</option>
                        </select>
                    </div>
                    
                    <div class="mb-4">
                        <label for="notification_title" class="block text-sm font-medium text-gray-700 mb-2">
                            หัวข้อ
                        </label>
                        <input type="text" name="notification_title" id="notification_title" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" 
                               placeholder="กรอกหัวข้อการแจ้งเตือน" required>
                    </div>
                    
                    <div class="mb-6">
                        <label for="notification_message" class="block text-sm font-medium text-gray-700 mb-2">
                            ข้อความ
                        </label>
                        <textarea name="notification_message" id="notification_message" rows="4" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent" 
                                  placeholder="กรอกรายละเอียดปัญหา หรือข้อความที่ต้องการแจ้งเตือนแอดมิน" required></textarea>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeNotificationModal()" 
                                class="px-4 py-2 text-gray-600 bg-gray-200 hover:bg-gray-300 rounded-lg transition duration-200">
                            ยกเลิก
                        </button>
                        <button type="submit" name="send_notification" 
                                class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-lg transition duration-200">
                            <i class="fas fa-paper-plane mr-2"></i>
                            ส่งการแจ้งเตือน
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            document.getElementById('mobile-menu').classList.toggle('hidden');
        });

        // Modal functions
        function openNotificationModal() {
            document.getElementById('notificationModal').style.display = 'block';
        }

        function closeNotificationModal() {
            document.getElementById('notificationModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('notificationModal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }
    </script>
    <footer class="bg-gradient-to-r from-green-700 to-green-600 text-white py-4 text-center text-sm mt-10 w-full" style="position:relative; bottom:0; left:0;">
        <div class="container mx-auto px-4">
            <p>จัดทำโดย นายธีรภัทร เสนาคำ</p>
            <p>&copy; 2025 โรงเรียนสกลราชวิทยานุกูล. สงวนลิขสิทธิ์ทุกประการ.</p>
        </div>
    </footer>
</body>
</html>
