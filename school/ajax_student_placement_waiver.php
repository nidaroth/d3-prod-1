<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/student.php"); 

$PK_STUDENT_PLACEMENT_WAIVER 	= $_REQUEST['PK_STUDENT_PLACEMENT_WAIVER'];
$placement_waiver_count			= $_REQUEST['placement_waiver_count'];

if($PK_STUDENT_PLACEMENT_WAIVER == '') {
	$OTHER_SCHOOL_PK_EDUCATION_TYPE 		= '';
	$OTHER_SCHOOL_GRADUATED 				= '';
	$OTHER_SCHOOL_GRADUATED_DATE			= '';
	$OTHER_SCHOOL_TRANSCRIPT_REQUESTED 		= '';
	$OTHER_SCHOOL_TRANSCRIPT_REQUESTED_DATE	= '';
	$OTHER_SCHOOL_TRANSCRIPT_RECEIVED 		= '';
	$OTHER_SCHOOL_TRANSCRIPT_RECEIVED_DATE 	= '';
	$OTHER_SCHOOL_SCHOOL_NAME 				= '';
	$OTHER_SCHOOL_ADDRESS 					= '';
	$OTHER_SCHOOL_ADDRESS_1 				= '';
	$OTHER_SCHOOL_CITY 						= '';
	$OTHER_SCHOOL_PK_STATE 					= '';
	$OTHER_SCHOOL_ZIP 						= '';
} else {
	$res_11 = $db->Execute("select * from S_STUDENT_OTHER_EDU WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_OTHER_EDU = '$PK_STUDENT_OTHER_EDU' ");


	$OTHER_SCHOOL_PK_EDUCATION_TYPE 		= $res_11->fields['PK_EDUCATION_TYPE'];
	$OTHER_SCHOOL_GRADUATED 				= $res_11->fields['GRADUATED'];
	$OTHER_SCHOOL_GRADUATED_DATE			= $res_11->fields['GRADUATED_DATE'];
	$OTHER_SCHOOL_TRANSCRIPT_REQUESTED 		= $res_11->fields['TRANSCRIPT_REQUESTED'];
	$OTHER_SCHOOL_TRANSCRIPT_REQUESTED_DATE	= $res_11->fields['TRANSCRIPT_REQUESTED_DATE'];
	$OTHER_SCHOOL_TRANSCRIPT_RECEIVED 		= $res_11->fields['TRANSCRIPT_RECEIVED'];
	$OTHER_SCHOOL_TRANSCRIPT_RECEIVED_DATE 	= $res_11->fields['TRANSCRIPT_RECEIVED_DATE'];
	$OTHER_SCHOOL_SCHOOL_NAME 				= $res_11->fields['SCHOOL_NAME'];
	$OTHER_SCHOOL_ADDRESS 					= $res_11->fields['ADDRESS'];
	$OTHER_SCHOOL_ADDRESS_1 				= $res_11->fields['ADDRESS_1'];
	$OTHER_SCHOOL_CITY 						= $res_11->fields['CITY'];
	$OTHER_SCHOOL_PK_STATE 					= $res_11->fields['PK_STATE'];
	$OTHER_SCHOOL_ZIP 						= $res_11->fields['ZIP'];
	
	if($OTHER_SCHOOL_GRADUATED_DATE == '0000-00-00')
		$OTHER_SCHOOL_GRADUATED_DATE = '';
	else
		$OTHER_SCHOOL_GRADUATED_DATE = date("m/d/Y",strtotime($OTHER_SCHOOL_GRADUATED_DATE));
	
	if($OTHER_SCHOOL_TRANSCRIPT_REQUESTED_DATE == '0000-00-00')
		$OTHER_SCHOOL_TRANSCRIPT_REQUESTED_DATE = '';
	else
		$OTHER_SCHOOL_TRANSCRIPT_REQUESTED_DATE = date("m/d/Y",strtotime($OTHER_SCHOOL_TRANSCRIPT_REQUESTED_DATE));
		
	if($OTHER_SCHOOL_TRANSCRIPT_RECEIVED_DATE == '0000-00-00')
		$OTHER_SCHOOL_TRANSCRIPT_RECEIVED_DATE = '';
	else
		$OTHER_SCHOOL_TRANSCRIPT_RECEIVED_DATE = date("m/d/Y",strtotime($OTHER_SCHOOL_TRANSCRIPT_RECEIVED_DATE));
} ?>
<div id="placement_waiver_div_<?=$placement_waiver_count?>" >
	<input type="hidden" name="PLACEMENT_WAIVER_HID[]" value="<?=$placement_waiver_count?>" >
	<input type="hidden" name="PK_STUDENT_OTHER_EDU[]" value="<?=$PK_STUDENT_OTHER_EDU?>" >
	<div class="row">
		<div class="col-sm-6 ">
			<div><h5><b>Post Secondary</b></h5></div>
			<div class="d-flex">
				<div class="col-12 col-sm-6 form-group focused">
					<select id="PK_ENROLLMENT<?=$other_eduction_count?>" name="OTHER_SCHOOL_PK_EDUCATION_TYPE[]" class="form-control" onchange="change_other_school_label(this.value,<?=$other_eduction_count?>)" >
						<option ></option>
						<? $res_dd = $db->Execute("select * from M_EDUCATION_TYPE WHERE ACTIVE = '1'  ORDER BY EDUCATION_TYPE ASC ");
						while (!$res_dd->EOF) { ?>
							<option value="<?=$res_dd->fields['PK_EDUCATION_TYPE']?>" <? if($res_dd->fields['PK_EDUCATION_TYPE'] == $OTHER_SCHOOL_PK_EDUCATION_TYPE) echo 'selected = "selected"';?> ><?=$res_dd->fields['EDUCATION_TYPE'].' - '.$res_dd->fields['DESCRIPTION']?></option>
						<?	$res_dd->MoveNext();
						}	?>
					</select>
					<span class="bar"></span> 
					<label for="OTHER_SCHOOL_PK_EDUCATION_TYPE_<?=$other_eduction_count?>"><?=EDUCATION_TYPE?></label>
				</div>
			</div>
			
			<div class="d-flex">
				<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
					<input type="checkbox" class="custom-control-input" id="OTHER_SCHOOL_GRADUATED_<?=$other_eduction_count?>" name="OTHER_SCHOOL_GRADUATED_<?=$other_eduction_count?>" value="1" <? if($OTHER_SCHOOL_GRADUATED == 1) echo "checked"; ?> onclick="enable_other_school_field('<?=$other_eduction_count?>','OTHER_SCHOOL_GRADUATED')" >
					<label class="custom-control-label" for="OTHER_SCHOOL_GRADUATED_<?=$other_eduction_count?>" id="OTHER_SCHOOL_GRADUATED_LBL_<?=$other_eduction_count?>"><? if($OTHER_SCHOOL_PK_EDUCATION_TYPE == 1 || $OTHER_SCHOOL_PK_EDUCATION_TYPE == 3) echo PASSED; else echo GRADUATED;?></label>
				</div>
				<div class="col-12 col-sm-4 form-group focused">
					<? $disabled = "disabled";
					if($OTHER_SCHOOL_GRADUATED == 1)
						$disabled = ""; ?>
					<input id="OTHER_SCHOOL_GRADUATED_DATE_<?=$other_eduction_count?>" name="OTHER_SCHOOL_GRADUATED_DATE_<?=$other_eduction_count?>" type="text" class="form-control date" value="<?=$OTHER_SCHOOL_GRADUATED_DATE?>" <?=$disabled?> >
					<span class="bar"></span> 
					<label for="OTHER_SCHOOL_GRADUATED_DATE_<?=$other_eduction_count?>" id="OTHER_SCHOOL_GRADUATED_DATE_LBL_<?=$other_eduction_count?>" ><? if($OTHER_SCHOOL_PK_EDUCATION_TYPE == 1 || $OTHER_SCHOOL_PK_EDUCATION_TYPE == 3) echo PASSED_DATE; else echo GRADUATED_DATE;?> </label>
				</div>
			</div>
			
			<div class="d-flex">
				<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
					<input type="checkbox" class="custom-control-input" id="OTHER_SCHOOL_TRANSCRIPT_REQUESTED_<?=$other_eduction_count?>" name="OTHER_SCHOOL_TRANSCRIPT_REQUESTED_<?=$other_eduction_count?>" value="1" <? if($OTHER_SCHOOL_TRANSCRIPT_REQUESTED == 1) echo "checked"; ?> onclick="enable_other_school_field('<?=$other_eduction_count?>','OTHER_SCHOOL_TRANSCRIPT_REQUESTED')" >
					<label class="custom-control-label" for="OTHER_SCHOOL_TRANSCRIPT_REQUESTED_<?=$other_eduction_count?>"><?=TRANSCRIPT_REQUESTED?></label>
				</div>
				<div class="col-12 col-sm-4 form-group focused">
					<? $disabled = "disabled";
					if($OTHER_SCHOOL_TRANSCRIPT_REQUESTED == 1)
						$disabled = ""; ?>
					<input id="OTHER_SCHOOL_TRANSCRIPT_REQUESTED_DATE_<?=$other_eduction_count?>" name="OTHER_SCHOOL_TRANSCRIPT_REQUESTED_DATE_<?=$other_eduction_count?>" type="text" class="form-control date" value="<?=$OTHER_SCHOOL_TRANSCRIPT_REQUESTED_DATE?>" <?=$disabled?> >
					<span class="bar"></span> 
					<label for="OTHER_SCHOOL_TRANSCRIPT_REQUESTED_DATE_<?=$other_eduction_count?>"><?=TRANSCRIPT_REQUESTED_DATE?></label>
				</div>
				
			</div>
			
			<div class="d-flex">
				<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
					<input type="checkbox" class="custom-control-input" id="OTHER_SCHOOL_TRANSCRIPT_RECEIVED_<?=$other_eduction_count?>" name="OTHER_SCHOOL_TRANSCRIPT_RECEIVED_<?=$other_eduction_count?>" value="1" <? if($OTHER_SCHOOL_TRANSCRIPT_RECEIVED == 1) echo "checked"; ?> onclick="enable_other_school_field('<?=$other_eduction_count?>','OTHER_SCHOOL_TRANSCRIPT_RECEIVED')" >
					<label class="custom-control-label" for="OTHER_SCHOOL_TRANSCRIPT_RECEIVED_<?=$other_eduction_count?>"><?=TRANSCRIPT_RECEIVED?></label>
				</div>
				<? $disabled = "disabled";
				if($OTHER_SCHOOL_TRANSCRIPT_RECEIVED == 1)
					$disabled = ""; ?>
				<div class="col-12 col-sm-4 form-group focused">
					<input id="OTHER_SCHOOL_TRANSCRIPT_RECEIVED_DATE_<?=$other_eduction_count?>" name="OTHER_SCHOOL_TRANSCRIPT_RECEIVED_DATE_<?=$other_eduction_count?>" type="text" class="form-control date" value="<?=$OTHER_SCHOOL_TRANSCRIPT_RECEIVED_DATE?>" <?=$disabled?> >
					<span class="bar"></span> 
					<label for="OTHER_SCHOOL_TRANSCRIPT_RECEIVED_DATE_<?=$other_eduction_count?>"><?=TRANSCRIPT_RECEIVED_DATE?></label>
				</div>
			</div>
			
		</div>
		<div class="col-sm-6 ">
			<div class="d-flex">
				<div class="col-12 col-sm-12 form-group focused">
					<input id="OTHER_SCHOOL_SCHOOL_NAME_<?=$other_eduction_count?>" name="OTHER_SCHOOL_SCHOOL_NAME[]" type="text" class="form-control" value="<?=$OTHER_SCHOOL_SCHOOL_NAME?>">
					<span class="bar"></span> 
					<label for="OTHER_SCHOOL_SCHOOL_NAME_<?=$other_eduction_count?>"><?=SCHOOL_NAME?></label>
				</div>
			</div>
			
			<div class="d-flex">
				<div class="col-12 col-sm-6 form-group focused">
					<input id="OTHER_SCHOOL_ADDRESS_<?=$other_eduction_count?>" name="OTHER_SCHOOL_ADDRESS[]" type="text" class="form-control" value="<?=$OTHER_SCHOOL_ADDRESS?>">
					<span class="bar"></span> 
					<label for="OTHER_SCHOOL_ADDRESS_<?=$other_eduction_count?>"><?=ADDRESS?></label>
				</div>
				
				<div class="col-12 col-sm-6 form-group focused">
					<input id="OTHER_SCHOOL_ADDRESS_1_<?=$other_eduction_count?>" name="OTHER_SCHOOL_ADDRESS_1[]" type="text" class="form-control" value="<?=$OTHER_SCHOOL_ADDRESS_1?>">
					<span class="bar"></span> 
					<label for="OTHER_SCHOOL_ADDRESS_1_<?=$other_eduction_count?>"><?=ADDRESS_1?></label>
				</div>
			</div>
			
			<div class="d-flex">
				<div class="col-12 col-sm-4 form-group focused">
					<input id="OTHER_SCHOOL_CITY_<?=$other_eduction_count?>" name="OTHER_SCHOOL_CITY[]" type="text" class="form-control" value="<?=$OTHER_SCHOOL_CITY?>">
					<span class="bar"></span> 
					<label for="OTHER_SCHOOL_CITY_<?=$other_eduction_count?>"><?=CITY?></label>
				</div>
				
				<div class="col-12 col-sm-4 form-group focused">
					<select id="OTHER_SCHOOL_PK_STATE_<?=$other_eduction_count?>" name="OTHER_SCHOOL_PK_STATE[]" class="form-control">
						<option selected></option>
						 <? $res_type = $db->Execute("select PK_STATES, STATE_NAME from Z_STATES WHERE ACTIVE = '1' ORDER BY STATE_NAME ASC ");
						while (!$res_type->EOF) { ?>
							<option value="<?=$res_type->fields['PK_STATES'] ?>" <? if($OTHER_SCHOOL_PK_STATE == $res_type->fields['PK_STATES']) echo "selected"; ?> ><?=$res_type->fields['STATE_NAME']?></option>
						<?	$res_type->MoveNext();
						} ?>
					</select>
					<span class="bar"></span> 
					<label for="OTHER_SCHOOL_PK_STATE_<?=$other_eduction_count?>"><?=STATE?></label>
				</div>
				
				<div class="col-12 col-sm-4 form-group focused">
					<input id="OTHER_SCHOOL_ZIP_<?=$other_eduction_count?>" name="OTHER_SCHOOL_ZIP[]" type="text" class="form-control" value="<?=$OTHER_SCHOOL_ZIP?>">
					<span class="bar"></span> 
					<label for="OTHER_SCHOOL_ZIP_<?=$other_eduction_count?>"><?=ZIP?></label>
				</div>
			</div>
			
			<div class="d-flex">
				<a href="javascript:void(0);" onclick="delete_row('<?=$other_eduction_count?>','OTHER_SCHOOL')" title="<?=DELETE?>" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i> </a>
			</div>
		</div>
	</div>
	
	<hr /><br />
</div>