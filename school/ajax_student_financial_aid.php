<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/student.php");
require_once("get_department_from_t.php");
require_once("check_access.php");

$FINANCE_ACCESS 	= check_access('FINANCE_ACCESS');
$ACCOUNTING_ACCESS 	= check_access('ACCOUNTING_ACCESS');

if($FINANCE_ACCESS == 0 && $ACCOUNTING_ACCESS == 0){
	header("location:../index");
	exit;
}

$PK_STUDENT_MASTER = $_REQUEST['sid'];
if($_REQUEST['PK_STUDENT_FINANCIAL'] == ''){
	$res = $db->Execute("SELECT PK_CAMPUS_PROGRAM FROM S_STUDENT_ENROLLMENT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_ENROLLMENT = '$_REQUEST[eid]' AND PK_STUDENT_MASTER = '$_REQUEST[sid]'");
	$PK_CAMPUS_PROGRAM = $res->fields['PK_CAMPUS_PROGRAM'];
	
	/*Ticket # 1033*/
	require_once("function_get_details_for_fa_from_program.php");
	$data123 = get_details_for_fa_from_program($_REQUEST['eid']);
	
	$FA_PROGRAM_LENGTH	= $data123['PROGRAM_LENGTH'];
	$FA_PROGRAM_COST	= $data123['PROGRAM_COST'];
	/*Ticket # 1033*/
	
	$FA_PK_STUDENT_ENROLLMENT		= $_REQUEST['eid'];
	$FA_SCHOOL_CODE					= '';
	$FA_UPDATED						= '';
	$FA_REPACKAGE_DATE				= '';
	$FA_NEED						= '';
	$FA_COA							= '';
	$FA_EFC_NO						= '';
	$FA_AUTOMATIC_ZERO_EFC			= 2;
	$FA_YEAR_ROUND_PELL 			= 2;
	$FA_PK_VA_STUDENT 				= 2;
	$FA_PK_ELIGIBLE_CITIZEN 		= 2;
	$FA_SELECTED_FOR_VERIFICATION 	= 2;
	$FA_PK_DEPENDENT_STATUS			= '';
	$FA_IS_FOREIGN 					= 2;
	$FA_PK_DEPENDENCY_OVERRIDE		= '';
	$FA_PROFESSIONAL_JUDGEMENT 		= 2;
	$FA_NO_OF_DEPENDENTS			= '';
	$FA_NUMBER_IN_COLLEGE			= '';
	$FA_DEPENDENTS_IN_COLLEGE		= '';
	$FA_STUDENT_INCOME				= '';
	$FA_PARENT_INCOME				= '';
	$FA_STUDENT_CONTRIBUTION		= '';
	$FA_PARENT_CONTRIBUTION			= '';
	$FA_INCOME_LEVEL				= '';
	$FA_I551N0						= '';
	$FA_PK_MARITAL_STATUS			= '';
	$FA_MARITAL_STATUS_DATE			= '';
	$FA_ISIR_PROCESSED_DATE			= '';
	$FA_ISIR_SIGNED_DATE			= '';
	$FA_ISIR_TRANS_NO				= '';
	$FA_ISIR_CLEAR_PAY 				= 2;
	$FA_PREVIOUS_COLLEGE 			= 2;
	$FA_ACADEMIC_YEAR_BEGIN			= '';
	$FA_ACADEMIC_YEAR_END			= '';
	$FA_ACADEMIC_YEAR				= '';

	$FA_PK_AWARD_YEAR			= '';
	$FA_ACADEMIC_YEAR_BEGIN		= '';
	$FA_ACADEMIC_YEAR_END		= '';
	$FA_AY_MONTH				= '';
	$FA_PK_LENDER_MASTER		= '';
	
	$FA_PK_COA_CATEGORY			= '';
	$FA_PK_DEGREE_CERT			= '';
	$FA_STUDENT_DEGREE			= '';
	$FA_ADVISOR					= '';
} else {
	$cond = "";
	
	$res_fin = $db->Execute("select * FROM S_STUDENT_FINANCIAL WHERE PK_STUDENT_MASTER = '$_REQUEST[sid]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_FINANCIAL = '$_REQUEST[PK_STUDENT_FINANCIAL]' ");

	$FA_PK_STUDENT_ENROLLMENT		= $res_fin->fields['PK_STUDENT_ENROLLMENT'];
	$FA_SCHOOL_CODE					= $res_fin->fields['SCHOOL_CODE'];
	$FA_PROGRAM_LENGTH				= $res_fin->fields['PROGRAM_LENGTH'];
	$FA_PROGRAM_COST				= $res_fin->fields['PROGRAM_COST'];
	$FA_UPDATED						= $res_fin->fields['UPDATED'];
	$FA_REPACKAGE_DATE				= $res_fin->fields['REPACKAGE_DATE'];
	$FA_NEED						= $res_fin->fields['NEED'];
	$FA_COA							= $res_fin->fields['COA'];
	$FA_EFC_NO						= $res_fin->fields['EFC_NO'];
	$FA_AUTOMATIC_ZERO_EFC			= $res_fin->fields['AUTOMATIC_ZERO_EFC'];
	$FA_YEAR_ROUND_PELL 			= $res_fin->fields['YEAR_ROUND_PELL'];
	$FA_PK_VA_STUDENT 				= $res_fin->fields['PK_VA_STUDENT'];
	$FA_PK_ELIGIBLE_CITIZEN 		= $res_fin->fields['PK_ELIGIBLE_CITIZEN'];
	$FA_SELECTED_FOR_VERIFICATION 	= $res_fin->fields['SELECTED_FOR_VERIFICATION'];
	$FA_PK_DEPENDENT_STATUS			= $res_fin->fields['PK_DEPENDENT_STATUS'];
	$FA_IS_FOREIGN 					= $res_fin->fields['IS_FOREIGN'];
	$FA_PK_DEPENDENCY_OVERRIDE		= $res_fin->fields['PK_DEPENDENCY_OVERRIDE'];
	$FA_PROFESSIONAL_JUDGEMENT 		= $res_fin->fields['PROFESSIONAL_JUDGEMENT'];
	$FA_NO_OF_DEPENDENTS			= $res_fin->fields['NO_OF_DEPENDENTS'];
	$FA_NUMBER_IN_COLLEGE			= $res_fin->fields['NUMBER_IN_COLLEGE'];
	$FA_DEPENDENTS_IN_COLLEGE		= $res_fin->fields['DEPENDENTS_IN_COLLEGE'];
	$FA_STUDENT_INCOME				= $res_fin->fields['STUDENT_INCOME'];
	$FA_PARENT_INCOME				= $res_fin->fields['PARENT_INCOME'];
	$FA_STUDENT_CONTRIBUTION		= $res_fin->fields['STUDENT_CONTRIBUTION'];
	$FA_PARENT_CONTRIBUTION			= $res_fin->fields['PARENT_CONTRIBUTION'];
	$FA_INCOME_LEVEL				= $res_fin->fields['INCOME_LEVEL'];
	$FA_I551N0						= $res_fin->fields['I551N0'];
	$FA_PK_MARITAL_STATUS			= $res_fin->fields['PK_MARITAL_STATUS'];
	$FA_MARITAL_STATUS_DATE			= $res_fin->fields['MARITAL_STATUS_DATE'];
	$FA_ISIR_PROCESSED_DATE			= $res_fin->fields['ISIR_PROCESSED_DATE'];
	$FA_ISIR_SIGNED_DATE			= $res_fin->fields['ISIR_SIGNED_DATE'];
	$FA_ISIR_TRANS_NO				= $res_fin->fields['ISIR_TRANS_NO'];
	$FA_ISIR_CLEAR_PAY 				= $res_fin->fields['ISIR_CLEAR_PAY'];
	$FA_PK_AWARD_YEAR				= $res_fin->fields['PK_AWARD_YEAR'];
	$FA_ACADEMIC_YEAR_BEGIN			= $res_fin->fields['ACADEMIC_YEAR_BEGIN'];
	$FA_ACADEMIC_YEAR_END			= $res_fin->fields['ACADEMIC_YEAR_END'];
	$FA_AY_MONTH					= $res_fin->fields['AY_MONTH'];
	$FA_ACADEMIC_YEAR				= $res_fin->fields['ACADEMIC_YEAR'];
	$FA_PK_LENDER_MASTER			= $res_fin->fields['PK_LENDER_MASTER'];
	$FA_PK_COA_CATEGORY				= $res_fin->fields['PK_COA_CATEGORY'];
	$FA_PK_DEGREE_CERT				= $res_fin->fields['PK_DEGREE_CERT'];
	$FA_STUDENT_DEGREE				= $res_fin->fields['STUDENT_DEGREE'];
	$FA_ADVISOR						= $res_fin->fields['FA_ADVISOR'];
	
	if($FA_REPACKAGE_DATE == '0000-00-00')
		$FA_REPACKAGE_DATE = '';
	else
		$FA_REPACKAGE_DATE = date("m/d/Y",strtotime($FA_REPACKAGE_DATE));
		
	if($FA_MARITAL_STATUS_DATE == '0000-00-00')
		$FA_MARITAL_STATUS_DATE = '';
	else
		$FA_MARITAL_STATUS_DATE = date("m/d/Y",strtotime($FA_MARITAL_STATUS_DATE));
		
	if($FA_ISIR_PROCESSED_DATE == '0000-00-00')
		$FA_ISIR_PROCESSED_DATE = '';
	else
		$FA_ISIR_PROCESSED_DATE = date("m/d/Y",strtotime($FA_ISIR_PROCESSED_DATE));
		
	if($FA_ISIR_SIGNED_DATE == '0000-00-00')
		$FA_ISIR_SIGNED_DATE = '';
	else
		$FA_ISIR_SIGNED_DATE = date("m/d/Y",strtotime($FA_ISIR_SIGNED_DATE));
		
	if($FA_ACADEMIC_YEAR_BEGIN == '0000-00-00')
		$FA_ACADEMIC_YEAR_BEGIN = '';
	else
		$FA_ACADEMIC_YEAR_BEGIN = date("m/d/Y",strtotime($FA_ACADEMIC_YEAR_BEGIN));
		
	if($FA_ACADEMIC_YEAR_END == '0000-00-00')
		$FA_ACADEMIC_YEAR_END = '';
	else
		$FA_ACADEMIC_YEAR_END = date("m/d/Y",strtotime($FA_ACADEMIC_YEAR_END));
}
// DIAM-2228, DIAM-2231
// Prevent caching of this page
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
?>
<style>
#advice-validate-efc-number-FA_EFC_NO { line-height : 1.4;}
</style>
<div class="row">  
	<div class="col-sm-4"> 
		<div class="row">
			<div class="col-sm-12 form-group">
				<!-- Ticket # 1033 -->
				<select id="FA_PK_ENROLLMENT" name="FA_PK_ENROLLMENT" class="form-control" onchange="get_details_for_fa_from_program(this.value);get_month_from_program(this.value);" >
					<? /* Ticket # 1692 */
					$res_type = $db->Execute("SELECT S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, STATUS_DATE, STUDENT_STATUS, CODE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, IS_ACTIVE_ENROLLMENT, CAMPUS_CODE FROM S_STUDENT_ENROLLMENT LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
					while (!$res_type->EOF) { ?>
						<option value="<?=$res_type->fields['PK_STUDENT_ENROLLMENT']?>" <? if($res_type->fields['PK_STUDENT_ENROLLMENT'] == $FA_PK_STUDENT_ENROLLMENT) echo "selected"; ?> <? if($res_type->fields['IS_ACTIVE_ENROLLMENT'] == 1) echo "class='option_red'";  ?> ><?=$res_type->fields['BEGIN_DATE_1'].' - '.$res_type->fields['CODE'].' - '.$res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['CAMPUS_CODE']?></option>
					<?	$res_type->MoveNext();
					} /* Ticket # 1692 */ ?>
				</select>
				<span class="bar"></span> 
				<label for="FA_PK_ENROLLMENT"><?=ENROLLMENT?></label> 
			</div>
		</div>
		<div class="row">
			<div class="col-sm-12 ">
				<div class="d-flex theme-h-border"></div>
			</div>
		</div>
		<div class="d-flex">
			<div class="col-12 col-sm-6 form-group"> 
				<!-- Ticket # 1033 -->
				<select id="FA_ACADEMIC_YEAR_1" name="FA_ACADEMIC_YEAR_1" class="form-control" onchange="get_month_from_program(this.value)" >
					<option>Select</option>
					<? for($i = 1; $i <= 12 ; $i++){ ?>
						<option value="<?=$i?>" <? if($FA_ACADEMIC_YEAR == $i) echo "selected"; ?> ><?=$i?></option>
					<? } ?>
				</select>
				<label for="FA_ACADEMIC_YEAR_1"><?=ACADEMIC_YEAR_1?></label> 
			</div>
		
			<div class="col-12 col-sm-6 form-group"> 
				<select id="FA_PK_AWARD_YEAR" name="FA_PK_AWARD_YEAR" placeholder="Select" class="form-control">
					<option>Select</option>
					<? $res_type = $db->Execute("select PK_AWARD_YEAR,AWARD_YEAR from M_AWARD_YEAR WHERE ACTIVE = 1 order by BEGIN_DATE DESC");
					while (!$res_type->EOF) { ?>
						<option value="<?=$res_type->fields['PK_AWARD_YEAR']?>" <? if($FA_PK_AWARD_YEAR == $res_type->fields['PK_AWARD_YEAR']) echo "selected"; ?> ><?=$res_type->fields['AWARD_YEAR']?></option>
					<?	$res_type->MoveNext();
					} ?>
				</select>
				<label for="FA_PK_AWARD_YEAR"><?=AWARD_YEAR?></label> 
				<input type="hidden" id="HAS_FINANCIAL_FORM" name="HAS_FINANCIAL_FORM" value="1" />
				<input type="hidden" id="PK_STUDENT_FINANCIAL" name="PK_STUDENT_FINANCIAL" value="<?=$_REQUEST['PK_STUDENT_FINANCIAL']?>" />
				
			</div>
		</div>
		<div class="row">
			<div class="col-sm-12 ">
				<div class="d-flex theme-h-border"></div>
			</div>
		</div>
		<br />
		<div class="d-flex">   
			<div class="row">
				<div class="row">
					<div class="d-flex pd-2 mb-3" style="text-align: center;width:25rem;">  
					<div class="col-12 col-sm-4 form-group" style="text-align: left;"> 
						<label for="ACADEMIC"><?=ACADEMIC?></label>
					</div>			
					<div class="col-12 col-sm-4 form-group"> 
						<label for="ACADEMIC_YEAR_BEGIN"><?=ACADEMIC_YEAR_BEGIN?></label>
					</div>
					<div class="col-12 col-sm-4 form-group"> 
						<label for="ACADEMIC_YEAR_END"><?=ACADEMIC_YEAR_END?></label>						
					</div>
					</div>
				</div>
				<div class="d-flex">
					<div class="col-12 col-sm-4 form-group"> 
						<label for="ACADEMIC_YEAR"><?=ACADEMIC_YEAR?></label>
					</div>			
					<div class="col-12 col-sm-4 form-group"> 
						<input id="FA_ACADEMIC_YEAR_BEGIN" name="FA_ACADEMIC_YEAR_BEGIN" value="<?=$FA_ACADEMIC_YEAR_BEGIN?>" type="text" class="form-control date1" />
					</div>
					<div class="col-12 col-sm-4 form-group"> 
						<input id="FA_ACADEMIC_YEAR_END" name="FA_ACADEMIC_YEAR_END" value="<?=$FA_ACADEMIC_YEAR_END?>" type="text" class="form-control date2" />
					</div>
				</div>
				
				<div class="row " style="width:100%">
					<div class="col-12 col-sm-4"> 
						<label for="PERIOD"><?=PERIOD?></label>
					</div>
					<div class="col-12 col-sm-4"> 
						<a href="javascript:void(0)" onclick="add_period()" ><i class="fa fa-plus-circle"></i></a>
					</div>
				</div>
				<div id="period_div" >
					<? $period_count = 1;
					$res = $db->Execute("select PK_STUDENT_FINANCIAL_ACADEMY from S_STUDENT_FINANCIAL_ACADEMY WHERE PK_STUDENT_MASTER = '$_REQUEST[sid]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_FINANCIAL = '$_REQUEST[PK_STUDENT_FINANCIAL]' ");
					while (!$res->EOF) { 
						$_REQUEST['period_count'] 					= $period_count;
						$_REQUEST['PK_STUDENT_FINANCIAL_ACADEMY'] 	= $res->fields['PK_STUDENT_FINANCIAL_ACADEMY'];
						
						include("ajax_student_fa_period.php");
						$period_count++;
						
						$res->MoveNext();
					} ?>
				</div>
				
			</div>  
		</div>
		
		<div class="row">
			<div class="col-sm-12 ">
				<div class="d-flex theme-h-border"></div>
			</div>
		</div>
		<br />
		
		<div class="d-flex">  
			<div class="col-12 col-sm-4 form-group">
				<label for="AY_MONTH"><?=AY_MONTH?></label>
			</div>
			<div class="col-12 col-sm-4 form-group"> 
				<input id="FA_AY_MONTH" name="FA_AY_MONTH" value="<?=$FA_AY_MONTH?>" type="text" class="form-control" />
			</div>
		</div>
		
		<div class="row">
			<div class="col-sm-12 ">
				<div class="d-flex theme-h-border"></div>
			</div>
		</div>
		<br />
		<div class="d-flex">
			<div class="col-12 col-sm-6 form-group"> 
				<input id="FA_PROGRAM_LENGTH" name="FA_PROGRAM_LENGTH" value="<?=$FA_PROGRAM_LENGTH?>" type="text" class="form-control" />
				<span class="bar"></span>
				<label for="FA_PROGRAM_LENGTH"><?=PROGRAM_LENGTH?></label>
			</div>
			<div class="col-12 col-sm-6 form-group"> 
				<input id="FA_PROGRAM_COST" name="FA_PROGRAM_COST" value="<?=$FA_PROGRAM_COST?>" type="currency" class="form-control" />
				<span class="bar"></span>
				<label for="FA_PROGRAM_COST"><?=PROGRAM_COST?></label>
			</div>
		</div>
		
		<? /* Ticket # 1284 */
		$res_type = $db->Execute("select PK_CUSTOM_FIELDS,FIELD_NAME,PK_DATA_TYPES, PK_USER_DEFINED_FIELDS from S_CUSTOM_FIELDS WHERE S_CUSTOM_FIELDS.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  AND TAB = 'Financial Aid (AY Specific)' AND SECTION = 1 AND (PK_DEPARTMENT = '$_REQUEST[PK_DEPARTMENT]' OR PK_DEPARTMENT = -1) "); 
		while (!$res_type->EOF) { ?>
		<div class="d-flex ">
			<div class="col-12 col-sm-12 form-group">
				<? $PK_CUSTOM_FIELDS 	= $res_type->fields['PK_CUSTOM_FIELDS'];
				$PK_USER_DEFINED_FIELDS = $res_type->fields['PK_USER_DEFINED_FIELDS'];
				
				$res_1 = $db->Execute("select FIELD_VALUE from S_STUDENT_CUSTOM_FIELDS WHERE PK_STUDENT_MASTER = '$_REQUEST[sid]' AND PK_STUDENT_FINANCIAL = '$_REQUEST[PK_STUDENT_FINANCIAL]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CUSTOM_FIELDS = '$PK_CUSTOM_FIELDS' "); ?>
				
				<input name="FA_AY_PK_CUSTOM_FIELDS[]" type="hidden" value="<?=$PK_CUSTOM_FIELDS?>" />
				<input name="FA_AY_FIELD_NAME[]" type="hidden" value="<?=$res_type->fields['FIELD_NAME']?>" />
				<input name="FA_AY_PK_DATA_TYPES[]" type="hidden" value="<?=$res_type->fields['PK_DATA_TYPES']?>" />
				
				<? $date_cls = "";
				if($res_type->fields['PK_DATA_TYPES'] == 1 || $res_type->fields['PK_DATA_TYPES'] == 4) { 
					$FIELD_VALUE = $res_1->fields['FIELD_VALUE'];
					if($res_type->fields['PK_DATA_TYPES'] == 4) {
						$date_cls = "date"; 
						if($FIELD_VALUE != '')
							$FIELD_VALUE = date("m/d/Y",strtotime($FIELD_VALUE));
					} ?>
						
					<input name="FA_AY_CUSTOM_FIELDS_<?=$PK_CUSTOM_FIELDS?>" id="FA_AY_CUSTOM_FIELDS_<?=$res_type->fields['PK_CUSTOM_FIELDS']?>" type="text" class="form-control <?=$date_cls?>" value="<?=$FIELD_VALUE?>" />
					
					<span class="bar"></span> 
					<label for="FA_AY_CUSTOM_FIELDS_<?=$res_type->fields['PK_CUSTOM_FIELDS']?>"><?=$res_type->fields['FIELD_NAME']?></label>
					
				<? } else if($res_type->fields['PK_DATA_TYPES'] == 2) { ?>
					<select name="FA_AY_CUSTOM_FIELDS_<?=$PK_CUSTOM_FIELDS?>" id="FA_AY_CUSTOM_FIELDS_<?=$res_type->fields['PK_CUSTOM_FIELDS']?>" class="form-control" >
						<option value=""></option>
						<? $res_dd = $db->Execute("select * from S_USER_DEFINED_FIELDS_DETAIL WHERE ACTIVE = '1' AND PK_USER_DEFINED_FIELDS = '$PK_USER_DEFINED_FIELDS' ORDER BY OPTION_NAME ASC ");
						while (!$res_dd->EOF) { ?>
							<option value="<?=$res_dd->fields['PK_USER_DEFINED_FIELDS_DETAIL']?>" <? if($res_dd->fields['PK_USER_DEFINED_FIELDS_DETAIL'] == $res_1->fields['FIELD_VALUE']) echo 'selected = "selected"';?> ><?=$res_dd->fields['OPTION_NAME']?></option>
						<?	$res_dd->MoveNext();
						}	?>
					</select>
					
					<span class="bar"></span> 
					<label for="CUSTOM_FIELDS_<?=$res_type->fields['PK_CUSTOM_FIELDS']?>"><?=$res_type->fields['FIELD_NAME']?></label>
					
				<? } else if($res_type->fields['PK_DATA_TYPES'] == 3) {
					$OPTIONS = explode(",",$res_1->fields['FIELD_VALUE']);
					$res_dd = $db->Execute("select * from S_USER_DEFINED_FIELDS_DETAIL WHERE ACTIVE = '1' AND PK_USER_DEFINED_FIELDS = '$PK_USER_DEFINED_FIELDS' ORDER BY OPTION_NAME ASC "); ?>
					<div class="col-12 col-sm-6 focused">
						<span class="bar"></span> 
						<label for="CAMPUS"><?=$res_type->fields['FIELD_NAME']?></label>
					</div>
					<? while (!$res_dd->EOF) { 
						$checked = '';
						foreach($OPTIONS as $OPTION){
							if($OPTION == $res_dd->fields['PK_USER_DEFINED_FIELDS_DETAIL']) {
								$checked = 'checked="checked"';
								break;
							}
						} ?>
						<div class="d-flex">
							<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
								<input type="checkbox" class="custom-control-input" id="FA_AY_CUSTOM_FIELDS_<?=$PK_CUSTOM_FIELDS?>_<?=$res_dd->fields['PK_USER_DEFINED_FIELDS_DETAIL']?>" name="FA_AY_CUSTOM_FIELDS_<?=$PK_CUSTOM_FIELDS?>[]" value="<?=$res_dd->fields['PK_USER_DEFINED_FIELDS_DETAIL']?>" <?=$checked?> >
								<label class="custom-control-label" for="FA_AY_CUSTOM_FIELDS_<?=$PK_CUSTOM_FIELDS?>_<?=$res_dd->fields['PK_USER_DEFINED_FIELDS_DETAIL']?>"><?=$res_dd->fields['OPTION_NAME']?></label>
							</div>
						</div>
						
					<?	$res_dd->MoveNext();
					}
				} ?>
				
				
			</div>
		</div>
		<?	$res_type->MoveNext();
		} 
		/* Ticket # 1284 */ ?>
	</div>
	<div class="col-sm-4 theme-v-border" style="padding-top: 25px;" >
		<div class="d-flex">
			<div class="col-12 col-sm-6 form-group"> 
				<input id="FA_SCHOOL_CODE" name="FA_SCHOOL_CODE" value="<?=$FA_SCHOOL_CODE?>" type="text" class="form-control" />
				<span class="bar"></span>
				<label for="FA_SCHOOL_CODE"><?=SCHOOL_CODE?></label>
			</div>
			
			<div class="col-12 col-sm-6 form-group"> 
				<select id="FA_PK_DEPENDENT_STATUS" name="FA_PK_DEPENDENT_STATUS" class="form-control">
					<option></option>
					<? $res_type = $db->Execute("select PK_DEPENDENT_STATUS,CODE,DESCRIPTION from M_DEPENDENT_STATUS WHERE ACTIVE = 1 order by DESCRIPTION ASC");
					while (!$res_type->EOF) { ?>
						<option value="<?=$res_type->fields['PK_DEPENDENT_STATUS']?>" <? if($FA_PK_DEPENDENT_STATUS == $res_type->fields['PK_DEPENDENT_STATUS']) echo "selected"; ?> ><?=$res_type->fields['CODE'].' - '.$res_type->fields['DESCRIPTION']?></option>
					<?	$res_type->MoveNext();
					} ?>
				</select>
				<span class="bar"></span>
				<label for="FA_PK_DEPENDENT_STATUS"><?=DEPENDENCY_STATUS?></label>
			</div>
		</div>
		
		<div class="d-flex">
			<div class="col-12 col-sm-6 form-group"> 
				<input id="FA_EFC_NO" name="FA_EFC_NO" value="<?=$FA_EFC_NO?>" maxlength="7" class="form-control " onkeypress="check_number_validations(this);" onchange="check_number_validations(this);"  />
				<span class="bar"></span>
				<label for="FA_EFC_NO"><?=EFC_NO?></label>
			</div>
			
			<div class="col-12 col-sm-6 form-group"> 
				<select id="FA_AUTOMATIC_ZERO_EFC" name="FA_AUTOMATIC_ZERO_EFC" class="form-control">
					<option ></option>
					<option value="1" <? if($FA_AUTOMATIC_ZERO_EFC == 1) echo "selected"; ?> >Yes</option>
					<option value="2" <? if($FA_AUTOMATIC_ZERO_EFC == 2) echo "selected"; ?> >No</option>
				</select>
				<span class="bar"></span>
				<label for="FA_AUTO_ZERO_EFC"><?=AUTO_ZERO_EFC?></label>
			</div>
		</div>
		
		<!--<div class="d-flex">
			<div class="col-12 col-sm-6 form-group"> 
				<select id="FA_YEAR_ROUND_PELL" name="FA_YEAR_ROUND_PELL" class="form-control">
					<option ></option>
					<option value="1" <? if($FA_YEAR_ROUND_PELL == 1) echo "selected"; ?> >Yes</option>
					<option value="2" <? if($FA_YEAR_ROUND_PELL == 2) echo "selected"; ?> >No</option>
				</select>
				<span class="bar"></span>
				<label for="FA_YEAR_ROUND_PELL"><?=YEAR_ROUND_PELL?></label>
			</div>
		</div>-->
		
		<div class="row">
			<div class="col-sm-12 ">
				<div class="d-flex theme-h-border"></div>
			</div>
		</div>
		<br />
		<div class="d-flex">
			<div class="col-12 col-sm-6 form-group"> 
				<input id="FA_ISIR_TRANS_NO" name="FA_ISIR_TRANS_NO" value="<?=$FA_ISIR_TRANS_NO?>" type="number" min="0" oninput="validity.valid||(value='');" class="form-control" />
				<span class="bar"></span>
				<label for="FA_ISIR_TRANS_NO"><?=ISIR_TRANS_NO?></label>
			</div>
			<div class="col-12 col-sm-6 form-group">   
				<select id="FA_ISIR_CLEAR_PAY" name="FA_ISIR_CLEAR_PAY" class="form-control">
					<option ></option>
					<option value="1" <? if($FA_ISIR_CLEAR_PAY == 1) echo "selected"; ?> >Yes</option>
					<option value="2" <? if($FA_ISIR_CLEAR_PAY == 2) echo "selected"; ?> >No</option>
				</select>
				<span class="bar"></span>
				<label for="FA_ISIR_CLEAR_PAY"><?=ISIR_CLEAR_PAY?></label>
			</div>
		</div>
		<div class="d-flex">
			<div class="col-12 col-sm-6 form-group"> 
				<input id="FA_ISIR_PROCESSED_DATE" name="FA_ISIR_PROCESSED_DATE" value="<?=$FA_ISIR_PROCESSED_DATE?>" type="text" class="form-control date" />
				<span class="bar"></span>
				<label for="FA_ISIR_PROCESSED_DATE"><?=ISIR_PROCESSED_DATE?></label>
			</div>
			<!--<div class="col-12 col-sm-6 form-group"> 
				<input id="ISIR_SIGNED_DATE" name="FA_ISIR_SIGNED_DATE" value="<?=$FA_ISIR_SIGNED_DATE?>" type="text" class="form-control date" />
				<span class="bar"></span>
				<label for="FA_ISIR_SIGNED_DATE"><?=FA_ISIR_SIGNED_DATE?></label>
			</div>-->
		</div>
		<div class="row">
			<div class="col-sm-12 ">
				<div class="d-flex theme-h-border"></div>
			</div>
		</div>
		<br />
		<div class="d-flex">
			<div class="col-12 col-sm-6 form-group"> 
				<select id="FA_SELECTED_FOR_VERIFICATION" name="FA_SELECTED_FOR_VERIFICATION" class="form-control">
					<option ></option>
					<option value="1" <? if($FA_SELECTED_FOR_VERIFICATION == 1) echo "selected"; ?> >Yes</option>
					<option value="2" <? if($FA_SELECTED_FOR_VERIFICATION == 2) echo "selected"; ?> >No</option>
					<option value="3" <? if($FA_SELECTED_FOR_VERIFICATION == 3) echo "selected"; ?> >Change in Verification Tracking Group</option>
				</select>
				<span class="bar"></span>
				<label for="FA_SELECTED_FOR_VERIFICATION"><?=SELECTED_FOR_VERIFICATION?></label>
			</div>
			
		</div>
		<div class="d-flex">
			<div class="col-12 col-sm-6 form-group"> 
				<select id="FA_PROFESSIONAL_JUDGEMENT" name="FA_PROFESSIONAL_JUDGEMENT" class="form-control">
					<option ></option>
					<option value="1" <? if($FA_PROFESSIONAL_JUDGEMENT == 1) echo "selected"; ?> >Yes</option>
					<option value="2" <? if($FA_PROFESSIONAL_JUDGEMENT == 2) echo "selected"; ?> >No</option>
				</select>
				<span class="bar"></span>
				<label for="FA_PROFESSIONAL_JUDGEMENT"><?=PROFESSIONAL_JUDGEMENT?></label>
			</div>
			<div class="col-12 col-sm-6 form-group"> 
				<select id="FA_PK_DEPENDENCY_OVERRIDE" name="FA_PK_DEPENDENCY_OVERRIDE" class="form-control">
					<option ></option>
					<? $res_type = $db->Execute("select PK_DEPENDENCY_OVERRIDE,DEPENDENCY_OVERRIDE from M_DEPENDENCY_OVERRIDE WHERE ACTIVE = 1 order by DEPENDENCY_OVERRIDE ASC");
					while (!$res_type->EOF) { ?>
						<option value="<?=$res_type->fields['PK_DEPENDENCY_OVERRIDE']?>" <? if($FA_PK_DEPENDENCY_OVERRIDE == $res_type->fields['PK_DEPENDENCY_OVERRIDE']) echo "selected"; ?> ><?=$res_type->fields['DEPENDENCY_OVERRIDE']?></option>
					<?	$res_type->MoveNext();
					} ?>
				</select>
				<span class="bar"></span>
				<label for="FA_PK_DEPENDENCY_OVERRIDE"><?=OVERRIDE?></label>
			</div>
		</div>
		
		<div class="row">
			<div class="col-sm-12 ">
				<div class="d-flex theme-h-border"></div>
			</div>
		</div>
		<br />
		
		<div class="d-flex">
			<div class="col-12 col-sm-6 form-group"> 
				<input id="FA_NO_OF_DEPENDENTS" name="FA_NO_OF_DEPENDENTS" value="<?=$FA_NO_OF_DEPENDENTS?>" type="number" class="form-control" />
				<span class="bar"></span>
				<label for="FA_NO_OF_DEPENDENTS"><?=NO_OF_DEPENDENTS?></label>
			</div>
			
			<div class="col-12 col-sm-6 form-group"> 
				<input id="FA_NUMBER_IN_COLLEGE" name="FA_NUMBER_IN_COLLEGE" value="<?=$FA_NUMBER_IN_COLLEGE?>" type="number" class="form-control" />
				<span class="bar"></span>
				<label for="FA_NUMBER_IN_COLLEGE"><?=NUMBER_IN_COLLEGE?></label>
			</div>
		</div>
		
		<div class="d-flex">
			<div class="col-12 col-sm-6 form-group"> 
				<select id="FA_PK_MARITAL_STATUS" name="FA_PK_MARITAL_STATUS" class="form-control">
					<option selected></option>
					<? $res_type = $db->Execute("select * from Z_MARITAL_STATUS WHERE ACTIVE = 1 order by MARITAL_STATUS ASC");
					while (!$res_type->EOF) { ?>
						<option value="<?=$res_type->fields['PK_MARITAL_STATUS']?>"  <? if($res_type->fields['PK_MARITAL_STATUS'] == $FA_PK_MARITAL_STATUS) echo "selected"; ?> ><?=$res_type->fields['MARITAL_STATUS']?></option>
					<?	$res_type->MoveNext();
					} ?>
				</select>
				<span class="bar"></span>
				<label for="FA_MARITAL_STATUS"><?=MARITAL_STATUS?></label>
			</div>
			<div class="col-12 col-sm-6 form-group"> 
				<input id="FA_MARITAL_STATUS_DATE" name="FA_MARITAL_STATUS_DATE" value="<?=$FA_MARITAL_STATUS_DATE?>" type="text" class="form-control date" />
				<span class="bar"></span>
				<label for="FA_MARITAL_STATUS_DATE"><?=MARITAL_STATUS_DATE?></label>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-12 ">
				<div class="d-flex theme-h-border"></div>
			</div>
		</div>
		<br />
		
		<div class="d-flex">
			<div class="col-12 col-sm-6 form-group"> 
				<select id="FA_PK_ELIGIBLE_CITIZEN" name="FA_PK_ELIGIBLE_CITIZEN" class="form-control">
					<option ></option>
					<? $res_type = $db->Execute("select PK_ELIGIBLE_CITIZEN,ELIGIBLE_CITIZEN from M_ELIGIBLE_CITIZEN WHERE ACTIVE = 1 order by ELIGIBLE_CITIZEN ASC");
					while (!$res_type->EOF) { ?>
						<option value="<?=$res_type->fields['PK_ELIGIBLE_CITIZEN']?>" <? if($FA_PK_ELIGIBLE_CITIZEN == $res_type->fields['PK_ELIGIBLE_CITIZEN']) echo "selected"; ?> ><?=$res_type->fields['ELIGIBLE_CITIZEN']?></option>
					<?	$res_type->MoveNext();
					} ?>
				</select>
				<span class="bar"></span>
				<label for="FA_PK_ELIGIBLE_CITIZEN"><?=ELIGIBLE_CITIZEN?></label>
			</div>
			<div class="col-12 col-sm-6 form-group"> 
				<input id="FA_I551N0" name="FA_I551N0" value="<?=$FA_I551N0?>" type="text" class="form-control" />
				<span class="bar"></span>
				<label for="I551N0"><?=I551N0?></label>
			</div>
		</div>
		
		<div class="row">
			<div class="col-sm-12 ">
				<div class="d-flex theme-h-border"></div>
			</div>
		</div>
		<br />
		
		<div class="d-flex">
			<div class="col-12 col-sm-6 form-group">   
				<select id="FA_PK_DEGREE_CERT" name="FA_PK_DEGREE_CERT" class="form-control">
					<option></option>
					<? $res_type = $db->Execute("select PK_DEGREE_CERT,CODE,DESCRIPTION from M_DEGREE_CERT WHERE ACTIVE = 1 order by DESCRIPTION ASC");
					while (!$res_type->EOF) { ?>
						<option value="<?=$res_type->fields['PK_DEGREE_CERT']?>" <? if($FA_PK_DEGREE_CERT == $res_type->fields['PK_DEGREE_CERT']) echo "selected"; ?> ><?=$res_type->fields['CODE'].' - '.$res_type->fields['DESCRIPTION']?></option>
					<?	$res_type->MoveNext();
					} ?>
				</select>
				<span class="bar"></span>
				<label for="FA_DEGREE_CERT"><?=DEGREE_CERT?></label>
			</div>
			<div class="col-12 col-sm-6 form-group">   
				<select id="FA_STUDENT_DEGREE" name="FA_STUDENT_DEGREE" class="form-control">
					<option></option>
					<option value="1" <? if($FA_STUDENT_DEGREE == 1) echo "selected"; ?> >Yes</option>
					<option value="2" <? if($FA_STUDENT_DEGREE == 2) echo "selected"; ?> >No</option>
				</select>
				<span class="bar"></span>
				<label for="FA_STUDENT_DEGREE"><?=STUDENT_DEGREE?></label>
			</div>
		</div>
		
		<div class="row">
			<div class="col-sm-12 ">
				<div class="d-flex theme-h-border"></div>
			</div>
		</div>
		<br />
		
		<div class="d-flex">
			<div class="col-12 col-sm-6 form-group"> 
				<select id="FA_PK_VA_STUDENT" name="FA_PK_VA_STUDENT" class="form-control">
					<option ></option>
					<? $res_type = $db->Execute("select PK_VA_STUDENT,VA_STUDENT from M_VA_STUDENT WHERE ACTIVE = 1 order by VA_STUDENT ASC");
					while (!$res_type->EOF) { ?>
						<option value="<?=$res_type->fields['PK_VA_STUDENT']?>" <? if($FA_PK_VA_STUDENT == $res_type->fields['PK_VA_STUDENT']) echo "selected"; ?> ><?=$res_type->fields['VA_STUDENT']?></option>
					<?	$res_type->MoveNext();
					} ?>
				</select>
				<span class="bar"></span>
				<label for="FA_PK_VA_STUDENT"><?=VA_STUDENT?></label>
			</div>
			<!--<div class="col-12 col-sm-6 form-group"> 
				<select id="FA_S_FOREIGN" name="FA_IS_FOREIGN" class="form-control">
					<option ></option>
					<option value="1" <? if($FA_IS_FOREIGN == 1) echo "selected"; ?> >Yes</option>
					<option value="2" <? if($FA_IS_FOREIGN == 2) echo "selected"; ?> >No</option>
				</select>
				<span class="bar"></span>
				<label for="FA_IS_FOREIGN"><?=FOREIGN?></label>
			</div>-->
		</div>
		
		<!--<div class="d-flex">
			<div class="col-12 col-sm-6 form-group"> 
				<input id="FA_DEPENDENTS_IN_COLLEGE" name="FA_DEPENDENTS_IN_COLLEGE" value="<?=$FA_DEPENDENTS_IN_COLLEGE?>" type="text" class="form-control" />
				<span class="bar"></span>
				<label for="FA_DEPENDENTS_IN_COLLEGE"><?=DEPENDENTS_IN_COLLEGE?></label>
			</div>
		</div>-->
	</div>  	
	<div class="col-sm-4 theme-v-border" style="padding-top: 25px;" >
		<div class="d-flex">
			<div class="col-12 col-sm-6 form-group"> 
				<input id="FA_COA" name="FA_COA" value="<?=number_format_value_checker($FA_COA,2)?>" type="currency" class="form-control" /><!-- // DIAM-1159-->
				<span class="bar"></span>
				<label for="FA_COA"><?=COA?></label>
			</div>
			
			<div class="col-12 col-sm-6 form-group"> 
				<select id="FA_PK_COA_CATEGORY" name="FA_PK_COA_CATEGORY" class="form-control">
					<option></option>
					<? $res_type = $db->Execute("select PK_COA_CATEGORY,CODE,DESCRIPTION from M_COA_CATEGORY WHERE ACTIVE = 1 order by DESCRIPTION ASC");
					while (!$res_type->EOF) { ?>
						<option value="<?=$res_type->fields['PK_COA_CATEGORY']?>" <? if($FA_PK_COA_CATEGORY == $res_type->fields['PK_COA_CATEGORY']) echo "selected"; ?> ><?=$res_type->fields['CODE'].' - '.$res_type->fields['DESCRIPTION']?></option>
					<?	$res_type->MoveNext();
					} ?>
				</select>
				<span class="bar"></span>
				<label for="FA_COA_CATEGORY"><?=COA_CATEGORY?></label>
			</div>
		</div>
		<div class="d-flex">
			<div class="col-12 col-sm-6 form-group"> 
				<input id="FA_NEED" name="FA_NEED" value="<?=number_format_value_checker($FA_NEED,2)?>" type="currency" class="form-control" /> <!-- // DIAM-1159-->
				<span class="bar"></span>
				<label for="FA_NEED"><?=NEED?></label>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-12 ">
				<div class="d-flex theme-h-border"></div>
			</div>
		</div>
		<br />
		<div class="d-flex">
			<div class="col-12 col-sm-6 form-group"> 
				<input id="FA_INCOME_LEVEL" name="FA_INCOME_LEVEL" value="<?=$FA_INCOME_LEVEL?>" type="number" min="0" oninput="validity.valid||(value='');" class="form-control" />
				<span class="bar"></span>
				<label for="FA_INCOME_LEVEL"><?=INCOME_LEVEL?></label>
			</div>
		</div>
		<div class="d-flex">
			<div class="col-12 col-sm-6 form-group"> 
				<input id="FA_STUDENT_INCOME" name="FA_STUDENT_INCOME" value="<?=$FA_STUDENT_INCOME?>" type="number" min="0" oninput="validity.valid||(value='');" class="form-control" />
				<span class="bar"></span>
				<label for="FA_STUDENT_INCOME"><?=STUDENT_INCOME?></label>
			</div>
			<div class="col-12 col-sm-6 form-group"> 
				<input id="FA_STUDENT_CONTRIBUTION" name="FA_STUDENT_CONTRIBUTION" value="<?=$FA_STUDENT_CONTRIBUTION?>" type="number" min="0" oninput="validity.valid||(value='');" class="form-control" />
				<span class="bar"></span>
				<label for="FA_STUDENT_CONTRIBUTION"><?=STUDENT_CONTRIBUTION?></label>
			</div>
			
		</div>
		<div class="d-flex">
			<div class="col-12 col-sm-6 form-group"> 
				<input id="FA_PARENT_INCOME" name="FA_PARENT_INCOME" value="<?=$FA_PARENT_INCOME?>" type="number" min="0" oninput="validity.valid||(value='');" class="form-control" />
				<span class="bar"></span>
				<label for="FA_PARENT_INCOME"><?=PARENT_INCOME?></label>
			</div>
			<div class="col-12 col-sm-6 form-group"> 
				<input id="FA_PARENT_CONTRIBUTION" name="FA_PARENT_CONTRIBUTION" value="<?=$FA_PARENT_CONTRIBUTION?>" type="number" min="0" oninput="validity.valid||(value='');" class="form-control" />
				<span class="bar"></span>
				<label for="FA_PARENT_CONTRIBUTION"><?=PARENT_CONTRIBUTION?></label>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-12 ">
				<div class="d-flex theme-h-border"></div>
			</div>
		</div>
		<br />
		
		<div class="d-flex">
			<div class="col-12 col-sm-6 form-group">     
				<select id="FA_PK_LENDER_MASTER" name="FA_PK_LENDER_MASTER" class="form-control">
					<option></option>
					<? /* Ticket # 1692  */
					$res_type = $db->Execute("select PK_LENDER_MASTER, LENDER, ACTIVE from S_LENDER_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by ACTIVE DESC, LENDER ASC");
					while (!$res_type->EOF) { 
						$option_label = $res_type->fields['LENDER'];
						if($res_type->fields['ACTIVE'] == 0)
							$option_label .= " (Inactive)"; ?>
						<option value="<?=$res_type->fields['PK_LENDER_MASTER']?>"  <? if($res_type->fields['PK_LENDER_MASTER'] == $FA_PK_LENDER_MASTER) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label?></option>
					<?	$res_type->MoveNext();
					} /* Ticket # 1692  */ ?>
				</select>
				<span class="bar"></span>
				<label for="FA_LENDER"><?=LENDER?></label>
			</div>
			<div class="col-12 col-sm-6 form-group"> 
				<input id="FA_REPACKAGE_DATE" name="FA_REPACKAGE_DATE" value="<?=$FA_REPACKAGE_DATE?>" type="text" class="form-control date" />
				<span class="bar"></span>
				<label for="FA_REPACKAGE_DATE"><?=REPACKAGE_DATE?></label>
			</div>
		</div>
		
		<div class="row">
			<div class="col-sm-12 ">
				<div class="d-flex theme-h-border"></div>
			</div>
		</div>
		<br />
		
		<div class="d-flex">
			<div class="col-12 col-sm-6 form-group">   
				<select id="FA_ADVISOR" name="FA_ADVISOR" value="<?=$FA_ADVISOR?>" class="form-control">
					<option></option>
					<? $res_type = $db->Execute("select S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER,CONCAT(FIRST_NAME,' ',LAST_NAME) AS NAME from S_EMPLOYEE_MASTER, M_DEPARTMENT , S_EMPLOYEE_DEPARTMENT  WHERE S_EMPLOYEE_MASTER.ACTIVE = 1 AND S_EMPLOYEE_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_DEPARTMENT_MASTER = 4 AND S_EMPLOYEE_DEPARTMENT.PK_DEPARTMENT = M_DEPARTMENT.PK_DEPARTMENT AND S_EMPLOYEE_DEPARTMENT.PK_EMPLOYEE_MASTER = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER AND IS_ADMIN = 0 order by CONCAT(FIRST_NAME,' ',LAST_NAME) ASC ");
					while (!$res_type->EOF) { ?>
						<option value="<?=$res_type->fields['PK_EMPLOYEE_MASTER']?>" <? if($FA_ADVISOR == $res_type->fields['PK_EMPLOYEE_MASTER']) echo "selected"; ?> ><?=$res_type->fields['NAME']?></option>
					<?	$res_type->MoveNext();
					} ?>
				</select>
				<span class="bar"></span>
				<label for="FA_ADVISOR"><?=FA_ADVISOR?></label>
			</div>
			<div class="col-12 col-sm-6 form-group"> 
				<input id="FA_UPDATED" name="FA_UPDATED" value="<?=$FA_UPDATED?>" type="text" class="form-control" />
				<span class="bar"></span>
				<label for="FA_UPDATED"><?=UPDATED?></label>
			</div>
		</div>
		
		<? $res_type = $db->Execute("select PK_CUSTOM_FIELDS,FIELD_NAME,PK_DATA_TYPES, PK_USER_DEFINED_FIELDS from S_CUSTOM_FIELDS WHERE S_CUSTOM_FIELDS.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_CUSTOM_FIELDS.ACTIVE = 1 AND TAB = 'Financial Aid' AND SECTION = 1 AND (PK_DEPARTMENT = '$_REQUEST[PK_DEPARTMENT]' OR PK_DEPARTMENT = -1) "); 
		while (!$res_type->EOF) { ?>
		<div class="d-flex ">
			<div class="col-12 col-sm-12 form-group">
				<? $PK_CUSTOM_FIELDS 	= $res_type->fields['PK_CUSTOM_FIELDS'];
				$PK_USER_DEFINED_FIELDS = $res_type->fields['PK_USER_DEFINED_FIELDS'];
				
				$res_1 = $db->Execute("select FIELD_VALUE from S_STUDENT_CUSTOM_FIELDS WHERE PK_STUDENT_MASTER = '$_REQUEST[sid]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CUSTOM_FIELDS = '$PK_CUSTOM_FIELDS' "); ?>
				
				<input name="PK_CUSTOM_FIELDS[]" type="hidden" value="<?=$PK_CUSTOM_FIELDS?>" />
				<input name="FIELD_NAME[]" type="hidden" value="<?=$res_type->fields['FIELD_NAME']?>" />
				<input name="PK_DATA_TYPES[]" type="hidden" value="<?=$res_type->fields['PK_DATA_TYPES']?>" />
				
				<? $date_cls = "";
				if($res_type->fields['PK_DATA_TYPES'] == 1 || $res_type->fields['PK_DATA_TYPES'] == 4) { 
					$FIELD_VALUE = $res_1->fields['FIELD_VALUE'];
					if($res_type->fields['PK_DATA_TYPES'] == 4) {
						$date_cls = "date"; 
						if($FIELD_VALUE != '')
							$FIELD_VALUE = date("m/d/Y",strtotime($FIELD_VALUE));
					} ?>
						
					<input name="CUSTOM_FIELDS_<?=$PK_CUSTOM_FIELDS?>" id="CUSTOM_FIELDS_<?=$res_type->fields['PK_CUSTOM_FIELDS']?>" type="text" class="form-control <?=$date_cls?>" value="<?=$FIELD_VALUE?>" />
					
					<span class="bar"></span> 
					<label for="CUSTOM_FIELDS_<?=$res_type->fields['PK_CUSTOM_FIELDS']?>"><?=$res_type->fields['FIELD_NAME']?></label>
					
				<? } else if($res_type->fields['PK_DATA_TYPES'] == 2) { ?>
					<select name="CUSTOM_FIELDS_<?=$PK_CUSTOM_FIELDS?>" id="CUSTOM_FIELDS_<?=$res_type->fields['PK_CUSTOM_FIELDS']?>" class="form-control" >
						<option value=""></option>
						<? $res_dd = $db->Execute("select * from S_USER_DEFINED_FIELDS_DETAIL WHERE ACTIVE = '1' AND PK_USER_DEFINED_FIELDS = '$PK_USER_DEFINED_FIELDS' ORDER BY OPTION_NAME ASC ");
						while (!$res_dd->EOF) { ?>
							<option value="<?=$res_dd->fields['PK_USER_DEFINED_FIELDS_DETAIL']?>" <? if($res_dd->fields['PK_USER_DEFINED_FIELDS_DETAIL'] == $res_1->fields['FIELD_VALUE']) echo 'selected = "selected"';?> ><?=$res_dd->fields['OPTION_NAME']?></option>
						<?	$res_dd->MoveNext();
						}	?>
					</select>
					
					<span class="bar"></span> 
					<label for="CUSTOM_FIELDS_<?=$res_type->fields['PK_CUSTOM_FIELDS']?>"><?=$res_type->fields['FIELD_NAME']?></label>
					
				<? } else if($res_type->fields['PK_DATA_TYPES'] == 3) {
					$OPTIONS = explode(",",$res_1->fields['FIELD_VALUE']);
					$res_dd = $db->Execute("select * from S_USER_DEFINED_FIELDS_DETAIL WHERE ACTIVE = '1' AND PK_USER_DEFINED_FIELDS = '$PK_USER_DEFINED_FIELDS' ORDER BY OPTION_NAME ASC "); ?>
					<div class="col-12 col-sm-6 focused">
						<span class="bar"></span> 
						<label for="CAMPUS"><?=$res_type->fields['FIELD_NAME']?></label>
					</div>
					<? while (!$res_dd->EOF) { 
						$checked = '';
						foreach($OPTIONS as $OPTION){
							if($OPTION == $res_dd->fields['PK_USER_DEFINED_FIELDS_DETAIL']) {
								$checked = 'checked="checked"';
								break;
							}
						} ?>
						<div class="d-flex">
							<div class="col-12 col-sm-4 custom-control custom-checkbox form-group" >
								<input type="checkbox" class="custom-control-input" id="CUSTOM_FIELDS_<?=$PK_CUSTOM_FIELDS?>_<?=$res_dd->fields['PK_USER_DEFINED_FIELDS_DETAIL']?>" name="CUSTOM_FIELDS_<?=$PK_CUSTOM_FIELDS?>[]" value="<?=$res_dd->fields['PK_USER_DEFINED_FIELDS_DETAIL']?>" <?=$checked?> >
								<label class="custom-control-label" for="CUSTOM_FIELDS_<?=$PK_CUSTOM_FIELDS?>_<?=$res_dd->fields['PK_USER_DEFINED_FIELDS_DETAIL']?>"><?=$res_dd->fields['OPTION_NAME']?></label>
							</div>
						</div>
						
					<?	$res_dd->MoveNext();
					}
				} ?>
				
				
			</div>
		</div>
		<?	$res_type->MoveNext();
		} ?>
		
		<div class="row">
			<div class=" col-sm-12">
				<? if($FINANCE_ACCESS == 2 || $FINANCE_ACCESS == 3){ ?>
				<button onclick="validate_form(1)" type="button" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
				<button onclick="validate_form(0)" type="button" class="btn waves-effect waves-light btn-info"><?=SAVE_EXIT?></button>
				<button type="button" onclick="window.location.href='manage_student?t=<?=$_REQUEST['t']?>'" class="btn waves-effect waves-light btn-dark"><?=CANCEL?></button>
				<? if($_REQUEST['PK_STUDENT_FINANCIAL'] != ''){ ?>
				<button onclick="delete_row('<?=$_REQUEST['PK_STUDENT_FINANCIAL']?>','FA')" type="button" class="btn waves-effect waves-light btn-dark"><?=DELETE?></button>
				<? } 
				} ?>
			</div>
		</div>
	</div>
</div>
