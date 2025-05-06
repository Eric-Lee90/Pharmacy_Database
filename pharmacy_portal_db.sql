-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 06, 2025 at 05:32 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pharmacy_portal_db`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `AddOrUpdateUser` (IN `p_userId` INT, IN `p_userName` VARCHAR(45), IN `p_contactInfo` VARCHAR(200), IN `p_userType` ENUM('pharmacist','patient'))   BEGIN
    IF EXISTS (SELECT 1 FROM Users WHERE userId = p_userId) THEN
        -- Update existing user
        UPDATE Users
        SET userName = p_userName,
            contactInfo = p_contactInfo,
            userType = p_userType
        WHERE userId = p_userId;
    ELSE
        -- Insert new user
        INSERT INTO Users (userName, contactInfo, userType)
        VALUES (p_userName, p_contactInfo, p_userType);
    END IF;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `ProcessSale` (IN `p_prescriptionId` INT, IN `p_quantitySold` INT)   BEGIN
    DECLARE v_medicationId INT;
    DECLARE v_saleAmount DECIMAL(10,2);

    -- Retrieve medication ID and sale amount from prescription
    SELECT medicationId, (p_quantitySold * unitPrice) INTO v_medicationId, v_saleAmount
    FROM prescriptions
    WHERE prescriptionId = p_prescriptionId;

    -- Update inventory
    UPDATE inventory
    SET quantityAvailable = quantityAvailable - p_quantitySold
    WHERE medicationId = v_medicationId;

    -- Insert sale record
    INSERT INTO sales (prescriptionId, saleDate, quantitySold, saleAmount)
    VALUES (p_prescriptionId, NOW(), p_quantitySold, v_saleAmount);
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `inventoryId` int(11) NOT NULL,
  `medicationId` int(11) NOT NULL,
  `quantityAvailable` int(11) NOT NULL,
  `lastUpdated` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `medicationinventoryview`
-- (See below for the actual view)
--
CREATE TABLE `medicationinventoryview` (
`medicationName` varchar(45)
,`dosage` varchar(45)
,`manufacturer` varchar(100)
,`quantityAvailable` int(11)
);

-- --------------------------------------------------------

--
-- Table structure for table `medications`
--

CREATE TABLE `medications` (
  `medicationId` int(11) NOT NULL,
  `medicationName` varchar(45) NOT NULL,
  `dosage` varchar(45) NOT NULL,
  `manufacturer` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prescriptions`
--

CREATE TABLE `prescriptions` (
  `prescriptionId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `medicationId` int(11) NOT NULL,
  `prescribedDate` datetime NOT NULL,
  `dosageInstructions` varchar(200) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `refillCount` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `prescriptions`
--
DELIMITER $$
CREATE TRIGGER `AfterPrescriptionInsert` AFTER INSERT ON `prescriptions` FOR EACH ROW BEGIN
    -- Update inventory
    UPDATE Inventory
    SET quantityAvailable = quantityAvailable - NEW.quantity
    WHERE medicationId = NEW.medicationId;

    -- Check for low stock and notify
    IF (SELECT quantityAvailable FROM Inventory WHERE medicationId = NEW.medicationId) < 10 THEN
        INSERT INTO Notifications (message, medicationId, alertDate)
        VALUES (CONCAT('Low stock alert for medication ID: ', NEW.medicationId), NEW.medicationId, NOW());
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `saleId` int(11) NOT NULL,
  `prescriptionId` int(11) NOT NULL,
  `saleDate` datetime NOT NULL,
  `quantitySold` int(11) NOT NULL,
  `saleAmount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `userId` int(11) NOT NULL,
  `userName` varchar(45) NOT NULL,
  `contactInfo` varchar(200) DEFAULT NULL,
  `userType` enum('pharmacist','patient') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`userId`, `userName`, `contactInfo`, `userType`) VALUES
(1, 'JohnDoe', 'johndoe@example.com', 'pharmacist');

-- --------------------------------------------------------

--
-- Structure for view `medicationinventoryview`
--
DROP TABLE IF EXISTS `medicationinventoryview`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `medicationinventoryview`  AS SELECT `m`.`medicationName` AS `medicationName`, `m`.`dosage` AS `dosage`, `m`.`manufacturer` AS `manufacturer`, `i`.`quantityAvailable` AS `quantityAvailable` FROM (`medications` `m` join `inventory` `i` on(`m`.`medicationId` = `i`.`medicationId`)) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`inventoryId`),
  ADD KEY `medicationId` (`medicationId`);

--
-- Indexes for table `medications`
--
ALTER TABLE `medications`
  ADD PRIMARY KEY (`medicationId`);

--
-- Indexes for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD UNIQUE KEY `prescriptionId` (`prescriptionId`),
  ADD KEY `userId` (`userId`),
  ADD KEY `medicationId` (`medicationId`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`saleId`),
  ADD UNIQUE KEY `saleId` (`saleId`),
  ADD KEY `prescriptionId` (`prescriptionId`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`userId`),
  ADD UNIQUE KEY `userName` (`userName`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `inventoryId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medications`
--
ALTER TABLE `medications`
  MODIFY `medicationId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `prescriptions`
--
ALTER TABLE `prescriptions`
  MODIFY `prescriptionId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `saleId` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `userId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`medicationId`) REFERENCES `medications` (`medicationId`);

--
-- Constraints for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD CONSTRAINT `prescriptions_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`userId`),
  ADD CONSTRAINT `prescriptions_ibfk_2` FOREIGN KEY (`medicationId`) REFERENCES `medications` (`medicationId`);

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`prescriptionId`) REFERENCES `prescriptions` (`prescriptionId`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
