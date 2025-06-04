<?php
require_once 'config.php';
require_once 'functions.php';
checkAuth();

$pageType = 'customers';
$pageTitle = "Customers";
require_once 'header.php';


// Fetch customers data
$customers = $pdo->query("SELECT * FROM customers ORDER BY customer_id")->fetchAll();
$segments = $pdo->query("
    SELECT customer_segment, COUNT(*) AS count 
    FROM customers 
    GROUP BY customer_segment
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
    margin-left: 280px;
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
    font-weight: 600;
}

.btn-add {
    background-color: var(--primary);
    color: white;
    border: none;
    padding: 0.7rem 1.2rem;
    border-radius: 6px;
    font-size: 0.9rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s ease;
}

.btn-add:hover {
    background-color: #3a56d4;
    transform: translateY(-1px);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    background-color: var(--card-bg);
    border-radius: 10px;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    transition: transform 0.2s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
}

.stat-card h3 {
    font-size: 1rem;
    color: var(--text-light);
    margin-top: 0;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.stat-card .value {
    font-size: 2rem;
    font-weight: 600;
    color: var(--text);
}

.chart-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
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
    font-weight: 600;
}

.chart-wrapper {
    position: relative;
    height: 300px;
    width: 100%;
}

.filters {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
}

.filters input,
.filters select {
    padding: 0.7rem 1rem;
    border: 1px solid var(--border);
    border-radius: 6px;
    font-size: 0.9rem;
    min-width: 200px;
    color: var(--text);
    transition: all 0.2s ease;
}

.filters input:focus,
.filters select:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px var(--primary-light);
}

.table-container {
    background-color: var(--card-bg);
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    overflow: hidden;
    margin-bottom: 2rem;
}

#customers-table {
    width: 100%;
    border-collapse: collapse;
}

#customers-table thead {
    background-color: var(--primary-light);
}

#customers-table th {
    padding: 1rem;
    text-align: left;
    color: var(--text);
    font-weight: 600;
    border-bottom: 1px solid var(--border);
}

#customers-table td {
    padding: 1rem;
    border-bottom: 1px solid var(--border);
    color: var(--text);
}

#customers-table tr:last-child td {
    border-bottom: none;
}

#customers-table tr:hover {
    background-color: var(--primary-light);
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
}

.btn-edit, 
.btn-delete {
    border: none;
    background: none;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 4px;
    transition: all 0.2s ease;
}

.btn-edit {
    color: var(--primary);
}

.btn-delete {
    color: var(--negative);
}

.btn-edit:hover {
    background-color: var(--primary-light);
}

.btn-delete:hover {
    background-color: rgba(247, 37, 133, 0.1);
}

/* Modal Styles */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    padding: 1rem;
    overflow-y: auto;
}

.modal-content {
    background: white;
    width: 100%;
    max-width: 600px;
    margin: 2rem auto;
    border-radius: 10px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    border-bottom: 1px solid var(--border);
}

.modal-header h2 {
    font-size: 1.25rem;
    color: var(--text);
    margin: 0;
    font-weight: 600;
}

.close-btn {
    background: none;
    border: none;
    font-size: 1.5rem;
    color: var(--text-light);
    cursor: pointer;
    padding: 0.5rem;
    transition: color 0.2s ease;
}

.close-btn:hover {
    color: var(--text);
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    padding: 1.5rem;
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    color: var(--text);
    font-weight: 500;
    font-size: 0.9rem;
}

.form-group input,
.form-group select {
    width: 100%;
    padding: 0.7rem 1rem;
    border: 1px solid var(--border);
    border-radius: 6px;
    font-size: 0.9rem;
    transition: all 0.2s ease;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px var(--primary-light);
}

.modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    padding: 1.5rem;
    border-top: 1px solid var(--border);
}

.btn-cancel {
    padding: 0.7rem 1.5rem;
    border: 1px solid var(--border);
    background: white;
    color: var(--text);
    border-radius: 6px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-cancel:hover {
    background: var(--bg);
}

.btn-primary {
    padding: 0.7rem 1.5rem;
    border: none;
    background: var(--primary);
    color: white;
    border-radius: 6px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-primary:hover {
    background: #3a56d4;
    transform: translateY(-1px);
}

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

    .content-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .filters {
        flex-direction: column;
        gap: 0.8rem;
    }
    
    .filters input,
    .filters select {
        width: 100%;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    #customers-table {
        display: block;
        overflow-x: auto;
    }

    .stat-card .value {
        font-size: 1.5rem;
    }
}
</style>

<div class="dashboard-container">
    <?php include 'sidebar.php'; ?>
    
    <main class="main-content">
        <div class="content-header">
            <h1>Customer Management</h1>
            <button class="btn-add" onclick="addCustomer()">
                <i class="fas fa-plus"></i> Add Customer
            </button>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Customers</h3>
                <div class="value"><?= count($customers) ?></div>
            </div>
            <div class="stat-card">
                <h3>Active Segments</h3>
                <div class="value"><?= count($segments) ?></div>
            </div>
        </div>

        <div class="chart-grid">
            <div class="chart-card">
                <h3>Customer Segments</h3>
                <div class="chart-wrapper">
                    <canvas id="segmentChart"></canvas>
                </div>
            </div>
        </div>
        
        <div class="filters">
            <input type="text" id="customer-search" placeholder="Search customers...">
            <select id="segment-filter">
                <option value="">All Segments</option>
                <?php foreach ($segments as $segment): ?>
                    <option value="<?= htmlspecialchars($segment['customer_segment']) ?>">
                        <?= htmlspecialchars($segment['customer_segment']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="table-container">
            <table id="customers-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Segment</th>
                        <th>Region</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $customer): ?>
                    <tr>
                        <td><?= $customer['customer_id'] ?></td>
                        <td><?= htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']) ?></td>
                        <td><?= htmlspecialchars($customer['email']) ?></td>
                        <td><?= htmlspecialchars($customer['customer_segment']) ?></td>
                        <td><?= htmlspecialchars($customer['region']) ?></td>
                        <td class="action-buttons">
                            <button onclick="editCustomer(<?= $customer['customer_id'] ?>)" class="btn-edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deleteCustomer(<?= $customer['customer_id'] ?>)" class="btn-delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        </div> <!-- end of table-container -->

<!-- Add Customer Modal -->
<div id="customerModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2><span id="modalTitle">Add Customer</span></h2>
            <button type="button" class="close-btn" onclick="closeModal()">×</button>
        </div>
        <form id="customerForm" onsubmit="handleCustomerSubmit(event)">
            <input type="hidden" id="customer_id" name="customer_id">
            <div class="form-grid">
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="customer_segment">Segment</label>
                    <select id="customer_segment" name="customer_segment" required>
                        <option value="">Select Segment</option>
                        <?php foreach ($segments as $segment): ?>
                        <option value="<?= htmlspecialchars($segment['customer_segment']) ?>">
                            <?= htmlspecialchars($segment['customer_segment']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="region">Region</label>
                    <input type="text" id="region" name="region" required>
                </div>
            
            </div>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn-primary">Save Customer</button>
            </div>
        </form>
    </div>
</div>
    </main>
</div>

<script>
let segmentChart;

document.addEventListener('DOMContentLoaded', function() {
    // Initialize segment chart with global reference
    segmentChart = new Chart(document.getElementById('segmentChart'), {
        type: 'pie',
        data: {
            labels: <?= json_encode(array_column($segments, 'customer_segment')) ?>,
            datasets: [{
                data: <?= json_encode(array_column($segments, 'count')) ?>,
                backgroundColor: ['#4361ee', '#4895ef', '#4cc9f0', '#3f37c9']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Initialize search and filter
    document.getElementById('customer-search').addEventListener('input', filterCustomers);
    document.getElementById('segment-filter').addEventListener('change', filterCustomers);

    // Start auto-update
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
        const response = await fetch(`get_updates.php?type=customers&last_update=${lastUpdate}`);
        const data = await response.json();
        
        if (data.success && data.data) {
            updateCustomersData(data.data);
            lastUpdate = new Date().toISOString();
        }
    } catch (error) {
        console.error('Update error:', error);
    }
}

function updateCustomersData(data) {
    // Update summary cards with animation
    updateStatCard('Total Customers', data.total_customers);
    updateStatCard('Active Segments', data.total_segments);

    // Update segment chart
    if (data.segments) {
        segmentChart.data.labels = data.segments.map(s => s.customer_segment);
        segmentChart.data.datasets[0].data = data.segments.map(s => s.count);
        segmentChart.update();
    }

    // Update customers table
    if (data.customers) {
        updateCustomersTable(data.customers);
    }
}

function updateStatCard(title, value) {
    const card = Array.from(document.querySelectorAll('.stat-card'))
        .find(card => card.querySelector('h3').textContent === title);
    
    if (card) {
        const valueElement = card.querySelector('.value');
        const currentValue = parseInt(valueElement.textContent);
        animateValue(valueElement, currentValue, value, 500);
    }
}

function animateValue(element, start, end, duration) {
    const range = end - start;
    const startTime = performance.now();
    
    function updateNumber(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        
        const current = start + (range * progress);
        element.textContent = Math.round(current).toLocaleString();
        
        if (progress < 1) {
            requestAnimationFrame(updateNumber);
        }
    }
    
    requestAnimationFrame(updateNumber);
}

function updateCustomersTable(customers) {
    const tbody = document.querySelector('#customers-table tbody');
    
    customers.forEach(customer => {
        const existing = tbody.querySelector(`tr[data-id="${customer.customer_id}"]`);
        const newRow = createCustomerRow(customer);
        
        if (existing) {
            existing.innerHTML = newRow;
        } else {
            tbody.insertAdjacentHTML('afterbegin', `<tr data-id="${customer.customer_id}">${newRow}</tr>`);
        }
    });
}

function createCustomerRow(customer) {
    return `
        <td>${customer.customer_id}</td>
        <td>${escapeHtml(customer.first_name + ' ' + customer.last_name)}</td>
        <td>${escapeHtml(customer.email)}</td>
        <td>${escapeHtml(customer.customer_segment)}</td>
        <td>${escapeHtml(customer.region)}</td>
        <td class="action-buttons">
            <button onclick="editCustomer(${customer.customer_id})" class="btn-edit">
                <i class="fas fa-edit"></i>
            </button>
            <button onclick="deleteCustomer(${customer.customer_id})" class="btn-delete">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    `;
}

function escapeHtml(unsafe) {
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function filterCustomers() {
    const searchTerm = document.getElementById('customer-search').value.toLowerCase();
    const segment = document.getElementById('segment-filter').value;
    const rows = document.querySelectorAll('#customers-table tbody tr');

    rows.forEach(row => {
        const name = row.cells[1].textContent.toLowerCase();
        const customerSegment = row.cells[3].textContent;
        const matchesSearch = name.includes(searchTerm);
        const matchesSegment = !segment || customerSegment === segment;
        
        row.style.display = matchesSearch && matchesSegment ? '' : 'none';
    });
}

async function addCustomer() {
    const modal = document.getElementById('customerModal');
    const form = document.getElementById('customerForm');
    const title = document.getElementById('modalTitle');
    
    form.reset();
    document.getElementById('customer_id').value = '';
    title.textContent = 'Add Customer';
    modal.style.display = 'block';
}

async function editCustomer(id) {
    const modal = document.getElementById('customerModal');
    const form = document.getElementById('customerForm');
    const title = document.getElementById('modalTitle');
    
    try {
        const response = await fetch(`get_customer.php?id=${id}`);
        const customer = await response.json();
        
        if (customer) {
            document.getElementById('customer_id').value = customer.customer_id;
            document.getElementById('first_name').value = customer.first_name;
            document.getElementById('last_name').value = customer.last_name;
            document.getElementById('email').value = customer.email;
            document.getElementById('customer_segment').value = customer.customer_segment;
            document.getElementById('region').value = customer.region;
            document.getElementById('phone').value = customer.phone;
            
            title.textContent = 'Edit Customer';
            modal.style.display = 'block';
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error loading customer data');
    }
}

async function deleteCustomer(id) {
    if (confirm('Are you sure you want to delete this customer?')) {
        try {
            const response = await fetch('delete_customer.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id=${id}`
            });
            
            const result = await response.json();
            
            if (result.success) {
                const row = document.querySelector(`#customers-table tr[data-id="${id}"]`);
                if (row) {
                    row.remove();
                    updateStatCard('Total Customers', result.total_customers);
                }
            } else {
                alert(result.message || 'Error deleting customer');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error deleting customer');
        }
    }
}

function closeModal() {
    document.getElementById('customerModal').style.display = 'none';
}

async function handleCustomerSubmit(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    
    try {
        const response = await fetch('save_customer.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            closeModal();
            // Update the table without full page reload
            autoUpdate();
        } else {
            alert(result.message || 'Error saving customer');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error saving customer');
    }
}
</script>

<?php require_once 'footer.php'; ?>