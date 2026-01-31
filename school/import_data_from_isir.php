<? /*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/

require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/student.php");
require_once("../language/isir.php");
require_once("get_department_from_t.php");

require_once("check_access.php");
$FINANCE_ACCESS = check_access('FINANCE_ACCESS');

$timezone = $_SESSION['PK_TIMEZONE'];
if($timezone == '' || $timezone == 0) {
	$res = $db->Execute("SELECT PK_TIMEZONE FROM Z_ACCOUNT WHERE  PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	$timezone = $res->fields['PK_TIMEZONE'];
	if($timezone == '' || $timezone == 0)
		$timezone = 4;
}
$res_tz = $db->Execute("SELECT TIMEZONE FROM Z_TIMEZONE WHERE  PK_TIMEZONE = '$timezone' ");

if($FINANCE_ACCESS == 0) {
	header("location:../index");
	exit;
} 

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	/*Ticket # 1033*/
	require_once("function_get_details_for_fa_from_program.php");
	$data123 = get_details_for_fa_from_program($_GET['eid']);
	$STUDENT_FINANCIAL['PROGRAM_LENGTH'] = $data123['PROGRAM_LENGTH'];
	$STUDENT_FINANCIAL['PROGRAM_COST'] 	 = $data123['PROGRAM_COST'];
	/*Ticket # 1033*/
	
	//if($_GET['fid'] == '') {
		$STUDENT_FINANCIAL['PK_STUDENT_MASTER'] 	 = $_GET['sid'];
		$STUDENT_FINANCIAL['PK_STUDENT_ENROLLMENT']  = $_GET['eid'];
		$STUDENT_FINANCIAL['PK_ACCOUNT'] 			 = $_SESSION['PK_ACCOUNT'];
		$STUDENT_FINANCIAL['CREATED_BY']  			 = $_SESSION['PK_USER'];
		$STUDENT_FINANCIAL['CREATED_ON']  			 = date("Y-m-d H:i");
		db_perform('S_STUDENT_FINANCIAL', $STUDENT_FINANCIAL, 'insert');
		$PK_STUDENT_FINANCIAL = $db->insert_ID();
	/*} else
		$PK_STUDENT_FINANCIAL = $_GET['fid'];*/
	
	$PK_ISIR_STUDENT_MASTER = $_POST['PK_ISIR_STUDENT_MASTER'];
	$res_type1 = $db->Execute("select VALUE, RAW_VALUE, DSIS_FIELD_NAME, HAS_LEDGEND, S_ISIR_STUDENT_DETAIL.PK_ISIR_SETUP_DETAIL from S_ISIR_STUDENT_DETAIL, Z_ISIR_SETUP_DETAIL WHERE S_ISIR_STUDENT_DETAIL.ACTIVE = 1 AND PK_ISIR_STUDENT_MASTER = '$PK_ISIR_STUDENT_MASTER' AND S_ISIR_STUDENT_DETAIL.PK_ISIR_SETUP_DETAIL = Z_ISIR_SETUP_DETAIL.PK_ISIR_SETUP_DETAIL AND DSIS_FIELD_NAME LIKE 'S_STUDENT_FINANCIAL.%' AND S_ISIR_STUDENT_DETAIL.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
	while (!$res_type1->EOF) { 
		$DSIS_FIELD_NAME 						= explode(".",$res_type1->fields['DSIS_FIELD_NAME']);
		$STUDENT_FINANCIAL[$DSIS_FIELD_NAME[1]] = $res_type1->fields['RAW_VALUE'];

		if($DSIS_FIELD_NAME[1] == 'ISIR_PROCESSED_DATE' || $DSIS_FIELD_NAME[1] == 'REPACKAGE_DATE' || $DSIS_FIELD_NAME[1] == 'MARITAL_STATUS_DATE') {
			 // Ticket #1132
			if(trim($res_type1->fields['RAW_VALUE']) != '') {
				$RAW_VALUE11 = $res_type1->fields['RAW_VALUE'];		
				if(strlen($RAW_VALUE11) == 6)
					$RAW_VALUE11 = '20'.$RAW_VALUE11;
									
				$STUDENT_FINANCIAL[$DSIS_FIELD_NAME[1]] = date("Y-m-d",strtotime($RAW_VALUE11));
			} else if(trim($res_type1->fields['VALUE']) != '') {
				$VALUE11 = $res_type1->fields['VALUE'];		
				if(strlen($VALUE11) == 6)
					$VALUE11 = '20'.$VALUE11;
					
				$STUDENT_FINANCIAL[$DSIS_FIELD_NAME[1]] = date("Y-m-d",strtotime($VALUE11));
			} else
				$STUDENT_FINANCIAL[$DSIS_FIELD_NAME[1]] = '';
			 // Ticket #1132
		} else if($DSIS_FIELD_NAME[1] == 'PK_DEPENDENT_STATUS'){
			$val1 = addslashes($res_type1->fields['RAW_VALUE']);
			$res_st = $db->Execute("select PK_DEPENDENT_STATUS from M_DEPENDENT_STATUS WHERE (TRIM(DESCRIPTION) = '$val1' OR TRIM(CODE) = '$val1') AND ACTIVE = 1");
			$STUDENT_FINANCIAL['PK_DEPENDENT_STATUS'] = $res_st->fields['PK_DEPENDENT_STATUS'];
		} else if($DSIS_FIELD_NAME[1] == 'PK_DEGREE_CERT'){
			$val1 = addslashes($res_type1->fields['RAW_VALUE']);
			$res_st = $db->Execute("select PK_DEGREE_CERT from M_DEGREE_CERT WHERE TRIM(CODE) = '$val1' AND ACTIVE = 1");
			$STUDENT_FINANCIAL['PK_DEGREE_CERT'] = $res_st->fields['PK_DEGREE_CERT'];
		} else if($DSIS_FIELD_NAME[1] == 'PK_COA_CATEGORY'){
			$val1 = addslashes($res_type1->fields['VALUE']);
			$res_st = $db->Execute("select PK_COA_CATEGORY from M_COA_CATEGORY WHERE TRIM(DESCRIPTION) = '$val1' AND ACTIVE = 1");
			$STUDENT_FINANCIAL['PK_COA_CATEGORY'] = $res_st->fields['PK_COA_CATEGORY'];
		} else if($DSIS_FIELD_NAME[1] == 'PK_LENDER_MASTER'){
			$val1 = addslashes($res_type1->fields['VALUE']);
			$res_st = $db->Execute("select PK_LENDER_MASTER from S_LENDER_MASTER WHERE TRIM(LENDER) = '$val1' AND ACTIVE = 1 AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
			$STUDENT_FINANCIAL['PK_LENDER_MASTER'] = $res_st->fields['PK_LENDER_MASTER'];
		} 
		else if($DSIS_FIELD_NAME[1] == 'SELECTED_FOR_VERIFICATION' || $DSIS_FIELD_NAME[1] == 'PROFESSIONAL_JUDGEMENT' || $DSIS_FIELD_NAME[1] == 'STUDENT_DEGREE' ) // DIAM-2230
		{ 
			$STUDENT_FINANCIAL[$DSIS_FIELD_NAME[1]] = $res_type1->fields['RAW_VALUE'];
			if(trim(strtolower($STUDENT_FINANCIAL[$DSIS_FIELD_NAME[1]])) == 'yes' || trim(strtolower($STUDENT_FINANCIAL[$DSIS_FIELD_NAME[1]])) == 'y' || trim(strtolower($STUDENT_FINANCIAL[$DSIS_FIELD_NAME[1]])) == '1')
				$STUDENT_FINANCIAL[$DSIS_FIELD_NAME[1]] = 1;
			else if(trim(strtolower($STUDENT_FINANCIAL[$DSIS_FIELD_NAME[1]])) == 'no' || trim(strtolower($STUDENT_FINANCIAL[$DSIS_FIELD_NAME[1]])) == 'n' || trim(strtolower($STUDENT_FINANCIAL[$DSIS_FIELD_NAME[1]])) == '2' )
				$STUDENT_FINANCIAL[$DSIS_FIELD_NAME[1]] = 2;
			else if(trim(strtolower($STUDENT_FINANCIAL[$DSIS_FIELD_NAME[1]])) == 'c' || trim(strtolower($STUDENT_FINANCIAL[$DSIS_FIELD_NAME[1]])) == 'change in verification tracking group' )
				$STUDENT_FINANCIAL[$DSIS_FIELD_NAME[1]] = 3;
			else
				$STUDENT_FINANCIAL[$DSIS_FIELD_NAME[1]] = '';
		} 
		else if($DSIS_FIELD_NAME[1] == 'AUTOMATIC_ZERO_EFC' || $DSIS_FIELD_NAME[1] == 'ISIR_CLEAR_PAY'){

			$STUDENT_FINANCIAL[$DSIS_FIELD_NAME[1]] = $res_type1->fields['VALUE'];
			
			if(trim(strtolower($STUDENT_FINANCIAL[$DSIS_FIELD_NAME[1]])) == 'yes' || trim(strtolower($STUDENT_FINANCIAL[$DSIS_FIELD_NAME[1]])) == 'y' || trim(strtolower($STUDENT_FINANCIAL[$DSIS_FIELD_NAME[1]])) == '1')
				$STUDENT_FINANCIAL[$DSIS_FIELD_NAME[1]] = 1;
			else if(trim(strtolower($STUDENT_FINANCIAL[$DSIS_FIELD_NAME[1]])) == 'no' || trim(strtolower($STUDENT_FINANCIAL[$DSIS_FIELD_NAME[1]])) == 'n' || trim(strtolower($STUDENT_FINANCIAL[$DSIS_FIELD_NAME[1]])) == '2' )
				$STUDENT_FINANCIAL[$DSIS_FIELD_NAME[1]] = 2;
			else if(trim(strtolower($STUDENT_FINANCIAL[$DSIS_FIELD_NAME[1]])) == 'c' || trim(strtolower($STUDENT_FINANCIAL[$DSIS_FIELD_NAME[1]])) == 'change in verification tracking group' )
				$STUDENT_FINANCIAL[$DSIS_FIELD_NAME[1]] = 3;
			else
				$STUDENT_FINANCIAL[$DSIS_FIELD_NAME[1]] = '';
		} else if($DSIS_FIELD_NAME[1] == 'OVERRIDE'){
			$val1 = addslashes($res_type1->fields['RAW_VALUE']); // DIAM-2230
			$res_st = $db->Execute("select PK_DEPENDENCY_OVERRIDE from M_DEPENDENCY_OVERRIDE WHERE TRIM(DEPENDENCY_OVERRIDE) = '$val1' AND ACTIVE = 1");
			$STUDENT_FINANCIAL['PK_DEPENDENCY_OVERRIDE'] = $res_st->fields['PK_DEPENDENCY_OVERRIDE'];
		} else if($DSIS_FIELD_NAME[1] == 'PK_VA_STUDENT'){
			$val1 = addslashes($res_type1->fields['RAW_VALUE']); // DIAM-2230
			$res_st = $db->Execute("select PK_VA_STUDENT from M_VA_STUDENT WHERE TRIM(VA_STUDENT) = '$val1' AND ACTIVE = 1");
			$STUDENT_FINANCIAL['PK_VA_STUDENT'] = $res_st->fields['PK_VA_STUDENT'];
		} else if($DSIS_FIELD_NAME[1] == 'PK_AWARD_YEAR'){
			$val1 = '202'.addslashes($res_type1->fields['VALUE']);
			$res_st = $db->Execute("select PK_AWARD_YEAR from M_AWARD_YEAR WHERE YEAR(END_DATE) = '$val1' AND ACTIVE = 1");
			$STUDENT_FINANCIAL['PK_AWARD_YEAR'] = $res_st->fields['PK_AWARD_YEAR'];
		} else {
			$STUDENT_FINANCIAL[$DSIS_FIELD_NAME[1]] = $res_type1->fields['VALUE'];
		}
		
		$res_type1->MoveNext();
	}

	$res_type1 = $db->Execute("select VALUE, RAW_VALUE, DSIS_FIELD_NAME, HAS_LEDGEND, S_ISIR_STUDENT_DETAIL.PK_ISIR_SETUP_DETAIL from S_ISIR_STUDENT_DETAIL, Z_ISIR_SETUP_DETAIL WHERE S_ISIR_STUDENT_DETAIL.ACTIVE = 1 AND PK_ISIR_STUDENT_MASTER = '$PK_ISIR_STUDENT_MASTER' AND S_ISIR_STUDENT_DETAIL.PK_ISIR_SETUP_DETAIL = Z_ISIR_SETUP_DETAIL.PK_ISIR_SETUP_DETAIL AND DSIS_FIELD_NAME LIKE 'S_STUDENT_MASTER.PK_MARITAL_STATUS' AND S_ISIR_STUDENT_DETAIL.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
	if($res_type1->RecordCount() > 0){
		$val1 = addslashes($res_type1->fields['VALUE']);
		$res_st = $db->Execute("select PK_MARITAL_STATUS from Z_MARITAL_STATUS WHERE TRIM(MARITAL_STATUS) = '$val1' AND ACTIVE = 1 ");
		$STUDENT_FINANCIAL['PK_MARITAL_STATUS'] = $res_st->fields['PK_MARITAL_STATUS'];
	}
	
	$res_type1 = $db->Execute("select VALUE, RAW_VALUE, DSIS_FIELD_NAME, HAS_LEDGEND, S_ISIR_STUDENT_DETAIL.PK_ISIR_SETUP_DETAIL from S_ISIR_STUDENT_DETAIL, Z_ISIR_SETUP_DETAIL WHERE S_ISIR_STUDENT_DETAIL.ACTIVE = 1 AND PK_ISIR_STUDENT_MASTER = '$PK_ISIR_STUDENT_MASTER' AND S_ISIR_STUDENT_DETAIL.PK_ISIR_SETUP_DETAIL = Z_ISIR_SETUP_DETAIL.PK_ISIR_SETUP_DETAIL AND DSIS_FIELD_NAME LIKE 'S_STUDENT_MASTER.PK_CITIZENSHIP' AND S_ISIR_STUDENT_DETAIL.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
	if($res_type1->RecordCount() > 0){
		$val1 = addslashes($res_type1->fields['RAW_VALUE']);
		$res_st = $db->Execute("select PK_ELIGIBLE_CITIZEN from M_ELIGIBLE_CITIZEN WHERE TRIM(ELIGIBLE_CITIZEN) = '$val1' OR PK_ELIGIBLE_CITIZEN='$val1' AND ACTIVE = 1");
		$STUDENT_FINANCIAL['PK_ELIGIBLE_CITIZEN'] = $res_st->fields['PK_ELIGIBLE_CITIZEN'];
	}
	
	$STUDENT_FINANCIAL['EDITED_BY']  = $_SESSION['PK_USER'];
	$STUDENT_FINANCIAL['EDITED_ON']  = date("Y-m-d H:i");
	unset($STUDENT_FINANCIAL['NO_OF_DEPENDENTS_PARENT']);
	unset($STUDENT_FINANCIAL['NUMBER_IN_COLLEGE_PARENT']);
	unset($STUDENT_FINANCIAL['NO_OF_DEPENDENTS_STUDENT']);
	unset($STUDENT_FINANCIAL['NUMBER_IN_COLLEGE_STUDENT']);
	unset($STUDENT_FINANCIAL['OVERRIDE']);
	
	db_perform('S_STUDENT_FINANCIAL', $STUDENT_FINANCIAL, 'update'," PK_STUDENT_FINANCIAL = '$PK_STUDENT_FINANCIAL' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	//echo "<pre> PK_STUDENT_FINANCIAL = '$PK_STUDENT_FINANCIAL' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ";print_r($STUDENT_FINANCIAL);exit;
	$res_type1 = $db->Execute("select PK_DEPENDENT_STATUS FROM S_STUDENT_FINANCIAL WHERE PK_STUDENT_FINANCIAL = '$PK_STUDENT_FINANCIAL' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	if($res_type1->fields['PK_DEPENDENT_STATUS'] == 1) {
		//Independent
		$res_type1 = $db->Execute("select VALUE, RAW_VALUE from S_ISIR_STUDENT_DETAIL, Z_ISIR_SETUP_DETAIL WHERE S_ISIR_STUDENT_DETAIL.ACTIVE = 1 AND PK_ISIR_STUDENT_MASTER = '$PK_ISIR_STUDENT_MASTER' AND S_ISIR_STUDENT_DETAIL.PK_ISIR_SETUP_DETAIL = Z_ISIR_SETUP_DETAIL.PK_ISIR_SETUP_DETAIL AND DSIS_FIELD_NAME LIKE 'S_STUDENT_FINANCIAL.NO_OF_DEPENDENTS_STUDENT' AND S_ISIR_STUDENT_DETAIL.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
		
		$STUDENT_FINANCIAL = array();
		$STUDENT_FINANCIAL['NO_OF_DEPENDENTS'] = $res_type1->fields['VALUE'];
		db_perform('S_STUDENT_FINANCIAL', $STUDENT_FINANCIAL, 'update'," PK_STUDENT_FINANCIAL = '$PK_STUDENT_FINANCIAL' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		//echo "<pre>";print_r($STUDENT_FINANCIAL);		
		$res_type1 = $db->Execute("select VALUE, RAW_VALUE from S_ISIR_STUDENT_DETAIL, Z_ISIR_SETUP_DETAIL WHERE S_ISIR_STUDENT_DETAIL.ACTIVE = 1 AND PK_ISIR_STUDENT_MASTER = '$PK_ISIR_STUDENT_MASTER' AND S_ISIR_STUDENT_DETAIL.PK_ISIR_SETUP_DETAIL = Z_ISIR_SETUP_DETAIL.PK_ISIR_SETUP_DETAIL AND DSIS_FIELD_NAME LIKE 'S_STUDENT_FINANCIAL.NUMBER_IN_COLLEGE_STUDENT' AND S_ISIR_STUDENT_DETAIL.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
		
		$STUDENT_FINANCIAL = array();
		$STUDENT_FINANCIAL['NUMBER_IN_COLLEGE'] = $res_type1->fields['VALUE'];
		db_perform('S_STUDENT_FINANCIAL', $STUDENT_FINANCIAL, 'update'," PK_STUDENT_FINANCIAL = '$PK_STUDENT_FINANCIAL' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		//echo "<pre>";print_r($STUDENT_FINANCIAL);		
	} else if($res_type1->fields['PK_DEPENDENT_STATUS'] == 2) {
		//Dependent
		
		$res_type1 = $db->Execute("select VALUE, RAW_VALUE from S_ISIR_STUDENT_DETAIL, Z_ISIR_SETUP_DETAIL WHERE S_ISIR_STUDENT_DETAIL.ACTIVE = 1 AND PK_ISIR_STUDENT_MASTER = '$PK_ISIR_STUDENT_MASTER' AND S_ISIR_STUDENT_DETAIL.PK_ISIR_SETUP_DETAIL = Z_ISIR_SETUP_DETAIL.PK_ISIR_SETUP_DETAIL AND DSIS_FIELD_NAME LIKE 'S_STUDENT_FINANCIAL.NO_OF_DEPENDENTS_PARENT' AND S_ISIR_STUDENT_DETAIL.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
		
		$STUDENT_FINANCIAL = array();
		$STUDENT_FINANCIAL['NO_OF_DEPENDENTS'] = $res_type1->fields['VALUE'];
		db_perform('S_STUDENT_FINANCIAL', $STUDENT_FINANCIAL, 'update'," PK_STUDENT_FINANCIAL = '$PK_STUDENT_FINANCIAL' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		//echo "<pre>";print_r($STUDENT_FINANCIAL);		
		$res_type1 = $db->Execute("select VALUE, RAW_VALUE from S_ISIR_STUDENT_DETAIL, Z_ISIR_SETUP_DETAIL WHERE S_ISIR_STUDENT_DETAIL.ACTIVE = 1 AND PK_ISIR_STUDENT_MASTER = '$PK_ISIR_STUDENT_MASTER' AND S_ISIR_STUDENT_DETAIL.PK_ISIR_SETUP_DETAIL = Z_ISIR_SETUP_DETAIL.PK_ISIR_SETUP_DETAIL AND DSIS_FIELD_NAME LIKE 'S_STUDENT_FINANCIAL.NUMBER_IN_COLLEGE_PARENT' AND S_ISIR_STUDENT_DETAIL.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
		
		$STUDENT_FINANCIAL = array();
		$STUDENT_FINANCIAL['NUMBER_IN_COLLEGE'] = $res_type1->fields['VALUE'];
		db_perform('S_STUDENT_FINANCIAL', $STUDENT_FINANCIAL, 'update'," PK_STUDENT_FINANCIAL = '$PK_STUDENT_FINANCIAL' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
		//echo "<pre>";print_r($STUDENT_FINANCIAL);
	}
	//exit;	
	?>
	<script type="text/javascript">window.opener.ref_fa_win('<?=$PK_STUDENT_FINANCIAL ?>',this)</script>
	<?
} ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
	<? require_once("css.php"); ?>
	<title><?=GET_ISIR_DATA ?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? //require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-3 align-self-center">
                        <h4 class="text-themecolor">
							<?=GET_ISIR_DATA ?> 
						</h4>
                    </div>
					
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
								<table class="table table-hover" >
									<thead>
										<tr>
											<th width="20%"><?=STUDENT_NAME?></th>
											<th width="20%"><?=TRANS?></th>
											<th width="20%"><?=AWARD_YEAR?></th>
											<th width="20%"><?=IMPORTED?></th>
											<th width="20%"><?=IMPORT?></th>
										</tr>
									</thead>
									<tbody>
										<? $res_type = $db->Execute("SELECT S_ISIR_STUDENT_MASTER.PK_ISIR_STUDENT_MASTER,FILE_NAME, CONCAT(LAST_NAME,', ',FIRST_NAME) AS NAME,  EMAIL, FROM_NAME, S_ISIR_STUDENT_MASTER.PK_ISIR_SETUP_MASTER,S_ISIR_STUDENT_MASTER.CREATED_ON FROM S_ISIR_STUDENT_MASTER LEFT JOIN Z_ISIR_SETUP_MASTER ON S_ISIR_STUDENT_MASTER.PK_ISIR_SETUP_MASTER = Z_ISIR_SETUP_MASTER.PK_ISIR_SETUP_MASTER WHERE S_ISIR_STUDENT_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND S_ISIR_STUDENT_MASTER.PK_STUDENT_MASTER = '$_GET[sid]' ");
										while (!$res_type->EOF) { 
											$PK_ISIR_STUDENT_MASTER = $res_type->fields['PK_ISIR_STUDENT_MASTER']; 
											
											$res_type1 = $db->Execute("select VALUE from S_ISIR_STUDENT_DETAIL, Z_ISIR_SETUP_DETAIL WHERE S_ISIR_STUDENT_DETAIL.ACTIVE = 1 AND PK_ISIR_STUDENT_MASTER = '$PK_ISIR_STUDENT_MASTER' AND S_ISIR_STUDENT_DETAIL.PK_ISIR_SETUP_DETAIL = Z_ISIR_SETUP_DETAIL.PK_ISIR_SETUP_DETAIL AND DSIS_FIELD_NAME = 'S_STUDENT_FINANCIAL.ISIR_TRANS_NO' "); ?>
											<tr>
												<td><?=$res_type->fields['NAME']?></td>
												<td><?=$res_type1->fields['VALUE']?></td>
												<td><?=$res_type->fields['FROM_NAME']?></td>
												<td>
													<? if($res_type->fields['CREATED_ON'] != '0000-00-00 00:00:00')
														echo convert_to_user_date($res_type->fields['CREATED_ON'],'m/d/Y h:i A',$res_tz->fields['TIMEZONE'],date_default_timezone_get()); ?>
												</td>
												<td>
													<a href="javascript:void(0)" onclick="import_isir(<?=$PK_ISIR_STUDENT_MASTER?>)" class="btn waves-effect waves-light btn-dark m-l-15"><?=IMPORT?></a>
												</td>
											</tr>
										<?	$res_type->MoveNext();
										} ?>
									</tbody>
								</table>
                            </div>
                        </div>
					</div>
				</div>
				
            </div>
        </div>
        <? require_once("footer.php"); ?>
		
		<div class="modal" id="importModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
			<form class="floating-labels m-t-40" method="post" name="form2" id="form2" >
				<div class="modal-dialog" role="document">
					<div class="modal-content">
						<div class="modal-header">
							<h4 class="modal-title" id="exampleModalLabel1"><?=CONFIRMATION?></h4>
							<input id="PK_ISIR_STUDENT_MASTER" name="PK_ISIR_STUDENT_MASTER" type="hidden" value="" >
						</div>
						<div class="modal-body">
							<div class="form-group">
								Are you sure you want to import the selected ISIR record? 
							</div>
						</div>
						<div class="modal-footer">
							<button type="submit" class="btn waves-effect waves-light btn-info"><?=YES?></button>
							<button type="button" class="btn waves-effect waves-light btn-dark" onclick="conf_import_isir(0)" ><?=NO?></button>
						</div>
					</div>
				</div>
			</form>
		</div>
		
    </div>
   
	<? require_once("js.php"); ?>
	
	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
	
	<script type="text/javascript">

	function import_isir(id){
		jQuery(document).ready(function($) {
			$("#importModal").modal()
			$("#PK_ISIR_STUDENT_MASTER").val(id)
		});
	}
	function conf_import_isir(val,id){
		jQuery(document).ready(function($) {
			if(val == 1)
				window.location.href = 'manage_course?act=del&id='+$("#DELETE_ID").val();
			else
				$("#importModal").modal("hide");
		});
	}
	</script>
</body>

</html>