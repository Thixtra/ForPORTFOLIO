// Teacher JavaScript Functions

// Mobile menu toggle
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    
    if (mobileMenuButton && mobileMenu) {
        mobileMenuButton.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });
    }
});

// Modal functions
function openNotificationModal() {
    const modal = document.getElementById('notificationModal');
    if (modal) {
        modal.style.display = 'block';
    }
}

function closeNotificationModal() {
    const modal = document.getElementById('notificationModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('notificationModal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
}

// Attendance form functions
function selectAllStatus(status) {
    const statusSelects = document.querySelectorAll('select[name="status[]"]');
    statusSelects.forEach(select => {
        select.value = status;
    });
}

function quickSelectPresent() {
    selectAllStatus('1');
}

function quickSelectAbsent() {
    selectAllStatus('2');
}

function quickSelectLeave() {
    selectAllStatus('3');
}

function quickSelectLate() {
    selectAllStatus('4');
}

function resetAll() {
    selectAllStatus('0');
}

// Chart functions
function initializeWeeklyChart(weeklyStats) {
    if (!weeklyStats || weeklyStats.length === 0) return;
    
    const ctx = document.getElementById('weeklyChart');
    if (!ctx) return;
    
    const chart = new Chart(ctx.getContext('2d'), {
        type: 'line',
        data: {
            labels: weeklyStats.map(item => item.date),
            datasets: [{
                label: 'มา',
                data: weeklyStats.map(item => item.present),
                borderColor: 'rgb(34, 197, 94)',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                tension: 0.1
            }, {
                label: 'ขาด',
                data: weeklyStats.map(item => item.absent),
                borderColor: 'rgb(239, 68, 68)',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                tension: 0.1
            }, {
                label: 'ลา',
                data: weeklyStats.map(item => item.leave_status),
                borderColor: 'rgb(234, 179, 8)',
                backgroundColor: 'rgba(234, 179, 8, 0.1)',
                tension: 0.1
            }, {
                label: 'สาย',
                data: weeklyStats.map(item => item.late),
                borderColor: 'rgb(249, 115, 22)',
                backgroundColor: 'rgba(249, 115, 22, 0.1)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
}

// Print functions
function printReport() {
    const printContent = document.getElementById('printContent');
    if (printContent) {
        const originalContents = document.body.innerHTML;
        document.body.innerHTML = printContent.innerHTML;
        window.print();
        document.body.innerHTML = originalContents;
        location.reload();
    }
}

function downloadReport() {
    const printContent = document.getElementById('printContent');
    if (printContent) {
        html2canvas(printContent).then(canvas => {
            const link = document.createElement('a');
            link.download = 'attendance_report_' + new Date().toISOString().split('T')[0] + '.png';
            link.href = canvas.toDataURL();
            link.click();
        });
    }
}

// Utility functions
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('th-TH', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

function formatTime(timeString) {
    if (!timeString) return '-';
    const time = new Date(timeString);
    return time.toLocaleTimeString('th-TH', {
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Auto-save attendance data
let autoSaveTimer;
function setupAutoSave() {
    const form = document.getElementById('attendanceForm');
    if (!form) return;
    
    const inputs = form.querySelectorAll('select, input');
    inputs.forEach(input => {
        input.addEventListener('change', function() {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(() => {
                saveAttendanceData();
            }, 2000); // Auto-save after 2 seconds of inactivity
        });
    });
}

function saveAttendanceData() {
    const form = document.getElementById('attendanceForm');
    if (!form) return;
    
    const formData = new FormData(form);
    
    fetch('attendance.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        console.log('Auto-save completed');
    })
    .catch(error => {
        console.error('Auto-save failed:', error);
    });
}

// Initialize functions when page loads
document.addEventListener('DOMContentLoaded', function() {
    setupAutoSave();
    
    // Initialize charts if they exist
    if (typeof weeklyStats !== 'undefined') {
        initializeWeeklyChart(weeklyStats);
    }
}); 