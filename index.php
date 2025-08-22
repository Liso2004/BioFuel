<?php include 'includes/header.php'; ?>
<?php include 'includes/config.php'; ?>

<style>
    body {
        background-color: #ffffffff;
    }
    .slider {
        width: auto;
        height: 700px;
        position: relative;
        overflow: hidden;
        perspective: 1000px;
    }
    
    .slide-track {
        width: 100%;
        height: 100%;
        display: flex;
        transition: transform 1.2s cubic-bezier(0.645, 0.045, 0.355, 1);
    }
    
    .slide {
        min-width: 100%;
        height: 100%;
        object-fit: fill;
        background-position: center;
        background-repeat: no-repeat;
        will-change: transform;
    }
    
    /* Individual slides - now we have 5 slides (original 4 + duplicate first) */
    .slide:nth-child(1) {
        background-image: url('./assets/images/ban.jpg');
    }
    
    .slide:nth-child(2) {
        background-image: url('./assets/images/ban1.jpg');
    }
    
    .slide:nth-child(3) {
        background-image: url('./assets/images/ban2.jpg');
    }
    
    .slide:nth-child(4) {
        background-image: url('./assets/images/ban3.jpg');
    }
    
    .slide:nth-child(5) {
        background-image: url('./assets/images/ban4.jpg');
    }

    .slide:nth-child(6) {
        background-image: url('./assets/images/ban5.jpg');
    }

    .slide:nth-child(7) {
        background-image: url('./assets/images/ban.jpg');
    }

    video {
        max-width: 100%;
        height: auto;
        border-radius: 10px;
        margin: 10px 0;
    }

    .video-container {
        position: relative;
        padding-bottom: 56.25%; /* 16:9 aspect ratio */
        height: 0;
        overflow: hidden;
    }

    .video-container video {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }

    #background-video {
        position: relative;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        width: 800px; 
        height: auto;
    }
</style>

<!-- Success Modal -->
<div class="modal fade" id="orderSuccessModal" tabindex="-1" aria-labelledby="orderSuccessModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderSuccessModalLabel">Order Successful!</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <?php if (isset($_GET['order_success']) && isset($_GET['order_id'])): ?>
                    <p>Your order has been placed successfully!</p>
                    <p>Thank you for choosing Biofuel!</p>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-dark" data-bs-dismiss="modal">Continue Shopping</button>
            </div>
        </div>
    </div>
</div>

<div class="slider">
    <div class="slide-track">
        <div class="slide"></div>
        <div class="slide"></div>
        <div class="slide"></div>
        <div class="slide"></div>
        <div class="slide"></div>
        <div class="slide"></div>
        <div class="slide"></div> <!-- Duplicate of first slide -->
    </div>
</div>

<script>
    const track = document.querySelector('.slide-track');
    const slides = document.querySelectorAll('.slide');
    let currentIndex = 0;
    const slideCount = slides.length;
    const transitionDuration = 1200; // Should match your CSS transition duration
    
    function nextSlide() {
        currentIndex++;
        track.style.transform = `translateX(-${currentIndex * 100}%)`;
        
        // When we reach the duplicate slide (last one), instantly reset to first slide
        if (currentIndex === slideCount - 1) {
            setTimeout(() => {
                track.style.transition = 'none';
                currentIndex = 0;
                track.style.transform = 'translateX(0)';
                // Force reflow to apply the change
                track.offsetHeight;
                // Restore transition
                track.style.transition = `transform ${transitionDuration/1200}s cubic-bezier(0.645, 0.045, 0.355, 1)`;
            }, transitionDuration);
        }
    }
    
    // Start auto-rotation
    let interval = setInterval(nextSlide, 3000);
    
    // Pause on hover
    track.addEventListener('mouseenter', () => clearInterval(interval));
    track.addEventListener('mouseleave', () => {
        // Reset position if we're at the clone when hovering
        if (currentIndex === slideCount - 1) {
            track.style.transition = 'none';
            currentIndex = 0;
            track.style.transform = 'translateX(0)';
            track.offsetHeight;
            track.style.transition = `transform ${transitionDuration/1000}s cubic-bezier(0.645, 0.045, 0.355, 1)`;
        }
        interval = setInterval(nextSlide, 3000);
    });

// Show success modal when page loads if there's a success message
document.addEventListener('DOMContentLoaded', function() {
    <?php if (isset($_GET['order_success']) && isset($_GET['order_id'])): ?>
        var orderSuccessModal = new bootstrap.Modal(document.getElementById('orderSuccessModal'));
        orderSuccessModal.show();
        
        // Remove the success parameters from URL without reloading
        if (window.history.replaceState) {
            const url = new URL(window.location);
            url.searchParams.delete('order_success');
            url.searchParams.delete('order_id');
            window.history.replaceState({}, '', url);
        }
    <?php endif; ?>
});
</script>

<!-- Rest of your content remains the same -->
<div class="container my-5">
    <div class="row">
        <div class="col-md-6">
            <h2>The Problem</h2>
            <p>Most energy drinks are loaded with sugar and artificial ingredients that cause jitters, energy crashes, and health concerns. People need energy but don't want the side effects.</p>
        </div>
        <div class="col-md-6">
            <h2>Our Solution</h2>
            <p>Biofuel delivers clean energy with 100% natural ingredients, no refined sugar, and functional botanicals for sustained focus without the crash.</p>
        </div>
    </div>
</div>

<div>
    <div>
        <h2 class="text-center mb-5">Our Flavours</h2>
        <video id="background-video" autoplay loop muted playsinline>
            <source src="assets\images\BLUE SURGE.mp4" type="video/mp4">
            Your browser does not support HTML5 video.
        </video>
    </div>
    <div class="text-center"><a href="products.php" class="btn btn-dark">View Products</a></div>
    <br>
</div>

<?php include 'includes/footer.php'; ?>