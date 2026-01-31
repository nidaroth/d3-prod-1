<? require_once("../global/config.php"); 
require_once("../language/program.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == ''){ 
	header("location:../index");
	exit;
} 
$PK_CAMPUS_PROGRAM_AY  	= $_REQUEST['PK_CAMPUS_PROGRAM_AY'];
$ay_id 					= $_REQUEST['ay_id']; 

if($PK_CAMPUS_PROGRAM_AY == '') {
	$ACADEMIC_YEAR 	= '';
	$PERIOD 	 	= '';
	$MONTHS 	 	= '';
	$WEEKS 	 		= '';
	$UNITS 	 		= '';
	$HOUR 	 	 	= '';
	$FA_UNITS 	 	= '';
	$ACTIVE 	 	= 1;
} else {
	$res_dd = $db->Execute("select * FROM M_CAMPUS_PROGRAM_AY WHERE PK_CAMPUS_PROGRAM_AY = '$PK_CAMPUS_PROGRAM_AY' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	$ACADEMIC_YEAR 	= $res_dd->fields['ACADEMIC_YEAR'];
	$PERIOD   		= $res_dd->fields['PERIOD'];
	$MONTHS   		= $res_dd->fields['MONTHS'];
	$WEEKS   		= $res_dd->fields['WEEKS'];
	$UNITS   		= $res_dd->fields['UNITS'];
	$HOUR   		= $res_dd->fields['HOUR'];
	$FA_UNITS   	= $res_dd->fields['FA_UNITS'];
	$ACTIVE 	 	= $res_dd->fields['ACTIVE'];
}
?>
<div class="row" id="ay_div_<?=$ay_id?>" >
	<input type="hidden" name="PK_CAMPUS_PROGRAM_AY[]" value="<?=$PK_CAMPUS_PROGRAM_AY?>" />
	<input type="hidden" name="ay_id[]" value="<?=$ay_id?>" />
	<div class="col-sm-2">
		<input type="text" class="form-control" placeholder="" name="AY_ACADEMIC_YEAR[]" id="ACADEMIC_YEAR_<?=$ay_id?>" value="<?=$ACADEMIC_YEAR?>" />
	</div>
	<div class="col-sm-2">
		<input type="text" class="form-control" placeholder="" name="AY_PERIOD[]" id="PERIOD_<?=$ay_id?>" value="<?=$PERIOD?>" onchange="calc_ay_total()" /> <!-- Ticket # 1245  -->
	</div>
	<div class="col-sm-1">
		<input type="text" class="form-control" placeholder="" name="AY_MONTHS[]" id="MONTHS_<?=$ay_id?>" value="<?=$MONTHS?>" onchange="calc_ay_total()" /> <!-- Ticket # 1245  -->
	</div>
	<div class="col-sm-1">
		<input type="text" class="form-control" placeholder="" name="AY_WEEKS[]" id="WEEKS_<?=$ay_id?>" value="<?=$WEEKS?>" onchange="calc_ay_total()" /> <!-- Ticket # 1245  -->
	</div>
	<div class="col-sm-1">
		<input type="text" class="form-control" placeholder="" name="AY_UNITS[]" id="UNITS_<?=$ay_id?>" value="<?=$UNITS?>" onchange="calc_ay_total()" /> <!-- Ticket # 1245  -->
	</div>
	<div class="col-sm-1">
		<input type="text" class="form-control" placeholder="" name="AY_HOUR[]" id="HOUR_<?=$ay_id?>" value="<?=$HOUR?>" onchange="calc_ay_total()" /> <!-- Ticket # 1245  -->
	</div>
	<div class="col-sm-1">
		<input type="text" class="form-control" placeholder="" name="AY_FA_UNITS[]" id="FA_UNITS_<?=$ay_id?>" value="<?=$FA_UNITS?>" onchange="calc_ay_total()" /> <!-- Ticket # 1245  -->
	</div>
	<div class="col-sm-1">
		<div class="d-flex">
			<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
				<input type="checkbox" class="custom-control-input" id="AY_ACTIVE_<?=$ay_id?>" name="AY_ACTIVE_<?=$ay_id?>" value="1" <? if($ACTIVE == 1) echo "checked"; ?> >
				<label class="custom-control-label" for="AY_ACTIVE_<?=$ay_id?>"><?=YES?></label>
			</div>
		</div>
	</div>
	<div class="col-sm-2">
		<a href="javascript:void(0)" onclick="delete_row(<?=$ay_id?>,'AY')" class="btn delete-color btn-circle" ><i class="far fa-trash-alt"></i></a>
	</div>
</div>