<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$db = new Database();
$pdo = $db->getConnection();

// Get filters from URL
$filters = [
    'brand' => $_GET['brand'] ?? '',
    'model' => $_GET['model'] ?? '',
    'min_price' => $_GET['min_price'] ?? '',
    'max_price' => $_GET['max_price'] ?? '',
    'fuel_type' => $_GET['fuel_type'] ?? '',
    'transmission' => $_GET['transmission'] ?? '',
    'location' => $_GET['location'] ?? '',
    'year_min' => $_GET['year_min'] ?? '',
    'year_max' => $_GET['year_max'] ?? '',
    'mileage_max' => $_GET['mileage_max'] ?? ''
];

// Get cars based on filters
$cars = searchCars($pdo, $filters);

// Get brands for filter dropdown
$brands = getCarBrands($pdo);

// Pagination
$page = $_GET['page'] ?? 1;
$per_page = 12;
$total_cars = count($cars);
$total_pages = ceil($total_cars / $per_page);
$offset = ($page - 1) * $per_page;
$cars = array_slice($cars, $offset, $per_page);
?>

<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Cars - AutoMarket </title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="logo">
                <i class="fas fa-car"></i>
                AutoMarket 
            </a>
            
            <ul class="nav-menu">
                <li><a href="index.php" class="nav-link">Home</a></li>
                <li><a href="browse-cars.php" class="nav-link active">Buy Cars</a></li>
                <li><a href="rentals.php" class="nav-link">Rentals</a></li>
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
            <h1>Browse Cars</h1>
            <p>Find your perfect vehicle from our extensive collection</p>
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
                            <h3>Filters</h3>
                            <button class="clear-filters" onclick="clearFilters()">Clear All</button>
                        </div>
                        
                        <form id="car-search-form" method="GET" class="filters-form">
                            <!-- Search -->
                            <div class="filter-group">
                                <label class="filter-label">Search</label>
                                <input type="text" name="search" class="filter-input" placeholder="Search cars..." 
                                       value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                            </div>
                            
                            <!-- Brand -->
                            <div class="filter-group">
                                <label class="filter-label">Brand</label>
                                <select name="brand" class="filter-select" onchange="updateModels(this.value)">
                                    <option value="">All Brands</option>
                                    <?php foreach ($brands as $brand): ?>
                                        <option value="<?php echo $brand['id']; ?>" <?php echo ($filters['brand'] == $brand['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($brand['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Model -->
                            <div class="filter-group">
                                <label class="filter-label">Model</label>
                                <select name="model" class="filter-select" id="model-select">
                                    <option value="">All Models</option>
                                </select>
                            </div>
                            
                            <!-- Price Range -->
                            <div class="filter-group">
                                <label class="filter-label">Price Range</label>
                                <div class="price-range">
                                    <input type="number" name="min_price" class="filter-input" placeholder="Min" 
                                           value="<?php echo htmlspecialchars($filters['min_price']); ?>">
                                    <span>-</span>
                                    <input type="number" name="max_price" class="filter-input" placeholder="Max" 
                                           value="<?php echo htmlspecialchars($filters['max_price']); ?>">
                                </div>
                            </div>
                            
                            <!-- Year Range -->
                            <div class="filter-group">
                                <label class="filter-label">Year Range</label>
                                <div class="year-range">
                                    <input type="number" name="year_min" class="filter-input" placeholder="From" 
                                           value="<?php echo htmlspecialchars($filters['year_min']); ?>" min="1990" max="<?php echo date('Y'); ?>">
                                    <span>-</span>
                                    <input type="number" name="year_max" class="filter-input" placeholder="To" 
                                           value="<?php echo htmlspecialchars($filters['year_max']); ?>" min="1990" max="<?php echo date('Y'); ?>">
                                </div>
                            </div>
                            
                            <!-- Fuel Type -->
                            <div class="filter-group">
                                <label class="filter-label">Fuel Type</label>
                                <select name="fuel_type" class="filter-select">
                                    <option value="">All Types</option>
                                    <option value="petrol" <?php echo ($filters['fuel_type'] === 'petrol') ? 'selected' : ''; ?>>Petrol</option>
                                    <option value="diesel" <?php echo ($filters['fuel_type'] === 'diesel') ? 'selected' : ''; ?>>Diesel</option>
                                    <option value="electric" <?php echo ($filters['fuel_type'] === 'electric') ? 'selected' : ''; ?>>Electric</option>
                                    <option value="hybrid" <?php echo ($filters['fuel_type'] === 'hybrid') ? 'selected' : ''; ?>>Hybrid</option>
                                    <option value="lpg" <?php echo ($filters['fuel_type'] === 'lpg') ? 'selected' : ''; ?>>LPG</option>
                                </select>
                            </div>
                            
                            <!-- Transmission -->
                            <div class="filter-group">
                                <label class="filter-label">Transmission</label>
                                <select name="transmission" class="filter-select">
                                    <option value="">All Types</option>
                                    <option value="manual" <?php echo ($filters['transmission'] === 'manual') ? 'selected' : ''; ?>>Manual</option>
                                    <option value="automatic" <?php echo ($filters['transmission'] === 'automatic') ? 'selected' : ''; ?>>Automatic</option>
                                    <option value="cvt" <?php echo ($filters['transmission'] === 'cvt') ? 'selected' : ''; ?>>CVT</option>
                                </select>
                            </div>
                            
                            <!-- Location -->
                            <div class="filter-group">
                                <label class="filter-label">Location</label>
                                <input type="text" name="location" class="filter-input" placeholder="Enter location" 
                                       value="<?php echo htmlspecialchars($filters['location']); ?>">
                            </div>
                            
                            <!-- Max Mileage -->
                            <div class="filter-group">
                                <label class="filter-label">Max Mileage (km)</label>
                                <input type="number" name="mileage_max" class="filter-input" placeholder="Maximum mileage" 
                                       value="<?php echo htmlspecialchars($filters['mileage_max']); ?>">
                            </div>
                            
                            <button type="submit" class="glass-button primary">Apply Filters</button>
                        </form>
                    </div>
                </aside>

                <!-- Cars Grid -->
                <main class="cars-main">
                    <!-- Results Header -->
                    <div class="results-header">
                        <div class="results-info">
                            <h2>Available Cars</h2>
                            <p>Showing <?php echo count($cars); ?> of <?php echo $total_cars; ?> cars</p>
                        </div>
                        
                        <div class="results-controls">
                            <div class="sort-dropdown">
                                <select class="filter-select" onchange="sortCars(this.value)">
                                    <option value="newest">Newest First</option>
                                    <option value="oldest">Oldest First</option>
                                    <option value="price_low">Price: Low to High</option>
                                    <option value="price_high">Price: High to Low</option>
                                    <option value="mileage_low">Mileage: Low to High</option>
                                    <option value="mileage_high">Mileage: High to Low</option>
                                </select>
                            </div>
                            
                            <div class="view-toggle">
                                <button class="view-btn active" data-view="grid">
                                    <i class="fas fa-th"></i>
                                </button>
                                <button class="view-btn" data-view="list">
                                    <i class="fas fa-list"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Cars Grid/List -->
                    <div id="cars-container" class="car-grid">
                        <?php if (empty($cars)): ?>
                            <div class="no-results glass-card">
                                <i class="fas fa-car"></i>
                                <h3>No cars found</h3>
                                <p>Try adjusting your filters or search criteria</p>
                                <button class="glass-button primary" onclick="clearFilters()">Clear Filters</button>
                            </div>
                        <?php else: ?>
                            <?php foreach ($cars as $car): ?>
                                <div class="car-card animate-fadeInUp">
                                    <div class="car-image">
                                        <img src="<?php echo $car['images'] ? json_decode($car['images'])[0] : 'assets/images/default-car.jpg'; ?>" 
                                             alt="<?php echo htmlspecialchars($car['title']); ?>">
                                        
                                        <?php if ($car['is_featured']): ?>
                                            <span class="car-badge">Featured</span>
                                        <?php endif; ?>
                                        
                                        <span class="car-price"><?php echo formatPrice($car['price']); ?></span>
                                        
                                        <div class="car-actions-overlay">
                                            <button class="wishlist-btn" onclick="toggleWishlist(<?php echo $car['id']; ?>, 'sale')" 
                                                    data-wishlist-car="<?php echo $car['id']; ?>">
                                                <i class="far fa-heart"></i>
                                            </button>
                                            <button class="compare-btn" onclick="addToComparison(<?php echo $car['id']; ?>)">
                                                <i class="fas fa-balance-scale"></i>
                                            </button>
                                        </div>
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
                                        
                                        <div class="car-location">
                                            <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($car['location']); ?>
                                        </div>
                                        
                                        <div class="car-meta">
                                            <span class="car-brand"><?php echo htmlspecialchars($car['brand_name']); ?></span>
                                            <span class="posted-date">Posted <?php echo timeAgo($car['created_at']); ?></span>
                                        </div>
                                        
                                        <div class="car-actions">
                                            <button class="glass-button" onclick="viewCar(<?php echo $car['id']; ?>)">View Details</button>
                                            <button class="glass-button primary" onclick="contactSeller(<?php echo $car['id']; ?>)">Contact</button>
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
                <p>&copy; 2026 AutoMarket . All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="assets/js/script.js"></script>
    
    <script>
        // Update models based on selected brand
        function updateModels(brandId) {
            const modelSelect = document.getElementById('model-select');
            modelSelect.innerHTML = '<option value="">All Models</option>';
            
            if (brandId) {
                fetch(`api/get-models.php?brand_id=${brandId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            data.models.forEach(model => {
                                const option = document.createElement('option');
                                option.value = model.id;
                                option.textContent = model.name;
                                modelSelect.appendChild(option);
                            });
                        }
                    });
            }
        }
        
        // Clear all filters
        function clearFilters() {
            window.location.href = 'browse-cars.php';
        }
        
        // Sort cars
        function sortCars(sortBy) {
            const url = new URL(window.location);
            url.searchParams.set('sort', sortBy);
            window.location.href = url.toString();
        }
        
        // Toggle view (grid/list)
        document.querySelectorAll('.view-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.view-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                const container = document.getElementById('cars-container');
                const view = this.dataset.view;
                
                if (view === 'list') {
                    container.classList.remove('car-grid');
                    container.classList.add('car-list');
                } else {
                    container.classList.remove('car-list');
                    container.classList.add('car-grid');
                }
            });
        });
        
        // Initialize models on page load
        document.addEventListener('DOMContentLoaded', function() {
            const brandSelect = document.querySelector('select[name="brand"]');
            if (brandSelect && brandSelect.value) {
                updateModels(brandSelect.value);
            }
        });
    </script>
</body>
</html>
