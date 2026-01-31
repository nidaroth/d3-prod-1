<? require_once("../global/config.php"); 
require_once("../language/common.php");

$PK_TERM_MASTER	= $_REQUEST['PK_TERM_MASTER']; ?>
<select id="INSTRUCTOR" name="INSTRUCTOR" class="form-control " >
	<option value=""></option>
	<? $res_cs = $db->Execute("SELECT
  INSTRUCTOR,
  CONCAT(LAST_NAME,
  ', ',
  FIRST_NAME) AS NAME
FROM
  S_COURSE_OFFERING
LEFT JOIN
  S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_COURSE_OFFERING.INSTRUCTOR,
  S_TERM_MASTER,
  S_COURSE
WHERE
  S_COURSE_OFFERING.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_COURSE_OFFERING.PK_TERM_MASTER IN($PK_TERM_MASTER) AND S_TERM_MASTER.PK_TERM_MASTER = S_COURSE_OFFERING.PK_TERM_MASTER AND S_COURSE.PK_COURSE = S_COURSE_OFFERING.PK_COURSE
  GROUP BY INSTRUCTOR
ORDER BY ISNULL(CONCAT(LAST_NAME,', ',FIRST_NAME)),CONCAT(LAST_NAME,', ',FIRST_NAME) ASC");

	while (!$res_cs->EOF) { 
	if($res_cs->fields['NAME'] == NULL){
		$res_cs->fields['NAME'] = 'Unassigned, Unassigned';
		$res_cs->fields['INSTRUCTOR'] = 'Unassigned';
	}?>
	<option value="<?=$res_cs->fields['INSTRUCTOR']?>" ><?=$res_cs->fields['NAME'] ?></option>
	<?	$res_cs->MoveNext(); } ?>

</select>