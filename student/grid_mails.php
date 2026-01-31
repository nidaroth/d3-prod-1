<?php require_once('../global/config.php'); 
require_once("../language/common.php");
require_once("../language/mail.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' ){ 
	header("location:../index.php");
	exit;
}

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'CREATED_ON';  
$order = isset($_POST['order']) ? strval($_POST['order']) : 'DESC';
				
$SEARCH = isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : '';
$offset = ($page-1)*$rows;

$timezone = $_SESSION['PK_TIMEZONE'];
if($timezone == '' || $timezone == 0) {
	$res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	$timezone = $res->fields['PK_TIMEZONE'];
	if($timezone == '' || $timezone == 0)
		$timezone = 4;
}
$res_tz = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");

$result = array();
$where = " (Z_INTERNAL_EMAIL.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' OR Z_INTERNAL_EMAIL.PK_ACCOUNT = '1') AND 
Z_INTERNAL_EMAIL_RECEPTION.PK_INTERNAL_EMAIL = Z_INTERNAL_EMAIL.PK_INTERNAL_EMAIL AND 
Z_INTERNAL_EMAIL.CREATED_BY = Z_USER.PK_USER 
AND 
PK_INTERNAL_EMAIL_RECEPTION IN (SELECT MAX(PK_INTERNAL_EMAIL_RECEPTION) AS PK_INTERNAL_EMAIL_RECEPTION FROM  Z_INTERNAL_EMAIL_RECEPTION WHERE SELF_ADDED = 0 AND  PK_USER = '$_SESSION[PK_USER]' AND DELETED = 0 GROUP BY INTERNAL_ID) ";

$table = "";
if($_GET['type'] == 'starred') {
	$table  = ",Z_INTERNAL_EMAIL_STARRED";
	$where .= " AND STARRED = 1 AND Z_INTERNAL_EMAIL_STARRED.PK_USER = '$_SESSION[PK_USER]' AND Z_INTERNAL_EMAIL_STARRED.INTERNAL_ID = Z_INTERNAL_EMAIL.INTERNAL_ID ";
}

if($_GET['type'] == 'trash') {
	$where .= " AND Z_INTERNAL_EMAIL_RECEPTION.DELETED = 1 ";
} else 
	$where .= " AND Z_INTERNAL_EMAIL_RECEPTION.DELETED = 0 ";
	
if($SEARCH != '')
	$where .= " AND (SUBJECT  like '%$SEARCH%'  )";

$rs = mysql_query("SELECT Z_INTERNAL_EMAIL.PK_INTERNAL_EMAIL 
FROM 
Z_INTERNAL_EMAIL_RECEPTION ,Z_INTERNAL_EMAIL, Z_USER 
LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = Z_USER.ID AND PK_USER_TYPE IN (1,2) 
LEFT JOIN S_STUDENT_MASTER ON S_STUDENT_MASTER.PK_STUDENT_MASTER = Z_USER.ID AND PK_USER_TYPE = 3  $table WHERE " . $where. " ")or die(mysql_error());
$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);
	
$query = "SELECT Z_INTERNAL_EMAIL.PK_INTERNAL_EMAIL,PK_INTERNAL_EMAIL_RECEPTION,VIWED, Z_INTERNAL_EMAIL.INTERNAL_ID, SUBJECT, IF(PK_USER_TYPE = 2, CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME) , IF(PK_USER_TYPE = 3, CONCAT(S_STUDENT_MASTER.FIRST_NAME,' ',S_STUDENT_MASTER.LAST_NAME) , IF(PK_USER_TYPE = 1, CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME),'') )) AS NAME,Z_INTERNAL_EMAIL_RECEPTION.CREATED_ON ,Z_USER.PK_USER, Z_INTERNAL_EMAIL.CREATED_BY 
FROM 
Z_INTERNAL_EMAIL_RECEPTION ,Z_INTERNAL_EMAIL, Z_USER 
LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = Z_USER.ID AND PK_USER_TYPE IN (1,2) 
LEFT JOIN S_STUDENT_MASTER ON S_STUDENT_MASTER.PK_STUDENT_MASTER = Z_USER.ID AND PK_USER_TYPE = 3  $table WHERE " . $where ." order by $sort $order " ;
//echo $query;exit;	
$rs = mysql_query($query. " limit $offset,$rows")or die(mysql_error());	
	
$items = array();
$i = 0;
while($row = mysql_fetch_array($rs)){
	$row['CREATED_ON'] = convert_to_user_date($row['CREATED_ON'],'m/d/Y h:i A',$res_tz->fields['TIMEZONE'],date_default_timezone_get());
	
	$INTERNAL_ID = $row['INTERNAL_ID'];
	
	if($row['CREATED_BY'] == $_SESSION['PK_USER'])
		$row['NAME'] = 'Me';
	
	$res_att = mysql_query("SELECT PK_INTERNAL_EMAIL_RECEPTION FROM Z_INTERNAL_EMAIL_RECEPTION WHERE INTERNAL_ID = '$INTERNAL_ID' AND PK_USER = '$_SESSION[PK_USER]' ")or die(mysql_error());
	if(mysql_num_rows($res_att) > 1)
		$row['NAME'] .= ' ('.mysql_num_rows($res_att).')';
		
	if($row['VIWED'] == 0 && $_GET['type'] != 'sent') {
		$row['SUBJECT'] = "<b style='font-weight: bold;' >".$row['SUBJECT']."</b>";
		$row['NAME'] 	= "<b style='font-weight: bold;' >".$row['NAME']."</b>";
	}	
	$res_att = mysql_query("SELECT * FROM Z_INTERNAL_EMAIL_STARRED WHERE INTERNAL_ID = '".$INTERNAL_ID."' AND STARRED = 1 AND PK_USER = '$_SESSION[PK_USER]' ")or die(mysql_error());
	if(mysql_num_rows($res_att) > 0)
		$color = 'gold';
	else
		$color = '#DDDDDD';
	
	$row['SELECT']  = '<input type="checkbox" name="INTERNAL_ID_SELECT[]" id="INTERNAL_ID_'.$INTERNAL_ID.'" value="'.$INTERNAL_ID.'" >';
	$row['SELECT'] .= '&nbsp;&nbsp;<i id="star_id_'.$row['PK_INTERNAL_EMAIL_RECEPTION'].'" onclick="star('.$INTERNAL_ID.','.$row['PK_INTERNAL_EMAIL_RECEPTION'].')" class="fa fa-star" style="font-size:15px;color:'.$color.'"></i>';
	
	$res_att = mysql_query("SELECT * FROM Z_INTERNAL_EMAIL_ATTACHMENT WHERE PK_INTERNAL_EMAIL = '".$row['PK_INTERNAL_EMAIL']."' ")or die(mysql_error());
	if(mysql_num_rows($res_att) > 0)
		$row['ATTACHMENT'] = '<i class="icon-paper-clip" style="font-size:18px"></i>';
	else
		$row['ATTACHMENT'] = '';
	
	$str = '<a href="email.php?type='.$_GET['type'].'&id='.$INTERNAL_ID.'" title="'.EDIT.'" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>';
	if($_GET['type'] != 'trash') {
		//$str .= '<a href="javascript:void(0)" onclick="delete_row('.$INTERNAL_ID.',3)" title="'.DELETE.'" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i></a>';
	}

	$row['ACTION'] = $str;
	$USERS = '';
	array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);