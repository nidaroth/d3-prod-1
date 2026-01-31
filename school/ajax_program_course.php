<? require_once("../global/config.php"); 
require_once("../language/program.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == ''){ 
	header("location:../index");
	exit;
} 
$PK_CAMPUS_PROGRAM_COURSE  	= $_REQUEST['PK_CAMPUS_PROGRAM_COURSE'];
$program_course_id 			= $_REQUEST['program_course_id']; 

//$debug=1;
$readOnlyelement = true;
if($PK_CAMPUS_PROGRAM_COURSE == '') {
	$PK_COURSE 			= '';
	$PK_COREQUISITE[]	= array();
	$PK_PREREQUISITE[]	= array();
	$COURSE_ORDER 	 	= '';
	$COURSE_TYPE		= 1;
	$ACTIVE 	 		= 1;
	$PK_TRANSCRIPT_GROUP = '';
	
	/* Ticket # 1244 */
	$C_UNITS 	= '';
	$C_FA_UNITS = '';
	$C_HOURS 	= '';
	/* Ticket # 1244 */

	$GRADE_INCLUDE_IN_SAP  = '0';

} else {
	$res_dd = $db->Execute("SELECT PK_COURSE,PK_COREQUISITE,PK_PREREQUISITE,COURSE_ORDER,COURSE_TYPE,ACTIVE,PK_TRANSCRIPT_GROUP,GRADE_INCLUDE_IN_SAP FROM M_CAMPUS_PROGRAM_COURSE WHERE PK_CAMPUS_PROGRAM_COURSE = '$PK_CAMPUS_PROGRAM_COURSE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
	$PK_COURSE 			= $res_dd->fields['PK_COURSE'];
	$PK_COREQUISITE 	= explode(",",$res_dd->fields['PK_COREQUISITE']);
	$PK_PREREQUISITE 	= explode(",",$res_dd->fields['PK_PREREQUISITE']);
	$COURSE_ORDER   	= $res_dd->fields['COURSE_ORDER'];
	$COURSE_TYPE		= $res_dd->fields['COURSE_TYPE'] ? $res_dd->fields['COURSE_TYPE'] : 1;
	$ACTIVE 	 		= $res_dd->fields['ACTIVE'];
	
	$PK_TRANSCRIPT_GROUP = $res_dd->fields['PK_TRANSCRIPT_GROUP'];
	$GRADE_INCLUDE_IN_SAP  = $res_dd->fields['GRADE_INCLUDE_IN_SAP']; // DIAM - 23, DIAM - 678
	
	/* Ticket # 1244 */
	$res_dd = $db->Execute("SELECT UNITS,FA_UNITS,HOURS FROM S_COURSE WHERE PK_COURSE = '$PK_COURSE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
	$C_UNITS 	= $res_dd->fields['UNITS'];
	$C_FA_UNITS = $res_dd->fields['FA_UNITS'];
	$C_HOURS 	= $res_dd->fields['HOURS'];
	/* Ticket # 1244 */

	
}

$S_COURSE_ROWS = array();
$res_type = $db->Execute("SELECT PK_COURSE, CONCAT(COURSE_CODE,' - ',TRANSCRIPT_CODE,' - ',COURSE_DESCRIPTION) as TRANSCRIPT_CODE, ACTIVE FROM S_COURSE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY ACTIVE DESC, TRANSCRIPT_CODE ASC");

$i=0;
while (!$res_type->EOF) { 
	$S_COURSE_ROWS[$i]['PK_COURSE'] = $res_type->fields['PK_COURSE'];
	$S_COURSE_ROWS[$i]['TRANSCRIPT_CODE'] = $res_type->fields['TRANSCRIPT_CODE'];
	$S_COURSE_ROWS[$i]['ACTIVE'] = $res_type->fields['ACTIVE'];
	$i++;
$res_type->MoveNext();
} 

// echo "<pre>";
// print_r($S_COURSE_ROWS); DIE;

?>
<style>
.disableElement{
	pointer-events: none;
	cursor: pointer;
}
.enableElement{
  background-color:#d3d3d3 !important;
}
</style>
<div data-id="<?=$program_course_id?>" id="program_course_div_<?=$program_course_id?>" class="list-group-item" style="padding: 0.75rem 0;" ><!-- Ticket # 1244 -->
	<div class="row" >
		<input type="hidden" name="PK_CAMPUS_PROGRAM_COURSE[]" value="<?=$PK_CAMPUS_PROGRAM_COURSE?>" />
		<input type="hidden" name="PROGRAM_COURSE_ID[]" value="<?=$program_course_id?>" />
		<label for="input-text" class="col-sm-4 control-label">&nbsp;</label>
		<div class="col-sm-1 disableElement c1" style="flex: 0 0  <?=($_REQUEST['USE_TRANSCRIPT_GROUP'] == 0 ?"16%":"10%");?>;max-width: 18%;" >
			<select id="COURSE_PK_COURSE_<?=$program_course_id?>" name="COURSE_PK_COURSE[]" class="form-control required-entry course_mulselect" error_label="<?=COURSE?> - <?=TAB_PROGRAM_COURSE?>" onchange="get_course_units('<?=$program_course_id?>', this.value)"><!-- Ticket # 1160 --><!-- Ticket # 1244 -->
			 <!-- ticket #1917  -->
				<option value="" ></option>
				<? /* Ticket #1697  */
				// //$res_type = $db->Execute("SELECT PK_COURSE, CONCAT(COURSE_CODE,' - ',TRANSCRIPT_CODE,' - ',COURSE_DESCRIPTION) as TRANSCRIPT_CODE, ACTIVE FROM S_COURSE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY ACTIVE DESC, TRANSCRIPT_CODE ASC");
				// while (!$res_type->EOF) { 
				// 	$option_label = $res_type->fields['TRANSCRIPT_CODE'];
				// 	if($res_type->fields['ACTIVE'] == 0)
				// 		$option_label .= " (Inactive)";?>
					<!-- <option value="<? //=$res_type->fields['PK_COURSE'] ?>" <? //if($PK_COURSE == $res_type->fields['PK_COURSE']) echo "selected"; ?> <? //if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?//=$option_label?></option> -->
				<?	//$res_type->MoveNext();
				//} /* Ticket #1697  */ ?>				
				<?php 
				if(!empty($S_COURSE_ROWS)){
					foreach ($S_COURSE_ROWS as $KEY => $S_COURSE_ROWS_VALUES) {
						$option_label = $S_COURSE_ROWS_VALUES['TRANSCRIPT_CODE'];
						if($S_COURSE_ROWS_VALUES['ACTIVE'] == 0)
							$option_label .= " (Inactive)";?>
							<option value="<?=$S_COURSE_ROWS_VALUES['PK_COURSE'] ?>" <? if($PK_COURSE == $S_COURSE_ROWS_VALUES['PK_COURSE']) echo "selected"; ?> <? if($S_COURSE_ROWS_VALUES['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label?></option>
						<?php						
						}	
					}			
				 ?>
			</select>
		</div>
		
		<!-- Ticket # 1244 -->
		<div class="col-sm-1" style="flex: 0 0 5.55%;max-width: 5.55%;text-align:right;" >
			<div class="C_UNITS_CLS" id="C_UNITS_DIV_<?=$program_course_id?>"><?=$C_UNITS?></div>
		</div>
		<div class="col-sm-1" style="flex: 0 0 5.55%;max-width: 5.55%;text-align:right;" >
			<div class="C_FA_UNITS_CLS" id="C_FA_UNITS_<?=$program_course_id?>"><?=$C_FA_UNITS?></div>
		</div>
		<div class="col-sm-1" style="flex: 0 0 5.55%;max-width: 5.55%;text-align:right;" >
			<div class="C_HOURS_CLS" id="C_HOURS_<?=$program_course_id?>"><?=$C_HOURS?></div>
		</div>
		<!-- Ticket # 1244 -->
		
		<div class="col-sm-2 disableElement" style="flex: 0 0 15%;max-width: 15%;" >
			<select id="COURSE_PK_COREQUISITE_<?=$program_course_id?>" name="COURSE_PK_COREQUISITE_<?=$program_course_id?>[]" multiple class="form-control multiselect1" >
				<? /* Ticket #1697  */
				//$res_type = $db->Execute("SELECT PK_COURSE, CONCAT(COURSE_CODE,' - ',TRANSCRIPT_CODE,' - ',COURSE_DESCRIPTION) as TRANSCRIPT_CODE, ACTIVE from S_COURSE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY ACTIVE DESC, TRANSCRIPT_CODE ASC");
				//while (!$res_type->EOF) { 
				//	$selected = "";
					// foreach($PK_COREQUISITE as $PK_COREQUISITE1) {
					// 	if($PK_COREQUISITE1 == $res_type->fields['PK_COURSE']) {
					// 		$selected = "selected";
					// 		break;
					// 	}
					// } 
					
					// if(in_array($res_type->fields['PK_COURSE'],$PK_COREQUISITE )){
					// 	$selected = "selected";
					// $option_label = $res_type->fields['TRANSCRIPT_CODE'];
					// if($res_type->fields['ACTIVE'] == 0)
					// 	$option_label .= " (Inactive)"; ?>
					<!-- <option value="<? //=$res_type->fields['PK_COURSE'] ?>" <? //=$selected ?> <? //if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><? //=$option_label ?></option> -->
				<?	
					//}
				//$res_type->MoveNext();
				//} /* Ticket #1697  */ ?>

				<?php 
				if(!empty($S_COURSE_ROWS)){
					foreach ($S_COURSE_ROWS as $KEY => $S_COURSE_ROWS_VALUES) {
						$selected = "";
						if(in_array($S_COURSE_ROWS_VALUES['PK_COURSE'],$PK_COREQUISITE )){
						 $selected = "selected";
						$option_label = $S_COURSE_ROWS_VALUES['TRANSCRIPT_CODE'];
						if($S_COURSE_ROWS_VALUES['ACTIVE'] == 0)
							$option_label .= " (Inactive)";?>
							 <option value="<?=$S_COURSE_ROWS_VALUES['PK_COURSE'] ?>" <?=$selected ?> <? if($S_COURSE_ROWS_VALUES['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
						<?php
							}						
						}	
					}			
				 ?>

			</select>
		</div>
		
		<div class="col-sm-2 disableElement" style="flex: 0 0 15%;max-width: 15%;" >
			<select id="COURSE_PK_PREREQUISITE_<?=$program_course_id?>" name="COURSE_PK_PREREQUISITE_<?=$program_course_id?>[]" multiple class="form-control multiselect2" >
				<? /* Ticket #1697  */
				//$res_type = $db->Execute("SELECT PK_COURSE, CONCAT(COURSE_CODE,' - ',TRANSCRIPT_CODE,' - ',COURSE_DESCRIPTION) as TRANSCRIPT_CODE, ACTIVE from S_COURSE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY ACTIVE DESC, TRANSCRIPT_CODE ASC"); 
				//while (!$res_type->EOF) { 
					//$selected = "";
					// foreach($PK_PREREQUISITE as $PK_PREREQUISITE1) {
					// 	if($PK_PREREQUISITE1 == $res_type->fields['PK_COURSE']) {
					// 		$selected = "selected";
					// 		break;
					// 	}
					// } 

					// if(in_array($res_type->fields['PK_COURSE'],$PK_PREREQUISITE )){
					// $selected = "selected";
					// $option_label = $res_type->fields['TRANSCRIPT_CODE'];
					// if($res_type->fields['ACTIVE'] == 0)
					// 	$option_label .= " (Inactive)"; ?>
					<!-- <option value="<? //=$res_type->fields['PK_COURSE'] ?>" <? //=$selected ?> <? //if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><? //=$option_label ?></option> -->
				<?
					//}
					//$res_type->MoveNext();
				//} /* Ticket #1697  */ ?>

				<?php 
				if(!empty($S_COURSE_ROWS)){
					foreach ($S_COURSE_ROWS as $KEY => $S_COURSE_ROWS_VALUES) {
						$selected = "";						
					 if(in_array($S_COURSE_ROWS_VALUES['PK_COURSE'],$PK_PREREQUISITE )){
						$selected = "selected";
						$option_label = $S_COURSE_ROWS_VALUES['TRANSCRIPT_CODE'];
						if($S_COURSE_ROWS_VALUES['ACTIVE'] == 0)
							$option_label .= " (Inactive)";
						 ?>
						 <option value="<?=$S_COURSE_ROWS_VALUES['PK_COURSE'] ?>" <?=$selected ?> <? if($S_COURSE_ROWS_VALUES['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>

						<?php
							}						
						}	
					}			
				 ?>

			</select>
		</div>
		
		<!-- Ticket # 1834  -->
		<div class="col-sm-1 TRANSCRIPT_GROUP_CLS disableElement" style="flex: 0 0 11%;max-width: 11%; <? if($_REQUEST['USE_TRANSCRIPT_GROUP'] == 0) echo "display:none;"; ?> " ><!-- Ticket # 1603  -->
			<select id="COURSE_PK_TRANSCRIPT_GROUP_<?=$program_course_id?>" name="COURSE_PK_TRANSCRIPT_GROUP[]" class="form-control" >
				<option value=""  ></option>
				<?  $res_type = $db->Execute("SELECT PK_TRANSCRIPT_GROUP, CONCAT(TRANSCRIPT_GROUP,' - ',DESCRIPTION) as TRANSCRIPT_GROUP, ACTIVE FROM M_TRANSCRIPT_GROUP WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY ACTIVE DESC, TRANSCRIPT_GROUP ASC"); 
				while (!$res_type->EOF) { 
					$option_label = $res_type->fields['TRANSCRIPT_GROUP'];
					if($res_type->fields['ACTIVE'] == 0)
						$option_label .= " (Inactive)"; ?>
					<option value="<?=$res_type->fields['PK_TRANSCRIPT_GROUP'] ?>" <? if($res_type->fields['PK_TRANSCRIPT_GROUP'] == $PK_TRANSCRIPT_GROUP) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
				<?	$res_type->MoveNext();
				}  ?>
			</select>
		</div>
		<!-- Ticket # 1834  -->
		
		<div class="col-sm-1 disableElement" style="flex: 0 0 5%;max-width: 5%;" > <!-- Ticket # 1834  -->
			<input type="text" class="form-control" placeholder="" name="COURSE_ORDER[]" id="COURSE_ORDER_<?=$program_course_id?>" value="<?=$COURSE_ORDER?>" />
		</div>
		
		<div class="col-sm-1 disableElement" >
			<select id="COURSE_TYPE_<?=$program_course_id?>" error_label="Course - Type" name="COURSE_TYPE[]" class="form-control required-entry" >
				<option selected ></option>
				<option value="1" <? if($COURSE_TYPE == 1) echo "selected"; ?> >Required</option>
				<option value="2" <? if($COURSE_TYPE == 2) echo "selected"; ?> >Elective</option>
			</select>
		</div>

		<!-- DIAM - 24, DIAM - 677 -->
		<div class="col-sm-1 disableElement" style="flex: 0 0 5%;max-width: 5%;" >
			<div class="d-flex">
				<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
				<input type="hidden" name="GRADE_PROGRAM_COURSE_SAP[]" id="GRADE_INCLUDE_IN_SAPS_<?= $program_course_id ?>" 
		
					<? if($GRADE_INCLUDE_IN_SAP == 1){echo 'value="1"';}
					else{echo 'value="0"';}
					?>
					>
					<input type="checkbox" class="custom-control-input" id="GRADE_INCLUDE_IN_SAP_<?=$program_course_id?>" name="GRADE_INCLUDE_IN_SAP_<?=$program_course_id?>" onclick="document.getElementById('GRADE_INCLUDE_IN_SAPS_<?= $program_course_id ?>').value = 1 - document.getElementById('GRADE_INCLUDE_IN_SAPS_<?= $program_course_id ?>').value"<? if($GRADE_INCLUDE_IN_SAP == 1){echo 'checked';}?> >
					<label class="custom-control-label" for="GRADE_INCLUDE_IN_SAP_<?=$program_course_id?>"> </label>
				</div>
			</div>
		</div> 
		<!-- End DIAM - 24, DIAM - 677 -->
		
		<div class="col-sm-1 disableElement" style="flex: 0 0 5%;max-width: 4%;" > <!-- Ticket # 1834  -->
			<div class="d-flex">
				<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
					<input type="checkbox" class="custom-control-input" id="COURSE_ACTIVE_<?=$program_course_id?>" name="COURSE_ACTIVE_<?=$program_course_id?>" value="1" <? if($ACTIVE == 1) echo "checked"; ?> >
					<label class="custom-control-label" for="COURSE_ACTIVE_<?=$program_course_id?>"> </label>
				</div>
			</div>
		</div> 
		<!-- <div class="col-sm-1" style="flex: 0 0 5.33333%;max-width: 5.33333%;padding-right: 0;" >
		<a href="javascript:void(0);" title="Edit" class="btn edit-color btn-circle" onclick='AjaxProgramLoadSelectOption(<?=$program_course_id?>,<?php echo json_encode($PK_COREQUISITE);?>,<?php echo json_encode($PK_PREREQUISITE);?>);'>
			<i class="far fa-edit"></i> </a>
		</div> -->

		<div class="col-sm-2" style="flex: 0 0 9.33333%;max-width: 15.33333%;padding-right: 0;" >
			<a href="javascript:void(0);" title="Edit" class="btn edit-color btn-circle" onclick='show_loder();AjaxProgramLoadSelectOption(<?=$program_course_id?>,<?php echo json_encode($PK_COREQUISITE);?>,<?php echo json_encode($PK_PREREQUISITE);?>);'>
			<i class="far fa-edit"></i> </a>
			<a href="javascript:void(0)" onclick="delete_row(<?=$program_course_id?>,'program_course')" class="btn delete-color btn-circle" style="width: 26px;height: 26px;padding: 2px;" ><i class="far fa-trash-alt"></i></a>
			
			<i class="mdi mdi-format-list-bulleted btn-circle" title="Drag & Drop to Sort" ></i>
			
		</div>
				
	</div>
</div>