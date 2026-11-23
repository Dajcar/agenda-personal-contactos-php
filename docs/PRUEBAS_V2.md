# Resultados y límites de verificación — versión 2.0

Hay cuatro niveles de pruebas; cada uno cubre un riesgo distinto:

1. `php tests/unit.php`: usa un **PDO simulado**, no una base real. Prueba validaciones, sesiones, seguridad, consultas de propietario, búsqueda, orden, categorías, favoritos y CRUD. Un resultado OK **no equivale** a conexión MySQL real.
2. `python tests/static_audit.py`: verifica estructura del proyecto, SQL, migración, whitelist SQL, condiciones de propietario, configuraciones protegidas, documentación, sintaxis PHP. No ejecuta SQL.
3. `python tests/http_smoke.py`: requiere ejecutar `php -S 127.0.0.1:8765 -t public`. Comprueba redirecciones sin sesión, CSRF, formularios, CSS y cabeceras sin base real.
4. `php tests/form_render.php`: renderiza el formulario real PHP y verifica las cinco categorías, el favorito, CSRF y escapado de HTML, sin conectar a MySQL.
5. `php tests/mysql_integration.php`: **prueba de integración REAL** usando la base configurada en `config/config.php`, la versión de esquema V2 y una transacción con ROLLBACK. Requiere acceso MySQL, extensión `pdo_mysql` y ejecutar la importación del esquema. Si falta, saldrá con código 2 «NO EJECUTADO». Las operaciones de registro/CRUD/filtros en el navegador también deben probarse en XAMPP e InfinityFree.

## Matriz cruzada entre consigna y código

| Requisito | Archivo(s) | Verificación |
|---|---|---|
| Búsqueda por nombre/correo/ciudad | `src/contacts.php`, `public/dashboard.php` | Filtro LIKE parametrizado + unit/integración |
| Orden por nombre/ciudad/fecha | `src/contacts.php`, `public/dashboard.php` | Lista blanca columna/dirección + unit/integración |
| Categorías fijas | `database/agenda_personal_v2_*.sql`, `templates/contact_form.php` | FK y validación servidor + unit/integración |
| Favoritos y filtro | `public/contacto_favorito.php`, `src/contacts.php`, `dashboard.php` | POST + CSRF + propietario + unit/integración |
| Autenticación y sesiones | `src/auth.php`, `src/security.php`, `registro.php`, `login.php`, `logout.php` | password_hash/verify + unit + HTTP |
| Restringir acceso a contactos ajenos | `src/contacts.php`, rutas privadas | WHERE id_usuario en todos los accesos + unit/integración |
| Hosting gratuito accesible | paquete de `htdocs`, `docs/DESPLIEGUE_INFINITYFREE.md` | **Pendiente de publicación y prueba real online** |
| GitHub, README, capturas, URL | `README.md`, `docs/capturas` | **Pendiente de repositorio y URL reales** |

**Nunca se deben atribuir al proyecto pruebas MySQL u online que no hayan sido ejecutadas.**

## Resultados de ejecución en el entorno de preparación

- `tests/unit.php`: **107/107** pruebas superadas (PDO simulado).
- `tests/static_audit.py`: verificar los resultados actuales ejecutando el script (estructura, sintaxis, consistencia).
- `tests/http_smoke.py`: **16/16** pruebas HTTP sin autenticación superadas.
- `tests/form_render.php`: **8/8** verificaciones de formulario superadas.
- `tests/mysql_integration.php`: **NO EJECUTADO** (falta el controlador `pdo_mysql` en este entorno). Código de salida `2`; no hay afirmaciones de éxito para la base real.
- Despliegue en InfinityFree y repositorio público: **pendientes** de crear en la cuenta del estudiante.
