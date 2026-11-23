<?php
declare(strict_types=1);
/** Ejecutar con php tests/mysql_integration.php después de cargar SQL V2; usa ROLLBACK. */
if (!in_array('mysql', PDO::getAvailableDrivers(),true)) {
    fwrite(STDERR,"NO EJECUTADO: falta el controlador PDO MySQL.\n");exit(2);
}
if (!is_file(dirname(__DIR__).'/config/config.php')) {
    fwrite(STDERR,"NO EJECUTADO: configura config/config.php.\n");exit(2);
}
require dirname(__DIR__).'/src/helpers.php';
require dirname(__DIR__).'/src/database.php';
require dirname(__DIR__).'/src/validators.php';
require dirname(__DIR__).'/src/auth.php';
require dirname(__DIR__).'/src/contacts.php';
$pdo=db();
$checks=0;
function check_real(string $message,bool $condition): void
{ global $checks; if (!$condition) throw new RuntimeException('FALLO '.$message);$checks++;echo "OK $message\n"; }
$pdo->beginTransaction();
$failed=false;
try {
    $cats=list_categories();
    check_real('Existen cinco categorías',count($cats)===5);
    check_real('Categoría 5 es Otro',end($cats)['nombre']==='Otro');
    $token=bin2hex(random_bytes(6));$mailA='a_'.$token.'@example.test';$mailB='b_'.$token.'@example.test';
    $password='PruebaMuySegura123';
    $create=$pdo->prepare('INSERT INTO usuarios (nombre,correo,password) VALUES (:nombre,:correo,:pass)');
    $create->execute(['nombre'=>'Cuenta A','correo'=>$mailA,'pass'=>password_hash($password,PASSWORD_DEFAULT)]);
    $idA=(int)$pdo->lastInsertId();
    $create->execute(['nombre'=>'Cuenta B','correo'=>$mailB,'pass'=>password_hash($password,PASSWORD_DEFAULT)]);
    $idB=(int)$pdo->lastInsertId();
    check_real('Usuarios creados',$idA>0 && $idB>$idA);
    check_real('Login correcto',authenticate($mailA,$password)['id']===$idA);
    check_real('Login erróneo denegado',authenticate($mailA,'no')===null);
    $v=['nombre'=>'Ana Lozano','telefono'=>'0987654321','correo'=>'ana@example.test','ciudad'=>'Loja','id_categoria'=>'1','favorito'=>'0'];
    check_real('Valores V2 validados',validate_contact($v,[1,2,3,4,5])===[]);
    add_contact($idA,$v);
    $idPrimero=(int)$pdo->lastInsertId();
    add_contact($idA,array_replace($v,['nombre'=>'Mario Diaz','correo'=>'mario@example.test','ciudad'=>'Zamora','id_categoria'=>'4','favorito'=>'1']));
    $idSegundo=(int)$pdo->lastInsertId();
    // Fechas explícitas para que el ensayo de orden no dependa de si ambas inserciones ocurren en el mismo segundo.
    $setDate=$pdo->prepare('UPDATE contactos SET fecha_creacion=:fecha WHERE id=:id AND id_usuario=:usuario');
    $setDate->execute(['fecha'=>'2026-01-01 10:00:00','id'=>$idPrimero,'usuario'=>$idA]);
    $setDate->execute(['fecha'=>'2026-01-02 10:00:00','id'=>$idSegundo,'usuario'=>$idA]);
    add_contact($idB,array_replace($v,['nombre'=>'Otro dueño','correo'=>'otro@example.test','id_categoria'=>'5']));
    $listA=list_contacts($idA);$listB=list_contacts($idB);
    check_real('Contacto A creado',count($listA)===2 && $listA[0]['nombre']==='Ana Lozano');
    check_real('Usuario B tiene solo un contacto',count($listB)===1);
    $contactId=(int)$listA[0]['id'];
    check_real('No consulta ID ajeno',find_contact($contactId,$idB)===null);
    check_real('Búsqueda nombre',count(list_contacts($idA,['q'=>'Mario']))===1);
    check_real('Búsqueda correo',count(list_contacts($idA,['q'=>'ana@']))===1);
    check_real('Búsqueda ciudad',count(list_contacts($idA,['q'=>'Zamora']))===1);
    check_real('Búsqueda no ve contactos B',count(list_contacts($idA,['q'=>'Otro dueño']))===0);
    check_real('SQL LIKE escapado no usa % como comodín',count(list_contacts($idA,['q'=>'%']))===0);
    check_real('Filtro categoría',count(list_contacts($idA,['categoria'=>4]))===1);
    check_real('Filtro favoritos',count(list_contacts($idA,['favoritos'=>true]))===1);
    check_real('Filtros combinados',count(list_contacts($idA,['q'=>'Zamora','categoria'=>4,'favoritos'=>true]))===1);
    check_real('Orden por nombre descendente',list_contacts($idA,['orden'=>'nombre','direccion'=>'desc'])[0]['nombre']==='Mario Diaz');
    check_real('Orden por ciudad ascendente',list_contacts($idA,['orden'=>'ciudad','direccion'=>'asc'])[0]['ciudad']==='Loja');
    check_real('Orden por fecha descendente',list_contacts($idA,['orden'=>'fecha','direccion'=>'desc'])[0]['nombre']==='Mario Diaz');
    check_real('Resumen personal',contact_counts($idA)['total']===2 && contact_counts($idA)['favoritos']===1);
    set_contact_favorite($contactId,$idB,true);
    check_real('Usuario B no cambia favorito ajeno',find_contact($contactId,$idA)['favorito']==0);
    set_contact_favorite($contactId,$idA,true);
    check_real('Usuario A cambia favorito propio',find_contact($contactId,$idA)['favorito']==1);
    edit_contact($contactId,$idB,array_replace($v,['nombre'=>'ATAQUE']));
    check_real('Usuario B no edita contacto A',find_contact($contactId,$idA)['nombre']==='Ana Lozano');
    edit_contact($contactId,$idA,array_replace($v,['nombre'=>'Ana Actualizada','id_categoria'=>'3','favorito'=>'1']));
    check_real('Actualización de categoría y favorito',find_contact($contactId,$idA)['id_categoria']==3 && find_contact($contactId,$idA)['favorito']==1);
    check_real('Borrado ajeno denegado',remove_contact($contactId,$idB)===false);
    check_real('Borrado propio permitido',remove_contact($contactId,$idA)===true);
    check_real('Contacto eliminado',find_contact($contactId,$idA)===null);
    // Verifica físicamente que la FK categorias existe.
    $fk=$pdo->query("SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='contactos' AND COLUMN_NAME='id_categoria' AND REFERENCED_TABLE_NAME='categorias'")->fetchColumn();
    check_real('Clave externa categoria válida',(int)$fk>=1);
    echo "\nRESULTADO: $checks pruebas REALES MySQL superadas; ROLLBACK aplicado.\n";
} catch (Throwable $e) {
    $failed=true;fwrite(STDERR,'FALLO INTEGRACIÓN REAL: '.$e->getMessage()."\n");
} finally {
    if ($pdo->inTransaction()) $pdo->rollBack();
}
if ($failed) exit(1);
