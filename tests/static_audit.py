#!/usr/bin/env python3
"""Auditoría de coherencia de PHP, esquema, seguridad, documentación y preparación hosting."""
from pathlib import Path
import re, subprocess, sys
root=Path(__file__).resolve().parents[1]
checks=[]
def check(msg,result):
    checks.append(bool(result));print(('OK' if result else 'FALLO')+' '+msg)
files=['public/index.php','public/login.php','public/registro.php','public/logout.php','public/dashboard.php',
 'public/contacto_nuevo.php','public/contacto_editar.php','public/contacto_eliminar.php','public/contacto_favorito.php',
 'templates/contact_form.php','src/contacts.php','src/auth.php','src/security.php','src/validators.php',
 'database/agenda_personal_v2_hosting.sql','database/agenda_personal_v2_local.sql',
 'database/migracion_v1_a_v2_hosting.sql','database/migracion_v1_a_v2_local.sql',
 'README.md','docs/DESPLIEGUE_INFINITYFREE.md','docs/PRUEBAS_V2.md','.htaccess']
check('Archivos obligatorios V2',all((root/file).is_file() for file in files))
for file in sorted(root.rglob('*.php')):
    result=subprocess.run(['php','-l',str(file)],capture_output=True,text=True)
    check('PHP válido '+str(file.relative_to(root)),result.returncode==0)
sql=(root/'database/agenda_personal_v2_hosting.sql').read_text()
local=(root/'database/agenda_personal_v2_local.sql').read_text()
mig=(root/'database/migracion_v1_a_v2_hosting.sql').read_text()
check('Base hosting sin CREATE DATABASE ni USE',not re.search(r'\b(CREATE DATABASE|USE agenda_personal)\b',sql,re.I))
check('Base local incluye creación y selección', 'CREATE DATABASE IF NOT EXISTS agenda_personal' in local and 'USE agenda_personal' in local)
check('Esquema hosting idéntico al local', local.strip().endswith(sql.strip()))
for table in ['usuarios','contactos','categorias']:
    check('Tabla '+table+' en esquema',bool(re.search(r'CREATE TABLE IF NOT EXISTS '+table+r'\b',sql,re.I)))
for col in ['id_usuario','id_categoria','favorito','fecha_creacion','nombre','correo','ciudad']:
    check('Columnas de contactos '+col,col in sql)
check('Cinco categorías correctas',all("'"+x+"'" in sql for x in ['Familia','Amigos','Trabajo','Universidad','Otro']))
check('FK usuario y categoría',all(s in sql for s in ['FOREIGN KEY (id_usuario)','FOREIGN KEY (id_categoria)','REFERENCES usuarios(id)','REFERENCES categorias(id)']))
check('Migración preserva usuarios/contactos',not re.search(r'\bDROP\b|\bTRUNCATE\b',mig,re.I) and 'UPDATE contactos SET id_categoria = 5' in mig)
check('Migración usa ALTER contactos', 'ALTER TABLE contactos' in mig)
check('No se incluyen credenciales locales',not (root/'config/config.php').exists() and '/config/config.php' in (root/'.gitignore').read_text())
contact=(root/'src/contacts.php').read_text()
for action in ['list_contacts','contact_counts','find_contact','add_contact','edit_contact','set_contact_favorite','remove_contact','list_categories']:
    check('Operación '+action,'function '+action+'(' in contact)
check('Todas las consultas de contactos incorporan propietario', contact.count('WHERE c.id_usuario = :usuario') >= 1 and contact.count('id_usuario = :usuario') >= 6 and 'INSERT INTO contactos (id_usuario,' in contact)
check('Orden SQL lista blanca',"$sortColumns = ['nombre' => 'c.nombre', 'ciudad' => 'c.ciudad', 'fecha' => 'c.fecha_creacion']" in contact)
check('Búsqueda parametrizada y escapada', 'LIKE :q_nombre' in contact and "['!', '%', '_']" in contact and ":q_correo" in contact and ":q_ciudad" in contact)
check('Filtrado categorías/favoritos', 'c.id_categoria = :categoria' in contact and 'c.favorito = 1' in contact)
check('Formulario no acepta ID propietario',"form_text('id_usuario')" not in (root/'public/contacto_nuevo.php').read_text())
check('Registro y sesión protegidos', 'password_hash(' in (root/'public/registro.php').read_text() and 'password_verify(' in (root/'src/auth.php').read_text())
for page in ['dashboard','contacto_nuevo','contacto_editar','contacto_eliminar','contacto_favorito','logout']:
    check('Acceso protegido '+page,'require_login();' in (root/'public'/f'{page}.php').read_text())
for page in ['registro','login','logout','contacto_nuevo','contacto_editar','contacto_eliminar','contacto_favorito']:
    check('POST protegido '+page,'verify_csrf();' in (root/'public'/f'{page}.php').read_text())
check('Endpoint favoritos únicamente POST', 'if (!is_post())' in (root/'public/contacto_favorito.php').read_text())
check('Confirmación antes de borrar', '¿Eliminar este contacto?' in (root/'public/contacto_eliminar.php').read_text())
for folder in ['src','config','templates','database']:
    check('Directorio '+folder+' bloqueado','Require all denied' in (root/folder/'.htaccess').read_text())
ht=(root/'.htaccess').read_text()
check('Rewrite a public y denegación directorios', 'RewriteRule ^$ public/index.php' in ht and 'RewriteRule ^(config|src|templates|database|tests|docs)' in ht)
check('CSS equilibrado', (root/'public/assets/css/styles.css').read_text().count('{')==(root/'public/assets/css/styles.css').read_text().count('}'))
readme=(root/'README.md').read_text()
check('README con URL plantilla y capturas', 'https://TU-SUBDOMINIO' in readme and 'capturas' in readme.lower())
check('README ruta local e instrucciones deploy','agenda_personal_v2_local.sql' in readme and 'InfinityFree' in readme)
# Validación cruzada específica: la migración debe reproducir el esquema V2.
old=(root/'database/agenda_personal_v1_referencia_NO_IMPORTAR.sql').read_text()
full=(root/'database/agenda_personal_v2_hosting.sql').read_text()
migration=(root/'database/migracion_v1_a_v2_hosting.sql').read_text()
for col in ['id_categoria','favorito','fecha_creacion']:
    check('Migración incorpora campo nuevo '+col, bool(re.search(r'ADD COLUMN\s+'+col+r'\b',migration)) and bool(re.search(r'\b'+col+r'\s+(INT|TINYINT|TIMESTAMP)',full)))
for name in ['idx_contactos_usuario_ciudad','idx_contactos_usuario_favorito','idx_contactos_usuario_fecha','idx_contactos_categoria']:
    check('Índice consistente '+name,name in full and name in migration)
check('Aísla clave externa de categoría en migración', 'ADD CONSTRAINT fk_contactos_categoria' in migration and 'FOREIGN KEY (id_categoria)' in full)
check('No elimina contactos de V1',not re.search(r'\bDELETE\s+FROM\s+contactos\b',migration,re.I))
check('Esquema V1 contiene columnas heredadas',all(re.search(r'\b'+col+r'\s+(INT|VARCHAR)',old) for col in ['id_usuario','nombre','correo','telefono','ciudad']))
check('Migración local selecciona BD existente', 'USE agenda_personal;' in (root/'database/migracion_v1_a_v2_local.sql').read_text())
print(f'\nRESULTADO: {sum(checks)}/{len(checks)} verificaciones estáticas superadas.')
sys.exit(0 if all(checks) else 1)
