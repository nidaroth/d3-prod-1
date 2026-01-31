<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/student.php");
require_once("../language/student_contact.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || ($_SESSION['PK_ROLES'] != 2 && $_SESSION['PK_ROLES'] != 3)){ 
	header("location:../index");
	exit;
}

$timezone = $_SESSION['PK_TIMEZONE'];
if($timezone == '' || $timezone == 0) {
	$res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	$timezone = $res->fields['PK_TIMEZONE'];
	if($timezone == '' || $timezone == 0)
		$timezone = 4;
}

$res_tz = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");

$FIRST_NAME = trim($_REQUEST['fname']);
$LAST_NAME  = trim($_REQUEST['lname']);

$dup_cond 	= " (TRIM(FIRST_NAME) = '$FIRST_NAME' AND TRIM(LAST_NAME) = '$LAST_NAME') ";
$home_ph  	= $_REQUEST['home_ph'];
$mobile_ph  = $_REQUEST['mobile_ph'];
$NEW_EMAIL  = $_REQUEST['NEW_EMAIL'];
if($NEW_EMAIL == ''){
	$NEW_EMAIL  = $_REQUEST['email'];

}

if($LAST_NAME != '' && $home_ph != '') {
	$home_ph 	= preg_replace( '/[^0-9]/', '', trim($home_ph));
	$dup_cond  .= " OR ( (REPLACE(REPLACE(REPLACE(REPLACE(HOME_PHONE, '(', ''), ')', ''), '-', ''),' ','') = '$home_ph') ) ";
}

if($LAST_NAME != '' && $mobile_ph != '') {
	$mobile_ph 	 = preg_replace( '/[^0-9]/', '', trim($mobile_ph));
	$dup_cond 	.= " OR ( (REPLACE(REPLACE(REPLACE(REPLACE(CELL_PHONE, '(', ''), ')', ''), '-', ''),' ','') = '$mobile_ph' ) ) ";
}

if($LAST_NAME != '' && $NEW_EMAIL != '') {
	$dup_cond 	.= " OR ( EMAIL = '$NEW_EMAIL' ) ";
}

$dup_cond  = " AND ($dup_cond) ";

$res = $db->Execute("SELECT S_STUDENT_MASTER.PK_STUDENT_MASTER, LAST_NAME, FIRST_NAME,  HOME_PHONE, CELL_PHONE, EMAIL 
FROM 
S_STUDENT_MASTER LEFT JOIN S_STUDENT_CONTACT ON S_STUDENT_CONTACT.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND PK_STUDENT_CONTACT_TYPE_MASTER = 1 
WHERE S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ARCHIVED = 0 $dup_cond "); 

if($res->RecordCount() == 0)
	echo -1;
else {
	$str = "<table width='100%' class='table table-hover' >";
	$str .= "<thead>
				<tr>
					<th style='width:10%' >".LAST_NAME."</th>
					<th style='width:10%' >".FIRST_NAME."</th>
					
					<th style='width:8%' >".HOME_PHONE."</th>
					<th style='width:13%' >Mobile Phone</th>
					<th style='width:10%' >".EMAIL."</th>
					<th style='width:10%' >Entry Date/Time</th>
					<th style='width:10%' >".CAMPUS."</th>
					
					<th style='width:10%' >".PROGRAM."</th>
					<th style='width:10%' >".STATUS."</th>
					<th style='width:5%' >".VIEW."</th>
				</tr>
			</thead>
			<tbody>";
			while (!$res->EOF) {
				$PK_STUDENT_MASTER = $res->fields['PK_STUDENT_MASTER'];
				$res_enroll = $db->Execute("SELECT S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, M_CAMPUS_PROGRAM.CODE AS PROGRAM, IF(ENTRY_DATE = '0000-00-00','',DATE_FORMAT(ENTRY_DATE, '%m/%d/%Y' )) AS ENTRY_DATE , ENTRY_TIME, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1,CONCAT(M_STUDENT_STATUS.STUDENT_STATUS) AS STUDENT_STATUS, CAMPUS_CODE FROM S_STUDENT_ENROLLMENT LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS WHERE S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' ORDER BY S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT DESC"); 	
				
				$ENTRY_TIME = $res_enroll->fields['ENTRY_DATE'];
				if($ENTRY_TIME == '00:00:00' || $res_enroll->fields['ENTRY_DATE'] == '')
					$ENTRY_TIME = '';
				else {
					$ENTRY_TIME = convert_to_user_date(date("Y-m-d ").$ENTRY_TIME,'h:i A',$res_tz->fields['TIMEZONE'],date_default_timezone_get());
				}
				
				$str .= "<tr>
							<td>".$res->fields['LAST_NAME']."</td>
							<td>".$res->fields['FIRST_NAME']."</td>
							<td>".$res->fields['HOME_PHONE']."</td>
							<td>".$res->fields['CELL_PHONE']."</td>
							<td>".$res->fields['EMAIL']."</td>
							
							<td>".$res_enroll->fields['ENTRY_DATE']." ".$ENTRY_TIME."</td>
							<td>".$res_enroll->fields['CAMPUS_CODE']."</td>
							
							<td>".$res_enroll->fields['PROGRAM']."</td>
							<td>".$res_enroll->fields['STUDENT_STATUS']."</td>
							<td><a href='student?id=".$PK_STUDENT_MASTER."&eid=".$res_enroll->fields['PK_STUDENT_ENROLLMENT']."&t=1' target='_blank' >".VIEW."</a></td>
						</tr>";
				$res->MoveNext();
			}
	$str .= '</tbody>
		</table>';
		
	echo $str;
}