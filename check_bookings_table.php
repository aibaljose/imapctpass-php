<?php
// Initialize session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set cache control headers to prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Past date

// Check if user is logged in and is admin (user_id = 1)
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != 1) {
    header('Location: login.php');
    exit();
}

// Database connection
require_once 'config/config.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get bookings table structure
$result = $conn->query("DESCRIBE bookings");

echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookings Table Structure - ImpactPass</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <?php include "nav.php"; ?>
    
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-32">
        <div class="bg-white shadow-lg rounded-lg p-6">
            <h1 class="text-2xl font-bold mb-6">Bookings Table Structure</h1>
            <div class="overflow-x-auto">
                <table class="min-w-full border-collapse border border-gray-300">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="border border-gray-300 px-4 py-2 text-left">Field</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">Type</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">Null</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">Key</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">Default</th>
                            <th class="border border-gray-300 px-4 py-2 text-left">Extra</th>
                        </tr>
                    </thead>
                    <tbody>';

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . $row["Field"] . '</td>';
        echo '<td>' . $row["Type"] . '</td>';
        echo '<td>' . $row["Null"] . '</td>';
        echo '<td>' . $row["Key"] . '</td>';
        echo '<td>' . $row["Default"] . '</td>';
        echo '<td>' . $row["Extra"] . '</td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="6">No columns found</td></tr>';
}
echo '</table>';

// Get sample bookings data
$result = $conn->query("SELECT * FROM bookings");

echo '<h2>Bookings Data</h2>';
echo '<table border="1" cellpadding="5">';
echo '<tr>';
$result_fields = $result->fetch_fields();
foreach ($result_fields as $field) {
    echo '<th>' . $field->name . '</th>';
}
echo '</tr>';

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        echo '<tr>';
        foreach($row as $key => $value) {
            echo '<td>' . ($value === NULL ? 'NULL' : $value) . '</td>';
        }
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="' . count($result_fields) . '">No bookings found</td></tr>';
}
echo '</table>';

$conn->close();
?>