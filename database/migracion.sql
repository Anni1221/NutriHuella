-- Para una base nutrihuella YA existente (la de tu script original + las columnas que la app agregaba sola).
-- Ejecutar UNA vez en phpMyAdmin (base nutrihuella). Haz una copia (Exportar) antes.

-- 1) Columnas nuevas de mascotas con valores controlados (antes eran VARCHAR creados por la app).
--    Si alguna columna aún no existe, cambia MODIFY por ADD en esa línea.
ALTER TABLE mascotas
  MODIFY COLUMN estatura_cm  DECIMAL(5,1) NULL,
  MODIFY COLUMN tamano       ENUM('pequeno','mediano','grande','gigante') NOT NULL DEFAULT 'mediano',
  MODIFY COLUMN etapa        ENUM('cachorro','adulto','senior') NOT NULL DEFAULT 'adulto',
  MODIFY COLUMN esterilizado TINYINT(1) NOT NULL DEFAULT 0,
  MODIFY COLUMN condicion    ENUM('bajo','ideal','sobrepeso') NOT NULL DEFAULT 'ideal',
  ADD INDEX idx_usuario_estado (usuario_id, estado),
  DROP COLUMN sintomas,                                 -- se quitó "Síntomas actuales"
  DROP COLUMN creado_en;                                -- no se usa

-- 2) transiciones: el alimento actual pasa de un JSON dentro de "notas" a su propia columna.
ALTER TABLE transiciones
  ADD COLUMN alimento_actual ENUM('natural','humedo','croqueta') NOT NULL DEFAULT 'croqueta' AFTER fecha_inicio;
UPDATE transiciones
   SET alimento_actual = JSON_UNQUOTE(JSON_EXTRACT(notas, '$.alimento_actual'))
 WHERE JSON_VALID(notas) AND JSON_UNQUOTE(JSON_EXTRACT(notas, '$.alimento_actual')) IN ('natural','humedo','croqueta');
ALTER TABLE transiciones
  DROP COLUMN notas,
  DROP COLUMN duracion_dias,                            -- siempre 28 y nunca se leía
  ADD INDEX idx_mascota (mascota_id);

-- 3) transicion_etapas: receta_id nunca se usó; una sola fila por semana.
ALTER TABLE transicion_etapas DROP FOREIGN KEY transicion_etapas_ibfk_2;   -- si falla, mira el nombre en phpMyAdmin > Estructura > Vista de relaciones
ALTER TABLE transicion_etapas
  DROP COLUMN receta_id,
  ADD UNIQUE KEY uq_transicion_semana (transicion_id, semana);

-- 4) recetas: creado_en no se usa.
ALTER TABLE recetas DROP COLUMN creado_en;
