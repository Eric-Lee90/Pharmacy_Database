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
    </form>
    <a href="PharmacyServer.php">Back to Home</a>
</body>
</html>
