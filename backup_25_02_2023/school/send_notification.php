<?php include('../global/config.php'); 
include('../global/mail.php');

function send_notification($data,$type){
	global $db,$http_path;
	$send_email = 0;
	if($type == 'TICKET CREATED'){
		$PK_TICKET = $data['PK_TICKET'];
		
		if($PK_TICKET > 0) {
			$res_ticket = $db->Execute("SELECT SUBJECT,TICKET_PRIORITY, INTERNAL_ID,TICKET_NO, CONTENT, CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME) AS NAME, S_EMPLOYEE_MASTER.EMAIL FROM Z_TICKET LEFT JOIN Z_USER ON Z_USER.PK_USER = Z_TICKET.CREATED_BY LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = Z_USER.ID LEFT JOIN Z_TICKET_STATUS ON Z_TICKET_STATUS.PK_TICKET_STATUS = Z_TICKET.PK_TICKET_STATUS LEFT JOIN Z_TICKET_PRIORITY on Z_TICKET.PK_TICKET_PRIORITY = Z_TICKET_PRIORITY.PK_TICKET_PRIORITY WHERE PK_TICKET = '$PK_TICKET' AND (Z_TICKET.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' OR Z_TICKET.PK_ACCOUNT = -1)");
			
			$INTERNAL_ID = $res_ticket->fields['INTERNAL_ID'];
			$TICKET_NO 	 = $res_ticket->fields['TICKET_NO'];
			$CONTENT	 = $res_ticket->fields['CONTENT'];
			
			/*$receiver['EMAIL'][] = $res_ticket->fields['EMAIL'];
			$receiver['NAME'][]  = $res_ticket->fields['NAME'];*/
			
			/*$receiver['EMAIL'][] = 'info@topcone.com';
			$receiver['NAME'][]  = 'Ramesh';*/
			
			$receiver['EMAIL'][] = 'j.queen@diamondsis.com';
			$receiver['NAME'][]  = '';
			
			$receiver['EMAIL'][] = 'Andrea@diamondsis.com';
			$receiver['NAME'][]  = '';
			
			$receiver['EMAIL'][] = 'Barre@diamondsis.com';
			$receiver['NAME'][]  = '';
			
			$receiver['EMAIL'][] = 'Debby@diamondsis.com';
			$receiver['NAME'][]  = '';
			
			$receiver['EMAIL'][] = 'mirian@diamondsis.com';
			$receiver['NAME'][]  = '';
			
			/*$receiver['EMAIL'][] = 'craig.linde@diamondsis.com';
			$receiver['NAME'][]  = '';
			
			$receiver['EMAIL'][] = 'Crystal@diamondsis.com';
			$receiver['NAME'][]  = '';
			
			$receiver['EMAIL'][] = 'kaleb@diamondsis.com';
			$receiver['NAME'][]  = '';*/

			/*$receiver['EMAIL'][] = 'balaji@codingdesk.in';
			$receiver['NAME'][]  = 'Balaji';*/
			
			/*$receiver['EMAIL'][] = 'ashish@topcone.com';
			$receiver['NAME'][]  = 'Ashish';*/
			
			$subject  = 'DSIS - '.'Ticket #'.$TICKET_NO.' : '.$res_ticket->fields['SUBJECT'].' ('.$res_ticket->fields['TICKET_PRIORITY'].')'.' by '.$res_ticket->fields['NAME'];
			$MESSAGE  = 'Hello,<br /><br />'.$res_ticket->fields['NAME'].' created Ticket #'.$TICKET_NO.'<br /><a href="'.$http_path.'super_admin/view_ticket?id='.$INTERNAL_ID.'">Click Here</a> to see the ticket<br /><br />';
			$MESSAGE  .= $CONTENT."<br /><br />";
			$MESSAGE  .= 'This Is a Automatically Generated Email';
		}
	} else if($type == 'COMMENTED ON TICKET'){
		
		$PK_TICKET = $data['PK_TICKET'];
		
		/*if($_SESSION['PK_ROLES'] == 1)
			$cond1 = "";
		else
			$cond1 = " AND Z_TICKET.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ";*/
			
		$cond1 = " AND (Z_TICKET.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' OR Z_TICKET.PK_ACCOUNT = -1)";
			
		$res_ticket = $db->Execute("SELECT SUBJECT,TICKET_PRIORITY, INTERNAL_ID,TICKET_NO, CONTENT, CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME) AS NAME, S_EMPLOYEE_MASTER.EMAIL FROM Z_TICKET LEFT JOIN Z_USER ON Z_USER.PK_USER = Z_TICKET.CREATED_BY LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = Z_USER.ID LEFT JOIN Z_TICKET_STATUS ON Z_TICKET_STATUS.PK_TICKET_STATUS = Z_TICKET.PK_TICKET_STATUS LEFT JOIN Z_TICKET_PRIORITY on Z_TICKET.PK_TICKET_PRIORITY = Z_TICKET_PRIORITY.PK_TICKET_PRIORITY WHERE PK_TICKET = '$PK_TICKET' $cond1 ");
		$INTERNAL_ID = $res_ticket->fields['INTERNAL_ID'];
		$TICKET_NO 	 = $res_ticket->fields['TICKET_NO'];
		$NAME		 = $res_ticket->fields['NAME'];
		
		if($res_ticket->fields['PK_ACCOUNT'] == -1){
			$res_ticket = $db->Execute("SELECT SUBJECT,TICKET_PRIORITY, INTERNAL_ID,TICKET_NO, CONTENT, CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME) AS NAME, S_EMPLOYEE_MASTER.EMAIL FROM Z_TICKET LEFT JOIN Z_USER ON Z_USER.PK_USER = Z_TICKET.CREATED_BY LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = Z_USER.ID LEFT JOIN Z_TICKET_STATUS ON Z_TICKET_STATUS.PK_TICKET_STATUS = Z_TICKET.PK_TICKET_STATUS LEFT JOIN Z_TICKET_PRIORITY on Z_TICKET.PK_TICKET_PRIORITY = Z_TICKET_PRIORITY.PK_TICKET_PRIORITY WHERE INTERNAL_ID = '$INTERNAL_ID' AND Z_TICKET.PK_ACCOUNT != -1 ORDER BY Z_TICKET.PK_TICKET DESC");
		}
		
		/*$receiver['EMAIL'][] = $res_ticket->fields['EMAIL'];
		$receiver['NAME'][]  = $res_ticket->fields['NAME'];*/
		
		/*$receiver['EMAIL'][] = 'info@topcone.com';
		$receiver['NAME'][]  = 'Ramesh';*/
		
		$receiver['EMAIL'][] = 'j.queen@diamondsis.com';
		$receiver['NAME'][]  = '';
		
		$receiver['EMAIL'][] = 'Andrea@diamondsis.com';
		$receiver['NAME'][]  = '';
		
		$receiver['EMAIL'][] = 'Barre@diamondsis.com';
		$receiver['NAME'][]  = '';
		
		$receiver['EMAIL'][] = 'Debby@diamondsis.com';
		$receiver['NAME'][]  = '';
		
		$receiver['EMAIL'][] = 'mirian@diamondsis.com';
		$receiver['NAME'][]  = '';
		
		/*$receiver['EMAIL'][] = 'craig.linde@diamondsis.com';
		$receiver['NAME'][]  = '';
		
		$receiver['EMAIL'][] = 'Crystal@diamondsis.com';
		$receiver['NAME'][]  = '';
		
		$receiver['EMAIL'][] = 'kaleb@diamondsis.com';
		$receiver['NAME'][]  = '';*/

		/*$receiver['EMAIL'][] = 'balaji@codingdesk.in';
		$receiver['NAME'][]  = 'Balaji';*/
		
		/*$receiver['EMAIL'][] = 'ashish@topcone.com';
		$receiver['NAME'][]  = 'Ashish';*/
		
		//$res_ticket_1 = $db->Execute("SELECT CONTENT FROM Z_TICKET WHERE PK_TICKET = '$PK_TICKET' ");
		//$CONTENT   	  = $res_ticket_1->fields['CONTENT'];
		$CONTENT = '';
		
		$res_ticket_1 = $db->Execute("SELECT CONTENT,CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME) AS NAME, Z_TICKET.CREATED_ON FROM Z_TICKET LEFT JOIN Z_USER ON Z_USER.PK_USER = Z_TICKET.CREATED_BY LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = Z_USER.ID WHERE TICKET_NO = '$TICKET_NO' ORDER BY PK_TICKET DESC ");
		while (!$res_ticket_1->EOF) {
			
			$CONTENT  .= "<hr /><b><center>".$res_ticket_1->fields['NAME'].' on '.date("m/d/Y",strtotime($res_ticket_1->fields['CREATED_ON'])).'</b></center><br /><br />';
			$CONTENT  .= $res_ticket_1->fields['CONTENT']."<br /><br />";
			
			$res_ticket_1->MoveNext();
		}
		
		$subject  = 'DSIS - '.$NAME.' Commented on Ticket #'.$TICKET_NO.' : '.$res_ticket->fields['SUBJECT'].' ('.$res_ticket->fields['TICKET_PRIORITY'].')';
		$MESSAGE  = 'Hello,<br /><br />'.$NAME.' Commented on Ticket #'.$TICKET_NO.'<br /><a href="'.$http_path.'super_admin/view_ticket?id='.$INTERNAL_ID.'">Click Here</a> to see the comments<br /><br />';
		$MESSAGE  .= $CONTENT."<br /><br />";
		$MESSAGE  .= 'This Is a Automatically Generated Email';
				
	} else if($type == 'TICKET STATUS CHANGE'){
		
		$PK_TICKET = $data['PK_TICKET'];
		
		/*if($_SESSION['PK_ROLES'] == 1)
			$cond1 = "";
		else
			$cond1 = " AND Z_TICKET.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ";*/
			
		$cond1 = " AND (Z_TICKET.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' OR Z_TICKET.PK_ACCOUNT = -1)";	
		$cond1 = "";
		
		$res_ticket = $db->Execute("SELECT SUBJECT,TICKET_PRIORITY, INTERNAL_ID,TICKET_NO, CONTENT, CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME) AS NAME, S_EMPLOYEE_MASTER.EMAIL, TICKET_STATUS FROM Z_TICKET LEFT JOIN Z_USER ON Z_USER.PK_USER = Z_TICKET.CREATED_BY LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = Z_USER.ID LEFT JOIN Z_TICKET_STATUS ON Z_TICKET_STATUS.PK_TICKET_STATUS = Z_TICKET.PK_TICKET_STATUS LEFT JOIN Z_TICKET_PRIORITY on Z_TICKET.PK_TICKET_PRIORITY = Z_TICKET_PRIORITY.PK_TICKET_PRIORITY WHERE PK_TICKET = '$PK_TICKET' $cond1 ");
		$INTERNAL_ID = $res_ticket->fields['INTERNAL_ID'];
		$TICKET_NO 	 = $res_ticket->fields['TICKET_NO'];
		$NAME		 = $res_ticket->fields['NAME'];
		
		if($res_ticket->fields['PK_ACCOUNT'] == -1){
			$res_ticket = $db->Execute("SELECT SUBJECT,TICKET_PRIORITY, INTERNAL_ID,TICKET_NO, CONTENT, CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME) AS NAME, S_EMPLOYEE_MASTER.EMAIL, TICKET_STATUS FROM Z_TICKET LEFT JOIN Z_USER ON Z_USER.PK_USER = Z_TICKET.CREATED_BY LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = Z_USER.ID LEFT JOIN Z_TICKET_STATUS ON Z_TICKET_STATUS.PK_TICKET_STATUS = Z_TICKET.PK_TICKET_STATUS LEFT JOIN Z_TICKET_PRIORITY on Z_TICKET.PK_TICKET_PRIORITY = Z_TICKET_PRIORITY.PK_TICKET_PRIORITY WHERE INTERNAL_ID = '$INTERNAL_ID' AND Z_TICKET.PK_ACCOUNT != -1 ORDER BY Z_TICKET.PK_TICKET DESC");
		}
		
		/*$receiver['EMAIL'][] = $res_ticket->fields['EMAIL'];
		$receiver['NAME'][]  = $res_ticket->fields['NAME'];*/
		
		/*$receiver['EMAIL'][] = 'info@topcone.com';
		$receiver['NAME'][]  = 'Ramesh';*/
		
		$receiver['EMAIL'][] = 'j.queen@diamondsis.com';
		$receiver['NAME'][]  = '';
		
		$receiver['EMAIL'][] = 'Andrea@diamondsis.com';
		$receiver['NAME'][]  = '';
		
		$receiver['EMAIL'][] = 'Barre@diamondsis.com';
		$receiver['NAME'][]  = '';
		
		$receiver['EMAIL'][] = 'Debby@diamondsis.com';
		$receiver['NAME'][]  = '';
		
		$receiver['EMAIL'][] = 'mirian@diamondsis.com';
		$receiver['NAME'][]  = '';
		
		/*$receiver['EMAIL'][] = 'craig.linde@diamondsis.com';
		$receiver['NAME'][]  = '';
		
		$receiver['EMAIL'][] = 'Crystal@diamondsis.com';
		$receiver['NAME'][]  = '';
		
		$receiver['EMAIL'][] = 'kaleb@diamondsis.com';
		$receiver['NAME'][]  = '';*/
		
		/*$receiver['EMAIL'][] = 'balaji@codingdesk.in';
		$receiver['NAME'][]  = 'Balaji';*/
		
		/*$receiver['EMAIL'][] = 'ashish@topcone.com';
		$receiver['NAME'][]  = 'Ashish';*/
		
		$res_name_1 = $db->Execute("SELECT CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME) AS NAME FROM S_EMPLOYEE_MASTER WHERE PK_EMPLOYEE_MASTER = '$_SESSION[PK_EMPLOYEE_MASTER]'");
		
		$subject  = 'DSIS - '.$res_name_1->fields['NAME'].' Changed Ticket #'.$TICKET_NO.' : '.$res_ticket->fields['SUBJECT'].' ('.$res_ticket->fields['TICKET_PRIORITY'].') Status as '.$res_ticket->fields['TICKET_STATUS'];
		$MESSAGE  = 'Hello,<br /><br />'.$res_name_1->fields['NAME'].' Changed Ticket #'.$TICKET_NO.' Status As '.$res_ticket->fields['TICKET_STATUS'].'<br /><a href="'.$http_path.'super_admin/view_ticket?id='.$INTERNAL_ID.'">Click Here</a> to see the comments<br /><br />This Is a Automatically Generated Email';
		
	} else if($type == 'TICKET STATUS CHANGED'){
		
		$PK_TICKET = $data['PK_TICKET'];
		
		/*if($_SESSION['PK_ROLES'] == 1)
			$cond1 = "";
		else
			$cond1 = " AND Z_TICKET.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ";*/
			
		$cond1 = " AND (Z_TICKET.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' OR Z_TICKET.PK_ACCOUNT = -1)";	
		$cond1 = "";
		
		$res_ticket = $db->Execute("SELECT SUBJECT,TICKET_PRIORITY, INTERNAL_ID,TICKET_NO, CONTENT, CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME) AS NAME, S_EMPLOYEE_MASTER.EMAIL, TICKET_STATUS FROM Z_TICKET LEFT JOIN Z_USER ON Z_USER.PK_USER = Z_TICKET.CREATED_BY LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = Z_USER.ID LEFT JOIN Z_TICKET_STATUS ON Z_TICKET_STATUS.PK_TICKET_STATUS = Z_TICKET.PK_TICKET_STATUS LEFT JOIN Z_TICKET_PRIORITY on Z_TICKET.PK_TICKET_PRIORITY = Z_TICKET_PRIORITY.PK_TICKET_PRIORITY WHERE PK_TICKET = '$PK_TICKET' $cond1 ");
		$INTERNAL_ID = $res_ticket->fields['INTERNAL_ID'];
		$TICKET_NO 	 = $res_ticket->fields['TICKET_NO'];
		$NAME		 = $res_ticket->fields['NAME'];
		
		if($res_ticket->fields['PK_ACCOUNT'] == -1){
			$res_ticket = $db->Execute("SELECT SUBJECT,TICKET_PRIORITY, INTERNAL_ID,TICKET_NO, CONTENT, CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME) AS NAME, S_EMPLOYEE_MASTER.EMAIL, TICKET_STATUS FROM Z_TICKET LEFT JOIN Z_USER ON Z_USER.PK_USER = Z_TICKET.CREATED_BY LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = Z_USER.ID LEFT JOIN Z_TICKET_STATUS ON Z_TICKET_STATUS.PK_TICKET_STATUS = Z_TICKET.PK_TICKET_STATUS LEFT JOIN Z_TICKET_PRIORITY on Z_TICKET.PK_TICKET_PRIORITY = Z_TICKET_PRIORITY.PK_TICKET_PRIORITY WHERE INTERNAL_ID = '$INTERNAL_ID' AND Z_TICKET.PK_ACCOUNT != -1 ORDER BY Z_TICKET.PK_TICKET DESC");
		}
		
		/*$receiver['EMAIL'][] = $res_ticket->fields['EMAIL'];
		$receiver['NAME'][]  = $res_ticket->fields['NAME'];*/
		
		/*$receiver['EMAIL'][] = 'info@topcone.com';
		$receiver['NAME'][]  = 'Ramesh';*/
		
		$receiver['EMAIL'][] = 'j.queen@diamondsis.com';
		$receiver['NAME'][]  = '';
		
		$receiver['EMAIL'][] = 'Andrea@diamondsis.com';
		$receiver['NAME'][]  = '';
		
		$receiver['EMAIL'][] = 'Barre@diamondsis.com';
		$receiver['NAME'][]  = '';
		
		$receiver['EMAIL'][] = 'Debby@diamondsis.com';
		$receiver['NAME'][]  = '';
		
		$receiver['EMAIL'][] = 'mirian@diamondsis.com';
		$receiver['NAME'][]  = '';
		
		/*$receiver['EMAIL'][] = 'craig.linde@diamondsis.com';
		$receiver['NAME'][]  = '';
		
		$receiver['EMAIL'][] = 'Crystal@diamondsis.com';
		$receiver['NAME'][]  = '';
		
		$receiver['EMAIL'][] = 'kaleb@diamondsis.com';
		$receiver['NAME'][]  = '';*/
		
		/*$receiver['EMAIL'][] = 'balaji@codingdesk.in';
		$receiver['NAME'][]  = 'Balaji';*/
		
		/*$receiver['EMAIL'][] = 'ashish@topcone.com';
		$receiver['NAME'][]  = 'Ashish';*/
		
		$res_name_1 = $db->Execute("SELECT CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME) AS NAME FROM S_EMPLOYEE_MASTER WHERE PK_EMPLOYEE_MASTER = '$_SESSION[PK_EMPLOYEE_MASTER]'");
		
		$subject  = 'DSIS - '.$res_name_1->fields['NAME'].' Changed Ticket #'.$TICKET_NO.' : '.$res_ticket->fields['SUBJECT'].' Priority as '.$res_ticket->fields['TICKET_PRIORITY'];
		$MESSAGE  = 'Hello,<br /><br />'.$res_name_1->fields['NAME'].' Changed Ticket #'.$TICKET_NO.' Priority As '.$res_ticket->fields['TICKET_PRIORITY'].'<br /><a href="'.$http_path.'super_admin/view_ticket?id='.$INTERNAL_ID.'">Click Here</a> to see the comments<br /><br />This Is a Automatically Generated Email';
		
	}
	$res = $db->Execute("SELECT PK_EMAIL_ACCOUNT FROM Z_EMAIL_ACCOUNT WHERE PK_ACCOUNT = '1'");
	send_mail($res->fields['PK_EMAIL_ACCOUNT'],$receiver,'','','',$subject,$MESSAGE,$Attachments);
}

?>
	