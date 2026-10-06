<?php
    // Lectura/validación del formulario de mascota (compartido por registrar y editar).
    require_once __DIR__.'/perfil_mascota.php';

    function mascota_desde_post(): array {
        return [
            'nombre' => trim($_POST['nombre'] ?? ''),
            'tipo' => $_POST['tipo'] ?? 'perro',
            'raza' => trim($_POST['raza'] ?? ''),
            'edad_anios' => ($_POST['edad_anios'] ?? '') === '' ? null : (float)$_POST['edad_anios'],
            'estatura_cm' => ($_POST['estatura_cm'] ?? '') === '' ? null : (float)str_replace(',', '.', $_POST['estatura_cm']),
            'esterilizado' => empty($_POST['esterilizado']) ? 0 : 1,
            'peso_kg' => (float)str_replace(',', '.', $_POST['peso_kg'] ?? '0'),
            'alergias' => trim($_POST['alergias'] ?? ''),
        ];
    }

    function mascota_validar(array $m): array {
        $er = [];
        if ($m['nombre'] === '' || mb_strlen($m['nombre']) > 80) $er[] = 'El nombre es obligatorio (máx. 80 caracteres).';
        if (!in_array($m['tipo'], ['perro', 'gato'], true)) $er[] = 'Tipo de mascota no válido.';
        $maxCm = $m['tipo'] === 'gato' ? 60 : 120;
        if ($m['estatura_cm'] === null || $m['estatura_cm'] < 10 || $m['estatura_cm'] > $maxCm) $er[] = "La estatura debe estar entre 10 y $maxCm cm (del piso al hombro).";
        if ($m['peso_kg'] < 0.1 || $m['peso_kg'] > 120) $er[] = 'El peso debe estar entre 0.1 y 120 kg.';
        if ($m['edad_anios'] === null) $er[] = 'La edad es obligatoria (se usa para calcular la etapa de vida).';
        elseif ($m['edad_anios'] < 0 || $m['edad_anios'] > 30) $er[] = 'La edad debe estar entre 0 y 30 años.';
        if (mb_strlen($m['raza']) > 80) $er[] = 'La raza es demasiado larga.';
        return $er;
    }

    function etiqueta_actividad(string $a): string { return ['bajo' => 'Baja', 'moderado' => 'Moderada', 'alto' => 'Alta'][$a] ?? $a; }
    function emoji_mascota(string $t): string { return $t === 'gato' ? '🐱' : '🐶'; }
