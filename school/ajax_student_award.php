<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/student.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || ($_SESSION['PK_ROLES'] != 2 && $_SESSION['PK_ROLES'] != 3 && $_SESSION['PK_ROLES'] != 4 && $_SESSION['PK_ROLES'] != 5)){ 
	header("location:../index");
	exit;
}

$PK_STUDENT_AWARD 	= $_REQUEST['PK_STUDENT_AWARD'];
$award_id			= $_REQUEST['award_id'];

if($PK_STUDENT_AWARD == '') {
	$PK_AR_LEDGER_CODE 	= '';
	$START_DATE 		= '';
	$END_DATE 			= '';
	$ACCEPTED_DATE 		= '';
	$AWARD_AMOUNT 		= '';
	$FEE_AMOUNT 		= '';
	$NET_AMOUNT 		= '';
	$ACADEMIC_YEAR 		= '';
	$AWARD_YEAR 		= '';
	$LOAN_ID 			= '';
	$PK_LENDER_MASTER 	= '';
	$SERVICE 			= '';
	$PK_GUARANTOR 		= '';
	$NOTES				= '';
} else {
	$res_11 = $db->Execute("select * from S_STUDENT_AWARD WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_AWARD = '$PK_STUDENT_AWARD' ");


	$PK_AR_LEDGER_CODE 	= $res_11->fields['PK_AR_LEDGER_CODE'];
	$START_DATE 		= $res_11->fields['START_DATE'];
	$END_DATE 			= $res_11->fields['END_DATE'];
	$ACCEPTED_DATE 		= $res_11->fields['ACCEPTED_DATE'];
	$AWARD_AMOUNT 		= $res_11->fields['AWARD_AMOUNT'];
	$FEE_AMOUNT 		= $res_11->fields['FEE_AMOUNT'];
	$NET_AMOUNT 		= $res_11->fields['NET_AMOUNT'];
	$ACADEMIC_YEAR 		= $res_11->fields['ACADEMIC_YEAR'];
	$AWARD_YEAR 		= $res_11->fields['AWARD_YEAR'];
	$LOAN_ID 			= $res_11->fields['LOAN_ID'];
	$PK_LENDER_MASTER 	= $res_11->fields['PK_LENDER_MASTER'];
	$SERVICE 			= $res_11->fields['SERVICE'];
	$PK_GUARANTOR 		= $res_11->fields['PK_GUARANTOR'];
	$NOTES 				= $res_11->fields['NOTES'];
	
	if($START_DATE != '0000-00-00')
		$START_DATE = date("m/d/Y",strtotime($START_DATE));
	else
		$START_DATE = '';
		
	if($END_DATE != '0000-00-00')
		$END_DATE = date("m/d/Y",strtotime($END_DATE));
	else
		$END_DATE = '';
		
	if($ACCEPTED_DATE != '0000-00-00')
		$ACCEPTED_DATE = date("m/d/Y",strtotime($ACCEPTED_DATE));
	else
		$ACCEPTED_DATE = '';
} ?>
<div class="p-20 theme-h-border" id="student_award_div_<?=$award_id?>" >	
	<div class="row"  >
		<input type="hidden" name="PK_STUDENT_AWARD[]" id="PK_STUDENT_AWARD" value="<?=$PK_STUDENT_AWARD?>" />
		
		<div class="col-md-2 "  >
			<div class="col-12 col-sm-12 form-group focused">
				<select name="AWARD_PK_AR_LEDGER_CODE[]" id="AWARD_PK_AR_LEDGER_CODE_<?=$award_id?>" class="form-control" >
					<option value=""></option>
					<? $res_type = $db->Execute("select PK_AR_LEDGER_CODE,CODE,LEDGER_DESCRIPTION from M_AR_LEDGER_CODE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND TYPE = 1 order by CODE ASC");
					while (!$res_type->EOF) { ?>
						<option value="<?=$res_type->fields['PK_AR_LEDGER_CODE'] ?>" <? if($res_type->fields['PK_AR_LEDGER_CODE'] == $PK_AR_LEDGER_CODE) echo "selected"; ?> ><?=$res_type->fields['CODE'].' - '.$res_type->fields['PK_AR_LEDGER_CODE']?></option>
					<?	$res_type->MoveNext();
					} ?>
				</select>
				<span class="bar"></span> 
				<label for="AWARD_ACADEMIC_YEAR_<?=$award_id?>"><?=LEDGER_CODE?></label>
			</div>
		</div> 
		<div class="col-md-2 theme-v-border" style="overflow: inherit;">
			<div class="col-12 col-sm-12 form-group focused">
				<input id="AWARD_START_DATE_<?=$award_id?>" name="AWARD_START_DATE[]" type="text" class="form-control date" value="<?=$START_DATE?>">
				<span class="bar"></span> 
				<label for="AWARD_START_DATE_<?=$award_id?>"><?=START_DATE?></label>
			</div>
			
			<div class="col-12 col-sm-12 form-group focused">
				<input id="AWARD_END_DATE_<?=$award_id?>" name="AWARD_END_DATE[]" type="text" class="form-control date" value="<?=$END_DATE?>">
				<span class="bar"></span> 
				<label for="AWARD_END_DATE_<?=$award_id?>"><?=END_DATE?></label>
			</div>
			
			<div class="col-12 col-sm-12 form-group focused">
				<input id="AWARD_ACCEPTED_DATE_<?=$award_id?>" name="AWARD_ACCEPTED_DATE[]" type="text" class="form-control date" value="<?=$ACCEPTED_DATE?>">
				<span class="bar"></span> 
				<label for="AWARD_ACCEPTED_DATE_<?=$award_id?>"><?=ACCEPTED_DATE?></label>
			</div>
		</div> 
		<div class="col-md-2 theme-v-border" style="overflow: inherit;" >
			<div class="col-12 col-sm-12 form-group focused input-group">
				<div class="input-group-prepend">
					<span class="input-group-text" style="padding: 5px 5px;height: 38px;" >$</span>
				</div>
				<input id="AWARD_AWARD_AMOUNT_<?=$award_id?>" name="AWARD_AWARD_AMOUNT[]" type="text" class="form-control text-right" value="<?=$AWARD_AMOUNT?>" onchange="calc_award_net(<?=$award_id?>)" >
				<span class="bar"></span> 
				<label for="AWARD_AWARD_AMOUNT_<?=$award_id?>"><?=AWARD?></label>
			</div>
			
			<div class="col-12 col-sm-12 form-group focused input-group">
				<div class="input-group-prepend">
					<span class="input-group-text" style="padding: 5px 5px;height: 38px;" >$</span>
				</div>
				<input id="AWARD_FEE_AMOUNT_<?=$award_id?>" name="AWARD_FEE_AMOUNT[]" type="text" class="form-control text-right" value="<?=$FEE_AMOUNT?>" onchange="calc_award_net(<?=$award_id?>)" >
				<span class="bar"></span> 
				<label for="AWARD_FEE_AMOUNT_<?=$award_id?>"><?=FEE?></label>
			</div>
			
			<div class="col-12 col-sm-12 form-group focused input-group">
				<div class="input-group-prepend">
					<span class="input-group-text" style="padding: 5px 5px;height: 38px;" >$</span>
				</div>
				<input id="AWARD_NET_AMOUNT_<?=$award_id?>" name="AWARD_NET_AMOUNT[]" type="text" class="form-control text-right" value="<?=$NET_AMOUNT?>" readonly>
				<span class="bar"></span> 
				<label for="AWARD_NET_AMOUNT_<?=$award_id?>"><?=NET?></label>
			</div>
		</div> 
		<div class="col-md-2 theme-v-border" style="overflow: inherit;">
			<div class="col-12 col-sm-12 form-group focused">
				<input id="AWARD_ACADEMIC_YEAR_<?=$award_id?>" name="AWARD_ACADEMIC_YEAR[]" type="text" class="form-control" value="<?=$ACADEMIC_YEAR?>">
				<span class="bar"></span> 
				<label for="AWARD_ACADEMIC_YEAR_<?=$award_id?>"><?=ACADEMIC_YEAR_1?></label>
			</div>
			
			<div class="col-12 col-sm-12 form-group focused">
				<input id="AWARD_AWARD_YEAR_<?=$award_id?>" name="AWARD_AWARD_YEAR[]" type="text" class="form-control" value="<?=$AWARD_YEAR?>">
				<span class="bar"></span> 
				<label for="AWARD_AWARD_YEAR_<?=$award_id?>"><?=AWARD_YEAR?></label>
			</div>
			
			<div class="col-12 col-sm-12 form-group focused">
				<input id="AWARD_LOAN_ID_<?=$award_id?>" name="AWARD_LOAN_ID[]" type="text" class="form-control" value="<?=$LOAN_ID?>">
				<span class="bar"></span> 
				<label for="AWARD_LOAN_ID_<?=$award_id?>"><?=LOAN_ID?></label>
			</div>
		</div> 
		
		<div class="col-md-2 theme-v-border" style="overflow: inherit;" >
			<div class="col-12 col-sm-12 form-group focused">
				<select name="AWARD_PK_LENDER_MASTER[]" id="AWARD_PK_LENDER_MASTER_<?=$award_id?>" class="form-control" >
					<option value=""></option>
					<? $res_dd = $db->Execute("select PK_LENDER_MASTER,LENDER from S_LENDER_MASTER WHERE ACTIVE = '1' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY LENDER ASC ");
					while (!$res_dd->EOF) { ?>
						<option value="<?=$res_dd->fields['PK_LENDER_MASTER']?>" <? if($res_dd->fields['PK_LENDER_MASTER'] == $PK_LENDER_MASTER) echo 'selected = "selected"';?> ><?=$res_dd->fields['LENDER']?></option>
					<?	$res_dd->MoveNext();
					}	?>
				</select>
				<span class="bar"></span> 
				<label for="AWARD_ACADEMIC_YEAR_<?=$award_id?>"><?=LENDER?></label>
			</div>
			
			<div class="col-12 col-sm-12 form-group focused">
				<select name="AWARD_SERVICE[]" id="AWARD_SERVICE_<?=$award_id?>" class="form-control" >
					<option value=""></option>
				</select>
				<span class="bar"></span> 
				<label for="AWARD_SERVICE_<?=$award_id?>"><?=SERVICER?></label>
			</div>
			
			<div class="col-12 col-sm-12 form-group focused">
				<select name="AWARD_PK_GUARANTOR[]" id="AWARD_PK_GUARANTOR_<?=$award_id?>" class="form-control" >
					<option value=""></option>
					<? $res_dd = $db->Execute("select PK_GUARANTOR,ITEM,DESCRIPTION from M_GUARANTOR WHERE ACTIVE = '1' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY ITEM ASC ");
					while (!$res_dd->EOF) { ?>
						<option value="<?=$res_dd->fields['PK_GUARANTOR']?>" <? if($res_dd->fields['PK_GUARANTOR'] == $PK_GUARANTOR) echo 'selected = "selected"';?> ><?=$res_dd->fields['ITEM'].' - '.$res_dd->fields['DESCRIPTION']?></option>
					<?	$res_dd->MoveNext();
					}	?>
				</select>
				<span class="bar"></span> 
				<label for="AWARD_ACADEMIC_YEAR_<?=$award_id?>"><?=GUARANTOR?></label>
			</div>
			
		</div> 
		<div class="col-md-2 theme-v-border" style="overflow: inherit;" >
			<div class="col-12 col-sm-12 form-group focused ">
				<textarea class="form-control" rows="3" name="AWARD_NOTES[]" id="AWARD_NOTES_<?=$award_id?>" ><?=$NOTES?></textarea>
				<span class="bar"></span> 
				<label for="AWARD_NOTES_<?=$award_id?>"><?=NOTES?></label>
			</div>
			
			<a href="javascript:void(0);" onclick="delete_row('<?=$award_id?>','award')" title="<?=DELETE?>" class="btn btn-primary btn-circle"><i class="far fa-trash-alt"></i> </a>
		</div> 
	</div> 
</div>