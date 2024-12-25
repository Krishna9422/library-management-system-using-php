-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 19, 2024 at 09:24 PM
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
-- Database: `librarydb`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `AddBookCopies` (IN `p_title` VARCHAR(255), IN `p_author` VARCHAR(255), IN `p_publishing_year` INT, IN `p_copies` INT)   BEGIN
    DECLARE i INT DEFAULT 1;
    DECLARE code_prefix VARCHAR(10) DEFAULT 'BOOK';

    WHILE i <= p_copies DO
        INSERT INTO book (title, author, publishing_year, total_copies, loaned_copies, book_code)
        VALUES (
            p_title,
            p_author,
            p_publishing_year,
            1,
            0,
            CONCAT(code_prefix, '-', LPAD(i, 3, '0'))
        );
        SET i = i + 1;
    END WHILE;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GenerateBookCopies` ()   BEGIN
    DECLARE i INT;
    DECLARE total INT;
    DECLARE book_title VARCHAR(255);
    DECLARE book_isbn VARCHAR(20);
    DECLARE book_id INT;

    DECLARE cur CURSOR FOR 
        SELECT book_id, title, isbn, available_copies FROM books;

    OPEN cur;

    read_loop: LOOP
        FETCH cur INTO book_id, book_title, book_isbn, total;

        IF total IS NULL THEN
            LEAVE read_loop;
        END IF;

        SET i = 1;
        
        copy_loop: WHILE i <= total DO
            INSERT INTO book_copies (book_id, title, isbn, referral_code)
            VALUES (
                book_id, 
                book_title, 
                book_isbn, 
                CONCAT(isbn, '-', LPAD(i, 4, '0')) -- Custom referral code format
            );
            SET i = i + 1;
        END WHILE;
    END LOOP;

    CLOSE cur;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `GenerateBookCopies11` ()   BEGIN
    DECLARE done INT DEFAULT 0;
    DECLARE i INT DEFAULT 1;
    DECLARE total INT;
    DECLARE book_title VARCHAR(255);
    DECLARE book_isbn VARCHAR(20);
    DECLARE book_id INT;

    -- Cursor to iterate over all books in the books table
    DECLARE cur CURSOR FOR
        SELECT book_id, title, isbn, available_copies FROM books;

    -- Declare a handler to handle the end of the cursor
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = 1;

    -- Open the cursor
    OPEN cur;

    -- Loop through each book
    read_loop: LOOP
        -- Fetch the next book's details
        FETCH cur INTO book_id, book_title, book_isbn, total;

        -- If there are no more rows, exit the loop
        IF done = 1 THEN
            LEAVE read_loop;
        END IF;

        -- Check if the ISBN already exists in the book_copies table
        IF NOT EXISTS (
            SELECT 1 FROM book_copies WHERE isbn = book_isbn
        ) THEN
            -- Only proceed if the ISBN is not already in book_copies
            SET i = 1;

            copy_loop: WHILE i <= total DO
                INSERT INTO book_copies (book_id, title, isbn, referral_code)
                VALUES (
                    book_id, 
                    book_title, 
                    book_isbn, 
                    CONCAT(book_isbn, '-', LPAD(i, 4, '0')) -- Custom referral code format
                );
                SET i = i + 1;
            END WHILE;
        END IF;
    END LOOP;

    -- Close the cursor
    CLOSE cur;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `name`, `email`, `password`) VALUES
(1, 'krishna', 'krishna9040@gmail.com', '*00A51F3F48415C7D4E8908980D443C29C69B60C9'),
(2, 'Admin Name', 'rajurkar_am@mgmcen.ac.in', '*00A51F3F48415C7D4E8908980D443C29C69B60C9');

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `book_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(255) NOT NULL,
  `publishing_year` int(11) DEFAULT NULL,
  `total_copies` int(11) DEFAULT 1,
  `loaned_copies` int(11) DEFAULT 0,
  `available_copies` int(11) GENERATED ALWAYS AS (`total_copies` - `loaned_copies`) STORED,
  `isbn` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`book_id`, `title`, `author`, `publishing_year`, `total_copies`, `loaned_copies`, `isbn`) VALUES
(1, 'Engineering Mechanics: Dynamics', 'J.L. Meriam', 2015, 10, 2, 101),
(2, 'Fundamentals of Thermodynamics', 'Richard E. Sonntag', 2018, 5, 2, 102),
(3, 'Control Systems Engineering', 'Nise', 2019, 7, 7, 103),
(4, 'Introduction to Electrical Engineering', 'M. H. Rashid', 2016, 8, 0, 104),
(5, 'Engineering Fluid Mechanics', 'Clayton T. Crowe', 2020, 6, 3, 105),
(6, 'Computer Networking: A Top-Down Approach', 'James Kurose', 2021, 10, 2, 106),
(7, 'Introduction to Robotics: Mechanics and Control', 'John J. Craig', 2019, 7, 3, 107),
(8, 'Fundamentals of Electric Circuits', 'Alexander & Sadiku', 2020, 9, 4, 108),
(9, 'Design of Concrete Structures', 'Arthur H. Nilson', 2016, 8, 1, 109),
(10, 'Introduction to Chemical Engineering', 'Warren McCabe', 2018, 6, 0, 110),
(11, 'Software Engineering', 'Ian Sommerville', 2020, 12, 3, 111),
(12, 'Engineering Economy', 'Leland Blank', 2017, 5, 5, 112),
(13, 'Digital Signal Processing', 'John G. Proakis', 2021, 10, 5, 113),
(14, 'Engineering Mechanics: Statics', 'J.L. Meriam', 2018, 11, 2, 114),
(15, 'Microelectronic Circuits', 'Adel S. Sedra', 2019, 4, 0, 115),
(16, 'Engineering Mechanics: Dynamics', 'J.L. Meriam', 2015, 10, 4, 1),
(17, 'Fundamentals of Thermodynamics', 'Richard E. Sonntag', 2018, 5, 2, 2),
(18, 'Control Systems Engineering', 'Nise', 2019, 7, 3, 3),
(19, 'Introduction to Electrical Engineering', 'M. H. Rashid', 2016, 8, 1, 4),
(20, 'Engineering Fluid Mechanics', 'Clayton T. Crowe', 2020, 6, 2, 5),
(21, 'Introduction to Computer Engineering', 'M. Morris Mano', 2017, 12, 4, 6),
(22, 'Mechanical Engineering Design', 'Joseph Shigley', 2014, 4, 1, 7),
(23, 'Engineering Statistics', 'D. C. Montgomery', 2019, 3, 1, 8),
(24, 'Materials Science and Engineering', 'William D. Callister', 2015, 9, 0, 9),
(25, 'Digital Logic Design', 'M. Morris Mano', 2018, 5, 1, 10),
(26, 'Computer Networking: A Top-Down Approach', 'James Kurose', 2021, 10, 2, 11),
(27, 'Introduction to Robotics: Mechanics and Control', 'John J. Craig', 2019, 7, 3, 12),
(28, 'Fundamentals of Electric Circuits', 'Alexander & Sadiku', 2020, 9, 5, 13),
(29, 'Design of Concrete Structures', 'Arthur H. Nilson', 2016, 8, 2, 14),
(30, 'Introduction to Chemical Engineering', 'Warren McCabe', 2018, 6, 1, 15),
(31, 'Software Engineering', 'Ian Sommerville', 2020, 12, 6, 16),
(32, 'Engineering Economy', 'Leland Blank', 2017, 5, 2, 17),
(33, 'Digital Signal Processing', 'John G. Proakis', 2021, 10, 6, 18),
(34, 'Engineering Mechanics: Statics', 'J.L. Meriam', 2018, 11, 3, 19),
(35, 'Microelectronic Circuits', 'Adel S. Sedra', 2019, 4, 0, 20),
(36, 'Introduction to Environmental Engineering', 'G. J. P. R. Rao', 2020, 7, 3, 21),
(37, 'Algorithms for Optimization', 'Zhongjie Shen', 2018, 6, 2, 22),
(38, 'Digital Communication', 'John G. Proakis', 2019, 8, 2, 23),
(39, 'Engineering Mechanics: Dynamics', 'J. L. Meriam', 2021, 12, 4, 24),
(40, 'Theory of Structures', 'H. P. Gupta', 2016, 5, 0, 25),
(41, 'Introduction to Fluid Mechanics', 'Robert W. Fox', 2021, 10, 2, 26),
(42, 'Engineering Ethics: Concepts and Cases in Engineering', 'Charles E. Harris', 2018, 9, 1, 27),
(43, 'Computer-Aided Design of Microelectronic Circuits', 'D. A. Neamen', 2019, 4, 0, 28),
(44, 'Introduction to Geotechnical Engineering', 'B. M. Das', 2020, 6, 3, 29),
(45, 'Civil Engineering Materials', 'J. F. Young', 2017, 7, 2, 30),
(46, 'Structural Analysis', 'R. C. Hibbeler', 2018, 5, 1, 31),
(47, 'Introduction to Systems Engineering', 'H. T. Papalambros', 2019, 8, 0, 32),
(48, 'Engineering Mechanics: Statics', 'J.L. Meriam', 2021, 12, 3, 33),
(49, 'Mechanical Vibrations', 'S. S. Rao', 2020, 11, 4, 34),
(50, 'Computer Vision: Algorithms and Applications', 'Richard Szeliski', 2021, 10, 5, 35),
(51, 'Advanced Engineering Mathematics', 'Erwin Kreyszig', 2021, 10, 5, 36),
(52, 'Introduction to Wireless Communications', 'Mischa Schwartz', 2019, 7, 2, 37),
(53, 'Principles of Engineering Economic Analysis', 'Charles S. Tapiero', 2018, 8, 1, 38),
(54, 'Engineering Design', 'G. K. L. T. L. Asaka', 2017, 5, 0, 39),
(55, 'Introduction to Electrical Engineering', 'G. R. Slemon', 2020, 6, 2, 40),
(56, 'Embedded Systems: Real-Time Interfacing to ARM Cortex-M3', 'Jonathan Valvano', 2018, 9, 3, 41),
(57, 'Advanced Mechanics of Materials', 'B. B. M. R. M. B. A. J. P. J. A. R. C. H. K. F. P. L. H. A. K. B. A. K. L. K. B. B.', 2019, 4, 1, 42),
(58, 'Fundamentals of Semiconductor Manufacturing and Process Control', 'S. T. Wu', 2021, 8, 2, 43),
(59, 'Finite Element Method: Theory and Applications with ANSYS', 'G. R. Liu', 2020, 10, 3, 44),
(60, 'Robotics: Control, Sensing, Vision, and Intelligence', 'R. B. R. C. H. M. J. H. P. A. R. C.', 2019, 7, 2, 45),
(61, 'Design of Fluid Thermal Systems', 'G. P. K. S. B. A. B. K. C. J. S. S.', 2021, 9, 0, 46),
(62, 'Hydrology and Water Resources Engineering', 'R. K. D. D. S. J. R.', 2018, 6, 1, 47),
(63, 'Optics', 'Eugene Hecht', 2019, 4, 2, 48),
(64, 'Signals and Systems', 'Alan V. Oppenheim', 2020, 10, 5, 49),
(65, 'Project Management: A Systems Approach to Planning, Scheduling, and Controlling', 'Harold Kerzner', 2021, 12, 3, 50),
(67, 'DBMS', 'bhandare', 2024, 20, 0, 230),
(68, 'mm', 'mm', 2024, 4, 0, 9422),
(69, 'mm', 'mm', 2024, 4, 0, 9423),
(70, 'mm', 'mm', 2024, 4, 0, 8623),
(72, 'mm', 'mm', 2024, 3, 0, 3805),
(73, 'nn', 'nn', 2024, 3, 0, 9623),
(77, 'nnn', 'ccc', 2024, 10, 0, 9040),
(78, 'vaibhav', 'uday', 1997, 2, 0, 9767),
(79, 'math', 'bb sing', 2024, 6, 0, 4455),
(80, 'corsera', 'bhandare', 2022, 5, 0, 333),
(81, 'dbms', 'anjali hajare', 2022, 5, 0, 234),
(82, 'math', 'grehewal', 2014, 5, 0, 445),
(84, 'Mathematics', 'B S Grehewal', 2022, 3, 0, 124),
(86, 'Mathemati', 'B S Grehewal', 2022, 3, 0, 125),
(87, 'Mathematics', 'B S Grehewal', 2022, 3, 0, 122),
(92, 'Mathematics', 'B S Grehewal', 2022, 3, 0, 135);

-- --------------------------------------------------------

--
-- Table structure for table `book_copies`
--

CREATE TABLE `book_copies` (
  `id` int(11) NOT NULL,
  `book_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `isbn` int(11) DEFAULT NULL,
  `referral_code` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `book_copies`
--

INSERT INTO `book_copies` (`id`, `book_id`, `title`, `isbn`, `referral_code`) VALUES
(1, NULL, 'Engineering Mechanics: Dynamics', 101, '101-0001'),
(2, NULL, 'Engineering Mechanics: Dynamics', 101, '101-0002'),
(3, NULL, 'Engineering Mechanics: Dynamics', 101, '101-0003'),
(4, NULL, 'Engineering Mechanics: Dynamics', 101, '101-0004'),
(5, NULL, 'Engineering Mechanics: Dynamics', 101, '101-0005'),
(6, NULL, 'Engineering Mechanics: Dynamics', 101, '101-0006'),
(7, NULL, 'Engineering Mechanics: Dynamics', 101, '101-0007'),
(8, NULL, 'Engineering Mechanics: Dynamics', 101, '101-0008'),
(9, NULL, 'Fundamentals of Thermodynamics', 102, '102-0001'),
(10, NULL, 'Fundamentals of Thermodynamics', 102, '102-0002'),
(11, NULL, 'Fundamentals of Thermodynamics', 102, '102-0003'),
(12, NULL, 'Fundamentals of Thermodynamics', 102, '102-0004'),
(13, NULL, 'Control Systems Engineering', 103, '103-0001'),
(14, NULL, 'Control Systems Engineering', 103, '103-0002'),
(15, NULL, 'Control Systems Engineering', 103, '103-0003'),
(16, NULL, 'Control Systems Engineering', 103, '103-0004'),
(17, NULL, 'Introduction to Electrical Engineering', 104, '104-0001'),
(18, NULL, 'Introduction to Electrical Engineering', 104, '104-0002'),
(19, NULL, 'Introduction to Electrical Engineering', 104, '104-0003'),
(20, NULL, 'Introduction to Electrical Engineering', 104, '104-0004'),
(21, NULL, 'Introduction to Electrical Engineering', 104, '104-0005'),
(22, NULL, 'Introduction to Electrical Engineering', 104, '104-0006'),
(23, NULL, 'Introduction to Electrical Engineering', 104, '104-0007'),
(24, NULL, 'Introduction to Electrical Engineering', 104, '104-0008'),
(25, NULL, 'Engineering Fluid Mechanics', 105, '105-0001'),
(26, NULL, 'Engineering Fluid Mechanics', 105, '105-0002'),
(27, NULL, 'Engineering Fluid Mechanics', 105, '105-0003'),
(28, NULL, 'Engineering Fluid Mechanics', 105, '105-0004'),
(29, NULL, 'Computer Networking: A Top-Down Approach', 106, '106-0001'),
(30, NULL, 'Computer Networking: A Top-Down Approach', 106, '106-0002'),
(31, NULL, 'Computer Networking: A Top-Down Approach', 106, '106-0003'),
(32, NULL, 'Computer Networking: A Top-Down Approach', 106, '106-0004'),
(33, NULL, 'Computer Networking: A Top-Down Approach', 106, '106-0005'),
(34, NULL, 'Computer Networking: A Top-Down Approach', 106, '106-0006'),
(35, NULL, 'Computer Networking: A Top-Down Approach', 106, '106-0007'),
(36, NULL, 'Computer Networking: A Top-Down Approach', 106, '106-0008'),
(37, NULL, 'Introduction to Robotics: Mechanics and Control', 107, '107-0001'),
(38, NULL, 'Introduction to Robotics: Mechanics and Control', 107, '107-0002'),
(39, NULL, 'Introduction to Robotics: Mechanics and Control', 107, '107-0003'),
(40, NULL, 'Introduction to Robotics: Mechanics and Control', 107, '107-0004'),
(41, NULL, 'Fundamentals of Electric Circuits', 108, '108-0001'),
(42, NULL, 'Fundamentals of Electric Circuits', 108, '108-0002'),
(43, NULL, 'Fundamentals of Electric Circuits', 108, '108-0003'),
(44, NULL, 'Fundamentals of Electric Circuits', 108, '108-0004'),
(45, NULL, 'Fundamentals of Electric Circuits', 108, '108-0005'),
(46, NULL, 'Design of Concrete Structures', 109, '109-0001'),
(47, NULL, 'Design of Concrete Structures', 109, '109-0002'),
(48, NULL, 'Design of Concrete Structures', 109, '109-0003'),
(49, NULL, 'Design of Concrete Structures', 109, '109-0004'),
(50, NULL, 'Design of Concrete Structures', 109, '109-0005'),
(51, NULL, 'Design of Concrete Structures', 109, '109-0006'),
(52, NULL, 'Design of Concrete Structures', 109, '109-0007'),
(53, NULL, 'Introduction to Chemical Engineering', 110, '110-0001'),
(54, NULL, 'Introduction to Chemical Engineering', 110, '110-0002'),
(55, NULL, 'Introduction to Chemical Engineering', 110, '110-0003'),
(56, NULL, 'Introduction to Chemical Engineering', 110, '110-0004'),
(57, NULL, 'Introduction to Chemical Engineering', 110, '110-0005'),
(58, NULL, 'Introduction to Chemical Engineering', 110, '110-0006'),
(59, NULL, 'Software Engineering', 111, '111-0001'),
(60, NULL, 'Software Engineering', 111, '111-0002'),
(61, NULL, 'Software Engineering', 111, '111-0003'),
(62, NULL, 'Software Engineering', 111, '111-0004'),
(63, NULL, 'Software Engineering', 111, '111-0005'),
(64, NULL, 'Software Engineering', 111, '111-0006'),
(65, NULL, 'Software Engineering', 111, '111-0007'),
(66, NULL, 'Software Engineering', 111, '111-0008'),
(67, NULL, 'Software Engineering', 111, '111-0009'),
(68, NULL, 'Engineering Economy', 112, '112-0001'),
(69, NULL, 'Engineering Economy', 112, '112-0002'),
(70, NULL, 'Engineering Economy', 112, '112-0003'),
(71, NULL, 'Digital Signal Processing', 113, '113-0001'),
(72, NULL, 'Digital Signal Processing', 113, '113-0002'),
(73, NULL, 'Digital Signal Processing', 113, '113-0003'),
(74, NULL, 'Digital Signal Processing', 113, '113-0004'),
(75, NULL, 'Digital Signal Processing', 113, '113-0005'),
(76, NULL, 'Engineering Mechanics: Statics', 114, '114-0001'),
(77, NULL, 'Engineering Mechanics: Statics', 114, '114-0002'),
(78, NULL, 'Engineering Mechanics: Statics', 114, '114-0003'),
(79, NULL, 'Engineering Mechanics: Statics', 114, '114-0004'),
(80, NULL, 'Engineering Mechanics: Statics', 114, '114-0005'),
(81, NULL, 'Engineering Mechanics: Statics', 114, '114-0006'),
(82, NULL, 'Engineering Mechanics: Statics', 114, '114-0007'),
(83, NULL, 'Engineering Mechanics: Statics', 114, '114-0008'),
(84, NULL, 'Engineering Mechanics: Statics', 114, '114-0009'),
(85, NULL, 'Microelectronic Circuits', 115, '115-0001'),
(86, NULL, 'Microelectronic Circuits', 115, '115-0002'),
(87, NULL, 'Microelectronic Circuits', 115, '115-0003'),
(88, NULL, 'Microelectronic Circuits', 115, '115-0004'),
(89, NULL, 'Engineering Mechanics: Dynamics', 1, '1-0001'),
(90, NULL, 'Engineering Mechanics: Dynamics', 1, '1-0002'),
(91, NULL, 'Engineering Mechanics: Dynamics', 1, '1-0003'),
(92, NULL, 'Engineering Mechanics: Dynamics', 1, '1-0004'),
(93, NULL, 'Engineering Mechanics: Dynamics', 1, '1-0005'),
(94, NULL, 'Engineering Mechanics: Dynamics', 1, '1-0006'),
(95, NULL, 'Engineering Mechanics: Dynamics', 1, '1-0007'),
(96, NULL, 'Engineering Mechanics: Dynamics', 1, '1-0008'),
(97, NULL, 'Fundamentals of Thermodynamics', 2, '2-0001'),
(98, NULL, 'Fundamentals of Thermodynamics', 2, '2-0002'),
(99, NULL, 'Fundamentals of Thermodynamics', 2, '2-0003'),
(100, NULL, 'Fundamentals of Thermodynamics', 2, '2-0004'),
(101, NULL, 'Control Systems Engineering', 3, '3-0001'),
(102, NULL, 'Control Systems Engineering', 3, '3-0002'),
(103, NULL, 'Control Systems Engineering', 3, '3-0003'),
(104, NULL, 'Control Systems Engineering', 3, '3-0004'),
(105, NULL, 'Introduction to Electrical Engineering', 4, '4-0001'),
(106, NULL, 'Introduction to Electrical Engineering', 4, '4-0002'),
(107, NULL, 'Introduction to Electrical Engineering', 4, '4-0003'),
(108, NULL, 'Introduction to Electrical Engineering', 4, '4-0004'),
(109, NULL, 'Introduction to Electrical Engineering', 4, '4-0005'),
(110, NULL, 'Introduction to Electrical Engineering', 4, '4-0006'),
(111, NULL, 'Introduction to Electrical Engineering', 4, '4-0007'),
(112, NULL, 'Introduction to Electrical Engineering', 4, '4-0008'),
(113, NULL, 'Engineering Fluid Mechanics', 5, '5-0001'),
(114, NULL, 'Engineering Fluid Mechanics', 5, '5-0002'),
(115, NULL, 'Engineering Fluid Mechanics', 5, '5-0003'),
(116, NULL, 'Engineering Fluid Mechanics', 5, '5-0004'),
(117, NULL, 'Introduction to Computer Engineering', 6, '6-0001'),
(118, NULL, 'Introduction to Computer Engineering', 6, '6-0002'),
(119, NULL, 'Introduction to Computer Engineering', 6, '6-0003'),
(120, NULL, 'Introduction to Computer Engineering', 6, '6-0004'),
(121, NULL, 'Introduction to Computer Engineering', 6, '6-0005'),
(122, NULL, 'Introduction to Computer Engineering', 6, '6-0006'),
(123, NULL, 'Introduction to Computer Engineering', 6, '6-0007'),
(124, NULL, 'Introduction to Computer Engineering', 6, '6-0008'),
(125, NULL, 'Mechanical Engineering Design', 7, '7-0001'),
(126, NULL, 'Mechanical Engineering Design', 7, '7-0002'),
(127, NULL, 'Mechanical Engineering Design', 7, '7-0003'),
(128, NULL, 'Engineering Statistics', 8, '8-0001'),
(129, NULL, 'Engineering Statistics', 8, '8-0002'),
(130, NULL, 'Materials Science and Engineering', 9, '9-0001'),
(131, NULL, 'Materials Science and Engineering', 9, '9-0002'),
(132, NULL, 'Materials Science and Engineering', 9, '9-0003'),
(133, NULL, 'Materials Science and Engineering', 9, '9-0004'),
(134, NULL, 'Materials Science and Engineering', 9, '9-0005'),
(135, NULL, 'Materials Science and Engineering', 9, '9-0006'),
(136, NULL, 'Materials Science and Engineering', 9, '9-0007'),
(137, NULL, 'Materials Science and Engineering', 9, '9-0008'),
(138, NULL, 'Materials Science and Engineering', 9, '9-0009'),
(139, NULL, 'Digital Logic Design', 10, '10-0001'),
(140, NULL, 'Digital Logic Design', 10, '10-0002'),
(141, NULL, 'Digital Logic Design', 10, '10-0003'),
(142, NULL, 'Digital Logic Design', 10, '10-0004'),
(143, NULL, 'Computer Networking: A Top-Down Approach', 11, '11-0001'),
(144, NULL, 'Computer Networking: A Top-Down Approach', 11, '11-0002'),
(145, NULL, 'Computer Networking: A Top-Down Approach', 11, '11-0003'),
(146, NULL, 'Computer Networking: A Top-Down Approach', 11, '11-0004'),
(147, NULL, 'Computer Networking: A Top-Down Approach', 11, '11-0005'),
(148, NULL, 'Computer Networking: A Top-Down Approach', 11, '11-0006'),
(149, NULL, 'Computer Networking: A Top-Down Approach', 11, '11-0007'),
(150, NULL, 'Computer Networking: A Top-Down Approach', 11, '11-0008'),
(151, NULL, 'Introduction to Robotics: Mechanics and Control', 12, '12-0001'),
(152, NULL, 'Introduction to Robotics: Mechanics and Control', 12, '12-0002'),
(153, NULL, 'Introduction to Robotics: Mechanics and Control', 12, '12-0003'),
(154, NULL, 'Introduction to Robotics: Mechanics and Control', 12, '12-0004'),
(155, NULL, 'Fundamentals of Electric Circuits', 13, '13-0001'),
(156, NULL, 'Fundamentals of Electric Circuits', 13, '13-0002'),
(157, NULL, 'Fundamentals of Electric Circuits', 13, '13-0003'),
(158, NULL, 'Fundamentals of Electric Circuits', 13, '13-0004'),
(159, NULL, 'Fundamentals of Electric Circuits', 13, '13-0005'),
(160, NULL, 'Design of Concrete Structures', 14, '14-0001'),
(161, NULL, 'Design of Concrete Structures', 14, '14-0002'),
(162, NULL, 'Design of Concrete Structures', 14, '14-0003'),
(163, NULL, 'Design of Concrete Structures', 14, '14-0004'),
(164, NULL, 'Design of Concrete Structures', 14, '14-0005'),
(165, NULL, 'Design of Concrete Structures', 14, '14-0006'),
(166, NULL, 'Design of Concrete Structures', 14, '14-0007'),
(167, NULL, 'Introduction to Chemical Engineering', 15, '15-0001'),
(168, NULL, 'Introduction to Chemical Engineering', 15, '15-0002'),
(169, NULL, 'Introduction to Chemical Engineering', 15, '15-0003'),
(170, NULL, 'Introduction to Chemical Engineering', 15, '15-0004'),
(171, NULL, 'Introduction to Chemical Engineering', 15, '15-0005'),
(172, NULL, 'Introduction to Chemical Engineering', 15, '15-0006'),
(173, NULL, 'Software Engineering', 16, '16-0001'),
(174, NULL, 'Software Engineering', 16, '16-0002'),
(175, NULL, 'Software Engineering', 16, '16-0003'),
(176, NULL, 'Software Engineering', 16, '16-0004'),
(177, NULL, 'Software Engineering', 16, '16-0005'),
(178, NULL, 'Software Engineering', 16, '16-0006'),
(179, NULL, 'Software Engineering', 16, '16-0007'),
(180, NULL, 'Software Engineering', 16, '16-0008'),
(181, NULL, 'Software Engineering', 16, '16-0009'),
(182, NULL, 'Engineering Economy', 17, '17-0001'),
(183, NULL, 'Engineering Economy', 17, '17-0002'),
(184, NULL, 'Engineering Economy', 17, '17-0003'),
(185, NULL, 'Digital Signal Processing', 18, '18-0001'),
(186, NULL, 'Digital Signal Processing', 18, '18-0002'),
(187, NULL, 'Digital Signal Processing', 18, '18-0003'),
(188, NULL, 'Digital Signal Processing', 18, '18-0004'),
(189, NULL, 'Digital Signal Processing', 18, '18-0005'),
(190, NULL, 'Engineering Mechanics: Statics', 19, '19-0001'),
(191, NULL, 'Engineering Mechanics: Statics', 19, '19-0002'),
(192, NULL, 'Engineering Mechanics: Statics', 19, '19-0003'),
(193, NULL, 'Engineering Mechanics: Statics', 19, '19-0004'),
(194, NULL, 'Engineering Mechanics: Statics', 19, '19-0005'),
(195, NULL, 'Engineering Mechanics: Statics', 19, '19-0006'),
(196, NULL, 'Engineering Mechanics: Statics', 19, '19-0007'),
(197, NULL, 'Engineering Mechanics: Statics', 19, '19-0008'),
(198, NULL, 'Engineering Mechanics: Statics', 19, '19-0009'),
(199, NULL, 'Microelectronic Circuits', 20, '20-0001'),
(200, NULL, 'Microelectronic Circuits', 20, '20-0002'),
(201, NULL, 'Microelectronic Circuits', 20, '20-0003'),
(202, NULL, 'Microelectronic Circuits', 20, '20-0004'),
(203, NULL, 'Introduction to Environmental Engineering', 21, '21-0001'),
(204, NULL, 'Introduction to Environmental Engineering', 21, '21-0002'),
(205, NULL, 'Introduction to Environmental Engineering', 21, '21-0003'),
(206, NULL, 'Introduction to Environmental Engineering', 21, '21-0004'),
(207, NULL, 'Algorithms for Optimization', 22, '22-0001'),
(208, NULL, 'Algorithms for Optimization', 22, '22-0002'),
(209, NULL, 'Algorithms for Optimization', 22, '22-0003'),
(210, NULL, 'Algorithms for Optimization', 22, '22-0004'),
(211, NULL, 'Algorithms for Optimization', 22, '22-0005'),
(212, NULL, 'Digital Communication', 23, '23-0001'),
(213, NULL, 'Digital Communication', 23, '23-0002'),
(214, NULL, 'Digital Communication', 23, '23-0003'),
(215, NULL, 'Digital Communication', 23, '23-0004'),
(216, NULL, 'Digital Communication', 23, '23-0005'),
(217, NULL, 'Digital Communication', 23, '23-0006'),
(218, NULL, 'Engineering Mechanics: Dynamics', 24, '24-0001'),
(219, NULL, 'Engineering Mechanics: Dynamics', 24, '24-0002'),
(220, NULL, 'Engineering Mechanics: Dynamics', 24, '24-0003'),
(221, NULL, 'Engineering Mechanics: Dynamics', 24, '24-0004'),
(222, NULL, 'Engineering Mechanics: Dynamics', 24, '24-0005'),
(223, NULL, 'Engineering Mechanics: Dynamics', 24, '24-0006'),
(224, NULL, 'Engineering Mechanics: Dynamics', 24, '24-0007'),
(225, NULL, 'Engineering Mechanics: Dynamics', 24, '24-0008'),
(226, NULL, 'Theory of Structures', 25, '25-0001'),
(227, NULL, 'Theory of Structures', 25, '25-0002'),
(228, NULL, 'Theory of Structures', 25, '25-0003'),
(229, NULL, 'Theory of Structures', 25, '25-0004'),
(230, NULL, 'Theory of Structures', 25, '25-0005'),
(231, NULL, 'Introduction to Fluid Mechanics', 26, '26-0001'),
(232, NULL, 'Introduction to Fluid Mechanics', 26, '26-0002'),
(233, NULL, 'Introduction to Fluid Mechanics', 26, '26-0003'),
(234, NULL, 'Introduction to Fluid Mechanics', 26, '26-0004'),
(235, NULL, 'Introduction to Fluid Mechanics', 26, '26-0005'),
(236, NULL, 'Introduction to Fluid Mechanics', 26, '26-0006'),
(237, NULL, 'Introduction to Fluid Mechanics', 26, '26-0007'),
(238, NULL, 'Introduction to Fluid Mechanics', 26, '26-0008'),
(239, NULL, 'Engineering Ethics: Concepts and Cases in Engineering', 27, '27-0001'),
(240, NULL, 'Engineering Ethics: Concepts and Cases in Engineering', 27, '27-0002'),
(241, NULL, 'Engineering Ethics: Concepts and Cases in Engineering', 27, '27-0003'),
(242, NULL, 'Engineering Ethics: Concepts and Cases in Engineering', 27, '27-0004'),
(243, NULL, 'Engineering Ethics: Concepts and Cases in Engineering', 27, '27-0005'),
(244, NULL, 'Engineering Ethics: Concepts and Cases in Engineering', 27, '27-0006'),
(245, NULL, 'Engineering Ethics: Concepts and Cases in Engineering', 27, '27-0007'),
(246, NULL, 'Engineering Ethics: Concepts and Cases in Engineering', 27, '27-0008'),
(247, NULL, 'Computer-Aided Design of Microelectronic Circuits', 28, '28-0001'),
(248, NULL, 'Computer-Aided Design of Microelectronic Circuits', 28, '28-0002'),
(249, NULL, 'Computer-Aided Design of Microelectronic Circuits', 28, '28-0003'),
(250, NULL, 'Computer-Aided Design of Microelectronic Circuits', 28, '28-0004'),
(251, NULL, 'Introduction to Geotechnical Engineering', 29, '29-0001'),
(252, NULL, 'Introduction to Geotechnical Engineering', 29, '29-0002'),
(253, NULL, 'Introduction to Geotechnical Engineering', 29, '29-0003'),
(254, NULL, 'Civil Engineering Materials', 30, '30-0001'),
(255, NULL, 'Civil Engineering Materials', 30, '30-0002'),
(256, NULL, 'Civil Engineering Materials', 30, '30-0003'),
(257, NULL, 'Civil Engineering Materials', 30, '30-0004'),
(258, NULL, 'Civil Engineering Materials', 30, '30-0005'),
(259, NULL, 'Structural Analysis', 31, '31-0001'),
(260, NULL, 'Structural Analysis', 31, '31-0002'),
(261, NULL, 'Structural Analysis', 31, '31-0003'),
(262, NULL, 'Structural Analysis', 31, '31-0004'),
(263, NULL, 'Introduction to Systems Engineering', 32, '32-0001'),
(264, NULL, 'Introduction to Systems Engineering', 32, '32-0002'),
(265, NULL, 'Introduction to Systems Engineering', 32, '32-0003'),
(266, NULL, 'Introduction to Systems Engineering', 32, '32-0004'),
(267, NULL, 'Introduction to Systems Engineering', 32, '32-0005'),
(268, NULL, 'Introduction to Systems Engineering', 32, '32-0006'),
(269, NULL, 'Introduction to Systems Engineering', 32, '32-0007'),
(270, NULL, 'Introduction to Systems Engineering', 32, '32-0008'),
(271, NULL, 'Engineering Mechanics: Statics', 33, '33-0001'),
(272, NULL, 'Engineering Mechanics: Statics', 33, '33-0002'),
(273, NULL, 'Engineering Mechanics: Statics', 33, '33-0003'),
(274, NULL, 'Engineering Mechanics: Statics', 33, '33-0004'),
(275, NULL, 'Engineering Mechanics: Statics', 33, '33-0005'),
(276, NULL, 'Engineering Mechanics: Statics', 33, '33-0006'),
(277, NULL, 'Engineering Mechanics: Statics', 33, '33-0007'),
(278, NULL, 'Engineering Mechanics: Statics', 33, '33-0008'),
(279, NULL, 'Engineering Mechanics: Statics', 33, '33-0009'),
(280, NULL, 'Mechanical Vibrations', 34, '34-0001'),
(281, NULL, 'Mechanical Vibrations', 34, '34-0002'),
(282, NULL, 'Mechanical Vibrations', 34, '34-0003'),
(283, NULL, 'Mechanical Vibrations', 34, '34-0004'),
(284, NULL, 'Mechanical Vibrations', 34, '34-0005'),
(285, NULL, 'Mechanical Vibrations', 34, '34-0006'),
(286, NULL, 'Mechanical Vibrations', 34, '34-0007'),
(287, NULL, 'Computer Vision: Algorithms and Applications', 35, '35-0001'),
(288, NULL, 'Computer Vision: Algorithms and Applications', 35, '35-0002'),
(289, NULL, 'Computer Vision: Algorithms and Applications', 35, '35-0003'),
(290, NULL, 'Computer Vision: Algorithms and Applications', 35, '35-0004'),
(291, NULL, 'Computer Vision: Algorithms and Applications', 35, '35-0005'),
(292, NULL, 'Advanced Engineering Mathematics', 36, '36-0001'),
(293, NULL, 'Advanced Engineering Mathematics', 36, '36-0002'),
(294, NULL, 'Advanced Engineering Mathematics', 36, '36-0003'),
(295, NULL, 'Advanced Engineering Mathematics', 36, '36-0004'),
(296, NULL, 'Advanced Engineering Mathematics', 36, '36-0005'),
(297, NULL, 'Introduction to Wireless Communications', 37, '37-0001'),
(298, NULL, 'Introduction to Wireless Communications', 37, '37-0002'),
(299, NULL, 'Introduction to Wireless Communications', 37, '37-0003'),
(300, NULL, 'Introduction to Wireless Communications', 37, '37-0004'),
(301, NULL, 'Introduction to Wireless Communications', 37, '37-0005'),
(302, NULL, 'Principles of Engineering Economic Analysis', 38, '38-0001'),
(303, NULL, 'Principles of Engineering Economic Analysis', 38, '38-0002'),
(304, NULL, 'Principles of Engineering Economic Analysis', 38, '38-0003'),
(305, NULL, 'Principles of Engineering Economic Analysis', 38, '38-0004'),
(306, NULL, 'Principles of Engineering Economic Analysis', 38, '38-0005'),
(307, NULL, 'Principles of Engineering Economic Analysis', 38, '38-0006'),
(308, NULL, 'Principles of Engineering Economic Analysis', 38, '38-0007'),
(309, NULL, 'Engineering Design', 39, '39-0001'),
(310, NULL, 'Engineering Design', 39, '39-0002'),
(311, NULL, 'Engineering Design', 39, '39-0003'),
(312, NULL, 'Engineering Design', 39, '39-0004'),
(313, NULL, 'Engineering Design', 39, '39-0005'),
(314, NULL, 'Introduction to Electrical Engineering', 40, '40-0001'),
(315, NULL, 'Introduction to Electrical Engineering', 40, '40-0002'),
(316, NULL, 'Introduction to Electrical Engineering', 40, '40-0003'),
(317, NULL, 'Introduction to Electrical Engineering', 40, '40-0004'),
(318, NULL, 'Embedded Systems: Real-Time Interfacing to ARM Cortex-M3', 41, '41-0001'),
(319, NULL, 'Embedded Systems: Real-Time Interfacing to ARM Cortex-M3', 41, '41-0002'),
(320, NULL, 'Embedded Systems: Real-Time Interfacing to ARM Cortex-M3', 41, '41-0003'),
(321, NULL, 'Embedded Systems: Real-Time Interfacing to ARM Cortex-M3', 41, '41-0004'),
(322, NULL, 'Embedded Systems: Real-Time Interfacing to ARM Cortex-M3', 41, '41-0005'),
(323, NULL, 'Embedded Systems: Real-Time Interfacing to ARM Cortex-M3', 41, '41-0006'),
(324, NULL, 'Advanced Mechanics of Materials', 42, '42-0001'),
(325, NULL, 'Advanced Mechanics of Materials', 42, '42-0002'),
(326, NULL, 'Advanced Mechanics of Materials', 42, '42-0003'),
(327, NULL, 'Fundamentals of Semiconductor Manufacturing and Process Control', 43, '43-0001'),
(328, NULL, 'Fundamentals of Semiconductor Manufacturing and Process Control', 43, '43-0002'),
(329, NULL, 'Fundamentals of Semiconductor Manufacturing and Process Control', 43, '43-0003'),
(330, NULL, 'Fundamentals of Semiconductor Manufacturing and Process Control', 43, '43-0004'),
(331, NULL, 'Fundamentals of Semiconductor Manufacturing and Process Control', 43, '43-0005'),
(332, NULL, 'Fundamentals of Semiconductor Manufacturing and Process Control', 43, '43-0006'),
(333, NULL, 'Finite Element Method: Theory and Applications with ANSYS', 44, '44-0001'),
(334, NULL, 'Finite Element Method: Theory and Applications with ANSYS', 44, '44-0002'),
(335, NULL, 'Finite Element Method: Theory and Applications with ANSYS', 44, '44-0003'),
(336, NULL, 'Finite Element Method: Theory and Applications with ANSYS', 44, '44-0004'),
(337, NULL, 'Finite Element Method: Theory and Applications with ANSYS', 44, '44-0005'),
(338, NULL, 'Finite Element Method: Theory and Applications with ANSYS', 44, '44-0006'),
(339, NULL, 'Finite Element Method: Theory and Applications with ANSYS', 44, '44-0007'),
(340, NULL, 'Robotics: Control, Sensing, Vision, and Intelligence', 45, '45-0001'),
(341, NULL, 'Robotics: Control, Sensing, Vision, and Intelligence', 45, '45-0002'),
(342, NULL, 'Robotics: Control, Sensing, Vision, and Intelligence', 45, '45-0003'),
(343, NULL, 'Robotics: Control, Sensing, Vision, and Intelligence', 45, '45-0004'),
(344, NULL, 'Robotics: Control, Sensing, Vision, and Intelligence', 45, '45-0005'),
(345, NULL, 'Design of Fluid Thermal Systems', 46, '46-0001'),
(346, NULL, 'Design of Fluid Thermal Systems', 46, '46-0002'),
(347, NULL, 'Design of Fluid Thermal Systems', 46, '46-0003'),
(348, NULL, 'Design of Fluid Thermal Systems', 46, '46-0004'),
(349, NULL, 'Design of Fluid Thermal Systems', 46, '46-0005'),
(350, NULL, 'Design of Fluid Thermal Systems', 46, '46-0006'),
(351, NULL, 'Design of Fluid Thermal Systems', 46, '46-0007'),
(352, NULL, 'Design of Fluid Thermal Systems', 46, '46-0008'),
(353, NULL, 'Design of Fluid Thermal Systems', 46, '46-0009'),
(354, NULL, 'Hydrology and Water Resources Engineering', 47, '47-0001'),
(355, NULL, 'Hydrology and Water Resources Engineering', 47, '47-0002'),
(356, NULL, 'Hydrology and Water Resources Engineering', 47, '47-0003'),
(357, NULL, 'Hydrology and Water Resources Engineering', 47, '47-0004'),
(358, NULL, 'Hydrology and Water Resources Engineering', 47, '47-0005'),
(359, NULL, 'Optics', 48, '48-0001'),
(360, NULL, 'Optics', 48, '48-0002'),
(361, NULL, 'Signals and Systems', 49, '49-0001'),
(362, NULL, 'Signals and Systems', 49, '49-0002'),
(363, NULL, 'Signals and Systems', 49, '49-0003'),
(364, NULL, 'Signals and Systems', 49, '49-0004'),
(365, NULL, 'Signals and Systems', 49, '49-0005'),
(366, NULL, 'Project Management: A Systems Approach to Planning, Scheduling, and Controlling', 50, '50-0001'),
(367, NULL, 'Project Management: A Systems Approach to Planning, Scheduling, and Controlling', 50, '50-0002'),
(368, NULL, 'Project Management: A Systems Approach to Planning, Scheduling, and Controlling', 50, '50-0003'),
(369, NULL, 'Project Management: A Systems Approach to Planning, Scheduling, and Controlling', 50, '50-0004'),
(370, NULL, 'Project Management: A Systems Approach to Planning, Scheduling, and Controlling', 50, '50-0005'),
(371, NULL, 'Project Management: A Systems Approach to Planning, Scheduling, and Controlling', 50, '50-0006'),
(372, NULL, 'Project Management: A Systems Approach to Planning, Scheduling, and Controlling', 50, '50-0007'),
(373, NULL, 'Project Management: A Systems Approach to Planning, Scheduling, and Controlling', 50, '50-0008'),
(374, NULL, 'Project Management: A Systems Approach to Planning, Scheduling, and Controlling', 50, '50-0009'),
(375, NULL, 'DBMS', 230, '230-0001'),
(376, NULL, 'DBMS', 230, '230-0002'),
(377, NULL, 'DBMS', 230, '230-0003'),
(378, NULL, 'DBMS', 230, '230-0004'),
(379, NULL, 'DBMS', 230, '230-0005'),
(380, NULL, 'DBMS', 230, '230-0006'),
(381, NULL, 'DBMS', 230, '230-0007'),
(382, NULL, 'DBMS', 230, '230-0008'),
(383, NULL, 'DBMS', 230, '230-0009'),
(384, NULL, 'DBMS', 230, '230-0010'),
(385, NULL, 'DBMS', 230, '230-0011'),
(386, NULL, 'DBMS', 230, '230-0012'),
(387, NULL, 'DBMS', 230, '230-0013'),
(388, NULL, 'DBMS', 230, '230-0014'),
(389, NULL, 'DBMS', 230, '230-0015'),
(390, NULL, 'DBMS', 230, '230-0016'),
(391, NULL, 'DBMS', 230, '230-0017'),
(392, NULL, 'DBMS', 230, '230-0018'),
(393, NULL, 'DBMS', 230, '230-0019'),
(394, NULL, 'DBMS', 230, '230-0020'),
(395, NULL, 'mm', 9422, '9422-0001'),
(396, NULL, 'mm', 9422, '9422-0002'),
(397, NULL, 'mm', 9422, '9422-0003'),
(398, NULL, 'mm', 9422, '9422-0004'),
(399, NULL, 'mm', 9423, '9423-0001'),
(400, NULL, 'mm', 9423, '9423-0002'),
(401, NULL, 'mm', 9423, '9423-0003'),
(402, NULL, 'mm', 9423, '9423-0004'),
(403, NULL, 'mm', 8623, '8623-0001'),
(404, NULL, 'mm', 8623, '8623-0002'),
(405, NULL, 'mm', 8623, '8623-0003'),
(406, NULL, 'mm', 8623, '8623-0004'),
(407, NULL, 'mm', 3805, '3805-0001'),
(408, NULL, 'mm', 3805, '3805-0002'),
(409, NULL, 'mm', 3805, '3805-0003'),
(410, NULL, 'nn', 9623, '9623-0001'),
(411, NULL, 'nn', 9623, '9623-0002'),
(412, NULL, 'nn', 9623, '9623-0003'),
(414, NULL, 'nnn', 9040, '9040-0001'),
(415, NULL, 'nnn', 9040, '9040-0002'),
(416, NULL, 'nnn', 9040, '9040-0003'),
(417, NULL, 'nnn', 9040, '9040-0004'),
(418, NULL, 'nnn', 9040, '9040-0005'),
(419, NULL, 'nnn', 9040, '9040-0006'),
(420, NULL, 'nnn', 9040, '9040-0007'),
(421, NULL, 'nnn', 9040, '9040-0008'),
(422, NULL, 'nnn', 9040, '9040-0009'),
(423, NULL, 'nnn', 9040, '9040-0010'),
(425, NULL, 'vaibhav', 9767, '9767-0001'),
(426, NULL, 'vaibhav', 9767, '9767-0002'),
(427, NULL, 'math', 4455, '4455-0001'),
(428, NULL, 'math', 4455, '4455-0002'),
(429, NULL, 'math', 4455, '4455-0003'),
(430, NULL, 'math', 4455, '4455-0004'),
(431, NULL, 'math', 4455, '4455-0005'),
(432, NULL, 'math', 4455, '4455-0006'),
(433, NULL, 'corsera', 333, '333-0001'),
(434, NULL, 'corsera', 333, '333-0002'),
(435, NULL, 'corsera', 333, '333-0003'),
(436, NULL, 'corsera', 333, '333-0004'),
(437, NULL, 'corsera', 333, '333-0005'),
(438, NULL, 'dbms', 234, '234-0001'),
(439, NULL, 'dbms', 234, '234-0002'),
(440, NULL, 'dbms', 234, '234-0003'),
(441, NULL, 'dbms', 234, '234-0004'),
(442, NULL, 'dbms', 234, '234-0005'),
(443, NULL, 'math', 445, '445-0001'),
(444, NULL, 'math', 445, '445-0002'),
(445, NULL, 'math', 445, '445-0003'),
(446, NULL, 'math', 445, '445-0004'),
(447, NULL, 'math', 445, '445-0005'),
(448, NULL, 'Mathematics', 124, '124-0001'),
(449, NULL, 'Mathematics', 124, '124-0002'),
(450, NULL, 'Mathematics', 124, '124-0003'),
(451, NULL, 'Mathemati', 125, '125-0001'),
(452, NULL, 'Mathemati', 125, '125-0002'),
(453, NULL, 'Mathemati', 125, '125-0003'),
(454, NULL, 'Mathematics', 122, '122-0001'),
(455, NULL, 'Mathematics', 122, '122-0002'),
(456, NULL, 'Mathematics', 122, '122-0003'),
(457, NULL, 'Mathematics', 135, '135-0001'),
(458, NULL, 'Mathematics', 135, '135-0002'),
(459, NULL, 'Mathematics', 135, '135-0003');

-- --------------------------------------------------------

--
-- Table structure for table `borrowed_books`
--

CREATE TABLE `borrowed_books` (
  `transaction_id` int(11) NOT NULL,
  `borrower_id` int(11) DEFAULT NULL,
  `book_id` int(11) DEFAULT NULL,
  `book_title` varchar(255) DEFAULT NULL,
  `number_of_books` int(11) DEFAULT 1,
  `borrow_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `borrower`
--

CREATE TABLE `borrower` (
  `id` int(11) NOT NULL,
  `borrower_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `no_of_books_borrowed` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `borrowers`
--

CREATE TABLE `borrowers` (
  `id` int(11) NOT NULL,
  `borrower_name` varchar(255) NOT NULL,
  `borrower_email` varchar(255) NOT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `borrowers`
--

INSERT INTO `borrowers` (`id`, `borrower_name`, `borrower_email`, `password`) VALUES
(1, 'John Doe', 'john.doe@example.com', NULL),
(2, 'krishna', 'shrikrushnagandhewar@gmail.com', '$2y$10$kTiIER9htevPn.iKmyTbu.aOW9N9dWWwU5I2rDC9LIKWgnD1ZLiv.'),
(13, 'gandhewar', 'shivanigire2004@gmail.com', '*6BB4837EB74329105EE4568DDA7DC67ED2CA2AD9'),
(14, 'aditi mane', 's22_mane_aditi@mgmcen.ac.in', '*6A7A490FB9DC8C33C2B025A91737077A7E9CC5E5'),
(15, 'Alice Johnson', 'alice.johnson@exmple.com', 'mypassword789'),
(16, 'gajanan', 's22_jagdambe_gajanan@mgmcen.ac.in', '*00A51F3F48415C7D4E8908980D443C29C69B60C9'),
(17, 'Charlie Davis', 'charlie.davis@emple.com', 'password!234'),
(18, 'Diana Prince', 'diana.prince@examle.com', 'letmein456'),
(19, 'Ethan Hunt', 'ethan.hunt@example.com', 'topsecret789'),
(20, 'Fiona Gallagher', 'fiona.gallagher@example.com', 'password1234'),
(21, 'George Clooney', 'george.clooney@example.com', 'hollywood456'),
(22, 'Hannah Montana', 'hannah.montana@example.com', 'bestofbothworlds'),
(23, 'omkar', 'omkar@gmail.com', '$2y$10$xw.urw7Zx77dYghxGd6Ru.mlKP4jGkk22ZLcuVwYXG5QsZaQekSqW'),
(25, 'omkar', 'omm@gmail.com', '$2y$10$EzNzFcaC3URYbTgZVhuL1O3oBX3nhTllZaiVHTZNXs.y/HvvtPSNy'),
(28, 'sumit', 'sumit@gmail.com', '$2y$10$06kuPNp2CE9zUlWhgcojau2HoB9gTpTRV.bzpTcYKVYgLJkQVJuSS'),
(29, 'vaibhav', 'vgandhewar@gmailcom', '$2y$10$YfSu9UnjOpubsue8XFkhFemi2U3T623YqR.SvgRZJlJshBMpAld1u'),
(30, 'jiten', 'koundinye@gmail.com', '$2y$10$zqFZt0xaNBnsTla6EpiMtO8mag8AveLzY8yHHItThNC2mZ.5yJ3Ye'),
(34, 'vaibhav', 'vgandhewar50@gmail.com', '$2y$10$ZwSQmkU/QFreHnsyiV.fouXvI10iu6C0lAlnQQYihDBvJ7I5S9pGS'),
(35, 'Shrikrushna Uday Gandhewar', 'kgandhewar9040@gmail.com', '$2y$10$QhUnXPI1I1dfv67SMWupy.Rw/B6rj1M5QRZTsSg1fBpiRmduIQk9i'),
(39, 'uday', 'uday@gmail.com', '$2y$10$uIkDi78GXcxY2.7lORvO1eT3fv1MMSwS0sldmidYZBFByzXR.j9iG'),
(40, 'jiten', 'sd23_koundinye_jiten@mgmcen.ac.in', '$2y$10$JpKZ9bvnmC.tDUW.ofsWR.0FOUkR5XDySHlelC.koRwYZ571IwUfW'),
(41, 'w4tfwet', 'admin@example.com', '$2y$10$BNAxG9VhtitEiRkbnG9S..lnUVXyz36HJdmjUyvnZfvwm1UdRzo6u'),
(42, 'Shrikrushna Uday Gandhewar', 'kgandhewar@gmail.com', '$2y$10$impAAhmGrOxx.qjrr3K1CeChmZNsRb07lPKN8Raqa9S7GTBdvkkX6'),
(44, 'Shrikrushna Uday Gandhewar', 'krish@gmail.com', '$2y$10$x5dI.vQffCYog0.CFDPTQ.iPmj3t9hiOIH9wZT7iJ5x5OOZA5a/R.'),
(45, 'vaibhav', 'vaibhav@gmailcom', '$2y$10$GMxwCbGiqIj9s/Tj504to.tjS1npaaEpa/PZZd4Y65Uy9atIhUXnm'),
(46, 'Shrikrushna Uday Gandhewar', 'k@gmailcom', '$2y$10$Lrg7E/rzWpm9TK2enpTM8eCt02ZNjrtr9PEtBa2U8/HPaLZMNW7cS'),
(47, 'ganesh kalapad', 'ganeshkalapadgk@gmail.com', '$2y$10$o3snm2bpFz3BdvEHejTZVuXZ6I48FYuef9fy5hnGZ7ff2IV0HfXP6'),
(52, '122', 's22_fulari_omkar@mgmcen.ac.in', '$2y$10$9j50iGpjp5glQjmEQqtLRuKCFa9tuD/FWNIJlnTS0ONrg9G3RfDs2'),
(54, 'Shrikrushna Uday Gandhewar', 's22_gandhewar_shrikrushna@mgmcen.ac.in', '$2y$10$JZuwQcDyHLgEQyMe5SwnCeiTeve8rIxW5n7MzjVFXeDy/DP6uU4aq');

-- --------------------------------------------------------

--
-- Table structure for table `fine`
--

CREATE TABLE `fine` (
  `borrower_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fine1`
--

CREATE TABLE `fine1` (
  `id` int(11) NOT NULL,
  `borrower_id` int(11) NOT NULL,
  `borrower_email` varchar(255) NOT NULL,
  `overdue_fine` decimal(10,2) NOT NULL,
  `fine_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fine1`
--

INSERT INTO `fine1` (`id`, `borrower_id`, `borrower_email`, `overdue_fine`, `fine_date`) VALUES
(1, 16, '', 32.00, '2024-12-08'),
(2, 16, '', 40130.00, '2024-12-08'),
(3, 16, '', 40130.00, '2024-12-08'),
(4, 2, '', 40144.00, '2024-12-15'),
(5, 2, '', 40144.00, '2024-12-15');

-- --------------------------------------------------------

--
-- Table structure for table `fine2`
--

CREATE TABLE `fine2` (
  `id` int(11) NOT NULL,
  `borrower_id` int(11) NOT NULL,
  `total_fine` decimal(10,2) NOT NULL,
  `paid_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `loans`
--

CREATE TABLE `loans` (
  `id` int(11) NOT NULL,
  `borrower_id` int(11) DEFAULT NULL,
  `book_id` int(11) DEFAULT NULL,
  `borrow_date` date NOT NULL,
  `return_date` date DEFAULT NULL,
  `referral_code` varchar(50) DEFAULT NULL,
  `isbn` int(20) DEFAULT NULL,
  `due_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loans`
--

INSERT INTO `loans` (`id`, `borrower_id`, `book_id`, `borrow_date`, `return_date`, `referral_code`, `isbn`, `due_date`) VALUES
(49, 39, 28, '2024-11-28', NULL, '13-0004', 13, '2024-12-13'),
(64, 13, 34, '2024-11-02', NULL, '19-0004', 19, '2024-11-17'),
(72, 34, 29, '2024-12-08', NULL, '14-0003', 14, '2024-12-23'),
(73, 13, 2, '2024-12-16', NULL, '102-0003', 102, '2024-12-31'),
(77, 54, 12, '2024-12-19', NULL, '112-0002', 112, '2025-01-03');

-- --------------------------------------------------------

--
-- Table structure for table `managers`
--

CREATE TABLE `managers` (
  `id` int(11) NOT NULL,
  `name` varchar(222) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `managers`
--

INSERT INTO `managers` (`id`, `name`, `email`, `password`) VALUES
(1, '0', 'krishna@gmailcom', '12345'),
(2, '0', 'manager2@example.com', '123456'),
(3, '0', 'manager3@example.com', '$2y$10$WqgKgj8H3DS3Xiw9XqGtDu1VS1OwSbU9fr0Fr08ToVBo4GIz4T8Pe'),
(4, '0', 'kgandhewar9040@gmail.com', '$2y$10$1RFmwpPIoaCxW4IWX96whuUCyl3txLpilbquPxuc4GS4VlaUGWfIq'),
(5, '0', 'ganeshkalapadgk@gmail.com', '$2y$10$Cmmm7YNwbaeIw1yil34wte/yGSxZm2Y7E7gaH8h3PAW3kizKHVvZ.'),
(7, '0', 'shrikrushnagandhewar@gmail.com', '$2y$10$rc8ZbVmGnzRuk7AKEI3Jt.8AexAbK1A3OLIadl..nB/tOmPcRlCdG'),
(8, '0', 'krishna@gmai.com', '$2y$10$SiP6u/R1qZblxpSV/Lfdbe8oQ7qMT/e/Mdw67WTKNFn4vjsCIBtY.'),
(9, '0', 'koundinye@gmail.com', '$2y$10$GFKMc1T6APLBnttMdMsLre4TbhaxyZPLkGYHE9vwUlib/GBhBpyRm'),
(10, '0', 'vgandhewar@gmailcom', '$2y$10$ClyI6.hCzdjpg9s4L9i8Ae3NGDHtL5MitG2JDkjw1aXLMUohOfsPW'),
(11, '0', 'vgandhewar50@gmailcom', '$2y$10$UC7sieiZ1wUiN0USMJb4q.TjPRMYz7KUOFWrkIScSJiehCJUQntIu'),
(12, '0', 'rajurkar_am@mgmcen.ac.in', '$2y$10$AmncXThYQcBGk04tOPb80O7BBJNkW5amgLI4.lm6IhSo./Ri/KivG'),
(13, '0', 'k@gmail.com', '$2y$10$fd1jAUaa5qflHcnyT4U8Q.5wGzaNTiS2lkJ1ZCRC.BX0xzaDyduhC'),
(14, '0', 's22_kalapad_ganesh@mgmcen.ac.in', '$2y$10$AUZBPHcaIVo6qg8zVu57QueGLhNxLtUHz7xfVVtE2T.4MzLAn8Ppm'),
(15, '0', 's22_jagdambe_gajanan@mgmcen.ac.in', '$2y$10$kTa8xiq618O19jdMPJY0iOskpJOX49qZFQpmrG8m8Uap.4NAmuqRq'),
(16, '0', 'snooky9422@gmailcom', '$2y$10$5bTiUoZES7f2j0UBRgSQ3etU9RYRhAtVGDtPPGNfF0pYuYQ19yC12'),
(17, '0', 's22_kadam_ganessh@mgmcen.ac.in', '$2y$10$xnRy9NzXeVc.rlRe8Iq6fe0ZNVw16uj6Zoe5x6AOTLt72eZkyFL/m'),
(18, '0', 's22_fulari_omkar@mgmcen.ac.in', '$2y$10$4f9OTZJihP1TLH6Hio7XNejjxmwgywKWy2SK1NeAu/68e0cacudUy'),
(19, '0', 's22_atre_yash@mgmcen.ac.in', '$2y$10$l3LXf2RfTc2ZHok7tj4NAOUNqM2zm91ASod9fCWhGbG4bpOO0Z8eq'),
(20, '0', 's22_gandhewar_shrikrushna@mgmcen.ac', '$2y$10$BaW/lK9Cv2cfuLVuieO9YebuED2iHcONscmOVUL2czv2BlJ4lIuw2'),
(21, 'aditi', 's22_durpade_aditi@mgmcen.ac.in', '$2y$10$dO6eAbtWgoHOTz0RiCtjk.IBTGzAQt4smjXHby3JUBrI8xu0dIxcS'),
(32, 'Shrikrushna Uday Gandhewar', 's22_gandhewar_shrikrushna@mgmcen.ac.in', '$2y$10$TMOnt608G70swPwRLzXS6ureDZ1Hv3rIT72GUeJrbfLdHkdFJXcTK');

-- --------------------------------------------------------

--
-- Table structure for table `overdue_books_fines`
--

CREATE TABLE `overdue_books_fines` (
  `id` int(11) NOT NULL,
  `borrower_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `borrower_name` varchar(255) NOT NULL,
  `borrower_email` varchar(255) NOT NULL,
  `book_title` varchar(255) NOT NULL,
  `due_date` date NOT NULL,
  `fine_amount` decimal(10,2) NOT NULL,
  `overdue_days` int(11) NOT NULL,
  `fine_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `overdue_fines`
--

CREATE TABLE `overdue_fines` (
  `id` int(11) NOT NULL,
  `borrower_id` int(11) DEFAULT NULL,
  `borrower_name` varchar(255) DEFAULT NULL,
  `borrower_email` varchar(255) DEFAULT NULL,
  `book_id` int(11) DEFAULT NULL,
  `book_title` varchar(255) DEFAULT NULL,
  `referral_code` varchar(50) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `overdue_days` int(11) DEFAULT NULL,
  `fine_amount` decimal(10,2) DEFAULT NULL,
  `fine_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `overdue_fines`
--

INSERT INTO `overdue_fines` (`id`, `borrower_id`, `borrower_name`, `borrower_email`, `book_id`, `book_title`, `referral_code`, `due_date`, `overdue_days`, `fine_amount`, `fine_date`) VALUES
(1, 13, 'John Doe', 'snooky9422@gmail.com', 16, 'Engineering Mechanics: Dynamics', '1-0007', '2024-11-16', 22, 44.00, '2024-12-08 10:35:28'),
(2, 40, 'jiten', 'sd23_koundinye_jiten@mgmcen.ac.in', 5, 'Engineering Fluid Mechanics', '105-0002', '2024-11-16', 22, 44.00, '2024-12-08 10:35:28'),
(3, 16, 'gajanan', 's22_jagdambe_gajanan@mgmcen.ac.in', 12, 'Engineering Economy', '112-0003', '2024-11-22', 16, 32.00, '2024-12-08 10:35:28');

-- --------------------------------------------------------

--
-- Table structure for table `returns`
--

CREATE TABLE `returns` (
  `id` int(11) NOT NULL,
  `borrower_id` int(11) NOT NULL,
  `referral_code` varchar(50) NOT NULL,
  `return_date` date NOT NULL,
  `book_id` int(11) NOT NULL,
  `isbn` varchar(13) DEFAULT NULL,
  `borrow_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `returns`
--

INSERT INTO `returns` (`id`, `borrower_id`, `referral_code`, `return_date`, `book_id`, `isbn`, `borrow_date`) VALUES
(5, 2, '17-0002', '2024-11-15', 32, '17', NULL),
(6, 13, '20-0003', '2024-11-15', 35, '20', '2024-11-15'),
(8, 2, '18-0003', '2024-11-15', 33, '18', '2024-11-06'),
(9, 2, '14-0001', '2024-11-28', 29, '14', '2024-11-15'),
(10, 29, '2-0001', '2024-11-28', 17, '2', '2024-11-01'),
(11, 2, '102-0003', '2024-11-29', 2, '102', '2024-11-28'),
(12, 13, '103-0004', '2024-11-29', 3, '103', '2024-11-28'),
(13, 2, '2-0003', '2024-11-29', 17, '2', '2024-11-28'),
(14, 2, '17-0002', '2024-11-29', 32, '17', '2024-11-15'),
(15, 2, '2-0002', '2024-11-29', 17, '2', '2024-11-28'),
(16, 15, '11-0008', '2024-11-29', 26, '11', '2024-11-28'),
(17, 14, '15-0004', '2024-11-29', 30, '15', '2024-11-28'),
(18, 18, '102-0002', '2024-11-29', 2, '102', '2024-11-28'),
(19, 13, '18-0004', '2024-11-29', 33, '18', '2024-11-15'),
(20, 29, '102-0004', '2024-11-29', 2, '102', '2024-11-28'),
(21, 2, '4-0005', '2024-12-02', 19, '4', '2024-11-25'),
(22, 13, '16-0006', '2024-12-02', 31, '16', '2024-11-29'),
(23, 14, '11-0004', '2024-12-02', 26, '11', '2024-11-29'),
(24, 2, '112-0002', '2024-12-02', 12, '112', '2024-11-01'),
(25, 29, '102-0001', '2024-12-05', 2, '102', '2024-11-28'),
(26, 1, '112-0001', '2024-12-08', 12, '112', '2024-11-15'),
(27, 13, '14-0004', '2024-12-08', 29, '14', '2024-12-05'),
(28, 2, '2-0004', '2024-12-08', 17, '2', '2024-11-28'),
(29, 2, '15-0004', '2024-12-08', 30, '15', '2024-12-08'),
(30, 1, '18-0004', '2024-12-08', 33, '18', '2024-12-02'),
(31, 14, '16-0005', '2024-12-08', 31, '16', '2024-11-29'),
(32, 16, '112-0003', '2024-12-08', 12, '112', '2024-11-07'),
(33, 2, '2-0001', '2024-12-15', 17, '2', '2024-12-16'),
(34, 2, '4-0004', '2024-12-15', 19, '4', '2024-12-01'),
(35, 2, '112-0003', '2024-12-18', 12, '112', '2024-12-16'),
(36, 40, '105-0002', '2024-12-19', 5, '105', '2024-11-01'),
(37, 14, '103-0001', '2024-12-19', 3, '103', '2024-11-02'),
(38, 13, '103-0003', '2024-12-19', 3, '103', '2024-11-28'),
(39, 13, '1-0007', '2024-12-19', 16, '1', '2024-11-01'),
(40, 13, '16-0004', '2024-12-19', 31, '16', '2024-12-02'),
(41, 13, '16-0007', '2024-12-19', 31, '16', '2024-12-02'),
(42, 30, '22-0003', '2024-12-19', 37, '22', '2024-10-10');

-- --------------------------------------------------------

--
-- Table structure for table `verification_requests`
--

CREATE TABLE `verification_requests` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `otp` int(11) NOT NULL,
  `password` varchar(222) NOT NULL,
  `otp_verified` tinyint(1) DEFAULT 0,
  `request_time` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`book_id`),
  ADD UNIQUE KEY `unique_sid` (`isbn`);

--
-- Indexes for table `book_copies`
--
ALTER TABLE `book_copies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `referral_code` (`referral_code`),
  ADD UNIQUE KEY `referral_code_2` (`referral_code`),
  ADD KEY `isbn` (`isbn`);

--
-- Indexes for table `borrowed_books`
--
ALTER TABLE `borrowed_books`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `borrower_id` (`borrower_id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indexes for table `borrower`
--
ALTER TABLE `borrower`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `borrowers`
--
ALTER TABLE `borrowers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `borrower_email` (`borrower_email`),
  ADD UNIQUE KEY `pass` (`password`);

--
-- Indexes for table `fine`
--
ALTER TABLE `fine`
  ADD KEY `fine_ibfk_1` (`borrower_id`),
  ADD KEY `fine_ibfk_2` (`book_id`);

--
-- Indexes for table `fine1`
--
ALTER TABLE `fine1`
  ADD PRIMARY KEY (`id`),
  ADD KEY `borrower_id` (`borrower_id`);

--
-- Indexes for table `fine2`
--
ALTER TABLE `fine2`
  ADD PRIMARY KEY (`id`),
  ADD KEY `borrower_id` (`borrower_id`);

--
-- Indexes for table `loans`
--
ALTER TABLE `loans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `referral_code` (`referral_code`),
  ADD UNIQUE KEY `referral_code_2` (`referral_code`),
  ADD KEY `borrower_id` (`borrower_id`),
  ADD KEY `book_id` (`book_id`),
  ADD KEY `fk_isbn` (`isbn`);

--
-- Indexes for table `managers`
--
ALTER TABLE `managers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `overdue_books_fines`
--
ALTER TABLE `overdue_books_fines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `borrower_id` (`borrower_id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indexes for table `overdue_fines`
--
ALTER TABLE `overdue_fines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `borrower_id` (`borrower_id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indexes for table `returns`
--
ALTER TABLE `returns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `borrower_id` (`borrower_id`),
  ADD KEY `book_id` (`book_id`);

--
-- Indexes for table `verification_requests`
--
ALTER TABLE `verification_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `book_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=95;

--
-- AUTO_INCREMENT for table `book_copies`
--
ALTER TABLE `book_copies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=460;

--
-- AUTO_INCREMENT for table `borrowed_books`
--
ALTER TABLE `borrowed_books`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `borrower`
--
ALTER TABLE `borrower`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `borrowers`
--
ALTER TABLE `borrowers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `fine1`
--
ALTER TABLE `fine1`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `fine2`
--
ALTER TABLE `fine2`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `loans`
--
ALTER TABLE `loans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `managers`
--
ALTER TABLE `managers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `overdue_books_fines`
--
ALTER TABLE `overdue_books_fines`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `overdue_fines`
--
ALTER TABLE `overdue_fines`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `returns`
--
ALTER TABLE `returns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `verification_requests`
--
ALTER TABLE `verification_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `book_copies`
--
ALTER TABLE `book_copies`
  ADD CONSTRAINT `book_copies_ibfk_1` FOREIGN KEY (`isbn`) REFERENCES `books` (`isbn`);

--
-- Constraints for table `borrowed_books`
--
ALTER TABLE `borrowed_books`
  ADD CONSTRAINT `borrowed_books_ibfk_1` FOREIGN KEY (`borrower_id`) REFERENCES `borrower` (`id`),
  ADD CONSTRAINT `borrowed_books_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`);

--
-- Constraints for table `fine`
--
ALTER TABLE `fine`
  ADD CONSTRAINT `fine_ibfk_1` FOREIGN KEY (`borrower_id`) REFERENCES `borrowers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fine_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`) ON DELETE CASCADE;

--
-- Constraints for table `fine1`
--
ALTER TABLE `fine1`
  ADD CONSTRAINT `fine1_ibfk_1` FOREIGN KEY (`borrower_id`) REFERENCES `borrowers` (`id`);

--
-- Constraints for table `fine2`
--
ALTER TABLE `fine2`
  ADD CONSTRAINT `fine2_ibfk_1` FOREIGN KEY (`borrower_id`) REFERENCES `borrowers` (`id`);

--
-- Constraints for table `loans`
--
ALTER TABLE `loans`
  ADD CONSTRAINT `fk_isbn` FOREIGN KEY (`isbn`) REFERENCES `book_copies` (`isbn`),
  ADD CONSTRAINT `fk_referral_code` FOREIGN KEY (`referral_code`) REFERENCES `book_copies` (`referral_code`),
  ADD CONSTRAINT `loans_ibfk_1` FOREIGN KEY (`borrower_id`) REFERENCES `borrowers` (`id`),
  ADD CONSTRAINT `loans_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`);

--
-- Constraints for table `overdue_books_fines`
--
ALTER TABLE `overdue_books_fines`
  ADD CONSTRAINT `overdue_books_fines_ibfk_1` FOREIGN KEY (`borrower_id`) REFERENCES `borrowers` (`id`),
  ADD CONSTRAINT `overdue_books_fines_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`);

--
-- Constraints for table `overdue_fines`
--
ALTER TABLE `overdue_fines`
  ADD CONSTRAINT `overdue_fines_ibfk_1` FOREIGN KEY (`borrower_id`) REFERENCES `borrowers` (`id`),
  ADD CONSTRAINT `overdue_fines_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`);

--
-- Constraints for table `returns`
--
ALTER TABLE `returns`
  ADD CONSTRAINT `returns_ibfk_1` FOREIGN KEY (`borrower_id`) REFERENCES `borrowers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `returns_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`book_id`) ON DELETE CASCADE;

DELIMITER $$
--
-- Events
--
CREATE DEFINER=`root`@`localhost` EVENT `delete_old_returns` ON SCHEDULE EVERY 1 DAY STARTS '2024-11-14 23:14:14' ON COMPLETION NOT PRESERVE ENABLE DO DELETE FROM returns WHERE return_date < CURDATE() - INTERVAL 30 DAY$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
