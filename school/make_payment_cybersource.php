<?php
// /D3/school/make_payment_cybersource.php
require_once("../global/config.php");
require_once("../global/mail.php");
require_once("../global/texting.php");
require_once("function_student_ledger.php");
require_once("function_update_disbursement_status.php");

// Verificar acceso
$res_pay = $db->Execute("SELECT ENABLE_DIAMOND_PAY FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
if($res_pay->fields['ENABLE_DIAMOND_PAY'] != 3) {
    header("location:../index");
    exit;
}

if($_SESSION['PK_USER'] == 0){
    header("location:../index");
    exit;
}

$msg = "";
$PK_STUDENT_MASTER = $_GET['sid'] ?? 0;
$PK_STUDENT_ENROLLMENT = $_GET['eid'] ?? 0;

// Obtener configuración de CyberSource
$res_cs = $db->Execute("SELECT * FROM S_CYBERSOURCE_SETTINGS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
if(!$res_cs || $res_cs->RecordCount() == 0) {
    die("Error: Configuración de CyberSource no encontrada");
}

$CYBERSOURCE_CONFIG = [
    'MERCHANT_ID' => $res_cs->fields['MERCHANT_ID'],
    'KEY_ID' => $res_cs->fields['KEY_ID'],
    'SECRET_KEY' => $res_cs->fields['SECRET_KEY'],
    'ENVIRONMENT' => $res_cs->fields['ENVIRONMENT'] ?: 'apitest.cybersource.com'
];

// Función para procesar pago con token
function processCyberSourcePaymentWithToken($config, $customerId, $paymentInstrumentId, $amount, $description = '') {
    try {
        $host = $config['ENVIRONMENT'];
        $merchantId = $config['MERCHANT_ID'];
        $keyId = $config['KEY_ID'];
        $secretKey = $config['SECRET_KEY'];
        
        $requestBody = [
            'clientReferenceInformation' => [
                'code' => 'DIAM_' . time()
            ],
            'processingInformation' => [
                'capture' => true,
                'commerceIndicator' => 'internet'
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
        
        // Generar headers
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
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

// Función para crear batch (similar a make_payment_stax)
function make_payment_cybersource_batch($data, $payment_result) {
    global $db;
    
    $PK_ACCOUNT = $data['PK_ACCOUNT'];
    $PK_STUDENT_MASTER = $data['PK_STUDENT_MASTER'];
    $PK_STUDENT_ENROLLMENT = $data['PK_STUDENT_ENROLLMENT'];
    $TYPE = $data['TYPE'];
    $ID = $data['ID'];
    $PK_STUDENT_CREDIT_CARD_PAYMENT = $data['PK_STUDENT_CREDIT_CARD_PAYMENT'];
    $TRANSACTION_ID = $payment_result['transactionId'];
    
    if($TYPE == 'disp') {
        // Obtener nombre del estudiante
        $res_stud_name = $db->Execute("SELECT FIRST_NAME, LAST_NAME FROM S_STUDENT_MASTER 
                                       WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' 
                                       AND PK_ACCOUNT = '$PK_ACCOUNT'");
        
        // Crear PAYMENT_BATCH_MASTER
        $res_acc = $db->Execute("SELECT PAYMENT_BATCH_NO FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$PK_ACCOUNT'");
        
        $PAYMENT_BATCH_MASTER['BATCH_NO'] = 'P'.$res_acc->fields['PAYMENT_BATCH_NO'];
        $PAYMENT_BATCH_MASTER['AUTOMATIC_BATCH'] = 1;
        $PAYMENT_BATCH_MASTER['PK_BATCH_STATUS'] = 2; // Posted
        $PAYMENT_BATCH_MASTER['POSTED_DATE'] = date("Y-m-d");
        $PAYMENT_BATCH_MASTER['DATE_RECEIVED'] = date("Y-m-d");
        $PAYMENT_BATCH_MASTER['PK_ACCOUNT'] = $PK_ACCOUNT;
        $PAYMENT_BATCH_MASTER['CREATED_BY'] = $_SESSION['PK_USER'];
        $PAYMENT_BATCH_MASTER['CREATED_ON'] = date("Y-m-d H:i");
        
        if($data['FROM_CRON'] == 1) {
            $PAYMENT_BATCH_MASTER['COMMENTS'] = 'Automated CyberSource Payment - ' .
                                               $res_stud_name->fields['FIRST_NAME'] . ' ' .
                                               $res_stud_name->fields['LAST_NAME'];
        } else {
            $PAYMENT_BATCH_MASTER['COMMENTS'] = 'CyberSource Payment - ' .
                                               $res_stud_name->fields['FIRST_NAME'] . ' ' .
                                               $res_stud_name->fields['LAST_NAME'];
        }
        
        db_perform('S_PAYMENT_BATCH_MASTER', $PAYMENT_BATCH_MASTER, 'insert');
        $PK_PAYMENT_BATCH_MASTER = $db->insert_ID();
        
        // Actualizar contador de batch
        $NEW_BATCH_NO = $res_acc->fields['PAYMENT_BATCH_NO'] + 1;
        $db->Execute("UPDATE Z_ACCOUNT SET PAYMENT_BATCH_NO = '$NEW_BATCH_NO' WHERE PK_ACCOUNT = '$PK_ACCOUNT'");
        
        // Procesar cada disbursement
        $ID_ARR = explode(",", $ID);
        foreach($ID_ARR as $PK_STUDENT_DISBURSEMENT) {
            
            $res_disb = $db->Execute("SELECT * FROM S_STUDENT_DISBURSEMENT 
                                     WHERE PK_STUDENT_DISBURSEMENT = '$PK_STUDENT_DISBURSEMENT' 
                                     AND PK_ACCOUNT = '$PK_ACCOUNT'");
            
            // Obtener número de recibo
            $res_bat = $db->Execute("SELECT RECEIPT_NO FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$PK_ACCOUNT'");
            $RECEIPT_NO = $res_bat->fields['RECEIPT_NO'];
            $RECEIPT_NO1 = $RECEIPT_NO + 1;
            $db->Execute("UPDATE Z_ACCOUNT SET RECEIPT_NO = '$RECEIPT_NO1' WHERE PK_ACCOUNT = '$PK_ACCOUNT'");
            
            // Obtener campus
            $PK_CAMPUS = '';
            $res_camp = $db->Execute("SELECT PK_CAMPUS FROM S_STUDENT_CAMPUS 
                                     WHERE PK_STUDENT_ENROLLMENT = '".$res_disb->fields['PK_STUDENT_ENROLLMENT']."' 
                                     AND PK_ACCOUNT = '$PK_ACCOUNT'");
            while (!$res_camp->EOF) {
                if($PK_CAMPUS != '') $PK_CAMPUS .= ',';
                $PK_CAMPUS .= $res_camp->fields['PK_CAMPUS'];
                $res_camp->MoveNext();
            }
            
            // Actualizar ledger codes en batch master
            $res_batch = $db->Execute("SELECT PK_AR_LEDGER_CODE, BATCH_PK_CAMPUS FROM S_PAYMENT_BATCH_MASTER 
                                      WHERE PK_ACCOUNT = '$PK_ACCOUNT' 
                                      AND PK_PAYMENT_BATCH_MASTER = '$PK_PAYMENT_BATCH_MASTER'");
            
            $PK_AR_LEDGER_CODE_2 = $res_batch->fields['PK_AR_LEDGER_CODE'];
            if($PK_AR_LEDGER_CODE_2 != '') $PK_AR_LEDGER_CODE_2 .= ',';
            $PK_AR_LEDGER_CODE_2 .= $res_disb->fields['PK_AR_LEDGER_CODE'];
            
            $PAYMENT_BATCH_MASTER1['PK_AR_LEDGER_CODE'] = $PK_AR_LEDGER_CODE_2;
            $PAYMENT_BATCH_MASTER1['BATCH_PK_CAMPUS'] = $PK_CAMPUS;
            db_perform('S_PAYMENT_BATCH_MASTER', $PAYMENT_BATCH_MASTER1, 'update',
                      "PK_PAYMENT_BATCH_MASTER = '$PK_PAYMENT_BATCH_MASTER' AND PK_ACCOUNT = '$PK_ACCOUNT'");
            
            // Crear PAYMENT_BATCH_DETAIL
            $PAYMENT_BATCH_DETAIL = array();
            $PAYMENT_BATCH_DETAIL['RECEIPT_NO'] = $RECEIPT_NO1;
            $PAYMENT_BATCH_DETAIL['PK_STUDENT_MASTER'] = $res_disb->fields['PK_STUDENT_MASTER'];
            $PAYMENT_BATCH_DETAIL['PK_STUDENT_ENROLLMENT'] = $res_disb->fields['PK_STUDENT_ENROLLMENT'];
            $PAYMENT_BATCH_DETAIL['PK_PAYMENT_BATCH_MASTER'] = $PK_PAYMENT_BATCH_MASTER;
            $PAYMENT_BATCH_DETAIL['PK_STUDENT_DISBURSEMENT'] = $PK_STUDENT_DISBURSEMENT;
            $PAYMENT_BATCH_DETAIL['PK_STUDENT_CREDIT_CARD_PAYMENT'] = $PK_STUDENT_CREDIT_CARD_PAYMENT;
            $PAYMENT_BATCH_DETAIL['DUE_AMOUNT'] = $res_disb->fields['DISBURSEMENT_AMOUNT'];
            $PAYMENT_BATCH_DETAIL['RECEIVED_AMOUNT'] = $res_disb->fields['DISBURSEMENT_AMOUNT'];
            $PAYMENT_BATCH_DETAIL['BATCH_TRANSACTION_DATE'] = date("Y-m-d");
            $PAYMENT_BATCH_DETAIL['PK_TERM_BLOCK'] = $res_disb->fields['PK_TERM_BLOCK'];
            $PAYMENT_BATCH_DETAIL['PK_BATCH_PAYMENT_STATUS'] = 3; // Paid
            $PAYMENT_BATCH_DETAIL['REFERENCE_NO'] = $TRANSACTION_ID;
            $PAYMENT_BATCH_DETAIL['PK_ACCOUNT'] = $PK_ACCOUNT;
            $PAYMENT_BATCH_DETAIL['CREATED_BY'] = $_SESSION['PK_USER'];
            $PAYMENT_BATCH_DETAIL['CREATED_ON'] = date("Y-m-d H:i");
            
            if($data['FROM_CRON'] == 1) {
                $PAYMENT_BATCH_DETAIL['BATCH_DETAIL_DESCRIPTION'] = 'Automated CyberSource Payment';
            } else {
                $PAYMENT_BATCH_DETAIL['BATCH_DETAIL_DESCRIPTION'] = 'CyberSource Payment';
            }
            
            db_perform('S_PAYMENT_BATCH_DETAIL', $PAYMENT_BATCH_DETAIL, 'insert');
            $PK_PAYMENT_BATCH_DETAIL = $db->insert_ID();
            
            // Obtener payment type para credit card
            $res_payment_type = $db->Execute("SELECT PK_AR_PAYMENT_TYPE FROM M_AR_PAYMENT_TYPE 
                                             WHERE PK_ACCOUNT = '$PK_ACCOUNT' 
                                             AND (TRIM(AR_PAYMENT_TYPE) = 'credit card' 
                                                  OR TRIM(AR_PAYMENT_TYPE) = 'card' 
                                                  OR TRIM(AR_PAYMENT_TYPE) = 'cc')");
            
            // Actualizar disbursement
            $STUDENT_DISBURSEMENT['PK_DETAIL_TYPE'] = 4;
            $STUDENT_DISBURSEMENT['DETAIL'] = $res_payment_type->fields['PK_AR_PAYMENT_TYPE'];
            $STUDENT_DISBURSEMENT['PK_PAYMENT_BATCH_DETAIL'] = $PK_PAYMENT_BATCH_DETAIL;
            $STUDENT_DISBURSEMENT['DEPOSITED_DATE'] = date("Y-m-d");
            $STUDENT_DISBURSEMENT['DISBURSEMENT_AMOUNT'] = $PAYMENT_BATCH_DETAIL['RECEIVED_AMOUNT'];
            $STUDENT_DISBURSEMENT['PK_DISBURSEMENT_STATUS'] = 1;
            $STUDENT_DISBURSEMENT['PK_STUDENT_CREDIT_CARD_PAYMENT'] = $PK_STUDENT_CREDIT_CARD_PAYMENT;
            
            db_perform('S_STUDENT_DISBURSEMENT', $STUDENT_DISBURSEMENT, 'update',
                      "PK_STUDENT_DISBURSEMENT = '$PK_STUDENT_DISBURSEMENT' AND PK_ACCOUNT = '$PK_ACCOUNT'");
            
            // Usar la función student_ledger() estándar
            $ledger_data['PK_PAYMENT_BATCH_DETAIL'] = $PK_PAYMENT_BATCH_DETAIL;
            $ledger_data['PK_STUDENT_DISBURSEMENT'] = $PK_STUDENT_DISBURSEMENT;
            $ledger_data['PK_ACCOUNT'] = $PK_ACCOUNT;
            $ledger_data['PK_AR_LEDGER_CODE'] = $res_disb->fields['PK_AR_LEDGER_CODE'];
            $ledger_data['AMOUNT'] = $PAYMENT_BATCH_DETAIL['RECEIVED_AMOUNT'];
            $ledger_data['DATE'] = $PAYMENT_BATCH_DETAIL['BATCH_TRANSACTION_DATE'];
            $ledger_data['PK_STUDENT_ENROLLMENT'] = $res_disb->fields['PK_STUDENT_ENROLLMENT'];
            $ledger_data['PK_STUDENT_MASTER'] = $res_disb->fields['PK_STUDENT_MASTER'];
            $ledger_data['PK_STUDENT_CREDIT_CARD_PAYMENT'] = $PK_STUDENT_CREDIT_CARD_PAYMENT;
            
            student_ledger($ledger_data);
        }
        
        // Actualizar total en batch master
        $res_tot_amt = $db->Execute("SELECT SUM(DUE_AMOUNT) as DUE_AMOUNT FROM S_PAYMENT_BATCH_DETAIL 
                                    WHERE PK_ACCOUNT = '$PK_ACCOUNT' 
                                    AND PK_PAYMENT_BATCH_MASTER = '$PK_PAYMENT_BATCH_MASTER'");
        
        $PAYMENT_BATCH_MASTER2['AMOUNT'] = $res_tot_amt->fields['DUE_AMOUNT'];
        db_perform('S_PAYMENT_BATCH_MASTER', $PAYMENT_BATCH_MASTER2, 'update',
                  "PK_PAYMENT_BATCH_MASTER = '$PK_PAYMENT_BATCH_MASTER' AND PK_ACCOUNT = '$PK_ACCOUNT'");
        
        $PAY_RES['PK_PAYMENT_BATCH_MASTER'] = $PK_PAYMENT_BATCH_MASTER;
        $PAY_RES['STATUS'] = 1;
        
        return $PAY_RES;
    }
}

// Obtener tarjetas guardadas
$res_saved_cards = $db->Execute("SELECT * FROM S_STUDENT_CREDIT_CARD_CYBERSOURCE 
                                WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' 
                                AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' 
                                AND ACTIVE = 1
                                AND CUSTOMER_ID IS NOT NULL
                                AND PAYMENT_INSTRUMENT_ID IS NOT NULL
                                ORDER BY IS_PRIMARY DESC, PK_STUDENT_CREDIT_CARD_CYBERSOURCE DESC");

// Procesar pago cuando se envía el formulario
if(!empty($_POST) && isset($_POST['process_payment'])){
    
    // Validación del disbursement
    if($_GET['type'] == "disp") {
        $res = $db->Execute("SELECT PK_DISBURSEMENT_STATUS FROM S_STUDENT_DISBURSEMENT 
                            WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' 
                            AND PK_STUDENT_DISBURSEMENT = '$_GET[id]'");
        if($res->fields['PK_DISBURSEMENT_STATUS'] != 2){
            header("location:student?t=".$_GET['t']."&eid=".$_GET['eid']."&id=".$_GET['sid']."&tab=disbursementTab");
            exit;
        }
    }
    
    // Calcular monto total
    $AMOUNT = 0;
    if($_GET['type'] == 'disp'){
        $PK_STUDENT_DISBURSEMENT_ARR = explode(",",$_GET['id']);
        foreach($PK_STUDENT_DISBURSEMENT_ARR as $PK_STUDENT_DISBURSEMENT) {
            $res_amt = $db->Execute("SELECT DISBURSEMENT_AMOUNT FROM S_STUDENT_DISBURSEMENT 
                                    WHERE PK_STUDENT_DISBURSEMENT = '$PK_STUDENT_DISBURSEMENT'");
            $AMOUNT += $res_amt->fields['DISBURSEMENT_AMOUNT'];
        }
    }
    
    try {
        if($_POST['PK_STUDENT_CREDIT_CARD_CYBERSOURCE'] == '') {
            throw new Exception("Por favor seleccione una tarjeta");
        }
        
        // Obtener la tarjeta seleccionada
        $selectedCardId = $_POST['PK_STUDENT_CREDIT_CARD_CYBERSOURCE'];
        
        $res_selected_card = $db->Execute("SELECT * FROM S_STUDENT_CREDIT_CARD_CYBERSOURCE 
                                          WHERE PK_STUDENT_CREDIT_CARD_CYBERSOURCE = '$selectedCardId'
                                          AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
        
        if($res_selected_card->RecordCount() == 0) {
            throw new Exception("Tarjeta no encontrada");
        }
        
        $selectedCard = $res_selected_card->fields;
        
        // Verificar tokens
        if(empty($selectedCard['CUSTOMER_ID']) || empty($selectedCard['PAYMENT_INSTRUMENT_ID'])) {
            throw new Exception("Esta tarjeta no está tokenizada correctamente. Por favor, elimínela y agréguela nuevamente.");
        }
        
        // Procesar el pago con CyberSource
        $result = processCyberSourcePaymentWithToken(
            $CYBERSOURCE_CONFIG,
            $selectedCard['CUSTOMER_ID'],
            $selectedCard['PAYMENT_INSTRUMENT_ID'],
            $AMOUNT,
            'Disbursement Payment'
        );
        
        if ($result['success']) {
            // Primero, registrar el pago en S_STUDENT_CREDIT_CARD_PAYMENT
            $PAYMENT_DATA = [
                'PK_STUDENT_MASTER' => $PK_STUDENT_MASTER,
                'PK_STUDENT_ENROLLMENT' => $PK_STUDENT_ENROLLMENT,
                'PK_ACCOUNT' => $_SESSION['PK_ACCOUNT'],
                'PAYMENT_FOR' => 'DISBURSEMENT',
                'PK_STUDENT_CREDIT_CARD' => $selectedCardId,
                'ID' => $result['transactionId'],
                'ORDER_ID' => 'DIAM_' . time(),
                'AMOUNT_CHARGED' => $AMOUNT,
                'CONV_FEE_AMOUNT' => 0,
                'TOTAL_CHARGE' => $AMOUNT,
                'PAID_ON' => date('Y-m-d'),
                'CARD_NO' => '****' . $selectedCard['CARD_LAST_FOUR'],
                'CARD_NAME' => $selectedCard['NAME_ON_CARD'],
                'CARD_TYPE' => $selectedCard['CARD_BRAND'],
                'FAILED' => 0,
                'ACTIVE' => 1,
                'CREATED_BY' => $_SESSION['PK_USER'],
                'CREATED_ON' => date('Y-m-d H:i:s')
            ];
            
            db_perform('S_STUDENT_CREDIT_CARD_PAYMENT', $PAYMENT_DATA, 'insert');
            $PK_STUDENT_CREDIT_CARD_PAYMENT = $db->insert_ID();
            
            // CAMBIO PRINCIPAL: Crear batch usando la función
            $batch_data = [
                'PK_ACCOUNT' => $_SESSION['PK_ACCOUNT'],
                'PK_STUDENT_MASTER' => $PK_STUDENT_MASTER,
                'PK_STUDENT_ENROLLMENT' => $PK_STUDENT_ENROLLMENT,
                'TYPE' => $_GET['type'],
                'ID' => $_GET['id'],
                'PK_STUDENT_CREDIT_CARD_PAYMENT' => $PK_STUDENT_CREDIT_CARD_PAYMENT,
                'FROM_CRON' => 0
            ];
            
            $batch_result = make_payment_cybersource_batch($batch_data, $result);
            
            if($batch_result['STATUS'] == 1) {
                // Redirigir al batch creado
                header("location:batch_payment?id=".$batch_result['PK_PAYMENT_BATCH_MASTER']);
                exit;
            } else {
                $msg = "Error al crear el batch de pago";
            }
            
        } else {
            $msg = "Error: " . ($result['error'] ?? 'Error desconocido');
            if(isset($result['response']['message'])) {
                $msg .= " - " . $result['response']['message'];
            }
        }
        
    } catch (Exception $e) {
        $msg = $e->getMessage();
    }
}

// Obtener información del estudiante
$res_student = $db->Execute("SELECT CONCAT(FIRST_NAME, ' ', LAST_NAME) as NAME FROM S_STUDENT_MASTER 
                            WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER'");
$STUDENT_NAME = $res_student->fields['NAME'];

$res_academics = $db->Execute("SELECT STUDENT_ID FROM S_STUDENT_ACADEMICS 
                              WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER'");
$STUDENT_ID = $res_academics->fields['STUDENT_ID'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Procesar Pago - CyberSource</title>
    <? require_once("css.php"); ?>
</head>
<body class="horizontal-nav boxed skin-megna fixed-layout">
    <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
        <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor">Procesar Pago</h4>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <? if($msg != ''){ ?>
                                <div class="alert alert-danger">
                                    <?=$msg?>
                                </div>
                                <? } ?>
                                
                                <form method="post" name="paymentForm" id="paymentForm">
                                    <input type="hidden" name="process_payment" value="1">
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <!-- Información del estudiante y disbursement -->
                                            <h5>Información del Pago</h5>
                                            <table class="table">
                                                <tr>
                                                    <td><strong>Estudiante:</strong></td>
                                                    <td><?=$STUDENT_NAME?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>ID Estudiante:</strong></td>
                                                    <td><?=$STUDENT_ID?></td>
                                                </tr>
                                                <? if($_GET['type'] == 'disp'){
                                                    $AMOUNT = 0;
                                                    $PK_STUDENT_DISBURSEMENT_ARR = explode(",",$_GET['id']);
                                                    foreach($PK_STUDENT_DISBURSEMENT_ARR as $PK_STUDENT_DISBURSEMENT) {
                                                        $res_disb = $db->Execute("SELECT * FROM S_STUDENT_DISBURSEMENT 
                                                                                WHERE PK_STUDENT_DISBURSEMENT = '$PK_STUDENT_DISBURSEMENT'");
                                                        $AMOUNT += $res_disb->fields['DISBURSEMENT_AMOUNT'];
                                                    }
                                                ?>
                                                <tr>
                                                    <td><strong>Monto Total:</strong></td>
                                                    <td style="font-size: 1.2em; color: #0d6efd;">
                                                        <strong>$<?=number_format($AMOUNT, 2)?></strong>
                                                    </td>
                                                </tr>
                                                <? } ?>
                                            </table>
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <h5>Seleccionar Tarjeta</h5>
                                            
                                            <? if($res_saved_cards->RecordCount() > 0) { ?>
                                            <div class="form-group">
                                                <select name="PK_STUDENT_CREDIT_CARD_CYBERSOURCE"
                                                        id="PK_STUDENT_CREDIT_CARD_CYBERSOURCE"
                                                        class="form-control" required>
                                                    <option value="">Seleccione una tarjeta...</option>
                                                    <? while(!$res_saved_cards->EOF) { ?>
                                                    <option value="<?=$res_saved_cards->fields['PK_STUDENT_CREDIT_CARD_CYBERSOURCE']?>">
                                                        •••• <?=$res_saved_cards->fields['CARD_LAST_FOUR']?> -
                                                        <?=$res_saved_cards->fields['NAME_ON_CARD']?>
                                                        (<?=$res_saved_cards->fields['CARD_BRAND']?>)
                                                        <?=$res_saved_cards->fields['IS_PRIMARY'] ? ' - PRIMARIA' : ''?>
                                                    </option>
                                                    <? $res_saved_cards->MoveNext();
                                                    } ?>
                                                </select>
                                            </div>
                                            
                                            <div class="form-group">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fa fa-credit-card"></i> Procesar Pago
                                                </button>
                                                <a href="card_info_cybersource.php?s_id=<?=$PK_STUDENT_MASTER?>&eid=<?=$PK_STUDENT_ENROLLMENT?>&t=<?=$_GET['t']?>"
                                                   class="btn btn-info">
                                                    <i class="fa fa-plus"></i> Agregar Tarjeta
                                                </a>
                                                <button type="button"
                                                        onclick="window.location.href='student?t=<?=$_GET['t']?>&eid=<?=$PK_STUDENT_ENROLLMENT?>&id=<?=$PK_STUDENT_MASTER?>&tab=disbursementTab'"
                                                        class="btn btn-secondary">
                                                    Cancelar
                                                </button>
                                            </div>
                                            <? } else { ?>
                                            <div class="alert alert-warning">
                                                <i class="fa fa-exclamation-triangle"></i>
                                                No hay tarjetas guardadas para este estudiante.
                                                <br><br>
                                                <a href="card_info_cybersource.php?s_id=<?=$PK_STUDENT_MASTER?>&eid=<?=$PK_STUDENT_ENROLLMENT?>&t=<?=$_GET['t']?>"
                                                   class="btn btn-primary">
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
        
        if (!confirm('¿Está seguro de procesar el pago por $<?=number_format($AMOUNT, 2)?>?')) {
            e.preventDefault();
            return false;
        }
    });
    </script>
</body>
</html>
