<?php
session_start();
require_once 'PharmacyDatabase.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $db = new PharmacyDatabase("localhost", "root", "", "pharmacy_portal_db");
    $userName = $_POST['userName'];
    $password = $_POST['password'];

    $stmt = $db->connection->prepare("SELECT userId, userType, password FROM Users WHERE userName = ?");
    $stmt->bind_param("s", $userName);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['userId'] = $row['userId'];
            $_SESSION['userType'] = $row['userType'];

            if ($row['userType'] === 'pharmacist') {
                header("Location: PharmacyServer.php");
                exit();
            } else {
                header("Location: PharmacyServer.php");
            }
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
            // User does not exist, create new account
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $defaultUserType = "patient"; // You can change this if needed
    
            $insertStmt = $db->connection->prepare("INSERT INTO Users (userName, password, userType) VALUES (?, ?, ?)");
            $insertStmt->bind_param("sss", $userName, $hashedPassword, $defaultUserType);
    
            if ($insertStmt->execute()) {
                $_SESSION['userId'] = $db->conn->insert_id;
                $_SESSION['userType'] = $defaultUserType;
                
                header("Location: PharmacyServer.php"); // Redirect new users to the patient dashboard
                exit();
            } else {
                $error = "Error creating account.";
            }
    
            $insertStmt->close();
        }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Pharmacy Portal</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h2>Login</h2>
    <?php if (!empty($error)) echo "<p style='color: red;'>$error</p>"; ?>
    <form method="POST" action="login.php">
        <label for="userName">Username:</label>
        <input type="text" id="userName" name="userName" required>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Login</button>
    </form>
</body>
</html>

