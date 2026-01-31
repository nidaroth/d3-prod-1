<?php require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/student.php");
require_once("../language/student_contact.php");

$res = $db->Execute("select CHECK_SSN from Z_ACCOUNT where PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
if($res->fields['CHECK_SSN'] == 0)
	echo -1;
else {
	$SSN  = $_POST['SSN'];
	$SSN1 = my_encrypt('',$SSN);
	
	$cond = "";
	if($_REQUEST['pk_stud'] != '')
		$cond = " AND S_STUDENT_MASTER.PK_STUDENT_MASTER != '$_REQUEST[pk_stud]' ";
	
	$res = $db->Execute("select S_STUDENT_MASTER.PK_STUDENT_MASTER, LAST_NAME, FIRST_NAME,  HOME_PHONE, CELL_PHONE, EMAIL, SSN 
	from 
	S_STUDENT_MASTER 
	LEFT JOIN S_STUDENT_CONTACT ON S_STUDENT_CONTACT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND PK_STUDENT_CONTACT_TYPE_MASTER = 1 
	where 
	S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND SSN = '$SSN1' $cond ");
	if ($res->RecordCount() == 0){
		echo -1;
	} else {
		$str = "<table width='100%' class='table table-hover' >";
		$str .= "<thead>
					<tr>
						<th style='width:10%' >".LAST_NAME."</th>
						<th style='width:10%' >".FIRST_NAME."</th>
						
						<th style='width:8%' >".HOME_PHONE."</th>
						<th style='width:8%' >Mobile Phone</th>
						<th style='width:10%' >".EMAIL."</th>
						<th style='width:10%' >".FIRST_TERM_DATE."</th>
						<th style='width:10%' >".CAMPUS."</th>
						
						<th style='width:10%' >".PROGRAM."</th>
						<th style='width:10%' >".STATUS."</th>
						<th style='width:10%' >".SSN."</th>
						<th style='width:4%' >".VIEW."</th>
					</tr>
				</thead>
				<tbody>";
				while (!$res->EOF) {
					$PK_STUDENT_MASTER = $res->fields['PK_STUDENT_MASTER'];
					$res_enroll = $db->Execute("SELECT S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, M_CAMPUS_PROGRAM.CODE AS PROGRAM, IF(ENTRY_DATE = '0000-00-00','',DATE_FORMAT(ENTRY_DATE, '%m/%d/%Y' )) AS ENTRY_DATE , ENTRY_TIME, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1,CONCAT(M_STUDENT_STATUS.STUDENT_STATUS) AS STUDENT_STATUS, CAMPUS_CODE FROM S_STUDENT_ENROLLMENT LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS WHERE S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' ORDER BY S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT DESC"); 	
				
					$str .= "<tr>
								<td>".$res->fields['LAST_NAME']."</td>
								<td>".$res->fields['FIRST_NAME']."</td>
								<td>".$res->fields['HOME_PHONE']."</td>
								<td>".$res->fields['CELL_PHONE']."</td>
								<td>".$res->fields['EMAIL']."</td>
								
								<td>".$res_enroll->fields['BEGIN_DATE_1']."</td>
								<td>".$res_enroll->fields['CAMPUS_CODE']."</td>
								
								<td>".$res_enroll->fields['PROGRAM']."</td>
								<td>".$res_enroll->fields['STUDENT_STATUS']."</td>
								<td>".my_decrypt('',$res->fields['SSN'])."</td>
								<td><a href='student?id=".$PK_STUDENT_MASTER."&eid=".$res_enroll->fields['PK_STUDENT_ENROLLMENT']."&t=".$_REQUEST['t']."' target='_blank' >".VIEW."</a></td>
							</tr>";
					$res->MoveNext();
				}
		$str .= '</tbody>
			</table>';
			
		echo $str;
	}
}