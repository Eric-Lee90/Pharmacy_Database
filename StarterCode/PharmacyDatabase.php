<?php
class PharmacyDatabase {
    private $host = "localhost";
    private $port = "3306";
    private $database = "pharmacy_portal_db";
    private $user = "root";
    private $password = "";//no password is default
    public $connection;

    public function __construct() {
        $this->connect();
    }

    private function connect() {
        $this->connection = new mysqli($this->host, $this->user, $this->password, $this->database, $this->port);
        if ($this->connection->connect_error) {
            die("Connection failed: " . $this->connection->connect_error);
        }
        echo "Successfully connected to the database";
    }

    public function addPrescription($patientUserName, $medicationId, $dosageInstructions, $quantity)  {
        $stmt = $this->connection->prepare(
            "SELECT userId FROM Users WHERE userName = ? AND userType = 'patient'"
        );
        $stmt->bind_param("s", $patientUserName);
        $stmt->execute();
        $stmt->bind_result($patientId);
        $stmt->fetch();
        $stmt->close();
        
        if ($patientId){
            $stmt = $this->connection->prepare(
                "INSERT INTO prescriptions (userId, medicationId, dosageInstructions, quantity) VALUES (?, ?, ?, ?)"
            );
            $stmt->bind_param("iisi", $patientId, $medicationId, $dosageInstructions, $quantity);
            $stmt->execute();
            $stmt->close();
            echo "Prescription added successfully";
        }else{
            echo "failed to add prescription";
        }
    }

    public function getAllPrescriptions() {
        $result = $this->connection->query("SELECT * FROM  prescriptions join medications on prescriptions.medicationId= medications.medicationId");
        return $result->fetch_all(MYSQLI_ASSOC);
    }
        /*
        Complete this function to test the functionality of
        MedicationInventoryView and implement it in the server
        */

    // Function to retrieve medication inventory
    public function getMedicationInventory() {
        $query = "SELECT medicationName, dosage, manufacturer, quantityAvailable FROM MedicationInventoryView";
        $result = $this->connection->query($query);

        if ($result->num_rows > 0) {
            $medications = [];
            while ($row = $result->fetch_assoc()) {
                $medications[] = $row;
            }
            return $medications;
        } else {
            return [];
        }
    }

    // Method to add medication to the database
    public function addMedication($medicationName, $dosage, $manufacturer) {
        $stmt = $this->connection->prepare("
            INSERT INTO Medications (medicationName, dosage, manufacturer) 
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param("sss", $medicationName, $dosage, $manufacturer);

        if ($stmt->execute()) {
            echo "Medication added successfully.";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    }

    public function addUser($userName, $contactInfo, $userType) {
     
        $checkStmt = $this->connection->prepare("SELECT userId FROM Users WHERE userName = ?");
        $checkStmt->bind_param("s", $userName);
        $checkStmt->execute();
        $checkStmt->store_result();

        if ($checkStmt->num_rows > 0) {
            echo "User already exists.";
            return;
        }
        $checkStmt->close();

        // Insert new user
        $stmt = $this->connection->prepare("
            INSERT INTO Users (userName, contactInfo, userType) 
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param("sss", $userName, $contactInfo, $userType);

        if ($stmt->execute()) {
            echo "User added successfully.";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    }


    // Method to retrieve user details, including prescriptions
    public function getUserDetails($userId) {
        $stmt = $this->connection->prepare("
            SELECT u.userId, u.userName, u.contactInfo, u.userType, 
                   p.prescriptionId, p.medicationId, p.prescribedDate, p.dosageInstructions, p.quantity, p.refillCount
            FROM Users u
            LEFT JOIN Prescriptions p ON u.userId = p.userId
            WHERE u.userId = ?
        ");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        $userDetails = [];
        while ($row = $result->fetch_assoc()) {
            $userDetails[] = $row;
        }

        $stmt->close();
        return $userDetails;
    }

    // Close database connection
    public function closeConnection() {
        $this->connection->close();
    }
}