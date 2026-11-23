-- Selecciona la BD del proyecto anterior; ver migracion_v1_a_v2_hosting.sql.
USE agenda_personal;

-- Migración EN UNA SOLA OCASIÓN desde la base de datos de V1 (NO ejecutar en V2 instalada).
-- Haga un respaldo de la BD antes de ejecutar. Seleccione la BD existente en phpMyAdmin.
-- DDL de MySQL hace COMMIT implícito y no permite revertir con ROLLBACK.
CREATE TABLE IF NOT EXISTS categorias (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(40) NOT NULL,
  CONSTRAINT uq_categorias_nombre UNIQUE (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO categorias (id, nombre) VALUES
 (1, 'Familia'), (2, 'Amigos'), (3, 'Trabajo'), (4, 'Universidad'), (5, 'Otro')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

ALTER TABLE contactos
  ADD COLUMN id_categoria INT UNSIGNED NULL AFTER ciudad,
  ADD COLUMN favorito TINYINT(1) NOT NULL DEFAULT 0 AFTER id_categoria,
  ADD COLUMN fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER favorito;

-- Los contactos anteriores se clasifican como "Otro"; su fecha se fija al momento de migrar.
UPDATE contactos SET id_categoria = 5 WHERE id_categoria IS NULL;
ALTER TABLE contactos MODIFY COLUMN id_categoria INT UNSIGNED NOT NULL;
ALTER TABLE contactos
  ADD INDEX idx_contactos_usuario_ciudad (id_usuario, ciudad),
  ADD INDEX idx_contactos_usuario_favorito (id_usuario, favorito),
  ADD INDEX idx_contactos_usuario_fecha (id_usuario, fecha_creacion),
  ADD INDEX idx_contactos_categoria (id_categoria),
  ADD CONSTRAINT fk_contactos_categoria FOREIGN KEY (id_categoria)
    REFERENCES categorias(id) ON DELETE RESTRICT;
