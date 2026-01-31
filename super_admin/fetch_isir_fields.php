<? require_once("../global/config.php"); 
require_once("../language/common.php");
if($_SESSION['ADMIN_PK_USER'] == 0 || $_SESSION['ADMIN_PK_USER'] == '' || $_SESSION['ADMIN_PK_ROLES'] != 1 ){ 
	header("location:../index");
	exit;
}

$count  		     	= $_REQUEST['count'];
$PK_ISIR_SETUP_DETAIL   = $_REQUEST['PK_ISIR_SETUP_DETAIL'];

$result = $db->Execute("select * from Z_ISIR_SETUP_DETAIL WHERE PK_ISIR_SETUP_DETAIL = '$PK_ISIR_SETUP_DETAIL' ");
$START  		 = $result->fields['START'];
$END      		 = $result->fields['END'];
$HEADING 		 = $result->fields['HEADING'];
$DSIS_FIELD_NAME = $result->fields['DSIS_FIELD_NAME'];
$HAS_LEDGEND 	 = $result->fields['HAS_LEDGEND'];
$FIELD_NO		 = $result->fields['FIELD_NO'];
$ACTIVE   		 = $result->fields['ACTIVE'];

?>
<div id="table_<?=$count?>" >
	<div class="row" >
		<input type="hidden" name="PK_ISIR_SETUP_DETAIL_<?=$count?>"  value="<?=$PK_ISIR_SETUP_DETAIL?>" />
		<input type="hidden" name="COUNT[]"  value="<?=$count?>" />
		
		<div class="col-md-1">
			<input type="text" name="FIELD_NO_<?=$count?>" placeholder="" id="FIELD_NO_<?=$count?>"  class="form-control" value="<?=$FIELD_NO?>" />
		</div>
		
		
		<div class="col-md-4">
			<input type="text" name="HEADING_<?=$count?>" placeholder="" id="HEADING_<?=$count?>"  class="required-entry form-control" value="<?=$HEADING?>" />
		</div>
		
		<div class="col-md-1">
			<input type="text" name="START_<?=$count?>" placeholder="" id="START_<?=$count?>"  class="required-entry form-control" value="<?=$START?>" />
		</div>
		
		<div class="col-md-1">
			<input type="text" name="END_<?=$count?>" placeholder="" id="END_<?=$count?>"  class="required-entry form-control" value="<?=$END?>" />
		</div>
		
		<div class="col-md-3">
			<select name="DSIS_FIELD_NAME_<?=$count?>" id="DSIS_FIELD_NAME_<?=$count?>" class="form-control" <? if($PK_ISIR_SETUP_DETAIL == '' || $PK_ISIR_SETUP_DETAIL == 0) { ?> onclick="import_ledgend(<?=$count?>)" <? } ?> >
				<option value=""></option>
				<? $res_dsis_field = $db->Execute("select DSIS_FIELD, FIELD_NAME from Z_ISIR_DSIS_FIELDS WHERE ACTIVE = 1 ORDER BY FIELD_NAME ASC ");
				while (!$res_dsis_field->EOF) { ?>
					<option value="<?=trim($res_dsis_field->fields['DSIS_FIELD'])?>" <? if(trim($res_dsis_field->fields['DSIS_FIELD']) == trim($DSIS_FIELD_NAME)) echo "selected"; ?> ><?=$res_dsis_field->fields['FIELD_NAME'] ?></option>
				<?	$res_dsis_field->MoveNext();
				} ?>
			</select>
		</div> 
		<div class="col-md-1">
			<center><input type="checkbox" id="HAS_LEDGEND_<?=$count?>" name="HAS_LEDGEND_<?=$count?>" value="1" <? if($HAS_LEDGEND == 1) echo "checked"; ?> onclick="show_ledgend(<?=$count?>)" ></center>
		</div> 
		<div class="col-md-1">
			<input type="checkbox" id="ACTIVE_<?=$count?>" name="ACTIVE_<?=$count?>" value="1" <? if($ACTIVE == 1) echo "checked"; ?> >
			<a href="javascript:void(0)" onclick="delete_row('<?=$count?>','detail')" class="btn delete-color btn-circle" ><i class="far fa-trash-alt"></i></a>
		</div> 
	</div>
	<? if($HAS_LEDGEND == 1) $style = "display:block"; else $style = "display:none"; ?> 
	<div id="LEDGEND_<?=$count?>" style="<?=$style?>" >
		<br />
		<div class="row">
			<div class="col-md-1">
				<b style="font-weight: bold;" >Value</b>
			</div> 
			<div class="col-md-3">
				<b style="font-weight: bold;" >Legend</b>
			</div>
			<div class="col-md-5">
				<a href="javascript:void(0)" onclick="add_ledgend(<?=$count?>)" ><i style="font-size:25px" class="fa fa-plus-circle"></i></a>
				
				<a href="javascript:void(0)" onclick="import_ledgend(<?=$count?>)" ><i style="font-size:20px" class="fas fa-download"></i></a>
			</div>
		</div>
		
		<div id="LEDGEND_1_<?=$count?>" >
			<? $res_led = $db->Execute("select PK_ISIR_SETUP_LEGEND from Z_ISIR_SETUP_LEGEND WHERE PK_ISIR_SETUP_DETAIL = '$PK_ISIR_SETUP_DETAIL' AND PK_ISIR_SETUP_DETAIL > 0");
			while (!$res_led->EOF) {
				$_REQUEST['PK_ISIR_SETUP_LEGEND'] 	= $res_led->fields['PK_ISIR_SETUP_LEGEND'];
				$_REQUEST['det_count']  			= $count;
				$_REQUEST['ledgend_count']  		= $ledgend_count;
				
				include('fetch_isir_ledgend.php');
				
				$ledgend_count++;	
				$res_led->MoveNext();
			} ?>
		</div>
	</div>
	<br />
</div>