<?php include 'includes/header.php'; ?>
<?php include 'includes/config.php'; ?>

<?php
// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if order_id is provided
if (!isset($_GET['order_id'])) {
    header("Location: orders.php");
    exit();
}

$order_id = $_GET['order_id'];
$user_id = $_SESSION['user_id'];

// Verify the order belongs to the user and get order details
$sql = "SELECT o.id, o.created_at, o.total, o.payment_method,
        oi.product_id, oi.quantity, oi.price,
        p.name as product_name, p.image_path as product_image
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN products p ON oi.product_id = p.id
        WHERE o.id = ? AND o.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if order exists
if ($result->num_rows === 0) {
    header("Location: orders.php");
    exit();
}

// Get order header info (first row)
$first_row = $result->fetch_assoc();
$order_total = $first_row['total'];
$order_date = $first_row['created_at'];
$payment_method = $first_row['payment_method'];

// Format payment method for display
$payment_display = '';
switch($payment_method) {
    case 'visa':
        $payment_display = 'Visa';
        break;
    case 'mastercard':
        $payment_display = 'Mastercard';
        break;
    case 'paypal':
        $payment_display = 'PayPal';
        break;
    default:
        $payment_display = ucfirst($payment_method);
}
?>

<div class="container my-5 min-vh-100">
    <h1 class="text-center mb-5">Order Details</h1>
    
    <div class="card mb-4">
        <div class="card-header">
            <h5>Order Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Order Date:</strong> <?= date('M j, Y g:i A', strtotime($order_date)) ?></p>
                    <p><strong>Payment Method:</strong> <?= $payment_display ?></p>
                </div>
                <div class="col-md-6">
                    <p><strong>Order Total:</strong> R<?= number_format($order_total, 2) ?></p>
                </div>
            </div>
        </div>
    </div>

    <h4 class="mb-3">Order Items</h4>
    <div class="table-responsive mb-4">
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                // Reset pointer to first row since we fetched it already
                $result->data_seek(0);
                while ($item = $result->fetch_assoc()): 
                ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="<?= htmlspecialchars($item['product_image']) ?>" 
                                     alt="<?= htmlspecialchars($item['product_name']) ?>" 
                                     class="img-thumbnail me-3" 
                                     style="width: 70px; height: auto; object-fit: cover;">
                                <div>
                                    <h6 class="mb-0"><?= $item['product_name'] ?></h6>
                                </div>
                            </div>
                        </td>
                        <td>R<?= number_format($item['price'], 2) ?></td>
                        <td><?= $item['quantity'] ?></td>
                        <td>R<?= number_format($item['price'] * $item['quantity'], 2) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3" class="text-end">Total:</th>
                    <th>R<?= number_format($order_total, 2) ?></th>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="d-flex justify-content-between">
        <a href="orders.php" class="btn btn-outline-dark">
            <i class="bi bi-arrow-left"></i> Back to Orders
        </a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>