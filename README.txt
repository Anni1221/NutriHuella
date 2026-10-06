NUTRIHUELLA
===========
Funcionan con tu base de datos MySQL (PDO): registro, login/logout con sesiones,
registro/edición/perfil de mascotas (varias por usuario), calculadora nutricional,
panel con datos reales y registro automático del historial de peso.

INICIO (WAMP):
1. En phpMyAdmin importa TU script de la base (crea la base nutrihuella y sus tablas).
2. Copia la carpeta NutriHuella en C:\wamp64\www\ e inicia WAMP (MySQL en verde).
3. Abre http://localhost/NutriHuella/ -> Regístrate.
   Si falla la conexión, la página muestra el error exacto. Usuario/clave en includes/conexion.php
   (por defecto root sin contraseña). Si ya tenías la base creada con una versión anterior,
   no la importes de nuevo: ejecuta database/migracion.sql una sola vez.

AUTOMÁTICO AL REGISTRAR UNA MASCOTA (no se piden):
- Tamaño (pequeño/mediano/grande/gigante): según la estatura en cm (del piso al hombro).
- Etapa de vida: según la edad en años, el tipo y el tamaño (perros grandes/gigantes son senior antes).
- Nivel de actividad: según la raza (si está en la lista) o moderado; los senior pasan a bajo.
- Condición corporal: peso actual vs. peso ideal de la raza (o del tamaño si es criollo/mestizo):
  bajo peso / peso ideal / sobrepeso. Los cachorros no se evalúan (están creciendo).
Las razas y rangos están en includes/perfil_mascota.php (se pueden ampliar).

PÁGINAS CON DATOS REALES (nada inventado):
- Peso: historial de la mascota (tabla registros_peso); el peso actual es siempre la última medición.
- Progreso: peso inicial/actual, variación, tendencia, gráfica y distancia al rango ideal.
- Transición: plan de 4 semanas por mascota (tablas transiciones y transicion_etapas) con las
  raciones de cada semana calculadas según la energía diaria de la mascota.
- Recetas: solo las de su especie y con aviso si contienen algo que registraste como alergia.
- Recetas, artículos y FAQ se leen de la BD; la primera vez se cargan los textos de ejemplo.
- Panel admin (rol admin): resumen real, usuarios, mascotas, recetas y artículos.
  Para ser admin ejecuta database/hacer_admin.sql con tu correo.
- La estatura (cm) define el tamaño: perro <38 pequeño, <55 mediano, <68 grande, más gigante;
  gato <23 pequeño, <28 mediano, más grande.

CÁLCULO: RER = 70 x peso^0.75 ; DER = RER x factor (especie, etapa, esterilización,
actividad, condición corporal). Gramos = DER / kcal del alimento x 100.
Es orientativo y no reemplaza al veterinario.

NOTA: database/nutrihuella.sql contiene solo las tablas y columnas que la app usa.
