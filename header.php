<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Retail System - <?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></title>
    
    <!-- Font Awesome and Google Fonts -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --primary-50: #f0f4ff;
            --primary-500: #4361ee;
            --primary-600: #2d4ae4;
            --gray-50: #f8fafc;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-700: #334155;
            --gray-800: #1e293b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--gray-50);
            margin: 0;
        }

        .app-header {
            background: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid var(--gray-200);
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-left: 280px; /* Add this to align with sidebar */
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-500);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .btn-add {
            background: var(--primary-500);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 0.375rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-add:hover {
            background: var(--primary-600);
            transform: translateY(-1px);
        }

        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .content-header h1 {
            font-size: 1.875rem;
            color: var(--gray-800);
        }

        /* Sidebar Styles */
        .sidebar {
            background: var(--gray-800);
            width: 280px;
            min-height: 100vh;
            padding: 2rem 1rem;
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            z-index: 50;
        }

        .sidebar nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar nav li {
            margin-bottom: 0.5rem;
        }

        .sidebar nav a {
            display: flex;
            align-items: center;
            padding: 0.875rem 1.25rem;
            color: var(--gray-100);
            text-decoration: none;
            border-radius: 0.5rem;
            transition: all 0.2s;
            gap: 0.75rem;
        }

        .sidebar nav a:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        .sidebar nav li.active a {
            background: var(--primary-500);
            color: white;
        }

        .sidebar nav i {
            font-size: 1.25rem;
            width: 1.5rem;
        }

        .main-content {
            margin-left: 280px;
            padding: 2rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            
            .sidebar.active {
                transform: translateX(0);
            }

            .app-header,
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <header class="app-header">
        <a href="dashboard.php" class="logo">
            <i class="fas fa-store"></i>
            <span>Online Retail Sales Analysis</span>
        </a>
        <div class="user-menu">
            <?php if (isset($_SESSION['username'])): ?>
                <span>Welcome, <?= htmlspecialchars($_SESSION['username']) ?></span>
                <a href="logout.php" class="btn-logout" title="Logout">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            <?php endif; ?>
        </div>
    </header>