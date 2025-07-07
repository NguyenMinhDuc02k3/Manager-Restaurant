-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th5 22, 2025 lúc 09:09 AM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `restaurant`
--

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `ban`
--

CREATE TABLE `ban` (
  `idban` int(11) NOT NULL,
  `SoBan` varchar(10) DEFAULT NULL,
  `soluongKH` int(11) NOT NULL,
  `TrangThai` varchar(50) DEFAULT NULL,
  `MaKV` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `ban`
--

INSERT INTO `ban` (`idban`, `SoBan`, `soluongKH`, `TrangThai`, `MaKV`) VALUES
(1, 'B01', 0, 'Trống', 4),
(2, 'B02', 4, 'ĐãĐặt', 3),
(3, 'B03', 2, 'ĐangSửDụng', 1),
(4, 'B04', 0, 'Trống', 2),
(5, 'B05', 6, 'ĐãĐặt', 4);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chitietdatban`
--

CREATE TABLE `chitietdatban` (
  `idChiTiet` int(11) NOT NULL,
  `madatban` int(11) NOT NULL,
  `idmonan` int(11) NOT NULL,
  `SoLuong` int(11) NOT NULL,
  `DonGia` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `chitietdatban`
--

INSERT INTO `chitietdatban` (`idChiTiet`, `madatban`, `idmonan`, `SoLuong`, `DonGia`) VALUES
(17, 13, 1, 1, 80000.00),
(18, 13, 3, 1, 90000.00),
(19, 13, 5, 1, 100000.00),
(20, 14, 2, 1, 75000.00),
(21, 14, 1, 1, 80000.00),
(22, 15, 4, 1, 95000.00),
(23, 15, 5, 1, 100000.00),
(24, 16, 22, 1, 129000.00),
(25, 17, 10, 1, 15000.00),
(26, 17, 2, 1, 75000.00),
(27, 17, 4, 1, 95000.00),
(28, 18, 1, 1, 80000.00),
(29, 18, 4, 1, 95000.00),
(30, 18, 2, 1, 75000.00),
(31, 18, 18, 1, 30000.00),
(32, 18, 11, 1, 15000.00),
(33, 18, 14, 1, 15000.00),
(34, 18, 21, 1, 20000.00),
(35, 19, 1, 1, 80000.00),
(36, 20, 1, 1, 80000.00),
(37, 20, 2, 1, 75000.00),
(38, 20, 15, 1, 15000.00);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chitietdonhang`
--

CREATE TABLE `chitietdonhang` (
  `idCTDH` int(11) NOT NULL,
  `idDH` int(11) DEFAULT NULL,
  `idmonan` int(11) DEFAULT NULL,
  `SoLuong` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `chitietdonhang`
--

INSERT INTO `chitietdonhang` (`idCTDH`, `idDH`, `idmonan`, `SoLuong`) VALUES
(1, 5, NULL, 2),
(2, 2, 2, 1),
(3, 3, 3, 3),
(4, 4, 4, 2),
(5, 5, 5, 1),
(11, 2, 4, 1),
(12, 1, 5, 2),
(13, 2, 6, 1),
(29, 21, 13, 1),
(30, 22, 5, 1),
(31, 23, 5, 1),
(32, 24, 6, 1),
(33, 25, 11, 1),
(34, 26, 9, 1),
(35, 26, 6, 1),
(36, 26, 4, 1),
(51, 32, 2, 1),
(52, 32, 5, 1),
(53, 32, 6, 1),
(54, 33, 3, 1),
(55, 33, 6, 1),
(56, 33, 10, 1),
(57, 34, 13, 1),
(58, 34, 10, 1),
(59, 34, 5, 1),
(60, 34, 2, 1),
(61, 35, 2, 1),
(62, 35, 5, 1),
(63, 35, 9, 1),
(64, 36, 3, 1),
(65, 36, 6, 1),
(66, 37, 3, 1),
(67, 37, 6, 1),
(68, 38, 3, 1),
(69, 38, 6, 1),
(70, 38, 10, 1),
(71, 39, 3, 1),
(72, 39, 6, 1),
(73, 39, 10, 1),
(74, 40, 9, 1),
(75, 40, 5, 1),
(76, 41, 2, 1),
(77, 41, 5, 1),
(78, 42, 1, 1),
(79, 42, 5, 1),
(80, 42, 12, 1),
(81, 43, 1, 1),
(82, 44, 1, 1),
(83, 44, 2, 1),
(84, 44, 15, 1),
(85, 45, 2, 1),
(86, 45, 4, 1),
(87, 45, 6, 1),
(88, 46, 2, 1),
(89, 46, 4, 1),
(90, 46, 6, 1),
(91, 47, 4, 1),
(92, 47, 6, 1),
(93, 47, 9, 1),
(94, 48, 4, 1),
(95, 48, 6, 1),
(96, 49, 3, 1),
(97, 49, 1, 1),
(98, 49, 5, 1),
(99, 49, 6, 1),
(100, 49, 4, 1),
(101, 49, 2, 1),
(102, 49, 10, 1),
(103, 49, 7, 1),
(104, 49, 21, 1),
(105, 49, 19, 1),
(106, 49, 17, 1),
(107, 50, 4, 1),
(108, 50, 2, 1),
(109, 50, 6, 1),
(110, 50, 9, 1),
(111, 51, 2, 1),
(112, 51, 4, 1),
(113, 52, 6, 1),
(114, 52, 4, 1),
(115, 52, 2, 1),
(116, 53, 2, 1),
(117, 53, 4, 1),
(118, 54, 6, 1),
(119, 54, 9, 1),
(120, 54, 11, 1),
(121, 55, 2, 1),
(122, 55, 4, 1),
(123, 55, 9, 5),
(124, 56, 6, 1),
(125, 56, 4, 1),
(126, 57, 4, 4);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chitiethoadon`
--

CREATE TABLE `chitiethoadon` (
  `idCTHD` int(11) NOT NULL,
  `idHD` int(11) NOT NULL,
  `idmonan` int(11) NOT NULL,
  `soluong` double NOT NULL,
  `thanhtien` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `chitiethoadon`
--

INSERT INTO `chitiethoadon` (`idCTHD`, `idHD`, `idmonan`, `soluong`, `thanhtien`) VALUES
(1, 10, 2, 1, 75000),
(2, 10, 4, 1, 95000),
(3, 10, 6, 1, 85000),
(6, 12, 3, 3, 270000),
(9, 14, 4, 2, 190000),
(13, 20, 5, 1, 100000),
(14, 21, 11, 1, 15000),
(15, 22, 9, 1, 200000),
(16, 22, 6, 1, 85000),
(17, 22, 4, 1, 95000),
(36, 31, 2, 1, 75000),
(37, 31, 5, 1, 100000),
(38, 31, 6, 1, 85000),
(39, 32, 13, 1, 20000),
(40, 32, 10, 1, 15000),
(41, 32, 5, 1, 100000),
(42, 32, 2, 1, 75000),
(43, 33, 3, 1, 90000),
(44, 33, 6, 1, 85000),
(45, 33, 10, 1, 15000),
(46, 36, 3, 1, 90000),
(47, 36, 6, 1, 85000),
(48, 37, 3, 1, 90000),
(49, 37, 6, 1, 85000),
(50, 37, 10, 1, 15000),
(51, 38, 3, 1, 90000),
(52, 38, 6, 1, 85000),
(53, 38, 10, 1, 15000),
(54, 39, 9, 1, 200000),
(55, 39, 5, 1, 100000),
(56, 40, 2, 1, 75000),
(57, 40, 5, 1, 100000),
(58, 41, 1, 1, 80000),
(59, 42, 1, 1, 80000),
(60, 42, 5, 1, 100000),
(61, 42, 12, 1, 450000),
(62, 43, 1, 1, 80000),
(63, 43, 2, 1, 75000),
(64, 43, 15, 1, 15000),
(65, 44, 2, 1, 75000),
(66, 44, 4, 1, 95000),
(67, 44, 6, 1, 85000),
(68, 45, 2, 1, 75000),
(69, 45, 4, 1, 95000),
(70, 45, 6, 1, 85000),
(71, 46, 4, 1, 95000),
(72, 46, 6, 1, 85000),
(73, 46, 9, 1, 200000),
(74, 47, 4, 1, 95000),
(75, 47, 6, 1, 85000),
(76, 48, 3, 1, 90000),
(77, 48, 1, 1, 80000),
(78, 48, 5, 1, 100000),
(79, 48, 6, 1, 85000),
(80, 48, 4, 1, 95000),
(81, 48, 2, 1, 75000),
(82, 48, 10, 1, 15000),
(83, 48, 7, 1, 90000),
(84, 48, 21, 1, 20000),
(85, 48, 19, 1, 375000),
(86, 48, 17, 1, 25000),
(87, 49, 2, 1, 75000),
(88, 49, 4, 1, 95000),
(89, 50, 6, 1, 85000),
(90, 50, 4, 1, 95000);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `danhmuc`
--

CREATE TABLE `danhmuc` (
  `iddm` int(11) NOT NULL,
  `tendanhmuc` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `danhmuc`
--

INSERT INTO `danhmuc` (`iddm`, `tendanhmuc`) VALUES
(1, 'Khai vị'),
(2, 'Món chính'),
(3, 'Tráng miệng'),
(4, 'Đồ uống'),
(5, 'Đặc biệt');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `datban`
--

CREATE TABLE `datban` (
  `madatban` int(11) NOT NULL,
  `idKH` int(100) DEFAULT NULL,
  `idban` int(11) DEFAULT NULL,
  `NgayDatBan` datetime DEFAULT NULL,
  `SoLuongKhach` int(11) DEFAULT NULL,
  `TongTien` decimal(10,2) NOT NULL,
  `TrangThai` varchar(50) DEFAULT NULL,
  `tenKH` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `sodienthoai` varchar(20) DEFAULT NULL,
  `NgayTao` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `datban`
--

INSERT INTO `datban` (`madatban`, `idKH`, `idban`, `NgayDatBan`, `SoLuongKhach`, `TongTien`, `TrangThai`, `tenKH`, `email`, `sodienthoai`, `NgayTao`) VALUES
(13, NULL, 3, '2025-05-12 12:00:00', 1, 270000.00, 'danhan', 'Gia Hi', 'giahi@gmail.com', '0796123823', '2025-05-16 13:01:36'),
(14, NULL, 4, '2025-05-12 12:00:00', 1, 155000.00, 'danhan', 'Gia Hi', 'giahi@gmail.com', '0796123823', '2025-05-16 13:01:36'),
(15, NULL, 2, '2025-05-12 12:00:00', 1, 195000.00, 'confirmed', 'Gia Hi', 'giahi@gmail.com', '0796123823', '2025-05-16 13:01:36'),
(16, NULL, 2, '2025-05-12 16:00:00', 1, 129000.00, 'danhan', 'Gia Hi', 'giahi@gmail.com', '0796123823', '2025-05-16 13:01:36'),
(17, NULL, 4, '2025-05-19 18:00:00', 1, 185000.00, 'confirmed', 'Lê Nguyễn Gia Hân', 'lenguyengiahan0155@gmail.com', '0796133633', '2025-05-18 13:49:16'),
(18, NULL, 3, '2025-05-19 19:00:00', 2, 330000.00, 'confirmed', 'Phạm Văn Quân', 'phamquan100503@gmail.com', '0898543071', '2025-05-18 14:13:03'),
(19, NULL, 3, '2025-05-19 16:00:00', 1, 80000.00, 'confirmed', 'Nguyễn Minh Đức', 'tn6888295@gmail.com', '0928449664', '2025-05-18 14:14:47'),
(20, NULL, 3, '2025-05-20 16:00:00', 2, 170000.00, 'confirmed', 'Lê Hoàng Gia Hi', 'giahi0000@gmail.com', '0796123823', '2025-05-18 15:56:36');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `donhang`
--

CREATE TABLE `donhang` (
  `idDH` int(11) NOT NULL,
  `idKH` int(11) NOT NULL,
  `idban` int(11) NOT NULL,
  `NgayDatHang` datetime NOT NULL,
  `TongTien` decimal(10,2) DEFAULT NULL,
  `TrangThai` varchar(50) DEFAULT NULL,
  `MaDonHang` varchar(100) NOT NULL,
  `SoHoaDon` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `donhang`
--

INSERT INTO `donhang` (`idDH`, `idKH`, `idban`, `NgayDatHang`, `TongTien`, `TrangThai`, `MaDonHang`, `SoHoaDon`) VALUES
(1, 1, 2, '2025-04-14 00:00:00', 250000.00, 'Đã thanh toán', '250414-00001', NULL),
(2, 2, 1, '2025-04-21 00:00:00', 370000.00, 'Đã thanh toán', '250421-00002', NULL),
(3, 1, 3, '2025-04-21 00:00:00', 120000.00, 'Đã thanh toán', '250421-00003', NULL),
(4, 4, 4, '2025-04-22 00:00:00', 180000.00, 'Đã thanh toán', '250422-00004', NULL),
(5, 2, 5, '2025-04-22 00:00:00', 450000.00, 'Đã thanh toán', '250422-00005', NULL),
(6, 4, 2, '2025-04-20 00:00:00', 210000.00, 'Đã thanh toán', '250420-00006', NULL),
(21, 6, 3, '2025-05-16 00:00:00', 20000.00, 'Đã thanh toán', '250516-00021', '191759'),
(22, 6, 3, '2025-05-16 00:00:00', 100000.00, 'Đã thanh toán', '250516-00022', '192156'),
(23, 6, 1, '2025-05-16 00:00:00', 100000.00, 'Đã thanh toán', '250516-00023', '192600'),
(24, 6, 1, '2025-05-16 00:00:00', 85000.00, 'Đã thanh toán', '250516-00024', '192736'),
(25, 6, 3, '2025-05-16 00:00:00', 15000.00, 'Đã thanh toán', '250516-00025', '201210'),
(26, 20, 3, '2025-05-16 00:00:00', 380000.00, 'Đã thanh toán', '250516-00026', '202244'),
(32, 6, 3, '2025-05-16 00:00:00', 260000.00, 'Đã thanh toán', '250516-00032', '165342'),
(33, 20, 3, '2025-05-17 00:00:00', 190000.00, 'Đã thanh toán', '250517-00033', '064114'),
(34, 6, 4, '2025-05-17 00:00:00', 210000.00, 'Đã thanh toán', '250517-00034', '065744'),
(35, 6, 3, '2025-05-17 00:00:00', 375000.00, 'Đã thanh toán', '250517-00035', '073043'),
(36, 20, 1, '2025-05-17 00:00:00', 175000.00, 'Đã thanh toán', '250517-00036', '075438'),
(37, 6, 4, '2025-05-17 00:00:00', 175000.00, 'Đã thanh toán', '250517-00037', '080255'),
(38, 6, 3, '2025-05-17 00:00:00', 190000.00, 'Đã thanh toán', '250517-00038', '081539'),
(39, 20, 4, '2025-05-17 00:00:00', 190000.00, 'Đã thanh toán', '250517-00039', '081635'),
(40, 20, 4, '2025-05-17 00:00:00', 300000.00, 'Đã thanh toán', '250517-00040', '081935'),
(41, 6, 3, '2025-05-17 00:00:00', 175000.00, 'Đã thanh toán', '250517-00041', '083419'),
(42, 20, 3, '2025-05-17 00:00:00', 630000.00, 'Đã thanh toán', '250517-00042', '090330'),
(43, 21, 2, '2025-05-18 00:00:00', 80000.00, 'Đã thanh toán', '250518-00043', '085932'),
(44, 20, 3, '2025-05-20 16:00:00', 170000.00, 'Đã thanh toán', '250520-00044', '155636'),
(45, 6, 4, '2025-05-22 12:04:43', 255000.00, 'Đã thanh toán', '250522-120435', '120435'),
(46, 20, 3, '2025-05-22 12:13:45', 255000.00, 'Đã thanh toán', '250522-121338', '121338'),
(47, 6, 4, '2025-05-22 12:18:46', 380000.00, 'Đã thanh toán', '250522-121839', '121839'),
(48, 20, 4, '2025-05-22 12:21:04', 180000.00, 'Đã thanh toán', '250522-122104', '122104'),
(49, 20, 2, '2025-05-22 12:35:36', 1050000.00, 'Đã thanh toán', '250522073702', '123536'),
(50, 20, 3, '2025-05-22 12:42:34', 455000.00, 'Đã thanh toán', '250522-124234', '124234'),
(51, 6, 3, '2025-05-22 12:55:09', 170000.00, 'Đã thanh toán', '250522-125509', '125509'),
(52, 6, 1, '2025-05-22 13:02:41', 255000.00, 'Đã thanh toán', '250522-130241', '130241'),
(53, 20, 3, '2025-05-22 13:06:31', 170000.00, 'Đã thanh toán', '250522-130631', '130631'),
(54, 20, 1, '2025-05-22 13:09:24', 300000.00, 'Đã thanh toán', '250522-130924', '130924'),
(55, 20, 2, '2025-05-22 13:24:59', 1170000.00, 'Đã thanh toán', '250522084440', '132459'),
(56, 6, 2, '2025-05-22 13:52:27', 180000.00, 'Đã thanh toán', '220525-135227', '135227'),
(57, 6, 1, '2025-05-22 13:54:58', 380000.00, 'Đã thanh toán', '220525-085546', '135458');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `hoadon`
--

CREATE TABLE `hoadon` (
  `idHD` int(11) NOT NULL,
  `idKH` int(11) DEFAULT NULL,
  `idDH` int(11) NOT NULL,
  `Ngay` datetime NOT NULL,
  `hinhthucthanhtoan` varchar(100) NOT NULL,
  `TongTien` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `hoadon`
--

INSERT INTO `hoadon` (`idHD`, `idKH`, `idDH`, `Ngay`, `hinhthucthanhtoan`, `TongTien`) VALUES
(10, 2, 2, '2025-04-21 00:00:00', '', 255000),
(12, 1, 3, '2025-04-21 00:00:00', 'Tiền mặt', 270000),
(14, 4, 4, '2025-04-22 00:00:00', 'Chuyển khoản', 190000),
(20, 6, 23, '2025-05-16 00:00:00', 'Chuyển khoản', 100000),
(21, 6, 25, '2025-05-16 00:00:00', 'Chuyển khoản', 15000),
(22, 20, 26, '2025-05-16 00:00:00', 'Chuyển khoản', 380000),
(31, 6, 32, '2025-05-16 00:00:00', 'Chuyển khoản', 260000),
(32, 6, 34, '2025-05-17 00:00:00', 'Chuyển khoản', 210000),
(33, 20, 33, '2025-05-17 00:00:00', 'Chuyển khoản', 190000),
(34, 6, 35, '2025-05-17 00:00:00', 'Tiền mặt', 375000),
(35, 20, 36, '2025-05-17 00:00:00', 'Tiền mặt', 175000),
(36, 6, 37, '2025-05-17 00:00:00', 'Tiền mặt', 175000),
(37, 6, 38, '2025-05-17 00:00:00', 'Tiền mặt', 190000),
(38, 20, 39, '2025-05-17 00:00:00', 'Tiền mặt', 190000),
(39, 20, 40, '2025-05-17 00:00:00', 'Tiền mặt', 300000),
(40, 6, 41, '2025-05-17 00:00:00', 'Tiền mặt', 175000),
(41, 21, 43, '2025-05-22 11:53:56', 'Tiền mặt', 80000),
(42, 20, 42, '2025-05-22 11:55:26', 'Chuyển khoản', 630000),
(43, 20, 44, '2025-05-22 12:03:58', 'Chuyển khoản', 170000),
(44, 6, 45, '2025-05-22 12:05:47', 'Chuyển khoản', 255000),
(45, 20, 46, '2025-05-22 12:14:01', 'Tiền mặt', 255000),
(46, 6, 47, '2025-05-22 12:19:00', 'Tiền mặt', 380000),
(47, 20, 48, '2025-05-22 12:25:37', 'Tiền mặt', 180000),
(48, 20, 49, '2025-05-22 12:37:02', 'Chuyển khoản', 1050000),
(49, 6, 51, '2025-05-22 12:58:00', 'Tiền mặt', 170000),
(50, 6, 56, '2025-05-22 13:52:40', 'Tiền mặt', 180000);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `khachhang`
--

CREATE TABLE `khachhang` (
  `idKH` int(11) NOT NULL,
  `tenKH` varchar(100) NOT NULL,
  `sodienthoai` varchar(10) NOT NULL,
  `email` varchar(100) NOT NULL,
  `ngaysinh` date DEFAULT NULL,
  `gioitinh` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `khachhang`
--

INSERT INTO `khachhang` (`idKH`, `tenKH`, `sodienthoai`, `email`, `ngaysinh`, `gioitinh`) VALUES
(1, 'Nguyễn Văn A', '0235467584', 'vana@gmail.com', '1995-08-15', 'Nam'),
(2, 'Trần Thị B', '0765849302', 'tere@gmail.com', '1998-04-22', 'Nữ'),
(3, 'Lê Văn C', '0684975432', 'kh-c@gmail.com', '1990-12-01', 'Nam'),
(4, 'Phạm Thị D', '0285467839', '', '2000-06-30', 'Nữ'),
(5, 'Hoàng Văn E', '0756483902', '', '1992-11-10', 'Nam'),
(6, 'Nguyễn Minh Đức', '0928449664', 'tn6888295@gmail.com', '2003-06-26', 'Nam'),
(7, 'Phạm Văn Quân', '0378946527', 'phamquan@gmail.com', '2003-05-10', 'Nam'),
(8, 'Huỳnh Hồ Hoài Nam', '0945786380', 'namhuynh@gmail.com', '2003-12-02', 'Nam'),
(20, 'Lê Hoàng Gia Hi', '0796123823', 'giahi0000@gmail.com', '2003-01-05', 'Nữ'),
(21, 'Lê Nguyễn Gia Hân', '0796133633', 'lenguyengiahan0155@gmail.com', NULL, NULL),
(22, 'Phạm Văn Quân', '0898543071', 'phamquan100503@gmail.com', NULL, NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `khuvucban`
--

CREATE TABLE `khuvucban` (
  `MaKV` int(11) NOT NULL,
  `TenKV` varchar(100) NOT NULL,
  `MoTa` text DEFAULT NULL,
  `TrangThai` varchar(20) DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Đang đổ dữ liệu cho bảng `khuvucban`
--

INSERT INTO `khuvucban` (`MaKV`, `TenKV`, `MoTa`, `TrangThai`) VALUES
(1, 'Tầng 1', 'Khu vực thông thường gần cửa chính', 'active'),
(2, 'Tầng 2', 'Khu yên tĩnh, phù hợp nhóm gia đình', 'active'),
(3, 'Sân vườn', 'Ngoài trời, thoáng mát, hút thuốc được', 'active'),
(4, 'Phòng VIP', 'Riêng tư, có điều hoà, TV', 'active');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `khuyenmai`
--

CREATE TABLE `khuyenmai` (
  `MaKhuyenMai` varchar(20) NOT NULL,
  `MoTa` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_vietnamese_ci DEFAULT NULL,
  `GiaTri` decimal(5,2) NOT NULL,
  `LoaiGiam` enum('percent','fixed') NOT NULL,
  `NgayBatDau` date NOT NULL,
  `NgayKetThuc` date NOT NULL,
  `TrangThai` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `khuyenmai`
--

INSERT INTO `khuyenmai` (`MaKhuyenMai`, `MoTa`, `GiaTri`, `LoaiGiam`, `NgayBatDau`, `NgayKetThuc`, `TrangThai`) VALUES
('FIXED20000', 'Giảm 20,000 VND cho mọi hóa đơn', 999.99, 'fixed', '2025-05-01', '2025-05-31', 'active'),
('JUNE05', 'Giảm 5% cho hóa đơn từ tháng 6', 5.00, 'percent', '2025-06-01', '2025-06-30', 'active'),
('MAY05', 'Giảm 5% cho hóa đơn tháng 5', 5.00, 'percent', '2025-05-01', '2025-05-31', 'active'),
('SPRING15', 'Giảm 15% cho hóa đơn tháng 4', 15.00, 'percent', '2025-04-01', '2025-04-30', 'active'),
('SUMMER10', 'Giảm 10% cho hóa đơn trong tháng 5', 10.00, 'percent', '2025-05-01', '2025-05-31', 'active'),
('WINTER25', 'Giảm 25% nhưng không hoạt động', 25.00, 'percent', '2025-05-01', '2025-05-31', 'inactive');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `loaitonkho`
--

CREATE TABLE `loaitonkho` (
  `idloaiTK` int(11) NOT NULL,
  `tenloaiTK` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `loaitonkho`
--

INSERT INTO `loaitonkho` (`idloaiTK`, `tenloaiTK`) VALUES
(1, 'Nguyên liệu'),
(2, 'Vật dụng');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `log_giaodich`
--

CREATE TABLE `log_giaodich` (
  `id` int(11) NOT NULL,
  `idDH` int(11) DEFAULT NULL,
  `madatban` int(11) DEFAULT NULL,
  `MaGiaoDich` varchar(50) DEFAULT NULL,
  `SoTien` decimal(10,2) DEFAULT NULL,
  `TrangThai` varchar(20) DEFAULT NULL,
  `ThoiGian` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `log_giaodich`
--

INSERT INTO `log_giaodich` (`id`, `idDH`, `madatban`, `MaGiaoDich`, `SoTien`, `TrangThai`, `ThoiGian`) VALUES
(2, 5, NULL, '14961344', 100000.00, 'completed', '2025-05-16 17:59:41'),
(3, 21, NULL, '14961452', 20000.00, 'completed', '2025-05-16 19:19:13'),
(4, 22, NULL, '14961456', 100000.00, 'completed', '2025-05-16 19:22:49'),
(5, 24, NULL, '14961460', 85000.00, 'completed', '2025-05-16 19:28:28'),
(6, 23, NULL, '14961467', 100000.00, 'completed', '2025-05-16 19:35:20'),
(7, 25, NULL, '14961509', 15000.00, 'completed', '2025-05-16 20:13:52'),
(8, 26, NULL, '14961523', 380000.00, 'completed', '2025-05-16 20:24:05'),
(12, 32, NULL, '14961678', 260000.00, 'completed', '2025-05-16 21:54:46'),
(13, 34, NULL, '14962305', 210000.00, 'completed', '2025-05-17 12:13:31'),
(14, 33, NULL, '14962317', 190000.00, 'completed', '2025-05-17 12:29:31'),
(15, 42, NULL, '14972563', 630000.00, 'completed', '2025-05-22 11:55:26'),
(16, 44, NULL, '14972580', 170000.00, 'completed', '2025-05-22 12:03:58'),
(17, 45, NULL, '14972585', 255000.00, 'completed', '2025-05-22 12:05:47'),
(18, 49, NULL, '14972627', 1050000.00, 'completed', '2025-05-22 12:37:02');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `monan`
--

CREATE TABLE `monan` (
  `idmonan` int(11) NOT NULL,
  `tenmonan` varchar(100) NOT NULL,
  `mota` varchar(500) NOT NULL,
  `DonGia` double NOT NULL,
  `hinhanh` varchar(200) NOT NULL,
  `iddm` int(11) NOT NULL,
  `DonViTinh` varchar(100) NOT NULL,
  `TrangThai` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `monan`
--

INSERT INTO `monan` (`idmonan`, `tenmonan`, `mota`, `DonGia`, `hinhanh`, `iddm`, `DonViTinh`, `TrangThai`) VALUES
(1, 'Súp hải sản', 'Với sự kết hợp hoàn hảo giữa tôm, mực, nghêu và các loại rau củ tươi. Nước dùng ngọt thanh, cay nhẹ, tạo nên món khai vị hấp dẫn và bổ dưỡng.', 80000, 'menu_1.jpg', 1, 'phần', 'active'),
(2, 'Cơm gói lá sen', 'món ăn thanh nhã, thơm ngon với cơm được trộn cùng hạt sen, nấm, rau củ và thịt xào, sau đó được gói trong lá sen và hấp chín. Hương thơm đặc trưng của lá sen lan tỏa, hòa quyện cùng vị ngọt bùi của nguyên liệu, tạo nên món ăn vừa tinh tế vừa bổ dưỡng.', 75000, 'menu_2.jpg', 2, 'phần', 'active'),
(3, 'Gỏi xoài tôm khô', 'Vị chua cay ngọt hài hòa của xoài xanh kết hợp với tôm khô và đậu phộng rang.', 90000, 'menu_3.webp', 1, 'phần', 'active'),
(4, 'Bò lúc lắc', 'Thịt bò mềm, xào cùng rau củ, đậm đà hương vị và bắt mắt.', 95000, 'menu_4.jpg', 2, 'phần', 'active'),
(5, 'Cơm chiên hải sản', 'Cơm chiên vàng ruộm cùng hải sản tươi ngon và trứng gà.', 100000, 'menu_5.jpg', 2, 'phần', 'active'),
(6, 'Canh chua cá lóc', 'Vị chua thanh của me và thơm hòa quyện với cá lóc tươi ngọt.', 85000, 'menu_61.jpg', 2, 'phần', 'active'),
(7, 'Tôm rang me', 'Tôm tươi rang cùng sốt me chua ngọt đậm đà, hấp dẫn.', 90000, 'menu_71.jpg', 2, 'phần', 'active'),
(9, 'Vịt quay Bắc Kinh', 'Món ăn chính trong bữa ăn', 200000, 'vitquay.jpg', 2, 'phần', 'active'),
(10, 'Soda chanh', 'Kết hợp giữa vị chua nhẹ của chanh và độ sủi bọt mát lạnh của soda, thường được thêm đường và đá viên để tăng độ hấp dẫn', 15000, 'soda_chanh.jpg', 4, 'phần', 'active'),
(11, 'Bánh flan', 'Món tráng miệng mềm mịn, thơm béo từ trứng và sữa, với lớp caramel ngọt đậm phủ bên trên. Khi ăn, bánh tan nhẹ trong miệng, mang đến cảm giác mát lạnh và ngọt ngào dễ chịu', 15000, 'flan.jpg', 3, 'cái', 'active'),
(12, 'Combo 1', 'Combo Cá Lăng Đặc Sắc với ba món chuẩn vị truyền thống, mang đậm hương vị quê nhà. Từ món nóng đến món nguội, tất cả hòa quyện tạo nên bữa ăn tròn vị, thích hợp cho mọi dịp sum vầy.\r\n\r\n', 450000, 'special.jpg', 5, 'combo', 'active'),
(13, 'Kem dâu', 'Kem vani quyện sốt dâu rừng cũng với dâu tươi mọng nước và socola ngọt ngào – mát lạnh, phù hợp tráng miệng sau bữa ăn.', 20000, 'kemdau.jpg', 3, 'ly', 'active'),
(14, 'CocaCola', 'Nước ngọt có ga ', 15000, 'coca_cola.jpg', 4, 'lon', 'active'),
(15, 'Pepsi', 'Nước uống có ga', 15000, 'pepsi.jpg', 4, 'lon', 'active'),
(16, 'Combo2', 'Thưởng thức trọn vị miền Tây với lẩu mắm đậm đà kèm rau, hải sản tươi sống và mẹt gà 5 món thơm ngon hấp dẫn.', 25000, 'combo2.jpg', 5, 'combo', 'active'),
(17, 'Bánh lọt lá dứa nước cốt dừa', 'Sự kết hợp tinh tế giữa bánh lọt dai mềm làm từ lá dứa tươi, nước cốt dừa nguyên chất béo mịn và lớp đường thốt nốt thơm lừng. Món tráng miệng mát lạnh, ngọt thanh, mang đậm hương vị truyền thống.', 25000, 'banhlot.jpg', 3, 'ly', 'active'),
(18, 'Panna cotta Bơ', 'Lớp kem phô mai mềm mịn quyện cùng sốt bơ tươi thanh béo, trang trí cùng topping bơ tươi mát lạnh  – một món tráng miệng nhẹ nhàng nhưng đầy cuốn hút.', 30000, 'pannacotta_bơ.jpg', 3, 'ly', 'active'),
(19, 'Combo3', 'Cơm trưa chuẩn Việt -  cơm trắng dẻo thơm, canh hầm rau củ ngọt thanh, rau luộc xanh mướt, trứng kho đậm đà, thịt kho tộ thơm lừng, tôm rim mặn mà và đậu que xào thịt hấp dẫn – tất cả được bày biện tinh tế, đậm chất ẩm thực truyền thống', 375000, 'comtruachuanViet.jpg', 5, 'combo', 'active'),
(20, 'Nước cam ép ', 'Cam ép nguyên chất, mát lạnh, ngọt thanh và giàu vitamin – giải khát sảng khoái, trọn vị tươi mới.', 20000, 'camep.jpg', 4, 'ly', 'active'),
(21, 'Trà đào', 'Vị trà thơm nhẹ quyện cùng miếng đào giòn ngọt, thêm lát chanh chua dịu và đá viên mát rượi, đánh thức vị giác, giải nhiệt tức thì.', 20000, 'tradao.jpg', 4, 'ly', 'active'),
(22, 'Combo 3', 'Beefsteak thượng hạng -  miếng steak dày mọng, chín hoàn hảo, thơm ngậy vị bơ tỏi và thảo mộc, ăn kèm khoai nướng giòn rụm, măng tây, cà chua bi nướng, cùng rượu vang đỏ hảo hạng – chuẩn vị sang trọng cho bữa tối đẳng cấp', 129000, 'combo3.jpg', 5, 'combo', 'active'),
(23, 'Salad Hy Lạp', 'Sự hòa quyện thanh tao của dưa chuột , cà chua , ô liu đen , phô mai feta cao cấp, và húng quế tươi, rưới thêm dầu ô liu nguyên chất, mang đến trải nghiệm ẩm thực sang trọng.', 45000, 'salad4mua.jpg', 1, 'phần', 'active'),
(24, 'Combo 4', 'Ẩm Thực Huế -   với sự kết hợp tinh hoa của bánh bèo chén, há cảo tôm, bánh nậm mềm mịn, bánh lọc trong veo nhân tôm thịt đậm đà và Kèm theo nước chấm chua ngọt tinh tế', 120000, 'combo4.jpg', 5, 'combo', 'active');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nguoidung`
--

CREATE TABLE `nguoidung` (
  `idnguoidung` int(11) NOT NULL,
  `tennguoidung` varchar(100) NOT NULL,
  `idtaikhoan` int(11) NOT NULL,
  `sodienthoai` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nhacungcap`
--

CREATE TABLE `nhacungcap` (
  `idncc` int(11) NOT NULL,
  `tennhacungcap` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `nhacungcap`
--

INSERT INTO `nhacungcap` (`idncc`, `tennhacungcap`) VALUES
(1, 'Công ty nguyên liệu A'),
(2, 'Công ty gia dụng B');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nhanvien`
--

CREATE TABLE `nhanvien` (
  `idnv` int(11) NOT NULL,
  `HinhAnh` varchar(100) NOT NULL,
  `HoTen` varchar(100) DEFAULT NULL,
  `GioiTinh` varchar(100) NOT NULL,
  `ChucVu` varchar(50) DEFAULT NULL,
  `SoDienThoai` varchar(20) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `password` varchar(100) NOT NULL,
  `DiaChi` varchar(100) DEFAULT NULL,
  `Luong` decimal(10,2) DEFAULT NULL,
  `idvaitro` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `nhanvien`
--

INSERT INTO `nhanvien` (`idnv`, `HinhAnh`, `HoTen`, `GioiTinh`, `ChucVu`, `SoDienThoai`, `Email`, `password`, `DiaChi`, `Luong`, `idvaitro`) VALUES
(1, 'profile2.jpg', 'Nguyễn Văn Phúc', 'Nam', 'Phục vụ', '0912345678', 'phucnv@example.com', '1234', '123 Lê Lợi, Q.1, TP.HCM', 7000000.00, 1),
(2, 'testimonial-1.jpg', 'Trần Thị Hòa', 'Nữ', 'Đầu bếp', '0987654321', 'hoatt@example.com', '123', '45 Hai Bà Trưng, Q.3, TP.HCM', 10000000.00, 3),
(3, 'testimonial-3.jpg', 'Lê Văn Khánh', 'Nam', 'Thu ngân', '0909123456', 'khanhlv@example.com', '123', '87 Nguyễn Trãi, Q.5, TP.HCM', 8000000.00, 2),
(4, 'profile2.jpg', 'Phạm Thị Mai', '', 'Phục vụ', '0977123456', 'maipt@example.com', '', '16 Trần Hưng Đạo, Q.1, TP.HCM', 7000000.00, 1),
(7, 'about-3.jpg', 'Lê Hoàng Gia Hi', 'Nữ', 'Quản lý', '0796123823', 'giahi@gmail.com', '123', 'aksjfhhasd', 10000000.00, 4),
(10, 'jm_denis.jpg', 'Huỳnh Hồ Hoài Nam', 'Nam', 'Phục vụ', '0235478965', 'namhuynh@gmail.com', '$2y$10$2SW8xCsZCt5XbK7GRnr9leDABSyfHbkduFnPP970V2mOJePn3kH/2', '123afsdfdsgff', 5000000.00, 2),
(12, 'about-1.jpg', 'Nguyễn Uyển Quyên', 'Nữ', NULL, 'sdgsdg', 'quyen@gmail.com', '123', 'sddgsdg', 12445.00, 1),
(13, 'team-2.jpg', 'Hoàng Văn Minh', 'Nam', 'Đầu bếp', '0933123456', 'minhhv@example.com', '', '78 Nguyễn Huệ, Q.1, TP.HCM', 12000000.00, 3),
(14, 'team-3.jpg', 'Nguyễn Thị Lan', 'Nữ', 'Đầu bếp', '0944123456', 'lannt@example.com', '', '56 Lê Duẩn, Q.1, TP.HCM', 12000000.00, 3),
(15, 'team-4.jpg', 'Trần Văn Sơn', 'Nam', 'Đầu bếp', '0955123456', 'sontv@example.com', '123', '34 Đồng Khởi, Q.1, TP.HCM', 12000000.00, 3),
(17, 'about-3.jpg', 'Nguyễn Tiến Chung', 'Nam', 'Thu ngân', '0123456963', 'chung@gmail.com', '123', '123', 1.00, 2);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `phanhoi`
--

CREATE TABLE `phanhoi` (
  `idPhanHoi` int(11) NOT NULL,
  `idKH` int(11) DEFAULT NULL,
  `HoTen` varchar(100) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `SoDienThoai` varchar(20) NOT NULL,
  `ChuDe` varchar(200) NOT NULL,
  `NoiDung` text NOT NULL,
  `NgayGui` datetime DEFAULT current_timestamp(),
  `TrangThai` enum('Chưa đọc','Đã đọc') DEFAULT 'Chưa đọc'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `thanhtoan`
--

CREATE TABLE `thanhtoan` (
  `idThanhToan` int(11) NOT NULL,
  `madatban` int(11) DEFAULT NULL,
  `idDH` int(11) DEFAULT NULL,
  `SoTien` decimal(10,2) NOT NULL,
  `PhuongThuc` varchar(50) NOT NULL,
  `TrangThai` enum('pending','completed','failed') DEFAULT 'pending',
  `NgayThanhToan` datetime DEFAULT current_timestamp(),
  `MaGiaoDich` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `thanhtoan`
--

INSERT INTO `thanhtoan` (`idThanhToan`, `madatban`, `idDH`, `SoTien`, `PhuongThuc`, `TrangThai`, `NgayThanhToan`, `MaGiaoDich`) VALUES
(23, NULL, 34, 210000.00, 'vnpay', 'completed', '2025-05-17 12:13:31', '14962305'),
(24, NULL, 33, 190000.00, 'vnpay', 'completed', '2025-05-17 12:29:31', '14962317'),
(27, NULL, 37, 175000.00, 'Tiền mặt', 'completed', '2025-05-17 13:12:05', ''),
(28, NULL, 38, 190000.00, 'Tiền mặt', 'completed', '2025-05-17 13:16:03', ''),
(29, NULL, 39, 190000.00, 'Tiền mặt', 'completed', '2025-05-17 13:17:05', ''),
(30, NULL, 40, 300000.00, 'Tiền mặt', 'completed', '2025-05-17 13:22:32', ''),
(31, NULL, 41, 175000.00, 'Tiền mặt', 'completed', '2025-05-17 13:34:45', ''),
(32, NULL, 43, 80000.00, 'Tiền mặt', 'completed', '2025-05-22 11:53:56', ''),
(33, NULL, 42, 630000.00, 'vnpay', 'completed', '2025-05-22 11:55:26', '14972563'),
(34, NULL, 44, 170000.00, 'vnpay', 'completed', '2025-05-22 12:03:58', '14972580'),
(35, NULL, 45, 255000.00, 'vnpay', 'completed', '2025-05-22 12:05:47', '14972585'),
(36, NULL, 46, 255000.00, 'Tiền mặt', 'completed', '2025-05-22 12:14:01', ''),
(37, NULL, 47, 380000.00, 'Tiền mặt', 'completed', '2025-05-22 12:19:00', ''),
(38, NULL, 48, 180000.00, 'Tiền mặt', 'completed', '2025-05-22 12:25:37', ''),
(39, NULL, 49, 1050000.00, 'vnpay', 'completed', '2025-05-22 12:37:02', '14972627'),
(40, NULL, 51, 170000.00, 'Tiền mặt', 'completed', '2025-05-22 12:58:00', ''),
(41, NULL, 55, 1170000.00, 'Chuyển khoản', '', '2025-05-22 13:44:40', '14972745'),
(42, NULL, 56, 180000.00, 'Tiền mặt', 'completed', '2025-05-22 13:52:40', ''),
(43, NULL, 57, 380000.00, 'Chuyển khoản', '', '2025-05-22 13:55:46', '14972773');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tonkho`
--

CREATE TABLE `tonkho` (
  `matonkho` int(11) NOT NULL,
  `tentonkho` varchar(100) NOT NULL,
  `soluong` int(11) NOT NULL,
  `DonViTinh` varchar(100) NOT NULL,
  `idloaiTK` int(11) NOT NULL,
  `idncc` int(100) NOT NULL,
  `DonGia` double NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `tonkho`
--

INSERT INTO `tonkho` (`matonkho`, `tentonkho`, `soluong`, `DonViTinh`, `idloaiTK`, `idncc`, `DonGia`) VALUES
(1, 'Gạo nếp', 22, 'kg', 1, 1, 0),
(2, 'Dao nhỏ', 20, 'cái', 2, 2, 0),
(3, 'Thịt bò', 30, 'kg', 1, 1, 0),
(5, 'Cá hồi', 25, 'kg', 1, 1, 0),
(6, 'Muỗng inox', 80, 'cái', 2, 2, 0),
(7, 'Rau cải', 40, 'kg', 1, 1, 0),
(8, 'Khăn giấy', 200, 'gói', 2, 2, 0),
(9, 'Nước mắm', 60, 'lít', 1, 1, 0),
(10, 'Đĩa lớn', 50, 'cái', 2, 2, 0),
(12, 'Cà rốt', 20, 'kg', 1, 1, 0),
(13, 'Whipping Cream', 10, 'hộp', 1, 1, 0);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `vaitro`
--

CREATE TABLE `vaitro` (
  `idvaitro` int(11) NOT NULL,
  `tenvaitro` varchar(100) NOT NULL,
  `quyen` varchar(1000) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `vaitro`
--

INSERT INTO `vaitro` (`idvaitro`, `tenvaitro`, `quyen`) VALUES
(1, 'Phục vụ', 'Xem đơn hàng'),
(2, 'Thu ngân', 'Xem đơn hàng,Thêm đơn hàng,Sửa đơn hàng,Thanh toán đơn hàng,Xem hóa đơn'),
(3, 'Đầu bếp', 'Xem tồn kho, Sửa tồn kho, Xóa tồn kho, Thêm tồn kho, Xem đơn hàng'),
(4, 'Quản lý', 'Xem trang chủ,Xem nhân viên,Thêm nhân viên,Sửa nhân viên,Xóa nhân viên,Xem khách hàng,Thêm khách hàng,Sửa khách hàng,Xóa khách hàng,Xem món ăn,Thêm món ăn,Sửa món ăn,Xóa món ăn,Xem đơn hàng,Thêm đơn hàng,Sửa đơn hàng,Xóa đơn hàng,Thanh toán đơn hàng,Xem hóa đơn,Xem tồn kho,Sửa tồn kho,Xem vai trò,Thêm vai trò,Sửa vai trò'),
(22, 'Trưởng ca ', 'Xem nhân viên');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `ban`
--
ALTER TABLE `ban`
  ADD PRIMARY KEY (`idban`),
  ADD KEY `FK_Ban_KhuVuc` (`MaKV`);

--
-- Chỉ mục cho bảng `chitietdatban`
--
ALTER TABLE `chitietdatban`
  ADD PRIMARY KEY (`idChiTiet`),
  ADD KEY `madatban` (`madatban`),
  ADD KEY `idmonan` (`idmonan`);

--
-- Chỉ mục cho bảng `chitietdonhang`
--
ALTER TABLE `chitietdonhang`
  ADD PRIMARY KEY (`idCTDH`),
  ADD KEY `idDH` (`idDH`),
  ADD KEY `idmonan` (`idmonan`);

--
-- Chỉ mục cho bảng `chitiethoadon`
--
ALTER TABLE `chitiethoadon`
  ADD PRIMARY KEY (`idCTHD`),
  ADD KEY `idmonan` (`idmonan`),
  ADD KEY `idHD` (`idHD`);

--
-- Chỉ mục cho bảng `danhmuc`
--
ALTER TABLE `danhmuc`
  ADD PRIMARY KEY (`iddm`);

--
-- Chỉ mục cho bảng `datban`
--
ALTER TABLE `datban`
  ADD PRIMARY KEY (`madatban`),
  ADD KEY `makh` (`idKH`),
  ADD KEY `makh_2` (`idKH`),
  ADD KEY `datban_ibfk_4` (`idban`);

--
-- Chỉ mục cho bảng `donhang`
--
ALTER TABLE `donhang`
  ADD PRIMARY KEY (`idDH`),
  ADD KEY `idKH` (`idKH`),
  ADD KEY `idban` (`idban`);

--
-- Chỉ mục cho bảng `hoadon`
--
ALTER TABLE `hoadon`
  ADD PRIMARY KEY (`idHD`),
  ADD KEY `maKH` (`idKH`),
  ADD KEY `idKH` (`idKH`),
  ADD KEY `idmonan` (`idDH`);

--
-- Chỉ mục cho bảng `khachhang`
--
ALTER TABLE `khachhang`
  ADD PRIMARY KEY (`idKH`);

--
-- Chỉ mục cho bảng `khuvucban`
--
ALTER TABLE `khuvucban`
  ADD PRIMARY KEY (`MaKV`);

--
-- Chỉ mục cho bảng `khuyenmai`
--
ALTER TABLE `khuyenmai`
  ADD PRIMARY KEY (`MaKhuyenMai`);

--
-- Chỉ mục cho bảng `loaitonkho`
--
ALTER TABLE `loaitonkho`
  ADD PRIMARY KEY (`idloaiTK`);

--
-- Chỉ mục cho bảng `log_giaodich`
--
ALTER TABLE `log_giaodich`
  ADD PRIMARY KEY (`id`),
  ADD KEY `madatban` (`madatban`),
  ADD KEY `fk_log_giaodich_donhang` (`idDH`);

--
-- Chỉ mục cho bảng `monan`
--
ALTER TABLE `monan`
  ADD PRIMARY KEY (`idmonan`),
  ADD KEY `iddm` (`iddm`);

--
-- Chỉ mục cho bảng `nguoidung`
--
ALTER TABLE `nguoidung`
  ADD PRIMARY KEY (`idnguoidung`),
  ADD KEY `idtaikhoan` (`idtaikhoan`);

--
-- Chỉ mục cho bảng `nhacungcap`
--
ALTER TABLE `nhacungcap`
  ADD PRIMARY KEY (`idncc`);

--
-- Chỉ mục cho bảng `nhanvien`
--
ALTER TABLE `nhanvien`
  ADD PRIMARY KEY (`idnv`),
  ADD KEY `idvaitro` (`idvaitro`);

--
-- Chỉ mục cho bảng `phanhoi`
--
ALTER TABLE `phanhoi`
  ADD PRIMARY KEY (`idPhanHoi`),
  ADD KEY `idKH` (`idKH`);

--
-- Chỉ mục cho bảng `thanhtoan`
--
ALTER TABLE `thanhtoan`
  ADD PRIMARY KEY (`idThanhToan`),
  ADD KEY `madatban` (`madatban`),
  ADD KEY `fk_thanhtoan_donhang` (`idDH`);

--
-- Chỉ mục cho bảng `tonkho`
--
ALTER TABLE `tonkho`
  ADD PRIMARY KEY (`matonkho`),
  ADD KEY `idloaiTK` (`idloaiTK`),
  ADD KEY `idncc` (`idncc`);

--
-- Chỉ mục cho bảng `vaitro`
--
ALTER TABLE `vaitro`
  ADD PRIMARY KEY (`idvaitro`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `ban`
--
ALTER TABLE `ban`
  MODIFY `idban` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `chitietdatban`
--
ALTER TABLE `chitietdatban`
  MODIFY `idChiTiet` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT cho bảng `chitietdonhang`
--
ALTER TABLE `chitietdonhang`
  MODIFY `idCTDH` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=127;

--
-- AUTO_INCREMENT cho bảng `chitiethoadon`
--
ALTER TABLE `chitiethoadon`
  MODIFY `idCTHD` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT cho bảng `danhmuc`
--
ALTER TABLE `danhmuc`
  MODIFY `iddm` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `datban`
--
ALTER TABLE `datban`
  MODIFY `madatban` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT cho bảng `donhang`
--
ALTER TABLE `donhang`
  MODIFY `idDH` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT cho bảng `hoadon`
--
ALTER TABLE `hoadon`
  MODIFY `idHD` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT cho bảng `khachhang`
--
ALTER TABLE `khachhang`
  MODIFY `idKH` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT cho bảng `khuvucban`
--
ALTER TABLE `khuvucban`
  MODIFY `MaKV` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `loaitonkho`
--
ALTER TABLE `loaitonkho`
  MODIFY `idloaiTK` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `log_giaodich`
--
ALTER TABLE `log_giaodich`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT cho bảng `monan`
--
ALTER TABLE `monan`
  MODIFY `idmonan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT cho bảng `nguoidung`
--
ALTER TABLE `nguoidung`
  MODIFY `idnguoidung` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `nhacungcap`
--
ALTER TABLE `nhacungcap`
  MODIFY `idncc` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `nhanvien`
--
ALTER TABLE `nhanvien`
  MODIFY `idnv` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT cho bảng `phanhoi`
--
ALTER TABLE `phanhoi`
  MODIFY `idPhanHoi` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `thanhtoan`
--
ALTER TABLE `thanhtoan`
  MODIFY `idThanhToan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT cho bảng `tonkho`
--
ALTER TABLE `tonkho`
  MODIFY `matonkho` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT cho bảng `vaitro`
--
ALTER TABLE `vaitro`
  MODIFY `idvaitro` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `ban`
--
ALTER TABLE `ban`
  ADD CONSTRAINT `FK_Ban_KhuVuc` FOREIGN KEY (`MaKV`) REFERENCES `khuvucban` (`MaKV`);

--
-- Các ràng buộc cho bảng `chitietdatban`
--
ALTER TABLE `chitietdatban`
  ADD CONSTRAINT `chitietdatban_ibfk_1` FOREIGN KEY (`madatban`) REFERENCES `datban` (`madatban`) ON DELETE CASCADE,
  ADD CONSTRAINT `chitietdatban_ibfk_2` FOREIGN KEY (`idmonan`) REFERENCES `monan` (`idmonan`);

--
-- Các ràng buộc cho bảng `chitietdonhang`
--
ALTER TABLE `chitietdonhang`
  ADD CONSTRAINT `chitietdonhang_ibfk_1` FOREIGN KEY (`idDH`) REFERENCES `donhang` (`idDH`),
  ADD CONSTRAINT `chitietdonhang_ibfk_2` FOREIGN KEY (`idmonan`) REFERENCES `monan` (`idmonan`);

--
-- Các ràng buộc cho bảng `chitiethoadon`
--
ALTER TABLE `chitiethoadon`
  ADD CONSTRAINT `chitiethoadon_ibfk_1` FOREIGN KEY (`idHD`) REFERENCES `hoadon` (`idHD`);

--
-- Các ràng buộc cho bảng `datban`
--
ALTER TABLE `datban`
  ADD CONSTRAINT `datban_ibfk_1` FOREIGN KEY (`idKH`) REFERENCES `khachhang` (`idKH`) ON DELETE SET NULL,
  ADD CONSTRAINT `datban_ibfk_4` FOREIGN KEY (`idban`) REFERENCES `ban` (`idban`);

--
-- Các ràng buộc cho bảng `donhang`
--
ALTER TABLE `donhang`
  ADD CONSTRAINT `donhang_ibfk_1` FOREIGN KEY (`idKH`) REFERENCES `khachhang` (`idKH`),
  ADD CONSTRAINT `donhang_ibfk_4` FOREIGN KEY (`idban`) REFERENCES `ban` (`idban`);

--
-- Các ràng buộc cho bảng `hoadon`
--
ALTER TABLE `hoadon`
  ADD CONSTRAINT `hoadon_ibfk_1` FOREIGN KEY (`idKH`) REFERENCES `khachhang` (`idKH`),
  ADD CONSTRAINT `hoadon_ibfk_3` FOREIGN KEY (`idDH`) REFERENCES `donhang` (`idDH`);

--
-- Các ràng buộc cho bảng `log_giaodich`
--
ALTER TABLE `log_giaodich`
  ADD CONSTRAINT `fk_log_giaodich_donhang` FOREIGN KEY (`idDH`) REFERENCES `donhang` (`idDH`),
  ADD CONSTRAINT `log_giaodich_ibfk_1` FOREIGN KEY (`madatban`) REFERENCES `datban` (`madatban`);

--
-- Các ràng buộc cho bảng `monan`
--
ALTER TABLE `monan`
  ADD CONSTRAINT `monan_ibfk_1` FOREIGN KEY (`iddm`) REFERENCES `danhmuc` (`iddm`);

--
-- Các ràng buộc cho bảng `nhanvien`
--
ALTER TABLE `nhanvien`
  ADD CONSTRAINT `nhanvien_ibfk_1` FOREIGN KEY (`idvaitro`) REFERENCES `vaitro` (`idvaitro`);

--
-- Các ràng buộc cho bảng `phanhoi`
--
ALTER TABLE `phanhoi`
  ADD CONSTRAINT `phanhoi_ibfk_1` FOREIGN KEY (`idKH`) REFERENCES `khachhang` (`idKH`) ON DELETE SET NULL;

--
-- Các ràng buộc cho bảng `thanhtoan`
--
ALTER TABLE `thanhtoan`
  ADD CONSTRAINT `fk_thanhtoan_datban` FOREIGN KEY (`madatban`) REFERENCES `datban` (`madatban`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_thanhtoan_donhang` FOREIGN KEY (`idDH`) REFERENCES `donhang` (`idDH`);

--
-- Các ràng buộc cho bảng `tonkho`
--
ALTER TABLE `tonkho`
  ADD CONSTRAINT `tonkho_ibfk_1` FOREIGN KEY (`idloaiTK`) REFERENCES `loaitonkho` (`idloaiTK`),
  ADD CONSTRAINT `tonkho_ibfk_2` FOREIGN KEY (`idncc`) REFERENCES `nhacungcap` (`idncc`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
