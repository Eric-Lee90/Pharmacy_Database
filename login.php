<?php
session_start();
require_once 'PharmacyDatabase.php';

class LoginSystem {
    private $conn;

    public function __construct($host, $username, $password, $dbname) {
        $this->conn = new mysqli($host, $username, $password, $dbname);
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    public function loginUser($userName, $password) {
        $stmt = $this->conn->prepare("SELECT userId, userType, password FROM Users WHERE userName = ?");
        $stmt->bind_param("s", $userName);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            if (password_verify($password, $row['password'])) {
                $_SESSION['userId'] = $row['userId'];
                $_SESSION['userType'] = $row['userType'];

                if ($row['userType'] === 'pharmacist') {
                    header("Location: pharmacist_dashboard.php");
                } else {
                    header("Location: patient_dashboard.php");
                }
                exit();
            } else {
                echo "Invalid password.";
            }
        } else {
            echo "User not found.";
        }

        $stmt->close();
    }

    public function logoutUser() {
        session_destroy();
        header("Location: login.php");
        exit();
    }
}

// Example Usage
$db = new LoginSystem("localhost", "root", "", "pharmacy_portal_db");
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $db->loginUser($_POST['userName'], $_POST['password']);
}
?>
