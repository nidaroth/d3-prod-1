<?php
/**
 * population report dvb - VERSIÓN FINAL CORREGIDA
 * Fix: Reconectar DB después de cada SP call
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
set_time_limit(0);
ini_set('memory_limit', '4096M');

require_once("../global/config.php");
require_once("../language/population_report_setup.php");

/// accounts
$res_dashboard = $db->Execute("
    SELECT GROUP_CONCAT(PK_ACCOUNT ORDER BY PK_ACCOUNT SEPARATOR ',') AS pk_accounts
    FROM Z_ACCOUNT
    WHERE CAMPUSIQ_ENABLE = 1;
");
$pk_accounts_csv = $res_dashboard->fields['pk_accounts'] ?? '';
$arrayaccounts = array_values(array_filter(array_map('trim', explode(',', $pk_accounts_csv))));
// Opcional: Limpiar tabla antes de empezar
// $db->Execute("DELETE FROM CAMPUS_IQ_POPULATION_REPORT WHERE PK_ACCOUNT = 95");

// Generar rangos de meses desde 2024-01 hasta hoy
$current = new DateTime('first day of this month');
$today = new DateTime();
$monthCounter = 0;

echo "<pre>";
echo "=== Population Report Generator ===\n";
echo "Procesando desde 2024-01 hasta " . $today->format('Y-m') . "\n\n";


while ($current <= $today) {
    $monthCounter++;
    
    // Calcular fechas del mes
    $ST = $current->format('Y-m-01');
    $lastDay = clone $current;
    $lastDay->modify('last day of this month');
    $ET = $lastDay->format('Y-m-d');
    
    // Si es el mes actual, usar hoy como fecha final
    if ($current->format('Y-m') == $today->format('Y-m')) {
        $ET = $today->format('Y-m-d');
    }
    
    $MONTH = $current->format('m');
    $YEAR = $current->format('Y');
    $monthLabel = $current->format('F Y');
    
    echo "[$monthCounter] $monthLabel ($ST a $ET)... ";


    flush();
    ob_flush();
    
    // Loop por cada account
    foreach ($arrayaccounts as $PK_ACCOUNT) {
        // Obtener campus
        $campus_id = '';
        $campus_name = '';
        $res_campus = $db->Execute("SELECT PK_CAMPUS, CAMPUS_CODE 
            FROM S_CAMPUS 
            WHERE ACTIVE = 1 AND PK_ACCOUNT = '$PK_ACCOUNT'  
            ORDER BY CAMPUS_CODE ASC");

        print_r($res_campus);
        
        while (!$res_campus->EOF) {
            if ($campus_name != '') $campus_name .= ', ';
            $campus_name .= $res_campus->fields['CAMPUS_CODE'];
            if ($campus_id != '') $campus_id .= ',';
            $campus_id .= $res_campus->fields['PK_CAMPUS'];
            $res_campus->MoveNext();
        }
        
        if (empty($campus_id)) {
            echo "✗ ERROR: No campus \n";
            continue
;        }
        
        // Ejecutar el stored procedure
        try {
            $start_time = microtime(true);
            
            echo "CALL DSIS10002_DVB($PK_ACCOUNT, '$campus_id', 0, '$ST', '$ET', 'ALL', 'PROGRAM', 'DETAIL')";
            #continue;
            #$res = $db->Execute("CALL DSIS10002_DVB($PK_ACCOUNT, '$campus_id', 0, '$ST', '$ET', 'ALL', 'PROGRAM', 'DETAIL')");
            // ============================================================
            // CRÍTICO: Reconectar DB después del SP
            // ============================================================
            $db->close();
            $db->connect($db_host, 'root', $db_pass, $db_name);
            
            $end_time = microtime(true);
            $execution_time = round($end_time - $start_time, 2);
            
            // Verificar registros insertados
            $check_res = $db->Execute("SELECT COUNT(*) as total 
                          FROM CAMPUS_IQ_POPULATION_REPORT 
                          WHERE MONTH = $MONTH AND YEAR = $YEAR AND PK_ACCOUNT = $PK_ACCOUNT");
            $total_for_month = $check_res->fields['total'];
            
            echo "✓ OK ".$PK_ACCOUNT." ({$execution_time}s) - " . number_format($total_for_month) . " registros\n";
            
        } catch (Exception $e) {
            echo "✗ ERROR: " . $e->getMessage() . "\n";
            
            // Intentar reconectar aunque haya error
            try {
                $db->close();
                $db->connect($db_host, 'root', $db_pass, $db_name);
            } catch (Exception $e2) {
                // Ignorar error de reconexión
                echo 'error';
            }
        }
    }
    
    flush();
    ob_flush();
    
    // Avanzar al siguiente mes
    $current->modify('+1 month');
}

            
// Resumen final
echo "\n=== RESUMEN FINAL ===\n";
echo "Meses procesados: $monthCounter\n\n";

$summary_res = $db->Execute("SELECT YEAR, MONTH, COUNT(*) as registros, COUNT(DISTINCT PK_STUDENT_MASTER) as estudiantes
                FROM CAMPUS_IQ_POPULATION_REPORT
                WHERE PK_ACCOUNT = 95
                GROUP BY YEAR, MONTH
                ORDER BY YEAR, MONTH");

echo "Distribución de registros:\n";
$total_general = 0;
while (!$summary_res->EOF) {
    $year = $summary_res->fields['YEAR'];
    $month = $summary_res->fields['MONTH'];
    $registros = $summary_res->fields['registros'];
    $estudiantes = $summary_res->fields['estudiantes'];
    $total_general += $registros;
    
    $month_name = date('M Y', mktime(0, 0, 0, $month, 1, $year));
    echo "  $month_name: " . number_format($registros) . " registros (" . number_format($estudiantes) . " estudiantes)\n";
    
    $summary_res->MoveNext();
}

echo "\nTOTAL GENERAL: " . number_format($total_general) . " registros\n";
echo "\n=== PROCESO COMPLETADO ===\n";
echo "</pre><br>AHORA EJECUTAMOS EL ARCIVO campusiq_populate.php";
echo "\n=== Ejecutando campusiq_populate.php ===\n";
// Ajusta la ruta si este archivo está en otro folder
require_once '../nubo/campusiq_populate.php';
echo "\n=== campusiq_populate.php finalizado ===\n";
?>