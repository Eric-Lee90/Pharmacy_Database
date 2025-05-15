<?php
session_start();

// Ensure only pharmacists can access this page
if (!isset($_SESSION['userType']) || $_SESSION['userType'] !== 'pharmacist') {
    header("Location: login.php");
    exit();
}

require_once 'PharmacyDatabase.php';

// Initialize database connection
$db = new PharmacyDatabase("localhost", "root", "", "pharmacy_portal_db");
if ($db->conn->connect_error) {
    die("Database connection failed: " . $db->conn->connect_error);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pharmacist Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h1>Welcome, Pharmacist</h1>

    <h2>Inventory Overview</h2>
    <table>
        <tr><th>Medication Name</th><th>Dosage</th><th>Quantity Available</th></tr>
        <?php
        $inventory = $db->getMedicationInventory();
        foreach ($inventory as $item) {
            echo "<tr><td>{$item['medicationName']}</td><td>{$item['dosage']}</td><td>{$item['quantityAvailable']}</td></tr>";
        }
        ?>
    </table>

    <h2>Prescription Records</h2>
    <table>
        <tr><th>Patient Name</th><th>Medication</th><th>Dosage</th><th>Quantity</th></tr>
        <?php
        $prescriptions = $db->getAllPrescriptions();
        foreach ($prescriptions as $prescription) {
            echo "<tr><td>{$prescription['userName']}</td><td>{$prescription['medicationName']}</td><td>{$prescription['dosage']}</td><td>{$prescription['quantity']}</td></tr>";
        }
        ?>
    </table>

    <h2>Manage Users</h2>
    <table>
        <tr><th>User Name</th><th>Contact Info</th><th>User Type</th></tr>
        <?php 
        $users = $db->getUserDetails();
        foreach ($users as $user) {
            echo "<tr><td>{$user['userName']}</td><td>{$user['contactInfo']}</td><td>{$user['userType']}</td></tr>";
        }
        ?>
    </table>

    <br>
    <a href="logout.php">Logout</a>
</body>
</html>
