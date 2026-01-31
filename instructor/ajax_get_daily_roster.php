<? require_once("../global/config.php"); 
require_once("../language/attendance_entry.php");
require_once("../language/common.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || $_SESSION['PK_ROLES'] != 3 ){ 
	header("location:../index");
	exit;
}

?>
<div class="table-responsive">
	<table class="table table-bordered">
		<thead>
			<tr>
				<th>
					<?=STUDENTS?>&nbsp;&nbsp;
					<a target="_blank" href="daily_roster_pdf?id=<?=$_REQUEST['val']?>" class="btn waves-effect waves-light btn-info" ><?=PDF ?></a>
				</th>
			</tr>
		</thead>
		<tbody>
			<? $query = "select PK_STUDENT_SCHEDULE, S_STUDENT_MASTER.PK_STUDENT_MASTER, CONCAT(LAST_NAME,', ',FIRST_NAME, ' ',SUBSTRING(S_STUDENT_MASTER.MIDDLE_NAME, 1, 1)) AS NAME, STUDENT_ID from S_STUDENT_SCHEDULE, S_STUDENT_MASTER LEFT JOIN S_STUDENT_ACADEMICS ON S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER WHERE S_STUDENT_SCHEDULE.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING_SCHEDULE_DETAIL = '$_REQUEST[val]' AND S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_SCHEDULE.PK_STUDENT_MASTER ORDER BY CONCAT(LAST_NAME,' ',FIRST_NAME, ' ',S_STUDENT_MASTER.MIDDLE_NAME) ASC ";
			$_SESSION['QUERY'] = $query;
			$res_cs = $db->Execute($query);
			while (!$res_cs->EOF) { ?>
				<tr>
					<td>
						<?=$res_cs->fields['STUDENT_ID'].' - '.$res_cs->fields['NAME']?>
					</td>
				</tr>
			<?	$res_cs->MoveNext();
			} ?>
		</tbody>
	</table>
</div>