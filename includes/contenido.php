<?php
    // Recetas, artículos y preguntas frecuentes leídos desde la base de datos
    // (tablas recetas, articulos y faq). La primera vez, si están vacías, se cargan
    // los textos de ejemplo del proyecto para que se puedan administrar desde el panel admin.

    function contenido_sembrar(): void {
        global $pdo;
        static $hecho = false;
        if ($hecho || !$pdo) return;
        $hecho = true;
        try {
            if ((int)$pdo->query('SELECT COUNT(*) FROM recetas')->fetchColumn() === 0) {
                $ins = $pdo->prepare("INSERT INTO recetas (titulo, especie, categoria, descripcion, ingredientes, preparacion, advertencias, estado) VALUES (?,?,?,?,?,?,?, 'publicada')");
                foreach ([
                    ['Pollo & verduras', 'Perro', 'Proteína y verduras', 'Una combinación sencilla de proteína y vegetales cocidos, pensada como punto de partida.',
                     "Pollo cocido, sin piel, sin huesos y sin condimentos\nZanahoria cocida\nCalabaza cocida\nComponente nutricional formulado según necesidad (indicado por un veterinario)",
                     "Cocinar el pollo y las verduras sin sal, aceite ni condimentos.\nRetirar piel y huesos del pollo.\nDejar enfriar y cortar en trozos pequeños, según el tamaño del perro.\nMezclar y servir la porción indicada en su pauta individual.",
                     'No agregues cebolla, ajo ni condimentos. Las cantidades deben ajustarse al peso y a la actividad de cada perro.'],
                    ['Pescado suave', 'Ambos', 'Pescado y verdura', 'Una propuesta ligera a base de pescado blanco y verdura cocida, fácil de digerir.',
                     "Pescado blanco cocido y sin espinas\nCalabacín cocido\nUn poco del agua de cocción, sin sal\nComponente nutricional formulado según necesidad (indicado por un veterinario)",
                     "Cocer o cocinar al vapor el pescado y el calabacín, sin sal ni aceite.\nRevisar con cuidado que no queden espinas.\nDesmenuzar el pescado y picar el calabacín muy fino.\nMezclar con un poco de agua de cocción y servir tibio.",
                     'Los gatos son carnívoros estrictos y tienen necesidades particulares (por ejemplo, taurina). Para ellos conviene que un veterinario defina las proporciones, con pocas verduras.'],
                    ['Carne & vegetales', 'Perro', 'Proteína y verduras', 'Proteína magra combinada con vegetales permitidos para variar el menú.',
                     "Carne magra de res cocida, sin grasa visible ni condimentos\nBrócoli cocido\nZanahoria cocida\nComponente nutricional formulado según necesidad (indicado por un veterinario)",
                     "Cocinar la carne hasta que esté bien cocida, sin sal ni condimentos.\nCocer el brócoli y la zanahoria hasta que estén blandos.\nPicar todo en trozos pequeños y dejar enfriar.\nServir la porción indicada en su pauta individual.",
                     'El brócoli debe darse en cantidades pequeñas. Evita huesos cocidos, que pueden astillarse.'],
                ] as $r) $ins->execute($r);
            }
            if ((int)$pdo->query('SELECT COUNT(*) FROM articulos')->fetchColumn() === 0) {
                $ins = $pdo->prepare("INSERT INTO articulos (titulo, categoria, resumen, contenido, estado, fecha) VALUES (?,?,?,?, 'publicado', ?)");
                foreach ([
                    ['Alimentación ultraprocesada', 'Nutrición', 'Aspectos para comprender al evaluar la alimentación habitual de tu mascota.',
                     "## Qué es un alimento ultraprocesado\nSon productos elaborados industrialmente a partir de ingredientes refinados, con aditivos como saborizantes, colorantes o conservantes. Muchos alimentos comerciales secos pertenecen a esta categoría.\n\n## Qué conviene revisar\nLee la lista de ingredientes: los primeros son los que están en mayor cantidad. Observa también el estado del pelaje, las heces, el nivel de energía y el peso de tu mascota.\n\n## Antes de cambiar\nUn alimento procesado no es automáticamente malo, ni uno natural es automáticamente mejor. Cualquier cambio debe ser equilibrado y, de preferencia, orientado por un veterinario."],
                    ['¿Por qué hacerlo gradualmente?', 'Transición', 'Cómo observar la tolerancia y los cambios durante el proceso.',
                     "## El sistema digestivo necesita adaptarse\nUn cambio brusco de alimento puede causar vómito, diarrea o rechazo. La flora intestinal necesita tiempo para ajustarse a ingredientes nuevos.\n\n## Cómo suele plantearse\nSe mezcla una pequeña parte del alimento nuevo con el habitual y se aumenta poco a poco durante varios días o semanas, según cómo responda tu mascota. NutriHuella propone un plan de 4 semanas como referencia.\n\n## Qué registrar\nAnota el apetito, la consistencia de las heces, el peso y cualquier síntoma. Si algo empeora, detén el avance y consulta con un profesional."],
                    ['Señales para consultar', 'Cuidados', 'Cambios que justifican pedir una valoración profesional.',
                     "## Señales digestivas\nVómitos repetidos, diarrea que dura más de uno o dos días, sangre en las heces o abdomen hinchado requieren atención veterinaria.\n\n## Señales generales\nPérdida de peso sin explicación, falta de apetito prolongada, decaimiento, mucha sed o cambios marcados en el pelaje y la piel son motivo de consulta.\n\n## Cuándo actuar rápido\nSi tu mascota está muy decaída, tiene dificultad para respirar o sospechas que comió algo tóxico, acude de inmediato a un veterinario. No esperes a que pase."],
                ] as $r) $ins->execute([$r[0], $r[1], $r[2], $r[3], date('Y-m-d')]);
            }
            if ((int)$pdo->query('SELECT COUNT(*) FROM faq')->fetchColumn() === 0) {
                $ins = $pdo->prepare('INSERT INTO faq (pregunta, respuesta, orden, activa) VALUES (?,?,?,1)');
                foreach ([
                    ['¿NutriHuella reemplaza al veterinario?', 'No. Es una plataforma educativa y de seguimiento. Las necesidades nutricionales deben individualizarse profesionalmente.'],
                    ['¿Puedo cambiar la alimentación de un día para otro?', 'La plataforma plantea una transición gradual para facilitar la observación de tolerancia.'],
                    ['¿Puedo usar las mismas recetas para perros y gatos?', 'No necesariamente. Perros y gatos tienen necesidades nutricionales diferentes.'],
                ] as $i => $r) $ins->execute([$r[0], $r[1], $i + 1]);
            }
        } catch (PDOException $e) {
            $GLOBALS['db_aviso'] = 'No se pudo leer el contenido (recetas/artículos/faq). ¿Importaste todas las tablas de tu script SQL? '.$e->getMessage();
        }
    }

    function lineas(?string $t): array { return array_values(array_filter(array_map('trim', preg_split('/\R/', (string)$t)), 'strlen')); }

    function etiqueta_especie(string $e): string { return ['Perro' => 'Perros', 'Gato' => 'Gatos', 'Ambos' => 'Perros y gatos'][$e] ?? $e; }

    function emoji_receta(array $r): string {
        $t = nh_normalizar($r['titulo'].' '.$r['ingredientes']);
        $map = ['pollo' => '🍗', 'pavo' => '🍗', 'pescado' => '🐟', 'atun' => '🐟', 'salmon' => '🐟', 'carne' => '🥩', 'res ' => '🥩', 'cerdo' => '🥩', 'higado' => '🥩', 'huevo' => '🥚',
                'zanahoria' => '🥕', 'brocoli' => '🥦', 'calabac' => '🥒', 'calabaza' => '🎃', 'arroz' => '🍚', 'papa' => '🥔', 'batata' => '🍠'];
        $out = [];
        foreach ($map as $k => $e) if (strpos($t.' ', $k) !== false && !in_array($e, $out, true)) $out[] = $e;
        return implode('', array_slice($out, 0, 2)) ?: '🍲';
    }

    // Ingredientes de $texto que coinciden con las alergias registradas de la mascota.
    function alergenos_en(?string $alergias, string $texto): array {
        $hall = [];
        $t = ' '.nh_normalizar($texto).' ';
        foreach (preg_split('/[,;\n]| y /u', (string)$alergias) as $a) {
            $a = trim(nh_normalizar($a));
            if (strlen($a) < 3 || preg_match('/^(ningun|sin |no |na$|n a$)/', $a)) continue;
            $raiz = preg_replace('/s$/', '', $a);
            if (strpos($t, ' '.$raiz) !== false) $hall[] = $a;
        }
        return array_values(array_unique($hall));
    }

    function recetas_publicadas(?string $tipo = null): array {
        global $pdo;
        contenido_sembrar();
        if (!$pdo) return [];
        $sql = "SELECT * FROM recetas WHERE estado = 'publicada'";
        $par = [];
        if ($tipo) { $sql .= " AND especie IN (?, 'Ambos')"; $par[] = ucfirst($tipo); }
        $st = $pdo->prepare($sql.' ORDER BY id');
        $st->execute($par);
        return $st->fetchAll();
    }

    function receta_por_id(int $id): ?array {
        global $pdo;
        contenido_sembrar();
        if (!$pdo) return null;
        $st = $pdo->prepare("SELECT * FROM recetas WHERE id = ? AND estado = 'publicada'");
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }

    function articulos_publicados(): array {
        global $pdo;
        contenido_sembrar();
        if (!$pdo) return [];
        return $pdo->query("SELECT id, titulo, categoria, resumen, fecha FROM articulos WHERE estado = 'publicado' ORDER BY fecha DESC, id DESC")->fetchAll();
    }

    function articulo_por_id(int $id): ?array {
        global $pdo;
        contenido_sembrar();
        if (!$pdo) return null;
        $st = $pdo->prepare("SELECT * FROM articulos WHERE id = ? AND estado = 'publicado'");
        $st->execute([$id]);
        return $st->fetch() ?: null;
    }

    // "## Título\ntexto" -> [[título, texto], ...]
    function articulo_secciones(?string $contenido): array {
        $out = [];
        foreach (preg_split('/^##\s*/m', (string)$contenido) as $bloque) {
            $bloque = trim($bloque);
            if ($bloque === '') continue;
            $p = preg_split('/\R/', $bloque, 2);
            $out[] = [trim($p[0]), trim($p[1] ?? '')];
        }
        return $out;
    }

    function faq_activas(): array {
        global $pdo;
        contenido_sembrar();
        if (!$pdo) return [];
        return $pdo->query('SELECT pregunta, respuesta FROM faq WHERE activa = 1 ORDER BY orden, id')->fetchAll();
    }
