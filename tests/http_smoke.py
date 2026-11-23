#!/usr/bin/env python3
"""Pruebas HTTP reales sin base de datos. Servidor: php -S 127.0.0.1:8765 -t public"""
import re
import sys
import urllib.error
import urllib.request
from http.cookiejar import CookieJar

BASE = sys.argv[1].rstrip('/') if len(sys.argv)>1 else 'http://127.0.0.1:8765'
client=urllib.request.build_opener(urllib.request.HTTPCookieProcessor(CookieJar()))

def do(path, data=None, *, method=None):
    payload = data.encode() if data is not None else None
    request=urllib.request.Request(BASE+path, data=payload, method=method or ('POST' if data is not None else 'GET'))
    if data is not None:
        request.add_header('Content-Type','application/x-www-form-urlencoded')
    try:
        r=client.open(request, timeout=5)
    except urllib.error.HTTPError as ex:
        r=ex
    return r.status, r.geturl(), r.read().decode('utf-8', errors='replace'), r.headers

checks=[]
def test(name, cond):
    checks.append((name,cond))
    print(('OK' if cond else 'FALLO')+' '+name)

status,url,html,headers=do('/')
test('Portada redirige al inicio de sesión', status == 200 and url.endswith('/login.php'))
test('Encabezados de seguridad presentes', headers.get('X-Content-Type-Options') == 'nosniff' and "default-src 'self'" in headers.get('Content-Security-Policy',''))
test('Formulario login renderiza etiquetas', 'name="correo"' in html and 'name="password"' in html and 'Iniciar sesión' in html)
login_token=re.search(r'name="csrf_token" value="([a-f0-9]{64})"',html)
test('Formulario login expone token CSRF', login_token is not None)
status,url,html,headers=do('/dashboard.php')
test('Panel sin sesión redirige a login', status == 200 and 'login.php?acceso=1' in url and 'Inicia sesión' in html)
for path in ['/contacto_nuevo.php', '/contacto_editar.php?id=1', '/contacto_eliminar.php?id=1', '/contacto_favorito.php']:
    status,url,html,headers=do(path)
    test('Vista privada denegada: '+path, status == 200 and 'login.php?acceso=1' in url)
status,url,html,headers=do('/registro.php')
test('Formulario registro carga correctamente', status == 200 and 'Crear una cuenta' in html)
reg_token=re.search(r'name="csrf_token" value="([a-f0-9]{64})"',html)
test('Registro incluye token CSRF', reg_token is not None)
status,url,html,headers=do('/registro.php', 'nombre=Anonimo&correo=anonimo%40test.com&password=12345678&confirmacion=12345678')
test('POST sin token CSRF retorna 403', status == 403 and 'solicitud no pudo validarse' in html)
status,url,html,headers=do('/registro.php', 'nombre=A&correo=no-es-correo&password=123&confirmacion=456&csrf_token='+reg_token.group(1))
test('Registro con campos inválidos muestra errores de servidor', status == 200 and 'nombre debe contener' in html and 'contraseñas no coinciden' in html)
status,url,html,headers=do('/login.php', 'correo=INVALIDO&password=INVALIDO&csrf_token='+login_token.group(1))
test('Login con correo inválido no usa base de datos', status == 200 and 'correo y una contraseña válidos' in html)
status,url,html,headers=do('/logout.php')
test('Logout sin sesión no ejecuta acción', status == 200 and 'login.php?acceso=1' in url)
status,url,html,headers=do('/assets/css/styles.css')
test('Hoja de estilos accesible', status == 200 and '.auth-layout' in html)
print(f'\nRESULTADO: {sum(x for _,x in checks)}/{len(checks)} pruebas HTTP superadas.')
sys.exit(0 if all(x for _,x in checks) else 1)
