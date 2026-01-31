<?php require_once('../global/config.php'); 
if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' ){ 
	header("location:../index");
	exit;
}

$cunt_grade_book  		= $_REQUEST['cunt_grade_book'];
$PK_COURSE_GRADE_BOOK  	= $_REQUEST['PK_COURSE_GRADE_BOOK'];
if($PK_COURSE_GRADE_BOOK == '') {
	$COLUMN_NO  		= '';
	$CODE  				= '';
	$DESCRIPTION    	= '';
	$PK_GRADE_BOOK_TYPE	= '';
	$PERIOD   	  		= '';
	$POINTS   	  		= '';
	$WEIGHT   	  		= '';
	$WEIGHTED_POINTS   	= '';
} else {
	$result = $db->Execute("SELECT * FROM S_COURSE_GRADE_BOOK WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_GRADE_BOOK = '$PK_COURSE_GRADE_BOOK' ");
	$COLUMN_NO  		= $result->fields['COLUMN_NO'];
	$CODE  				= $result->fields['CODE'];
	$DESCRIPTION    	= $result->fields['DESCRIPTION'];
	$PK_GRADE_BOOK_TYPE = $result->fields['PK_GRADE_BOOK_TYPE'];
	$PERIOD				= $result->fields['PERIOD'];
	$POINTS				= $result->fields['POINTS'];
	$WEIGHT				= $result->fields['WEIGHT'];
	$WEIGHTED_POINTS	= $result->fields['WEIGHTED_POINTS'];
	
	/* Ticket #1161 */
	if($_REQUEST['copy'] == 1)
		$PK_COURSE_GRADE_BOOK = '';
	/* Ticket #1161 */
}
?>
<div class="row" id="GRADE_BOOK_<?=$cunt_grade_book?>" style="margin-bottom:5px" >
	<input type="hidden" name="PK_COURSE_GRADE_BOOK[]"  value="<?=$PK_COURSE_GRADE_BOOK?>" />
	<input type="hidden" name="cunt_grade_book[]"  value="<?=$cunt_grade_book?>" />
	
	<div class="col-sm-1" >
		<input type="text" class="form-control" placeholder="" name="COLUMN_NO[]" id="COLUMN_NO_<?=$cunt_grade_book?>" value="<?=$COLUMN_NO?>" />
	</div>
	
	<div class="col-sm-1" >
		<input type="text" class="form-control" placeholder="" name="CODE[]" id="CODE_<?=$cunt_grade_book?>" value="<?=$CODE?>" />
	</div>
	
	<div class="col-md-2">
		<input type="text" name="GRADE_BOOK_DESCRIPTION[]" placeholder="" id="GRADE_BOOK_DESCRIPTION_<?=$cunt_grade_book?>"  class="form-control" value="<?=$DESCRIPTION?>" />
	</div>
	
	<div class="col-md-2">
		<select id="PK_GRADE_BOOK_TYPE_<?=$cunt_grade_book?>" name="PK_GRADE_BOOK_TYPE[]" class="form-control">
			<option selected></option>
			<? /* Ticket #1696  */
			$res_type = $db->Execute("SELECT PK_GRADE_BOOK_TYPE, CONCAT(GRADE_BOOK_TYPE, ' - ', DESCRIPTION) as GRADE_BOOK_TYPE, ACTIVE FROM M_GRADE_BOOK_TYPE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY ACTIVE DESC, GRADE_BOOK_TYPE ASC"); 
			while (!$res_type->EOF) { 
				$option_label = $res_type->fields['GRADE_BOOK_TYPE'];
				if($res_type->fields['ACTIVE'] == 0)
					$option_label .= " (Inactive)"; ?>
				<option value="<?=$res_type->fields['PK_GRADE_BOOK_TYPE']?>"  <? if($res_type->fields['PK_GRADE_BOOK_TYPE'] == $PK_GRADE_BOOK_TYPE) echo "selected"; ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
			<?	$res_type->MoveNext();
			} /* Ticket #1696  */ ?>
		</select>
	</div>
	
	<div class="col-md-1">
		<input type="number" name="PERIOD[]" placeholder="" id="PERIOD_<?=$cunt_grade_book?>"  class="form-control" value="<?=$PERIOD?>" min="0" />
	</div>
	
	<div class="col-md-1">
		<input type="number" name="POINTS[]" placeholder="" id="POINTS_<?=$cunt_grade_book?>"  class="form-control" value="<?=$POINTS?>" onchange="calc_weighted_points(<?=$cunt_grade_book?>)" min="0" />
	</div>
	
	<div class="col-md-1">
		<input type="number" name="WEIGHT[]" placeholder="" id="WEIGHT_<?=$cunt_grade_book?>"  class="form-control" value="<?=$WEIGHT?>" onchange="calc_weighted_points(<?=$cunt_grade_book?>)" min="0" />
	</div>
	
	<div class="col-md-2">
		<input type="number" name="WEIGHTED_POINTS[]" placeholder="" id="WEIGHTED_POINTS_<?=$cunt_grade_book?>"  class="form-control" value="<?=$WEIGHTED_POINTS?>" min="0" readonly />
	</div>
	
	<div class="col-md-1">
		<a href="javascript:void(0)" onclick="delete_row('<?=$cunt_grade_book?>','GRADE_BOOK')" class="btn delete-color btn-circle" ><i class="far fa-trash-alt"></i></a>
	</div>
</div>
