<html>
<head><title>Add Prescription</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
    <h1>Add Reservation</h1>
    <form method="POST" action="?action=addPrescription">
        Patient Username: <input type="text" name="patient_username" /><br>
        Medication ID : <input type="number" name="medication_id"/><br>
        Dosage Instructions: <textarea name="dosage_instructions"></textarea><br>
        Quantity: <input type="number" name="quantity" /><br>
        <button type="submit">Save</button>
        <form method="POST" action="addPrescription.php">

    </form>
    <a href="PharmacyServer.php">Back to Home</a>
</body>
</html>

<?php
session_start();
require_once 'PharmacyDatabase.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Initialize Database Connection
    $db = new PharmacyDatabase("localhost", "root", "", "pharmacy_portal_db");

    // Retrieve Form Data
    $patient_username = $_POST['patient_username'];
    $medication_id = $_POST['medication_id'];
    $dosage_instructions = $_POST['dosage_instructions'];
    $quantity = $_POST['quantity'];

    // Validate Input
    if (empty($patient_username) || empty($medication_id) || empty($dosage_instructions) || empty($quantity)) {
        echo "All fields are required.";
        exit();
    }

    // Insert Data into the Prescriptions Table
    $stmt = $db->conn->prepare("INSERT INTO Prescriptions (patient_username, medication_id, dosage_instructions, quantity) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("siss", $patient_username, $medication_id, $dosage_instructions, $quantity);

    if ($stmt->execute()) {
        echo "Prescription successfully saved!";
        header("Location: PharmacyServer.php"); // Redirect back to home after saving
        exit();
    } else {
        echo "Error saving prescription.";
    }

    $stmt->close();
}
?>

