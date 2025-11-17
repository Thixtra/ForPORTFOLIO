<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['status'] != 1) {
    header('Location: ../login.php');
    exit;
}

$success = null;
$error = null;

// จัดการการอ่านการแจ้งเตือน
if (isset($_GET['mark_read'])) {
    $notification_id = $_GET['mark_read'];
    $stmt = $conn->prepare("UPDATE notifications SET status = 'read' WHERE id = ?");
    $stmt->bind_param("i", $notification_id);
    if ($stmt->execute()) {
        $success = "อัปเดตสถานะการแจ้งเตือนสำเร็จ";
    } else {
        $error = "เกิดข้อผิดพลาดในการอัปเดตสถานะ";
    }
    $stmt->close();
}

// ลบการแจ้งเตือน
if (isset($_GET['delete'])) {
    $notification_id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM notifications WHERE id = ?");
    $stmt->bind_param("i", $notification_id);
    if ($stmt->execute()) {
        $success = "ลบการแจ้งเตือนสำเร็จ";
    } else {
        $error = "เกิดข้อผิดพลาดในการลบการแจ้งเตือน";
    }
    $stmt->close();
}

// ดึงข้อมูลการแจ้งเตือนทั้งหมด
$notifications = [];
$stmt = $conn->prepare("SELECT n.*, t.title as teacher_title, t.name as teacher_name, t.surname as teacher_surname, t.classroom 
                       FROM notifications n 
                       JOIN teacher t ON n.teacher_id = t.id 
                       ORDER BY n.created_at DESC");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}
$stmt->close();

// นับจำนวนการแจ้งเตือนที่ยังไม่ได้อ่าน
$unread_count = 0;
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE status = 'unread'");
$stmt->execute();
$result = $stmt->get_result();
$unread_count = $result->fetch_assoc()['count'];
$stmt->close();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แจ้งเตือนข้อมูล - SKR Attendance</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography,aspect-ratio"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Sarabun', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
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
                    <a href="notify.php" class="flex items-center px-3 py-2 rounded bg-blue-800 md:bg-blue-500/20 font-bold transition">
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
    
    <div class="container mx-auto mt-12 p-8 bg-white rounded-xl shadow-lg max-w-6xl">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-800">แจ้งเตือนข้อมูล</h2>
            <div class="flex items-center space-x-4">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                    <i class="fas fa-bell mr-1"></i>
                    <?php echo $unread_count; ?> รายการใหม่
                </span>
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

        <!-- System Notifications -->
        <div class="mb-8">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-cog mr-2 text-blue-600"></i>
                การแจ้งเตือนระบบ
            </h3>
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-4" role="alert">
                <p class="font-bold">แจ้งเตือน</p>
                <p>มีนักเรียนที่ขาดเรียนเกิน 3 ครั้ง กรุณาตรวจสอบ!</p>
            </div>
            <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4" role="alert">
                <p class="font-bold">ประกาศ</p>
                <p>ระบบจะปิดปรับปรุงในวันที่ 1 มิถุนายน 2567 เวลา 22:00 น.</p>
            </div>
        </div>

        <!-- Teacher Notifications -->
        <div>
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-chalkboard-teacher mr-2 text-green-600"></i>
                การแจ้งเตือนจากอาจารย์
            </h3>
            
            <?php if (empty($notifications)): ?>
                <div class="text-center py-8 text-gray-500">
                    <i class="fas fa-bell-slash text-4xl mb-4 text-gray-300"></i>
                    <p>ไม่มีการแจ้งเตือนจากอาจารย์</p>
                </div>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($notifications as $notification): ?>
                        <div class="border rounded-lg p-4 <?php echo $notification['status'] === 'unread' ? 'bg-blue-50 border-blue-200' : 'bg-white border-gray-200'; ?>">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3 mb-2">
                                        <div class="flex items-center space-x-2">
                                            <?php
                                            $type_icon = '';
                                            $type_color = '';
                                            switch ($notification['type']) {
                                                case 'info':
                                                    $type_icon = 'fas fa-info-circle';
                                                    $type_color = 'text-blue-600';
                                                    break;
                                                case 'warning':
                                                    $type_icon = 'fas fa-exclamation-triangle';
                                                    $type_color = 'text-yellow-600';
                                                    break;
                                                case 'error':
                                                    $type_icon = 'fas fa-times-circle';
                                                    $type_color = 'text-red-600';
                                                    break;
                                                case 'success':
                                                    $type_icon = 'fas fa-check-circle';
                                                    $type_color = 'text-green-600';
                                                    break;
                                            }
                                            ?>
                                            <i class="<?php echo $type_icon . ' ' . $type_color; ?>"></i>
                                            <h4 class="font-semibold text-gray-800"><?php echo htmlspecialchars($notification['title']); ?></h4>
                                            <?php if ($notification['status'] === 'unread'): ?>
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    ใหม่
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <p class="text-gray-700 mb-3"><?php echo nl2br(htmlspecialchars($notification['message'])); ?></p>
                                    
                                    <div class="flex items-center justify-between text-sm text-gray-500">
                                        <div class="flex items-center space-x-4">
                                            <span>
                                                <i class="fas fa-user mr-1"></i>
                                                <?php echo htmlspecialchars($notification['teacher_title'] . $notification['teacher_name'] . ' ' . $notification['teacher_surname']); ?>
                                            </span>
                                            <span>
                                                <i class="fas fa-chalkboard mr-1"></i>
                                                ห้อง <?php echo htmlspecialchars($notification['classroom']); ?>
                                            </span>
                                            <span>
                                                <i class="fas fa-clock mr-1"></i>
                                                <?php echo date('d/m/Y H:i', strtotime($notification['created_at'])); ?>
                                            </span>
                                        </div>
                                        
                                        <div class="flex items-center space-x-2">
                                            <?php if ($notification['status'] === 'unread'): ?>
                                                <a href="?mark_read=<?php echo $notification['id']; ?>" 
                                                   class="text-blue-600 hover:text-blue-800 transition duration-200"
                                                   title="ทำเครื่องหมายว่าอ่านแล้ว">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="?delete=<?php echo $notification['id']; ?>" 
                                               class="text-red-600 hover:text-red-800 transition duration-200"
                                               onclick="return confirm('คุณต้องการลบการแจ้งเตือนนี้หรือไม่?')"
                                               title="ลบการแจ้งเตือน">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <footer class="bg-gradient-to-r from-blue-700 to-blue-600 text-white py-4 text-center text-sm mt-10 w-full" style="position:relative; bottom:0; left:0;">
        <div class="container mx-auto px-4">
            <p>จัดทำโดย นายธีรภัทร เสนาคำ</p>
            <p>&copy; 2025 โรงเรียนสกลราชวิทยานุกูล. สงวนลิขสิทธิ์ทุกประการ.</p>
        </div>
    </footer>
</body>
</html>
<script>
    document.getElementById('mobile-menu-button').addEventListener('click', function() {
        const menu = document.getElementById('mobile-menu');
        menu.classList.toggle('hidden');
    });
</script> 