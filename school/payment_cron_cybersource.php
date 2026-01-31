<?php
// /D3/school/payment_cron_cybersource.php
// Ejecutar diariamente para procesar pagos recurrentes
$path = '/var/www/html/D3/';
require_once($path."global/config.php");
require_once($path."global/mail.php");
require_once($path."school/function_student_ledger.php");

$date = date("Y-m-d");
$lb = php_sapi_name() == 'cli' ? "\n" : "<br />";

echo "Iniciando proceso de pagos recurrentes CyberSource - $date" . $lb;

// Función para procesar pago
function processCyberSourcePayment($config, $customerId, $paymentInstrumentId, $amount) {
    try {
        $host = $config['ENVIRONMENT'];
        $merchantId = $config['MERCHANT_ID'];
        $keyId = $config['KEY_ID'];
        $secretKey = $config['SECRET_KEY'];
        
        $requestBody = [
            'clientReferenceInformation' => [
                'code' => 'RECUR_' . time()
            ],
            'processingInformation' => [
                'capture' => true,
                'commerceIndicator' => 'recurring'
            ],
            'paymentInformation' => [
                'customer' => [
                    'id' => $customerId
                ],
                'paymentInstrument' => [
                    'id' => $paymentInstrumentId
                ]
            ],
            'orderInformation' => [
                'amountDetails' => [
                    'totalAmount' => number_format($amount, 2, '.', ''),
                    'currency' => 'USD'
                ]
            ]
        ];
        
        $requestBodyJson = json_encode($requestBody);
        $date = gmdate('D, d M Y H:i:s') . ' GMT';
        $digest = base64_encode(hash('sha256', $requestBodyJson, true));
        $requestTarget = 'post /pts/v2/payments';
        
        $signingString = "host: $host\n";
        $signingString .= "date: $date\n";
        $signingString .= "request-target: $requestTarget\n";
        $signingString .= "digest: SHA-256=$digest\n";
        $signingString .= "v-c-merchant-id: $merchantId";
        
        $signature = base64_encode(hash_hmac('sha256', $signingString, base64_decode($secretKey), true));
        
        $headers = [
            "Host: $host",
            "Date: $date",
            "Digest: SHA-256=$digest",
            "v-c-merchant-id: $merchantId",
            "Signature: keyid=\"$keyId\", algorithm=\"HmacSHA256\", headers=\"host date request-target digest v-c-merchant-id\", signature=\"$signature\"",
            "Content-Type: application/json"
        ];
        
        $url = "https://$host/pts/v2/payments";
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBodyJson);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $responseData = json_decode($response, true);
        
        return [
            'success' => $httpCode >= 200 && $httpCode < 300,
            'transactionId' => $responseData['id'] ?? null,
            'response' => $responseData
        ];
        
    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

// Obtener pagos pendientes
$sql = "SELECT * FROM M_PYAMENT_CRON_QUEUE 
        WHERE DISBURSEMENT_DATE = '$date' 
        AND CRON_STATUS = 0 
        AND PAYMENT_GATEWAY = 'CYBERSOURCE'";

$res_queue = $db->Execute($sql);

echo "Pagos pendientes: " . $res_queue->RecordCount() . $lb;

while (!$res_queue->EOF) {
    $PK_STUDENT_DISBURSEMENT = $res_queue->fields['PK_STUDENT_DISBURSEMENT'];
    $PK_STUDENT_MASTER = $res_queue->fields['PK_STUDENT_MASTER'];
    $PK_ACCOUNT = $res_queue->fields['PK_ACCOUNT'];
    $AMOUNT = $res_queue->fields['DISBURSEMENT_AMOUNT'];
    
    echo "Procesando disbursement $PK_STUDENT_DISBURSEMENT..." . $lb;
    
    // Obtener configuración
    $res_config = $db->Execute("SELECT * FROM S_CYBERSOURCE_SETTINGS WHERE PK_ACCOUNT = '$PK_ACCOUNT'");
    if($res_config->RecordCount() == 0) {
        echo "  ✗ Sin configuración de CyberSource" . $lb;
        $res_queue->MoveNext();
        continue;
    }
    
    $CONFIG = [
        'MERCHANT_ID' => $res_config->fields['MERCHANT_ID'],
        'KEY_ID' => $res_config->fields['KEY_ID'],
        'SECRET_KEY' => $res_config->fields['SECRET_KEY'],
        'ENVIRONMENT' => $res_config->fields['ENVIRONMENT'] ?: 'apitest.cybersource.com'
    ];
    
    // Obtener tarjeta primaria
    $res_card = $db->Execute("SELECT * FROM S_STUDENT_CREDIT_CARD_CYBERSOURCE 
                             WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' 
                             AND PK_ACCOUNT = '$PK_ACCOUNT'
                             AND IS_PRIMARY = 1 
                             AND ACTIVE = 1
                             AND CUSTOMER_ID IS NOT NULL
                             AND PAYMENT_INSTRUMENT_ID IS NOT NULL");
    
    if($res_card->RecordCount() == 0) {
        echo "  ✗ Sin tarjeta primaria válida" . $lb;
        
        // Actualizar queue
        $db->Execute("UPDATE M_PYAMENT_CRON_QUEUE 
                     SET CRON_STATUS = 2, 
                         PAYMENT_STATUS = 'NO_CARD',
                         LAST_UPDATED = NOW()
                     WHERE PK_STUDENT_DISBURSEMENT = '$PK_STUDENT_DISBURSEMENT'");
        
        $res_queue->MoveNext();
        continue;
    }
    
    // Procesar pago
    $result = processCyberSourcePayment(
        $CONFIG,
        $res_card->fields['CUSTOMER_ID'],
        $res_card->fields['PAYMENT_INSTRUMENT_ID'],
        $AMOUNT
    );
    
    if($result['success']) {
        echo "  ✓ Pago exitoso: " . $result['transactionId'] . $lb;
        
        // Registrar pago exitoso
        $PAYMENT_DATA = [
            'PK_STUDENT_MASTER' => $PK_STUDENT_MASTER,
            'PK_STUDENT_ENROLLMENT' => $res_queue->fields['PK_STUDENT_ENROLLMENT'],
            'PK_ACCOUNT' => $PK_ACCOUNT,
            'PAYMENT_FOR' => 'DISBURSEMENT',
            'PK_STUDENT_CREDIT_CARD' => $res_card->fields['PK_STUDENT_CREDIT_CARD_CYBERSOURCE'],
            'ID' => $result['transactionId'],
            'ORDER_ID' => 'RECUR_' . time(),
            'AMOUNT_CHARGED' => $AMOUNT,
            'TOTAL_CHARGE' => $AMOUNT,
            'PAID_ON' => date('Y-m-d'),
            'CARD_NO' => '****' . $res_card->fields['CARD_LAST_FOUR'],
            'CARD_NAME' => $res_card->fields['NAME_ON_CARD'],
            'CARD_TYPE' => $res_card->fields['CARD_BRAND'],
            'FAILED' => 0,
            'ACTIVE' => 1,
            'CREATED_BY' => 0,
            'CREATED_ON' => date('Y-m-d H:i:s')
        ];
        
        db_perform('S_STUDENT_CREDIT_CARD_PAYMENT', $PAYMENT_DATA, 'insert');
        $PK_PAYMENT = $db->insert_ID();
        
        // Actualizar disbursement
        $db->Execute("UPDATE S_STUDENT_DISBURSEMENT 
                     SET PK_DISBURSEMENT_STATUS = 1,
                         DEPOSITED_DATE = CURDATE(),
                         PK_STUDENT_CREDIT_CARD_PAYMENT = '$PK_PAYMENT'
                     WHERE PK_STUDENT_DISBURSEMENT = '$PK_STUDENT_DISBURSEMENT'");
        
        // Actualizar queue
        $db->Execute("UPDATE M_PYAMENT_CRON_QUEUE 
                     SET CRON_STATUS = 1,
                         PAYMENT_STATUS = 'SUCCESS',
                         LAST_UPDATED = NOW()
                     WHERE PK_STUDENT_DISBURSEMENT = '$PK_STUDENT_DISBURSEMENT'");
        
    } else {
        echo "  ✗ Pago fallido: " . ($result['error'] ?? 'Error desconocido') . $lb;
        
        // Actualizar queue
        $db->Execute("UPDATE M_PYAMENT_CRON_QUEUE 
                     SET CRON_STATUS = 2,
                         PAYMENT_STATUS = 'FAILED',
                         NOTE = '" . addslashes($result['error'] ?? '') . "',
                         LAST_UPDATED = NOW()
                     WHERE PK_STUDENT_DISBURSEMENT = '$PK_STUDENT_DISBURSEMENT'");
    }
    
    $res_queue->MoveNext();
}

echo "Proceso completado - " . date('Y-m-d H:i:s') . $lb;
?>

