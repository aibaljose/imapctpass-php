<?php
// Initialize session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'api/apimethods.php';

// Create an instance of ApiMethods which will run the schema check
$api = new ApiMethods();

// HTML output for user-friendly display
echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Schema Update</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-3xl mx-auto bg-white p-8 rounded-lg shadow-md">
        <h1 class="text-2xl font-bold mb-6">Database Schema Update</h1>
        
        <div class="mb-8">
            <h2 class="text-xl font-semibold mb-4">Database Structure Check</h2>
            <p class="mb-4">This utility checks and updates the database schema to ensure all tables and columns exist with the correct structure.</p>
            
            <div class="bg-green-50 border border-green-200 rounded p-4 mb-4">
                <p class="text-green-800">Schema check completed. Any missing tables or columns have been added.</p>
            </div>
            
            <a href="check_columns.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 inline-block">
                View Detailed Database Structure
            </a>
        </div>
        
        <div class="mb-4">
            <a href="index.php" class="text-blue-600 hover:underline mr-4">Return to Home</a>
            <a href="events.php" class="text-blue-600 hover:underline">View Events</a>
        </div>
    </div>
</body>
</html>';
?>