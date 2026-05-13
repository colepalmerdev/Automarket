<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Check if user is logged in and is a seller
requireLogin();

$db = new Database();
$pdo = $db->getConnection();
$user_id = getCurrentUserId();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $step = intval($_POST['step'] ?? 1);
    
    if ($step === 1) {
        // Step 1: Basic Information
        $brand_id = intval($_POST['brand_id']);
        $model_id = intval($_POST['model_id']);
        $year = intval($_POST['year']);
        $mileage = intval($_POST['mileage']);
        $fuel_type = cleanInput($_POST['fuel_type']);
        $transmission = cleanInput($_POST['transmission']);
        $body_type = cleanInput($_POST['body_type']);
        $color = cleanInput($_POST['color']);
        $engine_capacity = cleanInput($_POST['engine_capacity']);
        $power = cleanInput($_POST['power']);
        
        // Validate
        $errors = [];
        if (empty($brand_id) || empty($model_id)) $errors[] = 'Brand and model are required';
        if (empty($year) || $year < 1900 || $year > date('Y')) $errors[] = 'Invalid year';
        if (empty($fuel_type)) $errors[] = 'Fuel type is required';
        if (empty($transmission)) $errors[] = 'Transmission is required';
        if (empty($body_type)) $errors[] = 'Body type is required';
        
        if (empty($errors)) {
            $_SESSION['listing_step1'] = compact(
                'brand_id', 'model_id', 'year', 'mileage', 'fuel_type', 
                'transmission', 'body_type', 'color', 'engine_capacity', 'power'
            );
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'errors' => $errors]);
        }
        exit;
        
    } elseif ($step === 2) {
        // Step 2: Details and Pricing
        $title = cleanInput($_POST['title']);
        $description = cleanInput($_POST['description']);
        $price = floatval($_POST['price']);
        $location = cleanInput($_POST['location']);
        
        // Validate
        $errors = [];
        if (empty($title)) $errors[] = 'Title is required';
        if (empty($description)) $errors[] = 'Description is required';
        if (empty($price) || $price <= 0) $errors[] = 'Price must be greater than 0';
        if (empty($location)) $errors[] = 'Location is required';
        
        if (empty($errors)) {
            $_SESSION['listing_step2'] = compact('title', 'description', 'price', 'location');
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'errors' => $errors]);
        }
        exit;
        
    } elseif ($step === 3) {
        // Step 3: Images and Final Submission
        $images = [];
        
        // Handle image uploads
        if (!empty($_FILES['images']['name'][0])) {
            foreach ($_FILES['images']['name'] as $key => $name) {
                if ($_FILES['images']['error'][$key] === UPLOAD_ERR_OK) {
                    $file = [
                        'name' => $_FILES['images']['name'][$key],
                        'type' => $_FILES['images']['type'][$key],
                        'tmp_name' => $_FILES['images']['tmp_name'][$key],
                        'error' => $_FILES['images']['error'][$key],
                        'size' => $_FILES['images']['size'][$key]
                    ];
                    
                    // Validate image
                    $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
                    if (!in_array($file['type'], $allowed_types)) {
                        $errors[] = 'Only JPEG, PNG, and WebP images are allowed';
                        continue;
                    }
                    
                    if ($file['size'] > 5 * 1024 * 1024) { // 5MB limit
                        $errors[] = 'Image size must be less than 5MB';
                        continue;
                    }
                    
                    $uploaded_path = uploadImage($file, 'uploads/cars/');
                    if ($uploaded_path) {
                        $images[] = $uploaded_path;
                    }
                }
            }
        }
        
        if (empty($images)) {
            $errors[] = 'At least one image is required';
        }
        
        if (empty($errors)) {
            // Combine all session data
            $listing_data = array_merge(
                $_SESSION['listing_step1'] ?? [],
                $_SESSION['listing_step2'] ?? [],
                ['images' => json_encode($images)]
            );
            
            try {
                // Insert into database
                $stmt = $pdo->prepare("
                    INSERT INTO cars (
                        seller_id, brand_id, model_id, title, description, price, year, 
                        mileage, fuel_type, transmission, body_type, color, engine_capacity, 
                        power, location, images, is_approved
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $user_id,
                    $listing_data['brand_id'],
                    $listing_data['model_id'],
                    $listing_data['title'],
                    $listing_data['description'],
                    $listing_data['price'],
                    $listing_data['year'],
                    $listing_data['mileage'],
                    $listing_data['fuel_type'],
                    $listing_data['transmission'],
                    $listing_data['body_type'],
                    $listing_data['color'],
                    $listing_data['engine_capacity'],
                    $listing_data['power'],
                    $listing_data['location'],
                    $listing_data['images'],
                    false // Pending approval
                ]);
                
                // Clear session data
                unset($_SESSION['listing_step1'], $_SESSION['listing_step2']);
                
                echo json_encode(['success' => true, 'message' => 'Car listed successfully! It will be visible after admin approval.']);
                
            } catch(PDOException $e) {
                echo json_encode(['success' => false, 'errors' => ['Database error: ' . $e->getMessage()]]);
            }
        } else {
            echo json_encode(['success' => false, 'errors' => $errors]);
        }
        exit;
    }
}

// Get brands for dropdown
$brands = getCarBrands($pdo);
?>

<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sell Your Car - AutoMarket</title>
    
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
                AutoMarket
            </a>
            
            <ul class="nav-menu">
                <li><a href="index.php" class="nav-link">Home</a></li>
                <li><a href="browse-cars.php" class="nav-link">Buy Cars</a></li>
                <li><a href="rentals.php" class="nav-link">Rentals</a></li>
                <li><a href="sell-car.php" class="nav-link">Sell Car</a></li>
                <li><a href="about.php" class="nav-link">About</a></li>
                <li><a href="contact.php" class="nav-link">Contact</a></li>
            </ul>
            
            <div class="nav-actions">
                <button class="theme-toggle" id="theme-toggle">
                    <i class="fas fa-moon"></i>
                </button>
                
                <a href="dashboard.php" class="glass-button">
                    <i class="fas fa-user"></i> Dashboard
                </a>
                <a href="logout.php" class="glass-button">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <h1>Sell Your Car</h1>
            <p>List your vehicle on our premium marketplace and reach thousands of potential buyers</p>
        </div>
    </section>

    <!-- Multi-step Form -->
    <section class="section">
        <div class="container">
            <div class="form-container">
                <!-- Progress Steps -->
                <div class="progress-steps">
                    <div class="progress-step active" data-step="1">
                        <div class="step-number">1</div>
                        <div class="step-label">Basic Info</div>
                    </div>
                    <div class="progress-step" data-step="2">
                        <div class="step-number">2</div>
                        <div class="step-label">Details & Price</div>
                    </div>
                    <div class="progress-step" data-step="3">
                        <div class="step-number">3</div>
                        <div class="step-label">Photos</div>
                    </div>
                </div>

                <!-- Form Steps -->
                <form id="sell-car-form" class="multi-step-form">
                    <!-- Step 1: Basic Information -->
                    <div class="form-step active" id="step-1">
                        <div class="glass-card">
                            <h2>Vehicle Information</h2>
                            <p>Tell us about your car's basic specifications</p>
                            
                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">Brand *</label>
                                    <select name="brand_id" class="form-select" required onchange="updateModels(this.value)">
                                        <option value="">Select Brand</option>
                                        <?php foreach ($brands as $brand): ?>
                                            <option value="<?php echo $brand['id']; ?>"><?php echo htmlspecialchars($brand['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Model *</label>
                                    <select name="model_id" class="form-select" required id="model-select">
                                        <option value="">Select Model</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Year *</label>
                                    <input type="number" name="year" class="form-input" required 
                                           min="1900" max="<?php echo date('Y'); ?>" placeholder="e.g., 2020">
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Mileage (km)</label>
                                    <input type="number" name="mileage" class="form-input" 
                                           placeholder="e.g., 50000">
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Fuel Type *</label>
                                    <select name="fuel_type" class="form-select" required>
                                        <option value="">Select Fuel Type</option>
                                        <option value="petrol">Petrol</option>
                                        <option value="diesel">Diesel</option>
                                        <option value="electric">Electric</option>
                                        <option value="hybrid">Hybrid</option>
                                        <option value="lpg">LPG</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Transmission *</label>
                                    <select name="transmission" class="form-select" required>
                                        <option value="">Select Transmission</option>
                                        <option value="manual">Manual</option>
                                        <option value="automatic">Automatic</option>
                                        <option value="cvt">CVT</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Body Type *</label>
                                    <select name="body_type" class="form-select" required>
                                        <option value="">Select Body Type</option>
                                        <option value="sedan">Sedan</option>
                                        <option value="suv">SUV</option>
                                        <option value="hatchback">Hatchback</option>
                                        <option value="coupe">Coupe</option>
                                        <option value="convertible">Convertible</option>
                                        <option value="pickup">Pickup</option>
                                        <option value="van">Van</option>
                                        <option value="wagon">Wagon</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Color</label>
                                    <input type="text" name="color" class="form-input" placeholder="e.g., Black">
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Engine Capacity</label>
                                    <input type="text" name="engine_capacity" class="form-input" placeholder="e.g., 2.0L">
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Power</label>
                                    <input type="text" name="power" class="form-input" placeholder="e.g., 150 HP">
                                </div>
                            </div>
                            
                            <div class="form-navigation">
                                <button type="button" class="next-step glass-button primary">Next Step</button>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Details and Pricing -->
                    <div class="form-step" id="step-2">
                        <div class="glass-card">
                            <h2>Listing Details</h2>
                            <p>Provide details about your car and set your price</p>
                            
                            <div class="form-grid">
                                <div class="form-group full-width">
                                    <label class="form-label">Title *</label>
                                    <input type="text" name="title" class="form-input" required 
                                           placeholder="e.g., 2020 Toyota Camry - Excellent Condition">
                                </div>
                                
                                <div class="form-group full-width">
                                    <label class="form-label">Description *</label>
                                    <textarea name="description" class="form-textarea" required rows="6"
                                              placeholder="Describe your car's condition, features, maintenance history, etc."></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Price ($) *</label>
                                    <input type="number" name="price" class="form-input" required 
                                           min="0" step="0.01" placeholder="e.g., 25000">
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">Location *</label>
                                    <input type="text" name="location" class="form-input" required 
                                           placeholder="e.g., New York, NY">
                                </div>
                            </div>
                            
                            <div class="form-navigation">
                                <button type="button" class="prev-step glass-button">Previous</button>
                                <button type="button" class="next-step glass-button primary">Next Step</button>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Images -->
                    <div class="form-step" id="step-3">
                        <div class="glass-card">
                            <h2>Car Photos</h2>
                            <p>Upload high-quality photos of your vehicle (minimum 1, maximum 10)</p>
                            
                            <div class="form-group">
                                <label class="form-label">Images *</label>
                                <div class="image-upload-area" id="image-upload-area">
                                    <input type="file" id="images" name="images[]" multiple accept="image/*" style="display: none;">
                                    <div class="upload-placeholder">
                                        <i class="fas fa-cloud-upload-alt"></i>
                                        <p>Click to upload or drag and drop</p>
                                        <small>JPEG, PNG, WebP (Max 5MB per image)</small>
                                    </div>
                                    <div class="image-preview" id="image-preview"></div>
                                </div>
                            </div>
                            
                            <div class="form-navigation">
                                <button type="button" class="prev-step glass-button">Previous</button>
                                <button type="submit" class="glass-button primary">List Car</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-bottom">
                <p>&copy; 2026 AutoMarket. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="assets/js/script.js"></script>
    
    <script>
        // Multi-step form handling
        let currentStep = 1;
        const totalSteps = 3;
        
        function showStep(step) {
            document.querySelectorAll('.form-step').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.progress-step').forEach(el => el.classList.remove('active'));
            
            document.getElementById(`step-${step}`).classList.add('active');
            document.querySelector(`.progress-step[data-step="${step}"]`).classList.add('active');
            
            currentStep = step;
        }
        
        // Next step
        document.querySelectorAll('.next-step').forEach(btn => {
            btn.addEventListener('click', async function() {
                const step = currentStep;
                
                // Validate current step
                const stepForm = document.getElementById(`step-${step}`);
                const requiredFields = stepForm.querySelectorAll('[required]');
                let isValid = true;
                
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        isValid = false;
                        field.classList.add('error');
                    } else {
                        field.classList.remove('error');
                    }
                });
                
                if (!isValid) {
                    showToast('Please fill in all required fields', 'error');
                    return;
                }
                
                // Submit step data
                const formData = new FormData(document.getElementById('sell-car-form'));
                formData.append('step', step);
                
                try {
                    const response = await fetch('sell-car.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        showStep(step + 1);
                    } else {
                        showToast(result.errors?.join(', ') || 'An error occurred', 'error');
                    }
                } catch (error) {
                    showToast('Network error. Please try again.', 'error');
                }
            });
        });
        
        // Previous step
        document.querySelectorAll('.prev-step').forEach(btn => {
            btn.addEventListener('click', function() {
                showStep(currentStep - 1);
            });
        });
        
        // Form submission
        document.getElementById('sell-car-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('step', 3);
            
            try {
                const response = await fetch('sell-car.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast(result.message || 'Car listed successfully!', 'success');
                    setTimeout(() => {
                        window.location.href = 'dashboard.php';
                    }, 2000);
                } else {
                    showToast(result.errors?.join(', ') || 'An error occurred', 'error');
                }
            } catch (error) {
                showToast('Network error. Please try again.', 'error');
            }
        });
        
        // Update models based on brand
        async function updateModels(brandId) {
            const modelSelect = document.getElementById('model-select');
            modelSelect.innerHTML = '<option value="">Select Model</option>';
            
            if (brandId) {
                try {
                    const response = await fetch(`api/get-models.php?brand_id=${brandId}`);
                    const data = await response.json();
                    
                    if (data.success) {
                        data.models.forEach(model => {
                            const option = document.createElement('option');
                            option.value = model.id;
                            option.textContent = model.name;
                            modelSelect.appendChild(option);
                        });
                    }
                } catch (error) {
                    console.error('Error fetching models:', error);
                }
            }
        }
        
        // Image upload handling
        const imageUploadArea = document.getElementById('image-upload-area');
        const imageInput = document.getElementById('images');
        const imagePreview = document.getElementById('image-preview');
        
        imageUploadArea.addEventListener('click', () => imageInput.click());
        
        imageInput.addEventListener('change', function(e) {
            handleImageUpload(e.target.files);
        });
        
        // Drag and drop
        imageUploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            imageUploadArea.classList.add('drag-over');
        });
        
        imageUploadArea.addEventListener('dragleave', () => {
            imageUploadArea.classList.remove('drag-over');
        });
        
        imageUploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            imageUploadArea.classList.remove('drag-over');
            handleImageUpload(e.dataTransfer.files);
        });
        
        function handleImageUpload(files) {
            imagePreview.innerHTML = '';
            
            Array.from(files).forEach((file, index) => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        const div = document.createElement('div');
                        div.className = 'preview-item';
                        div.innerHTML = `
                            <img src="${e.target.result}" alt="Preview ${index + 1}">
                            <button type="button" class="remove-image" onclick="removeImage(${index})">
                                <i class="fas fa-times"></i>
                            </button>
                        `;
                        imagePreview.appendChild(div);
                    };
                    reader.readAsDataURL(file);
                }
            });
        }
        
        function removeImage(index) {
            const dt = new DataTransfer();
            const files = Array.from(imageInput.files);
            
            files.splice(index, 1);
            files.forEach(file => dt.items.add(file));
            
            imageInput.files = dt.files;
            handleImageUpload(imageInput.files);
        }
    </script>
    
    <style>
        .progress-steps {
            display: flex;
            justify-content: center;
            margin-bottom: 3rem;
            gap: 2rem;
        }
        
        .progress-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
            opacity: 0.5;
            transition: opacity 0.3s ease;
        }
        
        .progress-step.active {
            opacity: 1;
        }
        
        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--bg-tertiary);
            border: 2px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .progress-step.active .step-number {
            background: var(--secondary-color);
            color: var(--primary-color);
            border-color: var(--secondary-color);
        }
        
        .step-label {
            font-size: 0.9rem;
            color: var(--text-secondary);
        }
        
        .progress-step.active .step-label {
            color: var(--text-primary);
        }
        
        .form-step {
            display: none;
        }
        
        .form-step.active {
            display: block;
        }
        
        .form-navigation {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .full-width {
            grid-column: 1 / -1;
        }
        
        .image-upload-area {
            border: 2px dashed var(--border-color);
            border-radius: 10px;
            padding: 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            min-height: 200px;
        }
        
        .image-upload-area:hover,
        .image-upload-area.drag-over {
            border-color: var(--secondary-color);
            background: rgba(212, 175, 55, 0.05);
        }
        
        .upload-placeholder {
            color: var(--text-secondary);
        }
        
        .upload-placeholder i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--secondary-color);
        }
        
        .image-preview {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .preview-item {
            position: relative;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .preview-item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
        }
        
        .remove-image {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            background: rgba(220, 53, 69, 0.9);
            border: none;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }
        
        .remove-image:hover {
            background: rgba(220, 53, 69, 1);
            transform: scale(1.1);
        }
        
        .form-input.error {
            border-color: #dc3545;
        }
    </style>
</body>
</html>
