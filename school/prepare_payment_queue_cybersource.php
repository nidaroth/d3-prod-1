<?php
// /D3/school/prepare_payment_queue_cybersource.php
// Ejecutar 30 minutos antes del cron de pagos
$path = '/var/www/html/D3/';
require_once($path."global/config.php");

$date = date("Y-m-d");
$lb = php_sapi_name() == 'cli' ? "\n" : "<br />";

echo "Preparando cola de pagos CyberSource para: $date" . $lb;

// Obtener disbursements elegibles
$sql = "SELECT 
    SD.PK_STUDENT_DISBURSEMENT,
    SD.PK_STUDENT_MASTER,
    SD.PK_STUDENT_ENROLLMENT,
    SD.PK_ACCOUNT,
    SD.DISBURSEMENT_AMOUNT
FROM S_STUDENT_DISBURSEMENT SD
JOIN S_STUDENT_MASTER SM ON SD.PK_STUDENT_MASTER = SM.PK_STUDENT_MASTER
JOIN Z_ACCOUNT ZA ON SD.PK_ACCOUNT = ZA.PK_ACCOUNT
WHERE SD.PK_DISBURSEMENT_STATUS = 2
AND SD.DISBURSEMENT_DATE = '$date'
AND SM.ENABLE_AUTO_PAYMENT = 1
AND ZA.ENABLE_DIAMOND_PAY = 3
AND SM.ARCHIVED = 0";

$res = $db->Execute($sql);

echo "Disbursements encontrados: " . $res->RecordCount() . $lb;

$added = 0;
while (!$res->EOF) {
    // Verificar tarjeta primaria
    $res_card = $db->Execute("SELECT PK_STUDENT_CREDIT_CARD_CYBERSOURCE 
                             FROM S_STUDENT_CREDIT_CARD_CYBERSOURCE 
                             WHERE PK_STUDENT_MASTER = '".$res->fields['PK_STUDENT_MASTER']."'
                             AND PK_ACCOUNT = '".$res->fields['PK_ACCOUNT']."'
                             AND IS_PRIMARY = 1
                             AND ACTIVE = 1
                             AND CUSTOMER_ID IS NOT NULL");
    
    if($res_card->RecordCount() > 0) {
        // Verificar si ya existe en la cola
        $res_check = $db->Execute("SELECT 1 FROM M_PYAMENT_CRON_QUEUE 
                                  WHERE PK_STUDENT_DISBURSEMENT = '".$res->fields['PK_STUDENT_DISBURSEMENT']."'
                                  AND DISBURSEMENT_DATE = '$date'");
        
        if($res_check->RecordCount() == 0) {
            $QUEUE_DATA = [
                'DATE_CREATED' => date("Y-m-d H:i:s"),
                'PK_STUDENT_DISBURSEMENT' => $res->fields['PK_STUDENT_DISBURSEMENT'],
                'PK_STUDENT_MASTER' => $res->fields['PK_STUDENT_MASTER'],
                'PK_STUDENT_ENROLLMENT' => $res->fields['PK_STUDENT_ENROLLMENT'],
                'PK_STUDENT_CREDIT_CARD' => $res_card->fields['PK_STUDENT_CREDIT_CARD_CYBERSOURCE'],
                'PK_ACCOUNT' => $res->fields['PK_ACCOUNT'],
                'DISBURSEMENT_DATE' => $date,
                'DISBURSEMENT_AMOUNT' => $res->fields['DISBURSEMENT_AMOUNT'],
                'PAYMENT_GATEWAY' => 'CYBERSOURCE',
                'CRON_STATUS' => 0,
                'PAYMENT_STATUS' => 'PENDING'
            ];
            
            db_perform('M_PYAMENT_CRON_QUEUE', $QUEUE_DATA, 'insert');
            $added++;
            echo "  ✓ Agregado: Disbursement " . $res->fields['PK_STUDENT_DISBURSEMENT'] . $lb;
        }
    }
    
    $res->MoveNext();
}

echo "Total agregados a la cola: $added" . $lb;
echo "Completado: " . date('Y-m-d H:i:s') . $lb;
?>
