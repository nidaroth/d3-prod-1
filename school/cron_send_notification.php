<? 
// $path = '../'; 
$path = '/var/www/html/D3/';
require_once($path."global/config.php"); 
require_once($path."global/mail.php"); 
require_once($path."global/texting.php"); 
require_once($path.'school/replace_student_tags.php'); //Ticket # 1429 

$date = date("m-d");
$res = $db->Execute("select Z_ACCOUNT.PK_ACCOUNT,LOGO, PK_EMAIL_TEMPLATE, PK_TEXT_TEMPLATE, SEND_NOTIFICATION_BEFORE_DAYS from Z_ACCOUNT, S_NOTIFICATION_SETTINGS WHERE PK_EVENT_TYPE = 5 AND Z_ACCOUNT.PK_ACCOUNT = S_NOTIFICATION_SETTINGS.PK_ACCOUNT");
while (!$res->EOF){
	$PK_ACCOUNT 					= $res->fields['PK_ACCOUNT'];
	$SEND_NOTIFICATION_BEFORE_DAYS 	= $res->fields['SEND_NOTIFICATION_BEFORE_DAYS'];
	
	$query = "SELECT PK_STUDENT_MASTER, CONCAT(FIRST_NAME,' ',LAST_NAME) AS NAME FROM S_STUDENT_MASTER WHERE DATE_FORMAT(DATE_SUB(DATE_OF_BIRTH, INTERVAL $SEND_NOTIFICATION_BEFORE_DAYS DAY),'%m-%d') = '$date' AND S_STUDENT_MASTER.PK_ACCOUNT = '$PK_ACCOUNT' ";
	
	if($res->fields['PK_EMAIL_TEMPLATE'] > 0) {
		
		$res_template = $db->Execute("SELECT SUBJECT,CONTENT,PK_EMAIL_ACCOUNT FROM S_EMAIL_TEMPLATE WHERE PK_EMAIL_TEMPLATE = '".$res->fields['PK_EMAIL_TEMPLATE']."' ");
		$SUBJECT 			= $res_template->fields['SUBJECT'];
		$CONTENT 			= $res_template->fields['CONTENT'];
		$PK_EMAIL_ACCOUNT 	= $res_template->fields['PK_EMAIL_ACCOUNT'];
		
		$LOGO = '';
		if($res->fields['LOGO'] != '') {
			$LOGO = str_ireplace("../",$http_path,$res->fields['LOGO']);
			$LOGO = '<img src="'.$LOGO.'" style="width:250px">';
		}
		
		$res_stud = $db->Execute($query) ;
		while (!$res_stud->EOF) {
			$PK_STUDENT_MASTER = $res_stud->fields['PK_STUDENT_MASTER'];

			$SUBJECT_1 = str_ireplace("{Student Name}",$res_stud->fields['NAME'],$SUBJECT);
			
			$CONTENT_1 = str_ireplace("{Student Name}",$res_stud->fields['NAME'],$CONTENT);
			$CONTENT_1 = str_ireplace("{Logo}",$LOGO,$CONTENT_1);
			
			/* Ticket # 1429  */
			$res_en = $db->Execute("SELECT PK_STUDENT_ENROLLMENT FROM S_STUDENT_ENROLLMENT WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND IS_ACTIVE_ENROLLMENT = 1 ");
			$CONTENT_1 = replace_mail_content($CONTENT_1, $res_en->fields['PK_STUDENT_ENROLLMENT'], $PK_ACCOUNT);
			/* Ticket # 1429  */
			
			$res_mail = $db->Execute("SELECT EMAIL FROM S_STUDENT_CONTACT WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_STUDENT_MASTER != '' AND PK_STUDENT_MASTER != 0 AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' AND PK_ACCOUNT = '$PK_ACCOUNT' ");	
			if($res_mail->RecordCount() > 0){	
			
			$receiver = array();
			$receiver['EMAIL'][0] = $res_mail->fields['EMAIL'];
			$receiver['NAME'][0]  = $res_stud->fields['NAME'];
			
			send_mail($PK_EMAIL_ACCOUNT,$receiver,'','','',$SUBJECT_1,$CONTENT_1,'');
			
			$mail_data['PK_ACCOUNT'] 			= $PK_ACCOUNT;
			$mail_data['PK_STUDENT_MASTER'] 	= $PK_STUDENT_MASTER;
			$mail_data['SUBJECT'] 				= $SUBJECT_1;
			$mail_data['MAIL_CONTENT'] 			= $CONTENT_1;
			$mail_data['EMAIL_ID'] 				= $receiver['EMAIL'][0];
			$mail_data['PK_EMAIL_ACCOUNT'] 		= $PK_EMAIL_ACCOUNT;
			mail_log($mail_data);
			}
			
			$res_stud->MoveNext();
		}
	}
	
	if($res->fields['PK_TEXT_TEMPLATE'] > 0) {
		$res_template = $db->Execute("SELECT CONTENT FROM S_TEXT_TEMPLATE WHERE PK_TEXT_TEMPLATE = '".$res->fields['PK_TEXT_TEMPLATE']."' ");
		$CONTENT = $res_template->fields['CONTENT'];
	
		$SEND_BIRTHDAY_NOTIFICATION_BEFORE_DAYS = $res->fields['SEND_BIRTHDAY_NOTIFICATION_BEFORE_DAYS'];
		$res_stud = $db->Execute($query) ;
		while (!$res_stud->EOF) {
			$PK_STUDENT_MASTER = $res_stud->fields['PK_STUDENT_MASTER'];

			$res_phone = $db->Execute("SELECT CELL_PHONE FROM S_STUDENT_CONTACT WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' AND CELL_PHONE != '' ");
			if($res_phone->RecordCount() > 0){
				$CONTENT_1 = str_ireplace("{Student Name}",$res_stud->fields['NAME'],$CONTENT);
				
				/* Ticket # 1429  */
				$res_en = $db->Execute("SELECT PK_STUDENT_ENROLLMENT FROM S_STUDENT_ENROLLMENT WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND IS_ACTIVE_ENROLLMENT = 1 ");
				$CONTENT_1 = replace_mail_content($CONTENT_1, $res_en->fields['PK_STUDENT_ENROLLMENT'], $PK_ACCOUNT);
				/* Ticket # 1429  */
			
				$text_sent = send_text($res_phone->fields['CELL_PHONE'],$PK_ACCOUNT,$CONTENT_1,$res->fields['PK_TEXT_TEMPLATE'],'');
				
				if($text_sent == 1) {
					$text_data['PK_ACCOUNT'] 			= $PK_ACCOUNT;
					$text_data['PK_STUDENT_MASTER'] 	= $PK_STUDENT_MASTER;
					$text_data['TEXT_CONTENT'] 			= $CONTENT_1;
					$text_data['TO_PHONE'] 				= $res_phone->fields['CELL_PHONE'];
					text_log($text_data);
				}
			}
			
			$res_stud->MoveNext();
		}
	}
	
	$res->MoveNext();
}

$date = date("Y-m-d");
$res = $db->Execute("select Z_ACCOUNT.PK_ACCOUNT,LOGO, PK_EMAIL_TEMPLATE, PK_TEXT_TEMPLATE, SEND_NOTIFICATION_BEFORE_DAYS from Z_ACCOUNT, S_NOTIFICATION_SETTINGS WHERE PK_EVENT_TYPE = 16 AND Z_ACCOUNT.PK_ACCOUNT = S_NOTIFICATION_SETTINGS.PK_ACCOUNT");
while (!$res->EOF){
	$PK_ACCOUNT 					= $res->fields['PK_ACCOUNT'];
	$SEND_NOTIFICATION_BEFORE_DAYS 	= $res->fields['SEND_NOTIFICATION_BEFORE_DAYS'];
	
	$query = "SELECT PK_COURSE_OFFERING_SCHEDULE,S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT,S_STUDENT_MASTER.PK_STUDENT_MASTER, CONCAT(FIRST_NAME,' ',LAST_NAME) AS NAME,COURSE_CODE, S_COURSE_OFFERING_SCHEDULE.START_DATE FROM S_COURSE, S_COURSE_OFFERING_SCHEDULE,S_STUDENT_COURSE, S_STUDENT_MASTER WHERE S_COURSE.PK_COURSE =  S_COURSE_OFFERING_SCHEDULE.PK_COURSE AND S_COURSE.PK_ACCOUNT = '$PK_ACCOUNT' AND S_STUDENT_COURSE.PK_COURSE_OFFERING = S_COURSE_OFFERING_SCHEDULE.PK_COURSE_OFFERING AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_COURSE.PK_STUDENT_MASTER AND DATE_FORMAT(DATE_SUB(S_COURSE_OFFERING_SCHEDULE.START_DATE, INTERVAL $SEND_NOTIFICATION_BEFORE_DAYS DAY),'%Y-%m-%d') = '$date' GROUP BY S_STUDENT_MASTER.PK_STUDENT_MASTER";
	
	if($res->fields['PK_EMAIL_TEMPLATE'] > 0) {
		
		$res_template = $db->Execute("SELECT SUBJECT,CONTENT,PK_EMAIL_ACCOUNT FROM S_EMAIL_TEMPLATE WHERE PK_EMAIL_TEMPLATE = '".$res->fields['PK_EMAIL_TEMPLATE']."'");
		$SUBJECT 			= $res_template->fields['SUBJECT'];
		$CONTENT 			= $res_template->fields['CONTENT'];
		$PK_EMAIL_ACCOUNT 	= $res_template->fields['PK_EMAIL_ACCOUNT'];
		
		$LOGO = '';
		if($res->fields['LOGO'] != '') {
			$LOGO = str_ireplace("../",$http_path,$res->fields['LOGO']);
			$LOGO = '<img src="'.$LOGO.'" style="width:250px">';
		}
		
		$res_stud = $db->Execute($query) ;
		while (!$res_stud->EOF) {
			$PK_STUDENT_MASTER = $res_stud->fields['PK_STUDENT_MASTER'];

			$SUBJECT_1 = str_ireplace("{Student Name}",$res_stud->fields['NAME'],$SUBJECT);
			$SUBJECT_1 = str_ireplace("{COURSE_NAME}",$res_stud->fields['COURSE_CODE'],$SUBJECT_1);
			$SUBJECT_1 = str_ireplace("{COURSE_START_DATE}",date("m/d/Y",strtotime($res_stud->fields['START_DATE'])),$SUBJECT_1);
			
			$CONTENT_1 = str_ireplace("{Student Name}",$res_stud->fields['NAME'],$CONTENT);
			$CONTENT_1 = str_ireplace("{Logo}",$LOGO,$CONTENT_1);
			$CONTENT_1 = str_ireplace("{COURSE_NAME}",$res_stud->fields['COURSE_CODE'],$CONTENT_1);
			$CONTENT_1 = str_ireplace("{COURSE_START_DATE}",date("m/d/Y",strtotime($res_stud->fields['START_DATE'])),$CONTENT_1);
			
			$CONTENT_1 = replace_mail_content($CONTENT_1, $res_stud->fields['PK_STUDENT_ENROLLMENT'], $PK_ACCOUNT); //Ticket # 1429
			
			$res_mail = $db->Execute("SELECT EMAIL FROM S_STUDENT_CONTACT WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_STUDENT_MASTER != '' AND PK_STUDENT_MASTER != 0 AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' AND PK_ACCOUNT = '$PK_ACCOUNT' ");	
			if($res_mail->RecordCount() > 0){	
			
			$receiver = array();
			$receiver['EMAIL'][0] = $res_mail->fields['EMAIL'];
			$receiver['NAME'][0]  = $res_stud->fields['NAME'];
			
			send_mail($PK_EMAIL_ACCOUNT,$receiver,'','','',$SUBJECT_1,$CONTENT_1,'');
			
			$mail_data['PK_ACCOUNT'] 			= $PK_ACCOUNT;
			$mail_data['PK_STUDENT_MASTER'] 	= $PK_STUDENT_MASTER;
			$mail_data['PK_STUDENT_ENROLLMENT'] = $res_stud->fields['PK_STUDENT_ENROLLMENT'];
			$mail_data['SUBJECT'] 				= $SUBJECT_1;
			$mail_data['MAIL_CONTENT'] 			= $CONTENT_1;
			$mail_data['EMAIL_ID'] 				= $receiver['EMAIL'][0];
			$mail_data['PK_EMAIL_ACCOUNT'] 		= $PK_EMAIL_ACCOUNT;
			mail_log($mail_data);
			}
			
			$res_stud->MoveNext();
		}
	}
	
	if($res->fields['PK_TEXT_TEMPLATE'] > 0) {
		$res_template = $db->Execute("SELECT CONTENT FROM S_TEXT_TEMPLATE WHERE PK_TEXT_TEMPLATE = '".$res->fields['PK_TEXT_TEMPLATE']."' ");
		$CONTENT = $res_template->fields['CONTENT'];
	
		$SEND_BIRTHDAY_NOTIFICATION_BEFORE_DAYS = $res->fields['SEND_BIRTHDAY_NOTIFICATION_BEFORE_DAYS'];
		$res_stud = $db->Execute($query) ;
		while (!$res_stud->EOF) {
			$PK_STUDENT_MASTER = $res_stud->fields['PK_STUDENT_MASTER'];

			$res_phone = $db->Execute("SELECT CELL_PHONE FROM S_STUDENT_CONTACT WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' AND CELL_PHONE != '' ");
			if($res_phone->RecordCount() > 0){
				$CONTENT_1 = str_ireplace("{Student Name}",$res_stud->fields['NAME'],$CONTENT);
				$CONTENT_1 = str_ireplace("{COURSE_NAME}",$res_stud->fields['COURSE_CODE'],$CONTENT_1);
				$CONTENT_1 = str_ireplace("{COURSE_START_DATE}",date("m/d/Y",strtotime($res_stud->fields['START_DATE'])),$CONTENT_1);
				
				$CONTENT_1 = replace_mail_content($CONTENT_1, $res_stud->fields['PK_STUDENT_ENROLLMENT'], $PK_ACCOUNT); //Ticket # 1429
			
				$text_sent = send_text($res_phone->fields['CELL_PHONE'],$PK_ACCOUNT,$CONTENT_1,$res->fields['PK_TEXT_TEMPLATE'],'');
				
				if($text_sent == 1) {
					$text_data['PK_ACCOUNT'] 			= $PK_ACCOUNT;
					$text_data['PK_STUDENT_MASTER'] 	= $PK_STUDENT_MASTER;
					$text_data['PK_STUDENT_ENROLLMENT'] = $res_stud->fields['PK_STUDENT_ENROLLMENT'];
					$text_data['TEXT_CONTENT'] 			= $CONTENT_1;
					$text_data['TO_PHONE'] 				= $res_phone->fields['CELL_PHONE'];
					text_log($text_data);
				}
			}
			
			$res_stud->MoveNext();
		}
	}
	
	$res->MoveNext();
}

$res_noti = $db->Execute("SELECT PK_EMAIL_TEMPLATE,PK_TEXT_TEMPLATE,PK_ACCOUNT,PK_AR_LEDGER_CODE FROM S_NOTIFICATION_SETTINGS WHERE PK_EVENT_TYPE = 11"); // DIAM-2144
while (!$res_noti->EOF) {
	$PK_ACCOUNT = $res_noti->fields['PK_ACCOUNT'];
	$PK_AR_LEDGER_CODE = $res_noti->fields['PK_AR_LEDGER_CODE']; // DIAM-2144
	$TODAY		= date("Y-m-d");
	$query = "select S_STUDENT_DISBURSEMENT.PK_STUDENT_ENROLLMENT, S_STUDENT_DISBURSEMENT.PK_STUDENT_MASTER,DISBURSEMENT_AMOUNT,IF(DISBURSEMENT_DATE = '0000-00-00','',DATE_FORMAT(DISBURSEMENT_DATE, '%m/%d/%Y' )) AS DISBURSEMENT_DATE1, CODE,LEDGER_DESCRIPTION, CONCAT(FIRST_NAME,' ',LAST_NAME) AS NAME from S_STUDENT_MASTER, S_STUDENT_DISBURSEMENT, M_AR_LEDGER_CODE WHERE S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE = M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE AND INVOICE = 1 AND PK_DISBURSEMENT_STATUS = 2 AND DISBURSEMENT_DATE <= '$TODAY' AND S_STUDENT_DISBURSEMENT.PK_ACCOUNT = '$PK_ACCOUNT' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_DISBURSEMENT.PK_STUDENT_MASTER AND M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE IN ($PK_AR_LEDGER_CODE) "; // DIAM-2144

	if($res_noti->fields['PK_EMAIL_TEMPLATE'] > 0) {
		$res_template = $db->Execute("SELECT SUBJECT,CONTENT,PK_EMAIL_ACCOUNT FROM S_EMAIL_TEMPLATE WHERE PK_EMAIL_TEMPLATE = '".$res_noti->fields['PK_EMAIL_TEMPLATE']."'");
		$SUBJECT 			= $res_template->fields['SUBJECT'];
		$CONTENT 			= $res_template->fields['CONTENT'];
		$PK_EMAIL_ACCOUNT 	= $res_template->fields['PK_EMAIL_ACCOUNT'];
		
		$LOGO = '';
		if($res->fields['LOGO'] != '') {
			$LOGO = str_ireplace("../",$http_path,$res->fields['LOGO']);
			$LOGO = '<img src="'.$LOGO.'" style="width:250px">';
		}
		
		$res_stud = $db->Execute($query);
		while (!$res_stud->EOF) {
			$PK_STUDENT_MASTER = $res_stud->fields['PK_STUDENT_MASTER'];

			$SUBJECT_1 = str_ireplace("{Student Name}",$res_stud->fields['NAME'],$SUBJECT);
			
			$CONTENT_1 = str_ireplace("{Student Name}",$res_stud->fields['NAME'],$CONTENT);
			$CONTENT_1 = str_ireplace("{Logo}",$LOGO,$CONTENT_1);
			$CONTENT_1 = str_ireplace("{LEDGER_CODE}",$res_stud->fields['CODE'],$CONTENT_1);
			$CONTENT_1 = str_ireplace("{DUE_DATE}",$res_stud->fields['DISBURSEMENT_DATE1'],$CONTENT_1);
			$CONTENT_1 = str_ireplace("{DUE_AMOUNT}",$res_stud->fields['DISBURSEMENT_AMOUNT'],$CONTENT_1);
			
			$CONTENT_1 = replace_mail_content($CONTENT_1, $res_stud->fields['PK_STUDENT_ENROLLMENT'], $PK_ACCOUNT); //Ticket # 1429
			
			$res_mail = $db->Execute("SELECT EMAIL FROM S_STUDENT_CONTACT WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_STUDENT_MASTER != '' AND PK_STUDENT_MASTER != 0 AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' AND PK_ACCOUNT = '$PK_ACCOUNT' ");	
			if($res_mail->RecordCount() > 0){	
			
			$receiver = array();
			$receiver['EMAIL'][0] = $res_mail->fields['EMAIL'];
			$receiver['NAME'][0]  = $res_stud->fields['NAME'];
			
			send_mail($PK_EMAIL_ACCOUNT,$receiver,'','','',$SUBJECT_1,$CONTENT_1,'');
			
			$mail_data['PK_ACCOUNT'] 			= $PK_ACCOUNT;
			$mail_data['PK_STUDENT_MASTER'] 	= $PK_STUDENT_MASTER;
			$mail_data['PK_STUDENT_ENROLLMENT'] = $res_stud->fields['PK_STUDENT_ENROLLMENT'];
			$mail_data['SUBJECT'] 				= $SUBJECT_1;
			$mail_data['MAIL_CONTENT'] 			= $CONTENT_1;
			$mail_data['EMAIL_ID'] 				= $receiver['EMAIL'][0];
			$mail_data['PK_EMAIL_ACCOUNT'] 		= $PK_EMAIL_ACCOUNT;
			mail_log($mail_data);
			}
			
			$res_stud->MoveNext();
		}
	}
	
	if($res_noti->fields['PK_TEXT_TEMPLATE'] > 0) {
		$res_template = $db->Execute("SELECT CONTENT FROM S_TEXT_TEMPLATE WHERE PK_TEXT_TEMPLATE = '".$res_noti->fields['PK_TEXT_TEMPLATE']."' ");
		$CONTENT = $res_template->fields['CONTENT'];
	
		$res_stud = $db->Execute($query) ;
		while (!$res_stud->EOF) {
			$PK_STUDENT_MASTER = $res_stud->fields['PK_STUDENT_MASTER'];

			$res_phone = $db->Execute("SELECT CELL_PHONE FROM S_STUDENT_CONTACT WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' AND CELL_PHONE != '' ");
			if($res_phone->RecordCount() > 0){
				$CONTENT_1 = str_ireplace("{Student Name}",$res_stud->fields['NAME'],$CONTENT);
				$CONTENT_1 = str_ireplace("{LEDGER_CODE}",$res_stud->fields['CODE'],$CONTENT_1);
				$CONTENT_1 = str_ireplace("{DUE_DATE}",$res_stud->fields['DISBURSEMENT_DATE1'],$CONTENT_1);
				$CONTENT_1 = str_ireplace("{DUE_AMOUNT}",$res_stud->fields['DISBURSEMENT_AMOUNT'],$CONTENT_1);
				
				$CONTENT_1 = replace_mail_content($CONTENT_1, $res_stud->fields['PK_STUDENT_ENROLLMENT'], $PK_ACCOUNT); //Ticket # 1429
				
				$text_sent = send_text($res_phone->fields['CELL_PHONE'],$PK_ACCOUNT,$CONTENT_1,$res_noti->fields['PK_TEXT_TEMPLATE'],'');
				
				if($text_sent == 1) {
					$text_data['PK_ACCOUNT'] 			= $PK_ACCOUNT;
					$text_data['PK_STUDENT_MASTER'] 	= $PK_STUDENT_MASTER;
					$text_data['PK_STUDENT_ENROLLMENT'] = $res_stud->fields['PK_STUDENT_ENROLLMENT'];
					$text_data['TEXT_CONTENT'] 			= $CONTENT_1;
					$text_data['TO_PHONE'] 				= $res_phone->fields['CELL_PHONE'];
					text_log($text_data);
				}
			}
			
			$res_stud->MoveNext();
		}
	}
	
	$res_noti->MoveNext();
}

$res_noti = $db->Execute("SELECT PK_EMAIL_TEMPLATE,PK_TEXT_TEMPLATE,PK_ACCOUNT,SEND_NOTIFICATION_BEFORE_DAYS,PK_AR_LEDGER_CODE FROM S_NOTIFICATION_SETTINGS WHERE PK_EVENT_TYPE = 13"); // DIAM-2144
while (!$res_noti->EOF) {
	$SEND_NOTIFICATION_BEFORE_DAYS 	= $res_noti->fields['SEND_NOTIFICATION_BEFORE_DAYS'];
	$PK_ACCOUNT	 					= $res_noti->fields['PK_ACCOUNT'];
	$PK_AR_LEDGER_CODE	 			= $res_noti->fields['PK_AR_LEDGER_CODE']; // DIAM-2144
	$TODAY							= date("Y-m-d");
	
	// $query = "select S_STUDENT_DISBURSEMENT.PK_STUDENT_ENROLLMENT, S_STUDENT_DISBURSEMENT.PK_STUDENT_MASTER,DISBURSEMENT_AMOUNT,IF(DISBURSEMENT_DATE = '0000-00-00','',DATE_FORMAT(DISBURSEMENT_DATE, '%m/%d/%Y' )) AS DISBURSEMENT_DATE1, CODE,LEDGER_DESCRIPTION, CONCAT(FIRST_NAME,' ',LAST_NAME) AS NAME from S_STUDENT_MASTER, S_STUDENT_DISBURSEMENT, M_AR_LEDGER_CODE WHERE S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE = M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE AND INVOICE = 1 AND PK_DISBURSEMENT_STATUS = 2 AND DATE_FORMAT(DATE_SUB(DISBURSEMENT_DATE, INTERVAL $SEND_NOTIFICATION_BEFORE_DAYS DAY),'%Y-%m-%d') = '$TODAY' AND S_STUDENT_DISBURSEMENT.PK_ACCOUNT = '$PK_ACCOUNT' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_DISBURSEMENT.PK_STUDENT_MASTER AND M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE IN ($PK_AR_LEDGER_CODE) "; // DIAM-2144
	// dvb 10 12 2024 https://crmplus.zoho.com/diamondstudentinfosystem/index.do/cxapp/agent/diamondsis/d3-programming/tickets/details/109523000073369005
	$query = "select S_STUDENT_DISBURSEMENT.PK_STUDENT_ENROLLMENT, S_STUDENT_DISBURSEMENT.PK_STUDENT_MASTER,DISBURSEMENT_AMOUNT,IF(DISBURSEMENT_DATE = '0000-00-00','',DATE_FORMAT(DISBURSEMENT_DATE, '%m/%d/%Y' )) AS DISBURSEMENT_DATE1, CODE,LEDGER_DESCRIPTION, CONCAT(FIRST_NAME,' ',LAST_NAME) AS NAME from S_STUDENT_MASTER, S_STUDENT_DISBURSEMENT, M_AR_LEDGER_CODE WHERE S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE = M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE AND PK_DISBURSEMENT_STATUS = 2 AND DATE_FORMAT(DATE_SUB(DISBURSEMENT_DATE, INTERVAL $SEND_NOTIFICATION_BEFORE_DAYS DAY),'%Y-%m-%d') = '$TODAY' AND S_STUDENT_DISBURSEMENT.PK_ACCOUNT = '$PK_ACCOUNT' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_DISBURSEMENT.PK_STUDENT_MASTER AND M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE IN ($PK_AR_LEDGER_CODE) "; // DIAM-2144

	if($res_noti->fields['PK_EMAIL_TEMPLATE'] > 0) {
		$res_template = $db->Execute("SELECT SUBJECT,CONTENT,PK_EMAIL_ACCOUNT FROM S_EMAIL_TEMPLATE WHERE PK_EMAIL_TEMPLATE = '".$res_noti->fields['PK_EMAIL_TEMPLATE']."'");
		$SUBJECT 			= $res_template->fields['SUBJECT'];
		$CONTENT 			= $res_template->fields['CONTENT'];
		$PK_EMAIL_ACCOUNT 	= $res_template->fields['PK_EMAIL_ACCOUNT'];
		
		$LOGO = '';
		if($res->fields['LOGO'] != '') {
			$LOGO = str_ireplace("../",$http_path,$res->fields['LOGO']);
			$LOGO = '<img src="'.$LOGO.'" style="width:250px">';
		}
		
		$res_stud = $db->Execute($query);
		while (!$res_stud->EOF) {
			$PK_STUDENT_MASTER = $res_stud->fields['PK_STUDENT_MASTER'];

			$SUBJECT_1 = str_ireplace("{Student Name}",$res_stud->fields['NAME'],$SUBJECT);
			
			$CONTENT_1 = str_ireplace("{Student Name}",$res_stud->fields['NAME'],$CONTENT);
			$CONTENT_1 = str_ireplace("{Logo}",$LOGO,$CONTENT_1);
			$CONTENT_1 = str_ireplace("{LEDGER_CODE}",$res_stud->fields['CODE'],$CONTENT_1);
			$CONTENT_1 = str_ireplace("{DUE_DATE}",$res_stud->fields['DISBURSEMENT_DATE1'],$CONTENT_1);
			$CONTENT_1 = str_ireplace("{DUE_AMOUNT}",$res_stud->fields['DISBURSEMENT_AMOUNT'],$CONTENT_1);
			
			$CONTENT_1 = replace_mail_content($CONTENT_1, $res_stud->fields['PK_STUDENT_ENROLLMENT'], $PK_ACCOUNT); //Ticket # 1429
			
			$res_mail = $db->Execute("SELECT EMAIL FROM S_STUDENT_CONTACT WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_STUDENT_MASTER != '' AND PK_STUDENT_MASTER != 0 AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' AND PK_ACCOUNT = '$PK_ACCOUNT' ");	
			if($res_mail->RecordCount() > 0){	
			
			$receiver = array();
			$receiver['EMAIL'][0] = $res_mail->fields['EMAIL'];
			$receiver['NAME'][0]  = $res_stud->fields['NAME'];
			
			send_mail($PK_EMAIL_ACCOUNT,$receiver,'','','',$SUBJECT_1,$CONTENT_1,'');
			
			$mail_data['PK_ACCOUNT'] 			= $PK_ACCOUNT;
			$mail_data['PK_STUDENT_MASTER'] 	= $PK_STUDENT_MASTER;
			$mail_data['PK_STUDENT_ENROLLMENT'] = $res_stud->fields['PK_STUDENT_ENROLLMENT'];
			$mail_data['SUBJECT'] 				= $SUBJECT_1;
			$mail_data['MAIL_CONTENT'] 			= $CONTENT_1;
			$mail_data['EMAIL_ID'] 				= $receiver['EMAIL'][0];
			$mail_data['PK_EMAIL_ACCOUNT'] 		= $PK_EMAIL_ACCOUNT;
			mail_log($mail_data);
			}
			
			$res_stud->MoveNext();
		}
	}
	
	if($res_noti->fields['PK_TEXT_TEMPLATE'] > 0) {
		$res_template = $db->Execute("SELECT CONTENT FROM S_TEXT_TEMPLATE WHERE PK_TEXT_TEMPLATE = '".$res_noti->fields['PK_TEXT_TEMPLATE']."' ");
		$CONTENT = $res_template->fields['CONTENT'];
	
		$res_stud = $db->Execute($query) ;
		while (!$res_stud->EOF) {
			$PK_STUDENT_MASTER = $res_stud->fields['PK_STUDENT_MASTER'];

			$res_phone = $db->Execute("SELECT CELL_PHONE FROM S_STUDENT_CONTACT WHERE PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' AND CELL_PHONE != '' ");
			if($res_phone->RecordCount() > 0){
				$CONTENT_1 = str_ireplace("{Student Name}",$res_stud->fields['NAME'],$CONTENT);
				$CONTENT_1 = str_ireplace("{LEDGER_CODE}",$res_stud->fields['CODE'],$CONTENT_1);
				$CONTENT_1 = str_ireplace("{DUE_DATE}",$res_stud->fields['DISBURSEMENT_DATE1'],$CONTENT_1);
				$CONTENT_1 = str_ireplace("{DUE_AMOUNT}",$res_stud->fields['DISBURSEMENT_AMOUNT'],$CONTENT_1);
				
				$CONTENT_1 = replace_mail_content($CONTENT_1, $res_stud->fields['PK_STUDENT_ENROLLMENT'], $PK_ACCOUNT); //Ticket # 1429
				
				$text_sent = send_text($res_phone->fields['CELL_PHONE'],$PK_ACCOUNT,$CONTENT_1,$res_noti->fields['PK_TEXT_TEMPLATE'],'');
				
				if($text_sent == 1) {
					$text_data['PK_ACCOUNT'] 			= $PK_ACCOUNT;
					$text_data['PK_STUDENT_MASTER'] 	= $PK_STUDENT_MASTER;
					$text_data['PK_STUDENT_ENROLLMENT'] = $res_stud->fields['PK_STUDENT_ENROLLMENT'];
					$text_data['TEXT_CONTENT'] 			= $CONTENT_1;
					$text_data['TO_PHONE'] 				= $res_phone->fields['CELL_PHONE'];
					text_log($text_data);
				}
			}
			
			$res_stud->MoveNext();
		}
	}
	
	$res_noti->MoveNext();
}

echo "done";
