<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/isir_student.php");
require_once("../global/common_functions.php");
require_once("../global/create_notification.php"); 
require_once("check_access.php");
require_once("../global/s3-client-wrapper/s3-client-wrapper.php");

if(check_access('MANAGEMENT_FINANCE') == 0 ){
	header("location:../index");
	exit;
}
$msg = "";
if(!empty($_POST)) {
	$msg = ERROR_FILE_FORMAT;
	if($_FILES['txtFile']['name'] != ''){
		// $file_dir_1 	= 'temp/';
		// $file_dir_1 	= '../backend_assets/school/school_'.$_SESSION['PK_ACCOUNT'].'/other/';
		$file_dir_1 = '../backend_assets/tmp_upload/';
		$extn 			= explode(".",$_FILES['txtFile']['name']);
		$iindex			= count($extn) - 1;
		$rand_string 	= time()."-".rand(10000,99999);
		$file11			= 'isir_student_'.$_SESSION['PK_USER'].$rand_string.".".$extn[$iindex];	
		$extension   	= strtolower($extn[$iindex]);

		if($extension == "txt"){
			$msg = "";
			$newfile1    = $file_dir_1.$file11;
			$image_path  = $newfile1;
			move_uploaded_file($_FILES['txtFile']['tmp_name'], $image_path);
			
			// Upload file to S3 bucket
			$key_file_name = 'backend_assets/school/school_'.$_SESSION['PK_ACCOUNT'].'/other/'.$file11;
			$s3ClientWrapper = new s3ClientWrapper();
			$url = $s3ClientWrapper->uploadFile($key_file_name, $image_path);
			
			$file 		= file_get_contents($image_path, true);
			$text_arr 	= explode("\n", $file);

			// delete tmp file
			unlink($image_path);
			
			// echo "<pre>";print_r($text_arr);exit;
			$i=0;
			foreach($text_arr as $text){
				if(trim($text) != '') {
					$text = '-'.$text;
					$YEAR_INDICATION = substr($text,1,1);
					
					$res_mas = $db->Execute("select PK_ISIR_SETUP_MASTER from Z_ISIR_SETUP_MASTER WHERE ACTIVE = 1 AND YEAR_INDICATION = '$YEAR_INDICATION' ");
					if($res_mas->RecordCount() > 0){
						$PK_ISIR_SETUP_MASTER = $res_mas->fields['PK_ISIR_SETUP_MASTER'];

						// DIAM-2228
						$SSN_ENC_DUP = "";
						$DOB_DUP 	 = "";
						$flag = 1;
						$sflag_check = 1;
						$res_det_dup = $db->Execute("SELECT PK_ISIR_SETUP_DETAIL, START, END, DSIS_FIELD_NAME, HAS_LEDGEND from Z_ISIR_SETUP_DETAIL WHERE ACTIVE = 1 AND PK_ISIR_SETUP_MASTER = '$PK_ISIR_SETUP_MASTER' AND DSIS_FIELD_NAME != '' AND DSIS_FIELD_NAME = 'S_STUDENT_MASTER.DATE_OF_BIRTH' OR DSIS_FIELD_NAME = 'S_STUDENT_MASTER.SSN' ");

						$res_need_rec = $db->Execute("SELECT PK_ISIR_STUDENT_MASTER AS PK_ISIR_STUDENT_MASTER FROM S_ISIR_STUDENT_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 AND PK_ISIR_SETUP_MASTER = '$PK_ISIR_SETUP_MASTER' AND PK_STUDENT_MASTER = 0  ");
						$PK_ISIR_STUDENT_MASTER = $res_need_rec->fields['PK_ISIR_STUDENT_MASTER'];

						while (!$res_det_dup->EOF) 
						{
							$res_data_dup_rec = $db->Execute("SELECT PK_ISIR_STUDENT_MASTER AS PK_ISIR_STUDENT_MASTER FROM S_ISIR_STUDENT_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 AND PK_ISIR_SETUP_MASTER = '$PK_ISIR_SETUP_MASTER'  ");

							$G_PK_ISIR_SETUP_DETAIL = $res_det_dup->fields['PK_ISIR_SETUP_DETAIL']; 
							$SSN_DEC = array();
							$DOB_REC = array();
							while (!$res_data_dup_rec->EOF) 
							{
								$PK_ISIR_STUDENT_MASTER_1 = $res_data_dup_rec->fields['PK_ISIR_STUDENT_MASTER'];								
								$res_data = $db->Execute("SELECT VALUE from S_ISIR_STUDENT_DETAIL WHERE ACTIVE = 1 AND PK_ISIR_STUDENT_MASTER = '$PK_ISIR_STUDENT_MASTER_1' AND PK_ISIR_SETUP_DETAIL = '$G_PK_ISIR_SETUP_DETAIL' ");
								if($res_det_dup->fields['DSIS_FIELD_NAME'] == 'S_STUDENT_MASTER.SSN')
								{
									if($res_data->fields['VALUE'] != "")
									{
										$SSN_DEC[] = $res_data->fields['VALUE'];
									}
									
								}

								if($res_det_dup->fields['DSIS_FIELD_NAME'] == 'S_STUDENT_MASTER.DATE_OF_BIRTH')
								{
									if($res_data->fields['VALUE'] != "")
									{
										$DOB_REC[] = $res_data->fields['VALUE'];
									}
								}

								$res_data_dup_rec->MoveNext();
							}
							// print_r($SSN_DEC);
							// echo "<br>";
							
							$length_dup = ($res_det_dup->fields['END'] - $res_det_dup->fields['START']) + 1;
							if($res_det_dup->fields['DSIS_FIELD_NAME'] == 'S_STUDENT_MASTER.SSN')
							{
								
								$RAW_VALUE = substr($text,$res_det_dup->fields['START'],$length_dup);
								$RAW_VALUE = preg_replace( '/[^0-9]/', '',$RAW_VALUE);
								if($RAW_VALUE != '') {
									$aPK_ISIR_SETUP_DETAIL[] = $res_det_dup->fields['PK_ISIR_SETUP_DETAIL'];
									$SSN1 = trim($RAW_VALUE);
									$SSN  = $SSN1[0].$SSN1[1].$SSN1[2].'-'.$SSN1[3].$SSN1[4].'-'.$SSN1[5].$SSN1[6].$SSN1[7].$SSN1[8];
									
									$SSN_ENC_DUP = my_encrypt('',$SSN);
								}
							}

							if($res_det_dup->fields['DSIS_FIELD_NAME'] == 'S_STUDENT_MASTER.DATE_OF_BIRTH')
							{
								
								$RAW_VALUE 	= substr($text,$res_det_dup->fields['START'],$length_dup);
								if($RAW_VALUE != '') {
									$aPK_ISIR_SETUP_DETAIL[] = $res_det_dup->fields['PK_ISIR_SETUP_DETAIL'];
									$DOB_DUP 	= date("Y-m-d",strtotime($RAW_VALUE));
								}
							}
							// print_r($SSN_ENC_DUP);
							// echo "<br>";

							if(in_array($SSN_ENC_DUP, $SSN_DEC))
							{
								$flag = 0;
							}
							if(in_array($DOB_DUP, $DOB_REC))
							{
								$flag = 0;
							}

							if($SSN_ENC_DUP != "") {
								$res_stu_dup = $db->Execute("SELECT PK_STUDENT_MASTER FROM S_STUDENT_MASTER WHERE ACTIVE = 1 AND ARCHIVED = 0 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND SSN = '$SSN_ENC_DUP' AND DATE_OF_BIRTH = '$DOB_DUP' ");
								if($res_stu_dup->RecordCount() > 0){
									$sflag_check = 0;
								}
							}

							$res_det_dup->MoveNext();
						}
						
						// echo $flag." | ".$sflag_check;
						// exit;

						if($flag != 0) // Skip Record in file
						{
						
							$ISIR_STUDENT_MASTER  = array();
							$ISIR_STUDENT_MASTER1 = array();
							$ISIR_STUDENT_MASTER['PK_ACCOUNT']  			= $_SESSION['PK_ACCOUNT'];
							$ISIR_STUDENT_MASTER['PK_ISIR_SETUP_MASTER']  	= $PK_ISIR_SETUP_MASTER;
							$ISIR_STUDENT_MASTER['FILE_NAME']  				= $_FILES['txtFile']['name'];
							$ISIR_STUDENT_MASTER['CREATED_BY']  			= $_SESSION['PK_USER'];
							$ISIR_STUDENT_MASTER['CREATED_ON']  			= date("Y-m-d H:i");
							db_perform('S_ISIR_STUDENT_MASTER', $ISIR_STUDENT_MASTER, 'insert');
							$PK_ISIR_STUDENT_MASTER = $db->insert_ID();
							
							$SSN_ENC = "";
							$DOB 	 = "";
							$res_det = $db->Execute("select PK_ISIR_SETUP_DETAIL, START, END, DSIS_FIELD_NAME, HAS_LEDGEND from Z_ISIR_SETUP_DETAIL WHERE ACTIVE = 1 AND PK_ISIR_SETUP_MASTER = '$PK_ISIR_SETUP_MASTER' ");
							while (!$res_det->EOF) {
								$length 			 = ($res_det->fields['END'] - $res_det->fields['START']) + 1;
								$ISIR_STUDENT_DETAIL = array();
								$ISIR_STUDENT_DETAIL['PK_ACCOUNT']  			= $_SESSION['PK_ACCOUNT'];
								$ISIR_STUDENT_DETAIL['PK_ISIR_STUDENT_MASTER']  = $PK_ISIR_STUDENT_MASTER;
								$ISIR_STUDENT_DETAIL['PK_ISIR_SETUP_DETAIL']    = $res_det->fields['PK_ISIR_SETUP_DETAIL'];
								$ISIR_STUDENT_DETAIL['VALUE']  					= substr($text,$res_det->fields['START'],$length);
								$ISIR_STUDENT_DETAIL['CREATED_BY']  			= $_SESSION['PK_USER'];
								$ISIR_STUDENT_DETAIL['CREATED_ON']  			= date("Y-m-d H:i");
								
								if($res_det->fields['HAS_LEDGEND'] == 1){
									$res_led = $db->Execute("select TEXT from Z_ISIR_SETUP_LEGEND WHERE LEGEND = '$ISIR_STUDENT_DETAIL[VALUE]' AND PK_ISIR_SETUP_DETAIL = '$ISIR_STUDENT_DETAIL[PK_ISIR_SETUP_DETAIL]' ");
									$ISIR_STUDENT_DETAIL['VALUE'] 		= $res_led->fields['TEXT'];
									$ISIR_STUDENT_DETAIL['RAW_VALUE'] 	= substr($text,$res_det->fields['START'],$length);
								} 
								
								if($res_det->fields['DSIS_FIELD_NAME'] == 'S_STUDENT_MASTER.DATE_OF_BIRTH'){
									$ISIR_STUDENT_DETAIL['RAW_VALUE'] 	= substr($text,$res_det->fields['START'],$length);
									if($ISIR_STUDENT_DETAIL['RAW_VALUE'] != '') {
										$ISIR_STUDENT_DETAIL['VALUE'] 	= date("Y-m-d",strtotime($ISIR_STUDENT_DETAIL['RAW_VALUE']));
										$DOB							= $ISIR_STUDENT_DETAIL['VALUE'];
									} else
										$ISIR_STUDENT_DETAIL['VALUE'] = '';
								} else if($res_det->fields['DSIS_FIELD_NAME'] == 'S_STUDENT_MASTER.SSN'){
									$ISIR_STUDENT_DETAIL['RAW_VALUE'] = substr($text,$res_det->fields['START'],$length);
									$ISIR_STUDENT_DETAIL['RAW_VALUE'] = preg_replace( '/[^0-9]/', '',$ISIR_STUDENT_DETAIL['RAW_VALUE']);
									if($ISIR_STUDENT_DETAIL['RAW_VALUE'] != '') {
										$SSN1 = trim($ISIR_STUDENT_DETAIL['RAW_VALUE']);
										$SSN  = $SSN1[0].$SSN1[1].$SSN1[2].'-'.$SSN1[3].$SSN1[4].'-'.$SSN1[5].$SSN1[6].$SSN1[7].$SSN1[8];
										
										$SSN_ENC = my_encrypt('',$SSN);
										$ISIR_STUDENT_DETAIL['VALUE'] = $SSN_ENC;
									} else
										$ISIR_STUDENT_DETAIL['VALUE'] = '';
								} else if($res_det->fields['DSIS_FIELD_NAME'] == 'S_STUDENT_CONTACT.CELL_PHONE'){
									$ISIR_STUDENT_DETAIL['RAW_VALUE'] = substr($text,$res_det->fields['START'],$length);
									$ISIR_STUDENT_DETAIL['RAW_VALUE'] = preg_replace( '/[^0-9]/', '',$ISIR_STUDENT_DETAIL['RAW_VALUE']);
									if($ISIR_STUDENT_DETAIL['RAW_VALUE'] != '') {
										$PHONE = $ISIR_STUDENT_DETAIL['RAW_VALUE'];
										$PHONE = '('.$PHONE[0].$PHONE[1].$PHONE[2].') '.$PHONE[3].$PHONE[4].$PHONE[5].'-'.$PHONE[6].$PHONE[7].$PHONE[8].$PHONE[9];
										
										$ISIR_STUDENT_DETAIL['VALUE'] = $PHONE;
									} else
										$ISIR_STUDENT_DETAIL['VALUE'] = '';
								}
								
								db_perform('S_ISIR_STUDENT_DETAIL', $ISIR_STUDENT_DETAIL, 'insert');
								
								if($res_det->fields['DSIS_FIELD_NAME'] == 'S_STUDENT_MASTER.FIRST_NAME'){
									$ISIR_STUDENT_MASTER1['FIRST_NAME']  = $ISIR_STUDENT_DETAIL['VALUE'];
								}
								
								if($res_det->fields['DSIS_FIELD_NAME'] == 'S_STUDENT_MASTER.LAST_NAME'){
									$ISIR_STUDENT_MASTER1['LAST_NAME']  = $ISIR_STUDENT_DETAIL['VALUE'];
								}
								
								if($res_det->fields['DSIS_FIELD_NAME'] == 'S_STUDENT_CONTACT.EMAIL'){
									$ISIR_STUDENT_MASTER1['EMAIL']  = $ISIR_STUDENT_DETAIL['VALUE'];
								}
								
								$res_det->MoveNext();
							}
							
							if($SSN_ENC != "") {
								$res_stu = $db->Execute("select PK_STUDENT_MASTER from S_STUDENT_MASTER WHERE ACTIVE = 1 AND ARCHIVED = 0 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND SSN = '$SSN_ENC' AND DATE_OF_BIRTH = '$DOB' ");
								if($res_stu->RecordCount() > 0){
									$ISIR_STUDENT_MASTER1['PK_STUDENT_MASTER']  = $res_stu->fields['PK_STUDENT_MASTER'];
								}
							}
							db_perform('S_ISIR_STUDENT_MASTER', $ISIR_STUDENT_MASTER1, 'update'," PK_ISIR_STUDENT_MASTER = '$PK_ISIR_STUDENT_MASTER' ");
						
							// $flag = 1;
							// if($SSN_ENC != "") {
							// 	$res_stu = $db->Execute("select PK_STUDENT_MASTER from S_STUDENT_MASTER WHERE ACTIVE = 1 AND ARCHIVED = 0 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND SSN = '$SSN_ENC' AND DATE_OF_BIRTH = '$DOB' ");
							// 	if($res_stu->RecordCount() > 0){
							// 		$flag = 0;
							// 	}
							// }

						} // End DIAM-2228, End IF Loop - Skip Record in file

						if($_POST['CREATE_LEAD'] == 1 && $sflag_check == 1){
							
							$STUDENT_MASTER  	= array();
							$STUDENT_CONTACT 	= array();
							$STUDENT_OTHER_EDU 	= array();

							$res_det1 = $db->Execute("select PK_ISIR_SETUP_DETAIL, DSIS_FIELD_NAME from Z_ISIR_SETUP_DETAIL WHERE ACTIVE = 1 AND PK_ISIR_SETUP_MASTER = '$PK_ISIR_SETUP_MASTER' AND DSIS_FIELD_NAME != '' ");
							while (!$res_det1->EOF) {
								$PK_ISIR_SETUP_DETAIL = $res_det1->fields['PK_ISIR_SETUP_DETAIL']; 
								$res1 = $db->Execute("select VALUE from S_ISIR_STUDENT_DETAIL WHERE ACTIVE = 1 AND PK_ISIR_STUDENT_MASTER = '$PK_ISIR_STUDENT_MASTER' AND PK_ISIR_SETUP_DETAIL = '$PK_ISIR_SETUP_DETAIL' ");
								
								$DSIS_FIELD_NAME = explode(".",$res_det1->fields['DSIS_FIELD_NAME']);
								if($DSIS_FIELD_NAME[0] == 'S_STUDENT_MASTER')
									$STUDENT_MASTER[$DSIS_FIELD_NAME[1]] = trim($res1->fields['VALUE']);
								else if($DSIS_FIELD_NAME[0] == 'S_STUDENT_CONTACT')
									$STUDENT_CONTACT[$DSIS_FIELD_NAME[1]] = trim($res1->fields['VALUE']);
								else if($DSIS_FIELD_NAME[0] == 'S_STUDENT_OTHER_EDU')
									$STUDENT_OTHER_EDU[$DSIS_FIELD_NAME[1]] = trim($res1->fields['VALUE']);
		
								$res_det1->MoveNext();
							}

							/* Ticket # 1769  */
							if($STUDENT_MASTER['GENDER'] != '') {
								$res_st = $db->Execute("select PK_GENDER from Z_GENDER WHERE GENDER = '".$STUDENT_MASTER['GENDER']."' ");
								$STUDENT_MASTER['GENDER'] = $res_st->fields['PK_GENDER'];
							} else 
								$STUDENT_MASTER['GENDER'] = '';
							/* Ticket # 1769  */
							
							if($STUDENT_MASTER['PK_DRIVERS_LICENSE_STATE'] != '') {
								$res_st = $db->Execute("select PK_STATES from Z_STATES WHERE TRIM(STATE_CODE) = '$STUDENT_MASTER[PK_DRIVERS_LICENSE_STATE]' AND ACTIVE = 1");
								$STUDENT_MASTER['PK_DRIVERS_LICENSE_STATE'] = $res_st->fields['PK_STATES'];
							}
							
							if($STUDENT_MASTER['PK_STATE_OF_RESIDENCY'] != '') {
								$res_st = $db->Execute("select PK_STATES from Z_STATES WHERE TRIM(STATE_CODE) = '$STUDENT_MASTER[PK_STATE_OF_RESIDENCY]' AND ACTIVE = 1");
								$STUDENT_MASTER['PK_STATE_OF_RESIDENCY'] = $res_st->fields['PK_STATES'];
							}
							
							if($STUDENT_MASTER['PK_CITIZENSHIP'] != '') {
								$res_st = $db->Execute("select PK_CITIZENSHIP from Z_CITIZENSHIP WHERE TRIM(CITIZENSHIP) = '$STUDENT_MASTER[PK_CITIZENSHIP]' AND ACTIVE = 1");
								$STUDENT_MASTER['PK_CITIZENSHIP'] = $res_st->fields['PK_CITIZENSHIP'];
							}
							
							if($STUDENT_MASTER['PK_MARITAL_STATUS'] != '') {
								$res_st = $db->Execute("select PK_MARITAL_STATUS from Z_MARITAL_STATUS WHERE TRIM(MARITAL_STATUS) = '$STUDENT_MASTER[PK_MARITAL_STATUS]' AND ACTIVE = 1");
								$STUDENT_MASTER['PK_MARITAL_STATUS'] = $res_st->fields['PK_MARITAL_STATUS'];
							}
						
							if($STUDENT_CONTACT['PK_STATES'] != '') {
								$res_st = $db->Execute("select PK_STATES from Z_STATES WHERE TRIM(STATE_CODE) = '$STUDENT_CONTACT[PK_STATES]' AND ACTIVE = 1");
								$STUDENT_CONTACT['PK_STATES'] = $res_st->fields['PK_STATES'];
							}
							
							$res_acc = $db->Execute("SELECT AUTO_GENERATE_STUD_ID,STUD_CODE,STUD_NO FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
							if($res_acc->fields['AUTO_GENERATE_STUD_ID'] == 1 ) {
								$STUDENT_ACADEMICS['STUDENT_ID'] = $res_acc->fields['STUD_CODE'].$res_acc->fields['STUD_NO'];
								$STUD_NO = $res_acc->fields['STUD_NO'] + 1;
								$db->Execute("UPDATE Z_ACCOUNT SET STUD_NO = '$STUD_NO' WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
							}
							
							$create_stud_other_edu = 0;
							foreach($STUDENT_OTHER_EDU as $key => $value) {
								if(trim($value) != '') {
									$create_stud_other_edu = 1;
									break;
								}
							}
							
							$STUDENT_MASTER['PK_ISIR_MASTER']  	= $PK_ISIR_STUDENT_MASTER;
							$STUDENT_MASTER['PK_ACCOUNT']  		= $_SESSION['PK_ACCOUNT'];
							$STUDENT_MASTER['CREATED_BY']  		= $_SESSION['PK_USER'];
							$STUDENT_MASTER['CREATED_ON']  		= date("Y-m-d H:i");
							db_perform('S_STUDENT_MASTER', $STUDENT_MASTER, 'insert');
							$PK_STUDENT_MASTER = $db->insert_ID();
							
							/* Ticket # 1595
							$STUDENT_ACADEMICS['ENTRY_DATE'] 		= date("Y-m-d");
							$STUDENT_ACADEMICS['ENTRY_TIME'] 		= date("H:i:s",strtotime(date("Y-m-d H:i:s")));
							*/
							$STUDENT_ACADEMICS['PK_STUDENT_MASTER'] = $PK_STUDENT_MASTER;
							$STUDENT_ACADEMICS['PK_ACCOUNT']  		= $_SESSION['PK_ACCOUNT'];
							$STUDENT_ACADEMICS['CREATED_BY']  		= $_SESSION['PK_USER'];
							$STUDENT_ACADEMICS['CREATED_ON']  		= date("Y-m-d H:i");
							db_perform('S_STUDENT_ACADEMICS', $STUDENT_ACADEMICS, 'insert');
							
							$res  = $db->Execute("SELECT PK_STUDENT_STATUS FROM M_STUDENT_STATUS WHERE PK_STUDENT_STATUS_MASTER = '1' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
							$res1 = $db->Execute("SELECT PK_LEAD_SOURCE FROM M_LEAD_SOURCE WHERE PK_LEAD_SOURCE_MASTER = '5' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
							
							/* Ticket # 1595 */
							$STUDENT_ENROLLMENT['ENTRY_DATE'] 		= date("Y-m-d");
							$STUDENT_ENROLLMENT['ENTRY_TIME'] 		= date("H:i:s",strtotime(date("Y-m-d H:i:s")));
							/* Ticket # 1595 */
							$STUDENT_ENROLLMENT['PK_1098T_REPORTING_TYPE'] = 1; //Ticket # 1046
							$STUDENT_ENROLLMENT['PK_STUDENT_STATUS'] 	= $res->fields['PK_STUDENT_STATUS'];
							$STUDENT_ENROLLMENT['PK_LEAD_SOURCE'] 		= $res1->fields['PK_LEAD_SOURCE'];
							$STUDENT_ENROLLMENT['IS_ACTIVE_ENROLLMENT'] = 1;
							$STUDENT_ENROLLMENT['STATUS_DATE'] 		 	= date("Y-m-d");
							$STUDENT_ENROLLMENT['PK_STUDENT_MASTER'] 	= $PK_STUDENT_MASTER;
							$STUDENT_ENROLLMENT['PK_ACCOUNT']  		 	= $_SESSION['PK_ACCOUNT'];
							$STUDENT_ENROLLMENT['CREATED_BY']  		 	= $_SESSION['PK_USER'];
							$STUDENT_ENROLLMENT['CREATED_ON']  		 	= date("Y-m-d H:i");
							db_perform('S_STUDENT_ENROLLMENT', $STUDENT_ENROLLMENT, 'insert');
							$EID = $db->insert_ID();
							
							/* Ticket #1123 */
							$res_req = $db->Execute("select * from S_SCHOOL_REQUIREMENT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 ");
							while (!$res_req->EOF) {
								$STUDENT_REQUIREMENT['PK_STUDENT_MASTER'] 		= $PK_STUDENT_MASTER;
								$STUDENT_REQUIREMENT['PK_STUDENT_ENROLLMENT'] 	= $EID;
								$STUDENT_REQUIREMENT['TYPE'] 				  	= 1;
								$STUDENT_REQUIREMENT['ID'] 				  		= $res_req->fields['PK_SCHOOL_REQUIREMENT'];
								$STUDENT_REQUIREMENT['PK_REQUIREMENT_CATEGORY'] = $res_req->fields['PK_REQUIREMENT_CATEGORY'];
								$STUDENT_REQUIREMENT['REQUIREMENT'] 			= $res_req->fields['REQUIREMENT'];
								$STUDENT_REQUIREMENT['MANDATORY'] 				= $res_req->fields['MANDATORY'];
								$STUDENT_REQUIREMENT['PK_ACCOUNT']  			= $_SESSION['PK_ACCOUNT'];
								$STUDENT_REQUIREMENT['CREATED_BY']  			= $_SESSION['PK_USER'];
								$STUDENT_REQUIREMENT['CREATED_ON']  			= date("Y-m-d H:i");
								db_perform('S_STUDENT_REQUIREMENT', $STUDENT_REQUIREMENT, 'insert');
							
								$res_req->MoveNext();
							}
							/* Ticket #1123 */
							
							if($create_stud_other_edu == 1) {
								$res_st = $db->Execute("select PK_STATES from Z_STATES WHERE TRIM(STATE_CODE) = '$STUDENT_OTHER_EDU[PK_STATE]' AND ACTIVE = 1");
								$STUDENT_OTHER_EDU['PK_STATE'] = $res_st->fields['PK_STATES'];
								
								$STUDENT_OTHER_EDU['PK_EDUCATION_TYPE'] 		= 4;
								$STUDENT_OTHER_EDU['PK_STUDENT_MASTER'] 		= $PK_STUDENT_MASTER;
								$STUDENT_OTHER_EDU['PK_ACCOUNT'] 				= $_SESSION['PK_ACCOUNT'];
								$STUDENT_OTHER_EDU['CREATED_BY']  				= $_SESSION['PK_USER'];
								$STUDENT_OTHER_EDU['CREATED_ON']  				= date("Y-m-d H:i");
								db_perform('S_STUDENT_OTHER_EDU', $STUDENT_OTHER_EDU, 'insert');
							}
							
							$res_camp = $db->Execute("select PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
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
							
							$STUDENT_STATUS_LOG['PK_STUDENT_STATUS'] 		= $STUDENT_ENROLLMENT['PK_STUDENT_STATUS'];
							$STUDENT_STATUS_LOG['PK_STUDENT_MASTER'] 		= $PK_STUDENT_MASTER;
							$STUDENT_STATUS_LOG['PK_STUDENT_ENROLLMENT'] 	= $EID;
							$STUDENT_STATUS_LOG['PK_ACCOUNT']  				= $_SESSION['PK_ACCOUNT'];
							$STUDENT_STATUS_LOG['CHANGED_BY']  				= $_SESSION['PK_USER'];
							$STUDENT_STATUS_LOG['CHANGED_ON']  				= date("Y-m-d H:i");
							db_perform('S_STUDENT_STATUS_LOG', $STUDENT_STATUS_LOG, 'insert');
							
							$STUDENT_CONTACT['PK_STUDENT_CONTACT_TYPE_MASTER'] = 1;
							$STUDENT_CONTACT['PK_ACCOUNT']   		= $_SESSION['PK_ACCOUNT'];
							$STUDENT_CONTACT['PK_STUDENT_MASTER']   = $PK_STUDENT_MASTER;
							$STUDENT_CONTACT['CREATED_BY']  		= $_SESSION['PK_USER'];
							$STUDENT_CONTACT['CREATED_ON']  		= date("Y-m-d H:i");
							db_perform('S_STUDENT_CONTACT', $STUDENT_CONTACT, 'insert');
							
							$STUD_PK_CAMPUS = '';
							$res_campus = $db->Execute("SELECT PK_CAMPUS FROM S_STUDENT_CAMPUS WHERE PK_STUDENT_ENROLLMENT = '$EID' ");
							while (!$res_campus->EOF) { 
								if($STUD_PK_CAMPUS != '')
									$STUD_PK_CAMPUS .= ', ';
									
								$STUD_PK_CAMPUS .= $res_campus->fields['PK_CAMPUS'];
								$res_campus->MoveNext();
							}
							
							$res_not = $db->Execute("select S_EVENT_TEMPLATE.PK_EVENT_TEMPLATE from S_EVENT_TEMPLATE, S_EVENT_TEMPLATE_CAMPUS WHERE S_EVENT_TEMPLATE.PK_ACCOUNT = '$PK_ACCOUNT' AND S_EVENT_TEMPLATE.ACTIVE = 1 AND PK_EVENT_TYPE = '1' AND S_EVENT_TEMPLATE.PK_EVENT_TEMPLATE = S_EVENT_TEMPLATE_CAMPUS.PK_EVENT_TEMPLATE AND PK_CAMPUS IN ($PK_CAMPUS)");
							if($res_not->RecordCount() > 0) {
								$noti_data['PK_EVENT_TEMPLATE'] 	= $res_not->fields['PK_EVENT_TEMPLATE'];
								$noti_data['PK_STUDENT_MASTER'] 	= $PK_STUDENT_MASTER;
								$noti_data['PK_STUDENT_ENROLLMENT'] = $EID;
								create_notification($noti_data);
							}
							
							$ISIR_STUDENT_MASTER1['PK_STUDENT_MASTER']  = $PK_STUDENT_MASTER;
							db_perform('S_ISIR_STUDENT_MASTER', $ISIR_STUDENT_MASTER1, 'update'," PK_ISIR_STUDENT_MASTER = '$PK_ISIR_STUDENT_MASTER' ");
						}

					}
				}
				$i++;
			}
			
			header("location:manage_isir");
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
	<title><?=ISIR_PAGE_TITLE?> | <?=$title?></title>
	<link rel="stylesheet" type="text/css" href="../backend_assets/dist/css/easyui.css">
	<style>
		.lds-ring {
			position: absolute;
			left: 0;
			top: 0;
			right: 0;
			bottom: 0;
			margin: auto;
			width: 64px;
			height: 64px;
		}

		.lds-ring div {
			box-sizing: border-box;
			display: block;
			position: absolute;
			width: 51px;
			height: 51px;
			margin: 6px;
			border: 6px solid #0066ac;
			border-radius: 50%;
			animation: lds-ring 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
			border-color: #007bff transparent transparent transparent;
		}

		.lds-ring div:nth-child(1) {
			animation-delay: -0.45s;
		}

		.lds-ring div:nth-child(2) {
			animation-delay: -0.3s;
		}

		.lds-ring div:nth-child(3) {
			animation-delay: -0.15s;
		}

		@keyframes lds-ring {
			0% {
				transform: rotate(0deg);
			}

			100% {
				transform: rotate(360deg);
			}
		}
		#loaders {
			position: fixed;
			width: 100%;
			z-index: 9999;
			bottom: 0;
			background-color: #2c3e50;
			display: block;
			left: 0;
			top: 0;
			right: 0;
			bottom: 0;
			opacity: 0.6;
			display: none;
		}

		.loader-text{
			position: absolute;
			left: 26px;
			top: 177px;
			right: 0;
			bottom: 0;
			margin: auto;
			width: 133px;
			height: 64px;
			color: #fff;
			font-weight: bold;
		}
	</style>
</head>
<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="loaders" style="display: none;">
		<div class="lds-ring">
			<div></div>
			<div></div>
			<div></div>
			<div></div>
		</div>
		<div class="loader-text">Please wait.....!</div>
	</div>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-2 align-self-center">
                        <h4 class="text-themecolor"><?=ISIR_UPLOAD_PAGE_TITLE?></h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="p-20">
                            	<form enctype="multipart/form-data" class="floating-labels m-t-40" method="post" name="form1" id="form1">
                            		<? if ($msg != "") {
                        				echo '<div class="alert alert-danger" role="alert">'.$msg.'</div>';
                            		} ?>
									
									<div class="row" >
										<div class="col-md-2" >
											<div class="d-flex">
												<div class="col-12 col-sm-12 custom-control custom-checkbox form-group" >
													<input type="checkbox" class="custom-control-input" id="CREATE_LEAD" name="CREATE_LEAD" value="1" >
													<label class="custom-control-label" for="CREATE_LEAD"><?=CREATE_LEAD?></label>
												</div>
											</div>
										</div>
										<div class=" col-sm-1 ">
											<span class="mytooltip tooltip-effect-1">
												<span class="tooltip-item tool_tip_custom">
													<i class="mdi mdi-help-circle help_size"></i>
												</span>
												<span class="tooltip-content clearfix">
													<span class="tooltip-text">
														<? if($_SESSION['PK_LANGUAGE'] == 1)
															$lan_field = "TOOL_CONTENT_ENG";
														else
															$lan_field = "TOOL_CONTENT_SPA"; 
														$res_help = $db->Execute("select $lan_field from Z_HELP WHERE PK_HELP = 7"); 
														echo $res_help->fields[$lan_field]; ?>
													</span>
												</span>
											</span>
										</div>	
									</div>
									
									<div class="row" >
										<div class="col-md-6" >
											<input type="file" name="txtFile" />
										</div>
									</div>
									<!-- DIAM-2230 -->
									<br>
									<div class="row" >
										<div class="col-md-2"> 
											<div>Award Year</div>
											<select id="FA_PK_AWARD_YEAR" name="FA_PK_AWARD_YEAR" placeholder="Select" class="form-control">
												<option>Select</option>
												<? $res_type = $db->Execute("select PK_AWARD_YEAR,AWARD_YEAR from M_AWARD_YEAR WHERE ACTIVE = 1 order by BEGIN_DATE DESC");
												while (!$res_type->EOF) { 
													$selected = "";
													$sYear = date("Y");
													if($res_type->fields['PK_AWARD_YEAR'] == '27' && $sYear == '2024') // 2024-25
													{
														$selected = "selected";
													}
													else if($res_type->fields['PK_AWARD_YEAR'] == '28' && $sYear == '2025')  // 2025-26
													{
														$selected = "selected";
													}
													?>
													<option value="<?=$res_type->fields['PK_AWARD_YEAR']?>" <?=$selected?>  ><?=$res_type->fields['AWARD_YEAR']?></option>
												<?	$res_type->MoveNext();
												} ?>
											</select>
										</div>
									</div>
									<!-- End DIAM-2230 -->
									</p>
									<div class="d-flex">
										<input type="submit" name="Upload" value="<?=UPLOAD?>" class="btn btn-info d-none d-lg-block" />
										<a href="manage_isir" class="btn waves-effect waves-light btn-dark m-l-15"><?=CANCEL?></a>
									</div>
									
								</form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <? require_once("footer.php"); ?>
    </div>

	<? require_once("js.php"); ?>
	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />

	<script type="text/javascript">	

	$(document).ready(function(){
		$("#form1").on("submit", function(){
			$("#loaders").fadeIn();
		});//submit
	});//document ready
	
	</script>
					
</body>
</html>