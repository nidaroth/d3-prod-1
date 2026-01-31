<?php
// /D3/school/quick_payment_cybersource.php
require_once("../global/config.php");
require_once("../language/common.php");
require_once("function_student_ledger.php");

$msg = "";

// Verificar acceso
$res_pay = $db->Execute("SELECT ENABLE_DIAMOND_PAY FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
if($res_pay->fields['ENABLE_DIAMOND_PAY'] != 3) {
    header("location:../index");
    exit;
}

$PK_STUDENT_MASTER = $_GET['id'] ?? 0;
$PK_STUDENT_ENROLLMENT = $_GET['eid'] ?? 0;

// Obtener configuración de CyberSource
$res_cs = $db->Execute("SELECT * FROM S_CYBERSOURCE_SETTINGS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
if(!$res_cs || $res_cs->RecordCount() == 0) {
    die("Configuración de CyberSource no encontrada");
}

$CYBERSOURCE_CONFIG = [
    'MERCHANT_ID' => $res_cs->fields['MERCHANT_ID'],
    'KEY_ID' => $res_cs->fields['KEY_ID'],
    'SECRET_KEY' => $res_cs->fields['SECRET_KEY'],
    'ENVIRONMENT' => $res_cs->fields['ENVIRONMENT'] ?: 'apitest.cybersource.com'
];

// Función para procesar pago con token
function processQuickPayment($config, $customerId, $paymentInstrumentId, $amount, $description = '') {
    try {
        $host = $config['ENVIRONMENT'];
        $merchantId = $config['MERCHANT_ID'];
        $keyId = $config['KEY_ID'];
        $secretKey = $config['SECRET_KEY'];
        
        $requestBody = [
            'clientReferenceInformation' => [
                'code' => 'QUICK_' . time()
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

// Procesar pago si se envió el formulario
if(!empty($_POST) && isset($_POST['process_payment'])) {
    // Calcular monto total
    $AMOUNT = 0;
    foreach($_POST['BATCH_CREDIT'] as $credit) {
        $AMOUNT += floatval($credit);
    }
    
    if($_POST['PK_STUDENT_CREDIT_CARD_CYBERSOURCE'] && $AMOUNT > 0) {
        // Obtener tarjeta seleccionada
        $res_card = $db->Execute("SELECT * FROM S_STUDENT_CREDIT_CARD_CYBERSOURCE 
                                 WHERE PK_STUDENT_CREDIT_CARD_CYBERSOURCE = '".$_POST['PK_STUDENT_CREDIT_CARD_CYBERSOURCE']."'
                                 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
        
        if($res_card->RecordCount() > 0) {
            $card = $res_card->fields;
            
            if($card['CUSTOMER_ID'] && $card['PAYMENT_INSTRUMENT_ID']) {
                // Procesar pago
                $result = processQuickPayment(
                    $CYBERSOURCE_CONFIG,
                    $card['CUSTOMER_ID'],
                    $card['PAYMENT_INSTRUMENT_ID'],
                    $AMOUNT,
                    $_POST['BATCH_DETAIL_DESCRIPTION'] ?? 'Quick Payment'
                );
                
                if($result['success']) {
                    // Registrar pago exitoso
                    $PAYMENT_DATA = [
                        'PK_STUDENT_MASTER' => $PK_STUDENT_MASTER,
                        'PK_STUDENT_ENROLLMENT' => $PK_STUDENT_ENROLLMENT,
                        'PK_ACCOUNT' => $_SESSION['PK_ACCOUNT'],
                        'PAYMENT_FOR' => 'MISC',
                        'PK_STUDENT_CREDIT_CARD' => $_POST['PK_STUDENT_CREDIT_CARD_CYBERSOURCE'],
                        'ID' => $result['transactionId'],
                        'ORDER_ID' => 'QUICK_' . time(),
                        'AMOUNT_CHARGED' => $AMOUNT,
                        'TOTAL_CHARGE' => $AMOUNT,
                        'PAID_ON' => date('Y-m-d'),
                        'CARD_NO' => '****' . $card['CARD_LAST_FOUR'],
                        'CARD_NAME' => $card['NAME_ON_CARD'],
                        'CARD_TYPE' => $card['CARD_BRAND'],
                        'FAILED' => 0,
                        'ACTIVE' => 1,
                        'CREATED_BY' => $_SESSION['PK_USER'],
                        'CREATED_ON' => date('Y-m-d H:i:s')
                    ];
                    
                    db_perform('S_STUDENT_CREDIT_CARD_PAYMENT', $PAYMENT_DATA, 'insert');
                    $PK_PAYMENT = $db->insert_ID();
                    
                    // Crear entradas en el ledger
                    for($i = 0; $i < count($_POST['BATCH_PK_AR_LEDGER_CODE']); $i++) {
                        if($_POST['BATCH_CREDIT'][$i] > 0) {
                            $LEDGER_DATA = [
                                'PK_ACCOUNT' => $_SESSION['PK_ACCOUNT'],
                                'PK_STUDENT_MASTER' => $PK_STUDENT_MASTER,
                                'PK_STUDENT_ENROLLMENT' => $PK_STUDENT_ENROLLMENT,
                                'PK_STUDENT_CREDIT_CARD_PAYMENT' => $PK_PAYMENT,
                                'TRANSACTION_DATE' => $_POST['TRANS_DATE'] ?? date('Y-m-d'),
                                'PK_AR_LEDGER_CODE' => $_POST['BATCH_PK_AR_LEDGER_CODE'][$i],
                                'CREDIT' => $_POST['BATCH_CREDIT'][$i],
                                'DEBIT' => $_POST['BATCH_DEBIT'][$i] ?? 0,
                                'ACTIVE' => 1,
                                'CREATED_ON' => date('Y-m-d H:i:s'),
                                'CREATED_BY' => $_SESSION['PK_USER']
                            ];
                            
                            db_perform('S_STUDENT_LEDGER', $LEDGER_DATA, 'insert');
                        }
                    }
                    
                    echo "<script>
                            alert('Pago procesado exitosamente!');
                            window.opener.go_to_student_page(this);
                            window.close();
                          </script>";
                    exit;
                } else {
                    $msg = "Error procesando pago: " . ($result['error'] ?? 'Error desconocido');
                }
            } else {
                $msg = "La tarjeta seleccionada no está tokenizada correctamente";
            }
        } else {
            $msg = "Tarjeta no encontrada";
        }
    } else {
        $msg = "Seleccione una tarjeta y verifique el monto";
    }
}

// Obtener información del estudiante
$res_student = $db->Execute("SELECT * FROM S_STUDENT_MASTER WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER'");
$STUDENT_NAME = $res_student->fields['FIRST_NAME'] . ' ' . $res_student->fields['LAST_NAME'];

$res_academics = $db->Execute("SELECT STUDENT_ID FROM S_STUDENT_ACADEMICS WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER'");
$STUDENT_ID = $res_academics->fields['STUDENT_ID'];

// Obtener balance actual
$current_balance = $_SESSION['student_ledger_balance'] ?? 0;

// Obtener tarjetas del estudiante
$res_cards = $db->Execute("SELECT * FROM S_STUDENT_CREDIT_CARD_CYBERSOURCE 
                          WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' 
                          AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER'
                          AND ACTIVE = 1
                          AND CUSTOMER_ID IS NOT NULL
                          AND PAYMENT_INSTRUMENT_ID IS NOT NULL
                          ORDER BY IS_PRIMARY DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Quick Payment - CyberSource</title>
    <? require_once("css.php"); ?>
    <style>
        .no-records-found{display:none;}
        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
    </style>
</head>
<body class="horizontal-nav boxed skin-megna fixed-layout">
    <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
        <div class="page-wrapper" style="padding-top: 0;">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form method="post" name="form1" id="form1">
                                    <input type="hidden" name="process_payment" value="1">
                                    
                                    <div class="row">
                                        <div class="col-md-12" style="background-color:#022561;color:#FFFFFF;padding:10px;text-align:center;">
                                            <h4><b>Quick Batch - CyberSource</b></h4>
                                        </div>
                                    </div>
                                    
                                    <? if($msg != ''){ ?>
                                    <div class="alert alert-danger mt-3">
                                        <?=$msg?>
                                    </div>
                                    <? } ?>
                                    
                                    <!-- Student Info -->
                                    <div class="row mt-3">
                                        <div class="col-md-8">
                                            <table class="table">
                                                <tr>
                                                    <td><strong>Student:</strong></td>
                                                    <td><?=$STUDENT_NAME?></td>
                                                    <td><strong>Student ID:</strong></td>
                                                    <td><?=$STUDENT_ID?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Current Balance:</strong></td>
                                                    <td colspan="3" style="<?=$current_balance < 0 ? 'color:red;' : ''?>">
                                                        $<?=number_format($current_balance, 2)?>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-4 text-right">
                                            <a href="javascript:void(0)" onclick="add_ledger()" class="btn btn-primary">
                                                <i class="fa fa-plus"></i> Add Ledger
                                            </a>
                                        </div>
                                    </div>
                                    
                                    <!-- Ledger Table -->
                                    <div class="table-responsive">
                                        <table class="table table-striped" id="student_table">
                                            <thead>
                                                <tr>
                                                    <th>Ledger Code</th>
                                                    <th>Trans Date</th>
                                                    <th>Debit</th>
                                                    <th>Credit</th>
                                                    <th>Description</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody></tbody>
                                            <tfoot>
                                                <tr>
                                                    <th colspan="2">Total</th>
                                                    <th><div id="debit_total_div">$0.00</div></th>
                                                    <th><div id="credit_total_div">$0.00</div></th>
                                                    <th colspan="2"></th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                    
                                    <!-- Payment Method -->
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Comments</label>
                                                <textarea class="form-control" name="COMMENTS" rows="2"></textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Select Card *</label>
                                                <select name="PK_STUDENT_CREDIT_CARD_CYBERSOURCE" class="form-control" required>
                                                    <option value="">Select a card...</option>
                                                    <? while(!$res_cards->EOF) { ?>
                                                    <option value="<?=$res_cards->fields['PK_STUDENT_CREDIT_CARD_CYBERSOURCE']?>">
                                                        •••• <?=$res_cards->fields['CARD_LAST_FOUR']?> -
                                                        <?=$res_cards->fields['NAME_ON_CARD']?>
                                                        (<?=$res_cards->fields['CARD_BRAND']?>)
                                                        <?=$res_cards->fields['IS_PRIMARY'] ? ' - PRIMARY' : ''?>
                                                    </option>
                                                    <? $res_cards->MoveNext();
                                                    } ?>
                                                </select>
                                                <a href="card_info_cybersource.php?s_id=<?=$PK_STUDENT_MASTER?>&eid=<?=$PK_STUDENT_ENROLLMENT?>&t=<?=$_GET['t']?>"
                                                   target="_blank" class="btn btn-sm btn-info mt-2">
                                                    <i class="fa fa-plus"></i> Add New Card
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Buttons -->
                                    <div class="row">
                                        <div class="col-md-12 text-right">
                                            <button type="button" onclick="validate_payment_form()" class="btn btn-primary">
                                                Process Payment and Post to Ledger
                                            </button>
                                            <button type="button" onclick="window.close()" class="btn btn-secondary">
                                                Cancel
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <input type="hidden" name="TRANS_DATE" value="<?=date('Y-m-d')?>">
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <? require_once("js.php"); ?>
    
    <script>
    var student_count = 0;
    
    function add_ledger() {
        student_count++;
        var html = '<tr id="row_' + student_count + '">';
        html += '<td><select name="BATCH_PK_AR_LEDGER_CODE[]" class="form-control" required>';
        html += '<option value="">Select...</option>';
        <?php
        $res_ledger = $db->Execute("SELECT PK_AR_LEDGER_CODE, CODE, DESCRIPTION FROM M_AR_LEDGER_CODE 
                                   WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1");
        while(!$res_ledger->EOF) {
            echo "html += '<option value=\"".$res_ledger->fields['PK_AR_LEDGER_CODE']."\">".$res_ledger->fields['CODE']." - ".$res_ledger->fields['DESCRIPTION']."</option>';";
            $res_ledger->MoveNext();
        }
        ?>
        html += '</select></td>';
        html += '<td><input type="date" name="BATCH_TRANS_DATE[]" class="form-control" value="<?=date('Y-m-d')?>"></td>';
        html += '<td><input type="number" name="BATCH_DEBIT[]" class="form-control" step="0.01" value="0" onchange="calc_total()"></td>';
        html += '<td><input type="number" name="BATCH_CREDIT[]" class="form-control" step="0.01" value="0" onchange="calc_total()"></td>';
        html += '<td><input type="text" name="BATCH_DETAIL_DESCRIPTION[]" class="form-control"></td>';
        html += '<td><button type="button" onclick="delete_row(' + student_count + ')" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button></td>';
        html += '</tr>';
        
        document.getElementById('student_table').getElementsByTagName('tbody')[0].innerHTML += html;
        calc_total();
    }
    
    function delete_row(id) {
        document.getElementById('row_' + id).remove();
        calc_total();
    }
    
    function calc_total() {
        var debits = document.getElementsByName('BATCH_DEBIT[]');
        var credits = document.getElementsByName('BATCH_CREDIT[]');
        
        var debit_total = 0;
        var credit_total = 0;
        
        for(var i = 0; i < debits.length; i++) {
            debit_total += parseFloat(debits[i].value) || 0;
        }
        
        for(var i = 0; i < credits.length; i++) {
            credit_total += parseFloat(credits[i].value) || 0;
        }
        
        document.getElementById('debit_total_div').innerHTML = '$' + debit_total.toFixed(2);
        document.getElementById('credit_total_div').innerHTML = '$' + credit_total.toFixed(2);
    }
    
    function validate_payment_form() {
        var credits = document.getElementsByName('BATCH_CREDIT[]');
        var total = 0;
        
        for(var i = 0; i < credits.length; i++) {
            total += parseFloat(credits[i].value) || 0;
        }
        
        if(total <= 0) {
            alert('Please enter a valid amount');
            return false;
        }
        
        if(!document.getElementsByName('PK_STUDENT_CREDIT_CARD_CYBERSOURCE')[0].value) {
            alert('Please select a card');
            return false;
        }
        
        if(confirm('Process payment for $' + total.toFixed(2) + '?')) {
            document.form1.submit();
        }
    }
    
    // Add first row automatically
    window.onload = function() {
        add_ledger();
    }
    </script>
</body>
</html>

