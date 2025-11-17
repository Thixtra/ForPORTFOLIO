<?php
require_once '../config.php';
require_once 'logic/manage.logic.php';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>จัดการข้อมูล - SKR Attendance</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography,aspect-ratio"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { 
            font-family: 'Sarabun', sans-serif;
            -webkit-text-size-adjust: 100%;
        }
        
        /* สไตล์สำหรับ toggle switches */
        .attendance-toggle {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 30px;
        }
        
        .attendance-toggle input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #e2e8f0;
            transition: .4s;
            border-radius: 15px;
            -webkit-tap-highlight-color: transparent;
        }
        
        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 22px;
            width: 22px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .toggle-slider {
            background-color: #3b82f6;
        }
        
        input:checked + .toggle-slider:before {
            transform: translateX(30px);
        }
        
        /* สไตล์เฉพาะสำหรับสถานะต่างๆ */
        .present:checked + .toggle-slider { background-color: #10b981; }
        .late:checked + .toggle-slider { background-color: #f59e0b; }
        .absent:checked + .toggle-slider { background-color: #ef4444; }
        .leave:checked + .toggle-slider { background-color: #8b5cf6; }
        
        /* เอฟเฟกต์เมื่อโฮเวอร์ */
        .hover-scale {
            transition: transform 0.2s ease;
        }
        @media (hover: hover) {
            .hover-scale:hover {
                transform: scale(1.02);
            }
        }
        
        /* สไตล์สำหรับตาราง */
        .table-row-hover:hover {
            background-color: #f8fafc;
        }
        
        /* ปรับปรุงสำหรับมือถือ */
        @media (max-width: 640px) {
            .attendance-toggle {
                width: 50px;
                height: 25px;
            }
            .toggle-slider:before {
                height: 18px;
                width: 18px;
                bottom: 3.5px;
                left: 3.5px;
            }
            input:checked + .toggle-slider:before {
                transform: translateX(25px);
            }
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
                    <span class="whitespace-nowrap">SKR Admin</span>
                </div>
                <button id="mobile-menu-button" class="md:hidden focus:outline-none" aria-label="เมนู">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>
            <ul id="mobile-menu" class="hidden md:flex space-x-1 md:space-x-2 lg:space-x-4 text-sm md:text-base mt-4 md:mt-0 flex-col md:flex-row w-full md:w-auto bg-blue-700 md:bg-transparent p-2 md:p-0 rounded-lg">
                <li><a href="Main.php" class="flex items-center px-3 py-2 rounded hover:bg-blue-800 md:hover:bg-blue-500/20 transition"><i class="fas fa-home mr-2 w-5 text-center"></i>หน้าหลัก</a></li>
                <li><a href="add_student.php" class="flex items-center px-3 py-2 rounded hover:bg-blue-800 md:hover:bg-blue-500/20 transition"><i class="fas fa-user-plus mr-2 w-5 text-center"></i>เพิ่มนักเรียน</a></li>
                <li><a href="add_teacher.php" class="flex items-center px-3 py-2 rounded hover:bg-blue-800 md:hover:bg-blue-500/20 transition"><i class="fas fa-chalkboard-user mr-2 w-5 text-center"></i>เพิ่มอาจารย์</a></li>
                <li><a href="manage.php" class="flex items-center px-3 py-2 rounded bg-blue-800 md:bg-blue-500/20 font-bold"><i class="fas fa-tasks mr-2 w-5 text-center"></i>จัดการข้อมูล</a></li>
                <li><a href="attendance_report.php" class="flex items-center px-3 py-2 rounded hover:bg-blue-800 md:hover:bg-blue-500/20 transition"><i class="fas fa-file-alt mr-2 w-5 text-center"></i>รายงาน</a></li>
                <li><a href="notify.php" class="flex items-center px-3 py-2 rounded hover:bg-blue-800 md:hover:bg-blue-500/20 transition"><i class="fas fa-bell mr-2 w-5 text-center"></i>แจ้งเตือน</a></li>
                <li class="mt-2 md:mt-0 border-t md:border-t-0 pt-2 md:pt-0 border-blue-600/50"><a href="../login.php?logout=1" class="flex items-center px-3 py-2 rounded hover:bg-red-600/20 transition text-red-200"><i class="fas fa-sign-out-alt mr-2 w-5 text-center"></i>ออกจากระบบ</a></li>
            </ul>
        </div>
    </nav>
    
    <div class="max-w-7xl mx-auto px-4 py-8">
        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6 flex items-center hover-scale">
                <i class="fas fa-check-circle mr-2"></i><?php echo $success; ?>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6 flex items-center hover-scale">
                <i class="fas fa-times-circle mr-2"></i><?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <!-- จัดการการเช็คชื่อ -->
        <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6 mb-8 hover-scale">
            <h2 class="text-xl sm:text-2xl font-bold text-gray-800 mb-4 sm:mb-6 flex items-center">
                <i class="fas fa-calendar-check mr-2 text-blue-600"></i>จัดการการเช็คชื่อ
            </h2>
            
            <!-- เลือกห้องและวันที่ -->
            <form method="GET" class="mb-6 bg-gray-50 p-3 sm:p-4 rounded-lg">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 sm:gap-4">
                    <div>
                        <label class="block text-gray-700 mb-1 sm:mb-2 font-medium">เลือกห้องเรียน</label>
                        <select name="classroom" class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                            <option value="">เลือกห้องเรียน</option>
                            <?php foreach ($classrooms as $classroom): ?>
                                <option value="<?php echo $classroom; ?>" <?php echo (isset($_GET['classroom']) && $_GET['classroom'] == $classroom ? 'selected' : ''); ?>>
                                    <?php echo $classroom; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-1 sm:mb-2 font-medium">เลือกวันที่</label>
                        <input type="date" name="date" value="<?php echo isset($_GET['date']) ? $_GET['date'] : date('Y-m-d'); ?>" 
                               class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 sm:px-6 py-2 rounded-lg flex items-center justify-center transition duration-200 hover:shadow-md w-full">
                            <i class="fas fa-search mr-2"></i>ดูรายชื่อ
                        </button>
                    </div>
                </div>
            </form>
            
            <!-- แสดงรายชื่อนักเรียนสำหรับเช็คชื่อ -->
            <?php if (!empty($attendance_data)): ?>
                <form method="POST">
                    <input type="hidden" name="save_attendance" value="1">
                    <input type="hidden" name="date" value="<?php echo $_GET['date']; ?>">
                    <input type="hidden" name="classroom" value="<?php echo $_GET['classroom']; ?>">
                    
                    <div class="overflow-x-auto rounded-lg border border-gray-200">
                        <table class="min-w-full bg-white">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="py-2 sm:py-3 px-2 sm:px-4 border-b text-left text-xs sm:text-sm font-medium text-gray-700">เลขที่</th>
                                    <th class="py-2 sm:py-3 px-2 sm:px-4 border-b text-left text-xs sm:text-sm font-medium text-gray-700">ชื่อ-นามสกุล</th>
                                    <th class="py-2 sm:py-3 px-2 sm:px-4 border-b text-center text-xs sm:text-sm font-medium text-gray-700">มา</th>
                                    <th class="py-2 sm:py-3 px-2 sm:px-4 border-b text-center text-xs sm:text-sm font-medium text-gray-700">สาย</th>
                                    <th class="py-2 sm:py-3 px-2 sm:px-4 border-b text-center text-xs sm:text-sm font-medium text-gray-700">ขาด</th>
                                    <th class="py-2 sm:py-3 px-2 sm:px-4 border-b text-center text-xs sm:text-sm font-medium text-gray-700">ลา</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($attendance_data as $student): ?>
                                    <tr class="table-row-hover">
                                        <td class="py-2 sm:py-3 px-2 sm:px-4 border-b text-xs sm:text-sm text-gray-800 font-medium"><?php echo $student['number']; ?></td>
                                        <td class="py-2 sm:py-3 px-2 sm:px-4 border-b text-xs sm:text-sm text-gray-700 whitespace-nowrap"><?php echo $student['name'] . ' ' . $student['surname']; ?></td>
                                        
                                        <!-- มา -->
                                        <td class="py-2 sm:py-3 px-2 sm:px-4 border-b text-center">
                                            <label class="attendance-toggle">
                                                <input type="radio" name="attendance[<?php echo $student['id']; ?>]" value="1" 
                                                       <?php echo ($student['status'] == 1) ? 'checked' : ''; ?> class="present">
                                                <span class="toggle-slider"></span>
                                            </label>
                                        </td>
                                        
                                        <!-- สาย -->
                                        <td class="py-2 sm:py-3 px-2 sm:px-4 border-b text-center">
                                            <label class="attendance-toggle">
                                                <input type="radio" name="attendance[<?php echo $student['id']; ?>]" value="2" 
                                                       <?php echo ($student['status'] == 2) ? 'checked' : ''; ?> class="late">
                                                <span class="toggle-slider"></span>
                                            </label>
                                        </td>
                                        
                                        <!-- ขาด -->
                                        <td class="py-2 sm:py-3 px-2 sm:px-4 border-b text-center">
                                            <label class="attendance-toggle">
                                                <input type="radio" name="attendance[<?php echo $student['id']; ?>]" value="3" 
                                                       <?php echo ($student['status'] == 3) ? 'checked' : ''; ?> class="absent">
                                                <span class="toggle-slider"></span>
                                            </label>
                                        </td>
                                        
                                        <!-- ลา -->
                                        <td class="py-2 sm:py-3 px-2 sm:px-4 border-b text-center">
                                            <label class="attendance-toggle">
                                                <input type="radio" name="attendance[<?php echo $student['id']; ?>]" value="4" 
                                                       <?php echo ($student['status'] == 4) ? 'checked' : ''; ?> class="leave">
                                                <span class="toggle-slider"></span>
                                            </label>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4 sm:mt-6 flex justify-end">
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 sm:px-6 py-2 rounded-lg flex items-center transition duration-200 hover:shadow-md text-sm sm:text-base">
                            <i class="fas fa-save mr-2"></i>บันทึกการเช็คชื่อ
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
        
        <!-- จัดการข้อมูลนักเรียน -->
        <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6 mb-8 hover-scale">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 sm:mb-6 gap-3">
                <h2 class="text-xl sm:text-2xl font-bold text-gray-800 flex items-center">
                    <i class="fas fa-users mr-2 text-blue-600"></i>จัดการข้อมูลนักเรียน
                </h2>
                <a href="add_student.php" class="bg-blue-600 hover:bg-blue-700 text-white px-3 sm:px-4 py-1 sm:py-2 rounded-lg flex items-center transition duration-200 hover:shadow-md text-sm sm:text-base w-full sm:w-auto justify-center">
                    <i class="fas fa-plus mr-2"></i>เพิ่มนักเรียน
                </a>
            </div>
            
            <!-- ข้อมูลสรุปและช่องค้นหานักเรียน -->
            <div class="mb-3 sm:mb-4 space-y-2">
                <div class="text-sm text-gray-600">
                    <i class="fas fa-users mr-1"></i>จำนวนนักเรียนทั้งหมด: <span class="font-semibold"><?php echo $total_students; ?></span> คน
                </div>
                <div class="relative">
                    <input type="text" id="studentSearch" placeholder="ค้นหานักเรียน (ชื่อ, รหัสนักเรียน, ห้องเรียน, ปีการศึกษา)" 
                           class="w-full px-4 py-2 pl-10 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                </div>
            </div>
            
            <div class="overflow-x-auto rounded-lg border border-gray-200">
                <table class="min-w-full bg-white">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="py-2 sm:py-3 px-2 sm:px-4 border-b text-left text-xs sm:text-sm font-medium text-gray-700">รหัสนักเรียน</th>
                            <th class="py-2 sm:py-3 px-2 sm:px-4 border-b text-left text-xs sm:text-sm font-medium text-gray-700">ชื่อ-นามสกุล</th>
                            <th class="py-2 sm:py-3 px-2 sm:px-4 border-b text-left text-xs sm:text-sm font-medium text-gray-700">ปีการศึกษา</th>
                            <th class="py-2 sm:py-3 px-2 sm:px-4 border-b text-left text-xs sm:text-sm font-medium text-gray-700">เลขที่</th>
                            <th class="py-2 sm:py-3 px-2 sm:px-4 border-b text-left text-xs sm:text-sm font-medium text-gray-700">ห้องเรียน</th>
                            <th class="py-2 sm:py-3 px-2 sm:px-4 border-b text-center text-xs sm:text-sm font-medium text-gray-700">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody id="studentTableBody">
                        <?php if (empty($students)): ?>
                            <tr>
                                <td colspan="6" class="py-8 px-4 text-center text-gray-500">
                                    <i class="fas fa-users mr-2"></i>ไม่มีข้อมูลนักเรียน
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($students as $student): ?>
                                <tr class="table-row-hover student-row" data-search="<?php echo strtolower($student['userId'] . ' ' . $student['title'] . $student['name'] . ' ' . $student['surname'] . ' ' . $student['year'] . ' ' . $student['classroom']); ?>">
                                    <td class="py-2 sm:py-3 px-2 sm:px-4 border-b text-xs sm:text-sm text-gray-800 font-medium"><?php echo $student['userId']; ?></td>
                                    <td class="py-2 sm:py-3 px-2 sm:px-4 border-b text-xs sm:text-sm text-gray-700 whitespace-nowrap"><?php echo $student['title'] . $student['name'] . ' ' . $student['surname']; ?></td>
                                    <td class="py-2 sm:py-3 px-2 sm:px-4 border-b text-xs sm:text-sm text-gray-700"><?php echo $student['year']; ?></td>
                                    <td class="py-2 sm:py-3 px-2 sm:px-4 border-b text-xs sm:text-sm text-gray-700"><?php echo $student['number']; ?></td>
                                    <td class="py-2 sm:py-3 px-2 sm:px-4 border-b text-xs sm:text-sm text-gray-700"><?php echo $student['classroom']; ?></td>
                                    <td class="py-2 sm:py-3 px-2 sm:px-4 border-b text-center">
                                        <div class="flex justify-center space-x-1 sm:space-x-2">
                                            <button onclick="editStudent(<?php echo htmlspecialchars(json_encode($student), ENT_QUOTES, 'UTF-8'); ?>)" 
                                                    class="bg-blue-500 hover:bg-blue-600 text-white px-2 sm:px-3 py-1 rounded flex items-center transition duration-200 hover:shadow text-xs sm:text-sm">
                                                <i class="fas fa-edit mr-1"></i><span class="hidden sm:inline">แก้ไข</span>
                                            </button>
                                            <a href="?delete_student=<?php echo $student['id']; ?>" 
                                               onclick="return confirm('คุณแน่ใจหรือไม่ที่จะลบข้อมูลนักเรียนนี้?')" 
                                               class="bg-red-500 hover:bg-red-600 text-white px-2 sm:px-3 py-1 rounded flex items-center transition duration-200 hover:shadow text-xs sm:text-sm">
                                                <i class="fas fa-trash mr-1"></i><span class="hidden sm:inline">ลบ</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination สำหรับนักเรียน -->
            <?php if ($total_student_pages > 1): ?>
                <div class="mt-4 flex justify-center">
                    <div class="flex items-center space-x-1 sm:space-x-2">
                        <!-- ปุ่มหน้าแรก -->
                        <?php if ($student_page > 1): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['student_page' => 1])); ?>" 
                               class="px-2 sm:px-3 py-1 sm:py-2 text-xs sm:text-sm bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition duration-200">
                                <i class="fas fa-angle-double-left"></i>
                            </a>
                        <?php endif; ?>
                        
                        <!-- ปุ่มหน้าก่อนหน้า -->
                        <?php if ($student_page > 1): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['student_page' => $student_page - 1])); ?>" 
                               class="px-2 sm:px-3 py-1 sm:py-2 text-xs sm:text-sm bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition duration-200">
                                <i class="fas fa-angle-left"></i>
                            </a>
                        <?php endif; ?>
                        
                        <!-- แสดงหมายเลขหน้า -->
                        <?php
                        $start_page = max(1, $student_page - 2);
                        $end_page = min($total_student_pages, $student_page + 2);
                        
                        for ($i = $start_page; $i <= $end_page; $i++):
                        ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['student_page' => $i])); ?>" 
                               class="px-2 sm:px-3 py-1 sm:py-2 text-xs sm:text-sm rounded-lg transition duration-200 <?php echo $i == $student_page ? 'bg-blue-600 text-white' : 'bg-gray-200 hover:bg-gray-300 text-gray-700'; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <!-- ปุ่มหน้าถัดไป -->
                        <?php if ($student_page < $total_student_pages): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['student_page' => $student_page + 1])); ?>" 
                               class="px-2 sm:px-3 py-1 sm:py-2 text-xs sm:text-sm bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition duration-200">
                                <i class="fas fa-angle-right"></i>
                            </a>
                        <?php endif; ?>
                        
                        <!-- ปุ่มหน้าสุดท้าย -->
                        <?php if ($student_page < $total_student_pages): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['student_page' => $total_student_pages])); ?>" 
                               class="px-2 sm:px-3 py-1 sm:py-2 text-xs sm:text-sm bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition duration-200">
                                <i class="fas fa-angle-double-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- แสดงข้อมูลการแบ่งหน้า -->
                <div class="mt-2 text-center text-xs sm:text-sm text-gray-600">
                    แสดง <?php echo ($student_offset + 1); ?> - <?php echo min($student_offset + $items_per_page, $total_students); ?> จาก <?php echo $total_students; ?> รายการ
                </div>
            <?php endif; ?>
        </div>
        
        <!-- จัดการข้อมูลอาจารย์ -->
        <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6 hover-scale">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 sm:mb-6 gap-3">
                <h2 class="text-xl sm:text-2xl font-bold text-gray-800 flex items-center">
                    <i class="fas fa-user-tie mr-2 text-blue-600"></i>จัดการข้อมูลอาจารย์
                </h2>
                <a href="add_teacher.php" class="bg-blue-600 hover:bg-blue-700 text-white px-3 sm:px-4 py-1 sm:py-2 rounded-lg flex items-center transition duration-200 hover:shadow-md text-sm sm:text-base w-full sm:w-auto justify-center">
                    <i class="fas fa-plus mr-2"></i>เพิ่มอาจารย์
                </a>
            </div>
            
            <!-- ข้อมูลสรุปและช่องค้นหาอาจารย์ -->
            <div class="mb-3 sm:mb-4 space-y-2">
                <div class="text-sm text-gray-600">
                    <i class="fas fa-user-tie mr-1"></i>จำนวนอาจารย์ทั้งหมด: <span class="font-semibold"><?php echo $total_teachers; ?></span> คน
                </div>
                <div class="relative">
                    <input type="text" id="teacherSearch" placeholder="ค้นหาอาจารย์ (ชื่อ, รหัสอาจารย์, ห้องเรียน, กลุ่มสาระ)" 
                           class="w-full px-4 py-2 pl-10 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                </div>
            </div>
            
            <div class="overflow-x-auto rounded-lg border border-gray-200">
                <table class="min-w-full bg-white">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="py-2 sm:py-3 px-2 sm:px-4 border-b text-left text-xs sm:text-sm font-medium text-gray-700">รหัสอาจารย์</th>
                            <th class="py-2 sm:py-3 px-2 sm:px-4 border-b text-left text-xs sm:text-sm font-medium text-gray-700">ชื่อ-นามสกุล</th>
                            <th class="py-2 sm:py-3 px-2 sm:px-4 border-b text-left text-xs sm:text-sm font-medium text-gray-700">ปีการศึกษา</th>
                            <th class="py-2 sm:py-3 px-2 sm:px-4 border-b text-left text-xs sm:text-sm font-medium text-gray-700">ห้องเรียน</th>
                            <th class="py-2 sm:py-3 px-2 sm:px-4 border-b text-left text-xs sm:text-sm font-medium text-gray-700">กลุ่มสาระ</th>
                            <th class="py-2 sm:py-3 px-2 sm:px-4 border-b text-center text-xs sm:text-sm font-medium text-gray-700">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody id="teacherTableBody">
                        <?php if (empty($teachers)): ?>
                            <tr>
                                <td colspan="6" class="py-8 px-4 text-center text-gray-500">
                                    <i class="fas fa-user-tie mr-2"></i>ไม่มีข้อมูลอาจารย์
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($teachers as $teacher): ?>
                                <tr class="table-row-hover teacher-row" data-search="<?php echo strtolower($teacher['userId'] . ' ' . $teacher['title'] . $teacher['name'] . ' ' . $teacher['surname'] . ' ' . $teacher['year'] . ' ' . $teacher['classroom'] . ' ' . $teacher['subject_group']); ?>">
                                    <td class="py-2 sm:py-3 px-2 sm:px-4 border-b text-xs sm:text-sm text-gray-800 font-medium"><?php echo $teacher['userId']; ?></td>
                                    <td class="py-2 sm:py-3 px-2 sm:px-4 border-b text-xs sm:text-sm text-gray-700 whitespace-nowrap"><?php echo $teacher['title'] . $teacher['name'] . ' ' . $teacher['surname']; ?></td>
                                    <td class="py-2 sm:py-3 px-2 sm:px-4 border-b text-xs sm:text-sm text-gray-700"><?php echo $teacher['year']; ?></td>
                                    <td class="py-2 sm:py-3 px-2 sm:px-4 border-b text-xs sm:text-sm text-gray-700"><?php echo $teacher['classroom']; ?></td>
                                    <td class="py-2 sm:py-3 px-2 sm:px-4 border-b text-xs sm:text-sm text-gray-700"><?php echo $teacher['subject_group']; ?></td>
                                    <td class="py-2 sm:py-3 px-2 sm:px-4 border-b text-center">
                                        <div class="flex justify-center space-x-1 sm:space-x-2">
                                            <button onclick="editTeacher(<?php echo htmlspecialchars(json_encode($teacher), ENT_QUOTES, 'UTF-8'); ?>)" 
                                                    class="bg-blue-500 hover:bg-blue-600 text-white px-2 sm:px-3 py-1 rounded flex items-center transition duration-200 hover:shadow text-xs sm:text-sm">
                                                <i class="fas fa-edit mr-1"></i><span class="hidden sm:inline">แก้ไข</span>
                                            </button>
                                            <a href="?delete_teacher=<?php echo $teacher['id']; ?>" 
                                               onclick="return confirm('คุณแน่ใจหรือไม่ที่จะลบข้อมูลอาจารย์นี้?')" 
                                               class="bg-red-500 hover:bg-red-600 text-white px-2 sm:px-3 py-1 rounded flex items-center transition duration-200 hover:shadow text-xs sm:text-sm">
                                                <i class="fas fa-trash mr-1"></i><span class="hidden sm:inline">ลบ</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination สำหรับอาจารย์ -->
            <?php if ($total_teacher_pages > 1): ?>
                <div class="mt-4 flex justify-center">
                    <div class="flex items-center space-x-1 sm:space-x-2">
                        <!-- ปุ่มหน้าแรก -->
                        <?php if ($teacher_page > 1): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['teacher_page' => 1])); ?>" 
                               class="px-2 sm:px-3 py-1 sm:py-2 text-xs sm:text-sm bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition duration-200">
                                <i class="fas fa-angle-double-left"></i>
                            </a>
                        <?php endif; ?>
                        
                        <!-- ปุ่มหน้าก่อนหน้า -->
                        <?php if ($teacher_page > 1): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['teacher_page' => $teacher_page - 1])); ?>" 
                               class="px-2 sm:px-3 py-1 sm:py-2 text-xs sm:text-sm bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition duration-200">
                                <i class="fas fa-angle-left"></i>
                            </a>
                        <?php endif; ?>
                        
                        <!-- แสดงหมายเลขหน้า -->
                        <?php
                        $start_page = max(1, $teacher_page - 2);
                        $end_page = min($total_teacher_pages, $teacher_page + 2);
                        
                        for ($i = $start_page; $i <= $end_page; $i++):
                        ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['teacher_page' => $i])); ?>" 
                               class="px-2 sm:px-3 py-1 sm:py-2 text-xs sm:text-sm rounded-lg transition duration-200 <?php echo $i == $teacher_page ? 'bg-blue-600 text-white' : 'bg-gray-200 hover:bg-gray-300 text-gray-700'; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                        
                        <!-- ปุ่มหน้าถัดไป -->
                        <?php if ($teacher_page < $total_teacher_pages): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['teacher_page' => $teacher_page + 1])); ?>" 
                               class="px-2 sm:px-3 py-1 sm:py-2 text-xs sm:text-sm bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition duration-200">
                                <i class="fas fa-angle-right"></i>
                            </a>
                        <?php endif; ?>
                        
                        <!-- ปุ่มหน้าสุดท้าย -->
                        <?php if ($teacher_page < $total_teacher_pages): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['teacher_page' => $total_teacher_pages])); ?>" 
                               class="px-2 sm:px-3 py-1 sm:py-2 text-xs sm:text-sm bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition duration-200">
                                <i class="fas fa-angle-double-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- แสดงข้อมูลการแบ่งหน้า -->
                <div class="mt-2 text-center text-xs sm:text-sm text-gray-600">
                    แสดง <?php echo ($teacher_offset + 1); ?> - <?php echo min($teacher_offset + $items_per_page, $total_teachers); ?> จาก <?php echo $total_teachers; ?> รายการ
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Modal แก้ไขข้อมูลนักเรียน -->
    <div id="studentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50 p-4" aria-hidden="true">
        <div class="bg-white rounded-lg p-6 max-w-md w-full shadow-xl transform transition-all duration-300 max-h-[90vh] overflow-y-auto">
            <h3 class="text-xl font-bold mb-4 flex items-center">
                <i class="fas fa-user-edit mr-2 text-blue-600"></i>แก้ไขข้อมูลนักเรียน
            </h3>
            <form method="POST" id="editStudentForm">
                <input type="hidden" name="edit_student" value="1">
                <input type="hidden" name="student_id" id="edit_student_id">
                
                <div class="space-y-3 sm:space-y-4">
                    <div>
                        <label class="block text-gray-700 mb-1 sm:mb-2 font-medium">ปีการศึกษา</label>
                        <input type="number" name="year" id="edit_student_year" 
                               class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-1 sm:mb-2 font-medium">คำนำหน้า</label>
                        <input type="text" name="title" id="edit_student_title" 
                               class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-1 sm:mb-2 font-medium">ชื่อ</label>
                        <input type="text" name="name" id="edit_student_name" 
                               class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-1 sm:mb-2 font-medium">นามสกุล</label>
                        <input type="text" name="surname" id="edit_student_surname" 
                               class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-1 sm:mb-2 font-medium">เลขที่</label>
                        <input type="number" name="number" id="edit_student_number" 
                               class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-1 sm:mb-2 font-medium">ห้องเรียน</label>
                        <input type="text" name="classroom" id="edit_student_classroom" 
                               class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                </div>
                
                <div class="mt-4 sm:mt-6 flex justify-end space-x-2 sm:space-x-3">
                    <button type="button" onclick="closeStudentModal()" 
                            class="bg-gray-500 hover:bg-gray-600 text-white px-3 sm:px-4 py-1 sm:py-2 rounded-lg flex items-center transition duration-200 hover:shadow text-sm sm:text-base">
                        <i class="fas fa-times mr-1"></i>ยกเลิก
                    </button>
                    <button type="submit" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-3 sm:px-4 py-1 sm:py-2 rounded-lg flex items-center transition duration-200 hover:shadow text-sm sm:text-base">
                        <i class="fas fa-save mr-1"></i>บันทึก
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal แก้ไขข้อมูลอาจารย์ -->
    <div id="teacherModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50 p-4" aria-hidden="true">
        <div class="bg-white rounded-lg p-6 max-w-md w-full shadow-xl transform transition-all duration-300 max-h-[90vh] overflow-y-auto">
            <h3 class="text-xl font-bold mb-4 flex items-center">
                <i class="fas fa-user-edit mr-2 text-blue-600"></i>แก้ไขข้อมูลอาจารย์
            </h3>
            <form method="POST" id="editTeacherForm">
                <input type="hidden" name="edit_teacher" value="1">
                <input type="hidden" name="teacher_id" id="edit_teacher_id">
                
                <div class="space-y-3 sm:space-y-4">
                    <div>
                        <label class="block text-gray-700 mb-1 sm:mb-2 font-medium">ปีการศึกษา</label>
                        <input type="number" name="year" id="edit_teacher_year" 
                               class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-1 sm:mb-2 font-medium">คำนำหน้า</label>
                        <input type="text" name="title" id="edit_teacher_title" 
                               class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-1 sm:mb-2 font-medium">ชื่อ</label>
                        <input type="text" name="name" id="edit_teacher_name" 
                               class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-1 sm:mb-2 font-medium">นามสกุล</label>
                        <input type="text" name="surname" id="edit_teacher_surname" 
                               class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-1 sm:mb-2 font-medium">ห้องเรียน</label>
                        <input type="text" name="classroom" id="edit_teacher_classroom" 
                               class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-1 sm:mb-2 font-medium">กลุ่มสาระ</label>
                        <input type="text" name="subject_group" id="edit_teacher_subject_group" 
                               class="w-full px-3 py-2 text-sm sm:text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                    </div>
                </div>
                
                <div class="mt-4 sm:mt-6 flex justify-end space-x-2 sm:space-x-3">
                    <button type="button" onclick="closeTeacherModal()" 
                            class="bg-gray-500 hover:bg-gray-600 text-white px-3 sm:px-4 py-1 sm:py-2 rounded-lg flex items-center transition duration-200 hover:shadow text-sm sm:text-base">
                        <i class="fas fa-times mr-1"></i>ยกเลิก
                    </button>
                    <button type="submit" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-3 sm:px-4 py-1 sm:py-2 rounded-lg flex items-center transition duration-200 hover:shadow text-sm sm:text-base">
                        <i class="fas fa-save mr-1"></i>บันทึก
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- manage.js -->
    <script src="js/manage.js"></script>
    <footer class="bg-gradient-to-r from-blue-700 to-blue-600 text-white py-4 text-center text-sm mt-10 w-full" style="position:relative; bottom:0; left:0;">
        <div class="container mx-auto px-4">
            <p>จัดทำโดย นายธีรภัทร เสนาคำ</p>
            <p>&copy; 2025 โรงเรียนสกลราชวิทยานุกูล. สงวนลิขสิทธิ์ทุกประการ.</p>
        </div>
    </footer>
</body>
</html>