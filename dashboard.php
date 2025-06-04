<?php
require_once 'config.php';
require_once 'functions.php';
checkAuth();

$pageType = 'dashboard';
$pageTitle = "Dashboard";
require_once 'header.php';
// ...existing code...

// Get summary data
$summary = $pdo->query("
    SELECT 
        COUNT(DISTINCT o.order_id) as total_orders,
        COUNT(DISTINCT o.customer_id) as total_customers,
        COUNT(DISTINCT p.product_id) as total_products,
        SUM(oi.quantity * oi.unit_price) as total_sales
    FROM orders o
    JOIN order_items oi ON o.order_id = oi.order_id
    JOIN products p ON oi.product_id = p.product_id
")->fetch();

// Get monthly sales data with year-over-year comparison
$sales = $pdo->query("
    SELECT 
        DATE_FORMAT(order_date, '%Y-%m') AS month,
        SUM(oi.quantity * oi.unit_price) AS total,
        COUNT(DISTINCT o.order_id) as order_count,
        AVG(oi.quantity * oi.unit_price) as avg_order_value
    FROM orders o
    JOIN order_items oi ON o.order_id = oi.order_id
    WHERE order_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY month
    ORDER BY month DESC
")->fetchAll();

// Calculate growth percentages
$currentMonth = $sales[0]['total'] ?? 0;
$previousMonth = $sales[1]['total'] ?? 0;
$salesGrowth = $previousMonth > 0 ? (($currentMonth - $previousMonth) / $previousMonth) * 100 : 0;

$currentOrders = $sales[0]['order_count'] ?? 0;
$previousOrders = $sales[1]['order_count'] ?? 0;
$orderGrowth = $previousOrders > 0 ? (($currentOrders - $previousOrders) / $previousOrders) * 100 : 0;

// Get top selling products
$topProducts = $pdo->query("
    SELECT 
        p.product_name,
        SUM(oi.quantity) as qty_sold,
        SUM(oi.quantity * oi.unit_price) as revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.product_id
    GROUP BY p.product_id, p.product_name
    ORDER BY qty_sold DESC
    LIMIT 5
")->fetchAll();

require_once 'header.php';
?>

<div class="dashboard-container">
    <?php include 'sidebar.php'; ?>
    
    <main class="main-content">
        <div class="content-header">
            <h1>Dashboard Overview</h1>
            <div class="date-filter">
                <input type="month" id="date-filter" value="<?= date('Y-m') ?>" onchange="updateDashboard(this.value)">
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="label"><i class="fas fa-chart-line"></i> Total Sales</div>
                <div class="value">$<?= number_format($summary['total_sales'], 2) ?></div>
                <div class="change <?= $salesGrowth >= 0 ? 'positive' : 'negative' ?>">
                    <i class="fas fa-arrow-<?= $salesGrowth >= 0 ? 'up' : 'down' ?>"></i>
                    <?= number_format(abs($salesGrowth), 1) ?>% vs last month
                </div>
            </div>

            <div class="stat-card">
                <div class="label"><i class="fas fa-shopping-cart"></i> Orders</div>
                <div class="value"><?= number_format($summary['total_orders']) ?></div>
                <div class="change <?= $orderGrowth >= 0 ? 'positive' : 'negative' ?>">
                    <i class="fas fa-arrow-<?= $orderGrowth >= 0 ? 'up' : 'down' ?>"></i>
                    <?= number_format(abs($orderGrowth), 1) ?>% vs last month
                </div>
            </div>

            <div class="stat-card">
                <div class="label"><i class="fas fa-users"></i> Customers</div>
                <div class="value"><?= number_format($summary['total_customers']) ?></div>
                <div class="change positive">
                    <i class="fas fa-user-check"></i> Active Customers
                </div>
            </div>

            <div class="stat-card">
                <div class="label"><i class="fas fa-box"></i> Products</div>
                <div class="value"><?= number_format($summary['total_products']) ?></div>
                <div class="change">
                    <i class="fas fa-box-check"></i> In Stock
                </div>
            </div>
        </div>

        <div class="chart-grid">
            <div class="chart-card">
                <h3>Sales Analytics</h3>
                <div class="chart-wrapper">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>

            <div class="chart-card">
                <h3>Top Products</h3>
                <div class="chart-wrapper">
                    <canvas id="productsChart"></canvas>
                </div>
            </div>
        </div>

        <div class="analysis-grid">
            <div class="analysis-card">
                <h3>Monthly Performance</h3>
                <table class="analysis-table">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Orders</th>
                            <th>Sales</th>
                            <th>Avg Order Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($sales, 0, 6) as $month): ?>
                        <tr>
                            <td><?= date('F Y', strtotime($month['month'].'-01')) ?></td>
                            <td><?= number_format($month['order_count']) ?></td>
                            <td>$<?= number_format($month['total'], 2) ?></td>
                            <td>$<?= number_format($month['avg_order_value'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
 
<script>
// Store chart references globally
let salesChart, productsChart;

document.addEventListener('DOMContentLoaded', function() {
    // Initialize Sales Chart
    salesChart = new Chart(document.getElementById('salesChart'), {
        type: 'line',
        data: {
            labels: <?= json_encode(array_reverse(array_map(function($month) {
                return date('M Y', strtotime($month['month'] . '-01'));
            }, $sales))) ?>,
            datasets: [{
                label: 'Monthly Sales',
                data: <?= json_encode(array_reverse(array_column($sales, 'total'))) ?>,
                borderColor: '#4361ee',
                backgroundColor: 'rgba(67, 97, 238, 0.1)',
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Sales: $' + context.raw.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Initialize Products Chart
    productsChart = new Chart(document.getElementById('productsChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($topProducts, 'product_name')) ?>,
            datasets: [{
                label: 'Units Sold',
                data: <?= json_encode(array_column($topProducts, 'qty_sold')) ?>,
                backgroundColor: '#4361ee',
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top'
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

    // Start auto-update functionality
    startAutoUpdate();
});

// Auto-update functionality
let lastUpdate = new Date().toISOString();
let updateInterval;

function startAutoUpdate() {
    // Initial update
    autoUpdate();
    // Set interval for subsequent updates
    updateInterval = setInterval(autoUpdate, 30000); // Update every 30 seconds
}

async function autoUpdate() {
    try {
        const response = await fetch(`get_updates.php?type=dashboard&last_update=${lastUpdate}`);
        const data = await response.json();
        
        if (data.success && data.data) {
            updateDashboardData(data.data);
            lastUpdate = new Date().toISOString();
        }
    } catch (error) {
        console.error('Update error:', error);
    }
}

function updateDashboardData(data) {
    // Update summary cards with animation
    updateStatCard(1, data.total_sales, true); // Sales (with currency)
    updateStatCard(2, data.total_orders); // Orders
    updateStatCard(3, data.total_customers); // Customers
    updateStatCard(4, data.total_products); // Products

    // Update growth indicators
    updateGrowthIndicator(1, data.sales_growth);
    updateGrowthIndicator(2, data.orders_growth);

    // Update charts
    if (salesChart && data.monthly_sales) {
        salesChart.data.labels = data.monthly_sales.map(m => 
            new Date(m.month + '-01').toLocaleDateString('en-US', { month: 'short', year: 'numeric' })
        );
        salesChart.data.datasets[0].data = data.monthly_sales.map(m => m.total);
        salesChart.update('show');
    }

    if (productsChart && data.top_products) {
        productsChart.data.labels = data.top_products.map(p => p.product_name);
        productsChart.data.datasets[0].data = data.top_products.map(p => p.qty_sold);
        productsChart.update('show');
    }

    // Update monthly performance table
    if (data.monthly_performance) {
        updatePerformanceTable(data.monthly_performance);
    }
}

function updateStatCard(index, value, isCurrency = false) {
    const valueElement = document.querySelector(`.stat-card:nth-child(${index}) .value`);
    const currentValue = parseFloat(valueElement.textContent.replace(/[^0-9.-]+/g, ""));
    const newValue = parseFloat(value);

    // Animate the number change
    animateValue(valueElement, currentValue, newValue, 500, isCurrency);
}

function animateValue(element, start, end, duration, isCurrency) {
    const range = end - start;
    const startTime = performance.now();
    
    function updateNumber(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        const current = start + (range * progress);
        element.textContent = isCurrency ? 
            '$' + current.toLocaleString(undefined, {minimumFractionDigits: 2}) :
            Math.round(current).toLocaleString();
        
        if (progress < 1) {
            requestAnimationFrame(updateNumber);
        }
    }
    
    requestAnimationFrame(updateNumber);
}

function updateGrowthIndicator(index, growth) {
    const element = document.querySelector(`.stat-card:nth-child(${index}) .change`);
    const isPositive = growth >= 0;
    
    element.className = `change ${isPositive ? 'positive' : 'negative'}`;
    element.innerHTML = `
        <i class="fas fa-arrow-${isPositive ? 'up' : 'down'}"></i>
        ${Math.abs(growth).toFixed(1)}% vs last month
    `;
}

function updatePerformanceTable(data) {
    const tbody = document.querySelector('.analysis-table tbody');
    tbody.innerHTML = data.map(month => `
        <tr>
            <td>${new Date(month.month + '-01').toLocaleDateString('en-US', { month: 'long', year: 'numeric' })}</td>
            <td>${Number(month.orders).toLocaleString()}</td>
            <td>$${Number(month.sales).toLocaleString(undefined, {minimumFractionDigits: 2})}</td>
            <td>$${Number(month.avg_order).toLocaleString(undefined, {minimumFractionDigits: 2})}</td>
        </tr>
    `).join('');
}

// Date filter handler
function updateDashboard(date) {
    clearInterval(updateInterval); // Stop auto updates temporarily
    
    fetch(`get_updates.php?type=dashboard&date=${date}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateDashboardData(data.data);
                startAutoUpdate(); // Restart auto updates
            }
        })
        .catch(error => {
            console.error('Error:', error);
            startAutoUpdate(); // Restart auto updates even if there's an error
        });
}
</script>

<style>
    /* Dashboard Layout */
    .dashboard-container {
        display: grid;
        min-height: 100vh;
        background: #f8fafc;
    }

    .main-content {
        margin-left: 280px;
        padding: 2rem;
        transition: all 0.3s ease;
    }

    /* Header Styling */
    .content-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #e2e8f0;
    }

    .content-header h1 {
        font-size: 1.875rem;
        font-weight: 600;
        color: #1e293b;
    }

    .date-filter input {
        padding: 0.5rem 1rem;
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        transition: all 0.2s;
    }

    .date-filter input:focus {
        outline: none;
        border-color: #4361ee;
        box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
    }

    /* Stats Grid */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        padding: 1.5rem;
        border-radius: 1rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s ease;
    }

    .stat-card:hover {
        transform: translateY(-2px);
    }

    .stat-card .label {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        color: #64748b;
        font-size: 0.875rem;
        margin-bottom: 0.5rem;
    }

    .stat-card .value {
        font-size: 1.875rem;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 0.5rem;
    }

    .stat-card .change {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        padding: 0.25rem 0.75rem;
        border-radius: 1rem;
        width: fit-content;
    }

    .change.positive {
        background: #dcfce7;
        color: #15803d;
    }

    .change.negative {
        background: #fee2e2;
        color: #dc2626;
    }

    /* Charts Section */
    .chart-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .chart-card {
        background: white;
        padding: 1.5rem;
        border-radius: 1rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .chart-card h3 {
        color: #1e293b;
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 1.5rem;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid #f1f5f9;
    }

    .chart-wrapper {
        height: 300px;
        position: relative;
    }

    /* Analysis Section */
    .analysis-grid {
        margin-top: 2rem;
    }

    .analysis-card {
        background: white;
        border-radius: 1rem;
        padding: 1.5rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .analysis-card h3 {
        color: #1e293b;
        font-size: 1.25rem;
        font-weight: 600;
        margin-bottom: 1rem;
    }

    .analysis-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin-top: 1rem;
    }

    .analysis-table th,
    .analysis-table td {
        padding: 1rem;
        text-align: left;
        border-bottom: 1px solid #f1f5f9;
    }

    .analysis-table th {
        font-weight: 600;
        background: #f8fafc;
        color: #475569;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .analysis-table tr:last-child td {
        border-bottom: none;
    }

    .analysis-table tbody tr:hover {
        background: #f8fafc;
    }

    /* Responsive Design */
    @media (max-width: 1200px) {
        .chart-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .main-content {
            margin-left: 0;
            padding: 1rem;
        }

        .stats-grid {
            grid-template-columns: 1fr;
        }

        .content-header {
            flex-direction: column;
            gap: 1rem;
            align-items: flex-start;
        }
    }
</style>

<?php require_once 'footer.php'; ?>