<?php
/**
 * CRON JOB - Sincronización Campus Ivy
 *
 * Ejecutar a las: 9am, 12pm, 4pm, 9pm (Hora del Este)
 * Crontab: 0 9,12,16,21 * * * /usr/bin/php /var/www/html/D3/accounting/ivy_cron_sync.php
 *
 * Funcionalidad:
 * 1. Ejecuta awards() para insertar nuevas transacciones
 * 2. Ejecuta disbursementbatchdetail() para actualizar estados
 * 3. Rango: últimos 7 días (considerando filtro createdDate >= 2025-12-08)
 * 4. Notifica por email si hay errores o nuevas transacciones
 * 5. Registra todo en S_IVY_SYNC_LOG y archivo de log
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configuración de zona horaria
date_default_timezone_set('America/New_York'); // Hora del Este

$path = '/var/www/html/D3/';
require_once($path . "global/config.php");
require_once("Diamonsis.php");

// Configuración
$PK_ACCOUNT = 63;
$PRODUCTION = true;
$LOG_FILE = __DIR__ . '/logs/ivy_cron_' . date('Y-m-d') . '.log';
$EMAIL_RECIPIENTS = array(
    'luis@diamondsis.com'
);

// Crear directorio de logs si no existe
if (!file_exists(__DIR__ . '/logs')) {
    mkdir(__DIR__ . '/logs', 0755, true);
}

/**
 * Escribir en archivo de log
 */
function writeLog($message) {
    global $LOG_FILE;
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[$timestamp] $message\n";
    file_put_contents($LOG_FILE, $logMessage, FILE_APPEND);
    error_log($message);
}

/**
 * Enviar email de notificación
 */
function sendNotificationEmail($subject, $body, $recipients) {
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: Campus Ivy Cron <noreply@diamondsis.com>" . "\r\n";

    foreach ($recipients as $email) {
        mail($email, $subject, $body, $headers);
    }
}

/**
 * Ejecutar endpoint y retornar resultado
 */
function executeEndpoint($action, $startDate, $endDate) {
    global $PK_ACCOUNT, $PRODUCTION;

    $url = "http://localhost/D3/accounting/test-prod.php";
    $params = array(
        'action' => $action,
        'pk_account' => $PK_ACCOUNT,
        'production' => $PRODUCTION ? 'true' : 'false',
        'start_date' => $startDate,
        'end_date' => $endDate
    );

    $urlWithParams = $url . '?' . http_build_query($params);

    writeLog("Ejecutando: $action con fechas $startDate a $endDate");

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $urlWithParams);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 300); // 5 minutos timeout
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        writeLog("ERROR cURL en $action: $curlError");
        return array('success' => false, 'error' => $curlError);
    }

    if ($httpCode !== 200) {
        writeLog("ERROR HTTP $httpCode en $action");
        return array('success' => false, 'error' => "HTTP $httpCode");
    }

    $result = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        writeLog("ERROR JSON en $action: " . json_last_error_msg());
        return array('success' => false, 'error' => 'Invalid JSON');
    }

    return $result;
}

/**
 * Generar reporte HTML
 */
function generateHtmlReport($awardsResult, $batchResult, $startDate, $endDate, $executionTime) {
    $awardsOk = isset($awardsResult['success']) && $awardsResult['success'];
    $batchOk = isset($batchResult['success']) && $batchResult['success'];

    $awardsInserted = $awardsOk ? $awardsResult['processing_summary']['inserted'] : 0;
    $awardsRefunds = $awardsOk ? $awardsResult['processing_summary']['refunds'] : 0;
    $awardsSkipped = $awardsOk ? $awardsResult['processing_summary']['skipped'] : 0;
    $awardsErrors = $awardsOk ? $awardsResult['processing_summary']['errors'] : 0;
    $awardsFiltered = $awardsOk ? $awardsResult['processing_summary']['filtered'] : 0;

    $batchUpdated = $batchOk ? $batchResult['processing_summary']['updated'] : 0;
    $batchNotFound = $batchOk ? $batchResult['processing_summary']['not_found'] : 0;
    $batchErrors = $batchOk ? $batchResult['processing_summary']['errors'] : 0;
    $batchFiltered = $batchOk ? $batchResult['processing_summary']['filtered'] : 0;

    $totalProcessed = $awardsInserted + $awardsRefunds + $batchUpdated;
    $totalErrors = $awardsErrors + $batchErrors;

    $statusColor = ($totalErrors > 0) ? '#dc3545' : (($totalProcessed > 0) ? '#28a745' : '#ffc107');
    $statusText = ($totalErrors > 0) ? 'CON ERRORES' : (($totalProcessed > 0) ? 'EXITOSO' : 'SIN CAMBIOS');

    $html = '<!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; padding: 20px; background-color: #f5f5f5; }
            .container { max-width: 800px; margin: 0 auto; background-color: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
            .header { background-color: #007bff; color: white; padding: 20px; border-radius: 8px 8px 0 0; margin: -20px -20px 20px -20px; }
            .status { display: inline-block; padding: 8px 16px; border-radius: 4px; color: white; font-weight: bold; background-color: ' . $statusColor . '; }
            table { width: 100%; border-collapse: collapse; margin: 15px 0; }
            th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
            th { background-color: #f8f9fa; font-weight: bold; }
            .metric { font-size: 24px; font-weight: bold; color: #007bff; }
            .section { margin: 20px 0; padding: 15px; background-color: #f8f9fa; border-radius: 4px; }
            .error { color: #dc3545; font-weight: bold; }
            .success { color: #28a745; font-weight: bold; }
            .warning { color: #ffc107; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h2>Campus Ivy - Sincronización Automática</h2>
                <p>Ejecución: ' . date('Y-m-d H:i:s T') . '</p>
                <p class="status">' . $statusText . '</p>
            </div>

            <div class="section">
                <h3>Parámetros de Ejecución</h3>
                <table>
                    <tr><th>Parámetro</th><th>Valor</th></tr>
                    <tr><td>Rango de fechas</td><td>' . $startDate . ' a ' . $endDate . '</td></tr>
                    <tr><td>Ambiente</td><td>PRODUCCIÓN</td></tr>
                    <tr><td>Tiempo de ejecución</td><td>' . number_format($executionTime, 2) . ' segundos</td></tr>
                    <tr><td>Filtro createdDate</td><td>>= 2025-12-08</td></tr>
                </table>
            </div>

            <div class="section">
                <h3>📥 Awards (Nuevas Transacciones)</h3>
                <table>
                    <tr><th>Métrica</th><th>Cantidad</th></tr>
                    <tr><td>Insertadas</td><td class="success">' . $awardsInserted . '</td></tr>
                    <tr><td>Refunds</td><td class="warning">' . $awardsRefunds . '</td></tr>
                    <tr><td>Filtradas (createdDate)</td><td>' . $awardsFiltered . '</td></tr>
                    <tr><td>Saltadas (duplicados/sin ledger)</td><td>' . $awardsSkipped . '</td></tr>
                    <tr><td>Errores</td><td class="error">' . $awardsErrors . '</td></tr>
                </table>
            </div>

            <div class="section">
                <h3>🔄 Batch Detail (Actualizaciones de Estado)</h3>
                <table>
                    <tr><th>Métrica</th><th>Cantidad</th></tr>
                    <tr><td>Actualizadas</td><td class="success">' . $batchUpdated . '</td></tr>
                    <tr><td>Filtradas (createdDate)</td><td>' . $batchFiltered . '</td></tr>
                    <tr><td>No encontradas</td><td class="warning">' . $batchNotFound . '</td></tr>
                    <tr><td>Errores</td><td class="error">' . $batchErrors . '</td></tr>
                </table>
            </div>

            <div class="section">
                <h3>📊 Resumen Total</h3>
                <table>
                    <tr><th>Concepto</th><th>Valor</th></tr>
                    <tr><td><strong>Total procesado</strong></td><td class="metric">' . $totalProcessed . '</td></tr>
                    <tr><td><strong>Total errores</strong></td><td class="metric error">' . $totalErrors . '</td></tr>
                </table>
            </div>

            <div class="section">
                <h3>📝 Notas</h3>
                <ul>
                    <li>Solo se procesan transacciones con <code>createdDate >= 2025-12-08</code></li>
                    <li>Los detalles completos están en <code>S_IVY_SYNC_LOG</code></li>
                    <li>Log de ejecución: <code>' . basename($GLOBALS['LOG_FILE']) . '</code></li>
                </ul>
            </div>
        </div>
    </body>
    </html>';

    return $html;
}

/**
 * MAIN EXECUTION
 */
try {
    $startTime = microtime(true);

    writeLog("========================================");
    writeLog("INICIANDO SINCRONIZACIÓN CAMPUS IVY");
    writeLog("========================================");

    // Calcular fechas dinámicas (últimos 7 días)
    $endDate = date('Y-m-d');
    $startDate = date('Y-m-d', strtotime('-7 days'));

    writeLog("Fechas: $startDate a $endDate");

    // ========================================
    // 1. EJECUTAR AWARDS (insertar nuevas transacciones)
    // ========================================
    writeLog("--- Ejecutando AWARDS ---");
    $awardsResult = executeEndpoint('award', $startDate, $endDate);

    if ($awardsResult['success']) {
        $summary = $awardsResult['processing_summary'];
        writeLog("Awards completado: inserted={$summary['inserted']}, refunds={$summary['refunds']}, skipped={$summary['skipped']}, errors={$summary['errors']}, filtered={$summary['filtered']}");
    } else {
        writeLog("ERROR en Awards: " . ($awardsResult['error'] ?? 'Unknown error'));
    }

    // Esperar 5 segundos entre requests
    sleep(5);

    // ========================================
    // 2. EJECUTAR BATCH DETAIL (actualizar estados)
    // ========================================
    writeLog("--- Ejecutando BATCH DETAIL ---");
    $batchResult = executeEndpoint('disbursementbatchdetail', $startDate, $endDate);

    if ($batchResult['success']) {
        $summary = $batchResult['processing_summary'];
        writeLog("Batch Detail completado: updated={$summary['updated']}, not_found={$summary['not_found']}, errors={$summary['errors']}, filtered={$summary['filtered']}");
    } else {
        writeLog("ERROR en Batch Detail: " . ($batchResult['error'] ?? 'Unknown error'));
    }

    $executionTime = microtime(true) - $startTime;
    writeLog("Tiempo total de ejecución: " . number_format($executionTime, 2) . " segundos");

    // ========================================
    // 3. DETERMINAR SI HAY QUE NOTIFICAR
    // ========================================
    $awardsInserted = isset($awardsResult['processing_summary']['inserted']) ? $awardsResult['processing_summary']['inserted'] : 0;
    $awardsRefunds = isset($awardsResult['processing_summary']['refunds']) ? $awardsResult['processing_summary']['refunds'] : 0;
    $awardsErrors = isset($awardsResult['processing_summary']['errors']) ? $awardsResult['processing_summary']['errors'] : 0;

    $batchUpdated = isset($batchResult['processing_summary']['updated']) ? $batchResult['processing_summary']['updated'] : 0;
    $batchErrors = isset($batchResult['processing_summary']['errors']) ? $batchResult['processing_summary']['errors'] : 0;

    $totalProcessed = $awardsInserted + $awardsRefunds + $batchUpdated;
    $totalErrors = $awardsErrors + $batchErrors;

    $shouldNotify = ($totalProcessed > 0) || ($totalErrors > 0);

    if ($shouldNotify) {
        writeLog("--- Enviando email de notificación ---");

        $subject = "Campus Ivy Sync - ";
        if ($totalErrors > 0) {
            $subject .= "⚠️ ERRORES DETECTADOS";
        } elseif ($totalProcessed > 0) {
            $subject .= "✅ $totalProcessed nuevos registros";
        } else {
            $subject .= "ℹ️ Sin cambios";
        }

        $subject .= " - " . date('Y-m-d H:i');

        $htmlBody = generateHtmlReport($awardsResult, $batchResult, $startDate, $endDate, $executionTime);

        sendNotificationEmail($subject, $htmlBody, $EMAIL_RECIPIENTS);
        writeLog("Email enviado a: " . implode(', ', $EMAIL_RECIPIENTS));
    } else {
        writeLog("No se envía email: sin cambios ni errores");
    }

    // ========================================
    // 4. REGISTRAR EN S_IVY_SYNC_LOG (resumen del cron)
    // ========================================
    global $db;

    $cron_log = array(
        'PK_ACCOUNT' => $PK_ACCOUNT,
        'SYNC_DATE' => date('Y-m-d H:i:s'),
        'TRANSACTION_TYPE' => 'cron_execution',
        'TRANSACTION_ID' => 'CRON_' . date('YmdHis'),
        'STATUS' => ($totalErrors > 0) ? 'error' : 'success',
        'ACTION_TAKEN' => 'cron_sync_completed',
        'DETAILS' => json_encode(array(
            'start_date' => $startDate,
            'end_date' => $endDate,
            'awards' => $awardsResult['processing_summary'] ?? null,
            'batch_detail' => $batchResult['processing_summary'] ?? null,
            'execution_time_seconds' => $executionTime,
            'notification_sent' => $shouldNotify
        )),
        'ERROR_MESSAGE' => ($totalErrors > 0) ? "Total errors: $totalErrors" : null,
        'CREATED_ON' => date('Y-m-d H:i:s')
    );

    db_perform('S_IVY_SYNC_LOG', $cron_log, 'insert');

    writeLog("========================================");
    writeLog("SINCRONIZACIÓN COMPLETADA");
    writeLog("Total procesado: $totalProcessed, Total errores: $totalErrors");
    writeLog("========================================");

    exit(0);

} catch (Exception $e) {
    $errorMsg = "EXCEPCIÓN FATAL: " . $e->getMessage();
    writeLog($errorMsg);

    // Enviar email de error crítico
    $subject = "🚨 Campus Ivy Sync - ERROR CRÍTICO - " . date('Y-m-d H:i');
    $body = '<html><body style="font-family: Arial, sans-serif; padding: 20px;">
        <div style="background-color: #dc3545; color: white; padding: 20px; border-radius: 8px;">
            <h2>Error Crítico en Sincronización Campus Ivy</h2>
        </div>
        <div style="padding: 20px; background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; margin-top: 20px;">
            <h3>Detalles del Error:</h3>
            <p><strong>Fecha/Hora:</strong> ' . date('Y-m-d H:i:s T') . '</p>
            <p><strong>Mensaje:</strong> ' . htmlspecialchars($e->getMessage()) . '</p>
            <p><strong>Archivo:</strong> ' . $e->getFile() . '</p>
            <p><strong>Línea:</strong> ' . $e->getLine() . '</p>
        </div>
        <div style="margin-top: 20px; padding: 15px; background-color: #fff3cd; border-radius: 4px;">
            <p><strong>Acción Requerida:</strong></p>
            <ul>
                <li>Revisar el log: <code>' . basename($LOG_FILE) . '</code></li>
                <li>Verificar la tabla <code>S_IVY_SYNC_LOG</code></li>
                <li>Contactar al equipo de IT si el error persiste</li>
            </ul>
        </div>
    </body></html>';

    sendNotificationEmail($subject, $body, $EMAIL_RECIPIENTS);

    // Registrar en S_IVY_SYNC_LOG
    try {
        $error_log = array(
            'PK_ACCOUNT' => $PK_ACCOUNT,
            'SYNC_DATE' => date('Y-m-d H:i:s'),
            'TRANSACTION_TYPE' => 'cron_execution',
            'TRANSACTION_ID' => 'CRON_ERROR_' . date('YmdHis'),
            'STATUS' => 'error',
            'ACTION_TAKEN' => 'cron_fatal_error',
            'ERROR_MESSAGE' => $e->getMessage(),
            'DETAILS' => json_encode(array(
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            )),
            'CREATED_ON' => date('Y-m-d H:i:s')
        );

        db_perform('S_IVY_SYNC_LOG', $error_log, 'insert');
    } catch (Exception $logError) {
        writeLog("No se pudo registrar en S_IVY_SYNC_LOG: " . $logError->getMessage());
    }

    exit(1);
}
?>

