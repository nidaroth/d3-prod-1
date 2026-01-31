<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");

if(check_access('SETUP_COMMUNICATION') == 0 ){
	header("location:../index");
	exit;
}

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'TEMPLATE_NAME';  
$order = isset($_POST['order']) ? strval($_POST['order']) : 'ASC';
				
$SEARCH = isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : '';
$offset = ($page-1)*$rows;
	
$result = array();
$where = " S_PDF_TEMPLATE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ";
	
if($SEARCH != '')
	$where .= " AND (TEMPLATE_NAME  like '%$SEARCH%' )";

$rs = mysql_query("SELECT DISTINCT(PK_PDF_TEMPLATE) FROM S_PDF_TEMPLATE WHERE " . $where. " ")or die(mysql_error());
$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);
	
$query = "SELECT PK_PDF_TEMPLATE,TEMPLATE_NAME,IF(S_PDF_TEMPLATE.ACTIVE = 1,'<i class=\'fa fa-square round_green icon_size_active\' ></i>','<i class=\'fa fa-square round_red icon_size_active\' ></i>') AS ACTIVE FROM S_PDF_TEMPLATE WHERE " . $where ." order by $sort $order " ;
// echo $query;exit;	
$rs = mysql_query($query. " limit $offset,$rows")or die(mysql_error());	
	
$items = array();
while($row = mysql_fetch_array($rs)){

	$str  = '&nbsp;<a href="pdf_template?id='.$row['PK_PDF_TEMPLATE'].'" title="'.EDIT.'" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>';
	//$str .= '&nbsp;<a href="javascript:void(0);" onclick="delete_row('.$row['PK_PDF_TEMPLATE'].')" title="'.DELETE.'" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i></a>';
	$str .= '&nbsp;<a href="pdf_template_pdf?id='.$row['PK_PDF_TEMPLATE'].'" target="_blank" title="'.PDF.'" class="btn pdf-color btn-circle"><i class="fas fa-file-pdf"></i></a>';
	
	$CAMPUS = '';
	$rs_camp = mysql_query("SELECT CAMPUS_CODE FROM S_CAMPUS, S_PDF_TEMPLATE_CAMPUS WHERE S_PDF_TEMPLATE_CAMPUS.PK_CAMPUS = S_CAMPUS.PK_CAMPUS AND PK_PDF_TEMPLATE = '".$row['PK_PDF_TEMPLATE']."' ORDER BY CAMPUS_CODE ASC ")or die(mysql_error());
	while($row_camp = mysql_fetch_array($rs_camp)){
		if($CAMPUS != '')
			$CAMPUS .= ', ';
			
		$CAMPUS .= $row_camp['CAMPUS_CODE'];
	}
	$row['CAMPUS'] = $CAMPUS;
	$row['ACTION'] = $row['ACTIVE'].$str;
	
	array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);