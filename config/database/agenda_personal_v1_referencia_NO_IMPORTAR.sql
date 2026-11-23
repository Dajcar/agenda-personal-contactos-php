-- Agenda Personal de Contactos con Autenticación en PHP
-- Compatible con MySQL 8 y MariaDB 10.6+; ejecutar en phpMyAdmin o mysql.
-- No incluye credenciales ni datos personales de demostración.
CREATE DATABASE IF NOT EXISTS agenda_personal
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE agenda_personal;

CREATE TABLE IF NOT EXISTS usuarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(190) NOT NULL,
    password VARCHAR(255) NOT NULL,
    fecha_registro TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT uq_usuarios_correo UNIQUE (correo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS contactos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT UNSIGNED NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    telefono VARCHAR(30) NOT NULL,
    correo VARCHAR(190) NOT NULL,
    ciudad VARCHAR(100) NOT NULL,
    INDEX idx_contactos_usuario_nombre (id_usuario, nombre),
    CONSTRAINT fk_contactos_usuario FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
