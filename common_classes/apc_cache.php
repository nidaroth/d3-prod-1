<?php


class ApcCache
{
    var $db;
    var $iTtl = 300000000000000; // Time To Live
    var $known_vars;
    var $bEnabled = false; // APC enabled?
    // constructor
    function __construct()
    {
        require_once("../global/config.php");
        global $db;
        $this->db = $db;
        $this->bEnabled = apcu_enabled();

        if (!$this->bEnabled) {
            debug_print_backtrace();
            trigger_error("Fatal Error : APC CACHE is not able to initiate please check your system configuration", E_USER_ERROR);
        }

        $known_vars = [];
        $known_vars['PK_CITIZENSHIP'] = 'select PK_CITIZENSHIP,CITIZENSHIP from Z_CITIZENSHIP WHERE 1 = 1 Order by CITIZENSHIP ASC';

        $known_vars['PK_COUNTRY'] = 'select PK_COUNTRY, NAME from Z_COUNTRY WHERE 1=1  ORDER BY NAME ASC';
        $known_vars['PK_COUNTRY_CITIZEN'] = 'select PK_COUNTRY, NAME from Z_COUNTRY WHERE 1=1  ORDER BY NAME ASC';

        $known_vars['PK_STATES'] = 'select PK_STATES, STATE_NAME from Z_STATES WHERE 1 = 1  ORDER BY STATE_NAME ASC';
        $known_vars['PK_CAMPUS_PROGRAM'] = "select PK_CAMPUS_PROGRAM,CODE,DESCRIPTION from M_CAMPUS_PROGRAM WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CODE ASC";
        $known_vars['PK_CAMPUS'] = "select PK_CAMPUS,OFFICIAL_CAMPUS_NAME from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by OFFICIAL_CAMPUS_NAME ASC";

        $known_vars['PK_STUDENT_GROUP'] =  "select PK_STUDENT_GROUP,STUDENT_GROUP from M_STUDENT_GROUP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by STUDENT_GROUP ASC";


        $known_vars['PK_DRIVERS_LICENSE_STATE'] = "select PK_STATES, STATE_NAME from Z_STATES WHERE 1 = 1  ORDER BY STATE_NAME ASC";
        $known_vars['PK_HIGHEST_LEVEL_OF_EDU'] = "select * from M_HIGHEST_LEVEL_OF_EDU WHERE 1 = 1  ORDER BY HIGHEST_LEVEL_OF_EDU ASC";
        $known_vars['PK_MARITAL_STATUS'] = "select * from Z_MARITAL_STATUS WHERE 1 = 1  ORDER BY MARITAL_STATUS ASC";
        $known_vars['PK_RACE'] = "select * from Z_RACE WHERE 1 = 1  ORDER BY RACE ASC";
        $known_vars['PK_STATE_OF_RESIDENCY'] = "select PK_STATES, STATE_NAME from Z_STATES WHERE 1 = 1  ORDER BY STATE_NAME ASC";
        $known_vars['PK_1098T_REPORTING_TYPE'] = "select PK_1098T_REPORTING_TYPE,REPORTING_TYPE from Z_1098T_REPORTING_TYPE WHERE 1 = 1 Order by REPORTING_TYPE ASC";
        $known_vars['PK_REPRESENTATIVE'] = "SELECT * FROM (select S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER,CONCAT(FIRST_NAME,' ',LAST_NAME) AS NAME from S_EMPLOYEE_MASTER, M_DEPARTMENT , S_EMPLOYEE_DEPARTMENT  WHERE S_EMPLOYEE_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_DEPARTMENT_MASTER = 2 AND S_EMPLOYEE_DEPARTMENT.PK_DEPARTMENT = M_DEPARTMENT.PK_DEPARTMENT AND S_EMPLOYEE_DEPARTMENT.PK_EMPLOYEE_MASTER = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER $union) AS TEMP order by NAME ASC";
        $known_vars['PK_SPECIAL'] = "select PK_SPECIAL,SPECIAL from Z_SPECIAL WHERE 1 = 1  order by SPECIAL ASC";
        $known_vars['PK_LEAD_CONTACT_SOURCE'] = "select PK_LEAD_CONTACT_SOURCE,LEAD_CONTACT_SOURCE,DESCRIPTION from M_LEAD_CONTACT_SOURCE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by LEAD_CONTACT_SOURCE ASC";
        $known_vars['PK_DISTANCE_LEARNING'] = "select * from M_DISTANCE_LEARNING WHERE 1 = 1 order by DISTANCE_LEARNING ASC";
        $known_vars['PK_DROP_REASON'] = "select PK_DROP_REASON,DROP_REASON,DESCRIPTION from M_DROP_REASON WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by DROP_REASON ASC";
        $known_vars['PK_ENROLLMENT_STATUS'] = "select PK_ENROLLMENT_STATUS,CONCAT(CODE,' - ',DESCRIPTION) AS DESCRIPTION from M_ENROLLMENT_STATUS WHERE 1 = 1";
        $known_vars['PK_FUNDING'] = "select PK_FUNDING_MASTER,FUNDING from M_FUNDING WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by FUNDING ASC";
        $known_vars['FIRST_TERM'] = "select PK_TERM_MASTER, BEGIN_DATE from S_TERM_MASTER WHERE 1 = 1";
        $known_vars['PK_LEAD_SOURCE'] = "select PK_LEAD_SOURCE,LEAD_SOURCE,DESCRIPTION from M_LEAD_SOURCE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by LEAD_SOURCE ASC";
        $known_vars['PK_PLACEMENT_STATUS'] = "select PK_PLACEMENT_STATUS,PLACEMENT_STATUS from M_PLACEMENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by PLACEMENT_STATUS ASC";
        $known_vars['PK_CAMPUS_PROGRAM'] = "select PK_CAMPUS_PROGRAM,CODE,DESCRIPTION from M_CAMPUS_PROGRAM WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CODE ASC";
        $known_vars['PK_SAP_GROUP'] = "select PK_SAP_GROUP,SAP_GROUP_NAME from S_SAP_GROUP WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by SAP_GROUP_NAME ASC";
        $known_vars['PK_SESSION'] = "select PK_SESSION,SESSION from M_SESSION WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by DISPLAY_ORDER ASC";
        $known_vars['PK_STUDENT_STATUS'] = "select PK_STUDENT_STATUS,STUDENT_STATUS from M_STUDENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY STUDENT_STATUS";
        $known_vars['PK_STUDENT_GROUP'] = "select PK_STUDENT_GROUP,STUDENT_GROUP from M_STUDENT_GROUP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by STUDENT_GROUP ASC";
        $known_vars['EVENT_EMPLOYEE'] = "SELECT * FROM (select S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER,CONCAT(FIRST_NAME,' ',LAST_NAME) AS NAME from S_EMPLOYEE_MASTER, M_DEPARTMENT , S_EMPLOYEE_DEPARTMENT  WHERE S_EMPLOYEE_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_EMPLOYEE_DEPARTMENT.PK_DEPARTMENT = M_DEPARTMENT.PK_DEPARTMENT AND S_EMPLOYEE_DEPARTMENT.PK_EMPLOYEE_MASTER = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER) AS TEMP GROUP BY PK_EMPLOYEE_MASTER order by NAME ASC";
        $known_vars['EVENT_OTHER'] = "select PK_EVENT_OTHER,EVENT_OTHER from M_EVENT_OTHER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 2 order by EVENT_OTHER ASC";
        $known_vars['EVENT_STATUS'] = "select PK_NOTE_STATUS,NOTE_STATUS from M_NOTE_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 3 order by NOTE_STATUS ASC";
        $known_vars['EVENT_TYPE'] = "select PK_NOTE_TYPE, NOTE_TYPE from M_NOTE_TYPE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 2 order by NOTE_TYPE ASC";
        $known_vars['INTERNAL_MSG_SENT_FROM'] = "SELECT * FROM (select S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER,CONCAT(FIRST_NAME,' ',LAST_NAME) AS NAME from S_EMPLOYEE_MASTER, M_DEPARTMENT , S_EMPLOYEE_DEPARTMENT  WHERE S_EMPLOYEE_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_EMPLOYEE_DEPARTMENT.PK_DEPARTMENT = M_DEPARTMENT.PK_DEPARTMENT AND S_EMPLOYEE_DEPARTMENT.PK_EMPLOYEE_MASTER = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER) AS TEMP GROUP BY PK_EMPLOYEE_MASTER order by NAME ASC";
        $known_vars['NOTES_EMPLOYEE'] = "SELECT * FROM (select S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER,CONCAT(FIRST_NAME,' ',LAST_NAME) AS NAME from S_EMPLOYEE_MASTER, M_DEPARTMENT , S_EMPLOYEE_DEPARTMENT  WHERE S_EMPLOYEE_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_EMPLOYEE_DEPARTMENT.PK_DEPARTMENT = M_DEPARTMENT.PK_DEPARTMENT AND S_EMPLOYEE_DEPARTMENT.PK_EMPLOYEE_MASTER = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER) AS TEMP GROUP BY PK_EMPLOYEE_MASTER order by NAME ASC";
        $known_vars['NOTE_STATUS'] = "select PK_NOTE_STATUS,NOTE_STATUS from M_NOTE_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 2 order by NOTE_STATUS ASC";
        $known_vars['NOTE_TYPE'] = "select PK_NOTE_TYPE, NOTE_TYPE from M_NOTE_TYPE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 1 order by NOTE_TYPE ASC";
        $known_vars['PROBATION_LEVEL'] = "select * from M_PROBATION_LEVEL WHERE 1=1 ORDER BY SORT_ORDER ASC";
        $known_vars['ROBATION_STATUS'] = "select * from M_PROBATION_STATUS WHERE 1=1 ORDER BY PROBATION_STATUS ASC";
        $known_vars['PROBATION_TYPE'] = "select * from M_PROBATION_TYPE WHERE 1=1 ORDER BY PROBATION_TYPE ASC";
        $known_vars['TASK_EMPLOYEE'] = "SELECT * FROM (select S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER,CONCAT(FIRST_NAME,' ',LAST_NAME) AS NAME from S_EMPLOYEE_MASTER, M_DEPARTMENT , S_EMPLOYEE_DEPARTMENT  WHERE S_EMPLOYEE_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_EMPLOYEE_DEPARTMENT.PK_DEPARTMENT = M_DEPARTMENT.PK_DEPARTMENT AND S_EMPLOYEE_DEPARTMENT.PK_EMPLOYEE_MASTER = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER) AS TEMP GROUP BY PK_EMPLOYEE_MASTER order by NAME ASC";
        $known_vars['TASK_OTHER'] = "select PK_EVENT_OTHER,EVENT_OTHER from M_EVENT_OTHER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 1 order by EVENT_OTHER ASC";
        $known_vars['TASK_PRIORITY'] = "select PK_NOTES_PRIORITY_MASTER,NOTES_PRIORITY from M_NOTES_PRIORITY_MASTER WHERE 1 = 1";
        $known_vars['TASK_STATUS'] = "select PK_TASK_STATUS,TASK_STATUS,DESCRIPTION from M_TASK_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER By TASK_STATUS";
        $known_vars['TASK_TYPE'] = "select PK_TASK_TYPE,TASK_TYPE,DESCRIPTION from M_TASK_TYPE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY TASK_TYPE ";
        $known_vars['TEXT_DEPARTMENT'] = "select PK_DEPARTMENT, DEPARTMENT FROM M_DEPARTMENT WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_DEPARTMENT_MASTER IN (1,2,4,6,7) ORDER BY DEPARTMENT ASC";
        $known_vars['TEXT_EMPLOYEE'] = "SELECT * FROM (select S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER,CONCAT(FIRST_NAME,' ',LAST_NAME) AS NAME from S_EMPLOYEE_MASTER, M_DEPARTMENT , S_EMPLOYEE_DEPARTMENT  WHERE S_EMPLOYEE_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_EMPLOYEE_DEPARTMENT.PK_DEPARTMENT = M_DEPARTMENT.PK_DEPARTMENT AND S_EMPLOYEE_DEPARTMENT.PK_EMPLOYEE_MASTER = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER) AS TEMP GROUP BY PK_EMPLOYEE_MASTER order by NAME ASC";
        $known_vars['DOC_DEPARTMENT'] = "select PK_DEPARTMENT, DEPARTMENT FROM M_DEPARTMENT WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_DEPARTMENT_MASTER IN (1,2,4,6,7) ORDER BY DEPARTMENT ASC";
        $known_vars['DOC_EMPLOYEE'] = "SELECT * FROM (select S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER,CONCAT(FIRST_NAME,' ',LAST_NAME) AS NAME from S_EMPLOYEE_MASTER, M_DEPARTMENT , S_EMPLOYEE_DEPARTMENT  WHERE S_EMPLOYEE_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_EMPLOYEE_DEPARTMENT.PK_DEPARTMENT = M_DEPARTMENT.PK_DEPARTMENT AND S_EMPLOYEE_DEPARTMENT.PK_EMPLOYEE_MASTER = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER) AS TEMP GROUP BY PK_EMPLOYEE_MASTER order by NAME ASC ";
        $known_vars['REQUIREMENT_CATEGORY'] = "select PK_REQUIREMENT_CATEGORY, REQUIREMENT_CATEGORY FROM Z_REQUIREMENT_CATEGORY";
        $known_vars['OTHER_EDU_EDU_TYPE'] = "select PK_EDUCATION_TYPE, EDUCATION_TYPE FROM M_EDUCATION_TYPE ORDER BY EDUCATION_TYPE ASC ";


        $known_vars['OTHER_EDU_SCHOOL_STATE'] = "select PK_STATES, STATE_NAME from Z_STATES WHERE 1 = 1  ORDER BY STATE_NAME ASC";
        $known_vars['DISBURSEMENT_AWARD_YEAR'] = "select PK_AWARD_YEAR,AWARD_YEAR from M_AWARD_YEAR WHERE 1 = 1  order by PK_AWARD_YEAR DESC";
        $known_vars['DISBURSEMENT_LEDGER_CODE'] = "select PK_AR_LEDGER_CODE,CODE,LEDGER_DESCRIPTION from M_AR_LEDGER_CODE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 1 order by CODE ASC";
        $known_vars['DISBURSEMENT_STATUS'] = "select * from M_DISBURSEMENT_STATUS WHERE ACTIVE = 1";

        $known_vars['ESTIMATED_FEES_FEE_TYPE '] = "select * from M_FEE_TYPE WHERE ACTIVE = 1 order by FEE_TYPE ASC";
        $known_vars['LEDGER_BATCH_DETAIL'] = "select PK_CAMPUS_PROGRAM,CODE,DESCRIPTION from M_CAMPUS_PROGRAM WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CODE ASC";
        $known_vars['LEDGER_CODE'] = "";
        $known_vars['LEDGER_PAYMENT_TYPE_DETAIL'] = "select PK_AR_LEDGER_CODE,CODE,LEDGER_DESCRIPTION from M_AR_LEDGER_CODE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CODE ASC";
        $known_vars['LEDGER_TERM_BLOCK'] = "select PK_TERM_BLOCK,IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1 from S_TERM_BLOCK WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  order by BEGIN_DATE DESC";
        $known_vars['STUDENT_JOB_STATUS'] = "select * from M_PLACEMENT_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by PLACEMENT_STATUS ASC";
        $known_vars['STUDENT_JOB_TYPE'] = "select PK_PLACEMENT_TYPE, TYPE from M_PLACEMENT_TYPE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY TYPE ASC";
        $known_vars['STUDENT_JOB_PAY_TYPE'] = "select PK_PAY_TYPE,PAY_TYPE FROM M_PAY_TYPE WHERE 1 = 1 order by PAY_TYPE ASC";
        $known_vars['STUDENT_JOB_SOC_CODE'] = "select PK_SOC_CODE, SOC_CODE, SOC_TITLE from M_SOC_CODE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY SOC_CODE ASC";
        $known_vars['STUDENT_JOB_VERIFICATION_SOURCE'] = "select PK_PLACEMENT_VERIFICATION_SOURCE,VERIFICATION_SOURCE from M_PLACEMENT_VERIFICATION_SOURCE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by VERIFICATION_SOURCE ASC";
        $known_vars['STUDENT_JOB_COMPANY_NAME'] = "select PK_COMPANY, COMPANY_NAME from S_COMPANY WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY COMPANY_NAME ASC";
        $known_vars['GENDER'] = 'SELECT PK_GENDER,GENDER FROM `Z_GENDER`';
        $known_vars['ENROLLMENT_PK_TERM_BLOCK'] = "select PK_TERM_BLOCK,IF(BEGIN_DATE = '0000-00-00','', CONCAT (DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' ) ,' - ', DESCRIPTION)) AS TERM from S_TERM_BLOCK WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by BEGIN_DATE DESC";






        $known_vars['FERPA_BLOCK'] = [1 => "Yes", 2 => "No", 0 => "No"];
        $known_vars['OTHER_EDU_GRADUATED'] = [1 => "Yes", 2 => "No", 0 => "--"];
        $known_vars['PREVIOUS_COLLEGE'] = [1 => "Yes", 2 => "No", 0 => "No"];
        $known_vars['SSN_VERIFIED'] = [1 => "Yes", 2 => "No", 0 => "Not Set"];
        $known_vars['REENTRY'] = [1 => "Yes", 2 => "No", 0 => "No"];
        $known_vars['TRANSFER_IN'] = [1 => "Yes", 2 => "No", 0 => "No"];
        $known_vars['TRANSFER_OUT'] = [1 => "Yes", 2 => "No", 0 => "No"];
        $known_vars['EVENT_COMPLETED'] = [1 => "Yes", 2 => "No", 0 => "--"];
        $known_vars['NOTES_COMPLETED'] = [1 => "Yes", 2 => "No", 0 => "--"];
        $known_vars['TASK_ATTACHMENT'] = [1 => "Yes", 2 => "No", 0 => "--"];
        $known_vars['TASK_COMPLETED'] = [1 => "Yes", 2 => "No", 0 => "--"];
        $known_vars['TEXT_STATUS'] = [1 => "Yes", 2 => "No", 0 => "--"];
        $known_vars['DOC_RECEIVED'] = [1 => "Yes", 2 => "No", 0 => "--"];
        $known_vars['REQUIREMENT_COMPLETED'] = [1 => "Yes", 2 => "No", 0 => "--"];
        $known_vars['REQUIREMENT_TYPE'] = [1 => "School", 2 => "Program", 0 => "--"];
        $known_vars['OTHER_EDU_GRADUATED'] = [1 => "Yes", 2 => "No", 0 => "--"];
        $known_vars['OTHER_EDU_TRANSCRIPT_RECEIVED'] = [1 => "Yes", 2 => "No", 0 => "--"];
        $known_vars['OTHER_EDU_TRANSCRIPT_REQUESTED'] = [1 => "Yes", 2 => "No", 0 => "--"];
        $known_vars['DISBURSEMENT_FUND_REQUESTED'] = [1 => "Yes", 2 => "No", 0 => "--"];
        $known_vars['COMPANY_EVENT_COMPLETE'] = [1 => "Yes", 2 => "No", 0 => "--"];
        $known_vars['COMPANY_JOB_FULL_PART_TIME'] = [1 => "Full Time", 2 => "Part Time", 0 => "--"];
        $known_vars['LEDGER_PYA'] = [1 => "Yes", 2 => "No", 0 => "--"];
        $known_vars['STUDENT_JOB_ACTIVE'] = [1 => "Yes", 2 => "No", 0 => "--"];
        $known_vars['STUDENT_JOB_CURRENT_JOB'] = [1 => "Yes", 2 => "No", 0 => "--"];
        $known_vars['STUDENT_JOB_PART_FULL_TIME'] = [1 => "Full Time", 2 => "Part Time", 0 => "--"];
        $known_vars['STUDENT_JOB_INSTITUTIONAL_EMPLOYEMENT'] = [1 => "Yes", 2 => "No", 0 => "--"];
        $known_vars['STUDENT_JOB_SELF_EMPLOYED'] = [1 => "Yes", 2 => "No", 0 => "--"];


        //Company report cache 
        $known_vars['PK_COMPANY_SOURCE']  = "select PK_COMPANY_SOURCE, COMPANY_SOURCE from M_COMPANY_SOURCE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY COMPANY_SOURCE ASC";
        $known_vars['PK_COMPANY_CONTACT']  = "select PK_COMPANY_CONTACT, NAME from S_COMPANY_CONTACT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ";
        $known_vars['PK_COMPANY_ADVISOR']  = "SELECT * FROM (select S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER,CONCAT(FIRST_NAME,' ',LAST_NAME) AS NAME from S_EMPLOYEE_MASTER, M_DEPARTMENT , S_EMPLOYEE_DEPARTMENT  WHERE S_EMPLOYEE_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_EMPLOYEE_DEPARTMENT.PK_DEPARTMENT = M_DEPARTMENT.PK_DEPARTMENT AND S_EMPLOYEE_DEPARTMENT.PK_EMPLOYEE_MASTER = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER) AS TEMP GROUP BY PK_EMPLOYEE_MASTER order by NAME ASC";


        $known_vars['PK_PLACEMENT_COMPANY_STATUS']  = "select PK_PLACEMENT_COMPANY_STATUS, PLACEMENT_COMPANY_STATUS from M_PLACEMENT_COMPANY_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY PLACEMENT_COMPANY_STATUS ASC";
        $known_vars['PK_PLACEMENT_TYPE']  =  "select PK_PLACEMENT_TYPE, TYPE from M_PLACEMENT_TYPE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ORDER BY TYPE ASC";




        $this->known_vars = $known_vars;
    }
    // get data from memory
    function getData($sKey, $selfcall = false)
    {
        $bRes = false;
        // apcu_clear_cache();
        $vData = apcu_fetch($sKey . '_' . $_SESSION['PK_ACCOUNT'], $bRes);
        if ($vData) {
            return $vData;
        } else {

            $rdata = $this->refresh_known_vars($sKey);
            if ($rdata && $selfcall == false) {
                return  $this->getData($sKey, true);
            } else {
                return false;
            };
        }
        // return ($bRes) ? $vData : null;
    }
    public function GetValueFromCache($sKey, $primary_key)
    {


        $sKey = $sKey;

        $results = $this->getData($sKey);

        // $debug_key = 'PK_FUNDING';
        // if($debug_key == $sKey){
        //     echo "Calling " . $sKey . " >> " . $this->known_vars[$sKey] . "  <<";
        //     echo " results >> ";
        //     print_r($results);
        // }



        // if ($sKey == 'PK_CAMPUS') { 
        //     print_r('printing result for value'.$primary_key);
        //     print_r($results);
        //     // exit;
        // }
        if (!empty($results)) { 
            if(isset($results[$primary_key])){
                return $results[$primary_key];
            }else{
                return null;
            } 
        } else
            return $primary_key;
    }

    function refresh_known_vars($sKey)
    {
        if (isset($this->known_vars[$sKey])) {
            $returnData = [];
            #check if known var is set by query (string) or static (array)
            if (gettype($this->known_vars[$sKey]) == 'string') {

                $db_result = $this->db->Execute($this->known_vars[$sKey]);


                while (!$db_result->EOF) {


                    $returnData[array_values($db_result->fields)[0]] = array_values($db_result->fields)[1];
                    $db_result->MoveNext();
                }
            } else if (gettype($this->known_vars[$sKey]) == 'array') {
                $returnData = $this->known_vars[$sKey];
            }
            if ($sKey == 'PK_COMPANY_CONTACT') {

                // echo "sseting APCU CACHE for ".$sKey." ,,";
                // print_r($returnData);
                // echo " ---- ";
            }
            $this->setData($sKey, $returnData);
            return true;
        } else {
            return false;
        }
    }
    // save data to memory
    function setData($sKey, $vData)
    {
        // echo "function setData : >$sKey< for next ".$this->iTtl;
        // print_r($vData);
        // echo "--- end of fn setdata ---";
        return apcu_add($sKey . '_' . $_SESSION['PK_ACCOUNT'], $vData, $this->iTtl);
    }
    // delete data from memory
    function delData($sKey)
    {
        return (apcu_exists($sKey)) ? apcu_delete($sKey) : true;
    }
}
