<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<aside class="sidebar">
    <nav>
        <ul>
            <li class="<?= isActivePage('dashboard') ? 'active' : '' ?>">
                <a href="dashboard.php">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="<?= isActivePage('products') ? 'active' : '' ?>">
                <a href="products.php">
                    <i class="fas fa-box-open"></i>
                    <span>Products</span>
                </a>
            </li>
            <li class="<?= isActivePage('customers') ? 'active' : '' ?>">
                <a href="customers.php">
                    <i class="fas fa-users"></i>
                    <span>Customers</span>
                </a>
            </li>
            <li class="<?= isActivePage('reports') ? 'active' : '' ?>">
                <a href="reports.php">
                    <i class="fas fa-chart-bar"></i>
                    <span>Reports</span>
                </a>
            </li>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <li class="<?= isActivePage('admin') ? 'active' : '' ?>">
                <a href="admin.php">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </nav>
</aside>