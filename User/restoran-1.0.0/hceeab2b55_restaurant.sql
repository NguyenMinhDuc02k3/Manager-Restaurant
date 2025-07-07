-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th5 23, 2025 lúc 09:31 AM
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
-- Cơ sở dữ liệu: `hceeab2b55_restaurant`
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Đang đổ dữ liệu cho bảng `ban`
--

INSERT INTO `ban` (`idban`, `SoBan`, `soluongKH`, `TrangThai`, `MaKV`) VALUES
(1, 'B01', 0, 'Trống', 4),
(2, 'B02', 4, 'ĐãĐặt', 3),
(3, 'B03', 2, 'ĐangSửDụng', 1),
(4, 'B04', 0, 'Trống', 2),
(5, 'B05', 6, 'ĐãĐặt', 4),
(6, 'B06', 4, 'Trống', 1),
(7, 'B07', 6, 'Trống', 1),
(8, 'B08', 8, 'Trống', 1),
(9, 'B09', 4, 'Trống', 2),
(10, 'B10', 6, 'Trống', 2),
(11, 'B11', 8, 'Trống', 2),
(12, 'B12', 4, 'Trống', 3),
(13, 'B13', 6, 'Trống', 3),
(14, 'B14', 8, 'Trống', 3),
(15, 'B15', 8, 'Trống', 4),
(16, 'B16', 10, 'Trống', 4),
(17, 'B17', 12, 'Trống', 4),
(18, 'B18', 15, 'Trống', 4),
(19, 'B19', 20, 'Trống', 4),
(20, 'B20', 25, 'Trống', 4),
(21, 'B21', 4, 'Trống', 1),
(22, 'B22', 6, 'Trống', 1),
(23, 'B23', 8, 'Trống', 1),
(24, 'B24', 4, 'Trống', 1),
(25, 'B25', 6, 'Trống', 1),
(26, 'B26', 8, 'Trống', 1),
(27, 'B27', 4, 'Trống', 1),
(28, 'B28', 6, 'Trống', 1),
(29, 'B29', 8, 'Trống', 1),
(30, 'B30', 4, 'Trống', 2),
(31, 'B31', 6, 'Trống', 2),
(32, 'B32', 8, 'Trống', 2),
(33, 'B33', 4, 'Trống', 2),
(34, 'B34', 6, 'Trống', 2),
(35, 'B35', 8, 'Trống', 2),
(36, 'B36', 4, 'Trống', 2),
(37, 'B37', 6, 'Trống', 2),
(38, 'B38', 8, 'Trống', 2),
(39, 'B39', 4, 'Trống', 3),
(40, 'B40', 6, 'Trống', 3),
(41, 'B41', 8, 'Trống', 3),
(42, 'B42', 4, 'Trống', 3),
(43, 'B43', 6, 'Trống', 3),
(44, 'B44', 8, 'Trống', 3),
(45, 'B45', 4, 'Trống', 3),
(46, 'B46', 6, 'Trống', 3),
(47, 'B47', 8, 'Trống', 3);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chatbot_qa`
--

CREATE TABLE `chatbot_qa` (
  `id` int(11) NOT NULL,
  `question_pattern` varchar(255) NOT NULL,
  `answer` text NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `chatbot_qa`
--

INSERT INTO `chatbot_qa` (`id`, `question_pattern`, `answer`, `category`, `created_at`) VALUES
(7, 'menu|thực đơn|món ăn', 'SELECT name, price, description FROM monan WHERE status = 1', 'menu', '2025-05-23 07:20:59'),
(8, 'đặt bàn|book|reservation', 'SELECT SoBan, soluongKH, TrangThai FROM ban WHERE TrangThai = \"Trống\"', 'booking', '2025-05-23 07:20:59'),
(9, 'khuyến mãi|ưu đãi|promotion', 'SELECT TenKM, NoiDung, NgayBD, NgayKT FROM khuyenmai WHERE NOW() BETWEEN NgayBD AND NgayKT', 'promotion', '2025-05-23 07:20:59'),
(10, 'giờ mở cửa|opening', 'Nhà hàng chúng tôi mở cửa từ 8:00 - 22:00 các ngày trong tuần', 'info', '2025-05-23 07:20:59'),
(11, 'liên hệ|contact', 'Bạn có thể liên hệ với chúng tôi qua số điện thoại: 0123456789 hoặc email: info@restaurant.com', 'info', '2025-05-23 07:20:59');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chat_history`
--

CREATE TABLE `chat_history` (
  `id` int(11) NOT NULL,
  `session_id` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `sender` enum('user','bot') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Đang đổ dữ liệu cho bảng `chitietdatban`
--

INSERT INTO `chitietdatban` (`idChiTiet`, `madatban`, `idmonan`, `SoLuong`, `DonGia`) VALUES
(45, 26, 1, 1, 80000.00),
(46, 26, 2, 1, 75000.00),
(47, 26, 3, 1, 90000.00),
(48, 27, 1, 2, 80000.00),
(49, 27, 2, 1, 75000.00),
(50, 27, 3, 1, 90000.00),
(51, 27, 4, 1, 95000.00),
(52, 28, 1, 1, 80000.00),
(53, 28, 2, 1, 75000.00),
(54, 28, 3, 1, 90000.00),
(55, 29, 1, 1, 80000.00),
(56, 29, 2, 1, 75000.00),
(57, 29, 3, 2, 90000.00),
(58, 30, 1, 1, 80000.00),
(59, 30, 2, 1, 75000.00),
(60, 30, 3, 1, 90000.00);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chitietdonhang`
--

CREATE TABLE `chitietdonhang` (
  `idCTDH` int(11) NOT NULL,
  `idDH` int(11) DEFAULT NULL,
  `idmonan` int(11) DEFAULT NULL,
  `SoLuong` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Đang đổ dữ liệu cho bảng `chitietdonhang`
--

INSERT INTO `chitietdonhang` (`idCTDH`, `idDH`, `idmonan`, `SoLuong`) VALUES
(100, 51, 4, 1),
(101, 51, 6, 1),
(102, 51, 2, 1),
(103, 52, 2, 1),
(104, 52, 4, 1),
(105, 53, 2, 1),
(106, 53, 4, 1),
(107, 54, 2, 1),
(108, 54, 4, 1),
(109, 54, 9, 1),
(110, 55, 2, 1),
(111, 55, 4, 1),
(112, 55, 6, 1),
(113, 56, 2, 1),
(114, 56, 4, 1),
(115, 56, 6, 1),
(116, 57, 4, 1),
(117, 57, 6, 1),
(118, 58, 1, 1),
(119, 58, 3, 1),
(120, 58, 5, 1),
(121, 59, 2, 1),
(122, 59, 4, 1),
(123, 60, 2, 1),
(124, 60, 4, 1),
(125, 61, 4, 1),
(126, 61, 6, 1),
(127, 62, 1, 1),
(128, 62, 2, 1),
(129, 62, 3, 1),
(130, 63, 1, 2),
(131, 63, 2, 1),
(132, 63, 3, 1),
(133, 63, 4, 1),
(134, 64, 2, 1),
(135, 64, 4, 1),
(136, 65, 2, 1),
(137, 65, 4, 1),
(138, 65, 6, 1),
(139, 66, 1, 1),
(140, 66, 2, 1),
(141, 66, 3, 2),
(142, 67, 1, 1),
(143, 67, 2, 1),
(144, 67, 3, 1);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Đang đổ dữ liệu cho bảng `chitiethoadon`
--

INSERT INTO `chitiethoadon` (`idCTHD`, `idHD`, `idmonan`, `soluong`, `thanhtien`) VALUES
(72, 47, 4, 1, 95000),
(73, 47, 6, 1, 85000),
(74, 47, 2, 1, 75000),
(75, 48, 2, 1, 75000),
(76, 48, 4, 1, 95000),
(77, 49, 2, 1, 75000),
(78, 49, 4, 1, 95000),
(79, 50, 2, 1, 75000),
(80, 50, 4, 1, 95000),
(81, 50, 9, 1, 200000),
(82, 51, 2, 1, 75000),
(83, 51, 4, 1, 95000),
(84, 51, 6, 1, 85000),
(85, 52, 2, 1, 75000),
(86, 52, 4, 1, 95000),
(87, 52, 6, 1, 85000),
(88, 53, 4, 1, 95000),
(89, 53, 6, 1, 85000),
(90, 54, 1, 1, 80000),
(91, 54, 3, 1, 90000),
(92, 54, 5, 1, 100000),
(93, 55, 2, 1, 75000),
(94, 55, 4, 1, 95000),
(95, 56, 2, 1, 75000),
(96, 56, 4, 1, 95000),
(97, 57, 2, 1, 75000),
(98, 57, 4, 1, 95000),
(100, 58, 1, 2, 160000),
(101, 58, 2, 1, 75000),
(102, 58, 3, 1, 90000),
(103, 58, 4, 1, 95000),
(107, 62, 1, 1, 80000),
(108, 62, 2, 1, 75000),
(109, 62, 3, 2, 180000);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `danhmuc`
--

CREATE TABLE `danhmuc` (
  `iddm` int(11) NOT NULL,
  `tendanhmuc` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Đang đổ dữ liệu cho bảng `datban`
--

INSERT INTO `datban` (`madatban`, `idKH`, `idban`, `NgayDatBan`, `SoLuongKhach`, `TongTien`, `TrangThai`, `tenKH`, `email`, `sodienthoai`, `NgayTao`) VALUES
(26, NULL, 11, '2025-05-24 12:20:00', 1, 245000.00, 'confirmed', 'Nguyễn Minh Đức', 'tn6888295@gmail.com', '0928449664', '2025-05-22 22:56:44'),
(27, NULL, 39, '2025-05-23 12:00:00', 3, 420000.00, 'confirmed', 'Nguyễn Minh Đức', 'tn6888295@gmail.com', '0928449664', '2025-05-22 23:21:21'),
(28, NULL, 30, '2025-05-23 12:00:00', 1, 245000.00, 'confirmed', 'Nguyễn Minh Đức', 'tn6888295@gmail.com', '0928449664', '2025-05-22 23:38:44'),
(29, NULL, 39, '2025-05-24 12:00:00', 3, 335000.00, 'confirmed', 'Nguyễn Minh Đức', 'tn6888295@gmail.com', '0928449664', '2025-05-22 23:44:00'),
(30, NULL, 40, '2025-05-23 12:00:00', 3, 245000.00, 'confirmed', 'Lê Hoàng Gia Hi', 'giahi0000@gmail.com', '0796123823', '2025-05-22 23:45:40');

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
  `MaDonHang` varchar(50) DEFAULT NULL,
  `SoHoaDon` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Đang đổ dữ liệu cho bảng `donhang`
--

INSERT INTO `donhang` (`idDH`, `idKH`, `idban`, `NgayDatHang`, `TongTien`, `TrangThai`, `MaDonHang`, `SoHoaDon`) VALUES
(51, 6, 2, '2025-05-20 16:12:08', 255000.00, 'Đã thanh toán', '250520-161159', '161159'),
(52, 6, 3, '2025-05-20 16:19:02', 170000.00, 'Đã thanh toán', '250520-161855', '161855'),
(53, 6, 3, '2025-05-20 16:23:56', 170000.00, 'Đã thanh toán', '250520-162347', '162347'),
(54, 6, 2, '2025-05-20 16:34:37', 370000.00, 'Đã thanh toán', '250520-163428', '163428'),
(55, 6, 2, '2025-05-20 16:37:54', 255000.00, 'Đã thanh toán', '250520-163745', '163745'),
(56, 6, 4, '2025-05-20 16:39:23', 255000.00, 'Đã thanh toán', '250520-163916', '163916'),
(57, 6, 2, '2025-05-20 16:46:46', 180000.00, 'Đã thanh toán', '250520-164637', '164637'),
(58, 6, 3, '2025-05-20 18:06:01', 270000.00, 'Đã thanh toán', '250520-180553', '180553'),
(59, 6, 2, '2025-05-20 18:08:55', 170000.00, 'Đã thanh toán', '250520-180833', '180833'),
(60, 6, 2, '2025-05-20 18:13:32', 170000.00, 'Đã thanh toán', '250520-181319', '181319'),
(61, 6, 2, '2025-05-20 18:28:58', 180000.00, 'Đã thanh toán', '220525-183236', '182850'),
(62, 6, 11, '2025-05-24 12:20:00', 245000.00, 'Đã thanh toán', '220525-183603', '225644'),
(63, 6, 39, '2025-05-23 12:00:00', 420000.00, 'Đã thanh toán', '250522-232241-27', '232241'),
(64, 6, 2, '2025-05-22 23:27:44', 170000.00, 'Đã thanh toán', '220525-232744', '232744'),
(65, 6, 4, '2025-05-22 23:36:50', 255000.00, 'Đã thanh toán', '220525-183801', '233650'),
(66, 6, 39, '2025-05-24 12:00:00', 335000.00, 'Đã thanh toán', '250522-234400-29', '234400'),
(67, 20, 40, '2025-05-23 12:00:00', 245000.00, 'Đã thanh toán', '220525-184705', '234541');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Đang đổ dữ liệu cho bảng `hoadon`
--

INSERT INTO `hoadon` (`idHD`, `idKH`, `idDH`, `Ngay`, `hinhthucthanhtoan`, `TongTien`) VALUES
(47, 6, 51, '2025-05-20 16:13:21', 'Chuyển khoản', 255000),
(48, 6, 52, '2025-05-20 16:20:34', 'Chuyển khoản', 170000),
(49, 6, 53, '2025-05-20 16:24:17', 'Tiền mặt', 170000),
(50, 6, 54, '2025-05-20 16:35:36', 'Chuyển khoản', 370000),
(51, 6, 55, '2025-05-20 16:38:44', 'Chuyển khoản', 255000),
(52, 6, 56, '2025-05-20 16:40:45', 'Chuyển khoản', 255000),
(53, 6, 57, '2025-05-20 16:47:42', 'Chuyển khoản', 180000),
(54, 6, 58, '2025-05-20 18:06:59', 'Chuyển khoản', 270000),
(55, 6, 59, '2025-05-20 18:10:43', 'Chuyển khoản', 170000),
(56, 6, 60, '2025-05-20 18:14:40', 'Chuyển khoản', 170000),
(57, 6, 64, '2025-05-22 23:29:46', 'Tiền mặt', 170000),
(58, 6, 63, '2025-05-22 23:30:10', 'Tiền mặt', 420000),
(59, 6, 61, '2025-05-22 23:32:36', 'Chuyển khoản', 180000),
(60, 6, 62, '2025-05-22 23:36:03', 'Chuyển khoản', 245000),
(61, 6, 65, '2025-05-22 23:38:01', 'Chuyển khoản', 255000),
(62, 6, 66, '2025-05-22 23:45:04', 'Tiền mặt', 335000),
(63, 20, 67, '2025-05-22 23:47:05', 'Chuyển khoản', 245000);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

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
(20, 'Lê Hoàng Gia Hi', '0796123823', 'giahi0000@gmail.com', '2003-01-05', 'Nữ');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `khuvucban`
--

CREATE TABLE `khuvucban` (
  `MaKV` int(11) NOT NULL,
  `TenKV` varchar(100) NOT NULL,
  `MoTa` text DEFAULT NULL,
  `TrangThai` varchar(20) DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

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
  `MoTa` varchar(255) DEFAULT NULL,
  `GiaTri` decimal(5,2) NOT NULL,
  `LoaiGiam` enum('percent','fixed') NOT NULL,
  `NgayBatDau` date NOT NULL,
  `NgayKetThuc` date NOT NULL,
  `TrangThai` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Đang đổ dữ liệu cho bảng `log_giaodich`
--

INSERT INTO `log_giaodich` (`id`, `idDH`, `madatban`, `MaGiaoDich`, `SoTien`, `TrangThai`, `ThoiGian`) VALUES
(18, 51, NULL, '14968226', 255000.00, 'completed', '2025-05-20 16:13:21'),
(19, 52, NULL, '14968263', 170000.00, 'completed', '2025-05-20 16:20:34'),
(20, 54, NULL, '14968332', 370000.00, 'completed', '2025-05-20 16:35:36'),
(21, 55, NULL, '14968352', 255000.00, 'completed', '2025-05-20 16:38:44'),
(22, 56, NULL, '14968363', 255000.00, 'completed', '2025-05-20 16:40:45'),
(23, 57, NULL, '14968396', 180000.00, 'completed', '2025-05-20 16:47:42'),
(24, 58, NULL, '14968651', 270000.00, 'completed', '2025-05-20 18:06:59'),
(25, 59, NULL, '14968656', 170000.00, 'completed', '2025-05-20 18:10:43'),
(26, 60, NULL, '14968657', 170000.00, 'completed', '2025-05-20 18:14:40');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nhacungcap`
--

CREATE TABLE `nhacungcap` (
  `idncc` int(11) NOT NULL,
  `tennhacungcap` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Đang đổ dữ liệu cho bảng `nhanvien`
--

INSERT INTO `nhanvien` (`idnv`, `HinhAnh`, `HoTen`, `GioiTinh`, `ChucVu`, `SoDienThoai`, `Email`, `password`, `DiaChi`, `Luong`, `idvaitro`) VALUES
(1, 'profile2.jpg', 'Nguyễn Văn Phúc', '', 'Phục vụ', '0912345678', 'phucnv@example.com', '1234', '123 Lê Lợi, Q.1, TP.HCM', 7000000.00, 1),
(2, 'team-1.jpg', 'Trần Thị Hòa', 'Nữ', 'Đầu bếp', '0987654321', 'hoatt@example.com', '123', '45 Hai Bà Trưng, Q.3, TP.HCM', 10000000.00, 3),
(3, 'testimonial-3.jpg', 'Lê Văn Khánh', 'Nam', 'Thu ngân', '0909123456', 'khanhlv@example.com', '123', '87 Nguyễn Trãi, Q.5, TP.HCM', 8000000.00, 2),
(4, 'profile2.jpg', 'Phạm Thị Mai', '', 'Phục vụ', '0977123456', 'maipt@example.com', '', '16 Trần Hưng Đạo, Q.1, TP.HCM', 7000000.00, 1),
(7, 'about-3.jpg', 'Lê Hoàng Gia Hi', 'Nữ', NULL, '0796123823', 'giahi@gmail.com', '123', 'aksjfhhasd', 10000000.00, 4),
(10, 'jm_denis.jpg', 'Huỳnh Hồ Hoài Nam', 'Nam', 'Phục vụ', '0235478965', 'namhuynh@gmail.com', '$2y$10$2SW8xCsZCt5XbK7GRnr9leDABSyfHbkduFnPP970V2mOJePn3kH/2', '123afsdfdsgff', 5000000.00, 2),
(11, 'testimonial-4.jpg', 'Nguyễn Thị Hoàng Nga', 'Nữ', NULL, '0935713677', 'hoangnga@gmail.com', '$2y$10$ViAkURtJju4dB.ArSKzIr.zcNJnPYVNDOQJDKM2Lz9V/8WZ.WCdBC', 'hjgfjdjkfa', 5000000.00, 2),
(12, 'about-1.jpg', 'Nguyễn Uyển Quyên', 'Nữ', NULL, 'sdgsdg', 'quyen@gmail.com', '123', 'sddgsdg', 12445.00, 18),
(13, 'team-2.jpg', 'Hoàng Văn Minh', 'Nam', 'Đầu bếp', '0933123456', 'minhhv@example.com', '', '78 Nguyễn Huệ, Q.1, TP.HCM', 12000000.00, 3),
(14, 'team-3.jpg', 'Nguyễn Thị Lan', 'Nữ', 'Đầu bếp', '0944123456', 'lannt@example.com', '', '56 Lê Duẩn, Q.1, TP.HCM', 12000000.00, 3),
(15, 'team-4.jpg', 'Trần Văn Sơn', 'Nam', 'Đầu bếp', '0955123456', 'sontv@example.com', '123', '34 Đồng Khởi, Q.1, TP.HCM', 12000000.00, 3),
(16, 'arashmil.jpg', 'Thành Đạt', 'Nam', NULL, '1234567892', 'dat@gmail.com', '$2y$10$IscBC9a94U7znmxX8CYXfeBg9eecGVQe8SnxUFV/aDjF3BYDvfHL2', '153 le van tho', 1.00, 3);

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Đang đổ dữ liệu cho bảng `phanhoi`
--

INSERT INTO `phanhoi` (`idPhanHoi`, `idKH`, `HoTen`, `Email`, `SoDienThoai`, `ChuDe`, `NoiDung`, `NgayGui`, `TrangThai`) VALUES
(1, NULL, 'Nguyễn Văn A', 'vana@gmail.com', '0235467584', 'Dịch vụ', 'Nhà hàng có không gian đẹp, nhân viên phục vụ nhiệt tình. Món ăn ngon và giá cả hợp lý.', '2025-05-19 16:47:03', 'Chưa đọc'),
(2, NULL, 'Trần Thị B', 'tere@gmail.com', '0765849302', 'Món ăn', 'Các món ăn được chế biến cẩn thận, hương vị thơm ngon. Đặc biệt là món bò lúc lắc rất tuyệt vời.', '2025-05-19 16:47:03', 'Chưa đọc'),
(3, NULL, 'Lê Văn C', 'kh-c@gmail.com', '0684975432', 'Không gian', 'Không gian nhà hàng rộng rãi, thoáng mát. Phù hợp cho các buổi họp mặt gia đình và bạn bè.', '2025-05-19 16:47:03', 'Chưa đọc'),
(4, NULL, 'Phạm Thị D', 'phamd@gmail.com', '0285467839', 'Đánh giá chung', 'Nhà hàng có view đẹp, món ăn ngon, giá cả phải chăng. Sẽ quay lại vào lần sau.', '2025-05-19 16:47:03', 'Chưa đọc');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Đang đổ dữ liệu cho bảng `thanhtoan`
--

INSERT INTO `thanhtoan` (`idThanhToan`, `madatban`, `idDH`, `SoTien`, `PhuongThuc`, `TrangThai`, `NgayThanhToan`, `MaGiaoDich`) VALUES
(38, NULL, 51, 255000.00, 'vnpay', 'completed', '2025-05-20 16:13:21', '14968226'),
(39, NULL, 52, 170000.00, 'vnpay', 'completed', '2025-05-20 16:20:34', '14968263'),
(40, NULL, 53, 170000.00, 'Tiền mặt', 'completed', '2025-05-20 16:24:17', ''),
(41, NULL, 54, 370000.00, 'vnpay', 'completed', '2025-05-20 16:35:36', '14968332'),
(42, NULL, 55, 255000.00, 'vnpay', 'completed', '2025-05-20 16:38:44', '14968352'),
(43, NULL, 56, 255000.00, 'vnpay', 'completed', '2025-05-20 16:40:45', '14968363'),
(44, NULL, 57, 180000.00, 'vnpay', 'completed', '2025-05-20 16:47:42', '14968396'),
(45, NULL, 58, 270000.00, 'vnpay', 'completed', '2025-05-20 18:06:59', '14968651'),
(46, NULL, 59, 170000.00, 'vnpay', 'completed', '2025-05-20 18:10:43', '14968656'),
(47, NULL, 60, 170000.00, 'vnpay', 'completed', '2025-05-20 18:14:40', '14968657'),
(48, NULL, 61, 180000.00, 'Chuyển khoản', '', '2025-05-22 23:32:36', '14974064'),
(49, NULL, 62, 245000.00, 'Chuyển khoản', '', '2025-05-22 23:36:03', '14974070'),
(50, NULL, 65, 255000.00, 'Chuyển khoản', '', '2025-05-22 23:38:01', '14974074'),
(51, NULL, 67, 245000.00, 'Chuyển khoản', '', '2025-05-22 23:47:05', '14974088');

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Đang đổ dữ liệu cho bảng `vaitro`
--

INSERT INTO `vaitro` (`idvaitro`, `tenvaitro`, `quyen`) VALUES
(1, 'Phục vụ', 'Xem đơn hàng'),
(2, 'Thu ngân', 'Xem đơn hàng, Sửa đơn hàng, Xem hóa đơn, Xuất hóa đơn, Thêm đơn hàng, Thanh toán'),
(3, 'Đầu bếp', 'Xem tồn kho, Sửa tồn kho, Xóa tồn kho, Thêm tồn kho'),
(4, 'Quản lý', 'Xem nhân viên,Thêm nhân viên,Sửa nhân viên,Xóa nhân viên,Xem khách hàng,Thêm khách hàng,Sửa khách hàng,Xóa khách hàng,Xem món ăn,Thêm món ăn,Sửa món ăn,Xóa món ăn,Xem đơn hàng,Thêm đơn hàng,Sửa đơn hàng,Xóa đơn hàng,Thanh toán đơn hàng,Xem hóa đơn,Xem tồn kho,Xem vai trò, Xem trang chủ'),
(18, 'Trưởng ca 1', 'Xem nhân viên');

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
-- Chỉ mục cho bảng `chatbot_qa`
--
ALTER TABLE `chatbot_qa`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `chat_history`
--
ALTER TABLE `chat_history`
  ADD PRIMARY KEY (`id`);

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
  ADD UNIQUE KEY `MaDonHang` (`MaDonHang`),
  ADD UNIQUE KEY `MaDonHang_2` (`MaDonHang`),
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
  MODIFY `idban` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT cho bảng `chatbot_qa`
--
ALTER TABLE `chatbot_qa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT cho bảng `chat_history`
--
ALTER TABLE `chat_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `chitietdatban`
--
ALTER TABLE `chitietdatban`
  MODIFY `idChiTiet` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT cho bảng `chitietdonhang`
--
ALTER TABLE `chitietdonhang`
  MODIFY `idCTDH` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=145;

--
-- AUTO_INCREMENT cho bảng `chitiethoadon`
--
ALTER TABLE `chitiethoadon`
  MODIFY `idCTHD` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=110;

--
-- AUTO_INCREMENT cho bảng `danhmuc`
--
ALTER TABLE `danhmuc`
  MODIFY `iddm` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT cho bảng `datban`
--
ALTER TABLE `datban`
  MODIFY `madatban` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT cho bảng `donhang`
--
ALTER TABLE `donhang`
  MODIFY `idDH` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT cho bảng `hoadon`
--
ALTER TABLE `hoadon`
  MODIFY `idHD` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT cho bảng `khachhang`
--
ALTER TABLE `khachhang`
  MODIFY `idKH` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

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
  MODIFY `idnv` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT cho bảng `phanhoi`
--
ALTER TABLE `phanhoi`
  MODIFY `idPhanHoi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `thanhtoan`
--
ALTER TABLE `thanhtoan`
  MODIFY `idThanhToan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT cho bảng `tonkho`
--
ALTER TABLE `tonkho`
  MODIFY `matonkho` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT cho bảng `vaitro`
--
ALTER TABLE `vaitro`
  MODIFY `idvaitro` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

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
