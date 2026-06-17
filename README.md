# AutoMarket - Premium Car Marketplace & Rental Platform

A comprehensive full-stack car marketplace and rental booking website with a premium automotive design aesthetic. Built with PHP, MySQL, HTML, CSS, and JavaScript.

## Features

### Core Functionality
- **Car Marketplace**: Browse, search, and filter cars for sale
- **Car Listings**: Multi-step form for sellers to list their vehicles
- **Car Rentals**: Browse and book rental cars with availability calendar
- **Booking System**: Complete rental booking workflow with pricing calculator
- **User Authentication**: Secure login/registration with role-based access
- **Admin Dashboard**: Comprehensive management interface for administrators

### User Roles
- **Buyer**: Browse cars, save to wishlist, contact sellers
- **Seller**: List and manage vehicles for sale
- **Rental Customer**: Browse and book rental cars
- **Admin**: Manage users, approve listings, view analytics

### Verification and Password Recovery
- Email verification for new user registrations
- Resend verification links for unverified accounts
- Forgot password flow with expiring reset links

### Advanced Features
- Advanced search and filtering system
- Multi-step car listing form with image upload
- Real-time availability checking for rentals
- Dynamic pricing calculator (daily/weekly/monthly rates)
- Wishlist and car comparison features
- User dashboard with activity tracking
- Email notifications for important events
- Responsive design with dark/light theme support

### Payment Integration
- Multiple payment methods: Credit Card, M-Pesa, Cash
- Secure payment processing
- Transaction tracking and receipts

## Technology Stack

### Backend
- **PHP 7.4+**: Server-side logic and API endpoints
- **MySQL**: Database management
- **PDO**: Database connectivity
- **JSON API**: RESTful API structure

### Frontend
- **HTML5**: Semantic markup
- **CSS3**: Modern styling with CSS variables
- **JavaScript ES6+**: Interactive functionality
- **Font Awesome**: Icon library

### Design Features
- **Glassmorphism**: Modern glass-morphism effects
- **Dark/Light Theme**: Toggle between themes
- **Responsive Design**: Mobile-first approach
- **Smooth Animations**: CSS transitions and JavaScript animations
- **Premium Automotive Aesthetic**: Professional, luxury feel

## Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Composer (optional, for dependency management)

### Setup Instructions

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd cars
   ```

2. **Database Setup**
   - Create a MySQL database named `car_marketplace`
   - Import the database schema:
     ```bash
     mysql -u username -p car_marketplace < database/schema.sql
     ```

3. **Configuration**
   - Update database credentials in `config/database.php`:
     ```php
     private $host = 'localhost';
     private $db_name = 'car_marketplace';
     private $username = 'your_db_username';
     private $password = 'your_db_password';
     ```

4. **File Permissions**
   - Set write permissions for uploads directory:
     ```bash
     chmod -R 755 uploads/
     chmod -R 777 uploads/cars/
     ```

5. **Web Server Configuration**
   - Point your web server to the project root
   - Ensure `.htaccess` is enabled for URL rewriting (if using Apache)

## Project Structure

```
cars/
├── admin/                  # Admin panel
│   ├── dashboard.php       # Admin dashboard
│   ├── api/                # Admin API endpoints
│   └── ...                 # Other admin pages
├── api/                    # API endpoints
│   ├── search.php          # Car search API
│   ├── get-models.php      # Get car models API
│   ├── payment.php         # Payment processing API
│   └── ...                 # Other API endpoints
├── assets/                 # Static assets
│   ├── css/                # Stylesheets
│   │   ├── style.css       # Main styles
│   │   └── additional.css  # Additional styles
│   ├── js/                 # JavaScript files
│   │   └── script.js       # Main JavaScript
│   └── images/             # Image assets
├── config/                 # Configuration files
│   └── database.php        # Database configuration
├── database/               # Database files
│   └── schema.sql          # Database schema
├── includes/               # PHP includes
│   └── functions.php       # Helper functions
├── uploads/                # User uploads
│   └── cars/               # Car images
├── index.php               # Homepage
├── login.php               # Login page
├── register.php            # Registration page
├── dashboard.php           # User dashboard
├── browse-cars.php         # Browse cars page
├── sell-car.php            # Sell car form
├── rentals.php             # Rentals page
├── booking.php             # Booking form
├── booking-confirmation.php # Booking confirmation
└── ...                     # Other pages
```

## Database Schema

### Main Tables
- **users**: User accounts and authentication
- **cars**: Cars for sale
- **rental_cars**: Available rental cars
- **bookings**: Rental bookings
- **payments**: Payment transactions
- **messages**: User messaging system
- **reviews**: Car and service reviews
- **wishlist**: User wishlists

### Supporting Tables
- **car_brands**: Vehicle manufacturers
- **car_models**: Vehicle models
- **settings**: System configuration
- **recently_viewed**: User browsing history

## API Endpoints

### Search and Filtering
- `GET /api/search.php` - Search cars with filters
- `GET /api/get-models.php` - Get models by brand

### User Management
- `POST /api/login.php` - User authentication
- `POST /api/register.php` - User registration
- `POST /api/logout.php` - User logout

### Car Management
- `POST /api/car-listing.php` - Submit car listing
- `GET /api/car-details.php` - Get car details

### Booking and Payments
- `POST /api/booking.php` - Create booking
- `POST /api/payment.php` - Process payment
- `GET /api/booking-status.php` - Check booking status

### Admin Functions
- `POST /admin/api/approve.php` - Approve/reject listings
- `GET /admin/api/analytics.php` - Get analytics data

## Security Features

- **Password Hashing**: Secure password storage using bcrypt
- **SQL Injection Prevention**: Prepared statements for all database queries
- **XSS Protection**: Input sanitization and output escaping
- **CSRF Protection**: Token-based request validation
- **Session Management**: Secure session handling
- **File Upload Security**: Image validation and secure storage

## Performance Optimizations

- **Database Indexing**: Optimized queries with proper indexes
- **Image Optimization**: Responsive image serving
- **Lazy Loading**: Progressive content loading
- **Caching**: Browser caching headers
- **Minification**: Optimized CSS and JavaScript

## Browser Support

- Chrome 70+
- Firefox 65+
- Safari 12+
- Edge 79+

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## License

This project is licensed under the MIT License.

## Support

For support and inquiries, please contact:
- Email: info@automarket.com
- Phone: +254 745 554 951

## Future Enhancements

- **Mobile App**: Native iOS and Android applications
- **Advanced Analytics**: Comprehensive reporting dashboard
- **AI Integration**: Smart recommendations and price predictions
- **Multi-language Support**: Internationalization
- **Live Chat**: Real-time customer support
- **Insurance Integration**: Direct insurance purchasing
- **Financing Options**: Loan calculator and applications
- **GPS Tracking**: Real-time vehicle tracking for rentals
