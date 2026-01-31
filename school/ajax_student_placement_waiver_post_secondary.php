<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/student.php");
require_once("check_access.php");

$PLACEMENT_ACCESS 	= check_access('PLACEMENT_ACCESS');
if($PLACEMENT_ACCESS == 0){
	header("location:../index");
	exit;
}
$PK_STUDENT_WAIVER 			= $_REQUEST['PK_STUDENT_WAIVER'];
$placement_waiver_count_ps	= $_REQUEST['placement_waiver_count_ps'];
$PK_STUDENT_MASTER			= $_REQUEST['id'];
$PK_STUDENT_ENROLLMENT		= $_REQUEST['eid'];

if($PK_STUDENT_WAIVER == '') {
	$WAIVER_FORM_PK_WAIVER_TYPE 		= '';
	$WAIVER_FORM_PK_PLACEMENT_STATUS 	= '';
	$WAIVER_FORM_PK_ENROLLMENT_STATUS	= '';
	$WAIVER_FORM_POST_SEC_INSTITUTION	= '';
	$WAIVER_FORM_PROGRAM_MAJOR			= '';
	$WAIVER_FORM_MILITARY_BRANCH		= '';
	$WAIVER_FORM_WAIVER_PHONE			= '';
	$WAIVER_ADDRESS						= '';
	$WAIVER_FORM_WAIVER_CITY			= '';
	$WAIVER_PK_STATES					= '';
	$WAIVER_FORM_WAIVER_ZIP				= '';
	$WAIVER_FORM_WAIVER_NOTES			= '';
	$WAIVER_FORM_ACTIVE					= '';
} else {
	$res = $db->Execute("select * from S_STUDENT_WAIVER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_STUDENT_WAIVER = '$PK_STUDENT_WAIVER' AND PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND PK_STUDENT_ENROLLMENT = '$PK_STUDENT_ENROLLMENT'");
	$WAIVER_FORM_PK_PLACEMENT_STATUS 	= $res->fields['PK_PLACEMENT_STATUS'];
	$WAIVER_FORM_PK_ENROLLMENT_STATUS	= $res->fields['PK_ENROLLMENT_STATUS'];
	$WAIVER_FORM_POST_SEC_INSTITUTION	= $res->fields['POST_SEC_INSTITUTION'];
	$WAIVER_FORM_PROGRAM_MAJOR			= $res->fields['PROGRAM_MAJOR'];
	$WAIVER_FORM_MILITARY_BRANCH		= $res->fields['MILITARY_BRANCH'];
	$WAIVER_FORM_WAIVER_PHONE			= $res->fields['WAIVER_PHONE'];
	$WAIVER_FORM_WAIVER_ADDRESS			= $res->fields['WAIVER_ADDRESS'];
	$WAIVER_FORM_WAIVER_CITY			= $res->fields['WAIVER_CITY'];
	$WAIVER_PK_STATES					= $res->fields['WAIVER_PK_STATES'];
	$WAIVER_FORM_WAIVER_ZIP				= $res->fields['WAIVER_ZIP'];
	$WAIVER_FORM_ACTIVE					= $res->fields['ACTIVE'];
	$WAIVER_FORM_WAIVER_NOTES			= $res->fields['WAIVER_NOTES'];
} ?>
<div id="placement_waiver_div_<?=$placement_waiver_count_ps?>" class="m-b-20" style="border: 1px solid #eceaea;padding: 2rem;padding-bottom: 1rem;padding-top: 1rem;border-radius: 3px;">
	<? if($PLACEMENT_ACCESS == 2 || $PLACEMENT_ACCESS == 3 ){ ?>
	<div class="d-flex">
		<div class="col-12 col-sm-12">
			<div class="col-12 col-sm-1" style="float: right;">
				<div class="form-group">
					<a href="javascript:void(0);" onclick="delete_row(<?=$placement_waiver_count_ps?>,'waiver')" title="Delete" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i> </a>
				</div>
			</div>
		</div>
	</div>
	<? } ?>
	<div class="d-flex flex-wrap">
		<div class="col-12 col-md-6">
			<div class="row">
				<div class="col-12">
					<div class="form-group m-b-30">
						<select id="WAIVER_FORM_PK_ENROLLMENT_STATUS_<?=$placement_waiver_count_ps?>" name="WAIVER_FORM_PK_ENROLLMENT_STATUS[<?=$placement_waiver_count_ps?>]" class="form-control">
							<? /* Ticket # 1694 */
							$res_type = $db->Execute("SELECT S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT, STATUS_DATE, STUDENT_STATUS, CODE, IF(BEGIN_DATE = '0000-00-00','',DATE_FORMAT(BEGIN_DATE, '%m/%d/%Y' )) AS BEGIN_DATE_1, IS_ACTIVE_ENROLLMENT, CAMPUS_CODE FROM S_STUDENT_ENROLLMENT LEFT JOIN S_STUDENT_CAMPUS ON S_STUDENT_CAMPUS.PK_STUDENT_ENROLLMENT = S_STUDENT_ENROLLMENT.PK_STUDENT_ENROLLMENT LEFT JOIN S_CAMPUS ON S_CAMPUS.PK_CAMPUS = S_STUDENT_CAMPUS.PK_CAMPUS LEFT JOIN M_CAMPUS_PROGRAM ON M_CAMPUS_PROGRAM.PK_CAMPUS_PROGRAM =  S_STUDENT_ENROLLMENT.PK_CAMPUS_PROGRAM LEFT JOIN M_STUDENT_STATUS ON M_STUDENT_STATUS.PK_STUDENT_STATUS = S_STUDENT_ENROLLMENT.PK_STUDENT_STATUS LEFT JOIN S_TERM_MASTER ON S_TERM_MASTER.PK_TERM_MASTER = S_STUDENT_ENROLLMENT.PK_TERM_MASTER WHERE S_STUDENT_ENROLLMENT.PK_STUDENT_MASTER = '$PK_STUDENT_MASTER' AND S_STUDENT_ENROLLMENT.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
							while (!$res_type->EOF) { ?>
								<option value="<?=$res_type->fields['PK_STUDENT_ENROLLMENT']?>" <? if($res_type->fields['PK_STUDENT_ENROLLMENT'] == $WAIVER_FORM_PK_ENROLLMENT_STATUS) echo "selected"; ?> <? if($res_type->fields['IS_ACTIVE_ENROLLMENT'] == 1) echo "class='option_red'";  ?> ><?=$res_type->fields['BEGIN_DATE_1'].' - '.$res_type->fields['CODE'].' - '.$res_type->fields['STUDENT_STATUS'].' - '.$res_type->fields['CAMPUS_CODE']?></option>
							<?	$res_type->MoveNext();
							} /* Ticket # 1694 */ ?>
						</select>
						<span class="bar"></span> 
						<label for="PK_ENROLLMENT_STATUS"><?=ENROLLMENT?></label>
						<input type="hidden" class="form-control" id="WAIVER_FORM_PK_STUDENT_WAIVER_<?=$placement_waiver_count_ps?>" name="WAIVER_FORM_PK_STUDENT_WAIVER[<?=$placement_waiver_count_ps?>]" value="<?=$PK_STUDENT_WAIVER?>">
						<? if($PK_STUDENT_WAIVER == '') { ?>
						<input type="hidden" class="form-control" id="WAIVER_FORM_PK_WAIVER_TYPE_<?=$placement_waiver_count_ps?>" name="WAIVER_FORM_PK_WAIVER_TYPE[<?=$placement_waiver_count_ps?>]" value="1" >
						<? } ?>
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="col-12">
					<div class="form-group m-b-20">
						<input type="text" class="form-control" id="WAIVER_FORM_POST_SEC_INSTITUTION_<?=$placement_waiver_count_ps?>" name="WAIVER_FORM_POST_SEC_INSTITUTION[<?=$placement_waiver_count_ps?>]" value="<?=$WAIVER_FORM_POST_SEC_INSTITUTION?>" >
						<span class="bar"></span>
						<label for="POST_SEC_INSTITUTION"><?=POST_SEC_INSTITUTION?></label>
					</div>
				</div>
			</div>
			
			<div class="row" >
				<div class="col-12">
					<div class="form-group m-b-20">
						<input type="text" class="form-control" id="WAIVER_FORM_WAIVER_ADDRESS_<?=$placement_waiver_count_ps?>" name="WAIVER_FORM_WAIVER_ADDRESS[<?=$placement_waiver_count_ps?>]" value="<?=$WAIVER_FORM_WAIVER_ADDRESS?>" >
						<span class="bar"></span>
						<label for="WAIVER_ADDRESS"><?=ADDRESS?></label>
					</div>
				</div>
			</div>
			
			<div class="row" >			
				<div class="col-12">
					<div class="form-group m-b-20">
						<input type="text" class="form-control phone-inputmask" id="WAIVER_FORM_WAIVER_PHONE_<?=$placement_waiver_count_ps?>" name="WAIVER_FORM_WAIVER_PHONE[<?=$placement_waiver_count_ps?>]" value="<?=$WAIVER_FORM_WAIVER_PHONE?>" >
						<span class="bar"></span>
						<label for="WAIVER_PHONE"><?=PHONE?></label>
					</div>
				</div>
			</div>
		</div>
		<div class="col-12 col-md-6">
			<div class="row">
				<div class="col-12">
					<div class="form-group m-b-30">
						<select id="WAIVER_FORM_PK_PLACEMENT_STATUS_<?=$placement_waiver_count_ps?>" name="WAIVER_FORM_PK_PLACEMENT_STATUS[<?=$placement_waiver_count_ps?>]" class="form-control">
							<option value=""></option>
							<? /* Ticket # 1694 */
							$res_type = $db->Execute("select PK_PLACEMENT_STATUS, CONCAT(PLACEMENT_STATUS, ' - ', PLACEMENT_STUDENT_STATUS_CATEGORY) as  PLACEMENT_STATUS, M_PLACEMENT_STATUS.ACTIVE from M_PLACEMENT_STATUS LEFT JOIN M_PLACEMENT_STUDENT_STATUS_CATEGORY ON M_PLACEMENT_STUDENT_STATUS_CATEGORY.PK_PLACEMENT_STUDENT_STATUS_CATEGORY = M_PLACEMENT_STATUS.PK_PLACEMENT_STUDENT_STATUS_CATEGORY WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' order by M_PLACEMENT_STATUS.ACTIVE DESC, PLACEMENT_STATUS ASC");
							while (!$res_type->EOF) { 
								$option_label = $res_type->fields['PLACEMENT_STATUS'];
								if($res_type->fields['ACTIVE'] == 0)
									$option_label .= " (Inactive)"; ?>
								<option value="<?=$res_type->fields['PK_PLACEMENT_STATUS']?>" <? if($WAIVER_FORM_PK_PLACEMENT_STATUS == $res_type->fields['PK_PLACEMENT_STATUS']) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label?></option>
							<?	$res_type->MoveNext();
							} /* Ticket # 1694 */ ?>
						</select>
						<span class="bar"></span> 
						<label for="PK_PLACEMENT_STATUS"><?=PLACEMENT_WAIVER_STATUS?></label><!-- Ticket # 1714 -->
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="col-12">
					<div class="form-group m-b-30">
						<input type="text" class="form-control" id="WAIVER_FORM_PROGRAM_MAJOR_<?=$placement_waiver_count_ps?>" name="WAIVER_FORM_PROGRAM_MAJOR[<?=$placement_waiver_count_ps?>]" value="<?=$WAIVER_FORM_PROGRAM_MAJOR?>" >
						<span class="bar"></span>
						<label for="PROGRAM_MAJOR"><?=PROGRAM_MAJOR?></label>
					</div>
				</div>
			</div>
				
			<div class="row" >
				<div class="col-4">
					<div class="form-group m-b-20">
						<input type="text" class="form-control" id="WAIVER_FORM_WAIVER_CITY_<?=$placement_waiver_count_ps?>" name="WAIVER_FORM_WAIVER_CITY[<?=$placement_waiver_count_ps?>]" value="<?=$WAIVER_FORM_WAIVER_CITY?>" >
						<span class="bar"></span>
						<label for="WAIVER_CITY"><?=CITY?></label>
					</div>
				</div>
				
				<div class="col-4">
					<div class="form-group m-b-20">
						<select id="WAIVER_FORM_WAIVER_PK_STATES_<?=$placement_waiver_count_ps?>" name="WAIVER_FORM_WAIVER_PK_STATES[<?=$placement_waiver_count_ps?>]" class="form-control">
							<option value=""></option>
							<? $res_type = $db->Execute("select PK_STATES, STATE_NAME from Z_STATES WHERE ACTIVE = '1' ORDER BY STATE_NAME ASC ");
							while (!$res_type->EOF) { ?>
								<option value="<?=$res_type->fields['PK_STATES'] ?>" <? if($WAIVER_PK_STATES == $res_type->fields['PK_STATES']) echo "selected"; ?> ><?=$res_type->fields['STATE_NAME']?></option>
							<?	$res_type->MoveNext();
							} ?>
						</select>
					
						<span class="bar"></span>
						<label for="WAIVER_PK_STATES"><?=STATE?></label>
					</div>
				</div>
				
				<div class="col-4">
					<div class="form-group m-b-20">
						<input type="text" class="form-control" id="WAIVER_FORM_WAIVER_ZIP_<?=$placement_waiver_count_ps?>" name="WAIVER_FORM_WAIVER_ZIP[<?=$placement_waiver_count_ps?>]" value="<?=$WAIVER_FORM_WAIVER_ZIP?>" >
						<span class="bar"></span>
						<label for="WAIVER_ZIP"><?=ZIP?></label>
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="col-12">
					<div class="form-group m-b-30" style="margin-top: 0.4rem;">
						<textarea class="form-control rich" id="WAIVER_FORM_WAIVER_NOTES_<?=$placement_waiver_count_ps?>" name="WAIVER_FORM_WAIVER_NOTES[<?=$placement_waiver_count_ps?>]" rows="3"><?=$WAIVER_FORM_WAIVER_NOTES?></textarea>
						<span class="bar"></span> 
						<label for="WAIVER_NOTES"><?=NOTES?></label>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>