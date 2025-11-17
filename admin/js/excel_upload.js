document.addEventListener('DOMContentLoaded', function() {
    const excelForm = document.getElementById('excel-upload-form');
    const fileInput = document.getElementById('excel_file');
    const uploadButton = excelForm.querySelector('button[type="submit"]');

    // ตรวจสอบไฟล์ที่เลือก
    fileInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            // ตรวจสอบขนาดไฟล์
            if (file.size > 5 * 1024 * 1024) {
                alert('ขนาดไฟล์ต้องไม่เกิน 5MB');
                this.value = '';
                return;
            }

            // ตรวจสอบนามสกุลไฟล์
            const allowedTypes = ['.xlsx', '.xls', '.csv'];
            const fileName = file.name.toLowerCase();
            const isValidType = allowedTypes.some(type => fileName.endsWith(type));
            
            if (!isValidType) {
                alert('ไฟล์ที่อัปโหลดต้องเป็น .xlsx, .xls หรือ .csv เท่านั้น');
                this.value = '';
                return;
            }

            // แสดงชื่อไฟล์
            console.log('ไฟล์ที่เลือก:', file.name);
        }
    });

    // จัดการการส่งฟอร์ม
    excelForm.addEventListener('submit', function(e) {
        const file = fileInput.files[0];
        
        if (!file) {
            e.preventDefault();
            alert('กรุณาเลือกไฟล์ Excel');
            return;
        }

        // แสดง loading
        uploadButton.disabled = true;
        uploadButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>กำลังอัปโหลด...';
        
        // ตรวจสอบขนาดไฟล์อีกครั้ง
        if (file.size > 5 * 1024 * 1024) {
            e.preventDefault();
            alert('ขนาดไฟล์ต้องไม่เกิน 5MB');
            uploadButton.disabled = false;
            uploadButton.innerHTML = '<i class="fas fa-upload mr-2"></i>อัปโหลด';
            return;
        }
    });

    // เพิ่ม drag and drop functionality
    const dropZone = excelForm;
    
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, highlight, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, unhighlight, false);
    });

    function highlight(e) {
        dropZone.classList.add('border-green-500', 'bg-green-50');
    }

    function unhighlight(e) {
        dropZone.classList.remove('border-green-500', 'bg-green-50');
    }

    dropZone.addEventListener('drop', handleDrop, false);

    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        
        if (files.length > 0) {
            fileInput.files = files;
            fileInput.dispatchEvent(new Event('change'));
        }
    }

    // เพิ่มการแสดงตัวอย่างข้อมูล CSV
    fileInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file && file.name.toLowerCase().endsWith('.csv')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const text = e.target.result;
                const lines = text.split('\n');
                if (lines.length > 1) {
                    console.log('ตัวอย่างข้อมูล CSV:');
                    console.log('Header:', lines[0]);
                    console.log('Row 1:', lines[1]);
                }
            };
            reader.readAsText(file, 'UTF-8');
        }
    });
});

// ฟังก์ชันสำหรับแสดง progress bar (ถ้าต้องการ)
function showProgress(percent) {
    // สร้าง progress bar ถ้าต้องการ
    console.log(`Progress: ${percent}%`);
}

// ฟังก์ชันสำหรับแสดงข้อความแจ้งเตือน
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 p-4 rounded-lg shadow-lg z-50 ${
        type === 'success' ? 'bg-green-500 text-white' :
        type === 'error' ? 'bg-red-500 text-white' :
        'bg-blue-500 text-white'
    }`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
} 