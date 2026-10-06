-- NutriHuella · esquema final (solo lo que la app usa).
-- Importar completo en phpMyAdmin. Si ya tienes la base creada, usa database/migracion.sql.
CREATE DATABASE IF NOT EXISTS nutrihuella
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE nutrihuella;

-- ---------- USUARIOS ----------
CREATE TABLE usuarios (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre        VARCHAR(120) NOT NULL,
  correo        VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,              -- password_hash() en PHP
  rol           ENUM('usuario','admin') NOT NULL DEFAULT 'usuario',
  estado        ENUM('activo','inactivo') NOT NULL DEFAULT 'activo',
  creado_en     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------- MASCOTAS ----------
CREATE TABLE mascotas (
  id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  usuario_id         INT UNSIGNED NOT NULL,
  nombre             VARCHAR(80) NOT NULL,
  tipo               ENUM('Perro','Gato') NOT NULL,
  raza               VARCHAR(80),
  edad_anios         DECIMAL(4,1),
  estatura_cm        DECIMAL(5,1) NULL,                                    -- piso al hombro; define el tamaño
  tamano             ENUM('pequeno','mediano','grande','gigante') NOT NULL DEFAULT 'mediano',
  etapa              ENUM('cachorro','adulto','senior') NOT NULL DEFAULT 'adulto',
  esterilizado       TINYINT(1) NOT NULL DEFAULT 0,
  peso_actual        DECIMAL(5,2),                                         -- siempre = última fila de registros_peso
  nivel_actividad    ENUM('Bajo','Moderado','Alto') NOT NULL DEFAULT 'Moderado',
  condicion          ENUM('bajo','ideal','sobrepeso') NOT NULL DEFAULT 'ideal',
  alergias           TEXT,
  estado             ENUM('activa','inactiva') NOT NULL DEFAULT 'activa',
  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
  INDEX (usuario_id, estado)
) ENGINE=InnoDB;

-- ---------- SEGUIMIENTO DE PESO ----------
CREATE TABLE registros_peso (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  mascota_id  INT UNSIGNED NOT NULL,
  fecha       DATE NOT NULL,
  peso        DECIMAL(5,2) NOT NULL,
  FOREIGN KEY (mascota_id) REFERENCES mascotas(id) ON DELETE CASCADE,
  INDEX (mascota_id, fecha)
) ENGINE=InnoDB;

-- ---------- RECETAS ----------
CREATE TABLE recetas (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  titulo        VARCHAR(150) NOT NULL,
  especie       ENUM('Perro','Gato','Ambos') NOT NULL DEFAULT 'Ambos',
  categoria     VARCHAR(60),
  descripcion   TEXT,
  ingredientes  TEXT,
  preparacion   TEXT,
  advertencias  TEXT,
  estado        ENUM('publicada','borrador') NOT NULL DEFAULT 'borrador'
) ENGINE=InnoDB;

-- ---------- TRANSICIÓN ALIMENTARIA (4 semanas) ----------
CREATE TABLE transiciones (
  id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  mascota_id      INT UNSIGNED NOT NULL,
  fecha_inicio    DATE NOT NULL,
  alimento_actual ENUM('natural','humedo','croqueta') NOT NULL DEFAULT 'croqueta',
  estado          ENUM('en_curso','completada','pausada') NOT NULL DEFAULT 'en_curso',
  FOREIGN KEY (mascota_id) REFERENCES mascotas(id) ON DELETE CASCADE,
  INDEX (mascota_id)
) ENGINE=InnoDB;

CREATE TABLE transicion_etapas (
  id               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  transicion_id    INT UNSIGNED NOT NULL,
  semana           TINYINT UNSIGNED NOT NULL,       -- 1..4
  titulo           VARCHAR(120),
  porcentaje_nuevo TINYINT UNSIGNED NOT NULL,       -- % alimento natural en esa semana
  completado       TINYINT(1) NOT NULL DEFAULT 0,
  observaciones    TEXT,
  FOREIGN KEY (transicion_id) REFERENCES transiciones(id) ON DELETE CASCADE,
  UNIQUE (transicion_id, semana)
) ENGINE=InnoDB;

-- ---------- CONTENIDO EDUCATIVO ----------
CREATE TABLE articulos (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  titulo      VARCHAR(180) NOT NULL,
  categoria   VARCHAR(60),
  resumen     VARCHAR(300),
  contenido   MEDIUMTEXT,
  autor_id    INT UNSIGNED NULL,
  estado      ENUM('publicado','borrador') NOT NULL DEFAULT 'borrador',
  fecha       DATE DEFAULT (CURRENT_DATE),
  FOREIGN KEY (autor_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE faq (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  pregunta   VARCHAR(255) NOT NULL,
  respuesta  TEXT NOT NULL,
  orden      INT NOT NULL DEFAULT 0,
  activa     TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;
