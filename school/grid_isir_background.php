<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_FINANCE') == 0 ){
	header("location:../index");
	exit;
}

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'PK_ISIR_BACKGROND_PROCESS';  
$order = isset($_POST['order']) ? strval($_POST['order']) : 'DESC';
				
$SEARCH = isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : '';
$offset = ($page-1)*$rows;
	
$result = array();

$STATUS 					= isset($_REQUEST['STATUS']) ? mysql_real_escape_string($_REQUEST['STATUS']) : '';

$where = " SI.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ";

if($_GET['id'] != '')
{
	$where .= " AND SI.PK_STUDENT_MASTER = '$_GET[id]' ";
}

if($STATUS != '') {
	$where .= " AND SI.STATUS = '$STATUS' ";
}

	
if($SEARCH != '')
	$where .= " AND (SI.FILE like '%$SEARCH%')";

$rs = mysql_query("SELECT 
						*
					FROM 
						S_ISIR_BACKGROUND_PROCESS  SI
					WHERE 
						" . $where. " 
					 ")or die(mysql_error());

$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);
	
$query = "  SELECT 
				SI.PK_ISIR_BACKGROND_PROCESS
				,SI.FILE
				,SI.EMAIL
				,SI.STATUS
				,SI.CREATED_ON
				,CONCAT(SE.LAST_NAME,', ',SE.FIRST_NAME) AS CREATED_BY_NAME
				,SI.EXECUTING_START_DATE
				,SI.EXECUTING_FINISH_DATE

			FROM 
				S_ISIR_BACKGROUND_PROCESS SI
			LEFT JOIN Z_USER ZU ON ZU.PK_USER = SI.CREATED_BY
			LEFT JOIN S_EMPLOYEE_MASTER SE ON SE.PK_EMPLOYEE_MASTER = ZU.ID
			WHERE 
				" . $where. " 
			
			ORDER BY 
				$sort $order " ;
   // echo $query;exit;	
$rs = mysql_query($query. " limit $offset,$rows")or die(mysql_error());	
	// time zone
		$timezone = $_SESSION['PK_TIMEZONE'];
		if($timezone == '' || $timezone == 0) {
			$res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$timezone = $res->fields['PK_TIMEZONE'];
			if($timezone == '' || $timezone == 0)
				$timezone = 4;
		}

		$res = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");
	// end
$items = array();
while($row = mysql_fetch_array($rs)){
	
	$status = $row['STATUS'];
	if($row['STATUS'] == 1){
		$row['STATUS'] = '<span class="badge rounded-pill bg-primary mb-0 px-3 ">NEW</span>';
	}elseif($row['STATUS'] == 2){
		$row['STATUS'] = '<span class="badge rounded-pill bg-warning text-dark mb-0 px-3 ">IN PROGRESS</span>';
	}elseif($row['STATUS'] == 3){
		$row['STATUS'] = '<span class="badge rounded-pill bg-success mb-0 px-3 ">DONE</span>';
	}


	if($status == 1){
		$str = '&nbsp;<a href="javascript:void(0)" onclick="delete_row('.$row['PK_ISIR_BACKGROND_PROCESS'].')" title="Delete" class="btn btn-primary btn-circle"><i class="far fa-trash-alt"></i></a>';
	}else{
		$str='';
	}

	$row['ACTION'] = $str;
	$row['FILE'] = basename($row['FILE']);;;

	// date
	$row['CREATED_ON'] = convert_to_user_date($row['CREATED_ON'],'l, M d, Y h:i A',$res->fields['TIMEZONE'],date_default_timezone_get());
	$row['EXECUTING_FINISH_DATE'] = convert_to_user_date($row['EXECUTING_FINISH_DATE'],'l, M d, Y h:i A',$res->fields['TIMEZONE'],date_default_timezone_get());

	array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);