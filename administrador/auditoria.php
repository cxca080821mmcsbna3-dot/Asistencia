<?php
session_start();

if (!isset($_SESSION['idAdmin']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

$idAdmin     = $_SESSION['idAdmin'];
$nombreAdmin = $_SESSION['nombre'];

$pdo = new PDO(
    "mysql:host=localhost;dbname=asistencia;charset=utf8mb4",
    "root",
    "",
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
);

/* ===============================
   PASAR ADMIN A MYSQL (TRIGGERS)
================================ */
$stmt = $pdo->prepare("
    SET @admin_id = :id,
        @admin_nombre = :nombre
");
$stmt->execute([
    'id' => $idAdmin,
    'nombre' => $nombreAdmin
]);

$sql = "
SELECT 
    a.id_auditoria,
    a.tabla_afectada,
    a.accion,
    a.datos_antes,
    a.datos_despues,
    a.admin_nombre,
    a.fecha,
    CASE a.tabla_afectada
        WHEN 'alumno' THEN (
            SELECT CONCAT(nombre,' ',apellidos)
            FROM alumno 
            WHERE id_alumno = a.id_registro
        )
        WHEN 'profesor' THEN (
            SELECT CONCAT(nombre,' ',apellidos)
            FROM profesor 
            WHERE id_profesor = a.id_registro
        )
        WHEN 'materias' THEN (
            SELECT nombre
            FROM materias 
            WHERE id_materia = a.id_registro
        )
        WHEN 'grupo' THEN (
            SELECT nombre
            FROM grupo 
            WHERE idGrupo = a.id_registro
        )
        WHEN 'administrador' THEN (
            SELECT correo
            FROM administrador
            WHERE id_admin = a.id_registro
        )
        ELSE a.id_registro
    END AS registro_legible
FROM auditoria a
WHERE 1=1
";

$params = [];

if (!empty($_GET['tabla'])) {
    $sql .= " AND a.tabla_afectada = :tabla";
    $params['tabla'] = $_GET['tabla'];
}

if (!empty($_GET['accion'])) {
    $sql .= " AND a.accion = :accion";
    $params['accion'] = $_GET['accion'];
}

if (!empty($_GET['admin'])) {
    $sql .= " AND a.admin_nombre LIKE :admin";
    $params['admin'] = '%' . $_GET['admin'] . '%';
}

if (!empty($_GET['desde'])) {
    $sql .= " AND a.fecha >= :desde";
    $params['desde'] = $_GET['desde'] . " 00:00:00";
}

if (!empty($_GET['hasta'])) {
    $sql .= " AND a.fecha <= :hasta";
    $params['hasta'] = $_GET['hasta'] . " 23:59:59";
}

$sql .= " ORDER BY a.fecha DESC LIMIT 200";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$registros = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<<<<<<< HEAD
=======
<<<<<<< HEAD
>>>>>>> 6f2679b44ad18e58f4b0c15589a2663122a804be
    <meta charset="UTF-8">
    <title>Auditoría del Sistema</title>
    <link rel="stylesheet" href="css/auditoria.css">
    <link rel="stylesheet" href="css/materiascrud.css">
</head>
<body>

<?php include_once "layout/header_admin.php"; ?>

<a href="menuGrupos.php" class="back-arrow">← Regresar</a>

<form method="GET" class="filtros">
    <select name="tabla">
        <option value="">Todas las tablas</option>
        <?php
        $tablas = ['alumno','grupo','profesor','materias','grupo_materia','asistencia','administrador'];
        foreach ($tablas as $t):
        ?>
            <option value="<?= $t ?>" <?= ($_GET['tabla'] ?? '') === $t ? 'selected' : '' ?>>
                <?= ucfirst(str_replace('_',' ', $t)) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <select name="accion">
        <option value="">Todas las acciones</option>
        <?php foreach (['INSERT','UPDATE','DELETE'] as $a): ?>
            <option value="<?= $a ?>" <?= ($_GET['accion'] ?? '') === $a ? 'selected' : '' ?>>
                <?= $a ?>
            </option>
        <?php endforeach; ?>
    </select>

    <input type="text" name="admin" placeholder="Administrador"
           value="<?= htmlspecialchars($_GET['admin'] ?? '') ?>">

    <input type="date" name="desde" value="<?= $_GET['desde'] ?? '' ?>">
    <input type="date" name="hasta" value="<?= $_GET['hasta'] ?? '' ?>">

    <button type="submit">Filtrar</button>
    <a href="auditoria.php" class="btn-reset">Limpiar</a>
</form>

<div class="wrapper">
<<<<<<< HEAD
=======
=======
    <meta charset="UTF-8" />
    <title>Registros de Auditoría</title>
    <link rel="stylesheet" href="css/auditoria.css?v=1.2">
    <link rel="stylesheet" href="css/materiascrud.css?v=1.2">
</head>
<body>
    <?php include_once "layout/header_admin.php"; ?>
  <div class="wrapper">
>>>>>>> b84acd2e1f92af2a14f4a536182aab7b9dc555d5
>>>>>>> 6f2679b44ad18e58f4b0c15589a2663122a804be
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Tabla</th>
                <th>Registro</th>
                <th>Acción</th>
                <th>Antes</th>
                <th>Después</th>
                <th>Administrador</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
        <?php if (!$registros): ?>
            <tr>
                <td colspan="8">No hay registros</td>
            </tr>
        <?php else: foreach ($registros as $r): ?>
            <tr>
                <td><?= $r['id_auditoria'] ?></td>
                <td><?= $r['tabla_afectada'] ?></td>
                <td><?= htmlspecialchars($r['registro_legible']) ?></td>
                <td><?= $r['accion'] ?></td>
                <td><?= nl2br(htmlspecialchars($r['datos_antes'] ?? '—')) ?></td>
                <td><?= nl2br(htmlspecialchars($r['datos_despues'] ?? '—')) ?></td>
                <td><?= $r['admin_nombre'] ?></td>
                <td><?= $r['fecha'] ?></td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

</body>
</html>
