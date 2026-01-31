<?php
// /D3/school/ajax_set_primary_card.php
require_once("../global/config.php");

$cardId = $_POST['card_id'] ?? 0;
$studentId = $_POST['student_id'] ?? 0;

if ($cardId && $studentId && $_SESSION['PK_USER']) {
    $db->StartTrans();
    
    // Desmarcar todas
    $db->Execute("UPDATE S_STUDENT_CREDIT_CARD_CYBERSOURCE 
                 SET IS_PRIMARY = 0,
                     EDITED_BY = '$_SESSION[PK_USER]',
                     EDITED_ON = NOW()
                 WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' 
                 AND PK_STUDENT_MASTER = '$studentId'");
    
    // Marcar la seleccionada
    $db->Execute("UPDATE S_STUDENT_CREDIT_CARD_CYBERSOURCE 
                 SET IS_PRIMARY = 1,
                     EDITED_BY = '$_SESSION[PK_USER]',
                     EDITED_ON = NOW()
                 WHERE PK_STUDENT_CREDIT_CARD_CYBERSOURCE = '$cardId' 
                 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
    
    $db->CompleteTrans();
    
    echo "success";
} else {
    echo "error";
}
?>

