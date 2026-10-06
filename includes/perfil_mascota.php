<?php
    // Deducción automática de etapa de vida, nivel de actividad y condición corporal
    // a partir de: tipo, raza, tamaño, edad (años) y peso. Es una estimación orientativa.

    // [alias normalizados], tamaño, peso ideal mín (kg), peso ideal máx (kg), actividad típica
    const NH_RAZAS = [
        'perro' => [
            [['chihuahua'], 'pequeno', 1.5, 3, 'moderado'],
            [['yorkshire', 'yorkie'], 'pequeno', 2, 3.2, 'moderado'],
            [['pomerania', 'pomeranian'], 'pequeno', 1.8, 3.5, 'moderado'],
            [['maltes', 'maltese'], 'pequeno', 2, 4, 'moderado'],
            [['shih tzu', 'shihtzu'], 'pequeno', 4, 7.5, 'bajo'],
            [['pug', 'carlino'], 'pequeno', 6, 9, 'bajo'],
            [['bulldog frances', 'frances bulldog', 'french bulldog'], 'pequeno', 8, 14, 'bajo'],
            [['bulldog ingles', 'bulldog'], 'mediano', 18, 25, 'bajo'],
            [['dachshund', 'salchicha', 'teckel'], 'pequeno', 5, 12, 'moderado'],
            [['jack russell'], 'pequeno', 5, 8, 'alto'],
            [['westie', 'west highland'], 'pequeno', 6, 10, 'moderado'],
            [['beagle'], 'mediano', 9, 14, 'alto'],
            [['cocker'], 'mediano', 9, 15, 'moderado'],
            [['corgi'], 'mediano', 10, 14, 'moderado'],
            [['shar pei', 'sharpei'], 'mediano', 16, 29, 'moderado'],
            [['border collie'], 'mediano', 14, 20, 'alto'],
            [['pitbull', 'pit bull'], 'mediano', 14, 27, 'alto'],
            [['dalmata'], 'grande', 16, 32, 'alto'],
            [['husky'], 'grande', 16, 27, 'alto'],
            [['samoyedo'], 'grande', 16, 30, 'alto'],
            [['pastor australiano'], 'grande', 16, 32, 'alto'],
            [['pastor belga', 'malinois'], 'grande', 20, 34, 'alto'],
            [['chow chow'], 'grande', 20, 32, 'bajo'],
            [['pastor aleman'], 'grande', 22, 40, 'alto'],
            [['labrador'], 'grande', 25, 36, 'alto'],
            [['golden'], 'grande', 25, 34, 'alto'],
            [['boxer'], 'grande', 25, 32, 'alto'],
            [['weimaraner'], 'grande', 25, 40, 'alto'],
            [['akita'], 'grande', 30, 45, 'moderado'],
            [['doberman'], 'grande', 32, 45, 'alto'],
            [['boyero de berna', 'bernes'], 'grande', 32, 52, 'moderado'],
            [['rottweiler', 'rottweiller'], 'grande', 35, 60, 'moderado'],
            [['gran danes', 'gran dane', 'dogo aleman'], 'gigante', 45, 90, 'moderado'],
            [['san bernardo'], 'gigante', 55, 80, 'bajo'],
            [['mastin'], 'gigante', 60, 100, 'bajo'],
        ],
        'gato' => [
            [['abisinio'], 'pequeno', 3, 5, 'alto'],
            [['siames'], 'pequeno', 3, 5, 'alto'],
            [['sphynx', 'esfinge'], 'pequeno', 3.5, 5.5, 'alto'],
            [['angora'], 'pequeno', 3, 5, 'moderado'],
            [['azul ruso', 'ruso azul'], 'mediano', 3, 5.5, 'moderado'],
            [['persa', 'exotico'], 'mediano', 3.5, 6, 'bajo'],
            [['british', 'britanico'], 'mediano', 4, 8, 'bajo'],
            [['bosque de noruega', 'noruego'], 'grande', 4, 8, 'moderado'],
            [['bengala', 'bengali'], 'mediano', 4, 7, 'alto'],
            [['ragdoll'], 'grande', 4.5, 9, 'bajo'],
            [['maine coon'], 'grande', 4.5, 9, 'moderado'],
        ],
    ];

    // Rangos de peso ideal cuando la raza no está en la lista (criollo, mestizo, otra...).
    const NH_RANGO_TAMANO = [
        'perro' => ['pequeno' => [3, 10], 'mediano' => [10, 25], 'grande' => [25, 45], 'gigante' => [45, 80]],
        'gato'  => ['pequeno' => [2.5, 3.5], 'mediano' => [3.5, 5], 'grande' => [5, 8], 'gigante' => [5, 8]],
    ];
    const NH_TAMANOS = ['pequeno' => 'Pequeño', 'mediano' => 'Mediano', 'grande' => 'Grande', 'gigante' => 'Gigante'];
    // A partir de qué edad (años) un perro se considera senior según su tamaño.
    const NH_SENIOR_PERRO = ['pequeno' => 10, 'mediano' => 8, 'grande' => 7, 'gigante' => 6];

    // Tamaño según la estatura (cm, desde el piso hasta la cruz/hombro).
    function nh_tamano_por_estatura(string $tipo, float $cm): string {
        // límites: [pequeño hasta, mediano hasta, grande hasta]; por encima = gigante
        [$p, $m, $g] = $tipo === 'gato' ? [23, 28, 9999] : [38, 55, 68];
        return $cm < $p ? 'pequeno' : ($cm < $m ? 'mediano' : ($cm < $g ? 'grande' : 'gigante'));
    }

    function nh_normalizar(string $t): string {
        $t = mb_strtolower(trim($t), 'UTF-8');
        $t = strtr($t, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n']);
        return preg_replace('/[^a-z0-9]+/', ' ', $t);
    }

    function nh_buscar_raza(string $tipo, string $raza): ?array {
        $r = ' '.nh_normalizar($raza).' ';
        if (trim($r) === '') return null;
        foreach (NH_RAZAS[$tipo] ?? [] as $fila) {
            foreach ($fila[0] as $alias) {
                if (strpos($r, ' '.$alias.' ') !== false) return $fila;
            }
        }
        return null;
    }

    // Devuelve etapa, actividad, condicion, rango ideal y si se usó la raza.
    function nh_derivar(array $m): array {
        $tipo = ($m['tipo'] ?? 'perro') === 'gato' ? 'gato' : 'perro';
        $raza = nh_buscar_raza($tipo, (string)($m['raza'] ?? ''));
        $estatura = (float)($m['estatura_cm'] ?? 0);
        if ($estatura > 0) $tamano = nh_tamano_por_estatura($tipo, $estatura);          // lo normal: por estatura
        elseif ($raza) $tamano = $raza[1];                                               // fichas antiguas sin estatura
        else $tamano = array_key_exists($m['tamano'] ?? '', NH_TAMANOS) ? $m['tamano'] : 'mediano';
        $edad = isset($m['edad_anios']) && $m['edad_anios'] !== '' ? (float)$m['edad_anios'] : null;
        $peso = (float)($m['peso_kg'] ?? 0);

        [$min, $max] = $raza ? [$raza[2], $raza[3]] : NH_RANGO_TAMANO[$tipo][$tamano];

        // Etapa de vida según la edad
        if ($edad === null) {
            $etapa = 'adulto';
        } elseif ($tipo === 'gato') {
            $etapa = $edad < 1 ? 'cachorro' : ($edad >= 10 ? 'senior' : 'adulto');
        } else {
            $etapa = $edad < ($tamano === 'gigante' ? 1.5 : 1) ? 'cachorro'
                   : ($edad >= NH_SENIOR_PERRO[$tamano] ? 'senior' : 'adulto');
        }

        // Actividad según raza/tamaño (los senior bajan su actividad)
        $actividad = $raza ? $raza[4] : 'moderado';
        if ($etapa === 'senior') $actividad = 'bajo';

        // Condición corporal: peso vs. rango ideal (los cachorros están creciendo)
        if ($etapa === 'cachorro') $condicion = 'ideal';
        elseif ($peso > 0 && $peso < $min) $condicion = 'bajo';
        elseif ($peso > $max) $condicion = 'sobrepeso';
        else $condicion = 'ideal';

        return [
            'tamano' => $tamano, 'etapa' => $etapa, 'actividad' => $actividad, 'condicion' => $condicion,
            'rango_min' => $min, 'rango_max' => $max, 'raza_conocida' => (bool)$raza,
        ];
    }
