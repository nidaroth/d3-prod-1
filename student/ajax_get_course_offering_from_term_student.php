<? require_once("../global/config.php"); 
require_once("../language/common.php");

$cond = "";
if($_REQUEST['PK_TERM_MASTER'] != '') 
{
    $cond .= " AND S_COURSE_OFFERING.PK_TERM_MASTER  = '$_REQUEST[PK_TERM_MASTER]' ";
}

?>
<select id="stud_course_id" name="stud_course_id" class="form-control required-entry" >
    <option value="" >Select Course</option>
    <? $res_type = $db->Execute("SELECT S_COURSE.PK_COURSE, S_COURSE.COURSE_CODE FROM  S_STUDENT_COURSE, S_COURSE_OFFERING, S_COURSE WHERE  S_STUDENT_COURSE.PK_STUDENT_MASTER = '$_SESSION[PK_STUDENT_MASTER]' AND S_STUDENT_COURSE.PK_COURSE_OFFERING = S_COURSE_OFFERING.PK_COURSE_OFFERING AND S_STUDENT_COURSE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE $cond GROUP BY S_COURSE.PK_COURSE ORDER BY COURSE_CODE ASC ");
    $cnt = $res_type->RecordCount();
    $i = 1;
    $PK_TERM_MASTER = $_REQUEST['PK_TERM_MASTER']; // ($i == 1 && $PK_TERM_MASTER != "")
    while (!$res_type->EOF) { ?>
        <option value="<?=$res_type->fields['PK_COURSE'] ?>" <? if( ($_REQUEST['stud_course_id'] == $res_type->fields['PK_COURSE']) || ($cnt < 2) ) echo "selected"; ?> ><?=$res_type->fields['COURSE_CODE'] ?></option>
    <?	$res_type->MoveNext();
        $i++;
    } ?>
</select>