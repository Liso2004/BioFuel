<?php include 'includes/header.php'; ?>
<?php include 'includes/config.php'; ?>

<?php
// Redirect if cart is empty or user not logged in
if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit();
}

if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_to'] = 'checkout.php';
    header("Location: login.php");
    exit();
}

// Get user's details including email
$user_details = [];
$user_sql = "SELECT email FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->bind_param("i", $_SESSION['user_id']);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
if ($user_result->num_rows > 0) {
    $user_details = $user_result->fetch_assoc();
}

// Get user's saved address and payment info
$user_id = $_SESSION['user_id'];
$address = [];
$payment = [];

$address_sql = "SELECT * FROM user_addresses WHERE user_id = ? AND is_default = TRUE LIMIT 1";
$address_stmt = $conn->prepare($address_sql);
$address_stmt->bind_param("i", $user_id);
$address_stmt->execute();
$address_result = $address_stmt->get_result();
if ($address_result->num_rows > 0) {
    $address = $address_result->fetch_assoc();
}

$payment_sql = "SELECT * FROM user_payments WHERE user_id = ? AND is_default = TRUE LIMIT 1";
$payment_stmt = $conn->prepare($payment_sql);
$payment_stmt->bind_param("i", $user_id);
$payment_stmt->execute();
$payment_result = $payment_stmt->get_result();
if ($payment_result->num_rows > 0) {
    $payment = $payment_result->fetch_assoc();
}

// Process checkout
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate form data
    $required_fields = ['first_name', 'last_name', 'address', 'city', 'zip', 'email', 'phone'];
    $errors = [];
    
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . " is required.";
        }
    }
    
    // Validate email format
    if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }
    
    // Validate payment method
    if (!isset($_POST['payment']) || !in_array($_POST['payment'], ['visa', 'mastercard', 'paypal'])) {
        $errors[] = "Please select a valid payment method.";
    }
    
    // Validate credit card details if payment method is visa or mastercard
    if (in_array($_POST['payment'], ['visa', 'mastercard'])) {
        // Validate card number
        $cc_number = str_replace(' ', '', $_POST['cc_number']);
        if (empty($_POST['cc_number'])) {
            $errors[] = "Card number is required.";
        } elseif (!preg_match('/^[0-9]{13,16}$/', $cc_number)) {
            $errors[] = "Card number must be 13 to 16 digits.";
        } elseif ($_POST['payment'] == 'visa' && !preg_match('/^4/', $cc_number)) {
            $errors[] = "Visa card numbers must start with 4.";
        } elseif ($_POST['payment'] == 'mastercard' && !preg_match('/^5[1-5]/', $cc_number)) {
            $errors[] = "Mastercard numbers must start with 51 through 55.";
        }
        
        // Validate card name
        if (empty($_POST['cc_name'])) {
            $errors[] = "Name on card is required.";
        }
        
        // Validate expiration date
        if (empty($_POST['cc_expiration'])) {
            $errors[] = "Expiration date is required.";
        } elseif (!preg_match('/^(0[1-9]|1[0-2])\/?([0-9]{2})$/', $_POST['cc_expiration'], $matches)) {
            $errors[] = "Expiration date must be in MM/YY format.";
        } else {
            $exp_month = $matches[1];
            $exp_year = '20' . $matches[2];
            $current_month = date('m');
            $current_year = date('Y');
            
            // Convert to DateTime objects for accurate comparison
            $exp_date = DateTime::createFromFormat('Y-m', "$exp_year-$exp_month");
            $current_date = DateTime::createFromFormat('Y-m', "$current_year-$current_month");
            
            if ($exp_date < $current_date) {
                $errors[] = "Card has expired. Please use a future date.";
            }
        }
        
        // Validate CVV
        if (empty($_POST['cc_cvv'])) {
            $errors[] = "CVV is required.";
        } elseif (!preg_match('/^[0-9]{3,4}$/', $_POST['cc_cvv'])) {
            $errors[] = "CVV must be 3 or 4 digits.";
        }
    }
    
    // Validate PayPal email if payment method is PayPal
    if ($_POST['payment'] == 'paypal') {
        if (empty($_POST['paypal_email'])) {
            $errors[] = "PayPal email is required.";
        } elseif (!filter_var($_POST['paypal_email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Please enter a valid PayPal email address.";
        }
}
    
    if (!empty($errors)) {
        $_SESSION['checkout_errors'] = $errors;
        header("Location: checkout.php");
        exit();
    }
    
    // Save/update address information
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $address_text = $_POST['address'];
    $city = $_POST['city'];
    $zip = $_POST['zip'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    
    if (!empty($address)) {
        // Update existing address
        $sql = "UPDATE user_addresses SET 
                first_name = ?, 
                last_name = ?, 
                address = ?, 
                city = ?, 
                zip = ?, 
                phone = ?
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssi", $first_name, $last_name, $address_text, $city, $zip, $phone, $address['id']);
    } else {
        // Insert new address
        $sql = "INSERT INTO user_addresses 
                (user_id, first_name, last_name, address, city, zip, phone, is_default) 
                VALUES (?, ?, ?, ?, ?, ?, ?, TRUE)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issssss", $user_id, $first_name, $last_name, $address_text, $city, $zip, $phone);
    }
    $stmt->execute();
    
    // Save/update payment information
    $payment_type = $_POST['payment'];
    
    if (!empty($payment)) {
        // Update existing payment
        if (in_array($payment_type, ['visa', 'mastercard'])) {
            $cc_name = $_POST['cc_name'];
            $cc_number = $_POST['cc_number'];
            $cc_expiration = $_POST['cc_expiration'];
            $cc_cvv = $_POST['cc_cvv'];
            
            $sql = "UPDATE user_payments SET 
                    payment_type = ?, 
                    cc_name = ?, 
                    cc_number = ?, 
                    cc_expiration = ?, 
                    cc_cvv = ?,
                    paypal_email = NULL
                    WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssi", $payment_type, $cc_name, $cc_number, $cc_expiration, $cc_cvv, $payment['id']);
        } else {
            // PayPal
$paypal_email = $_POST['paypal_email'];
    $paypal_username = $_POST['paypal_username'] ?? ''; // Add this line
    
    $sql = "UPDATE user_payments SET 
            payment_type = ?, 
            paypal_email = ?,
            paypal_username = ?, 
            cc_name = NULL, 
            cc_number = NULL, 
            cc_expiration = NULL, 
            cc_cvv = NULL
            WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $payment_type, $paypal_email, $paypal_username, $payment['id']);
        }
} else {
        // Insert new payment
        if (in_array($payment_type, ['visa', 'mastercard'])) {
            $cc_name = $_POST['cc_name'];
            $cc_number = $_POST['cc_number'];
            $cc_expiration = $_POST['cc_expiration'];
            $cc_cvv = $_POST['cc_cvv'];
            
            $sql = "INSERT INTO user_payments 
                    (user_id, payment_type, cc_name, cc_number, cc_expiration, cc_cvv, is_default) 
                    VALUES (?, ?, ?, ?, ?, ?, TRUE)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isssss", $user_id, $payment_type, $cc_name, $cc_number, $cc_expiration, $cc_cvv);
        }
        else {
        // PayPal
        $paypal_email = $_POST['paypal_email'];
        $paypal_username = $_POST['paypal_username'] ?? '';
        
        $sql = "INSERT INTO user_payments 
                (user_id, payment_type, paypal_email, paypal_username, is_default) 
                VALUES (?, ?, ?, ?, TRUE)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isss", $user_id, $payment_type, $paypal_email, $paypal_username); 
        }
    }
    $stmt->execute();
    
    // Rest of the checkout process (create order, etc.)
    // Calculate total
    $total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    
    // Add shipping cost
    $shipping_cost = ($subtotal < 50) ? 20 : 0;
    $total += $shipping_cost;
    
    // Create order
    $payment_method = $_POST['payment'];
    $sql = "INSERT INTO orders (user_id, total, payment_method) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ids", $user_id, $total, $payment_method);
    $stmt->execute();
    $order_id = $stmt->insert_id;

    
    // Add order items
    foreach ($_SESSION['cart'] as $item) {
        $product_id = $item['id'];
        $quantity = $item['quantity'];
        $price = $item['price'];
        
        $sql = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiid", $order_id, $product_id, $quantity, $price);
        $stmt->execute();
    }
    echo '<script>sessionStorage.setItem("cartCount", "0");</script>';
    // Clear cart
    unset($_SESSION['cart']);
    
    // Update database cart
    saveUserCart($_SESSION['user_id'], [], $conn);
    
    // Set success message
    header("Location: index.php?order_success=1&order_id=$order_id");
    exit();
}
?>

<div class="container my-5">
    <h1 class="text-center mb-5">Checkout</h1>
    
    <?php if (isset($_SESSION['checkout_errors'])): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach ($_SESSION['checkout_errors'] as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php unset($_SESSION['checkout_errors']); ?>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-8">
            <form method="POST" action="checkout.php" id="checkoutForm">
                <div class="card mb-4">
                    <div class="card-header">
                        <h4>Shipping Information</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">First Name</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" 
                                       value="<?= !empty($address) ? htmlspecialchars($address['first_name']) : '' ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Last Name</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" 
                                       value="<?= !empty($address) ? htmlspecialchars($address['last_name']) : '' ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <input type="text" class="form-control" id="address" name="address" 
                                   value="<?= !empty($address) ? htmlspecialchars($address['address']) : '' ?>" required>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control" id="city" name="city" 
                                       value="<?= !empty($address) ? htmlspecialchars($address['city']) : '' ?>" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="zip" class="form-label">Zip Code</label>
                                <input type="text" class="form-control" id="zip" name="zip" 
                                       value="<?= !empty($address) ? htmlspecialchars($address['zip']) : '' ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= isset($user_details['email']) ? htmlspecialchars($user_details['email']) : '' ?>" 
                                   required>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?= !empty($address) ? htmlspecialchars($address['phone']) : '' ?>" 
                                   required maxlength="10" pattern="[0-9]{10}" 
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);">
                        </div>
                    </div>
                </div>
                
                <div class="card mb-5"> <!-- Added mb-5 for bottom margin -->
                    <div class="card-header">
                        <h4>Payment Method</h4>
                    </div>
                    <div class="card-body">
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="payment" id="visa" value="visa" 
                                   <?= (!empty($payment) && in_array($payment['payment_type'], ['visa', 'credit'])) ? 'checked' : 'checked' ?>>
                            <label class="form-check-label" for="visa">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5e/Visa_Inc._logo.svg/2560px-Visa_Inc._logo.svg.png" alt="Visa" style="height: 20px; margin-left: 10px;">
                            </label>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="payment" id="mastercard" value="mastercard"
                                   <?= (!empty($payment) && in_array($payment['payment_type'], ['mastercard', 'credit'])) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="mastercard">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/2a/Mastercard-logo.svg/1280px-Mastercard-logo.svg.png" alt="Mastercard" style="height: 20px; margin-left: 10px;">
                            </label>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="radio" name="payment" id="paypal" value="paypal"
                                   <?= (!empty($payment) && $payment['payment_type'] == 'paypal') ? 'checked' : '' ?>>
                            <label class="form-check-label" for="paypal">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/b/b5/PayPal.svg/2560px-PayPal.svg.png" alt="PayPal" style="height: 20px; margin-left: 10px;">
                            </label>
                        </div>
                        
                        <div id="creditCardForm" style="display: none;">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="cc_name" class="form-label">Name on Card</label>
                                    <input type="text" class="form-control" id="cc_name" name="cc_name" 
                                           value="<?= (!empty($payment) && in_array($payment['payment_type'], ['visa', 'mastercard', 'credit'])) ? htmlspecialchars($payment['cc_name']) : '' ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="cc_number" class="form-label">Card Number</label>
                                    <input type="text" class="form-control" id="cc_number" name="cc_number" 
                                           value="<?= (!empty($payment) && in_array($payment['payment_type'], ['visa', 'mastercard', 'credit'])) ? htmlspecialchars($payment['cc_number']) : '' ?>" 
                                           placeholder="1234 5678 9012 3456" maxlength="19" oninput="formatCardNumber(this)">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="cc_expiration" class="form-label">Expiration (MM/YY)</label>
                                    <input type="text" class="form-control" id="cc_expiration" name="cc_expiration" placeholder="MM/YY" 
                                           value="<?= (!empty($payment) && in_array($payment['payment_type'], ['visa', 'mastercard', 'credit'])) ? htmlspecialchars($payment['cc_expiration']) : '' ?>" 
                                           maxlength="5" oninput="formatExpiration(this)">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="cc_cvv" class="form-label">CVV</label>
                                    <input type="text" class="form-control" id="cc_cvv" name="cc_cvv" 
                                           value="<?= (!empty($payment) && in_array($payment['payment_type'], ['visa', 'mastercard', 'credit'])) ? htmlspecialchars($payment['cc_cvv']) : '' ?>" 
                                           placeholder="123" maxlength="4" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                </div>
                            </div>
                        </div>
                        
                        <div id="paypalForm" style="display: none;">
                            <div class="mb-3"> <!-- Add this new field -->
                                <label for="paypal_username" class="form-label">PayPal Username</label>
                                <input type="text" class="form-control" id="paypal_username" name="paypal_username" 
                                    value="<?= (!empty($payment) && $payment['payment_type'] == 'paypal') ? htmlspecialchars($payment['paypal_username'] ?? '') : '' ?>" 
                                    placeholder="PayPal username">
                            </div>
                            <div class="mb-3">
                                <label for="paypal_email" class="form-label">PayPal Email</label>
                                <input type="email" class="form-control" id="paypal_email" name="paypal_email" 
                                    value="<?= (!empty($payment) && $payment['payment_type'] == 'paypal') ? htmlspecialchars($payment['paypal_email']) : '' ?>" 
                                    placeholder="PayPal email address" required>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
        
        <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h4>Order Summary</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <tbody>
                                    <?php
                                    $subtotal = 0;
                                    foreach ($_SESSION['cart'] as $item):
                                        $item_total = $item['price'] * $item['quantity'];
                                        $subtotal += $item_total;
                                    ?>
                                        <tr>
                                            <td><?= htmlspecialchars($item['name']) ?> × <?= htmlspecialchars($item['quantity']) ?></td>
                                            <td class="text-end">R<?= number_format($item_total, 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <tr>
                                        <td>Subtotal</td>
                                        <td class="text-end">R<?= number_format($subtotal, 2) ?></td>
                                    </tr>
                                    <tr>
                                        <td>Shipping</td>
                                        <td class="text-end">
                                            <?php 
                                            $shipping_cost = ($subtotal < 50) ? 20 : 0;
                                            echo ($shipping_cost > 0) ? 'R' . number_format($shipping_cost, 2) : 'FREE';
                                            ?>
                                        </td>
                                    </tr>
                                    <tr class="fw-bold">
                                        <td>Total</td>
                                        <td class="text-end">R<?= number_format($subtotal + $shipping_cost, 2) ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <button type="submit" class="btn btn-dark w-100 mt-3" name="place_order">Place Order</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
// Show/hide payment forms based on selected payment method
function togglePaymentForms() {
    const paymentMethod = document.querySelector('input[name="payment"]:checked').value;
    const creditCardForm = document.getElementById('creditCardForm');
    const paypalForm = document.getElementById('paypalForm');
    
    if (paymentMethod === 'visa' || paymentMethod === 'mastercard') {
        creditCardForm.style.display = 'block';
        paypalForm.style.display = 'none';
        
        // Set required fields for credit card
        document.getElementById('cc_name').setAttribute('required', 'required');
        document.getElementById('cc_number').setAttribute('required', 'required');
        document.getElementById('cc_expiration').setAttribute('required', 'required');
        document.getElementById('cc_cvv').setAttribute('required', 'required');
        document.getElementById('paypal_email').removeAttribute('required');
    } else if (paymentMethod === 'paypal') {
        creditCardForm.style.display = 'none';
        paypalForm.style.display = 'block';
        
        // Set required fields for PayPal
        document.getElementById('paypal_email').setAttribute('required', 'required');
        document.getElementById('cc_name').removeAttribute('required');
        document.getElementById('cc_number').removeAttribute('required');
        document.getElementById('cc_expiration').removeAttribute('required');
        document.getElementById('cc_cvv').removeAttribute('required');
    }
}

// Initialize payment form visibility
document.addEventListener('DOMContentLoaded', function() {
    togglePaymentForms();
    
    // Add event listeners to payment method radio buttons
    document.querySelectorAll('input[name="payment"]').forEach(radio => {
        radio.addEventListener('change', togglePaymentForms);
    });
});

// Format card number with spaces (e.g., 1234 5678 9012 3456)
function formatCardNumber(input) {
    // Remove all non-digit characters
    let value = input.value.replace(/\D/g, '');
    
    // Add space after every 4 digits
    value = value.replace(/(\d{4})(?=\d)/g, '$1 ');
    
    // Update the input value
    input.value = value.substring(0, 19); // Limit to 16 digits + 3 spaces
}

// Format expiration date as MM/YY
function formatExpiration(input) {
    let value = input.value.replace(/\D/g, '');
    
    if (value.length > 2) {
        value = value.substring(0, 2) + '/' + value.substring(2, 4);
    }
    
    input.value = value.substring(0, 5); // Limit to MM/YY format
    
    // Validate month
    if (value.length >= 2) {
        const month = parseInt(value.substring(0, 2), 10);
        if (month < 1 || month > 12) {
            input.setCustomValidity('Please enter a valid month (01-12)');
            return;
        }
    }
    
    // Validate future date
    if (value.length === 5) {
        const currentDate = new Date();
        const currentYear = currentDate.getFullYear() % 100;
        const currentMonth = currentDate.getMonth() + 1;
        
        const inputMonth = parseInt(value.substring(0, 2), 10);
        const inputYear = parseInt(value.substring(3, 5), 10);
        
        if (inputYear < currentYear || (inputYear === currentYear && inputMonth < currentMonth)) {
            // Show error message using the same alert system as other errors
            showExpirationError();
            input.setCustomValidity('Card has expired. Please use a future date.');
            return;
        }
    }
    
    input.setCustomValidity('');
}

// Function to show expiration error using the same style as other error messages
function showExpirationError() {
    // Check if there's already an error alert
    let alertContainer = document.querySelector('.alert.alert-danger');
    
    if (!alertContainer) {
        // Create a new alert container if none exists
        alertContainer = document.createElement('div');
        alertContainer.className = 'alert alert-danger';
        alertContainer.innerHTML = '<ul class="mb-0" id="errorList"></ul>';
        
        // Insert the alert at the top of the container
        const container = document.querySelector('.container.my-5');
        container.insertBefore(alertContainer, container.firstChild);
    }
    
    // Find or create the error list
    let errorList = document.getElementById('errorList');
    if (!errorList) {
        errorList = document.createElement('ul');
        errorList.className = 'mb-0';
        errorList.id = 'errorList';
        alertContainer.appendChild(errorList);
    }
    
    // Check if expiration error already exists
    const existingErrors = errorList.querySelectorAll('li');
    let hasExpirationError = false;
    
    for (let i = 0; i < existingErrors.length; i++) {
        if (existingErrors[i].textContent.includes('expired')) {
            hasExpirationError = true;
            break;
        }
    }
    
    // Add expiration error if it doesn't exist
    if (!hasExpirationError) {
        const errorItem = document.createElement('li');
        errorItem.textContent = 'Card has expired. Please use a future date.';
        errorList.appendChild(errorItem);
    }
}

// Form submission validation
document.getElementById('checkoutForm').addEventListener('submit', function(e) {
    const paymentMethod = document.querySelector('input[name="payment"]:checked').value;
    
    if (paymentMethod === 'visa' || paymentMethod === 'mastercard') {
        const ccNumber = document.getElementById('cc_number').value.replace(/\s/g, '');
        if (ccNumber.length !== 16) {
            // Show error using the same style
            showCustomError('Please enter a valid 16-digit card number');
            e.preventDefault();
            return false;
        }
        
        const ccExp = document.getElementById('cc_expiration').value;
        if (!/^\d{2}\/\d{2}$/.test(ccExp)) {
            showCustomError('Please enter expiration date in MM/YY format');
            e.preventDefault();
            return false;
        }
        
        // Additional expiration date validation
        if (/^\d{2}\/\d{2}$/.test(ccExp)) {
            const currentDate = new Date();
            const currentYear = currentDate.getFullYear() % 100;
            const currentMonth = currentDate.getMonth() + 1;
            
            const inputMonth = parseInt(ccExp.substring(0, 2), 10);
            const inputYear = parseInt(ccExp.substring(3, 5), 10);
            
            if (inputYear < currentYear || (inputYear === currentYear && inputMonth < currentMonth)) {
                showExpirationError();
                e.preventDefault();
                return false;
            }
        }
        
        const ccCvv = document.getElementById('cc_cvv').value;
        if (ccCvv.length < 3 || ccCvv.length > 4) {
            showCustomError('Please enter a valid CVV (3-4 digits)');
            e.preventDefault();
            return false;
        }
    } else if (paymentMethod === 'paypal') {
        const paypalEmail = document.getElementById('paypal_email').value;
        if (!paypalEmail || !isValidEmail(paypalEmail)) {
            showCustomError('Please enter a valid PayPal email address');
            e.preventDefault();
            return false;
        }
    }
});

// Email validation function
function isValidEmail(email) {
    const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email).toLowerCase());
}

// Function to show custom error messages using the same style
function showCustomError(message) {
    // Check if there's already an error alert
    let alertContainer = document.querySelector('.alert.alert-danger');
    
    if (!alertContainer) {
        // Create a new alert container if none exists
        alertContainer = document.createElement('div');
        alertContainer.className = 'alert alert-danger';
        alertContainer.innerHTML = '<ul class="mb-0" id="errorList"></ul>';
        
        // Insert the alert at the top of the container
        const container = document.querySelector('.container.my-5');
        container.insertBefore(alertContainer, container.firstChild);
    }
    
    // Find or create the error list
    let errorList = document.getElementById('errorList');
    if (!errorList) {
        errorList = document.createElement('ul');
        errorList.className = 'mb-0';
        errorList.id = 'errorList';
        alertContainer.appendChild(errorList);
    }
    
    // Add the error message
    const errorItem = document.createElement('li');
    errorItem.textContent = message;
    errorList.appendChild(errorItem);
    
    // Scroll to the error message
    alertContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
}
</script>

<?php include 'includes/footer.php'; ?>