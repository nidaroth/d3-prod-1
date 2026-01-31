<?php require_once('../global/config.php'); 
if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' ){ 
	header("location:../index");
	exit;
}

////////////////////////////
////////////////////////////
////////////////////////////if grade book is edited check on instructor panel > GRADE BOOK SETUP
////////////////////////////
////////////////////////////

$grade_cunt  		 		= $_REQUEST['grade_cunt'];
$PK_COURSE_OFFERING_GRADE  	= $_REQUEST['PK_COURSE_OFFERING_GRADE'];
$PK_COURSE_GRADE_BOOK1  	= $_REQUEST['PK_COURSE_GRADE_BOOK'];

if($PK_COURSE_GRADE_BOOK1 > 0) {
	$result = $db->Execute("SELECT * FROM S_COURSE_GRADE_BOOK WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_GRADE_BOOK = '$PK_COURSE_GRADE_BOOK1' ");
	$GRADE_ORDER		= $result->fields['COLUMN_NO'];
	$CODE  				= $result->fields['CODE'];
	$DESCRIPTION    	= $result->fields['DESCRIPTION'];
	$PK_GRADE_BOOK_TYPE = $result->fields['PK_GRADE_BOOK_TYPE'];
	$PERIOD				= $result->fields['PERIOD'];
	$POINTS				= $result->fields['POINTS'];
	$WEIGHT				= $result->fields['WEIGHT'];
	$WEIGHTED_POINTS	= $result->fields['WEIGHTED_POINTS'];
} else if($PK_COURSE_OFFERING_GRADE == '') {
	$GRADE_ORDER		= ''; //Ticket #1290
	$CODE  				= '';
	$DESCRIPTION   		= '';
	$DATE   			= '';
	$PERIOD    			= '';
	$POINTS    			= '';
	$WEIGHT				= '';
	$WEIGHTED_POINTS    = '';
	$ACTIVE   	  		= 1;
	$PK_GRADE_BOOK_TYPE = '';
} else if($PK_COURSE_OFFERING_GRADE > 0) {
	$result = $db->Execute("SELECT * FROM S_COURSE_OFFERING_GRADE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_COURSE_OFFERING_GRADE = '$PK_COURSE_OFFERING_GRADE' ");
	$GRADE_ORDER		= $result->fields['GRADE_ORDER']; //Ticket #1290
	$CODE  				= $result->fields['CODE'];
	$DESCRIPTION    	= $result->fields['DESCRIPTION'];
	$DATE    			= $result->fields['DATE'];
	$PERIOD    			= $result->fields['PERIOD'];
	$POINTS    			= $result->fields['POINTS'];
	$WEIGHT    			= $result->fields['WEIGHT'];
	$WEIGHTED_POINTS    = $result->fields['WEIGHTED_POINTS'];
	$ACTIVE   	 		= $result->fields['ACTIVE'];
	$PK_GRADE_BOOK_TYPE = $result->fields['PK_GRADE_BOOK_TYPE'];
	if($DATE != '0000-00-00')
		$DATE = date("m/d/Y",strtotime($DATE));
	else
		$DATE = '';
} 
?>
<tr id="grade_table_<?=$grade_cunt?>" >
	<!-- Ticket #1290 -->
	<td>
		<input type="text" class="form-control" placeholder="" name="GRADE_ORDER[]" id="GRADE_ORDER_<?=$grade_cunt?>" value="<?=$GRADE_ORDER?>" />
	</td>
	<!-- Ticket #1290 -->
	<td >
		<input type="hidden" name="PK_COURSE_OFFERING_GRADE[]"  value="<?=$PK_COURSE_OFFERING_GRADE?>" />
		<input type="hidden" name="GRADE_CUNT[]"  value="<?=$grade_cunt?>" />
		<input type="text" class="form-control" placeholder="" name="CODE[]" id="CODE_<?=$grade_cunt?>" value="<?=$CODE?>" />
	</td>
	<td>
		<input type="text" class="form-control" placeholder="" name="DESCRIPTION[]" id="DESCRIPTION_<?=$grade_cunt?>" value="<?=$DESCRIPTION?>" />
	</td>
	<td>
		<select id="PK_GRADE_BOOK_TYPE_<?=$grade_cunt?>" name="PK_GRADE_BOOK_TYPE[]" class="form-control" style="width:150px" >
			<option ></option>
			<? /* Ticket #1695  */
			$res_cs = $db->Execute("SELECT PK_GRADE_BOOK_TYPE, CONCAT(GRADE_BOOK_TYPE, ' - ', DESCRIPTION) as GRADE_BOOK_TYPE, ACTIVE FROM M_GRADE_BOOK_TYPE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY ACTIVE DESC, GRADE_BOOK_TYPE ASC");
			while (!$res_cs->EOF) { 
				$option_label = $res_cs->fields['GRADE_BOOK_TYPE'];
				if($res_cs->fields['ACTIVE'] == 0)
					$option_label .= " (Inactive)"; ?>
				<option value="<?=$res_cs->fields['PK_GRADE_BOOK_TYPE']?>" <? if($PK_GRADE_BOOK_TYPE == $res_cs->fields['PK_GRADE_BOOK_TYPE'] ) echo "selected";?> <? if($res_cs->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
			<?	$res_cs->MoveNext();
			} /* Ticket #1695  */ ?>
		</select>
	</td>
	<td>
		<input type="text" class="form-control date" placeholder="" name="DATE[]" id="DATE_<?=$grade_cunt?>" value="<?=$DATE?>" />
	</td>
	<!--<td>
		<input type="text" class="form-control" placeholder="" name="PERIOD[]" id="PERIOD_<?=$grade_cunt?>" value="<?=$PERIOD?>" />
	</td>-->
	<td>
		<input type="text" class="form-control" placeholder="" name="POINTS[]" id="POINTS_<?=$grade_cunt?>" value="<?=$POINTS?>" onchange="calc_wp()" />
	</td>
	<td>
		<input type="text" class="form-control" placeholder="" name="WEIGHT[]" id="WEIGHT_<?=$grade_cunt?>" value="<?=$WEIGHT?>" onchange="calc_wp()" />
	</td>
	<td>
		<input type="text" class="form-control" placeholder="" name="WEIGHTED_POINTS[]" id="WEIGHTED_POINTS_<?=$grade_cunt?>" value="<?=$WEIGHTED_POINTS?>" readonly />
	</td>
	<td>
		<a href="javascript:void(0);" onclick="ajax_del_grade_modal('<?=$PK_COURSE_OFFERING_GRADE?>')" title="<?=DELETE?>" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i> </a>
	</td>
</tr>
