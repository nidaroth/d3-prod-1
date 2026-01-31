<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/bulk_text.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' ){ 
	header("location:../index");
	exit;
}

$cond 		= "";
$group_by 	= "";
$table 		= "";

$TREM_BEGIN_START_DATE = isset($_REQUEST['TREM_BEGIN_START_DATE']) ? mysql_real_escape_string($_REQUEST['TREM_BEGIN_START_DATE']) : $_SESSION['TREM_BEGIN_START_DATE'];

$TREM_BEGIN_END_DATE = isset($_REQUEST['TREM_BEGIN_END_DATE']) ? mysql_real_escape_string($_REQUEST['TREM_BEGIN_END_DATE']) : $_SESSION['TREM_BEGIN_END_DATE'];

$TREM_END_START_DATE = isset($_REQUEST['TREM_END_START_DATE']) ? mysql_real_escape_string($_REQUEST['TREM_END_START_DATE']) : $_SESSION['TREM_END_START_DATE'];

$TREM_END_END_DATE = isset($_REQUEST['TREM_END_END_DATE']) ? mysql_real_escape_string($_REQUEST['TREM_END_END_DATE']) : $_SESSION['TREM_END_END_DATE'];


//589	
if($TREM_BEGIN_START_DATE != '' && $TREM_BEGIN_END_DATE != '' ) {
	$TREM_BEGIN_START_DATE=date('Y-m-d',strtotime($TREM_BEGIN_START_DATE));
	$TREM_BEGIN_END_DATE=date('Y-m-d',strtotime($TREM_BEGIN_END_DATE));
	$cond .= " AND T.BEGIN_DATE BETWEEN '$TREM_BEGIN_START_DATE' AND '$TREM_BEGIN_END_DATE' ";

}else if($TREM_BEGIN_START_DATE != ''){
	$TREM_BEGIN_START_DATE 	= date('Y-m-d',strtotime($TREM_BEGIN_START_DATE));
	$cond .= " AND T.BEGIN_DATE >= '$TREM_BEGIN_START_DATE'";
} else if($TREM_BEGIN_END_DATE != ''){
	$TREM_BEGIN_END_DATE 	= date('Y-m-d',strtotime($TREM_BEGIN_END_DATE));
	$cond .= " AND T.BEGIN_DATE <= '$TREM_BEGIN_END_DATE' ";
}

if($TREM_END_START_DATE != '' && $TREM_END_END_DATE != '' ) {
	$TREM_END_START_DATE=date('Y-m-d',strtotime($TREM_END_START_DATE));
	$TREM_END_END_DATE=date('Y-m-d',strtotime($TREM_END_END_DATE));
	$cond .= " AND T.END_DATE BETWEEN '$TREM_END_START_DATE' AND '$TREM_END_END_DATE' ";
	
}else if($TREM_END_START_DATE != ''){
	$TREM_END_START_DATE 	= date('Y-m-d',strtotime($TREM_END_START_DATE));
	$cond .= " AND T.END_DATE >= '$TREM_END_START_DATE'";
} else if($TREM_END_END_DATE != ''){
	$TREM_END_END_DATE 	= date('Y-m-d',strtotime($TREM_END_END_DATE));
	$cond .= " AND T.END_DATE <= '$TREM_END_END_DATE' ";
} 
//589

$FROM_DATE 	= $_REQUEST['START_DATE'];
$TO_DATE 	= $_REQUEST['END_DATE'];
if($FROM_DATE != '' && $TO_DATE != ''){
	$FROM_DATE 	= date('Y-m-d',strtotime($FROM_DATE));
	$TO_DATE 	= date('Y-m-d',strtotime($TO_DATE));	
	$cond .= " AND D.DISBURSEMENT_DATE BETWEEN '$FROM_DATE' AND '$TO_DATE' ";
} else if($FROM_DATE != ''){
	$FROM_DATE 	= date('Y-m-d',strtotime($FROM_DATE));
	$cond .= " AND D.DISBURSEMENT_DATE >= '$FROM_DATE' ";
} else if($TO_DATE != ''){
	$TO_DATE 	= date('Y-m-d',strtotime($TO_DATE));
	$cond .= " AND D.DISBURSEMENT_DATE <= '$TO_DATE' ";
}

if(!empty($_REQUEST['PK_STUDENT_GROUP']))
	$cond .= " AND FIND_IN_SET(SE.PK_STUDENT_GROUP, '".$_REQUEST['PK_STUDENT_GROUP']."') ";

if(!empty($_REQUEST['PK_CAMPUS_PROGRAM']))
	$cond .= " AND FIND_IN_SET(SE.PK_CAMPUS_PROGRAM, '".$_REQUEST['PK_CAMPUS_PROGRAM']."')";
	
if(!empty($_REQUEST['PK_STUDENT_STATUS']))
	$cond .= " AND FIND_IN_SET(SE.PK_STUDENT_STATUS, '".$_REQUEST['PK_STUDENT_STATUS']."')";
	
$camp_cond = "";
if(!empty($_REQUEST['PK_CAMPUS'])) {
	$cond .= " AND FIND_IN_SET(SC.PK_CAMPUS, '".$_REQUEST['PK_CAMPUS']."')";
}



$group_by = " GROUP BY D.PK_STUDENT_ENROLLMENT ";

// As barre request change the query
$res_stud = $db->Execute("SELECT D.PK_STUDENT_ENROLLMENT
,CONCAT(S.LAST_NAME,', ', S.FIRST_NAME, ' ', S.MIDDLE_NAME) AS STUDENT_NAME
,SA.STUDENT_ID
,COALESCE(C.CAMPUS_CODE,'') AS CAMPUS_CODE
,CASE WHEN T.BEGIN_DATE = '0000-00-00' THEN '' ELSE COALESCE(T.BEGIN_DATE,'') END AS ENROLLMENT_START
,COALESCE(P.CODE,'') AS PROGRAM
,COALESCE(SS.STUDENT_STATUS,'') AS STUDENT_STATUS
,COALESCE(SG.STUDENT_GROUP,'') AS STUDENT_GROUP,
S.PK_STUDENT_MASTER
FROM S_STUDENT_DISBURSEMENT AS D
INNER JOIN M_AR_LEDGER_CODE AS LC ON D.PK_AR_LEDGER_CODE = LC.PK_AR_LEDGER_CODE
INNER JOIN S_STUDENT_ENROLLMENT AS SE ON D.PK_STUDENT_ENROLLMENT = SE.PK_STUDENT_ENROLLMENT
INNER JOIN S_STUDENT_MASTER AS S ON D.PK_STUDENT_MASTER = S.PK_STUDENT_MASTER
INNER JOIN S_STUDENT_ACADEMICS AS SA ON S.PK_STUDENT_MASTER = SA.PK_STUDENT_MASTER
INNER JOIN M_STUDENT_STATUS AS SS ON SE.PK_STUDENT_STATUS = SS.PK_STUDENT_STATUS
LEFT JOIN M_CAMPUS_PROGRAM AS P ON  SE.PK_CAMPUS_PROGRAM = P.PK_CAMPUS_PROGRAM
LEFT JOIN S_TERM_MASTER AS T ON  SE.PK_TERM_MASTER = T.PK_TERM_MASTER
LEFT JOIN M_STUDENT_GROUP AS SG ON  SE.PK_STUDENT_GROUP = SG.PK_STUDENT_GROUP
LEFT JOIN S_STUDENT_CAMPUS AS SC ON SE.PK_STUDENT_ENROLLMENT = SC.PK_STUDENT_ENROLLMENT
LEFT JOIN S_CAMPUS AS C ON SC.PK_CAMPUS = C.PK_CAMPUS 
WHERE D.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' 
AND LC.INVOICE = 1
AND D.PK_DISBURSEMENT_STATUS = 2 
$cond $group_by ORDER BY S.LAST_NAME, S.FIRST_NAME, SA.STUDENT_ID, SS.STUDENT_STATUS, P.CODE, T.BEGIN_DATE, C.CAMPUS_CODE"); 


?>
<table class="table table-hover" id="student_update_table" >
	<thead>
		<tr>
			<? if($_REQUEST['show_check'] == 1){ ?>
			<th>
				<input type="checkbox" name="SEARCH_SELECT_ALL" id="SEARCH_SELECT_ALL" value="1" onclick="fun_select_all()" />
			</th>
			<? } ?>
			<th><?=STUDENT?></th>
			<th><?=STUDENT_ID?></th><!-- Ticket # 1371 -->
			<th><?=CAMPUS?></th><!-- Ticket # 1371 -->
			<th><?=FIRST_TERM?></th>
			<th><?=PROGRAM?></th>
			<th><?=STATUS?></th>
			<th><?=STUDENT_GROUP?></th> <!-- Ticket # 1247 -->
			<th>
				<?=TOTAL_COUNT.': '.$res_stud->RecordCount() ?>
				<? if($_REQUEST['bulk_text'] == 1 || $_REQUEST['show_count'] == 1 || $_REQUEST['page'] == 'letter_gen') { ?>
				<br /><?=SELECTED_COUNT.': ' ?><span id="SELECTED_COUNT"></span>
				<? } ?>
			</th>
		</tr>
	</thead>
	<tbody>
	<? while (!$res_stud->EOF) { ?>
		<tr>
			<? if($_REQUEST['show_check'] == 1){ ?>
			<th>
				<input type="checkbox" name="PK_STUDENT_ENROLLMENT[]" id="PK_STUDENT_ENROLLMENT" value="<?=$res_stud->fields['PK_STUDENT_ENROLLMENT']?>" <? if($_REQUEST['bulk_text'] == 1 || $_REQUEST['show_count'] == 1 || $_REQUEST['page'] == 'letter_gen') { ?> onclick="get_count()" <? } ?> />
			</th>
			<? } ?>
			<td >
				<input type="hidden" name="PK_STUDENT_MASTER[]" value="<?=$res_stud->fields['PK_STUDENT_MASTER']?>" >
				<input type="hidden" name="PK_STUDENT_MASTER_<?=$res_stud->fields['PK_STUDENT_ENROLLMENT']?>" id="S_PK_STUDENT_MASTER_<?=$res_stud->fields['PK_STUDENT_ENROLLMENT']?>" value="<?=$res_stud->fields['PK_STUDENT_MASTER']?>" ><!-- Ticket # 1193 --><!-- Ticket # 1673 -->
				
				<? if($_REQUEST['show_check'] != 1){ ?>
				<input type="hidden" name="PK_STUDENT_ENROLLMENT[]" value="<?=$res_stud->fields['PK_STUDENT_ENROLLMENT']?>" >
				<? } ?>
				
				<?=$res_stud->fields['STUDENT_NAME']?>
			</td>
			<td  > <!-- Ticket # 1371 -->
				<?=$res_stud->fields['STUDENT_ID']?>
			</td>
			<!-- Ticket # 1371 -->
			<td >
			<?=$res_stud->fields['CAMPUS_CODE']?>
			</td>
			<td  > <!-- Ticket # 1247 -->
				<?=$res_stud->fields['ENROLLMENT_START']?>
			</td>
			<!-- Ticket # 1247 -->
			<td >
				<?=$res_stud->fields['PROGRAM']?>
			</td>
			<td >
				<?=$res_stud->fields['STUDENT_STATUS']?>
			</td>
			
			
			<td colspan="2" >
				<?=$res_stud->fields['STUDENT_GROUP']?>
			</td>
		</tr>
		
	<?	$res_stud->MoveNext();
	} ?>
	</tbody>
</table>