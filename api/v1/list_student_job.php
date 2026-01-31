<? require_once("../../global/config.php"); 

$DATA = (file_get_contents('php://input'));

//$DATA = '{"PLACEMENT_STATUS":"From"}';

$DATA = urldecode($DATA);
$DATA = json_decode($DATA);

$API_KEY = '';
foreach (getallheaders() as $name => $value) {
    //echo "$name: $value<br />";
	if(strtolower(trim($name)) == 'apikey')
		$API_KEY = trim($value);
}
$flag = 1;
if($API_KEY == ''){
	$data['SUCCESS'] = 0;
	$data['MESSAGE'] = 'API Key Missing';
	
	$flag = 0;
} else {
	$res = $db->Execute("SELECT PK_ACCOUNT,ACTIVE FROM Z_ACCOUNT where API_KEY = '$API_KEY'");
	if($res->RecordCount() == 0){
		$data['SUCCESS'] = 0;
		$data['MESSAGE'] = 'Invalid API Key';
		
		$flag = 0;
	} else if($res->fields['ACTIVE'] == 0){
		$data['SUCCESS'] = 0;
		$data['MESSAGE'] = 'Your Account Is Blocked.';
		
		$flag = 0;
	}
	
	$PK_ACCOUNT = $res->fields['PK_ACCOUNT'];
}
if($flag == 1){
	$data['SUCCESS'] = 1;
	$data['MESSAGE'] = '';

    $query = "SELECT S_STUDENT_JOB.PK_STUDENT_JOB, S_STUDENT_JOB.SUPERVISOR, S_STUDENT_JOB.NOTES, S_STUDENT_JOB.START_DATE, S_STUDENT_JOB.END_DATE, S_STUDENT_JOB.PK_FULL_PART_TIME, S_STUDENT_JOB.CURRENT_JOB, S_STUDENT_JOB.PAY_AMOUNT, S_STUDENT_JOB.WEEKLY_HOURS, S_STUDENT_JOB.ANNUAL_SALARY, S_STUDENT_JOB.DOCUMENTED, S_STUDENT_JOB.VERIFICATION_DATE,
    S_COMPANY.PK_COMPANY, S_COMPANY.COMPANY_NAME,
    S_COMPANY_JOB.PK_COMPANY_JOB, S_COMPANY_JOB.JOB_TITLE,
    S_COMPANY_CONTACT.PK_COMPANY_CONTACT, S_COMPANY_CONTACT.NAME AS COMPANY_CONTACT_NAME,
    M_PLACEMENT_TYPE.PK_PLACEMENT_TYPE, M_PLACEMENT_TYPE.TYPE AS PLACEMENT_TYPE,
    M_PAY_TYPE.PK_PAY_TYPE, M_PAY_TYPE.PAY_TYPE,
    S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT,
    M_PLACEMENT_STATUS.PK_PLACEMENT_STATUS, M_PLACEMENT_STATUS.PLACEMENT_STATUS,
    M_PLACEMENT_VERIFICATION_SOURCE.PK_PLACEMENT_VERIFICATION_SOURCE, M_PLACEMENT_VERIFICATION_SOURCE.VERIFICATION_SOURCE,
    M_SOC_CODE.PK_SOC_CODE, M_SOC_CODE.SOC_CODE
    FROM
    S_STUDENT_JOB
    LEFT JOIN S_COMPANY ON S_COMPANY.PK_COMPANY = S_STUDENT_JOB.PK_COMPANY
    LEFT JOIN S_COMPANY_JOB ON S_COMPANY_JOB.PK_COMPANY_JOB = S_STUDENT_JOB.PK_COMPANY_JOB
    LEFT JOIN S_COMPANY_CONTACT ON S_COMPANY_CONTACT.PK_COMPANY_CONTACT = S_STUDENT_JOB.PK_COMPANY_CONTACT
    LEFT JOIN M_PLACEMENT_TYPE ON M_PLACEMENT_TYPE.PK_PLACEMENT_TYPE = S_STUDENT_JOB.PK_PLACEMENT_TYPE
    LEFT JOIN M_PAY_TYPE ON M_PAY_TYPE.PK_PAY_TYPE = S_STUDENT_JOB.PK_PAY_TYPE
    LEFT JOIN S_STUDENT_ENROLLMENT ON S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = S_STUDENT_JOB.PK_STUDENT_ENROLLMENT
    LEFT JOIN M_PLACEMENT_STATUS ON M_PLACEMENT_STATUS.PK_PLACEMENT_STATUS = S_STUDENT_JOB.PK_PLACEMENT_STATUS
    LEFT JOIN M_PLACEMENT_VERIFICATION_SOURCE ON M_PLACEMENT_VERIFICATION_SOURCE.PK_PLACEMENT_VERIFICATION_SOURCE = S_STUDENT_JOB.PK_PLACEMENT_VERIFICATION_SOURCE
    LEFT JOIN M_SOC_CODE ON M_SOC_CODE.PK_SOC_CODE = S_STUDENT_JOB.PK_SOC_CODE
    
    WHERE S_STUDENT_JOB.ACTIVE = 1 AND S_STUDENT_JOB.PK_ACCOUNT='$PK_ACCOUNT'";
    $res = $db->Execute($query);

    $i = 0;
	while (!$res->EOF) {
        $data['STUDENT_JOB'][$i]['ID']  					= $res->fields['PK_STUDENT_JOB'];
        $data['STUDENT_JOB'][$i]['COMPANY_ID']  			= $res->fields['PK_COMPANY'];
        $data['STUDENT_JOB'][$i]['SUPERVISOR']  			= $res->fields['SUPERVISOR'];
        $data['STUDENT_JOB'][$i]['NOTES']  					= $res->fields['NOTES'];
        $data['STUDENT_JOB'][$i]['START_DATE'] 				= ($res->fields['START_DATE'] != '0000-00-00' && $res->fields['START_DATE'] != '' ? date("m/d/Y",strtotime($res->fields['START_DATE'])) : '');
        $data['STUDENT_JOB'][$i]['END_DATE'] 				= ($res->fields['END_DATE'] != '0000-00-00' && $res->fields['END_DATE'] != '' ? date("m/d/Y",strtotime($res->fields['END_DATE'])) : '');
        $data['STUDENT_JOB'][$i]['DOCUMENTED_DATE']  		= ($res->fields['DOCUMENTED'] != '0000-00-00' && $res->fields['DOCUMENTED'] != '' ? date("m/d/Y",strtotime($res->fields['DOCUMENTED'])) : '');
        $data['STUDENT_JOB'][$i]['VERIFICATION_DATE']  		= ($res->fields['VERIFICATION_DATE'] != '0000-00-00' && $res->fields['VERIFICATION_DATE'] != '' ? date("m/d/Y",strtotime($res->fields['VERIFICATION_DATE'])) : '');
        $data['STUDENT_JOB'][$i]['FULL_PART_TIME']  		= (($res->fields['PK_FULL_PART_TIME'] == null || $res->fields['PK_FULL_PART_TIME'] == '' )?'':($res->fields['PK_FULL_PART_TIME']==1?'Full Time':'Part Time'));
        $data['STUDENT_JOB'][$i]['CURRENT_JOB']  			= (($res->fields['CURRENT_JOB'] == null || $res->fields['CURRENT_JOB'] == '' )?'':($res->fields['CURRENT_JOB']==1?'Yes':'No'));
        $data['STUDENT_JOB'][$i]['COMPANY_NAME']  			= $res->fields['COMPANY_NAME'];
        $data['STUDENT_JOB'][$i]['COMPANY_JOB_ID']  		= $res->fields['PK_COMPANY_JOB'];
        $data['STUDENT_JOB'][$i]['COMPANY_CONTACT_ID']  	= $res->fields['PK_COMPANY_CONTACT'];
        $data['STUDENT_JOB'][$i]['COMPANY_CONTACT_NAME']  	= $res->fields['COMPANY_CONTACT_NAME'];
        $data['STUDENT_JOB'][$i]['PLACEMENT_TYPE_ID']  		= $res->fields['PK_PLACEMENT_TYPE'];
        $data['STUDENT_JOB'][$i]['PLACEMENT_TYPE']  		= $res->fields['PLACEMENT_TYPE'];
        $data['STUDENT_JOB'][$i]['JOB_TITLE']  				= $res->fields['JOB_TITLE'];
        $data['STUDENT_JOB'][$i]['PAY_TYPE_ID']  			= $res->fields['PK_PAY_TYPE'];
        $data['STUDENT_JOB'][$i]['PAY_TYPE']  				= $res->fields['PAY_TYPE'];
        $data['STUDENT_JOB'][$i]['PAY_AMOUNT']  			= $res->fields['PAY_AMOUNT'];
        $data['STUDENT_JOB'][$i]['WEEKLY_HOURS']  			= $res->fields['WEEKLY_HOURS'];
        $data['STUDENT_JOB'][$i]['STUDENT_ENROLLMENT_ID']  	= $res->fields['PK_STUDENT_ENROLLMENT'];
        $data['STUDENT_JOB'][$i]['ANNUAL_SALARY']  			= $res->fields['ANNUAL_SALARY'];
        $data['STUDENT_JOB'][$i]['PLACEMENT_STATUS_ID']  	= $res->fields['PK_PLACEMENT_STATUS'];
        $data['STUDENT_JOB'][$i]['PLACEMENT_STATUS']  		= $res->fields['PLACEMENT_STATUS'];
        $data['STUDENT_JOB'][$i]['PLACEMENT_VERIFICATION_SOURCE_ID']  = $res->fields['PK_PLACEMENT_VERIFICATION_SOURCE'];
        $data['STUDENT_JOB'][$i]['VERIFICATION_SOURCE']  	= $res->fields['VERIFICATION_SOURCE'];
        $data['STUDENT_JOB'][$i]['SOC_CODE_ID']  			= $res->fields['PK_SOC_CODE'];
        $data['STUDENT_JOB'][$i]['SOC_CODE']  				= $res->fields['SOC_CODE'];
        $i++;
		$res->MoveNext();
    }
}
$data = json_encode($data);
echo $data;
