-- Agenda Personal de Contactos V2.0 - instalación nueva (tablas)
-- En hosting: crear primero la base de datos desde el panel y seleccionar esa base en phpMyAdmin.
-- NO crea usuarios reales ni almacena contraseñas de prueba.
CREATE TABLE IF NOT EXISTS usuarios (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  correo VARCHAR(190) NOT NULL,
  password VARCHAR(255) NOT NULL,
  fecha_registro TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT uq_usuarios_correo UNIQUE (correo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS categorias (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(40) NOT NULL,
  CONSTRAINT uq_categorias_nombre UNIQUE (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO categorias (id, nombre) VALUES
 (1, 'Familia'), (2, 'Amigos'), (3, 'Trabajo'), (4, 'Universidad'), (5, 'Otro')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

CREATE TABLE IF NOT EXISTS contactos (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT UNSIGNED NOT NULL,
  nombre VARCHAR(100) NOT NULL,
  telefono VARCHAR(30) NOT NULL,
  correo VARCHAR(190) NOT NULL,
  ciudad VARCHAR(100) NOT NULL,
  id_categoria INT UNSIGNED NOT NULL,
  favorito TINYINT(1) NOT NULL DEFAULT 0,
  fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_contactos_usuario_nombre (id_usuario, nombre),
  INDEX idx_contactos_usuario_ciudad (id_usuario, ciudad),
  INDEX idx_contactos_usuario_favorito (id_usuario, favorito),
  INDEX idx_contactos_usuario_fecha (id_usuario, fecha_creacion),
  INDEX idx_contactos_categoria (id_categoria),
  CONSTRAINT fk_contactos_usuario FOREIGN KEY (id_usuario)
    REFERENCES usuarios(id) ON DELETE CASCADE,
  CONSTRAINT fk_contactos_categoria FOREIGN KEY (id_categoria)
    REFERENCES categorias(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
