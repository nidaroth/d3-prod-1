<?php

function make_payment_cybersource($data){
    global $db;
    
    $PK_ACCOUNT = $data['PK_ACCOUNT'];
    $PK_STUDENT_MASTER = $data['PK_STUDENT_MASTER'];
    $PK_STUDENT_ENROLLMENT1 = $data['PK_STUDENT_ENROLLMENT'];
    
    $TYPE = $data['TYPE'];
    $ID = $data['ID'];
    $AMOUNT = $data['AMOUNT'];
    $TRANSACTION_ID = $data['TRANSACTION_ID']; // ID de CyberSource
    $PK_STUDENT_CREDIT_CARD_PAYMENT = $data['PK_STUDENT_CREDIT_CARD_PAYMENT'];
    
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
        
        // Actualizar contador
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
            $PAYMENT_BATCH_DETAIL['REFERENCE_NO'] = $TRANSACTION_ID; // CyberSource transaction ID
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
            
            // Actualizar disbursement
            $STUDENT_DISBURSEMENT['PK_PAYMENT_BATCH_DETAIL'] = $PK_PAYMENT_BATCH_DETAIL;
            $STUDENT_DISBURSEMENT['DEPOSITED_DATE'] = date("Y-m-d");
            $STUDENT_DISBURSEMENT['PK_DISBURSEMENT_STATUS'] = 1; // Paid
            $STUDENT_DISBURSEMENT['PK_STUDENT_CREDIT_CARD_PAYMENT'] = $PK_STUDENT_CREDIT_CARD_PAYMENT;
            db_perform('S_STUDENT_DISBURSEMENT', $STUDENT_DISBURSEMENT, 'update',
                      "PK_STUDENT_DISBURSEMENT = '$PK_STUDENT_DISBURSEMENT' AND PK_ACCOUNT = '$PK_ACCOUNT'");
            
            // Usar la función student_ledger() estándar
            $ledger_data['PK_PAYMENT_BATCH_DETAIL'] = $PK_PAYMENT_BATCH_DETAIL;
            $ledger_data['PK_STUDENT_DISBURSEMENT'] = $PK_STUDENT_DISBURSEMENT;
            $ledger_data['PK_STUDENT_CREDIT_CARD_PAYMENT'] = $PK_STUDENT_CREDIT_CARD_PAYMENT;
            $ledger_data['PK_ACCOUNT'] = $PK_ACCOUNT;
            $ledger_data['PK_AR_LEDGER_CODE'] = $res_disb->fields['PK_AR_LEDGER_CODE'];
            $ledger_data['AMOUNT'] = $PAYMENT_BATCH_DETAIL['RECEIVED_AMOUNT'];
            $ledger_data['DATE'] = $PAYMENT_BATCH_DETAIL['BATCH_TRANSACTION_DATE'];
            $ledger_data['PK_STUDENT_ENROLLMENT'] = $res_disb->fields['PK_STUDENT_ENROLLMENT'];
            $ledger_data['PK_STUDENT_MASTER'] = $res_disb->fields['PK_STUDENT_MASTER'];
            
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
    }
    
    // Aquí agregarías lógica para TYPE == 'misc' si es necesario
    
    $PAY_RES['STATUS'] = 1;
    return $PAY_RES;
}
?>

