<? ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set("pcre.backtrack_limit", "5000000");

require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/employee.php");
require_once("../language/menu.php");
require_once("check_access.php");
require_once("get_department_from_t.php");

if(check_access('SETUP_SCHOOL') == 0 ){
	header("location:../index");
	exit;
}

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	
	$campus_name = "";
	$campus_cond = "";
	$campus_id	 = "";
	if(!empty($_POST['PK_CAMPUS'])){
		$PK_CAMPUS 	 = implode(",",$_POST['PK_CAMPUS']);
		$campus_cond = " AND PK_CAMPUS IN ($PK_CAMPUS) ";
	}
	
	$cond = "";
	$res_campus = $db->Execute("select PK_CAMPUS,CAMPUS_CODE from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $campus_cond order by CAMPUS_CODE ASC");
	while (!$res_campus->EOF) {
		if($campus_name != '')
			$campus_name .= ', ';
		$campus_name .= $res_campus->fields['CAMPUS_CODE'];
		
		if($campus_id != '')
			$campus_id .= ',';
		$campus_id .= $res_campus->fields['PK_CAMPUS'];
		
		$res_campus->MoveNext();
	}
	
	$timezone = $_SESSION['PK_TIMEZONE'];
	if($timezone == '' || $timezone == 0) {
		$res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		$timezone = $res->fields['PK_TIMEZONE'];
		if($timezone == '' || $timezone == 0)
			$timezone = 4;
	}
	
	$res = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");
	$TIMEZONE = $res->fields['TIMEZONE'];
	
	$PK_EMPLOYEE_MASTER = implode(",", $_POST['PK_EMPLOYEE_MASTER']);
	
	if($_POST['REPORT_TYPE'] == 1) {
		$query = "select S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER, CONCAT(S_EMPLOYEE_MASTER.LAST_NAME,', ', S_EMPLOYEE_MASTER.FIRST_NAME) AS EMP_NAME, GROUP_CONCAT(CAMPUS_CODE ORDER BY CAMPUS_CODE SEPARATOR ', ') as CAMPUS, CONCAT(S_EMPLOYEE_MASTER_SUP.LAST_NAME,', ', S_EMPLOYEE_MASTER_SUP.FIRST_NAME) AS SUP_NAME, S_EMPLOYEE_MASTER.TITLE, IF(S_EMPLOYEE_MASTER.FULL_PART_TIME = 1, 'Full Time', IF(S_EMPLOYEE_MASTER.FULL_PART_TIME = 2, 'Part Time', '') ) as FULL_PART_TIME_1, IF(S_EMPLOYEE_MASTER.LOGIN_CREATED = 1, 'Yes', 'No') as LOGIN_CREATED_1, IF(S_EMPLOYEE_MASTER.IS_FACULTY = 1, 'Yes', 'No') as IS_FACULTY_1, IF(S_EMPLOYEE_MASTER.IS_ADMIN = 1, 'Yes', 'No') as IS_ADMIN_1, IF(S_EMPLOYEE_MASTER.ACTIVE = 1, 'Yes', 'No') as ACTIVE_1,IF(S_EMPLOYEE_MASTER.DATE_HIRED = '0000-00-00','',DATE_FORMAT(S_EMPLOYEE_MASTER.DATE_HIRED, '%m/%d/%Y' )) AS  DATE_HIRED_1 ,IF(S_EMPLOYEE_MASTER.DATE_TERMINATED = '0000-00-00','',DATE_FORMAT(S_EMPLOYEE_MASTER.DATE_TERMINATED, '%m/%d/%Y' )) AS  DATE_TERMINATED_1, S_EMPLOYEE_MASTER.LAST_NAME, S_EMPLOYEE_MASTER.FIRST_NAME, S_EMPLOYEE_MASTER.MIDDLE_NAME, PRE_FIX, SOC_CODE, IF(S_EMPLOYEE_MASTER.ELIGIBLE_FOR_REHIRE = 1, 'Yes', 'No') as ELIGIBLE_FOR_REHIRE, S_EMPLOYEE_MASTER.EMPLOYEE_ID, S_EMPLOYEE_MASTER.NETWORK_ID, S_EMPLOYEE_MASTER.COMPANY_EMP_ID, IF(S_EMPLOYEE_MASTER.DOB = '0000-00-00','',DATE_FORMAT(S_EMPLOYEE_MASTER.DOB, '%m/%d/%Y' )) AS  DOB, Z_GENDER.GENDER, S_EMPLOYEE_MASTER.IPEDS_ETHNICITY, MARITAL_STATUS, S_EMPLOYEE_MASTER.SSN, S_EMPLOYEE_CONTACT.ADDRESS, S_EMPLOYEE_CONTACT.ADDRESS_1, S_EMPLOYEE_CONTACT.CITY, STATE_CODE, S_EMPLOYEE_CONTACT.ZIP, Z_COUNTRY.NAME as COUNTRY_NAME, S_EMPLOYEE_CONTACT.HOME_PHONE, S_EMPLOYEE_CONTACT.WORK_PHONE, S_EMPLOYEE_CONTACT.CELL_PHONE, S_EMPLOYEE_MASTER.EMAIL_OTHER, USER_ID      
		FROM 
		S_EMPLOYEE_MASTER 
		LEFT JOIN S_EMPLOYEE_CONTACT ON S_EMPLOYEE_CONTACT.PK_EMPLOYEE_MASTER = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER 
		LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = S_EMPLOYEE_CONTACT.PK_STATES 
		LEFT JOIN Z_COUNTRY ON Z_COUNTRY.PK_COUNTRY = S_EMPLOYEE_CONTACT.PK_COUNTRY  
		LEFT JOIN Z_PRE_FIX ON Z_PRE_FIX.PK_PRE_FIX = S_EMPLOYEE_MASTER.PK_PRE_FIX 
		LEFT JOIN M_SOC_CODE ON M_SOC_CODE.PK_SOC_CODE = S_EMPLOYEE_MASTER.PK_SOC_CODE 
		LEFT JOIN Z_MARITAL_STATUS ON Z_MARITAL_STATUS.PK_MARITAL_STATUS = S_EMPLOYEE_MASTER.PK_MARITAL_STATUS  
		LEFT JOIN Z_GENDER ON Z_GENDER.PK_GENDER = S_EMPLOYEE_MASTER.GENDER 
		LEFT JOIN S_EMPLOYEE_CAMPUS ON S_EMPLOYEE_CAMPUS.PK_EMPLOYEE_MASTER = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER 
		LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_EMPLOYEE_CAMPUS.PK_CAMPUS 
		LEFT JOIN S_EMPLOYEE_MASTER as S_EMPLOYEE_MASTER_SUP ON S_EMPLOYEE_MASTER_SUP.PK_EMPLOYEE_MASTER = S_EMPLOYEE_MASTER.PK_SUPERVISOR 
		LEFT JOIN Z_USER ON Z_USER.ID = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER AND PK_USER_TYPE = 2 
		$table 
		WHERE S_EMPLOYEE_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER IN ($PK_EMPLOYEE_MASTER)  
		GROUP BY S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER ORDER BY S_EMPLOYEE_MASTER.ACTIVE DESC, S_EMPLOYEE_MASTER.LAST_NAME ASC , S_EMPLOYEE_MASTER.FIRST_NAME ASC";
		
		if($_POST['FORMAT'] == 1){
			require_once '../global/mpdf/vendor/autoload.php';
			
			$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, WEBSITE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
			$SCHOOL_NAME = $res->fields['SCHOOL_NAME'];
			$PDF_LOGO 	 = $res->fields['PDF_LOGO'];
			
			$logo = "";
			if($PDF_LOGO != '')
				$logo = '<img src="'.$PDF_LOGO.'" height="50px" />';
				
			$header = '<table width="100%" >
							<tr>
								<td width="20%" valign="top" >'.$logo.'</td>
								<td width="50%" valign="top" style="font-size:20px" >'.$SCHOOL_NAME.'</td>
								<td width="30%" valign="top" >
									<table width="100%" >
										<tr>
											<td width="100%" align="right" style="font-size:20px;border-bottom:1px solid #000;" ><b>Employees</b></td>
										</tr>
									</table>
								</td>
							</tr>
							<tr>
								<td colspan="3" width="100%" align="right" style="font-size:13px;" >Campus(es): '.$campus_name.'</td>
							</tr>
						</table>';
						
			$date = convert_to_user_date(date('Y-m-d H:i:s'),'l, F d, Y h:i A',$TIMEZONE,date_default_timezone_get());
						
			$footer = '<table width="100%" >
							<tr>
								<td width="33%" valign="top" style="font-size:10px;" ><i>'.$date.'</i></td>
								<td width="33%" valign="top" style="font-size:10px;" align="center" ></td>
								<td width="33%" valign="top" style="font-size:10px;" align="right" ><i>Page {PAGENO} of {nb}</i></td>
							</tr>
						</table>';
			
			$mpdf = new \Mpdf\Mpdf([
				'margin_left' => 7,
				'margin_right' => 5,
				'margin_top' => 35,
				'margin_bottom' => 15,
				'margin_header' => 3,
				'margin_footer' => 10,
				'default_font_size' => 9,
				'format' => [210, 296],
				'orientation' => 'L'
			]);
			$mpdf->autoPageBreak = true;
			
			$mpdf->SetHTMLHeader($header);
			$mpdf->SetHTMLFooter($footer);
			
			$txt = '<table border="0" cellspacing="0" cellpadding="3" width="100%">
						<thead>
							<tr>
								<td width="14%" style="border-bottom:1px solid #000;">
									<b><i>Employee</i></b>
								</td>
								<td width="11%" style="border-bottom:1px solid #000;">
									<b><i>Campus</i></b>
								</td>
								<td width="11%" style="border-bottom:1px solid #000;">
									<b><i>Department</i></b>
								</td>
								<td width="14%" style="border-bottom:1px solid #000;">
									<b><i>Supervisor</i></b>
								</td>
								<td width="10%" style="border-bottom:1px solid #000;">
									<b><i>Job Title</i></b>
								</td>
								<td width="10%" style="border-bottom:1px solid #000;">
									<b><i>Full/Part Time</i></b>
								</td>
								<td width="7%" style="border-bottom:1px solid #000;">
									<b><i>Has Login</i></b>
								</td>
								<td width="8%" style="border-bottom:1px solid #000;">
									<b><i>Instructor</i></b>
								</td>
								<td width="6%" style="border-bottom:1px solid #000;">
									<b><i>School Admin</i></b>
								</td>
								<td width="5%" style="border-bottom:1px solid #000;">
									<b><i>Active</i></b>
								</td>
							</tr>
						</thead>';
			$res = $db->Execute($query);			
			while (!$res->EOF) { 
				$date = convert_to_user_date($res->fields['SENT_ON'],'m/d/Y h:i A',$TIMEZONE,date_default_timezone_get());
				
				$res_dep = $db->Execute("select GROUP_CONCAT(DEPARTMENT SEPARATOR ', ') as DEPARTMENT FROM S_EMPLOYEE_DEPARTMENT, M_DEPARTMENT WHERE M_DEPARTMENT.PK_DEPARTMENT = S_EMPLOYEE_DEPARTMENT.PK_DEPARTMENT  AND PK_EMPLOYEE_MASTER = '".$res->fields['PK_EMPLOYEE_MASTER']."' ");
				
				$txt .= '<tr>
							<td >'.$res->fields['EMP_NAME'].'</td>
							<td >'.$res->fields['CAMPUS'].'</td>
							<td >'.$res_dep->fields['DEPARTMENT'].'</td>
							<td >'.$res->fields['SUP_NAME'].'</td>
							<td >'.$res->fields['TITLE'].'</td>
							<td >'.$res->fields['FULL_PART_TIME_1'].'</td>
							<td >'.$res->fields['LOGIN_CREATED_1'].'</td>
							<td >'.$res->fields['IS_FACULTY_1'].'</td>
							<td >'.$res->fields['IS_ADMIN_1'].'</td>
							<td >'.$res->fields['ACTIVE_1'].'</td>
						</tr>';
				$res->MoveNext();
			}
			$txt .= '</table>';
		
			$mpdf->WriteHTML($txt);
			$mpdf->Output("Employee.pdf", 'D');
			exit;
		} else {
			include '../global/excel/Classes/PHPExcel/IOFactory.php';
			$cell1  = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");		
			define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

			$total_fields = 120;
			for($i = 0 ; $i <= $total_fields ; $i++){
				if($i <= 25)
					$cell[] = $cell1[$i];
				else {
					$j = floor($i / 26) - 1;
					$k = ($i % 26);
					//echo $j."--".$k."<br />";
					$cell[] = $cell1[$j].$cell1[$k];
				}	
			}

			$file_name 		= "Employee.xlsx";
			$dir 			= 'temp/';
			$inputFileType  = 'Excel2007';
			$outputFileName = $dir.$file_name; 
$outputFileName = str_replace(
	pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),
	$outputFileName );  

			$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
			$objReader->setIncludeCharts(TRUE);
			$objPHPExcel = new PHPExcel();
			$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			
			$line = 1;
			$index 	= -1;
			$heading[] = 'Last Name';
			$width[]   = 15;
			$heading[] = 'First Name';
			$width[]   = 15;
			$heading[] = 'Middle Name';
			$width[]   = 15;
			$heading[] = 'Prefix';
			$width[]   = 15;
			$heading[] = 'Campus';
			$width[]   = 15;
			$heading[] = 'Department';
			$width[]   = 15;
			$heading[] = 'Title';
			$width[]   = 15;
			$heading[] = 'SOC Code';
			$width[]   = 15;
			$heading[] = 'Full/Part Time';
			$width[]   = 15;
			$heading[] = 'Supervisor';
			$width[]   = 15;
			$heading[] = 'Date Hired';
			$width[]   = 15;
			$heading[] = 'Date Terminated';
			$width[]   = 15;
			$heading[] = 'Eligible for Rehire';
			$width[]   = 15;
			$heading[] = 'Employee ID';
			$width[]   = 15;
			$heading[] = 'Network ID';
			$width[]   = 15;
			$heading[] = 'Company Emp ID';
			$width[]   = 15;
			$heading[] = 'Date of Birth';
			$width[]   = 15;
			$heading[] = 'Gender';
			$width[]   = 15;
			$heading[] = 'IPEDS Ethnicity';
			$width[]   = 15;
			$heading[] = 'Race';
			$width[]   = 15;
			$heading[] = 'Martial Status';
			$width[]   = 15;
			$heading[] = 'SSN';
			$width[]   = 15;
			$heading[] = 'Address';
			$width[]   = 15;
			$heading[] = 'Address 2nd Line';
			$width[]   = 15;
			$heading[] = 'City';
			$width[]   = 15;
			$heading[] = 'State';
			$width[]   = 15;
			$heading[] = 'Zip';
			$width[]   = 15;
			$heading[] = 'Country';
			$width[]   = 15;
			$heading[] = 'Home Phone';
			$width[]   = 15;
			$heading[] = 'Mobile Phone';
			$width[]   = 15;
			$heading[] = 'Work Phone';
			$width[]   = 15;
			$heading[] = 'Email / User ID';
			$width[]   = 15;
			$heading[] = 'Email Other';
			$width[]   = 15;
			$heading[] = 'Active';
			$width[]   = 15;
			
			$PK_CUSTOM_FIELDS_ARR 		= array();
			$PK_DATA_TYPES_ARR 			= array();
			$PK_USER_DEFINED_FIELDS_ARR = array();
			
			$res_type = $db->Execute("select PK_CUSTOM_FIELDS, FIELD_NAME, PK_USER_DEFINED_FIELDS, PK_DATA_TYPES from S_CUSTOM_FIELDS WHERE S_CUSTOM_FIELDS.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_CUSTOM_FIELDS.ACTIVE = 1 AND SECTION = 2 ORDER BY FIELD_NAME ASC ");
			while (!$res_type->EOF) {
				$PK_CUSTOM_FIELDS_ARR[] 		= $res_type->fields['PK_CUSTOM_FIELDS'];
				$PK_DATA_TYPES_ARR[] 			= $res_type->fields['PK_DATA_TYPES'];
				$PK_USER_DEFINED_FIELDS_ARR[] 	= $res_type->fields['PK_USER_DEFINED_FIELDS'];
				
				$heading[] = $res_type->fields['FIELD_NAME'];
				$width[]   = 15;
				
				$res_type->MoveNext();
			}
			
			$i = 0;
			foreach($heading as $title) {
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
				$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
				$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);
				
				$i++;
			}

			$res = $db->Execute($query);
			while (!$res->EOF) { 
				
				$line++;
				$index = -1;
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['LAST_NAME']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['FIRST_NAME']);

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['MIDDLE_NAME']);
			
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['PRE_FIX']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CAMPUS']);
				
				$res_dep = $db->Execute("select GROUP_CONCAT(DEPARTMENT ORDER BY DEPARTMENT SEPARATOR ', ') as DEPARTMENT FROM S_EMPLOYEE_DEPARTMENT, M_DEPARTMENT WHERE M_DEPARTMENT.PK_DEPARTMENT = S_EMPLOYEE_DEPARTMENT.PK_DEPARTMENT  AND PK_EMPLOYEE_MASTER = '".$res->fields['PK_EMPLOYEE_MASTER']."' ");
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_dep->fields['DEPARTMENT']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['TITLE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['SOC_CODE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['FULL_PART_TIME_1']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['SUP_NAME']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['DATE_HIRED_1']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['DATE_TERMINATED_1']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['ELIGIBLE_FOR_REHIRE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['EMPLOYEE_ID']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['NETWORK_ID']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['COMPANY_EMP_ID']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['DOB']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['GENDER']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['IPEDS_ETHNICITY']);
				
				$res_race = $db->Execute("select GROUP_CONCAT(RACE ORDER BY RACE SEPARATOR ', ') as RACE FROM S_EMPLOYEE_RACE, Z_RACE WHERE Z_RACE.PK_RACE = S_EMPLOYEE_RACE.PK_RACE  AND PK_EMPLOYEE_MASTER = '".$res->fields['PK_EMPLOYEE_MASTER']."' ");
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_race->fields['RACE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['MARITAL_STATUS']);
				
				$SSN = $res->fields['SSN'];
				if($SSN != '') {
					$SSN 	 = my_decrypt($_SESSION['PK_ACCOUNT'].$_GET['id'],$SSN);
					$SSN_ORG = $SSN;
					$SSN_ARR = explode("-",$SSN);
					$SSN 	 = 'xxx-xx-'.$SSN_ARR[2];
				}
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($SSN);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['ADDRESS']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['ADDRESS_1']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CITY']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['STATE_CODE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['ZIP']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['COUNTRY_NAME']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['HOME_PHONE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CELL_PHONE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['WORK_PHONE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['USER_ID']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['EMAIL_OTHER']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['ACTIVE_1']);
				
				$PK_EMPLOYEE_MASTER = $res->fields['PK_EMPLOYEE_MASTER'];
				foreach($PK_CUSTOM_FIELDS_ARR as $ii1 => $PK_CUSTOM_FIELDS){
					$PK_DATA_TYPES 			= $PK_DATA_TYPES_ARR[$ii1];
					$PK_USER_DEFINED_FIELDS = $PK_USER_DEFINED_FIELDS_ARR[$ii1];
					
					$value = "";
					if($PK_DATA_TYPES == 1 || $PK_DATA_TYPES == 4) { 
						//Text, Date
						$res_stu_cus = $db->Execute("select FIELD_VALUE FROM S_EMPLOYEE_CUSTOM_FIELDS WHERE PK_EMPLOYEE_MASTER = '$PK_EMPLOYEE_MASTER' AND PK_CUSTOM_FIELDS = '$PK_CUSTOM_FIELDS' ");
						$value = $res_stu_cus->fields['FIELD_VALUE'];
						
						if($PK_DATA_TYPES == 4 && $value != ''){
							$value = date("m/d/Y",strtotime($value));
						}
					} else if($PK_DATA_TYPES == 2) { 
						//Drop Down
						$res_stu_cus = $db->Execute("select OPTION_NAME FROM S_EMPLOYEE_CUSTOM_FIELDS, S_USER_DEFINED_FIELDS_DETAIL WHERE PK_EMPLOYEE_MASTER = '$PK_EMPLOYEE_MASTER' AND PK_CUSTOM_FIELDS = '$PK_CUSTOM_FIELDS' AND PK_USER_DEFINED_FIELDS_DETAIL =  FIELD_VALUE ");
						$value = $res_stu_cus->fields['OPTION_NAME'];
						
					} else if($PK_DATA_TYPES == 3) { 
						//Multiple Choice
						$res_stu_cus = $db->Execute("select FIELD_VALUE FROM S_EMPLOYEE_CUSTOM_FIELDS WHERE PK_EMPLOYEE_MASTER = '$PK_EMPLOYEE_MASTER' AND PK_CUSTOM_FIELDS = '$PK_CUSTOM_FIELDS' ");
						$value = $res_stu_cus->fields['FIELD_VALUE'];
						
						$res_stu_cus = $db->Execute("select GROUP_CONCAT(OPTION_NAME ORDER BY OPTION_NAME ASC SEPARATOR ', ') as OPTION_NAME FROM S_USER_DEFINED_FIELDS_DETAIL WHERE PK_USER_DEFINED_FIELDS_DETAIL IN ($value)  ");
						$value = $res_stu_cus->fields['OPTION_NAME'];
					}
					
					$index++;
					$cell_no = $cell[$index].$line;
					$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($value);
				}
			
				$res->MoveNext();
			}
			
			$objWriter->save($outputFileName);
			$objPHPExcel->disconnectWorksheets();
			header("location:".$outputFileName);
			exit;
		}
	} else if($_POST['REPORT_TYPE'] == 2) {
		$query = "select S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER, CONCAT(S_EMPLOYEE_MASTER.LAST_NAME,', ', S_EMPLOYEE_MASTER.FIRST_NAME) AS EMP_NAME, IF(S_EMPLOYEE_MASTER.ACTIVE = 1, 'Yes', 'No') as ACTIVE_1, GROUP_CONCAT(CAMPUS_CODE ORDER BY CAMPUS_CODE SEPARATOR ', ') as CAMPUS, CONCAT(S_EMPLOYEE_MASTER_SUP.LAST_NAME,', ', S_EMPLOYEE_MASTER_SUP.FIRST_NAME) AS SUP_NAME, S_EMPLOYEE_MASTER.TITLE,  IF(S_EMPLOYEE_MASTER.LOGIN_CREATED = 1, 'Yes', 'No') as LOGIN_CREATED_1, IF(S_EMPLOYEE_MASTER.IS_FACULTY = 1, 'Yes', 'No') as IS_FACULTY_1, IF(S_EMPLOYEE_MASTER.IS_ADMIN = 1, 'Yes', 'No') as IS_ADMIN_1, S_EMPLOYEE_CONTACT.HOME_PHONE, S_EMPLOYEE_CONTACT.WORK_PHONE, S_EMPLOYEE_CONTACT.CELL_PHONE, S_EMPLOYEE_MASTER.EMAIL_OTHER, USER_ID, Z_USER.PK_USER, IF(S_EMPLOYEE_MASTER.TURN_OFF_ASSIGNMENTS = 1, 'Yes', 'No') as TURN_OFF_ASSIGNMENTS, LANGUAGE, IF(S_EMPLOYEE_MASTER.NEED_SCHOOL_ACCESS = 1, 'Yes', 'No') as D3_ACCESS        
		FROM 
		S_EMPLOYEE_MASTER 
		LEFT JOIN S_EMPLOYEE_CONTACT ON S_EMPLOYEE_CONTACT.PK_EMPLOYEE_MASTER = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER 
		LEFT JOIN S_EMPLOYEE_CAMPUS ON S_EMPLOYEE_CAMPUS.PK_EMPLOYEE_MASTER = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER 
		LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_EMPLOYEE_CAMPUS.PK_CAMPUS 
		LEFT JOIN S_EMPLOYEE_MASTER as S_EMPLOYEE_MASTER_SUP ON S_EMPLOYEE_MASTER_SUP.PK_EMPLOYEE_MASTER = S_EMPLOYEE_MASTER.PK_SUPERVISOR 
		LEFT JOIN Z_USER ON Z_USER.ID = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER AND PK_USER_TYPE = 2 
		LEFT JOIN Z_LANGUAGE ON Z_USER.PK_LANGUAGE = Z_LANGUAGE.PK_LANGUAGE 
		$table 
		WHERE S_EMPLOYEE_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER IN ($PK_EMPLOYEE_MASTER)  
		GROUP BY S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER ORDER BY S_EMPLOYEE_MASTER.ACTIVE DESC, S_EMPLOYEE_MASTER.LAST_NAME ASC , S_EMPLOYEE_MASTER.FIRST_NAME ASC";
		
		$access_query = " select 
		IF(ADMISSION_ACCESS = 0, 'No Access', IF(ADMISSION_ACCESS = 1, 'Read Only', IF(ADMISSION_ACCESS = 2, 'User Access', IF(ADMISSION_ACCESS = 3, 'Full Access', '') ))) as ADMISSION_ACCESS, 
		IF(REGISTRAR_ACCESS = 0, 'No Access', IF(REGISTRAR_ACCESS = 1, 'Read Only', IF(REGISTRAR_ACCESS = 2, 'User Access', IF(REGISTRAR_ACCESS = 3, 'Full Access', '') ))) as REGISTRAR_ACCESS, 
		IF(FINANCE_ACCESS = 0, 'No Access', IF(FINANCE_ACCESS = 1, 'Read Only', IF(FINANCE_ACCESS = 2, 'User Access', IF(FINANCE_ACCESS = 3, 'Full Access', '') ))) as FINANCE_ACCESS, 
		IF(ACCOUNTING_ACCESS = 0, 'No Access', IF(ACCOUNTING_ACCESS = 1, 'Read Only', IF(ACCOUNTING_ACCESS = 2, 'User Access', IF(ACCOUNTING_ACCESS = 3, 'Full Access', '') ))) as ACCOUNTING_ACCESS, 
		IF(PLACEMENT_ACCESS = 0, 'No Access', IF(PLACEMENT_ACCESS = 1, 'Read Only', IF(PLACEMENT_ACCESS = 2, 'User Access', IF(PLACEMENT_ACCESS = 3, 'Full Access', '') ))) as PLACEMENT_ACCESS, 
		
		IF(MANAGEMENT_ADMISSION = 1, 'Yes', IF(MANAGEMENT_ADMISSION = 0, 'No' ,'' )) as MANAGEMENT_ADMISSION, 
		IF(MANAGEMENT_REGISTRAR = 1, 'Yes', IF(MANAGEMENT_REGISTRAR = 0, 'No' ,'' )) as MANAGEMENT_REGISTRAR, 
		IF(MANAGEMENT_FINANCE = 1, 'Yes', IF(MANAGEMENT_FINANCE = 0, 'No' ,'' )) as MANAGEMENT_FINANCE, 
		IF(MANAGEMENT_ACCOUNTING = 1, 'Yes', IF(MANAGEMENT_ACCOUNTING = 0, 'No' ,'' )) as MANAGEMENT_ACCOUNTING, 
		IF(MANAGEMENT_PLACEMENT = 1, 'Yes', IF(MANAGEMENT_PLACEMENT = 0, 'No' ,'' )) as MANAGEMENT_PLACEMENT, 

		IF(REPORT_ADMISSION = 1, 'Yes', IF(REPORT_ADMISSION = 0, 'No' ,'' )) as REPORT_ADMISSION, 
		IF(REPORT_REGISTRAR = 1, 'Yes', IF(REPORT_REGISTRAR = 0, 'No' ,'' )) as REPORT_REGISTRAR, 
		IF(REPORT_FINANCE = 1, 'Yes', IF(REPORT_FINANCE = 0, 'No' ,'' )) as REPORT_FINANCE, 
		IF(REPORT_ACCOUNTING = 1, 'Yes', IF(REPORT_ACCOUNTING = 0, 'No' ,'' )) as REPORT_ACCOUNTING, 
		IF(REPORT_PLACEMENT = 1, 'Yes', IF(REPORT_PLACEMENT = 0, 'No' ,'' )) as REPORT_PLACEMENT, 
		
		IF(SETUP_ADMISSION = 1, 'Yes', IF(SETUP_ADMISSION = 0, 'No' ,'' )) as SETUP_ADMISSION, 
		IF(SETUP_REGISTRAR = 1, 'Yes', IF(SETUP_REGISTRAR = 0, 'No' ,'' )) as SETUP_REGISTRAR, 
		IF(SETUP_FINANCE = 1, 'Yes', IF(SETUP_FINANCE = 0, 'No' ,'' )) as SETUP_FINANCE, 
		IF(SETUP_ACCOUNTING = 1, 'Yes', IF(SETUP_ACCOUNTING = 0, 'No' ,'' )) as SETUP_ACCOUNTING, 
		IF(SETUP_PLACEMENT = 1, 'Yes', IF(SETUP_PLACEMENT = 0, 'No' ,'' )) as SETUP_PLACEMENT, 
		
		IF(REPORT_CUSTOM_REPORT = 1, 'Yes', IF(REPORT_CUSTOM_REPORT = 0, 'No' ,'' )) as REPORT_CUSTOM_REPORT, 
		
		IF(MANAGEMENT_ACCREDITATION = 1, 'Yes', IF(MANAGEMENT_ACCREDITATION = 0, 'No' ,'' )) as MANAGEMENT_ACCREDITATION, 
		IF(MANAGEMENT_TITLE_IV_SERVICER = 1, 'Yes', IF(MANAGEMENT_TITLE_IV_SERVICER = 0, 'No' ,'' )) as MANAGEMENT_TITLE_IV_SERVICER, 
		IF(MANAGEMENT_90_10 = 1, 'Yes', IF(MANAGEMENT_90_10 = 0, 'No' ,'' )) as MANAGEMENT_90_10, 
		IF(MANAGEMENT_FISAP = 1, 'Yes', IF(MANAGEMENT_FISAP = 0, 'No' ,'' )) as MANAGEMENT_FISAP, 
		IF(MANAGEMENT_IPEDS = 1, 'Yes', IF(MANAGEMENT_IPEDS = 0, 'No' ,'' )) as MANAGEMENT_IPEDS, 
		IF(MANAGEMENT_POPULATION_REPORT = 1, 'Yes', IF(MANAGEMENT_POPULATION_REPORT = 0, 'No' ,'' )) as MANAGEMENT_POPULATION_REPORT, 
		IF(MANAGEMENT_CUSTOM_QUERY = 1, 'Yes', IF(MANAGEMENT_CUSTOM_QUERY = 0, 'No' ,'' )) as MANAGEMENT_CUSTOM_QUERY, 

		IF(SETUP_SCHOOL = 1, 'Yes', IF(SETUP_SCHOOL = 0, 'No' ,'' )) as SETUP_SCHOOL, 
		IF(SETUP_STUDENT = 1, 'Yes', IF(SETUP_STUDENT = 0, 'No' ,'' )) as SETUP_STUDENT, 
		IF(SETUP_COMMUNICATION = 1, 'Yes', IF(SETUP_COMMUNICATION = 0, 'No' ,'' )) as SETUP_COMMUNICATION, 
		IF(SETUP_TASK_MANAGEMENT = 1, 'Yes', IF(SETUP_TASK_MANAGEMENT = 0, 'No' ,'' )) as SETUP_TASK_MANAGEMENT, 
		IF(SETUP_CONSOLIDATION_TOOL = 1, 'Yes', IF(SETUP_CONSOLIDATION_TOOL = 0, 'No' ,'' )) as SETUP_CONSOLIDATION_TOOL  
		
		FROM Z_USER_ACCESS WHERE 1=1 ";
		
		if($_POST['FORMAT'] == 1){
			require_once '../global/mpdf/vendor/autoload.php';
			
			$res = $db->Execute("SELECT PDF_LOGO,SCHOOL_NAME, ADDRESS, ADDRESS_1, CITY, STATE_CODE, ZIP, PHONE, WEBSITE FROM Z_ACCOUNT LEFT JOIN Z_STATES ON Z_STATES.PK_STATES = Z_ACCOUNT.PK_STATES WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
			$SCHOOL_NAME = $res->fields['SCHOOL_NAME'];
			$PDF_LOGO 	 = $res->fields['PDF_LOGO'];
			
			$logo = "";
			if($PDF_LOGO != '')
				$logo = '<img src="'.$PDF_LOGO.'" height="50px" />';
				
			$header = '<table width="100%" >
							<tr>
								<td width="20%" valign="top" >'.$logo.'</td>
								<td width="50%" valign="top" style="font-size:20px" >'.$SCHOOL_NAME.'</td>
								<td width="30%" valign="top" >
									<table width="100%" >
										<tr>
											<td width="100%" align="right" style="font-size:20px;border-bottom:1px solid #000;" ><b>User Access</b></td>
										</tr>
									</table>
								</td>
							</tr>
						</table>';
						
			$date = convert_to_user_date(date('Y-m-d H:i:s'),'l, F d, Y h:i A',$TIMEZONE,date_default_timezone_get());
						
			$footer = '<table width="100%" >
							<tr>
								<td width="33%" valign="top" style="font-size:10px;" ><i>'.$date.'</i></td>
								<td width="33%" valign="top" style="font-size:10px;" align="center" ></td>
								<td width="33%" valign="top" style="font-size:10px;" align="right" ><i>Page {PAGENO} of [pagetotal]</i></td>
							</tr>
						</table>';
			
			$mpdf = new \Mpdf\Mpdf([
				'margin_left' => 7,
				'margin_right' => 5,
				'margin_top' => 35,
				'margin_bottom' => 15,
				'margin_header' => 3,
				'margin_footer' => 10,
				'default_font_size' => 9,
			]);
			$mpdf->autoPageBreak = true;
			
			$mpdf->SetHTMLHeader($header);
			$mpdf->SetHTMLFooter($footer);
			
			$res = $db->Execute($query);			
			while (!$res->EOF) { 
				
				$res_dep = $db->Execute("select GROUP_CONCAT(DEPARTMENT SEPARATOR ', ') as DEPARTMENT FROM S_EMPLOYEE_DEPARTMENT, M_DEPARTMENT WHERE M_DEPARTMENT.PK_DEPARTMENT = S_EMPLOYEE_DEPARTMENT.PK_DEPARTMENT  AND PK_EMPLOYEE_MASTER = '".$res->fields['PK_EMPLOYEE_MASTER']."' ");
				
				$txt  = '<table border="0" cellspacing="0" cellpadding="3" width="70%">
							<tr>
								<td width="30%" ><b>Employee:</b></td>
								<td width="70%" >'.$res->fields['EMP_NAME'].'</td>
							</tr>
							<tr>
								<td ><b>Active:</b></td>
								<td >'.$res->fields['ACTIVE_1'].'</td>
							</tr>
							<tr>
								<td ><b>Campus(es):</b></td>
								<td >'.$res->fields['CAMPUS'].'</td>
							</tr>
							<tr>
								<td ><b>Department(s):</b></td>
								<td >'.$res_dep->fields['DEPARTMENT'].'</td>
							</tr>
							<tr>
								<td ><b>Job Title:</b></td>
								<td >'.$res->fields['TITLE'].'</td>
							</tr>
							<tr>
								<td ><b>Supervisior:</b></td>
								<td >'.$res->fields['SUP_NAME'].'</td>
							</tr>
							<tr>
								<td colspan="2" ><br /></td>
							</tr>
							<tr>
								<td ><b>Has Login:</b></td>
								<td >'.$res->fields['LOGIN_CREATED_1'].'</td>
							</tr>
							<tr>
								<td ><b>Email/User ID:</b></td>
								<td >'.$res->fields['USER_ID'].'</td>
							</tr>
							<tr>
								<td colspan="2" ><br /></td>
							</tr>
							<tr>
								<td ><b>Instructor:</b></td>
								<td >'.$res->fields['IS_FACULTY_1'].'</td>
							</tr>
							<tr>
								<td ><b>School Admin:</b></td>
								<td >'.$res->fields['IS_ADMIN_1'].'</td>
							</tr>
							<tr>
								<td ><b>D3 Access(Paid User):</b></td>
								<td >'.$res->fields['D3_ACCESS'].'</td>
							</tr>
						</table>
						<br />';
			
					$res_sec = $db->Execute($access_query." AND PK_USER = '".$res->fields['PK_USER']."' ");
					
					$txt .= '<table border="1" cellspacing="0" cellpadding="3" width="100%">
								<tr>
									<td width="20%" style="border-top:1px solid #000;border-bottom:1px solid #000;background-color: #ccc;">
										<b><i>Section</i></b>
									</td>
									<td width="20%" style="border-top:1px solid #000;border-bottom:1px solid #000;background-color: #ccc;" align="center" >
										<b><i>Access Type</i></b>
									</td>
									<td width="20%" style="border-top:1px solid #000;border-bottom:1px solid #000;background-color: #ccc;" align="center" >
										<b><i>Management</i></b>
									</td>
									<td width="20%" style="border-top:1px solid #000;border-bottom:1px solid #000;background-color: #ccc;" align="center" >
										<b><i>Reports</i></b>
									</td>
									<td width="20%" style="border-top:1px solid #000;border-bottom:1px solid #000;background-color: #ccc; " align="center" >
										<b><i>Setup</i></b>
									</td>
								</tr>';
					
					$ACCESS_TYPE 	= "";
					$MANAGEMENT 	= "";
					$REPORT 		= "";
					$SETUP 			= "";
					if($res_sec->fields['ADMISSION_ACCESS'] == "No Access")
						$ACCESS_TYPE = "background-color: #ddd;";
						
					if($res_sec->fields['MANAGEMENT_ADMISSION'] == "No")
						$MANAGEMENT = "background-color: #ddd;";
						
					if($res_sec->fields['REPORT_ADMISSION'] == "No")
						$REPORT = "background-color: #ddd;";
						
					if($res_sec->fields['SETUP_ADMISSION'] == "No")
						$SETUP = "background-color: #ddd;";
						
					$txt .= '<tr>
								<td >Admission</td>
								<td align="center" style="'.$ACCESS_TYPE.'" >'.$res_sec->fields['ADMISSION_ACCESS'].'</td>
								<td align="center" style="'.$MANAGEMENT.'" >'.$res_sec->fields['MANAGEMENT_ADMISSION'].'</td>
								<td align="center" style="'.$REPORT.'" >'.$res_sec->fields['REPORT_ADMISSION'].'</td>
								<td align="center" style="'.$SETUP.'" >'.$res_sec->fields['SETUP_ADMISSION'].'</td>
							</tr>';
							
					$ACCESS_TYPE 	= "";
					$MANAGEMENT 	= "";
					$REPORT 		= "";
					$SETUP 			= "";
					if($res_sec->fields['REGISTRAR_ACCESS'] == "No Access")
						$ACCESS_TYPE = "background-color: #ddd;";
						
					if($res_sec->fields['MANAGEMENT_REGISTRAR'] == "No")
						$MANAGEMENT = "background-color: #ddd;";
						
					if($res_sec->fields['REPORT_REGISTRAR'] == "No")
						$REPORT = "background-color: #ddd;";
						
					if($res_sec->fields['SETUP_REGISTRAR'] == "No")
						$SETUP = "background-color: #ddd;";
							
					$txt .= '<tr>
								<td >Registrar</td>
								<td align="center" style="'.$ACCESS_TYPE.'" >'.$res_sec->fields['REGISTRAR_ACCESS'].'</td>
								<td align="center" style="'.$MANAGEMENT.'" >'.$res_sec->fields['MANAGEMENT_REGISTRAR'].'</td>
								<td align="center" style="'.$REPORT.'" >'.$res_sec->fields['REPORT_REGISTRAR'].'</td>
								<td align="center" style="'.$SETUP.'"  >'.$res_sec->fields['SETUP_REGISTRAR'].'</td>
							</tr>';
							
					$ACCESS_TYPE 	= "";
					$MANAGEMENT 	= "";
					$REPORT 		= "";
					$SETUP 			= "";
					if($res_sec->fields['FINANCE_ACCESS'] == "No Access")
						$ACCESS_TYPE = "background-color: #ddd;";
						
					if($res_sec->fields['MANAGEMENT_FINANCE'] == "No")
						$MANAGEMENT = "background-color: #ddd;";
						
					if($res_sec->fields['REPORT_FINANCE'] == "No")
						$REPORT = "background-color: #ddd;";
						
					if($res_sec->fields['SETUP_FINANCE'] == "No")
						$SETUP = "background-color: #ddd;";
						
					$txt .= '<tr>
								<td >Finance</td>
								<td align="center" style="'.$ACCESS_TYPE.'" >'.$res_sec->fields['FINANCE_ACCESS'].'</td>
								<td align="center" style="'.$MANAGEMENT.'" >'.$res_sec->fields['MANAGEMENT_FINANCE'].'</td>
								<td align="center" style="'.$REPORT.'" >'.$res_sec->fields['REPORT_FINANCE'].'</td>
								<td align="center" style="'.$SETUP.'"  >'.$res_sec->fields['SETUP_FINANCE'].'</td>
							</tr>';
							
					$ACCESS_TYPE 	= "";
					$MANAGEMENT 	= "";
					$REPORT 		= "";
					$SETUP 			= "";
					if($res_sec->fields['ACCOUNTING_ACCESS'] == "No Access")
						$ACCESS_TYPE = "background-color: #ddd;";
						
					if($res_sec->fields['MANAGEMENT_ACCOUNTING'] == "No")
						$MANAGEMENT = "background-color: #ddd;";
						
					if($res_sec->fields['REPORT_ACCOUNTING'] == "No")
						$REPORT = "background-color: #ddd;";
						
					if($res_sec->fields['SETUP_ACCOUNTING'] == "No")
						$SETUP = "background-color: #ddd;";
						
					$txt .= '<tr>
								<td >Accounting</td>
								<td align="center" style="'.$ACCESS_TYPE.'" >'.$res_sec->fields['ACCOUNTING_ACCESS'].'</td>
								<td align="center" style="'.$MANAGEMENT.'" >'.$res_sec->fields['MANAGEMENT_ACCOUNTING'].'</td>
								<td align="center" style="'.$REPORT.'" >'.$res_sec->fields['REPORT_ACCOUNTING'].'</td>
								<td align="center" style="'.$SETUP.'"  >'.$res_sec->fields['SETUP_ACCOUNTING'].'</td>
							</tr>';
							
					$ACCESS_TYPE 	= "";
					$MANAGEMENT 	= "";
					$REPORT 		= "";
					$SETUP 			= "";
					if($res_sec->fields['PLACEMENT_ACCESS'] == "No Access")
						$ACCESS_TYPE = "background-color: #ddd;";
						
					if($res_sec->fields['MANAGEMENT_PLACEMENT'] == "No")
						$MANAGEMENT = "background-color: #ddd;";
						
					if($res_sec->fields['REPORT_PLACEMENT'] == "No")
						$REPORT = "background-color: #ddd;";
						
					if($res_sec->fields['SETUP_PLACEMENT'] == "No")
						$SETUP = "background-color: #ddd;";
						
					$txt .= '<tr>
								<td style="border-bottom:1px solid #000;" >Placement</td>
								<td style="border-bottom:1px solid #000;" align="center" style="'.$ACCESS_TYPE.'" >'.$res_sec->fields['PLACEMENT_ACCESS'].'</td>
								<td style="border-bottom:1px solid #000;" align="center" style="'.$MANAGEMENT.'" >'.$res_sec->fields['MANAGEMENT_PLACEMENT'].'</td>
								<td style="border-bottom:1px solid #000;" align="center" style="'.$REPORT.'" >'.$res_sec->fields['REPORT_PLACEMENT'].'</td>
								<td style="border-bottom:1px solid #000;" align="center" style="'.$SETUP.'"  >'.$res_sec->fields['SETUP_PLACEMENT'].'</td>
							</tr>';
							
					$txt .= '<tr>
								<td colspan="5" ><br /></td>
							</tr>
							<tr>
								<td style="border-top:1px solid #000;border-bottom:1px solid #000;background-color: #ccc;" >
									<b><i>Reports</i></b>
								</td>
								<td style="border-top:1px solid #000;border-bottom:1px solid #000;background-color: #ccc;" align="center" >
									<b><i></i></b>
								</td>
								<td style="border-top:1px solid #000;border-bottom:1px solid #000;background-color: #ccc;" align="center" >
									<b><i></i></b>
								</td>
								<td style="border-top:1px solid #000;border-bottom:1px solid #000;background-color: #ccc;" align="center" >
									<b><i>Reports</i></b>
								</td>
								<td style="border-top:1px solid #000;border-bottom:1px solid #000;background-color: #ccc; " align="center" >
									<b><i></i></b>
								</td>
							</tr>';
							
							$REPORT 			= "";
							if($res_sec->fields['REPORT_CUSTOM_REPORT'] == "No")
								$REPORT = "background-color: #ddd;";
						
							$txt .= '<tr>
										<td style="border-bottom:1px solid #000;" >General</td>
										<td style="border-bottom:1px solid #000;" align="center" ></td>
										<td style="border-bottom:1px solid #000;" align="center" ></td>
										<td style="border-bottom:1px solid #000;" align="center" style="'.$REPORT.'" >'.$res_sec->fields['REPORT_CUSTOM_REPORT'].'</td>
										<td style="border-bottom:1px solid #000;" align="center" ></td>
									</tr>';
					
					$txt .= '<tr>
								<td colspan="5" ><br /></td>
							</tr>
							<tr>
								<td style="border-top:1px solid #000;border-bottom:1px solid #000;background-color: #ccc;" >
									<b><i>Management</i></b>
								</td>
								<td style="border-top:1px solid #000;border-bottom:1px solid #000;background-color: #ccc;" align="center" >
									<b><i></i></b>
								</td>
								<td style="border-top:1px solid #000;border-bottom:1px solid #000;background-color: #ccc;" align="center" >
									<b><i>Management</i></b>
								</td>
								<td style="border-top:1px solid #000;border-bottom:1px solid #000;background-color: #ccc;" align="center" >
									<b><i></i></b>
								</td>
								<td style="border-top:1px solid #000;border-bottom:1px solid #000;background-color: #ccc; " align="center" >
									<b><i></i></b>
								</td>
							</tr>';
							
					$MANAGEMENT = "";
					if($res_sec->fields['MANAGEMENT_ACCREDITATION'] == "No")
						$MANAGEMENT = "background-color: #ddd;";
						
					$txt .= '<tr>
								<td >Accreditation</td>
								<td align="center" ></td>
								<td align="center" style="'.$MANAGEMENT.'" >'.$res_sec->fields['MANAGEMENT_ACCREDITATION'].'</td>
								<td align="center" ></td>
								<td align="center" ></td>
							</tr>';
							
					$MANAGEMENT = "";
					if($res_sec->fields['MANAGEMENT_TITLE_IV_SERVICER'] == "No")
						$MANAGEMENT = "background-color: #ddd;";
						
					$txt .= '<tr>
								<td >Title IV Servicer</td>
								<td align="center" ></td>
								<td align="center" style="'.$MANAGEMENT.'" >'.$res_sec->fields['MANAGEMENT_TITLE_IV_SERVICER'].'</td>
								<td align="center" ></td>
								<td align="center" ></td>
							</tr>';

					$MANAGEMENT = "";
					if($res_sec->fields['MANAGEMENT_90_10'] == "No")
						$MANAGEMENT = "background-color: #ddd;";
						
					$txt .= '<tr>
								<td >90/10</td>
								<td align="center" ></td>
								<td align="center" style="'.$MANAGEMENT.'" >'.$res_sec->fields['MANAGEMENT_90_10'].'</td>
								<td align="center" ></td>
								<td align="center" ></td>
							</tr>';
							
					$MANAGEMENT = "";
					if($res_sec->fields['MANAGEMENT_FISAP'] == "No")
						$MANAGEMENT = "background-color: #ddd;";
						
					$txt .= '<tr>
								<td >FISAP</td>
								<td align="center" ></td>
								<td align="center" style="'.$MANAGEMENT.'" >'.$res_sec->fields['MANAGEMENT_FISAP'].'</td>
								<td align="center" ></td>
								<td align="center" ></td>
							</tr>';
							
					$MANAGEMENT = "";
					if($res_sec->fields['MANAGEMENT_IPEDS'] == "No")
						$MANAGEMENT = "background-color: #ddd;";
						
					$txt .= '<tr>
								<td >IPEDS</td>
								<td align="center" ></td>
								<td align="center" style="'.$MANAGEMENT.'" >'.$res_sec->fields['MANAGEMENT_IPEDS'].'</td>
								<td align="center" ></td>
								<td align="center" ></td>
							</tr>';
							
					$MANAGEMENT = "";
					if($res_sec->fields['MANAGEMENT_POPULATION_REPORT'] == "No")
						$MANAGEMENT = "background-color: #ddd;";
						
					$txt .= '<tr>
								<td >Population Report</td>
								<td align="center" ></td>
								<td align="center" style="'.$MANAGEMENT.'" >'.$res_sec->fields['MANAGEMENT_POPULATION_REPORT'].'</td>
								<td align="center" ></td>
								<td align="center" ></td>
							</tr>';
							
					$MANAGEMENT = "";
					if($res_sec->fields['MANAGEMENT_CUSTOM_QUERY'] == "No")
						$MANAGEMENT = "background-color: #ddd;";
						
					$txt .= '<tr>
								<td >Custom</td>
								<td align="center" ></td>
								<td align="center" style="'.$MANAGEMENT.'" >'.$res_sec->fields['MANAGEMENT_CUSTOM_QUERY'].'</td>
								<td align="center" ></td>
								<td align="center" ></td>
							</tr>';
							
					$txt .= '<tr>
								<td colspan="5" ><br /></td>
							</tr>
							<tr>
								<td style="border-top:1px solid #000;border-bottom:1px solid #000;background-color: #ccc;" >
									<b><i>Setup</i></b>
								</td>
								<td style="border-top:1px solid #000;border-bottom:1px solid #000;background-color: #ccc;" align="center" >
									<b><i></i></b>
								</td>
								<td style="border-top:1px solid #000;border-bottom:1px solid #000;background-color: #ccc;" align="center" >
									<b><i></i></b>
								</td>
								<td style="border-top:1px solid #000;border-bottom:1px solid #000;background-color: #ccc;" align="center" >
									<b><i></i></b>
								</td>
								<td style="border-top:1px solid #000;border-bottom:1px solid #000;background-color: #ccc; " align="center" >
									<b><i>Setup</i></b>
								</td>
							</tr>';
							
					$SETUP = "";
					if($res_sec->fields['SETUP_SCHOOL'] == "No")
						$SETUP = "background-color: #ddd;";
						
					$txt .= '<tr>
								<td >School</td>
								<td align="center" ></td>
								<td align="center" ></td>
								<td align="center" ></td>
								<td align="center" style="'.$SETUP.'" >'.$res_sec->fields['SETUP_SCHOOL'].'</td>
							</tr>';
					$SETUP = "";
					if($res_sec->fields['SETUP_STUDENT'] == "No")
						$SETUP = "background-color: #ddd;";
						
					$txt .= '<tr>
								<td >Student</td>
								<td align="center" ></td>
								<td align="center" ></td>
								<td align="center" ></td>
								<td align="center" style="'.$SETUP.'" >'.$res_sec->fields['SETUP_STUDENT'].'</td>
							</tr>';
							
					$SETUP = "";
					if($res_sec->fields['SETUP_COMMUNICATION'] == "No")
						$SETUP = "background-color: #ddd;";
						
					$txt .= '<tr>
								<td >Communication</td>
								<td align="center" ></td>
								<td align="center" ></td>
								<td align="center" ></td>
								<td align="center" style="'.$SETUP.'" >'.$res_sec->fields['SETUP_COMMUNICATION'].'</td>
							</tr>';
							
					$SETUP = "";
					if($res_sec->fields['SETUP_TASK_MANAGEMENT'] == "No")
						$SETUP = "background-color: #ddd;";
						
					$txt .= '<tr>
								<td >Task Management</td>
								<td align="center" ></td>
								<td align="center" ></td>
								<td align="center" ></td>
								<td align="center" style="'.$SETUP.'" >'.$res_sec->fields['SETUP_TASK_MANAGEMENT'].'</td>
							</tr>';
							
					$SETUP = "";
					if($res_sec->fields['SETUP_CONSOLIDATION_TOOL'] == "No")
						$SETUP = "background-color: #ddd;";
						
					$txt .= '<tr>
								<td >Consolidation Tool</td>
								<td align="center" ></td>
								<td align="center" ></td>
								<td align="center" ></td>
								<td align="center" style="'.$SETUP.'" >'.$res_sec->fields['SETUP_CONSOLIDATION_TOOL'].'</td>
							</tr>';
						
					$txt .= '</table>';
				
				$mpdf->AddPage('','',1);
				$mpdf->AliasNbPageGroups('[pagetotal]');
				$mpdf->WriteHTML($txt);
				
				$res->MoveNext();
			}
			
			$mpdf->Output("User Access.pdf", 'D');
			exit;
		} else {
			include '../global/excel/Classes/PHPExcel/IOFactory.php';
			$cell1  = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");		
			define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

			$total_fields = 120;
			for($i = 0 ; $i <= $total_fields ; $i++){
				if($i <= 25)
					$cell[] = $cell1[$i];
				else {
					$j = floor($i / 26) - 1;
					$k = ($i % 26);
					//echo $j."--".$k."<br />";
					$cell[] = $cell1[$j].$cell1[$k];
				}	
			}

			$file_name 		= "User Access.xlsx";
			$dir 			= 'temp/';
			$inputFileType  = 'Excel2007';
			$outputFileName = $dir.$file_name; 
$outputFileName = str_replace(
	pathinfo($outputFileName,PATHINFO_FILENAME), pathinfo($outputFileName,PATHINFO_FILENAME)."_".$_SESSION['PK_USER']."_".time(),
	$outputFileName );  

			$objReader      = PHPExcel_IOFactory::createReader($inputFileType);
			$objReader->setIncludeCharts(TRUE);
			$objPHPExcel = new PHPExcel();
			$objWriter     = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			
			$line = 1;
			$index 	= -1;
			$heading[] = 'Last Name';
			$width[]   = 15;
			$heading[] = 'First Name';
			$width[]   = 15;
			$heading[] = 'Campus';
			$width[]   = 15;
			$heading[] = 'Department - Admissions';
			$width[]   = 15;
			$heading[] = 'Department - Accounting';
			$width[]   = 15;
			$heading[] = 'Department - Faculty';
			$width[]   = 15;
			$heading[] = 'Department - Finance';
			$width[]   = 15;
			$heading[] = 'Department - Management';
			$width[]   = 15;
			$heading[] = 'Department - Placement';
			$width[]   = 15;
			$heading[] = 'Department - Registrar';
			$width[]   = 15;
			$heading[] = 'Department - Student Services';
			$width[]   = 15;
			$heading[] = 'Title';
			$width[]   = 15;
			$heading[] = 'Has Login';
			$width[]   = 15;
			$heading[] = 'Email / User ID';
			$width[]   = 15;
			$heading[] = 'Language';
			$width[]   = 15;
			$heading[] = 'Instructor';
			$width[]   = 15;
			$heading[] = 'School Admin';
			$width[]   = 15;
			$heading[] = 'Active';
			$width[]   = 15;
			$heading[] = 'Turn Off New Assignments';
			$width[]   = 15;
			$heading[] = 'Section - Admissions';
			$width[]   = 15;
			$heading[] = 'Section - Registrar';
			$width[]   = 15;
			$heading[] = 'Section - Finance';
			$width[]   = 15;
			$heading[] = 'Section - Accounting';
			$width[]   = 15;
			$heading[] = 'Section - Placement';
			$width[]   = 15;
			$heading[] = 'Management - Admissions';
			$width[]   = 15;
			$heading[] = 'Management - Registrar';
			$width[]   = 15;
			$heading[] = 'Management - Finance';
			$width[]   = 15;
			$heading[] = 'Management - Accounting';
			$width[]   = 15;
			$heading[] = 'Management - Placement';
			$width[]   = 15;
			$heading[] = 'Management - Accreditation';
			$width[]   = 15;
			$heading[] = 'Management - Title IV Servicer';
			$width[]   = 15;
			$heading[] = 'Management - 90/10';
			$width[]   = 15;
			$heading[] = 'Management - FISAP';
			$width[]   = 15;
			$heading[] = 'Management - IPEDS';
			$width[]   = 15;
			$heading[] = 'Management - Population Report';
			$width[]   = 15;
			$heading[] = 'Management - Custom';
			$width[]   = 15;
			$heading[] = 'Reports - Admissions';
			$width[]   = 15;
			$heading[] = 'Reports - Registrar';
			$width[]   = 15;
			$heading[] = 'Reports - Finance';
			$width[]   = 15;
			$heading[] = 'Reports - Accounting';
			$width[]   = 15;
			$heading[] = 'Reports - Placement';
			$width[]   = 15;
			$heading[] = 'Reports - General';
			$width[]   = 15;
			$heading[] = 'Setup - School';
			$width[]   = 15;
			$heading[] = 'Setup - Admissions';
			$width[]   = 15;
			$heading[] = 'Setup - Student';
			$width[]   = 15;
			$heading[] = 'Setup - Finance';
			$width[]   = 15;
			$heading[] = 'Setup - Registrar';
			$width[]   = 15;
			$heading[] = 'Setup - Accounting';
			$width[]   = 15;
			$heading[] = 'Setup - Placement';
			$width[]   = 15;
			$heading[] = 'Setup - Communication';
			$width[]   = 15;
			$heading[] = 'Setup - Task Management';
			$width[]   = 15;
			$heading[] = 'Setup - Consolidation Tool';
			$width[]   = 15;
			
			$i = 0;
			foreach($heading as $title) {
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($title);
				$objPHPExcel->getActiveSheet()->getStyle($cell_no)->getFont()->setBold(true);
				$objPHPExcel->getActiveSheet()->getColumnDimension($cell[$index])->setWidth($width[$i]);
				
				$i++;
			}

			$res = $db->Execute($query);
			while (!$res->EOF) { 
				
				$line++;
				$index = -1;
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['LAST_NAME']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['FIRST_NAME']);

				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['CAMPUS']);
				
				$DEPARTMENT = "";
				$res_dep = $db->Execute("select PK_EMPLOYEE_DEPARTMENT FROM S_EMPLOYEE_DEPARTMENT, M_DEPARTMENT WHERE S_EMPLOYEE_DEPARTMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_EMPLOYEE_MASTER = '".$res->fields['PK_EMPLOYEE_MASTER']."' AND M_DEPARTMENT.PK_DEPARTMENT = S_EMPLOYEE_DEPARTMENT.PK_DEPARTMENT AND PK_DEPARTMENT_MASTER = 2");
				if($res_dep->RecordCount() > 0)
					$DEPARTMENT = "Yes";
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($DEPARTMENT);
				
				$DEPARTMENT = "";
				$res_dep = $db->Execute("select PK_EMPLOYEE_DEPARTMENT FROM S_EMPLOYEE_DEPARTMENT, M_DEPARTMENT WHERE S_EMPLOYEE_DEPARTMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_EMPLOYEE_MASTER = '".$res->fields['PK_EMPLOYEE_MASTER']."' AND M_DEPARTMENT.PK_DEPARTMENT = S_EMPLOYEE_DEPARTMENT.PK_DEPARTMENT AND PK_DEPARTMENT_MASTER = 1");
				if($res_dep->RecordCount() > 0)
					$DEPARTMENT = "Yes";
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($DEPARTMENT);
				
				$DEPARTMENT = "";
				$res_dep = $db->Execute("select PK_EMPLOYEE_DEPARTMENT FROM S_EMPLOYEE_DEPARTMENT, M_DEPARTMENT WHERE S_EMPLOYEE_DEPARTMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_EMPLOYEE_MASTER = '".$res->fields['PK_EMPLOYEE_MASTER']."' AND M_DEPARTMENT.PK_DEPARTMENT = S_EMPLOYEE_DEPARTMENT.PK_DEPARTMENT AND PK_DEPARTMENT_MASTER = 3");
				if($res_dep->RecordCount() > 0)
					$DEPARTMENT = "Yes";
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($DEPARTMENT);
				
				$DEPARTMENT = "";
				$res_dep = $db->Execute("select PK_EMPLOYEE_DEPARTMENT FROM S_EMPLOYEE_DEPARTMENT, M_DEPARTMENT WHERE S_EMPLOYEE_DEPARTMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_EMPLOYEE_MASTER = '".$res->fields['PK_EMPLOYEE_MASTER']."' AND M_DEPARTMENT.PK_DEPARTMENT = S_EMPLOYEE_DEPARTMENT.PK_DEPARTMENT AND PK_DEPARTMENT_MASTER = 4");
				if($res_dep->RecordCount() > 0)
					$DEPARTMENT = "Yes";
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($DEPARTMENT);
				
				$DEPARTMENT = "";
				$res_dep = $db->Execute("select PK_EMPLOYEE_DEPARTMENT FROM S_EMPLOYEE_DEPARTMENT, M_DEPARTMENT WHERE S_EMPLOYEE_DEPARTMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_EMPLOYEE_MASTER = '".$res->fields['PK_EMPLOYEE_MASTER']."' AND M_DEPARTMENT.PK_DEPARTMENT = S_EMPLOYEE_DEPARTMENT.PK_DEPARTMENT AND PK_DEPARTMENT_MASTER = 5");
				if($res_dep->RecordCount() > 0)
					$DEPARTMENT = "Yes";
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($DEPARTMENT);
				
				$DEPARTMENT = "";
				$res_dep = $db->Execute("select PK_EMPLOYEE_DEPARTMENT FROM S_EMPLOYEE_DEPARTMENT, M_DEPARTMENT WHERE S_EMPLOYEE_DEPARTMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_EMPLOYEE_MASTER = '".$res->fields['PK_EMPLOYEE_MASTER']."' AND M_DEPARTMENT.PK_DEPARTMENT = S_EMPLOYEE_DEPARTMENT.PK_DEPARTMENT AND PK_DEPARTMENT_MASTER = 6");
				if($res_dep->RecordCount() > 0)
					$DEPARTMENT = "Yes";
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($DEPARTMENT);
				
				$DEPARTMENT = "";
				$res_dep = $db->Execute("select PK_EMPLOYEE_DEPARTMENT FROM S_EMPLOYEE_DEPARTMENT, M_DEPARTMENT WHERE S_EMPLOYEE_DEPARTMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_EMPLOYEE_MASTER = '".$res->fields['PK_EMPLOYEE_MASTER']."' AND M_DEPARTMENT.PK_DEPARTMENT = S_EMPLOYEE_DEPARTMENT.PK_DEPARTMENT AND PK_DEPARTMENT_MASTER = 7");
				if($res_dep->RecordCount() > 0)
					$DEPARTMENT = "Yes";
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($DEPARTMENT);
				
				$DEPARTMENT = "";
				$res_dep = $db->Execute("select PK_EMPLOYEE_DEPARTMENT FROM S_EMPLOYEE_DEPARTMENT, M_DEPARTMENT WHERE S_EMPLOYEE_DEPARTMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_EMPLOYEE_MASTER = '".$res->fields['PK_EMPLOYEE_MASTER']."' AND M_DEPARTMENT.PK_DEPARTMENT = S_EMPLOYEE_DEPARTMENT.PK_DEPARTMENT AND PK_DEPARTMENT_MASTER = 8");
				if($res_dep->RecordCount() > 0)
					$DEPARTMENT = "Yes";
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($DEPARTMENT);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['TITLE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['LOGIN_CREATED_1']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['USER_ID']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['LANGUAGE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['IS_FACULTY_1']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['IS_ADMIN_1']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['ACTIVE_1']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res->fields['TURN_OFF_ASSIGNMENTS']);
				
				$res_sec = $db->Execute($access_query." AND PK_USER = '".$res->fields['PK_USER']."' ");
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_sec->fields['ADMISSION_ACCESS']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_sec->fields['REGISTRAR_ACCESS']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_sec->fields['FINANCE_ACCESS']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_sec->fields['ACCOUNTING_ACCESS']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_sec->fields['PLACEMENT_ACCESS']);
				
				/////////////////////////////
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_sec->fields['MANAGEMENT_ADMISSION']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_sec->fields['MANAGEMENT_REGISTRAR']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_sec->fields['MANAGEMENT_FINANCE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_sec->fields['MANAGEMENT_ACCOUNTING']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_sec->fields['MANAGEMENT_PLACEMENT']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_sec->fields['MANAGEMENT_ACCREDITATION']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_sec->fields['MANAGEMENT_TITLE_IV_SERVICER']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_sec->fields['MANAGEMENT_90_10']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_sec->fields['MANAGEMENT_FISAP']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_sec->fields['MANAGEMENT_IPEDS']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_sec->fields['MANAGEMENT_POPULATION_REPORT']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_sec->fields['MANAGEMENT_CUSTOM_QUERY']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_sec->fields['REPORT_ADMISSION']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_sec->fields['REPORT_REGISTRAR']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_sec->fields['REPORT_FINANCE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_sec->fields['REPORT_ACCOUNTING']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_sec->fields['REPORT_PLACEMENT']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_sec->fields['REPORT_CUSTOM_REPORT']);
				
				//////////////////////////////////////////////
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_sec->fields['SETUP_SCHOOL']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_sec->fields['SETUP_ADMISSION']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_sec->fields['SETUP_STUDENT']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_sec->fields['SETUP_FINANCE']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_sec->fields['SETUP_REGISTRAR']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_sec->fields['SETUP_ACCOUNTING']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_sec->fields['SETUP_PLACEMENT']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_sec->fields['SETUP_COMMUNICATION']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_sec->fields['SETUP_TASK_MANAGEMENT']);
				
				$index++;
				$cell_no = $cell[$index].$line;
				$objPHPExcel->getActiveSheet()->getCell($cell_no)->setValue($res_sec->fields['SETUP_CONSOLIDATION_TOOL']);
				
				$res->MoveNext();
			}
			
			$objWriter->save($outputFileName);
			$objPHPExcel->disconnectWorksheets();
			header("location:".$outputFileName);
			exit;
		}
	}
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
	<? require_once("css.php"); ?>
	<title><?=MNU_EMPLOYEE_REPORT?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}
		#advice-required-entry-PK_CAMPUS {position: absolute;top: 55px;width: 142px}
		
		.dropdown-menu>li>a { white-space: nowrap; }
		.option_red > a > label{color:red !important}
		
	</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor">
						<? echo MNU_EMPLOYEE_REPORT ?> </h4>
                    </div>
                </div>
				<form class="floating-labels" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off" >
					<div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body">
									<div class="row" style="margin-bottom:20px" >
										<div class="col-md-2">
											<?=REPORT_TYPE?>
											<select id="REPORT_TYPE" name="REPORT_TYPE" class="form-control" onchange="show_fields()" >
												<option value="1">Employees</option>
												<option value="2">User Access</option>
											</select>
										</div>
										
										<div class="col-md-2 ">
											<br />
											<button type="button" onclick="submit_form(1)" id="btn_1" class="btn waves-effect waves-light btn-info"><?=PDF?></button>
											<button type="button" onclick="submit_form(2)" id="btn_2" class="btn waves-effect waves-light btn-info"><?=EXCEL?></button>
										</div>
										
										<div class="col-md-8 text-right">
											<button type="button" onclick="window.location.href='manage_employee'" class="btn waves-effect waves-light btn-info"><?=RETURN_TO_EMPLOYEE ?></button>
										</div>
										
									</div>
									<hr style="border-top: 1px solid #ccc;" />
									
									<div class="row" style="margin-bottom:20px" >
										<div class="col-md-2" id="PK_CAMPUS_DIV" style="display:none" >
											<?=CAMPUS?>
											<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control required-entry" onchange="clear_search()" >
												<? /* Ticket # 1753 */
												$res_type = $db->Execute("select CAMPUS_CODE, PK_CAMPUS, ACTIVE from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, CAMPUS_CODE ASC");
												while (!$res_type->EOF) { 
													$option_label = $res_type->fields['CAMPUS_CODE'];
													if($res_type->fields['ACTIVE'] == 0)
														$option_label .= " (Inactive)"; 
														?>
													<option value="<?=$res_type->fields['PK_CAMPUS']?>" <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
												<?	$res_type->MoveNext();
												} /* Ticket # 1753 */ ?>
											</select>
										</div>
										
										<div class="col-md-2" id="PK_DEPARTMENT_DIV" style="display:none" >
											<?=DEPARTMENT?>
											<select id="PK_DEPARTMENT" name="PK_DEPARTMENT[]" multiple class="form-control" onchange="clear_search()" >
												<? $res_type = $db->Execute("select * from M_DEPARTMENT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, DEPARTMENT ASC");
												while (!$res_type->EOF) { 
													$option_label = $res_type->fields['DEPARTMENT'];
													if($res_type->fields['ACTIVE'] == 0)
														$option_label .= " (Inactive)";  ?>
													<option value="<?=$res_type->fields['PK_DEPARTMENT'] ?>" <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-2" id="SUPERVISOR_DIV" style="display:none" >
											<?=SUPERVISOR?>
											<select id="PK_SUPERVISOR" name="PK_SUPERVISOR[]" multiple class="form-control" onchange="clear_search()" >
												<? $res_type = $db->Execute("select CONCAT(LAST_NAME,', ',FIRST_NAME) AS EMP_NAME, PK_EMPLOYEE_MASTER, ACTIVE from S_EMPLOYEE_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, CONCAT(LAST_NAME,', ',FIRST_NAME) ASC");
												while (!$res_type->EOF) { 
													$option_label = $res_type->fields['EMP_NAME'];
													if($res_type->fields['ACTIVE'] == 0)
														$option_label .= " (Inactive)";  ?>
													<option value="<?=$res_type->fields['PK_EMPLOYEE_MASTER'] ?>" <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
										
										<div class="col-md-1 form-group" id="FULL_PART_TIME_DIV" style="display:none" >
											<?=FULL_PART_TIME?>
											<select id="FULL_PART_TIME" name="FULL_PART_TIME" class="form-control" onchange="clear_search()" >
												<option value="" ></option>
												<option value="1" >Full Time</option>
												<option value="2" >Part Time</option>
											</select>
										</div>
										
										<div class="col-md-1 form-group" id="HAS_LOGIN_DIV" style="display:none" >
											<?=HAS_LOGIN?>
											<select id="HAS_LOGIN" name="HAS_LOGIN" class="form-control" onchange="clear_search()" >
												<option value="" ></option>
												<option value="2" >No</option>
												<option value="1" >Yes</option>
											</select>
										</div>
										
										<div class="col-md-1 form-group" id="INSTRUCTOR_DIV" style="display:none" >
											<?=INSTRUCTOR?>
											<select id="INSTRUCTOR" name="INSTRUCTOR" class="form-control" onchange="clear_search()" >
												<option value="" ></option>
												<option value="2" >No</option>
												<option value="1" >Yes</option>
											</select>
										</div>
										
										<div class="col-md-1 form-group" id="SCHOOL_ADMIN_DIV" style="display:none" >
											<?=SCHOOL_ADMIN?>
											<select id="SCHOOL_ADMIN" name="SCHOOL_ADMIN" class="form-control" onchange="clear_search()" >
												<option value="" ></option>
												<option value="2" >No</option>
												<option value="1" >Yes</option>
											</select>
										</div>
										
										<div class="col-md-1 form-group" id="ACTIVE_DIV" style="display:none" >
											<?=ACTIVE?>
											<select id="ACTIVE" name="ACTIVE" class="form-control" onchange="clear_search()" >
												<option value="" ></option>
												<option value="2" >No</option>
												<option value="1" >Yes</option>
											</select>
										</div>
										
										<div class="col-md-1 ">
											<br />
											<button type="button" onclick="search(1)" id="btn_search" class="btn waves-effect waves-light btn-info" style="display:none" ><?=SEARCH?></button>
											<input type="hidden" name="FORMAT" id="FORMAT" >
										</div>
									</div>
									
									<br />
									<div id="PHONE_DIV" >
										
									</div>
								</div>
							</div>
						</div>
					</div>
				</form>
            </div>
        </div>
        <? require_once("footer.php"); ?>
    </div>
   
	<? require_once("js.php"); ?>
	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />

	<script type="text/javascript">
	jQuery(document).ready(function($) { 
		jQuery('.date').datepicker({
			todayHighlight: true,
			orientation: "bottom auto"
		});
		
		show_fields()
	});
	</script>
	
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
		function show_fields(){
			var val = document.getElementById('REPORT_TYPE').value
			document.getElementById('PK_CAMPUS_DIV').style.display 			= 'none';
			document.getElementById('PK_DEPARTMENT_DIV').style.display 		= 'none';
			document.getElementById('SUPERVISOR_DIV').style.display 		= 'none';
			document.getElementById('FULL_PART_TIME_DIV').style.display 	= 'none';
			document.getElementById('HAS_LOGIN_DIV').style.display 			= 'none';
			document.getElementById('INSTRUCTOR_DIV').style.display 		= 'none';
			document.getElementById('SCHOOL_ADMIN_DIV').style.display 		= 'none';
			document.getElementById('ACTIVE_DIV').style.display 			= 'none';
			document.getElementById('btn_search').style.display 			= 'none';
			document.getElementById('PHONE_DIV').innerHTML					= '';
			
			document.getElementById('btn_1').style.display 			= 'none';
			document.getElementById('btn_2').style.display 			= 'none';
		
			if(val == 1 || val == 2) {
				document.getElementById('PK_CAMPUS_DIV').style.display 			= 'inline';
				document.getElementById('PK_DEPARTMENT_DIV').style.display 		= 'inline';
				document.getElementById('SUPERVISOR_DIV').style.display 		= 'inline';
				document.getElementById('FULL_PART_TIME_DIV').style.display 	= 'inline';
				document.getElementById('HAS_LOGIN_DIV').style.display 			= 'inline';
				document.getElementById('INSTRUCTOR_DIV').style.display 		= 'inline';
				document.getElementById('SCHOOL_ADMIN_DIV').style.display 		= 'inline';
				document.getElementById('ACTIVE_DIV').style.display 			= 'inline';
				document.getElementById('btn_search').style.display 			= 'inline';
			}
		}
		
		function clear_search(){
			document.getElementById('PHONE_DIV').innerHTML = '';
			show_btn()
		}
		
		function search(type){
			if(type == 0) {
				document.getElementById('PHONE_DIV').innerHTML = ''
			} else {
				var valid = new Validation('form1', {onSubmit:false});
				var result = valid.validate();
				if(result == true){ 
					jQuery(document).ready(function($) {
						if(document.getElementById('REPORT_TYPE').value == 1 || document.getElementById('REPORT_TYPE').value == 2)
							var data  = 'PK_CAMPUS='+$('#PK_CAMPUS').val()+'&PK_DEPARTMENT='+$('#PK_DEPARTMENT').val()+'&PK_SUPERVISOR='+$('#PK_SUPERVISOR').val()+'&FULL_PART_TIME='+$('#FULL_PART_TIME').val()+'&HAS_LOGIN='+$('#HAS_LOGIN').val()+'&INSTRUCTOR='+$('#INSTRUCTOR').val()+'&SCHOOL_ADMIN='+$('#SCHOOL_ADMIN').val()+'&ACTIVE='+$('#ACTIVE').val()+'&show_check=1';
							
						//alert(data)
						var value = $.ajax({
							url: "ajax_search_employee_for_reports",	
							type: "POST",		 
							data: data,		
							async: false,
							cache: false,
							success: function (data) {	
								document.getElementById('PHONE_DIV').innerHTML = data
								show_btn()
							}		
						}).responseText;
					});
				}
			}
		}
		
		function fun_select_all(){
			var str = '';
			if(document.getElementById('SEARCH_SELECT_ALL').checked == true)
				str = true;
			else
				str = false;
				
			var PK_EMPLOYEE_MASTER = document.getElementsByName('PK_EMPLOYEE_MASTER[]')
			for(var i = 0 ; i < PK_EMPLOYEE_MASTER.length ; i++){
				PK_EMPLOYEE_MASTER[i].checked = str
			}
			get_count()
		}
		
		function show_btn(){
			
			var flag = 0;
			var PK_EMPLOYEE_MASTER = document.getElementsByName('PK_EMPLOYEE_MASTER[]')
			for(var i = 0 ; i < PK_EMPLOYEE_MASTER.length ; i++){
				if(PK_EMPLOYEE_MASTER[i].checked == true) {
					flag++;
					break;
				}
			}
			
			if(flag == 1) {
				document.getElementById('btn_1').style.display = 'inline';
				document.getElementById('btn_2').style.display = 'inline';
			} else {
				document.getElementById('btn_1').style.display = 'none';
				document.getElementById('btn_2').style.display = 'none';
			}
		}
		
		function get_count(){
			var tot = 0
			var PK_EMPLOYEE_MASTER = document.getElementsByName('PK_EMPLOYEE_MASTER[]')
			for(var i = 0 ; i < PK_EMPLOYEE_MASTER.length ; i++){
				if(PK_EMPLOYEE_MASTER[i].checked == true)
					tot++;
			}
			document.getElementById('SELECTED_COUNT').innerHTML = tot
			show_btn()
		}
		
		
	</script>
	
	<script type="text/javascript" src="https://code.jquery.com/jquery-migrate-1.4.1.min.js"></script>
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
	function submit_form(val){
		jQuery(document).ready(function($) {
			var valid = new Validation('form1', {onSubmit:false});
			var result = valid.validate();
			if(result == true){ 
				document.getElementById('FORMAT').value = val
				document.form1.submit();
			}
		});
	}
	</script>
	
	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css"/>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#PK_CAMPUS').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=CAMPUS?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=CAMPUS?> selected'
		});
		
		$('#PK_DEPARTMENT').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=DEPARTMENT?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=DEPARTMENT?> selected'
		});
		
		$('#PK_SUPERVISOR').multiselect({
			includeSelectAllOption: true,
			allSelectedText: 'All <?=SUPERVISOR?>',
			nonSelectedText: '',
			numberDisplayed: 2,
			nSelectedText: '<?=SUPERVISOR?> selected'
		});
	});
	</script>

</body>

</html>