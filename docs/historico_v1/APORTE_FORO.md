# Aporte para publicar en el foro

Estimado docente y compañeros:

Comparto el desarrollo de mi proyecto **Agenda Personal de Contactos con Autenticación en PHP**, realizado utilizando PHP, MySQL, HTML y CSS. La aplicación permite registrar usuarios, iniciar y cerrar sesión, y administrar contactos mediante las operaciones de creación, consulta, edición y eliminación.

Para proteger la información implementé `password_hash()` al registrar las contraseñas y `password_verify()` para la autenticación. El acceso a las páginas privadas se controla mediante sesiones. Además, cada consulta, modificación o eliminación de contactos se restringe al `id_usuario` de la sesión, de manera que ningún usuario pueda acceder a los contactos de otra persona modificando manualmente la dirección URL. Incorporé consultas preparadas, validaciones en el servidor y confirmación previa a la eliminación.

Este proyecto responde al enfoque de **programación orientada a eventos** porque cada acción del usuario (enviar el formulario de registro, iniciar sesión o pulsar guardar, editar y eliminar) desencadena un procedimiento PHP. A diferencia de un programa de línea de comandos que sigue una secuencia iniciada desde la consola, la aplicación web espera solicitudes originadas por la interacción con sus formularios y enlaces.

El repositorio incluye el código fuente, el script SQL para crear las dos tablas relacionadas y el archivo README con los pasos necesarios para instalar la aplicación.

**Repositorio público:** [colocar enlace real una vez publicado]

**Video de demostración (máximo 3 minutos):** [colocar enlace real una vez grabado y subido]

Agradezco sus observaciones y sugerencias para seguir mejorando el proyecto.

> Antes de publicar, reemplaza ambos marcadores por enlaces reales, confirma que el repositorio sea público y que el video abra con los permisos adecuados.
