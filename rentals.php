<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$db = new Database();
$pdo = $db->getConnection();

// Get filters from URL
$filters = [
    'brand' => $_GET['brand'] ?? '',
    'location' => $_GET['location'] ?? '',
    'pickup_date' => $_GET['pickup_date'] ?? '',
    'return_date' => $_GET['return_date'] ?? ''
];

// Get rental cars based on filters
$rental_cars = searchRentalCars($pdo, $filters);

// Get brands for filter dropdown
$brands = getCarBrands($pdo);

// Pagination
$page = $_GET['page'] ?? 1;
$per_page = 12;
$total_cars = count($rental_cars);
$total_pages = ceil($total_cars / $per_page);
$offset = ($page - 1) * $per_page;
$rental_cars = array_slice($rental_cars, $offset, $per_page);
?>

<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Rentals - AutoMarket Pro</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/additional.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo">
                <i class="fas fa-car"></i>
                AutoMarket Pro
            </a>
            
            <ul class="nav-menu">
                <li><a href="index.php" class="nav-link">Home</a></li>
                <li><a href="browse-cars.php" class="nav-link">Buy Cars</a></li>
                <li><a href="rentals.php" class="nav-link active">Rentals</a></li>
                <li><a href="sell-car.php" class="nav-link">Sell Car</a></li>
                <li><a href="about.php" class="nav-link">About</a></li>
                <li><a href="contact.php" class="nav-link">Contact</a></li>
            </ul>
            
            <div class="nav-actions">
                <button class="theme-toggle" id="theme-toggle">
                    <i class="fas fa-moon"></i>
                </button>
                
                <?php if (isLoggedIn()): ?>
                    <a href="dashboard.php" class="glass-button">
                        <i class="fas fa-user"></i> Dashboard
                    </a>
                    <a href="logout.php" class="glass-button">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                <?php else: ?>
                    <a href="login.php" class="glass-button">Login</a>
                    <a href="register.php" class="glass-button primary">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <h1>Car Rentals</h1>
            <p>Rent premium vehicles for your journeys - flexible terms, competitive rates</p>
        </div>
    </section>

    <!-- Main Content -->
    <section class="section">
        <div class="container">
            <div class="browse-layout">
                <!-- Filters Sidebar -->
                <aside class="filters-sidebar">
                    <div class="glass-card">
                        <div class="filters-header">
                            <h3>Rental Filters</h3>
                            <button class="clear-filters" onclick="clearFilters()">Clear All</button>
                        </div>
                        
                        <form id="rental-search-form" method="GET" class="filters-form">
                            <!-- Brand -->
                            <div class="filter-group">
                                <label class="filter-label">Brand</label>
                                <select name="brand" class="filter-select">
                                    <option value="">All Brands</option>
                                    <?php foreach ($brands as $brand): ?>
                                        <option value="<?php echo $brand['id']; ?>" <?php echo ($filters['brand'] == $brand['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($brand['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Location -->
                            <div class="filter-group">
                                <label class="filter-label">Location</label>
                                <input type="text" name="location" class="filter-input" placeholder="Enter location" 
                                       value="<?php echo htmlspecialchars($filters['location']); ?>">
                            </div>
                            
                            <!-- Rental Dates -->
                            <div class="filter-group">
                                <label class="filter-label">Pickup Date</label>
                                <input type="date" name="pickup_date" class="filter-input" 
                                       value="<?php echo htmlspecialchars($filters['pickup_date']); ?>"
                                       min="<?php echo date('Y-m-d'); ?>">
                            </div>
                            
                            <div class="filter-group">
                                <label class="filter-label">Return Date</label>
                                <input type="date" name="return_date" class="filter-input" 
                                       value="<?php echo htmlspecialchars($filters['return_date']); ?>"
                                       min="<?php echo date('Y-m-d'); ?>">
                            </div>
                            
                            <button type="submit" class="glass-button primary">Search Rentals</button>
                        </form>
                    </div>
                </aside>

                <!-- Rentals Grid -->
                <main class="cars-main">
                    <!-- Results Header -->
                    <div class="results-header">
                        <div class="results-info">
                            <h2>Available Rentals</h2>
                            <p>Showing <?php echo count($rental_cars); ?> of <?php echo $total_cars; ?> cars</p>
                        </div>
                        
                        <div class="results-controls">
                            <div class="sort-dropdown">
                                <select class="filter-select" onchange="sortRentals(this.value)">
                                    <option value="price_low">Price: Low to High</option>
                                    <option value="price_high">Price: High to Low</option>
                                    <option value="newest">Newest First</option>
                                    <option value="rating">Highest Rated</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Rentals Grid -->
                    <div class="car-grid">
                        <?php if (empty($rental_cars)): ?>
                            <div class="no-results glass-card">
                                <i class="fas fa-car"></i>
                                <h3>No rental cars found</h3>
                                <p>Try adjusting your filters or search criteria</p>
                                <button class="glass-button primary" onclick="clearFilters()">Clear Filters</button>
                            </div>
                        <?php else: ?>
                            <?php foreach ($rental_cars as $car): ?>
                                <div class="car-card rental-card animate-fadeInUp">
                                    <div class="car-image">
                                        <img src="<?php echo $car['images'] ? json_decode($car['images'])[0] : 'assets/images/default-car.jpg'; ?>" 
                                             alt="<?php echo htmlspecialchars($car['title']); ?>">
                                        
                                        <span class="car-badge">Available</span>
                                        <span class="car-price">$<?php echo number_format($car['daily_rate'], 2); ?>/day</span>
                                        
                                        <?php if ($car['weekly_rate']): ?>
                                            <span class="weekly-rate">$<?php echo number_format($car['weekly_rate'], 2); ?>/week</span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="car-details">
                                        <h3 class="car-title"><?php echo htmlspecialchars($car['title']); ?></h3>
                                        
                                        <div class="car-specs">
                                            <span class="spec-item">
                                                <i class="fas fa-calendar"></i> <?php echo $car['year']; ?>
                                            </span>
                                            <span class="spec-item">
                                                <i class="fas fa-gas-pump"></i> <?php echo ucfirst($car['fuel_type']); ?>
                                            </span>
                                            <span class="spec-item">
                                                <i class="fas fa-cog"></i> <?php echo ucfirst($car['transmission']); ?>
                                            </span>
                                            <span class="spec-item">
                                                <i class="fas fa-tachometer-alt"></i> <?php echo number_format($car['mileage'] ?? 0); ?> km
                                            </span>
                                        </div>
                                        
                                        <div class="rental-info">
                                            <div class="rental-specs">
                                                <span class="rental-spec">
                                                    <i class="fas fa-dollar-sign"></i>
                                                    Daily: $<?php echo number_format($car['daily_rate'], 2); ?>
                                                </span>
                                                <?php if ($car['weekly_rate']): ?>
                                                    <span class="rental-spec">
                                                        <i class="fas fa-calendar-week"></i>
                                                        Weekly: $<?php echo number_format($car['weekly_rate'], 2); ?>
                                                    </span>
                                                <?php endif; ?>
                                                <?php if ($car['monthly_rate']): ?>
                                                    <span class="rental-spec">
                                                        <i class="fas fa-calendar-alt"></i>
                                                        Monthly: $<?php echo number_format($car['monthly_rate'], 2); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <?php if ($car['security_deposit']): ?>
                                                <div class="security-deposit">
                                                    <i class="fas fa-shield-alt"></i>
                                                    Security Deposit: $<?php echo number_format($car['security_deposit'], 2); ?>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="min-rental">
                                                <i class="fas fa-clock"></i>
                                                Minimum rental: <?php echo $car['min_rental_days']; ?> day(s)
                                            </div>
                                        </div>
                                        
                                        <div class="car-location">
                                            <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($car['location']); ?>
                                        </div>
                                        
                                        <div class="car-meta">
                                            <span class="car-brand"><?php echo htmlspecialchars($car['brand_name']); ?></span>
                                            <span class="owner">Owner: <?php echo htmlspecialchars($car['owner_name']); ?></span>
                                        </div>
                                        
                                        <div class="car-actions">
                                            <button class="glass-button" onclick="viewRentalCar(<?php echo $car['id']; ?>)">View Details</button>
                                            <button class="glass-button primary" onclick="bookRental(<?php echo $car['id']; ?>)">Book Now</button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" class="page-link">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <?php if ($i == $page): ?>
                                    <span class="page-link active"><?php echo $i; ?></span>
                                <?php elseif (abs($i - $page) <= 2 || $i == 1 || $i == $total_pages): ?>
                                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" class="page-link">
                                        <?php echo $i; ?>
                                    </a>
                                <?php elseif (abs($i - $page) == 3): ?>
                                    <span class="page-ellipsis">...</span>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" class="page-link">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </main>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; 2024 AutoMarket Pro. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="assets/js/script.js"></script>
    
    <script>
        // Clear all filters
        function clearFilters() {
            window.location.href = 'rentals.php';
        }
        
        // Sort rentals
        function sortRentals(sortBy) {
            const url = new URL(window.location);
            url.searchParams.set('sort', sortBy);
            window.location.href = url.toString();
        }
        
        // View rental car details
        function viewRentalCar(carId) {
            window.location.href = `rental-details.php?id=${carId}`;
        }
        
        // Book rental
        function bookRental(carId) {
            <?php if (isLoggedIn()): ?>
                window.location.href = `booking.php?car_id=${carId}`;
            <?php else: ?>
                if (confirm('You need to login to book a rental. Would you like to login now?')) {
                    window.location.href = `login.php?redirect=booking.php?car_id=${carId}`;
                }
            <?php endif; ?>
        }
        
        // Date validation
        document.addEventListener('DOMContentLoaded', function() {
            const pickupDate = document.querySelector('input[name="pickup_date"]');
            const returnDate = document.querySelector('input[name="return_date"]');
            
            if (pickupDate && returnDate) {
                pickupDate.addEventListener('change', function() {
                    returnDate.min = this.value;
                    if (returnDate.value && returnDate.value < this.value) {
                        returnDate.value = this.value;
                    }
                });
                
                returnDate.addEventListener('change', function() {
                    if (pickupDate.value && this.value < pickupDate.value) {
                        this.value = pickupDate.value;
                    }
                });
            }
        });
    </script>
    
    <style>
        .rental-card .weekly-rate {
            position: absolute;
            bottom: 1rem;
            right: 1rem;
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            padding: 0.25rem 0.75rem;
            border-radius: 10px;
            font-size: 0.8rem;
            color: var(--text-secondary);
        }
        
        .rental-info {
            margin: 1rem 0;
            padding: 1rem;
            background: var(--bg-tertiary);
            border-radius: 10px;
        }
        
        .rental-specs {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-bottom: 0.75rem;
        }
        
        .rental-spec {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            color: var(--text-secondary);
        }
        
        .rental-spec i {
            color: var(--secondary-color);
        }
        
        .security-deposit {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
        }
        
        .security-deposit i {
            color: var(--secondary-color);
        }
        
        .min-rental {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            color: var(--text-secondary);
            font-style: italic;
        }
        
        .min-rental i {
            color: var(--accent-color);
        }
        
        .owner {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
    </style>
</body>
</html>
