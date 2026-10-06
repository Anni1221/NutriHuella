<?php
    // Sesión, autenticación, CSRF, mensajes flash y mascota activa.
    if (session_status() === PHP_SESSION_NONE) session_start();
    require_once __DIR__.'/conexion.php';
    require_once __DIR__.'/perfil_mascota.php';

    function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

    function redirigir(string $url): void { header('Location: '.$url); exit; }

    function flash(string $tipo, string $msg): void { $_SESSION['flash'][] = [$tipo, $msg]; }

    function flash_render(): string {
        global $pdo, $db_error, $db_aviso;
        $out = '';
        if (!$pdo) $out .= '<div class="container mt-3"><div class="alert alert-danger mb-0"><b>Sin conexión a la base de datos.</b> Verifica que MySQL esté iniciado, que exista la base <code>nutrihuella</code> y los datos de <code>includes/conexion.php</code>.<br><small>'.e($db_error).'</small></div></div>';
        elseif ($db_aviso) $out .= '<div class="container mt-3"><div class="alert alert-warning mb-0">'.e($db_aviso).'</div></div>';
        foreach ($_SESSION['flash'] ?? [] as [$t, $m]) {
            $out .= '<div class="container mt-3"><div class="alert alert-'.e($t).' mb-0">'.e($m).'</div></div>';
        }
        unset($_SESSION['flash']);
        return $out;
    }

    function csrf_token(): string {
        if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
        return $_SESSION['csrf'];
    }
    function csrf_field(): string { return '<input type="hidden" name="csrf" value="'.e(csrf_token()).'">'; }
    function csrf_ok(): bool {
        return isset($_POST['csrf'], $_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $_POST['csrf']);
    }

    function es_post(): bool { return $_SERVER['REQUEST_METHOD'] === 'POST'; }

    function usuario_actual(): ?array {
        global $pdo;
        if (empty($_SESSION['uid']) || !$pdo) return null;
        static $u = false;
        if ($u === false) {
            $st = $pdo->prepare("SELECT id, nombre, correo, rol FROM usuarios WHERE id = ? AND estado = 'activo'");
            $st->execute([$_SESSION['uid']]);
            $u = $st->fetch() ?: null;
            if (!$u) unset($_SESSION['uid']);
        }
        return $u;
    }

    // $base = ruta relativa a la raíz del proyecto ('' o '../')
    function requerir_login(string $base): array {
        global $pdo, $db_error;
        if (!$pdo) {
            http_response_code(500);
            exit('<p style="font-family:sans-serif;padding:2rem">No se pudo conectar a la base de datos. '
                .'Verifica que MySQL esté iniciado en WAMP y los datos de <code>includes/conexion.php</code>.<br><small>'.e($db_error).'</small></p>');
        }
        $u = usuario_actual();
        if (!$u) { flash('warning', 'Inicia sesión para continuar.'); redirigir($base.'login.html'); }
        return $u;
    }

    function iniciar_sesion(int $uid): void {
        session_regenerate_id(true);
        $_SESSION['uid'] = $uid;
        unset($_SESSION['mascota_id']);
    }

    function cerrar_sesion(): void {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    function mascotas_de(int $uid): array {
        global $pdo;
        $st = $pdo->prepare("SELECT * FROM mascotas WHERE usuario_id = ? AND estado = 'activa' ORDER BY id");
        $st->execute([$uid]);
        // Se adapta a los nombres internos que usan las páginas y la calculadora.
        return array_map(function ($r) {
            $r['tipo'] = strtolower($r['tipo']);
            $r['actividad'] = strtolower($r['nivel_actividad']);
            $r['peso_kg'] = $r['peso_actual'];
            // Etapa, actividad y condición se calculan (no dependen de lo guardado).
            return array_merge($r, nh_derivar($r));
        }, $st->fetchAll());
    }

    // Mascota activa del usuario (la elegida en sesión, o la primera registrada).
    function mascota_activa(int $uid): ?array {
        $todas = mascotas_de($uid);
        if (!$todas) return null;
        foreach ($todas as $m) if ((int)$m['id'] === (int)($_SESSION['mascota_id'] ?? 0)) return $m;
        $_SESSION['mascota_id'] = $todas[0]['id'];
        return $todas[0];
    }

    // Usuario con sesión y su mascota activa; si aún no tiene mascota, lo manda a registrarla.
    function requerir_mascota(string $base): array {
        $u = requerir_login($base);
        $m = mascota_activa((int)$u['id']);
        if (!$m) { flash('info', 'Primero registra a tu mascota.'); redirigir($base.'mascota/registrar.html'); }
        return [$u, $m];
    }

    function requerir_admin(string $base): array {
        $u = requerir_login($base);
        if (($u['rol'] ?? '') !== 'admin') { flash('warning', 'No tienes permiso para entrar al panel de administración.'); redirigir($base.'dashboard.html'); }
        return $u;
    }

    function fecha_es(?string $f): string { return $f ? date('d/m/Y', strtotime($f)) : '—'; }
    function kg(float $v): string { return rtrim(rtrim(number_format($v, 2, '.', ''), '0'), '.'); }
