-- phpMyAdmin SQL Dump
-- version 4.6.5.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Aug 18, 2018 at 02:54 PM
-- Server version: 5.7.14
-- PHP Version: 5.6.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `upper`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(400) NOT NULL,
  `privilege` int(11) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `privilege`) VALUES
(1, 'admin', '21232f297a57a5a743894a0e4a801fc3', 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_profile`
--

CREATE TABLE `user_profile` (
  `id` int(11) NOT NULL,
  `surname` varchar(50) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(14) NOT NULL,
  `cover` text NOT NULL,
  `passport` varchar(100) DEFAULT NULL,
  `cv` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `user_profile`
--

INSERT INTO `user_profile` (`id`, `surname`, `firstname`, `email`, `phone`, `cover`, `passport`, `cv`) VALUES
(1, 'jnfsdjnfsj', 'fksjfksjn', 'sdkfnkdfn@Eknfdfn', '09034399', 'fsdfjbajf', NULL, NULL),
(2, 'dfsfd', 'fdsfsf', 'fsdfsd', 'fsdfsf', 'fsdfsdf', NULL, NULL),
(3, 'dfsfd', 'fdsfsf', 'fsdfsd', '888888', 'fsdfsdf', NULL, NULL),
(4, 'dfsfd', 'fdsfsf', 'fsdfsd', '888888', 'fsdfsdf', NULL, NULL),
(6, 'dfsfd', 'fdsfsf', 'fsdfsd', '888888', 'fsdfsdf', NULL, NULL),
(7, 'dfsfd', 'fdsfsf', 'fsdfsd', '888888', 'fsdfsdf', NULL, NULL),
(11, 'dfsfd', 'fdsfsf', 'fsdfsd', '888888', 'fsdfsdf', '2017-04-17-11-18-56-306.jpg', 'izuu2.jpg'),
(12, 'fsdfnsjfn', 'ifrmfdsk', 'fsdfsmm', 'fsdfksffsdkm', 'fdsfdsf', 'izuu2.jpg', '2017-04-17-11-18-56-306.jpg');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_profile`
--
ALTER TABLE `user_profile`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `user_profile`
--
ALTER TABLE `user_profile`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
