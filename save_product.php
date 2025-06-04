<?php
require_once 'config.php';
require_once 'functions.php';

checkAuth();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode(['success' => false, 'message' => 'Invalid request method']));
}

try {
    // Sanitize inputs
    $product_id = !empty($_POST['product_id']) ? (int)$_POST['product_id'] : null;
    $product_name = trim($_POST['product_name']);
    $category = trim($_POST['category']);
    $price = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);
    $stock_quantity = filter_var($_POST['stock_quantity'], FILTER_VALIDATE_INT);

    // Validate required fields
    if (empty($product_name) || empty($category)) {
        throw new Exception('Product name and category are required');
    }

    if ($price === false || $price < 0) {
        throw new Exception('Invalid price');
    }

    if ($stock_quantity === false || $stock_quantity < 0) {
        throw new Exception('Invalid stock quantity');
    }

    // Start transaction
    $pdo->beginTransaction();

    if ($product_id) {
        // Update existing product
        $stmt = $pdo->prepare("
            UPDATE products 
            SET product_name = ?, 
                category = ?, 
                price = ?, 
                stock_quantity = ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE product_id = ?
        ");
        
        $success = $stmt->execute([
            $product_name,
            $category,
            $price,
            $stock_quantity,
            $product_id
        ]);
    } else {
        // Insert new product
        $stmt = $pdo->prepare("
            INSERT INTO products 
            (product_name, category, price, stock_quantity) 
            VALUES (?, ?, ?, ?)
        ");
        
        $success = $stmt->execute([
            $product_name,
            $category,
            $price,
            $stock_quantity
        ]);
    }

    if (!$success) {
        throw new Exception('Failed to save product');
    }

    // Get updated counts
    $total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
    
    // Commit transaction
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => $product_id ? 'Product updated successfully' : 'Product added successfully',
        'total_products' => $total_products
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}