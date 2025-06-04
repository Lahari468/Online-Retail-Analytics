<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'config.php';
require_once 'functions.php';

checkAuth();

$pageTitle = "Reports";
require_once 'header.php';

// Fetch summary data
$summary = $pdo->query("
    SELECT 
        COUNT(DISTINCT o.order_id) as total_orders,
        COUNT(DISTINCT o.customer_id) as total_customers,
        SUM(oi.quantity * oi.unit_price) as total_revenue,
        AVG(oi.quantity * oi.unit_price) as avg_order_value
    FROM orders o
    JOIN order_items oi ON o.order_id = oi.order_id
")->fetch();

// Get monthly sales data
$monthlySales = $pdo->query("
    SELECT 
        DATE_FORMAT(o.order_date, '%Y-%m') as month,
        SUM(oi.quantity * oi.unit_price) as revenue,
        COUNT(DISTINCT o.order_id) as orders
    FROM orders o
    JOIN order_items oi ON o.order_id = oi.order_id
    GROUP BY month
    ORDER BY month DESC
    LIMIT 12
")->fetchAll();
?>

<style>
:root {
    --primary: #4361ee;
    --primary-light: rgba(67, 97, 238, 0.1);
    --text: #2b2d42;
    --text-light: #8d99ae;
    --bg: #f8f9fa;
    --card-bg: #ffffff;
    --border: #e9ecef;
    --positive: #4cc9a0;
    --negative: #f72585;
}

.dashboard-container {
    display: flex;
    min-height: 100vh;
    background-color: var(--bg);
}

.main-content {
    flex: 1;
    padding: 2rem;
    overflow-y: auto;
}

.content-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid var(--border);
}

.content-header h1 {
    font-size: 1.8rem;
    color: var(--text);
    margin: 0;
}

.date-range {
    display: flex;
    align-items: center;
    gap: 0.8rem;
}

.date-range input {
    padding: 0.6rem;
    border: 1px solid var(--border);
    border-radius: 6px;
    font-size: 0.9rem;
}

.date-range span {
    color: var(--text-light);
    font-size: 0.9rem;
}

.btn-primary {
    background-color: var(--primary);
    color: white;
    border: none;
    padding: 0.6rem 1.2rem;
    border-radius: 6px;
    font-size: 0.9rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: background-color 0.2s ease;
}

.btn-primary:hover {
    background-color: #3a56d4;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background-color: var(--card-bg);
    border-radius: 10px;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.stat-card .label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    color: var(--text-light);
    margin-bottom: 0.5rem;
}

.stat-card .value {
    font-size: 1.8rem;
    font-weight: 600;
    color: var(--text);
}

.chart-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.5rem;
    margin-bottom: 2rem;
}

@media (min-width: 992px) {
    .chart-grid {
        grid-template-columns: 1fr 1fr;
    }
}

.chart-card {
    background-color: var(--card-bg);
    border-radius: 10px;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.chart-card h3 {
    margin-top: 0;
    margin-bottom: 1.5rem;
    font-size: 1.2rem;
    color: var(--text);
}

.chart-wrapper {
    position: relative;
    height: 300px;
    width: 100%;
}

.table-container {
    background-color: var(--card-bg);
    border-radius: 10px;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    margin-bottom: 2rem;
}

.table-container h3 {
    margin-top: 0;
    margin-bottom: 1.5rem;
    font-size: 1.2rem;
    color: var(--text);
}

#reports-table {
    width: 100%;
    border-collapse: collapse;
}

#reports-table thead {
    background-color: var(--primary-light);
}

#reports-table th {
    padding: 1rem;
    text-align: left;
    color: var(--text);
    font-weight: 600;
    border-bottom: 1px solid var(--border);
}

#reports-table td {
    padding: 1rem;
    border-bottom: 1px solid var(--border);
    color: var(--text);
}

#reports-table tr:last-child td {
    border-bottom: none;
}

#reports-table tr:hover {
    background-color: var(--primary-light);
}

@media (max-width: 768px) {
    .content-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .date-range {
        width: 100%;
        flex-wrap: wrap;
    }
    
    .date-range input {
        flex: 1;
        min-width: 120px;
    }
    
    #reports-table {
        display: block;
        overflow-x: auto;
    }
}
</style>

<div class="dashboard-container">
    <?php include 'sidebar.php'; ?>
    
    <main class="main-content">
        <div class="content-header">
            <h1>Reports & Analytics</h1>
            <div class="date-range">
                <input type="date" id="date-from" value="<?= date('Y-m-d', strtotime('-30 days')) ?>">
                <span>to</span>
                <input type="date" id="date-to" value="<?= date('Y-m-d') ?>">
                <button class="btn-primary" onclick="generateReport()">
                    <i class="fas fa-sync"></i> Update
                </button>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="label">
                    <i class="fas fa-shopping-cart"></i> Total Orders
                </div>
                <div class="value"><?= number_format($summary['total_orders']) ?></div>
            </div>
            <div class="stat-card">
                <div class="label">
                    <i class="fas fa-users"></i> Total Customers
                </div>
                <div class="value"><?= number_format($summary['total_customers']) ?></div>
            </div>
            <div class="stat-card">
                <div class="label">
                    <i class="fas fa-dollar-sign"></i> Total Revenue
                </div>
                <div class="value">$<?= number_format($summary['total_revenue'], 2) ?></div>
            </div>
            <div class="stat-card">
                <div class="label">
                    <i class="fas fa-chart-line"></i> Avg Order Value
                </div>
                <div class="value">$<?= number_format($summary['avg_order_value'], 2) ?></div>
            </div>
        </div>

        <div class="chart-grid">
            <div class="chart-card">
                <h3>Revenue Trend</h3>
                <div class="chart-wrapper">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
            <div class="chart-card">
                <h3>Orders Overview</h3>
                <div class="chart-wrapper">
                    <canvas id="ordersChart"></canvas>
                </div>
            </div>
        </div>

        <div class="table-container">
            <h3>Monthly Performance</h3>
            <table id="reports-table">
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Revenue</th>
                        <th>Orders</th>
                        <th>Avg Order Value</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($monthlySales as $month): ?>
                    <tr>
                        <td><?= date('F Y', strtotime($month['month'] . '-01')) ?></td>
                        <td>$<?= number_format($month['revenue'], 2) ?></td>
                        <td><?= number_format($month['orders']) ?></td>
                        <td>$<?= number_format($month['revenue'] / $month['orders'], 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Revenue Chart
    const revenueChart = new Chart(document.getElementById('revenueChart'), {
        type: 'line',
        data: {
            labels: <?= json_encode(array_map(function($m) { 
                return date('M Y', strtotime($m['month'] . '-01')); 
            }, array_reverse($monthlySales))) ?>,
            datasets: [{
                label: 'Revenue',
                data: <?= json_encode(array_map(function($m) { 
                    return $m['revenue']; 
                }, array_reverse($monthlySales))) ?>,
                borderColor: '#4361ee',
                tension: 0.3,
                fill: true,
                backgroundColor: 'rgba(67, 97, 238, 0.1)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        borderDash: [2, 2]
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

    // Orders Chart
    const ordersChart = new Chart(document.getElementById('ordersChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_map(function($m) { 
                return date('M Y', strtotime($m['month'] . '-01')); 
            }, array_reverse($monthlySales))) ?>,
            datasets: [{
                label: 'Orders',
                data: <?= json_encode(array_map(function($m) { 
                    return $m['orders']; 
                }, array_reverse($monthlySales))) ?>,
                backgroundColor: '#4361ee',
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        borderDash: [2, 2]
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
});

function generateReport() {
    const dateFrom = document.getElementById('date-from').value;
    const dateTo = document.getElementById('date-to').value;
    // TODO: Implement report generation with date range
    console.log('Generating report for:', dateFrom, 'to', dateTo);
}
</script>

<?php require_once 'footer.php'; ?>