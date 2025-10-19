<?php

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }


//db connect
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "impactpass"; // Changed to match your database name

// Create connection
global $conn;
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create events table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    event_date DATETIME NOT NULL,
    image_url VARCHAR(255) DEFAULT 'https://via.placeholder.com/300x200',
    location VARCHAR(255),
    price DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) !== TRUE) {
    echo "Error creating events table: " . $conn->error;
}

// Add price column if it doesn't exist
$alterSql = "ALTER TABLE events ADD COLUMN IF NOT EXISTS price DECIMAL(10,2) DEFAULT 0.00";
$conn->query($alterSql);

// Create bookings table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    user_id INT NOT NULL,
    quantity INT DEFAULT 1,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_id VARCHAR(255),
    payment_status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
)";

if ($conn->query($sql) !== TRUE) {
    echo "Error creating bookings table: " . $conn->error;
}

class ApiMethods {
    
    // Constructor
    function __construct() {
        // Check and upgrade database schema if needed
        $this->checkAndUpgradeSchema();
    }
    
    function login(){
        // Implement your login logic here
        global $conn;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'];
            $password = $_POST['password'];
            //decode hashed password
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    // User found, login successful
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['role'];

                    return ['status' => 'success', 'message' => 'Login successful'];
                } else {
                    // Invalid credentials
                    return ['status' => 'error', 'message' => 'Invalid email or password'];
                }
            } else {
                // Invalid credentials
                return ['status' => 'error', 'message' => 'Invalid email or password'];
            }
        }
        return ['status' => 'error', 'message' => 'Invalid request method'];
    }



    function signup(){  
        // Implement your signup logic here
        global $conn;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'];
            $email = $_POST['email'];
            $password = $_POST['password'];
            
            // Check if email already exists
            $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $check->bind_param("s", $email);
            $check->execute();
            $checkResult = $check->get_result();
            
            if ($checkResult->num_rows > 0) {
                return ['status' => 'error', 'message' => 'Email already in use'];
            }
            
            //hash password
            $password = password_hash($password, PASSWORD_BCRYPT);

            // Insert user into database
            $role = 'user';
            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $password, $role);
            if ($stmt->execute()) {
                return ['status' => 'success', 'message' => 'Signup successful! Please login.'];
            } else {
                return ['status' => 'error', 'message' => 'Signup failed: ' . $conn->error];
            }
        }
        return ['status' => 'error', 'message' => 'Invalid request method'];
    }


//get all events
    function getEvents(){   
        global $conn;
        $events = [];
        $stmt = $conn->prepare("SELECT * FROM events ORDER BY event_date ASC");
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $events[] = $row;
        }
        return $events;
    }



    //add demo events to database events table create table if not exists
        function addDemoEvents(){   
            global $conn;

            
            // Check if events table is empty
            $check = $conn->query("SELECT COUNT(*) as count FROM events");
            $row = $check->fetch_assoc();
            
            if ($row['count'] == 0) {
                // Table is empty, add demo events
                $demoEvents = [
                    [
                        'title' => 'Annual Tech Conference',
                        'description' => 'Join us for our annual technology conference featuring keynote speakers from top tech companies.',
                        'event_date' => '2025-11-15 09:00:00',
                        'image_url' => 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80',
                        'location' => 'Convention Center, Downtown',
                        'price' => 49.99
                    ],
                    [
                        'title' => 'Community Volunteer Day',
                        'description' => 'Help clean up local parks and plant trees with fellow community members.',
                        'event_date' => '2025-12-05 10:00:00',
                        'image_url' => 'https://images.unsplash.com/photo-1559027615-cd4628902d4a?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80',
                        'location' => 'City Park',
                        'price' => 0.00
                    ],
                    [
                        'title' => 'Holiday Fundraising Gala',
                        'description' => 'An elegant evening to raise funds for children\'s education programs.',
                        'event_date' => '2025-12-20 18:30:00',
                        'image_url' => 'https://images.unsplash.com/photo-1511795409834-ef04bbd61622?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80',
                        'location' => 'Grand Hotel Ballroom',
                        'price' => 99.99
                    ],
                    [
                        'title' => 'New Year Celebration',
                        'description' => 'Ring in the New Year with music, fireworks, and festivities for all ages.',
                        'event_date' => '2025-12-31 20:00:00',
                        'image_url' => 'https://images.unsplash.com/photo-1467810563316-b5476525c0f9?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80',
                        'location' => 'City Square',
                        'price' => 149.99
                    ],
                    [
                        'title' => 'Startup Pitch Competition',
                        'description' => 'Watch innovative startups pitch their ideas to investors and win funding.',
                        'event_date' => '2026-01-15 13:00:00',
                        'image_url' => 'https://images.unsplash.com/photo-1551818255-e6e10975bc17?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80',
                        'location' => 'Innovation Hub',
                        'price' => 75.00
                    ],
                    [
                        'title' => 'Winter Marathon',
                        'description' => 'Challenge yourself with our annual winter marathon through scenic routes.',
                        'event_date' => '2026-02-01 07:00:00',
                        'image_url' => 'https://images.unsplash.com/photo-1452626038306-9aae5e071dd3?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80',
                        'location' => 'River Park Trail',
                        'price' => 25.00
                    ]
                ];
                
                $stmt = $conn->prepare("INSERT INTO events (title, description, event_date, image_url, location, price) VALUES (?, ?, ?, ?, ?, ?)");
                
                // Check if price column exists
                $result = $conn->query("SHOW COLUMNS FROM events LIKE 'price'");
                if($result->num_rows == 0) {
                    // Add price column if it doesn't exist
                    $conn->query("ALTER TABLE events ADD COLUMN price DECIMAL(10,2) DEFAULT 0.00");
                }
                
                foreach ($demoEvents as $event) {
                    $stmt->bind_param("sssssd", 
                        $event['title'], 
                        $event['description'], 
                        $event['event_date'], 
                        $event['image_url'], 
                        $event['location'],
                        $event['price']
                    );
                    $stmt->execute();
                }
                
                return ['status' => 'success', 'message' => 'Demo events added successfully'];
            }
            
            return ['status' => 'info', 'message' => 'Demo events already exist'];
        }
 




    // Book an event
    function bookEvent() {
        global $conn;
        
        if (!isset($_SESSION['user_id'])) {
            return ['status' => 'error', 'message' => 'Please login to book an event'];
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $event_id = isset($_POST['event_id']) ? intval($_POST['event_id']) : 0;
            $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
            
            // Limit to maximum of 2 tickets per event per user
            if ($quantity > 2) {
                $quantity = 2; // Cap at 2 tickets
            }
            
            // Check if the user already has tickets for this event
            $stmt = $conn->prepare("SELECT SUM(quantity) as total_tickets FROM bookings WHERE event_id = ? AND user_id = ?");
            $stmt->bind_param("ii", $event_id, $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            $existing_tickets = $row['total_tickets'] ? intval($row['total_tickets']) : 0;
            
            if ($existing_tickets >= 2) {
                return ['status' => 'error', 'message' => 'You have already booked the maximum of 2 tickets for this event'];
            }
            
            // Adjust quantity to respect the 2 ticket maximum
            if (($existing_tickets + $quantity) > 2) {
                $quantity = 2 - $existing_tickets;
                if ($quantity <= 0) {
                    return ['status' => 'error', 'message' => 'You have already booked the maximum of 2 tickets for this event'];
                }
            }
            
            // Check if price column exists
            $columnResult = $conn->query("SHOW COLUMNS FROM events LIKE 'price'");
            if($columnResult->num_rows == 0) {
                // Add price column if it doesn't exist
                $conn->query("ALTER TABLE events ADD COLUMN price DECIMAL(10,2) DEFAULT 0.00");
            }
            
            // Validate event exists
            $stmt = $conn->prepare("SELECT id, title, price FROM events WHERE id = ?");
            $stmt->bind_param("i", $event_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows == 0) {
                return ['status' => 'error', 'message' => 'Event not found'];
            }
            
            $event = $result->fetch_assoc();
            $price = isset($event['price']) ? $event['price'] : 0; // Default to free event if price not set
            $total_amount = $price * $quantity;
            
            // Check if bookings table exists
            $tableResult = $conn->query("SHOW TABLES LIKE 'bookings'");
            if($tableResult->num_rows == 0) {
                // Create bookings table if it doesn't exist
                $createBookingsTable = "CREATE TABLE IF NOT EXISTS bookings (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    event_id INT NOT NULL,
                    user_id INT NOT NULL,
                    quantity INT NOT NULL DEFAULT 1,
                    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                    payment_status ENUM('pending', 'completed', 'failed') NOT NULL DEFAULT 'pending',
                    razorpay_order_id VARCHAR(255) NULL,
                    razorpay_payment_id VARCHAR(255) NULL,
                    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )";
                $conn->query($createBookingsTable);
            }
            
            // Check if it's a free event
            if ($total_amount == 0) {
                // For free events, directly create booking
                $stmt = $conn->prepare("INSERT INTO bookings (event_id, user_id, quantity, total_amount, payment_status) VALUES (?, ?, ?, ?, 'completed')");
                $stmt->bind_param("iiid", $event_id, $_SESSION['user_id'], $quantity, $total_amount);
                
                if ($stmt->execute()) {
                    return [
                        'status' => 'success', 
                        'message' => 'You have successfully registered for this free event',
                        'booking_id' => $conn->insert_id
                    ];
                } else {
                    return ['status' => 'error', 'message' => 'Booking failed: ' . $conn->error];
                }
            } else {
                // For paid events, create pending booking and generate Razorpay order
                $stmt = $conn->prepare("INSERT INTO bookings (event_id, user_id, quantity, total_amount, payment_status) VALUES (?, ?, ?, ?, 'pending')");
                $stmt->bind_param("iiid", $event_id, $_SESSION['user_id'], $quantity, $total_amount);
                
                if ($stmt->execute()) {
                    $booking_id = $conn->insert_id;
                    
                    // Generate Razorpay order 
                    // For testing purposes, set a default price if not available
                    $price_for_payment = $price > 0 ? $price : 99.00; // Default price if no price column
                    $total_for_payment = $price_for_payment * $quantity;
                    
                    $razorpay_data = $this->createRazorpayOrder($booking_id, $total_for_payment, $event['title']);
                    
                    if ($razorpay_data['status'] == 'success') {
                        return [
                            'status' => 'success',
                            'message' => 'Proceed to payment',
                            'razorpay_data' => $razorpay_data['data'],
                            'event_title' => $event['title'],
                            'booking_id' => $booking_id,
                            'total_amount' => $total_for_payment
                        ];
                    } else {
                        return ['status' => 'error', 'message' => 'Payment initialization failed: ' . $razorpay_data['message']];
                    }
                } else {
                    return ['status' => 'error', 'message' => 'Booking failed: ' . $conn->error];
                }
            }
        }
        
        return ['status' => 'error', 'message' => 'Invalid request method'];
    }
    
    // Create Razorpay order
    private function createRazorpayOrder($booking_id, $amount, $event_title) {
        // Razorpay API credentials (replace with your actual credentials)
        $key_id = "rzp_test_5fIpDiq0CC4SjF";
        $key_secret = "yKuiw8ieBLCqqBhukMYBTIRH";
        
        // Razorpay API endpoint for creating orders
        $url = 'https://api.razorpay.com/v1/orders';
        
        // Convert amount to paisa (Razorpay uses smallest currency unit)
        $amount_in_paisa = $amount * 100;
        
        // Prepare order data
        $order_data = array(
            'amount' => $amount_in_paisa,
            'currency' => 'INR',
            'receipt' => 'booking_' . $booking_id,
            'payment_capture' => 1, // Auto-capture payment
            'notes' => array(
                'event_title' => $event_title,
                'booking_id' => $booking_id,
                'customer_name' => isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '',
                'customer_email' => isset($_SESSION['user_email']) ? $_SESSION['user_email'] : ''
            )
        );
        
        // Initialize cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERPWD, $key_id . ":" . $key_secret);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($order_data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($error) {
            return ['status' => 'error', 'message' => 'cURL Error: ' . $error];
        }
        
        $response_array = json_decode($response, true);
        
        if (isset($response_array['error'])) {
            return ['status' => 'error', 'message' => $response_array['error']['description']];
        }
        
        // Add key_id to the response so it's available in the frontend
        $response_array['key_id'] = $key_id;
        
        return ['status' => 'success', 'data' => $response_array];
    }
    
    // Check and upgrade database schema if needed
    private function checkAndUpgradeSchema() {
        global $conn;
        
        // Check if tables exist and create them if they don't
        $tables = ['users', 'events', 'bookings'];
        foreach ($tables as $table) {
            $result = $conn->query("SHOW TABLES LIKE '$table'");
            if ($result->num_rows == 0) {
                switch ($table) {
                    case 'bookings':
                        $conn->query("CREATE TABLE IF NOT EXISTS bookings (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            event_id INT NOT NULL,
                            user_id INT NOT NULL,
                            quantity INT NOT NULL DEFAULT 1,
                            total_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
                            payment_status ENUM('pending', 'completed', 'failed') NOT NULL DEFAULT 'pending',
                            razorpay_order_id VARCHAR(255) NULL,
                            razorpay_payment_id VARCHAR(255) NULL,
                            booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                        )");
                        break;
                    case 'events':
                        $conn->query("CREATE TABLE IF NOT EXISTS events (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            title VARCHAR(255) NOT NULL,
                            description TEXT,
                            event_date DATETIME NOT NULL,
                            image_url VARCHAR(255),
                            location VARCHAR(255),
                            price DECIMAL(10,2) DEFAULT 0.00,
                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                        )");
                        break;
                    case 'users':
                        $conn->query("CREATE TABLE IF NOT EXISTS users (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            name VARCHAR(100) NOT NULL,
                            email VARCHAR(100) NOT NULL UNIQUE,
                            password VARCHAR(255) NOT NULL,
                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                        )");
                        break;
                }
            }
        }
        
        // Check for specific columns
        $columnChecks = [
            ['table' => 'events', 'column' => 'price', 'definition' => 'DECIMAL(10,2) DEFAULT 0.00'],
            ['table' => 'bookings', 'column' => 'payment_id', 'definition' => 'VARCHAR(255) NULL'],
            ['table' => 'bookings', 'column' => 'razorpay_payment_id', 'definition' => 'VARCHAR(255) NULL'],
            ['table' => 'bookings', 'column' => 'razorpay_order_id', 'definition' => 'VARCHAR(255) NULL']
        ];
        
        foreach ($columnChecks as $check) {
            $result = $conn->query("SHOW COLUMNS FROM {$check['table']} LIKE '{$check['column']}'");
            if ($result->num_rows == 0) {
                $conn->query("ALTER TABLE {$check['table']} ADD COLUMN {$check['column']} {$check['definition']}");
            }
        }
    }
    
    // Verify Razorpay payment
    function verifyPayment() {
        global $conn;
        
        // Check and upgrade schema if needed
        $this->checkAndUpgradeSchema();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $payment_id = $_POST['razorpay_payment_id'];
            $booking_id = $_POST['booking_id'];
            
            // Check if payment_id column exists (for backward compatibility)
            $paymentIdColumnName = "payment_id";
            $result = $conn->query("SHOW COLUMNS FROM bookings LIKE 'payment_id'");
            if ($result->num_rows > 0) {
                $paymentIdColumnName = "payment_id";
            } else {
                // Check if razorpay_payment_id column exists
                $result = $conn->query("SHOW COLUMNS FROM bookings LIKE 'razorpay_payment_id'");
                if ($result->num_rows > 0) {
                    $paymentIdColumnName = "razorpay_payment_id";
                } else {
                    // Add payment_id column if neither exists
                    $conn->query("ALTER TABLE bookings ADD COLUMN payment_id VARCHAR(255) NULL");
                    $paymentIdColumnName = "payment_id";
                }
            }
            
            // Update booking with payment info using the correct column name
            $stmt = $conn->prepare("UPDATE bookings SET {$paymentIdColumnName} = ?, payment_status = 'completed' WHERE id = ?");
            $stmt->bind_param("si", $payment_id, $booking_id);
            
            if ($stmt->execute()) {
                return ['status' => 'success', 'message' => 'Payment verified successfully!'];
            } else {
                return ['status' => 'error', 'message' => 'Payment verification failed: ' . $conn->error];
            }
        }
        
        return ['status' => 'error', 'message' => 'Invalid request method'];
    }
    
    // Get user's bookings
    function getUserBookings() {
        global $conn;
        
        if (!isset($_SESSION['user_id'])) {
            return ['status' => 'error', 'message' => 'Please login to view your bookings'];
        }
        
        $bookings = [];
        $stmt = $conn->prepare("
            SELECT b.*, e.title, e.event_date, e.location 
            FROM bookings b 
            JOIN events e ON b.event_id = e.id 
            WHERE b.user_id = ? 
            ORDER BY b.booking_date DESC
        ");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $bookings[] = $row;
        }
        
        return ['status' => 'success', 'bookings' => $bookings];
    }
}











?>