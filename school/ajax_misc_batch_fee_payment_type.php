<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("check_access.php");

if(check_access('MANAGEMENT_ACCOUNTING') == 0 ){
	header("location:../index");
	exit;
}

$res_type = $db->Execute("select TYPE from M_AR_LEDGER_CODE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_AR_LEDGER_CODE = '$_REQUEST[ledger_id]' AND ACTIVE = 1 ");
if($res_type->fields['TYPE'] == 1){ ?>
	<select id="PK_AR_PAYMENT_TYPE_<?=$_REQUEST['count_1']?>" name="PK_AR_PAYMENT_TYPE_<?=$_REQUEST['count_1']?>" class="form-control" style="width:100px;" >
		<option value="" ></option>
		<? $res_type = $db->Execute("select PK_AR_PAYMENT_TYPE, AR_PAYMENT_TYPE from M_AR_PAYMENT_TYPE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by AR_PAYMENT_TYPE ASC");
		while (!$res_type->EOF) { ?>
			<option value="<?=$res_type->fields['PK_AR_PAYMENT_TYPE'] ?>" <? if($res_type->fields['PK_AR_PAYMENT_TYPE'] == $_REQUEST['DEF_pk_ar_payment_type']) echo "selected"; ?> ><?=$res_type->fields['AR_PAYMENT_TYPE'] ?></option>
		<?	$res_type->MoveNext();
		} ?>
	</select>
<? } else { ?>
	<select id="PK_AR_FEE_TYPE_<?=$_REQUEST['count_1']?>" name="PK_AR_FEE_TYPE_<?=$_REQUEST['count_1']?>" class="form-control" style="width:100px;" >
		<option value="" ></option>
		<? $res_type = $db->Execute("select PK_AR_FEE_TYPE, AR_FEE_TYPE from M_AR_FEE_TYPE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by AR_FEE_TYPE ASC");
		while (!$res_type->EOF) { ?>
			<option value="<?=$res_type->fields['PK_AR_FEE_TYPE'] ?>" <? if($res_type->fields['PK_AR_FEE_TYPE'] == $_REQUEST['DEF_pk_ar_fee_type']) echo "selected"; ?> ><?=$res_type->fields['AR_FEE_TYPE'] ?></option>
		<?	$res_type->MoveNext();
		} ?>
	</select>
<? } 