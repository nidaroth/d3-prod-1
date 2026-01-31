<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/earnings_setup.php");
require_once("../language/menu.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_ACCOUNTING') == 0 ){
	header("location:../index");
	exit;
}
?>
<style>
	.tableFixHead          { overflow-y: auto; height: 500px; }
	.tableFixHead thead th { position: sticky; top: 0; }
	.tableFixHead thead th { background:#E8E8E8; }
</style>
<div class="row">
	<div class="col-9 col-sm-9">
		<div class="tableFixHead" >
			<table class="table table-hover table-striped" id="stud_ledger_table_1" > 
				<thead>
					<tr>
						<th class="sticky_header" scope="col" ><?=YEAR_MONTH?></th>
						<th class="sticky_header" scope="col" ><?=RECORD_COUNT?></th>
						<th class="sticky_header" scope="col" ><?=STATUS?></th>
						<th class="sticky_header" scope="col" ><?=CREATED_ON?></th>
						<th class="sticky_header" scope="col" ><?=CREATED_BY?></th>
					</tr>
				</thead>
				<tbody>
					<? $i = 0;
					$STATUS = "";
					$EARNINGS_YEAR 			= "";
					$EARNINGS_MONTH 		= "";
					$EARNINGS_YEAR_MONTH 	= "";
					$res = $db->Execute("CALL ACCT20012(".$_SESSION['PK_ACCOUNT'].",".$_REQUEST['id'].")");
					while (!$res->EOF) { 
						if($i == 0) {
							$i++;
							$STATUS = $res->fields['STATUS'];
							
							$EARNINGS_YEAR 			= $res->fields['EARNINGS_YEAR'];
							$EARNINGS_MONTH 		= $res->fields['EARNINGS_MONTH'];
							$EARNINGS_YEAR_MONTH 	= $res->fields['EARNINGS_YEAR_MONTH'];
						}?>
					<tr>
						<td ><?=$res->fields['EARNINGS_YEAR_MONTH']?></td>
						<td ><?=$res->fields['RECORD_COUNT']?></td>
						<td ><?=$res->fields['STATUS']?></td>
						<td ><? if($res->fields['ON'] != '' && $res->fields['ON'] != '0000-00-00') echo date("m/d/Y", strtotime($res->fields['ON'])); ?></td>
						<td ><?=$res->fields['BY']?></td>
					</tr>
					<? $res->MoveNext();
					} ?>
				</tbody>
			</table>
		</div>
	</div>
	<div class="col-3 col-sm-3">
		<? if($STATUS == '') {

			// dvb 05 03 2025 - get last month and then fill to current month year
			if(empty($EARNINGS_MONTH)){
				$EARNINGS_MONTH = 1;
			}

			if(empty($EARNINGS_YEAR)){
				$EARNINGS_YEAR = date("Y")-1;
			}

			$months = [
			    1 => 'January', 2 => 'February', 3 => 'March',
			    4 => 'April', 5 => 'May', 6 => 'June',
			    7 => 'July', 8 => 'August', 9 => 'September',
			    10 => 'October', 11 => 'November', 12 => 'December'
			];

			$STATUS 				= "no_data";
			$EARNINGS_YEAR 			= $EARNINGS_YEAR;
			$EARNINGS_MONTH 		= $EARNINGS_MONTH;
			$EARNINGS_YEAR_MONTH	= $EARNINGS_YEAR." ".strtoupper($months[$EARNINGS_MONTH]);
			
			?>
			<button onclick="earning_operation('CALCULATE','<?=$EARNINGS_YEAR?>','<?=$EARNINGS_MONTH?>','<?=$EARNINGS_YEAR_MONTH?>')" type="button" style="width: 166px;" class="btn waves-effect waves-light btn-info"><?=CALCULATE.' '.$EARNINGS_YEAR_MONTH ?></button>
		<?php	
		if($EARNINGS_YEAR == 12){
					$EARNINGS_YEAR = 1;
				}
			// $STATUS 				= "no_data";
			// $EARNINGS_YEAR 			= date("Y");
			// $EARNINGS_MONTH 		= date("m");
			// $EARNINGS_YEAR_MONTH	= date("Y")." ".strtoupper(date("M"));
		}
		
		if(strtolower($STATUS) == "finalized") { 
			if($EARNINGS_MONTH == 1) {
				$NEXT_EARNINGS_YEAR 		= $EARNINGS_YEAR;
				$NEXT_EARNINGS_MONTH 		= 2; 
				$NEXT_EARNINGS_YEAR_MONTH 	= $NEXT_EARNINGS_YEAR.' FEB';
			} else if($EARNINGS_MONTH == 2) {
				$NEXT_EARNINGS_YEAR 		= $EARNINGS_YEAR;
				$NEXT_EARNINGS_MONTH 		= 3; 
				$NEXT_EARNINGS_YEAR_MONTH 	= $NEXT_EARNINGS_YEAR.' MAR';
			} else if($EARNINGS_MONTH == 3) {
				$NEXT_EARNINGS_YEAR 		= $EARNINGS_YEAR;
				$NEXT_EARNINGS_MONTH 		= 4; 
				$NEXT_EARNINGS_YEAR_MONTH 	= $NEXT_EARNINGS_YEAR.' APR';
			} else if($EARNINGS_MONTH == 4) {
				$NEXT_EARNINGS_YEAR 		= $EARNINGS_YEAR;
				$NEXT_EARNINGS_MONTH 		= 5; 
				$NEXT_EARNINGS_YEAR_MONTH 	= $NEXT_EARNINGS_YEAR.' MAY';
			} else if($EARNINGS_MONTH == 5) {
				$NEXT_EARNINGS_YEAR 		= $EARNINGS_YEAR;
				$NEXT_EARNINGS_MONTH 		= 6; 
				$NEXT_EARNINGS_YEAR_MONTH 	= $NEXT_EARNINGS_YEAR.' JUN';
			} else if($EARNINGS_MONTH == 6) {
				$NEXT_EARNINGS_YEAR 		= $EARNINGS_YEAR;
				$NEXT_EARNINGS_MONTH 		= 7; 
				$NEXT_EARNINGS_YEAR_MONTH 	= $NEXT_EARNINGS_YEAR.' JUL';
			} else if($EARNINGS_MONTH == 7) {
				$NEXT_EARNINGS_YEAR 		= $EARNINGS_YEAR;
				$NEXT_EARNINGS_MONTH 		= 8; 
				$NEXT_EARNINGS_YEAR_MONTH 	= $NEXT_EARNINGS_YEAR.' AUG';
			} else if($EARNINGS_MONTH == 8) {
				$NEXT_EARNINGS_YEAR 		= $EARNINGS_YEAR;
				$NEXT_EARNINGS_MONTH 		= 9; 
				$NEXT_EARNINGS_YEAR_MONTH 	= $NEXT_EARNINGS_YEAR.' SEP';
			} else if($EARNINGS_MONTH == 9) {
				$NEXT_EARNINGS_YEAR 		= $EARNINGS_YEAR;
				$NEXT_EARNINGS_MONTH 		= 10; 
				$NEXT_EARNINGS_YEAR_MONTH 	= $NEXT_EARNINGS_YEAR.' OCT';
			} else if($EARNINGS_MONTH == 10) {
				$NEXT_EARNINGS_YEAR 		= $EARNINGS_YEAR;
				$NEXT_EARNINGS_MONTH 		= 11; 
				$NEXT_EARNINGS_YEAR_MONTH 	= $NEXT_EARNINGS_YEAR.' NOV';
			} else if($EARNINGS_MONTH == 11) {
				$NEXT_EARNINGS_YEAR 		= $EARNINGS_YEAR;
				$NEXT_EARNINGS_MONTH 		= 12; 
				$NEXT_EARNINGS_YEAR_MONTH 	= $NEXT_EARNINGS_YEAR.' DEC';
			} else if($EARNINGS_MONTH == 12) {
				$NEXT_EARNINGS_YEAR 		= $EARNINGS_YEAR + 1;
				$NEXT_EARNINGS_MONTH 		= 1; 
				$NEXT_EARNINGS_YEAR_MONTH 	= $NEXT_EARNINGS_YEAR.' JAN';
			} 
			?>
			<button onclick="earning_operation('CALCULATE','<?=$NEXT_EARNINGS_YEAR?>','<?=$NEXT_EARNINGS_MONTH?>','<?=$NEXT_EARNINGS_YEAR_MONTH?>')" type="button" style="width: 166px;" class="btn waves-effect waves-light btn-info"><?=CALCULATE.' '.$NEXT_EARNINGS_YEAR_MONTH ?></button>
			<br /><br />
			
			<!-- onclick="earning_operation('FINALIZE','<?=$NEXT_EARNINGS_YEAR?>','<?=$NEXT_EARNINGS_MONTH?>','<?=$NEXT_EARNINGS_YEAR_MONTH?>')" -->
			<button disabled type="button" style="width: 166px;" class="btn waves-effect waves-light btn-info"><?=FINALIZE ?></button>
			<br /><br />
			
			<button onclick="earning_operation('DELETE','<?=$EARNINGS_YEAR?>','<?=$EARNINGS_MONTH?>','<?=$EARNINGS_YEAR_MONTH?>')" type="button" style="width: 166px;" class="btn waves-effect waves-light btn-info"><?=DELETE.' '.$EARNINGS_YEAR_MONTH ?></button>
		<? } else if($STATUS == 'no_data') { ?>
			<!-- <button onclick="earning_operation('CALCULATE','<?=$EARNINGS_YEAR?>','<?=$EARNINGS_MONTH?>','<?=$EARNINGS_YEAR_MONTH?>')" type="button" style="width: 166px;" class="btn waves-effect waves-light btn-info"><?=CALCULATE.' '.$EARNINGS_YEAR_MONTH ?></button> -->
		
		<? } else if($STATUS != '') { ?>
			<button onclick="earning_operation('CALCULATE','<?=$EARNINGS_YEAR?>','<?=$EARNINGS_MONTH?>','<?=$EARNINGS_YEAR_MONTH?>')" type="button" style="width: 166px;" class="btn waves-effect waves-light btn-info"><?=CALCULATE.' '.$EARNINGS_YEAR_MONTH ?></button>
			<br /><br />
			
			<button onclick="earning_operation('FINALIZE','<?=$EARNINGS_YEAR?>','<?=$EARNINGS_MONTH?>','<?=$EARNINGS_YEAR_MONTH?>')" type="button" style="width: 166px;" class="btn waves-effect waves-light btn-info"><?=FINALIZE.' '.$EARNINGS_YEAR_MONTH ?></button>
			<br /><br />
			
			<button onclick="earning_operation('DELETE','<?=$EARNINGS_YEAR?>','<?=$EARNINGS_MONTH?>','<?=$EARNINGS_YEAR_MONTH?>')" type="button" style="width: 166px;" class="btn waves-effect waves-light btn-info"><?=DELETE.' '.$EARNINGS_YEAR_MONTH ?></button>
		<? } ?>
	</div>
</div>