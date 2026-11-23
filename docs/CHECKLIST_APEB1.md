# Lista de entrega — APEB1 30% (rúbrica 10 puntos)

| Criterio | Máximo | Evidencia necesaria | Estado antes de usar tu cuenta |
|---|---:|---|---|
| Cuatro mejoras funcionales | 2.5 | Búsqueda, orden, categorías y favoritos | Código preparado; pruebas unitarias simuladas |
| Despliegue y funcionamiento online | 2.5 | Sitio accesible por URL pública con todo el CRUD | **Pendiente de publicación y verificación** |
| Calidad técnica | 2.5 | Código ordenado, validaciones, consultas seguras, sesiones | Auditoría estática y pruebas locales disponibles |
| Repositorio y documentación | 2.5 | GitHub con código, SQL, README, capturas, URL real | Preparado localmente; falta GitHub, URL y capturas del panel |

## Pasos finales (no omitir)

- [ ] Configurar XAMPP y ejecutar `php tests/mysql_integration.php` con SQL V2.
- [ ] Probar registro y sesión real, CRUD, filtros, favoritos y que no se accede a contactos ajenos.
- [ ] Crear sitio en InfinityFree con HTTPS y base de datos MySQL desde el panel.
- [ ] Importar SQL hosting si es instalación nueva, o migración hosting si ya existe base V1.
- [ ] Subir archivos del ZIP de hosting al `htdocs` real y configurar `config/config.php` sin compartir contraseña.
- [ ] Probar desde internet todos los flujos con dos usuarios.
- [ ] Obtener y guardar la URL pública definitiva (no URL plantilla).
- [ ] Capturar pantallas reales de panel con filtros, formulario con categorías, favoritos y URL HTTPS; subirlas a `docs/capturas/`.
- [ ] Crear/actualizar repositorio GitHub público. Comprobar que `config.php` no esté en el historial ni el directorio visible.
- [ ] Sustituir marcador `TU-SUBDOMINIO` en README y enlaces del aula virtual por las URL REALES.
- [ ] Subir el archivo `database/agenda_personal_v2_hosting.sql` o el de instalación local como script SQL actualizado, según indicaciones docentes.
- [ ] Entregar los tres elementos: URL pública, repositorio GitHub, script SQL actualizado.

**No calificar como completas las comprobaciones en hosting hasta ejecutarlas allí.**
