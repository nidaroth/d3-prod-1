<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("check_access.php");

$res = $db->Execute("SELECT ENABLE_LSQ FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
if($res->fields['ENABLE_LSQ'] == 0) {
	header("location:../index");
	exit;
}

require_once("../global/lsq.php"); 

if(!empty($_POST)){

	$data_retrived 	= 0;
	$PageIndex 		= 1;
	$PageSize  		= 100;
	
	$EXCEL_MAP_MASTER['HEADING_ROW_NO'] = $_POST['HEADING_ROW_NO'];
	$EXCEL_MAP_MASTER['FILE_NAME'] 		= 'LSQ Import';
	$EXCEL_MAP_MASTER['CREATED_BY'] 	= $_SESSION['PK_USER'];
	$EXCEL_MAP_MASTER['CREATED_ON'] 	= date("Y-m-d H:i");
	db_perform('Z_EXCEL_MAP_MASTER', $EXCEL_MAP_MASTER, 'insert');
	$PK_MAP_MASTER = $db->insert_ID();
	
	do{
		$stud_data = get_ls_student_data($_POST['START_DATE'], $_POST['END_DATE'], $PageIndex, $PageSize);
		
		//echo "<pre><br />--------------------------------------<br />data_retrived: ".$data_retrived."<br />RecordCount: ".$stud_data->RecordCount."<br />";print_r($stud_data);
		//echo "<pre>";print_r($stud_data);
		
		foreach($stud_data->Leads as $Leads) {
			foreach($Leads as $Lead) {
				//echo "<pre>";print_r($Lead);exit;
				
				$STUDENT_MASTER  	= array();
				$STUDENT_CONTACT 	= array();
				$STUDENT_ENROLLMENT = array();
				$STUDENT_ACADEMICS	= array();
				$STUDENT_OTHER_EDU	= array();
				
				$PhotoUrl = '';
				
				$mx_High_School_City_and_State 	= '';
				$mx_High_School_Attended 		= '';
				$mx_Year_Graduated 				= '';
				
				foreach($Lead as $LeadPropertyList) {
					//echo $LeadPropertyList->Attribute.": ".trim($LeadPropertyList->Value)."<br />";
					
					if($LeadPropertyList->Attribute == "ProspectID")
						$ProspectID = trim($LeadPropertyList->Value);
					else if($LeadPropertyList->Attribute == "FirstName")
						$STUDENT_MASTER['FIRST_NAME'] = trim($LeadPropertyList->Value);
					else if($LeadPropertyList->Attribute == "mx_Middle_Name")
						$STUDENT_MASTER['MIDDLE_NAME'] = trim($LeadPropertyList->Value);
					else if($LeadPropertyList->Attribute == "LastName")
						$STUDENT_MASTER['LAST_NAME'] = trim($LeadPropertyList->Value);
					else if($LeadPropertyList->Attribute == "PhotoUrl")
						$PhotoUrl = trim($LeadPropertyList->Value);
					else if($LeadPropertyList->Attribute == "mx_Are_you_a_US_citizen") {
						if(strtolower(trim($LeadPropertyList->Value)) == 'yes') {
							$STUDENT_MASTER['PK_COUNTRY_CITIZEN'] 	= 1;
							$STUDENT_MASTER['PK_CITIZENSHIP'] 		= 1;
						}
					} else if($LeadPropertyList->Attribute == "DoNotEmail") {
						if(trim($LeadPropertyList->Value) == 1)
							$STUDENT_CONTACT['USE_EMAIL'] = 0;
						else
							$STUDENT_CONTACT['USE_EMAIL'] = 1;
					} else if($LeadPropertyList->Attribute == "DoNotCall") {
						if(trim($LeadPropertyList->Value) == 1)
							$STUDENT_CONTACT['OPT_OUT'] = 1;
						else
							$STUDENT_CONTACT['OPT_OUT'] = 0;
					} else if($LeadPropertyList->Attribute == "OwnerIdName") {
						$name = trim($LeadPropertyList->Value);
						
						$res_l = $db->Execute("select PK_EMPLOYEE_MASTER from S_EMPLOYEE_MASTER  WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND (TRIM(CONCAT(TRIM(FIRST_NAME),' ',TRIM(LAST_NAME))) = '$name' OR TRIM(CONCAT(TRIM(LAST_NAME),' ',TRIM(FIRST_NAME))) = '$name' ) ORDER BY ACTIVE DESC");
						$STUDENT_ENROLLMENT['PK_REPRESENTATIVE'] = $res_l->fields['PK_EMPLOYEE_MASTER'];
					} else if($LeadPropertyList->Attribute == "mx_Preferred_Start_Date") {
						/*$start_date_arr = explode(" ",trim($LeadPropertyList->Value));
						$month 	= strtolower($start_date_arr[0]);
						$year 	= strtolower($start_date_arr[1]);
						
						if($month == "january")
							$month = "01";
						else if($month == "february")
							$month = "02";
						else if($month == "march")
							$month = "03";
						else if($month == "april")
							$month = "04";
						else if($month == "may")
							$month = "05";
						else if($month == "june")
							$month = "06";
						else if($month == "july")
							$month = "07";
						else if($month == "august")
							$month = "08";
						else if($month == "september")
							$month = "09";
						else if($month == "october")
							$month = "10";
						else if($month == "november")
							$month = "11";
						else if($month == "december")
							$month = "12";
							
						$res_l = $db->Execute("select PK_TERM_MASTER from S_TERM_MASTER  WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND MONTH(BEGIN_DATE) = '$month' AND YEAR(BEGIN_DATE) = '$year' ORDER BY ACTIVE DESC");*/
						
						$TERM_DESCRIPTION = trim($LeadPropertyList->Value);
						$res_l = $db->Execute("select PK_TERM_MASTER from S_TERM_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TRIM(TERM_DESCRIPTION) = '$TERM_DESCRIPTION' ORDER BY ACTIVE DESC");
						$STUDENT_ENROLLMENT['PK_TERM_MASTER'] = $res_l->fields['PK_TERM_MASTER'];
					} else if($LeadPropertyList->Attribute == "mx_High_School_City_and_State"){
						$mx_High_School_City_and_State = trim($LeadPropertyList->Value);
						
						$other_city_state = explode(",", trim($LeadPropertyList->Value));
						$other_city 	= trim($other_city_state[0]);
						$other_state 	= trim($other_city_state[1]);
						
						$res_l = $db->Execute("select PK_STATES, PK_COUNTRY from Z_STATES WHERE (trim(STATE_CODE) = '$other_state' or trim(STATE_NAME) = '$other_state') ");
						$STUDENT_OTHER_EDU['PK_STATE'] 	= trim($res_l->fields['PK_STATES']);
						$STUDENT_OTHER_EDU['CITY'] 		= trim($other_city);
						
					} else if($LeadPropertyList->Attribute == "mx_High_School_Attended") {
						$mx_High_School_Attended = trim($LeadPropertyList->Value);
						
						$STUDENT_OTHER_EDU['SCHOOL_NAME'] = trim($LeadPropertyList->Value);
					} else if($LeadPropertyList->Attribute == "mx_Year_Graduated") {
						$mx_Year_Graduated = trim($LeadPropertyList->Value);
						
						if($mx_Year_Graduated != ''){
							$STUDENT_OTHER_EDU['GRADUATED'] 	 = 1;
							$STUDENT_OTHER_EDU['GRADUATED_DATE'] = date("Y-m-d", strtotime(trim($LeadPropertyList->Value)));
						}
					}
					else if($LeadPropertyList->Attribute == "Origin")
						$STUDENT_ENROLLMENT['PK_LEAD_CONTACT_SOURCE'] = trim($LeadPropertyList->Value);
						
					else if($LeadPropertyList->Attribute == "EmailAddress")
						$STUDENT_CONTACT['EMAIL'] = trim($LeadPropertyList->Value);
					else if($LeadPropertyList->Attribute == "mx_Home_Phone_Number") {
						$STUDENT_CONTACT['HOME_PHONE'] = '';					
						$HOME_PHONE = trim($LeadPropertyList->Value);
						if($HOME_PHONE != '') {
							$HOME_PHONE_arr = explode("-", $HOME_PHONE);
							if(count($HOME_PHONE_arr) == 2)
								$STUDENT_CONTACT['HOME_PHONE'] = $HOME_PHONE_arr[1];
							else
								$STUDENT_CONTACT['HOME_PHONE'] = $HOME_PHONE_arr[0];
						}
					} else if($LeadPropertyList->Attribute == "Phone") {
						$STUDENT_CONTACT['OTHER_PHONE'] = '';											
						$OTHER_PHONE = trim($LeadPropertyList->Value);
						if($OTHER_PHONE != '') {
							$OTHER_PHONE_arr = explode("-", $OTHER_PHONE);
							if(count($OTHER_PHONE_arr) == 2)
								$STUDENT_CONTACT['OTHER_PHONE'] = $OTHER_PHONE_arr[1];
							else
								$STUDENT_CONTACT['OTHER_PHONE'] = $OTHER_PHONE_arr[0];
						}
						
					} else if($LeadPropertyList->Attribute == "Mobile") {
						$STUDENT_CONTACT['CELL_PHONE'] = '';							
						$CELL_PHONE = trim($LeadPropertyList->Value);
						if($CELL_PHONE != '') {
							$CELL_PHONE_arr = explode("-", $CELL_PHONE);
							if(count($CELL_PHONE_arr) == 2)
								$STUDENT_CONTACT['CELL_PHONE'] = $CELL_PHONE_arr[1];
							else
								$STUDENT_CONTACT['CELL_PHONE'] = $CELL_PHONE_arr[0];
						}
					} else if($LeadPropertyList->Attribute == "Source")
						$STUDENT_ENROLLMENT['PK_LEAD_SOURCE'] = trim($LeadPropertyList->Value);
					else if($LeadPropertyList->Attribute == "mx_Street1")
						$STUDENT_CONTACT['ADDRESS'] = trim($LeadPropertyList->Value);
					else if($LeadPropertyList->Attribute == "mx_Street2")
						$STUDENT_CONTACT['ADDRESS_1'] = trim($LeadPropertyList->Value);
					else if($LeadPropertyList->Attribute == "mx_City")
						$STUDENT_CONTACT['CITY'] = trim($LeadPropertyList->Value);
					else if($LeadPropertyList->Attribute == "mx_State")
						$STUDENT_CONTACT['PK_STATES'] = trim($LeadPropertyList->Value);
					else if($LeadPropertyList->Attribute == "mx_Country")
						$STUDENT_CONTACT['PK_COUNTRY'] = trim($LeadPropertyList->Value);
					else if($LeadPropertyList->Attribute == "mx_Zip")
						$STUDENT_CONTACT['ZIP'] = trim($LeadPropertyList->Value);
					else if($LeadPropertyList->Attribute == "mx_Date_of_Birth")
						$STUDENT_MASTER['DATE_OF_BIRTH'] = trim($LeadPropertyList->Value);
					else if($LeadPropertyList->Attribute == "mx_Program")
						$STUDENT_ENROLLMENT['PK_CAMPUS_PROGRAM'] = trim($LeadPropertyList->Value);
					else if($LeadPropertyList->Attribute == "mx_Marital_Status")
						$STUDENT_MASTER['PK_MARITAL_STATUS'] = trim($LeadPropertyList->Value);
					else if($LeadPropertyList->Attribute == "mx_Gender")
						$STUDENT_MASTER['GENDER'] = trim($LeadPropertyList->Value);
					
				}
				//exit;
				if($PhotoUrl != ''){
					//$file_dir_1 = '../backend_assets/school/school_'.$PK_ACCOUNT.'/student/';
					$file_dir_1 = '../backend_assets/tmp_upload/';

					$extn 			= explode(".",$data->AttachmentName);
					$iindex			= count($extn) - 1;
					$rand_string 	= time()."_".rand(10000,99999);
					$file11			= 'stu_profile_'.$_SESSION['PK_USER'].$rand_string.".".$extn[$iindex];	
					$extension   	= strtolower($extn[$iindex]);
					$COPY_TO		= $file_dir_1.$file11;
					
					file_put_contents($COPY_TO, fopen($PhotoUrl, 'r'));
					
					$STUDENT_MASTER['IMAGE'] = $COPY_TO;
				}
				
				$res_stu = $db->Execute("SELECT PK_STUDENT_MASTER FROM S_STUDENT_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND LSQ_ID = '$ProspectID' AND ARCHIVED = 0 "); 
				if($res_stu->RecordCount() == 0) {
					
					if($STUDENT_MASTER['DATE_OF_BIRTH'] != '')
						$STUDENT_MASTER['DATE_OF_BIRTH'] = date("Y-m-d", strtotime($STUDENT_MASTER['DATE_OF_BIRTH']));
						
					if(strtolower($STUDENT_MASTER['PK_MARITAL_STATUS']) == "single")
						$STUDENT_MASTER['PK_MARITAL_STATUS'] = "Single";
					else if(strtolower($STUDENT_MASTER['PK_MARITAL_STATUS']) == "married")
						$STUDENT_MASTER['PK_MARITAL_STATUS'] = "Married/Remarried";
					else if(strtolower($STUDENT_MASTER['PK_MARITAL_STATUS']) == "separated")
						$STUDENT_MASTER['PK_MARITAL_STATUS'] = "Separated";
					else if(strtolower($STUDENT_MASTER['PK_MARITAL_STATUS']) == "divorced")
						$STUDENT_MASTER['PK_MARITAL_STATUS'] = "Divorced or Widowed";
					else if(strtolower($STUDENT_MASTER['PK_MARITAL_STATUS']) == "")
						$STUDENT_MASTER['PK_MARITAL_STATUS'] = "Unknown";
					
					$res_l = $db->Execute("select PK_MARITAL_STATUS from Z_MARITAL_STATUS WHERE trim(MARITAL_STATUS) = '$STUDENT_MASTER[PK_MARITAL_STATUS]' ");
					if($res_l->RecordCount() == 0) {
						$STUDENT_MASTER['PK_MARITAL_STATUS'] = '';
					} else {
						$STUDENT_MASTER['PK_MARITAL_STATUS'] = $res_l->fields['PK_MARITAL_STATUS'];
					}
					
					if(strtolower($STUDENT_MASTER['GENDER']) == "female")
						$STUDENT_MASTER['GENDER'] = "Female";
					else if(strtolower($STUDENT_MASTER['GENDER']) == "male")
						$STUDENT_MASTER['GENDER'] = "Male";
					else if(strtolower($STUDENT_MASTER['GENDER']) == "prefer not to disclose")
						$STUDENT_MASTER['GENDER'] = "Other";
					else if(strtolower($STUDENT_MASTER['GENDER']) == "")
						$STUDENT_MASTER['GENDER'] = "Unknown";
						
					if(strtolower($STUDENT_MASTER['GENDER']) != '') {
						$res_l = $db->Execute("select PK_GENDER from Z_GENDER WHERE TRIM(GENDER) = '".$STUDENT_MASTER['GENDER']."' ");
						$STUDENT_MASTER['GENDER'] = $res_l->fields['PK_GENDER'];
					} else 
						$STUDENT_MASTER['GENDER'] = '';
						
					
					$STUDENT_MASTER['PK_MAP_MASTER']  	= $PK_MAP_MASTER;
					$STUDENT_MASTER['LSQ_ID']  			= $ProspectID;
					$STUDENT_MASTER['PK_ACCOUNT']  		= $_SESSION['PK_ACCOUNT'];
					$STUDENT_MASTER['CREATED_BY']  		= $_SESSION['PK_USER'];
					$STUDENT_MASTER['CREATED_ON']  		= date("Y-m-d H:i");
					db_perform('S_STUDENT_MASTER', $STUDENT_MASTER, 'insert');
					$PK_STUDENT_MASTER = $db->insert_ID();
					
					$res_acc = $db->Execute("SELECT AUTO_GENERATE_STUD_ID,STUD_CODE,STUD_NO FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
					if($res_acc->fields['AUTO_GENERATE_STUD_ID'] == 1 ) {
						$STUDENT_ACADEMICS['STUDENT_ID'] = $res_acc->fields['STUD_CODE'].$res_acc->fields['STUD_NO'];
						$STUD_NO = $res_acc->fields['STUD_NO'] + 1;
						$db->Execute("UPDATE Z_ACCOUNT SET STUD_NO = '$STUD_NO' WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
					}
					
					$STUDENT_ACADEMICS['PK_STUDENT_MASTER'] = $PK_STUDENT_MASTER;
					$STUDENT_ACADEMICS['PK_ACCOUNT']  		= $_SESSION['PK_ACCOUNT'];
					$STUDENT_ACADEMICS['CREATED_BY']  		= $_SESSION['PK_USER'];
					$STUDENT_ACADEMICS['CREATED_ON']  		= date("Y-m-d H:i");
					db_perform('S_STUDENT_ACADEMICS', $STUDENT_ACADEMICS, 'insert');
					
					if($STUDENT_ENROLLMENT['PK_LEAD_SOURCE'] != '') {
						$res_l = $db->Execute("select PK_LEAD_SOURCE from M_LEAD_SOURCE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TRIM(LEAD_SOURCE) = '$STUDENT_ENROLLMENT[PK_LEAD_SOURCE]' ");
							
						if($res_l->RecordCount() == 0) {
							$LEAD_SOURCE = array();
							$LEAD_SOURCE['PK_ACCOUNT']  	= $_SESSION['PK_ACCOUNT'];
							$LEAD_SOURCE['LEAD_SOURCE'] 	= trim($STUDENT_ENROLLMENT['PK_LEAD_SOURCE']);
							$LEAD_SOURCE['CREATED_BY']  	= $_SESSION['PK_USER'];
							$LEAD_SOURCE['CREATED_ON']  	= date("Y-m-d H:i:s");
							db_perform('M_LEAD_SOURCE', $LEAD_SOURCE, 'insert');
							
							$STUDENT_ENROLLMENT['PK_LEAD_SOURCE'] = $db->insert_ID();
						} else {
							$STUDENT_ENROLLMENT['PK_LEAD_SOURCE'] = $res_l->fields['PK_LEAD_SOURCE'];
						}
					}
					
					if($STUDENT_ENROLLMENT['PK_LEAD_CONTACT_SOURCE'] != '') {
						$res_l = $db->Execute("select PK_LEAD_CONTACT_SOURCE from M_LEAD_CONTACT_SOURCE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TRIM(LEAD_CONTACT_SOURCE) = '$STUDENT_ENROLLMENT[PK_LEAD_CONTACT_SOURCE]' ORDER BY ACTIVE DESC");
							
						if($res_l->RecordCount() == 0) {
							$STUDENT_ENROLLMENT['PK_LEAD_CONTACT_SOURCE'] = '';
						} else {
							$STUDENT_ENROLLMENT['PK_LEAD_CONTACT_SOURCE'] = $res_l->fields['PK_LEAD_CONTACT_SOURCE'];
						}
					}
										
					if($STUDENT_ENROLLMENT['PK_CAMPUS_PROGRAM'] != '') {
						$res_l = $db->Execute("select PK_CAMPUS_PROGRAM from M_CAMPUS_PROGRAM WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND (TRIM(CODE) = '$STUDENT_ENROLLMENT[PK_CAMPUS_PROGRAM]' OR TRIM(PROGRAM_TRANSCRIPT_CODE) = '$STUDENT_ENROLLMENT[PK_CAMPUS_PROGRAM]' OR TRIM(DESCRIPTION) = '$STUDENT_ENROLLMENT[PK_CAMPUS_PROGRAM]' ) ORDER BY ACTIVE DESC ");
							
						if($res_l->RecordCount() == 0) {
							$STUDENT_ENROLLMENT['PK_CAMPUS_PROGRAM'] = 0;
						} else {
							$STUDENT_ENROLLMENT['PK_CAMPUS_PROGRAM'] = $res_l->fields['PK_CAMPUS_PROGRAM'];
						}
					}
					
					$res_l = $db->Execute("SELECT ASSIGN_PK_STUDENT_STATUS FROM Z_ACCOUNT_LSQ_SETTINGS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
					if($res_l->fields['ASSIGN_PK_STUDENT_STATUS'] > 0)
						$STUDENT_ENROLLMENT['PK_STUDENT_STATUS'] = $res_l->fields['ASSIGN_PK_STUDENT_STATUS'];
					else {
						$res_l = $db->Execute("SELECT PK_STUDENT_STATUS FROM M_STUDENT_STATUS WHERE PK_STUDENT_STATUS_MASTER = '1' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
						$STUDENT_ENROLLMENT['PK_STUDENT_STATUS'] 	= $res_l->fields['PK_STUDENT_STATUS'];
					}
					
					$STUDENT_ENROLLMENT['ENTRY_DATE'] 			= date("Y-m-d");
					$STUDENT_ENROLLMENT['ENTRY_TIME'] 			= date("H:i:s",strtotime(date("Y-m-d H:i:s")));
					$STUDENT_ENROLLMENT['IS_ACTIVE_ENROLLMENT'] = 1;
					$STUDENT_ENROLLMENT['STATUS_DATE'] 		 	= date("Y-m-d");
					$STUDENT_ENROLLMENT['PK_STUDENT_MASTER'] 	= $PK_STUDENT_MASTER;
					$STUDENT_ENROLLMENT['PK_ACCOUNT']  		 	= $_SESSION['PK_ACCOUNT'];
					$STUDENT_ENROLLMENT['CREATED_BY']  		 	= $_SESSION['PK_USER'];
					$STUDENT_ENROLLMENT['CREATED_ON']  		 	= date("Y-m-d H:i");
					db_perform('S_STUDENT_ENROLLMENT', $STUDENT_ENROLLMENT, 'insert');
					$EID = $db->insert_ID();
					
					$res_camp = $db->Execute("select PK_CAMPUS from S_CAMPUS WHERE PRIMARY_CAMPUS = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
					if($res_camp->RecordCount() == 1) {
						$STUDENT_CAMPUS['PK_CAMPUS']   				= $res_camp->fields['PK_CAMPUS'];
						$STUDENT_CAMPUS['PK_STUDENT_MASTER'] 		= $PK_STUDENT_MASTER;
						$STUDENT_CAMPUS['PK_STUDENT_ENROLLMENT'] 	= $EID;
						$STUDENT_CAMPUS['PK_ACCOUNT'] 				= $_SESSION['PK_ACCOUNT'];
						$STUDENT_CAMPUS['CREATED_BY']  				= $_SESSION['PK_USER'];
						$STUDENT_CAMPUS['CREATED_ON']  				= date("Y-m-d H:i");
						db_perform('S_STUDENT_CAMPUS', $STUDENT_CAMPUS, 'insert');
						$PK_STUDENT_CAMPUS_ARR[] = $db->insert_ID();
					}
					
					$res_req = $db->Execute("select * from S_SCHOOL_REQUIREMENT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by REQUIREMENT ASC");
					while (!$res_req->EOF) {
						$STUDENT_REQUIREMENT['PK_STUDENT_MASTER'] 		= $PK_STUDENT_MASTER;
						$STUDENT_REQUIREMENT['PK_STUDENT_ENROLLMENT'] 	= $EID;
						$STUDENT_REQUIREMENT['TYPE'] 				  	= 1;
						$STUDENT_REQUIREMENT['ID'] 				  		= $res_req->fields['PK_SCHOOL_REQUIREMENT'];
						$STUDENT_REQUIREMENT['PK_REQUIREMENT_CATEGORY'] = $res_req->fields['PK_REQUIREMENT_CATEGORY']; //ticket #1059
						$STUDENT_REQUIREMENT['REQUIREMENT'] 			= $res_req->fields['REQUIREMENT'];
						$STUDENT_REQUIREMENT['MANDATORY'] 				= $res_req->fields['MANDATORY'];
						$STUDENT_REQUIREMENT['PK_ACCOUNT']  			= $_SESSION['PK_ACCOUNT'];
						$STUDENT_REQUIREMENT['CREATED_BY']  			= $_SESSION['PK_USER'];
						$STUDENT_REQUIREMENT['CREATED_ON']  			= date("Y-m-d H:i");
						db_perform('S_STUDENT_REQUIREMENT', $STUDENT_REQUIREMENT, 'insert');
					
						$res_req->MoveNext();
					}
					
					$flag = 0;
					foreach($STUDENT_CONTACT as $key => $val){
						if($val != '') {
							$flag = 1;
							break;
						}	
					}
					
					if($STUDENT_CONTACT['PK_STATES'] != '') {
						$res_l = $db->Execute("select PK_STATES, PK_COUNTRY from Z_STATES WHERE (trim(STATE_CODE) = '$STUDENT_CONTACT[PK_STATES]' or trim(STATE_NAME) = '$STUDENT_CONTACT[PK_STATES]') ");
					
						if($res_l->RecordCount() == 0) {
							$STUDENT_CONTACT['PK_STATES'] = '';
						} else {
							$STUDENT_CONTACT['PK_STATES']  = $res_l->fields['PK_STATES'];
							$STUDENT_CONTACT['PK_COUNTRY'] = $res_l->fields['PK_COUNTRY'];
						}
					}
					
					if($STUDENT_CONTACT['CELL_PHONE'] != '') {
						$STUDENT_CONTACT['CELL_PHONE'] = preg_replace( '/[^0-9]/', '',$STUDENT_CONTACT['CELL_PHONE']);
						$CELL_PHONE = $STUDENT_CONTACT['CELL_PHONE'];
						
						$CELL_PHONE = '('.$CELL_PHONE[0].$CELL_PHONE[1].$CELL_PHONE[2].') '.$CELL_PHONE[3].$CELL_PHONE[4].$CELL_PHONE[5].'-'.$CELL_PHONE[6].$CELL_PHONE[7].$CELL_PHONE[8].$CELL_PHONE[9];
							
						$STUDENT_CONTACT['CELL_PHONE'] = $CELL_PHONE;
					}
					
					if($STUDENT_CONTACT['HOME_PHONE'] != '') {
						$STUDENT_CONTACT['HOME_PHONE'] = preg_replace( '/[^0-9]/', '',$STUDENT_CONTACT['HOME_PHONE']);
						$HOME_PHONE = $STUDENT_CONTACT['HOME_PHONE'];
						
						$HOME_PHONE = '('.$HOME_PHONE[0].$HOME_PHONE[1].$HOME_PHONE[2].') '.$HOME_PHONE[3].$HOME_PHONE[4].$HOME_PHONE[5].'-'.$HOME_PHONE[6].$HOME_PHONE[7].$HOME_PHONE[8].$HOME_PHONE[9];
							
						$STUDENT_CONTACT['HOME_PHONE'] = $HOME_PHONE;
					}
					
					if($STUDENT_CONTACT['OTHER_PHONE'] != '') {
						$STUDENT_CONTACT['OTHER_PHONE'] = preg_replace( '/[^0-9]/', '',$STUDENT_CONTACT['OTHER_PHONE']);
						$OTHER_PHONE = $STUDENT_CONTACT['OTHER_PHONE'];
						
						$OTHER_PHONE = '('.$OTHER_PHONE[0].$OTHER_PHONE[1].$OTHER_PHONE[2].') '.$OTHER_PHONE[3].$OTHER_PHONE[4].$OTHER_PHONE[5].'-'.$OTHER_PHONE[6].$OTHER_PHONE[7].$OTHER_PHONE[8].$OTHER_PHONE[9];
							
						$STUDENT_CONTACT['OTHER_PHONE'] = $OTHER_PHONE;
					}
					
					if($flag == 1) {
						$STUDENT_CONTACT['PK_STUDENT_CONTACT_TYPE_MASTER'] 	= 1;
						$STUDENT_CONTACT['PK_ACCOUNT']   					= $_SESSION['PK_ACCOUNT'];
						$STUDENT_CONTACT['PK_STUDENT_MASTER']   			= $PK_STUDENT_MASTER;
						$STUDENT_CONTACT['CREATED_BY']  					= $_SESSION['PK_USER'];
						$STUDENT_CONTACT['CREATED_ON']  					= date("Y-m-d H:i");
						db_perform('S_STUDENT_CONTACT', $STUDENT_CONTACT, 'insert');
					}
					
					if($mx_High_School_City_and_State != "" || $mx_High_School_Attended != "" || $mx_Year_Graduated != "")
						$STUDENT_OTHER_EDU['PK_EDUCATION_TYPE'] = 4;
					
					if(!empty($STUDENT_OTHER_EDU)) {
						$STUDENT_OTHER_EDU['PK_ACCOUNT']		= $_SESSION['PK_ACCOUNT'];
						$STUDENT_OTHER_EDU['PK_STUDENT_MASTER']	= $PK_STUDENT_MASTER;
						$STUDENT_OTHER_EDU['CREATED_BY']		= $_SESSION['PK_USER'];
						$STUDENT_OTHER_EDU['CREATED_ON']		= date("Y-m-d H:i");
						db_perform('S_STUDENT_OTHER_EDU', $STUDENT_OTHER_EDU, 'insert');
					}
					
					$notes_data_retrived 	= 0;
					$notes_PageIndex 		= 1;
					$notes_PageSize  		= 100;
					do{
						
						$stud_notes_data = get_ls_student_notes_data($STUDENT_MASTER['LSQ_ID'], $notes_PageIndex, $notes_PageSize);
						foreach($stud_notes_data->List as $List) {
							//echo "<pre>";print_r($List);exit;
							insert_lsq_student_notes($_SESSION['PK_ACCOUNT'], $PK_STUDENT_MASTER, $EID, $List);
						}
						
						$notes_data_retrived += $notes_PageSize;
						$notes_PageIndex++;
						
					} while($notes_data_retrived <= $stud_notes_data->RecordCount);
					
					///////////////////////////////////////////////////
			
					$notes_data_retrived 	= 0;
					$notes_PageIndex 		= 1;
					$notes_PageSize  		= 100;
					do{
						
						$stud_notes_data = get_ls_student_activity_data($STUDENT_MASTER['LSQ_ID'], $notes_PageIndex, $notes_PageSize);
						foreach($stud_notes_data->ProspectActivities as $ProspectActivities){
							$insert_notes = 0;
							foreach($ProspectActivities->Data as $Data) {
								if($Data->Key == 'NotableEventDescription') {
									$insert_notes = 1;
									break;
								}
							}
							if($insert_notes == 1) {
								//echo "<pre>";print_r($ProspectActivities);
								
								$PK_STUDENT_NOTES = insert_lsq_student_activity($_SESSION['PK_ACCOUNT'], $PK_STUDENT_MASTER, $EID, $ProspectActivities);
							
								if($PK_STUDENT_NOTES > 0)
									$inserted++;
							}
						}
						
						$notes_data_retrived += $notes_PageSize;
						$notes_PageIndex++;
						
					} while($notes_data_retrived <= $stud_notes_data->RecordCount);

					//echo "<pre><br />------------------------<br />";print_r($STUDENT_MASTER);print_r($STUDENT_ENROLLMENT);print_r($STUDENT_ACADEMICS);print_r($STUDENT_CONTACT);
				}
				
				/* $res_not = $db->Execute("select S_EVENT_TEMPLATE.PK_EVENT_TEMPLATE from S_EVENT_TEMPLATE, S_EVENT_TEMPLATE_CAMPUS WHERE S_EVENT_TEMPLATE.PK_ACCOUNT = '$PK_ACCOUNT' AND S_EVENT_TEMPLATE.ACTIVE = 1 AND PK_EVENT_TYPE = '1' AND S_EVENT_TEMPLATE.PK_EVENT_TEMPLATE = S_EVENT_TEMPLATE_CAMPUS.PK_EVENT_TEMPLATE AND PK_CAMPUS IN ($PK_CAMPUS)");
				if($res_not->RecordCount() > 0) {
					$noti_data['PK_EVENT_TEMPLATE'] 	= $res_not->fields['PK_EVENT_TEMPLATE'];
					$noti_data['PK_STUDENT_MASTER'] 	= $PK_STUDENT_MASTER;
					$noti_data['PK_STUDENT_ENROLLMENT'] = $EID;
					create_notification($noti_data);
				} */
			}
		}

	
		$data_retrived += $PageSize;
		$PageIndex++;
	} while($data_retrived <= $stud_data->RecordCount);

	header("location:student_data_view?t=100&id=".$PK_MAP_MASTER);exit;
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
	<title><?=MNU_IMPORT_LSQ_LEAD?> | <?=$title?></title>
	<style>
		li > a > label{position: unset !important;}
		#advice-required-entry-PK_CAMPUS {position: absolute;top: 55px;width: 142px}
	</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor">
							<?=MNU_IMPORT_LSQ_LEAD?>
						</h4>
                    </div>
                </div>
				
				<form class="floating-labels" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off" >
					<div class="row">
						<div class="col-12">
							<div class="card">
								<div class="card-body">
									<div class="row">
										
										<div class="col-md-2">
											<?=START_DATE?>
											<input type="text" class="form-control date required-entry" id="START_DATE" name="START_DATE" value="" >
										</div>
										<div class="col-md-2">
											<?=END_DATE?>
											<input type="text" class="form-control date required-entry" id="END_DATE" name="END_DATE" value="" >
										</div>
										
										<div class="col-md-2" style="flex: 0 0 12.667%;max-width: 12.667%;"  >
											<br />
											<button type="button" onclick="submit_form()" class="btn waves-effect waves-light btn-info"><?=IMPORT?></button>
										</div>
									</div>
									<br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />
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
	});

	
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
				document.form1.submit();
			}
		});
	}
	</script>
	
</body>

</html>