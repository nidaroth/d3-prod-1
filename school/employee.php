<? require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/employee.php");
require_once("../language/school_profile.php");

require_once("../global/mail.php");
require_once("../global/texting.php");
require_once("check_access.php");
require_once("../global/s3-client-wrapper/s3-client-wrapper.php");

if (check_access('SETUP_SCHOOL') == 0) {
	header("location:../index");
	exit;
}

if ($_GET['id'] != '' && $_SESSION['PK_ROLES'] == 3) {
	$res = $db->Execute("SELECT S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER FROM S_EMPLOYEE_MASTER,S_EMPLOYEE_CAMPUS WHERE S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = '$_GET[id]' AND S_EMPLOYEE_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_EMPLOYEE_CAMPUS.PK_EMPLOYEE_MASTER = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER AND S_EMPLOYEE_CAMPUS.PK_CAMPUS IN ($_SESSION[PK_CAMPUS]) ");
	if ($res->RecordCount() == 0) {
		header("location:manage_employee?t=" . $_GET['t']);
		exit;
	}
}

if ($_GET['act'] == 'user_login') {
	$db->Execute("DELETE FROM Z_USER WHERE ID = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_USER_TYPE = 2");
	$db->Execute("UPDATE S_EMPLOYEE_MASTER SET LOGIN_CREATED = 0, IS_ADMIN = 0 WHERE PK_EMPLOYEE_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	header("location:employee?id=" . $_GET['id'] . '&t=' . $_GET['t']);
} else if ($_GET['act'] == 'img_del') {
	$res = $db->Execute("SELECT IMAGE FROM S_EMPLOYEE_MASTER WHERE PK_EMPLOYEE_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	unlink($res->fields['IMAGE']);
	$db->Execute("UPDATE S_EMPLOYEE_MASTER SET IMAGE = '' WHERE PK_EMPLOYEE_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	header("location:employee?id=" . $_GET['id'] . '&t=' . $_GET['t']);
} else if ($_GET['act'] == 'notes') {
	$db->Execute("DELETE FROM S_EMPLOYEE_NOTES WHERE PK_EMPLOYEE_NOTES = '$_GET[iid]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	header("location:employee?id=" . $_GET['id'] . '&t=' . $_GET['t'] . '&tab=notes');
}

/* $txt = "000-11-1235";
$enc = my_encrypt($_SESSION['PK_ACCOUNT'].$_GET['id'],$txt);
$dec = my_decrypt($_SESSION['PK_ACCOUNT'].$_GET['id'],$enc);
echo $txt.'<br />'.$enc.'<br />'.$dec;exit; */

/* Ticket # 1723  */
if ($_GET['t'] == '')
	$_GET['t'] = 1;
/* Ticket # 1723  */

if (!empty($_POST)) {
	//echo "<pre>";print_r($_POST);exit;

	if ($_POST['FORM_NAME'] == 'form1') {
		if (isset($_POST['PK_PRE_FIX']))
			$EMPLOYEE_MASTER['PK_PRE_FIX'] = $_POST['PK_PRE_FIX'];

		if (isset($_POST['PK_SUPERVISOR']))
			$EMPLOYEE_MASTER['PK_SUPERVISOR'] = $_POST['PK_SUPERVISOR'];

		$EMPLOYEE_MASTER['IS_FACULTY']  		= $_POST['IS_FACULTY'];
		$EMPLOYEE_MASTER['LAST_NAME']  			= $_POST['LAST_NAME'];
		$EMPLOYEE_MASTER['FIRST_NAME']  		= $_POST['FIRST_NAME'];
		$EMPLOYEE_MASTER['MIDDLE_NAME']  		= $_POST['MIDDLE_NAME'];
		$EMPLOYEE_MASTER['EMPLOYEE_ID']  		= $_POST['EMPLOYEE_ID'];
		$EMPLOYEE_MASTER['TITLE']  				= $_POST['TITLE'];
		$EMPLOYEE_MASTER['EMAIL']  				= $_POST['EMAIL'];
		$EMPLOYEE_MASTER['EMAIL_OTHER']  		= $_POST['EMAIL_OTHER'];
		$EMPLOYEE_MASTER['DOB']  				= $_POST['DOB'];
		$EMPLOYEE_MASTER['GENDER']  			= $_POST['GENDER'];
		$EMPLOYEE_MASTER['PK_MARITAL_STATUS']  	= $_POST['PK_MARITAL_STATUS'];
		$EMPLOYEE_MASTER['IPEDS_ETHNICITY']  	= $_POST['IPEDS_ETHNICITY'];
		$EMPLOYEE_MASTER['NETWORK_ID']  		= $_POST['NETWORK_ID'];
		$EMPLOYEE_MASTER['COMPANY_EMP_ID']  	= $_POST['COMPANY_EMP_ID'];
		$EMPLOYEE_MASTER['SUPERVISOR']  		= $_POST['SUPERVISOR'];
		$EMPLOYEE_MASTER['FULL_PART_TIME']		= $_POST['FULL_PART_TIME'];
		$EMPLOYEE_MASTER['ELIGIBLE_FOR_REHIRE'] = $_POST['ELIGIBLE_FOR_REHIRE'];
		$EMPLOYEE_MASTER['PK_SOC_CODE']  		= $_POST['PK_SOC_CODE'];
		$EMPLOYEE_MASTER['DATE_HIRED']  		= $_POST['DATE_HIRED'];
		$EMPLOYEE_MASTER['DATE_TERMINATED']  	= $_POST['DATE_TERMINATED'];
		$EMPLOYEE_MASTER['NEED_SCHOOL_ACCESS']  = $_POST['NEED_SCHOOL_ACCESS'];
		$EMPLOYEE_MASTER['TURN_OFF_ASSIGNMENTS'] 	 = $_POST['TURN_OFF_ASSIGNMENTS'];
		//$EMPLOYEE_MASTER['INTERNAL_MESSAGE_ENABLED'] = $_POST['INTERNAL_MESSAGE_ENABLED']; //ticket #967  Ticket # 1511

		if ($EMPLOYEE_MASTER['DOB'] != '')
			$EMPLOYEE_MASTER['DOB'] = date("Y-m-d", strtotime($EMPLOYEE_MASTER['DOB']));
		else
			$EMPLOYEE_MASTER['DOB'] = '';

		if ($EMPLOYEE_MASTER['DATE_HIRED'] != '')
			$EMPLOYEE_MASTER['DATE_HIRED'] = date("Y-m-d", strtotime($EMPLOYEE_MASTER['DATE_HIRED']));
		else
			$EMPLOYEE_MASTER['DATE_HIRED'] = '';

		if ($EMPLOYEE_MASTER['DATE_TERMINATED'] != '')
			$EMPLOYEE_MASTER['DATE_TERMINATED'] = date("Y-m-d", strtotime($EMPLOYEE_MASTER['DATE_TERMINATED']));
		else
			$EMPLOYEE_MASTER['DATE_TERMINATED'] = '';

		$EMPLOYEE_CONTACT['ADDRESS'] 	= $_POST['ADDRESS'];
		$EMPLOYEE_CONTACT['ADDRESS_1'] 	= $_POST['ADDRESS_1'];
		$EMPLOYEE_CONTACT['CITY'] 		= $_POST['CITY'];
		$EMPLOYEE_CONTACT['PK_STATES'] 	= $_POST['PK_STATES'];
		$EMPLOYEE_CONTACT['ZIP'] 		= $_POST['ZIP'];
		$EMPLOYEE_CONTACT['PK_COUNTRY'] = $_POST['PK_COUNTRY'];
		$EMPLOYEE_CONTACT['HOME_PHONE'] = $_POST['HOME_PHONE'];
		$EMPLOYEE_CONTACT['WORK_PHONE'] = $_POST['WORK_PHONE'];
		$EMPLOYEE_CONTACT['CELL_PHONE'] = $_POST['CELL_PHONE'];

		if ($_GET['id'] == '') {
			if ($_GET['t'] == 2)
				$EMPLOYEE_MASTER['IS_FACULTY'] = 1;

			$EMPLOYEE_MASTER['PK_ACCOUNT']  = $_SESSION['PK_ACCOUNT'];
			$EMPLOYEE_MASTER['CREATED_BY']  = $_SESSION['PK_USER'];
			$EMPLOYEE_MASTER['CREATED_ON']  = date("Y-m-d H:i");
			db_perform('S_EMPLOYEE_MASTER', $EMPLOYEE_MASTER, 'insert');
			$PK_EMPLOYEE_MASTER = $db->insert_ID();

			$EMPLOYEE_CONTACT['PK_EMPLOYEE_MASTER'] = $PK_EMPLOYEE_MASTER;
			$EMPLOYEE_CONTACT['PK_ACCOUNT']  		= $_SESSION['PK_ACCOUNT'];
			$EMPLOYEE_CONTACT['CREATED_BY']  		= $_SESSION['PK_USER'];
			$EMPLOYEE_CONTACT['CREATED_ON']  		= date("Y-m-d H:i");
			db_perform('S_EMPLOYEE_CONTACT', $EMPLOYEE_CONTACT, 'insert');

			if ($_GET['cid'] != '' && $_POST['EMAIL'] != '' && $_POST['PASSWORD'] != '') {
				if ($_POST['IS_ADMIN'] == 1)
					$PK_ROLES = 2;
				else
					$PK_ROLES = 3;

				$EMPLOYEE_MASTER1['IS_ADMIN']	   = $_POST['IS_ADMIN'];
				$EMPLOYEE_MASTER1['LOGIN_CREATED'] = 1;
				db_perform('S_EMPLOYEE_MASTER', $EMPLOYEE_MASTER1, 'update', " PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_EMPLOYEE_MASTER = '$PK_EMPLOYEE_MASTER' ");

				do {
					$USER_API_KEY = generateRandomString(60);
					$res_key = $db->Execute("SELECT PK_USER FROM Z_USER where USER_API_KEY = '$USER_API_KEY'");
				} while ($res_key->RecordCount() > 0);

				$salt = substr(strtr(base64_encode(openssl_random_pseudo_bytes(22)), '+', '.'), 0, 22);
				$hash = crypt($_POST['PASSWORD'], '$2y$12$' . $salt);
				$USER['PASSWORD']  	 	= $hash;
				$USER['ID']  	 	 	= $PK_EMPLOYEE_MASTER;
				$USER['USER_API_KEY']  	= $USER_API_KEY;
				$USER['PK_ROLES']  		= $PK_ROLES;
				$USER['USER_ID']  		= $_POST['EMAIL'];
				$USER['PK_USER_TYPE']  	= 2;
				$USER['FIRST_LOGIN']  	= 1;
				$USER['PK_LANGUAGE']  	= $_POST['PK_LANGUAGE'];
				$USER['PK_ACCOUNT']  	= $_SESSION['PK_ACCOUNT'];
				$USER['CREATED_BY']  	= $_SESSION['PK_USER'];
				$USER['CREATED_ON']  	= date("Y-m-d H:i");
				db_perform('Z_USER', $USER, 'insert');

				$res_noti = $db->Execute("SELECT PK_EMAIL_TEMPLATE,PK_TEXT_TEMPLATE FROM S_NOTIFICATION_SETTINGS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_EVENT_TYPE = 15");
				if ($res_noti->RecordCount() > 0) {
					if ($res_noti->fields['PK_EMAIL_TEMPLATE'] > 0) {
						send_instructor_portal_access_mail($PK_EMPLOYEE_MASTER, $res_noti->fields['PK_EMAIL_TEMPLATE'], $USER['USER_ID'], $_POST['PASSWORD']);
					}

					if ($res_noti->fields['PK_TEXT_TEMPLATE'] > 0) {
						send_instructor_portal_access_text($PK_EMPLOYEE_MASTER, $res_noti->fields['PK_TEXT_TEMPLATE'], $USER['USER_ID'], $_POST['PASSWORD']);
					}
				}
			}

			$CUR_ACTIVE = 1;
		} else {

			$PK_EMPLOYEE_MASTER = $_GET['id'];

			$res_noti = $db->Execute("SELECT ACTIVE FROM S_EMPLOYEE_MASTER WHERE PK_EMPLOYEE_MASTER = '$PK_EMPLOYEE_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$CUR_ACTIVE = $res_noti->fields['ACTIVE'];

			$EMPLOYEE_MASTER['ACTIVE']  	= $_POST['ACTIVE'];
			$EMPLOYEE_MASTER['EDITED_BY']   = $_SESSION['PK_USER'];
			$EMPLOYEE_MASTER['EDITED_ON']   = date("Y-m-d H:i");
			db_perform('S_EMPLOYEE_MASTER', $EMPLOYEE_MASTER, 'update', " PK_EMPLOYEE_MASTER = '$PK_EMPLOYEE_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");

			$EMPLOYEE_CONTACT['ACTIVE']  	 = $_POST['ACTIVE'];
			$EMPLOYEE_CONTACT['EDITED_BY']   = $_SESSION['PK_USER'];
			$EMPLOYEE_CONTACT['EDITED_ON']   = date("Y-m-d H:i");
			db_perform('S_EMPLOYEE_CONTACT', $EMPLOYEE_CONTACT, 'update', " PK_EMPLOYEE_MASTER = '$PK_EMPLOYEE_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ");

			if (isset($_POST['PK_LANGUAGE'])) {
				if ($_POST['IS_ADMIN'] == 1)
					$PK_ROLES = 2;
				else
					$PK_ROLES = 3;

				$EMPLOYEE_MASTER1['IS_ADMIN'] = $_POST['IS_ADMIN'];
				db_perform('S_EMPLOYEE_MASTER', $EMPLOYEE_MASTER1, 'update', " PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_EMPLOYEE_MASTER = '$PK_EMPLOYEE_MASTER' ");

				//$USER['USER_ID']   		= $_POST['USER_ID'];
				if ($EMPLOYEE_MASTER['EMAIL'] != '')
					$USER['USER_ID'] = $EMPLOYEE_MASTER['EMAIL'];
				else
					$USER['USER_ID'] = $_POST['USER_ID'];

				$USER['PK_LANGUAGE']   	= $_POST['PK_LANGUAGE'];
				$USER['ACTIVE']   		= $_POST['ACTIVE'];
				$USER['PK_ROLES'] 		= $PK_ROLES;
				db_perform('Z_USER', $USER, 'update', " ID = '$PK_EMPLOYEE_MASTER' AND PK_USER_TYPE = 2 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ");
			}
		}

		if ($CUR_ACTIVE == 1 && $EMPLOYEE_MASTER['ACTIVE'] == '') {
			$EMPLOYEE_MASTER2['INACTIVATED_ON'] = date("Y-m-s H:i:s");
			db_perform('S_EMPLOYEE_MASTER', $EMPLOYEE_MASTER2, 'update', " PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_EMPLOYEE_MASTER = '$PK_EMPLOYEE_MASTER' ");
		} else if ($EMPLOYEE_MASTER['ACTIVE'] == 1) {
			$EMPLOYEE_MASTER2['INACTIVATED_ON'] = '';
			db_perform('S_EMPLOYEE_MASTER', $EMPLOYEE_MASTER2, 'update', " PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_EMPLOYEE_MASTER = '$PK_EMPLOYEE_MASTER' ");
		}

		if (isset($_POST['PK_LANGUAGE'])) {
			$res = $db->Execute("SELECT PK_USER FROM Z_USER WHERE ID = '$PK_EMPLOYEE_MASTER' AND PK_USER_TYPE = 2 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			if ($res->RecordCount() > 0) {
				$PK_USER = $res->fields['PK_USER'];

				if ($_POST['IS_ADMIN'] == 1 && $_GET['id'] == '') {
					$_POST['ADMISSION_ACCESS']				= 3;
					$_POST['REGISTRAR_ACCESS']				= 3;
					$_POST['FINANCE_ACCESS']				= 3;
					$_POST['ACCOUNTING_ACCESS']				= 3;
					$_POST['PLACEMENT_ACCESS']				= 3;
					$_POST['MANAGEMENT_ADMISSION']			= 1;
					$_POST['MANAGEMENT_REGISTRAR']			= 1;
					$_POST['MANAGEMENT_FINANCE']			= 1;
					$_POST['MANAGEMENT_ACCOUNTING']			= 1;
					$_POST['MANAGEMENT_PLACEMENT']			= 1;
					$_POST['MANAGEMENT_ACCREDITATION']		= 1;
					$_POST['MANAGEMENT_TITLE_IV_SERVICER']	= 1;

					$_POST['MANAGEMENT_90_10']				= 1;
					$_POST['MANAGEMENT_IPEDS']				= 1;
					$_POST['MANAGEMENT_POPULATION_REPORT']	= 1;
					$_POST['MANAGEMENT_CUSTOM_QUERY']		= 1; //Ticket # 1295
					$_POST['MANAGEMENT_FISAP']				= 1; //Ticket # 1778 
					$_POST['MANAGEMENT_BULK_UPDATE']		= 1; //Ticket # 1911
					$_POST['MANAGEMENT_DIAMOND_PAY']		= 1; //Ticket # 1940	
					$_POST['MANAGEMENT_DATA_TOOLS']			= 1; //Ticket # 569
					$_POST['MANAGEMENT_UNPOST_BATCHES']		= 1; //Ticket # 1940		
					//$_POST['MANAGEMENT_CUSTOM']			= 1; //Ticket # 569	
					$_POST['MANAGEMENT_CUSTOM_REPORT']			= 1; // DIAM-2090
					$_POST['MANAGEMENT_COMPLIANCE']			= 1; // DVB 25 03 2025

					//$_POST['MANAGEMENT_UPLOADS']			= 1; Ticket # 921
					$_POST['REPORT_ADMISSION']				= 1;
					$_POST['REPORT_REGISTRAR']				= 1;
					$_POST['REPORT_FINANCE']				= 1;
					$_POST['REPORT_ACCOUNTING']				= 1;
					$_POST['REPORT_PLACEMENT']				= 1;
					$_POST['REPORT_CUSTOM_REPORT']			= 1;
					$_POST['REPORT_COMPLIANCE_REPORTS']		= 1;
					$_POST['SETUP_SCHOOL']					= 1;
					$_POST['SETUP_ADMISSION']				= 1;
					$_POST['SETUP_STUDENT']					= 1;
					$_POST['SETUP_FINANCE']					= 1;
					$_POST['SETUP_REGISTRAR']				= 1;
					$_POST['SETUP_ACCOUNTING']				= 1;
					$_POST['SETUP_PLACEMENT']				= 1;
					$_POST['SETUP_COMMUNICATION']			= 1;
					$_POST['SETUP_TASK_MANAGEMENT']			= 1;

					$_POST['CONSOLIDATION_TOOL']			= 1; //Ticket # 1357
				}

				$USER_ACCESS['ADMISSION_ACCESS']   			= $_POST['ADMISSION_ACCESS'];
				$USER_ACCESS['REGISTRAR_ACCESS']   			= $_POST['REGISTRAR_ACCESS'];
				$USER_ACCESS['FINANCE_ACCESS']   			= $_POST['FINANCE_ACCESS'];
				$USER_ACCESS['ACCOUNTING_ACCESS']   		= $_POST['ACCOUNTING_ACCESS'];
				$USER_ACCESS['PLACEMENT_ACCESS']   			= $_POST['PLACEMENT_ACCESS'];
				$USER_ACCESS['MANAGEMENT_ADMISSION']   		= $_POST['MANAGEMENT_ADMISSION'];
				$USER_ACCESS['MANAGEMENT_REGISTRAR']   		= $_POST['MANAGEMENT_REGISTRAR'];
				$USER_ACCESS['MANAGEMENT_FINANCE']   		= $_POST['MANAGEMENT_FINANCE'];
				$USER_ACCESS['MANAGEMENT_ACCOUNTING']   	= $_POST['MANAGEMENT_ACCOUNTING'];
				$USER_ACCESS['MANAGEMENT_PLACEMENT']   		= $_POST['MANAGEMENT_PLACEMENT'];
				$USER_ACCESS['MANAGEMENT_ACCREDITATION']   	= $_POST['MANAGEMENT_ACCREDITATION'];
				$USER_ACCESS['MANAGEMENT_TITLE_IV_SERVICER'] = $_POST['MANAGEMENT_TITLE_IV_SERVICER'];

				$USER_ACCESS['MANAGEMENT_FISAP']			= $_POST['MANAGEMENT_FISAP']; //Ticket # 1778 
				$USER_ACCESS['MANAGEMENT_BULK_UPDATE']		= $_POST['MANAGEMENT_BULK_UPDATE']; //Ticket # 1911
				$USER_ACCESS['MANAGEMENT_DIAMOND_PAY']		= $_POST['MANAGEMENT_DIAMOND_PAY']; //Ticket # 1940

				$USER_ACCESS['MANAGEMENT_UNPOST_BATCHES']	= $_POST['MANAGEMENT_UNPOST_BATCHES']; //Ticket # 1940

				$USER_ACCESS['MANAGEMENT_DATA_TOOLS']		= $_POST['MANAGEMENT_DATA_TOOLS']; //Ticket # 569
				//$USER_ACCESS['MANAGEMENT_CUSTOM']			= $_POST['MANAGEMENT_CUSTOM']; //DIAM-842
				$USER_ACCESS['MANAGEMENT_90_10']			= $_POST['MANAGEMENT_90_10'];
				$USER_ACCESS['MANAGEMENT_IPEDS']			= $_POST['MANAGEMENT_IPEDS'];
				$USER_ACCESS['MANAGEMENT_POPULATION_REPORT'] = $_POST['MANAGEMENT_POPULATION_REPORT'];
				$USER_ACCESS['MANAGEMENT_CUSTOM_QUERY']		= $_POST['MANAGEMENT_CUSTOM_QUERY']; //Ticket # 1295
				$USER_ACCESS['MANAGEMENT_CUSTOM_REPORT']			= $_POST['MANAGEMENT_CUSTOM_REPORT']; //DIAM-2090
				$USER_ACCESS['MANAGEMENT_COMPLIANCE']			= $_POST['MANAGEMENT_COMPLIANCE']; //dvb 25 03 2025

				//$USER_ACCESS['MANAGEMENT_UPLOADS']   		= $_POST['MANAGEMENT_UPLOADS']; Ticket # 921
				$USER_ACCESS['REPORT_ADMISSION']   			= $_POST['REPORT_ADMISSION'];
				$USER_ACCESS['REPORT_REGISTRAR']   			= $_POST['REPORT_REGISTRAR'];
				$USER_ACCESS['REPORT_FINANCE']   			= $_POST['REPORT_FINANCE'];
				$USER_ACCESS['REPORT_ACCOUNTING']   		= $_POST['REPORT_ACCOUNTING'];
				$USER_ACCESS['REPORT_PLACEMENT']   			= $_POST['REPORT_PLACEMENT'];
				$USER_ACCESS['REPORT_CUSTOM_REPORT']   		= $_POST['REPORT_CUSTOM_REPORT'];
				$USER_ACCESS['REPORT_COMPLIANCE_REPORTS']	= $_POST['REPORT_COMPLIANCE_REPORTS'];
				$USER_ACCESS['SETUP_SCHOOL']   				= $_POST['SETUP_SCHOOL'];
				$USER_ACCESS['SETUP_ADMISSION']   			= $_POST['SETUP_ADMISSION'];
				$USER_ACCESS['SETUP_STUDENT']   			= $_POST['SETUP_STUDENT'];
				$USER_ACCESS['SETUP_FINANCE']   			= $_POST['SETUP_FINANCE'];
				$USER_ACCESS['SETUP_REGISTRAR']   			= $_POST['SETUP_REGISTRAR'];
				$USER_ACCESS['SETUP_ACCOUNTING']   			= $_POST['SETUP_ACCOUNTING'];
				$USER_ACCESS['SETUP_PLACEMENT']   			= $_POST['SETUP_PLACEMENT'];
				$USER_ACCESS['SETUP_COMMUNICATION']   		= $_POST['SETUP_COMMUNICATION'];
				$USER_ACCESS['SETUP_TASK_MANAGEMENT']   	= $_POST['SETUP_TASK_MANAGEMENT'];
				$USER_ACCESS['SETUP_CONSOLIDATION_TOOL']   	= $_POST['SETUP_CONSOLIDATION_TOOL'];  // DIAM-2177
				$USER_ACCESS['SETUP_DATA_EXPORT']   		= $_POST['SETUP_DATA_EXPORT'];  // DIAM-2177


				$res = $db->Execute("SELECT PK_USER_ACCESS FROM Z_USER_ACCESS WHERE PK_USER = '$PK_USER'  AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
				if ($res->RecordCount() == 0) {
					$USER_ACCESS['PK_USER']  	= $PK_USER;
					$USER_ACCESS['PK_ACCOUNT']  = $_SESSION['PK_ACCOUNT'];
					$USER_ACCESS['CREATED_BY']  = $_SESSION['PK_USER'];
					$USER_ACCESS['CREATED_ON']  = date("Y-m-d H:i");
					db_perform('Z_USER_ACCESS', $USER_ACCESS, 'insert');
				} else {
					$USER_ACCESS['EDITED_BY']  = $_SESSION['PK_USER'];
					$USER_ACCESS['EDITED_ON']  = date("Y-m-d H:i");
					db_perform('Z_USER_ACCESS', $USER_ACCESS, 'update', " PK_USER = '$PK_USER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
				}
			}
		}


		// $file_dir_1 = '../backend_assets/school/school_'.$_SESSION['PK_ACCOUNT'].'/employee/';
		$file_dir_1 = '../backend_assets/tmp_upload/';
		if ($_FILES['IMAGE']['name'] != '') {
			require_once("../global/image_fun.php");
			$extn 			= explode(".", $_FILES['IMAGE']['name']);
			$iindex			= count($extn) - 1;
			$rand_string 	= time() . "-" . rand(10000, 99999);
			$file11			= 'stu_profile_' . $_SESSION['PK_USER'] . $rand_string . "." . $extn[$iindex];
			$extension   	= strtolower($extn[$iindex]);

			if ($extension == "gif" || $extension == "jpeg" || $extension == "pjpeg" || $extension == "png" || $extension == "jpg") {
				$newfile1    = $file_dir_1 . $file11;
				$image_path  = $newfile1;

				move_uploaded_file($_FILES['IMAGE']['tmp_name'], $image_path);
				$size = getimagesize($image_path);
				$new_w = 500;
				$new_h = 500;

				if ($size['0'] > $new_w || $size['1'] >  $new_h) {
					$image_path = thumb_gallery($file11, $file11, $new_w, $new_h, $file_dir_1, 1);
				}

				// Upload file to S3 bucket
				$key_file_name = 'backend_assets/school/school_' . $_SESSION['PK_ACCOUNT'] . '/employee/' . $file11;
				$s3ClientWrapper = new s3ClientWrapper();
				$url = $s3ClientWrapper->uploadFile($key_file_name, $image_path);

				// $EMPLOYEE_MASTER1['IMAGE'] = $image_path;
				$EMPLOYEE_MASTER1['IMAGE'] = $url;
				db_perform('S_EMPLOYEE_MASTER', $EMPLOYEE_MASTER1, 'update', " PK_EMPLOYEE_MASTER = '$PK_EMPLOYEE_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");

				// delete tmp file
				unlink($image_path);
			}
		}

		if ($_POST['SSN'] != '') {
			$EMPLOYEE_MASTER2['SSN'] = my_encrypt($_SESSION['PK_ACCOUNT'] . $PK_EMPLOYEE_MASTER, $_POST['SSN']);
			db_perform('S_EMPLOYEE_MASTER', $EMPLOYEE_MASTER2, 'update', " PK_EMPLOYEE_MASTER = '$PK_EMPLOYEE_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		}

		$i = 0;
		foreach ($_POST['PK_CUSTOM_FIELDS'] as $PK_CUSTOM_FIELDS) {
			$CUSTOM_FIELDS = array();

			if ($_POST['PK_DATA_TYPES'][$i] == 1 || $_POST['PK_DATA_TYPES'][$i] == 2)
				$CUSTOM_FIELDS['FIELD_VALUE'] = $_POST['CUSTOM_FIELDS_' . $PK_CUSTOM_FIELDS];
			else if ($_POST['PK_DATA_TYPES'][$i] == 3) {
				$CUSTOM_FIELDS['FIELD_VALUE'] = implode(",", $_POST['CUSTOM_FIELDS_' . $PK_CUSTOM_FIELDS]);
			} else if ($_POST['PK_DATA_TYPES'][$i] == 4) {
				if ($_POST['CUSTOM_FIELDS_' . $PK_CUSTOM_FIELDS] != '')
					$CUSTOM_FIELDS['FIELD_VALUE'] = date("Y-m-d", strtotime($_POST['CUSTOM_FIELDS_' . $PK_CUSTOM_FIELDS]));
				else
					$CUSTOM_FIELDS['FIELD_VALUE'] = '';
			}

			$res_1 = $db->Execute("select PK_EMPLOYEE_CUSTOM_FIELDS from S_EMPLOYEE_CUSTOM_FIELDS WHERE PK_EMPLOYEE_MASTER = '$PK_EMPLOYEE_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CUSTOM_FIELDS = '$PK_CUSTOM_FIELDS' ");
			if ($res_1->RecordCount() == 0) {
				$CUSTOM_FIELDS['PK_ACCOUNT'] 		= $_SESSION['PK_ACCOUNT'];
				$CUSTOM_FIELDS['PK_EMPLOYEE_MASTER'] = $PK_EMPLOYEE_MASTER;
				$CUSTOM_FIELDS['PK_CUSTOM_FIELDS'] 	= $PK_CUSTOM_FIELDS;
				$CUSTOM_FIELDS['FIELD_NAME'] 		= $_POST['FIELD_NAME'][$i];
				$CUSTOM_FIELDS['CREATED_BY'] 		= $_SESSION['PK_USER'];
				$CUSTOM_FIELDS['CREATED_ON']  		= date("Y-m-d H:i");
				db_perform('S_EMPLOYEE_CUSTOM_FIELDS', $CUSTOM_FIELDS, 'insert');
			} else {
				$CUSTOM_FIELDS['EDITED_BY'] 		= $_SESSION['PK_USER'];
				$CUSTOM_FIELDS['EDITED_ON']  		= date("Y-m-d H:i");
				db_perform('S_EMPLOYEE_CUSTOM_FIELDS', $CUSTOM_FIELDS, 'update', " PK_EMPLOYEE_CUSTOM_FIELDS = '" . $res_1->fields['PK_EMPLOYEE_CUSTOM_FIELDS'] . "' ");
			}

			$i++;
		}

		foreach ($_POST['PK_DEPARTMENT'] as $PK_DEPARTMENT) {
			$res = $db->Execute("SELECT PK_EMPLOYEE_DEPARTMENT FROM S_EMPLOYEE_DEPARTMENT WHERE PK_EMPLOYEE_MASTER = '$PK_EMPLOYEE_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_DEPARTMENT = '$PK_DEPARTMENT' ");
			if ($res->RecordCount() == 0) {
				$EMPLOYEE_DEPARTMENT['PK_DEPARTMENT']   	= $PK_DEPARTMENT;
				$EMPLOYEE_DEPARTMENT['PK_EMPLOYEE_MASTER'] 	= $PK_EMPLOYEE_MASTER;
				$EMPLOYEE_DEPARTMENT['PK_ACCOUNT'] 			= $_SESSION['PK_ACCOUNT'];
				$EMPLOYEE_DEPARTMENT['CREATED_BY']  		= $_SESSION['PK_USER'];
				$EMPLOYEE_DEPARTMENT['CREATED_ON']  		= date("Y-m-d H:i");
				db_perform('S_EMPLOYEE_DEPARTMENT', $EMPLOYEE_DEPARTMENT, 'insert');
				$PK_EMPLOYEE_DEPARTMENT_ARR[] = $db->insert_ID();
			} else {
				$PK_EMPLOYEE_DEPARTMENT_ARR[] = $res->fields['PK_EMPLOYEE_DEPARTMENT'];
			}
		}

		$cond = "";
		if (!empty($PK_EMPLOYEE_DEPARTMENT_ARR))
			$cond = " AND PK_EMPLOYEE_DEPARTMENT NOT IN (" . implode(",", $PK_EMPLOYEE_DEPARTMENT_ARR) . ") ";
		$db->Execute("DELETE FROM S_EMPLOYEE_DEPARTMENT WHERE PK_EMPLOYEE_MASTER = '$PK_EMPLOYEE_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond ");

		foreach ($_POST['PK_CAMPUS'] as $PK_CAMPUS) {
			$res = $db->Execute("SELECT PK_EMPLOYEE_CAMPUS FROM S_EMPLOYEE_CAMPUS WHERE PK_EMPLOYEE_MASTER = '$PK_EMPLOYEE_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS = '$PK_CAMPUS' ");
			if ($res->RecordCount() == 0) {
				$EMPLOYEE_CAMPUS['PK_CAMPUS']   		= $PK_CAMPUS;
				$EMPLOYEE_CAMPUS['PK_EMPLOYEE_MASTER'] 	= $PK_EMPLOYEE_MASTER;
				$EMPLOYEE_CAMPUS['PK_ACCOUNT'] 			= $_SESSION['PK_ACCOUNT'];
				$EMPLOYEE_CAMPUS['CREATED_BY']  		= $_SESSION['PK_USER'];
				$EMPLOYEE_CAMPUS['CREATED_ON']  		= date("Y-m-d H:i");
				db_perform('S_EMPLOYEE_CAMPUS', $EMPLOYEE_CAMPUS, 'insert');
				$PK_EMPLOYEE_CAMPUS_ARR[] = $db->insert_ID();
			} else {
				$PK_EMPLOYEE_CAMPUS_ARR[] = $res->fields['PK_EMPLOYEE_CAMPUS'];
			}
		}

		$cond = "";
		if (!empty($PK_EMPLOYEE_CAMPUS_ARR))
			$cond = " AND PK_EMPLOYEE_CAMPUS NOT IN (" . implode(",", $PK_EMPLOYEE_CAMPUS_ARR) . ") ";

		if ($_SESSION['PK_ROLES'] == 2)
			$db->Execute("DELETE FROM S_EMPLOYEE_CAMPUS WHERE PK_EMPLOYEE_MASTER = '$PK_EMPLOYEE_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond ");

		foreach ($_POST['RACE'] as $PK_RACE) {
			$res = $db->Execute("SELECT PK_EMPLOYEE_RACE FROM S_EMPLOYEE_RACE WHERE PK_EMPLOYEE_MASTER = '$PK_EMPLOYEE_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_RACE = '$PK_RACE' ");
			if ($res->RecordCount() == 0) {
				$EMPLOYEE_RACE['PK_RACE']   			= $PK_RACE;
				$EMPLOYEE_RACE['PK_EMPLOYEE_MASTER'] 	= $PK_EMPLOYEE_MASTER;
				$EMPLOYEE_RACE['PK_ACCOUNT'] 			= $_SESSION['PK_ACCOUNT'];
				$EMPLOYEE_RACE['CREATED_BY']  			= $_SESSION['PK_USER'];
				$EMPLOYEE_RACE['CREATED_ON']  			= date("Y-m-d H:i");
				db_perform('S_EMPLOYEE_RACE', $EMPLOYEE_RACE, 'insert');
				$PK_EMPLOYEE_RACE_ARR[] = $db->insert_ID();
			} else {
				$PK_EMPLOYEE_RACE_ARR[] = $res->fields['PK_EMPLOYEE_RACE'];
			}
		}

		$cond = "";
		if (!empty($PK_EMPLOYEE_RACE_ARR))
			$cond = " AND PK_EMPLOYEE_RACE NOT IN (" . implode(",", $PK_EMPLOYEE_RACE_ARR) . ") ";

		$db->Execute("DELETE FROM S_EMPLOYEE_RACE WHERE PK_EMPLOYEE_MASTER = '$PK_EMPLOYEE_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond ");

		/* Ticket # 1737 */
		if (isset($_POST['MOODLE_ID'])) {
			$res_ethink = $db->Execute("SELECT ETHINK_ID FROM S_EMPLOYEE_MASTER_ETHINK WHERE PK_ACCOUNT = '$PK_ACCOUNT' AND PK_EMPLOYEE_MASTER = '$PK_EMPLOYEE_MASTER' ");
			if ($res_ethink->RecordCount() > 0)
				$db->Execute("UPDATE S_EMPLOYEE_MASTER_ETHINK SET ETHINK_ID = '$_POST[MOODLE_ID]' WHERE PK_ACCOUNT = '$PK_ACCOUNT' AND PK_EMPLOYEE_MASTER = '$PK_EMPLOYEE_MASTER' ");
			else {
				$ETHINK['PK_ACCOUNT'] 			= $_SESSION['PK_ACCOUNT'];
				$ETHINK['CREATED_ON'] 			= date("Y-m-d H:i:s");
				$ETHINK['CREATED_BY'] 			= $_SESSION['PK_USER'];
				$ETHINK['PK_EMPLOYEE_MASTER'] 	= $PK_EMPLOYEE_MASTER;
				$ETHINK['ETHINK_ID'] 			= $_POST['MOODLE_ID'];
				db_perform('S_EMPLOYEE_MASTER_ETHINK', $ETHINK, 'insert');
			}
		}
		/* Ticket # 1737 */

		if ($_POST['SAVE_CONTINUE'] == 1) {
			header("location:employee?id=" . $PK_EMPLOYEE_MASTER . '&t=' . $_GET['t'] . "&tab=" . str_replace("#", "", $_POST['current_tab']));
		} else {
			if ($_GET['cid'] != '')
				header("location:campus?id=" . $_GET['cid'] . '&tab=usersTab');
			else if ($_GET['id'] == '')
				header("location:employee?id=" . $PK_EMPLOYEE_MASTER . '&t=' . $_GET['t']);
			else
				header("location:manage_employee?t=" . $_GET['t']);
		}
	} else if ($_POST['FORM_NAME'] == 'ssn') {
		if ($_POST['SSN_1'] != '') {
			$EMPLOYEE_MASTER2['SSN'] = my_encrypt($_SESSION['PK_ACCOUNT'] . $PK_EMPLOYEE_MASTER, $_POST['SSN_1']);
			db_perform('S_EMPLOYEE_MASTER', $EMPLOYEE_MASTER2, 'update', " PK_EMPLOYEE_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		}

		header("location:employee?id=" . $_GET['id'] . '&t=' . $_GET['t']);
	} else if ($_POST['FORM_NAME'] == 'notes') {
		/*$EMPLOYEE_NOTES['PK_ACCOUNT']   		 = $_SESSION['PK_ACCOUNT'];
		$EMPLOYEE_NOTES['PK_EMPLOYEE_MASTER'] 	 = $_GET['id'];
		$EMPLOYEE_NOTES['PK_EMPLOYEE_NOTE_TYPE'] = $_POST['PK_EMPLOYEE_NOTE_TYPE'];
		$EMPLOYEE_NOTES['NOTES'] 				 = $_POST['NOTES'];
		
		$EMPLOYEE_NOTES['CREATED_BY']  			= $_SESSION['PK_USER'];
		$EMPLOYEE_NOTES['CREATED_ON']  			= date("Y-m-d H:i");
		db_perform('S_EMPLOYEE_NOTES', $EMPLOYEE_NOTES, 'insert');
		
		header("location:employee?id=".$_GET['id'].'&t='.$_GET['t'].'&tab=notes');*/
	}
}

if ($_GET['id'] == '') {
	$PK_PRE_FIX				= '';
	$FIRST_NAME 			= '';
	$LAST_NAME 				= '';
	$MIDDLE_NAME	 		= '';
	$SSN	 				= '';
	$EMPLOYEE_ID	 		= '';
	$TITLE	 				= '';
	$EMAIL	 				= '';
	$EMAIL_OTHER	 		= '';
	$DOB	 				= '';
	$GENDER	 				= '';
	$PK_MARITAL_STATUS	 	= '';
	$IPEDS_ETHNICITY	 	= '';
	$NETWORK_ID	 			= '';
	$COMPANY_EMP_ID	 		= '';
	$SUPERVISOR	 			= '';
	$FULL_PART_TIME	 		= '';
	$ELIGIBLE_FOR_REHIRE	= '';
	$PK_SOC_CODE	 		= '';
	$DATE_HIRED	 			= '';
	$DATE_TERMINATED	 	= '';
	$PK_DEPARTMENT	 		= '';
	$ADDRESS	 			= '';
	$ADDRESS_1	 			= '';
	$CITY	 				= '';
	$PK_STATES	 			= '';
	$ZIP	 				= '';
	$PK_COUNTRY	 			= '';
	$HOME_PHONE	 			= '';
	$WORK_PHONE	 			= '';
	$CELL_PHONE	 			= '';
	$IMAGE	 				= '';
	$LOGIN_CREATED			= 0;
	$IS_FACULTY				= 0;
	$PK_SUPERVISOR			= '';

	if ($_GET['t'] == 2) {
		$res = $db->Execute("select PK_DEPARTMENT from M_DEPARTMENT WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_DEPARTMENT_MASTER = 3");
		$PK_DEPARTMENT 	= $res->fields['PK_DEPARTMENT'];
		$IS_FACULTY 	= 1;
	}

	/* Ticket #849  */
	$def_camp = "";
	if ($_GET['cid'] != '')
		$def_camp = $_GET['cid'];
	else {
		$res_camp = $db->Execute("select PK_CAMPUS from S_CAMPUS WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		if ($res_camp->RecordCount() == 1)
			$def_camp = $res_camp->fields['PK_CAMPUS'];
	}
	/* Ticket #849  */
} else {
	//echo "SELECT * FROM S_EMPLOYEE_MASTER WHERE PK_EMPLOYEE_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ";exit;
	$res = $db->Execute("SELECT * FROM S_EMPLOYEE_MASTER WHERE PK_EMPLOYEE_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	if ($res->RecordCount() == 0) {
		header("location:manage_employee?t=" . $_GET['t']);
		exit;
	}

	$IMAGE	 				= $res->fields['IMAGE'];
	$PK_PRE_FIX 			= $res->fields['PK_PRE_FIX'];
	$FIRST_NAME 			= $res->fields['FIRST_NAME'];
	$LAST_NAME 				= $res->fields['LAST_NAME'];
	$MIDDLE_NAME  			= $res->fields['MIDDLE_NAME'];
	$SSN 					= $res->fields['SSN'];
	$EMPLOYEE_ID			= $res->fields['EMPLOYEE_ID'];
	$TITLE					= $res->fields['TITLE'];
	$EMAIL					= $res->fields['EMAIL'];
	$EMAIL_OTHER			= $res->fields['EMAIL_OTHER'];
	$DOB					= $res->fields['DOB'];
	$GENDER					= $res->fields['GENDER'];
	$PK_MARITAL_STATUS		= $res->fields['PK_MARITAL_STATUS'];
	$IPEDS_ETHNICITY		= $res->fields['IPEDS_ETHNICITY'];
	$NETWORK_ID				= $res->fields['NETWORK_ID'];
	$COMPANY_EMP_ID			= $res->fields['COMPANY_EMP_ID'];
	$SUPERVISOR				= $res->fields['SUPERVISOR'];
	$FULL_PART_TIME			= $res->fields['FULL_PART_TIME'];
	$ELIGIBLE_FOR_REHIRE	= $res->fields['ELIGIBLE_FOR_REHIRE'];
	$PK_SOC_CODE			= $res->fields['PK_SOC_CODE'];
	$DATE_HIRED				= $res->fields['DATE_HIRED'];
	$DATE_TERMINATED		= $res->fields['DATE_TERMINATED'];
	$PK_DEPARTMENT			= $res->fields['PK_DEPARTMENT'];
	$ACTIVE					= $res->fields['ACTIVE'];
	$LOGIN_CREATED			= $res->fields['LOGIN_CREATED'];
	$TURN_OFF_ASSIGNMENTS	= $res->fields['TURN_OFF_ASSIGNMENTS'];
	$NEED_SCHOOL_ACCESS		= $res->fields['NEED_SCHOOL_ACCESS'];
	$IS_FACULTY				= $res->fields['IS_FACULTY'];
	$IS_ADMIN				= $res->fields['IS_ADMIN'];
	$PK_SUPERVISOR			= $res->fields['PK_SUPERVISOR'];
	$INTERNAL_MESSAGE_ENABLED = $res->fields['INTERNAL_MESSAGE_ENABLED']; //ticket #967  

	$SSN_ORG = '';
	if ($SSN != '') {
		$SSN 	 = my_decrypt($_SESSION['PK_ACCOUNT'] . $_GET['id'], $SSN);
		$SSN_ORG = $SSN;
		$SSN_ARR = explode("-", $SSN);
		$SSN 	 = 'xxx-xx-' . $SSN_ARR[2];
	}

	/* Ticket # 1426 */
	if ($LOGIN_CREATED == 0) {
		$res_user  = $db->Execute("SELECT PK_USER FROM Z_USER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_USER_TYPE = 2 AND ID = '$_GET[id]' ");
		if ($res_user->RecordCount() > 0) {
			$EMPLOYEE_MASTER23['LOGIN_CREATED'] = 1;
			db_perform('S_EMPLOYEE_MASTER', $EMPLOYEE_MASTER23, 'update', " PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_EMPLOYEE_MASTER = '$_GET[id]' ");
			$LOGIN_CREATED = 1;
		}
	}
	/* Ticket # 1426 */

	if ($LOGIN_CREATED == 1) {
		$res = $db->Execute("SELECT PK_USER,PK_ROLES,USER_ID,PK_LANGUAGE FROM Z_USER WHERE Z_USER.ID = '$_GET[id]' AND PK_USER_TYPE = 2 ");
		$PK_ROLES		= $res->fields['PK_ROLES'];
		$USER_ID		= $res->fields['USER_ID'];
		$PK_LANGUAGE	= $res->fields['PK_LANGUAGE'];
		$PK_USER		= $res->fields['PK_USER'];
	}

	if ($DOB != '0000-00-00')
		$DOB = date("m/d/Y", strtotime($DOB));
	else
		$DOB = '';

	if ($DATE_HIRED != '0000-00-00')
		$DATE_HIRED = date("m/d/Y", strtotime($DATE_HIRED));
	else
		$DATE_HIRED = '';

	if ($DATE_TERMINATED != '0000-00-00')
		$DATE_TERMINATED = date("m/d/Y", strtotime($DATE_TERMINATED));
	else
		$DATE_TERMINATED = '';

	$res = $db->Execute("SELECT * FROM S_EMPLOYEE_CONTACT WHERE PK_EMPLOYEE_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	$ADDRESS				= $res->fields['ADDRESS'];
	$ADDRESS_1				= $res->fields['ADDRESS_1'];
	$CITY					= $res->fields['CITY'];
	$PK_STATES				= $res->fields['PK_STATES'];
	$ZIP					= $res->fields['ZIP'];
	$PK_COUNTRY				= $res->fields['PK_COUNTRY'];
	$HOME_PHONE				= $res->fields['HOME_PHONE'];
	$WORK_PHONE				= $res->fields['WORK_PHONE'];
	$CELL_PHONE				= $res->fields['CELL_PHONE'];

	$res = $db->Execute("SELECT PK_USER FROM Z_USER WHERE ID = '$_GET[id]' AND PK_USER_TYPE = 2 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	if ($res->RecordCount() > 0) {
		$PK_USER = $res->fields['PK_USER'];
		$res = $db->Execute("SELECT * FROM Z_USER_ACCESS WHERE PK_USER = '$PK_USER'  AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		if ($res->RecordCount() > 0) {
			$ADMISSION_ACCESS	= $res->fields['ADMISSION_ACCESS'];
			$REGISTRAR_ACCESS	= $res->fields['REGISTRAR_ACCESS'];
			$FINANCE_ACCESS		= $res->fields['FINANCE_ACCESS'];
			$ACCOUNTING_ACCESS	= $res->fields['ACCOUNTING_ACCESS'];
			$PLACEMENT_ACCESS	= $res->fields['PLACEMENT_ACCESS'];

			$MANAGEMENT_ADMISSION			= $res->fields['MANAGEMENT_ADMISSION'];
			$MANAGEMENT_REGISTRAR			= $res->fields['MANAGEMENT_REGISTRAR'];
			$MANAGEMENT_FINANCE				= $res->fields['MANAGEMENT_FINANCE'];
			$MANAGEMENT_ACCOUNTING			= $res->fields['MANAGEMENT_ACCOUNTING'];
			$MANAGEMENT_PLACEMENT			= $res->fields['MANAGEMENT_PLACEMENT'];
			$MANAGEMENT_ACCREDITATION		= $res->fields['MANAGEMENT_ACCREDITATION'];
			$MANAGEMENT_TITLE_IV_SERVICER 	= $res->fields['MANAGEMENT_TITLE_IV_SERVICER'];
			//$MANAGEMENT_UPLOADS				= $res->fields['MANAGEMENT_UPLOADS']; Ticket # 921

			$MANAGEMENT_90_10 				= $res->fields['MANAGEMENT_90_10'];
			$MANAGEMENT_FISAP				= $res->fields['MANAGEMENT_FISAP']; //Ticket # 1778 
			$MANAGEMENT_BULK_UPDATE			= $res->fields['MANAGEMENT_BULK_UPDATE']; //Ticket # 1911
			$MANAGEMENT_DIAMOND_PAY			= $res->fields['MANAGEMENT_DIAMOND_PAY']; //Ticket # 1940
			$MANAGEMENT_UNPOST_BATCHES		= $res->fields['MANAGEMENT_UNPOST_BATCHES'];
			$MANAGEMENT_DATA_TOOLS			= $res->fields['MANAGEMENT_DATA_TOOLS']; //Ticket # 569
			//$MANAGEMENT_CUSTOM 				= $res->fields['MANAGEMENT_CUSTOM']; //DIAM-842,843
			$MANAGEMENT_IPEDS 				= $res->fields['MANAGEMENT_IPEDS'];
			$MANAGEMENT_POPULATION_REPORT 	= $res->fields['MANAGEMENT_POPULATION_REPORT'];
			$MANAGEMENT_CUSTOM_QUERY 		= $res->fields['MANAGEMENT_CUSTOM_QUERY']; //Ticket # 1295
			$MANAGEMENT_CUSTOM_REPORT		= $res->fields['MANAGEMENT_CUSTOM_REPORT']; //DIAM-2090
			$MANAGEMENT_COMPLIANCE		= $res->fields['MANAGEMENT_COMPLIANCE']; //dvb 25 03 2025

			$REPORT_ADMISSION			= $res->fields['REPORT_ADMISSION'];
			$REPORT_REGISTRAR			= $res->fields['REPORT_REGISTRAR'];
			$REPORT_FINANCE				= $res->fields['REPORT_FINANCE'];
			$REPORT_ACCOUNTING			= $res->fields['REPORT_ACCOUNTING'];
			$REPORT_PLACEMENT			= $res->fields['REPORT_PLACEMENT'];
			$REPORT_CUSTOM_REPORT		= $res->fields['REPORT_CUSTOM_REPORT'];
			$REPORT_COMPLIANCE_REPORTS	= $res->fields['REPORT_COMPLIANCE_REPORTS'];

			$SETUP_SCHOOL			= $res->fields['SETUP_SCHOOL'];
			$SETUP_ADMISSION		= $res->fields['SETUP_ADMISSION'];
			$SETUP_STUDENT			= $res->fields['SETUP_STUDENT'];
			$SETUP_FINANCE			= $res->fields['SETUP_FINANCE'];
			$SETUP_REGISTRAR		= $res->fields['SETUP_REGISTRAR'];
			$SETUP_ACCOUNTING		= $res->fields['SETUP_ACCOUNTING'];
			$SETUP_PLACEMENT		= $res->fields['SETUP_PLACEMENT'];
			$SETUP_COMMUNICATION	= $res->fields['SETUP_COMMUNICATION'];
			$SETUP_TASK_MANAGEMENT	= $res->fields['SETUP_TASK_MANAGEMENT'];
			$SETUP_CONSOLIDATION_TOOL	= $res->fields['SETUP_CONSOLIDATION_TOOL']; // DIAM-2177
			$SETUP_DATA_EXPORT		= $res->fields['SETUP_DATA_EXPORT']; // DIAM-2177

		}
	}
}


if ($_GET['t'] == 1) {
	$page_title = EMPLOYEE_PAGE_TITLE;
	$tab_title  = TAB_EMPLOYEE;
} else if ($_GET['t'] == 2) {
	$page_title = TEACHER_PAGE_TITLE;
	$tab_title  = TAB_TEACHER;
}

//$page_title = $_SESSION['EMPLOYEE_LABEL'];
//$tab_title  = $_SESSION['EMPLOYEE_LABEL'];

if ($_GET['tab'] == '' || $_GET['tab'] == 'generalTab')
	$home_tab = 'active';
else if ($_GET['tab'] == 'campus')
	$campus_tab = 'active';
else if ($_GET['tab'] == 'notes')
	$notes_tab = 'active';
else if ($_GET['tab'] == 'details')
	$detail_tab = 'active';
else if ($_GET['tab'] == 'user_access')
	$user_access_tab = 'active';
else
	$home_tab = 'active';

$res_type = $res_type = $db->Execute("select PK_DEPARTMENT from M_DEPARTMENT WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_DEPARTMENT_MASTER = 3");
$PK_DEPARTMENT_FACULTY = $res_type->fields['PK_DEPARTMENT'];

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
	<title><?= $page_title ?> | <?= $title ?></title>
	<style>
		li>a>label {
			position: unset !important;
		}
	</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
	<? require_once("pre_load.php"); ?>
	<div id="main-wrapper">
		<? require_once("menu.php"); ?>
		<div class="page-wrapper">
			<div class="container-fluid">
				<div class="row page-titles">
					<!-- Ticket # 1452  -->
					<div class="col-md-12 align-self-center">
						<h4 class="text-themecolor">
							<? if ($_GET['id'] == '') echo ADD;
							else echo EDIT; ?> <?= $page_title ?>
							<? if ($_GET['id'] != '') echo " - " . $LAST_NAME . ', ' . $FIRST_NAME; ?>
						</h4>
					</div>
					<!-- Ticket # 1452  -->
				</div>
				<div class="row">
					<div class="col-12">
						<div class="card">
							<ul class="nav nav-tabs customtab" role="tablist">
								<li class="nav-item"> <a class="nav-link <?= $home_tab ?>" data-toggle="tab" href="#generalTab" role="tab"><span class="hidden-sm-up"><i class="ti-home"></i></span> <span class="hidden-xs-down"><?= $tab_title ?></span></a> </li>
								<? if ($_GET['id'] != '') { ?>
									<li class="nav-item"> <a class="nav-link <?= $detail_tab ?> " data-toggle="tab" href="#details" role="tab"><span class="hidden-sm-up"><i class="ti-home"></i></span> <span class="hidden-xs-down"><?= TAB_DETAILS ?></span></a> </li>

									<li class="nav-item"> <a class="nav-link <?= $user_access_tab ?> " data-toggle="tab" href="#user_access" role="tab"><span class="hidden-sm-up"><i class="ti-home"></i></span> <span class="hidden-xs-down"><?= TAB_USER_ACCESS ?></span></a> </li>

									<li class="nav-item"> <a class="nav-link <?= $notes_tab ?> " data-toggle="tab" href="#notes" role="tab"><span class="hidden-sm-up"><i class="ti-home"></i></span> <span class="hidden-xs-down"><?= TAB_NOTES ?></span></a> </li>
								<? } ?>
							</ul>

							<!-- Tab panes -->
							<form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off">
								<input type="hidden" name="FORM_NAME" value="form1">
								<div class="tab-content">
									<!-- Ticket # 1723  -->
									<div class="tab-pane <?= $home_tab ?>" id="generalTab" role="tabpanel">
										<div class="p-20">
											<div class="row">
												<div class="col-9 col-sm-9 form-group">
													<div class="row">
														<div class="col-12 col-sm-3 form-group">
															<input id="LAST_NAME" name="LAST_NAME" type="text" class="form-control required-entry" value="<?= $LAST_NAME ?>">
															<span class="bar"></span>
															<label for="LAST_NAME"><?= LAST_NAME ?></label>
														</div>
														<div class="col-12 col-sm-3 form-group">
															<input id="FIRST_NAME" name="FIRST_NAME" type="text" class="form-control required-entry" value="<?= $FIRST_NAME ?>">
															<span class="bar"></span>
															<label for="FIRST_NAME"><?= FIRST_NAME ?></label>
														</div>
														<div class="col-12 col-sm-3 form-group">
															<input id="MIDDLE_NAME" name="MIDDLE_NAME" type="text" class="form-control" value="<?= $MIDDLE_NAME ?>">
															<span class="bar"></span>
															<label for="MIDDLE_NAME"><?= MIDDLE_NAME ?></label>
														</div>
														<? if ($_GET['t'] != 2) { ?>
															<div class="col-12 col-sm-3 form-group">
																<select id="PK_PRE_FIX" name="PK_PRE_FIX" class="form-control">
																	<option selected></option>
																	<? $res_type = $db->Execute("select PK_PRE_FIX, PRE_FIX from Z_PRE_FIX WHERE ACTIVE = '1' ORDER BY PRE_FIX ASC");
																	while (!$res_type->EOF) { ?>
																		<option value="<?= $res_type->fields['PK_PRE_FIX'] ?>" <? if ($PK_PRE_FIX == $res_type->fields['PK_PRE_FIX']) echo "selected"; ?>><?= $res_type->fields['PRE_FIX'] ?></option>
																	<? $res_type->MoveNext();
																	} ?>
																</select>
																<span class="bar"></span>
																<label for="PREFIX"><?= PREFIX ?></label>
															</div>
														<? } ?>
													</div>

													<div class="row">
														<div class="col-12 col-sm-3 form-group">
															<input id="TITLE" name="TITLE" type="text" class="form-control" value="<?= $TITLE ?>">
															<span class="bar"></span>
															<label for="TITLE"><?= TITLE ?></label>
														</div>

														<div class="col-12 col-sm-3 form-group">
															<select id="PK_SUPERVISOR" name="PK_SUPERVISOR" class="form-control">
																<option value=""></option>
																<? $res_type = $db->Execute("select CONCAT(LAST_NAME,', ',FIRST_NAME) AS EMP_NAME, PK_EMPLOYEE_MASTER from S_EMPLOYEE_MASTER WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CONCAT(LAST_NAME,', ',FIRST_NAME) ASC");
																while (!$res_type->EOF) { ?>
																	<option value="<?= $res_type->fields['PK_EMPLOYEE_MASTER'] ?>" <? if ($res_type->fields['PK_EMPLOYEE_MASTER'] == $PK_SUPERVISOR) echo "selected"; ?>><?= $res_type->fields['EMP_NAME'] ?></option>
																<? $res_type->MoveNext();
																} ?>
															</select>
															<span class="bar"></span>
															<label for="PK_SUPERVISOR"><?= SUPERVISOR ?></label>
														</div>

														<div class="col-12 col-sm-3 form-group">
															<select id="FULL_PART_TIME" name="FULL_PART_TIME" class="form-control">
																<option value=""></option>
																<option value="1" <? if ($FULL_PART_TIME == 1) echo "selected"; ?>>Full Time</option>
																<option value="2" <? if ($FULL_PART_TIME == 2) echo "selected"; ?>>Part Time</option>
															</select>
															<span class="bar"></span>
															<label for="FULL_PART_TIME"><?= FULL_PART_TIME ?></label>
														</div>

														<div class="col-12 col-sm-3 form-group">
															<select id="PK_SOC_CODE" name="PK_SOC_CODE" class="form-control">
																<option></option>
																<? $res_type = $db->Execute("select PK_SOC_CODE, SOC_CODE, SOC_TITLE from M_SOC_CODE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = '1' ORDER BY SOC_CODE ASC ");
																while (!$res_type->EOF) { ?>
																	<option value="<?= $res_type->fields['PK_SOC_CODE'] ?>" <? if ($PK_SOC_CODE == $res_type->fields['PK_SOC_CODE']) echo "selected"; ?>><?= $res_type->fields['SOC_CODE'] . ' - ' . $res_type->fields['SOC_TITLE'] ?></option>
																<? $res_type->MoveNext();
																} ?>
															</select>
															<span class="bar"></span>
															<label for="PK_SOC_CODE"><?= SOC_CODE ?></label>
														</div>
													</div>
												</div>

												<div class="col-3 col-sm-3 form-group">
													<div class="col-sm-12">
														<? if ($_GET['cid'] == '') {
															if ($IMAGE == '') { ?>
																<div class="input-group">
																	<div class="custom-file student-profile-image">
																		<input type="file" name="IMAGE" class="custom-file-input" id="inputGroupFile01">
																		<label class="custom-file-label" for="inputGroupFile01" style="margin-top: 13px;">
																			<img src="../backend_assets/images/user.png" style="width: 87px;">
																			<i class="fa fa-edit"></i>
																		</label>
																	</div>
																</div>
																<br /><br />
															<? } else { ?>
																<table>
																	<tr>
																		<td><img src="<?= $IMAGE ?>" style="height:80px;" /></td>
																		<td>
																			<a href="javascript:void(0);" onclick="delete_row('','img')" title="Delete" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i> </a>
																		</td>
																	</tr>
																</table>
														<? }
														} ?>
													</div>

													<? if ($_GET['cid'] == '') { ?>
														<div class="col-sm-12">
															<div class="d-flex">
																<div class="col-12 col-sm-12 custom-control custom-checkbox form-group">
																	<input type="checkbox" class="custom-control-input" id="IS_FACULTY" name="IS_FACULTY" value="1" <? if ($IS_FACULTY == 1) echo "checked"; ?> onclick="set_faculty_department()">
																	<label class="custom-control-label" for="IS_FACULTY"><?= INSTRUCTOR ?></label>
																</div>
															</div>
														</div>
													<? } ?>
												</div>
											</div>

											<div class="row theme-h-border">
												<div class="col-8 col-sm-9 ">
													<? if ($_GET['cid'] != '') { ?>
														<div class="d-flex ">
															<!-- Ticket #1143 -->
															<div class="col-12 col-sm-4 form-group">
																<input id="EMAIL" name="EMAIL" type="text" class="form-control validate-email required-entry" value="<?= $EMAIL ?>" onBlur="duplicate_check_1()">
																<span class="bar"></span>
																<label for="EMAIL"><?= EMAIL . ' (' . USER_ID . ')' ?></label>
																<div id="already_exit_email" style="display:none;color:#ff0000;"><?= EMAIL_EXISTS ?></div>
															</div>

															<div class="col-12 col-sm-4 form-group">
																<input id="EMAIL_OTHER" name="EMAIL_OTHER" type="text" class="form-control" value="<?= $EMAIL_OTHER ?>">
																<span class="bar"></span>
																<label for="EMAIL_OTHER"><?= EMAIL_OTHER ?></label>
															</div>

															<div class="col-12 col-sm-4 form-group">
																<input id="PASSWORD" name="PASSWORD" type="PASSWORD" class="form-control required-entry" value="" autocomplete="new-password">
																<span class="bar"></span>
																<label for="PASSWORD"><?= PASSWORD ?></label>
															</div>

															<div class="col-12 col-sm-2 form-group">
																<div class="col-12 col-sm-12 custom-control custom-checkbox form-group">
																	<input type="checkbox" class="custom-control-input" id="IS_ADMIN" name="IS_ADMIN" value="1" <? if ($IS_ADMIN == 1) echo "checked"; ?>>
																	<label class="custom-control-label" for="IS_ADMIN"><?= SCHOOL_ADMIN ?></label>
																</div>
															</div>

															<div class="col-12 col-sm-2 form-group">
																<select id="PK_LANGUAGE" name="PK_LANGUAGE" class="form-control required-entry">
																	<option selected></option>
																	<? $res_type = $db->Execute("select PK_LANGUAGE, LANGUAGE from Z_LANGUAGE WHERE ACTIVE = '1' ORDER BY LANGUAGE ASC ");
																	while (!$res_type->EOF) { ?>
																		<option value="<?= $res_type->fields['PK_LANGUAGE'] ?>" <? if ($PK_LANGUAGE == $res_type->fields['PK_LANGUAGE']) echo "selected"; ?>><?= $res_type->fields['LANGUAGE'] ?></option>
																	<? $res_type->MoveNext();
																	} ?>
																</select>
																<span class="bar"></span>
																<label for="PK_LANGUAGE">Language</label>
															</div>
															<!-- Ticket #1143 -->
														</div>

													<? } ?>
												</div>
											</div>

											<div class="row">
												<div class="col-sm-6 pt-25">
													<div class="row">
														<div class="col-12 col-sm-4 form-group">
															<input id="HOME_PHONE" name="HOME_PHONE" type="text" class="form-control phone-inputmask" value="<?= $HOME_PHONE ?>">
															<span class="bar"></span>
															<label for="HOME_PHONE"><?= HOME_PHONE ?></label>
														</div>
														<div class="col-12 col-sm-4 form-group">
															<input id="CELL_PHONE" name="CELL_PHONE" type="text" class="form-control phone-inputmask" value="<?= $CELL_PHONE ?>">
															<span class="bar"></span>
															<label for="CELL_PHONE"><?= CELL_PHONE ?></label>
														</div>
														<div class="col-12 col-sm-4 form-group">
															<input id="WORK_PHONE" name="WORK_PHONE" type="text" class="form-control phone-inputmask" value="<?= $WORK_PHONE ?>">
															<span class="bar"></span>
															<label for="WORK_PHONE"><?= WORK_PHONE ?></label>
														</div>
													</div>
													<? if ($_GET['cid'] == '') { ?>
														<div class=" row">
															<div class="col-12 col-sm-12 form-group">
																<input id="EMAIL" name="EMAIL" type="text" class="form-control required-entry validate-email" value="<?= $EMAIL ?>" onBlur="duplicate_check_1()">
																<span class="bar"></span>
																<label for="EMAIL"><?= EMAIL ?></label>
																<div id="already_exit_email" style="display:none;color:#ff0000;"><?= EMAIL_EXISTS ?></div>
															</div>

															<div class="col-12 col-sm-12 form-group">
																<input id="EMAIL_OTHER" name="EMAIL_OTHER" type="text" class="form-control" value="<?= $EMAIL_OTHER ?>">
																<span class="bar"></span>
																<label for="EMAIL_OTHER"><?= EMAIL_OTHER ?></label>
															</div>
														</div>
													<? } ?>
													<!-- Ticket #1143 -->

													<? if ($_GET['id'] == '') { ?>
														<div class="row">
															<div class="col-12 col-sm-6 form-group">
																<div class="col-12 col-sm-6 focused">
																	<span class="bar"></span>
																	<label for="CAMPUS"><?= CAMPUS ?></label>
																</div>
																<div class="form-group row d-flex">
																	<div class="form-group col-12 col-sm-12">
																		<!-- Ticket #849  -->
																		<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control">
																			<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
																			while (!$res_type->EOF) {  ?>
																				<option value="<?= $res_type->fields['PK_CAMPUS'] ?>" <? if ($res_type->fields['PK_CAMPUS'] == $def_camp) echo "selected"; ?>><?= $res_type->fields['CAMPUS_CODE'] ?></option>
																			<? $res_type->MoveNext();
																			} ?>
																		</select>
																	</div>
																</div>
															</div>
															<div class="col-12 col-sm-6 form-group">
																<div class="col-12 col-sm-6 focused">
																	<span class="bar"></span>
																	<label for="DEPARTMENT"><?= DEPARTMENT ?></label>
																</div>
																<div class="form-group row d-flex">
																	<? $res_type = $res_type = $db->Execute("select * from M_DEPARTMENT WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by DEPARTMENT ASC");
																	while (!$res_type->EOF) { ?>
																		<div class="col-12 col-sm-6">
																			<div class="custom-control custom-checkbox mr-sm-2">
																				<? $checked = '';
																				$PK_DEPARTMENT = $res_type->fields['PK_DEPARTMENT']; ?>
																				<input type="checkbox" class="custom-control-input" id="PK_DEPARTMENT_<?= $PK_DEPARTMENT ?>" name="PK_DEPARTMENT[]" value="<?= $PK_DEPARTMENT ?>">
																				<label class="custom-control-label" for="PK_DEPARTMENT_<?= $PK_DEPARTMENT ?>"><?= $res_type->fields['DEPARTMENT'] ?></label>
																			</div>
																		</div>
																	<? $res_type->MoveNext();
																	} ?>
																</div>
															</div>
														</div>
													<? } ?>
												</div>

												<div class="col-sm-6 pt-25 theme-v-border">
													<div class="row">
														<div class="col-12 col-sm-12 ">
															<div class="d-flex">
																<div class="col-12 col-sm-12 custom-control custom-checkbox form-group">
																	<input type="checkbox" class="custom-control-input" id="TURN_OFF_ASSIGNMENTS" name="TURN_OFF_ASSIGNMENTS" value="1" <? if ($TURN_OFF_ASSIGNMENTS == 1) echo "checked"; ?>>
																	<label class="custom-control-label" for="TURN_OFF_ASSIGNMENTS"><?= TURN_OFF_ASSIGNMENTS ?></label>
																</div>
															</div>
														</div>

														<? if ($_GET['id'] != '') { ?>
															<div class="col-12 col-sm-12 ">
																<div class="d-flex">
																	<div class="col-12 col-sm-12 custom-control custom-checkbox form-group">
																		<input type="checkbox" class="custom-control-input" id="ACTIVE" name="ACTIVE" value="1" <? if ($ACTIVE == 1) echo "checked"; ?>>
																		<label class="custom-control-label" for="ACTIVE"><?= ACTIVE ?></label>
																	</div>
																</div>
															</div>
														<? } ?>
													</div>

													<? $res_type = $db->Execute("select PK_CUSTOM_FIELDS,FIELD_NAME,PK_DATA_TYPES, PK_USER_DEFINED_FIELDS from S_CUSTOM_FIELDS WHERE S_CUSTOM_FIELDS.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_CUSTOM_FIELDS.ACTIVE = 1 AND SECTION = 2 ");
													while (!$res_type->EOF) { ?>
														<div class="d-flex ">
															<div class="col-12 col-sm-12 form-group">
																<? $PK_CUSTOM_FIELDS 	= $res_type->fields['PK_CUSTOM_FIELDS'];
																$PK_USER_DEFINED_FIELDS = $res_type->fields['PK_USER_DEFINED_FIELDS'];

																$res_1 = $db->Execute("select FIELD_VALUE from S_EMPLOYEE_CUSTOM_FIELDS WHERE PK_EMPLOYEE_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CUSTOM_FIELDS = '$PK_CUSTOM_FIELDS' "); ?>

																<input name="PK_CUSTOM_FIELDS[]" type="hidden" value="<?= $PK_CUSTOM_FIELDS ?>" />
																<input name="FIELD_NAME[]" type="hidden" value="<?= $res_type->fields['FIELD_NAME'] ?>" />
																<input name="PK_DATA_TYPES[]" type="hidden" value="<?= $res_type->fields['PK_DATA_TYPES'] ?>" />

																<? $date_cls = "";
																if ($res_type->fields['PK_DATA_TYPES'] == 1 || $res_type->fields['PK_DATA_TYPES'] == 4) {
																	$FIELD_VALUE = $res_1->fields['FIELD_VALUE'];
																	if ($res_type->fields['PK_DATA_TYPES'] == 4) {
																		$date_cls = "date";
																		if ($FIELD_VALUE != '')
																			$FIELD_VALUE = date("m/d/Y", strtotime($FIELD_VALUE));
																	} ?>

																	<input name="CUSTOM_FIELDS_<?= $PK_CUSTOM_FIELDS ?>" id="CUSTOM_FIELDS_<?= $res_type->fields['PK_CUSTOM_FIELDS'] ?>" type="text" class="form-control <?= $date_cls ?>" value="<?= $FIELD_VALUE ?>" />

																	<span class="bar"></span>
																	<label for="CUSTOM_FIELDS_<?= $res_type->fields['PK_CUSTOM_FIELDS'] ?>"><?= $res_type->fields['FIELD_NAME'] ?></label>

																<? } else if ($res_type->fields['PK_DATA_TYPES'] == 2) { ?>
																	<select name="CUSTOM_FIELDS_<?= $PK_CUSTOM_FIELDS ?>" id="CUSTOM_FIELDS_<?= $res_type->fields['PK_CUSTOM_FIELDS'] ?>" class="form-control">
																		<option value=""></option>
																		<? $res_dd = $db->Execute("select * from S_USER_DEFINED_FIELDS_DETAIL WHERE ACTIVE = '1' AND PK_USER_DEFINED_FIELDS = '$PK_USER_DEFINED_FIELDS' ORDER BY OPTION_NAME ASC ");
																		while (!$res_dd->EOF) { ?>
																			<option value="<?= $res_dd->fields['PK_USER_DEFINED_FIELDS_DETAIL'] ?>" <? if ($res_dd->fields['PK_USER_DEFINED_FIELDS_DETAIL'] == $res_1->fields['FIELD_VALUE']) echo 'selected = "selected"'; ?>><?= $res_dd->fields['OPTION_NAME'] ?></option>
																		<? $res_dd->MoveNext();
																		}	?>
																	</select>

																	<span class="bar"></span>
																	<label for="CUSTOM_FIELDS_<?= $res_type->fields['PK_CUSTOM_FIELDS'] ?>"><?= $res_type->fields['FIELD_NAME'] ?></label>

																<? } else if ($res_type->fields['PK_DATA_TYPES'] == 3) {
																	$OPTIONS = explode(",", $res_1->fields['FIELD_VALUE']);
																	$res_dd = $db->Execute("select * from S_USER_DEFINED_FIELDS_DETAIL WHERE ACTIVE = '1' AND PK_USER_DEFINED_FIELDS = '$PK_USER_DEFINED_FIELDS' ORDER BY OPTION_NAME ASC "); ?>
																	<div class="col-12 col-sm-6 focused">
																		<span class="bar"></span>
																		<label for="CAMPUS"><?= $res_type->fields['FIELD_NAME'] ?></label>
																	</div>
																	<? while (!$res_dd->EOF) {
																		$checked = '';
																		foreach ($OPTIONS as $OPTION) {
																			if ($OPTION == $res_dd->fields['PK_USER_DEFINED_FIELDS_DETAIL']) {
																				$checked = 'checked="checked"';
																				break;
																			}
																		} ?>
																		<div class="d-flex">
																			<div class="col-12 col-sm-4 custom-control custom-checkbox form-group">
																				<input type="checkbox" class="custom-control-input" id="CUSTOM_FIELDS_<?= $PK_CUSTOM_FIELDS ?>_<?= $res_dd->fields['PK_USER_DEFINED_FIELDS_DETAIL'] ?>" name="CUSTOM_FIELDS_<?= $PK_CUSTOM_FIELDS ?>[]" value="<?= $res_dd->fields['PK_USER_DEFINED_FIELDS_DETAIL'] ?>" <?= $checked ?>>
																				<label class="custom-control-label" for="CUSTOM_FIELDS_<?= $PK_CUSTOM_FIELDS ?>_<?= $res_dd->fields['PK_USER_DEFINED_FIELDS_DETAIL'] ?>"><?= $res_dd->fields['OPTION_NAME'] ?></label>
																			</div>
																		</div>

																<? $res_dd->MoveNext();
																	}
																} ?>
															</div>
														</div>
													<? $res_type->MoveNext();
													} ?>

													<div class="d-flex">
														<div class="form-group">
															<button onclick="validate_form(1)" type="button" class="btn waves-effect waves-light btn-info"><?= SAVE ?></button>
															<button onclick="validate_form(0)" type="button" class="btn waves-effect waves-light btn-info"><?= SAVE_EXIT ?></button>

															<? if ($_GET['id'] != '') {
																$res_ethink = $db->Execute("SELECT ENABLE_ETHINK FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
																if ($res_ethink->fields['ENABLE_ETHINK'] == 1) {
																	$res_ethink_emp = $db->Execute("SELECT PK_EMPLOYEE_MASTER_ETHINK FROM S_EMPLOYEE_MASTER_ETHINK WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_EMPLOYEE_MASTER = '$_GET[id]' ");
																	if ($res_ethink_emp->RecordCount() == 0) { ?>
																		<button type="button" onclick="send_to_ethink()" id="ethink_button" class="btn waves-effect waves-light btn-dark"><?= SEND_TO_ETHINK ?></button>
															<? 	}
																}
															} ?>

															<button type="button" onclick="window.location.href='manage_employee?t=<?= $_GET['t'] ?>'" class="btn waves-effect waves-light btn-dark"><?= CANCEL ?></button>
														</div>
													</div>
												</div>
											</div>

										</div>
									</div>

									<? if ($_GET['id'] != '') { ?>
										<div class="tab-pane <?= $detail_tab ?> " id="details" role="tabpanel">
											<div class="p-20" style="padding-top:0">
												<div class="row">
													<div class="col-sm-6 pt-25">
														<!-- Ticket # 1737 -->
														<? $res_ethink = $db->Execute("SELECT ENABLE_ETHINK FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
														if ($res_ethink->fields['ENABLE_ETHINK'] == 1) {
															$res_ethink = $db->Execute("SELECT ETHINK_ID FROM S_EMPLOYEE_MASTER_ETHINK WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_EMPLOYEE_MASTER = '$_GET[id]' AND ETHINK_ID != '' ORDER BY PK_EMPLOYEE_MASTER_ETHINK DESC ");   ?>
															<div class="row">
																<div class="col-12 col-sm-4 form-group">
																	<input id="MOODLE_ID" name="MOODLE_ID" type="text" class="form-control" value="<?= $res_ethink->fields['ETHINK_ID'] ?>">
																	<span class="bar"></span>
																	<label for="MOODLE_ID"><?= MOODLE_ID ?></label>
																</div>
															</div>
														<? } ?>
														<!-- Ticket # 1737 -->

														<div class="row">
															<div class="col-12 col-sm-4 form-group">
																<input id="EMPLOYEE_ID" name="EMPLOYEE_ID" type="text" class="form-control" value="<?= $EMPLOYEE_ID ?>">
																<span class="bar"></span>
																<label for="EMPLOYEE_ID"><?= EMPLOYEE_ID ?></label>
															</div>

															<div class="col-12 col-sm-4 form-group">
																<input id="COMPANY_EMP_ID" name="COMPANY_EMP_ID" type="text" class="form-control" value="<?= $COMPANY_EMP_ID ?>">
																<span class="bar"></span>
																<label for="COMPANY_EMP_ID"><?= COMPANY_EMP_ID ?></label>
															</div>

															<div class="col-12 col-sm-4 form-group">
																<input id="NETWORK_ID" name="NETWORK_ID" type="text" class="form-control" value="<?= $NETWORK_ID ?>">
																<span class="bar"></span>
																<label for="NETWORK_ID"><?= NETWORK_ID ?></label>
															</div>
														</div>

														<div class="row theme-h-border">
															<div class="col-12 col-sm-4 form-group">
																<input class="form-control date" value="<?= $DATE_HIRED ?>" name="DATE_HIRED" id="DATE_HIRED">
																<span class="bar"></span>
																<label for="DATE_HIRED"><?= DATE_HIRED ?></label>
															</div>
															<div class="col-12 col-sm-4 form-group">
																<input class="form-control date" value="<?= $DATE_TERMINATED ?>" name="DATE_TERMINATED" id="DATE_TERMINATED">
																<span class="bar"></span>
																<label for="DATE_TERMINATED"><?= DATE_TERMINATED ?></label>
															</div>
															<div class="col-12 col-sm-4 form-group">
																<select id="ELIGIBLE_FOR_REHIRE" name="ELIGIBLE_FOR_REHIRE" class="form-control">
																	<option></option>
																	<option value="1" <? if ($ELIGIBLE_FOR_REHIRE == 1) echo "selected"; ?>>Yes</option>
																	<option value="2" <? if ($ELIGIBLE_FOR_REHIRE == 2) echo "selected"; ?>>No</option>
																</select>
																<span class="bar"></span>
																<label for="ELIGIBLE_FOR_REHIRE"><?= ELIGIBLE_FOR_REHIRE ?></label>
															</div>
														</div>

														<br /><br />
														<div class="row ">
															<div class="col-12 col-sm-6 form-group">
																<input id="ADDRESS" name="ADDRESS" type="text" class="form-control" value="<?= $ADDRESS ?>">
																<span class="bar"></span>
																<label for="ADDRESS"><?= ADDRESS ?></label>
															</div>

															<div class="col-12 col-sm-6 form-group">
																<input id="ADDRESS_1" name="ADDRESS_1" type="text" class="form-control" value="<?= $ADDRESS_1 ?>">
																<span class="bar"></span>
																<label for="ADDRESS_1"><?= ADDRESS_1 ?></label>
															</div>
														</div>

														<div class="row">
															<div class="col-12 col-sm-4 form-group">
																<input id="CITY" name="CITY" type="text" class="form-control" value="<?= $CITY ?>">
																<span class="bar"></span>
																<label for="CITY"><?= CITY ?></label>
															</div>
															<div class="col-12 col-sm-4 form-group">
																<select id="PK_STATES" name="PK_STATES" class="form-control"> <!-- onchange="get_country(this.value,'PK_COUNTRY')" -->
																	<option selected></option>
																	<? $res_type = $db->Execute("select PK_STATES, STATE_NAME from Z_STATES WHERE ACTIVE = '1' ORDER BY STATE_NAME ASC ");
																	while (!$res_type->EOF) { ?>
																		<option value="<?= $res_type->fields['PK_STATES'] ?>" <? if ($res_type->fields['PK_STATES'] == $PK_STATES) echo "selected"; ?>><?= $res_type->fields['STATE_NAME'] ?></option>
																	<? $res_type->MoveNext();
																	} ?>
																</select>
																<span class="bar"></span>
																<label for="STATE"><?= STATE ?></label>
															</div>
															<div class="col-12 col-sm-4 form-group">
																<input id="ZIP" name="ZIP" type="text" class="form-control" value="<?= $ZIP ?>">
																<span class="bar"></span>
																<label for="ZIP"><?= ZIP ?></label>
															</div>
														</div>

														<div class="row">
															<div class="col-12 col-sm-4 form-group" id="PK_COUNTRY_LABEL">
																<div id="PK_COUNTRY_DIV">
																	<select id="PK_COUNTRY" name="PK_COUNTRY" class="form-control">
																		<option selected></option>
																		<? $res_type1 = $db->Execute("select PK_COUNTRY, NAME from Z_COUNTRY WHERE ACTIVE = '1' ORDER BY NAME ASC ");
																		while (!$res_type1->EOF) { ?>
																			<option value="<?= $res_type1->fields['PK_COUNTRY'] ?>" <? if ($PK_COUNTRY == $res_type1->fields['PK_COUNTRY']) echo "selected"; ?>><?= $res_type1->fields['NAME'] ?></option>
																		<? $res_type1->MoveNext();
																		}
																		?>
																	</select>
																</div>
																<span class="bar"></span>
																<label for="COUNTRY"><?= COUNTRY ?></label>
															</div>
														</div>
													</div>

													<div class="col-sm-6 pt-25 theme-v-border">
														<div class="row">
															<div class="form-group col-12 col-sm-6">
																<input class="form-control date date-inputmask" value="<?= $DOB ?>" name="DOB" id="DOB">
																<span class="bar"></span>
																<label for="DOB"><?= DOB ?></label>
															</div>

															<div class="col-12 col-sm-6 form-group">
																<select id="GENDER" name="GENDER" class="form-control">
																	<option value=""></option>
																	<? /* Ticket # 1769   */
																	$res_type = $db->Execute("select PK_GENDER, GENDER from Z_GENDER WHERE 1=1");
																	while (!$res_type->EOF) { ?>
																		<option value="<?= $res_type->fields['PK_GENDER'] ?>" <? if ($res_type->fields['PK_GENDER'] == $GENDER) echo "selected"; ?>><?= $res_type->fields['GENDER'] ?></option>
																	<? $res_type->MoveNext();
																	} /* Ticket # 1769   */ ?>
																</select>
																<span class="bar"></span>
																<label for="GENDER"><?= GENDER ?></label>
															</div>
														</div>

														<div class="row theme-h-border">
															<div class="col-12 col-sm-6 form-group">
																<input id="SSN" <? if ($SSN == '') { ?> name="SSN" <? } else echo "disabled"; ?> type="text" class="form-control  <? if ($SSN == '') { ?> validate-ssn ssn-inputmask <? } ?> " value="<?= $SSN ?>">
																<span class="bar"></span>
																<label for="SSN">
																	<?= SSN ?>
																	<? if ($SSN != '') { ?>
																		&nbsp;&nbsp;&nbsp;&nbsp;
																		<a href="javascript:void(0)" onclick="change_ssn()"><?= CHANGE ?></a>
																	<? } ?>
																</label>
															</div>

															<div class="col-12 col-sm-6 form-group">
																<select id="PK_MARITAL_STATUS" name="PK_MARITAL_STATUS" class="form-control">
																	<option selected></option>
																	<? $res_type = $db->Execute("select * from Z_MARITAL_STATUS WHERE ACTIVE= 1 order by MARITAL_STATUS ASC");
																	while (!$res_type->EOF) { ?>
																		<option value="<?= $res_type->fields['PK_MARITAL_STATUS'] ?>" <? if ($res_type->fields['PK_MARITAL_STATUS'] == $PK_MARITAL_STATUS) echo "selected"; ?>><?= $res_type->fields['MARITAL_STATUS'] ?></option>
																	<? $res_type->MoveNext();
																	} ?>
																</select>
																<span class="bar"></span>
																<label for="MARITAL_STATUS"><?= MARITAL_STATUS ?></label>
															</div>
														</div>

														<div class="row">
															<div class="p-20" style="width:100%">
																<div class="d-flex">
																	<div class="col-12 col-sm-12 form-group" id="IPEDS_ETHNICITY_LABEL">
																		<input id="IPEDS_ETHNICITY" name="IPEDS_ETHNICITY" type="text" class="form-control" value="<?= $IPEDS_ETHNICITY ?>" readonly>
																		<span class="bar"></span>
																		<label for="IPEDS_ETHNICITY"><?= IPEDS_ETHNICITY ?></label>
																	</div>
																</div>

																<div class="col-12 col-sm-12 focused">
																	<span class="bar"></span>
																	<label for="RACE"><?= RACE ?></label>
																</div>
																<div class="form-group row d-flex">
																	<? $res_type = $db->Execute("select * from Z_RACE WHERE ACTIVE = 1 ");
																	while (!$res_type->EOF) { ?>
																		<div class="col-12 col-sm-6" style="height:30px">
																			<div class="custom-control custom-checkbox mr-sm-2">
																				<? $checked = '';
																				$PK_RACE = $res_type->fields['PK_RACE'];
																				$res = $db->Execute("select PK_EMPLOYEE_RACE FROM S_EMPLOYEE_RACE WHERE PK_RACE = '$PK_RACE' AND PK_EMPLOYEE_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
																				if ($res->RecordCount() > 0)
																					$checked = 'checked';
																				?>

																				<input type="checkbox" class="custom-control-input" id="RACE_<?= $PK_RACE ?>" name="RACE[]" value="<?= $PK_RACE ?>" onclick="generate_ethnicity()" <?= $checked ?>>
																				<label class="custom-control-label" id="LBL_RACE_<?= $PK_RACE ?>" for="RACE_<?= $res_type->fields['PK_RACE'] ?>" style="line-height: 15px;"><?= $res_type->fields['RACE'] ?></label>
																			</div>
																		</div>
																	<? $res_type->MoveNext();
																	} ?>
																</div>

																<div class="d-flex">
																	<div class="form-group">
																		<button onclick="validate_form(1)" type="button" class="btn waves-effect waves-light btn-info"><?= SAVE ?></button>
																		<button onclick="validate_form(0)" type="button" class="btn waves-effect waves-light btn-info"><?= SAVE_EXIT ?></button>

																		<button type="button" onclick="window.location.href='manage_employee?t=<?= $_GET['t'] ?>'" class="btn waves-effect waves-light btn-dark"><?= CANCEL ?></button>

																	</div>
																</div>
															</div>
														</div>
													</div>
												</div>
											</div>
										</div>
										<!-- Ticket # 1723  -->

										<div class="tab-pane <?= $user_access_tab ?> " id="user_access" role="tabpanel">
											<div class="p-20">
												<div class="row">
													<div class="col-md-3">
														<? if ($LOGIN_CREATED == 1) { ?>
															<div class="form-group row d-flex">
																<div class="col-12 col-sm-12 form-group">
																	<input id="USER_ID" type="text" class="form-control <? if ($_GET['cid'] != '') echo "required-entry"; ?>" value="<?= $USER_ID ?>" onBlur="duplicate_check()" name="USER_ID" <? if ($USER_ID != '') echo "readonly"; ?>>
																	<span class="bar"></span>
																	<label for="USER_ID"><?= USER_ID ?></label>
																	<div id="already_exit" style="display:none;color:#ff0000;"><?= USER_ID_EXISTS ?></div>
																</div>
															</div>

															<div class="form-group row d-flex">
																<div class="col-12 col-sm-12 form-group">
																	<select id="PK_LANGUAGE" name="PK_LANGUAGE" class="form-control required-entry">
																		<option selected></option>
																		<? $res_type = $db->Execute("select PK_LANGUAGE, LANGUAGE from Z_LANGUAGE WHERE ACTIVE = '1' ORDER BY LANGUAGE ASC ");
																		while (!$res_type->EOF) { ?>
																			<option value="<?= $res_type->fields['PK_LANGUAGE'] ?>" <? if ($PK_LANGUAGE == $res_type->fields['PK_LANGUAGE']) echo "selected"; ?>><?= $res_type->fields['LANGUAGE'] ?></option>
																		<? $res_type->MoveNext();
																		} ?>
																	</select>
																	<span class="bar"></span>
																	<label for="PK_LANGUAGE">Language</label>
																</div>
															</div>
														<? } ?>
														<div class="form-group">
															<? if ($_GET['id'] != '') {
																if ($LOGIN_CREATED == 0) { ?>
																	<button type="button" onclick="window.location.href='create_login?eid=<?= $_GET['id'] ?>&t=<?= $_GET['t'] ?>'" class="btn waves-effect waves-light btn-dark"><?= CREATE_LOGIN ?></button>
																<? } else { ?>
																	<button type="button" onclick="window.location.href='reset_password?id=<?= $_GET['id'] ?>&p=e&t=<?= $_GET['t'] ?>'" class="btn waves-effect waves-light btn-dark"><?= RESET_PASSWORD ?></button>

																	<button type="button" onclick="delete_row('<?= $_GET['id'] ?>','user_login')" class="btn waves-effect waves-light btn-dark"><?= REMOVE_LOGIN ?></button>
															<? }
															} ?>
														</div>
													</div>

													<div class="col-md-3">
														<div class="form-group row d-flex">
															<? if ($IS_FACULTY == 0) { ?>
																<div class="col-12 col-sm-12 custom-control custom-checkbox form-group">
																	<input type="checkbox" class="custom-control-input" id="IS_ADMIN" name="IS_ADMIN" value="1" <? if ($IS_ADMIN == 1) echo "checked"; ?> onclick="set_def_value()">
																	<label class="custom-control-label" for="IS_ADMIN"><?= SCHOOL_ADMIN ?></label>
																</div>
															<? } else { ?>
																<div class="col-12 col-sm-12 custom-control custom-checkbox form-group">
																	<input type="checkbox" class="custom-control-input" id="NEED_SCHOOL_ACCESS" name="NEED_SCHOOL_ACCESS" value="1" <? if ($NEED_SCHOOL_ACCESS == 1) echo "checked"; ?>>
																	<label class="custom-control-label" for="NEED_SCHOOL_ACCESS"><?= NEED_SCHOOL_ACCESS ?></label>
																</div>
															<? } ?>
														</div>

														<!-- ticket #967  -->
														<? /* Ticket # 1511 
													$res_acc1 = $db->Execute("SELECT ENABLE_INTERNAL_MESSAGE FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
													if($res_acc1->fields['ENABLE_INTERNAL_MESSAGE'] == 1) { ?>
													<div class="form-group row d-flex" >
														<div class="col-12 col-sm-12 custom-control custom-checkbox form-group" >
															<input type="checkbox" class="custom-control-input" id="INTERNAL_MESSAGE_ENABLED" name="INTERNAL_MESSAGE_ENABLED" value="1" <? if($INTERNAL_MESSAGE_ENABLED == 1) echo "checked";?> >
															<label class="custom-control-label" for="INTERNAL_MESSAGE_ENABLED"><?=ENABLE_INTERNAL_MESSAGE ?></label>
														</div>
													</div>
													<? } 
													Ticket # 1511 */ ?>
														<!-- ticket #967  -->

													</div>
													<div class="col-md-3">
														<div class="col-12 col-sm-12 focused">
															<span class="bar"></span>
															<label for="CAMPUS"><?= CAMPUS ?></label>
														</div>
														<div class="form-group row d-flex">
															<div class="form-group col-12 col-sm-12">
																<select id="PK_CAMPUS" name="PK_CAMPUS[]" multiple class="form-control">
																	<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by CAMPUS_CODE ASC");
																	while (!$res_type->EOF) {
																		$selected = '';
																		$PK_CAMPUS = $res_type->fields['PK_CAMPUS'];
																		$res = $db->Execute("select PK_EMPLOYEE_CAMPUS FROM S_EMPLOYEE_CAMPUS WHERE PK_CAMPUS = '$PK_CAMPUS' AND PK_EMPLOYEE_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
																		if ($res->RecordCount() > 0)
																			$selected = 'selected'; ?>
																		<option value="<?= $res_type->fields['PK_CAMPUS'] ?>" <?= $selected ?>><?= $res_type->fields['CAMPUS_CODE'] ?></option>
																	<? $res_type->MoveNext();
																	} ?>
																</select>
															</div>
														</div>
													</div>
													<div class="col-md-3">
														<div class="col-12 col-sm-6 focused">
															<span class="bar"></span>
															<label for="DEPARTMENT"><?= DEPARTMENT ?></label>
														</div>
														<div class="form-group row d-flex">
															<? $res_type = $res_type = $db->Execute("select * from M_DEPARTMENT WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by DEPARTMENT ASC");
															while (!$res_type->EOF) { ?>
																<div class="col-12 col-sm-6">
																	<div class="custom-control custom-checkbox mr-sm-2">
																		<? $checked = '';
																		$PK_DEPARTMENT = $res_type->fields['PK_DEPARTMENT'];
																		$res = $db->Execute("select PK_EMPLOYEE_DEPARTMENT FROM S_EMPLOYEE_DEPARTMENT WHERE PK_DEPARTMENT = '$PK_DEPARTMENT' AND PK_EMPLOYEE_MASTER = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
																		if ($res->RecordCount() > 0)
																			$checked = 'checked'; ?>
																		<input type="checkbox" class="custom-control-input" id="PK_DEPARTMENT_<?= $PK_DEPARTMENT ?>" name="PK_DEPARTMENT[]" value="<?= $PK_DEPARTMENT ?>" <?= $checked ?>>
																		<label class="custom-control-label" for="PK_DEPARTMENT_<?= $PK_DEPARTMENT ?>"><?= $res_type->fields['DEPARTMENT'] ?></label>
																	</div>
																</div>
															<? $res_type->MoveNext();
															} ?>
														</div>
													</div>
												</div>

												<hr />
												<div class="row">
													<? if ($LOGIN_CREATED == 1) { ?>
														<div class="col-md-5">
															<div style="width:80%">
																<table class="table table-hover">
																	<thead>
																		<tr>
																			<th width="20%"><?= SECTION ?></th>
																			<th width="20%"><?= NO_ACCESS ?></th>
																			<th width="20%"><?= READ_ONLY ?></th>
																			<th width="20%"><?= USER_ACCESS ?></th>
																			<th width="20%"><?= FULL_ACCESS ?></th>
																		</tr>
																	</thead>
																	<tbody>
																		<tr>
																			<td><?= MNU_ADMISSION ?></td>
																			<td align="center">
																				<input type="radio" name="ADMISSION_ACCESS" id="ADMISSION_NO_ACCESS" value="0" <? if ($ADMISSION_ACCESS == 0) echo "checked"; ?> />
																			</td>
																			<td align="center">
																				<input type="radio" name="ADMISSION_ACCESS" id="ADMISSION_READ_ONLY" value="1" <? if ($ADMISSION_ACCESS == 1) echo "checked"; ?> />
																			</td>
																			<td align="center">
																				<input type="radio" name="ADMISSION_ACCESS" id="ADMISSION_READ_ONLY" value="2" <? if ($ADMISSION_ACCESS == 2) echo "checked"; ?> />
																			</td>
																			<td align="center">
																				<input type="radio" name="ADMISSION_ACCESS" id="ADMISSION_FULL_ACCESS" value="3" <? if ($ADMISSION_ACCESS == 3) echo "checked"; ?> />
																			</td>
																		</tr>
																		<tr>
																			<td><?= MNU_REGISTRAR ?></td>
																			<td align="center">
																				<input type="radio" name="REGISTRAR_ACCESS" id="REGISTRAR_NO_ACCESS" value="0" <? if ($REGISTRAR_ACCESS == 0) echo "checked"; ?> />
																			</td>
																			<td align="center">
																				<input type="radio" name="REGISTRAR_ACCESS" id="REGISTRAR_READ_ONLY" value="1" <? if ($REGISTRAR_ACCESS == 1) echo "checked"; ?> />
																			</td>
																			<td align="center">
																				<input type="radio" name="REGISTRAR_ACCESS" id="REGISTRAR_READ_ONLY" value="2" <? if ($REGISTRAR_ACCESS == 2) echo "checked"; ?> />
																			</td>
																			<td align="center">
																				<input type="radio" name="REGISTRAR_ACCESS" id="REGISTRAR_FULL_ACCESS" value="3" <? if ($REGISTRAR_ACCESS == 3) echo "checked"; ?> />
																			</td>
																		</tr>
																		<tr>
																			<td><?= MNU_FINANCE ?></td>
																			<td align="center">
																				<input type="radio" name="FINANCE_ACCESS" id="FINANCE_NO_ACCESS" value="0" <? if ($FINANCE_ACCESS == 0) echo "checked"; ?> />
																			</td>
																			<td align="center">
																				<input type="radio" name="FINANCE_ACCESS" id="FINANCE_READ_ONLY" value="1" <? if ($FINANCE_ACCESS == 1) echo "checked"; ?> />
																			</td>
																			<td align="center">
																				<input type="radio" name="FINANCE_ACCESS" id="FINANCE_READ_ONLY" value="2" <? if ($FINANCE_ACCESS == 2) echo "checked"; ?> />
																			</td>
																			<td align="center">
																				<input type="radio" name="FINANCE_ACCESS" id="FINANCE_FULL_ACCESS" value="3" <? if ($FINANCE_ACCESS == 3) echo "checked"; ?> />
																			</td>
																		</tr>
																		<tr>
																			<td><?= MNU_ACCOUNTING ?></td>
																			<td align="center">
																				<input type="radio" name="ACCOUNTING_ACCESS" id="ACCOUNTING_NO_ACCESS" value="0" <? if ($ACCOUNTING_ACCESS == 0) echo "checked"; ?> />
																			</td>
																			<td align="center">
																				<input type="radio" name="ACCOUNTING_ACCESS" id="ACCOUNTING_READ_ONLY" value="1" <? if ($ACCOUNTING_ACCESS == 1) echo "checked"; ?> />
																			</td>
																			<td align="center">
																				<input type="radio" name="ACCOUNTING_ACCESS" id="ACCOUNTING_READ_ONLY" value="2" <? if ($ACCOUNTING_ACCESS == 2) echo "checked"; ?> />
																			</td>
																			<td align="center">
																				<input type="radio" name="ACCOUNTING_ACCESS" id="ACCOUNTING_FULL_ACCESS" value="3" <? if ($ACCOUNTING_ACCESS == 3) echo "checked"; ?> />
																			</td>
																		</tr>
																		<tr>
																			<td><?= MNU_PLACEMENT ?></td>
																			<td align="center">
																				<input type="radio" name="PLACEMENT_ACCESS" id="PLACEMENT_NO_ACCESS" value="0" <? if ($PLACEMENT_ACCESS == 0) echo "checked"; ?> />
																			</td>
																			<td align="center">
																				<input type="radio" name="PLACEMENT_ACCESS" id="PLACEMENT_READ_ONLY" value="1" <? if ($PLACEMENT_ACCESS == 1) echo "checked"; ?> />
																			</td>
																			<td align="center">
																				<input type="radio" name="PLACEMENT_ACCESS" id="PLACEMENT_READ_ONLY" value="2" <? if ($PLACEMENT_ACCESS == 2) echo "checked"; ?> />
																			</td>
																			<td align="center">
																				<input type="radio" name="PLACEMENT_ACCESS" id="PLACEMENT_FULL_ACCESS" value="3" <? if ($PLACEMENT_ACCESS == 3) echo "checked"; ?> />
																			</td>
																		</tr>
																	</tbody>
																</table>
															</div>
														</div>
														<div class="col-md-7">
															<div class="row">
																<div class="col-md-4">
																	<div class="row">
																		<h4 style="font-size: 14px;font-weight: bold;"><?= MNU_MANAGEMENT ?></h4>
																		<div class="col-md-12 form-group custom-control custom-checkbox form-group">
																			<input type="checkbox" class="custom-control-input" id="MANAGEMENT_ADMISSION" name="MANAGEMENT_ADMISSION" value="1" <? if ($MANAGEMENT_ADMISSION == 1) echo "checked"; ?>>
																			<label class="custom-control-label" for="MANAGEMENT_ADMISSION"><?= MNU_ADMISSION ?></label>
																		</div>

																		<div class="col-md-12 form-group custom-control custom-checkbox form-group">
																			<input type="checkbox" class="custom-control-input" id="MANAGEMENT_REGISTRAR" name="MANAGEMENT_REGISTRAR" value="1" <? if ($MANAGEMENT_REGISTRAR == 1) echo "checked"; ?>>
																			<label class="custom-control-label" for="MANAGEMENT_REGISTRAR"><?= MNU_REGISTRAR ?></label>
																		</div>

																		<div class="col-md-12 form-group custom-control custom-checkbox form-group">
																			<input type="checkbox" class="custom-control-input" id="MANAGEMENT_FINANCE" name="MANAGEMENT_FINANCE" value="1" <? if ($MANAGEMENT_FINANCE == 1) echo "checked"; ?>>
																			<label class="custom-control-label" for="MANAGEMENT_FINANCE"><?= MNU_FINANCE ?></label>
																		</div>

																		<div class="col-md-12 form-group custom-control custom-checkbox form-group">
																			<input type="checkbox" class="custom-control-input" id="MANAGEMENT_ACCOUNTING" name="MANAGEMENT_ACCOUNTING" value="1" <? if ($MANAGEMENT_ACCOUNTING == 1) echo "checked"; ?>>
																			<label class="custom-control-label" for="MANAGEMENT_ACCOUNTING"><?= MNU_ACCOUNTING ?></label>
																		</div>
																		<!-- Accounting -->
																		<div class="col-md-12">
																			<?php if (check_global_access() == 1) { ?>
																				<div class="col-md-12 form-group custom-control custom-checkbox form-group">
																					<input type="checkbox" class="custom-control-input" id="MANAGEMENT_UNPOST_BATCHES" name="MANAGEMENT_UNPOST_BATCHES" value="1" <? if ($MANAGEMENT_UNPOST_BATCHES == 1) echo "checked"; ?>>
																					<label class="custom-control-label" for="MANAGEMENT_UNPOST_BATCHES"><?= MNU_UNPOST_BATCHES ?></label>
																				</div>
																			<?php } ?>
																			<div class="col-md-12 form-group custom-control custom-checkbox form-group">
																				<input type="checkbox" class="custom-control-input" id="MANAGEMENT_DIAMOND_PAY" name="MANAGEMENT_DIAMOND_PAY" value="1" <? if ($MANAGEMENT_DIAMOND_PAY == 1) echo "checked"; ?>>
																				<label class="custom-control-label" for="MANAGEMENT_DIAMOND_PAY"><?= MNU_DIAMOND_PAY ?></label>
																			</div>
																		</div>

																		<div class="col-md-12 form-group custom-control custom-checkbox form-group">
																			<input type="checkbox" class="custom-control-input" id="MANAGEMENT_PLACEMENT" name="MANAGEMENT_PLACEMENT" value="1" <? if ($MANAGEMENT_PLACEMENT == 1) echo "checked"; ?>>
																			<label class="custom-control-label" for="MANAGEMENT_PLACEMENT"><?= MNU_PLACEMENT ?></label>
																		</div>
																		<? $res_coe = $db->Execute("SELECT COE,ECM,_1098T,_90_10, IPEDS, POPULATION_REPORT, CUSTOM_QUERIES, FISAP  FROM Z_ACCOUNT_REPORTS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); //Ticket # 1295 Ticket # 1778
																		?>
																		<!-- Ticket # 1911 -->
																		<div class="col-md-12 form-group custom-control custom-checkbox form-group">
																			<input type="checkbox" class="custom-control-input" id="MANAGEMENT_BULK_UPDATE" name="MANAGEMENT_BULK_UPDATE" value="1" <? if ($MANAGEMENT_BULK_UPDATE == 1) echo "checked"; ?>>
																			<label class="custom-control-label" for="MANAGEMENT_BULK_UPDATE"><?= MNU_BULK_UPDATES ?></label>
																		</div>
																		<!-- Ticket # 1911  -->
																		<div class="col-md-12 form-group custom-control custom-checkbox form-group">
																			<input type="checkbox" class="custom-control-input" id="MANAGEMENT_COMPLIANCE" name="MANAGEMENT_COMPLIANCE" value="1" <? if ($MANAGEMENT_COMPLIANCE == 1) echo "checked"; ?>>
																			<label class="custom-control-label" for="MANAGEMENT_COMPLIANCE"><?= MNU_COMPLIANCE ?></label>
																		</div>
																		<div class="col-md-12">
																			<? if ($res_coe->fields['_90_10'] == 1) { ?>
																				<div class="col-md-12 form-group custom-control custom-checkbox form-group">
																					<input type="checkbox" class="custom-control-input" id="MANAGEMENT_90_10" name="MANAGEMENT_90_10" value="1" <? if ($MANAGEMENT_90_10 == 1) echo "checked"; ?>>
																					<label class="custom-control-label" for="MANAGEMENT_90_10"><?= MNU_90_10 ?></label><!-- Ticket # 1717 -->
																				</div>
																			<? } ?>
																			<? /* Ticket # 1778  */
																			if ($res_coe->fields['FISAP'] == 1) { ?>
																				<div class="col-md-12 form-group custom-control custom-checkbox form-group">
																					<input type="checkbox" class="custom-control-input" id="MANAGEMENT_FISAP" name="MANAGEMENT_FISAP" value="1" <? if ($MANAGEMENT_FISAP == 1) echo "checked"; ?>>
																					<label class="custom-control-label" for="MANAGEMENT_FISAP"><?= MNU_FISAP_REPORT ?></label>
																				</div>
																			<? } /* Ticket # 1778  */ ?>

																			<? if ($res_coe->fields['IPEDS'] == 1) { ?>
																				<div class="col-md-12 form-group custom-control custom-checkbox form-group">
																					<input type="checkbox" class="custom-control-input" id="MANAGEMENT_IPEDS" name="MANAGEMENT_IPEDS" value="1" <? if ($MANAGEMENT_IPEDS == 1) echo "checked"; ?>>
																					<label class="custom-control-label" for="MANAGEMENT_IPEDS"><?= MNU_IPEDS ?></label><!-- Ticket # 1717 -->
																				</div>
																			<? } ?>

																			<? if ($res_coe->fields['POPULATION_REPORT'] == 1) { ?>
																				<div class="col-md-12 form-group custom-control custom-checkbox form-group">
																					<input type="checkbox" class="custom-control-input" id="MANAGEMENT_POPULATION_REPORT" name="MANAGEMENT_POPULATION_REPORT" value="1" <? if ($MANAGEMENT_POPULATION_REPORT == 1) echo "checked"; ?>>
																					<label class="custom-control-label" for="MANAGEMENT_POPULATION_REPORT"><?= POPULATION_REPORT ?></label><!-- Ticket #1294 -->
																				</div>
																			<? } ?>
																		</div>

																		<? if ($res_coe->fields['ECM'] == 1) { ?>
																			<div class="col-md-12 form-group custom-control custom-checkbox form-group">
																				<input type="checkbox" class="custom-control-input" id="MANAGEMENT_TITLE_IV_SERVICER" name="MANAGEMENT_TITLE_IV_SERVICER" value="1" <? if ($MANAGEMENT_TITLE_IV_SERVICER == 1) echo "checked"; ?>>
																				<label class="custom-control-label" for="MANAGEMENT_TITLE_IV_SERVICER"><?= MNU_TITLE_IV_SERVICER ?></label>
																			</div>
																		<? } ?>

																		<?php if ($res_coe->fields['COE'] == 1) { ?>
																			<div class="col-md-12 form-group custom-control custom-checkbox form-group">
																				<input type="checkbox" class="custom-control-input" id="MANAGEMENT_ACCREDITATION" name="MANAGEMENT_ACCREDITATION" value="1" <? if ($MANAGEMENT_ACCREDITATION == 1) echo "checked"; ?>>
																				<label class="custom-control-label" for="MANAGEMENT_ACCREDITATION"><?= MNU_ACCREDITATION ?></label>
																			</div>
																		<? } ?>




																		<? /* Ticket # 1295 */
																		if ($res_coe->fields['CUSTOM_QUERIES'] == 1) { ?>
																			<div class="col-md-12 form-group custom-control custom-checkbox form-group">
																				<input type="checkbox" class="custom-control-input" id="MANAGEMENT_CUSTOM_QUERY" name="MANAGEMENT_CUSTOM_QUERY" value="1" <? if ($MANAGEMENT_CUSTOM_QUERY == 1) echo "checked"; ?>>
																				<label class="custom-control-label" for="MANAGEMENT_CUSTOM_QUERY"><?= MNU_CUSTOM_QUERIES ?></label><!-- Ticket #1294 --><!-- Ticket # 1717 -->
																			</div>
																		<? }
																		/* Ticket # 1295 */ ?>

																		<!-- Ticket # 1940 -->

																		<!-- Ticket # 569 -->
																		<div class="col-md-12 form-group custom-control custom-checkbox form-group">
																			<input type="checkbox" class="custom-control-input" id="MANAGEMENT_DATA_TOOLS" name="MANAGEMENT_DATA_TOOLS" value="1" <? if ($MANAGEMENT_DATA_TOOLS == 1) echo "checked"; ?>>
																			<label class="custom-control-label" for="MANAGEMENT_DATA_TOOLS"><?= MNU_DATA_TOOLS ?></label>
																		</div>
																		<!-- DIAM-2177 -->
																		<div class="col-md-12">
																			<div class="col-md-12 form-group custom-control custom-checkbox form-group">
																				<input type="checkbox" class="custom-control-input" id="SETUP_CONSOLIDATION_TOOL" name="SETUP_CONSOLIDATION_TOOL" value="1" <? if ($SETUP_CONSOLIDATION_TOOL == 1) echo "checked"; ?>>
																				<label class="custom-control-label" for="SETUP_CONSOLIDATION_TOOL"><?= MNU_CONSOLIDATION_TOOL ?></label>
																			</div>
																		</div>
																		<div class="col-md-12">
																			<div class="col-md-12 form-group custom-control custom-checkbox form-group">
																				<input type="checkbox" class="custom-control-input" id="SETUP_DATA_EXPORT" name="SETUP_DATA_EXPORT" value="1" <? if ($SETUP_DATA_EXPORT == 1) echo "checked"; ?>>
																				<label class="custom-control-label" for="SETUP_DATA_EXPORT">Data Export</label>
																			</div>
																		</div>
																		<!-- End DIAM-2177 -->
																		<!-- DIAM-842,843 -->
																		<?php //if(has_custom_sap_report($_SESSION['PK_ACCOUNT'])){ 
																		?>
																		<!-- <div class="col-md-12 form-group custom-control custom-checkbox form-group">
																	<input type="checkbox" class="custom-control-input" id="MANAGEMENT_CUSTOM" name="MANAGEMENT_CUSTOM" value="1" <? //if($MANAGEMENT_CUSTOM == 1) echo "checked"; 
																																													?> >
																	<label class="custom-control-label" for="MANAGEMENT_CUSTOM"><? //=MNU_CUSTOM 
																																?></label>
																</div> -->
																		<?php //} 
																		?>
																		<!-- DIAM-2090 -->
																		<div class="col-md-12 form-group custom-control custom-checkbox form-group">
																			<input type="checkbox" class="custom-control-input" id="MANAGEMENT_CUSTOM_REPORT" name="MANAGEMENT_CUSTOM_REPORT" value="1" <? if ($MANAGEMENT_CUSTOM_REPORT == 1) echo "checked"; ?>>
																			<label class="custom-control-label" for="MANAGEMENT_CUSTOM_REPORT"><?= MNU_CUSTOM_REPORTS ?></label>
																		</div>
																		<!-- DIAM-2090 -->
																		<!-- Ticket # 921  -->
																		<!--<div class="col-md-12 form-group custom-control custom-checkbox form-group">
																	<input type="checkbox" class="custom-control-input" id="MANAGEMENT_UPLOADS" name="MANAGEMENT_UPLOADS" value="1" <? if ($MANAGEMENT_UPLOADS == 1) echo "checked"; ?> >
																	<label class="custom-control-label" for="MANAGEMENT_UPLOADS"><?= UPLOADS ?></label>
																</div>--><!-- Ticket # 921  -->
																	</div>
																</div>

																<div class="col-md-4">
																	<div class="row">
																		<h4 style="font-size: 14px;font-weight: bold;"><?= MNU_REPORTS ?></h4>
																		<div class="col-md-12 form-group custom-control custom-checkbox form-group">
																			<input type="checkbox" class="custom-control-input" id="REPORT_ADMISSION" name="REPORT_ADMISSION" value="1" <? if ($REPORT_ADMISSION == 1) echo "checked"; ?>>
																			<label class="custom-control-label" for="REPORT_ADMISSION"><?= MNU_ADMISSION ?></label>
																		</div>

																		<div class="col-md-12 form-group custom-control custom-checkbox form-group">
																			<input type="checkbox" class="custom-control-input" id="REPORT_REGISTRAR" name="REPORT_REGISTRAR" value="1" <? if ($REPORT_REGISTRAR == 1) echo "checked"; ?>>
																			<label class="custom-control-label" for="REPORT_REGISTRAR"><?= MNU_REGISTRAR ?></label>
																		</div>

																		<div class="col-md-12 form-group custom-control custom-checkbox form-group">
																			<input type="checkbox" class="custom-control-input" id="REPORT_FINANCE" name="REPORT_FINANCE" value="1" <? if ($REPORT_FINANCE == 1) echo "checked"; ?>>
																			<label class="custom-control-label" for="REPORT_FINANCE"><?= MNU_FINANCE ?></label>
																		</div>

																		<div class="col-md-12 form-group custom-control custom-checkbox form-group">
																			<input type="checkbox" class="custom-control-input" id="REPORT_ACCOUNTING" name="REPORT_ACCOUNTING" value="1" <? if ($REPORT_ACCOUNTING == 1) echo "checked"; ?>>
																			<label class="custom-control-label" for="REPORT_ACCOUNTING"><?= MNU_ACCOUNTING ?></label>
																		</div>

																		<div class="col-md-12 form-group custom-control custom-checkbox form-group">
																			<input type="checkbox" class="custom-control-input" id="REPORT_PLACEMENT" name="REPORT_PLACEMENT" value="1" <? if ($REPORT_PLACEMENT == 1) echo "checked"; ?>>
																			<label class="custom-control-label" for="REPORT_PLACEMENT"><?= MNU_PLACEMENT ?></label>
																		</div>

																		<div class="col-md-12 form-group custom-control custom-checkbox form-group">
																			<input type="checkbox" class="custom-control-input" id="REPORT_CUSTOM_REPORT" name="REPORT_CUSTOM_REPORT" value="1" <? if ($REPORT_CUSTOM_REPORT == 1) echo "checked"; ?>>
																			<label class="custom-control-label" for="REPORT_CUSTOM_REPORT"><?= MNU_GENERAL ?></label>
																		</div>

																	</div>
																</div>

																<div class="col-md-4">
																	<div class="row">
																		<h4 style="font-size: 14px;font-weight: bold;"><?= MNU_SETUP ?></h4>

																		<div class="col-md-12 form-group custom-control custom-checkbox form-group">
																			<input type="checkbox" class="custom-control-input" id="SETUP_SCHOOL" name="SETUP_SCHOOL" value="1" <? if ($SETUP_SCHOOL == 1) echo "checked"; ?>>
																			<label class="custom-control-label" for="SETUP_SCHOOL"><?= MNU_SCHOOL ?></label>
																		</div>

																		<div class="col-md-12 form-group custom-control custom-checkbox form-group">
																			<input type="checkbox" class="custom-control-input" id="SETUP_ADMISSION" name="SETUP_ADMISSION" value="1" <? if ($SETUP_ADMISSION == 1) echo "checked"; ?>>
																			<label class="custom-control-label" for="SETUP_ADMISSION"><?= MNU_ADMISSION ?></label>
																		</div>

																		<div class="col-md-12 form-group custom-control custom-checkbox form-group">
																			<input type="checkbox" class="custom-control-input" id="SETUP_STUDENT" name="SETUP_STUDENT" value="1" <? if ($SETUP_STUDENT == 1) echo "checked"; ?>>
																			<label class="custom-control-label" for="SETUP_STUDENT"><?= MNU_STUDENT ?></label>
																		</div>

																		<div class="col-md-12 form-group custom-control custom-checkbox form-group">
																			<input type="checkbox" class="custom-control-input" id="SETUP_FINANCE" name="SETUP_FINANCE" value="1" <? if ($SETUP_FINANCE == 1) echo "checked"; ?>>
																			<label class="custom-control-label" for="SETUP_FINANCE"><?= MNU_FINANCE ?></label>
																		</div>

																		<div class="col-md-12 form-group custom-control custom-checkbox form-group">
																			<input type="checkbox" class="custom-control-input" id="SETUP_REGISTRAR" name="SETUP_REGISTRAR" value="1" <? if ($SETUP_REGISTRAR == 1) echo "checked"; ?>>
																			<label class="custom-control-label" for="SETUP_REGISTRAR"><?= MNU_REGISTRAR ?></label>
																		</div>

																		<div class="col-md-12 form-group custom-control custom-checkbox form-group">
																			<input type="checkbox" class="custom-control-input" id="SETUP_ACCOUNTING" name="SETUP_ACCOUNTING" value="1" <? if ($SETUP_ACCOUNTING == 1) echo "checked"; ?>>
																			<label class="custom-control-label" for="SETUP_ACCOUNTING"><?= MNU_ACCOUNTING ?></label>
																		</div>

																		<div class="col-md-12 form-group custom-control custom-checkbox form-group">
																			<input type="checkbox" class="custom-control-input" id="SETUP_PLACEMENT" name="SETUP_PLACEMENT" value="1" <? if ($SETUP_PLACEMENT == 1) echo "checked"; ?>>
																			<label class="custom-control-label" for="SETUP_PLACEMENT"><?= MNU_PLACEMENT ?></label>
																		</div>

																		<div class="col-md-12 form-group custom-control custom-checkbox form-group">
																			<input type="checkbox" class="custom-control-input" id="SETUP_COMMUNICATION" name="SETUP_COMMUNICATION" value="1" <? if ($SETUP_COMMUNICATION == 1) echo "checked"; ?>>
																			<label class="custom-control-label" for="SETUP_COMMUNICATION"><?= MNU_COMMUNICATION ?></label>
																		</div>

																		<div class="col-md-12 form-group custom-control custom-checkbox form-group">
																			<input type="checkbox" class="custom-control-input" id="SETUP_TASK_MANAGEMENT" name="SETUP_TASK_MANAGEMENT" value="1" <? if ($SETUP_TASK_MANAGEMENT == 1) echo "checked"; ?>>
																			<label class="custom-control-label" for="SETUP_TASK_MANAGEMENT"><?= MNU_TASK_MANAGEMENT ?></label>
																		</div>

																	</div>
																</div>
															</div>
														</div>

													<? } ?>
												</div>

												<div class="row">
													<div class="col-md-8">&nbsp;</div>
													<div class="col-md-4">
														<button onclick="validate_form(1)" type="button" class="btn waves-effect waves-light btn-info"><?= SAVE ?></button>
														<button onclick="validate_form(0)" type="button" class="btn waves-effect waves-light btn-info"><?= SAVE_EXIT ?></button>

														<button type="button" onclick="window.location.href='manage_employee?t=<?= $_GET['t'] ?>'" class="btn waves-effect waves-light btn-dark"><?= CANCEL ?></button>
													</div>
												</div>
											</div>
										</div>

										<div class="tab-pane <?= $notes_tab ?> " id="notes" role="tabpanel">

											<div class="row">
												<div class="col-md-10 align-self-center">
												</div>
												<div class="col-md-2 align-self-center text-right">
													<div class="d-flex justify-content-end align-items-center">
														<a href="employee_notes.php?eid=<?= $_GET['id'] ?>&t=<?= $_GET['t'] ?>" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> <?= CREATE_NEW ?></a>&nbsp;&nbsp;
													</div>
												</div>
											</div>
											<div class="table-responsive p-20" id="employee_notes_div">

												<? $_REQUEST['PK_EMPLOYEE_MASTER']	= $_GET['id'];
												include("ajax_get_employee_notes.php"); ?>

											</div>
										</div>
									<? } ?>
								</div>

								<input type="text" id="form_edited" name="form_edited" value="0" style="display:none" />
								<input type="hidden" name="SAVE_CONTINUE" id="SAVE_CONTINUE" value="0" />
								<input type="hidden" id="current_tab" name="current_tab" value="0">
							</form>
						</div>
					</div>
				</div>

			</div>
		</div>
		<? require_once("footer.php"); ?>

		<div class="modal" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title" id="exampleModalLabel1"><?= DELETE_CONFIRMATION ?></h4>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<div class="modal-body">
						<div class="form-group" id="delete_message"></div>
						<input type="hidden" id="DELETE_ID" value="0" />
						<input type="hidden" id="DELETE_TYPE" value="0" />
					</div>
					<div class="modal-footer">
						<button type="button" onclick="conf_delete(1)" class="btn waves-effect waves-light btn-info"><?= YES ?></button>
						<button type="button" class="btn waves-effect waves-light btn-dark" onclick="conf_delete(0)"><?= NO ?></button>
					</div>
				</div>
			</div>
		</div>

		<div class="modal" id="SSNModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
			<form class="floating-labels m-t-40" method="post" name="form2" id="form2">
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-body">
							<input type="hidden" name="FORM_NAME" value="ssn">
							<div class="d-flex row">
								<div class="col-12 col-sm-12 form-group">
									<b>Existing SSN: <?= $SSN_ORG ?></b>
								</div>
								<div class="col-12 col-sm-12 form-group">
									<input id="SSN_1" name="SSN_1" type="text" class="form-control validate-ssn required-entry ssn-inputmask" value="">
									<span class="bar"></span>
									<label for="SSN">
										<?= SSN ?>
									</label>
								</div>

								<div class="col-12 col-sm-12 form-group">
									<button type="submit" class="btn waves-effect waves-light btn-info"><?= SAVE ?></button>
									<button type="button" class="btn waves-effect waves-light btn-dark" onclick="close_pop('SSNModal')"><?= CANCEL ?></button>
								</div>
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>

		<div class="modal" id="errorModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<div class="modal-body">
						<div class="form-group" id="error_message"></div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn waves-effect waves-light btn-dark" onclick="close_popup()"><?= OK ?></button>
					</div>
				</div>
			</div>
		</div>

		<div class="modal" id="sendToEthinkModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title" id="exampleModalLabel1"><?= CONFIRMATION ?></h4>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<div class="modal-body">
						<div class="form-group"><?= CONFIRMATION_SEND_TO_ETHINK ?></div>
					</div>
					<div class="modal-footer">
						<button type="button" onclick="conf_send_to_ethink(1)" class="btn waves-effect waves-light btn-info"><?= YES ?></button>
						<button type="button" class="btn waves-effect waves-light btn-dark" onclick="conf_send_to_ethink(0)"><?= NO ?></button>
					</div>
				</div>
			</div>
		</div>

		<!--
		<div class="modal" id="notesModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
			<form class="floating-labels m-t-40" method="post" name="form3" id="form3" >
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-body">
							<input type="hidden" name="FORM_NAME" value="notes" >
							<div class="d-flex row">
								<div class="col-12 col-sm-12 form-group">
									<select id="PK_EMPLOYEE_NOTE_TYPE" name="PK_EMPLOYEE_NOTE_TYPE" class="form-control">
										<option></option>
										<? /*$res_type = $db->Execute("select PK_EMPLOYEE_NOTE_TYPE,EMPLOYEE_NOTE_TYPE from M_EMPLOYEE_NOTE_TYPE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by EMPLOYEE_NOTE_TYPE ASC");
										while (!$res_type->EOF) { ?>
											<option value="<?=$res_type->fields['PK_EMPLOYEE_NOTE_TYPE']?>" ><?=$res_type->fields['EMPLOYEE_NOTE_TYPE']?></option>
										<?	$res_type->MoveNext();
										}*/ ?>
									</select>
									<span class="bar"></span> 
									<label for="PK_EMPLOYEE_NOTE_TYPE">
										<?= TYPE ?>
									</label>
								</div>
								
								<div class="col-12 col-sm-12 form-group">
									<textarea class="form-control" rows="2" id="NOTES" name="NOTES"></textarea>
									<span class="bar"></span> 
									<label for="NOTES">
										<?= NOTES ?>
									</label>
								</div>
								
								<div class="col-12 col-sm-12 form-group">
									<button type="submit" class="btn waves-effect waves-light btn-info"><?= SAVE ?></button>
									<button type="button" class="btn waves-effect waves-light btn-dark" onclick="close_pop('notesModal')" ><?= CANCEL ?></button>
								</div>
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>
		-->
	</div>

	<? require_once("js.php"); ?>
	<script src="../backend_assets/dist/js/jquery.are-you-sure.js"></script>

	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />

	<script type="text/javascript">
		<? if ($_GET['tab'] != '') { ?>
			var current_tab = '<?= $_GET['tab'] ?>';
		<? } else { ?>
			var current_tab = 'generalTab';
		<? } ?>
		jQuery(document).ready(function($) {
			jQuery('.date').datepicker({
				todayHighlight: true,
				orientation: "bottom auto"
			});

			$('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
				current_tab = $(e.target).attr("href") // activated tab
				//alert(current_tab)
			});

			//$('#form1').areYouSure();

			<? if ($_GET['t'] == 2) { ?>
				document.getElementById('PK_DEPARTMENT_<?= $PK_DEPARTMENT_FACULTY ?>').checked = true;
			<? } ?>
		});

		function validate_form(val) {
			jQuery(document).ready(function($) {
				document.getElementById("SAVE_CONTINUE").value = val;
				document.getElementById('current_tab').value = current_tab;
				<? if ($_GET['id'] == '') { ?>
					var valid = new Validation('form1', {
						onSubmit: false
					});
					var result = valid.validate();
					if (result == true) {
						//document.form1.submit();
						check_department_campus()
					} else {
						return false;
					}
					<? } else {
					if ($LOGIN_CREATED == 0) { ?>
						document.form1.submit();
					<? } else { ?>
						check_department_campus()
				<? }
				} ?>
			});
		}

		function check_department_campus() {
			jQuery(document).ready(function($) {
				var PK_CAMPUS = $('#PK_CAMPUS').val()

				var flag = 0;
				var PK_DEPARTMENT = document.getElementsByName('PK_DEPARTMENT[]')
				for (var i = 0; i < PK_DEPARTMENT.length; i++) {
					if (PK_DEPARTMENT[i].checked == true) {
						flag++;
						break;
					}
				}

				var IS_ADMIN = 0;
				if (document.getElementById('IS_ADMIN')) {
					if (document.getElementById('IS_ADMIN').checked == true)
						IS_ADMIN = 1;
				} else if (document.getElementById('NEED_SCHOOL_ACCESS')) {
					if (document.getElementById('NEED_SCHOOL_ACCESS').checked == true)
						IS_ADMIN = 0;
					else
						IS_ADMIN = 1;
				}

				var error_msg = '';
				if ((PK_CAMPUS == '' || flag == 0) && IS_ADMIN == 0) {
					if (PK_CAMPUS == '' && flag == 0) {
						error_msg = '<?= PLEASE_SELECT_DEPARTMENT_CAMPUS ?>';
					} else if (PK_CAMPUS == '') {
						error_msg = '<?= PLEASE_SELECT_CAMPUS ?>';
					} else if (flag == 0) {
						error_msg = '<?= PLEASE_SELECT_DEPARTMENT ?>';
					}

					show_error_popup(error_msg)
					return false;
				} else {
					document.form1.submit();
				}
			});
		}

		function show_error_popup(msg) {
			jQuery(document).ready(function($) {
				$("#errorModal").modal()
				document.getElementById('error_message').innerHTML = msg
			});
		}

		function close_popup() {
			jQuery(document).ready(function($) {
				$("#errorModal").modal("hide");
			});
		}

		function set_def_value() {
			if (document.getElementById('IS_ADMIN').checked == true) {
				document.getElementById('ADMISSION_FULL_ACCESS').checked = true
				document.getElementById('REGISTRAR_FULL_ACCESS').checked = true
				document.getElementById('FINANCE_FULL_ACCESS').checked = true
				document.getElementById('ACCOUNTING_FULL_ACCESS').checked = true
				document.getElementById('PLACEMENT_FULL_ACCESS').checked = true

				document.getElementById('MANAGEMENT_ADMISSION').checked = true
				document.getElementById('MANAGEMENT_REGISTRAR').checked = true
				document.getElementById('MANAGEMENT_FINANCE').checked = true
				document.getElementById('MANAGEMENT_ACCOUNTING').checked = true
				document.getElementById('MANAGEMENT_PLACEMENT').checked = true
				//document.getElementById('MANAGEMENT_UPLOADS').checked 		= true Ticket # 921

				if (document.getElementById('MANAGEMENT_ACCREDITATION'))
					document.getElementById('MANAGEMENT_ACCREDITATION').checked = true

				if (document.getElementById('MANAGEMENT_TITLE_IV_SERVICER'))
					document.getElementById('MANAGEMENT_TITLE_IV_SERVICER').checked = true

				if (document.getElementById('MANAGEMENT_90_10'))
					document.getElementById('MANAGEMENT_90_10').checked = true

				if (document.getElementById('MANAGEMENT_FISAP')) //Ticket # 1778
					document.getElementById('MANAGEMENT_FISAP').checked = true //Ticket # 1778

				if (document.getElementById('MANAGEMENT_IPEDS'))
					document.getElementById('MANAGEMENT_IPEDS').checked = true

				if (document.getElementById('MANAGEMENT_POPULATION_REPORT'))
					document.getElementById('MANAGEMENT_POPULATION_REPORT').checked = true

				document.getElementById('REPORT_ADMISSION').checked = true
				document.getElementById('REPORT_REGISTRAR').checked = true
				document.getElementById('REPORT_FINANCE').checked = true
				document.getElementById('REPORT_ACCOUNTING').checked = true
				document.getElementById('REPORT_PLACEMENT').checked = true
				document.getElementById('REPORT_CUSTOM_REPORT').checked = true

				if (document.getElementById('REPORT_COMPLIANCE_REPORTS'))
					document.getElementById('REPORT_COMPLIANCE_REPORTS').checked = true

				document.getElementById('SETUP_SCHOOL').checked = true
				document.getElementById('SETUP_ADMISSION').checked = true
				document.getElementById('SETUP_STUDENT').checked = true
				document.getElementById('SETUP_FINANCE').checked = true
				document.getElementById('SETUP_REGISTRAR').checked = true
				document.getElementById('SETUP_ACCOUNTING').checked = true
				document.getElementById('SETUP_PLACEMENT').checked = true
				document.getElementById('SETUP_COMMUNICATION').checked = true
				document.getElementById('SETUP_TASK_MANAGEMENT').checked = true
				document.getElementById('SETUP_CONSOLIDATION_TOOL').checked = true // DIAM-2177
				document.getElementById('SETUP_DATA_EXPORT').checked = true // DIAM-2177

				if (document.getElementById('MANAGEMENT_BULK_UPDATE')) //Ticket # 1911
					document.getElementById('MANAGEMENT_BULK_UPDATE').checked = true //Ticket # 1911

				if (document.getElementById('MANAGEMENT_DIAMOND_PAY')) //Ticket # 1911
					document.getElementById('MANAGEMENT_DIAMOND_PAY').checked = true //Ticket # 1940
				if (document.getElementById('MANAGEMENT_DATA_TOOLS')) //Ticket # 589
					document.getElementById('MANAGEMENT_DATA_TOOLS').checked = true //Ticket # 589
				//if(document.getElementById('MANAGEMENT_CUSTOM')) //Ticket # DIAM-842,843
				//document.getElementById('MANAGEMENT_CUSTOM').checked = true //Ticket # 589
			} else {
				document.getElementById('ADMISSION_NO_ACCESS').checked = true
				document.getElementById('REGISTRAR_NO_ACCESS').checked = true
				document.getElementById('FINANCE_NO_ACCESS').checked = true
				document.getElementById('ACCOUNTING_NO_ACCESS').checked = true
				document.getElementById('PLACEMENT_NO_ACCESS').checked = true

				document.getElementById('MANAGEMENT_ADMISSION').checked = false
				document.getElementById('MANAGEMENT_REGISTRAR').checked = false
				document.getElementById('MANAGEMENT_FINANCE').checked = false
				document.getElementById('MANAGEMENT_ACCOUNTING').checked = false
				document.getElementById('MANAGEMENT_PLACEMENT').checked = false
				//document.getElementById('MANAGEMENT_UPLOADS').checked 		= false Ticket # 921

				if (document.getElementById('MANAGEMENT_ACCREDITATION'))
					document.getElementById('MANAGEMENT_ACCREDITATION').checked = false

				if (document.getElementById('MANAGEMENT_TITLE_IV_SERVICER'))
					document.getElementById('MANAGEMENT_TITLE_IV_SERVICER').checked = false

				if (document.getElementById('MANAGEMENT_90_10'))
					document.getElementById('MANAGEMENT_90_10').checked = false

				if (document.getElementById('MANAGEMENT_FISAP')) //Ticket # 1778
					document.getElementById('MANAGEMENT_FISAP').checked = false //Ticket # 1778

				if (document.getElementById('MANAGEMENT_IPEDS'))
					document.getElementById('MANAGEMENT_IPEDS').checked = false

				if (document.getElementById('MANAGEMENT_POPULATION_REPORT'))
					document.getElementById('MANAGEMENT_POPULATION_REPORT').checked = false

				document.getElementById('REPORT_ADMISSION').checked = false
				document.getElementById('REPORT_REGISTRAR').checked = false
				document.getElementById('REPORT_FINANCE').checked = false
				document.getElementById('REPORT_ACCOUNTING').checked = false
				document.getElementById('REPORT_PLACEMENT').checked = false
				document.getElementById('REPORT_CUSTOM_REPORT').checked = false

				if (document.getElementById('REPORT_COMPLIANCE_REPORTS'))
					document.getElementById('REPORT_COMPLIANCE_REPORTS').checked = false

				document.getElementById('SETUP_SCHOOL').checked = false
				document.getElementById('SETUP_ADMISSION').checked = false
				document.getElementById('SETUP_STUDENT').checked = false
				document.getElementById('SETUP_FINANCE').checked = false
				document.getElementById('SETUP_REGISTRAR').checked = false
				document.getElementById('SETUP_ACCOUNTING').checked = false
				document.getElementById('SETUP_PLACEMENT').checked = false
				document.getElementById('SETUP_COMMUNICATION').checked = false
				document.getElementById('SETUP_TASK_MANAGEMENT').checked = false
				document.getElementById('SETUP_CONSOLIDATION_TOOL').checked = false // DIAM-2177
				document.getElementById('SETUP_DATA_EXPORT').checked = false // DIAM-2177

				if (document.getElementById('MANAGEMENT_BULK_UPDATE')) //Ticket # 1911
					document.getElementById('MANAGEMENT_BULK_UPDATE').checked = false //Ticket # 1911

				if (document.getElementById('MANAGEMENT_DIAMOND_PAY')) //Ticket # 1911
					document.getElementById('MANAGEMENT_DIAMOND_PAY').checked = false //Ticket # 1940
				if (document.getElementById('MANAGEMENT_DATA_TOOLS')) //Ticket # 568
					document.getElementById('MANAGEMENT_DATA_TOOLS').checked = false //Ticket # 568
				//if(document.getElementById('MANAGEMENT_CUSTOM')) //Ticket # DIAM-842,843
				//document.getElementById('MANAGEMENT_CUSTOM').checked = false //Ticket # DIAM-842,843

			}
		}
	</script>

	<script type="text/javascript" src="https://code.jquery.com/jquery-migrate-1.4.1.min.js"></script>
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			<? if ($_GET['id'] != '') { ?>
				//get_country('<?= $PK_STATES ?>','PK_COUNTRY')
			<? } ?>
		});

		function set_faculty_department() {
			if (document.getElementById('IS_FACULTY').checked == true)
				document.getElementById('PK_DEPARTMENT_<?= $PK_DEPARTMENT_FACULTY ?>').checked = true;
			else {
				<? if ($_GET['id'] == '') { ?>
					document.getElementById('PK_DEPARTMENT_<?= $PK_DEPARTMENT_FACULTY ?>').checked = false;
				<? } ?>
			}
		}

		function get_country(val, id) {
			jQuery(document).ready(function($) {
				var data = 'state=' + val + '&id=' + id;
				var value = $.ajax({
					url: "../super_admin/ajax_get_country_from_state",
					type: "POST",
					data: data,
					async: false,
					cache: false,
					success: function(data) {
						//alert(data)
						document.getElementById(id).innerHTML = data;
						document.getElementById('PK_COUNTRY_LABEL').classList.add("focused");
						set_form_edited()
					}
				}).responseText;
			});
		}

		function delete_row(id, type) {
			jQuery(document).ready(function($) {
				if (type == 'user_login')
					document.getElementById('delete_message').innerHTML = '<?= REMOVE_LOGIN_CONFIRMATION ?>';
				else if (type == 'img')
					document.getElementById('delete_message').innerHTML = '<?= DELETE_MESSAGE . IMAGE ?>?';
				else if (type == 'notes')
					document.getElementById('delete_message').innerHTML = '<?= DELETE_MESSAGE . NOTES ?>?';

				$("#deleteModal").modal()
				$("#DELETE_ID").val(id)
				$("#DELETE_TYPE").val(type)
			});
		}

		function conf_delete(val) {
			jQuery(document).ready(function($) {
				if (val == 1) {
					if ($("#DELETE_TYPE").val() == 'user_login')
						window.location.href = 'employee?act=user_login&id=' + $("#DELETE_ID").val() + '&t=<?= $_GET['t'] ?>';
					else if ($("#DELETE_TYPE").val() == 'img')
						window.location.href = 'employee?act=img_del&id=<?= $_GET['id'] ?>&t=<?= $_GET['t'] ?>';
					else if ($("#DELETE_TYPE").val() == 'notes')
						window.location.href = 'employee?act=notes&id=<?= $_GET['id'] ?>&iid=' + $("#DELETE_ID").val() + '&t=<?= $_GET['t'] ?>';
				}
				$("#deleteModal").modal("hide");
			});
		}

		function send_to_ethink() {
			jQuery(document).ready(function($) {
				$("#sendToEthinkModal").modal()
			});
		}

		function conf_send_to_ethink(val) {
			jQuery(document).ready(function($) {
				if (val == 1) {
					var data = "id=<?= $_GET['id'] ?>&type=emp";
					$.ajax({
						type: "POST",
						url: "ajax_send_data_to_ethink",
						data: data,
						success: function(result1) {
							result1 = result1.split("|||");
							alert(result1[1])
							$("#sendToEthinkModal").modal("hide");
							if (result1[0] == 1)
								document.getElementById('ethink_button').style.display = 'none'
						}
					});
				} else
					$("#sendToEthinkModal").modal("hide");
			});
		}

		function generate_ethnicity() {
			var ethnicity = '';
			if (document.getElementById('RACE_1').checked == true)
				ethnicity = 'Hispanic/Latino';
			else {
				var RACE = document.getElementsByName('RACE[]')
				for (var i = 0; i < RACE.length; i++) {
					if (RACE[i].checked == true)
						if (ethnicity == '') {
							//alert(('LBL_RACE_'+RACE[i].value))
							ethnicity = document.getElementById('LBL_RACE_' + RACE[i].value).innerHTML;
						} else
							ethnicity = 'Two or more races';
				}
			}
			document.getElementById('IPEDS_ETHNICITY').value = ethnicity;
			document.getElementById('IPEDS_ETHNICITY_LABEL').classList.add("focused");

		}

		function duplicate_check() {
			jQuery(document).ready(function($) {
				if (document.form1.USER_ID.value != "") {
					var USER_ID = document.form1.USER_ID.value;
					var data = "USER_ID=" + USER_ID + '&type=USER_ID&id=<?= $PK_USER ?>';
					$.ajax({
						type: "POST",
						url: "../check_duplicate",
						data: data,
						success: function(result1) {
							if (result1 == 1) {
								document.getElementById('already_exit').style.display = "block";
								document.getElementById('USER_ID').value = "";
								return false;
							} else {
								document.getElementById('already_exit').style.display = "none";
							}
						}
					});
				}
			});
		}

		function duplicate_check_1() {
			jQuery(document).ready(function($) {
				if (document.form1.EMAIL.value != "") {
					var EMAIL = document.form1.EMAIL.value;
					var data = "USER_ID=" + EMAIL + '&type=USER_ID&id=<?= $PK_USER ?>';
					$.ajax({
						type: "POST",
						url: "../check_duplicate",
						data: data,
						success: function(result1) {
							if (result1 == 1) {
								document.getElementById('already_exit_email').style.display = "block";
								document.getElementById('EMAIL').value = "";
								return false;
							} else {
								document.getElementById('already_exit_email').style.display = "none";
							}
						}
					});
				}
			});
		}

		function change_ssn() {
			jQuery(document).ready(function($) {
				$("#SSNModal").modal()
				$("#SSN_1").val('')
			});
		}

		function create_notes() {
			jQuery(document).ready(function($) {
				$("#notesModal").modal()
				$("#NOTES").val('')
				$("#PK_EMPLOYEE_NOTE_TYPE").val('')
				$("#PK_NOTE_STATUS").val('')
			});
		}

		function close_pop(id) {
			jQuery(document).ready(function($) {
				$("#" + id).modal("hide");
			});
		}

		function save_notes() {
			var flag = 1;
			if (document.getElementById('PK_EMPLOYEE_NOTE_TYPE').value == '') {
				document.getElementById('PK_EMPLOYEE_NOTE_TYPE').className = 'form-control validation-failed';
				document.getElementById('PK_EMPLOYEE_NOTE_TYPE_ERROR').innerHTML = '<div class="validation-advice" id="advice-required-entry-FIRST_NAME" style="">This is a required field.</div>';
				flag = 0;
			} else {
				document.getElementById('PK_EMPLOYEE_NOTE_TYPE').className = 'form-control';
				document.getElementById('PK_EMPLOYEE_NOTE_TYPE_ERROR').innerHTML = '';
			}

			if (document.getElementById('NOTES').value == '') {
				document.getElementById('NOTES').className = 'form-control validation-failed';
				document.getElementById('NOTES_ERROR').innerHTML = '<div class="validation-advice" id="advice-required-entry-FIRST_NAME" style="">This is a required field.</div>';
				flag = 0;
			} else {
				document.getElementById('NOTES').className = 'form-control';
				document.getElementById('NOTES_ERROR').innerHTML = '';
			}

			if (flag == 1) {
				jQuery(document).ready(function($) {
					var data = 'PK_EMPLOYEE_NOTE_TYPE=' + $("#PK_EMPLOYEE_NOTE_TYPE").val() + '&PK_NOTE_STATUS=' + $("#PK_NOTE_STATUS").val() + '&NOTES=' + $("#NOTES").val() + '&id=<?= $_GET['id'] ?>';
					var value = $.ajax({
						url: "ajax_save_employee_notes",
						type: "POST",
						data: data,
						async: false,
						cache: false,
						success: function(data) {
							get_employee_notes()
						}
					}).responseText;
				});
			}
		}

		function get_employee_notes() {
			jQuery(document).ready(function($) {
				var data = 'id=<?= $_GET['id'] ?>';
				var value = $.ajax({
					url: "ajax_get_employee_notes",
					type: "POST",
					data: data,
					async: false,
					cache: false,
					success: function(data) {
						document.getElementById('employee_notes_div').innerHTML = data;
						document.getElementById('PK_EMPLOYEE_NOTE_TYPE').value = '';
						document.getElementById('NOTES').value = '';
						$("#PK_NOTE_STATUS").val('')

						$("#form1").removeClass('dirty');
					}
				}).responseText;
			});
		}

		function set_form_edited() {
			document.getElementById('form_edited').focus();
			document.getElementById('form_edited').value = document.getElementById('form_edited').value + "a";
			document.getElementById('form_edited').focus();
			//alert(document.getElementById('form_edited').value)
		}
	</script>

	<script type="text/javascript" src="../backend_assets/dist/js/bootstrap-multiselect.js"></script>
	<link rel="stylesheet" href="../backend_assets/dist/css/bootstrap-multiselect.css" type="text/css" />
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			$('#PK_CAMPUS').multiselect({
				includeSelectAllOption: true,
				allSelectedText: 'All <?= CAMPUS ?>',
				nonSelectedText: '',
				numberDisplayed: 2,
				nSelectedText: '<?= CAMPUS ?> selected'
			});

		});

		var form1 = new Validation('form1');
		var form2 = new Validation('form2');
		var form3 = new Validation('form3');
	</script>
</body>

</html>