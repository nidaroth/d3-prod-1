<?php
require_once("../global/config.php");


try {
    //code...


    $data['showpopup'] = false;
    
    $check_if_status_is_enrolled = $db->Execute("SELECT * FROM M_STUDENT_STATUS WHERE PK_STUDENT_STATUS = $_POST[PK_STUDENT_STATUS] AND PK_STUDENT_STATUS_MASTER = 5 AND PK_ACCOUNT = $_SESSION[PK_ACCOUNT]");


    if ($check_if_status_is_enrolled->RecordCount() > 0 || $_POST['IS_NEW_ENROLLMENT'] == 'yes') {
       
        // $res_address = $db->Execute("SELECT ADDRESS,ADDRESS_1, CITY, STATE_CODE, ZIP, Z_COUNTRY.NAME as COUNTRY, HOME_PHONE, WORK_PHONE, CELL_PHONE, OTHER_PHONE, EMAIL  FROM S_STUDENT_CONTACT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_STUDENT_CONTACT.PK_STATES LEFT JOIN Z_COUNTRY  ON Z_COUNTRY.PK_COUNTRY = S_STUDENT_CONTACT.PK_COUNTRY WHERE PK_STUDENT_MASTER = $_POST[PK_STUDENT_MASTER] AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' "); 

        $check_state_autherization_sql =  "SELECT S_STUDENT_CONTACT.PK_STATES FROM S_STUDENT_CONTACT INNER JOIN NC_SARA_STATE_AUTHORIZATION ON NC_SARA_STATE_AUTHORIZATION.PK_STATES = S_STUDENT_CONTACT.PK_STATES AND NC_SARA_STATE_AUTHORIZATION.PK_ACCOUNT = $_SESSION[PK_ACCOUNT] WHERE S_STUDENT_CONTACT.PK_ACCOUNT = $_SESSION[PK_ACCOUNT] AND PK_STUDENT_MASTER = $_POST[PK_STUDENT_MASTER] AND  NC_SARA_STATE_AUTHORIZATION.STATUS = 6 AND  S_STUDENT_CONTACT.PK_STUDENT_CONTACT_TYPE_MASTER = 1 AND S_STUDENT_CONTACT.ACTIVE = 1 AND S_STUDENT_CONTACT.ADDRESS_INVALID = 0 AND NC_SARA_STATE_AUTHORIZATION.PK_ACCOUNT = $_SESSION[PK_ACCOUNT]"; 
        
        $check_state_autherization = $db->Execute($check_state_autherization_sql); 
        if ($check_state_autherization->recordCount() > 0) {
            $data['showpopup'] = true;
        }
    } 
   
} catch (\Throwable $th) {
    // throw $th;
}
header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;