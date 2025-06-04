<?php
require_once 'config.php';
require_once 'functions.php';
checkAuth();

$pageType = 'products';
$pageTitle = "Products";
require_once 'header.php';

// Fetch products data with sales data
$products = $pdo->query("
    SELECT p.*, 
           COALESCE(SUM(oi.quantity), 0) as units_sold,
           COALESCE(SUM(oi.quantity * oi.unit_price), 0) as revenue
    FROM products p
    LEFT JOIN order_items oi ON p.product_id = oi.product_id
    GROUP BY p.product_id
    ORDER BY units_sold DESC
")->fetchAll();

// Get categories
$categories = $pdo->query("
    SELECT category, COUNT(*) as count
    FROM products
    GROUP BY category
")->fetchAll(); // Added missing semicolon here

$topProducts = array_slice($products, 0, 5);
?>
<style>
:root {
    --primary: #4361ee;
    --primary-light: rgba(67, 97, 238, 0.1);
    --text: #2b2d42;
    --text-light: #8d99ae;
    --bg: #f8fafc;
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
    transition: background-color 0.2s ease;
}

.btn-add:hover {
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
}

.filters input:focus,
.filters select:focus {
    outline: none;
    border-color: var(--primary);
}

.table-container {
    background-color: var(--card-bg);
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    overflow: hidden;
    margin-bottom: 2rem;
}

#products-table {
    width: 100%;
    border-collapse: collapse;
}

#products-table thead {
    background-color: var(--primary-light);
}

#products-table th {
    padding: 1rem;
    text-align: left;
    color: var(--text);
    font-weight: 600;
    border-bottom: 1px solid var(--border);
}

#products-table td {
    padding: 1rem;
    border-bottom: 1px solid var(--border);
    color: var(--text);
}

#products-table tr:last-child td {
    border-bottom: none;
}

#products-table tr:hover {
    background-color: var(--primary-light);
}

.badge {
    display: inline-block;
    padding: 0.3rem 0.6rem;
    background-color: var(--primary-light);
    color: var(--primary);
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
}

.actions {
    display: flex;
    gap: 0.5rem;
}

.btn-edit, .btn-delete {
    border: none;
    background: none;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 4px;
    transition: background-color 0.2s ease;
}

.btn-edit {
    color: var(--primary);
}

.btn-delete {
    color: var(--negative);
}

.btn-edit:hover {
    background-color: rgba(67, 97, 238, 0.1);
}

.btn-delete:hover {
    background-color: rgba(247, 37, 133, 0.1);
}

.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
}

.modal-content {
    background: white;
    width: 90%;
    max-width: 500px;
    margin: 2rem auto;
    border-radius: 10px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    overflow: hidden;
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
}

.close-btn {
    background: none;
    border: none;
    font-size: 1.5rem;
    color: var(--text-light);
    cursor: pointer;
    padding: 0.5rem;
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
    transition: all 0.2s;
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
    transition: all 0.2s;
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
    transition: all 0.2s;
}

.btn-primary:hover {
    background: #3a56d4;
}

@media (max-width: 768px) {
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
    
    #products-table {
        display: block;
        overflow-x: auto;
    }
}
</style>

<div class="dashboard-container">
    <?php include 'sidebar.php'; ?>
    
    <main class="main-content">
        <div class="content-header">
            <h1>Product Management</h1>
            <button class="btn-add" onclick="addProduct()">
                <i class="fas fa-plus"></i> Add Product
            </button>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="label">
                    <i class="fas fa-box"></i> Total Products
                </div>
                <div class="value"><?= count($products) ?></div>
            </div>
            <?php foreach ($categories as $category): ?>
            <div class="stat-card">
                <div class="label">
                    <i class="fas fa-tag"></i> <?= htmlspecialchars($category['category']) ?>
                </div>
                <div class="value"><?= $category['count'] ?></div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="filters">
            <input type="text" id="product-search" placeholder="Search products...">
            <select id="category-filter">
                <option value="">All Categories</option>
                <?php foreach ($categories as $category): ?>
                <option value="<?= htmlspecialchars($category['category']) ?>">
                    <?= htmlspecialchars($category['category']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="table-container">
            <table id="products-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Units Sold</th>
                        <th>Revenue</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                    <tr>
                        <td><?= $product['product_id'] ?></td>
                        <td><?= htmlspecialchars($product['product_name']) ?></td>
                        <td>
                            <span class="badge"><?= htmlspecialchars($product['category']) ?></span>
                        </td>
                        <td>$<?= number_format($product['price'], 2) ?></td>
                        <td><?= $product['stock_quantity'] ?></td>
                        <td><?= $product['units_sold'] ?? 0 ?></td>
                        <td>$<?= number_format($product['revenue'] ?? 0, 2) ?></td>
                        <td class="actions">
                            <button onclick="editProduct(<?= $product['product_id'] ?>)" class="btn-edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="deleteProduct(<?= $product['product_id'] ?>)" class="btn-delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div> <!-- end of table-container -->
                <!-- Add Product Modal -->
        <div id="productModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 id="modalTitle">Add Product</h2>
                    <button type="button" class="close-btn" onclick="closeModal()">&times;</button>
                </div>
                <form id="productForm" onsubmit="handleProductSubmit(event)">
                    <input type="hidden" id="product_id" name="product_id">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="product_name">Product Name</label>
                            <input type="text" id="product_name" name="product_name" required>
                        </div>
                        <div class="form-group">
                            <label for="category">Category</label>
                            <input type="text" id="category" name="category" required>
                        </div>
                        <div class="form-group">
                            <label for="price">Price ($)</label>
                            <input type="number" id="price" name="price" step="0.01" min="0" required>
                        </div>
                        <div class="form-group">
                            <label for="stock_quantity">Stock Quantity</label>
                            <input type="number" id="stock_quantity" name="stock_quantity" min="0" required>
                        </div>
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
                        <button type="submit" class="btn-primary">Save Product</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize chart and other functionality
    initializeChart();
    
    // Add event listeners for search and filter
    document.getElementById('product-search').addEventListener('input', filterProducts);
    document.getElementById('category-filter').addEventListener('change', filterProducts);
});

function addProduct() {
    const modal = document.getElementById('productModal');
    const form = document.getElementById('productForm');
    
    // Reset form and clear hidden ID
    form.reset();
    document.getElementById('product_id').value = '';
    document.getElementById('modalTitle').textContent = 'Add Product';
    
    // Show modal
    modal.style.display = 'block';
}

function closeModal() {
    const modal = document.getElementById('productModal');
    modal.style.display = 'none';
}

// Replace or update the handleProductSubmit function
async function handleProductSubmit(event) {
    event.preventDefault();
    
    try {
        const form = event.target;
        const formData = new FormData(form);
        
        // Basic validation
        if (!formData.get('product_name').trim()) {
            throw new Error('Product name is required');
        }
        
        if (!formData.get('category').trim()) {
            throw new Error('Category is required');
        }
        
        if (parseFloat(formData.get('price')) < 0) {
            throw new Error('Price must be positive');
        }
        
        if (parseInt(formData.get('stock_quantity')) < 0) {
            throw new Error('Stock quantity must be positive');
        }

        const response = await fetch('save_product.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            closeModal();
            // Refresh the page to show updated data
            location.reload();
        } else {
            alert(result.message || 'Error saving product');
        }
    } catch (error) {
        console.error('Error:', error);
        alert(error.message || 'Error saving product');
    }

}

function updateProductsData(data) {
    // Update summary cards with animation
    updateStatCard('Total Products', data.total_products);
    updateStatCard('Total Stock', data.total_stock);

    // Update products chart if top products changed
    if (data.top_products) {
        productsChart.data.labels = data.top_products.map(p => p.product_name);
        productsChart.data.datasets[0].data = data.top_products.map(p => p.qty_sold);
        productsChart.update();
    }

    // Update products table
    if (data.products) {
        updateProductsTable(data.products);
    }
}

function updateStatCard(title, value) {
    const card = Array.from(document.querySelectorAll('.stat-card'))
        .find(card => card.querySelector('.label').textContent.includes(title));
    
    if (card) {
        const valueElement = card.querySelector('.value');
        const currentValue = parseInt(valueElement.textContent.replace(/,/g, ''));
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

function updateProductsTable(products) {
    const tbody = document.querySelector('#products-table tbody');
    
    products.forEach(product => {
        const existing = tbody.querySelector(`tr[data-id="${product.product_id}"]`);
        const newRow = createProductRow(product);
        
        if (existing) {
            existing.innerHTML = newRow;
        } else {
            tbody.insertAdjacentHTML('afterbegin', `<tr data-id="${product.product_id}">${newRow}</tr>`);
        }
    });
}

function createProductRow(product) {
    return `
        <td>${product.product_id}</td>
        <td>${escapeHtml(product.product_name)}</td>
        <td>${escapeHtml(product.category)}</td>
        <td>$${parseFloat(product.price).toLocaleString(undefined, {minimumFractionDigits: 2})}</td>
        <td>${product.stock_quantity}</td>
        <td class="action-buttons">
            <button onclick="editProduct(${product.product_id})" class="btn-edit">
                <i class="fas fa-edit"></i>
            </button>
            <button onclick="deleteProduct(${product.product_id})" class="btn-delete">
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

function filterProducts() {
    const searchTerm = document.getElementById('product-search').value.toLowerCase();
    const category = document.getElementById('category-filter').value;
    const rows = document.querySelectorAll('#products-table tbody tr');

    rows.forEach(row => {
        const name = row.cells[1].textContent.toLowerCase();
        const productCategory = row.cells[2].textContent;
        const matchesSearch = name.includes(searchTerm);
        const matchesCategory = !category || productCategory === category;
        
        row.style.display = matchesSearch && matchesCategory ? '' : 'none';
    });
}

async function addProduct() {
    const modal = document.getElementById('productModal');
    const form = document.getElementById('productForm');
    const title = document.getElementById('modalTitle');
    
    form.reset();
    document.getElementById('product_id').value = '';
    title.textContent = 'Add Product';
    modal.style.display = 'block';
}

async function editProduct(id) {
    const modal = document.getElementById('productModal');
    const form = document.getElementById('productForm');
    const title = document.getElementById('modalTitle');
    
    try {
        const response = await fetch(`get_product.php?id=${id}`);
        const product = await response.json();
        
        if (product) {
            document.getElementById('product_id').value = product.product_id;
            document.getElementById('product_name').value = product.product_name;
            document.getElementById('category').value = product.category;
            document.getElementById('price').value = product.price;
            document.getElementById('stock_quantity').value = product.stock_quantity;
            
            title.textContent = 'Edit Product';
            modal.style.display = 'block';
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error loading product data');
    }
}

async function deleteProduct(id) {
    if (confirm('Are you sure you want to delete this product?')) {
        try {
            const response = await fetch('delete_product.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id=${id}`
            });
            
            const result = await response.json();
            
            if (result.success) {
                const row = document.querySelector(`#products-table tr[data-id="${id}"]`);
                if (row) {
                    row.remove();
                    updateStatCard('Total Products', result.total_products);
                }
            } else {
                alert(result.message || 'Error deleting product');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error deleting product');
        }
    }
}

function closeModal() {
    document.getElementById('productModal').style.display = 'none';
}

async function handleProductSubmit(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    
    try {
        const response = await fetch('save_product.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            closeModal();
            autoUpdate(); // Update the table without full page reload
        } else {
            alert(result.message || 'Error saving product');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error saving product');
    }
}
</script>
