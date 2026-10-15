/**
 * Main JavaScript for Disaster Help System
 * Handles: Animations, Image Previews, Alerts, and Maps
 */

document.addEventListener("DOMContentLoaded", function() {
    
    // ==========================================
    // 1. AUTO-DISMISS ALERTS
    // ==========================================
    // Automatically closes success/error messages after 5 seconds
    setTimeout(function() {
        let alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            // Use Bootstrap's alert instance to close it smoothly
            let bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);


    // ==========================================
    // 2. IMAGE UPLOAD PREVIEW
    // ==========================================
    // Shows a thumbnail immediately when a user selects a file in 'raise_complaint.php'
    const fileInput = document.querySelector('input[type="file"]');
    
    if (fileInput) {
        fileInput.addEventListener('change', function(event) {
            const file = event.target.files[0];
            
            if (file) {
                // Check if preview container exists, if not create one
                let previewContainer = document.getElementById('image-preview');
                
                if (!previewContainer) {
                    previewContainer = document.createElement('div');
                    previewContainer.id = 'image-preview';
                    previewContainer.className = 'mt-3 text-center';
                    // Append it right after the file input
                    fileInput.parentNode.appendChild(previewContainer);
                }

                // Read the file and render image
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewContainer.innerHTML = `
                        <div class="card p-2 d-inline-block shadow-sm">
                            <img src="${e.target.result}" class="img-fluid rounded" 
                                 style="max-height: 200px; animation: fadeIn 0.5s ease;">
                            <div class="text-muted small mt-1">Image Preview</div>
                        </div>`;
                }
                reader.readAsDataURL(file);
            }
        });
    }


    // ==========================================
    // 3. SCROLL REVEAL ANIMATIONS
    // ==========================================
    // Adds a 'fade-in' effect as elements scroll into view
    
    const observerOptions = {
        threshold: 0.15, // Trigger when 15% of element is visible
        rootMargin: "0px 0px -50px 0px" // Offset slightly to look smoother
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // Add visible styles
                entry.target.style.opacity = "1";
                entry.target.style.transform = "translateY(0)";
                
                // Stop observing once animated
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    // Target specific elements for animation
    const animateElements = document.querySelectorAll('.card, .table-responsive, .alert');
    
    animateElements.forEach(el => {
        // Only apply JavaScript animation if it doesn't have the CSS initial load animation
        // This prevents double-animation on page load
        if(getComputedStyle(el).animationName === 'none') {
            el.style.opacity = "0";
            el.style.transform = "translateY(20px)";
            el.style.transition = "all 0.6s cubic-bezier(0.25, 0.8, 0.25, 1)";
            observer.observe(el);
        }
    });

});


// ==========================================
// 4. HELPER FUNCTIONS
// ==========================================

/**
 * Confirm Action Dialog
 * Used for Delete buttons or critical updates
 * Usage: <button onclick="return confirmAction('Delete this user?');">
 */
function confirmAction(message) {
    return confirm(message || "Are you sure you want to proceed?");
}

/**
 * Open Maps Link
 * Helper to open coordinates in Google Maps
 */
function openMap(lat, lng) {
    if(lat && lng) {
        window.open(`https://www.google.com/maps/search/?api=1&query=${lat},${lng}`, '_blank');
    } else {
        alert("Location data not available.");
    }
}