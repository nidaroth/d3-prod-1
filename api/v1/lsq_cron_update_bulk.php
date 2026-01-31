<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
//  error_reporting(E_ALL); 

$error_collector = [];
$success_collector = [];
#require_once("../../vendor/autoload.php"); dvb 28 10 2024
require_once("../../global/config.php");
require_once('../classes/api_key_authenticater.php');

use GuzzleHttp\Psr7\Request;

header('Content-Type: application/json; charset=utf-8');

global $db;
// Log in comming 

// end of log 

#GET UPDATED ENTRIES FROM DATABASE 
log_debug_msg("Running CRON at - " . time());



# NEW GROUP BY IMPLEMENTATION 

/*
1. Select all PK_ACCOUNTS that have recived changes 
2. Get upto 25 students which are updated 
3. Make JSON for updating to LSQ
4. Get fail / success responsed per STUDENT ID 
5. Mark Fail / SUCCESS 
*/


$sql_pk_accounts = "SELECT * FROM STUDENT_UPDATE_LOG  WHERE SYNC_STATUS = 0 AND PK_ACCOUNT IN (63,15,80,100)  GROUP BY PK_ACCOUNT ORDER BY FIELD(PK_ACCOUNT,63,15,80,100)";
$pk_accounts = $db->Execute($sql_pk_accounts);
while (!$pk_accounts->EOF) {

    $PK_ACCOUNT = $pk_accounts->fields['PK_ACCOUNT'];
    $sql_students_for_update = "SELECT STUDENT_UPDATE_LOG.* , S_STUDENT_MASTER.LSQ_ID FROM STUDENT_UPDATE_LOG LEFT JOIN S_STUDENT_MASTER ON S_STUDENT_MASTER.PK_STUDENT_MASTER = STUDENT_UPDATE_LOG.PK_STUDENT_MASTER  WHERE SYNC_STATUS = 0 AND STUDENT_UPDATE_LOG.PK_ACCOUNT = $PK_ACCOUNT AND (S_STUDENT_MASTER.LSQ_ID IS NOT NULL AND S_STUDENT_MASTER.LSQ_ID != '')
    -- and S_STUDENT_MASTER.PK_STUDENT_MASTER = 1450837
     GROUP BY PK_STUDENT_MASTER LIMIT 25"; 
    $students_for_update = $db->Execute($sql_students_for_update);
    log_debug_msg("CRON : SQL TO SELECT UPDATED 25 STUDENTS PER PK ACCOUNT - " . $sql_students_for_update);
    $Records_25 = [];
    $MAP_PK_STUDENT_MASTER_AND_LSQ_ID = [];

    if ($students_for_update->RecordCount() > 0) {

        while (!$students_for_update->EOF) {

            // ECHO 'HERE';EXIT;

            $LSQ_ID = $students_for_update->fields['LSQ_ID'];
            $MAP_PK_STUDENT_MASTER_AND_LSQ_ID[$LSQ_ID] = $students_for_update->fields['PK_STUDENT_MASTER'];
            $Records_25[]['Fields'] = update_lead($LSQ_ID);
            $students_for_update->MoveNext();
        }
        $LeadPropertiesList = [];
        $LeadPropertiesList['LeadPropertiesList'] = $Records_25;
    
        #GET CREDENTIALS OF PK_ACCOUNT
         $cred_sql = "SELECT * FROM Z_ACCOUNT_LSQ_SETTINGS WHERE PK_ACCOUNT = $PK_ACCOUNT AND PK_ACCOUNT IS NOT NULL AND ( ACCESS_KEY  != '' AND ACCESS_KEY IS NOT NULL )";
        $cred_res = $db->Execute($cred_sql);
        if ($cred_res->RecordCount() > 0) { 
    
            $ACCESS_KEY = $cred_res->fields['ACCESS_KEY'];
            $SECRET_KEY = $cred_res->fields['SECRET_KEY'];
        
            // dvb 28 10 2024
            if($PK_ACCOUNT == 63){
                //         string(207) "Non-existent field(s) provided: mx_Are_you_a_US_citizen,mx_Home_Phone_Number,mx_program_code,mx_Preferred_Start_Date,mx_Term_start_date,mx_High_School_Attended,mx_High_School_City_and_State,mx_Year_Graduated"
                // Definimos los campos a eliminar
                $fieldsToRemove = [
                    'mx_Are_you_a_US_citizen',
                    'mx_Home_Phone_Number',
                    'mx_program_code',
                    'mx_Preferred_Start_Date',
                    'mx_Term_start_date',
                    'mx_High_School_Attended',
                    'mx_High_School_City_and_State',
                    'mx_Year_Graduated',
                ];

                // Iteramos sobre el arreglo y eliminamos los campos especificados
                foreach ($LeadPropertiesList['LeadPropertiesList'] as &$leadProperty) {
                    foreach ($leadProperty['Fields'] as $key => $field) {
                        if (in_array($field['Attribute'], $fieldsToRemove, true)) {
                            unset($leadProperty['Fields'][$key]);
                        }
                    }
                }

                // Reindexar el arreglo después de eliminar elementos
                foreach ($LeadPropertiesList['LeadPropertiesList'] as &$leadProperty) {
                    $leadProperty['Fields'] = array_values($leadProperty['Fields']);
                }

                // Mostramos el resultado
                // print_r($LeadPropertiesList);exit;
                // ADD Lead Status in Diamond - Update ProspectStage (LSQ Schema) in LSQ && Student ID in Diamond - Update mx_Student_ID(LSQ Schema) in LSQ
            }
            // dvb 01 09 2025
            if($PK_ACCOUNT == 100){
                // {"Reason":"Non-existent field(s) provided: mx_Middle_Name,mx_Marital_Status,mx_Are_you_a_US_citizen,mx_Home_Phone_Number,mx_Program,mx_program_code,mx_Term_start_date,mx_High_School_Attended,mx_High_School_City_and_State,mx_Year_Graduated"
                // Definimos los campos a eliminar
                $fieldsToRemove = [
                    'mx_Middle_Name',
                    'mx_Marital_Status',
                    'mx_Are_you_a_US_citizen',
                    'mx_Home_Phone_Number',
                    'mx_Program',
                    'mx_program_code',
                    'mx_Term_start_date',
                    'mx_High_School_Attended',
                    'mx_High_School_City_and_State',
                    'mx_Year_Graduated',
                ];

                // Iteramos sobre el arreglo y eliminamos los campos especificados
                foreach ($LeadPropertiesList['LeadPropertiesList'] as &$leadProperty) {
                    foreach ($leadProperty['Fields'] as $key => $field) {
                        if (in_array($field['Attribute'], $fieldsToRemove, true)) {
                            unset($leadProperty['Fields'][$key]);
                        }
                    }
                }

                // Reindexar el arreglo después de eliminar elementos
                foreach ($LeadPropertiesList['LeadPropertiesList'] as &$leadProperty) {
                    $leadProperty['Fields'] = array_values($leadProperty['Fields']);
                }

                // Mostramos el resultado
                // print_r($LeadPropertiesList);exit;
                // ADD Lead Status in Diamond - Update ProspectStage (LSQ Schema) in LSQ && Student ID in Diamond - Update mx_Student_ID(LSQ Schema) in LSQ
            }

            // dump(json_encode($LeadPropertiesList));
            $RESPONSE = send_update_request_to_lsq_via_curl_bulk(json_encode($LeadPropertiesList), $ACCESS_KEY, $SECRET_KEY);
    
            $failed = $RESPONSE->Details->FailedLeads;
            $successful = $RESPONSE->Details->SuccessfulLeads;
            // dump($failed, $successful);
            // print_r($RESPONSE);
            foreach ($failed as $keyf => $valuef) {
                # code... 
                $PK_STUDENT_MASTER_F = $MAP_PK_STUDENT_MASTER_AND_LSQ_ID[$valuef->IdentifierValue];
                update_lead_status(3, $PK_STUDENT_MASTER_F);
            }
            foreach ($successful as $keys => $values) {
                # code... 
                $PK_STUDENT_MASTER_S = $MAP_PK_STUDENT_MASTER_AND_LSQ_ID[$values->IdentifierValue];
                update_lead_status(2, $PK_STUDENT_MASTER_S);
            }
        }
    } 
    var_dump($RESPONSE);
    // dump($RESPONSE);
    $pk_accounts->MoveNext();
}



function update_lead_status($STATUS, $PK_STUDENT_MASTER)
{
    global $db;
    $turn_off = "UPDATE STUDENT_UPDATE_LOG SET SYNC_STATUS = '$STATUS'  WHERE PK_STUDENT_MASTER = $PK_STUDENT_MASTER AND SYNC_STATUS = '0'";
    $turn_off_res = $db->Execute($turn_off);
}



// $get_updated = "SELECT * FROM STUDENT_UPDATE_LOG  WHERE SYNC_STATUS = 0 GROUP BY PK_STUDENT_MASTER";
// $updated = $db->Execute($get_updated);
// log_debug_msg("Running CRON on following sql - " . $get_updated);
// while (!$updated->EOF) {
//     # code...

//     $PK_STUDENT_MASTER_1 = $updated->fields['PK_STUDENT_MASTER'];
//     $get_lsq_id = "SELECT LSQ_ID FROM S_STUDENT_MASTER WHERE PK_STUDENT_MASTER = $PK_STUDENT_MASTER_1";
//     $LSQ_ID_res = $db->Execute($get_lsq_id);
//     $LSQ_ID = $LSQ_ID_res->fields['LSQ_ID'];
//     if ($LSQ_ID == '' || $LSQ_ID == null) {
//     } else {
//         update_lead($LSQ_ID);
//         $turn_off = "UPDATE STUDENT_UPDATE_LOG SET SYNC_STATUS = '1'  WHERE PK_STUDENT_MASTER = $PK_STUDENT_MASTER_1";
//         $turn_off_res = $db->Execute($turn_off);
//     }
//     $updated->MoveNext();
// }


function update_lead($LSQ_ID)
{
    // dump("Updating lead $LSQ_ID - at " . date("d-m-Y H:i:s"));
    log_debug_msg("Updating lead $LSQ_ID - at " . date("d-m-Y H:i:s"));
    global $db;
     $query = "SELECT SSM.* FROM S_STUDENT_MASTER AS SSM WHERE LSQ_ID = '$LSQ_ID'";
    $student_master_info = $db->Execute($query);
    if ($student_master_info->RecordCount() == 0) {
        $data['SUCCESS'] = 1;
        $data['MESSAGE'] = "No record found for given ProspectID.";
        $data = json_encode($data);
        echo $data;
        exit;
    }

    $PK_STUDENT_MASTER = get_student_master_info($RESPONSE, $student_master_info);
    get_contact_info($RESPONSE, $PK_STUDENT_MASTER);
    #get enrollment
    list(
        $PK_STUDENT_ENROLLMENT,
        $PK_CAMPUS_PROGRAM,
        $PK_CAMPUS_PROGRAM_CODE,
        $PK_CAMPUS, $PK_REPRESENTATIVE,
        $PK_LEAD_SOURCE,
        $PK_LEAD_CONTACT_SOURCE,
        $PK_TERM_MASTER,
        $PK_STUDENT_STATUS
    ) = get_student_enrollment_info($PK_STUDENT_MASTER, $LSQ_ID , $RESPONSE['PK_ACCOUNT']);
    
    // $RESPONSE['mx_Campus_E'] = $PK_STUDENT_ENROLLMENT;
    log_debug_msg("PK_TERM_MASTER".$PK_TERM_MASTER);
    
    //$RESPONSE['ProspectStage'] = $PK_STUDENT_STATUS;
    $RESPONSE['mx_Program'] = $PK_CAMPUS_PROGRAM;
    $RESPONSE['mx_program_code'] = $PK_CAMPUS_PROGRAM_CODE;

    // dvb 01 11 2024
    // dvb 01 11 2024
    if($RESPONSE['PK_ACCOUNT'] == 63){
        // ADD Lead Status in Diamond - Update ProspectStage (LSQ Schema) in LSQ && Student ID in Diamond - Update mx_Student_ID(LSQ Schema) in LSQ
        $RESPONSE['ProspectStage'] = $PK_STUDENT_STATUS;
        $academicssql =  "SELECT STUDENT_ID FROM S_STUDENT_ACADEMICS where PK_ACCOUNT = '$RESPONSE[PK_ACCOUNT]' AND PK_STUDENT_MASTER = ".$PK_STUDENT_MASTER;
        $academicsinfo = $db->Execute($academicssql);
        if ($academicsinfo->RecordCount() > 0) {
            $RESPONSE['mx_Student_ID'] = $academicsinfo->fields['STUDENT_ID'];
        }
    }
    // dvb 01 11 2024
    // dvb 01 11 2024
    // dvb 09 01 2025
    if($RESPONSE['PK_ACCOUNT'] == 100){
        // ADD Lead Status in Diamond - Update ProspectStage (LSQ Schema) in LSQ && Student ID in Diamond - Update mx_Student_ID(LSQ Schema) in LSQ
        $RESPONSE['ProspectStage'] = $PK_STUDENT_STATUS;
    }
    // dvb 09 01 2025

    $RESPONSE['mx_Campus'] = $PK_CAMPUS;
    // $RESPONSE['OwnerIdEmailAddress'] = getAdmissionRep($PK_REPRESENTATIVE);
    $RESPONSE['Source'] = GetLeadSource($PK_LEAD_SOURCE);
    $RESPONSE['Origin'] = GetLeadSourceContact($PK_LEAD_CONTACT_SOURCE);
    //Sep-1 Reverse API for preferred start date & mx_Term_start_date
    if ($RESPONSE['LSQ_PREFFERED_START_DATE'] == '' || $RESPONSE['LSQ_PREFFERED_START_DATE'] == null) {
        $RESPONSE['mx_Preferred_Start_Date'] =  GetTermDate_OR_GetFromNote($PK_TERM_MASTER, $PK_STUDENT_ENROLLMENT);
    } else {
        $RESPONSE['mx_Preferred_Start_Date'] =  $RESPONSE['LSQ_PREFFERED_START_DATE'];
    }
    unset($RESPONSE['LSQ_PREFFERED_START_DATE']);
    log_debug_msg("PK_TERM_MASTER".$PK_TERM_MASTER);
    $RESPONSE['mx_Term_start_date'] = GetTermDateActual($PK_TERM_MASTER, $PK_STUDENT_ENROLLMENT);
    //End of Sep-1 peffered date & mx_Term_start_date
    list($mx_High_School_Attended, $mx_High_School_City_and_State, $mx_Year_Graduated) = get_other_education($PK_STUDENT_MASTER);
    $RESPONSE['mx_High_School_Attended'] = $mx_High_School_Attended;
    $RESPONSE['mx_High_School_City_and_State'] = $mx_High_School_City_and_State;
    $RESPONSE['mx_Year_Graduated'] = $mx_Year_Graduated;
unset($RESPONSE['PK_ACCOUNT']);
    // $RESPONSE['sql'] = $query;

    #CONVERT "key" => "value" to {"KEY"=> "KEY" , "VALUE"=>"VALUE"}
    $FORMATTER = [];
    foreach ($RESPONSE as $key => $value) {
        // dump($key,$value);
        $FORMATTER[] = ["Attribute" => $key, "Value" => $value];
    }
    // dd($FORMATTER);
    return $FORMATTER;
    /* dump("dumping text");
    log_debug_msg("Outbound JSON encoded \n " . json_encode($FORMATTER));
    log_debug_msg("Outbound JSON RAW \n " . print_r($FORMATTER, true));
    send_update_request_to_lsq_via_curl(json_encode($FORMATTER), $LSQ_ID);
    dump("dumping text");*/
}

function ReturneSuccessResponse(array $message_array, array &$success_collector, $exit_immidiatly = false)
{
    global $LSQ_ID;
    $success_collector = array_merge($success_collector, $message_array);
    $data = $success_collector;
    //Send response
    if ($exit_immidiatly) {
        $data = json_encode($data);
        // dump($data);
        // send_update_request_to_lsq_via_guzzle($data);
        send_update_request_to_lsq_via_curl($data, $LSQ_ID);
        exit;
    } else {
        return $message_array;
    }
}
// function send_update_request_to_lsq_via_guzzle($data)
// {

//     $client = new GuzzleHttp\Client();
//     $body = $data;
//     $request = new GuzzleHttp\Psr7\Request('POST', 'https://api-us11.leadsquared.com/v2/LeadManagement.svc/Lead.Capture?accessKey=u$re943180527afce8447ca4f64458c01ba&secretKey=c219e457c3ac78c89b7023ec60bc33bf3f44dda9', [], $body);


//     // dump($request);
//     $res = $client->sendAsync($request)->wait();
//     // dump($res);
//     // echo dump($res->getBody());
// }
function send_update_request_to_lsq_via_curl_bulk($data, $ACCESS_KEY, $SECRET_KEY)
{

    // echo 'https://api-us11.leadsquared.com/v2/LeadManagement.svc/Lead/Bulk/UpdateV2?accessKey=' . $ACCESS_KEY . '&secretKey=' . $SECRET_KEY . '&postUpdatedLead=true';
    // exit;
    // print_r($data);exit;


    $curl = curl_init();

    curl_setopt_array($curl, array(


        CURLOPT_URL => 'https://api-us11.leadsquared.com/v2/LeadManagement.svc/Lead/Bulk/UpdateV2?accessKey=' . $ACCESS_KEY . '&secretKey=' . $SECRET_KEY . '&postUpdatedLead=true',

        // CURLOPT_URL => 'https://api-us11.leadsquared.com/v2/LeadManagement.svc/Lead.Update?accessKey=u$re943180527afce8447ca4f64458c01ba&secretKey=c219e457c3ac78c89b7023ec60bc33bf3f44dda9&leadId=30c7bd43-28d7-4df7-88cb-bfe3fe3fc71b&postUpdatedLead=true',

        // CURLOPT_URL => 'https://asyncapi-us11.leadsquared.com/lead/update?accessKey=u$re943180527afce8447ca4f64458c01ba&secretKey=c219e457c3ac78c89b7023ec60bc33bf3f44dda9&leadId=' . $LSQ_ID . '&postUpdatedLead=true',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data),
            'x-api-key:VAtduEJR2Q29Nscdqxrsj8zq7CFmnCDL5ZhAO4PC'
        ),
        CURLOPT_POSTFIELDS => $data

    ));
    curl_setopt($curl, CURLINFO_HEADER_OUT, true);
    $response = curl_exec($curl);
    // echo $response;
    // dump($response);
    // dump(curl_getinfo($curl, CURLINFO_HEADER_OUT));

    log_debug_msg(print_r("Sending below data", true));
    log_debug_msg(print_r($data, true));
    log_debug_msg(print_r("Got below data", true));
    log_debug_msg(print_r($response, true));
    curl_close($curl);
    return json_decode($response);
}
function get_student_master_info(&$RESPONSE, &$student_info)
{
    $RESPONSE['PK_ACCOUNT'] = $student_info->fields['PK_ACCOUNT'];
    $PK_STUDENT_MASTER = $student_info->fields['PK_STUDENT_MASTER'];
    $RESPONSE['ProspectID'] = $student_info->fields['LSQ_ID'];
    $RESPONSE['LSQ_PREFFERED_START_DATE'] = $student_info->fields['LSQ_PREFFERED_START_DATE'];
    $RESPONSE['FirstName'] = $student_info->fields['FIRST_NAME'];
    $RESPONSE['LastName'] = $student_info->fields['LAST_NAME'];
    $RESPONSE['mx_Middle_Name'] = $student_info->fields['MIDDLE_NAME'];
    $RESPONSE['mx_Date_of_Birth'] = $student_info->fields['DATE_OF_BIRTH'] == '0000-00-00' ? null : $student_info->fields['DATE_OF_BIRTH'];
    $RESPONSE['mx_Marital_Status'] = get_marital_status($student_info->fields['PK_MARITAL_STATUS']);
    $RESPONSE['mx_Gender'] = get_gender($student_info->fields['GENDER']);
    if ($student_info->fields['PK_CITIZENSHIP'] == 0) {
        $RESPONSE['mx_Are_you_a_US_citizen'] = "no";
    } else if ($student_info->fields['PK_CITIZENSHIP'] == 1) {
        $RESPONSE['mx_Are_you_a_US_citizen'] = "yes";
    } else {
        $RESPONSE['mx_Are_you_a_US_citizen'] = "no"; //Sep-1 changed default return vlaue from null to "NO"
    }
    return $PK_STUDENT_MASTER;
}
function get_contact_info(&$RESPONSE, $PK_STUDENT_MASTER)
{
    global $db;
    $contact_query = "SELECT 
    (SELECT CONCAT(EMAIL , '||||^^', USE_EMAIL) FROM S_STUDENT_CONTACT WHERE EMAIL != ''  AND  PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' ORDER BY  PK_STUDENT_CONTACT LIMIT 1 ) as EMAIL , 
    (SELECT OTHER_PHONE FROM S_STUDENT_CONTACT WHERE OTHER_PHONE != '' AND  PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' ORDER BY  PK_STUDENT_CONTACT LIMIT 1 ) as OTHER_PHONE,
    (SELECT HOME_PHONE FROM S_STUDENT_CONTACT WHERE HOME_PHONE != '' AND  PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' ORDER BY  PK_STUDENT_CONTACT LIMIT 1 ) as HOME_PHONE,
    (SELECT CONCAT(CELL_PHONE ,'||||^^', OPT_OUT) FROM S_STUDENT_CONTACT WHERE CELL_PHONE != '' AND  PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' ORDER BY  PK_STUDENT_CONTACT LIMIT 1 ) as CELL_PHONE,  
    (SELECT CONCAT(ADDRESS , '||||^^' , ADDRESS_1 , '||||^^' , CITY , '||||^^' ,PK_STATES , '||||^^' , ZIP , '||||^^' , PK_COUNTRY) FROM S_STUDENT_CONTACT WHERE ADDRESS != '' AND  PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' ORDER BY  PK_STUDENT_CONTACT LIMIT 1 ) as ADDRESS 
    FROM S_STUDENT_CONTACT AS SSC WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER'
    GROUP BY PK_STUDENT_MASTER";
    // dds($contact_query);
    $student_contact_info = $db->Execute($contact_query);
    /*   EMAIL=> EMAIL , USE_EMAIL
    OTHER_PHONE
    HOME_PHONE
    CELL_PHONE => CELL_PHONE,OPT_OUT
    ADDRESS => ADDRESS,  ADDRESS_1,    CITY,    ZIP,    PK_COUNTRY */
    $EMAIL_CONCAT = explode('||||^^', $student_contact_info->fields['EMAIL']);
    $CELL_PHONE_CONCAT = explode('||||^^', $student_contact_info->fields['CELL_PHONE']);
    $ADDRESS_CONCAT = explode('||||^^', $student_contact_info->fields['ADDRESS']);

    // dump($CELL_PHONE_CONCAT);

    /* 
    LABLES EXPECED BY LSQ  
    EmailAddress
    Phone
    mx_Home_Phone_Number
    Mobile
    DoNotCall
    DoNotEmail
    mx_Street1
    mx_Street2
    mx_City
    mx_State
    mx_Country
    mx_Zip */
    $RESPONSE['EmailAddress'] = $EMAIL = $EMAIL_CONCAT[0];
    //Sep-1 : Fixed USE_EMAIL LOGIC
    $USE_EMAIL  = $EMAIL_CONCAT[1] == '' ? 0 : $EMAIL_CONCAT[1];
    if ($USE_EMAIL == 1) {
        $RESPONSE['DoNotEmail'] = 0;
    } else {
        $RESPONSE['DoNotEmail'] = 1;
    }

    $RESPONSE['Mobile'] = $OTHER_PHONE = return_digits_from_contact_string($student_contact_info->fields['OTHER_PHONE']);
    $RESPONSE['mx_Home_Phone_Number'] = $HOME_PHONE = return_digits_from_contact_string($student_contact_info->fields['HOME_PHONE']);
    $RESPONSE['Phone'] = $CELL_PHONE = return_digits_from_contact_string($CELL_PHONE_CONCAT[0]);
    $RESPONSE['DoNotCall'] = $OPT_OUT = $CELL_PHONE_CONCAT[1]  == '' ? 0 : $CELL_PHONE_CONCAT[1];
    $RESPONSE['mx_Street1'] = $ADDRESS = $ADDRESS_CONCAT[0];
    $RESPONSE['mx_Street2'] = $ADDRESS_1 = $ADDRESS_CONCAT[1];
    $RESPONSE['mx_City'] = $CITY = $ADDRESS_CONCAT[2];
    $PK_STATE = $ADDRESS_CONCAT[3];
    $RESPONSE['mx_State'] = get_state_name($PK_STATE);

    $RESPONSE['mx_Zip'] = $ZIP = $ADDRESS_CONCAT[4];
    $PK_COUNTRY = $ADDRESS_CONCAT[5];
    $RESPONSE['mx_Country'] = get_country_name($PK_COUNTRY);
}
function return_digits_from_contact_string($contact_string)
{

    if ($contact_string != '' || $contact_string != null) {
        return preg_replace('/[^0-9]/', '', $contact_string);
    } else {
        return null;
    }
}
function get_state_name($PK_STATES)
{
    global $db;
    $state_query =  "SELECT * FROM `Z_STATES` WHERE PK_STATES = '$PK_STATES'";
    $state_info = $db->Execute($state_query);
    if ($state_info->RecordCount() > 0) {
        return $state_info->fields['STATE_NAME'];
    } else {
        return null;
    }
}
function get_country_name($PK_COUNTRY)
{
    global $db;
    $country_query =  "SELECT * FROM `Z_COUNTRY` WHERE PK_COUNTRY = '$PK_COUNTRY'";
    $country_info = $db->Execute($country_query);
    if ($country_info->RecordCount() > 0) {
        return $country_info->fields['NAME'];
    } else {
        return null;
    }
}
function get_marital_status($PK_MARITAL_STATUS)
{
    global $db;
    $marital_query =  "SELECT * FROM `Z_MARITAL_STATUS` WHERE PK_MARITAL_STATUS = '$PK_MARITAL_STATUS'";
    $marital_info = $db->Execute($marital_query);
    if ($marital_info->RecordCount() > 0) {
        return $marital_info->fields['MARITAL_STATUS'];
    } else {
        return null;
    }
}
function get_gender($PK_GENDER)
{
    global $db;
    $gender_query =  "SELECT * FROM `Z_GENDER` WHERE PK_GENDER = '$PK_GENDER'";
    $gender_info = $db->Execute($gender_query);
    if ($gender_info->RecordCount() > 0) {
        return $gender_info->fields['GENDER'];
    } else {
        return null;
    }
}
function get_other_education($PK_STUDENT_MASTER)
{


    global $db;
    $gender_query =  "SELECT * FROM `S_STUDENT_OTHER_EDU` WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER'";
    $gender_info = $db->Execute($gender_query);
    if ($gender_info->RecordCount() > 0) {

        $SCHOOL_NAME = $gender_info->fields['SCHOOL_NAME'];
        $CITY = $gender_info->fields['CITY'];
        $STATE = get_state_name($gender_info->fields['PK_STATE']);
        $GRADUATED_DATE = $gender_info->fields['GRADUATED_DATE'] == '0000-00-00' ? null : $gender_info->fields['GRADUATED_DATE'];
        return [$SCHOOL_NAME, $CITY . ',' . $STATE, $GRADUATED_DATE];
    } else {
        return [null, null, null];
    }
}
function getAdmissionRep($PK_REPRESENTATIVE)
{
    global $db;
    $res_st = $db->Execute("SELECT EMAIL from S_EMPLOYEE_MASTER WHERE PK_EMPLOYEE_MASTER = '$PK_REPRESENTATIVE'");
    return $PK_REPRESENTATIVE = $res_st->fields['EMAIL'];
}
function ReturneErrorResponse(array $message_array, array &$error_collector, $exit_immidiatly = false)
{

    $error_collector = array_merge($error_collector, $message_array);
    $data['SUCCESS'] = 0;
    $data['ERROR'] = $error_collector;
    //Send response
    if ($exit_immidiatly) {
        $data = json_encode($data);
        echo $data;
        exit;
    } else {

        return $message_array;
    }
}
function GetTermDate_OR_GetFromNote($PK_TERM_MASTER, $PK_STUDENT_ENROLLMENT)
{
    global $db;
    $res_st = $db->Execute("SELECT TERM_DESCRIPTION FROM S_TERM_MASTER WHERE  PK_TERM_MASTER = '$PK_TERM_MASTER'");
    if ($res_st->RecordCount() > 0) {
        return $res_st->fields['TERM_DESCRIPTION'];
    } else {

        $res_st = $db->Execute('SELECT NOTES FROM S_STUDENT_NOTES WHERE  NOTES LIKE "%LeadSquared API%" AND PK_STUDENT_ENROLLMENT = "' . $PK_STUDENT_ENROLLMENT . '"');
        $NOTE = $res_st->fields['NOTES'];
        $NOTE = str_replace("LeadSquared API - Student's preferred start date is", '', $NOTE);
        $DATE = trim($NOTE);
        return $DATE;
    }
}

function GetTermDateActual($PK_TERM_MASTER)
{
    global $db;
    if ($PK_TERM_MASTER != '') {
        log_debug_msg("SELECT BEGIN_DATE FROM S_TERM_MASTER WHERE  PK_TERM_MASTER = '$PK_TERM_MASTER'");
        $res_st = $db->Execute("SELECT BEGIN_DATE FROM S_TERM_MASTER WHERE  PK_TERM_MASTER = '$PK_TERM_MASTER'");
        if ($res_st->RecordCount() > 0) {
            return $res_st->fields['BEGIN_DATE'];
        } else {
            return null;
        }
    } else {
        return null;
    }
}
function GetLeadSource($PK_LEAD_SOURCE)
{
    global $db;
    $query = "SELECT * FROM `M_LEAD_SOURCE` WHERE PK_LEAD_SOURCE = '$PK_LEAD_SOURCE'";
    $query = $db->Execute($query);
    return $query->fields['LEAD_SOURCE'];
}
function GetLeadSourceContact($PK_LEAD_CONTACT_SOURCE)
{
    global $db;
    $res_st = $db->Execute("select LEAD_CONTACT_SOURCE from M_LEAD_CONTACT_SOURCE WHERE  PK_LEAD_CONTACT_SOURCE = '$PK_LEAD_CONTACT_SOURCE' ");
    return  $res_st->fields['LEAD_CONTACT_SOURCE'];
}
function get_student_enrollment_info($PK_STUDENT_MASTER, $LSQ_ID , $S_PK_ACCOUNT)
{

    global $db;
    $ENROLLMENT_WITH_LSQ_ID = "SELECT * FROM S_STUDENT_ENROLLMENT WHERE LSQ_ID = '$LSQ_ID' AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER'";
    $ANY_LATEST_ENROLLMENT = "SELECT * FROM S_STUDENT_ENROLLMENT WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND IS_ACTIVE_ENROLLMENT = 1 ORDER BY PK_STUDENT_ENROLLMENT DESC";

    

    $query1 = $db->Execute($ENROLLMENT_WITH_LSQ_ID);
    if ($query1->RecordCount() > 0) {
        //GET DATA 1

        $PK_STUDENT_ENROLLMENT = $query1->fields['PK_STUDENT_ENROLLMENT'];
        $PK_CAMPUS_PROGRAM = get_program_campus($query1->fields['PK_CAMPUS_PROGRAM']);        
        $PK_CAMPUS_PROGRAM_CODE = get_program_code_campus($query1->fields['PK_CAMPUS_PROGRAM']);
        
        $PK_CAMPUS = get_campus_name_for_enrollment($PK_STUDENT_ENROLLMENT);
        $PK_REPRESENTATIVE = $query1->fields['PK_REPRESENTATIVE'];
        $PK_LEAD_SOURCE  = $query1->fields['PK_LEAD_SOURCE'];
        $PK_LEAD_CONTACT_SOURCE = $query1->fields['PK_LEAD_CONTACT_SOURCE'];
        $PK_TERM_MASTER = $query1->fields['PK_TERM_MASTER'];
        $PK_STUDENT_STATUS_ID = $query1->fields['PK_STUDENT_STATUS'];
        $PK_STUDENT_STATUS = GetStudentStatus($S_PK_ACCOUNT, $PK_STUDENT_STATUS_ID);
    } else {
        $query2 = $db->Execute($ANY_LATEST_ENROLLMENT);


        if ($query2->RecordCount() > 0) {
            $PK_STUDENT_ENROLLMENT = $query2->fields['PK_STUDENT_ENROLLMENT'];
            $PK_CAMPUS_PROGRAM = get_program_campus($query2->fields['PK_CAMPUS_PROGRAM']);            
            $PK_CAMPUS_PROGRAM_CODE = get_program_code_campus($query2->fields['PK_CAMPUS_PROGRAM']);
            
            $PK_CAMPUS = get_campus_name_for_enrollment($PK_STUDENT_ENROLLMENT);
            $PK_REPRESENTATIVE = $query2->fields['PK_REPRESENTATIVE'];
            $PK_LEAD_SOURCE  = $query2->fields['PK_LEAD_SOURCE'];
            $PK_LEAD_CONTACT_SOURCE = $query2->fields['PK_LEAD_CONTACT_SOURCE'];
            $PK_TERM_MASTER = $query2->fields['PK_TERM_MASTER'];
            $PK_STUDENT_STATUS_ID = $query2->fields['PK_STUDENT_STATUS'];
            $PK_STUDENT_STATUS = GetStudentStatus($S_PK_ACCOUNT, $PK_STUDENT_STATUS_ID);
        } else {
            return [null, null, null];
        }
    }

    return [
        $PK_STUDENT_ENROLLMENT,
        $PK_CAMPUS_PROGRAM,
        $PK_CAMPUS_PROGRAM_CODE,
        $PK_CAMPUS,
        $PK_REPRESENTATIVE,
        $PK_LEAD_SOURCE,
        $PK_LEAD_CONTACT_SOURCE,
        $PK_TERM_MASTER,
        $PK_STUDENT_STATUS
    ];
}
function GetStudentStatus($PK_ACCOUNT , $PK_STUDENT_STATUS_MASTER){
    global $db;
    $res_st = $db->Execute("SELECT PK_STUDENT_STATUS, CONCAT(STUDENT_STATUS ) AS STUDENT_STATUS FROM M_STUDENT_STATUS WHERE PK_STUDENT_STATUS = '$PK_STUDENT_STATUS_MASTER' AND PK_ACCOUNT = '$PK_ACCOUNT' "); //todo //static 1 for "lead" status code
    $STUDENT_STATUS = $res_st->fields['STUDENT_STATUS'];
    log_debug_msg("SELECT PK_STUDENT_STATUS, CONCAT(STUDENT_STATUS ) AS STUDENT_STATUS FROM M_STUDENT_STATUS WHERE PK_STUDENT_STATUS = '$PK_STUDENT_STATUS_MASTER' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
    if ($res_st->RecordCount() == 0) {
        // ReturneErrorResponse(["Student cannot be entered due to invalid status. Please check with school admin if  an active 'lead' status for admissions"], $error_collector); //todo // check this error text // check if we have to fire this error at all ?
        return null;
    } else {
        return $STUDENT_STATUS;
    }
}
function get_program_campus($PK_CAMPUS_PROGRAM)
{

    global $db;
    $query = "SELECT * FROM `M_CAMPUS_PROGRAM` WHERE PK_CAMPUS_PROGRAM = '$PK_CAMPUS_PROGRAM'";
    $query = $db->Execute($query);
    return $query->fields['DESCRIPTION'];
}
function get_program_code_campus($PK_CAMPUS_PROGRAM)
{

    global $db;
    $query = "SELECT * FROM `M_CAMPUS_PROGRAM` WHERE PK_CAMPUS_PROGRAM = '$PK_CAMPUS_PROGRAM'";
    $query = $db->Execute($query);
    return $query->fields['CODE'];
}
function get_campus_name_for_enrollment($PK_STUDENT_ENROLLMENT)
{
    global $db;
    $query = "SELECT CAMPUS_NAME FROM `S_STUDENT_CAMPUS` LEFT JOIN S_CAMPUS on S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS WHERE PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ";
    $query = $db->Execute($query);
    return $query->fields['CAMPUS_NAME'];
}

function log_debug_msg($msg)
{
    // $file_path = '';
    $msg = print_r($msg, true);
    if ($_SERVER['HTTP_HOST'] == 'localhost') {
        $file_path = '/var/www/html/D3/school/temp/LSQ_REVERSE_LOG.txt';
        $myFile = "/tmp/LSQ_REVERSE_API_LOG_BULK.txt";
    } else {
        $file_path = '/var/www/html/D3/school/temp/LSQ_REVERSE_LOG.txt';
        $myFile = "/tmp/LSQ_REVERSE_API_LOG_BULK.txt";
    }
    // $myFile = "../../school/temp/leadsquared_incoming.txt";
    $fh = fopen($myFile, 'a') or ("can't open file");
    fwrite($fh, "\n -- \n $msg \n --\n");
    fclose($fh);
}
