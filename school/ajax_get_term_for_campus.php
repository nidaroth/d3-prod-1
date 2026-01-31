<? require_once("../language/common.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || ($_SESSION['PK_ROLES'] != 2 && $_SESSION['PK_ROLES'] != 3)){ 
    header("location:../index");
    exit;
}
$PK_CAMPUS = implode(',',$_REQUEST['PK_CAMPUS']);
$sql = "SELECT DISTINCT(PK_TERM_MASTER) AS PK_TERM_MASTER   FROM S_COURSE_OFFERING WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS IN ($PK_CAMPUS)";
$result = $db->Execute($sql);
while (!$result->EOF) {
    $PK_TERM_MASTER_CSV_ARR[] = $result->fields['PK_TERM_MASTER'];
    $result->MoveNext();
}
$PK_TERM_MASTER_CSV = implode(',',$PK_TERM_MASTER_CSV_ARR);

$sql = "select S_TERM_MASTER.PK_TERM_MASTER AS TERM_ID,IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1 from S_TERM_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_TERM_MASTER.PK_TERM_MASTER IN ($PK_TERM_MASTER_CSV) ORDER BY  BEGIN_DATE DESC";
$result = $db->Execute($sql);

while (!$result->EOF) {
    $row[] = [$result->fields['TERM_ID'] , $result->fields['BEGIN_DATE_1']];
    $result->MoveNext();
}
header('Content-type: application/json');
if(isset($_REQUEST['DATATYPE']) && $_REQUEST['DATATYPE'] == 'json'){

    foreach ($row as $item) { 
        $json_options[] = ['id' => $item[0], 'text' => $item[1]]; 
    }
   
    echo json_encode($json_options);
}else{
    echo json_encode($row);
}
