-- Database + tabel + data awal To-Do List (import sekali aja)
CREATE DATABASE IF NOT EXISTS junior_web CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

USE junior_web;

-- Tabel tugas: id unik, judul wajib isi, status cuma belum/selesai
CREATE TABLE IF NOT EXISTS todos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  judul VARCHAR(255) NOT NULL,
  status ENUM('belum','selesai') NOT NULL DEFAULT 'belum',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2 tugas contoh biar halaman gak kosong pas pertama dibuka
INSERT INTO todos (judul, status) VALUES
  ('Belajar HTML/CSS', 'belum'),
  ('Kerjakan tugas UX', 'belum');
