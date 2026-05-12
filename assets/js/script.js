// AutoMarket Pro - JavaScript Functionality

// Theme Toggle
function initThemeToggle() {
    const themeToggle = document.getElementById('theme-toggle');
    const currentTheme = localStorage.getItem('theme') || 'dark';
    
    document.documentElement.setAttribute('data-theme', currentTheme);
    updateThemeIcon(currentTheme);
    
    themeToggle.addEventListener('click', () => {
        const theme = document.documentElement.getAttribute('data-theme');
        const newTheme = theme === 'dark' ? 'light' : 'dark';
        
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        updateThemeIcon(newTheme);
    });
}

function updateThemeIcon(theme) {
    const icon = document.querySelector('#theme-toggle i');
    if (icon) {
        icon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
    }
}

// Navigation Scroll Effect
function initNavbarScroll() {
    const navbar = document.querySelector('.navbar');
    
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });
}

// Mobile Menu Toggle
function initMobileMenu() {
    const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
    const navMenu = document.querySelector('.nav-menu');
    
    if (mobileMenuToggle && navMenu) {
        mobileMenuToggle.addEventListener('click', () => {
            navMenu.classList.toggle('active');
            mobileMenuToggle.classList.toggle('active');
        });
    }
}

// Smooth Scroll for Anchor Links
function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

// Image Gallery
function initImageGallery() {
    const galleryImages = document.querySelectorAll('.gallery-image');
    const lightbox = document.getElementById('lightbox');
    const lightboxImage = document.getElementById('lightbox-image');
    const lightboxClose = document.getElementById('lightbox-close');
    
    galleryImages.forEach(image => {
        image.addEventListener('click', () => {
            lightboxImage.src = image.src;
            lightbox.classList.add('active');
        });
    });
    
    if (lightboxClose) {
        lightboxClose.addEventListener('click', () => {
            lightbox.classList.remove('active');
        });
    }
    
    if (lightbox) {
        lightbox.addEventListener('click', (e) => {
            if (e.target === lightbox) {
                lightbox.classList.remove('active');
            }
        });
    }
}

// Form Validation
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    let isValid = true;
    const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
    
    inputs.forEach(input => {
        if (!input.value.trim()) {
            isValid = false;
            input.classList.add('error');
        } else {
            input.classList.remove('error');
        }
    });
    
    return isValid;
}

// Show/Hide Password
function initPasswordToggles() {
    const passwordToggles = document.querySelectorAll('.password-toggle');
    
    passwordToggles.forEach(toggle => {
        toggle.addEventListener('click', () => {
            const input = toggle.previousElementSibling;
            const icon = toggle.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'fas fa-eye';
            }
        });
    });
}

// Toast Notifications
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.add('show');
    }, 100);
    
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 300);
    }, 3000);
}

// AJAX Request Helper
function ajaxRequest(url, method = 'GET', data = null) {
    return fetch(url, {
        method: method,
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: data ? JSON.stringify(data) : null
    })
    .then(response => response.json())
    .catch(error => {
        console.error('AJAX Error:', error);
        showToast('An error occurred. Please try again.', 'error');
    });
}

// Car Search and Filters
function initCarSearch() {
    const searchForm = document.getElementById('car-search-form');
    const filters = document.querySelectorAll('.filter-input, .filter-select');
    
    if (searchForm) {
        searchForm.addEventListener('submit', (e) => {
            e.preventDefault();
            performSearch();
        });
    }
    
    filters.forEach(filter => {
        filter.addEventListener('change', () => {
            if (searchForm) performSearch();
        });
    });
}

function performSearch() {
    const formData = new FormData(document.getElementById('car-search-form'));
    const params = new URLSearchParams(formData);
    
    // Show loading state
    const carGrid = document.querySelector('.car-grid');
    if (carGrid) {
        carGrid.innerHTML = '<div class="spinner"></div>';
    }
    
    // Perform search
    ajaxRequest(`api/search.php?${params.toString()}`)
        .then(data => {
            if (data.success) {
                updateCarGrid(data.cars);
            } else {
                showToast(data.message || 'Search failed', 'error');
            }
        });
}

function updateCarGrid(cars) {
    const carGrid = document.querySelector('.car-grid');
    if (!carGrid) return;
    
    if (cars.length === 0) {
        carGrid.innerHTML = '<p class="no-results">No cars found matching your criteria.</p>';
        return;
    }
    
    carGrid.innerHTML = cars.map(car => `
        <div class="car-card animate-fadeInUp">
            <div class="car-image">
                <img src="${car.images?.[0] || 'assets/images/default-car.jpg'}" alt="${car.title}">
                ${car.is_featured ? '<span class="car-badge">Featured</span>' : ''}
                <span class="car-price">$${car.price.toLocaleString()}</span>
            </div>
            <div class="car-details">
                <h3 class="car-title">${car.title}</h3>
                <div class="car-specs">
                    <span class="spec-item">
                        <i class="fas fa-calendar"></i> ${car.year}
                    </span>
                    <span class="spec-item">
                        <i class="fas fa-gas-pump"></i> ${car.fuel_type}
                    </span>
                    <span class="spec-item">
                        <i class="fas fa-cog"></i> ${car.transmission}
                    </span>
                    <span class="spec-item">
                        <i class="fas fa-tachometer-alt"></i> ${car.mileage || 'N/A'} km
                    </span>
                </div>
                <div class="car-location">
                    <i class="fas fa-map-marker-alt"></i> ${car.location}
                </div>
                <div class="car-actions">
                    <button class="glass-button" onclick="viewCar(${car.id})">View Details</button>
                    <button class="glass-button primary" onclick="contactSeller(${car.id})">Contact</button>
                </div>
            </div>
        </div>
    `).join('');
}

// Rental Booking
function initRentalBooking() {
    const pickupDate = document.getElementById('pickup-date');
    const returnDate = document.getElementById('return-date');
    const priceCalculator = document.getElementById('price-calculator');
    
    if (pickupDate && returnDate) {
        [pickupDate, returnDate].forEach(input => {
            input.addEventListener('change', calculateRentalPrice);
        });
    }
}

function calculateRentalPrice() {
    const pickupDate = document.getElementById('pickup-date')?.value;
    const returnDate = document.getElementById('return-date')?.value;
    const dailyRate = parseFloat(document.getElementById('daily-rate')?.value || 0);
    
    if (!pickupDate || !returnDate || !dailyRate) return;
    
    const days = Math.ceil((new Date(returnDate) - new Date(pickupDate)) / (1000 * 60 * 60 * 24));
    const totalPrice = days * dailyRate;
    
    const priceDisplay = document.getElementById('price-display');
    if (priceDisplay) {
        priceDisplay.textContent = `$${totalPrice.toLocaleString()}`;
    }
    
    const hiddenPrice = document.getElementById('total-price');
    if (hiddenPrice) {
        hiddenPrice.value = totalPrice;
    }
}

// Wishlist Management
function toggleWishlist(carId, type = 'sale') {
    const button = document.querySelector(`[data-wishlist-car="${carId}"]`);
    if (!button) return;
    
    const isAdded = button.classList.contains('added');
    
    ajaxRequest('api/wishlist.php', 'POST', {
        action: isAdded ? 'remove' : 'add',
        car_id: carId,
        type: type
    })
    .then(data => {
        if (data.success) {
            button.classList.toggle('added');
            button.querySelector('i').className = isAdded ? 'far fa-heart' : 'fas fa-heart';
            showToast(isAdded ? 'Removed from wishlist' : 'Added to wishlist');
        } else {
            showToast(data.message || 'Action failed', 'error');
        }
    });
}

// File Upload Preview
function initFileUpload() {
    const fileInputs = document.querySelectorAll('input[type="file"][accept="image/*"]');
    
    fileInputs.forEach(input => {
        input.addEventListener('change', (e) => {
            const files = Array.from(e.target.files);
            const preview = document.getElementById(`${input.id}-preview`);
            
            if (preview) {
                preview.innerHTML = '';
                files.forEach(file => {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'preview-image';
                        preview.appendChild(img);
                    };
                    reader.readAsDataURL(file);
                });
            }
        });
    });
}

// Multi-step Form
function initMultiStepForm() {
    const steps = document.querySelectorAll('.form-step');
    const nextButtons = document.querySelectorAll('.next-step');
    const prevButtons = document.querySelectorAll('.prev-step');
    const progressSteps = document.querySelectorAll('.progress-step');
    
    let currentStep = 0;
    
    function showStep(stepIndex) {
        steps.forEach((step, index) => {
            step.style.display = index === stepIndex ? 'block' : 'none';
        });
        
        progressSteps.forEach((progress, index) => {
            progress.classList.toggle('active', index <= stepIndex);
        });
        
        currentStep = stepIndex;
    }
    
    nextButtons.forEach(button => {
        button.addEventListener('click', () => {
            if (validateForm(`step-${currentStep}`)) {
                showStep(currentStep + 1);
            }
        });
    });
    
    prevButtons.forEach(button => {
        button.addEventListener('click', () => {
            showStep(currentStep - 1);
        });
    });
    
    showStep(0);
}

// Car Comparison
function initCarComparison() {
    const compareButtons = document.querySelectorAll('.compare-car');
    const compareList = document.getElementById('compare-list');
    
    compareButtons.forEach(button => {
        button.addEventListener('click', () => {
            const carId = button.dataset.carId;
            addToComparison(carId);
        });
    });
}

function addToComparison(carId) {
    let comparison = JSON.parse(localStorage.getItem('carComparison') || '[]');
    
    if (comparison.includes(carId)) {
        showToast('Car already in comparison', 'warning');
        return;
    }
    
    if (comparison.length >= 3) {
        showToast('Maximum 3 cars can be compared', 'warning');
        return;
    }
    
    comparison.push(carId);
    localStorage.setItem('carComparison', JSON.stringify(comparison));
    updateComparisonUI();
    showToast('Added to comparison');
}

function updateComparisonUI() {
    const comparison = JSON.parse(localStorage.getItem('carComparison') || '[]');
    const compareList = document.getElementById('compare-list');
    const compareCount = document.getElementById('compare-count');
    
    if (compareCount) {
        compareCount.textContent = comparison.length;
    }
    
    if (compareList && comparison.length > 0) {
        compareList.style.display = 'block';
    }
}

// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    initThemeToggle();
    initNavbarScroll();
    initMobileMenu();
    initSmoothScroll();
    initImageGallery();
    initPasswordToggles();
    initCarSearch();
    initRentalBooking();
    initFileUpload();
    initMultiStepForm();
    initCarComparison();
    updateComparisonUI();
    
    // Add fade-in animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-fadeInUp');
            }
        });
    }, observerOptions);
    
    document.querySelectorAll('.car-card, .glass-card').forEach(el => {
        observer.observe(el);
    });
});

// Utility Functions
function formatCurrency(amount, currency = 'USD') {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currency
    }).format(amount);
}

function formatDate(date, format = 'short') {
    const options = format === 'short' 
        ? { year: 'numeric', month: 'short', day: 'numeric' }
        : { year: 'numeric', month: 'long', day: 'numeric' };
    
    return new Date(date).toLocaleDateString('en-US', options);
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Export functions for use in HTML
window.viewCar = (carId) => {
    window.location.href = `car-details.php?id=${carId}`;
};

window.contactSeller = (carId) => {
    window.location.href = `contact-seller.php?car_id=${carId}`;
};

window.toggleWishlist = toggleWishlist;
window.showToast = showToast;
window.validateForm = validateForm;
