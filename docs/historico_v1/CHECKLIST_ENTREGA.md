# Lista final de entrega según consigna y rúbrica

## Archivos que contiene este paquete

- [x] Carpeta del proyecto PHP completa (`public`, `src`, `templates`, `config`).
- [x] SQL de la base de datos: `database/agenda_personal.sql`.
- [x] README con descripción, tecnologías, requisitos e instalación.
- [x] Seguridad con sesiones, hash y autorización por propietario implementada.
- [x] Script de pruebas unitarias y plan de pruebas de integración.
- [x] Guion detallado de video de máximo 3 minutos.

## Acciones que debe completar el estudiante

- [ ] Instalar y probar en XAMPP con **MySQL real**, ejecutar `php tests/mysql_integration.php` y seguir `docs/PRUEBAS.md`.
- [ ] Crear un repositorio **público** en GitHub, GitLab o Bitbucket y subir el proyecto **sin `config/config.php` ni datos confidenciales**.
- [ ] Grabar un video demostrativo **real** (duración máxima de 3 minutos) donde se vean las seis acciones y se expliquen sesiones, contraseñas y privacidad.
- [ ] Publicar en el foro los enlaces accesibles al repositorio y video, y adjuntar el ZIP/SQL si la plataforma lo permite o lo solicita.

## Cómo crear el repositorio en GitHub

1. En GitHub, pulsa **New repository**, utiliza como nombre sugerido `agenda-personal-php`, selecciona **Public** y crea el repositorio vacío.
2. Abre PowerShell en la carpeta **raíz** del proyecto (no dentro de `public`).
3. Ejecuta estos comandos, sustituyendo `TU_USUARIO` por tu nombre de usuario de GitHub:

```powershell
git init
git add .
git commit -m "Agenda personal PHP MySQL con autenticacion y CRUD"
git branch -M main
git remote add origin https://github.com/TU_USUARIO/agenda-personal-php.git
git push -u origin main
```

4. Abre el repositorio en modo incógnito para confirmar que sea **público** y que el README y `database/agenda_personal.sql` sean visibles.
5. Revisa que **NO** aparezca `config/config.php`; para ejecutarlo cada desarrollador copiará `config.example.php`.

## Puntuación de la rúbrica

| Criterio de excelencia | Valor | Evidencia |
| --- | ---: | --- |
| Autenticación y gestión de sesiones | 2 | `registro.php`, `login.php`, `logout.php`, `src/auth.php`, `src/security.php` |
| CRUD de contactos privado | 2 | `dashboard.php`, `contacto_*.php`, `src/contacts.php` |
| Calidad técnica | 2 | Separación de carpetas, consultas preparadas, validaciones, pruebas |
| Video de presentación | 2 | **Por grabar** con el guion proporcionado |
| Entrega y repositorio | 2 | Código + SQL + README incluidos; **repositorio público por crear** |
| **Total posible** | **10** | Depende de que los pasos pendientes se ejecuten correctamente |

**Nota:** Que una función esté implementada no significa que haya sido verificada contra MySQL real. La nota excelente depende de la evaluación del docente y de completar la grabación y publicación.
