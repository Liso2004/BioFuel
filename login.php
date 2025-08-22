<?php include 'includes/header.php'; ?>
<?php include 'includes/config.php'; ?>

<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            // Load user's cart from database
            $_SESSION['cart'] = loadUserCart($_SESSION['user_id'], $conn);
            
            // Check if there's a cart message to display
            if (isset($_SESSION['cart_message'])) {
                $cart_message = $_SESSION['cart_message'];
                unset($_SESSION['cart_message']);
            }
            
            // Check if there's a redirect URL
            if (isset($_SESSION['redirect_to'])) {
                $redirect = $_SESSION['redirect_to'];
                unset($_SESSION['redirect_to']);
                header("Location: $redirect");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            $error = "Invalid email or password.";
        }
    } else {
        $error = "Invalid email or password.";
    }
}
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="text-center">Login</h3>
                </div>
                <div class="card-body">
                    <?php if(isset($error)): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>
                    
                    <?php if(isset($_SESSION['cart_message'])): ?>
                        <div class="alert alert-info"><?= $_SESSION['cart_message'] ?></div>
                        <?php unset($_SESSION['cart_message']); ?>
                    <?php endif; ?>
                    
                    <form method="POST" action="login.php">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-dark w-100">Login</button>
                    </form>
                    
                    <div class="mt-3 text-center">
                        <p>Don't have an account? <a href="register.php">Sign Up</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>