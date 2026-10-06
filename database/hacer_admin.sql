-- Convierte una cuenta ya registrada en administrador (cambia el correo por el tuyo).
-- Luego aparece el enlace "Admin" en el menú.
UPDATE usuarios SET rol = 'admin' WHERE correo = 'tucorreo@ejemplo.com';
