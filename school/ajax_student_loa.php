<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/student.php");
require_once("../language/student_loa.php");

/* Ticket #1066  */
if($_SESSION['PK_ROLES'] == 3){
} else {
	require_once("get_department_from_t.php");
	require_once("check_access.php");

	$ADMISSION_ACCESS 	= check_access('ADMISSION_ACCESS');
	$REGISTRAR_ACCESS 	= check_access('REGISTRAR_ACCESS');
	$FINANCE_ACCESS 	= check_access('FINANCE_ACCESS');
	$ACCOUNTING_ACCESS 	= check_access('ACCOUNTING_ACCESS');
	$PLACEMENT_ACCESS 	= check_access('PLACEMENT_ACCESS');

	if($ADMISSION_ACCESS == 0 && $REGISTRAR_ACCESS == 0 && $FINANCE_ACCESS == 0 && $ACCOUNTING_ACCESS == 0 && $PLACEMENT_ACCESS == 0 ){ 
		header("location:../index");
		exit;
	}
	
	$PK_DEPARTMENT = get_department_from_t($_REQUEST['t']);
}

$search = $_REQUEST['search'];
$sid 	= $_REQUEST['sid'];
$eid 	= $_REQUEST['eid'];
$t 		= $_REQUEST['t'];
$event	= $_REQUEST['event'];
$loa_cond = "";
if($search != '')
	$loa_cond .= " AND (REASON like '%$search%' OR S_STUDENT_LOA.NOTES like '%$search%') ";

$s_field = $_REQUEST['field'];
$s_order = $_REQUEST['order'];

if($_REQUEST['field'] == '')
	$_REQUEST['field'] = ' S_TERM_MASTER.BEGIN_DATE DESC ';
?>
<table class="table table-hover">
	<thead>
		<tr>
			<? if($s_field == 'BEGIN_DATE_1') {
				if(trim($_REQUEST['order']) == '' || trim($_REQUEST['order']) == 'ASC')
					$s_order = ' DESC '; 
				else
					$s_order = ' ASC ';
			} else 
				$s_order = ' ASC '; ?>
			<th onclick="search_loa('','BEGIN_DATE_1','<?=$s_order?>')" style="cursor: pointer;" ><?=ENROLLMENT?></th>
			
			<? if($s_field == 'LOA_BEGIN_DATE') {
				if(trim($_REQUEST['order']) == '' || trim($_REQUEST['order']) == 'ASC')
					$s_order = ' DESC '; 
				else
					$s_order = ' ASC ';
			} else 
				$s_order = ' ASC '; ?>
			<th onclick="search_loa('','LOA_BEGIN_DATE','<?=$s_order?>')" style="cursor: pointer;" ><?=BEGIN_DATE?></th>
			
			<? if($s_field == 'LOA_END_DATE') {
				if(trim($_REQUEST['order']) == '' || trim($_REQUEST['order']) == 'ASC')
					$s_order = ' DESC '; 
				else
					$s_order = ' ASC ';
			} else 
				$s_order = ' ASC '; ?>
			<th onclick="search_loa('','LOA_END_DATE','<?=$s_order?>')" style="cursor: pointer;" ><?=END_DATE?></th>
			
			<? if($s_field == 'NO_OF_DAYS') {
				if(trim($_REQUEST['order']) == '' || trim($_REQUEST['order']) == 'ASC')
					$s_order = ' DESC '; 
				else
					$s_order = ' ASC ';
			} else 
				$s_order = ' ASC '; ?>
			<th onclick="search_loa('','NO_OF_DAYS','<?=$s_order?>')" style="cursor: pointer;" ><?=NO_OF_DAYS?></th>
			
			<? if($s_field == 'REASON') {
				if(trim($_REQUEST['order']) == '' || trim($_REQUEST['order']) == 'ASC')
					$s_order = ' DESC '; 
				else
					$s_order = ' ASC ';
			} else 
				$s_order = ' ASC '; ?>
			<th onclick="search_loa('','REASON','<?=$s_order?>')" style="cursor: pointer;" ><?=REASON?></th>
			
			<? if($s_field == 'NOTES') {
				if(trim($_REQUEST['order']) == '' || trim($_REQUEST['order']) == 'ASC')
					$s_order = ' DESC '; 
				else
					$s_order = ' ASC ';
			} else 
				$s_order = ' ASC '; ?>
			<th onclick="search_loa('','NOTES','<?=$s_order?>')" style="cursor: pointer;" ><?=NOTES?></th>
			
			<th><?=OPTION?></th>
		</tr>
	</thead>
	<tbody>
	<? //AND PK_STUDENT_ENROLLMENT = '$eid'
	//$cond = " AND S_STUDENT_LOA.PK_DEPARTMENT = '$PK_DEPARTMENT' ";
	$cond = "";
	$res_type = $db->Execute("select PK_STUDENT_LOA, CODE,IF(S_TERM_MASTER.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(S_TERM_MASTER.BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, S_STUDENT_LOA.NOTES, REASON, STUDENT_STATUS, S_STUDENT_LOA.PK_STUDENT_ENROLLMENT, IF(S_STUDENT_LOA.BEGIN_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_LOA.BEGIN_DATE, '%m/%d/%Y' )) AS LOA_BEGIN_DATE ,IF(S_STUDENT_LOA.END_DATE = '0000-00-00','',DATE_FORMAT(S_STUDENT_LOA.END_DATE, '%m/%d/%Y' )) AS LOA_END_DATE, DATEDIFF(S_STUDENT_LOA.END_DATE, S_STUDENT_LOA.BEGIN_DATE) AS NO_OF_DAYS FROM S_STUDENT_LOA LEFT JOIN S_STUDENT_ENROLLMENT ON S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = S_STUDENT_LOA.PK_STUDENT_ENROLLMENT LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS WHERE S_STUDENT_LOA.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_STUDENT_LOA.PK_STUDENT_MASTER = '$sid' $loa_cond ORDER BY $_REQUEST[field] $_REQUEST[order] ");

	while (!$res_type->EOF) { 
		$PK_STUDENT_ENROLLMENT = $res_type->fields['PK_NOTE_TYPE_MASTER'];
		$res_camp = $db->Execute("SELECT CAMPUS_CODE FROM S_STUDENT_CAMPUS, S_CAMPUS WHERE S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT' "); ?>
		<tr <? if($res_type->fields['PK_NOTE_TYPE_MASTER'] == 1 && $res_type->fields['SATISFIED'] == 0) { ?> style="background-color: #F77C7C !important;color: #fff;" <? } ?> >
			<td><div style="width:250px"><?=$res_type->fields['BEGIN_DATE_1'].' - '.$res_type->fields['CODE'].' - '.$res_type->fields['STUDENT_STATUS'].' - '.$res_camp->fields['CAMPUS_CODE']?></div></td>
			<td><div style="width:80px"><?=$res_type->fields['LOA_BEGIN_DATE']?></div></td>
			<td><div style="width:80px"><?=$res_type->fields['LOA_END_DATE']?></div></td>
			<td>
				<div style="width:80px"><?=($res_type->fields['NO_OF_DAYS'] + 1); //Ticket #1153 ?></div>
			</td>
			<td><div style="width:300px"><?=$res_type->fields['REASON']?></div></td>
			<td><div style="width:300px"><?=nl2br($res_type->fields['NOTES'])?></div></td>
			<td>
				<? if($_REQUEST['rd'] == '' && ($REGISTRAR_ACCESS == 2 || $REGISTRAR_ACCESS == 3) ){ ?>
				<a href="student_loa?sid=<?=$sid?>&id=<?=$res_type->fields['PK_STUDENT_LOA']?>&t=<?=$_REQUEST['t']?>&eid=<?=$eid?>" title="<?=EDIT?>" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>
				
				<a href="javascript:void(0);" onclick="delete_row('<?=$res_type->fields['PK_STUDENT_LOA']?>','LOA')" title="<?=DELETE?>" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i> </a>
				
				<? } ?>
			</td>
		</tr>
	<?	$res_type->MoveNext();
	} ?>
	</tbody>
</table>