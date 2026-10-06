<?php
    // Peso, progreso y plan de transición alimentaria de la mascota (tablas registros_peso,
    // transiciones y transicion_etapas).

    // ---------- PESO ----------
    function pesos_de(int $mid): array {
        global $pdo;
        $st = $pdo->prepare('SELECT id, fecha, peso FROM registros_peso WHERE mascota_id = ? ORDER BY fecha, id');
        $st->execute([$mid]);
        return array_map(fn($r) => ['id' => (int)$r['id'], 'fecha' => $r['fecha'], 'peso' => (float)$r['peso']], $st->fetchAll());
    }

    // El peso actual de la mascota es siempre el de la medición más reciente.
    function sincronizar_peso(int $mid): void {
        global $pdo;
        $st = $pdo->prepare('SELECT peso FROM registros_peso WHERE mascota_id = ? ORDER BY fecha DESC, id DESC LIMIT 1');
        $st->execute([$mid]);
        if (($p = $st->fetchColumn()) !== false) {
            $pdo->prepare('UPDATE mascotas SET peso_actual = ? WHERE id = ?')->execute([$p, $mid]);
        }
    }

    // Análisis del historial frente al rango ideal. $m viene de mascota_activa() (ya derivado).
    function analizar_peso(array $m, array $pesos): array {
        $r = ['n' => count($pesos), 'inicial' => null, 'actual' => (float)$m['peso_kg'], 'variacion' => null,
              'kg_semana' => null, 'tendencia' => null, 'dias' => null, 'meta_kg' => 0.0, 'meta_semanas' => null];
        if ($pesos) {
            $r['inicial'] = $pesos[0]['peso'];
            $r['actual'] = end($pesos)['peso'];
            $r['variacion'] = round($r['actual'] - $r['inicial'], 2);
            if (count($pesos) > 1) {
                $dias = (strtotime(end($pesos)['fecha']) - strtotime($pesos[0]['fecha'])) / 86400;
                $r['dias'] = (int)$dias;
                if ($dias >= 7) $r['kg_semana'] = round($r['variacion'] / $dias * 7, 2);
                $ult = $pesos[count($pesos) - 2]['peso'];
                $d = $r['actual'] - $ult;
                $r['tendencia'] = abs($d) < 0.1 ? 'estable' : ($d > 0 ? 'subiendo' : 'bajando');
            }
        }
        // Distancia al rango ideal (los cachorros están creciendo: no se fija meta).
        if ($m['etapa'] !== 'cachorro') {
            if ($m['condicion'] === 'sobrepeso') $r['meta_kg'] = round($r['actual'] - $m['rango_max'], 2);
            elseif ($m['condicion'] === 'bajo') $r['meta_kg'] = round($m['rango_min'] - $r['actual'], 2);
            if ($r['meta_kg'] > 0) $r['meta_semanas'] = max(1, (int)ceil($r['meta_kg'] / max(0.01, $r['actual'] * 0.01)));  // ~1 % del peso por semana
        }
        return $r;
    }

    // ---------- TRANSICIÓN ----------
    const NH_SEMANAS = [
        1 => ['Observación y adaptación', 25, 'Se introduce una cuarta parte de la nueva alimentación. Registra apetito, heces, peso y respuesta digestiva.'],
        2 => ['Introducción gradual', 50, 'La mitad de la ración es la nueva alimentación. Observa la tolerancia antes de avanzar.'],
        3 => ['Ajuste', 75, 'Tres cuartas partes de la ración son la nueva alimentación. Revisa tolerancia, peso y condición corporal.'],
        4 => ['Consolidación', 100, 'Toda la ración es la nueva alimentación. Evalúa el plan completo con seguimiento profesional.'],
    ];

    function transicion_de(int $mid): ?array {
        global $pdo;
        $st = $pdo->prepare('SELECT * FROM transiciones WHERE mascota_id = ? ORDER BY id DESC LIMIT 1');
        $st->execute([$mid]);
        $t = $st->fetch();
        if (!$t) return null;
        $e = $pdo->prepare('SELECT * FROM transicion_etapas WHERE transicion_id = ? ORDER BY semana');
        $e->execute([$t['id']]);
        $t['etapas'] = $e->fetchAll();
        $t['hechas'] = count(array_filter($t['etapas'], fn($x) => $x['completado']));
        $t['total'] = count($t['etapas']);
        $t['pct'] = $t['total'] ? (int)round($t['hechas'] / $t['total'] * 100) : 0;
        $t['actual'] = null;   // semana en curso = la primera sin completar
        foreach ($t['etapas'] as $x) if (!$x['completado']) { $t['actual'] = (int)$x['semana']; break; }
        return $t;
    }

    function transicion_crear(int $mid, string $inicio, string $alimento_actual): void {
        global $pdo;
        $pdo->beginTransaction();
        $pdo->prepare('INSERT INTO transiciones (mascota_id, fecha_inicio, alimento_actual, estado) VALUES (?,?,?,?)')
            ->execute([$mid, $inicio, $alimento_actual, 'en_curso']);
        $tid = (int)$pdo->lastInsertId();
        $ins = $pdo->prepare('INSERT INTO transicion_etapas (transicion_id, semana, titulo, porcentaje_nuevo, completado) VALUES (?,?,?,?,0)');
        foreach (NH_SEMANAS as $sem => [$titulo, $pct]) $ins->execute([$tid, $sem, $titulo, $pct]);
        $pdo->commit();
    }

    // Raciones de cada semana: la energía diaria (DER) se reparte entre la nueva comida (natural)
    // y la actual, y cada parte se convierte a gramos con las kcal de su alimento.
    function plan_semana(array $m, string $actual, int $pct): array {
        $der = nh_calcular($m + ['alimento' => 'natural', 'comidas' => 1])['der'];
        $kcalNuevo = $der * $pct / 100;
        return [
            'der' => $der,
            'g_nuevo' => (int)round($kcalNuevo / NH_ALIMENTOS['natural'][1] * 100),
            'g_actual' => (int)round(($der - $kcalNuevo) / NH_ALIMENTOS[$actual][1] * 100),
        ];
    }
