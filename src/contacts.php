<?php
declare(strict_types=1);

/** @return list<array{id:int,nombre:string}> */
function list_categories(): array
{
    return db()->query('SELECT id, nombre FROM categorias ORDER BY id ASC')->fetchAll();
}

/** @param array<string,mixed> $query
 *  @return array{q:string,orden:string,direccion:string,categoria:int,favoritos:bool}
 */
function contact_filters_from_query(array $query): array
{
    $q = $query['q'] ?? '';
    $q = is_string($q) ? trim($q) : '';
    // Limita longitud y rechaza caracteres de control sin depender de mbstring.
    if (preg_match('/\A.{0,100}\z/us', $q) !== 1 || preg_match('/[\x00-\x1F\x7F]/u', $q) === 1) {
        $q = '';
    }
    $order = $query['orden'] ?? 'nombre';
    $order = is_string($order) && in_array($order, ['nombre', 'ciudad', 'fecha'], true) ? $order : 'nombre';
    $direction = $query['direccion'] ?? 'asc';
    $direction = is_string($direction) && in_array($direction, ['asc', 'desc'], true) ? $direction : 'asc';
    $category = positive_id($query['categoria'] ?? null) ?? 0;
    return [
        'q' => $q,
        'orden' => $order,
        'direccion' => $direction,
        'categoria' => $category,
        'favoritos' => ($query['favoritos'] ?? null) === '1',
    ];
}

/** @param array<string,mixed> $filters
 * @return list<array<string,mixed>> */
function list_contacts(int $userId, array $filters = []): array
{
    $sortColumns = ['nombre' => 'c.nombre', 'ciudad' => 'c.ciudad', 'fecha' => 'c.fecha_creacion'];
    $sort = $filters['orden'] ?? 'nombre';
    $direction = $filters['direccion'] ?? 'asc';
    $sort = is_string($sort) && isset($sortColumns[$sort]) ? $sort : 'nombre';
    $direction = $direction === 'desc' ? 'DESC' : 'ASC';

    $sql = 'SELECT c.id, c.nombre, c.telefono, c.correo, c.ciudad, c.id_categoria, c.favorito, c.fecha_creacion, ca.nombre AS categoria
        FROM contactos c INNER JOIN categorias ca ON ca.id = c.id_categoria WHERE c.id_usuario = :usuario';
    $params = ['usuario' => $userId];
    $search = $filters['q'] ?? '';
    if (is_string($search) && $search !== '') {
        $escaped = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $search);
        $sql .= " AND (c.nombre LIKE :q_nombre ESCAPE '!' OR c.correo LIKE :q_correo ESCAPE '!' OR c.ciudad LIKE :q_ciudad ESCAPE '!')";
        $params['q_nombre'] = '%' . $escaped . '%';
        $params['q_correo'] = '%' . $escaped . '%';
        $params['q_ciudad'] = '%' . $escaped . '%';
    }
    if (($filters['categoria'] ?? 0) > 0) {
        $sql .= ' AND c.id_categoria = :categoria';
        $params['categoria'] = (int) $filters['categoria'];
    }
    if (($filters['favoritos'] ?? false) === true) {
        $sql .= ' AND c.favorito = 1';
    }
    // Tanto la columna como la dirección proceden de listas blancas cerradas.
    $sql .= ' ORDER BY ' . $sortColumns[$sort] . ' ' . $direction . ', c.id ASC';
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/** @return array{total:int,favoritos:int} */
function contact_counts(int $userId): array
{
    $stmt = db()->prepare('SELECT COUNT(*) AS total, COALESCE(SUM(favorito = 1), 0) AS favoritos FROM contactos WHERE id_usuario = :usuario');
    $stmt->execute(['usuario' => $userId]);
    $count = $stmt->fetch();
    return ['total' => (int) $count['total'], 'favoritos' => (int) $count['favoritos']];
}

/** @return array<string,mixed>|null */
function find_contact(int $contactId, int $userId): ?array
{
    $stmt = db()->prepare('SELECT id, nombre, telefono, correo, ciudad, id_categoria, favorito, fecha_creacion FROM contactos WHERE id = :id AND id_usuario = :usuario LIMIT 1');
    $stmt->execute(['id' => $contactId, 'usuario' => $userId]);
    $contact = $stmt->fetch();
    return $contact ?: null;
}

/** @param array<string,string> $values */
function add_contact(int $userId, array $values): void
{
    $stmt = db()->prepare('INSERT INTO contactos (id_usuario, nombre, telefono, correo, ciudad, id_categoria, favorito) VALUES (:usuario, :nombre, :telefono, :correo, :ciudad, :categoria, :favorito)');
    $stmt->execute([
        'usuario' => $userId, 'nombre' => $values['nombre'], 'telefono' => $values['telefono'],
        'correo' => $values['correo'], 'ciudad' => $values['ciudad'],
        'categoria' => (int) $values['id_categoria'], 'favorito' => (int) $values['favorito'],
    ]);
}

/** @param array<string,string> $values */
function edit_contact(int $contactId, int $userId, array $values): void
{
    $stmt = db()->prepare('UPDATE contactos SET nombre = :nombre, telefono = :telefono, correo = :correo, ciudad = :ciudad, id_categoria = :categoria, favorito = :favorito WHERE id = :id AND id_usuario = :usuario');
    $stmt->execute([
        'nombre' => $values['nombre'], 'telefono' => $values['telefono'], 'correo' => $values['correo'],
        'ciudad' => $values['ciudad'], 'categoria' => (int) $values['id_categoria'],
        'favorito' => (int) $values['favorito'], 'id' => $contactId, 'usuario' => $userId,
    ]);
}

function set_contact_favorite(int $contactId, int $userId, bool $favorite): void
{
    $stmt = db()->prepare('UPDATE contactos SET favorito = :favorito WHERE id = :id AND id_usuario = :usuario');
    $stmt->execute(['favorito' => $favorite ? 1 : 0, 'id' => $contactId, 'usuario' => $userId]);
}

function remove_contact(int $contactId, int $userId): bool
{
    $stmt = db()->prepare('DELETE FROM contactos WHERE id = :id AND id_usuario = :usuario');
    $stmt->execute(['id' => $contactId, 'usuario' => $userId]);
    return $stmt->rowCount() === 1;
}
