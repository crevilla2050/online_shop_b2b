<?php
include 'init_session.php';
include 'header.php';

if (!isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}
// End session check

require_once("dbConn.php");

// Debug output for session user
//echo '<pre>Session user: ' . print_r($_SESSION['user'] ?? null, true) . '</pre>';

if (!isset($_SESSION['user']['id']) || !is_numeric($_SESSION['user']['id'])) {
    header('Location: index.php');
    exit;
}

$client_id = intval($_SESSION['user']['client_id']);
$user_role = intval($_SESSION['user']['tipo'] ?? 0);

// Debug output for session user and client id
//echo '<pre>Session user: ' . print_r($_SESSION['user'], true) . '</pre>';
//echo '<pre>Client ID used: ' . $client_id . '</pre>';

try {
    // Obtener info del cliente
    $stmt = $pdo->prepare("SELECT * FROM tbl_clientes WHERE id_cliente = ?");
    $stmt->execute([$client_id]);
    $client = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$client) {
        die("Cliente no encontrado.");
    }

    if ($user_role >= 3) {
        // Obtener crédito asignado, crédito usado y crédito restante para el usuario
        $stmtCreditSummary = $pdo->prepare("
            SELECT 
                COALESCE(SUM(fl_monto_credito), 0) AS credito_asignado
            FROM tbl_creditos_empresa
            WHERE id_cliente = ? AND bit_activo = 1
        ");
        $stmtCreditSummary->execute([$client_id]);
        $creditSummary = $stmtCreditSummary->fetch(PDO::FETCH_ASSOC);
        $credito_asignado = $creditSummary['credito_asignado'];
        $credito_usado = 0;
        $credito_restante = $credito_asignado;

        // Obtener suma de créditos asignados a empleados
        $stmtSumaCreditosEmpleados = $pdo->prepare("
            SELECT COALESCE(SUM(fl_limite_credito_individual), 0) AS suma_creditos_empleados
            FROM tbl_empleados_cliente
            WHERE id_cliente = ? AND bit_activo = 1
        ");
        $stmtSumaCreditosEmpleados->execute([$client_id]);
        $sumaCreditosEmpleados = $stmtSumaCreditosEmpleados->fetch(PDO::FETCH_ASSOC);
        $suma_creditos_empleados = $sumaCreditosEmpleados['suma_creditos_empleados'];

        // Calcular crédito restante para la empresa después de asignar a empleados
        $credito_restante_empresa = $credito_asignado - $suma_creditos_empleados;
    } else if ($user_role < 3) {
        // Obtener empleados actuales del cliente con info de usuario y crédito
        $stmtEmpleados = $pdo->prepare("
            SELECT ec.id_empleado_cliente, u.chr_login AS nombre_empleado
            FROM tbl_empleados_cliente ec
            JOIN tbl_usuarios u ON ec.id_empleado = u.id_usuario
            WHERE ec.id_cliente = ? AND ec.bit_activo = 1
        ");
        $stmtEmpleados->execute([$client_id]);
        $empleados = $stmtEmpleados->fetchAll(PDO::FETCH_ASSOC);

        // Para cada empleado, obtener créditos asignados, crédito usado y crédito disponible
        foreach ($empleados as &$empleado) {
            // Obtener créditos activos asignados a cada empleado desde tbl_creditos_empleado
            $stmtCreditosEmpleado = $pdo->prepare("
                SELECT id_credito_empleado, fl_monto_credito, dt_fecha_creacion, bit_activo
                FROM tbl_creditos_empleado
                WHERE id_empleado_cliente = ? AND bit_activo = 1
                ORDER BY dt_fecha_creacion DESC, id_credito_empleado DESC
            ");
            $stmtCreditosEmpleado->execute([$empleado['id_empleado_cliente']]);
            $creditosEmpleado = $stmtCreditosEmpleado->fetchAll(PDO::FETCH_ASSOC);
            $empleado['creditos'] = $creditosEmpleado;

            // Calcular crédito asignado (sumatoria de créditos)
            $credito_asignado = 0;
            foreach ($creditosEmpleado as $credito) {
                $credito_asignado += floatval($credito['fl_monto_credito']);
            }
            $empleado['credito_asignado'] = $credito_asignado;

            // Obtener crédito usado sumando consumos en tbl_consumos_empleado
            $stmtCreditoUsado = $pdo->prepare("
                SELECT COALESCE(SUM(fl_monto_consumido), 0) AS credito_usado
                FROM tbl_consumos_empleado
                WHERE id_empleado_cliente = ?
            ");
            $stmtCreditoUsado->execute([$empleado['id_empleado_cliente']]);
            $creditoUsadoRow = $stmtCreditoUsado->fetch(PDO::FETCH_ASSOC);
            $credito_usado = floatval($creditoUsadoRow['credito_usado']);
            $empleado['credito_usado'] = $credito_usado;

            // Calcular crédito disponible
            $empleado['credito_disponible'] = $credito_asignado - $credito_usado;
        }
        unset($empleado);

        // Obtener crédito total disponible para la empresa
        $stmtCreditoEmpresa = $pdo->prepare("
            SELECT COALESCE(SUM(fl_monto_credito), 0) AS credito_total
            FROM tbl_creditos_empresa
            WHERE id_cliente = ? AND bit_activo = 1
        ");
        $stmtCreditoEmpresa->execute([$client_id]);
        $creditoEmpresa = $stmtCreditoEmpresa->fetch(PDO::FETCH_ASSOC);
        $credito_total = $creditoEmpresa['credito_total'];
    }
} catch (PDOException $e) {
    die("Error en la base de datos: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Línea de Crédito - <?= htmlspecialchars($client['chr_nombre']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="styles/header.css" rel="stylesheet" />
</head>
<body>
    <?php include 'menu.php'; ?>
    <div class="container mt-5" style="margin-left: 220px;">
        <h2>Línea de Crédito para Cliente: <?= htmlspecialchars($client['chr_nombre']) ?></h2>
        <div class="card mt-3">
            <div class="card-header bg-primary text-white">
                Líneas de Crédito Activas
            </div>
            <div class="card-body">
                <?php if ($user_role < 3): ?>
                    <div>
                        <p><strong>Crédito Asignado:</strong> $<?= number_format($credito_asignado, 2) ?></p>
                        <p><strong>Crédito Usado:</strong> $<?= number_format($credito_usado, 2) ?></p>
                        <p><strong>Crédito Restante:</strong> $<?= number_format($credito_restante, 2) ?></p>
                    </div>
                <?php else: ?>
                    <h3>Empleados Actuales</h3>
                    <?php if (empty($empleados)): ?>
                        <p>No hay empleados registrados para este cliente.</p>
                    <?php else: ?>
                        <p><strong>Crédito Total Disponible para la Empresa:</strong> $<?= number_format($credito_total, 2) ?></p>
                        <p><strong>Crédito Asignado:</strong> $<?= number_format(array_sum(array_column($empleados, 'credito_asignado')), 2) ?></p>
                        <p><strong>Crédito Usado:</strong> $<?= number_format(array_sum(array_column($empleados, 'credito_usado')), 2) ?></p>
                        <p><strong>Crédito Restante:</strong> $<?= number_format(array_sum(array_column($empleados, 'credito_disponible')), 2) ?></p>
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID Empleado Cliente</th>
                                    <th>Nombre Empleado</th>
                                    <th>Límite Crédito Individual</th>
                                    <th>Crédito Disponible</th>
                                    <th>Asignar Crédito</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($empleados as $empleado): ?>
                                <tr>
                                    <td><?= htmlspecialchars($empleado['id_empleado_cliente']) ?></td>
                                    <td><?= htmlspecialchars($empleado['nombre_empleado']) ?></td>
                                    <td>
                                        $<?= number_format($empleado['credito_asignado'], 2) ?>
                                    </td>
                                    <td>
                                        $<?= number_format($empleado['credito_disponible'], 2) ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($empleado['creditos'])): ?>
                                            <table class="table table-sm table-bordered mb-2">
                                                <thead>
                                                    <tr>
                                                        <th>ID Crédito</th>
                                                        <th>Monto Crédito</th>
                                                        <th>Fecha Creación</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($empleado['creditos'] as $credito): ?>
                                                    <tr>
                                                        <td><?= htmlspecialchars($credito['id_credito_empleado']) ?></td>
                                                        <td>$<?= number_format($credito['fl_monto_credito'], 2) ?></td>
                                                        <td><?= htmlspecialchars($credito['dt_fecha_creacion']) ?></td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        <?php else: ?>
                                            <p>No hay créditos asignados.</p>
                                        <?php endif; ?>
                                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalModificarCredito" data-empleado-id="<?= htmlspecialchars($empleado['id_empleado_cliente']) ?>" data-creditos='<?= json_encode($empleado['creditos']) ?>'>
                                            Modificar Actual
                                        </button>
                                        <form method="post" action="asignar_credito_individual.php" class="d-flex align-items-center gap-2 mt-2">
                                            <input type="hidden" name="id_empleado_cliente" value="<?= htmlspecialchars($empleado['id_empleado_cliente']) ?>" />
                                            <input type="hidden" name="action" value="assign" />
                                            <input type="number" name="monto_credito" step="0.01" min="0" max="<?= htmlspecialchars($credito_total) ?>" placeholder="Nuevo crédito" required />
                                            <button type="submit" class="btn btn-success btn-sm">Asignar Nuevo Crédito</button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                <?php endif; ?>
                <?php if ($user_role < 3): ?>
                    <a href="asignar_credito.php?id=<?= htmlspecialchars($client_id) ?>" class="btn btn-primary mt-3">Asignar Nuevo Crédito</a>
                <?php endif; ?>
                <a href="detalle_cliente.php?id=<?= htmlspecialchars($client_id) ?>" class="btn btn-secondary mt-3 ms-2">Volver a Detalle Cliente</a>
            </div>
        </div>
    </div>
    <?php if ($user_role < 3): ?>
    <!-- Modal para modificar creditos actuales -->
    <div class="modal fade" id="modalModificarCredito" tabindex="-1" aria-labelledby="modalModificarCreditoLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <form id="formModificarCreditos" method="post" action="modificar_creditos_empleado.php">
            <div class="modal-header">
              <h5 class="modal-title" id="modalModificarCreditoLabel">Modificar Créditos del Empleado</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
              <input type="hidden" name="id_empleado_cliente" id="modal_id_empleado_cliente" value="" />
              <table class="table table-bordered" id="tablaCreditosEmpleado">
                <thead>
                  <tr>
                    <th>ID Crédito</th>
                    <th>Monto Crédito Actual</th>
                    <th>Nuevo Monto Crédito</th>
                  </tr>
                </thead>
                <tbody>
                  <!-- La tabla se llenará usando JavaScript -->
                </tbody>
              </table>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
              <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <?php endif; ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('modalModificarCredito');
    modal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var empleadoId = button.getAttribute('data-empleado-id');
        var creditosData = button.getAttribute('data-creditos');
        var creditos = JSON.parse(creditosData);

        var modalIdEmpleado = document.getElementById('modal_id_empleado_cliente');
        modalIdEmpleado.value = empleadoId;

        var tbody = document.querySelector('#tablaCreditosEmpleado tbody');
        tbody.innerHTML = '';

        creditos.forEach(function(credito) {
            var tr = document.createElement('tr');

            var tdId = document.createElement('td');
            tdId.textContent = credito.id_credito_empleado;
            tr.appendChild(tdId);

            var tdMontoActual = document.createElement('td');
            tdMontoActual.textContent = '$' + parseFloat(credito.fl_monto_credito).toFixed(2);
            tr.appendChild(tdMontoActual);

            var tdNuevoMonto = document.createElement('td');
            var inputNuevoMonto = document.createElement('input');
            inputNuevoMonto.type = 'number';
            inputNuevoMonto.name = 'monto_credito[' + credito.id_credito_empleado + ']';
            inputNuevoMonto.step = '0.01';
            inputNuevoMonto.min = '0';
            inputNuevoMonto.max = credito.fl_monto_credito;
            inputNuevoMonto.value = parseFloat(credito.fl_monto_credito).toFixed(2);
            inputNuevoMonto.required = true;
            tdNuevoMonto.appendChild(inputNuevoMonto);
            tr.appendChild(tdNuevoMonto);

            tbody.appendChild(tr);
        });
    });
});
</script>

</body>
</html>
