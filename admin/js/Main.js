const ctx = document.getElementById('attendanceChart').getContext('2d');
const attendanceChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['มา', 'ขาด', 'ลา', 'สาย'],
        datasets: [{
            data: [stats_ma, stats_khad, stats_la, stats_sai],
            backgroundColor: [
                'rgba(74, 222, 128, 0.8)',
                'rgba(248, 113, 113, 0.8)',
                'rgba(250, 204, 21, 0.8)',
                'rgba(251, 146, 60, 0.8)'
            ],
            borderColor: [
                'rgba(22, 163, 74, 1)',
                'rgba(220, 38, 38, 1)',
                'rgba(202, 138, 4, 1)',
                'rgba(234, 88, 12, 1)'
            ],
            borderWidth: 2,
            hoverOffset: 15,
            spacing: 5,
            borderRadius: 10
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '65%',
        radius: '90%',
        plugins: {
            legend: {
                position: 'right',
                labels: {
                    font: {
                        size: 14,
                        family: "'Sarabun', sans-serif"
                    },
                    padding: 20,
                    usePointStyle: true,
                    pointStyle: 'circle'
                }
            },
            tooltip: {
                backgroundColor: 'rgba(0,0,0,0.8)',
                titleFont: {
                    size: 16,
                    family: "'Sarabun', sans-serif",
                    weight: 'bold'
                },
                bodyFont: {
                    size: 14,
                    family: "'Sarabun', sans-serif"
                },
                padding: 12,
                cornerRadius: 12,
                usePointStyle: true,
                callbacks: {
                    label: function(context) {
                        const label = context.label || '';
                        const value = context.raw;
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = Math.round((value / total) * 100);
                        return `${label}: ${value} คน (${percentage}%)`;
                    }
                }
            },
            title: {
                display: true,
                text: 'สถิติการมาเรียนของนักเรียน',
                font: {
                    size: 18,
                    family: "'Sarabun', sans-serif",
                    weight: 'bold'
                },
                padding: {
                    top: 10,
                    bottom: 20
                }
            }
        },
        animation: {
            animateScale: true,
            animateRotate: true
        }
    }
});