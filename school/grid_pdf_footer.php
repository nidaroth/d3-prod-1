<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");

if(check_access('SETUP_SCHOOL') == 0 ){
	header("location:../index");
	exit;
}

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'NAME';  
$order = isset($_POST['order']) ? strval($_POST['order']) : 'ASC';
				
$SEARCH = isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : '';
$offset = ($page-1)*$rows;
	
$result = array();

$where = " S_PDF_FOOTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ";

if($SEARCH != '')
	$where .= " AND (NAME like '%$SEARCH%' OR CAMPUS_CODE like '%$SEARCH%' )";

$rs = mysql_query("SELECT DISTINCT(S_PDF_FOOTER.PK_PDF_FOOTER) FROM S_PDF_FOOTER LEFT JOIN S_PDF_FOOTER_CAMPUS ON S_PDF_FOOTER_CAMPUS.PK_PDF_FOOTER = S_PDF_FOOTER.PK_PDF_FOOTER LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_PDF_FOOTER_CAMPUS.PK_CAMPUS WHERE " . $where. " GROUP BY S_PDF_FOOTER.PK_PDF_FOOTER ")or die(mysql_error());
$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);

//Ticket # 1183
$query = "SELECT S_PDF_FOOTER.PK_PDF_FOOTER,NAME,IF(PDF_FOR = 1, 'Student Schedule',IF(PDF_FOR = 2, 'Offer Letter',IF(PDF_FOR = 3, 'Report Card',IF(PDF_FOR = 4, 'Student Transcript',IF(PDF_FOR = 5, 'Student Transcript List',IF(PDF_FOR = 6, 'Unofficial Student Transcript',IF(PDF_FOR = 7, 'Payments Due',IF(PDF_FOR = 8, 'Student Invoice',IF(PDF_FOR = 9, 'Program Grade Book Progress Report Card', IF(PDF_FOR = 10, 'Student Transcript List - Numeric Grade', IF(PDF_FOR = 11, 'Student Transcript - FA Units', IF(PDF_FOR = 12, 'Attendance Daily Sign In Sheet', IF(PDF_FOR = 13, 'Ledger Worksheet', IF(PDF_FOR = 14, 'Balance Sheet', IF(PDF_FOR = 15, 'Student Schedule with Books', IF(PDF_FOR = 16, 'Student Transcript - Transcript Group',IF(PDF_FOR = 17, 'Attendance Course Offering 2 Week',IF(PDF_FOR = 18, 'Financial Aid Estimate',IF(PDF_FOR = 19, 'Program grade book Transcript',IF(PDF_FOR = 20, 'Student SAP',IF(PDF_FOR = 21, 'Course Offering Grade Book Transcript',IF(PDF_FOR = 22, 'Satisfactory Progress Report Card',''))) ))) )))))))))))))))) as PDF_FOR, IF(S_PDF_FOOTER.ACTIVE = 1,'<i class=\'fa fa-square round_green icon_size_active\' ></i>','<i class=\'fa fa-square round_red icon_size_active\' ></i>') AS ACTIVE, GROUP_CONCAT(CAMPUS_CODE SEPARATOR ', ') as CAMPUS FROM S_PDF_FOOTER LEFT JOIN S_PDF_FOOTER_CAMPUS ON S_PDF_FOOTER_CAMPUS.PK_PDF_FOOTER = S_PDF_FOOTER.PK_PDF_FOOTER LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_PDF_FOOTER_CAMPUS.PK_CAMPUS WHERE " . $where ." GROUP BY S_PDF_FOOTER.PK_PDF_FOOTER  order by $sort $order " ;
// echo $query;exit;	
$rs = mysql_query($query. " limit $offset,$rows")or die(mysql_error());	
	
$items = array();
while($row = mysql_fetch_array($rs)){
	
	$str  = '&nbsp;<a href="pdf_footer?id='.$row['PK_PDF_FOOTER'].'" title="'.EDIT.'" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>';
	
	//$str .= '&nbsp;<a href="javascript:void(0);" onclick="delete_row('.$row['PK_PDF_FOOTER'].')" title="'.DELETE.'" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i></a>';
	
	$row['ACTION'] = $row['ACTIVE'].$str;
	
	array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);