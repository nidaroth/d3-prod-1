<?php
include_once("../../global/config.php");
include_once("../../global/Models/StudentEnrollment.php");
include_once("../../global/Models/StudentMaster.php");
include_once("../../global/Models/StudentContact.php");
include_once("../../global/Models/Z_State.php");
include_once("../../global/Models/Z_Country.php");
include_once("../../global/Models/Gender.php");
include_once("../../global/Models/StudentAcademics.php");
include_once("../../global/Models/StudentCampus.php");
include_once("../../global/Models/Campus.php");
include_once("../../global/Models/SCUSTOMFIELDS.php");
include_once("../../global/Models/SSTUDENTCUSTOMFIELD.php");
include_once("../../global/Models/S_USER_DEFINED_FIELDS.php");
include_once("../../global/Models/S_USER_DEFINED_FIELDS_DETAIL.php");
include_once("../../global/Models/StudentNote.php");
include_once("../../global/Models/Z_RACE.php");


header('Content-Type: application/json; charset=utf-8');

use Illuminate\Database\Capsule\Manager as CapsuleDB;

ENABLE_DEBUGGING(true);
error_reporting(E_ERROR);


// Initialize input parameters

$Field3 = null; // Last name--
$Field4 = null; // First name--
$Field5 = null; // Middle initial--

$Field6 = null; // Email--
$Field7 = null; // Home Phone--
$Field8 = null; // Mobile Phone--
$Field9 = null; // Address Line 1--
$Field10 = null; // Address Line 2--
$Field11 = null; // City--
$Field12 = null; // State--
$Field13 = null; // Zip--
$Field14 = null; // Country (Address)--

$Field15 = null; // Notes/Comments -- 

$Field16 = null; // Lead Status --
$Field17 = null; // Lead Source --
$Field18 = null; // Ad Rep (Last name, First name) -- 
$Field19 = null; // Program --
$Field20 = null; // First Term Date/Start Date --
$Field21 = null; // Funding -- 
$Field22 = null; // HLE
$Field23 = null; // Gender --
$Field24 = null; // Date of Birth -- 
$Field25 = null; // Session --
$Field26 = null; // Full/Part Time Status --
$Field27 = null; // Ethnicity --
$Field28 = null; // Marital Status --
$Field29 = null; // SSN --
$Field30 = null; // Previous College (Y/N) --
$Field31 = null; // Campus --
$Field32 = null; // U.S. Citizen --
$Field33 = null;
$Field34 = null;
$Field35 = null; // HS Grad Date (Year or Date)
$Field36 = null;
$Field37 = null;
$Field38 = null;
$Field39 = null;
$Field40 = null; // Contact Source --
$Field41 = null; // Lead Custom List
$Field42 = null; // Ad Rep 2 (Last name, first name)
$Field43 = null;
$Field44 = null; // Student Custom list 1 
$Field45 = null; // Student User Defined 1 
$Field46 = null; // Student User Defined 2 
$Field47 = null; // Student User Defined 3 
$Field48 = null; // Student User Defined 4 
$Field49 = null; // Student User Defined 5 
$Field50 = null; // Student User Defined 6  

$PK_ACCOUNT  = $_REQUEST['PK_ACCOUNT'];
$Field3  = $_REQUEST['Field3'];
$Field4  = $_REQUEST['Field4'];
$Field5  = $_REQUEST['Field5'];
$Field6  = $_REQUEST['Field6'];
$Field7  = $_REQUEST['Field7'];
$Field8  = $_REQUEST['Field8'];
$Field9  = $_REQUEST['Field9'];
$Field10 = $_REQUEST['Field10'];
$Field11 = $_REQUEST['Field11'];
$Field12 = $_REQUEST['Field12'];
$Field13 = $_REQUEST['Field13'];
$Field14 = $_REQUEST['Field14'];
$Field15 = $_REQUEST['Field15'];
$Field16 = $_REQUEST['Field16'];
$Field17 = $_REQUEST['Field17'];
$Field18 = $_REQUEST['Field18'];
$Field19 = $_REQUEST['Field19'];
$Field20 = $_REQUEST['Field20'];
$Field21 = $_REQUEST['Field21'];
$Field22 = $_REQUEST['Field22'];
$Field23 = $_REQUEST['Field23'];
$Field24 = parsedate($_REQUEST['Field24']);
$Field25 = $_REQUEST['Field25'];
$Field26 = $_REQUEST['Field26'];
$Field27 = $_REQUEST['Field27'];
$Field28 = $_REQUEST['Field28'];
$Field29 = $_REQUEST['Field29'];
$Field30 = $_REQUEST['Field30'];
$Field31 = $_REQUEST['Field31'];
$Field32 = $_REQUEST['Field32'];
$Field33 = $_REQUEST['Field33'];
$Field34 = $_REQUEST['Field34'];
$Field35 = $_REQUEST['Field35'];
$Field36 = $_REQUEST['Field36'];
$Field37 = $_REQUEST['Field37'];
$Field38 = $_REQUEST['Field38'];
$Field39 = $_REQUEST['Field39'];
$Field40 = $_REQUEST['Field40'];
$Field41 = $_REQUEST['Field41'];
$Field42 = $_REQUEST['Field42'];
$Field43 = $_REQUEST['Field43'];
$Field44 = $_REQUEST['Field44'];
$Field45 = $_REQUEST['Field45'];
$Field46 = $_REQUEST['Field46'];
$Field47 = $_REQUEST['Field47'];
$Field48 = $_REQUEST['Field48'];
$Field49 = $_REQUEST['Field49'];
$Field50 = $_REQUEST['Field50'];

$CREATED_ON_DATE = date("Y-m-d H:i");

$required = ["Field3", "Field4", "Field6"];
$error_collector = [];
foreach ($required as $field) {

    if (!isset($_REQUEST[$field])) {
        $error_collector[] = " $field is required";
    } else if ($_REQUEST[$field] == '') {
        $error_collector[] = " $field is required, Given empty";
    }
}
if (!filter_var($_REQUEST["Field6"], FILTER_VALIDATE_EMAIL)) {
    $error_collector[] = "Given 'Email' is invalid (Field6)";
}
if (!empty($error_collector)) {
    echo json_encode($error_collector);
    exit;
}

$new_student = new StudentMaster();
$new_student->PK_ACCOUNT = $PK_ACCOUNT;
$new_student->LAST_NAME = $Field3;
$new_student->FIRST_NAME = $Field4;
$new_student->MIDDLE_NAME = $Field5;
$new_student->GENDER = getGenderKey($Field23) ?? '';
$new_student->DATE_OF_BIRTH = $Field24 ?? '00-00-0000';
$new_student->PK_MARITAL_STATUS =  CapsuleDB::select("select PK_MARITAL_STATUS from Z_MARITAL_STATUS WHERE MARITAL_STATUS = '$Field28'")[0]->PK_MARITAL_STATUS ?? '';
$new_student->PK_CITIZENSHIP =  CapsuleDB::select("SELECT * FROM Z_CITIZENSHIP WHERE ACTIVE = 1 AND CITIZENSHIP = '$Field32'")[0]->PK_CITIZENSHIP ?? '';
$new_student->PK_COUNTRY_CITIZEN = Z_Country::where('CODE', $Field14)->orWhere('NAME', $Field14)->value('PK_COUNTRY') ?? '';


//PK RACE / ETHNICITY PART 1
if ($Field27 != '') { 
    $PK_RACE_ARR = explode(',', $_REQUEST['Field27']);
    if (!empty($PK_RACE_ARR)) {
        $IPEDS_ETHNICITY = '';
       
        foreach ($PK_RACE_ARR as $PK_RACE_1) {
            if ($PK_RACE_1 == 1) {
                $IPEDS_ETHNICITY = 'Hispanic/Latino';
                break;
            }
        }
        if ($IPEDS_ETHNICITY == '') {
            if (count($PK_RACE_ARR) > 1) {
                $IPEDS_ETHNICITY = 'Two or more races';
            } else { 
                $res_l = $db->Execute("SELECT RACE FROM Z_RACE WHERE RACE = '$PK_RACE_ARR[0]'");
                $IPEDS_ETHNICITY = $res_l->fields['RACE'];
            }
        }
    }

$new_student->IPEDS_ETHNICITY = $IPEDS_ETHNICITY ?? '';
}





if (isset($_REQUEST['Field29']) && $Field29 != '') {
    $SSN1 = preg_replace('/[^0-9]/', '', $Field29);
    $SSN1 = $SSN1[0] . $SSN1[1] . $SSN1[2] . '-' . $SSN1[3] . $SSN1[4] . '-' . $SSN1[5] . $SSN1[6] . $SSN1[7] . $SSN1[8];
    $SSN1 = my_encrypt($PK_ACCOUNT . $PK_EMPLOYEE_MASTER, $SSN1);
    $new_student->SSN = $SSN1;
}


$new_student->save();
$PK_STUDENT_MASTER = $new_student->PK_STUDENT_MASTER;
#enrollment 
$StudentEnrollment = new StudentEnrollment();

$StudentEnrollment->PK_FUNDING = CapsuleDB::select("SELECT PK_FUNDING  from M_FUNDING WHERE PK_ACCOUNT = '$PK_ACCOUNT' AND (FUNDING = '$Field21' OR DESCRIPTION = '$Field21') ")[0]->PK_FUNDING ?? '';
$StudentEnrollment->PK_ACCOUNT = $PK_ACCOUNT;
$StudentEnrollment->PK_STUDENT_MASTER = $PK_STUDENT_MASTER;
$StudentEnrollment->PK_CAMPUS_PROGRAM = CapsuleDB::select("select PK_CAMPUS_PROGRAM from M_CAMPUS_PROGRAM WHERE CODE = '$Field19' AND PK_ACCOUNT = '$PK_ACCOUNT' ")[0]->PK_CAMPUS_PROGRAM ?? '';

$StudentEnrollment->PK_SESSION = CapsuleDB::select("SELECT PK_SESSION from M_SESSION WHERE ( SESSION_ABBREVIATION = '$Field25' OR SESSION = '$Field25') AND PK_ACCOUNT = '$PK_ACCOUNT' ")[0]->PK_SESSION ?? '';
$StudentEnrollment->PK_ENROLLMENT_STATUS = CapsuleDB::select("SELECT PK_ENROLLMENT_STATUS from M_ENROLLMENT_STATUS WHERE (DESCRIPTION = '$Field26' OR CODE = '$Field26') ")[0]->PK_ENROLLMENT_STATUS ?? '';



#FIrst term
$Field20_date = parsedate($Field20);
if ($Field20_date != '' && $Field20_date != '0000-00-00') {
    $cond_pk_term = "OR BEGIN_DATE='$Field20_date'";
}
$StudentEnrollment->PK_TERM_MASTER = CapsuleDB::select("select PK_TERM_MASTER from S_TERM_MASTER WHERE (TERM_DESCRIPTION = '$Field20' $cond_pk_term ) AND PK_ACCOUNT = '$PK_ACCOUNT' ")[0]->PK_TERM_MASTER ?? '';
$Field18 = strtolower(trim($Field18)) ?? '';
$StudentEnrollment->PK_REPRESENTATIVE = CapsuleDB::select("SELECT PK_EMPLOYEE_MASTER from S_EMPLOYEE_MASTER WHERE ( LOWER(CONCAT(LAST_NAME, ' ', FIRST_NAME)) = LOWER('$Field18') OR LOWER(CONCAT(LAST_NAME, ', ', FIRST_NAME)) = LOWER('$Field18'))  AND PK_ACCOUNT = '$PK_ACCOUNT' ")[0]->PK_EMPLOYEE_MASTER ?? '';
$StudentEnrollment->PK_LEAD_SOURCE = CapsuleDB::select("SELECT PK_LEAD_SOURCE from M_LEAD_SOURCE WHERE PK_ACCOUNT = '$PK_ACCOUNT' AND (LEAD_SOURCE = '$Field17' OR DESCRIPTION ='$Field17') ")[0]->PK_LEAD_SOURCE ?? '';
$StudentEnrollment->PK_LEAD_CONTACT_SOURCE = CapsuleDB::select("SELECT PK_LEAD_CONTACT_SOURCE from M_LEAD_CONTACT_SOURCE WHERE PK_ACCOUNT = '$PK_ACCOUNT' AND ( LEAD_CONTACT_SOURCE = '$Field40' OR DESCRIPTION ='$Field40' )")[0]->PK_LEAD_CONTACT_SOURCE ?? '';

// $StudentEnrollment->PK_1098T_REPORTING_TYPE =  ;
$StudentEnrollment->IS_ACTIVE_ENROLLMENT =  1;
$StudentEnrollment->ENTRY_DATE = date("Y-m-d");
$StudentEnrollment->ENTRY_TIME = date("H:i:s", strtotime(date("Y-m-d H:i:s")));
$StudentEnrollment->PK_STUDENT_STATUS = CapsuleDB::select("SELECT PK_STUDENT_STATUS, CONCAT(STUDENT_STATUS,' - ',DESCRIPTION) AS NAME FROM M_STUDENT_STATUS WHERE STUDENT_STATUS = '$Field16' AND PK_ACCOUNT = '$PK_ACCOUNT'")[0]->PK_STUDENT_STATUS ?? CapsuleDB::select("SELECT PK_STUDENT_STATUS, CONCAT(STUDENT_STATUS,' - ',DESCRIPTION) AS NAME FROM M_STUDENT_STATUS WHERE PK_STUDENT_STATUS_MASTER = '1' AND PK_ACCOUNT = '$PK_ACCOUNT' ")[0]->PK_STUDENT_STATUS;
$StudentEnrollment->STATUS_DATE = date("Y-m-d");
$StudentEnrollment->CREATED_ON =  date("Y-m-d H:i");




// $STUDENT_ACADEMICS['ADM_USER_ID']				= $ADM_USER_ID;
// $STUDENT_ACADEMICS['']	= $PK_HIGHEST_LEVEL_OF_EDU;
// $STUDENT_ACADEMICS['PREVIOUS_COLLEGE']			= $PREVIOUS_COLLEGE;
// $STUDENT_ACADEMICS['FERPA_BLOCK']				= $FERPA_BLOCK; 


//!!!!!!
//!!!!!!

//!!!!!!
//!!!!!!






$StudentEnrollment->save();
$PK_STUDENT_ENROLLMENT = $StudentEnrollment->PK_STUDENT_ENROLLMENT;
#create student notes 
if ($Field15 != '') {
    $student_note = new StudentNote();
    $student_note->PK_ACCOUNT = $PK_ACCOUNT;
    $student_note->NOTES = $Field15;
    $student_note->NOTE_DATE = date('Y-m-d');
    $student_note->NOTE_TIME = date('H:i:s');
    $student_note->IS_EVENT =  0;
    $student_note->PK_DEPARTMENT = -1;
    $student_note->CREATED_ON = $CREATED_ON_DATE;
    $student_note->PK_STUDENT_MASTER = $PK_STUDENT_MASTER;
    $student_note->PK_STUDENT_ENROLLMENT = $PK_STUDENT_ENROLLMENT;
    $student_note->save();
}

$student_contact = new StudentContact();
$student_contact->PK_ACCOUNT = $PK_ACCOUNT;
$student_contact->EMAIL = $Field6;
$student_contact->HOME_PHONE = $Field7;
$student_contact->CELL_PHONE = $Field8;
$student_contact->ADDRESS = $Field9;
$student_contact->ADDRESS_1 = $Field10;
$student_contact->CITY = $Field11;
$student_contact->PK_STATES = Z_State::where('STATE_NAME', $Field12)->orWhere('STATE_CODE', $Field12)->value('PK_STATES') ?? '';
$student_contact->ZIP = $Field13;
$student_contact->PK_COUNTRY = Z_Country::where('CODE', $Field14)->orWhere('NAME', $Field14)->value('PK_COUNTRY') ?? '';
$student_contact->PK_STUDENT_MASTER = $PK_STUDENT_MASTER;
$student_contact->save();


$studentCampus = new StudentCampus();
$studentCampus->PK_CAMPUS = Campus::where('OFFICIAL_CAMPUS_NAME', $Field31)->orWhere('CAMPUS_NAME', $Field31)->orWhere('CAMPUS_CODE', $Field31)->value('PK_CAMPUS') ?? '';

$studentCampus->PK_STUDENT_MASTER = $PK_STUDENT_MASTER;
$studentCampus->PK_STUDENT_ENROLLMENT = $PK_STUDENT_ENROLLMENT;
$studentCampus->PK_ACCOUNT = $PK_ACCOUNT;
$studentCampus->CREATED_ON = $CREATED_ON_DATE;
$studentCampus->save();


$STUDENT_ACADEMICS = new StudentAcademics();
$STUDENT_ACADEMICS->PK_HIGHEST_LEVEL_OF_EDU = CapsuleDB::select("SELECT * FROM M_HIGHEST_LEVEL_OF_EDU WHERE HIGHEST_LEVEL_OF_EDU = '$Field22'")[0]->PK_HIGHEST_LEVEL_OF_EDU ?? '';

$STUDENT_ACADEMICS->PK_STUDENT_MASTER = $PK_STUDENT_MASTER;
$STUDENT_ACADEMICS->PK_ACCOUNT = $PK_ACCOUNT;
if (isset($_REQUEST['Field30'])  && $Field30 != '') {
    if (strtolower($Field30) == 'yes') {
        $STUDENT_ACADEMICS->PREVIOUS_COLLEGE = 1;
    } elseif (strtolower($Field30) == 'no') {
        $STUDENT_ACADEMICS->PREVIOUS_COLLEGE = 2;
    } else {
        $STUDENT_ACADEMICS->PREVIOUS_COLLEGE = 0;
    }
} else {
    $STUDENT_ACADEMICS->PREVIOUS_COLLEGE = 0;
}

$res_acc = $db->Execute("SELECT AUTO_GENERATE_STUD_ID,STUD_CODE,STUD_NO FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$PK_ACCOUNT' ");
if ($res_acc->fields['AUTO_GENERATE_STUD_ID'] == 1) {
    $STUDENT_ID = $res_acc->fields['STUD_CODE'] . $res_acc->fields['STUD_NO'];
    $STUD_NO = $res_acc->fields['STUD_NO'] + 1;
    $db->Execute("UPDATE Z_ACCOUNT SET STUD_NO = '$STUD_NO' WHERE PK_ACCOUNT = '$PK_ACCOUNT' ");
}


$STUDENT_ACADEMICS->STUDENT_ID = $STUDENT_ID ?? '';
$STUDENT_ACADEMICS->CREATED_ON = $CREATED_ON_DATE;
$STUDENT_ACADEMICS->save();

//PK RACE / ETHNICITY PART 2 
if ($_REQUEST['Field27'] != '') {
    $PK_RACE_ARR = explode(',', $_REQUEST['Field27']);
    if (!empty($PK_RACE_ARR)) {
        foreach ($PK_RACE_ARR as $RACE) {
            $STUDENT_RACE['PK_RACE']           = Z_RACE::where('RACE' ,$RACE )->first()->PK_RACE ?? '';
            $STUDENT_RACE['PK_STUDENT_MASTER'] = $PK_STUDENT_MASTER;
            $STUDENT_RACE['PK_ACCOUNT']        = $PK_ACCOUNT;
            $STUDENT_RACE['CREATED_ON']        = $CREATED_ON_DATE;
            db_perform('S_STUDENT_RACE', $STUDENT_RACE, 'insert');
        }
    }
}

#Custom Fields 


/*
@Field44 nvarchar(255) = NULL, --Student Custom list 1

@Field45 nvarchar(255) = NULL, --Student User Defined 1

@Field46 nvarchar(255) = NULL, --Student User Defined 2

@Field47 nvarchar(255) = NULL, --Student User Defined 3

@Field48 nvarchar(255) = NULL, --Student User Defined 4

@Field49 nvarchar(255) = NULL, --Student User Defined 5

@Field50 nvarchar(255) = NULL, --Student User Defined 6
*/

$filed_names_for_arlignton = [
    "Student Custom list 1" => "Field44",
    "Student User Defined 1" => "Field45",
    "Student User Defined 2"  => "Field46",
    "Student User Defined 3" => "Field47",
    "Student User Defined 4" => "Field48",
    "Student User Defined 5" => "Field49",
    "Student User Defined 6" => "Field50"
];
foreach ($filed_names_for_arlignton as $field_name => $field_index) {
    $field_value = $$field_index;
    if (isset($field_value) && $field_value != '') {
        store_custom_fields($field_name, $field_value, $PK_ACCOUNT, $PK_STUDENT_MASTER, $PK_STUDENT_ENROLLMENT, $CREATED_ON_DATE);
    }
}

//

///////





echo "finished";
exit;
function getGenderKey($gender)
{
    $gender = Gender::where('GENDER', $gender)->get();
    if (!$gender) {
        return '';
    }

    return $gender[0]->PK_GENDER;
}

function parsedate($date)
{

    // Try to parse the date in "Y-m-d" format
    $date = DateTime::createFromFormat('Y-m-d', $date);
    if ($date !== false) {
        return $date->format('Y-m-d'); // Already in "Y-m-d" format, return as is
    }
    // Try to parse the date in "m/d/Y" format
    $date = DateTime::createFromFormat('m/d/Y', $date);
    if ($date !== false) {
        return $date->format('Y-m-d');
    }
    return '0000-00-00'; // Invalid date format

}


// function store_custom_fields($field_name, $field_value, $PK_ACCOUNT, $PK_STUDENT_MASTER,$PK_STUDENT_ENROLLMENT, $CREATED_ON_DATE)
// {

//     $customField = SCUSTOMFIELDS::where('FIELD_NAME', "$field_name")->first();

//     if ($customField) {
//         // Custom field exists, create a new entry in SSTUDENTCUSTOMFIELD
//         $studentCustomField = new SSTUDENTCUSTOMFIELD();
//         $studentCustomField->PK_ACCOUNT = $PK_ACCOUNT; // Set your account value
//         $studentCustomField->PK_STUDENT_MASTER = $PK_STUDENT_MASTER; // Set your student master value
//         $studentCustomField->PK_CUSTOM_FIELDS = $customField->PK_CUSTOM_FIELDS;
//         $studentCustomField->FIELD_VALUE = $field_value; // Set your field value
//         $studentCustomField->CREATED_ON = $CREATED_ON_DATE;
//         $studentCustomField->save();
//     }


//     //NEW LOGIC 

// foreach ($_POST['PK_CUSTOM_FIELDS'] as $PK_CUSTOM_FIELDS) {
//     $CUSTOM_FIELDS = [];

//     if ($_POST['PK_DATA_TYPES'][$i] == 1 || $_POST['PK_DATA_TYPES'][$i] == 2) {
//         $CUSTOM_FIELDS['FIELD_VALUE'] = $_POST['CUSTOM_FIELDS_' . $PK_CUSTOM_FIELDS];
//     } elseif ($_POST['PK_DATA_TYPES'][$i] == 3) {
//         $CUSTOM_FIELDS['FIELD_VALUE'] = implode(",", $_POST['CUSTOM_FIELDS_' . $PK_CUSTOM_FIELDS]);
//     } elseif ($_POST['PK_DATA_TYPES'][$i] == 4) {
//         if ($_POST['CUSTOM_FIELDS_' . $PK_CUSTOM_FIELDS] != '') {
//             $CUSTOM_FIELDS['FIELD_VALUE'] = date("Y-m-d", strtotime($_POST['CUSTOM_FIELDS_' . $PK_CUSTOM_FIELDS]));
//         } else {
//             $CUSTOM_FIELDS['FIELD_VALUE'] = '';
//         }
//     }

//     // Custom fields changes
//     $res_cust_12 = SCUSTOMFIELDS::where('PK_ACCOUNT', $_SESSION['PK_ACCOUNT'])
//         ->where('SECTION', 1)
//         ->where('NAME', $PK_CUSTOM_FIELDS)
//         ->first();

//     $cust_en_cond = '';
//     if (strtolower($res_cust_12->TAB) == 'other') {
//         $cust_en_cond = " AND PK_STUDENT_ENROLLMENT = '$_GET[eid]' ";
//     }

//     $res_1 = SSTUDENTCUSTOMFIELD::where('PK_STUDENT_MASTER', $PK_STUDENT_MASTER)
//         ->where('PK_ACCOUNT', $_SESSION['PK_ACCOUNT'])
//         ->where('PK_CUSTOM_FIELDS', $PK_CUSTOM_FIELDS)
//         ->whereRaw($cust_en_cond)
//         ->first();

//     if (!$res_1) {
//         $CUSTOM_FIELDS['PK_ACCOUNT'] = $_SESSION['PK_ACCOUNT'];
//         $CUSTOM_FIELDS['PK_STUDENT_MASTER'] = $PK_STUDENT_MASTER;
//         $CUSTOM_FIELDS['PK_CUSTOM_FIELDS'] = $PK_CUSTOM_FIELDS;
//         $CUSTOM_FIELDS['FIELD_NAME'] = $_POST['FIELD_NAME'][$i];
//         $CUSTOM_FIELDS['CREATED_BY'] = $_SESSION['PK_USER'];
//         $CUSTOM_FIELDS['CREATED_ON'] = now();

//         if (strtolower($res_cust_12->TAB) == 'other') {
//             $CUSTOM_FIELDS['PK_STUDENT_ENROLLMENT'] = $_GET['eid'];
//         }

//         SSTUDENTCUSTOMFIELD::create($CUSTOM_FIELDS);

//         $OLD_VALUE = '';
//     } else {
//         $OLD_VALUE = $res_1->FIELD_VALUE;
//         $CUSTOM_FIELDS['EDITED_BY'] = $_SESSION['PK_USER'];
//         $CUSTOM_FIELDS['EDITED_ON'] = now();
//         $res_1->update($CUSTOM_FIELDS);
//     }
// } 


// }


function store_custom_fields($field_name, $field_value, $PK_ACCOUNT, $PK_STUDENT_MASTER, $PK_STUDENT_ENROLLMENT, $CREATED_ON_DATE)
{


    //NEW LOGIC 
    $CUSTOM_FIELDS = [];

    // Custom fields changes
    $res_cust_12 = SCUSTOMFIELDS::where('PK_ACCOUNT', $PK_ACCOUNT)
        ->where('SECTION', 1)
        ->where('FIELD_NAME', $field_name)
        ->first();


    if ($res_cust_12) {

        $PK_CUSTOM_FIELDS = $res_cust_12->PK_CUSTOM_FIELDS;
        if ($res_cust_12->PK_DATA_TYPES == 1) {
            $CUSTOM_FIELDS['FIELD_VALUE'] = $field_value;
        }
        if ($res_cust_12->PK_DATA_TYPES == 2) {
            $PK_VALUES = S_USER_DEFINED_FIELDS_DETAIL::where('PK_USER_DEFINED_FIELDS', $res_cust_12->PK_USER_DEFINED_FIELDS)->whereIn('OPTION_NAME', explode(',', $field_value))->where('PK_ACCOUNT', $PK_ACCOUNT)->where('ACTIVE', 1)->groupBy('PK_USER_DEFINED_FIELDS')->selectRaw('GROUP_CONCAT(PK_USER_DEFINED_FIELDS_DETAIL) as PK_USER_DEFINED_FIELDS_DETAILS')->get();
            $CUSTOM_FIELDS['FIELD_VALUE'] = $PK_VALUES[0]->PK_USER_DEFINED_FIELDS_DETAILS ?? '';
        } elseif ($res_cust_12->PK_DATA_TYPES == 3) {
            $PK_VALUES = S_USER_DEFINED_FIELDS_DETAIL::where('PK_USER_DEFINED_FIELDS', $res_cust_12->PK_USER_DEFINED_FIELDS)->whereIn('OPTION_NAME', explode(',', $field_value))->where('PK_ACCOUNT', $PK_ACCOUNT)->where('ACTIVE', 1)->groupBy('PK_USER_DEFINED_FIELDS')->selectRaw('GROUP_CONCAT(PK_USER_DEFINED_FIELDS_DETAIL) as PK_USER_DEFINED_FIELDS_DETAILS')->get();
            $CUSTOM_FIELDS['FIELD_VALUE'] = $PK_VALUES[0]->PK_USER_DEFINED_FIELDS_DETAILS ?? '';
        } elseif ($res_cust_12->PK_DATA_TYPES == 4) {
            if ($field_value != '') {
                $CUSTOM_FIELDS['FIELD_VALUE'] = date("Y-m-d", strtotime($field_value));
            } else {
                $CUSTOM_FIELDS['FIELD_VALUE'] = '';
            }
        }

        $cust_en_cond = '';
        if (strtolower($res_cust_12->TAB) == 'other') {
            $cust_en_cond = " AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' ";
        }



        $custom_field = SSTUDENTCUSTOMFIELD::where('PK_STUDENT_MASTER', $PK_STUDENT_MASTER)
            ->where('PK_ACCOUNT', $PK_ACCOUNT)
            ->where('PK_CUSTOM_FIELDS', $PK_CUSTOM_FIELDS);
        if ($cust_en_cond != '') {
            $custom_field->whereRaw($cust_en_cond);
        }

        if ($custom_field->count() == 0) {
            $CUSTOM_FIELDS['PK_ACCOUNT'] = $PK_ACCOUNT;
            $CUSTOM_FIELDS['PK_STUDENT_MASTER'] = $PK_STUDENT_MASTER;
            $CUSTOM_FIELDS['PK_CUSTOM_FIELDS'] = $PK_CUSTOM_FIELDS;
            $CUSTOM_FIELDS['FIELD_NAME'] = $field_name;
            $CUSTOM_FIELDS['CREATED_BY'] = 'API';
            $CUSTOM_FIELDS['CREATED_ON'] = $CREATED_ON_DATE;
            if (strtolower($res_cust_12->TAB) == 'other') {
                $CUSTOM_FIELDS['PK_STUDENT_ENROLLMENT'] = $PK_STUDENT_ENROLLMENT;
            }

            SSTUDENTCUSTOMFIELD::create($CUSTOM_FIELDS);
        } else {
            $CUSTOM_FIELDS['EDITED_BY'] = 'API';
            $CUSTOM_FIELDS['EDITED_ON'] = $CREATED_ON_DATE;
            $custom_field->update($CUSTOM_FIELDS);
        }
    }
}
