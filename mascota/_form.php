<?php
    // Formulario de mascota. Espera $m (datos) y $errores. Etapa, actividad y condición
    // corporal NO se piden: se calculan con includes/perfil_mascota.php.
    $sel = fn($campo, $val) => ($m[$campo] ?? '') === $val ? 'selected' : '';
?>
<?php if (!empty($errores)): ?>
    <div class="alert alert-danger"><ul class="mb-0">
        <?php foreach ($errores as $er): ?><li><?= e($er) ?></li><?php endforeach; ?>
    </ul></div>
<?php endif; ?>
<?= csrf_field() ?>
<div class="row g-4">
    <div class="col-md-6">
        <label class="form-label fw-bold">Nombre</label>
        <input name="nombre" class="form-control" placeholder="Ej. Luna" value="<?= e($m['nombre'] ?? '') ?>" required maxlength="80">
    </div>
    <div class="col-md-6">
        <label class="form-label fw-bold">Tipo</label>
        <select name="tipo" class="form-select">
            <option value="perro" <?= $sel('tipo', 'perro') ?>>Perro</option>
            <option value="gato" <?= $sel('tipo', 'gato') ?>>Gato</option>
        </select>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-bold">Raza</label>
        <input name="raza" class="form-control" placeholder="Ej. Labrador, Criollo, Siamés" value="<?= e($m['raza'] ?? '') ?>" maxlength="80">
        <div class="form-text">Si la raza es conocida se usa su peso ideal; si es criollo/mestizo se usa el tamaño calculado.</div>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-bold">Estatura (cm)</label>
        <input name="estatura_cm" type="number" step=".1" min="10" max="120" class="form-control" placeholder="Ej. 45" value="<?= e($m['estatura_cm'] ?? '') ?>" required>
        <div class="form-text">Mide del piso al hombro (parte más alta de la espalda). Con esto se calcula si es pequeño, mediano o grande.</div>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-bold">Edad (años)</label>
        <input name="edad_anios" type="number" step=".1" class="form-control" min="0" max="30" placeholder="Ej. 4 (cachorros: 0.5 = 6 meses)" value="<?= e($m['edad_anios'] ?? '') ?>" required>
        <div class="form-text">Con la edad se calcula su etapa de vida.</div>
    </div>
    <div class="col-md-6">
        <label class="form-label fw-bold">Peso actual (kg)</label>
        <input name="peso_kg" type="number" step=".01" min="0.1" max="120" class="form-control" placeholder="12.4" value="<?= e($m['peso_kg'] ?? '') ?>" required>
        <div class="form-text">Con el peso se evalúa su condición corporal.</div>
    </div>
    <div class="col-12">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="esterilizado" value="1" id="est" <?= !empty($m['esterilizado']) ? 'checked' : '' ?>>
            <label class="form-check-label fw-bold" for="est">Esterilizado / castrado</label>
        </div>
    </div>
    <div class="col-12">
        <label class="form-label fw-bold">Alergias o sensibilidades</label>
        <textarea name="alergias" class="form-control" rows="3" placeholder="Indica alimentos o ingredientes conocidos..."><?= e($m['alergias'] ?? '') ?></textarea>
    </div>
</div>
