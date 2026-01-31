<?php 

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
//
set_time_limit(600); // 600 segundos = 10 minutos
ini_set('max_execution_time', 600);
//

require_once('../global/config.php');
require_once("../language/common.php");
require_once("../language/menu.php");
require_once("../language/student.php");
require_once("../language/custom_report.php");
require_once("../language/student_contact.php");
require_once("check_access.php");

$res_add_on = $db->Execute("SELECT CUSTOM_QUERIES FROM Z_ACCOUNT_REPORTS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
if($res_add_on->fields['CUSTOM_QUERIES'] == 0 || check_access('MANAGEMENT_CUSTOM_QUERY') == 0){
	header("location:../index");
	exit;
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
	<title>
		<?=MNU_QUERIES ?> | <?=$title?>
	</title>
	<link rel="stylesheet" type="text/css" href="../backend_assets/dist/css/easyui.css">
	<style>
	.custom_table{};
	</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
				<div class="row page-titles" style="padding-bottom: 10px;" >
                    <div class="col-md-8 align-self-center">
                        <h4 class="text-themecolor">
							<? $queryhead = $db->Execute("SELECT PK_CUSTOM_QUERY_ACCOUNT,CUSTOM_NAME,EXTERNAL_DESCRIPTION, M_CUSTOM_QUERY.PK_CUSTOM_QUERY,CUSTOM_QUERY FROM M_CUSTOM_QUERY_ACCOUNT, M_CUSTOM_QUERY WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND M_CUSTOM_QUERY.PK_CUSTOM_QUERY = M_CUSTOM_QUERY_ACCOUNT.PK_CUSTOM_QUERY  AND PK_CUSTOM_QUERY_ACCOUNT = '$_GET[id]' ");
							echo MNU_QUERIES.' - '.$queryhead->fields['CUSTOM_NAME']; ?>
							
							<button onclick="javascript:window.location.href='custom_queries_excel?id=<?=$_GET['id']?>'" type="button" class="btn waves-effect waves-light btn-info"><?=EXCEL?></button>
						</h4>
                    </div>
				</div>
				
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
								<div class="row">
									<div class="col-md-12" style="max-height:600px;overflow-x: auto;overflow-y: auto;">
										<table data-toggle="table" data-height="500" data-mobile-responsive="true" class="table-striped " >
											<? $i = 0;
											if($queryhead->fields['PK_CUSTOM_QUERY'] == 80){


												try {
												    // Mostrar el query
												    // echo "<pre>" . $res->fields['CUSTOM_QUERY'] . "</pre>";

												    // Ejecutar query
												    $res = $db->Execute($queryhead->fields['CUSTOM_QUERY']);

												    if ($res === false) {
												        // Captura error de ADOdb si el query falla
												        throw new Exception("Error query: " . $db->ErrorMsg());
												    }

												    // Mostrar resultado del query (para debug)
												    // echo "<pre>";
												    // print_r($res);
												    // echo "</pre>";
												    
												} catch (Exception $e) {
												    echo "<div style='color:red; font-weight:bold;'>Error:</div>";
												    echo "<pre>" . $e->getMessage() . "</pre>";
												    // Aquí puedes loguear también: error_log($e->getMessage());
												}
												// exit;

											}else{
												$res = $db->Execute("CALL DSIS_CUSTOM_QUERY(".$_SESSION['PK_ACCOUNT'].", ".$queryhead->fields['PK_CUSTOM_QUERY'].")"); 	
											}?>
											<thead>
											    <tr>
											        <?php foreach ($res->fields as $key => $val) { ?>
											            <th><?= trim($key) ?></th>
											        <?php } ?>
											    </tr>
											</thead>
											<tbody>
											    <?php
											    while (!$res->EOF) { ?>
											        <tr>
											            <?php foreach ($res->fields as $key => $val) { ?>
											                <td><?= trim($val) ?></td>
											            <?php } ?>
											        </tr>
											    <?php
											        $res->MoveNext();
											    } ?>
											</tbody>
										</table>
									</div>
								</div>
								
								<br />
								<div id="paginator1" class="datepaginator">
									<ul class="pagination">
										<li><div class="pagination-info1 float-right col-xs-5 " ><strong>Total Records: <?=$res->RecordCount() ?></strong></div></li>
									</ul>
								</div>
                            </div>
                        </div>
					</div>
				</div>
				
            </div>
        </div>

        <? require_once("footer.php"); ?>
	
    </div>
	<? require_once("js.php"); ?>
	<link href="../backend_assets/node_modules/bootstrap-table/dist/bootstrap-table.min.css" rel="stylesheet" type="text/css" />
	<script src="../backend_assets/node_modules/bootstrap-table/dist/bootstrap-table.min.js"></script>
	
</body>

</html>