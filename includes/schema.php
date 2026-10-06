<?php
    // MySQL: se usa TU estructura (database/nutrihuella.sql). Aquí solo se agregan, si faltan,
    // las columnas que necesita la calculadora: estatura_cm, tamano, etapa, esterilizado y condicion.
    // SQLite: solo para pruebas locales del desarrollador.
    function nh_instalar(PDO $pdo, string $driver): void {
        if ($driver === 'sqlite') {
            $pdo->exec("CREATE TABLE IF NOT EXISTS usuarios (id INTEGER PRIMARY KEY AUTOINCREMENT, nombre TEXT NOT NULL, correo TEXT NOT NULL UNIQUE,
                password_hash TEXT NOT NULL, rol TEXT NOT NULL DEFAULT 'usuario', estado TEXT NOT NULL DEFAULT 'activo', creado_en TEXT DEFAULT CURRENT_TIMESTAMP)");
            $pdo->exec("CREATE TABLE IF NOT EXISTS mascotas (id INTEGER PRIMARY KEY AUTOINCREMENT, usuario_id INT NOT NULL, nombre TEXT NOT NULL, tipo TEXT NOT NULL,
                raza TEXT, edad_anios REAL, peso_actual REAL, nivel_actividad TEXT NOT NULL DEFAULT 'Moderado', alergias TEXT, sintomas TEXT,
                estado TEXT NOT NULL DEFAULT 'activa', creado_en TEXT DEFAULT CURRENT_TIMESTAMP,
                estatura_cm REAL, tamano TEXT NOT NULL DEFAULT 'mediano', etapa TEXT NOT NULL DEFAULT 'adulto', esterilizado INT NOT NULL DEFAULT 0, condicion TEXT NOT NULL DEFAULT 'ideal',
                FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE)");
            $pdo->exec("CREATE TABLE IF NOT EXISTS registros_peso (id INTEGER PRIMARY KEY AUTOINCREMENT, mascota_id INT NOT NULL, fecha TEXT NOT NULL, peso REAL NOT NULL,
                FOREIGN KEY (mascota_id) REFERENCES mascotas(id) ON DELETE CASCADE)");
            $pdo->exec("CREATE TABLE IF NOT EXISTS recetas (id INTEGER PRIMARY KEY AUTOINCREMENT, titulo TEXT NOT NULL, especie TEXT NOT NULL DEFAULT 'Ambos', categoria TEXT,
                descripcion TEXT, ingredientes TEXT, preparacion TEXT, advertencias TEXT, estado TEXT NOT NULL DEFAULT 'borrador', creado_en TEXT DEFAULT CURRENT_TIMESTAMP)");
            $pdo->exec("CREATE TABLE IF NOT EXISTS transiciones (id INTEGER PRIMARY KEY AUTOINCREMENT, mascota_id INT NOT NULL, fecha_inicio TEXT NOT NULL,
                duracion_dias INT NOT NULL DEFAULT 10, estado TEXT NOT NULL DEFAULT 'en_curso', notas TEXT, FOREIGN KEY (mascota_id) REFERENCES mascotas(id) ON DELETE CASCADE)");
            $pdo->exec("CREATE TABLE IF NOT EXISTS transicion_etapas (id INTEGER PRIMARY KEY AUTOINCREMENT, transicion_id INT NOT NULL, semana INT NOT NULL, titulo TEXT,
                porcentaje_nuevo INT NOT NULL, receta_id INT NULL, completado INT NOT NULL DEFAULT 0, observaciones TEXT, FOREIGN KEY (transicion_id) REFERENCES transiciones(id) ON DELETE CASCADE)");
            $pdo->exec("CREATE TABLE IF NOT EXISTS articulos (id INTEGER PRIMARY KEY AUTOINCREMENT, titulo TEXT NOT NULL, categoria TEXT, resumen TEXT, contenido TEXT,
                autor_id INT NULL, estado TEXT NOT NULL DEFAULT 'borrador', fecha TEXT DEFAULT CURRENT_DATE)");
            $pdo->exec("CREATE TABLE IF NOT EXISTS faq (id INTEGER PRIMARY KEY AUTOINCREMENT, pregunta TEXT NOT NULL, respuesta TEXT NOT NULL, orden INT NOT NULL DEFAULT 0, activa INT NOT NULL DEFAULT 1)");
            return;
        }
        $extra = [
            'estatura_cm'  => "DECIMAL(5,1) NULL",
            'tamano'       => "VARCHAR(10) NOT NULL DEFAULT 'mediano'",
            'etapa'        => "VARCHAR(10) NOT NULL DEFAULT 'adulto'",
            'esterilizado' => "TINYINT(1) NOT NULL DEFAULT 0",
            'condicion'    => "VARCHAR(10) NOT NULL DEFAULT 'ideal'",
        ];
        foreach ($extra as $col => $def) {
            if (!$pdo->query("SHOW COLUMNS FROM mascotas LIKE '$col'")->fetch()) {
                $pdo->exec("ALTER TABLE mascotas ADD COLUMN $col $def");
            }
        }
    }
