# Plan y registro de pruebas — Agenda Personal

## 1. Alcance y condiciones

La validación está separada en tres niveles para evitar confundir una simulación con una instalación real:

- **Estática:** `php -l` en los archivos PHP, revisión de SQL/README y restricciones de acceso.
- **Unitarias:** `php tests/unit.php`. Emplea un **PDO simulado**, con dos propietarios distintos, para comprobar la lógica PHP, el hash y el alcance de las consultas. **No ejecuta MySQL.**
- **Integración real:** la siguiente matriz debe realizarse con PHP + extensión `pdo_mysql` y MySQL/MariaDB activos. No se debe marcar como aprobada sin ejecutar cada caso.

## 2. Matriz de casos para comprobar en XAMPP

| ID | Prueba / procedimiento | Resultado esperado |
| --- | --- | --- |
| P01 | Ejecutar `agenda_personal.sql` en phpMyAdmin | Existen tablas `usuarios`, `contactos`, FK `contactos.id_usuario` y correo de usuario único |
| P02 | Registrar usuario A con datos válidos | Cuenta creada; tabla `usuarios.password` contiene un **hash**, no el texto original |
| P03 | Intentar registrar el correo de A otra vez | Mensaje de correo ya registrado; no se crea duplicado |
| P04 | Registro con datos inválidos, nombre vacío o contraseñas distintas | Se muestran errores y no se crea cuenta |
| P05 | Iniciar sesión con contraseña equivocada | Mensaje genérico; no se crea sesión autenticada |
| P06 | Iniciar sesión con credenciales correctas | Panel muestra el nombre y correo del usuario A |
| P07 | Acceder directamente a `dashboard.php` sin sesión | Redirige a `login.php?acceso=1` |
| P08 | Crear contacto X completo con usuario A | Se muestra en el panel; pertenece a A |
| P09 | Registrar contacto con teléfono o correo incorrectos | Errores del servidor; no se almacena |
| P10 | Editar contacto X con A | Datos actualizados en el panel |
| P11 | Eliminar contacto X desde el panel | Se abre **página de confirmación** antes de borrar |
| P12 | Confirmar eliminación con A | Contacto X deja de existir y aparece mensaje de éxito |
| P13 | Cerrar sesión | Se invalida sesión y se redirige al login; volver al panel exige autenticación |
| P14 | Crear usuario B y un contacto Y propio | B ve Y, no los contactos de A; A tampoco ve Y |
| P15 | Con A, intentar `contacto_editar.php?id=ID_DE_Y` | **404**; no se expone información de Y |
| P16 | Con A, intentar `contacto_eliminar.php?id=ID_DE_Y` | **404**; no se expone ni elimina Y |
| P17 | Enviar POST de edición/borrado sin token CSRF | **403**, operación denegada |
| P18 | Modificar manualmente la URL con `id=1 OR 1=1` | **404** por ID inválido |
| P19 | Ingresar `<script>alert(1)</script>` como nombre de contacto | El contenido se trata como texto y no se ejecuta JavaScript |
| P20 | Abrir interfaz en computadora y teléfono | Componentes adaptables, sin desplazamiento horizontal principal |

> Para P19, el texto se valida como un nombre con longitud válida y se escapa cuando se representa. Nunca debe interpretarse como código HTML.

## 3. Procedimiento rápido para comprobar aislamiento

1. Con usuario A, crea un contacto y observa su URL de edición: `contacto_editar.php?id=1` (el ID real puede ser distinto).
2. Cierra sesión; registra al usuario B e inicia sesión con sus credenciales.
3. Copia en el navegador la URL del contacto creado por A.
4. Debe aparecer el error **404 Contacto no encontrado**. Ni la edición ni la eliminación permiten saltar esta restricción por URL.
5. Comprueba en phpMyAdmin que el contacto sigue intacto y que su `id_usuario` pertenece a A.

## 4. Validaciones automáticas reproducibles

Desde la raíz del proyecto:

```bash
php tests/unit.php
```

Con MySQL activo y `config/config.php` configurado, ejecuta la verificación automatizada de integración real:

```bash
php tests/mysql_integration.php
```

Esta última prueba crea temporalmente dos usuarios, comprueba hash, correo único, creación, edición, eliminación, autorización y `ON DELETE CASCADE`, y revierte la transacción al terminar.

Con XAMPP encendido puedes repetir el recorrido funcional anterior; registra en una tabla de evidencias fecha, versión de PHP, motor de base de datos, ID de prueba, resultado real y captura.

## 5. Distinción entre pruebas

La simulación de `MemoryPDO` representa los resultados previstos de las consultas y comprueba que los métodos CRUD incluyen `id_usuario`. Para certificar completamente la aplicación antes de la entrega académica, ejecutar obligatoriamente **P01–P20** contra MySQL/MariaDB real. Los scripts de prueba no sustituyen esa integración ni el video solicitado.
