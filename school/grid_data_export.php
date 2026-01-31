<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_DATA_TOOLS') == 0 ){
	header("location:../index");
	exit;
}
$PK_ACCOUNT = $_SESSION['PK_ACCOUNT'];

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'EXPORT_NAME';  
$order = isset($_POST['order']) ? strval($_POST['order']) : 'ASC';
				
$SEARCH = isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : '';
$offset = ($page-1)*$rows;
	
$result = array();
// $where = " PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ";
$where = "WHERE M_DATA_EXPORT.ACTIVE =  1 ";
	
if($SEARCH != '')
	$where .= "AND    (EXPORT_NAME  like '%$SEARCH%'  OR LAST_EXPORTED_ON like '%$SEARCH%'  OR LAST_EXPORTED_BY like '%$SEARCH%')"; 



	$query = "SELECT
	M_DATA_EXPORT.*,
	CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME ,' ',S_EMPLOYEE_MASTER.LAST_NAME) AS LAST_EXPORTED_BY,
	ljt.LAST_EXPORTED_ON,
ljt.EXPORT_LINK,
ljt.PK_USER
	
  FROM
	M_DATA_EXPORT
  LEFT JOIN
	(
	SELECT
	  s.PK_DATA_EXPORT,
	  s.EXECUTED_ON AS LAST_EXPORTED_ON,

	  s.EXPORT_LINK,
	  t.PK_USER AS PK_USER
	  
	 
	FROM
	  M_DATA_EXPORT_LOG t
	JOIN
	  (
	  SELECT
		PK_DATA_EXPORT,
		MAX(EXECUTED_ON) AS EXECUTED_ON,
		EXPORT_LINK,
		PK_USER,
		PK_ACCOUNT
	  FROM
		M_DATA_EXPORT_LOG
	  WHERE
		PK_ACCOUNT = $PK_ACCOUNT
	  GROUP BY
		PK_DATA_EXPORT
	) s ON s.PK_DATA_EXPORT = t.PK_DATA_EXPORT AND s.EXECUTED_ON = t.EXECUTED_ON
	
  WHERE
  s.PK_ACCOUNT = $PK_ACCOUNT
  ) AS ljt ON ljt.PK_DATA_EXPORT = M_DATA_EXPORT.PK_DATA_EXPORT 
  LEFT JOIN 
	S_EMPLOYEE_MASTER
	ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = PK_USER 
  " . $where."  order by $sort $order";
 
$rs = mysql_query($query)or die(mysql_error()); 
$result["total"] = mysql_num_rows($rs); 	

$query .= " limit $offset,$rows ";


$rs = mysql_query($query)or die(mysql_error()); 
	
$items = array();
while($row = mysql_fetch_array($rs)){
	$PK_DATA_EXPORT = $row['PK_DATA_EXPORT'];
	$PK_DATA_EXPORT = $row['PK_DATA_EXPORT'];
	// echo $query_time = "SELECT M_DATA_EXPORT_LOG.* FROM M_DATA_EXPORT_LOG LEFT JOIN M_STORED_PROCEDURE_LOG ON M_STORED_PROCEDURE_LOG.PK_STORED_PROCEDURE_LOG =   M_DATA_EXPORT_LOG.PK_M_STORED_PROCEDURE_LOG WHERE PK_DATA_EXPORT = '$PK_DATA_EXPORT' AND PK_ACCOUNT = '$PK_ACCOUNT'  ORDER BY M_DATA_EXPORT_LOG.EXECUTED_ON DESC LIMIT 1"; 
	// $rs_time = mysql_query($query_time)or die(mysql_error()); 
	// $row_time = mysql_fetch_row($rs_time); 

	$row['SELECT']  = '<input type="checkbox" name="PK_DATA_EXPORT[]"  id="PK_DATA_EXPORT_ID'.$row['PK_DATA_EXPORT'].'" value="'.$row['PK_DATA_EXPORT'].'" >';
	array_push($items, $row); 
}
// echo $query;exit;
$result["rows"] = $items;
echo json_encode($result);