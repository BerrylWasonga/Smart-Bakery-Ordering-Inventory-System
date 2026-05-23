<?php
require_once __DIR__ . '/../includes/bootstrap.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']); exit;
}

if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
    echo json_encode(['success' => false, 'message' => 'Invalid token']); exit;
}

$action = sanitize($_POST['action'] ?? '');

function getCartIdentifier(): array {
    if (isLoggedIn()) return ['user_id = ?', [$_SESSION['user_id']]];
    return ['session_id = ?', [session_id()]];
}

function getCartTotal(): float {
    [$cond, $params] = getCartIdentifier();
    $stmt = db()->prepare("SELECT SUM(COALESCE(v.price, p.discount_price, p.price) * c.quantity) 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        LEFT JOIN product_variants v ON c.product_variant_id = v.id
        WHERE c.$cond");
    $stmt->execute($params);
    return (float) $stmt->fetchColumn();
}

function getCartCount2(): int {
    [$cond, $params] = getCartIdentifier();
    $stmt = db()->prepare("SELECT COALESCE(SUM(quantity),0) FROM cart WHERE $cond");
    $stmt->execute($params);
    return (int) $stmt->fetchColumn();
}

if ($action === 'add') {
    $productId = sanitizeInt($_POST['product_id'] ?? 0);
    $variantId = isset($_POST['variant_id']) && $_POST['variant_id'] !== '' ? sanitizeInt($_POST['variant_id']) : null;
    $qty = max(1, sanitizeInt($_POST['quantity'] ?? 1));
    if (!$productId) { echo json_encode(['success' => false, 'message' => 'Invalid product']); exit; }

    // Check product exists and status is active
    $prod = db()->prepare("SELECT id, name, stock_quantity FROM products WHERE id = ? AND status = 'active'");
    $prod->execute([$productId]);
    $product = $prod->fetch();
    if (!$product) { echo json_encode(['success' => false, 'message' => 'Product not found']); exit; }

    // Check if the product has variants
    $vCountStmt = db()->prepare("SELECT COUNT(*) FROM product_variants WHERE product_id = ?");
    $vCountStmt->execute([$productId]);
    $variantsCount = (int)$vCountStmt->fetchColumn();

    if ($variantsCount > 0) {
        if ($variantId === null) {
            echo json_encode(['success' => false, 'message' => 'Please select a variant (e.g. Small, Medium, Large)']);
            exit;
        }
        // Verify variant exists and is for this product
        $varStmt = db()->prepare("SELECT id, variant_name, price, stock_quantity FROM product_variants WHERE id = ? AND product_id = ?");
        $varStmt->execute([$variantId, $productId]);
        $variant = $varStmt->fetch();
        if (!$variant) {
            echo json_encode(['success' => false, 'message' => 'Invalid product variant selected']);
            exit;
        }
        // Check stock for variant
        if ($variant['stock_quantity'] < $qty) {
            echo json_encode(['success' => false, 'message' => 'Requested quantity exceeds available stock']);
            exit;
        }
    } else {
        // Product has no variants
        $variantId = null;
        // Check standard stock
        if ($product['stock_quantity'] < $qty) {
            echo json_encode(['success' => false, 'message' => 'Requested quantity exceeds available stock']);
            exit;
        }
    }

    // Check existing cart entry
    [$cond, $params] = getCartIdentifier();
    if ($variantId !== null) {
        $existSql = "SELECT id, quantity FROM cart WHERE $cond AND product_id = ? AND product_variant_id = ?";
        $existParams = array_merge($params, [$productId, $variantId]);
    } else {
        $existSql = "SELECT id, quantity FROM cart WHERE $cond AND product_id = ? AND product_variant_id IS NULL";
        $existParams = array_merge($params, [$productId]);
    }
    
    $exist = db()->prepare($existSql);
    $exist->execute($existParams);
    $row = $exist->fetch();
    
    if ($row) {
        // Verify final total quantity won't exceed stock
        $newQty = $row['quantity'] + $qty;
        $maxStock = ($variantId !== null) ? $variant['stock_quantity'] : $product['stock_quantity'];
        if ($newQty > $maxStock) {
            echo json_encode(['success' => false, 'message' => 'Cannot add more. Exceeds available stock.']);
            exit;
        }
        db()->prepare("UPDATE cart SET quantity = ? WHERE id = ?")->execute([$newQty, $row['id']]);
    } else {
        // Insert new item
        if (isLoggedIn()) {
            db()->prepare("INSERT INTO cart (user_id, product_id, product_variant_id, quantity) VALUES (?,?,?,?)")
                ->execute([$_SESSION['user_id'], $productId, $variantId, $qty]);
        } else {
            db()->prepare("INSERT INTO cart (session_id, product_id, product_variant_id, quantity) VALUES (?,?,?,?)")
                ->execute([session_id(), $productId, $variantId, $qty]);
        }
    }
    echo json_encode(['success' => true, 'message' => 'Added to cart', 'cart_count' => getCartCount2()]);

} elseif ($action === 'increase') {
    $cartId = sanitizeInt($_POST['cart_id'] ?? 0);
    $cartItemStmt = db()->prepare("SELECT c.quantity, c.product_id, c.product_variant_id, p.stock_quantity AS prod_stock, v.stock_quantity AS var_stock 
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        LEFT JOIN product_variants v ON c.product_variant_id = v.id 
        WHERE c.id = ?");
    $cartItemStmt->execute([$cartId]);
    $item = $cartItemStmt->fetch();
    if ($item) {
        $currentQty = (int)$item['quantity'];
        $maxStock = ($item['product_variant_id'] !== null) ? (int)$item['var_stock'] : (int)$item['prod_stock'];
        if ($currentQty >= $maxStock) {
            echo json_encode(['success' => false, 'message' => 'Cannot increase. Exceeds available stock.']);
            exit;
        }
        db()->prepare("UPDATE cart SET quantity = quantity + 1 WHERE id = ?")->execute([$cartId]);
        echo json_encode(['success' => true, 'cart_count' => getCartCount2()]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Item not found']);
    }

} elseif ($action === 'decrease') {
    $cartId = sanitizeInt($_POST['cart_id'] ?? 0);
    $stmt = db()->prepare("SELECT quantity FROM cart WHERE id = ?");
    $stmt->execute([$cartId]);
    $qty = (int) $stmt->fetchColumn();
    if ($qty <= 1) {
        db()->prepare("DELETE FROM cart WHERE id = ?")->execute([$cartId]);
    } else {
        db()->prepare("UPDATE cart SET quantity = quantity - 1 WHERE id = ?")->execute([$cartId]);
    }
    echo json_encode(['success' => true, 'cart_count' => getCartCount2()]);

} elseif ($action === 'remove') {
    $cartId = sanitizeInt($_POST['cart_id'] ?? 0);
    db()->prepare("DELETE FROM cart WHERE id = ?")->execute([$cartId]);
    echo json_encode(['success' => true, 'cart_count' => getCartCount2()]);

} else {
    echo json_encode(['success' => false, 'message' => 'Unknown action']);
}
