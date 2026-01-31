<?php require_once('../global/config.php'); 
require_once("../language/common.php");
require_once("../language/transfer_credit.php");
require_once("check_access.php");

$REGISTRAR_ACCESS 	= check_access('REGISTRAR_ACCESS');

if($REGISTRAR_ACCESS == 0){
	header("location:../index");
	exit;
}

$transfer_credit_count 		 = $_REQUEST['transfer_credit_count'];
$PK_STUDENT_CREDIT_TRANSFER  = $_REQUEST['PK_STUDENT_CREDIT_TRANSFER'];  

if($PK_STUDENT_CREDIT_TRANSFER == ''){
	$TC_PK_STUDENT_MASTER			= $_REQUEST['sid'];
	$TC_PK_STUDENT_ENROLLMENT		= $_REQUEST['eid'];
	$TC_SCHOOL_NAME					= '';
	$TC_COURSE_CODE  				= '';
	$TC_COURSE_DESCRIPTION  		= '';
	$TC_PK_EQUIVALENT_COURSE_MASTER = '';
	$TC_YEAR   						= '';
	$TC_TERM  						= '';
	$TC_PK_CREDIT_TRANSFER_STATUS  	= '';
	$TC_PK_GRADE  					= '';
	$TC_HOUR  						= '';
	$TC_PREP  						= '';
	$TC_UNITS  						= '';
	$TC_FA_UNITS  					= '';
	$TC_NOTES  						= '';
	$TC_TC_NUMERIC_GRADE			= ''; //Ticket # 1240
	
	$TC_PRIOR_COURSE_DESCRIPTION	= '';
	$TC_PRIOR_HOUR					= '';
	$TC_PRIOR_PREP					= '';
	$TC_PRIOR_FA_UNITS				= '';
	$TC_PRIOR_UNITS					= '';
	$TC_PRIOR_GRADE					= '';
	$TC_TC_PRIOR_NUMERIC_GRADE		= '';

} else {
	$result 	= $db->Execute("SELECT * FROM S_STUDENT_CREDIT_TRANSFER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_CREDIT_TRANSFER = '$PK_STUDENT_CREDIT_TRANSFER'");

	$TC_PK_STUDENT_MASTER			= $result->fields['PK_STUDENT_MASTER'];
	$TC_PK_STUDENT_ENROLLMENT		= $result->fields['PK_STUDENT_ENROLLMENT'];
	$TC_SCHOOL_NAME					= $result->fields['SCHOOL_NAME'];
	$TC_COURSE_CODE  				= $result->fields['COURSE_CODE'];	
	$TC_COURSE_DESCRIPTION  		= $result->fields['COURSE_DESCRIPTION'];	
	$TC_PK_EQUIVALENT_COURSE_MASTER = $result->fields['PK_EQUIVALENT_COURSE_MASTER'];	
	$TC_YEAR   						= $result->fields['YEAR'];	
	$TC_TERM  						= $result->fields['TERM'];	
	$TC_PK_CREDIT_TRANSFER_STATUS  	= $result->fields['PK_CREDIT_TRANSFER_STATUS'];	
	$TC_PK_GRADE  					= $result->fields['PK_GRADE'];	
	$TC_HOUR  						= $result->fields['HOUR'];	
	$TC_PREP  						= $result->fields['PREP'];	
	$TC_UNITS  						= $result->fields['UNITS'];	
	$TC_FA_UNITS  					= $result->fields['FA_UNITS'];		
	$TC_NOTES  						= $result->fields['NOTES'];	
	$TC_TC_NUMERIC_GRADE			= $result->fields['TC_NUMERIC_GRADE'];	//Ticket # 1240
	
	$TC_PRIOR_COURSE_DESCRIPTION	= $result->fields['PRIOR_COURSE_DESCRIPTION'];
	$TC_PRIOR_HOUR					= $result->fields['PRIOR_HOUR'];
	$TC_PRIOR_PREP					= $result->fields['PRIOR_PREP'];
	$TC_PRIOR_FA_UNITS				= $result->fields['PRIOR_FA_UNITS'];
	$TC_PRIOR_UNITS					= $result->fields['PRIOR_UNITS'];
	$TC_PRIOR_GRADE					= $result->fields['PRIOR_GRADE'];
	$TC_TC_PRIOR_NUMERIC_GRADE		= $result->fields['TC_PRIOR_NUMERIC_GRADE'];
}
?>

<div class="p-20 pb-0 lender-form" id="transfer_credit_<?=$transfer_credit_count?>">
	<div class="row pt-3" style="border: 1px solid #fdfdfd;box-shadow: 1px 0px 20px rgba(0, 0, 0, 0.08);">
		<input type="hidden" name="PK_STUDENT_CREDIT_TRANSFER[]"  value="<?=$PK_STUDENT_CREDIT_TRANSFER?>" />
		<input type="hidden" name="TRANSFER_CREDIT_COUNT[]"  value="<?=$transfer_credit_count?>" />
		
		<div class="col-sm-6 "> 
			<div class="row" >
				<div class="col-12 col-sm-12 form-group">
					<h4 class="card-title"><?=THIS_INSTITUTION?></h4>
				</div>
			</div>
			
			<div class="row" >
				<div class="col-12 col-sm-12 form-group">
					<select id="TC_PK_STUDENT_ENROLLMENT_<?=$transfer_credit_count?>" name="TC_PK_STUDENT_ENROLLMENT[]" class="form-control required-entry " >  
						<option value="" ></option>
						<? /* Ticket # 1689 */
						$res_type = $db->Execute("SELECT S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, STATUS_DATE, STUDENT_STATUS, CODE, IF(BEGIN_DATE = '0000-00-00', '', DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, IS_ACTIVE_ENROLLMENT, CAMPUS_CODE FROM S_STUDENT_ENROLLMENT LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = '$TC_PK_STUDENT_MASTER' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
						while (!$res_type->EOF) { ?>
							<option value="<?=$res_type->fields['PK_STUDENT_ENROLLMENT']?>" <? if($res_type->fields['PK_STUDENT_ENROLLMENT'] == $TC_PK_STUDENT_ENROLLMENT) echo "selected"; ?> ><? if($res_type->fields['IS_ACTIVE_ENROLLMENT'] == 1) echo "Current: "; ?> <?=$res_type->fields['BEGIN_DATE_1'].' - '.$res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['CODE'].' - '.$res_type->fields['CAMPUS_CODE']?></option>
						<?	$res_type->MoveNext();
						} /* Ticket # 1689 */ ?>
					</select>
					<span class="bar"></span> 
					<label for="TC_PK_STUDENT_ENROLLMENT_<?=$transfer_credit_count?>"><?=ENROLLMENT?></label>
				</div>
			</div>
			
			<div class="row" >
				<div class="col-12 col-sm-12 form-group">
					<select id="TC_PK_CREDIT_TRANSFER_STATUS_<?=$transfer_credit_count?>" name="TC_PK_CREDIT_TRANSFER_STATUS[]" class="form-control required-entry" >  
						<option value="" ></option>
						<? /* Ticket #1689  */
						$res_type = $db->Execute("SELECT PK_CREDIT_TRANSFER_STATUS, CONCAT(CREDIT_TRANSFER_STATUS, ' - ', DESCRIPTION) as CREDIT_TRANSFER_STATUS, ACTIVE FROM M_CREDIT_TRANSFER_STATUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ORDER BY ACTIVE DESC, CREDIT_TRANSFER_STATUS ASC ");
						while (!$res_type->EOF) { 
							$option_label = $res_type->fields['CREDIT_TRANSFER_STATUS'];
							if($res_type->fields['ACTIVE'] == 0)
								$option_label .= " (Inactive)"; ?>
							<option value="<?=$res_type->fields['PK_CREDIT_TRANSFER_STATUS']?>" <? if($res_type->fields['PK_CREDIT_TRANSFER_STATUS'] == $TC_PK_CREDIT_TRANSFER_STATUS) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
						<?	$res_type->MoveNext();
						} /* Ticket #1689  */ ?>
					</select>
					
					<span class="bar"></span>
					<label for="TC_PK_CREDIT_TRANSFER_STATUS_<?=$PK_CREDIT_TRANSFER_STATUS?>"><?=TRANSFER_STATUS?></label>
				</div>
			</div>
			
			<div class="row" >
				<div class="col-12 col-sm-12 form-group">
					<select id="TC_PK_EQUIVALENT_COURSE_MASTER_<?=$transfer_credit_count?>" name="TC_PK_EQUIVALENT_COURSE_MASTER[]" class="form-control required-entry" onchange="get_course_detail_for_tc(this.value, '<?=$transfer_credit_count?>' )" >  
						<option value="" ></option>
						<? /* Ticket # 1689 */
						$res_type = $db->Execute("SELECT PK_COURSE, CONCAT(COURSE_CODE,' - ', TRANSCRIPT_CODE, ' - ', COURSE_DESCRIPTION) AS COURSE_CODE, ACTIVE FROM S_COURSE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY ACTIVE DESC, COURSE_CODE ASC");
						while (!$res_type->EOF) { 
							$option_label = $res_type->fields['COURSE_CODE'];
							if($res_type->fields['ACTIVE'] == 0)
								$option_label .= " (Inactive)"; ?>
							<option value="<?=$res_type->fields['PK_COURSE']?>" <? if($res_type->fields['PK_COURSE'] == $TC_PK_EQUIVALENT_COURSE_MASTER) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
						<?	$res_type->MoveNext();
						} 
						/* Ticket # 1689 */?>
					</select>
					<span class="bar"></span>
					<label for="TC_PK_EQUIVALENT_COURSE_MASTER_<?=$transfer_credit_count?>"><?=EQV_COURSE?></label>
				</div>
			</div>
			
			<div class="row" >
				<div class="col-12 col-sm-12 form-group">
					<input id="TC_COURSE_DESCRIPTION_<?=$transfer_credit_count?>" name="TC_COURSE_DESCRIPTION[]" type="text" class="form-control" value="<?=$TC_COURSE_DESCRIPTION?>" readonly />
					<span class="bar"></span>
					<label for="TC_COURSE_DESCRIPTION_<?=$transfer_credit_count?>"><?=EQUIVALENT_COURSE_DESC?></label>
				</div>
			</div>
			
			<div class="row" >
				<div class="col-6 col-sm-6 form-group">
					<input id="TC_HOUR_<?=$transfer_credit_count?>" name="TC_HOUR[]" type="text" class="form-control" value="<?=$TC_HOUR?>"  />
					<span class="bar"></span>
					<label for="TC_HOUR_<?=$transfer_credit_count?>"><?=HOUR?></label>
				</div>
				
				<div class="col-6 col-sm-6 form-group">
					<input id="TC_PREP_<?=$transfer_credit_count?>" name="TC_PREP[]" type="text" class="form-control" value="<?=$TC_PREP?>" autofocus  />
					<span class="bar"></span>
					<label for="TC_PREP_<?=$transfer_credit_count?>"><?=PREP?></label>
				</div>
			</div>
			
			<div class="row" >
				<div class="col-6 col-sm-6 form-group">
					<input id="TC_FA_UNITS_<?=$transfer_credit_count?>" name="TC_FA_UNITS[]" type="text" class="form-control" value="<?=$TC_FA_UNITS?>"  />
					<span class="bar"></span>
					<label for="TC_FA_UNITS_<?=$transfer_credit_count?>"><?=FA_UNITS?></label>
				</div>
				
				<div class="col-6 col-sm-6 form-group">
					<input id="TC_UNITS_<?=$transfer_credit_count?>" name="TC_UNITS[]" type="text" class="form-control" value="<?=$TC_UNITS?>"  />
					<span class="bar"></span>
					<label for="TC_UNITS_<?=$transfer_credit_count?>"><?=UNITS?></label>
				</div>
			</div>
			
			<div class="row" >
				<div class="col-6 col-sm-6 form-group">
					<select id="TC_PK_GRADE_<?=$transfer_credit_count?>" name="TC_PK_GRADE[]" class="form-control" onchange="get_numeric_grade_tc(this.value, '<?=$transfer_credit_count?>')" >
						<option value="" ></option>
						<? /* Ticket #1689  */
						$res_type = $db->Execute("SELECT PK_GRADE, CONCAT(GRADE, ' - ' , NUMBER_GRADE) as GRADE, ACTIVE FROM S_GRADE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY ACTIVE DESC, GRADE ASC ");
						while (!$res_type->EOF) { 
							$option_label = $res_type->fields['GRADE'];
							if($res_type->fields['ACTIVE'] == 0)
								$option_label .= " (Inactive)"; ?>
							<option value="<?=$res_type->fields['PK_GRADE']?>" <? if($res_type->fields['PK_GRADE'] == $TC_PK_GRADE) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$res_type->fields['GRADE'] ?></option>
						<?	$res_type->MoveNext();
						} /* Ticket #1689  */ ?>
					</select>
					<span class="bar"></span>
					<label for="TC_PK_GRADE_<?=$transfer_credit_count?>"><?=GRADE?></label>
				</div>
				<div class="col-6 col-sm-6 form-group">
					<input id="TC_TC_NUMERIC_GRADE_<?=$transfer_credit_count?>" name="TC_TC_NUMERIC_GRADE[]" type="text" class="form-control" value="<?=$TC_TC_NUMERIC_GRADE?>"  />
					<span class="bar"></span>
					<label for="TC_TC_NUMERIC_GRADE_<?=$transfer_credit_count?>"><?=NUMERIC_GRADE?></label>
				</div>
			</div>
			
			<div class="row" >
				<div class="col-12 col-sm-12 form-group">
					<input id="TC_NOTES_<?=$transfer_credit_count?>" name="TC_NOTES[]" type="text" class="form-control" value="<?=$TC_NOTES?>" />
					<span class="bar"></span>
					<label for="TC_NOTES_<?=$transfer_credit_count?>"><?=NOTES?></label>
				</div>
			</div>
			
		</div>
		
		<div class="col-sm-6 theme-v-border"> 
			<div class="row" >
				<div class="col-12 col-sm-12 form-group">
					<h4 class="card-title"><?=PRIOR_INSTITUTION?></h4>
				</div>
			</div>
			
			<div class="row" style="margin-top: -4px;" >
				<div class="col-12 col-sm-12 form-group">
					<br />
					<br />
				</div>
			</div>
			
			<div class="row" >
				<div class="col-12 col-sm-12 form-group">
					<input id="TC_SCHOOL_NAME_<?=$transfer_credit_count?>" name="TC_SCHOOL_NAME[]" type="text" class="form-control required-entry" value="<?=$TC_SCHOOL_NAME?>" />
					<span class="bar"></span>
					<label for="TC_SCHOOL_NAME_<?=$transfer_credit_count?>"><?=SCHOOL_NAME?></label>
				</div>
			</div>
			
			<div class="row" >
				<div class="col-12 col-sm-12 form-group">
					<input id="TC_COURSE_CODE_<?=$transfer_credit_count?>" name="TC_COURSE_CODE[]" type="text" class="form-control required-entry" value="<?=$TC_COURSE_CODE?>" />
					<span class="bar"></span>
					<label for="TC_COURSE_CODE_<?=$transfer_credit_count?>"><?=COURSE_CODE?></label>
				</div>
			</div>
			
			<div class="row" >
				<div class="col-12 col-sm-12 form-group">
					<input id="TC_PRIOR_COURSE_DESCRIPTION_<?=$transfer_credit_count?>" name="TC_PRIOR_COURSE_DESCRIPTION[]" type="text" class="form-control required-entry" value="<?=$TC_PRIOR_COURSE_DESCRIPTION?>" />
					<span class="bar"></span>
					<label for="TC_PRIOR_COURSE_DESCRIPTION_<?=$transfer_credit_count?>"><?=COURSE_DESC?></label>
				</div>
			</div>
			
			<div class="row" >
				<div class="col-6 col-sm-6 form-group">
					<input id="TC_PRIOR_HOUR_<?=$transfer_credit_count?>" name="TC_PRIOR_HOUR[]" type="text" class="form-control" value="<?=$TC_PRIOR_HOUR?>" />
					<span class="bar"></span>
					<label for="TC_PRIOR_HOUR_<?=$transfer_credit_count?>"><?=HOUR?></label>
				</div>
				
				<div class="col-6 col-sm-6 form-group">
					<input id="TC_PRIOR_PERP_<?=$transfer_credit_count?>" name="TC_PRIOR_PREP[]" type="text" class="form-control" value="<?=$TC_PRIOR_PREP?>" autofocus />
					<span class="bar"></span>
					<label for="TC_PRIOR_PERP_<?=$transfer_credit_count?>"><?=PREP?></label>
				</div>
			</div>
			
			<div class="row" >
				<div class="col-6 col-sm-6 form-group">
					<input id="TC_PRIOR_FA_UNITS_<?=$transfer_credit_count?>" name="TC_PRIOR_FA_UNITS[]" type="text" class="form-control" value="<?=$TC_PRIOR_FA_UNITS?>" />
					<span class="bar"></span>
					<label for="TC_PRIOR_FA_UNITS_<?=$transfer_credit_count?>"><?=FA_UNITS?></label>
				</div>
				
				<div class="col-6 col-sm-6 form-group">
					<input id="TC_PRIOR_UNITS_<?=$transfer_credit_count?>" name="TC_PRIOR_UNITS[]" type="text" class="form-control" value="<?=$TC_PRIOR_UNITS?>" />
					<span class="bar"></span>
					<label for="TC_PRIOR_UNITS_<?=$transfer_credit_count?>"><?=UNITS?></label>
				</div>
			</div>
			
			<div class="row" >
				<div class="col-6 col-sm-6 form-group">
					<input id="TC_PRIOR_GRADE_<?=$transfer_credit_count?>" name="TC_PRIOR_GRADE[]" type="text" class="form-control" value="<?=$TC_PRIOR_GRADE?>" />
					<span class="bar"></span>
					<label for="TC_PRIOR_GRADE_<?=$transfer_credit_count?>"><?=GRADE?></label>
				</div>
				<div class="col-6 col-sm-6 form-group">
					<input id="TC_TC_PRIOR_NUMERIC_GRADE_<?=$transfer_credit_count?>" name="TC_TC_PRIOR_NUMERIC_GRADE[]" type="text" class="form-control" value="<?=$TC_TC_PRIOR_NUMERIC_GRADE?>" />
					<span class="bar"></span>
					<label for="TC_TC_PRIOR_NUMERIC_GRADE_<?=$transfer_credit_count?>"><?=NUMERIC_GRADE?></label>
				</div>
			</div>
			
			<div class="row" >
				<div class="col-6 col-sm-6 form-group">
					<input id="TC_YEAR_<?=$transfer_credit_count?>" name="TC_YEAR[]" type="text" class="form-control" value="<?=$TC_YEAR?>" />
					<span class="bar"></span>
					<label for="TC_YEAR_<?=$transfer_credit_count?>"><?=YEAR?></label>
				</div>
			
				<div class="col-6 col-sm-4 form-group">
					<input id="TC_TERM_<?=$transfer_credit_count?>" name="TC_TERM[]" type="text" class="form-control" value="<?=$TC_TERM?>" />
					<span class="bar"></span>
					<label for="TC_TERM_<?=$transfer_credit_count?>"><?=TERM?></label>
				</div>
			
				<div class="col-sm-2 pt-2"> 
					<div class="d-flex">
						<div class="col-12 col-sm-12 form-group text-right">
							<? if($REGISTRAR_ACCESS == 2 || $REGISTRAR_ACCESS == 3){ ?>
							<a href="javascript:void(0)" onclick="delete_row('<?=$transfer_credit_count?>','transfer_credit')" class="btn delete-color btn-circle" ><i class="far fa-trash-alt"></i></a>
							<? } ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>