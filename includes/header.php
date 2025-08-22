
<?php
// Start session at the very top
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize cart count
$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Biofuel</title>
    <!-- icon  -->
    <link rel="icon" href="assets/images/logo.jpg" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="assets/css/style.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-black">
        <div class="container">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
                <div class="navbar-nav me-auto m-auto" >
                    <a class="nav-link" href="index.php">Home</a>
                    <a class="nav-link" href="products.php">Products</a>
                    <a class="nav-link" href="about.php">About</a>
                    <a class="nav-link" href="contact.php">Contact</a>
                </div>
                <div class="d-flex align-items-center">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <!-- Logged In State -->
                        <a href="cart.php" class="btn btn-outline-light me-3 position-relative">
                            <i class="bi bi-cart-fill"></i>
                            <span id="cartCount" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?= $cart_count ?>
                            </span>
                        </a>
                        
                        <div class="dropdown">
                            <a class="btn btn-outline-light dropdown-toggle" href="#" role="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle me-1"></i>
                                <?= htmlspecialchars($_SESSION['username']) ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li><a class="dropdown-item text-dark-hover" href="orders.php">Orders</a></li>
                                <hr>
                                <li><a class="dropdown-item text-danger" href="logout.php">Logout</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <!-- Not Logged In State -->
                        <a href="login.php" class="btn btn-outline-light me-2">Login</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <style>
        .text-danger-hover:hover {
    color: var(--bs-danger) !important;
}
.dropdown-text{
    color: white;
}

    .dropdown-item:focus {
        background-color: transparent;
        color: inherit;
    }
    .dropdown-item:hover {
        background-color: #f8f9fa; /* Light gray background on hover */
    }
    .dropdown-item.text-danger-hover:hover {
        color: var(--bs-danger) !important;
        background-color: #f8f9fa;
    }

    </style>
    
    <script>
    // Update cart count from session storage on page load
    document.addEventListener('DOMContentLoaded', function() {
        const cartCount = sessionStorage.getItem('cartCount');
        if (cartCount) {
            document.getElementById('cartCount').textContent = cartCount;
            document.getElementById('cartCount').style.display = 'block';
        }
        
        // Clear the stored count after using it
        sessionStorage.removeItem('cartCount');
    });
    </script>