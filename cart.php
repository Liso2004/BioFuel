<?php include 'includes/header.php'; ?>
<?php include 'includes/config.php'; ?>

<?php
// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Remove from cart
if (isset($_GET['remove'])) {
    $product_id = $_GET['remove'];
    unset($_SESSION['cart'][$product_id]);
    
    // Update database if user is logged in
    if (isset($_SESSION['user_id'])) {
        saveUserCart($_SESSION['user_id'], $_SESSION['cart'], $conn);
    }
}

// Update quantity
if (isset($_POST['update_cart'])) {
    foreach ($_POST['quantities'] as $product_id => $quantity) {
        if ($quantity > 0) {
            $_SESSION['cart'][$product_id]['quantity'] = $quantity;
        } else {
            unset($_SESSION['cart'][$product_id]);
        }
    }
    
    // Update database if user is logged in
    if (isset($_SESSION['user_id'])) {
        saveUserCart($_SESSION['user_id'], $_SESSION['cart'], $conn);
    }
}

echo '<script>sessionStorage.setItem("cartCount", "' . count($_SESSION['cart']) . '");</script>';
?>

<div class="container my-4 min-vh-90">
    <h1 class="text-center mb-4">Your Cart</h1>
    <h5 class="text-center mb-4">Free shipping for orders over R50</h5>
    
    <?php if (empty($_SESSION['cart'])): ?>
        <div class="alert alert-info">
            Your cart is empty. <a href="products.php">Browse our products</a> to add items.
        </div>
    <?php else: ?>
        <form method="POST" action="cart.php">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $subtotal = 0;
                        foreach ($_SESSION['cart'] as $item):
                            $item_total = $item['price'] * $item['quantity'];
                            $subtotal += $item_total;
                        ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="<?= $item['image'] ?>" alt="<?= $item['name'] ?>" width="50" class="me-3">
                                        <?= $item['name'] ?>
                                    </div>
                                </td>
                                <td>R<?= number_format($item['price'], 2) ?></td>
                                <td>
                                    <input type="number" name="quantities[<?= $item['id'] ?>]" value="<?= $item['quantity'] ?>" min="1" class="form-control" style="width: 70px;">
                                </td>
                                <td>R<?= number_format($item_total, 2) ?></td>
                                <td>
                                    <a href="cart.php?remove=<?= $item['id'] ?>" class="btn btn-sm btn-danger">Remove</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end fw-bold">Subtotal:</td>
                            <td colspan="2" class="fw-bold">R<?= number_format($subtotal, 2) ?></td>
                        </tr>
                        <?php 
                        $shipping_cost = ($subtotal < 50) ? 20 : 0;
                        $total = $subtotal + $shipping_cost;
                        ?>
                        <tr>
                            <td colspan="3" class="text-end">Shipping:</td>
                            <td colspan="2"><?= ($shipping_cost > 0) ? 'R' . number_format($shipping_cost, 2) : 'FREE' ?></td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end fw-bold">Grand Total:</td>
                            <td colspan="2" class="fw-bold">R<?= number_format($total, 2) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <div class="d-flex justify-content-between mt-4">
                <a href="products.php" class="btn btn-outline-dark">Continue Shopping</a>
                <div>
                    <button type="submit" name="update_cart" class="btn btn-secondary me-2">Update Cart</button>
                    <a href="checkout.php" class="btn btn-dark">Proceed to Checkout</a>
                </div>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>