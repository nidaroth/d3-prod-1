<? require_once("../global/config.php"); 
require_once("../language/common.php");
if($_SESSION['ADMIN_PK_USER'] == 0 || $_SESSION['ADMIN_PK_USER'] == '' || $_SESSION['ADMIN_PK_ROLES'] != 1 ){ 
	header("location:../index");
	exit;
}

$ledgend_count  		= $_REQUEST['ledgend_count'];
$det_count  			= $_REQUEST['det_count'];
$PK_ISIR_SETUP_LEGEND   = $_REQUEST['PK_ISIR_SETUP_LEGEND'];

$result = $db->Execute("select * from Z_ISIR_SETUP_LEGEND WHERE PK_ISIR_SETUP_LEGEND = '$PK_ISIR_SETUP_LEGEND' ");
$LEGEND  = $result->fields['LEGEND'];
$TEXT    = $result->fields['TEXT'];

?>
<div id="ledgend_table_<?=$ledgend_count?>" >
	<div class="row" >
		<div class="col-md-1">
			<input type="hidden" name="PK_ISIR_SETUP_LEGEND_<?=$ledgend_count?>"  value="<?=$PK_ISIR_SETUP_LEGEND?>" />
			<input type="hidden" name="ledgend_count_<?=$det_count?>[]"  value="<?=$ledgend_count?>" />
			<input type="text" name="LEGEND_<?=$ledgend_count?>" placeholder="" id="LEGEND_<?=$ledgend_count?>"  class="required-entry form-control" value="<?=$LEGEND ?>" />
		</div>
		<div class="col-md-3">
			<input type="text" name="TEXT_<?=$ledgend_count?>" placeholder="" id="TEXT_<?=$ledgend_count?>"  class="required-entry form-control" value="<?=$TEXT?>" />
		</div>
		<div class="col-md-5">
			<a href="javascript:void(0)" onclick="delete_row('<?=$ledgend_count?>','ledgend')" class="btn delete-color btn-circle" ><i class="far fa-trash-alt"></i></a>
		</div> 
	</div>	
</div>