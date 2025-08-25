#  PROJECT 3 — E-COMMERCE SYSTEM
e-com site that is used for the selling of energry drinks with robust backend for future scaling into other markets the compay is called biofuel 
---

## Team Members

- **Project Manager:** Likhona Benayo
- **Backend Developer:** Liso Hlatshwayo/Likhona Benayo 
- **Frontend/UI Designer:** Sanelisiwe Mhlawuli/Nafees De Kock

---

## Technologies Used Lamp-stack but we did not used linix 

- **PHP 8.1** — backend logic and server-side processing  
- **MySQL** — relational database for users, products, and orders  
- **Bootstrap 5** — responsive and modern UI  
- **HTML & CSS** — structure and styling   
- **Sessions** — for cart and user authentication  

---

## Features

1. User registration and login system  
2. Product catalog with details  
3. Shopping cart functionality  
4. Checkout process with order confirmation  
5. Order history and details page  
6. Contact and About pages  
7. Terms and Conditions page (refund and privacy policy)
8. Logout and session management  
9. Responsive design for desktop and mobile  

---

## Phase Highlights

### Phase 1 Achievements
- Basic frontend with Bootstrap styling  
- Product listing and cart system implemented  
- Checkout flow simulated  
- user flow navigation created 

### Phase 2 Goals
- Full backend integration with MySQL  
- Payment gateway simulation (visa/mastercard/PayPal integration possible)  
- customer account for product & order management  

---

## Role-Based Access

Role Access Level                                   
Users can  Browse products, register and login , add to cart, checkout, view orders |

---

## Installation & Setup

###  Prerequisites
- PHP 8.1 or higher  
- MySQL (via XAMPP, WAMP, etc.)  
- Browser (Chrome, Edge, etc.)  

---

###  Steps to Run Locally

#### 1. Backend & Project Setup
- Place the project folder into:  
  `C:\xampp\htdocs\module_3_project`  

- Start **Apache** and **MySQL** in XAMPP  

- Import the database (e.g., `database_setup.sql`) into phpMyAdmin  

- Update database connection in `config.php`:

```php
<?php
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = ''; // your MySQL password
$DB_NAME = 'biofuel';
$conn = new mysqli($host, $user, $password, $dbname);
?>
```

#### 2. Access the System
Open browser and go to:  
`http://localhost/module_3_project/index.php`

---

##  Login Credentials
  

###  User Login
- Register via `register.php` or use test accounts seeded in the database  

---

## Architecture Highlights

- PHP backend with MySQL database  
- Session-based authentication & cart management  
- Modular structure with pages (`index.php`, `cart.php`, `checkout.php`, etc.)  
- Bootstrap for responsive UI  
- Database-driven product & order management  


Built with passion by nafees ,sunny, likona, liso  
