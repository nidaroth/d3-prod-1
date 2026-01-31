<?php
// /D3/school/ajax_delete_card.php
require_once("../global/config.php");

$cardId = $_POST['card_id'] ?? 0;

if ($cardId && $_SESSION['PK_USER']) {
    $db->Execute("UPDATE S_STUDENT_CREDIT_CARD_CYBERSOURCE 
                 SET ACTIVE = 0, 
                     EDITED_BY = '$_SESSION[PK_USER]',
                     EDITED_ON = NOW()
                 WHERE PK_STUDENT_CREDIT_CARD_CYBERSOURCE = '$cardId' 
                 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
    
    // Log de eliminación
    $db->Execute("INSERT INTO S_PAYMENT_CYBERSOURCE_LOG 
                 (PK_ACCOUNT, PK_STUDENT_CREDIT_CARD_CYBERSOURCE, STATUS, CREATED_ON, CREATED_BY)
                 VALUES ('$_SESSION[PK_ACCOUNT]', '$cardId', 'CARD_DELETED', NOW(), '$_SESSION[PK_USER]')");
    
    echo "success";
} else {
    echo "error";
}
?>

