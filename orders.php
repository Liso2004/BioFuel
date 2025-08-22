
<?php include 'includes/header.php'; ?>
<?php include 'includes/config.php'; ?>

<?php
// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user's orders
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result();
?>

<div class="container my-5">
    <h1 class="text-center mb-5">Your Orders</h1>
    
    <?php if ($orders->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = $orders->fetch_assoc()): ?>
                        <tr>
                            <td><?= date('M j, Y g:i A', strtotime($order['created_at'])) ?></td>
                            <td>R<?= number_format($order['total'], 2) ?></td>
                            <td>
                                <a href="order_details.php?order_id=<?= $order['id'] ?>" class="btn btn-sm btn-outline-dark">
                                    View Details
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            You haven't placed any orders yet. <a href="products.php">Browse our products</a> to get started.
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
