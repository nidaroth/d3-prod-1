<?php

$error_collector = [];
$success_collector = [];
require_once("../../global/config.php");
require_once('../classes/api_key_authenticater.php');
error_reporting(0);
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

//Log in comming 
$myFile = "../../school/temp/leadsquared.txt";
try {
    $fh = fopen($myFile, 'a') or ("can't open file");
    fwrite($fh, "\n\n--------------------- Below is incomming API REQUEST at " . date('d-m-Y H:i:s') . "-----------------
            -------------------------\n");
    foreach ($_SERVER as $h => $v)
        if (preg_match('/HTTP\_(.+)/', $h, $hp))
            fwrite($fh, "$h = $v\n");
    fwrite($fh, "\r\n");
    fwrite($fh, file_get_contents('php://input'));
    fclose($fh);
    fwrite($fh, "\n\n--------------------------------------
        -------------------------\n");
} catch (\Throwable $th) {
    //throw $th;
}
//end of log

//$DATA = "ADD STRUCTURE OF IN COMMING DATA"; 
$DATA = $HEADERDATA = json_decode(urldecode(file_get_contents('php://input')));

if (isset($DATA->Current)) {
    $DATA = $DATA->Current;
}
//Check API Authentication  
header('Content-Type: application/json; charset=utf-8');
$PK_ACCOUNT = API_KEY_AUTHENTICATER::api_auth($HEADERDATA);
if ($DATA == null) {
    ReturneErrorResponse(["Invalid JSON recived in incomming request. Please validate format of JSON data."], $error_collector, true);
}
$DATA->PK_ACCOUNT = $PK_ACCOUNT;


#Algorithem
//ADDITIONAL : send data to D4
//1.Validate all fields and dependencies/refenracnes
//2.Check for Duplicate using LSQ  
//3.Check for Duplicate using DIAMONDSIS ALGO
//4.Insert Lead

//DIAM-2038 - Sending to D4
$D4_data = [];
try {
    if($DATA->PK_ACCOUNT == 95 && $_SERVER['HTTP_HOST'] == 'd3-2.diamondsis.com'){
      
        $D4_data['previousLeadId'] = $DATA->ProspectID;
        $D4_data['firstName'] = $DATA->FirstName;
        $D4_data['middleName'] = $DATA->mx_Middle_Name;
        $D4_data['lastName'] = $DATA->LastName     ;
        $D4_data['gender'] = $DATA->mx_Gender     ;
        $D4_data['dateOfBirth'] = $DATA->mx_Date_Of_Birth     ;
        $D4_data['email'] = $DATA->EmailAddress     ;
        $D4_data['otherPhone'] = $DATA->Mobile     ;
        $D4_data['homePhone'] = $DATA->mx_Home_Phone_Number     ;
        $D4_data['mobilePhone'] = $DATA->Phone ?? $DATA->Mobile      ;
        $D4_data['doNotCall'] = $DATA->DoNotCall     ;
        $D4_data['doNotEmail'] = $DATA->DoNotEmail     ;
        $D4_data['address'] = $DATA->mx_Street1     ;
        $D4_data['addressSecondLine'] = $DATA->mx_Street2     ;
        $D4_data['city'] = $DATA->mx_City     ;
        $D4_data['state'] = $DATA->mx_State     ;
        $D4_data['country'] = $DATA->mx_Country     ;
        $D4_data['zip'] = $DATA->mx_Zip     ;
        $D4_data['isUSCitizen'] = $DATA->mx_Are_you_a_US_citizen     ;
        $D4_data['maritalStatus'] = $DATA->mx_Marital_Status     ;
        $D4_data['cityState'] = $DATA->mx_City.','.$DATA->mx_State    ;

        // $D4_data['graduatedDate'] = $DATA->     ;
        if (isset($DATA->mx_Year_Graduated)) {
            if (substr($DATA->mx_Year_Graduated, 0, 2) == '00') {
                $D4_data['graduatedDate'] = "06/01/" . date('Y');
            } else {
                $D4_data['graduatedDate'] = $DATA->mx_Year_Graduated;
            }
        }

        // $D4_data['schoolName'] = $DATA->schoolName     ;
        // $D4_data['transcriptCode'] = $DATA->    ;
        $D4_data['campus'] = $DATA->mx_Campus     ;
        $D4_data['programCode'] = $DATA->mx_program_code ?? $DATA->mx_Program     ;
        $D4_data['leadSource'] = 'Leadsqaured'     ;

        // $D4_response = sendToExternalD4API(json_encode($D4_data));
        // dd($D4_response);
    }
} catch (\Throwable $th) {
    //throw $th;
}

function sendToExternalD4API($data) {
    // API endpoint URL
    $apiUrl = 'https://app.diamondsis.com/commonServer/api/v1/lead/add';

    // Headers
    $apiHeaders = array(
        'X-Client-Key: tenant_unitech',
        'X-Client-Token: c6314466c9ca54cf68f81d7cb50886b1c4a3aea772ce1fa9a3978e0404230097',
        'Content-Type: application/json'
    );

    // Initialize cURL session
    $apiCurl = curl_init($apiUrl);

    // Set cURL options
    curl_setopt($apiCurl, CURLOPT_RETURNTRANSFER,  TRUE );
    curl_setopt($apiCurl, CURLOPT_POST,  TRUE );
    curl_setopt($apiCurl, CURLOPT_POSTFIELDS, $data);
    curl_setopt($apiCurl, CURLOPT_HTTPHEADER, $apiHeaders);

    // Execute cURL session
    $apiResponse = curl_exec($apiCurl);

    // Check for errors
    if(curl_errno($apiCurl)) {
        echo 'Curl error: ' . curl_error($apiCurl);
    }

    // Close cURL session
    curl_close($apiCurl);
    // dump($data);
    // Handle response
    if ($apiResponse !== false) {
        // Response received successfully
        return $apiResponse;
    } else {
        // Error handling response
        return 'Error receiving response.';
    }
   
}
//END OF DIAM-2038 

#pre_requisite 
ConvertDataFields($DATA);
#1
// ValidateData($DATA, $error_collector);
CheckRequired($DATA, $error_collector);
#2 
CheckDuplicateByLsqID($DATA->ProspectID, $error_collector);
#3 
CheckDuplicateByDiamondAlgo($DATA, $error_collector);
#4
CreateStudent($DATA, $error_collector,$D4_data);

//Function declartions 
function ConvertDataFields(&$DATA)
{

    #if LSQ's default dummy code is recived make this field null
    // if(isset($DATA->mx_program_code)){
    //     if(strtolower($DATA->mx_program_code) == strtolower("LSQ_D")){
    //         unset($DATA->mx_program_code);
    //     }
    // }

    //
    if (isset($DATA->mx_Campus)) {
        $DATA->campus_code = $DATA->mx_Campus;
    }

    if (isset($DATA->EmailAddress)) {
        $DATA->EmailAddress = $DATA->EmailAddress;
    }


    if (!isset($DATA->Phone) && isset($DATA->Mobile)) {
        $DATA->Phone = $DATA->Mobile;
        $DATA->Mobile = '';
    }


    if (isset($DATA->DoNotCall)) {
        if ($DATA->DoNotCall == "0") {
            $DATA->DoNotCall = false;
        } else if ($DATA->DoNotCall == "1") {
            $DATA->DoNotCall = true;
        }
    }

    if (isset($DATA->DoNotEmail)) {
        if ($DATA->DoNotEmail == "0") {
            $DATA->DoNotEmail = true;
        } else if ($DATA->DoNotEmail == "1") {
            $DATA->DoNotEmail = false;
        }
    }else{
        $DATA->DoNotEmail = true;
    }

    if (isset($DATA->mx_Are_you_a_US_citizen)) {
        $DATA->mx_Are_you_a_US_citizen = strtolower($DATA->mx_Are_you_a_US_citizen);
        if ($DATA->mx_Are_you_a_US_citizen == "no") {
            $DATA->mx_Are_you_a_US_citizen = false;
        } else if ($DATA->mx_Are_you_a_US_citizen == "yes") {
            $DATA->mx_Are_you_a_US_citizen = true;
        }
    } else {
        $DATA->mx_Are_you_a_US_citizen = true;
    }

    if (isset($DATA->mx_High_School_Attended)) {
        //convert and enter values 


        $DATA->other_education = [];
        $arr_other_education = [];
        $arr_other_education['type'] = "Other";
        if (isset($DATA->mx_High_School_City_and_State))
            $arr_other_education['address'] = $DATA->mx_High_School_City_and_State;
        if (isset($DATA->mx_High_School_Attended))
            $arr_other_education['school_name'] = $DATA->mx_High_School_Attended;
        if (isset($DATA->mx_Year_Graduated)) {
            if (substr($DATA->mx_Year_Graduated, 0, 2) == '00') {
                $arr_other_education['graduation_date'] = "06/01/" . date('Y');
            } else {
                $arr_other_education['graduation_date'] = $DATA->mx_Year_Graduated;
            }
        }


        $DATA->other_education[] = json_encode($arr_other_education);

        //validate 

    }
}
function CheckRequired($DATA, array &$error_collector)
{

    $required = ["ProspectID", "FirstName", "LastName", /* "mx_program_code", */ "mx_Campus" /*"mx_Date_of_Birth", "mx_Street1", "mx_City", "mx_State", "mx_Zip","mx_Are_you_a_US_citizen" , , "mx_Preferred_Start_Date"*/];

    foreach ($required as $field) {
        if (!isset($DATA->$field)) {
            ReturneErrorResponse(["Field '$field' is required"], $error_collector);
        } else if ($DATA->$field == '') {
            ReturneErrorResponse(["Field '$field' is required, Given empty"], $error_collector);
        }
    }

    if (!isset($DATA->EmailAddress) && !isset($DATA->Phone) && !isset($DATA->mx_Home_Phone_Number)) {
        ReturneErrorResponse(["Either of 'EmailAddress','Phone' or 'mx_Home_Phone_Number' is required"], $error_collector);
    }

    if (isset($DATA->EmailAddress)) {
        if ($DATA->EmailAddress != '') {
            if (!filter_var($DATA->EmailAddress, FILTER_VALIDATE_EMAIL)) {
                ReturneErrorResponse(["Given 'EmailAddress' is invalid"], $error_collector);
            }
        }
    }
    #validate incomming gmail address 
    if (isset($DATA->mx_Student_Gmail_Id)) {
        if ($DATA->mx_Student_Gmail_Id != '') {
            if (!filter_var($DATA->mx_Student_Gmail_Id, FILTER_VALIDATE_EMAIL)) {
                ReturneErrorResponse(["Given 'mx_Student_Gmail_Id' is invalid"], $error_collector);
            }
        }
    }

    if (isset($DATA->mx_Home_Phone_Number)) {
        if (!valid_phone($DATA->mx_Home_Phone_Number)) {
            ReturneErrorResponse(["Given 'mx_Home_Phone_Number' is invalid"], $error_collector);
        }
    }

    if (isset($DATA->Phone)) {
        if (!valid_phone($DATA->Phone)) {
            ReturneErrorResponse(["Given 'Phone' is invalid"], $error_collector);
        }
    }

    if (($DATA->Phone == $DATA->mx_Home_Phone_Number && $DATA->Phone != '') || ($DATA->Phone == $DATA->Mobile && $DATA->Phone != '') || ($DATA->mx_Home_Phone_Number == $DATA->Mobile && $DATA->mx_Home_Phone_Number != '')) {
        ReturneErrorResponse(["Fields 'Phone' , 'mx_Home_Phone_Number' or 'Mobile' cannot be same"], $error_collector);
    }
    if (isset($DATA->mx_State)) {
        GetState($DATA->mx_State, $error_collector) ?: ReturneErrorResponse(["Given 'mx_State' is invalid"], $error_collector);
    }
    #removed due to updated api do not have this ield mandetory //todo : unit test ui
    if (isset($DATA->mx_Zip)) {
        if ((strlen($DATA->mx_Zip) != 5 && strlen($DATA->mx_Zip) != 6) || !is_numeric($DATA->mx_Zip)) {
            ReturneErrorResponse(["Given 'mx_Zip' is invalid"], $error_collector);
        }
    }


    // if (isset($DATA->Mobile)) {
    //     if (!valid_phone($DATA->Mobile)) {
    //         ReturneErrorResponse(["Given 'Mobile' is invalid"], $error_collector);
    //     }
    // }



    if (isset($DATA->DoNotCall)) {
        if (!is_bool($DATA->DoNotCall)) {
            ReturneErrorResponse(["Given 'DoNotCall' is invalid, value must be boolean"], $error_collector);
        }
    }

    if (isset($DATA->DoNotEmail)) {
        if (!is_bool($DATA->DoNotEmail)) {
            ReturneErrorResponse(["Given 'DoNotEmail' is invalid, value must be boolean"], $error_collector);
        }
    }


    //Updated for new api request format
    if (isset($DATA->other_education)) {

        foreach ($DATA->other_education as $key => $other_education) {
            $other_education = json_decode($other_education);

            $required = [ /*"address",*/"school_name"];
            $replacer = [
                "address" => "mx_High_School_City_and_State",
                "school_name" => "mx_High_School_Attended"
            ];


            foreach ($required as $field) {
                /*if ($field == 'address' && $field != '') {
                if (substr_count($other_education->$field, ',') != 1) {
                ReturneErrorResponse(["Field 'mx_High_School_City_and_State' is invalid .The address must contain country and state seperated by a comma"], $error_collector);
                }
                } else */
                if (!isset($other_education->$field)) {
                    ReturneErrorResponse(["Field $field " . $replacer[$field] . " is required"], $error_collector);
                } else if ($other_education->$field == '') {
                    ReturneErrorResponse(["Field $field " . $replacer[$field] . " is required "], $error_collector);
                }
            }
            if (isset($other_education->graduation_date)) {
                if ($other_education->graduation_date == '' || $other_education->graduation_date == '00-00-0000') {
                    $other_education->graduation_date = '00-00-0000';
                } else {

                    valid_date($other_education->graduation_date, $error_collector, "Field mx_Year_Graduated is invalid. The date should be correctly formatted ex. mm/dd/yyyy");
                }
            }

            if (isset($other_education->address) && $other_education->address != '') {
                $education_state = GetState(explode(',', $other_education->address)[1], $error_collector);
                if ($education_state === false) {
                    ReturneErrorResponse(["Field 'state' is invalid . Given 'state' cannot be found"], $error_collector);
                }
            }
        }
    }
    if (!empty($error_collector)) {
        $data['SUCCESS'] = 0;
        $data['ERROR'] = $error_collector;
        $data = json_encode($data);
        echo $data;
        exit;
    }
}
function extract_contact($c, $flag = false)
{
    if ($flag)
        return preg_replace("/[^0-9]/", "", $c);
    else
        return substr(preg_replace("/[^0-9]/", "", $c), -10);
}
function CreateStudent($DATA, &$error_collector , $D4_data)
{

    global $db;
    $ignore_error_collector = [];
    //GET PK_
    $STUDENT_MASTER['PK_ACCOUNT'] = $DATA->PK_ACCOUNT;
    $STUDENT_MASTER['LSQ_ID'] = $DATA->ProspectID;
    $STUDENT_MASTER['FIRST_NAME'] = $DATA->FirstName;
    $STUDENT_MASTER['LAST_NAME'] = $DATA->LastName;
    $STUDENT_MASTER['MIDDLE_NAME'] = $DATA->mx_Middle_Name;
    $STUDENT_MASTER['LSQ_PREFFERED_START_DATE'] = $DATA->mx_Preferred_Start_Date;



    $c_info = GetCitizenshipANdCounty([$DATA->mx_Are_you_a_US_citizen, $DATA->mx_Country], $error_collector);
    $STUDENT_MASTER['PK_COUNTRY_CITIZEN'] = $c_info[1];
    $STUDENT_MASTER['PK_CITIZENSHIP'] = $c_info[0];
    // echo "--->";
    // print_r($STUDENT_MASTER['PK_COUNTRY_CITIZEN'], $STUDENT_MASTER['PK_CITIZENSHIP']);
    //todo : Check for date formate

    $DATA->mx_Date_of_Birth = $DATA->mx_Date_of_Birth;
    $STUDENT_MASTER['DATE_OF_BIRTH'] = $DATA->mx_Date_of_Birth;

    // dvb 31 10 2024
    if(isset($DATA->mx_Marital_Status) && !empty($DATA->mx_Marital_Status)){
        $STUDENT_MASTER['PK_MARITAL_STATUS'] = GetMaritalStatus($DATA->mx_Marital_Status, $error_collector);
    }
    // dvb 31 10 2024
    $STUDENT_MASTER['GENDER'] = GetGender($DATA->mx_Gender, $error_collector);


    $S_STUDENT_CONTACT['PK_ACCOUNT'] = $DATA->PK_ACCOUNT;
    #Special condition for pk_account  for NPTI 
    if ($DATA->PK_ACCOUNT == 80) {
        $z_account_setting = $db->Execute("SELECT * FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$DATA->PK_ACCOUNT'  ");
        $USE_SECONDARY_EMAIL_AS_DEFAULT = $z_account_setting->fields['USE_SECONDARY_EMAIL_AS_DEFAULT']; //DIAM-569-GMAIL
        if ($USE_SECONDARY_EMAIL_AS_DEFAULT == 1) {
            $S_STUDENT_CONTACT['USE_SECONDARY_EMAIL_AS_DEFAULT_STD'] = '1';
        }
    }
    $S_STUDENT_CONTACT['EMAIL'] = $DATA->EmailAddress;
    $S_STUDENT_CONTACT['OTHER_PHONE'] = extract_contact($DATA->Mobile);
    $S_STUDENT_CONTACT['HOME_PHONE'] = extract_contact($DATA->mx_Home_Phone_Number);
    $S_STUDENT_CONTACT['CELL_PHONE'] = extract_contact($DATA->Phone);
    $S_STUDENT_CONTACT['OPT_OUT'] = $DATA->DoNotCall;
    $S_STUDENT_CONTACT['USE_EMAIL'] = $DATA->DoNotEmail;
    $S_STUDENT_CONTACT['ADDRESS'] = $DATA->mx_Street1;
    $S_STUDENT_CONTACT['ADDRESS_1'] = $DATA->mx_Street2;
    $S_STUDENT_CONTACT['CITY'] = $DATA->mx_City;
    #Added GMail support
    $S_STUDENT_CONTACT['EMAIL_OTHER'] = $DATA->mx_Student_Gmail_Id;




    $S_STUDENT_CONTACT['PK_STATES'] = GetState($DATA->mx_State, $ignore_error_collector);
    $S_STUDENT_CONTACT['ZIP'] = $DATA->mx_Zip;
    if (isset($DATA->mx_Country))
        $S_STUDENT_CONTACT['PK_COUNTRY'] = GetCountry($DATA->mx_Country, $error_collector);


    foreach ($DATA->other_education as $key => $other_education) {
        $other_education = json_decode($other_education);
        $S_STUDENT_OTHER_EDU['PK_EDUCATION_TYPE'] = GetEducationType($other_education->type, $ignore_error_collector);
        $S_STUDENT_OTHER_EDU['SCHOOL_NAME'] = $other_education->school_name;
        $address = explode(',', $other_education->address);
        $S_STUDENT_OTHER_EDU['CITY'] = $address[0];
        $S_STUDENT_OTHER_EDU['PK_STATE'] = GetState($address[1], $ignore_error_collector);
        if (isset($other_education->graduation_date) && $other_education->graduation_date != '00-00-0000') {
            //todo(done) : add formated date
            //todo(done) : checkdate formate for all dates both while inserting and reciving 
            $ignore = [];
            $other_education->graduation_date = valid_date($other_education->graduation_date, $ignore, "Invalid mx_Year_Graduated. The date should be correctly formatted ex. mm/dd/yyyy");
            $S_STUDENT_OTHER_EDU['GRADUATED_DATE'] = $other_education->graduation_date;
            $S_STUDENT_OTHER_EDU['GRADUATED'] = 1;
        } else {
            $S_STUDENT_OTHER_EDU['GRADUATED'] = 0;
        }
    }

    //PK_STUDENT_STATUS and _STUDENT_STATUS table insertion  //todo
    //S_STUDENT_CAMPUS table insertion //line 1540 //todo

    $S_STUDENT_ENROLLMENT['PK_ACCOUNT'] = $DATA->PK_ACCOUNT;
    $S_STUDENT_ENROLLMENT['IS_ACTIVE_ENROLLMENT'] = 1; // todo // this is default value
    $S_STUDENT_ENROLLMENT['PK_STUDENT_STATUS'] = GetStudentStatus($DATA->PK_ACCOUNT, $error_collector);

    // dvb 31 10 2024
    if (isset($DATA->mx_Program_Code) && !empty($DATA->mx_Program_Code)){
        $S_STUDENT_ENROLLMENT['PK_CAMPUS_PROGRAM'] = GetCampusProgram($DATA->mx_Program_Code, $DATA->PK_ACCOUNT, $error_collector);
    }elseif (isset($DATA->mx_program_code) && !empty($DATA->mx_program_code)){
        $S_STUDENT_ENROLLMENT['PK_CAMPUS_PROGRAM'] = GetCampusProgram($DATA->mx_program_code, $DATA->PK_ACCOUNT, $error_collector);
    }elseif (isset($DATA->mx_Program) && !empty($DATA->mx_Program)){
        $S_STUDENT_ENROLLMENT['PK_CAMPUS_PROGRAM'] = GetCampusProgram($DATA->mx_Program, $DATA->PK_ACCOUNT, $error_collector);
    }
    
    // dvb 31 10 2024

    
    //!!!!!!
    //!!!!!!
    $DATA->PK_CAMPUS = GetCampus($DATA, $error_collector);
    //!!!!!!
    //!!!!!!
    if (isset($DATA->mx_Preferred_Start_Date))
        $S_STUDENT_ENROLLMENT['PK_TERM_MASTER'] = GetTerm($DATA->mx_Preferred_Start_Date, $DATA->PK_ACCOUNT, $error_collector);
    if (isset($DATA->OwnerIdEmailAddress)) {
        $S_STUDENT_ENROLLMENT['PK_REPRESENTATIVE'] = GetAdmissionRep($DATA->OwnerIdEmailAddress, $DATA->PK_ACCOUNT, $error_collector);
    }

    //M_LEAD_SOURCE_GROUP table entry //todo
    $S_STUDENT_ENROLLMENT['PK_LEAD_SOURCE'] = GetorCreateLeadSource($DATA->Source, "", $DATA->PK_ACCOUNT, $error_collector);
    $S_STUDENT_ENROLLMENT['PK_LEAD_CONTACT_SOURCE'] = GetorCreateLeadSourceContact($DATA->Origin, $DATA->PK_ACCOUNT, $error_collector);
    if (!count($error_collector) > 0) {
        //echo "Hi trivial";
        db_perform('S_STUDENT_MASTER', $STUDENT_MASTER, 'insert');
        $DATA->PK_STUDENT_MASTER = $PK_STUDENT_MASTER = $db->insert_ID();
        $S_STUDENT_CONTACT['PK_STUDENT_MASTER'] = $PK_STUDENT_MASTER;
        // $S_STUDENT_CONTACT['PK_STUDENT_CONTACT_TYPE_MASTER'] = 1;

        // dvb 05 11 2024
        if($DATA->PK_ACCOUNT == 63){
            $S_STUDENT_CONTACT['PK_STUDENT_CONTACT_TYPE_MASTER'] = 1;
        }
        // end
        $S_STUDENT_ENROLLMENT['PK_STUDENT_MASTER'] = $PK_STUDENT_MASTER;
        CreateOtherEducation($DATA, $PK_STUDENT_MASTER, $error_collector);
        $S_STUDENT_OTHER_EDU['PK_STUDENT_MASTER'] = $PK_STUDENT_MASTER;
        db_perform('S_STUDENT_CONTACT', $S_STUDENT_CONTACT, 'insert');
        db_perform('S_STUDENT_OTHER_EDU', $S_STUDENT_OTHER_EDU, 'insert'); 
        //NEW CHANGES TO IMPROVE ENROLLMENT DATA  
        $PK_STUDENT_STATUS_MASTER11 = 1;
        $res = $db->Execute("SELECT PK_STUDENT_STATUS FROM M_STUDENT_STATUS WHERE PK_STUDENT_STATUS_MASTER = '$PK_STUDENT_STATUS_MASTER11' AND PK_ACCOUNT = '$DATA->PK_ACCOUNT'");
        $S_STUDENT_ENROLLMENT['ENTRY_DATE']         = date("Y-m-d");
        $S_STUDENT_ENROLLMENT['ENTRY_TIME']         = date("H:i:s", strtotime(date("Y-m-d H:i:s")));
        $S_STUDENT_ENROLLMENT['PK_1098T_REPORTING_TYPE']     = 1; //Ticket # 1046
        $S_STUDENT_ENROLLMENT['IS_ACTIVE_ENROLLMENT']     = 1;
        // $S_STUDENT_ENROLLMENT['PK_STUDENT_STATUS']         = $res->fields['PK_STUDENT_STATUS']; DVB 01 11 2024
        $S_STUDENT_ENROLLMENT['STATUS_DATE']                  = date("Y-m-d");
        $S_STUDENT_ENROLLMENT['CREATED_ON']                   = date("Y-m-d H:i");
        db_perform('S_STUDENT_ENROLLMENT', $S_STUDENT_ENROLLMENT, 'insert');
        $DATA->PK_STUDENT_ENROLLMENT = $PK_STUDENT_ENROLLMENT = $db->insert_ID();
        //ADDING REQUIREMENTS  
        $res_req = $db->Execute("select * from S_SCHOOL_REQUIREMENT WHERE PK_ACCOUNT = '$DATA->PK_ACCOUNT' AND ACTIVE = 1 ");
        while (!$res_req->EOF) {

            $STUDENT_REQUIREMENT['PK_STUDENT_MASTER']         = $PK_STUDENT_MASTER;
            $STUDENT_REQUIREMENT['PK_STUDENT_ENROLLMENT']     = $PK_STUDENT_ENROLLMENT;
            $STUDENT_REQUIREMENT['TYPE']                       = 1;
            $STUDENT_REQUIREMENT['ID']                           = $res_req->fields['PK_SCHOOL_REQUIREMENT'];
            $STUDENT_REQUIREMENT['PK_REQUIREMENT_CATEGORY'] = $res_req->fields['PK_REQUIREMENT_CATEGORY']; //ticket #1059
            $STUDENT_REQUIREMENT['REQUIREMENT']             = $res_req->fields['REQUIREMENT'];
            $STUDENT_REQUIREMENT['MANDATORY']                 = $res_req->fields['MANDATORY'];
            $STUDENT_REQUIREMENT['PK_ACCOUNT']              = $DATA->PK_ACCOUNT; 
            $STUDENT_REQUIREMENT['CREATED_ON']              = date("Y-m-d H:i");
            $res_x1234 = db_perform('S_STUDENT_REQUIREMENT', $STUDENT_REQUIREMENT, 'insert'); 
            $res_req->MoveNext();
        }
        //ENDING REQUIREMENT BLOCK  

        $success_collector = [];

        //CREATE SUPPORTING TRIVIAL DATA 

        create_trivial_entries($DATA);
        try {
            if(!empty($D4_data)){
                $D4_response = sendToExternalD4API(json_encode($D4_data));
            }
        } catch (\Throwable $th) {
            //throw $th;
        }
       
        ReturneSuccessResponse(["PK_STUDENT_MASTER" => "$PK_STUDENT_MASTER"], $success_collector, true);
    } else {
        $data['SUCCESS'] = 0;
        $data['ERROR'] = $error_collector;

        $data = json_encode($data);
        echo $data;
        exit;
    }
}

function create_trivial_entries($DATA)
{

    global $db;
    $CREATED_ON_DATE = date("Y-m-d H:i");
    #ACT0 : Add entry in S_CAMPUS_MASTER
    $STUDENT_CAMPUS['PK_CAMPUS'] = $DATA->PK_CAMPUS;
    $STUDENT_CAMPUS['PK_STUDENT_MASTER'] = $DATA->PK_STUDENT_MASTER;
    $STUDENT_CAMPUS['PK_STUDENT_ENROLLMENT'] = $DATA->PK_STUDENT_ENROLLMENT;
    $STUDENT_CAMPUS['PK_ACCOUNT'] = $DATA->PK_ACCOUNT;
    $STUDENT_CAMPUS['CREATED_ON'] = date("Y-m-d H:i");
    db_perform('S_STUDENT_CAMPUS', $STUDENT_CAMPUS, 'insert');
    //todo : CREATED_ON what should be the ID ?
    #ACt1 : increament Z_ACCOUNT no of students
    $res_acc = $db->Execute("SELECT AUTO_GENERATE_STUD_ID,STUD_CODE,STUD_NO FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$DATA->PK_ACCOUNT' ");
    if ($res_acc->fields['AUTO_GENERATE_STUD_ID'] == 1) {
        $STUDENT_ID = $res_acc->fields['STUD_CODE'] . $res_acc->fields['STUD_NO'];
        $STUD_NO = $res_acc->fields['STUD_NO'] + 1;
        $db->Execute("UPDATE Z_ACCOUNT SET STUD_NO = '$STUD_NO' WHERE PK_ACCOUNT = '$DATA->PK_ACCOUNT' ");
    }

    #Act2: Add data in S_STUDENT_ACADEMICS

    // `PK_STUDENT_ACADEMICS`, `PK_ACCOUNT`, `CONVERSION_ID`, `PK_STUDENT_MASTER`, `HS_CLASS_RANK`, `HS_CGPA`, `POST_SEC_CUM_CGPA`, `PREVIOUS_COLLEGE`, `PK_HIGHEST_LEVEL_OF_EDU`, `FERPA_BLOCK`, `STUDENT_ID`, `PK_SECOND_REPRESENTATIVE`, `ADM_USER_ID`, `ACTIVE`, 

    $STUDENT_ACADEMICS['PK_STUDENT_MASTER'] = $DATA->PK_STUDENT_MASTER;
    $STUDENT_ACADEMICS['PK_ACCOUNT'] = $DATA->PK_ACCOUNT;
    $STUDENT_ACADEMICS['STUDENT_ID'] = $STUDENT_ID;
    $STUDENT_ACADEMICS['CREATED_ON'] = $CREATED_ON_DATE;
    // $STUDENT_ACADEMICS['ADM_USER_ID']				= $ADM_USER_ID;
    // $STUDENT_ACADEMICS['PK_HIGHEST_LEVEL_OF_EDU']	= $PK_HIGHEST_LEVEL_OF_EDU;
    // $STUDENT_ACADEMICS['PREVIOUS_COLLEGE']			= $PREVIOUS_COLLEGE;
    // $STUDENT_ACADEMICS['FERPA_BLOCK']				= $FERPA_BLOCK; 
    //todo // should we add FREPA BLOCK 2 and PREV COLLEGE  = 2 ?
    db_perform('S_STUDENT_ACADEMICS', $STUDENT_ACADEMICS, 'insert');
    //todo //what to fill inside CREATED BY in every table ?
    //todo // what to do about rest of the unfilled data ? 



    #NEW condition added due to mx_preffered_date issue discussed with leadsquared
    //if mx_prefered_date is not found as a first term date , we have to add a note in admission > student > activity > notes section 

    // Array ( [] => 2564 [] => 6 [] => 2023-04-28 [] => 11:43:00 [] => [] => [] => [] => [] => [] => 0 [] => 205 [] => 15 [] => 554340 [] => 578678 [] => 1 [] => 2023-04-28 11:44 )

    //ommiting PK_NOTE_TYPE , PK_NOTE_STATUS ,  ,  , FOLLOWUP_DATE , FOLLOWUP_TIME , NOTES , PK_EMPLOYEE_MASTER , SATISFIED , 
    if (isset($DATA->mx_Preferred_Start_Date) && $DATA->mx_Preferred_Start_Date != null) {
        $mx_psd_flag = GetTerm($DATA->mx_Preferred_Start_Date, $DATA->PK_ACCOUNT, $error_collector);
        if ($mx_psd_flag == null || $mx_psd_flag == '' || $DATA->PK_ACCOUNT==63) {
            $STUDENT_NOTES['PK_ACCOUNT'] = $DATA->PK_ACCOUNT;
            $STUDENT_NOTES['PK_STUDENT_MASTER'] = $DATA->PK_STUDENT_MASTER;
            $STUDENT_NOTES['PK_STUDENT_ENROLLMENT'] = $DATA->PK_STUDENT_ENROLLMENT;
            $STUDENT_NOTES['PREFERRED_START_DATE'] = $DATA->mx_Preferred_Start_Date;
            // $STUDENT_NOTES['PK_NOTE_TYPE'] = $_POST['PK_NOTE_TYPE'];
            $STUDENT_NOTES['NOTES'] = "LeadSquared API - Student's preferred start date is " . $DATA->mx_Preferred_Start_Date;
            $STUDENT_NOTES['NOTE_DATE'] = date('Y-m-d');
            $STUDENT_NOTES['NOTE_TIME'] = date('H:i:s');
            $STUDENT_NOTES['IS_EVENT'] = 0;
            $STUDENT_NOTES['PK_DEPARTMENT'] = -1;
            $STUDENT_NOTES['CREATED_ON'] = date("Y-m-d H:i");
            db_perform('S_STUDENT_NOTES', $STUDENT_NOTES, 'insert');
        }
    }
}
function GetCampus($DATA, &$error_collector)
{
    global $db;

    if (isset($DATA->campus_code)) {
        $res_st = $db->Execute("SELECT * FROM S_CAMPUS WHERE PK_ACCOUNT = '$DATA->PK_ACCOUNT' AND (CAMPUS_NAME = '$DATA->campus_code' OR OFFICIAL_CAMPUS_NAME ='$DATA->campus_code' OR CAMPUS_CODE = '$DATA->campus_code') AND ACTIVE = '1' ORDER BY PRIMARY_CAMPUS DESC ");
        $PK_CAMPUS = $res_st->fields['PK_CAMPUS'];
        if ($res_st->RecordCount() == 0) {
            ReturneErrorResponse(["Given 'mx_Campus' is invalid or inactive."], $error_collector); //todo : should we block the entry if campus is inactive ? and if any ther fields are inactive in diferent entry / queries
            return false;
        } else {
            return $PK_CAMPUS;
        }
    } else {
        $res_st = $db->Execute("SELECT * FROM S_CAMPUS WHERE PK_ACCOUNT = '$DATA->PK_ACCOUNT' AND ACTIVE = '1' ORDER BY  PK_CAMPUS ASC LIMIT 1 ");
        $PK_CAMPUS = $res_st->fields['PK_CAMPUS'];
        if ($res_st->RecordCount() == 0) {
            ReturneErrorResponse(["No active campus found for given account"], $error_collector);
            return false;
        } else {
            return $PK_CAMPUS;
        }
    }
}
function GetStudentStatus($PK_ACCOUNT, &$error_collector)
{

    #act3 : Add data into M_STUDENT_STATUS // for admission = 1 or the student wont show in front end //todo 

    global $db;
    $res_st = $db->Execute("SELECT PK_STUDENT_STATUS, CONCAT(STUDENT_STATUS,' - ',DESCRIPTION) AS NAME FROM M_STUDENT_STATUS WHERE PK_STUDENT_STATUS_MASTER = '1' AND PK_ACCOUNT = '$PK_ACCOUNT' "); //todo //static 1 for "lead" status code
    $PK_STUDENT_STATUS = $res_st->fields['PK_STUDENT_STATUS'];

    // dvb 31 10 2024
    $res_l = $db->Execute("SELECT ASSIGN_PK_STUDENT_STATUS FROM Z_ACCOUNT_LSQ_SETTINGS WHERE PK_ACCOUNT = '$PK_ACCOUNT' "); 
    if($res_l->fields['ASSIGN_PK_STUDENT_STATUS'] > 0){
        $PK_STUDENT_STATUS = $res_l->fields['ASSIGN_PK_STUDENT_STATUS'];
    }
    // if($PK_ACCOUNT == 63){
    //     ReturneErrorResponse(["PK_STUDENT_STATUS ".$PK_STUDENT_STATUS ], $error_collector);
    //     return false;
    // }
    // end 

    if ($res_st->RecordCount() == 0) {
        // ReturneErrorResponse(["Student cannot be entered due to invalid status. Please check with school admin if  an active 'lead' status for admissions"], $error_collector); //todo // check this error text // check if we have to fire this error at all ?
        return null;
    } else {
        return $PK_STUDENT_STATUS;
    }
}

function GetState($value, &$error_collector)
{
    global $db;
    $res_st = $db->Execute("select PK_STATES from Z_STATES WHERE STATE_NAME = '$value' OR STATE_CODE = '$value'");
    $pk_state = $res_st->fields['PK_STATES'];

    if ($res_st->RecordCount() == 0) {
        //ReturneErrorResponse(["invalid mx_State"], $error_collector);
        return false;
    } else {
        return $pk_state;
    }
}

function GetCountry($value, &$error_collector)
{
    global $db;
    $res_st = $db->Execute("select PK_COUNTRY from Z_COUNTRY WHERE NAME = '$value' OR CODE = '$value'");
    $pk_country = $res_st->fields['PK_COUNTRY'];

    if ($res_st->RecordCount() == 0) {
        ReturneErrorResponse(["Given 'mx_Country' is invalid"], $error_collector);
        return false;
    } else {
        return $pk_country;
    }
}

function valid_phone($phone)
{
    $phone = extract_contact($phone, true);

    return preg_match('/^[0-9]{10}+$/', $phone) || preg_match('/^[0-9]{11}+$/', $phone) || preg_match('/^[0-9]{12}+$/', $phone) || preg_match('/^[0-9]{13}+$/', $phone);
}
function valid_date($date, &$error_collector, $errormsg)
{

    $date = trim(substr($date, 0, 10));


    $dt = DateTime::createFromFormat("m/d/Y", $date);
    // print_r(array_sum($dt::getLastErrors()));
    // exit;

    if ($dt !== false && !array_sum($dt::getLastErrors())) {
        return date("Y-m-d", strtotime($date));
    } else {


        $dt = DateTime::createFromFormat("m-d-Y", $date);
        // print_r(array_sum($dt::getLastErrors()));
        // exit;
        if ($dt !== false && !array_sum($dt::getLastErrors())) {
            return date("Y-m-d", strtotime($date));
        } else {
            $dt = DateTime::createFromFormat("Y-m-d", $date);
            // print_r(array_sum($dt::getLastErrors()));
            // exit;
            if ($dt !== false && !array_sum($dt::getLastErrors())) {
                return date("Y-m-d", strtotime($date));
            } else {
                $dt = DateTime::createFromFormat("Y/m/d", $date);
                // print_r(array_sum($dt::getLastErrors()));
                // exit;
                if ($dt !== false && !array_sum($dt::getLastErrors())) {
                    return date("Y-m-d", strtotime($date));
                } else {
                    ReturneErrorResponse([$errormsg], $error_collector);
                    return false;
                }
            }
        }
    }
}

function CheckDuplicateByLsqID($ProspectID, array &$error_collector)
{
    global $db;
    $res = $db->Execute("SELECT * FROM S_STUDENT_MASTER where LSQ_ID = '$ProspectID'");
    if ($res->RecordCount() > 0) {
        ReturneErrorResponse(["Duplicate 'ProspectID' found. Given value is : $ProspectID"], $error_collector, true);
    }
}
function CheckDuplicateByDiamondAlgo($STUDENT, array &$error_collector)
{

    global $db;
    $FIRST_NAME = trim($STUDENT->FirstName);
    $LAST_NAME = trim($STUDENT->LastName);

    $dup_cond = " (TRIM(FIRST_NAME) = '$FIRST_NAME' AND TRIM(LAST_NAME) = '$LAST_NAME') ";
    $home_ph = $STUDENT->mx_Home_Phone_Number;
    $mobile_ph = $STUDENT->Phone;
    $NEW_EMAIL = $STUDENT->EmailAddress;

    if ($LAST_NAME != '' && $home_ph != '') {
        $home_ph = preg_replace('/[^0-9]/', '', trim($home_ph));
        $dup_cond .= " OR (TRIM(LAST_NAME) = '$LAST_NAME' AND (REPLACE(REPLACE(REPLACE(REPLACE(HOME_PHONE, '(', ''), ')', ''), '-', ''),' ','') = '$home_ph') ) ";
    }

    if ($LAST_NAME != '' && $mobile_ph != '') {
        $mobile_ph = preg_replace('/[^0-9]/', '', trim($mobile_ph));
        $dup_cond .= " OR (TRIM(LAST_NAME) = '$LAST_NAME' AND (REPLACE(REPLACE(REPLACE(REPLACE(CELL_PHONE, '(', ''), ')', ''), '-', ''),' ','') = '$mobile_ph' ) ) ";
    }

    if ($LAST_NAME != '' && $NEW_EMAIL != '') {
        $dup_cond .= " OR (TRIM(LAST_NAME) = '$LAST_NAME' AND EMAIL = '$NEW_EMAIL' ) ";
    }

    $dup_cond = " AND ($dup_cond) ";
    $PK_ACCOUNT = $STUDENT->PK_ACCOUNT;
    $dup_checker_sql = "SELECT S_STUDENT_MASTER.PK_STUDENT_MASTER, LAST_NAME, FIRST_NAME,  HOME_PHONE, CELL_PHONE, EMAIL FROM S_STUDENT_MASTER LEFT JOIN S_STUDENT_CONTACT ON S_STUDENT_CONTACT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER WHERE S_STUDENT_MASTER.PK_ACCOUNT = '$PK_ACCOUNT' AND ARCHIVED = 0 $dup_cond ";
    $res = $db->Execute($dup_checker_sql);


    // print_r($dup_checker_sql);
    // exit;

    if ($res->RecordCount() > 0 && $PK_ACCOUNT != 63) { // dvb 27 12 2024 added PK_ACCOUNT != 63
        ReturneErrorResponse(["Duplicate student record found in system"], $error_collector, true);
    }
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
function ReturneSuccessResponse(array $message_array, array &$success_collector, $exit_immidiatly = false)
{

    $success_collector = array_merge($success_collector, $message_array);
    $data['SUCCESS'] = 1;
    $data['MESSAGE'] = $success_collector;
    //Send response
    if ($exit_immidiatly) {
        $data = json_encode($data);
        echo $data;
        exit;
    } else {

        return $message_array;
    }
}
function GetCitizenshipANdCounty(array $value, &$error_collector)
{
    global $db;
    $mx_Are_you_a_US_citizen = $value[0];
    $citizenship = 1; //true by default
    $mx_Country = strtolower($value[1]);
    if ($mx_Are_you_a_US_citizen === true) {
        $citizenship = 1;
        $mx_Country = 1;
        return [$citizenship, $mx_Country];
    } else if ($mx_Are_you_a_US_citizen === false) {

        //check value if value of county name is correct
        $res_st = $db->Execute("select PK_COUNTRY from Z_COUNTRY WHERE LOWER(NAME) = '$mx_Country' ");
        if ($res_st->RecordCount() == 0) {
            ReturneErrorResponse(["Given 'mx_Country' is invalid"], $error_collector);
        } else {
            $citizenship = 0;
            $mx_Country = $res_st->fields['PK_COUNTRY'];
            return [$citizenship, $mx_Country];
        }
    } else {
        ReturneErrorResponse(["Given 'mx_Are_you_a_US_citizen' is invalid. 'mx_Are_you_a_US_citizen' must be of boolean"], $error_collector);
    }
}
function GetMaritalStatus($value, &$error_collector)
{
    if (isset($value)) {
        if (!($value == null || $value == 'null')) {

            $marrage_status_lsq_to_diamond = array(
                'single' => ['Single', 1],
                'married' => ['Married/Remarried', 2],
                'married/remarried'=> ['Married/Remarried', 2],
                'separated' => ['Separated', 5],
                'divorced' => ['Divorced or Widowed', 3],
            );

            if (!in_array(strtolower($value), array_keys($marrage_status_lsq_to_diamond))) {
                ReturneErrorResponse(["Given 'mx_Marital_Status' is invalid"], $error_collector);
            } else {
                $mx_Marital_Status = $marrage_status_lsq_to_diamond[strtolower($value)][1];
            }
        } else {
            $mx_Marital_Status = 7;
        }
    } else {
        $mx_Marital_Status = 7;
    }
    return $mx_Marital_Status;
}
function GetGender($value, &$error_collector)
{
    $genders =
        array(
            'male' => ['Male', 1],
            'female' => ['Female', 2],
            'prefer not to disclose' => ['Other', 3]
        );
    if (isset($value)) {
        if ($value != '' || $value != 'null') {
            if (in_array(strtolower($value), array_keys($genders))) {
                $value = $genders[strtolower($value)][1];
            } else {
                ReturneErrorResponse(["Given 'mx_Gender' is invalid"], $error_collector);
            }
        } else {
            $value = 4; //unknown
        }
    }
    return $value;
}

function CreateOtherEducation($DATA, $PK_STUDENT_MASTER, &$error_collector)
{
    foreach ($DATA->other_education as $key => $other_education) {
        $other_education = json_decode($other_education);
        $S_STUDENT_OTHER_EDU['PK_ACCOUNT'] = $DATA->PK_ACCOUNT;
        $S_STUDENT_OTHER_EDU['PK_STUDENT_MASTER'] = $PK_STUDENT_MASTER;
        $S_STUDENT_OTHER_EDU['PK_EDUCATION_TYPE'] = GetEducationType($other_education->type, $ignore_error_collector);
        $S_STUDENT_OTHER_EDU['SCHOOL_NAME'] = $other_education->school_name;
        $address = explode(',', $other_education->address);
        $S_STUDENT_OTHER_EDU['CITY'] = $address[0];
        $S_STUDENT_OTHER_EDU['PK_STATE'] = GetState($address[1], $ignore_error_collector);
        if (isset($other_education->graduation_date) && $other_education->graduation_date != '00-00-0000') {
            //todo : add formated date
            //todo : checkdate formate for all dates both while inserting and reciving 
            $S_STUDENT_OTHER_EDU['GRADUATED_DATE'] = $other_education->graduation_date;
            $S_STUDENT_OTHER_EDU['GRADUATED'] = 1;
        } else {
            $S_STUDENT_OTHER_EDU['GRADUATED'] = 0;
        }
        db_perform('S_STUDENT_OTHER_EDU', $S_STUDENT_OTHER_EDU, 'insert');
    }
}
function GetEducationType($value, &$error_collector)
{
    global $db;
    $res_st = $db->Execute("select PK_EDUCATION_TYPE from M_EDUCATION_TYPE WHERE EDUCATION_TYPE = '$value' ");
    if ($res_st->RecordCount() == 0) {
        return false;
    } else {
        $education_type = $res_st->fields['PK_EDUCATION_TYPE'];
        return $education_type;
    }
}

function GetCampusProgram($code, $PK_ACCOUNT, &$error_collector)
{
    global $db;
    $res_st = $db->Execute("select PK_CAMPUS_PROGRAM from M_CAMPUS_PROGRAM WHERE (CODE = '$code' || LOWER(DESCRIPTION) = LOWER('$code')) AND PK_ACCOUNT = '$PK_ACCOUNT' ");
    $PK_CAMPUS_PROGRAM = $res_st->fields['PK_CAMPUS_PROGRAM'];
    if ($res_st->RecordCount() == 0) {
        ReturneErrorResponse(["Given 'mx_Program_Code' cannot be found ".$code], $error_collector);
        return null;
    } else {
        return $PK_CAMPUS_PROGRAM;
    }
}

function GetTerm($term_description, $PK_ACCOUNT, &$error_collector)
{
    global $db;
    $res_st = $db->Execute("select PK_TERM_MASTER from S_TERM_MASTER WHERE TERM_DESCRIPTION = '$term_description' AND PK_ACCOUNT = '$PK_ACCOUNT' ");


    // ReturneErrorResponse(["select PK_TERM_MASTER from S_TERM_MASTER WHERE TERM_DESCRIPTION = '$term_description' AND PK_ACCOUNT = '$PK_ACCOUNT' "], $error_collector , true);
    $PK_TERM_MASTER = $res_st->fields['PK_TERM_MASTER'];
    if ($res_st->RecordCount() == 0) {
        // ReturneErrorResponse(["Given 'mx_Preferred_Start_Date' cannot be found"], $error_collector);
        return null;
    } else {
        return $PK_TERM_MASTER;
    }
}

function GetAdmissionRep($PK_REPRESENTATIVE, $PK_ACCOUNT, &$error_collector)
{
    global $db;
    $res_st = $db->Execute("select PK_EMPLOYEE_MASTER from S_EMPLOYEE_MASTER WHERE EMAIL = '$PK_REPRESENTATIVE' AND PK_ACCOUNT = '$PK_ACCOUNT' ");
    //todo : CHECK active flag for all inputs ????
    $PK_REPRESENTATIVE = $res_st->fields['PK_EMPLOYEE_MASTER'];
    if ($res_st->RecordCount() == 0) {
        //ReturneErrorResponse(["Given 'OwnerIdEmailAddress' cannot be found"], $error_collector);
        return '--';
    } else {
        return $PK_REPRESENTATIVE;
    }
}

function GetorCreateLeadSource($source_name, $source_description, $PK_ACCOUNT, &$error_collector)
{
    global $db;
    $res_st = $db->Execute("select PK_LEAD_SOURCE from M_LEAD_SOURCE WHERE PK_ACCOUNT = '$PK_ACCOUNT' AND LEAD_SOURCE = '$source_name' ");
    if ($res_st->RecordCount() == 0) {
        //create lead source 
        $LEAD_SOURCE_GROUP = 'LeadSqaured';
        if ($LEAD_SOURCE_GROUP != '') {
            $res_1 = $db->Execute("select PK_LEAD_SOURCE_GROUP from M_LEAD_SOURCE_GROUP WHERE PK_ACCOUNT = '$PK_ACCOUNT' AND LEAD_SOURCE_GROUP = '$LEAD_SOURCE_GROUP' ");
            if ($res_1->RecordCount() > 0)
                $PK_LEAD_SOURCE_GROUP = $res_1->fields['PK_LEAD_SOURCE_GROUP'];
            else {
                $LEAD_SOURCE_GROUP_ARR['LEAD_SOURCE_GROUP'] = $LEAD_SOURCE_GROUP;
                $LEAD_SOURCE_GROUP_ARR['PK_ACCOUNT'] = $PK_ACCOUNT;
                $LEAD_SOURCE_GROUP_ARR['CREATED_ON'] = date("Y-m-d H:i");
                db_perform('M_LEAD_SOURCE_GROUP', $LEAD_SOURCE_GROUP_ARR, 'insert');

                $PK_LEAD_SOURCE_GROUP = $db->insert_ID();
            }
        }

        $LEAD_SOURCE['LEAD_SOURCE'] = trim($source_name);
        $LEAD_SOURCE['DESCRIPTION'] = trim($source_description);
        $LEAD_SOURCE['PK_LEAD_SOURCE_GROUP'] = $PK_LEAD_SOURCE_GROUP;
        $LEAD_SOURCE['PK_ACCOUNT'] = $PK_ACCOUNT;
        $LEAD_SOURCE['CREATED_ON'] = date("Y-m-d H:i");
        db_perform('M_LEAD_SOURCE', $LEAD_SOURCE, 'insert');
        return $PK_LEAD_SOURCE = $db->insert_ID();
    } else {
        return $PK_LEAD_SOURCE = $res_st->fields['PK_LEAD_SOURCE'];
    }
}

function GetorCreateLeadSourceContact($contact, $PK_ACCOUNT, &$error_collector)
{
    global $db;
    $res_st = $db->Execute("select PK_LEAD_CONTACT_SOURCE from M_LEAD_CONTACT_SOURCE WHERE PK_ACCOUNT = '$PK_ACCOUNT' AND LEAD_CONTACT_SOURCE = '$contact' ");
    $PK_LEAD_CONTACT_SOURCE = $res_st->fields['PK_LEAD_CONTACT_SOURCE'];

    if ($res_st->RecordCount() == 0) {
        //todo // add M_LEAD_CONTACT_SOURCE_MASTER table entry or make a leadsquared static choice
        //take deafult 6 for now until above is done
        $LEAD_CONTACT_SOURCE['LEAD_CONTACT_SOURCE'] = trim($contact);
        $LEAD_CONTACT_SOURCE['DESCRIPTION'] = trim("Leadsqaured");
        $LEAD_CONTACT_SOURCE['PK_ACCOUNT'] = $PK_ACCOUNT;
        $LEAD_CONTACT_SOURCE['CREATED_ON'] = date("Y-m-d H:i");
        db_perform('M_LEAD_CONTACT_SOURCE', $LEAD_CONTACT_SOURCE, 'insert');
        $PK_LEAD_CONTACT_SOURCE = $db->insert_ID();
    }

    return $PK_LEAD_CONTACT_SOURCE;
}
