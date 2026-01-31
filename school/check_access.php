<? /* Ticket # 1241  */
if ($_SESSION['ADMIN_PK_ROLES'] != 1) {
    $res_login_session = $db->Execute("SELECT LOGIN_SESSION_ID FROM Z_USER WHERE PK_USER = '$_SESSION[PK_USER]'  AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
    if ($res_login_session->fields['LOGIN_SESSION_ID'] != $_SESSION['LOGIN_SESSION_ID']) {
        header("location:../index");
        exit;
    }
}
/* Ticket # 1241  */

function check_access($access_for)
{
    global $db;

    if ($_SESSION['PK_ROLES'] == 1 || $_SESSION['PK_ROLES'] == 2) {
        if ($access_for == 'ADMISSION_ACCESS' || $access_for == 'REGISTRAR_ACCESS' || $access_for == 'FINANCE_ACCESS' || $access_for == 'ACCOUNTING_ACCESS' || $access_for == 'PLACEMENT_ACCESS')
            return 3;
        else
            return 1;
    } else {
        $res = $db->Execute("SELECT $access_for FROM Z_USER_ACCESS WHERE PK_USER = '$_SESSION[PK_USER]'  AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
        return $res->fields[$access_for];
    }
}


function check_global_access()
{
    global $db;
    $res_unpost = $db->Execute("select ENABLE_UNPOST_BATCH from Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
    if ($res_unpost->fields['ENABLE_UNPOST_BATCH'] == 1) {
        return 1;
    } else {
        return 0;
    }
}


function has_report_access()
{
    global $db;
    if ($_SESSION['PK_ROLES'] == 1 || $_SESSION['PK_ROLES'] == 2) {
        return 1;
    } else {
        $res = $db->Execute("SELECT PK_USER_ACCESS FROM Z_USER_ACCESS WHERE PK_USER = '$_SESSION[PK_USER]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND (REPORT_ADMISSION = 1 OR REPORT_REGISTRAR = 1 OR REPORT_FINANCE = 1 OR REPORT_ACCOUNTING = 1 OR REPORT_PLACEMENT = 1 OR REPORT_CUSTOM_REPORT = 1 OR REPORT_COMPLIANCE_REPORTS = 1) ");
        if ($res->RecordCount() == 0)
            return 0;
        else
            return 1;
    }
}

function has_setup_access()
{
    global $db;
    if ($_SESSION['PK_ROLES'] == 1 || $_SESSION['PK_ROLES'] == 2) {
        return 1;
    } else {
        $res = $db->Execute("SELECT PK_USER_ACCESS FROM Z_USER_ACCESS WHERE PK_USER = '$_SESSION[PK_USER]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND (SETUP_SCHOOL = 1 OR SETUP_ADMISSION = 1 OR SETUP_STUDENT = 1 OR SETUP_FINANCE = 1 OR SETUP_REGISTRAR = 1 OR SETUP_ACCOUNTING = 1 OR SETUP_PLACEMENT = 1 OR SETUP_COMMUNICATION = 1 OR SETUP_TASK_MANAGEMENT = 1) ");
        if ($res->RecordCount() == 0)
            return 0;
        else
            return 1;
    }
}

function has_management_access()
{
    global $db;
    if ($_SESSION['PK_ROLES'] == 1 || $_SESSION['PK_ROLES'] == 2) {
        return 1;
    } else {
        $res = $db->Execute("SELECT PK_USER_ACCESS FROM Z_USER_ACCESS WHERE PK_USER = '$_SESSION[PK_USER]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND (MANAGEMENT_ADMISSION = 1 OR MANAGEMENT_REGISTRAR = 1 OR MANAGEMENT_FINANCE = 1 OR MANAGEMENT_ACCOUNTING = 1 OR MANAGEMENT_PLACEMENT = 1 OR MANAGEMENT_ACCREDITATION = 1 OR MANAGEMENT_UPLOADS = 1 OR MANAGEMENT_BULK_UPDATE = 1 OR MANAGEMENT_DIAMOND_PAY = 1 )");
        if ($res->RecordCount() == 0)
            return 0;
        else
            return 1;
    }
}

function has_custom_sap_report($pk_account)
{
    //To activate add pk account 99-prod,96-UAT-74,90-UAT-74-2,

    $domain_name = array('d3.diamondsis.com', 'uat-74.diamondsis.io', 'd3-2.diamondsis.com', 'uat-74-2.diamondsis.io', 'localhost');
    $actual_URL = $_SERVER['HTTP_HOST'];

    $account_array = array('15', '99', '96', '90', '515', '512', '517', '522', '501', '502');
    if (in_array($pk_account, $account_array) && in_array($actual_URL, $domain_name)) {
        return 1;
    } else {
        return 0;
    }
}


// DIAM-ACCOUNT-SAME
function has_sap_report($pk_account)
{
    $domain_name = array('d3.diamondsis.com', 'uat-74.diamondsis.io');
    $actual_URL = $_SERVER['HTTP_HOST'];

    $account_array = array('15', '67', '72', '64');
    if (in_array($pk_account, $account_array) && in_array($actual_URL, $domain_name)) {
        return 1;
    } else {
        return 0;
    }
}

function has_campus_ivy_report($pk_account)
{
    $domain_name = array('d3.diamondsis.com', 'uat-74.diamondsis.io');
    $actual_URL = $_SERVER['HTTP_HOST'];

    $account_array = array('67', '101');
    if (in_array($pk_account, $account_array) && in_array($actual_URL, $domain_name)) {
        return 1;
    } else {
        return 0;
    }
}

function av_check_access($feature)
{
    //To activate add pk account 99-prod,96-UAT-74,90-UAT-74-2,
    $checkfor['uat-74.diamondsis.io']  =
        [
            'transcript_sap' => [15],
            'has_campus_ivy_report' => [67, 101],
            'sap_report' => [15, 64, 67, 72],
            'has_custom_report' => [15, 96],
            'custom_transcript_century_college' => [87], //DIAM-863
            'event_report' => [15], //DIAM-1488-->
            'has_attendance_makeup_report' => [15],
            'student_transcripts_npti' => [15, 80], //DIAM-1959
            'student_transcripts_evcc' => [15], //DIAM-1959
            'has_ipeds_fall_12_enrollment' => [15],
            'has_transcript_report' => [15] //DIAM-1151
        ];
    $checkfor['uat-74-2.diamondsis.io'] =
        [
            'transcript_sap' => [91],
            'has_campus_ivy_report' => [505],
            'catherin_hind_custom_sap' => [118],
            'transcripts_arlington' => [113, 114],
            'sap_report' => [522, 117],
            'has_custom_report' => [90, 522, 515, 501],
            'has_attendance_makeup_report' => [515],
            'event_report' => [123], //DIAM-1488-->
            'has_ipeds_fall_12_enrollment' => [517],
            'has_transcript_report' => [501] //DIAM-1151
        ];
    $checkfor['d3.diamondsis.com'] =
        [
            'transcript_sap' => [15, 100],
            'has_campus_ivy_report' => [67, 101, 80, 63],
            'sap_report' => [15, 64, 67, 72],
            'has_custom_report' => [15, 99],
            'has_attendance_makeup_report' => [15],
            'custom_transcript_century_college' => [87], //DIAM-863
            'event_report' => [15], //DIAM-1488-->
            'student_transcripts_npti' => [15, 80], //DIAM-1959
            'student_transcripts_evcc' => [15, 96], //DIAM-1959
            'has_ipeds_fall_12_enrollment' => [15],
            'custom_pgb_report_card' => [15, 100, 35, 82, 87, 86, 32], //DIAM-2024 100-FBA
            'custom_satisfactory_progress_report_card' => [15, 51], //DIAM-1806
            'has_transcript_report' => [15], //DIAM-1151
        ];
    $checkfor['d3-2.diamondsis.com'] =
        [
            'transcript_sap' => [91],
            'has_campus_ivy_report' => [505],
            'transcripts_arlington' => [97],
            'sap_report' => [517, 99],
            'has_attendance_makeup_report' => [512],
            'has_custom_report' => [517, 512, 502],
            'event_report' => [501], //DIAM-1488-->
            'has_ipeds_fall_12_enrollment' => [517],
            'custom_pgb_report_card' => [100, 520], //DIAM-2024
            'has_transcript_report' => [502] //DIAM-1151
        ];

    $current_host = $_SERVER['HTTP_HOST'];

    if ($current_host == 'localhost') {
        return true;
    } else {
        if (isset($checkfor[$current_host][$feature])) {
            if (in_array($_SESSION['PK_ACCOUNT'], $checkfor[$current_host][$feature])) {
                return true;
            }
        }
    }
    return false;
}

// WVJC college
function has_wvjc_access($pk_account, $enabled = 0)
{
    //To activate add pk account 15,
    $domain_name = array('d3-2.diamondsis.com', 'd3.diamondsis.com', 'uat-74-2.diamondsis.io', 'uat-74.diamondsis.io', 'localhost');
    $actual_URL = $_SERVER['HTTP_HOST'];
    $account_array = array('15', '120', '516');
    if ($enabled == 1) {
        if (in_array($pk_account, $account_array) && in_array($actual_URL, $domain_name)) {
            return 1;
        } else {
            return 0;
        }
    } else {
        return 1;
    }
}

//DIAM-1471
function has_eic_access($pk_account, $enabled = 0)
{
    //To activate add pk account 15,
    $domain_name = array('d3-2.diamondsis.com', 'd3.diamondsis.com', 'uat-74-2.diamondsis.io', 'uat-74.diamondsis.io', 'localhost');
    $actual_URL = $_SERVER['HTTP_HOST'];
    $account_array = array('15', '522', '517'); //517-EIC PROD
    if ($enabled == 1) {
        if (in_array($pk_account, $account_array) && in_array($actual_URL, $domain_name)) {
            return 1;
        } else {
            return 0;
        }
    } else {
        return 1;
    }
}
//DIAM-1471

// WVJC college
function has_wvjc_access_transcript_desc($pk_account, $enabled = 0)
{
    //To activate add pk account 15,
    /*
    $domain_name = array('d3-2.diamondsis.com','d3.diamondsis.com','uat-74-2.diamondsis.io','uat-74.diamondsis.io','localhost');
    $actual_URL = $_SERVER['HTTP_HOST'];
    $account_array=array('15','120','516','501'); // CEM-UAT-2 => 501,
    if($enabled==1)
    {
        if(in_array($pk_account,$account_array) && in_array($actual_URL, $domain_name))
        {
            return 1;
        }
        else
        {
            return 0;
        }
    }
    else
    {
        return 1;
    }
    */
    return 1;
}
//wvjc
function has_wvjc_access_show_only_term_desc($pk_account, $enabled = 0)
{
    //To activate add pk account 15,
    // $domain_name = array('d3-2.diamondsis.com','d3.diamondsis.com','uat-74-2.diamondsis.io','uat-74.diamondsis.io','localhost');
    // $actual_URL = $_SERVER['HTTP_HOST'];
    // $account_array=array('15','120','516');
    // if($enabled==1){
    // if(in_array($pk_account,$account_array) && in_array($actual_URL, $domain_name)){
    //     return 1;
    // }else{
    //     return 0;
    // }
    // }else{
    //     return 1;
    // }

    return 1;
}
// DIAM - 1314
function has_student_trascript_access($pk_account)
{
    //To activate add pk account 15,
    $domain_name = array('d3-2.diamondsis.com', 'uat-74-2.diamondsis.io', 'localhost');
    $actual_URL = $_SERVER['HTTP_HOST'];
    $account_array = array('504');
    if (in_array($pk_account, $account_array) && in_array($actual_URL, $domain_name)) {
        return 1;
    } else {
        return 0;
    }
}
//DIAM-1527
function has_ccmc_access($pk_account, $enabled = 0)
{
    //To activate add pk account 15,
    // $domain_name = array('d3-2.diamondsis.com','uat-74-2.diamondsis.io','localhost');
    // $actual_URL = $_SERVER['HTTP_HOST'];
    // $account_array=array('115','98'); //98- PROD
    // if($enabled==1){
    // if(in_array($pk_account,$account_array) && in_array($actual_URL, $domain_name)){
    //     return 1;
    // }else{
    //     return 0;
    // }
    // }else{
    return 1;
    //}
}
//DIAM-1527
//DIAM-1757
function has_etc_access($pk_account, $enabled = 0)
{
    //To activate add pk account 15,
    // $domain_name = array('d3-2.diamondsis.com','uat-74-2.diamondsis.io','localhost');
    // $actual_URL = $_SERVER['HTTP_HOST'];
    // $account_array=array('117','99'); //99- PROD
    // if($enabled==1){
    // if(in_array($pk_account,$account_array) && in_array($actual_URL, $domain_name)){
    //     return 1;
    // }else{
    //     return 0;
    // }
    // }else{
    return 1;
    //}
}
//DIAM-1757
