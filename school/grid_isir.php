<? require_once("../global/config.php");
require_once("../language/common.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_FINANCE') == 0 ){
    header("location:../index");
    exit;
}

$page = isset($_POST['page']) ? intval($_POST['page']) : 1;
$rows = isset($_POST['rows']) ? intval($_POST['rows']) : 10;
$sort = isset($_POST['sort']) ? strval($_POST['sort']) : 'PK_ISIR_STUDENT_MASTER';
$order = isset($_POST['order']) ? strval($_POST['order']) : 'DESC';
                
$SEARCH = isset($_REQUEST['SEARCH']) ? mysql_real_escape_string($_REQUEST['SEARCH']) : '';
$offset = ($page-1)*$rows;
    
$result = array();

$PK_CAMPUS                     = isset($_REQUEST['PK_CAMPUS']) ? mysql_real_escape_string($_REQUEST['PK_CAMPUS']) : '';
$PK_AWARD_YEAR                 = isset($_REQUEST['PK_AWARD_YEAR']) ? mysql_real_escape_string($_REQUEST['PK_AWARD_YEAR']) : '';
$IMPORT_START_DATE             = isset($_REQUEST['IMPORT_START_DATE']) ? mysql_real_escape_string($_REQUEST['IMPORT_START_DATE']) : '';
$IMPORT_END_DATE             = isset($_REQUEST['IMPORT_END_DATE']) ? mysql_real_escape_string($_REQUEST['IMPORT_END_DATE']) : '';

$where = " S_ISIR_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_ISIR_STUDENT_MASTER.ACTIVE=1 ";

if($_GET['id'] != '')
{
    $where .= " AND S_ISIR_STUDENT_MASTER.PK_STUDENT_MASTER = '$_GET[id]' ";
}

if($PK_CAMPUS != '') {
    $where .= " AND S_STUDENT_CAMPUS.PK_CAMPUS = '$PK_CAMPUS' ";
}

if($PK_AWARD_YEAR != '') {
    $where .= " AND Z_ISIR_SETUP_MASTER.PK_ISIR_SETUP_MASTER = '$PK_AWARD_YEAR' ";
}

if($IMPORT_START_DATE != '' && $IMPORT_END_DATE != '') {
    $IMPORT_START_DATE = date('Y-m-d',strtotime($IMPORT_START_DATE));
    $IMPORT_END_DATE   = date('Y-m-d',strtotime($IMPORT_END_DATE));
    $where .= " AND DATE_FORMAT(S_ISIR_STUDENT_MASTER.CREATED_ON, '%Y-%m-%d') BETWEEN '$IMPORT_START_DATE' AND '$IMPORT_END_DATE' ";
}
else if($IMPORT_START_DATE != '')
{
    $IMPORT_START_DATE = date('Y-m-d',strtotime($IMPORT_START_DATE));
    $where .= " AND DATE_FORMAT(S_ISIR_STUDENT_MASTER.CREATED_ON, '%Y-%m-%d') >= '$IMPORT_START_DATE' ";
}
else if($IMPORT_END_DATE != '')
{
    $IMPORT_END_DATE = date('Y-m-d',strtotime($IMPORT_END_DATE));
    $where .= " AND DATE_FORMAT(S_ISIR_STUDENT_MASTER.CREATED_ON, '%Y-%m-%d') <= '$IMPORT_END_DATE' ";
}
    
if($SEARCH != '')
    $where .= " AND (S_ISIR_STUDENT_MASTER.FIRST_NAME like '%$SEARCH%' OR S_ISIR_STUDENT_MASTER.LAST_NAME LIKE '%$SEARCH%' OR S_ISIR_STUDENT_MASTER.EMAIL LIKE '%$SEARCH%' OR S_ISIR_STUDENT_MASTER.FILE_NAME LIKE '%$SEARCH%' OR Z_ISIR_SETUP_MASTER.FROM_NAME like '%$SEARCH%' OR S_STUDENT_ACADEMICS.STUDENT_ID like '%$SEARCH%' OR S_CAMPUS.CAMPUS_CODE like '%$SEARCH%' )"; // OR M_AWARD_YEAR.AWARD_YEAR like '%$SEARCH%'

$rs = mysql_query("SELECT 
                        DISTINCT(S_ISIR_STUDENT_MASTER.PK_ISIR_STUDENT_MASTER) 
                    FROM 
                        S_ISIR_STUDENT_MASTER 
                        LEFT JOIN Z_ISIR_SETUP_MASTER ON S_ISIR_STUDENT_MASTER.PK_ISIR_SETUP_MASTER = Z_ISIR_SETUP_MASTER.PK_ISIR_SETUP_MASTER 

                        LEFT JOIN Z_ISIR_SETUP_DETAIL ON Z_ISIR_SETUP_DETAIL.PK_ISIR_SETUP_MASTER = Z_ISIR_SETUP_MASTER.PK_ISIR_SETUP_MASTER AND Z_ISIR_SETUP_DETAIL.DSIS_FIELD_NAME = 'S_STUDENT_FINANCIAL.ISIR_TRANS_NO'

                        LEFT JOIN S_ISIR_STUDENT_DETAIL ON S_ISIR_STUDENT_DETAIL.PK_ISIR_STUDENT_MASTER = S_ISIR_STUDENT_MASTER.PK_ISIR_STUDENT_MASTER AND S_ISIR_STUDENT_DETAIL.PK_ISIR_SETUP_DETAIL = Z_ISIR_SETUP_DETAIL.PK_ISIR_SETUP_DETAIL

                        LEFT JOIN S_STUDENT_MASTER ON S_STUDENT_MASTER.PK_STUDENT_MASTER = S_ISIR_STUDENT_MASTER.PK_STUDENT_MASTER
                        LEFT JOIN S_STUDENT_ENROLLMENT ON S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER
                        LEFT JOIN S_STUDENT_FINANCIAL ON S_STUDENT_FINANCIAL.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT
                        -- LEFT JOIN M_AWARD_YEAR ON M_AWARD_YEAR.PK_AWARD_YEAR = S_STUDENT_FINANCIAL.PK_AWARD_YEAR
                        LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER
                        LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT
                        LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS
                    WHERE 
                        " . $where. " 
                     ")or die(mysql_error());

$row = mysql_fetch_row($rs);
$result["total"] = mysql_num_rows($rs);
    
$query = "SELECT 
                S_ISIR_STUDENT_MASTER.PK_ISIR_STUDENT_MASTER, 
                S_ISIR_STUDENT_MASTER.FILE_NAME, 
                S_ISIR_STUDENT_MASTER.FIRST_NAME, 
                S_ISIR_STUDENT_MASTER.LAST_NAME, 
                S_ISIR_STUDENT_MASTER.EMAIL, 
                SUBSTRING(Z_ISIR_SETUP_MASTER.FROM_NAME,1,9) AS AWARD_YEAR, 
                S_ISIR_STUDENT_MASTER.PK_ISIR_SETUP_MASTER, 
                S_ISIR_STUDENT_MASTER.PK_STUDENT_MASTER, 
                S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT,
                -- M_AWARD_YEAR.AWARD_YEAR AS AWARD_YEAR,
                S_STUDENT_ACADEMICS.STUDENT_ID AS STUDENT_ID,
                S_STUDENT_FINANCIAL.ISIR_TRANS_NO AS ISIR_TRANS_NO_1,
                S_ISIR_STUDENT_DETAIL.VALUE AS ISIR_TRANS_NO,
                S_CAMPUS.CAMPUS_CODE AS CAMPUS_CODE
            FROM 
                S_ISIR_STUDENT_MASTER 
                LEFT JOIN Z_ISIR_SETUP_MASTER ON S_ISIR_STUDENT_MASTER.PK_ISIR_SETUP_MASTER = Z_ISIR_SETUP_MASTER.PK_ISIR_SETUP_MASTER 

                LEFT JOIN Z_ISIR_SETUP_DETAIL ON Z_ISIR_SETUP_DETAIL.PK_ISIR_SETUP_MASTER = Z_ISIR_SETUP_MASTER.PK_ISIR_SETUP_MASTER AND Z_ISIR_SETUP_DETAIL.DSIS_FIELD_NAME = 'S_STUDENT_FINANCIAL.ISIR_TRANS_NO'

                LEFT JOIN S_ISIR_STUDENT_DETAIL ON S_ISIR_STUDENT_DETAIL.PK_ISIR_STUDENT_MASTER = S_ISIR_STUDENT_MASTER.PK_ISIR_STUDENT_MASTER AND S_ISIR_STUDENT_DETAIL.PK_ISIR_SETUP_DETAIL = Z_ISIR_SETUP_DETAIL.PK_ISIR_SETUP_DETAIL

                LEFT JOIN S_STUDENT_MASTER ON S_STUDENT_MASTER.PK_STUDENT_MASTER = S_ISIR_STUDENT_MASTER.PK_STUDENT_MASTER
                LEFT JOIN S_STUDENT_ENROLLMENT ON S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER
                LEFT JOIN S_STUDENT_FINANCIAL ON S_STUDENT_FINANCIAL.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT
                -- LEFT JOIN M_AWARD_YEAR ON M_AWARD_YEAR.PK_AWARD_YEAR = S_STUDENT_FINANCIAL.PK_AWARD_YEAR
                LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER
                LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT
                LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS
            WHERE 
                " . $where ." 
            
            ORDER BY 
                $sort $order " ;
// echo $query;exit;
$rs = mysql_query($query. " limit $offset,$rows")or die(mysql_error());
    
$items = array();
while($row = mysql_fetch_array($rs)){
    
    $str  = '&nbsp;<a href="isir?id='.$row['PK_ISIR_STUDENT_MASTER'].'&iid='.$row['PK_ISIR_SETUP_MASTER'].'&sid='.$_GET['id'].'" title="'.EDIT.'" class="btn edit-color btn-circle"><i class="far fa-eye"></i> </a>';
    
    if($row['PK_STUDENT_MASTER'] > 0 && $_GET['id'] == '' ) {
        $PK_STUDENT_MASTER = $row['PK_STUDENT_MASTER'];
        $PK_STUDENT_ENROLLMENT = $row['PK_STUDENT_ENROLLMENT'];

        $str  .= '&nbsp;<a href="student?t=3&id='.$PK_STUDENT_MASTER.'&eid='.$PK_STUDENT_ENROLLMENT.'&tab=financialAidTab" title="Go To Student" class="btn edit-color btn-circle"><i class="mdi mdi-account"></i> </a>';
    }

    // Mostrar el botón cuando PK_STUDENT_MASTER = 0 Y PK_ACCOUNT = 72
    if($row['PK_STUDENT_MASTER'] == 0 && $_SESSION['PK_ACCOUNT'] == 72) {
        $str .= '&nbsp;<button type="button" onclick="openStudentModal('.$row['PK_ISIR_STUDENT_MASTER'].')" title="Link Student" class="btn btn-success btn-circle"><i class="fas fa-link"></i></button>';
    }

    $row['STUDENT_ID'] = $row['STUDENT_ID'];
    $row['CAMPUS_CODE'] = $row['CAMPUS_CODE'];
    
    $row['ACTION'] = $row['ACTIVE'].$str;
    
    array_push($items, $row);
}
$result["rows"] = $items;
echo json_encode($result);
?>
