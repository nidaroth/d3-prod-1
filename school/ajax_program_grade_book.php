<?php require_once('../global/config.php'); 
if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' ){ 
	header("location:../index");
	exit;
}

$grade_cunt  		 		= $_REQUEST['grade_cunt'];
$PK_PROGRAM_GRADE_BOOK  	= $_REQUEST['PK_PROGRAM_GRADE_BOOK'];

if($PK_PROGRAM_GRADE_BOOK == '') {
	$GRADE_PK_GRADE_BOOK_CODE  	= '';
	$GRADE_DESCRIPTION   		= '';
	$PK_GRADE_BOOK_TYPE			= '';
	$GRADE_DATE   				= '';
	$GRADE_SESSION    			= '';
	$GRADE_HOUR    				= '';
	$GRADE_POINTS				= '';

} else {
	$result = $db->Execute("SELECT * FROM S_PROGRAM_GRADE_BOOK WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_PROGRAM_GRADE_BOOK = '$PK_PROGRAM_GRADE_BOOK' ");
	$GRADE_PK_GRADE_BOOK_CODE  	= $result->fields['PK_GRADE_BOOK_CODE'];
	$GRADE_DESCRIPTION    		= $result->fields['DESCRIPTION'];
	$PK_GRADE_BOOK_TYPE    		= $result->fields['PK_GRADE_BOOK_TYPE'];
	$GRADE_DATE    				= $result->fields['DATE'];
	$GRADE_SESSION    			= $result->fields['SESSION'];
	$GRADE_HOUR    				= $result->fields['HOUR'];
	$GRADE_POINTS    			= $result->fields['POINTS'];
	
	if($DATE != '0000-00-00')
		$DATE = date("m/d/Y",strtotime($DATE));
	else
		$DATE = '';
} 
?>
<tr id="grade_table_<?=$grade_cunt?>" >
	<td >
		<input type="hidden" name="PK_PROGRAM_GRADE_BOOK[]"  value="<?=$PK_PROGRAM_GRADE_BOOK?>" />
		<input type="hidden" name="GRADE_CUNT[]"  value="<?=$grade_cunt?>" />
		
		<select id="GRADE_PK_GRADE_BOOK_CODE_<?=$grade_cunt?>" name="GRADE_PK_GRADE_BOOK_CODE[]" class="form-control" style="width:150px" onchange="get_grade_book_code_value(this.value,'<?=$grade_cunt?>')" >
			<option ></option>
			<? /* Ticket #1697  */
			$res_cs = $db->Execute("SELECT PK_GRADE_BOOK_CODE, CONCAT(CODE, ' - ',DESCRIPTION) as CODE, ACTIVE FROM M_GRADE_BOOK_CODE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY ACTIVE DESC, CODE ASC"); 
			while (!$res_cs->EOF) { 
				$option_label = $res_cs->fields['CODE'];
				if($res_cs->fields['ACTIVE'] == 0)
					$option_label .= " (Inactive)"; ?>
				<option value="<?=$res_cs->fields['PK_GRADE_BOOK_CODE']?>" <? if($GRADE_PK_GRADE_BOOK_CODE == $res_cs->fields['PK_GRADE_BOOK_CODE'] ) echo "selected";?> <? if($res_cs->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
			<?	$res_cs->MoveNext();
			} /* Ticket #1697  */ ?>
		</select>
		
	</td>
	<td>
		<input type="text" class="form-control" placeholder="" name="GRADE_DESCRIPTION[]" id="GRADE_DESCRIPTION_<?=$grade_cunt?>" value="<?=$GRADE_DESCRIPTION?>" />
	</td>

	<td>
		<select id="PK_GRADE_BOOK_TYPE_<?=$grade_cunt?>" name="PK_GRADE_BOOK_TYPE[]" class="form-control" style="width:150px" >
			<option ></option>
			<? /* Ticket #1697  */
			$res_cs = $db->Execute("SELECT PK_GRADE_BOOK_TYPE, CONCAT(GRADE_BOOK_TYPE, ' - ',DESCRIPTION) as GRADE_BOOK_TYPE , ACTIVE FROM M_GRADE_BOOK_TYPE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY ACTIVE DESC, GRADE_BOOK_TYPE ASC");
			while (!$res_cs->EOF) { 
				$option_label = $res_cs->fields['GRADE_BOOK_TYPE'];
				if($res_cs->fields['ACTIVE'] == 0)
					$option_label .= " (Inactive)"; ?>
				<option value="<?=$res_cs->fields['PK_GRADE_BOOK_TYPE']?>" <? if($PK_GRADE_BOOK_TYPE == $res_cs->fields['PK_GRADE_BOOK_TYPE'] ) echo "selected";?> <? if($res_cs->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
			<?	$res_cs->MoveNext();
			} /* Ticket #1697  */ ?>
		</select>
	</td>
	<td>
		<input type="text" class="form-control" placeholder="" name="GRADE_SESSION[]" id="GRADE_SESSION_<?=$grade_cunt?>" value="<?=$GRADE_SESSION?>" onchange="calc_tot_prog_grade()" /> <!-- Ticket # 1525 -->
	</td>
	<td>
		<input type="text" class="form-control" placeholder="" name="GRADE_HOUR[]" id="GRADE_HOUR_<?=$grade_cunt?>" value="<?=$GRADE_HOUR?>" onchange="calc_tot_prog_grade()" /> <!-- Ticket # 1525 -->
	</td>
	<td>
		<input type="text" class="form-control" placeholder="" name="GRADE_POINTS[]" id="GRADE_POINTS_<?=$grade_cunt?>" value="<?=$GRADE_POINTS?>" onchange="calc_tot_prog_grade()" /> <!-- Ticket # 1525 -->
	</td>
	<td>
		<a href="javascript:void(0);" onclick="delete_row('<?=$grade_cunt?>','grade')" title="<?=DELETE?>" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i> </a>
	</td>
</tr>
