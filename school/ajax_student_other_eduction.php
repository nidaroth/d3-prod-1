<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/student.php"); 

require_once("check_access.php");

$ADMISSION_ACCESS 	= check_access('ADMISSION_ACCESS');
$REGISTRAR_ACCESS 	= check_access('REGISTRAR_ACCESS');
$FINANCE_ACCESS 	= check_access('FINANCE_ACCESS');
$ACCOUNTING_ACCESS 	= check_access('ACCOUNTING_ACCESS');
$PLACEMENT_ACCESS 	= check_access('PLACEMENT_ACCESS');

if($ADMISSION_ACCESS == 0 && $REGISTRAR_ACCESS == 0 && $FINANCE_ACCESS == 0 && $ACCOUNTING_ACCESS == 0 && $PLACEMENT_ACCESS == 0){
	header("location:../index");
	exit;
}

$PK_STUDENT_OTHER_EDU 	= $_REQUEST['PK_STUDENT_OTHER_EDU'];
$other_eduction_count	= $_REQUEST['other_eduction_count'];
$disabled_other			= $_REQUEST['disabled_other'];

if($PK_STUDENT_OTHER_EDU == '') {
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
	$OTHER_SCHOOL_PHONE 					= '';
	$OTHER_SCHOOL_FAX 						= '';
	$OTHER_SCHOOL_COMMENTS					= ''; //Ticket # 1428
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
	$OTHER_SCHOOL_PHONE 					= $res_11->fields['OTHER_SCHOOL_PHONE'];
	$OTHER_SCHOOL_FAX 						= $res_11->fields['OTHER_SCHOOL_FAX'];
	$OTHER_SCHOOL_COMMENTS					= $res_11->fields['OTHER_SCHOOL_COMMENTS']; //Ticket # 1428
	
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
<div id="other_education_div_<?=$other_eduction_count?>" >
	<input type="hidden" name="OTHER_EDUCATION_HID[]" value="<?=$other_eduction_count?>" >
	<input type="hidden" name="PK_STUDENT_OTHER_EDU[]" value="<?=$PK_STUDENT_OTHER_EDU?>" >
	<div class="row">
		<div class="col-sm-6 ">
			<div class="d-flex">
				<div class="col-12 col-sm-6 form-group focused">
					<select id="OTHER_SCHOOL_PK_EDUCATION_TYPE_<?=$other_eduction_count?>" name="OTHER_SCHOOL_PK_EDUCATION_TYPE[]" class="form-control" onchange="change_other_school_label(this.value,<?=$other_eduction_count?>)" <?=$disabled_other?> >
						<option ></option>
						<? /* Ticket #1149  */
						$act_type_cond = " AND ACTIVE = 1 ";
						if($OTHER_SCHOOL_PK_EDUCATION_TYPE > 0)
							$act_type_cond = " AND (ACTIVE = 1 OR PK_EDUCATION_TYPE = '$OTHER_SCHOOL_PK_EDUCATION_TYPE' ) ";
							
						$res_dd = $db->Execute("select * from M_EDUCATION_TYPE WHERE 1=1 $act_type_cond  ORDER BY EDUCATION_TYPE ASC ");
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
					<input type="checkbox" class="custom-control-input" id="OTHER_SCHOOL_GRADUATED_<?=$other_eduction_count?>" name="OTHER_SCHOOL_GRADUATED_<?=$other_eduction_count?>" value="1" <? if($OTHER_SCHOOL_GRADUATED == 1) echo "checked"; ?> onclick="enable_other_school_field('<?=$other_eduction_count?>','OTHER_SCHOOL_GRADUATED')" <?=$disabled_other?> >
					<label class="custom-control-label" for="OTHER_SCHOOL_GRADUATED_<?=$other_eduction_count?>" id="OTHER_SCHOOL_GRADUATED_LBL_<?=$other_eduction_count?>"><? if($OTHER_SCHOOL_PK_EDUCATION_TYPE == 1 || $OTHER_SCHOOL_PK_EDUCATION_TYPE == 3) echo PASSED; else echo GRADUATED;?></label>
				</div>
				<div class="col-12 col-sm-4 form-group focused">
					<? $disabled = "disabled";
					if($OTHER_SCHOOL_GRADUATED == 1)
						$disabled = ""; ?>
					<input id="OTHER_SCHOOL_GRADUATED_DATE_<?=$other_eduction_count?>" name="OTHER_SCHOOL_GRADUATED_DATE_<?=$other_eduction_count?>" type="text" class="form-control date" value="<?=$OTHER_SCHOOL_GRADUATED_DATE?>" <?=$disabled?> <?=$disabled_other?> >
					<span class="bar"></span> 
					<label for="OTHER_SCHOOL_GRADUATED_DATE_<?=$other_eduction_count?>" id="OTHER_SCHOOL_GRADUATED_DATE_LBL_<?=$other_eduction_count?>" ><? if($OTHER_SCHOOL_PK_EDUCATION_TYPE == 1 || $OTHER_SCHOOL_PK_EDUCATION_TYPE == 3) echo PASSED_DATE; else echo GRADUATED_DATE;?> </label>
				</div>
			</div>
			
			<div class="d-flex">
				<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
					<input type="checkbox" class="custom-control-input" id="OTHER_SCHOOL_TRANSCRIPT_REQUESTED_<?=$other_eduction_count?>" name="OTHER_SCHOOL_TRANSCRIPT_REQUESTED_<?=$other_eduction_count?>" value="1" <? if($OTHER_SCHOOL_TRANSCRIPT_REQUESTED == 1) echo "checked"; ?> onclick="enable_other_school_field('<?=$other_eduction_count?>','OTHER_SCHOOL_TRANSCRIPT_REQUESTED')" <?=$disabled_other?> >
					<label class="custom-control-label" for="OTHER_SCHOOL_TRANSCRIPT_REQUESTED_<?=$other_eduction_count?>"><?=TRANSCRIPT_REQUESTED?></label>
				</div>
				<div class="col-12 col-sm-4 form-group focused">
					<? $disabled = "disabled";
					if($OTHER_SCHOOL_TRANSCRIPT_REQUESTED == 1)
						$disabled = ""; ?>
					<input id="OTHER_SCHOOL_TRANSCRIPT_REQUESTED_DATE_<?=$other_eduction_count?>" name="OTHER_SCHOOL_TRANSCRIPT_REQUESTED_DATE_<?=$other_eduction_count?>" type="text" class="form-control date" value="<?=$OTHER_SCHOOL_TRANSCRIPT_REQUESTED_DATE?>" <?=$disabled?> <?=$disabled_other?> >
					<span class="bar"></span> 
					<label for="OTHER_SCHOOL_TRANSCRIPT_REQUESTED_DATE_<?=$other_eduction_count?>"><?=TRANSCRIPT_REQUESTED_DATE?></label>
				</div>
				
			</div>
			
			<div class="d-flex">
				<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
					<input type="checkbox" class="custom-control-input" id="OTHER_SCHOOL_TRANSCRIPT_RECEIVED_<?=$other_eduction_count?>" name="OTHER_SCHOOL_TRANSCRIPT_RECEIVED_<?=$other_eduction_count?>" value="1" <? if($OTHER_SCHOOL_TRANSCRIPT_RECEIVED == 1) echo "checked"; ?> onclick="enable_other_school_field('<?=$other_eduction_count?>','OTHER_SCHOOL_TRANSCRIPT_RECEIVED')" <?=$disabled_other?> >
					<label class="custom-control-label" for="OTHER_SCHOOL_TRANSCRIPT_RECEIVED_<?=$other_eduction_count?>"><?=TRANSCRIPT_RECEIVED?></label>
				</div>
				<? $disabled = "disabled";
				if($OTHER_SCHOOL_TRANSCRIPT_RECEIVED == 1)
					$disabled = ""; ?>
				<div class="col-12 col-sm-4 form-group focused">
					<input id="OTHER_SCHOOL_TRANSCRIPT_RECEIVED_DATE_<?=$other_eduction_count?>" name="OTHER_SCHOOL_TRANSCRIPT_RECEIVED_DATE_<?=$other_eduction_count?>" type="text" class="form-control date" value="<?=$OTHER_SCHOOL_TRANSCRIPT_RECEIVED_DATE?>" <?=$disabled?> <?=$disabled_other?> >
					<span class="bar"></span> 
					<label for="OTHER_SCHOOL_TRANSCRIPT_RECEIVED_DATE_<?=$other_eduction_count?>"><?=TRANSCRIPT_RECEIVED_DATE?></label>
				</div>
			</div>
			
			<!-- Ticket # 1428  -->
			<div class="d-flex">
				<div class="col-12 col-sm-12 form-group focused">
					<textarea id="OTHER_SCHOOL_COMMENTS_<?=$other_eduction_count?>" name="OTHER_SCHOOL_COMMENTS[]" type="text" class="form-control" <?=$disabled_other?> ><?=$OTHER_SCHOOL_COMMENTS?></textarea>
					<span class="bar"></span> 
					<label for="OTHER_SCHOOL_COMMENTS_<?=$other_eduction_count?>"><?=COMMENTS?></label>
				</div>
			</div>
			<!-- Ticket # 1428  -->
		</div>
		<div class="col-sm-6 ">
			<div class="d-flex">
				<div class="col-12 col-sm-12 form-group focused">
					<!-- Ticket # 1731 -->
					<select id="OTHER_SCHOOL_SCHOOL_NAME_<?=$other_eduction_count?>" name="OTHER_SCHOOL_SCHOOL_NAME[]"  onchange="get_other_school_info(this.value, <?=$other_eduction_count?>)" >
						<option value="<?=$OTHER_SCHOOL_SCHOOL_NAME?>" ><?=$OTHER_SCHOOL_SCHOOL_NAME?></option>
					</select>
					<!-- Ticket # 1731 -->
					<span class="bar"></span> 
					<label for="OTHER_SCHOOL_SCHOOL_NAME_<?=$other_eduction_count?>"><?=SCHOOL_NAME?></label>
				</div>
			</div>
			
			<div class="d-flex">
				<div class="col-12 col-sm-6 form-group focused">
					<input id="OTHER_SCHOOL_ADDRESS_<?=$other_eduction_count?>" name="OTHER_SCHOOL_ADDRESS[]" type="text" class="form-control" value="<?=$OTHER_SCHOOL_ADDRESS?>" <?=$disabled_other?> >
					<span class="bar"></span> 
					<label for="OTHER_SCHOOL_ADDRESS_<?=$other_eduction_count?>"><?=ADDRESS?></label>
				</div>
				
				<div class="col-12 col-sm-6 form-group focused">
					<input id="OTHER_SCHOOL_ADDRESS_1_<?=$other_eduction_count?>" name="OTHER_SCHOOL_ADDRESS_1[]" type="text" class="form-control" value="<?=$OTHER_SCHOOL_ADDRESS_1?>" <?=$disabled_other?> >
					<span class="bar"></span> 
					<label for="OTHER_SCHOOL_ADDRESS_1_<?=$other_eduction_count?>"><?=ADDRESS_1?></label>
				</div>
			</div>
			
			<div class="d-flex">
				<div class="col-12 col-sm-4 form-group focused">
					<input id="OTHER_SCHOOL_CITY_<?=$other_eduction_count?>" name="OTHER_SCHOOL_CITY[]" type="text" class="form-control" value="<?=$OTHER_SCHOOL_CITY?>" <?=$disabled_other?> >
					<span class="bar"></span> 
					<label for="OTHER_SCHOOL_CITY_<?=$other_eduction_count?>"><?=CITY?></label>
				</div>
				
				<div class="col-12 col-sm-4 form-group focused">
					<select id="OTHER_SCHOOL_PK_STATE_<?=$other_eduction_count?>" name="OTHER_SCHOOL_PK_STATE[]" class="form-control" <?=$disabled_other?> >
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
					<input id="OTHER_SCHOOL_ZIP_<?=$other_eduction_count?>" name="OTHER_SCHOOL_ZIP[]" type="text" class="form-control" value="<?=$OTHER_SCHOOL_ZIP?>" <?=$disabled_other?> >
					<span class="bar"></span> 
					<label for="OTHER_SCHOOL_ZIP_<?=$other_eduction_count?>"><?=ZIP?></label>
				</div>
			</div>
			
			<div class="d-flex">
				<div class="col-12 col-sm-4 form-group focused">
					<input id="OTHER_SCHOOL_PHONE_<?=$other_eduction_count?>" name="OTHER_SCHOOL_PHONE[]" type="text" class="form-control phone-inputmask phone" value="<?=$OTHER_SCHOOL_PHONE?>" <?=$disabled_other?> >
					<span class="bar"></span> 
					<label for="OTHER_SCHOOL_PHONE_<?=$other_eduction_count?>"><?=SCHOOL_PHONE?></label>
				</div>
				
				<div class="col-12 col-sm-4 form-group focused">
					<input id="OTHER_SCHOOL_FAX_<?=$other_eduction_count?>" name="OTHER_SCHOOL_FAX[]" type="text" class="form-control phone-inputmask phone" value="<?=$OTHER_SCHOOL_FAX?>" <?=$disabled_other?> >
					<span class="bar"></span> 
					<label for="OTHER_SCHOOL_FAX_<?=$other_eduction_count?>"><?=SCHOOL_FAX?></label>
				</div>
				
				<div class="col-12 col-sm-4 form-group focused">
					<? if($ADMISSION_ACCESS == 2 || $ADMISSION_ACCESS == 3 || $REGISTRAR_ACCESS == 2 || $REGISTRAR_ACCESS == 3 || $FINANCE_ACCESS == 2 || $FINANCE_ACCESS == 3 || $ACCOUNTING_ACCESS == 2 || $ACCOUNTING_ACCESS == 3){ ?>
						<? //if($PK_STUDENT_OTHER_EDU == '' && $disabled_other == '') { 
						if($disabled_other == '') {?>
						<a href="javascript:void(0);" onclick="delete_row('<?=$other_eduction_count?>','OTHER_SCHOOL')" title="<?=DELETE?>" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i> </a>
						<? } ?>
					<? } ?>
				</div>
			</div>
		</div>
	</div>
	
	<hr /><br />
</div>