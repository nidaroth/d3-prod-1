<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/student.php");
require_once("check_access.php");


$enrollment_ids = $_REQUEST['enrollment_ids'];
if($enrollment_ids != '')
{
    $_SESSION['BULK_EN'] = $enrollment_ids;
    echo "success";
}
else{
    echo "error";
}
?>