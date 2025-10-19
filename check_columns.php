<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "impactpass";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Add some basic styling
echo "
<!DOCTYPE html>
<html>
<head>
    <title>Database Structure Check</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        .table { margin-top: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        h2 { margin-top: 30px; }
    </style>
</head>
<body>
    <h1>ImpactPass Database Structure Check</h1>
";

// Check if events table exists
$tableResult = $conn->query("SHOW TABLES LIKE 'events'");
if($tableResult->num_rows == 0) {
    echo "<p class='error'>Events table does not exist! Creating it now...</p>";
    
    $createEventsTable = "CREATE TABLE IF NOT EXISTS events (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        event_date DATETIME NOT NULL,
        image_url VARCHAR(255),
        location VARCHAR(255),
        price DECIMAL(10,2) DEFAULT 0.00,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if($conn->query($createEventsTable) === TRUE) {
        echo "<p class='success'>Events table created successfully.</p>";
    } else {
        echo "<p class='error'>Error creating events table: " . $conn->error . "</p>";
    }
} else {
    echo "<p class='success'>Events table exists.</p>";
}

// Check if bookings table exists
$tableResult = $conn->query("SHOW TABLES LIKE 'bookings'");
if($tableResult->num_rows == 0) {
    echo "<p class='error'>Bookings table does not exist! Creating it now...</p>";
    
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
    
    if($conn->query($createBookingsTable) === TRUE) {
        echo "<p class='success'>Bookings table created successfully.</p>";
    } else {
        echo "<p class='error'>Error creating bookings table: " . $conn->error . "</p>";
    }
} else {
    echo "<p class='success'>Bookings table exists.</p>";
}

// Check if price column exists in events table
$result = $conn->query("SHOW COLUMNS FROM events LIKE 'price'");
if($result->num_rows > 0) {
    echo "<p class='success'>Price column exists in events table.</p>";
} else {
    echo "<p class='error'>Price column does NOT exist in events table. Adding it now...</p>";
    
    // Add price column if it doesn't exist
    $alterSql = "ALTER TABLE events ADD COLUMN price DECIMAL(10,2) DEFAULT 0.00";
    if($conn->query($alterSql) === TRUE) {
        echo "<p class='success'>Price column added successfully to events table.</p>";
    } else {
        echo "<p class='error'>Error adding price column: " . $conn->error . "</p>";
    }
}

// Check payment ID columns in bookings table
$payment_id_exists = $conn->query("SHOW COLUMNS FROM bookings LIKE 'payment_id'")->num_rows > 0;
$razorpay_payment_id_exists = $conn->query("SHOW COLUMNS FROM bookings LIKE 'razorpay_payment_id'")->num_rows > 0;

echo "<h3>Payment ID Columns Check:</h3>";
echo "<p" . ($payment_id_exists ? " class='success'>payment_id column exists" : " class='error'>payment_id column does NOT exist") . " in bookings table.</p>";
echo "<p" . ($razorpay_payment_id_exists ? " class='success'>razorpay_payment_id column exists" : " class='error'>razorpay_payment_id column does NOT exist") . " in bookings table.</p>";

// Add missing payment ID columns
if (!$payment_id_exists) {
    echo "<p class='info'>Adding payment_id column to bookings table...</p>";
    if ($conn->query("ALTER TABLE bookings ADD COLUMN payment_id VARCHAR(255) NULL") === TRUE) {
        echo "<p class='success'>payment_id column added successfully.</p>";
    } else {
        echo "<p class='error'>Error adding payment_id column: " . $conn->error . "</p>";
    }
}

if (!$razorpay_payment_id_exists) {
    echo "<p class='info'>Adding razorpay_payment_id column to bookings table...</p>";
    if ($conn->query("ALTER TABLE bookings ADD COLUMN razorpay_payment_id VARCHAR(255) NULL") === TRUE) {
        echo "<p class='success'>razorpay_payment_id column added successfully.</p>";
    } else {
        echo "<p class='error'>Error adding razorpay_payment_id column: " . $conn->error . "</p>";
    }
}

// Display all tables in the database
echo "<h2>Tables in the Database:</h2>";
$result = $conn->query("SHOW TABLES");
if ($result->num_rows > 0) {
    echo "<ul>";
    while($row = $result->fetch_row()) {
        echo "<li>" . $row[0] . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>No tables found in database.</p>";
}

// Display events table structure
echo "<h2>Events Table Structure:</h2>";
$result = $conn->query("SHOW COLUMNS FROM events");
if ($result->num_rows > 0) {
    echo "<table><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row["Field"] . "</td>";
        echo "<td>" . $row["Type"] . "</td>";
        echo "<td>" . $row["Null"] . "</td>";
        echo "<td>" . $row["Key"] . "</td>";
        echo "<td>" . $row["Default"] . "</td>";
        echo "<td>" . $row["Extra"] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Display bookings table structure
echo "<h2>Bookings Table Structure:</h2>";
$result = $conn->query("SHOW COLUMNS FROM bookings");
if ($result->num_rows > 0) {
    echo "<table><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row["Field"] . "</td>";
        echo "<td>" . $row["Type"] . "</td>";
        echo "<td>" . $row["Null"] . "</td>";
        echo "<td>" . $row["Key"] . "</td>";
        echo "<td>" . $row["Default"] . "</td>";
        echo "<td>" . $row["Extra"] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Check for data in events table
echo "<h2>Events Data Check:</h2>";
$result = $conn->query("SELECT COUNT(*) as count FROM events");
$row = $result->fetch_assoc();
echo "<p>Number of events in database: " . $row['count'] . "</p>";

if ($row['count'] == 0) {
    echo "<p class='info'>No events found. Visit the events page to generate demo events.</p>";
}

$conn->close();
echo "</body></html>";
?>