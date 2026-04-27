# Swabi StitchHub - Online Tailor Booking System

A complete web-based Final Year Project (FYP) for an online tailor booking system specifically for verified women tailors in Swabi, Khyber Pakhtunkhwa, Pakistan.

## Features

### User Roles
- **Admin**: Verify tailors, manage commissions, view reports
- **Tailor**: Manage designs, accept orders, track earnings (women only, requires verification)
- **Customer**: Search tailors, place orders, save measurements, leave reviews

### Core Features
- OTP-based phone verification (simulated)
- Role-based access control (RBAC)
- Design portfolio management for tailors
- Customer measurement saving
- Order tracking with status updates
- Payment system with commission deduction (10%)
- Rating and review system

## Tech Stack
- **Backend**: Python Flask
- **Database**: MySQL
- **Frontend**: HTML, CSS (Vanilla), JavaScript
- **Authentication**: Flask-Login with Bcrypt password hashing

## Setup Instructions

### Prerequisites
- Python 3.8+
- MySQL Server

### 1. Clone and Setup
```bash
git clone <repository-url>
cd swabi
```

### 2. Create Virtual Environment
```bash
python -m venv venv
source venv/bin/activate  # Linux/Mac
# or
venv\Scripts\activate  # Windows
```

### 3. Install Dependencies
```bash
pip install -r requirements.txt
```

### 4. Configure Environment
```bash
# Copy the example environment file
cp .env.example .env

# Edit .env with your database credentials
# MYSQL_PASSWORD=your_password
# MYSQL_DATABASE=stitchhub_db
```

### 5. Create Database
```bash
# Login to MySQL
mysql -u root -p

# Run the database script
source database.sql

# Or use a GUI tool like phpMyAdmin
```

### 6. Run the Application
```bash
python run.py
```

The application will start at `http://localhost:5000`

### 7. Default Admin Credentials
- Email: admin@stitchhub.com
- Password: admin123 (hash: $2b$12$LQv3c1yqBWVHxkd0LHAkCOYz6TtxMQJqhN8/LewY5lWz5m5f5vH2a)

## Project Structure
```
swabi/
├── app/
│   ├── __init__.py       # App factory
│   ├── decorators.py     # RBAC decorators
│   └── routes/
│       ├── auth.py       # Authentication routes
│       ├── customer.py   # Customer routes
│       ├── tailor.py     # Tailor routes
│       └── admin.py      # Admin routes
├── templates/             # HTML templates
│   ├── base.html
│   ├── index.html
│   ├── auth/
│   ├── customer/
│   ├── tailor/
│   └── admin/
├── static/
│   ├── css/style.css
│   └── js/main.js
├── models.py             # Database models
├── config.py             # Configuration
├── extensions.py         # Flask extensions
├── database.sql          # Database schema
└── run.py               # Application entry point
```

## OTP Verification (Simulated)
In development mode, OTP codes are logged to the console. Check the terminal output for the OTP after registering.

## Payment System (Simulated)
The payment system is simulated for demonstration purposes. No real money is processed.

## Screenshots

### Home Page
- Hero section with call-to-action
- Featured verified tailors
- How it works section

### Customer Dashboard
- Order management
- Find tailors
- Measurement saving

### Tailor Dashboard
- Order acceptance/rejection
- Design portfolio management
- Earnings tracking

### Admin Dashboard
- User management
- Tailor verification
- Payment transfers
- Reports and analytics

## License
This project is for educational purposes.

## Author
Final Year Project - Computer Science