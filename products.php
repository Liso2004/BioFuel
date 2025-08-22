
<?php include 'includes/header.php'; ?>
<?php include 'includes/config.php'; ?>

<?php
// Handle add to cart on this page
if (isset($_POST['add_to_cart'])) {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['redirect_to'] = 'products.php';
        $_SESSION['cart_message'] = "Please login to add items to your cart.";
        $_SESSION['pending_product_id'] = $_POST['product_id'];
        header("Location: login.php");
        exit();
    }
    
    $product_id = $_POST['product_id'];
    
    // Initialize cart if not exists
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]['quantity'] += 1;
    } else {
        $sql = "SELECT * FROM products WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $product = $result->fetch_assoc();
            $_SESSION['cart'][$product_id] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'price' => $product['price'],
                'quantity' => 1,
                'image' => $product['image_path']
            ];
        }
    }
    
    // Save to database if user is logged in
    if (isset($_SESSION['user_id'])) {
        if (!saveUserCart($_SESSION['user_id'], $_SESSION['cart'], $conn)) {
            error_log("Failed to save cart for user: " . $_SESSION['user_id']);
        }
    }
    
    // Store the updated cart count in session storage for the next page load
    echo '<script>sessionStorage.setItem("cartCount", "' . count($_SESSION['cart']) . '");</script>';
    
    // Show success message
    $success_message = "Item added to cart successfully!";
}

// Check if there's a pending product to add after login
if (isset($_SESSION['pending_product_id']) && isset($_SESSION['user_id'])) {
    $product_id = $_SESSION['pending_product_id'];
    unset($_SESSION['pending_product_id']);
    
    // Add the pending product to cart
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]['quantity'] += 1;
    } else {
        $sql = "SELECT * FROM products WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $product = $result->fetch_assoc();
            $_SESSION['cart'][$product_id] = [
                'id' => $product['id'],
                'name' => $product['name'],
                'price' => $product['price'],
                'quantity' => 1,
                'image' => $product['image_path']
            ];
        }
    }
    
    // Save to database
    if (!saveUserCart($_SESSION['user_id'], $_SESSION['cart'], $conn)) {
        error_log("Failed to save cart for user: " . $_SESSION['user_id']);
    }
    
    // Store the updated cart count in session storage for the next page load
    echo '<script>sessionStorage.setItem("cartCount", "' . count($_SESSION['cart']) . '");</script>';
    
    $success_message = "Item added to cart successfully!";
}
?>

<div class="container my-5">
    <h1 class="text-center mb-5">Products</h1>
    
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" style="position: fixed; top: 20px; right: 20px; z-index: 1000;">
            <?= $success_message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <?php
        $sql = "SELECT * FROM products";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo '<div class="col-md-4 mb-4">';
                echo '<div class="card h-110">';
                echo '<br>';
                echo '<img src="' . $row['image_path'] . '" class="card-img-top w-50 m-auto h-50 img-hover" alt="' . $row['name'] . '" style="cursor: pointer;" onclick="document.getElementById(\'seeMoreBtn' . $row['id'] . '\').click()">';
                echo '<div class="card-body">';
                echo '<h5 class="card-title">' . $row['name'] . '</h5>';
                echo '<p class="card-text">' . $row['description'] . '</p>';
                
                echo '<button type="button" class="btn-23 mx-auto d-block" id="seeMoreBtn' . $row['id'] . '" data-bs-toggle="modal" data-bs-target="#productModal' . $row['id'] . '">';
                echo '<span class="text">Show More</span>';
                echo '<span aria-hidden="" class="marquee">View</span>';
                echo '</button>';

                echo '</div>';
                echo '<div class="card-footer bg-white">';
                echo '<div class="d-flex justify-content-between align-items-center">';
                echo '<span class="h5">R' . $row['price'] . '</span>';
                // Form submits to the same page
                echo '<form method="POST" action="products.php">';
                echo '<input type="hidden" name="product_id" value="' . $row['id'] . '">';
                echo '<button type="submit" name="add_to_cart" class="button">
                        <span>Add to cart</span>
                        <svg fill="#fff" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><g stroke-width="0" id="SVGRepo_bgCarrier"></g><g stroke-linejoin="round" stroke-linecap="round" id="SVGRepo_tracerCarrier"></g><g id="SVGRepo_iconCarrier"> <defs></defs> <g id="cart"> <circle r="1.91" cy="20.59" cx="10.07" class="cls-1"></circle> <circle r="1.91" cy="20.59" cx="18.66" class="cls-1"></circle> <path d="M.52,1.5H3.18a2.87,2.87,0,0,1,2.74,2L9.11,13.91H8.64A2.39,2.39,0,0,0,6.25,16.3h0a2.39,2.39,0,0,0,2.39,2.38h10" class="cls-1"></path> <polyline points="7.21 5.32 22.48 5.32 22.48 7.23 20.57 13.91 9.11 13.91" class="cls-1"></polyline> </g> </g></svg>
                      </button>';
                echo '</form>';
                echo '</div>';
                echo '</div>';
                echo '</div>';
                echo '</div>';
                
                // Modal for each product (unchanged)
                echo '<div class="modal fade" id="productModal' . $row['id'] . '" tabindex="-1" aria-labelledby="productModalLabel' . $row['id'] . '" aria-hidden="true">';
                echo '<div class="modal-dialog">';
                echo '<div class="modal-content">';
                echo '<div class="modal-header">';
                echo '<h5 class="modal-title" id="productModalLabel' . $row['id'] . '">' . $row['name'] . '</h5>';
                echo '<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
                echo '</div>';
                echo '<div class="modal-body">';
                echo '<img src="' . $row['image_path'] . '" class="img-fluid mb-3" alt="' . $row['name'] . '">';
                echo '<h6 class="text-center"><u>Description:</u></h6>';
                echo '<p class="card-text">' . $row['description'] . '</p>';
                echo '<hr>' ;
                echo '<h6 class="text-center"><u>Caffeine:</u></h6>';
                echo '<p class="small">' . $row['caffeine_mg'] . 'mg per can (' . $row['caffeine_source'] . ')</p>';
                echo '<hr>' ;
                echo '<h6 class="text-center"><u>Ingredients:</u></h6>';
                echo '<p class="small">' . $row['ingredients'] . '</p>';
                echo '<hr>' ;
                echo '<h6 class="text-center"><u>Benefits:</u></h6>';
                echo '<p>' . $row['benefits'] . '</p>';
                echo '</div>';
                echo '<div class="modal-footer">';
                echo '<button type="button" class="btn btn-dark m-auto" data-bs-dismiss="modal">Close</button>';
                echo '</div>';
                echo '</div>';
                echo '</div>';
                echo '</div>';
            }
        } else {
            echo '<div class="col-12"><p>No products found.</p></div>';
        }
        ?>
    </div>
</div>

<script>
// Auto-dismiss the success message after 0.5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alert = document.querySelector('.alert');
    if (alert) {
        setTimeout(() => {
            alert.style.display = 'none';
        }, 1000);
    }
});
</script>

<?php include 'includes/footer.php'; ?>

<style>
/* Your existing styles remain unchanged */
.card-img-top {
    transition: transform 0.3s ease;
}

.img-hover:hover {
    transform: scale(1.15);
    cursor: pointer;
}

/* From Uiverse.io by doniaskima */ 
.btn-23,
.btn-23 *,
.btn-23 :after,
.btn-23 :before,
.btn-23:after,
.btn-23:before {
  border: 0 solid;
  box-sizing: border-box;
}

.btn-23 {
  -webkit-tap-highlight-color: transparent;
  -webkit-appearance: button;
  background-color: #000;
  background-image: none;
  color: #fff;
  cursor: pointer;
  font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont,
    Segoe UI, Roboto, Helvetica Neue, Arial, Noto Sans, sans-serif,
    Apple Color Emoji, Segoe UI Emoji, Segoe UI Symbol, Noto Color Emoji;
  font-size: 80%;
  font-weight: 900;
  line-height: 0.20;
  -webkit-mask-image: -webkit-radial-gradient(#000, #fff);
  padding: 0;
  text-transform: uppercase;

}

.btn-23:disabled {
  cursor: default;
}

.btn-23:-moz-focusring {
  outline: auto;
}

.btn-23 svg {
  display: block;
  vertical-align: middle;
}

.btn-23 [hidden] {
  display: none;
}

.btn-23 {
  border-radius: 99rem;
  border-width: 2px;
  overflow: hidden;
  padding: 0.8rem 3rem;
  position: relative;
}

.btn-23 span {
  display: grid;
  inset: 0;
  place-items: center;
  position: absolute;
  transition: opacity 0.2s ease;
}

.btn-23 .marquee {
  --spacing: 5em;
  --start: 0em;
  --end: 5em;
  -webkit-animation: marquee 1s linear infinite;
  animation: marquee 1s linear infinite;
  -webkit-animation-play-state: paused;
  animation-play-state: paused;
  opacity: 0;
  position: relative;
  text-shadow: #fff var(--spacing) 0, #fff calc(var(--spacing) * -1) 0,
    #fff calc(var(--spacing) * -2) 0;
}

.btn-23:hover .marquee {
  -webkit-animation-play-state: running;
  animation-play-state: running;
  opacity: 1;
}

.btn-23:hover .text {
  opacity: 0;
}

@-webkit-keyframes marquee {
  0% {
    transform: translateX(var(--start));
  }

  to {
    transform: translateX(var(--end));
  }
}

@keyframes marquee {
  0% {
    transform: translateX(var(--start));
  }

  to {
    transform: translateX(var(--end));
  }
}

/* From Uiverse.io by Df12345677 */ 
/* Improved Add to Cart Button */
.button {
  height: 30px;
  width: 130px;
  background-color: black;
  border: none;
  color: white;
  transition: all 0.4s ease-in-out;
  font-size: 15px;
  border-radius: 50px;
  display: flex;
  justify-content: center;
  align-items: center;
  position: relative;
  overflow: hidden;
  padding: 0 15px;
  box-sizing: border-box;
  cursor: pointer;
}

.button span {
  transform: translateX(0);
  transition: transform 0.4s ease-in-out, opacity 0.3s ease;
  white-space: nowrap;
  position: absolute;
  left: 50%;
  transform: translateX(-50%);
}

.button svg {
  width: 20px;
  height: 20px;
  opacity: 0;
  transform: translateX(30px);
  transition: all 0.4s ease-in-out;
  position: absolute;
  right: 15px;
}

.button:hover {
  width: 60px;
  padding: 0 10px;
  background-color: black;
}

.button:hover span {
  transform: translateX(-100%);
  opacity: 0;
}

.button:hover svg {
  transform: translateX(0);
  opacity: 1;
  right: 20px;
}

.card-footer .d-flex {
  min-height: 40px; 
}
</style>
