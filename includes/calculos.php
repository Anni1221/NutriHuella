<?php
    // Cálculos nutricionales orientativos (fórmulas estándar de veterinaria):
    //   RER (energía en reposo) = 70 × peso_kg^0.75
    //   DER (energía diaria)    = RER × factor según especie, etapa, esterilización,
    //                             actividad y condición corporal.

    const NH_ALIMENTOS = [
        'natural'  => ['Comida natural / casera', 150],  // kcal por 100 g (aprox.)
        'humedo'   => ['Alimento húmedo (lata/sobre)', 90],
        'croqueta' => ['Croquetas / alimento seco', 350],
    ];

    function nh_rer(float $kg): float { return 70 * pow($kg, 0.75); }

    function nh_factor(string $tipo, string $etapa, bool $esterilizado, string $actividad, string $condicion): float {
        if ($etapa === 'cachorro') {
            $f = $tipo === 'gato' ? 2.5 : 2.0;
            $actividad = 'moderado';   // en crecimiento no se ajusta por actividad
        } elseif ($etapa === 'senior') {
            $f = $tipo === 'gato' ? 1.1 : 1.4;
        } elseif ($tipo === 'gato') {
            $f = $esterilizado ? 1.2 : 1.4;
        } else {
            $f = $esterilizado ? 1.6 : 1.8;
        }
        $f *= ['bajo' => 0.85, 'moderado' => 1.0, 'alto' => 1.25][$actividad] ?? 1.0;
        if ($etapa !== 'cachorro') {
            $f *= ['bajo' => 1.15, 'ideal' => 1.0, 'sobrepeso' => 0.8][$condicion] ?? 1.0;
        }
        return round($f, 2);
    }

    // Devuelve todos los resultados para mostrar.
    function nh_calcular(array $d): array {
        $kg = (float)$d['peso_kg'];
        $rer = nh_rer($kg);
        $factor = nh_factor($d['tipo'], $d['etapa'], !empty($d['esterilizado']), $d['actividad'], $d['condicion']);
        $der = $rer * $factor;
        [$nombreAlim, $dens] = NH_ALIMENTOS[$d['alimento']] ?? NH_ALIMENTOS['natural'];
        $gramos = $der / $dens * 100;
        $comidas = max(1, min(6, (int)$d['comidas']));
        return [
            'rer' => round($rer),
            'factor' => $factor,
            'der' => round($der),
            'alimento' => $nombreAlim,
            'densidad' => $dens,
            'gramos_dia' => round($gramos),
            'comidas' => $comidas,
            'gramos_comida' => round($gramos / $comidas),
            'pct_peso' => round($gramos / ($kg * 1000) * 100, 1),
        ];
    }
