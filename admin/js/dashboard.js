// Biểu đồ đơn hàng theo tháng
const ordersCtx = document.getElementById('ordersChart').getContext('2d');
new Chart(ordersCtx, {
    type: 'line',
    data: {
        labels: chartData.map(item => item.month),
        datasets: [{
            label: 'Số đơn hàng',
            data: chartData.map(item => item.orders),
            borderColor: '#52a7e0',
            backgroundColor: 'rgba(82, 167, 224, 0.1)',
            tension: 0.3,
            fill: true,
            pointBackgroundColor: '#52a7e0',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 5,
            pointHoverRadius: 7
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
            },
            tooltip: {
                padding: 10,
                backgroundColor: 'rgba(0,0,0,0.8)',
                titleColor: '#fff',
                bodyColor: '#fff',
                bodySpacing: 5,
                borderColor: '#52a7e0',
                borderWidth: 1,
                callbacks: {
                    label: function (context) {
                        return `Số đơn hàng: ${context.parsed.y}`;
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: '#f0f0f0'
                },
                ticks: {
                    precision: 0
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        }
    }
});

// Biểu đồ doanh thu theo tháng
const revenueCtx = document.getElementById('revenueChart').getContext('2d');
new Chart(revenueCtx, {
    type: 'bar',
    data: {
        labels: chartData.map(item => item.month),
        datasets: [{
            label: 'Doanh thu (Triệu VNĐ)',
            data: chartData.map(item => item.revenue / 1000000),
            backgroundColor: '#2ecc71',
            borderRadius: 5,
            maxBarThickness: 50
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
            },
            tooltip: {
                padding: 10,
                backgroundColor: 'rgba(0,0,0,0.8)',
                titleColor: '#fff',
                bodyColor: '#fff',
                bodySpacing: 5,
                borderColor: '#2ecc71',
                borderWidth: 1,
                callbacks: {
                    label: function (context) {
                        return `Doanh thu: ${context.parsed.y.toFixed(1)} triệu VNĐ`;
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: {
                    color: '#f0f0f0'
                }
            },
            x: {
                grid: {
                    display: false
                }
            }
        }
    }
});