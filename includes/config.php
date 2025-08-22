<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'liso1707');
define('DB_NAME', 'biofuel');

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Helper function to load cart from database
function loadUserCart($user_id, $conn) {
    $cart = [];
    
    // First verify the user exists
    $check_sql = "SELECT id FROM users WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        return $cart; // Return empty cart if user doesn't exist
    }

    $sql = "SELECT p.id, p.name, p.price, p.image_path, uc.quantity 
            FROM user_carts uc
            JOIN products p ON uc.product_id = p.id
            WHERE uc.user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $cart[$row['id']] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'price' => $row['price'],
                'quantity' => $row['quantity'],
                'image' => $row['image_path']
            ];
        }
    } else {
        error_log("Error loading cart for user: " . $user_id);
    }
    
    return $cart;
}

// Helper function to save cart to database
function saveUserCart($user_id, $cart, $conn) {
    // First verify the user exists
    $check_sql = "SELECT id FROM users WHERE id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        return false; // User doesn't exist
    }

    // Start transaction
    $conn->begin_transaction();

    try {
        // First clear existing cart items for this user
        $sql = "DELETE FROM user_carts WHERE user_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        // Insert current cart items
        if (!empty($cart)) {
            $sql = "INSERT INTO user_carts (user_id, product_id, quantity) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            
            foreach ($cart as $product_id => $item) {
                // Verify product exists
                $product_check = "SELECT id FROM products WHERE id = ?";
                $product_stmt = $conn->prepare($product_check);
                $product_stmt->bind_param("i", $product_id);
                $product_stmt->execute();
                $product_result = $product_stmt->get_result();
                
                if ($product_result->num_rows > 0) {
                    $stmt->bind_param("iii", $user_id, $product_id, $item['quantity']);
                    $stmt->execute();
                }
            }
        }
        
        $conn->commit();
        return true;
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error saving cart: " . $e->getMessage());
        return false;
    }
}
?>