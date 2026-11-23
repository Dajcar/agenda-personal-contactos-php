# Resultados de pruebas realizadas durante la preparación del paquete

## Entorno comprobado

- PHP CLI **8.4.24** disponible.
- Servidor HTTP integrado de PHP utilizado para pruebas de rutas y formularios.
- El entorno de ejecución **no dispone de la extensión PDO MySQL ni de un servidor MySQL/MariaDB**, por lo que **no fue posible ejecutar pruebas de integración con base de datos real**.
- El navegador automatizado del entorno rechazó la navegación local por una restricción administrativa; por ello, no se certifica una inspección visual automatizada en Chrome. La interfaz sí se revisó a nivel de código HTML y de sus puntos de ruptura CSS.

## Resultados confirmados

| Tipo | Comando | Resultado |
| --- | --- | --- |
| Sintaxis PHP y consistencia | `python tests/static_audit.py` | **46/46** verificaciones superadas |
| Lógica de seguridad, validación y CRUD con PDO simulado | `php tests/unit.php` | **46/46** pruebas superadas, sin advertencias |
| Pruebas HTTP del servidor PHP local (rutas de invitados, acceso denegado, CSRF, validación) | `python tests/http_smoke.py` con servidor local en puerto 8765 | **15/15** pruebas superadas |
| Integración PHP ↔ MySQL | `php tests/mysql_integration.php` con XAMPP configurado | **No ejecutada**: el entorno actual carece de `pdo_mysql` |
| Validación visual en navegador | Pendiente en XAMPP/Chrome del estudiante | **No ejecutada** |

### Qué cubren y qué no

Las pruebas **no simulan la ejecución de MySQL real**. La simulación PDO verifica la lógica de operaciones y de aislamiento, pero no reemplaza comprobar el `CREATE TABLE`, los índices, `INSERT`, `UPDATE`, `DELETE` y las consultas reales mediante PDO MySQL. Tampoco sustituyen la grabación del video ni la publicación del repositorio.

Para completar la verificación antes de presentar el foro, abre `docs/PRUEBAS.md` y sigue **P01–P20** en XAMPP; guarda las capturas y actualiza esta evidencia con tus resultados. No asumas que estas pruebas están aprobadas antes de ejecutarlas.
