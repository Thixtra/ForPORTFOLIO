<?php
require_once '../config.php';
require_once 'logic/add_student.logic.php';
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มข้อมูลนักเรียน - SKR Attendance</title>
    <!-- Load Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography,aspect-ratio"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
        }

        .student-row {
            transition: all 0.3s ease;
        }

        .student-row:hover {
            background-color: #f8fafc;
        }

        /* Mobile responsive styles */
        @media (max-width: 768px) {
            .student-row td {
                display: block;
                width: 100%;
                padding: 0.75rem 0.5rem;
                border-bottom: 1px solid #e5e7eb;
            }
            
            .student-row td:before {
                content: attr(data-label);
                font-weight: bold;
                display: inline-block;
                width: 120px;
                color: #4b5563;
            }
            
            .student-row td:last-child {
                text-align: right;
                border-bottom: none;
            }
            
            table thead {
                display: none;
            }
            
            .mobile-flex-col {
                flex-direction: column;
            }
        }

        /* Accessibility improvements */
        input:not([type="file"]), select {
            min-height: 44px;
            font-size: 16px;
        }
        
        input:focus, button:focus, select:focus {
            outline: 2px solid #3b82f6;
            outline-offset: 2px;
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
                <button id="mobile-menu-button" class="md:hidden focus:outline-none" aria-label="Toggle menu" aria-expanded="false">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>
            <ul id="mobile-menu" class="hidden md:flex space-x-1 md:space-x-2 lg:space-x-4 text-sm md:text-base mt-4 md:mt-0 flex-col md:flex-row w-full md:w-auto bg-blue-700 md:bg-transparent p-2 md:p-0 rounded-lg">
                <li><a href="Main.php" class="flex items-center px-3 py-2 rounded hover:bg-blue-800 md:hover:bg-blue-500/20 transition"><i class="fas fa-home mr-2 w-5 text-center"></i>หน้าหลัก</a></li>
                <li><a href="add_student.php" class="flex items-center px-3 py-2 rounded bg-blue-800 md:bg-blue-500/20 font-bold"><i class="fas fa-user-plus mr-2 w-5 text-center"></i>เพิ่มนักเรียน</a></li>
                <li><a href="add_teacher.php" class="flex items-center px-3 py-2 rounded hover:bg-blue-800 md:hover:bg-blue-500/20 transition"><i class="fas fa-chalkboard-user mr-2 w-5 text-center"></i>เพิ่มอาจารย์</a></li>
                <li><a href="manage.php" class="flex items-center px-3 py-2 rounded hover:bg-blue-800 md:hover:bg-blue-500/20 transition"><i class="fas fa-tasks mr-2 w-5 text-center"></i>จัดการข้อมูล</a></li>
                <li><a href="attendance_report.php" class="flex items-center px-3 py-2 rounded hover:bg-blue-800 md:hover:bg-blue-500/20 transition"><i class="fas fa-file-alt mr-2 w-5 text-center"></i>รายงาน</a></li>
                <li><a href="notify.php" class="flex items-center px-3 py-2 rounded hover:bg-blue-800 md:hover:bg-blue-500/20 transition"><i class="fas fa-bell mr-2 w-5 text-center"></i>แจ้งเตือน</a></li>
                <li class="mt-2 md:mt-0 border-t md:border-t-0 pt-2 md:pt-0 border-blue-600/50"><a href="../login.php?logout=1" class="flex items-center px-3 py-2 rounded hover:bg-red-600/20 transition text-red-200"><i class="fas fa-sign-out-alt mr-2 w-5 text-center"></i>ออกจากระบบ</a></li>
            </ul>
        </div>
    </nav>

    <div class="max-w-6xl mx-auto px-4 py-8">
        <!-- Excel Upload Section -->
        <div class="bg-white rounded-xl shadow-lg p-8 mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                <i class="fas fa-file-excel mr-3 text-green-600 bg-green-100 p-3 rounded-full"></i>
                <span>อัปโหลดข้อมูลนักเรียนด้วย Excel</span>
            </h2>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-blue-800 mb-3 flex items-center">
                    <i class="fas fa-info-circle mr-2"></i>
                    คำแนะนำการใช้งาน
                </h3>
                <ul class="text-blue-700 space-y-2 text-sm">
                    <li class="flex items-start">
                        <i class="fas fa-check-circle mr-2 mt-1 text-green-500"></i>
                        <span>รองรับไฟล์ .xlsx, .xls และ .csv</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle mr-2 mt-1 text-green-500"></i>
                        <span>ขนาดไฟล์ไม่เกิน 5MB</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle mr-2 mt-1 text-green-500"></i>
                        <span>ต้องมีคอลัมน์ตามลำดับ: รหัสนักเรียน, คำนำหน้า, ชื่อ, นามสกุล, ปีการศึกษา, เลขที่, ห้องเรียน, เลขบัตรประชาชน</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle mr-2 mt-1 text-green-500"></i>
                        <span>แถวแรกจะเป็น header (จะถูกข้าม)</span>
                    </li>
                </ul>
            </div>

            <form action="#" method="post" enctype="multipart/form-data" id="excel-upload-form" class="space-y-6">
                <input type="hidden" name="excel_upload" value="1">
                
                <div class="flex flex-col md:flex-row gap-4 items-center">
                    <div class="flex-1">
                        <label for="excel_file" class="block text-sm font-medium text-gray-700 mb-2">
                            เลือกไฟล์ Excel
                        </label>
                        <input type="file" id="excel_file" name="excel_file" accept=".xlsx,.xls,.csv" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center justify-center shadow-md hover:shadow-lg">
                            <i class="fas fa-upload mr-2"></i>
                            อัปโหลด
                        </button>
                        <a href="download_template.php" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg font-medium transition-colors duration-200 flex items-center justify-center shadow-md hover:shadow-lg">
                            <i class="fas fa-download mr-2"></i>
                            ดาวน์โหลด Template
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Multi Student Addition Section -->
        <div class="bg-white rounded-xl shadow-lg p-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                <i class="fas fa-users mr-3 text-blue-600 bg-blue-100 p-3 rounded-full"></i>
                <span>เพิ่มข้อมูลนักเรียนแบบรายบุคคล</span>
            </h2>

            <?php if (!empty($success)): ?>
                <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        <div>
                            <h4 class="font-bold text-green-800">สำเร็จ!</h4>
                            <?php foreach ($success as $msg): ?>
                                <p class="text-green-700"><?= htmlspecialchars($msg) ?></p>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                        <div>
                            <h4 class="font-bold text-red-800">เกิดข้อผิดพลาด!</h4>
                            <?php foreach ($error as $msg): ?>
                                <p class="text-red-700"><?= htmlspecialchars($msg) ?></p>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($excel_success)): ?>
                <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                        <div>
                            <h4 class="font-bold text-green-800">อัปโหลด Excel สำเร็จ!</h4>
                            <?php foreach ($excel_success as $msg): ?>
                                <p class="text-green-700"><?= htmlspecialchars($msg) ?></p>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($excel_error)): ?>
                <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                        <div>
                            <h4 class="font-bold text-red-800">เกิดข้อผิดพลาดในการอัปโหลด Excel!</h4>
                            <?php foreach ($excel_error as $msg): ?>
                                <p class="text-red-700"><?= htmlspecialchars($msg) ?></p>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <form action="#" method="post" id="multi-student-form" class="space-y-6">
                <input type="hidden" name="multi_student" value="1">

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">รหัสนักเรียน</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">คำนำหน้า</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ชื่อ</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">นามสกุล</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ปีการศึกษา</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">เลขที่</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ห้องเรียน</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">เลขบัตรประชาชน</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">การดำเนินการ</th>
                            </tr>
                        </thead>
                        <tbody id="student-rows" class="bg-white divide-y divide-gray-200">
                            <tr class="student-row hover:bg-gray-50 transition-colors duration-150">
                                <td class="px-4 py-3 whitespace-nowrap" data-label="รหัสนักเรียน">
                                    <div class="relative">
                                        <input type="text" name="student_id[]" required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 peer"
                                            placeholder=" " aria-label="รหัสนักเรียน">
                                        <label class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 pointer-events-none transition-all duration-200 
                                             peer-focus:text-xs peer-focus:-top-2 peer-focus:bg-white peer-focus:px-1 peer-focus:text-blue-600
                                             peer-placeholder-shown:text-base peer-placeholder-shown:top-1/2 peer-placeholder-shown:-translate-y-1/2">
                                            เช่น 64010001
                                        </label>
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap" data-label="คำนำหน้า">
                                    <div class="relative">
                                        <input type="text" name="student_title[]" required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 peer"
                                            placeholder=" " aria-label="คำนำหน้า">
                                        <label class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 pointer-events-none transition-all duration-200 
                                             peer-focus:text-xs peer-focus:-top-2 peer-focus:bg-white peer-focus:px-1 peer-focus:text-blue-600
                                             peer-placeholder-shown:text-base peer-placeholder-shown:top-1/2 peer-placeholder-shown:-translate-y-1/2">
                                            เช่น นาย/นางสาว
                                        </label>
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap" data-label="ชื่อ">
                                    <div class="relative">
                                        <input type="text" name="student_name[]" required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 peer"
                                            placeholder=" " aria-label="ชื่อ">
                                        <label class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 pointer-events-none transition-all duration-200 
                                             peer-focus:text-xs peer-focus:-top-2 peer-focus:bg-white peer-focus:px-1 peer-focus:text-blue-600
                                             peer-placeholder-shown:text-base peer-placeholder-shown:top-1/2 peer-placeholder-shown:-translate-y-1/2">
                                            ชื่อจริง
                                        </label>
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap" data-label="นามสกุล">
                                    <div class="relative">
                                        <input type="text" name="student_surname[]" required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 peer"
                                            placeholder=" " aria-label="นามสกุล">
                                        <label class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 pointer-events-none transition-all duration-200 
                                             peer-focus:text-xs peer-focus:-top-2 peer-focus:bg-white peer-focus:px-1 peer-focus:text-blue-600
                                             peer-placeholder-shown:text-base peer-placeholder-shown:top-1/2 peer-placeholder-shown:-translate-y-1/2">
                                            นามสกุล
                                        </label>
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap" data-label="ปีการศึกษา">
                                    <div class="relative">
                                        <input type="number" name="student_year[]" required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 peer"
                                            placeholder=" " aria-label="ปีการศึกษา">
                                        <label class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 pointer-events-none transition-all duration-200 
                                             peer-focus:text-xs peer-focus:-top-2 peer-focus:bg-white peer-focus:px-1 peer-focus:text-blue-600
                                             peer-placeholder-shown:text-base peer-placeholder-shown:top-1/2 peer-placeholder-shown:-translate-y-1/2">
                                            เช่น 2564
                                        </label>
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap" data-label="เลขที่">
                                    <div class="relative">
                                        <input type="number" name="student_number[]" required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 peer"
                                            placeholder=" " aria-label="เลขที่">
                                        <label class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 pointer-events-none transition-all duration-200 
                                             peer-focus:text-xs peer-focus:-top-2 peer-focus:bg-white peer-focus:px-1 peer-focus:text-blue-600
                                             peer-placeholder-shown:text-base peer-placeholder-shown:top-1/2 peer-placeholder-shown:-translate-y-1/2">
                                            เช่น 1
                                        </label>
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap" data-label="ห้องเรียน">
                                    <div class="relative">
                                        <input type="text" name="student_classroom[]" required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 peer"
                                            placeholder=" " aria-label="ห้องเรียน">
                                        <label class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 pointer-events-none transition-all duration-200 
                                             peer-focus:text-xs peer-focus:-top-2 peer-focus:bg-white peer-focus:px-1 peer-focus:text-blue-600
                                             peer-placeholder-shown:text-base peer-placeholder-shown:top-1/2 peer-placeholder-shown:-translate-y-1/2">
                                            เช่น ม.4/1
                                        </label>
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap" data-label="เลขบัตรประชาชน">
                                    <div class="relative">
                                        <input type="text" name="student_citizen_id[]" required
                                            pattern="[0-9]{13}" maxlength="13" minlength="13"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 peer"
                                            placeholder=" " aria-label="เลขบัตรประชาชน">
                                        <label class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 pointer-events-none transition-all duration-200 
                                             peer-focus:text-xs peer-focus:-top-2 peer-focus:bg-white peer-focus:px-1 peer-focus:text-blue-600
                                             peer-placeholder-shown:text-base peer-placeholder-shown:top-1/2 peer-placeholder-shown:-translate-y-1/2">
                                            13 หลัก
                                        </label>
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center" data-label="การดำเนินการ">
                                    <button type="button" class="remove-row text-red-500 hover:text-red-700 transition-colors" aria-label="ลบแถวนี้">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="flex flex-wrap gap-4 mt-6 mobile-flex-col md:flex-row">
                    <button type="button" id="add-row" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200 flex items-center justify-center shadow-md hover:shadow-lg">
                        <i class="fas fa-plus-circle mr-2"></i>
                        เพิ่มแถว
                    </button>
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-lg font-medium transition-colors duration-200 flex items-center justify-center shadow-md hover:shadow-lg">
                        <i class="fas fa-save mr-2"></i>
                        บันทึกทั้งหมด
                    </button>
                    <button type="button" id="clear-all" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-3 rounded-lg font-medium transition-colors duration-200 flex items-center justify-center shadow-md hover:shadow-lg">
                        <i class="fas fa-broom mr-2"></i>
                        ล้างทั้งหมด
                    </button>
                </div>
            </form>
        </div>
    </div>

    <footer class="bg-gradient-to-r from-blue-700 to-blue-600 text-white py-4 text-center text-sm mt-10 w-full" style="position:relative; bottom:0; left:0;">
        <div class="container mx-auto px-4">
            <p>จัดทำโดย นายธีรภัทร เสนาคำ</p>
            <p>&copy; 2025 โรงเรียนสกลราชวิทยานุกูล. สงวนลิขสิทธิ์ทุกประการ.</p>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="js/add_student.js"></script>
    <script src="js/excel_upload.js"></script>
</body>
</html>