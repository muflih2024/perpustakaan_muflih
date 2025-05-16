
USE perpustakaan_muflih;

-- Only create the table if it doesn't exist (won't drop existing data)
-- DROP TABLE IF EXISTS buku;  -- commented out to preserve existing data

-- Create the books table structure only if it doesn't exist yet
CREATE TABLE IF NOT EXISTS buku (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(255) NOT NULL,
    pengarang VARCHAR(100) NOT NULL,
    penerbit VARCHAR(100) NOT NULL,
    tahun_terbit INT NOT NULL,
    genre VARCHAR(50) NOT NULL,
    stok INT NOT NULL DEFAULT 0
);

-- Insert sample books data
INSERT INTO buku (judul, pengarang, penerbit, tahun_terbit, genre, stok) VALUES
('Bumi Manusia', 'Pramoedya Ananta Toer', 'Hasta Mitra', 1980, 'Fiksi Sejarah', 8),
('Filosofi Kopi', 'Dee Lestari', 'Truedee Books', 2006, 'Kumpulan Cerpen', 12),
('Negeri 5 Menara', 'Ahmad Fuadi', 'Gramedia Pustaka Utama', 2009, 'Novel', 15),
('Sang Pemimpi', 'Andrea Hirata', 'Bentang Pustaka', 2006, 'Novel', 7),
('Perahu Kertas', 'Dee Lestari', 'Bentang Pustaka', 2009, 'Novel', 9),
('Ayat-ayat Cinta', 'Habiburrahman El Shirazy', 'Republika', 2004, 'Novel Islami', 6),
('Supernova: Ksatria, Putri, dan Bintang Jatuh', 'Dee Lestari', 'Truedee Books', 2001, 'Novel', 5),
('Pulang', 'Tere Liye', 'Republika', 2015, 'Novel', 11),
('Cantik itu Luka', 'Eka Kurniawan', 'Gramedia Pustaka Utama', 2002, 'Novel', 8),
('Tenggelamnya Kapal Van Der Wijck', 'Hamka', 'Balai Pustaka', 1938, 'Novel', 4),
('Manusia Setengah Salmon', 'Raditya Dika', 'Gagas Media', 2011, 'Humor', 14),
('Indonesia Etc.', 'Elizabeth Pisani', 'Godown', 2014, 'Non-fiksi', 3),
('Critical Eleven', 'Ika Natassa', 'Gramedia Pustaka Utama', 2015, 'Novel', 7),
('Saman', 'Ayu Utami', 'Kepustakaan Populer Gramedia', 1998, 'Novel', 6);

-- Add some educational books
INSERT INTO buku (judul, pengarang, penerbit, tahun_terbit, genre, stok) VALUES
('Matematika untuk SMA Kelas X', 'Tim Penyusun Kemendikbud', 'Kemendikbud', 2021, 'Buku Pelajaran', 20),
('Fisika SMA Kelas XI', 'Marthen Kanginan', 'Erlangga', 2020, 'Buku Pelajaran', 15),
('Bahasa Indonesia SMA Kelas XII', 'Tim Penyusun', 'Yudhistira', 2019, 'Buku Pelajaran', 18),
('Biologi Campbell', 'Jane B. Reece', 'Erlangga', 2017, 'Buku Pelajaran', 8);

-- Add some computer science books
INSERT INTO buku (judul, pengarang, penerbit, tahun_terbit, genre, stok) VALUES
('Pemrograman Web dengan PHP dan MySQL', 'Betha Sidik', 'Informatika', 2019, 'Komputer', 12),
('Machine Learning with Python', 'Sebastian Raschka', 'Packt Publishing', 2019, 'Komputer', 5),
('Algoritma dan Pemrograman', 'Rinaldi Munir', 'Informatika', 2016, 'Komputer', 9),
('Database Management Systems', 'Raghu Ramakrishnan', 'McGraw-Hill', 2015, 'Komputer', 6),
('Software Engineering: A Practitioner''s Approach', 'Roger S. Pressman', 'McGraw-Hill', 2014, 'Komputer', 4);