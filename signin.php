<? require_once("global/config.php");
require_once("global/mail.php"); //Ticket # 871
require_once("global/texting.php"); //Ticket # 871

$msg = '';

function mask($str, $first, $last) {
    $len = strlen($str);
    $toShow = $first + $last;
    return substr($str, 0, $len <= $toShow ? 0 : $first).str_repeat("*", $len - ($len <= $toShow ? 0 : $toShow)).substr($str, $len - $last, $len <= $toShow ? 0 : $last);
}

function mask_email($email) {
    $mail_parts = explode("@", $email);
    $domain_parts = explode('.', $mail_parts[1]);

    $mail_parts[0] = mask($mail_parts[0], 2, 1); // show first 2 letters and last 1 letter
   //$domain_parts[0] = mask($domain_parts[0], 2, 1); // same here
    $mail_parts[1] = implode('.', $domain_parts);

    return implode("@", $mail_parts);
}

//Ticket # 871
function send_mfa($id, $type, $mfa_type){
	global $db;
	
	$_SESSION['MFA_CODE'] = rand(100000,999999);
	if($type == "employee") {
		$result = $db->Execute("SELECT S_EMPLOYEE_MASTER.PK_ACCOUNT,S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER, LAST_NAME, FIRST_NAME, CONCAT(LAST_NAME,', ',FIRST_NAME) AS NAME, CELL_PHONE, EMAIL FROM S_EMPLOYEE_MASTER, Z_USER, S_EMPLOYEE_CONTACT WHERE S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = ID AND PK_USER = '$id' AND S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_EMPLOYEE_CONTACT.PK_EMPLOYEE_MASTER ");
	} else if($type == "student") {
		$result = $db->Execute("SELECT S_STUDENT_MASTER.PK_ACCOUNT,S_STUDENT_MASTER.PK_STUDENT_MASTER, LAST_NAME, FIRST_NAME, CONCAT(LAST_NAME,', ',FIRST_NAME) AS NAME, CELL_PHONE, EMAIL  FROM S_STUDENT_MASTER, Z_USER, S_STUDENT_CONTACT WHERE S_STUDENT_MASTER.PK_STUDENT_MASTER = ID AND PK_USER = '$id' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_CONTACT.PK_STUDENT_MASTER AND PK_STUDENT_CONTACT_TYPE_MASTER = 1");
	}else{
		$result = $db->Execute("SELECT S_EMPLOYEE_MASTER.PK_ACCOUNT,S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER, LAST_NAME, FIRST_NAME, CONCAT(LAST_NAME,', ',FIRST_NAME) AS NAME, '' as CELL_PHONE , USER_ID as EMAIL  FROM S_EMPLOYEE_MASTER, Z_USER WHERE S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = ID AND PK_USER = '$id' ");
	}
	$PK_ACCOUNT = $result->fields['PK_ACCOUNT'];
	$NAME		= $result->fields['NAME'];
	$CELL_PHONE = $result->fields['CELL_PHONE'];
	$EMAIL 		= $result->fields['EMAIL'];
	$FIRST_NAME = $result->fields['FIRST_NAME'];
	$LAST_NAME 	= $result->fields['LAST_NAME'];
	$TEXT_FIELD 	= "";
	$res = $db->Execute("SELECT LOGO, '$EMAIL','$FIRST_NAME'  FROM Z_ACCOUNT WHERE PK_ACCOUNT = '$PK_ACCOUNT' ");
	$PK_EMAIL_TEMPLATE = $res->fields['PK_EMAIL_TEMPLATE'];
	$PK_TEXT_TEMPLATE  = $res->fields['PK_TEXT_TEMPLATE'];
	
	$LOGO = '';
	if($res->fields['LOGO'] != '') {
		$LOGO = str_replace("../",$http_path,$res->fields['LOGO']);
		$LOGO = '<img src="'.$LOGO.'" style="width:250px">';
	}
	
	if($mfa_type == 1 || $mfa_type == 3){
		//email, both
		$res_template = $db->Execute("SELECT SUBJECT,CONTENT FROM Z_EMAIL_TEMPLATE WHERE ID = 3 AND ACTIVE = 1");
		if($res_template->RecordCount() > 0) {
			$SUBJECT 			= $res_template->fields['SUBJECT'];
			$CONTENT 			= $res_template->fields['CONTENT'];
			$PK_EMAIL_ACCOUNT 	= 1;
			
			$CONTENT = str_ireplace("{Logo}",$LOGO,$CONTENT);
			$CONTENT = str_ireplace("{First Name}",$FIRST_NAME,$CONTENT);
			$CONTENT = str_ireplace("{Last Name}",$LAST_NAME,$CONTENT);
			$CONTENT = str_ireplace("{Student Name}",$NAME,$CONTENT);
			$CONTENT = str_ireplace("{MFA Code}",$_SESSION['MFA_CODE'],$CONTENT);
			
			$receiver['EMAIL'][0] = $EMAIL;
			$receiver['NAME'][0]  = $NAME;
			
			// echo "<pre>PK_EMAIL_ACCOUNT".$PK_EMAIL_ACCOUNT."<br />SUBJECT: ".$SUBJECT."<br />CONTENT: ".$CONTENT;print_r($receiver);exit;
			send_mail($PK_EMAIL_ACCOUNT,$receiver,'','','',$SUBJECT,$CONTENT,'');
		}
	}
	
	if($mfa_type == 2 || $mfa_type == 3 && $CELL_PHONE!=""){
		//phone, both
		$res = $db->Execute("select PK_TEXT_SETTINGS from S_TEXT_SETTINGS WHERE PK_ACCOUNT = '1' ");
		$res_template = $db->Execute("SELECT CONTENT FROM Z_TEXT_TEMPLATE WHERE ID = 1 AND ACTIVE = 1 ");
		if($res_template->RecordCount() > 0) {
			$CONTENT = $res_template->fields['CONTENT'];
			$CONTENT = str_ireplace("{First Name}",$FIRST_NAME,$CONTENT);
			$CONTENT = str_ireplace("{Last Name}",$LAST_NAME,$CONTENT);
			$CONTENT = str_ireplace("{MFA Code}",$_SESSION['MFA_CODE'],$CONTENT);
			
			//echo "<pre>CELL_PHONE".$CELL_PHONE."<br />PK_ACCOUNT: ".$PK_ACCOUNT."<br />CONTENT: ".$CONTENT."<br />PK_TEXT_SETTINGS: ".$res->fields['PK_TEXT_SETTINGS'];exit;
			send_text($CELL_PHONE,$PK_ACCOUNT,$CONTENT,0,$res->fields['PK_TEXT_SETTINGS']);
		}
	}
}

// DIAM-2193
function isUserBlocked($USER_ID) // Function to check if a user is blocked
{
	global $db;

	$result   = $db->Execute("SELECT DATE_FORMAT(BLOCKED_UNTIL, '%Y-%m-%d %H:%i:%s') AS BLOCKED_UNTIL FROM Z_USER where USER_ID = '$USER_ID'");
	// print_r($result);
	if($result->RecordCount() > 0)
	{
		$BLOCKED_UNTIL = $result->fields['BLOCKED_UNTIL'];
		$RECORD_BLOCKED_REC = date('Y-m-d', strtotime($result->fields['BLOCKED_UNTIL']));
		if($RECORD_BLOCKED_REC != '0000-00-00')
		{
			return $BLOCKED_UNTIL; // Return timestamp of block expiration
		}
		else{
			return false;
		}
		
	} else {
        return false;
    }
}

function blockUser($USER_ID, $blockDurationMinutes = 30) // Function to block user
{
	global $db;

    $block_until = date('Y-m-d H:i:s', strtotime("+{$blockDurationMinutes} minutes"));
	$db->Execute("UPDATE Z_USER SET BLOCKED_UNTIL = '$block_until' where USER_ID = '$USER_ID'");
}
// End DIAM-2193

//Ticket # 871
if(!empty($_POST) || $_SESSION['TEMP_PK_USER_1'] != ''){
	if($_POST['form_name'] == 'login' || $_SESSION['TEMP_PK_USER_1'] != '') {
		$USER_ID   = trim($_POST['USER_ID']);
		$result    = $db->Execute("SELECT PASSWORD FROM Z_USER where USER_ID = '$USER_ID'");
		// DIAM-2193
		if($result->RecordCount() == 0) // If User not found
		{
			$msg = 'Invalid User ID';
		}
		else 
		{
			$hash  	   = $result->fields['PASSWORD'];
			$PASSWORD  = crypt($_POST['PASSWORD'], $hash);
			//Ticket # 871
			$cond = " AND Z_USER.USER_ID = '$USER_ID' AND PASSWORD = '$PASSWORD' ";
			if($_SESSION['TEMP_PK_USER_1'] != '')
				$cond = " AND Z_USER.PK_USER = '$_SESSION[TEMP_PK_USER_1]' ";

			$result = $db->Execute("SELECT Z_USER.* ,PK_PANELS,Z_ACCOUNT.SCHOOL_NAME,EMPLOYEE_LABEL,Z_ACCOUNT.ACTIVE AS ACTIVE_1, Z_ACCOUNT.PK_TIMEZONE, HAS_STUDENT_PORTAL, HAS_INSTRUCTOR_PORTAL, STUDENT_MFA, EMPLOYEE_MFA FROM Z_USER,Z_ACCOUNT where Z_USER.PK_ACCOUNT = Z_ACCOUNT.PK_ACCOUNT $cond ");
			//Ticket # 873

			$PASSWORD_CHANGED_ON = $result->fields['PASSWORD_CHANGED_ON'];
			$RESET_PASSWORD 	 = $result->fields['RESET_PASSWORD'];
			//Ticket # 873
			$FIRST_LOGIN 		 = $result->fields['FIRST_LOGIN'];
			
			//Ticket # 871
			$STUDENT_MFA 		 		= $result->fields['STUDENT_MFA'];
			$EMPLOYEE_MFA 		 		= $result->fields['EMPLOYEE_MFA'];
			$LAST_LOGGED_IN_IP	 		= $result->fields['LAST_LOGGED_IN_IP'];
			$_SESSION['TEMP_PK_USER_1'] = '';
			unset($_SESSION['TEMP_PK_USER_1']);
			
			$IS_FACULTY 		= 0;
			$NEED_SCHOOL_ACCESS = 0;
			if($result->fields['PK_USER_TYPE'] == 1 || $result->fields['PK_USER_TYPE'] == 2) {
				$ID = $result->fields['ID'];
				$res_emp = $db->Execute("SELECT IS_FACULTY,NEED_SCHOOL_ACCESS FROM S_EMPLOYEE_MASTER WHERE PK_EMPLOYEE_MASTER = '$ID' ");
				
				if($res_emp->fields['IS_FACULTY'] == 1) {
					$IS_FACULTY 		= 1;
					$NEED_SCHOOL_ACCESS = $res_emp->fields['NEED_SCHOOL_ACCESS'];
				}
			}

			$blocked_until = isUserBlocked($USER_ID);
			$current_timestamp = date("Y-m-d H:m:s");
			if ($current_timestamp <= $blocked_until) // DIAM-2193
			{
				$msg = "Account Locked. Please reset your password, try again in 30 minutes or contact your School Admin for further assistance.";
			} 
			else if($result->RecordCount() == 0)
			{
				// DIAM-2193
				if (!isset($_SESSION['LOGIN_ATTEMPTS'])) {
					$_SESSION['LOGIN_ATTEMPTS'] = 2;
				} else {
					$_SESSION['LOGIN_ATTEMPTS']--;
				}

				if ($_SESSION['LOGIN_ATTEMPTS'] <= 0) {
					blockUser($USER_ID);
					$msg = "Account Locked. Please reset your password, try again in 30 minutes or contact your School Admin for further assistance.";
					// Reset session login attempts to prevent further increment
					unset($_SESSION['LOGIN_ATTEMPTS']);
				} else {
					if($_SESSION['LOGIN_ATTEMPTS'] == 1)
					{
						$param_attempt = "Attempt";
					}
					else{
						$param_attempt = "Attempts";
					}
					$msg = "Invalid User ID/Password. ".$_SESSION['LOGIN_ATTEMPTS']." ".$param_attempt." Remaining.";
				}
				// End DIAM-2193

			} else if($result->fields['PK_USER_TYPE'] == 2 && $result->fields['HAS_INSTRUCTOR_PORTAL'] == 0 && $IS_FACULTY == 1 && $NEED_SCHOOL_ACCESS == 0) {
				$msg = 'You Dont Have Access To This Portal';
			} else if($result->fields['PK_USER_TYPE'] == 3 && $result->fields['HAS_STUDENT_PORTAL'] == 0) {
				$msg = 'You Dont Have Access To This Portal';
			} else {
				if($result->fields['ACTIVE'] == 0 || $result->fields['ACTIVE_1'] == 0){
					$msg = 'Your Account Has Been Blocked. Please Contact The Admin';
				} else {
					$_SESSION['SELECT_SITE'] = 0;
					if($result->fields['PK_USER_TYPE'] == 1) {
						$folder = "super_admin/";
						
						$_SESSION['ADMIN_PK_USER'] 	 	= $result->fields['PK_USER'];
						$_SESSION['ADMIN_PK_ACCOUNT']  	= $result->fields['PK_ACCOUNT'];
						$_SESSION['ADMIN_PK_ROLES'] 	= $result->fields['PK_ROLES'];
						$_SESSION['ADMIN_PK_TIMEZONE'] 	= $result->fields['PK_TIMEZONE'];
						if($LAST_LOGGED_IN_IP != get_ip_address()) {
							$_SESSION['TEMP_NEW_IP'] 		= get_ip_address();
							$_SESSION['TEMP_PK_USER_TYPE'] 	= $result->fields['PK_USER_TYPE'];
							$_SESSION['TEMP_PK_USER'] 	 	= $result->fields['PK_USER'];
							send_mfa($_SESSION['TEMP_PK_USER'], 'super_admin', 1);
							header("location:login-code");
							exit;
						}
					} else if($result->fields['PK_USER_TYPE'] == 2) {
						$folder = "school/";
						//Ticket # 871
						if($EMPLOYEE_MFA==0){
							$EMPLOYEE_MFA=1;
						}
						if($EMPLOYEE_MFA > 0 && $LAST_LOGGED_IN_IP != get_ip_address()) {
							$_SESSION['TEMP_NEW_IP'] 		= get_ip_address();
							$_SESSION['TEMP_PK_USER_TYPE'] 	= $result->fields['PK_USER_TYPE'];
							$_SESSION['TEMP_PK_USER'] 	 	= $result->fields['PK_USER'];
							send_mfa($_SESSION['TEMP_PK_USER'], 'employee', $EMPLOYEE_MFA);
							header("location:login-code");
							exit;
						}
						
					} else if($result->fields['PK_USER_TYPE'] == 3) {
						$folder = "student/";
						//Ticket # 871
						if($STUDENT_MFA > 0 && $LAST_LOGGED_IN_IP != get_ip_address()) {
							$_SESSION['TEMP_NEW_IP'] 		= get_ip_address();
							$_SESSION['TEMP_PK_USER_TYPE'] 	= $result->fields['PK_USER_TYPE'];
							$_SESSION['TEMP_PK_USER'] 	 	= $result->fields['PK_USER'];
							send_mfa($_SESSION['TEMP_PK_USER'], 'student', $STUDENT_MFA);
							header("location:login-code");
							exit;
						}
					}
					
					$_SESSION['FOLDER'] 	 	= $folder;
					$_SESSION['PK_USER_TYPE'] 	= $result->fields['PK_USER_TYPE'];
					$_SESSION['SCHOOL_NAME'] 	= $result->fields['SCHOOL_NAME'];
					$_SESSION['EMPLOYEE_LABEL'] = $result->fields['EMPLOYEE_LABEL'];
					$_SESSION['PK_USER'] 	 	= $result->fields['PK_USER'];
					$_SESSION['PK_ACCOUNT']  	= $result->fields['PK_ACCOUNT'];
					$_SESSION['PK_ROLES'] 		= $result->fields['PK_ROLES'];
					$_SESSION['PK_LANGUAGE'] 	= $result->fields['PK_LANGUAGE'];
					
					if($_SESSION['PK_ROLES'] == 1 || $_SESSION['PK_ROLES'] == 2 || $_SESSION['PK_ROLES'] == 3)
						$_SESSION['PK_TIMEZONE'] = $result->fields['PK_TIMEZONE'];
					
					$LOGIN_HISTORY['PK_ROLES']   = $_SESSION['PK_ROLES'];
					$LOGIN_HISTORY['PK_USER'] 	 = $_SESSION['PK_USER'];
					$LOGIN_HISTORY['IP_ADDRESS'] = get_ip_address();
					$LOGIN_HISTORY['LOGIN_TIME'] = date("Y-m-d H:i:s");
					db_perform('Z_LOGIN_HISTORY', $LOGIN_HISTORY, 'insert');
					$PK_LOGIN_HISTORY = $db->insert_ID();
					$_SESSION['PK_LOGIN_HISTORY'] = $PK_LOGIN_HISTORY;
					
					/* Ticket # 1241  */
					$salt1 = substr(strtr(base64_encode(openssl_random_pseudo_bytes(22)),'+','.'),0,22);
					$hash1 = crypt(time(), '$2y$12$' . $salt1);
					$_SESSION['LOGIN_SESSION_ID'] = $hash1;
					
					$USER22['LOGIN_SESSION_ID'] = $_SESSION['LOGIN_SESSION_ID'];
					$USER22['LAST_LOGGED_IN_IP'] = get_ip_address();
					db_perform('Z_USER', $USER22, 'update'," PK_USER = '$_SESSION[PK_USER]' ");
					/* Ticket # 1241  */
					
					$multi = 0;
					if($result->fields['PK_USER_TYPE'] == 1 || $result->fields['PK_USER_TYPE'] == 2) {
						$_SESSION['PK_EMPLOYEE_MASTER'] = $result->fields['ID'];
						$res_emp = $db->Execute("SELECT FIRST_NAME, LAST_NAME,IMAGE,TURN_OFF_ASSIGNMENTS,IS_FACULTY FROM S_EMPLOYEE_MASTER WHERE PK_EMPLOYEE_MASTER = '$_SESSION[PK_EMPLOYEE_MASTER]' ");
						$_SESSION['NAME'] 					= $res_emp->fields['FIRST_NAME'].' '.$res_emp->fields['LAST_NAME'];
						$_SESSION['PROFILE_IMAGE']  		= $res_emp->fields['IMAGE'];
						$_SESSION['TURN_OFF_ASSIGNMENTS']  	= $res_emp->fields['TURN_OFF_ASSIGNMENTS'];
						
					} else if($result->fields['PK_USER_TYPE'] == 3){
						$_SESSION['PK_STUDENT_MASTER'] = $result->fields['ID'];
						
						$res_stu = $db->Execute("SELECT FIRST_NAME, LAST_NAME,IMAGE FROM S_STUDENT_MASTER WHERE PK_STUDENT_MASTER='$_SESSION[PK_STUDENT_MASTER]' ");
						$_SESSION['NAME'] 			= $res_stu->fields['FIRST_NAME'].' '.$res_stu->fields['LAST_NAME'];
						$_SESSION['PROFILE_IMAGE']  = $res_stu->fields['IMAGE'];
						
						$res_stu = $db->Execute("SELECT PK_STUDENT_ENROLLMENT FROM S_STUDENT_ENROLLMENT WHERE PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' AND IS_ACTIVE_ENROLLMENT = 1 ");
						$PK_STUDENT_ENROLLMENT = $res_stu->fields['PK_STUDENT_ENROLLMENT'];
						///////////////
						$res_camp = $db->Execute("SELECT S_CAMPUS.PK_CAMPUS,OFFICIAL_CAMPUS_NAME,PK_TIMEZONE FROM S_STUDENT_CAMPUS,S_CAMPUS WHERE PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' AND S_STUDENT_CAMPUS.ACTIVE = 1 AND S_STUDENT_CAMPUS.PK_CAMPUS = S_CAMPUS.PK_CAMPUS ");
						if($res_camp->RecordCount() <= 1) {
							$_SESSION['PK_CAMPUS'] 		= $res_camp->fields['PK_CAMPUS'];
							$_SESSION['CAMPUS_NAME'] 	= $res_camp->fields['OFFICIAL_CAMPUS_NAME'];
							$_SESSION['PK_TIMEZONE'] 	= $res_camp->fields['PK_TIMEZONE'];
							$_SESSION['MULTI_CAMPUS'] 	= 0;
						} else {
							$multi = 1;
							
							$CAMPUS_NAME 	= '';
							$PK_CAMPUS 		= '';
							while (!$res_camp->EOF) {
								if($CAMPUS_NAME != '')
									$CAMPUS_NAME .= ', ';
									
								$CAMPUS_NAME .= $res_camp->fields['OFFICIAL_CAMPUS_NAME'];
								
								if($PK_CAMPUS != '')
									$PK_CAMPUS .= ',';
									
								$PK_CAMPUS .= $res_camp->fields['PK_CAMPUS'];
								
								$_SESSION['PK_TIMEZONE'] = $res_camp->fields['PK_TIMEZONE'];
								
								$res_camp->MoveNext();
							}
							
							$_SESSION['CAMPUS_NAME'] 	= $CAMPUS_NAME;
							$_SESSION['PK_CAMPUS'] 	 	= $PK_CAMPUS;
							$_SESSION['MULTI_CAMPUS'] 	= 1;
						}
						//////////////////
						
						//Ticket # 873
						if($FIRST_LOGIN == 1)
							header("location:change-password");
						else {
							if($RESET_PASSWORD == 1) {
								header("location:change-current-password");
								exit;
							} else {
								$now 		= strtotime(date('Y-m-d')); 
								$your_date 	= strtotime($PASSWORD_CHANGED_ON);
								$datediff 	= $now - $your_date;
								$days		= floor($datediff / (60 * 60 * 24));
								if($days > 90){
									$USER_1['RESET_PASSWORD'] = 1;
									db_perform('Z_USER', $USER_1, 'update'," PK_USER = '$_SESSION[PK_USER]' ");
									
									header("location:change-current-password");
									exit;
								}
							}
							header("location:".$_SESSION['FOLDER']."index");
							exit;
						}
						//Ticket # 873
					}
					
					$res_dep = $db->Execute("SELECT M_DEPARTMENT.PK_DEPARTMENT,PK_DEPARTMENT_MASTER FROM S_EMPLOYEE_DEPARTMENT, M_DEPARTMENT WHERE PK_EMPLOYEE_MASTER = '$_SESSION[PK_EMPLOYEE_MASTER]' AND S_EMPLOYEE_DEPARTMENT.ACTIVE = 1 AND M_DEPARTMENT.ACTIVE = 1 AND M_DEPARTMENT.PK_DEPARTMENT = S_EMPLOYEE_DEPARTMENT.PK_DEPARTMENT ");
					if($res_dep->RecordCount() <= 1) {
						$_SESSION['PK_DEPARTMENT'] 			= $res_dep->fields['PK_DEPARTMENT'];
						$_SESSION['PK_DEPARTMENT_MASTER'] 	= $res_dep->fields['PK_DEPARTMENT_MASTER'];
					} else {
						$PK_DEPARTMENT 		  = '';
						$PK_DEPARTMENT_MASTER = '';
						while (!$res_dep->EOF) {
							if($PK_DEPARTMENT != '')
								$PK_DEPARTMENT .= ',';
								
							$PK_DEPARTMENT .= $res_dep->fields['PK_DEPARTMENT'];
							
							if($PK_DEPARTMENT_MASTER != '')
								$PK_DEPARTMENT_MASTER .= ',';
								
							$PK_DEPARTMENT_MASTER .= $res_dep->fields['PK_DEPARTMENT_MASTER'];
							
							$res_dep->MoveNext();
						}
						
						$_SESSION['PK_DEPARTMENT'] 			= $PK_DEPARTMENT;
						$_SESSION['PK_DEPARTMENT_MASTER'] 	= $PK_DEPARTMENT_MASTER;
					}
					
					if($_SESSION['PK_ROLES'] == 2 || $_SESSION['PK_ROLES'] == 3 || $_SESSION['PK_ROLES'] == 4 || $_SESSION['PK_ROLES'] == 5) {
						$res_camp = $db->Execute("SELECT S_CAMPUS.PK_CAMPUS,OFFICIAL_CAMPUS_NAME,PK_TIMEZONE FROM S_EMPLOYEE_CAMPUS,S_CAMPUS WHERE PK_EMPLOYEE_MASTER = '$_SESSION[PK_EMPLOYEE_MASTER]' AND S_EMPLOYEE_CAMPUS.ACTIVE = 1 AND S_EMPLOYEE_CAMPUS.PK_CAMPUS = S_CAMPUS.PK_CAMPUS ");
						if($res_camp->RecordCount() <= 1) {
							$_SESSION['PK_CAMPUS'] 		= $res_camp->fields['PK_CAMPUS'];
							$_SESSION['CAMPUS_NAME'] 	= $res_camp->fields['OFFICIAL_CAMPUS_NAME'];

							if(!empty($res_camp->fields['PK_TIMEZONE']) && $res_camp->fields['PK_TIMEZONE']!=0) //DIAM-2048
								$_SESSION['PK_TIMEZONE'] 	= $res_camp->fields['PK_TIMEZONE'];

							$_SESSION['MULTI_CAMPUS'] 	= 0;
						} else {
							$multi = 1;
							
							$CAMPUS_NAME 	= '';
							$PK_CAMPUS 		= '';
							while (!$res_camp->EOF) {
								if($CAMPUS_NAME != '')
									$CAMPUS_NAME .= ', ';
									
								$CAMPUS_NAME .= $res_camp->fields['OFFICIAL_CAMPUS_NAME'];
								
								if($PK_CAMPUS != '')
									$PK_CAMPUS .= ',';
									
								$PK_CAMPUS .= $res_camp->fields['PK_CAMPUS'];
								
								if(!empty($res_camp->fields['PK_TIMEZONE']) && $res_camp->fields['PK_TIMEZONE']!=0) //DIAM-2048
									$_SESSION['PK_TIMEZONE'] = $res_camp->fields['PK_TIMEZONE'];
								
								$res_camp->MoveNext();
							}
							
							$_SESSION['CAMPUS_NAME'] 	= $CAMPUS_NAME;
							$_SESSION['PK_CAMPUS'] 	 	= $PK_CAMPUS;
							$_SESSION['MULTI_CAMPUS'] 	= 1;
						}
					}

					if($IS_FACULTY == 1 && $NEED_SCHOOL_ACCESS == 1) {
						if($result->fields['HAS_INSTRUCTOR_PORTAL'] == 0) {
							$_SESSION['FOLDER'] = 'school/';
						} else {
							$_SESSION['SELECT_SITE'] = 1;
						}
					} else if($res_emp->fields['IS_FACULTY'] == 1){
						$_SESSION['FOLDER'] = 'instructor/';
					}
					
					if($FIRST_LOGIN == 1) {
						header("location:change-password");
						exit;
					} else {
						if($RESET_PASSWORD == 1) {
							header("location:change-current-password");
							exit;
						} else {
							$now 		= strtotime(date('Y-m-d')); 
							$your_date 	= strtotime($PASSWORD_CHANGED_ON);
							$datediff 	= $now - $your_date;
							$days		= floor($datediff / (60 * 60 * 24));
							if($days > 90){
								$USER_1['RESET_PASSWORD'] = 1;
								db_perform('Z_USER', $USER_1, 'update'," PK_USER = '$_SESSION[PK_USER]' ");
								
								header("location:change-current-password");
								exit;
							}
							if($_GET['p'] != ''){
								$q = '';
								if($_GET['id'] != '')
									$q = "?id=".$_GET['id'];
								header("location:".$_SESSION['FOLDER'].$_GET['p'].$q);
							} else {
								if($_SESSION['SELECT_SITE'] == 1)
									header("location:select-site");
								else
									header("location:".$_SESSION['FOLDER']."index");
							}
							exit;
						}
					}
					
				}
			}
		} // End DIAM-2193
		
	} else if($_POST['form_name'] == 'reset') {
		//echo "<pre>";print_r($_POST);exit;
		
		$USER_ID = trim($_POST['USER_ID']);
		$result  = $db->Execute("SELECT PK_USER,PK_ROLES,PK_USER_TYPE,ACTIVE FROM Z_USER where USER_ID = '$USER_ID'");
		//print_r($result->fields);	exit();

		if($result->RecordCount() == 0){
			$msg = 'Invalid User ID';
		} else {
			$PK_USER  	  = $result->fields['PK_USER'];
			$PK_ROLES 	  = $result->fields['PK_ROLES'];
			$PK_USER_TYPE = $result->fields['PK_USER_TYPE'];
			
			if($result->fields['ACTIVE'] == 0)
				$msg = 'Your Account Has Been Blocked. Please Contact The Admin';
			else {
				if($PK_USER_TYPE == 1) {
					$res_usr_email = $db->Execute("SELECT CONCAT(FIRST_NAME,' ',LAST_NAME) AS NAME,USER_ID AS EMAIL FROM S_EMPLOYEE_MASTER , Z_USER WHERE Z_USER.PK_USER = '$PK_USER' AND Z_USER.ID = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER");
				} else if($PK_USER_TYPE == 2) {
					$res_usr_email = $db->Execute("SELECT CONCAT(FIRST_NAME,' ',LAST_NAME) AS NAME, EMAIL FROM S_EMPLOYEE_MASTER , Z_USER WHERE Z_USER.PK_USER = '$PK_USER' AND Z_USER.ID = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER");
				} else if($PK_USER_TYPE == 3) {
					$res_usr_email = $db->Execute("SELECT CONCAT(FIRST_NAME,' ',LAST_NAME) AS NAME, EMAIL FROM S_STUDENT_MASTER, S_STUDENT_CONTACT , Z_USER WHERE Z_USER.PK_USER = '$PK_USER' AND Z_USER.ID = S_STUDENT_MASTER.PK_STUDENT_MASTER AND S_STUDENT_CONTACT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' ");
				}
				
				if($res_usr_email->fields['EMAIL'] != '') {
					do {
						$CODE = generateRandomString(30);
						$result = $db->Execute("SELECT CODE FROM Z_RESET_PASSWORD where CODE = '$CODE'");
					} while ($result->RecordCount() > 0);
					//invalidate the old link
					$res = $db->Execute("SELECT * FROM Z_RESET_PASSWORD where PK_USER = '$PK_USER' AND ACTIVE='1'");
					if($res->RecordCount() > 0){
						$db->Execute("UPDATE Z_RESET_PASSWORD SET ACTIVE = '0' where PK_USER = '$PK_USER'");
					}
					$RESET_PASSWORD['PK_USER'] 	= $PK_USER;
					$RESET_PASSWORD['CODE'] 	= $CODE;
					$RESET_PASSWORD['ACTIVE'] 	= '1';
					$result_perform = db_perform('Z_RESET_PASSWORD', $RESET_PASSWORD, 'insert');
					
					require_once("global/mail.php");
					forgot_password_mail($db,$http_path,$PK_USER,$CODE,$res_usr_email->fields['NAME'],$res_usr_email->fields['EMAIL']);
					
					$EMAIL = mask_email($res_usr_email->fields['EMAIL']);
					// $msg = 'An email was just sent to <b>'.$EMAIL.'</b> with a link to reset your Password, '. $CODE . ' DB Result: ' . var_export($result_perform, true);
					$msg = 'An email was just sent to <b>'.$EMAIL.'</b> with a link to reset your Password, ';
				} else {
					$msg = 'Email ID not found. Please contact Admin';
				}
			}
		}
	}
} ?>
<!DOCTYPE html>
<html lang="en">

<head>
	<title>Login | <?=$title?></title>
	
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
   <? require_once("css.php"); ?>
</head>

<body class="horizontal-nav skin-default card-no-border">
    <? require_once("loader.php"); ?>
    <section id="wrapper">
		<? //require_once("menu.php"); ?>
        <div class="login-register" style="background-image:url(backend_assets/images/background/login-register.jpg);">
            <div class="login-box card">
                <div class="card-body">
                    <form class="form-horizontal form-material" method="post" id="loginform" name="loginform" action="">
                        <h3 class="text-center m-b-20">Sign In</h3>
						
						<? if($msg != ''){ ?>
						<div class="form-group ">
                            <div class="col-xs-12" style="color:red" >
								<?=$msg?>
							</div>
                        </div>
						<? } ?>
						
                        <div class="form-group ">
                            <div class="col-xs-12">
                                 <input class="form-control required-entry" id="USER_ID" name="USER_ID" type="text" placeholder="User ID">
							</div>
                        </div>
                        <div class="form-group">
                            <div class="col-xs-12">
                                <input class="form-control required-entry" id="PASSWORD" name="PASSWORD" type="password" placeholder="Password">
							</div>
                        </div>
                       
                        <div class="form-group text-center">
                            <div class="col-xs-12 p-b-20">
								<input type="hidden" name="form_name" value="login" >
                                <button class="btn btn-block btn-lg btn-info btn-rounded" type="submit">Log In</button>
                            </div>
                        </div>
						
						 <div class="form-group row">
                            <div class="col-md-12">
                                <div class="d-flex no-block align-items-center">
                                    <div class="custom-control custom-checkbox">
                                       
                                    </div> 
                                    <div class="ml-auto">
                                        <a href="javascript:void(0)" id="to-recover" class="text-muted"><i class="fas fa-lock m-r-5"></i> Forgot Password?</a> 
                                    </div>
                                </div>
                            </div>
                        </div>
						
                    </form>
                    <form class="form-horizontal" id="recoverform" method="post" action="" >
                        <div class="form-group ">
                            <div class="col-xs-12">
                                <h3>Recover Password</h3>
                                <p class="text-muted">Enter your User ID and instructions will be sent to you! </p>
                            </div>
                        </div>
                        <div class="form-group ">
                            <div class="col-xs-12">
                                <input class="form-control" type="text" required="" name="USER_ID" id="USER_ID" placeholder="User ID"> </div>
                        </div>
                        <div class="form-group text-center m-t-20">
                            <div class="col-xs-12">
								<input type="hidden" name="form_name" value="reset" >
                                <button class="btn btn-primary btn-lg btn-block text-uppercase waves-effect waves-light" type="submit">Reset</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
	<? require_once("footer.php"); ?>
	<? require_once("js.php"); ?>
    <script type="text/javascript">
		jQuery(document).ready(function($) {
			$('#to-recover').on("click", function() {
				$("#loginform").slideUp();
				$("#recoverform").fadeIn();
			});
		});
    </script>
    
	<script src="backend_assets/dist/js/validation_prototype.js"></script>
	<script src="backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
		var form1 = new Validation('loginform');
	</script>
</body>

</html>
