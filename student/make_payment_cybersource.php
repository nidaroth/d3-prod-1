<?php
// /D3/student/make_payment_cybersource.php
require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/make_payment.php");

// Verificar acceso
$res_pay = $db->Execute("SELECT ENABLE_DIAMOND_PAY FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
if($res_pay->fields['ENABLE_DIAMOND_PAY'] != 3) {
    header("location:../index");
    exit;
}

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER_TYPE'] != 3){
    header("location:../index");
    exit;
}

$URL = ($_GET['page'] == 'p') ? 'payments' : 'index';
$msg = "";

// Obtener configuración de Cybersource
$res_cs = $db->Execute("SELECT * FROM S_CYBERSOURCE_SETTINGS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
if(!$res_cs || $res_cs->RecordCount() == 0) {
    die("Configuración de Cybersource no encontrada");
}

$CYBERSOURCE_CONFIG = [
    'MERCHANT_ID' => $res_cs->fields['MERCHANT_ID'],
    'KEY_ID' => $res_cs->fields['KEY_ID'],
    'SECRET_KEY' => $res_cs->fields['SECRET_KEY'],
    'ENVIRONMENT' => $res_cs->fields['ENVIRONMENT'] ?: 'apitest.cybersource.com'
];

// Función para procesar pago con Cybersource
function processCyberSourcePayment($config, $customerId, $paymentInstrumentId, $amount) {
    try {
        $host = $config['ENVIRONMENT'];
        $merchantId = $config['MERCHANT_ID'];
        $keyId = $config['KEY_ID'];
        $secretKey = $config['SECRET_KEY'];
        
        $requestBody = [
            'clientReferenceInformation' => [
                'code' => 'STUDENT_' . time()
            ],
            'processingInformation' => [
                'capture' => true,
                'commerceIndicator' => 'internet'
            ],
            'paymentInformation' => [
                'customer' => ['id' => $customerId],
                'paymentInstrument' => ['id' => $paymentInstrumentId]
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
        
        $ch = curl_init("https://$host/pts/v2/payments");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBodyJson);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception("cURL Error: $error");
        }
        
        $responseData = json_decode($response, true);
        
        return [
            'success' => $httpCode >= 200 && $httpCode < 300,
            'httpCode' => $httpCode,
            'response' => $responseData,
            'transactionId' => $responseData['id'] ?? null,
            'status' => $responseData['status'] ?? null,
            'message' => $responseData['message'] ?? null
        ];
        
    } catch (Exception $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

// Obtener información del disbursement
$disbursementId = $_GET['id'] ?? 0;

// Para múltiples disbursements
$PK_STUDENT_DISBURSEMENT_ARR = explode(",", $disbursementId);
$AMOUNT = 0;
$disbursements = [];

foreach($PK_STUDENT_DISBURSEMENT_ARR as $PK_STUDENT_DISBURSEMENT) {
    $res_disb = $db->Execute("SELECT sd.*, mlc.CODE, mlc.LEDGER_DESCRIPTION
                             FROM S_STUDENT_DISBURSEMENT sd
                             LEFT JOIN M_AR_LEDGER_CODE mlc ON sd.PK_AR_LEDGER_CODE = mlc.PK_AR_LEDGER_CODE
                             WHERE sd.PK_STUDENT_DISBURSEMENT = '$PK_STUDENT_DISBURSEMENT'
                             AND sd.PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]'
                             AND sd.PK_DISBURSEMENT_STATUS = 2");
    
    if($res_disb->RecordCount() > 0) {
        $disbursements[] = $res_disb->fields;
        $AMOUNT += $res_disb->fields['DISBURSEMENT_AMOUNT'];
    }
}

if(count($disbursements) == 0) {
    header("location:$URL");
    exit;
}

$PK_STUDENT_ENROLLMENT = $disbursements[0]['PK_STUDENT_ENROLLMENT'];

// Procesar pago cuando se envía el formulario
if(!empty($_POST) && isset($_POST['process_payment'])) {
    
    $cardId = $_POST['PK_STUDENT_CREDIT_CARD_CYBERSOURCE'] ?? 0;
    
    if($cardId) {
        // Obtener la tarjeta seleccionada
        $res_card = $db->Execute("SELECT * FROM S_STUDENT_CREDIT_CARD_CYBERSOURCE 
                                 WHERE PK_STUDENT_CREDIT_CARD_CYBERSOURCE = '$cardId'
                                 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'
                                 AND PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]'
                                 AND ACTIVE = 1");
        
        if($res_card->RecordCount() > 0 &&
           $res_card->fields['CUSTOMER_ID'] &&
           $res_card->fields['PAYMENT_INSTRUMENT_ID']) {
            
            // Procesar el pago
            $result = processCyberSourcePayment(
                $CYBERSOURCE_CONFIG,
                $res_card->fields['CUSTOMER_ID'],
                $res_card->fields['PAYMENT_INSTRUMENT_ID'],
                $AMOUNT
            );
            
            if($result['success']) {
                // Registrar pago exitoso
                $PAYMENT_DATA = [
                    'PK_STUDENT_MASTER' => $_SESSION['PK_STUDENT_MASTER'],
                    'PK_STUDENT_ENROLLMENT' => $PK_STUDENT_ENROLLMENT,
                    'PK_ACCOUNT' => $_SESSION['PK_ACCOUNT'],
                    'PAYMENT_FOR' => 'DISBURSEMENT',
                    'PK_STUDENT_CREDIT_CARD' => $cardId,
                    'ID' => $result['transactionId'],
                    'ORDER_ID' => 'STUDENT_' . time(),
                    'AMOUNT_CHARGED' => $AMOUNT,
                    'TOTAL_CHARGE' => $AMOUNT,
                    'PAID_ON' => date('Y-m-d'),
                    'CARD_NO' => '****' . $res_card->fields['CARD_LAST_FOUR'],
                    'CARD_NAME' => $res_card->fields['NAME_ON_CARD'],
                    'CARD_TYPE' => $res_card->fields['CARD_BRAND'],
                    'FAILED' => 0,
                    'ACTIVE' => 1,
                    'CREATED_BY' => $_SESSION['PK_USER'],
                    'CREATED_ON' => date('Y-m-d H:i:s')
                ];
                
                // Insertar el pago
                $fields = array_keys($PAYMENT_DATA);
                $values = array_map(function($v) {
                    return is_numeric($v) ? $v : "'" . addslashes($v) . "'";
                }, array_values($PAYMENT_DATA));
                
                $sql = "INSERT INTO S_STUDENT_CREDIT_CARD_PAYMENT 
                       (" . implode(', ', $fields) . ") 
                       VALUES (" . implode(', ', $values) . ")";
                $db->Execute($sql);
                $PK_STUDENT_CREDIT_CARD_PAYMENT = $db->insert_ID();
                
                // Actualizar cada disbursement
                foreach($PK_STUDENT_DISBURSEMENT_ARR as $PK_STUDENT_DISBURSEMENT) {
                    $db->Execute("UPDATE S_STUDENT_DISBURSEMENT 
                                 SET PK_DISBURSEMENT_STATUS = 1,
                                     DEPOSITED_DATE = CURDATE()
                                 WHERE PK_STUDENT_DISBURSEMENT = '$PK_STUDENT_DISBURSEMENT'");
                    
                    // Crear entrada en el ledger si la tabla existe
                    $res_check = $db->Execute("SHOW TABLES LIKE 'S_STUDENT_LEDGER'");
                    if($res_check->RecordCount() > 0) {
                        $res_ledger = $db->Execute("SELECT PK_AR_LEDGER_CODE, DISBURSEMENT_AMOUNT 
                                                   FROM S_STUDENT_DISBURSEMENT 
                                                   WHERE PK_STUDENT_DISBURSEMENT = '$PK_STUDENT_DISBURSEMENT'");
                        
                        $LEDGER_DATA = [
                            'PK_ACCOUNT' => $_SESSION['PK_ACCOUNT'],
                            'PK_STUDENT_MASTER' => $_SESSION['PK_STUDENT_MASTER'],
                            'PK_STUDENT_ENROLLMENT' => $PK_STUDENT_ENROLLMENT,
                            'PK_STUDENT_DISBURSEMENT' => $PK_STUDENT_DISBURSEMENT,
                            'PK_STUDENT_CREDIT_CARD_PAYMENT' => $PK_STUDENT_CREDIT_CARD_PAYMENT,
                            'TRANSACTION_DATE' => date('Y-m-d'),
                            'PK_AR_LEDGER_CODE' => $res_ledger->fields['PK_AR_LEDGER_CODE'],
                            'CREDIT' => $res_ledger->fields['DISBURSEMENT_AMOUNT'],
                            'DEBIT' => 0,
                            'ACTIVE' => 1,
                            'CREATED_ON' => date('Y-m-d H:i:s'),
                            'CREATED_BY' => $_SESSION['PK_USER']
                        ];
                        
                        $ledger_fields = array_keys($LEDGER_DATA);
                        $ledger_values = array_map(function($v) {
                            return is_numeric($v) ? $v : "'" . addslashes($v) . "'";
                        }, array_values($LEDGER_DATA));
                        
                        $sql_ledger = "INSERT INTO S_STUDENT_LEDGER 
                                      (" . implode(', ', $ledger_fields) . ") 
                                      VALUES (" . implode(', ', $ledger_values) . ")";
                        $db->Execute($sql_ledger);
                    }
                }
                
                // Redirigir con éxito
                echo "<script>
                        alert('Pago procesado exitosamente!');
                        window.location.href='$URL';
                      </script>";
                exit;
                
            } else {
                $msg = "Error procesando el pago: " . ($result['error'] ?? 'Error desconocido');
            }
        } else {
            $msg = "Tarjeta no válida o no tokenizada";
        }
    } else {
        $msg = "Por favor seleccione una tarjeta";
    }
}

// Obtener tarjetas del estudiante
$res_cards = $db->Execute("SELECT * FROM S_STUDENT_CREDIT_CARD_CYBERSOURCE 
                          WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' 
                          AND PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]'
                          AND ACTIVE = 1
                          AND CUSTOMER_ID IS NOT NULL
                          AND PAYMENT_INSTRUMENT_ID IS NOT NULL
                          ORDER BY IS_PRIMARY DESC");

// Obtener info del estudiante
$res_student = $db->Execute("SELECT FIRST_NAME, LAST_NAME FROM S_STUDENT_MASTER 
                            WHERE PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]'");
$STUDENT_NAME = $res_student->fields['FIRST_NAME'] . ' ' . $res_student->fields['LAST_NAME'];

$res_academics = $db->Execute("SELECT STUDENT_ID FROM S_STUDENT_ACADEMICS 
                              WHERE PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]'");
$STUDENT_ID = $res_academics->fields['STUDENT_ID'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Realizar Pago - Cybersource</title>
    <? require_once("css.php"); ?>
</head>
<body class="horizontal-nav boxed skin-megna fixed-layout">
    <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
        <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row page-titles">
                    <div class="col-md-6 align-self-center">
                        <h4 class="text-themecolor">Realizar Pago</h4>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <? if($msg != '') { ?>
                                <div class="alert alert-danger"><?=$msg?></div>
                                <? } ?>
                                
                                <form method="post" name="paymentForm" id="paymentForm">
                                    <input type="hidden" name="process_payment" value="1">
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h5>Información del Pago</h5>
                                            <table class="table">
                                                <tr>
                                                    <td><strong>Estudiante:</strong></td>
                                                    <td><?=$STUDENT_NAME?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>ID:</strong></td>
                                                    <td><?=$STUDENT_ID?></td>
                                                </tr>
                                                <? foreach($disbursements as $disb) { ?>
                                                <tr>
                                                    <td><strong>Descripción:</strong></td>
                                                    <td><?=$disb['CODE']?> - <?=$disb['LEDGER_DESCRIPTION']?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Monto:</strong></td>
                                                    <td>$<?=number_format($disb['DISBURSEMENT_AMOUNT'], 2)?></td>
                                                </tr>
                                                <tr>
                                                    <td colspan="2"><hr></td>
                                                </tr>
                                                <? } ?>
                                                <tr>
                                                    <td><strong>Monto Total:</strong></td>
                                                    <td style="font-size: 1.2em; color: #0d6efd;">
                                                        <strong>$<?=number_format($AMOUNT, 2)?></strong>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <h5>Seleccionar Tarjeta</h5>
                                            
                                            <? if($res_cards->RecordCount() > 0) { ?>
                                            <div class="form-group">
                                                <select name="PK_STUDENT_CREDIT_CARD_CYBERSOURCE"
                                                        id="PK_STUDENT_CREDIT_CARD_CYBERSOURCE"
                                                        class="form-control" required>
                                                    <option value="">Seleccione una tarjeta...</option>
                                                    <? while(!$res_cards->EOF) { ?>
                                                    <option value="<?=$res_cards->fields['PK_STUDENT_CREDIT_CARD_CYBERSOURCE']?>">
                                                        •••• <?=$res_cards->fields['CARD_LAST_FOUR']?> -
                                                        <?=$res_cards->fields['NAME_ON_CARD']?>
                                                        (<?=$res_cards->fields['CARD_BRAND']?>)
                                                        <?=$res_cards->fields['IS_PRIMARY'] ? ' - PRIMARIA' : ''?>
                                                    </option>
                                                    <? $res_cards->MoveNext();
                                                    } ?>
                                                </select>
                                            </div>
                                            
                                            <div class="form-group">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fa fa-credit-card"></i> Procesar Pago
                                                </button>
                                                <a href="add_cc_cybersource" class="btn btn-info">
                                                    <i class="fa fa-plus"></i> Agregar Tarjeta
                                                </a>
                                                <a href="<?=$URL?>" class="btn btn-secondary">
                                                    Cancelar
                                                </a>
                                            </div>
                                            <? } else { ?>
                                            <div class="alert alert-warning">
                                                No hay tarjetas guardadas.
                                                <br><br>
                                                <a href="add_cc_cybersource" class="btn btn-primary">
                                                    <i class="fa fa-plus"></i> Agregar Primera Tarjeta
                                                </a>
                                            </div>
                                            <? } ?>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <? require_once("footer.php"); ?>
    </div>
    
    <? require_once("js.php"); ?>
    
    <script>
    document.getElementById('paymentForm').addEventListener('submit', function(e) {
        const cardSelect = document.getElementById('PK_STUDENT_CREDIT_CARD_CYBERSOURCE');
        if (!cardSelect || !cardSelect.value) {
            e.preventDefault();
            alert('Por favor seleccione una tarjeta');
            return false;
        }
        
        if (!confirm('¿Confirmar pago por $<?=number_format($AMOUNT, 2)?>?')) {
            e.preventDefault();
            return false;
        }
    });
    </script>
</body>
</html>

