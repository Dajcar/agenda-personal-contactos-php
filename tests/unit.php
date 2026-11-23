<?php
declare(strict_types=1);
// Suite V2: MODELO PDO SIMULADO. No representa conexión ni ejecución sobre MySQL.
ob_start();
set_error_handler(static function (int $level, string $message): never {
    throw new ErrorException($message, 0, $level);
});
require dirname(__DIR__) . '/src/helpers.php';
require dirname(__DIR__) . '/src/security.php';
require dirname(__DIR__) . '/src/validators.php';

final class MemoryPDO extends PDO
{
    public array $users = [];
    public array $contacts = [];
    public array $prepared = [];
    public array $categories = [
        ['id' => 1, 'nombre' => 'Familia'], ['id' => 2, 'nombre' => 'Amigos'],
        ['id' => 3, 'nombre' => 'Trabajo'], ['id' => 4, 'nombre' => 'Universidad'],
        ['id' => 5, 'nombre' => 'Otro'],
    ];
    private int $nextContactId = 1;
    public function __construct() {}
    public function prepare(string $query, array $options = []): PDOStatement|false
    {
        $this->prepared[] = $query;
        return new MemoryStatement($this, $query);
    }
    public function query(string $query, ?int $fetchMode = null, mixed ...$fetchModeArgs): PDOStatement|false
    {
        $statement = $this->prepare($query);
        $statement->execute();
        return $statement;
    }
    public function queryData(string $query, array $p): array
    {
        if (str_starts_with($query, 'SELECT id, nombre FROM categorias')) return $this->categories;
        if (str_starts_with($query, 'SELECT id, nombre, correo, password FROM usuarios')) {
            foreach ($this->users as $u) if ($u['correo'] === $p['correo']) return [$u];
            return [];
        }
        if (str_starts_with($query, 'UPDATE usuarios SET password')) {
            if (isset($this->users[(int)$p['id']])) $this->users[(int)$p['id']]['password'] = $p['hash'];
            return ['affected' => 1];
        }
        if (str_starts_with($query, 'SELECT COUNT(*) AS total')) {
            $mine = array_filter($this->contacts, fn ($c) => $c['id_usuario'] === (int) $p['usuario']);
            return [['total' => count($mine), 'favoritos' => count(array_filter($mine, fn($c) => $c['favorito'] === 1))]];
        }
        if (str_starts_with($query, 'SELECT id, nombre, telefono')) {
            $items = array_values(array_filter($this->contacts, fn($c) => $c['id_usuario'] === (int)$p['usuario'] && $c['id'] === (int)$p['id']));
            return array_map(fn($c) => array_diff_key($c, ['id_usuario' => true]), $items);
        }
        if (str_starts_with($query, 'SELECT c.id')) {
            $items = array_values(array_filter($this->contacts, function ($c) use ($p, $query) {
                if ($c['id_usuario'] !== (int)$p['usuario']) return false;
                if (isset($p['categoria']) && $c['id_categoria'] !== (int)$p['categoria']) return false;
                if (str_contains($query, 'c.favorito = 1') && $c['favorito'] !== 1) return false;
                if (isset($p['q_nombre'])) {
                    $raw = substr($p['q_nombre'], 1, -1);
                    $search = str_replace(['!!','!%','!_'], ['!', '%', '_'], $raw);
                    if (!str_contains(strtolower($c['nombre']), strtolower($search)) && !str_contains(strtolower($c['correo']), strtolower($search)) && !str_contains(strtolower($c['ciudad']), strtolower($search))) return false;
                }
                return true;
            }));
            $key = str_contains($query,'ORDER BY c.ciudad') ? 'ciudad' : (str_contains($query,'ORDER BY c.fecha_creacion') ? 'fecha_creacion' : 'nombre');
            $dir = str_contains($query, ' DESC, c.id') ? -1 : 1;
            usort($items, fn($a,$b) => $dir * strcmp($a[$key],$b[$key]) ?: $a['id'] <=> $b['id']);
            return array_map(function($c) {
                $c['categoria'] = [1=>'Familia',2=>'Amigos',3=>'Trabajo',4=>'Universidad',5=>'Otro'][$c['id_categoria']];
                unset($c['id_usuario']); return $c;
            },$items);
        }
        if (str_starts_with($query, 'INSERT INTO contactos')) {
            $id = $this->nextContactId++;
            $this->contacts[$id] = [
                'id' => $id, 'id_usuario' => (int)$p['usuario'], 'nombre' => $p['nombre'],
                'telefono' => $p['telefono'], 'correo' => $p['correo'], 'ciudad' => $p['ciudad'],
                'id_categoria' => (int)$p['categoria'], 'favorito' => (int)$p['favorito'],
                'fecha_creacion' => sprintf('2026-11-%02d 10:00:00', $id),
            ];
            return ['affected' => 1];
        }
        if (str_starts_with($query, 'UPDATE contactos SET')) {
            $id = (int)$p['id'];
            if (!isset($this->contacts[$id]) || $this->contacts[$id]['id_usuario'] !== (int)$p['usuario']) return ['affected' => 0];
            if (str_starts_with($query, 'UPDATE contactos SET favorito =')) {
                $this->contacts[$id]['favorito'] = (int)$p['favorito'];
            } else {
                foreach (['nombre', 'telefono', 'correo', 'ciudad'] as $col) $this->contacts[$id][$col] = $p[$col];
                $this->contacts[$id]['id_categoria'] = (int)$p['categoria'];
                $this->contacts[$id]['favorito'] = (int)$p['favorito'];
            }
            return ['affected' => 1];
        }
        if (str_starts_with($query, 'DELETE FROM contactos')) {
            $id = (int)$p['id'];
            if (!isset($this->contacts[$id]) || $this->contacts[$id]['id_usuario'] !== (int)$p['usuario']) return ['affected' => 0];
            unset($this->contacts[$id]);return ['affected' => 1];
        }
        throw new LogicException('SQL no simulado: ' . $query);
    }
}

final class MemoryStatement extends PDOStatement
{
    private array $results = [];
    private int $affected = 0;
    public function __construct(private MemoryPDO $storage, private string $query) {}
    public function execute(?array $params = null): bool
    {
        $data = $this->storage->queryData($this->query, $params ?? []);
        $this->affected = $data['affected'] ?? 0;
        $this->results = isset($data['affected']) ? [] : $data;
        return true;
    }
    public function fetch(int $mode = PDO::FETCH_DEFAULT, int $cursorOrientation = PDO::FETCH_ORI_NEXT, int $cursorOffset = 0): mixed
    { return array_shift($this->results) ?: false; }
    public function fetchAll(int $mode = PDO::FETCH_DEFAULT, mixed ...$args): array
    { return $this->results; }
    public function rowCount(): int { return $this->affected; }
}
$memory = new MemoryPDO();
function db(): PDO { global $memory; return $memory; }
require dirname(__DIR__) . '/src/auth.php';
require dirname(__DIR__) . '/src/contacts.php';
$total = 0;
function test(string $message, bool $ok): void
{
    global $total;
    $total++;
    if (!$ok) {fwrite(STDERR, "FALLO: $message\n");exit(1);}
    echo "OK $message\n";
}
$reg = ['nombre' => 'Maria Torres','correo'=>'maria@test.com','password'=>'ClaveSegura123','confirmacion'=>'ClaveSegura123'];
test('Registro válido',validate_registration($reg)===[]);
test('Registro rechaza correo erróneo',isset(validate_registration(array_replace($reg,['correo'=>'error']))['correo']));
test('Registro rechaza contraseña corta',isset(validate_registration(array_replace($reg,['password'=>'a','confirmacion'=>'a']))['password']));
test('Registro rechaza contraseña sin confirmar',isset(validate_registration(array_replace($reg,['confirmacion'=>'errada']))['confirmacion']));
test('Registro rechaza nombre demasiado largo',isset(validate_registration(array_replace($reg,['nombre'=>str_repeat('a',101)]))['nombre']));
test('Escape XSS',e('<script>')==='&lt;script&gt;');
test('ID SQL inyectado rechazado',positive_id('1 OR 1=1')===null);
test('ID arrays rechazado',positive_id(['1'])===null);
test('ID normal aceptado',positive_id('3')===3);
$contact=['nombre'=>'Laura Soto','telefono'=>'0987654321','correo'=>'laura@test.com','ciudad'=>'Loja','id_categoria'=>'4','favorito'=>'0'];
test('Contacto V2 válido',validate_contact($contact,[1,2,3,4,5])===[]);
test('Categoría no existente rechazada',isset(validate_contact(array_replace($contact,['id_categoria'=>'999']),[1,2,3,4,5])['id_categoria']));
test('Categoría inyectada rechazada',isset(validate_contact(array_replace($contact,['id_categoria'=>'4 OR 1=1']),[1,2,3,4,5])['id_categoria']));
test('Favorito malicioso rechazado',isset(validate_contact(array_replace($contact,['favorito'=>'true']),[1,2,3,4,5])['favorito']));
test('Nombre inválido',isset(validate_contact(array_replace($contact,['nombre'=>'']),[1,2,3,4,5])['nombre']));
test('Ciudad inválida',isset(validate_contact(array_replace($contact,['ciudad'=>'']),[1,2,3,4,5])['ciudad']));
test('Teléfono inválido',isset(validate_contact(array_replace($contact,['telefono'=>'q12']),[1,2,3,4,5])['telefono']));
test('Correo inválido',isset(validate_contact(array_replace($contact,['correo'=>'x']),[1,2,3,4,5])['correo']));
test('Cinco categorías fijas',count(list_categories())===5);
$filter=contact_filters_from_query(['q'=>'  Loja  ','orden'=>'ciudad','direccion'=>'desc','categoria'=>'4','favoritos'=>'1']);
test('Normalización búsqueda', $filter['q']==='Loja');
test('Normalización orden', $filter['orden']==='ciudad' && $filter['direccion']==='desc');
test('Normalización categoría', $filter['categoria']===4);
test('Normalización favorito', $filter['favoritos']===true);
test('Columnas SQL arbitrarias rechazadas',contact_filters_from_query(['orden'=>'id_usuario; DROP TABLE usuarios'])['orden']==='nombre');
test('Dirección SQL arbitraria rechazada',contact_filters_from_query(['direccion'=>'desc; DROP TABLE usuarios'])['direccion']==='asc');
test('Búsqueda tipo array rechazada',contact_filters_from_query(['q'=>['a']])['q']==='');
test('Categoría maliciosa rechazada',contact_filters_from_query(['categoria'=>'5 UNION SELECT'])['categoria']===0);
test('Texto de búsqueda largo rechazado',contact_filters_from_query(['q'=>str_repeat('a',101)])['q']==='');
session_start();
$_SESSION = [];
test('CSRF token estable',csrf_token()===csrf_token());
test('Campo CSRF presente',str_contains(csrf_field(),csrf_token()));
$hash=password_hash('ClaveSegura123',PASSWORD_DEFAULT);
test('Hash distinto del texto original',$hash !== 'ClaveSegura123');
test('Verificación válida',password_verify('ClaveSegura123',$hash));
test('Verificación inválida',!password_verify('errada',$hash));
$memory->users[1]=['id'=>1,'nombre'=>'Maria','correo'=>'maria@test.com','password'=>$hash];
test('Autenticación válida',authenticate('maria@test.com','ClaveSegura123')['id']===1);
test('Autenticación incorrecta',authenticate('maria@test.com','incorrecta')===null);
log_in(['id'=>1,'nombre'=>'Maria','correo'=>'maria@test.com']);
test('Sesión autenticada',current_user()['id']===1);
add_contact(1,$contact);
test('Crear con usuario propietario',count($memory->contacts)===1 && $memory->contacts[1]['id_usuario']===1);
test('Crear con categoría',find_contact(1,1)['id_categoria']===4);
add_contact(1,array_replace($contact,['nombre'=>'Ana Perez','ciudad'=>'Zamora','correo'=>'ana@test.com','id_categoria'=>'1','favorito'=>'1']));
add_contact(1,array_replace($contact,['nombre'=>'Mateo Ruiz','ciudad'=>'Quito','correo'=>'mateo@test.com','id_categoria'=>'2','favorito'=>'0']));
add_contact(2,array_replace($contact,['nombre'=>'CONTACTO AJENO','correo'=>'otro@test.com','id_categoria'=>'5']));
test('Listar solo contactos propios',count(list_contacts(1))===3);
test('Segundo usuario ve solo sus datos',count(list_contacts(2))===1);
test('Consulta ID ajeno bloqueada',find_contact(4,1)===null);
test('Búsqueda por nombre',count(list_contacts(1,['q'=>'Ana']))===1);
test('Búsqueda por correo',count(list_contacts(1,['q'=>'mateo@test']))===1);
test('Búsqueda por ciudad',count(list_contacts(1,['q'=>'Loja']))===1);
test('Búsqueda no filtra datos ajenos',count(list_contacts(1,['q'=>'CONTACTO AJENO']))===0);
test('Búsqueda escapa % literal',count(list_contacts(1,['q'=>'%']))===0);
test('Filtro por categoría',count(list_contacts(1,['categoria'=>1]))===1);
test('Filtro favoritos',count(list_contacts(1,['favoritos'=>true]))===1);
test('Combinar filtros',count(list_contacts(1,['q'=>'Zamora','categoria'=>1,'favoritos'=>true]))===1);
test('Ordenar por nombre ascendente',list_contacts(1)[0]['nombre']==='Ana Perez');
test('Ordenar por nombre descendente',list_contacts(1,['orden'=>'nombre','direccion'=>'desc'])[0]['nombre']==='Mateo Ruiz');
test('Ordenar por ciudad ascendente',list_contacts(1,['orden'=>'ciudad','direccion'=>'asc'])[0]['ciudad']==='Loja');
test('Ordenar por ciudad descendente',list_contacts(1,['orden'=>'ciudad','direccion'=>'desc'])[0]['ciudad']==='Zamora');
test('Ordenar por fecha descendente',list_contacts(1,['orden'=>'fecha','direccion'=>'desc'])[0]['id']===3);
test('Contador propio',contact_counts(1)['total']===3 && contact_counts(2)['total']===1);
test('Contador favoritos propio',contact_counts(1)['favoritos']===1);
set_contact_favorite(1,2,true);
test('No cambiar favoritos ajenos',$memory->contacts[1]['favorito']===0);
set_contact_favorite(1,1,true);
test('Marcar favorito propio',find_contact(1,1)['favorito']===1);
test('Favoritos actualizados',contact_counts(1)['favoritos']===2);
set_contact_favorite(1,1,false);
test('Quitar favorito propio',find_contact(1,1)['favorito']===0);
$edit=array_replace($contact,['nombre'=>'Nombre Cambiado','id_categoria'=>'3','favorito'=>'1']);
edit_contact(1,2,$edit);
test('Editar contacto ajeno denegado',find_contact(1,1)['nombre']==='Laura Soto');
edit_contact(1,1,$edit);
test('Editar campos y categoría',find_contact(1,1)['nombre']==='Nombre Cambiado' && find_contact(1,1)['id_categoria']===3);
test('Editar campo favorito',find_contact(1,1)['favorito']===1);
test('Borrado contacto ajeno bloqueado',remove_contact(1,2)===false);
test('Contacto ajeno persiste',find_contact(1,1)!==null);
test('Eliminar propietario permitido',remove_contact(1,1)===true);
test('Contacto eliminado de los resultados',find_contact(1,1)===null && contact_counts(1)['total']===2);
foreach ($memory->prepared as $sql) {
    if (preg_match('/\\bFROM contactos\\b|\\bUPDATE contactos\\b|\\bINSERT INTO contactos\\b|\\bDELETE FROM contactos\\b/', $sql)) {
        test('Consultas de contacto incluyen propietario',str_contains($sql,'id_usuario'));
    }
}
echo "\nRESULTADO: $total pruebas unitarias simuladas superadas (NO MySQL real).\n";
ob_end_flush();
