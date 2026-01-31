<?php require_once('../global/config.php'); 
require_once("../language/common.php");
require_once("../language/batch_payment.php");
if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || ($_SESSION['PK_ROLES'] != 2 && $_SESSION['PK_ROLES'] != 3)){ 
	header("location:../index");
	exit;
} ?>
<? $s_field = $_REQUEST['field'];
$s_order = $_REQUEST['order'];

if($_REQUEST['field'] == '') {
	//$_REQUEST['field'] = ' COMPLETED ASC, TASK_DATE DESC ';
	$_REQUEST['field'] = "  CONCAT(LAST_NAME,', ',FIRST_NAME) ASC, DISBURSEMENT_DATE ASC ";
}

if($_REQUEST['table_id123'] != ''){
	$table_id = $_REQUEST['table_id123'];
} else {
	if($_REQUEST['add_stud'] == 1)
		$table_id = "table_unpaid_1";
	else
		$table_id = "table_unpaid";
}

// DIAM - 88, 68
$SEARCH = isset($_REQUEST['show_search']) ? mysql_real_escape_string($_REQUEST['show_search']) : '';

$search_data = "";
if($SEARCH != '') {
	
	$search_data = " AND (STUDENT_ID like '%$SEARCH%' OR CONCAT(S_STUDENT_MASTER.LAST_NAME,', ', S_STUDENT_MASTER.FIRST_NAME) like '%$SEARCH%' OR S_STUDENT_MASTER.SSN like '%$SEARCH%' )";
	
} 
// End DIAM - 88, 68


if($_REQUEST['disb_id'] == '') { ?>
<table class="table-striped table table-hover table-bordered table-batch" id="<?=$table_id?>" >
	<thead style="position: sticky;top: 0;z-index: 9;" >
		<tr>
			<? if($_REQUEST['BID'] != ''){ ?>
				<th class="sticky_header" scope="col" ><?=ACTION ?></th>
			<? } else { ?>
				<th class="sticky_header" scope="col" ><?=SELECT ?></th>
			<? } ?>
			<th class="sticky_header" scope="col" ><?=NAME?></th>
			<th class="sticky_header" scope="col" ><?=STUDENT_ID?></th>
			<th class="sticky_header" scope="col" ><?=LEDGER_CODE?></th>
			<th class="sticky_header" scope="col" ><?=DISBURSEMENT_DATE?></th>
			<th class="sticky_header" scope="col" ><?=TRANSACTION_DATE?></th>
			<th class="sticky_header" scope="col" ><div style="text-align:right"><?=DISBURSEMENT_AMOUNT.'<br />(Credits)'?></div></th>
			<th class="sticky_header" scope="col" ><?=BATCH_DETAIL?></th>
			<th class="sticky_header" scope="col" ><?=PAYMENT_TYPE?></th>
			<th class="sticky_header" scope="col" ><?=AY_1?></th>
			<th class="sticky_header" scope="col" ><?=AP_1?></th>
			<th class="sticky_header" scope="col" ><?=CHECK_NO?></th>
			<th class="sticky_header" scope="col" ><?=RECEIPT_NO?></th>
			<th class="sticky_header" scope="col" ><?=STATUS?></th>
			<th class="sticky_header" scope="col" ><?=ENROLLMENT?></th>
			<th class="sticky_header" scope="col" ><?=TERM_BLOCK?></th>
			<th class="sticky_header" scope="col" ><?=PRIOR_YEAR?></th>
			<th class="sticky_header" scope="col" ><?=MESSAGE?></th>
			<th class="sticky_header" scope="col" ><?=OPTION?></th>
		</tr>
	</thead>
	<tbody id="table-header-batch">
<? } ?>	
		<? $disp_cond = "";
		if($_REQUEST['disb_id'] != '') {
			if($_SESSION['DISB_ID'] != '')
				$ses_disb = $_SESSION['DISB_ID'].',';
				
			$_SESSION['DISB_ID'] = $ses_disb.$_REQUEST['disb_id'];
			
			$disp_cond .= " AND S_STUDENT_DISBURSEMENT.PK_STUDENT_DISBURSEMENT IN ($_REQUEST[disb_id]) ";	
		} else if($_REQUEST['BID'] == '')
			$disp_cond .= " AND S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE IN ($_REQUEST[ledger]) AND S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE > 0 ";

		$PK_CAMPUS		= $_REQUEST['campus_id'];
		$campus_cond  	= "";
		if($PK_CAMPUS != ''){ 
			$campus_cond  = " AND S_STUDENT_CAMPUS.PK_CAMPUS IN ($PK_CAMPUS) ";
		}
		
		$FROM_DATE 	= '';
		$END_DATE 	= '';
		if($_REQUEST['FROM_DATE'] != '')
			$FROM_DATE = date("Y-m-d",strtotime($_REQUEST['FROM_DATE']));
		
		if($_REQUEST['END_DATE'] != '')
			$END_DATE = date("Y-m-d",strtotime($_REQUEST['END_DATE']));

		if($FROM_DATE != '' && $END_DATE != '') {
			$disp_cond .= " AND DISBURSEMENT_DATE BETWEEN '$FROM_DATE' AND '$END_DATE' ";
		} else if($FROM_DATE != '') {
			$disp_cond .= " AND DISBURSEMENT_DATE >= '$FROM_DATE' ";
		} else if($END_DATE != '') {
			$disp_cond .= " AND DISBURSEMENT_DATE <= '$END_DATE' ";
		}
			
		$table = "";
		if($_REQUEST['BID'] != ''){
			$table = ", S_PAYMENT_BATCH_DETAIL";
			$disp_cond .= " AND PK_PAYMENT_BATCH_MASTER = '$_REQUEST[BID]' AND S_STUDENT_DISBURSEMENT.PK_STUDENT_DISBURSEMENT = S_PAYMENT_BATCH_DETAIL.PK_STUDENT_DISBURSEMENT AND PK_DISBURSEMENT_STATUS IN (2,3) "; 
			$group_by 	= " GROUP BY S_PAYMENT_BATCH_DETAIL.PK_PAYMENT_BATCH_DETAIL ";
			$field 		= ", BATCH_TRANSACTION_DATE, RECEIPT_NO ";
		} else {
			$disp_cond .= " AND S_STUDENT_DISBURSEMENT.PK_PAYMENT_BATCH_DETAIL = 0 AND PK_DISBURSEMENT_STATUS IN (2) ";
			
			if($_REQUEST['disb_id'] != '') {
				$group_by = "";
			} else
				$group_by = " GROUP BY S_STUDENT_DISBURSEMENT.PK_STUDENT_DISBURSEMENT ";
		}
		
		$query = "select S_STUDENT_DISBURSEMENT.PK_STUDENT_DISBURSEMENT,CONCAT(LAST_NAME,', ',FIRST_NAME) AS NAME, STUDENT_ID, STUDENT_STATUS, M_CAMPUS_PROGRAM.CODE, DISBURSEMENT_AMOUNT, S_STUDENT_DISBURSEMENT.PK_STUDENT_MASTER, ENROLLMENT_PK_TERM_BLOCK, PK_DETAIL_TYPE, DETAIL,S_STUDENT_DISBURSEMENT.PK_STUDENT_ENROLLMENT, S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE, M_AR_LEDGER_CODE.CODE as LEDGER_CODE, DISBURSEMENT_DATE, ACADEMIC_YEAR, ACADEMIC_PERIOD, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, CAMPUS_CODE $field 
		from 
		S_STUDENT_DISBURSEMENT, M_AR_LEDGER_CODE $table , S_STUDENT_MASTER, S_STUDENT_ACADEMICS,  
		S_STUDENT_ENROLLMENT 
		LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT 
		LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS 
		LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER 
		LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS 
		LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM = S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM 
		WHERE 
		S_STUDENT_DISBURSEMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
		S_STUDENT_DISBURSEMENT.PK_AR_LEDGER_CODE = M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE AND 
		S_STUDENT_MASTER.PK_STUDENT_MASTER = S_STUDENT_DISBURSEMENT.PK_STUDENT_MASTER AND 
		S_STUDENT_ACADEMICS.PK_STUDENT_MASTER = S_STUDENT_MASTER.PK_STUDENT_MASTER AND 
		S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT = S_STUDENT_DISBURSEMENT.PK_STUDENT_ENROLLMENT $campus_cond  $disp_cond $search_data $group_by ORDER BY CONCAT(LAST_NAME,', ',FIRST_NAME) ASC, DISBURSEMENT_DATE ASC, M_AR_LEDGER_CODE.CODE ASC ";
		$res_stu = $db->Execute($query);

		$EN_ARR  = array();
		$LED_ARR = array();
		$PK_STUDENT_ENROLLMENT_123 = '';
		
		while (!$res_stu->EOF) { 
		
			$PK_STUDENT_ENROLLMENT_123   = $res_stu->fields['PK_STUDENT_ENROLLMENT'];
			$PK_PAYMENT_BATCH_DETAIL	 = $res_stu->fields['PK_PAYMENT_BATCH_DETAIL'];
			$PK_STUDENT_DISBURSEMENT	 = $res_stu->fields['PK_STUDENT_DISBURSEMENT'];
			$PK_TERM_BLOCK	 			 = $res_stu->fields['ENROLLMENT_PK_TERM_BLOCK'];
			$BATCH_TRANSACTION_DATE	 	 = $res_stu->fields['BATCH_TRANSACTION_DATE'];
			$PK_AR_LEDGER_CODE11 		 = $res_stu->fields['PK_AR_LEDGER_CODE']; 
			$PK_STUDENT_MASTER           = $res_stu->fields['PK_STUDENT_MASTER']; 
			
			if($BATCH_TRANSACTION_DATE != '' && $BATCH_TRANSACTION_DATE != '0000-00-00')
				$BATCH_TRANSACTION_DATE = date("m/d/Y",strtotime($BATCH_TRANSACTION_DATE));
			else
				$BATCH_TRANSACTION_DATE = '';
			
			$PK_DETAIL_TYPE	 			 = $res_stu->fields['PK_DETAIL_TYPE'];
			$DISBURSEMENT_DETAIL	 	 = $res_stu->fields['DETAIL'];
			if($PK_DETAIL_TYPE != 4)
				$DISBURSEMENT_DETAIL = 0;
				
			$checked 					 = '';
			$DISBURSEMENT_style 	 	 = "display:none;";
			$PK_BATCH_PAYMENT_STATUS	 = 1;
			if($_REQUEST['BID'] != ''){
				$res_selected = $db->Execute("SELECT PK_PAYMENT_BATCH_DETAIL,PK_STUDENT_ENROLLMENT,RECEIVED_AMOUNT,DISBURSEMENT_TYPE,PRIOR_YEAR, PK_TERM_BLOCK, CHECK_NO,PK_BATCH_PAYMENT_STATUS, BATCH_DETAIL_DESCRIPTION FROM S_PAYMENT_BATCH_DETAIL WHERE PK_PAYMENT_BATCH_MASTER = '$_REQUEST[BID]' AND PK_STUDENT_DISBURSEMENT = '$PK_STUDENT_DISBURSEMENT' ");
					
				$PK_STUDENT_ENROLLMENT_123  = $res_selected->fields['PK_STUDENT_ENROLLMENT'];
				$PRIOR_YEAR	 			 	= $res_selected->fields['PRIOR_YEAR'];
				$PK_TERM_BLOCK	 		 	= $res_selected->fields['PK_TERM_BLOCK'];
				$PK_PAYMENT_BATCH_DETAIL 	= $res_selected->fields['PK_PAYMENT_BATCH_DETAIL'];
				$PAID_AMOUNT 			 	= $res_selected->fields['RECEIVED_AMOUNT'];
				$STUD_CHECK_NO 			 	= $res_selected->fields['CHECK_NO'];
				$PK_BATCH_PAYMENT_STATUS 	= $res_selected->fields['PK_BATCH_PAYMENT_STATUS'];
				$BATCH_DETAIL_DESCRIPTION	= $res_selected->fields['BATCH_DETAIL_DESCRIPTION'];
				$DISBURSEMENT_TYPE			= $res_selected->fields['DISBURSEMENT_TYPE'];
			
				if($res_selected->RecordCount() > 0) {
					$checked = 'checked';
					
					if($res_stu->fields['DISBURSEMENT_AMOUNT'] != $PAID_AMOUNT)
						$DISBURSEMENT_style = "";
				}
				
			} else {
				$PAID_AMOUNT 		 	= $res_stu->fields['DISBURSEMENT_AMOUNT']; 
				$PK_AR_LEDGER_CODE11 	= $res_stu->fields['PK_AR_LEDGER_CODE']; 
				//$BATCH_TRANSACTION_DATE = '';
				
				$DISBURSEMENT_TYPE			 = 0;
				$PRIOR_YEAR					 = 2;
				$BATCH_TRANSACTION_DATE 	 = $_REQUEST['batch_date'];
				$STUD_CHECK_NO				 = $_REQUEST['batch_check_no'];
				
				$res_ledger = $db->Execute("SELECT LEDGER_DESCRIPTION FROM M_AR_LEDGER_CODE WHERE PK_AR_LEDGER_CODE = '$PK_AR_LEDGER_CODE11' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
				$BATCH_DETAIL_DESCRIPTION = $res_ledger->fields['LEDGER_DESCRIPTION'];
			} 
			
			if($_REQUEST['disb_id'] != '')
				$checked = 'checked'; ?>
			<tr id="TR_PK_STUDENT_DISBURSEMENT_<?=$PK_STUDENT_DISBURSEMENT?>"  >
				<td>
					<div style="width:50px">
					<? if($_REQUEST['BID'] != ''){ ?>
						<a href="javascript:void(0);" onclick="delete_batch_detail('<?=$PK_PAYMENT_BATCH_DETAIL ?>','<?=$PK_STUDENT_DISBURSEMENT ?>')" title="<?=DELETE?>" class="btn delete-color btn-circle" style="width:25px; height:25px; padding: 2px;"><i class="far fa-trash-alt"></i> </a>
						
						<input type="hidden" name="PK_STUDENT_DISBURSEMENT[]" id="PK_STUDENT_DISBURSEMENT_<?=$PK_STUDENT_DISBURSEMENT?>" value='<?=$PK_STUDENT_DISBURSEMENT ?>' />
					<? } else { ?>
						<input type="checkbox" class="delete_if_not_selected" name="PK_STUDENT_DISBURSEMENT[]" id="PK_STUDENT_DISBURSEMENT_<?=$PK_STUDENT_DISBURSEMENT?>" value="<?=$PK_STUDENT_DISBURSEMENT?>" onchange="calc_total(1);set_required(<?=$PK_STUDENT_DISBURSEMENT?>);" <?=$checked?> /> <!-- DIAM - 88, remove enable_button(); -->
					<? } ?>
					</div>
					
					<input type="hidden" name="PK_PAYMENT_BATCH_DETAIL_<?=$PK_STUDENT_DISBURSEMENT?>" id="PK_PAYMENT_BATCH_DETAIL_<?=$PK_STUDENT_DISBURSEMENT?>" value='<?=$PK_PAYMENT_BATCH_DETAIL ?>' />
				</td>
				<td>
					<div style="width:150px">
						<?=$res_stu->fields['NAME']?>
					</div>
				</td>
				<td><div style="width:130px"><?=$res_stu->fields['STUDENT_ID']?></div></td>
				<td><div style="width:100px"><?=$res_stu->fields['LEDGER_CODE']?></div></td>
				<td >
					<div style="width:100px">
					<? if($res_stu->fields['DISBURSEMENT_DATE'] != '0000-00-00')
						echo date("m/d/Y",strtotime($res_stu->fields['DISBURSEMENT_DATE'])); 
					?>
					<input type="hidden" id="DISBURSEMENT_DT_<?=$PK_STUDENT_DISBURSEMENT?>" name="DISBURSEMENT_DT_<?=$PK_STUDENT_DISBURSEMENT?>" value="<?=date("m/d/Y",strtotime($res_stu->fields['DISBURSEMENT_DATE']));?>">
					</div>
				</td>
				<td>
					<input type="text" class="form-control date validate-date required-entry TRANSACTION_DATE nullable_on_batch_select" id="BATCH_TRANSACTION_DATE_<?=$PK_STUDENT_DISBURSEMENT?>" name="BATCH_TRANSACTION_DATE_<?=$PK_STUDENT_DISBURSEMENT?>" value="<?=$BATCH_TRANSACTION_DATE?>" style="width:100px"  />
				</td>
				<td>
					<input type="hidden" id="DISBURSEMENT_AMOUNT_<?=$PK_STUDENT_DISBURSEMENT?>" name="DISBURSEMENT_AMOUNT_<?=$PK_STUDENT_DISBURSEMENT?>" value="<?=$res_stu->fields['DISBURSEMENT_AMOUNT']?>" />
					<input type="text" class="form-control <? if($checked != ''){ ?> required-entry <? } ?> " placeholder="" name="PAID_AMOUNT_<?=$PK_STUDENT_DISBURSEMENT?>" id="PAID_AMOUNT_<?=$PK_STUDENT_DISBURSEMENT?>" paid-amt="<?=$PAID_AMOUNT?>" value="<?=$PAID_AMOUNT?>" onchange="calc_total(1);paid_amount_value_change(<?=$PK_STUDENT_DISBURSEMENT?>);check_number_validation(this);" <? if($DISBURSEMENT_TYPE != 1) { ?> readonly <? } ?> style="text-align:right;width:80px" />
				</td>
				<td>
					<div style="width:150px">
						<? echo $res_stu->fields['CODE'].' - '.$res_stu->fields['BEGIN_DATE_1'] ?>
					</div>
				</td>
				<th >
					<select id="DISBURSEMENT_DETAIL_<?=$PK_STUDENT_DISBURSEMENT?>" name="DISBURSEMENT_DETAIL_<?=$PK_STUDENT_DISBURSEMENT?>" class="form-control" style="width:120px;" >
						<option value="" ></option>
						<? $res_type = $db->Execute("select PK_AR_PAYMENT_TYPE,AR_PAYMENT_TYPE from M_AR_PAYMENT_TYPE WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY AR_PAYMENT_TYPE ASC");
						while (!$res_type->EOF) { ?>
							<option value="<?=$res_type->fields['PK_AR_PAYMENT_TYPE']?>" <? if($res_type->fields['PK_AR_PAYMENT_TYPE'] == $DISBURSEMENT_DETAIL) echo "selected"; ?> ><?=$res_type->fields['AR_PAYMENT_TYPE'] ?></option>
						<?	$res_type->MoveNext();
						} ?>
					</select>
				</th>
				<td><div style="width:30px"><?=$res_stu->fields['ACADEMIC_YEAR']?></div></td>
				<td><div style="width:30px"><?=$res_stu->fields['ACADEMIC_PERIOD']?></div></td>
				<td>
					<input type="text" class="form-control STUD_CHECK_NO" placeholder="" name="STUD_CHECK_NO_<?=$PK_STUDENT_DISBURSEMENT?>" id="STUD_CHECK_NO_<?=$PK_STUDENT_DISBURSEMENT?>" value="<?=$STUD_CHECK_NO?>" style="width:80px"  />
				</td>
				<td><div style="width:80px" ></div></td>
				<td>
					<? $res_b_status = $db->Execute("SELECT BATCH_PAYMENT_STATUS FROM M_BATCH_PAYMENT_STATUS WHERE PK_BATCH_PAYMENT_STATUS = '$PK_BATCH_PAYMENT_STATUS'  ");
					echo $res_b_status->fields['BATCH_PAYMENT_STATUS']; ?>
				</td>
				<td > 
					<!-- DIAM - 737 -->
					<select id="DISBURSEMENT_PK_ENROLLMENT_<?=$PK_STUDENT_DISBURSEMENT?>" name="DISBURSEMENT_PK_ENROLLMENT_<?=$PK_STUDENT_DISBURSEMENT?>" class="form-control" style="width:200px;"  >
					<?
						$res_type = $db->Execute("SELECT S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, STATUS_DATE, STUDENT_STATUS, CODE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, IS_ACTIVE_ENROLLMENT, CAMPUS_CODE FROM S_STUDENT_ENROLLMENT LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
						while (!$res_type->EOF) { ?>
							<option value="<?=$res_type->fields['PK_STUDENT_ENROLLMENT']?>" <? if($res_type->fields['PK_STUDENT_ENROLLMENT'] == $PK_STUDENT_ENROLLMENT_123) echo "selected"; ?> <? if($res_type->fields['IS_ACTIVE_ENROLLMENT'] == 1) echo "class='option_red'";  ?> ><?=$res_type->fields['BEGIN_DATE_1'].' - '.$res_type->fields['CODE'].' - '.$res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['CAMPUS_CODE']?></option>
						<?	$res_type->MoveNext(); 
						}	?>
					</select>
					<!-- End DIAM - 737 -->
					<!-- <div style="width:200px">
						<? //echo $res_stu->fields['BEGIN_DATE_1'].' - '.$res_stu->fields['CODE'].' - '.$res_stu->fields['STUDENT_STATUS'].' - '.$res_stu->fields['CAMPUS_CODE']; ?>
					</div> -->
				</td>
				<td>
					<select id="PK_TERM_BLOCK_<?=$PK_STUDENT_DISBURSEMENT?>" name="PK_TERM_BLOCK_<?=$PK_STUDENT_DISBURSEMENT?>" class="form-control" style="width:110px" >
						<option></option>
						<? $res_type = $db->Execute("select PK_TERM_BLOCK,IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, DESCRIPTION from S_TERM_BLOCK WHERE ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  order by BEGIN_DATE DESC");
						while (!$res_type->EOF) { ?>
							<option value="<?=$res_type->fields['PK_TERM_BLOCK']?>" <? if($PK_TERM_BLOCK == $res_type->fields['PK_TERM_BLOCK']) echo "selected"; ?> ><?=$res_type->fields['BEGIN_DATE_1'].' - '.$res_type->fields['DESCRIPTION']?></option>
						<?	$res_type->MoveNext();
						} ?>
					</select>
				</td>
				<td>
					<select id="PRIOR_YEAR_<?=$PK_STUDENT_DISBURSEMENT?>" name="PRIOR_YEAR_<?=$PK_STUDENT_DISBURSEMENT?>" class="form-control required-entry" style="width:50px" >
						<option ></option>
						<option value="1" <? if($PRIOR_YEAR == 1) echo "selected"; ?> >Yes</option>
						<option value="2" <? if($PRIOR_YEAR == 2) echo "selected"; ?> >No</option>
					</select>
				</td>
				
				<td>
					<div style="width:60px" id="DISBURSEMENT_TYPE_DIV_<?=$PK_STUDENT_DISBURSEMENT?>" > <? if($DISBURSEMENT_TYPE == 1) { echo "Split"; } elseif($DISBURSEMENT_TYPE == 2) { echo "Adjust"; } ?></div>
				</td>
				
				<td>
					<div id="DISBURSEMENT_TYPE_DIV_<?=$PK_STUDENT_DISBURSEMENT?>" >
						<input type="hidden" name="DISBURSEMENT_TYPE_<?=$PK_STUDENT_DISBURSEMENT?>" id="DISBURSEMENT_TYPE_<?=$PK_STUDENT_DISBURSEMENT?>" value="<?=$DISBURSEMENT_TYPE?>" />
						<a href="javascript:void(0)" id="adjust_<?=$PK_STUDENT_DISBURSEMENT?>" 
							<?php  
								if(in_array($DISBURSEMENT_TYPE , [1,2])) {
									echo "style='color:rgb(126, 125, 125)' ";
								}else{

								echo " onclick='adjust_disbursement($PK_STUDENT_DISBURSEMENT,`adjust`)'" ;
								}
							?> > Adjust </a>
						<br>
						<a href="javascript:void(0)" id="split_<?=$PK_STUDENT_DISBURSEMENT?>" 
							<?php  
								if(in_array($DISBURSEMENT_TYPE , [1,2])) {
									echo "style='color:rgb(126, 125, 125)' ";
								}else{

								echo " onclick='adjust_disbursement($PK_STUDENT_DISBURSEMENT,`split`)'" ;
								} 
							?> > Split </a>
					</div>
				</td>
			</tr>	
		<? $res_stu->MoveNext();
		} ?>
		
		<? if($_REQUEST['disb_id'] == '') { ?>
		</tbody>
</table>
<? } ?>