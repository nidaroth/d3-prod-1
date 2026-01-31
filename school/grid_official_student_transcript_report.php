<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/bulk_text.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' ){ 
	header("location:../index");
	exit;
}

$page = isset($_POST['page']) ? intval($_POST['page']) : $_SESSION['PAGE'];
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : $_SESSION['rows'];
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : $_SESSION['SORT_FIELD'];  
$order = isset($_POST['order']) ? strval($_POST['order']) : $_SESSION['SORT_ORDER'];

$_SESSION['rows'] 		= $rows;
$_SESSION['PAGE'] 		= $page;
$_SESSION['SORT_FIELD'] = $sort;
$_SESSION['SORT_ORDER'] = $order;

$offset = ($page-1)*$rows;

$cond 		= "";
$group_by 	= "";
$table 		= "";
$s_student_track_table=""; //DIAM-1017

$sort_order = "ORDER BY CONCAT(S_STUDENT_MASTER.LAST_NAME, ', ', S_STUDENT_MASTER.FIRST_NAME) ASC";
if($_SESSION['SORT_FIELD'] != '' && $_SESSION['SORT_ORDER'] != '')
{
	$sort_order = " ORDER BY $sort $order";
}


if($_REQUEST['LEAD_ENTRY_FROM_DATE'] != '' && $_REQUEST['LEAD_ENTRY_TO_DATE'] != '') {
	$LEAD_START_DATE = date("Y-m-d",strtotime($_REQUEST['LEAD_ENTRY_FROM_DATE']));
	$LEAD_END_DATE 	 = date("Y-m-d",strtotime($_REQUEST['LEAD_ENTRY_TO_DATE']));
	$cond .= " AND ENTRY_DATE BETWEEN '$LEAD_START_DATE' AND '$LEAD_END_DATE' ";
} else if($_REQUEST['LEAD_ENTRY_FROM_DATE'] != '') {
	$LEAD_END_DATE = date("Y-m-d",strtotime($_REQUEST['LEAD_ENTRY_FROM_DATE']));
	$cond .= " AND ENTRY_DATE >= '$LEAD_START_DATE' ";
} else if($_REQUEST['LEAD_ENTRY_TO_DATE'] != '') {
	$LEAD_END_DATE = date("Y-m-d",strtotime($_REQUEST['LEAD_ENTRY_TO_DATE']));
	$cond .= " AND ENTRY_DATE <= '$LEAD_END_DATE' ";
}

$TREM_BEGIN_START_DATE = isset($_REQUEST['TREM_BEGIN_START_DATE']) ? mysql_real_escape_string($_REQUEST['TREM_BEGIN_START_DATE']) : $_SESSION['TREM_BEGIN_START_DATE'];

$TREM_BEGIN_END_DATE = isset($_REQUEST['TREM_BEGIN_END_DATE']) ? mysql_real_escape_string($_REQUEST['TREM_BEGIN_END_DATE']) : $_SESSION['TREM_BEGIN_END_DATE'];

$TREM_END_START_DATE = isset($_REQUEST['TREM_END_START_DATE']) ? mysql_real_escape_string($_REQUEST['TREM_END_START_DATE']) : $_SESSION['TREM_END_START_DATE'];

$TREM_END_END_DATE = isset($_REQUEST['TREM_END_END_DATE']) ? mysql_real_escape_string($_REQUEST['TREM_END_END_DATE']) : $_SESSION['TREM_END_END_DATE'];


//589	
$TERM_CONDITION_AV = '  ';

function is_defined($variable){
	if(isset($variable) && $variable != 'undefined' && $variable != ''){
		return true;
	}else{
		return false;
	}
}

if(is_defined($TREM_BEGIN_START_DATE)){
	$TREM_BEGIN_START_DATE = date('Y-m-d',strtotime($TREM_BEGIN_START_DATE));	
	$TERM_CONDITION_AV .= " AND S_TERM_MASTER.BEGIN_DATE >= '$TREM_BEGIN_START_DATE' ";
} 

if(is_defined($TREM_BEGIN_END_DATE)){
	$TREM_BEGIN_END_DATE = date('Y-m-d',strtotime($TREM_BEGIN_END_DATE));	
	$TERM_CONDITION_AV .= "  AND S_TERM_MASTER.BEGIN_DATE <= '$TREM_BEGIN_END_DATE' ";
} 

if(is_defined($TREM_END_START_DATE)){
	$TREM_END_START_DATE = date('Y-m-d',strtotime($TREM_END_START_DATE));	
	$TERM_CONDITION_AV .= "  AND S_TERM_MASTER.END_DATE >= '$TREM_END_START_DATE' ";
} 
	 
if(is_defined($TREM_END_END_DATE)){
	$TREM_END_END_DATE = date('Y-m-d',strtotime($TREM_END_END_DATE));	
	$TERM_CONDITION_AV .= "  AND S_TERM_MASTER.END_DATE <= '$TREM_END_END_DATE' ";
} 
 
//589


if(!empty($_REQUEST['PK_FUNDING']))
	$cond .= " AND S_STUDENT_ENROLLMENT.PK_FUNDING IN (".$_REQUEST['PK_FUNDING'].") ";

if(!empty($_REQUEST['PK_STUDENT_GROUP']))
{
	$PK_STUDENT_GROUP = implode(',',$_REQUEST['PK_STUDENT_GROUP']);
	$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_GROUP IN (".$PK_STUDENT_GROUP.") ";
}
	
	
if(!empty($_REQUEST['PK_LEAD_SOURCE']))
	$cond .= " AND S_STUDENT_ENROLLMENT.PK_LEAD_SOURCE IN (".$_REQUEST['PK_LEAD_SOURCE'].") ";
	

$TREM_BEGIN_START_DATE = is_defined($_REQUEST['TREM_BEGIN_START_DATE']) ? mysql_real_escape_string($_REQUEST['TREM_BEGIN_START_DATE']) : $_SESSION['TREM_BEGIN_START_DATE'];

$TREM_BEGIN_END_DATE = is_defined($_REQUEST['TREM_BEGIN_END_DATE']) ? mysql_real_escape_string($_REQUEST['TREM_BEGIN_END_DATE']) : $_SESSION['TREM_BEGIN_END_DATE'];
	
$TREM_END_START_DATE = is_defined($_REQUEST['TREM_END_START_DATE']) ? mysql_real_escape_string($_REQUEST['TREM_END_START_DATE']) : $_SESSION['TREM_END_START_DATE'];
	
$TREM_END_END_DATE = is_defined($_REQUEST['TREM_END_END_DATE']) ? mysql_real_escape_string($_REQUEST['TREM_END_END_DATE']) : $_SESSION['TREM_END_END_DATE'];
	
if(is_defined($TREM_BEGIN_START_DATE) && is_defined($TREM_BEGIN_END_DATE)) {
	$TREM_BEGIN_START_DATE=date('Y-m-d',strtotime($TREM_BEGIN_START_DATE));
	$TREM_BEGIN_END_DATE=date('Y-m-d',strtotime($TREM_BEGIN_END_DATE));
	$cond .= " AND S_TERM_MASTER.BEGIN_DATE >= '$TREM_BEGIN_START_DATE' AND S_TERM_MASTER.BEGIN_DATE <= '$TREM_BEGIN_END_DATE' ";
}
	
if( is_defined($TREM_END_START_DATE) && is_defined($TREM_END_END_DATE) ) {
	$TREM_END_START_DATE=date('Y-m-d',strtotime($TREM_END_START_DATE));
	$TREM_END_END_DATE=date('Y-m-d',strtotime($TREM_END_END_DATE));
	$cond .= " AND S_TERM_MASTER.END_DATE >= '$TREM_END_START_DATE' AND S_TERM_MASTER.END_DATE <= '$TREM_END_END_DATE' ";
} 


if(!empty($_REQUEST['PK_TERM_MASTER']))
{
	$PK_TERM_MASTER = implode(',',$_REQUEST['PK_TERM_MASTER']);
	$cond .= " AND S_STUDENT_ENROLLMENT.PK_TERM_MASTER IN (".$PK_TERM_MASTER.") ";
}
	
if(!empty($_REQUEST['MIDPOINT_DATE']))
{
	$result = "'" . implode ( "', '", explode(',',$_REQUEST['MIDPOINT_DATE']) ) . "'";
	$cond .= " AND S_STUDENT_ENROLLMENT.MIDPOINT_DATE IN (".$result.") ";
}
	
if(!empty($_REQUEST['PK_CAMPUS_PROGRAM']))
{
	$PK_CAMPUS_PROGRAM = implode(',',$_REQUEST['PK_CAMPUS_PROGRAM']);
	$cond .= " AND S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM IN (".$PK_CAMPUS_PROGRAM.") ";
}
	
if(!empty($_REQUEST['PK_STUDENT_STATUS']))
{
	$PK_STUDENT_STATUS = implode(',',$_REQUEST['PK_STUDENT_STATUS']);
	$cond .= " AND S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS IN (".$PK_STUDENT_STATUS.") ";
}
	
if(!empty($_REQUEST['PK_COURSE']))
{
	$PK_COURSE = implode(',',$_REQUEST['PK_COURSE']);
	$cond .= " AND S_COURSE_OFFERING.PK_COURSE IN (".$PK_COURSE.") ";
}
	

if(!empty($_REQUEST['PK_COURSE_OFFERING']))
{
	$PK_COURSE_OFFERING = implode(',',$_REQUEST['PK_COURSE_OFFERING']);
	$cond .= " AND S_STUDENT_COURSE.PK_COURSE_OFFERING IN (".$PK_COURSE_OFFERING.") ";
}
	
if(!empty($_REQUEST['PK_REPRESENTATIVE']))
	$cond .= " AND S_STUDENT_ENROLLMENT.PK_REPRESENTATIVE IN (".$_REQUEST['PK_REPRESENTATIVE'].") ";
	
if(!empty($_REQUEST['PK_PLACEMENT_STATUS']))
	$cond .= " AND S_STUDENT_ENROLLMENT.PK_PLACEMENT_STATUS IN (".$_REQUEST['PK_PLACEMENT_STATUS'].") ";

if(!empty($_REQUEST['PK_CAMPUS_GPA'])) // DIAM-1419
	$cond .= " AND S_COURSE_OFFERING.PK_CAMPUS IN (".$_REQUEST['PK_CAMPUS_GPA'].") ";
	
$camp_cond = "";
if(!empty($_REQUEST['PK_CAMPUS'])) {
	$_REQUEST['PK_CAMPUS'] = implode(',',$_REQUEST['PK_CAMPUS']);
	$table .= ",S_STUDENT_CAMPUS ";
	$cond .= " AND S_STUDENT_CAMPUS.PK_CAMPUS IN (".$_REQUEST['PK_CAMPUS'].") AND S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT ";
	$camp_cond = " AND S_STUDENT_CAMPUS.PK_CAMPUS IN (".$_REQUEST['PK_CAMPUS'].")  ";
}

if($_REQUEST['STU_NAME'] != '') {
	$cond .= " AND CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME) LIKE '%$_REQUEST[STU_NAME]%' ";
}
if($_REQUEST['NO_LEAD'] == 1) {
	$cond .= " AND M_STUDENT_STATUS.ADMISSIONS = 0 ";
} else if($_REQUEST['LEAD'] == 1) {
	$cond .= " AND M_STUDENT_STATUS.ADMISSIONS = 1 ";
} else if($_REQUEST['no_admin_check'] == 1) {
} else 
	$cond .= " AND M_STUDENT_STATUS.ADMISSIONS = 0 ";
	
if($_REQUEST['bulk_text'] == 1) {
	$table .= ",S_STUDENT_CONTACT ";
	$cond .= " AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_CONTACT.PK_STUDENT_MASTER AND PK_STUDENT_CONTACT_TYPE_MASTER = '1' AND CELL_PHONE != '' AND OPT_OUT = 0 AND S_STUDENT_CONTACT.ACTIVE = 1 ";
}
	
//if(!empty($_REQUEST['PK_CAMPUS']) || !empty($_REQUEST['PK_COURSE_OFFERING']) || !empty($_REQUEST['PK_COURSE']) ) {
if(!empty($_REQUEST['PK_COURSE_OFFERING']) || !empty($_REQUEST['PK_COURSE']) || !empty($_REQUEST['COURSE_PK_TERM_MASTER']) || !empty($_REQUEST['PK_CAMPUS_GPA']) ) { //Ticket # 1212, 1214 
	if($_REQUEST['bulk_text'] == 1 || $_REQUEST['page'] == 'letter_gen') {
	} else {
		$table .= ",S_STUDENT_COURSE, S_COURSE_OFFERING ";
		
		$cond .= " AND S_STUDENT_COURSE.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT AND S_STUDENT_COURSE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING ";
	}
}	

//Ticket # 1212, 1214 
if(!empty($_REQUEST['COURSE_PK_TERM_MASTER'])){
	$cond .= " AND S_STUDENT_COURSE.PK_TERM_MASTER IN ($_REQUEST[COURSE_PK_TERM_MASTER]) ";
}
//Ticket # 1212, 1214 

if($_REQUEST['ENROLLMENT'] == 2 || $_REQUEST['ENROLLMENT'] == "") {
	$cond .= " AND IS_ACTIVE_ENROLLMENT = 1 ";
}

/* Ticket # 1552 */
if($_REQUEST['SEARCH_TXT'] != '') {
	$cond .= " AND (CONCAT(TRIM(S_STUDENT_MASTER.LAST_NAME),', ', TRIM(S_STUDENT_MASTER.FIRST_NAME)) LIKE '$_REQUEST[SEARCH_TXT]%' OR  TRIM(S_STUDENT_MASTER.FIRST_NAME) LIKE '$_REQUEST[SEARCH_TXT]%' OR  TRIM(S_STUDENT_MASTER.LAST_NAME) LIKE '$_REQUEST[SEARCH_TXT]%' ) ";
}
/* Ticket # 1552 */

/* Ticket # 1571 */
if($_REQUEST['PK_CREDIT_TRANSFER_STATUS'] != '') {
	$table .= ",S_STUDENT_CREDIT_TRANSFER ";
	$cond .= " AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_CREDIT_TRANSFER.PK_STUDENT_MASTER AND PK_CREDIT_TRANSFER_STATUS IN ($_REQUEST[PK_CREDIT_TRANSFER_STATUS]) ";
}
/* Ticket # 1571 */

/* Ticket # 1470 */
if($_REQUEST['FINAL_GRADE'] != '') {
	$cond .= " AND S_STUDENT_COURSE.FINAL_GRADE IN ($_REQUEST[FINAL_GRADE]) ";
}
/* Ticket # 1470 */
//DIAM-1017
$START_DATE = isset($_REQUEST['START_DATE']) ? mysql_real_escape_string($_REQUEST['START_DATE']) : $_SESSION['START_DATE'];
$END_DATE = isset($_REQUEST['END_DATE']) ? mysql_real_escape_string($_REQUEST['END_DATE']) : $_SESSION['END_DATE'];

if($START_DATE != '' && $END_DATE != '' ) {
	$START_DATE=date('Y-m-d',strtotime($START_DATE));
	$END_DATE=date('Y-m-d',strtotime($END_DATE));
	$cond .= " AND S_STUDENT_TRACK_CHANGES.CHANGED_ON >= '$START_DATE' AND S_STUDENT_TRACK_CHANGES.CHANGED_ON <= '$END_DATE' ";
	$s_student_track_table="LEFT JOIN S_STUDENT_TRACK_CHANGES ON S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_TRACK_CHANGES.PK_STUDENT_MASTER";
}
//DIAM-1017
$group_by = " GROUP BY S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT ";
if($_REQUEST['group_by'])
{
    $group_by = " GROUP BY S_STUDENT_MASTER.PK_STUDENT_MASTER ";
}
	

$sCntQuery = "SELECT DISTINCT(S_STUDENT_MASTER.PK_STUDENT_MASTER)
                FROM 
                    S_STUDENT_MASTER $s_student_track_table, 
                    S_STUDENT_ACADEMICS $table, 
                    S_STUDENT_ENROLLMENT 
                    LEFT JOIN M_STUDENT_GROUP ON M_STUDENT_GROUP.PK_STUDENT_GROUP = S_STUDENT_ENROLLMENT.PK_STUDENT_GROUP 
                    LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
                    LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM, 
                    M_STUDENT_STATUS 
                WHERE 
                    S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER 
                    AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' 
                    AND S_STUDENT_MASTER.ARCHIVED = 0 
                    AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER 
                    AND M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS $cond ";

$rs = mysql_query($sCntQuery)or die(mysql_error());
//$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);

$sQuery = "SELECT S_STUDENT_MASTER.PK_STUDENT_MASTER, 
                CONCAT(
                S_STUDENT_MASTER.LAST_NAME, ', ', 
                S_STUDENT_MASTER.FIRST_NAME
                ) AS STU_NAME, 
                STUDENT_GROUP, 
                STUDENT_STATUS, 
                M_CAMPUS_PROGRAM.CODE, 
                IF(
                BEGIN_DATE = '0000-00-00', 
                '', 
                DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y')
                ) AS BEGIN_DATE_1, 
                S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, 
                STUDENT_ID 
             FROM 
                S_STUDENT_MASTER $s_student_track_table, 
                S_STUDENT_ACADEMICS $table, 
                S_STUDENT_ENROLLMENT 
                LEFT JOIN M_STUDENT_GROUP ON M_STUDENT_GROUP.PK_STUDENT_GROUP = S_STUDENT_ENROLLMENT.PK_STUDENT_GROUP 
                LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
                LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM, 
                M_STUDENT_STATUS 
             WHERE 
                S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER 
                AND S_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' 
                AND S_STUDENT_MASTER.ARCHIVED = 0 
                AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ACADEMICS.PK_STUDENT_MASTER 
                AND M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS $cond $group_by 
				$sort_order
              ";

$rs = mysql_query($sQuery. " limit $offset,$rows")or die(mysql_error());	
$res_stud = $db->Execute($sQuery);
$_SESSION['total_student_count'] = $res_stud->RecordCount();
$items = array();
while($row = mysql_fetch_array($rs)){
    

    $PK_STUDENT_ENROLLMENT = $row['PK_STUDENT_ENROLLMENT'];
	$PK_STUDENT_MASTER = $row['PK_STUDENT_MASTER'];

    $row['SELECT'] = '<input type="checkbox" name="PK_STUDENT_ENROLLMENT[]" id="PK_STUDENT_ENROLLMENT" value="'.$PK_STUDENT_ENROLLMENT.'" onclick="get_count()" /><input type="hidden" name="PK_STUDENT_MASTER[]" value="'.$PK_STUDENT_MASTER.'" ><input type="hidden" name="PK_STUDENT_MASTER_'.$PK_STUDENT_ENROLLMENT.'" id="S_PK_STUDENT_MASTER_'.$PK_STUDENT_ENROLLMENT.'" value="'.$PK_STUDENT_MASTER.'" >';

				
	
	
	$row['STU_NAME']         = $row['STU_NAME'];
    $row['STUDENT_ID']       = $row['STUDENT_ID'];
   
    $res_camp_1 = $db->Execute("SELECT CAMPUS_CODE FROM S_STUDENT_CAMPUS, S_CAMPUS WHERE S_STUDENT_CAMPUS.PK_CAMPUS = S_CAMPUS.PK_CAMPUS AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' $camp_cond ");
    $CAMPUS = $res_camp_1->fields['CAMPUS_CODE'];

    $row['CAMPUS_CODE']          = $CAMPUS;
    $row['BEGIN_DATE_1']         = $row['BEGIN_DATE_1'];
    $row['CODE']                 = $row['CODE'];
    $row['STUDENT_STATUS']       = $row['STUDENT_STATUS'];
    $row['STUDENT_GROUP']        = $row['STUDENT_GROUP'];    
	
	array_push($items, $row);
}

$result["rows"] = $items;
echo json_encode($result);

?>


