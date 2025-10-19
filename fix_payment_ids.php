<?php
// Initialize session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
    <title>Payment ID Fix</title>
    <script src='https://cdn.tailwindcss.com'></script>
</head>
<body class='bg-gray-100 p-8'>
    <div class='max-w-4xl mx-auto bg-white rounded-lg shadow-md p-6'>
        <h1 class='text-2xl font-bold mb-6'>Booking Payment ID Fix Utility</h1>
";

// Check for both payment_id and razorpay_payment_id columns
$payment_id_exists = $conn->query("SHOW COLUMNS FROM bookings LIKE 'payment_id'")->num_rows > 0;
$razorpay_payment_id_exists = $conn->query("SHOW COLUMNS FROM bookings LIKE 'razorpay_payment_id'")->num_rows > 0;

echo "<div class='mb-6 p-4 border rounded-lg bg-gray-50'>";
echo "<h2 class='text-xl font-semibold mb-2'>Column Status:</h2>";
echo "<p>payment_id column: " . ($payment_id_exists ? "<span class='text-green-600'>Exists</span>" : "<span class='text-red-600'>Missing</span>") . "</p>";
echo "<p>razorpay_payment_id column: " . ($razorpay_payment_id_exists ? "<span class='text-green-600'>Exists</span>" : "<span class='text-red-600'>Missing</span>") . "</p>";
echo "</div>";

// Get payment info from all bookings
$bookings = $conn->query("SELECT * FROM bookings");

if ($bookings->num_rows > 0) {
    // Show booking data
    echo "<div class='mb-6'>";
    echo "<h2 class='text-xl font-semibold mb-4'>Current Booking Data:</h2>";
    echo "<div class='overflow-x-auto'>";
    echo "<table class='w-full border-collapse'>";
    echo "<thead class='bg-gray-100'>";
    echo "<tr>";
    
    $columns = [];
    $result_fields = $bookings->fetch_fields();
    foreach ($result_fields as $field) {
        echo "<th class='border p-2 text-left'>" . $field->name . "</th>";
        $columns[] = $field->name;
    }
    
    echo "</tr>";
    echo "</thead>";
    echo "<tbody>";
    
    $bookings->data_seek(0);
    while($row = $bookings->fetch_assoc()) {
        echo "<tr>";
        foreach($columns as $column) {
            echo "<td class='border p-2'>" . ($row[$column] === NULL ? "<span class='text-gray-400'>NULL</span>" : htmlspecialchars($row[$column])) . "</td>";
        }
        echo "</tr>";
    }
    
    echo "</tbody>";
    echo "</table>";
    echo "</div>";
    echo "</div>";
    
    // Fix action
    if (isset($_POST['fix']) && $_POST['fix'] == 'yes') {
        echo "<div class='mb-6 p-4 border rounded-lg bg-green-50'>";
        echo "<h2 class='text-xl font-semibold mb-2'>Fix Applied:</h2>";
        
        // Add missing columns
        if (!$payment_id_exists) {
            $conn->query("ALTER TABLE bookings ADD COLUMN payment_id VARCHAR(255) NULL");
            echo "<p class='text-green-700'>Added payment_id column.</p>";
        }
        
        // Copy data if needed
        if ($payment_id_exists && $razorpay_payment_id_exists) {
            // Copy any data from razorpay_payment_id to payment_id if payment_id is NULL
            $conn->query("UPDATE bookings SET payment_id = razorpay_payment_id WHERE payment_id IS NULL AND razorpay_payment_id IS NOT NULL");
            // Copy any data from payment_id to razorpay_payment_id if razorpay_payment_id is NULL
            $conn->query("UPDATE bookings SET razorpay_payment_id = payment_id WHERE razorpay_payment_id IS NULL AND payment_id IS NOT NULL");
            echo "<p class='text-green-700'>Synchronized payment data between columns.</p>";
        }
        
        echo "</div>";
        
        // Show updated booking data
        $bookings = $conn->query("SELECT * FROM bookings");
        
        if ($bookings->num_rows > 0) {
            echo "<div class='mb-6'>";
            echo "<h2 class='text-xl font-semibold mb-4'>Updated Booking Data:</h2>";
            echo "<div class='overflow-x-auto'>";
            echo "<table class='w-full border-collapse'>";
            echo "<thead class='bg-gray-100'>";
            echo "<tr>";
            
            $columns = [];
            $result_fields = $bookings->fetch_fields();
            foreach ($result_fields as $field) {
                echo "<th class='border p-2 text-left'>" . $field->name . "</th>";
                $columns[] = $field->name;
            }
            
            echo "</tr>";
            echo "</thead>";
            echo "<tbody>";
            
            $bookings->data_seek(0);
            while($row = $bookings->fetch_assoc()) {
                echo "<tr>";
                foreach($columns as $column) {
                    echo "<td class='border p-2'>" . ($row[$column] === NULL ? "<span class='text-gray-400'>NULL</span>" : htmlspecialchars($row[$column])) . "</td>";
                }
                echo "</tr>";
            }
            
            echo "</tbody>";
            echo "</table>";
            echo "</div>";
            echo "</div>";
        }
    } else {
        // Show fix form
        echo "<div class='mb-6'>";
        echo "<form method='post' action=''>";
        echo "<input type='hidden' name='fix' value='yes'>";
        echo "<button type='submit' class='bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700'>Fix Payment ID Columns</button>";
        echo "</form>";
        echo "</div>";
    }
} else {
    echo "<div class='mb-6 p-4 border rounded-lg bg-yellow-50'>";
    echo "<p class='text-yellow-700'>No bookings found in the database.</p>";
    echo "</div>";
}

// Navigation links
echo "<div class='mt-6'>";
echo "<a href='events.php' class='text-blue-600 hover:underline mr-4'>Back to Events</a>";
echo "<a href='check_columns.php' class='text-blue-600 hover:underline'>Check Database Structure</a>";
echo "</div>";

echo "</div>";
echo "</body></html>";

$conn->close();
?>