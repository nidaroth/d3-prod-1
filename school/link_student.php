<?php
require_once("../global/config.php");
require_once("../language/common.php");
require_once("check_access.php");

// Configurar headers para JSON
header('Content-Type: application/json');

if(check_access('MANAGEMENT_FINANCE') == 0 ){
    echo json_encode(array('success' => false, 'message' => 'Access denied'));
    exit;
}

// VALIDACIÓN ESPECÍFICA: Solo PK_ACCOUNT = 72
if($_SESSION['PK_ACCOUNT'] != 72) {
    echo json_encode(array('success' => false, 'message' => 'Account not authorized'));
    exit;
}

$pk_isir_student_master = isset($_POST['pk_isir_student_master']) ? intval($_POST['pk_isir_student_master']) : 0;
$pk_student_master = isset($_POST['pk_student_master']) ? intval($_POST['pk_student_master']) : 0;

if($pk_isir_student_master == 0 || $pk_student_master == 0) {
    echo json_encode(array('success' => false, 'message' => 'Invalid parameters'));
    exit;
}

// Verificar que el ISIR student existe, tiene PK_STUDENT_MASTER = 0 Y PK_ACCOUNT = 72
$check_isir = $db->Execute("SELECT PK_ISIR_STUDENT_MASTER FROM S_ISIR_STUDENT_MASTER 
                            WHERE PK_ISIR_STUDENT_MASTER = '$pk_isir_student_master' 
                            AND PK_ACCOUNT = 72 
                            AND PK_STUDENT_MASTER = 0 
                            AND ACTIVE = 1");

if($check_isir->RecordCount() == 0) {
    echo json_encode(array('success' => false, 'message' => 'ISIR student not found, already linked, or account not authorized'));
    exit;
}

// Verificar que el student master existe Y es de PK_ACCOUNT = 72
$check_student = $db->Execute("SELECT PK_STUDENT_MASTER FROM S_STUDENT_MASTER 
                               WHERE PK_STUDENT_MASTER = '$pk_student_master' 
                               AND PK_ACCOUNT = 72 
                               AND ACTIVE = 1 
                               AND ARCHIVED = 0");

if($check_student->RecordCount() == 0) {
    echo json_encode(array('success' => false, 'message' => 'Student not found or account not authorized'));
    exit;
}

// Realizar el UPDATE - Solo actualizar, no crear registros nuevos
$update_result = $db->Execute("UPDATE S_ISIR_STUDENT_MASTER 
                               SET PK_STUDENT_MASTER = '$pk_student_master',
                                   EDITED_BY = '".$_SESSION['PK_EMPLOYEE_MASTER']."',
                                   EDITED_ON = NOW()
                               WHERE PK_ISIR_STUDENT_MASTER = '$pk_isir_student_master' 
                               AND PK_ACCOUNT = 72");

if($update_result) {
    echo json_encode(array('success' => true, 'message' => 'Student linked successfully'));
} else {
    echo json_encode(array('success' => false, 'message' => 'Database error occurred'));
}
?>

