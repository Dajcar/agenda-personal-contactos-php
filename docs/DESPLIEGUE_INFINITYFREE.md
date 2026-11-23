# Guía: despliegue gratuito en InfinityFree (APE B1)

Esta guía evita los dos errores más comunes: importar `CREATE DATABASE` en un hosting que no lo permite y subir solo `public/` dejando el código PHP privado fuera de `htdocs`.

## Preparación

1. En XAMPP, importa `database/agenda_personal_v2_local.sql` **solo si no tienes datos previos**. Si ya existe V1, respalda y usa `database/migracion_v1_a_v2_local.sql` **una sola vez**.
2. Crea dos cuentas de prueba. Registra, edita, elimina, busca, ordena, clasifica y alterna favoritos. Comprueba acceso cruzado prohibido.
3. Comprueba `php tests/mysql_integration.php` desde una terminal con PHP y extensión `pdo_mysql` habilitada. Si ejecutas MySQL en el puerto 3307, cambia `port` también en `config/config.php`.
4. Conserva una copia de seguridad SQL con fecha y sin contraseñas en texto plano.

## Panel de InfinityFree

1. Regístrate en https://www.infinityfree.com/ y crea una cuenta de hosting y subdominio gratuito.
2. Anota la URL **HTTPS exacta** que asigne el panel. No uses el ejemplo `TU-SUBDOMINIO` como URL real.
3. Abre `MySQL Databases` y crea una base de datos. **El nombre puede tener prefijo asignado por InfinityFree.** Anota host SQL, nombre real, usuario y contraseña. NO uses `localhost` si el proveedor muestra `sql...`.
4. Accede a `phpMyAdmin` desde la base correcta y haz clic en **Importar**. Para una instalación vacía, selecciona `database/agenda_personal_v2_hosting.sql`. Este script no contiene `CREATE DATABASE`/`USE`. Para una aplicación V1 ya existente, exporta respaldo y usa `database/migracion_v1_a_v2_hosting.sql` exactamente una vez.
5. Revisa en phpMyAdmin que haya tres tablas (`usuarios`, `contactos`, `categorias`), cinco categorías y claves externas. Nunca importes ambas variantes (local y hosting) en la misma instalación.

## Archivos PHP

1. Descomprime `Agenda_Contactos_V2_Hosting_InfinityFree.zip` en la computadora; dentro hay un directorio `htdocs/`.
2. Mediante File Manager o FTP del proveedor, entra al directorio remoto `htdocs` de TU dominio. Sube **el contenido** del directorio local `htdocs/`, de modo que aparezcan directamente en el remoto: `.htaccess`, `public/`, `config/`, `src/`, `templates/`.
3. No olvides la extensión de archivo exacta `.htaccess`; habilita archivos ocultos si tu administrador los esconde. El archivo usa `RewriteEngine` y prohíbe acceso externo a `config`, `src`, `templates` y archivos sensibles. Las carpetas internas también llevan un `.htaccess` con `Require all denied`.
4. Desde File Manager, copia `config/config.example.php` a `config/config.php` y cambia **todos** los parámetros SQL por los valores del proveedor:

```php
return [
    'host' => 'HOST_SQL_QUE_INDICA_TU_PANEL',
    'port' => 3306,
    'database' => 'NOMBRE_EXACTO_ASIGNADO',
    'username' => 'USUARIO_SQL_ASIGNADO',
    'password' => 'CONTRASENA_SQL_PRIVADA',
];
```

No compartas esos datos, no los adjuntes en capturas y nunca hagas commit de `config.php`.

5. Si existe un `index.html` de muestra, elimínalo para que no impida que aparezca PHP. Entra a tu URL pública. **No uses una carpeta extra `agenda_personal_php` dentro de `htdocs`**, salvo que quieras una URL con subruta.

## Pruebas online obligatorias

- [ ] Abre la URL HTTPS sin iniciar sesión: debe mostrar login.
- [ ] Registra usuario A e inicia sesión; registra usuario B diferente.
- [ ] Usuario A crea contactos con cada categoría y favoritos.
- [ ] Busca por nombre, correo y ciudad; prueba un texto no coincidente.
- [ ] Ordena por nombre y ciudad asc/desc, y por fecha.
- [ ] Filtra por categoría y favoritos, y combina los filtros.
- [ ] Edita un contacto (nombre, ciudad, categoría, favorito); comprueba los cambios.
- [ ] Desmarca favorito desde su tarjeta.
- [ ] Elimina un contacto tras confirmar; verifica que desaparezca.
- [ ] Cierra sesión y comprueba redirección del panel privado a login.
- [ ] Con usuario B intenta abrir la URL de edición/eliminación del contacto A: debe responder 404.
- [ ] Comprueba `https://TUDOMINIO/config/config.php`: debe devolver acceso prohibido (403); no debe mostrar credenciales.
- [ ] Toma **capturas verdaderas** de los resultados, preferentemente sin datos personales.

## Errores frecuentes y correcciones

- **HTTP 403 al inicio**: comprueba `.htaccess` en raíz y carpetas dentro de `htdocs`; revisa permisos y que existe `public/index.php`. Si el sitio no soporta RewriteRule, usa un hosting Apache/PHP que sí lo soporte.
- **Parece el sitio por defecto**: elimina el `index.html` predeterminado que tiene prioridad sobre PHP.
- **Error de conexión SQL**: usa host, nombre y usuario exactos del panel; suelen ser diferentes de los locales (`127.0.0.1`, `agenda_personal`, `root`). Revisa `pdo_mysql`.
- **SQL #1044/1045 o CREATE DATABASE prohibido**: usaste el archivo local en hosting. Importa el archivo **hosting** dentro de la base creada previamente desde el panel.
- **Tabla o campo no encontrado**: no se ejecutó el SQL correcto. El esquema V2 añade `categorias`, `contactos.id_categoria`, `contactos.favorito`, `contactos.fecha_creacion`.
- **MySQL de XAMPP puerto 3306 ocupado**: con CMD administrador, `netstat -ano | findstr :3306`; identifica el PID mediante `tasklist /FI "PID eq NUMERO"`. Puedes detener otro servicio MySQL si lo conoces o configurar otro puerto (p.ej. 3307) en XAMPP, phpMyAdmin y `config.php`.
- **Falta la URL pública para entregar**: se obtiene únicamente al crear el sitio en tu cuenta; no se puede inventar.

## GitHub

1. Crea o reutiliza tu repositorio GitHub del primer foro. Sube **el contenido de `agenda_personal_php/`**, sin `config/config.php`, sin datos personales ni ZIP del hosting.
2. Añade `docs/capturas/*.png` de páginas reales, reemplaza el marcador de URL en `README.md`, confirma la documentación de las mejoras y del SQL.
3. Comparte el enlace a la raíz del repositorio y el enlace web HTTPS en tu aula virtual. Se proporciona una plantilla en `docs/TEXTO_ENTREGA_APEB1.md`.

**Fuentes de consulta:** https://www.infinityfree.com/ ; https://forum.infinityfree.com/t/change-document-root-to-htdocs-public/66265 ; https://forum.infinityfree.com/t/unable-to-rename-database-or-create-a-new-one/80054 .
