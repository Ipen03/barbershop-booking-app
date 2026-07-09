-- Hapus database jika sudah ada sebelumnya
DROP DATABASE IF EXISTS barbershop_db;

-- Buat database baru
CREATE DATABASE barbershop_db;
USE barbershop_db;

-- 1. TABEL USERS (Pelanggan)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. TABEL SERVICES (Layanan Barbershop)
CREATE TABLE services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price INT NOT NULL,
    duration INT NOT NULL, -- dalam satuan menit
    description TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. TABEL BARBERS (Tukang Cukur)
CREATE TABLE barbers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    rating DECIMAL(2,1) DEFAULT 5.0,
    photo VARCHAR(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. TABEL BOOKINGS (Pemesanan/Reservasi)
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    service_id INT NOT NULL,
    barber_id INT NOT NULL,
    booking_date DATE NOT NULL,
    booking_time VARCHAR(10) NOT NULL, -- Contoh: "09:00", "13:00"
    status VARCHAR(50) DEFAULT 'Pending', -- 'Pending', 'Confirmed', 'Completed', 'Cancelled'
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    FOREIGN KEY (barber_id) REFERENCES barbers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==========================================
-- SEED DATA AWAL (DUMMY DATA)
-- ==========================================

-- Data Layanan
INSERT INTO services (name, price, duration, description) VALUES
('Classic Haircut', 50000, 30, 'Potong rambut dengan model klasik atau modern, termasuk cuci rambut dan penataan menggunakan pomade.'),
('Premium Shaving', 30000, 20, 'Pencukuran kumis & jenggot bersih menggunakan pisau cukur steril dan terapi handuk hangat.'),
('Hair Coloring', 150000, 60, 'Pewarnaan rambut hitam pekat atau warna tren terbaru (blonde, ash grey, dll) menggunakan cat rambut premium.'),
('Creambath & Massage', 45000, 40, 'Perawatan kulit kepala dengan krim ginseng/lidah buaya untuk menguatkan akar rambut disertai pijat pundak.');

-- Data Barber
INSERT INTO barbers (name, rating, photo) VALUES
('Rian Wijaya', 4.9, 'rian.jpg'),
('Budi Santoso', 4.8, 'budi.jpg'),
('Fikri Alamsyah', 4.7, 'fikri.jpg');
