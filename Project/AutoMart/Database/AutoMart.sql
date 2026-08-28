-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Aug 29, 2026 at 12:50 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `AutoMart`
--

-- --------------------------------------------------------

--
-- Table structure for table `CAR_REQUEST`
--

CREATE TABLE `CAR_REQUEST` (
  `Request_Id` int(11) NOT NULL,
  `Customer_Email` varchar(100) NOT NULL,
  `Car_Make` varchar(50) NOT NULL,
  `Car_Model` varchar(50) NOT NULL,
  `Year_Range` varchar(20) NOT NULL,
  `Max_Budget` int(11) NOT NULL,
  `Color` varchar(50) DEFAULT NULL,
  `Additional_Notes` text DEFAULT NULL,
  `Status` varchar(20) DEFAULT 'Pending',
  `Request_Date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `CAR_REQUEST`
--

INSERT INTO `CAR_REQUEST` (`Request_Id`, `Customer_Email`, `Car_Make`, `Car_Model`, `Year_Range`, `Max_Budget`, `Color`, `Additional_Notes`, `Status`, `Request_Date`) VALUES
(1, 'customer@gmail.com', 'BMW', 'M4 CS', '2024-2025', 19999, NULL, 'brand new', 'Cancelled', '2026-08-28 17:56:53'),
(2, 'customer@gmail.com', 'Audi', 'M4 CS', '2024-2025', 56772, 'silver', 'no', 'Cancelled', '2026-08-28 18:43:12'),
(3, 'customer@gmail.com', 'Toyota', 'RAV4', '2023', 30000, 'White', '', 'Pending', '2026-08-28 20:17:17'),
(4, 'customer2@gmail.com', 'Honda', 'Civic', '2023', 25000, 'Red', '', 'Cancelled', '2026-08-28 21:44:55');

-- --------------------------------------------------------

--
-- Table structure for table `SALE`
--

CREATE TABLE `SALE` (
  `Sale_Id` int(11) NOT NULL,
  `Customer_Email` varchar(100) NOT NULL,
  `Phone` varchar(20) DEFAULT NULL,
  `Address` text DEFAULT NULL,
  `Vehicle_Id` int(11) NOT NULL,
  `Sale_Price` int(11) NOT NULL,
  `Payment_Method` varchar(50) DEFAULT NULL,
  `Status` varchar(20) DEFAULT 'Pending',
  `Order_Date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `SALE`
--

INSERT INTO `SALE` (`Sale_Id`, `Customer_Email`, `Phone`, `Address`, `Vehicle_Id`, `Sale_Price`, `Payment_Method`, `Status`, `Order_Date`) VALUES
(1, 'customer@gmail.com', NULL, NULL, 1, 90000, NULL, 'Cancelled', '2026-08-28 15:20:08'),
(2, 'customer@gmail.com', NULL, NULL, 1, 90000, NULL, 'Cancelled', '2026-08-28 16:08:03'),
(3, 'customer@gmail.com', NULL, NULL, 1, 90000, NULL, 'Cancelled', '2026-08-28 16:10:53'),
(4, 'customer@gmail.com', NULL, NULL, 1, 90000, NULL, 'Cancelled', '2026-08-28 20:58:17'),
(5, 'customer@gmail.com', '8801877763483', 'dwd', 2, 25000, 'Bank Transfer', 'Approved', '2026-08-28 21:20:57'),
(6, 'customer@gmail.com', '888', 'Ab', 1, 90000, 'Credit Card', 'Approved', '2026-08-28 21:36:20'),
(7, 'customer2@gmail.com', '1111', 'wdwdw', 4, 48000, 'Cash on Delivery', 'Approved', '2026-08-28 21:44:04');

-- --------------------------------------------------------

--
-- Table structure for table `TRADE_IN`
--

CREATE TABLE `TRADE_IN` (
  `Trade_Id` int(11) NOT NULL,
  `Customer_Email` varchar(100) NOT NULL,
  `Car_Make` varchar(50) NOT NULL,
  `Car_Model` varchar(50) NOT NULL,
  `Year` int(11) NOT NULL,
  `Mileage` int(11) NOT NULL,
  `Condition_Status` varchar(50) NOT NULL,
  `Expected_Price` int(11) NOT NULL,
  `Status` varchar(20) DEFAULT 'Pending',
  `Request_Date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `TRADE_IN`
--

INSERT INTO `TRADE_IN` (`Trade_Id`, `Customer_Email`, `Car_Make`, `Car_Model`, `Year`, `Mileage`, `Condition_Status`, `Expected_Price`, `Status`, `Request_Date`) VALUES
(1, 'customer@gmail.com', 'BMWscs', 'scs', 2013, 3437, 'Good', 123344, 'Pending', '2026-08-28 20:26:23'),
(2, 'customer@gmail.com', 'BMW', 'M4 CS', 30202, 233, 'Poor', 12, 'Cancelled', '2026-08-28 20:30:23');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Owner','Customer','Supplier','Evaluator') NOT NULL,
  `security_question` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `role`, `security_question`) VALUES
(12, 'owner', 'owner@gmail.com', '1234', 'Owner', 'enamul'),
(13, 'customer', 'customer@gmail.com', '1234', 'Customer', 'enamul'),
(14, 'supplier', 'supplier@gmail.com', '1234', 'Supplier', 'enamul'),
(15, 'evaluator', 'evaluator@gmail.com', '1234', 'Evaluator', 'enamul'),
(16, 'customer2', 'customer2@gmail.com', '1234', 'Customer', 'enamul'),
(17, 'customer3', 'customer3@gmail.com', '12345', 'Customer', 'enamul');

-- --------------------------------------------------------

--
-- Table structure for table `VEHICLE`
--

CREATE TABLE `VEHICLE` (
  `Vehicle_Id` int(11) NOT NULL,
  `Make` varchar(50) NOT NULL,
  `Model` varchar(50) NOT NULL,
  `Year` int(11) NOT NULL,
  `Condition_Status` varchar(200) NOT NULL,
  `Color` varchar(30) NOT NULL,
  `Body_Type` varchar(50) NOT NULL,
  `Mileage` int(11) NOT NULL,
  `Listed_Price` int(11) NOT NULL,
  `Availability` varchar(50) NOT NULL,
  `Image` varchar(255) DEFAULT 'default_car.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `VEHICLE`
--

INSERT INTO `VEHICLE` (`Vehicle_Id`, `Make`, `Model`, `Year`, `Condition_Status`, `Color`, `Body_Type`, `Mileage`, `Listed_Price`, `Availability`, `Image`) VALUES
(1, 'BMW', 'M4 Competition', 2025, 'Brand New', 'Silver', 'Coupe', 0, 90000, 'Not Available', 'bmw_m4.png'),
(2, 'Honda', 'Civic', 2023, 'Used', 'Red', 'Sedan', 9000, 25000, 'Not Available', 'honda_civic.png'),
(3, 'Toyota', 'RAV4', 2023, 'Used', 'White', 'SUV', 24000, 30000, 'Not Available', 'toyota_rav4.png'),
(4, 'Mercedes-Benz', 'C-Class', 2024, 'Brand New', 'Black', 'Sedan', 0, 48000, 'Not Available', 'default_car.png'),
(5, 'Audi', 'RS6 Avant', 2025, 'Brand New', 'Nardo Gray', 'Wagon', 0, 125000, 'Available', 'default_car.png'),
(6, 'Ford', 'Mustang GT', 2022, 'Used', 'Blue', 'Coupe', 18500, 36000, 'Available', 'default_car.png');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `CAR_REQUEST`
--
ALTER TABLE `CAR_REQUEST`
  ADD PRIMARY KEY (`Request_Id`);

--
-- Indexes for table `SALE`
--
ALTER TABLE `SALE`
  ADD PRIMARY KEY (`Sale_Id`);

--
-- Indexes for table `TRADE_IN`
--
ALTER TABLE `TRADE_IN`
  ADD PRIMARY KEY (`Trade_Id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `VEHICLE`
--
ALTER TABLE `VEHICLE`
  ADD PRIMARY KEY (`Vehicle_Id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `CAR_REQUEST`
--
ALTER TABLE `CAR_REQUEST`
  MODIFY `Request_Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `SALE`
--
ALTER TABLE `SALE`
  MODIFY `Sale_Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `TRADE_IN`
--
ALTER TABLE `TRADE_IN`
  MODIFY `Trade_Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `VEHICLE`
--
ALTER TABLE `VEHICLE`
  MODIFY `Vehicle_Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
