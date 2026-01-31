<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/course_offering.php");
require_once("../language/student.php");
require_once("check_access.php");

$cond = "";
if($_REQUEST['ACTIVE_TERMS_ONLY'] == 1)
	$cond .= " AND ACTIVE = 1 ";
	
if($_REQUEST['START_DATE'] != '' && $_REQUEST['END_DATE'] != '') {
	$START_DATE = date("Y-m-d", strtotime($_REQUEST['START_DATE']));
	$END_DATE 	= date("Y-m-d", strtotime($_REQUEST['END_DATE']));
	$cond .= " AND BEGIN_DATE BETWEEN '$START_DATE' AND '$END_DATE' ";
} else if($_REQUEST['START_DATE'] != '') {
	$START_DATE = date("Y-m-d", strtotime($_REQUEST['START_DATE']));
	$cond .= " AND BEGIN_DATE >= '$START_DATE' ";
} else if($_REQUEST['END_DATE'] != '') {
	$END_DATE 	= date("Y-m-d", strtotime($_REQUEST['END_DATE']));
	$cond .= " AND BEGIN_DATE <= '$END_DATE' ";
}

if($_REQUEST['START_DATE_1'] != '' && $_REQUEST['END_DATE_1'] != '') {
	$START_DATE_1	= date("Y-m-d", strtotime($_REQUEST['START_DATE_1']));
	$END_DATE_1 	= date("Y-m-d", strtotime($_REQUEST['END_DATE_1']));
	$cond .= " AND END_DATE BETWEEN '$START_DATE_1' AND '$END_DATE_1' ";
} else if($_REQUEST['START_DATE_1'] != '') {
	$START_DATE_1 = date("Y-m-d", strtotime($_REQUEST['START_DATE_1']));
	$cond .= " AND END_DATE >= '$START_DATE_1' ";
} else if($_REQUEST['END_DATE_1'] != '') {
	$END_DATE_1 = date("Y-m-d", strtotime($_REQUEST['END_DATE_1']));
	$cond .= " AND END_DATE <= '$END_DATE_1' ";
}

if($_REQUEST['SEARCH'] != '')
	$cond .= " AND TERM_DESCRIPTION like '%$_REQUEST[SEARCH]%' ";
	
//echo $cond;
?>

<div class="tableFixHead" >
	<table class="table table-hover table-striped" > 
		<thead>
			<tr>
				<th class="sticky_header" scope="col" >
					<input type="checkbox" name="SEARCH_SELECT_ALL" id="SEARCH_SELECT_ALL" value="1" onclick="fun_select_all()" />
				</th>
				<th class="sticky_header" scope="col" >Begin Date</th>
				<th class="sticky_header" scope="col" >End Date</th>
				<th class="sticky_header" scope="col" >Description</th>
			</tr>
		</thead>
		<tbody>
			<? $res_s = $db->Execute("select PK_TERM_MASTER from Z_USER_FILTER WHERE PK_USER = '$_SESSION[PK_USER]' AND PAGE_T = '$_GET[t]' ");
			$PK_TERM_MASTER_ARR = explode(",",$res_s->fields['PK_TERM_MASTER']);
			
			$res_type = $db->Execute("select PK_TERM_MASTER,IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS  BEGIN_DATE_1, If(END_DATE != '0000-00-00', DATE_FORMAT(END_DATE,'%m/%d/%Y'),'') AS END_DATE_1, TERM_DESCRIPTION, ACTIVE  from S_TERM_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $cond order by ACTIVE DESC, BEGIN_DATE DESC");
			while (!$res_type->EOF) { 
				$checked = "";
				if(!empty($PK_TERM_MASTER_ARR)){
					foreach($PK_TERM_MASTER_ARR as $PK_TERM_MASTER){
						if($res_type->fields['PK_TERM_MASTER'] == $PK_TERM_MASTER)
							$checked = "checked";
					}
				} ?>
				<tr>
					<td >
						<input type="checkbox" id="PK_TERM_MASTER_<?=$res_type->fields['PK_TERM_MASTER']?>" name="PK_TERM_MASTER[]" value="<?=$res_type->fields['PK_TERM_MASTER']?>" <?=$checked?> >
					</td>
					<td >
						<?=$res_type->fields['BEGIN_DATE_1'] ?>
					</td>
					<td >
						<?=$res_type->fields['END_DATE_1']?>
					</td>
					<td >
						<?=$res_type->fields['TERM_DESCRIPTION']?>
						<? if($res_type->fields['ACTIVE'] == 0) echo " <span style='color:red' >(Inactive)</span>"; ?>
					</td>
				</tr>
			<?	$res_type->MoveNext();
			} ?>
		</tbody>
	</table>
</div>