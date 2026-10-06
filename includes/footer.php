<footer class="nh-footer mt-5">
    <div class="container py-4">
        <div class="row g-4">
            <div class="col-md-5">
                <h5>
                    🐾 NutriHuella
                </h5>
                <p>
                    Una guía digital para acompañar la transición hacia una alimentación más saludable para perros y gatos.
                </p>
            </div>
            <div class="col-md-3">
                <h6>
                    Explora
                </h6>
                <a href="<?= $base ?>nutricion/recetas.html">
                    Recetas
                </a>
                <a href="<?= $base ?>seguimiento/transicion.html">
                    Transición
                </a>
                <a href="<?= $base ?>educativo/faq.html">
                    Preguntas frecuentes
                </a>
            </div>
            <div class="col-md-4">
                <h6>
                    Importante
                </h6>
                <p class="small">
                    La información del proyecto es educativa y no reemplaza la valoración de un médico veterinario.
                </p>
            </div>
        </div>
        <hr>
        <div class="small text-center">
            © <?= date('Y') ?> NutriHuella · Proyecto académico
        </div>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= $base ?>assets/js/app.js?v=<?= @filemtime(__DIR__.'/../assets/js/app.js') ?>"></script>
</body>
</html>
