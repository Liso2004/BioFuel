<?php 
include 'includes/header.php';

// Start session (if not already started)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process form data here (you can add your email sending logic, etc.)
    
    // Set success message in session
    $_SESSION['success_message'] = "Thank you! Your message has been sent successfully. Thank you for your feedback. We'll get back to you shortly.";
    
    // Redirect to prevent form resubmission on refresh
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// Check for success message in session
$showSuccess = false;
if (isset($_SESSION['success_message'])) {
    $successMessage = $_SESSION['success_message'];
    $showSuccess = true;
    // Clear the message so it doesn't show again
    unset($_SESSION['success_message']);
}
?>

<div class="container my-5">
    <h1 class="text-center mb-5">Contact Us</h1>
    
    <!-- Success Modal -->
    <?php if ($showSuccess): ?>
        <div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="successModalLabel">Message sent successfully!</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <?php echo htmlspecialchars($successMessage); ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-dark" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var successModal = new bootstrap.Modal(document.getElementById('successModal'));
                successModal.show();
                
                // Optional: Auto-close after 5 seconds
                setTimeout(function() {
                    successModal.hide();
                }, 5000);
            });
        </script>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-6">
            <h2>Get in Touch</h2>
            <p>Have questions about our products or want to learn more? Reach out to us!</p>
            
            <div class="mt-4">
                <h5>Email</h5>
                <p>biofuel.capetown@gmail.com</p>
                
                <h5>Phone</h5>
                <p>+27 23-789-4567</p>
                
                <h5>Address</h5>
                <p>123 Wellness Way<br>Cape Town, 7760</p>
            </div>
        </div>
        
        <div class="col-md-6">
            <h2>Send Us a Message</h2>
            <form method="POST">
                <div class="mb-3">
                    <label for="name" class="form-label">Name</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="subject" class="form-label">Subject</label>
                    <input type="text" class="form-control" id="subject" name="subject" required>
                </div>
                <div class="mb-3">
                    <label for="message" class="form-label">Message</label>
                    <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                </div>
                <button type="submit" class="btn btn-dark">Send Message</button>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>