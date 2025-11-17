<?php
session_start();

// เปิดโหมด Mock
define('MOCK_MODE', true);
require_once __DIR__ . '/config_mock.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // ใช้ MockData
    $user = MockData::getUserByUsername($username);
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['status'] = $user['status'];

        if ($user['status'] == 1) {
            header("Location: admin/Main.php");
            exit;
        } elseif ($user['status'] == 2) {
            header("Location: teacher/Main.php");
            exit;
        } elseif ($user['status'] == 3) {
            header("Location: student/Main.php");
            exit;
        } else {
            $error = "ไม่พบสิทธิ์ผู้ใช้งาน";
        }
    } else {
        $error = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - SKR Attendance Pro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Sarabun', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            200: '#bae6fd',
                            300: '#7dd3fc',
                            400: '#38bdf8',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            700: '#0369a1',
                            800: '#075985',
                            900: '#0c4a6e',
                        },
                        dark: {
                            800: '#1e293b',
                            900: '#0f172a'
                        }
                    },
                    boxShadow: {
                        '3d': '0 10px 30px -5px rgba(0, 0, 0, 0.3)',
                        'neumorphism': '8px 8px 16px #d1d9e6, -8px -8px 16px #ffffff'
                    }
                }
            }
        }
    </script>
    <style>
        :root {
            --primary-glow: conic-gradient(
                from 180deg at 50% 50%,
                #16abff33 0deg,
                #0885ff33 55deg,
                #54d6ff33 120deg,
                #0071ff33 160deg,
                transparent 360deg
            );
            --secondary-glow: radial-gradient(
                rgba(255, 255, 255, 1),
                rgba(255, 255, 255, 0)
            );
        }
        
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }
        
        .login-container {
            perspective: 1000px;
        }
        
        .login-card {
            transform-style: preserve-3d;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 25px 45px rgba(0, 0, 0, 0.1);
            transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        
        .login-card:hover {
            transform: translateY(-5px) rotateX(2deg);
            box-shadow: 0 30px 50px rgba(0, 0, 0, 0.15);
        }
        
        .input-field {
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8);
            box-shadow: inset 2px 2px 5px rgba(0, 0, 0, 0.05),
                        inset -2px -2px 5px rgba(255, 255, 255, 0.8);
        }
        
        .input-field:focus {
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.3),
                        inset 2px 2px 5px rgba(0, 0, 0, 0.05),
                        inset -2px -2px 5px rgba(255, 255, 255, 0.8);
        }
        
        .btn-login {
            background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            box-shadow: 0 4px 15px rgba(2, 132, 199, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .btn-login:hover {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(2, 132, 199, 0.4);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .btn-login::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                to bottom right,
                rgba(255, 255, 255, 0.3) 0%,
                rgba(255, 255, 255, 0) 60%
            );
            transform: rotate(30deg);
            transition: all 0.3s;
        }
        
        .btn-login:hover::after {
            left: 100%;
        }
        
        .floating {
            animation: floating 6s ease-in-out infinite;
        }
        
        @keyframes floating {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
            100% { transform: translateY(0px); }
        }
        
        .pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.8; }
        }
        
        .glow {
            filter: drop-shadow(0 0 8px rgba(255, 0, 0, 0.6));
        }
    </style>
</head>
<body class="font-sans antialiased text-gray-800 flex items-center justify-center p-4 md:p-8">
    <!-- Background elements -->
    <div class="fixed inset-0 overflow-hidden -z-10">
        <div class="absolute top-0 left-1/4 w-72 h-72 rounded-full bg-primary-100 opacity-20 blur-3xl animate-spin-slow"></div>
        <div class="absolute bottom-0 right-1/4 w-64 h-64 rounded-full bg-primary-200 opacity-20 blur-3xl animate-spin-slow-reverse"></div>
        <div class="absolute top-1/3 right-1/3 w-48 h-48 rounded-full bg-primary-300 opacity-15 blur-xl"></div>
    </div>
    
    <div class="login-container w-full max-w-md">
        <div class="login-card p-10 relative overflow-hidden">
            <!-- Decorative corner elements -->
            <div class="absolute top-0 right-0 w-24 h-24 border-t-4 border-r-4 border-primary-500 rounded-bl-3xl"></div>
            <div class="absolute bottom-0 left-0 w-24 h-24 border-b-4 border-l-4 border-primary-500 rounded-tr-3xl"></div>
            
            <!-- Logo section -->
            <div class="text-center mb-10 animate__animated animate__fadeIn">
                <div class="w-20 h-20 mx-auto mb-4 flex items-center justify-center floating glow">
                    <img src="../icon/SKR-logo-T.png" alt="SKR Logo" class="w-full h-full object-contain">
                </div>
                <h1 class="text-3xl font-bold text-gray-800 bg-gradient-to-r from-primary-600 to-primary-800 bg-clip-text text-transparent">SKR Attendance</h1>
                <p class="text-gray-600 mt-2">โรงเรียนสกลราชวิทยานุกูล</p>
            </div>

            <!-- Error message -->
            <?php if (!empty($error)): ?>
                <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-lg flex items-start animate__animated animate__shakeX">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="font-medium"><?php echo $error; ?></div>
                </div>
            <?php endif; ?>

            <!-- Login form -->
            <form action="" method="post" class="space-y-6 animate__animated animate__fadeInUp">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-2 ml-1">ชื่อผู้ใช้</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <input type="text" id="username" name="username" required 
                            class="input-field pl-10 w-full px-4 py-3 border-0 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 placeholder-gray-400">
                    </div>
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2 ml-1">รหัสผ่าน</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <input type="password" id="password" name="password" required 
                            class="input-field pl-10 w-full px-4 py-3 border-0 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 placeholder-gray-400">
                    </div>
                </div>
                
                <div class="pt-2">
                    <button type="submit" 
                        class="btn-login w-full text-white py-3 px-4 rounded-xl font-semibold transition-all duration-300">
                        <span class="relative z-10">เข้าสู่ระบบ</span>
                    </button>
                </div>
            </form>

            <!-- Footer links -->
            <div class="mt-8 text-center text-sm text-gray-600 animate__animated animate__fadeIn animate__delay-1s">
                <a href="index.php" class="inline-flex items-center text-primary-600 hover:text-primary-800 hover:underline font-medium transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    กลับหน้าแรก
                </a>
            </div>
        </div>
    </div>

    <!-- Animated background circles -->
    <style>
        @keyframes spin-slow {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        @keyframes spin-slow-reverse {
            0% { transform: rotate(-360deg); }
            100% { transform: rotate(0deg); }
        }
        
        .animate-spin-slow {
            animation: spin-slow 20s linear infinite;
        }
        
        .animate-spin-slow-reverse {
            animation: spin-slow-reverse 25s linear infinite;
        }
    </style>
</body>
</html>

