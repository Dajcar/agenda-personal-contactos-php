# Guion del video de presentación (máximo 3 minutos)

**Duración recomendada:** 2 minutos y 45 segundos. **Modalidad:** grabación de pantalla real, con narración del estudiante y una instancia PHP/MySQL funcional. Antes de iniciar, comprueba Apache/MySQL y la base de datos; deja listas dos cuentas si deseas la prueba adicional de aislamiento.

| Tiempo | Pantalla / acción | Narración sugerida |
| --- | --- | --- |
| 0:00–0:15 | Abrir aplicación en navegador | «Presento mi proyecto Agenda Personal de Contactos, desarrollado en PHP, MySQL, HTML y CSS. Permite administrar una agenda privada mediante autenticación». |
| 0:15–0:40 | Ir a **Crear cuenta**; registrar un usuario nuevo | «Registro un usuario con nombre, correo y contraseña. El sistema valida los datos y evita correos duplicados». |
| 0:40–0:58 | Iniciar sesión con usuario creado | «El sistema consulta las credenciales, verifica la contraseña con `password_verify()` y crea una sesión segura». |
| 0:58–1:15 | Mostrar panel, nombre, correo y listado | «En el panel aparece mi nombre, mi correo y únicamente mis contactos». |
| 1:15–1:38 | **Nuevo contacto**; registrar nombre, teléfono, correo y ciudad | «Agrego un contacto. Todos los campos se validan en el servidor y el registro queda vinculado a mi ID de usuario». |
| 1:38–1:58 | **Editar** contacto; modificar ciudad o teléfono y guardar | «Puedo actualizar la información de mis contactos sin afectar a los demás usuarios». |
| 1:58–2:17 | **Eliminar**; enseñar confirmación y aceptar | «Antes de eliminar se solicita una confirmación. La operación utiliza un token CSRF». |
| 2:17–2:39 | Mostrar brevemente código `auth.php`, `registro.php` y `contacts.php` | «Las contraseñas se almacenan como hashes mediante `password_hash()`; se verifican con `password_verify()`. Al iniciar sesión se regenera el ID de sesión y las páginas privadas verifican que el usuario esté autenticado. Las consultas SQL preparadas filtran siempre por `id_usuario`, por lo que cambiar el ID en la URL no permite ver, editar ni eliminar contactos ajenos». |
| 2:39–2:50 | Pulsar **Cerrar sesión** e intentar volver al panel | «Al cerrar sesión se elimina la sesión del usuario y se bloquea el acceso al panel». |
| 2:50–2:55 | Mostrar README y archivo SQL, si hay tiempo | «El repositorio incluye código, script SQL y README con instrucciones de instalación». |

## Antes de grabar

- Configura las ventanas para que la tipografía sea legible y no aparezcan contraseñas reales ni datos personales de terceros. Utiliza correos y contactos de demostración.
- Ensaya registrar un usuario nuevo para evitar repetir datos existentes.
- Usa Windows **Recortes / Herramienta de grabación** o **OBS Studio** para registrar pantalla y micrófono; exporta en MP4.
- Comprueba que aparezcan claramente **registro, inicio, creación, edición, eliminación y cierre de sesión**.
- Si llegas a 3 minutos, corta silencios o transiciones; no omitas los controles de seguridad.
- Sube el video donde indique el docente. Si pide enlace, verifica que el docente tenga permiso para abrirlo.

> Este archivo es el **guion**, no es un video ya grabado. La grabación debe mostrar acciones reales en el navegador con el sistema operativo.
