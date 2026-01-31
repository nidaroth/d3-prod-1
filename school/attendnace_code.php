<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/attendance.php");
require_once("../language/menu.php");
require_once("check_access.php");

if(check_access('SETUP_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}
if(!empty($_POST)){
	foreach($_POST['PK_ATTENDANCE_CODE'] as $PK_ATTENDANCE_CODE){
		$SAP_ATTENDANCE_CODE = array();
		$SAP_ATTENDANCE_CODE['PK_ATTENDANCE_CODE'] 	= $PK_ATTENDANCE_CODE;
		$SAP_ATTENDANCE_CODE['DESCRIPTION'] 		= $_POST['DESCRIPTION_'.$PK_ATTENDANCE_CODE];
		
		$SAP_ATTENDANCE_CODE['PRESENT'] 	= 0;
		$SAP_ATTENDANCE_CODE['ABSENT'] 		= 0;
		$SAP_ATTENDANCE_CODE['CANCELLED'] 	= 0;
		if($_POST['SETTINGS_'.$PK_ATTENDANCE_CODE] == 1)
			$SAP_ATTENDANCE_CODE['PRESENT'] = 1;
		else if($_POST['SETTINGS_'.$PK_ATTENDANCE_CODE] == 2)
			$SAP_ATTENDANCE_CODE['ABSENT'] = 1;
		else if($_POST['SETTINGS_'.$PK_ATTENDANCE_CODE] == 3)
			$SAP_ATTENDANCE_CODE['CANCELLED'] = 1;
		
		
		//$SAP_ATTENDANCE_CODE['SCHEDULED'] 		= $_POST['SCHEDULED_'.$PK_ATTENDANCE_CODE];
		//$SAP_ATTENDANCE_CODE['INSTRUCTOR_PORTAL'] = $_POST['ACTIVE_'.$PK_ATTENDANCE_CODE];
		$SAP_ATTENDANCE_CODE['ACTIVE'] 				= $_POST['ACTIVE_'.$PK_ATTENDANCE_CODE];

		$res = $db->Execute("SELECT PK_ATTENDANCE_CODE FROM S_ATTENDANCE_CODE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_ATTENDANCE_CODE = '$PK_ATTENDANCE_CODE' ");
		if($res->RecordCount() == 0) {	
			$SAP_ATTENDANCE_CODE['PK_ACCOUNT'] = $_SESSION['PK_ACCOUNT'];
			$SAP_ATTENDANCE_CODE['CREATED_BY'] = $_SESSION['PK_USER'];
			$SAP_ATTENDANCE_CODE['CREATED_ON'] = date("Y-m-d H:i");
			db_perform('S_ATTENDANCE_CODE', $SAP_ATTENDANCE_CODE, 'insert');
		} else {
			$SAP_ATTENDANCE_CODE['EDITED_BY'] = $_SESSION['PK_USER'];
			$SAP_ATTENDANCE_CODE['EDITED_ON'] = date("Y-m-d H:i");
			db_perform('S_ATTENDANCE_CODE', $SAP_ATTENDANCE_CODE, 'update'," PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_ATTENDANCE_CODE = '$PK_ATTENDANCE_CODE' ");
		}
	}
	header("location:attendnace_code");
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
	<? require_once("css.php"); ?>
	<title><?=MNU_ATTENDNACE_CODE?> | <?=$title?></title>
	<style>
		.disabled_color{background-color: #DDD !important;}
		.table th, .table td {padding: 0.5rem;}
	</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"> <?=MNU_ATTENDNACE_CODE?> </h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels " method="post" name="form1" id="form1" >
									<div class="table-responsive p-20">
										<table class="table table-hover" id="table_unpaid" >
											<thead>
												<tr>
													<th ><?=CODE?></th>
													<th ><?=DESCRIPTION?></th>
													<th ><?=PRESENT?></th>
													<th ><?=ABSENT?></th>
													<th ><?=CANCELLED?></th>
													<?/*<th ><?=SCHEDULED?></th>*/?>
													<th ><?=ACTIVE?></th>
												</tr>
											</thead>
											<tbody>
												<? $res_att = $db->Execute("select M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE as PK_ATTENDANCE_CODE_1,M_ATTENDANCE_CODE.ATTENDANCE_CODE, M_ATTENDANCE_CODE.CODE, S_ATTENDANCE_CODE.* FROM M_ATTENDANCE_CODE LEFT JOIN S_ATTENDANCE_CODE ON S_ATTENDANCE_CODE.PK_ATTENDANCE_CODE = M_ATTENDANCE_CODE.PK_ATTENDANCE_CODE AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY S_ATTENDANCE_CODE.ACTIVE DESC, M_ATTENDANCE_CODE.ATTENDANCE_CODE ASC");
												while (!$res_att->EOF) { 
													$PK_ATTENDANCE_CODE = $res_att->fields['PK_ATTENDANCE_CODE_1'];
													if($res_att->fields['PK_S_ATTENDANCE_CODE'] > 0)
														$DESCRIPTION = $res_att->fields['DESCRIPTION'];
													else
														$DESCRIPTION = $res_att->fields['ATTENDANCE_CODE']; ?>
													<tr>
														<td class="disabled_color" data-sort="<?=$res_att->fields['ATTENDANCE_CODE']?>" >
															<?=$res_att->fields['CODE']?>
															<input type="hidden" name="PK_ATTENDANCE_CODE[]" id="<?=$PK_ATTENDANCE_CODE?>" value="<?=$PK_ATTENDANCE_CODE?>" />
														</td>
									
														<td data-sort="<?=$DESCRIPTION?>" >
															<input type="text" name="DESCRIPTION_<?=$PK_ATTENDANCE_CODE?>" id="DESCRIPTION_<?=$PK_ATTENDANCE_CODE?>" value="<?=$DESCRIPTION?>" class="form-control" />
														</td>
														
														<td  data-sort="<?=$res_att->fields['PRESENT']?>" >
															<div class="row"  >
																<div class="custom-control custom-radio col-md-12">
																	<center>
																		<input type="radio" id="SETTINGS_P_<?=$PK_ATTENDANCE_CODE?>" name="SETTINGS_<?=$PK_ATTENDANCE_CODE?>" value="1" <? if($res_att->fields['PRESENT'] == 1) echo "checked"; ?> class="custom-control-input">
																		<label class="custom-control-label" for="SETTINGS_P_<?=$PK_ATTENDANCE_CODE?>">&nbsp;</label>
																	<center>
																</div>
															</div>
														</td>
														<td data-sort="<?=$res_att->fields['ABSENT']?>" >
															<div class="row"  >
																<div class="custom-control custom-radio col-md-12">
																	<center>
																		<input type="radio" id="SETTINGS_A_<?=$PK_ATTENDANCE_CODE?>" name="SETTINGS_<?=$PK_ATTENDANCE_CODE?>" value="2" <? if($res_att->fields['ABSENT'] == 1) echo "checked"; ?> class="custom-control-input">
																		<label class="custom-control-label" for="SETTINGS_A_<?=$PK_ATTENDANCE_CODE?>">&nbsp;</label>
																	<center>
																</div>
															</div>
														</td>
														<td data-sort="<?=$res_att->fields['CANCELLED']?>" >
															<div class="row"  >
																<div class="custom-control custom-radio col-md-12">
																	<center>
																		<input type="radio" id="SETTINGS_C_<?=$PK_ATTENDANCE_CODE?>" name="SETTINGS_<?=$PK_ATTENDANCE_CODE?>" value="3" <? if($res_att->fields['CANCELLED'] == 1) echo "checked"; ?> class="custom-control-input">
																		<label class="custom-control-label" for="SETTINGS_C_<?=$PK_ATTENDANCE_CODE?>">&nbsp;</label>
																	<center>
																</div>
															</div>
														</td>
														<? /* <td >
															<div class="d-flex" style="text-align:center;" >
																<div class="col-12 col-sm-12 custom-control custom-checkbox " >
																	<input type="checkbox" class="custom-control-input ENROLLMENT_check_box" id="SCHEDULED_<?=$PK_ATTENDANCE_CODE?>" name="SCHEDULED_<?=$PK_ATTENDANCE_CODE?>" value="1" <? if($res_att->fields['SCHEDULED'] == 1) echo "checked"; ?> >
																	<label class="custom-control-label" for="SCHEDULED_<?=$PK_ATTENDANCE_CODE?>">&nbsp;</label>
																</div>
															</div>
														</td> */?>
														<td data-sort="<?=$res_att->fields['ACTIVE']?>" >
															<div class="d-flex" style="text-align:center;" >
																<div class="col-12 col-sm-12 custom-control custom-checkbox " >
																	<input type="checkbox" class="custom-control-input ENROLLMENT_check_box" id="ACTIVE_<?=$PK_ATTENDANCE_CODE?>" name="ACTIVE_<?=$PK_ATTENDANCE_CODE?>" value="1" <? if($res_att->fields['ACTIVE'] == 1) echo "checked"; ?> >
																	<label class="custom-control-label" for="ACTIVE_<?=$PK_ATTENDANCE_CODE?>">&nbsp;</label>
																</div>
															</div>
														</td>
													</tr>
												<? $res_att->MoveNext();
												} ?>
											</tbody>
										</table>
									</div>			
										
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-5"  style="text-align:right" >
												<button type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
												
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='setup'" ><?=CANCEL?></button>
												
											</div>
										</div>
									</div>
                                </form>
                            </div>
                        </div>
					</div>
				</div>
				
            </div>
        </div>
        <? require_once("footer.php"); ?>
    </div>
	
	<? require_once("js.php"); ?>
	<script type="text/javascript">
	jQuery(document).ready(function($) { 
			$('#myTable').DataTable();
			var table = $('#table_unpaid').DataTable({
				"bPaginate": false,
				searching: false,
				info: false,
				 "aoColumns":[
					{"bSortable": true},
					{"bSortable": true},
					{"bSortable": true},
					{"bSortable": true},
					{"bSortable": true},
					{"bSortable": true}
				]
			});
		});
	</script>
	
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
		var form1 = new Validation('form1');
	</script>
	<link href="../backend_assets/node_modules/bootstrap-table/dist/bootstrap-table.min.css" rel="stylesheet" type="text/css" />
	<script src="../backend_assets/node_modules/bootstrap-table/dist/bootstrap-table.min.js"></script>
	
	 <!-- This is data table -->
    <script src="../backend_assets/node_modules/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="../backend_assets/node_modules/datatables.net-bs4/js/dataTables.responsive.min.js"></script>
</body>

</html>