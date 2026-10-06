<?php
    // Estructura común del panel de administración.
    function admin_inicio(string $titulo, string $subtitulo): void {
        $base = '../';
        $pageTitle = $titulo;
        include __DIR__.'/header.php';
        include __DIR__.'/navbar.php';
        $links = ['dashboard' => 'Resumen', 'usuarios' => 'Usuarios', 'mascotas' => 'Mascotas', 'recetas' => 'Recetas', 'articulos' => 'Artículos'];
        echo '<div class="container py-5"><div class="row"><div class="col-lg-3"><div class="admin-nav mb-4"><strong>🛠️ Administración</strong><div class="mt-2">';
        foreach ($links as $k => $l) echo '<a href="'.$k.'.html">'.$l.'</a>';
        echo '<a href="../dashboard.html">← Volver al sistema</a></div></div></div><div class="col-lg-9">';
        echo '<h1 class="fw-800">'.e($titulo).'</h1><p class="muted">'.e($subtitulo).'</p>';
    }
    function admin_fin(): void {
        echo '</div></div></div>';
        $base = '../';
        include __DIR__.'/footer.php';
    }
    // Botón POST pequeño (cambiar estado)
    function admin_boton(string $accion, int $id, string $texto, string $extra = ''): string {
        return '<form method="post" class="d-inline">'.csrf_field().'<input type="hidden" name="accion" value="'.e($accion).'"><input type="hidden" name="id" value="'.$id.'">'
             . '<button class="btn btn-sm btn-primary-soft" '.$extra.'>'.e($texto).'</button></form>';
    }
