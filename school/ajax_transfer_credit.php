<?php require_once('../global/config.php'); 
require_once("../language/common.php");
require_once("../language/transfer_credit.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' || $_SESSION['PK_ROLES'] != 2 ){ 
	header("location:../index");
	exit;
}

$count  		   = $_REQUEST['count'];
$PK_TRANSFER_CREDIT  = $_REQUEST['PK_TRANSFER_CREDIT'];  

if($PK_TRANSFER_CREDIT == ''){
	$SCHOOL					= '';
	$YEAR  					= '';
	$HOUR  					= '';
	$PK_STUDENT_ENROLLMENT  = '';
	$TERM   				= '';
	$PERP  					= '';
	$COURSE_CODE  			= '';
	$STATUS  				= '';
	$UNITS  				= '';
	$EQV_COURSE  			= '';
	$GRADE  				= '';
	$FA_UNITS  				= '';
	$COURSE_DESC  			= '';
	$NOTES  				= '';
} 
else{
	$result 	= $db->Execute("SELECT * FROM S_TRANSFER_CREDIT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_TRANSFER_CREDIT = '$PK_TRANSFER_CREDIT'");

	$SCHOOL					= $result->fields['SCHOOL'];
	$YEAR  					= $result->fields['YEAR'];	
	$HOUR  					= $result->fields['HOUR'];	
	$PK_STUDENT_ENROLLMENT  = $result->fields['PK_STUDENT_ENROLLMENT'];	
	$TERM   				= $result->fields['TERM'];	
	$PERP  					= $result->fields['PERP'];	
	$COURSE_CODE  			= $result->fields['COURSE_CODE'];	
	$STATUS  				= $result->fields['STATUS'];	
	$UNITS  				= $result->fields['UNITS'];	
	$EQV_COURSE  			= $result->fields['EQV_COURSE'];	
	$GRADE  				= $result->fields['GRADE'];	
	$FA_UNITS  				= $result->fields['FA_UNITS'];	
	$COURSE_DESC  			= $result->fields['COURSE_DESC'];	
	$NOTES  				= $result->fields['NOTES'];	
}
?>

<div class="p-20 pb-0 lender-form" id="table_<?=$count?>">
	
	<div class="row pt-3" style="border: 1px solid #fdfdfd;box-shadow: 1px 0px 20px rgba(0, 0, 0, 0.08);">
		<input type="hidden" name="PK_TRANSFER_CREDIT[]"  value="<?=$PK_TRANSFER_CREDIT?>" />
		<input type="hidden" name="COUNT[]"  value="<?=$count?>" />

		<div class="col-sm-12 pt-2"> 
			<div class="d-flex">
				<div class="col-4 col-sm-4 form-group">
					<input id="LENDER_<?=$count?>" name="SCHOOL[]" type="text" class="form-control required-entry" value="<?=$SCHOOL?>" />
					<span class="bar"></span>
					<label for="LENDER_<?=$count?>"><?=SCHOOL?></label>
				</div>
				
				<div class="col-4 col-sm-4 form-group">
					<input id="YEAR_<?=$count?>" name="YEAR[]" type="text" class="form-control required-entry" value="<?=$YEAR?>" />
					<span class="bar"></span>
					<label for="YEAR_<?=$count?>"><?=YEAR?></label>
				</div>
				
				<div class="col-4 col-sm-4 form-group">
					<input id="HOUR<?=$count?>" name="HOUR[]" type="text" class="form-control required-entry" value="<?=$HOUR?>" autofocus />
					<span class="bar"></span>
					<label for="HOUR<?=$count?>"><?=HOUR?></label>
				</div>
			</div>
		</div>
		
		<div class="col-sm-12 pt-2"> 
			<div class="d-flex">
				<div class="col-4 col-sm-4 form-group">
					<select id="PK_STUDENT_ENROLLMENT_<?=$count?>" name="PK_STUDENT_ENROLLMENT[]" value="<?=$PK_STUDENT_ENROLLMENT?>" class="form-control " >  
						<option selected></option>
					</select>
					<span class="bar"></span> 
					<label for="PK_STUDENT_ENROLLMENT_<?=$count?>"><?=ENROLLMENT?></label>
				</div>
				
				<div class="col-4 col-sm-4 form-group">
					<input id="TERM_<?=$count?>" name="TERM[]" type="text" class="form-control required-entry" value="<?=$TERM?>" />
					<span class="bar"></span>
					<label for="TERM_<?=$count?>"><?=TERM?></label>
				</div>
				
				<div class="col-4 col-sm-4 form-group">
					<input id="PERP_<?=$count?>" name="PERP[]" type="text" class="form-control required-entry" value="<?=$PERP?>" autofocus />
					<span class="bar"></span>
					<label for="PERP<?=$count?>"><?=PERP?></label>
				</div>
			</div>
		</div>
		
		<div class="col-sm-12 pt-2"> 
			<div class="d-flex">
				<div class="col-4 col-sm-4 form-group">
					<input id="COURSE_CODE_<?=$count?>" name="COURSE_CODE[]" type="text" class="form-control required-entry" value="<?=$COURSE_CODE?>" />
					<span class="bar"></span>
					<label for="COURSE_CODE_<?=$count?>"><?=COURSE_CODE?></label>
				</div>
				
				<div class="col-4 col-sm-4 form-group">
					<input id="STATUS_<?=$count?>" name="STATUS[]" type="text" class="form-control required-entry" value="<?=$STATUS?>" />
					<span class="bar"></span>
					<label for="STATUS_<?=$count?>"><?=STATUS?></label>
				</div>
				
				<div class="col-4 col-sm-4 form-group">
					<input id="UNITS_<?=$count?>" name="UNITS[]" type="text" class="form-control required-entry" value="<?=$UNITS?>" />
					<span class="bar"></span>
					<label for="UNITS_<?=$count?>"><?=UNITS?></label>
				</div>
			</div>
		</div>
		
		<div class="col-sm-12 pt-2"> 
			<div class="d-flex">
				<div class="col-4 col-sm-4 form-group">
					<input id="EQV_COURSE_<?=$count?>" name="EQV_COURSE[]" type="text" class="form-control required-entry" value="<?=$EQV_COURSE?>" />
					<span class="bar"></span>
					<label for="EQV_COURSE_<?=$count?>"><?=EQV_COURSE?></label>
				</div>
				
				<div class="col-4 col-sm-4 form-group">
					<input id="GRADE_<?=$count?>" name="GRADE[]" type="text" class="form-control required-entry" value="<?=$GRADE?>" />
					<span class="bar"></span>
					<label for="GRADE_<?=$count?>"><?=GRADE?></label>
				</div>
				
				<div class="col-4 col-sm-4 form-group">
					<input id="FA_UNITS_<?=$count?>" name="FA_UNITS[]" type="text" class="form-control required-entry" value="<?=$FA_UNITS?>" />
					<span class="bar"></span>
					<label for="FA_UNITS_<?=$count?>"><?=FA_UNITS?></label>
				</div>
			</div>
		</div>
		
		<div class="col-sm-12 pt-2"> 
			<div class="d-flex">
				<div class="col-12 col-sm-12 form-group">
					<input id="COURSE_DESC_<?=$count?>" name="COURSE_DESC[]" type="text" class="form-control required-entry" value="<?=$COURSE_DESC?>" />
					<span class="bar"></span>
					<label for="COURSE_DESC_<?=$count?>"><?=COURSE_DESC?></label>
				</div>
			</div>
		</div>
		
		<div class="col-sm-12 pt-2"> 
			<div class="d-flex">
				<div class="col-10 col-sm-10 form-group">
					<input id="NOTES_<?=$count?>" name="NOTES[]" type="text" class="form-control required-entry" value="<?=$NOTES?>" />
					<span class="bar"></span>
					<label for="NOTES_<?=$count?>"><?=NOTES?></label>
				</div>
				
				<div class="col-2 col-sm-2 form-group">
					<a href="javascript:void(0)" onclick="delete_row('<?=$count?>','detail')" class="btn delete-color btn-circle" ><i class="far fa-trash-alt"></i></a>
				</div>
			</div>
		</div>
		
	</div>
</div>