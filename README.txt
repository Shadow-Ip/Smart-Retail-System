========================================
SMART RETAIL SYSTEM – SETUP INSTRUCTIONS
========================================

Before running the system, please update your MySQL database password.

----------------------------------------
1. UPDATE DATABASE CONNECTION
----------------------------------------
Open the file:

  Smart Retail System/database/dbConnection.php

Find this section and update ONLY the password:

// <----- Database credentials (Change if needed) ----->
$host = '127.0.0.1';
$dbname = 'smart_retail_db';
$username = 'root';
$password = 'YOUR_MYSQL_PASSWORD_HERE';  // Replace this with your actual MySQL password

Make sure your MySQL server is running.

----------------------------------------
2. IMPORT THE DATABASE
----------------------------------------
Import the provided SQL script into your MySQL / phpMyAdmin:

  smart_retail_db.sql

This will create all required tables:
- customers
- sales_associate
- products
- orders
- order_items
- payments
- cart_items

----------------------------------------
3. RUN THE APPLICATION
----------------------------------------
To start the system:

  Open the file:
    Dashboard.php

This is the main entry point for the Smart Retail System.


----------------------------------------
4. FILE STRUCTURE OVERVIEW
----------------------------------------
/php           → All PHP application files  
/css           → CSS stylesheets  
/database      → Database connection + SQL file  
/Pictures      → Product images  
/script.js     → Client-side javascript  

----------------------------------------
6. LOGIN ACCOUNTS (OPTIONAL)
----------------------------------------
You may register your own customer or sales associate account inside the system.

Demo Accounts 
Customer 
Email - zambuck@gmail.com
Password - johnvan 

Sales Associate
Email - oscarDN@srs.co.za
Password - oscar


----------------------------------------
By Masilo Lebepe
----------------------------------------
