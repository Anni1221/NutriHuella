<?php require_once __DIR__.'/auth.php'; $__nu = usuario_actual(); ?>
<nav class="navbar navbar-expand-lg nh-navbar sticky-top">
    <div class="container">
        <a class="navbar-brand fw-800" href="<?= $base ?>index.html">
            <span class="brand-paw">
                🐾
            </span>
            Nutri
            <span>
                Huella
            </span>
        </a>
        <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="menu">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                <li class="nav-item">
                    <a class="nav-link" href="<?= $base ?>dashboard.html">
                        Inicio
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= $base ?>mascota/perfil.html">
                        Mi mascota
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= $base ?>nutricion/calculadora.html">
                        Nutrición
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= $base ?>nutricion/recetas.html">
                        Recetas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= $base ?>seguimiento/progreso.html">
                        Seguimiento
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= $base ?>educativo/articulos.html">
                        Educación
                    </a>
                </li>
                <?php if ($__nu && $__nu['rol'] === 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= $base ?>admin/dashboard.html">
                        Admin
                    </a>
                </li>
                <?php endif; ?>
                <li class="nav-item ms-lg-2">
                    <?php if (!empty($_SESSION['uid'])): ?>
                    <a class="btn btn-primary-soft" href="<?= $base ?>logout.html">
                        Cerrar sesión
                    </a>
                    <?php else: ?>
                    <a class="btn btn-primary-soft" href="<?= $base ?>login.html">
                        Iniciar sesión
                    </a>
                    <?php endif; ?>
                </li>
            </ul>
        </div>
    </div>
</nav>
<?php require_once __DIR__.'/auth.php'; echo flash_render(); ?>
