<?php
// Incluir header.php para encabezado consistente y manejo de sesión
include 'header.php';
ob_start();
// Start session and check login
$sessionPath = '/var/www/html/online_shop_b2b/sessions';
ini_set('session.save_path', $sessionPath);
session_start();

if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}
// End session check

require_once("dbConn.php");

// Debug output for session user
//echo '<pre>Session user: ' . print_r($_SESSION['user'] ?? null, true) . '</pre>';

if (!isset($_SESSION['user']['id']) || !is_numeric($_SESSION['user']['id'])) {
    echo "<p>Error: No se ha iniciado sesión correctamente. Por favor, inicie sesión para continuar.</p>";
    exit;
}

$client_id = intval($_SESSION['user']['client_id']);
$user_role = intval($_SESSION['user']['tipo'] ?? 0);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_empleado_cliente = $_POST['id_empleado_cliente'] ?? null;
    $monto_credito = $_POST['monto_credito'] ?? null;
    $action = $_POST['action'] ?? 'modify';

    if (!$id_empleado_cliente || !is_numeric($id_empleado_cliente)) {
        die("ID de empleado cliente inválido.");
    }

    if (!$monto_credito || !is_numeric($monto_credito) || $monto_credito < 0) {
        die("Monto de crédito inválido.");
    }

    try {
        // Verificar si el empleado cliente existe y está activo
        $stmtCheck = $pdo->prepare("SELECT * FROM tbl_empleados_cliente WHERE id_empleado_cliente = ? AND bit_activo = 1");
        $stmtCheck->execute([$id_empleado_cliente]);
        $empleado = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if (!$empleado) {
            die("Empleado cliente no encontrado o inactivo.");
        }

        // Obtener el id_cliente del empleado para verificar créditos
        $id_cliente = $empleado['id_cliente'];

        // Obtener el crédito total asignado a la empresa
        $stmtCreditoEmpresa = $pdo->prepare("
            SELECT COALESCE(SUM(fl_monto_credito), 0) AS credito_total
            FROM tbl_creditos_empresa
            WHERE id_cliente = ? AND bit_activo = 1
        ");
        $stmtCreditoEmpresa->execute([$id_cliente]);
        $creditoEmpresa = $stmtCreditoEmpresa->fetch(PDO::FETCH_ASSOC);
        $credito_total = $creditoEmpresa['credito_total'];

        // Obtener la suma de los créditos individuales de todos los empleados excepto el actual
        $stmtSumaCreditos = $pdo->prepare("
            SELECT COALESCE(SUM(fl_limite_credito_individual), 0) AS suma_creditos
            FROM tbl_empleados_cliente
            WHERE id_cliente = ? AND bit_activo = 1 AND id_empleado_cliente != ?
        ");
        $stmtSumaCreditos->execute([$id_cliente, $id_empleado_cliente]);
        $sumaCreditos = $stmtSumaCreditos->fetch(PDO::FETCH_ASSOC);
        $suma_creditos = $sumaCreditos['suma_creditos'];

        // Verificar que la suma de créditos no exceda el crédito total asignado a la empresa
        if (($suma_creditos + $monto_credito) > $credito_total) {
            die("Error: La suma de los créditos individuales excede el crédito total asignado a la empresa.");
        }

        // Actualizar o insertar según la acción
        if ($action === 'modify') {
            // Actualizar el límite de crédito y crédito disponible para el empleado cliente
            $stmtUpdate = $pdo->prepare("
                UPDATE tbl_empleados_cliente
                SET fl_limite_credito_individual = ?, fl_credito_disponible = ?
                WHERE id_empleado_cliente = ?
            ");

            // Para simplicidad, se asume que crédito disponible se reinicia al nuevo límite de crédito
            $stmtUpdate->execute([$monto_credito, $monto_credito, $id_empleado_cliente]);
        } else if ($action === 'assign') {
            // Insertar nuevo crédito sin actualizar el límite actual
            $stmtInsertLimite = $pdo->prepare("
                UPDATE tbl_empleados_cliente
                SET fl_limite_credito_individual = fl_limite_credito_individual + ?, fl_credito_disponible = fl_credito_disponible + ?
                WHERE id_empleado_cliente = ?
            ");
            $stmtInsertLimite->execute([$monto_credito, $monto_credito, $id_empleado_cliente]);
        }

        // Si el usuario tiene nivel >= 3, guardar crédito en tbl_creditos_empleado
        if ($user_role >= 3) {
            try {
                // Obtener id_credito_empresa para el cliente del empleado
                $stmtCreditoEmpresa = $pdo->prepare("
                    SELECT id_credito_empresa
                    FROM tbl_creditos_empresa
                    WHERE id_cliente = ? AND bit_activo = 1
                    LIMIT 1
                ");
                $stmtCreditoEmpresa->execute([$id_cliente]);
                $creditoEmpresa = $stmtCreditoEmpresa->fetch(PDO::FETCH_ASSOC);

                if ($creditoEmpresa) {
                    $id_credito_empresa = $creditoEmpresa['id_credito_empresa'];

                    if ($action === 'modify') {
                        // Verificar si ya existe un registro en tbl_creditos_empleado para este empleado
                        $stmtCheckCreditoEmpleado = $pdo->prepare("
                            SELECT id_credito_empleado
                            FROM tbl_creditos_empleado
                            WHERE id_empleado_cliente = ?
                            LIMIT 1
                        ");
                        $stmtCheckCreditoEmpleado->execute([$id_empleado_cliente]);
                        $creditoEmpleado = $stmtCheckCreditoEmpleado->fetch(PDO::FETCH_ASSOC);

                        if ($creditoEmpleado) {
                            // Actualizar el monto de crédito y activar el registro
                            $stmtUpdateCreditoEmpleado = $pdo->prepare("
                                UPDATE tbl_creditos_empleado
                                SET fl_monto_credito = ?, bit_activo = 1
                                WHERE id_credito_empleado = ?
                            ");
                            $stmtUpdateCreditoEmpleado->execute([$monto_credito, $creditoEmpleado['id_credito_empleado']]);
                        } else {
                            // Insertar nuevo registro de crédito empleado
                            $stmtInsertCreditoEmpleado = $pdo->prepare("
                                INSERT INTO tbl_creditos_empleado (id_empleado_cliente, id_credito_empresa, fl_monto_credito, bit_activo)
                                VALUES (?, ?, ?, 1)
                            ");
                            $stmtInsertCreditoEmpleado->execute([$id_empleado_cliente, $id_credito_empresa, $monto_credito]);
                        }
                    } else if ($action === 'assign') {
                        // Insertar nuevo registro de crédito empleado sin actualizar registros existentes
                        $stmtInsertCreditoEmpleado = $pdo->prepare("
                            INSERT INTO tbl_creditos_empleado (id_empleado_cliente, id_credito_empresa, fl_monto_credito, bit_activo)
                            VALUES (?, ?, ?, 1)
                        ");
                        $stmtInsertCreditoEmpleado->execute([$id_empleado_cliente, $id_credito_empresa, $monto_credito]);
                    }
                }
            } catch (PDOException $e) {
                die("Error al guardar crédito en tbl_creditos_empleado: " . $e->getMessage());
            }
        }

        // Redirigir de vuelta a linea_de_credito.php o mostrar mensaje de éxito
        header("Location: linea_de_credito.php");
        exit;

    } catch (PDOException $e) {
        die("Error en la base de datos: " . $e->getMessage());
    }
} else {
    die("Método no permitido.");
}
?>
