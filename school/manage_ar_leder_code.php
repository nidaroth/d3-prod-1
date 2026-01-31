<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/ar_leder_code.php");
require_once("check_access.php");

if(check_access('SETUP_ACCOUNTING') == 0 ){
	header("location:../index");
	exit;
}
if($_GET['act'] == 'del')	{
	$res_check1 = $db->Execute("select PK_CAMPUS_PROGRAM_AWARD from M_CAMPUS_PROGRAM_AWARD WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_AR_LEDGER_CODE = '$_GET[id]' ");
	$res_check2 = $db->Execute("select PK_CAMPUS_PROGRAM_FEE from M_CAMPUS_PROGRAM_FEE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_AR_LEDGER_CODE = '$_GET[id]' ");
	$res_check3 = $db->Execute("select PK_COURSE_FEE from S_COURSE_FEE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_AR_LEDGER_CODE = '$_GET[id]' ");
	$res_check4 = $db->Execute("select PK_MISC_BATCH_DETAIL from S_MISC_BATCH_DETAIL WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_AR_LEDGER_CODE = '$_GET[id]' ");
	$res_check5 = $db->Execute("select PK_PAYMENT_BATCH_MASTER from S_PAYMENT_BATCH_MASTER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_AR_LEDGER_CODE LIKE '%$_GET[id]%' ");
	$res_check6 = $db->Execute("select PK_STUDENT_APPROVED_AWARD_SUMMARY from S_STUDENT_APPROVED_AWARD_SUMMARY WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_AR_LEDGER_CODE = '$_GET[id]' ");
	$res_check7 = $db->Execute("select PK_STUDENT_AWARD from S_STUDENT_AWARD WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_AR_LEDGER_CODE = '$_GET[id]' ");
	$res_check8 = $db->Execute("select PK_STUDENT_DISBURSEMENT from S_STUDENT_DISBURSEMENT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_AR_LEDGER_CODE = '$_GET[id]' ");
	$res_check9 = $db->Execute("select PK_STUDENT_FEE_BUDGET from S_STUDENT_FEE_BUDGET WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_AR_LEDGER_CODE = '$_GET[id]' ");
	$res_check10 = $db->Execute("select PK_STUDENT_LEDGER from S_STUDENT_LEDGER WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_AR_LEDGER_CODE = '$_GET[id]' ");
	$res_check11 = $db->Execute("select PK_TUITION_BATCH_DETAIL from S_TUITION_BATCH_DETAIL WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_AR_LEDGER_CODE = '$_GET[id]' ");
	
	if($res_check1->RecordCount() == 0 && $res_check2->RecordCount() == 0 && $res_check3->RecordCount() == 0 && $res_check4->RecordCount() == 0 && $res_check5->RecordCount() == 0 && $res_check6->RecordCount() == 0 && $res_check7->RecordCount() == 0 && $res_check8->RecordCount() == 0 && $res_check9->RecordCount() == 0 && $res_check10->RecordCount() == 0 && $res_check11->RecordCount() == 0) 
		$db->Execute("DELETE FROM M_AR_LEDGER_CODE WHERE PK_AR_LEDGER_CODE = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	header("location:manage_ar_leder_code");
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
	<title><?=AR_LEDGER_CODE_PAGE_TITLE?> | <?=$title?></title>
	<link rel="stylesheet" type="text/css" href="../backend_assets/dist/css/easyui.css">
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
				 
                    <div class="col-md-2 align-self-center" style="flex: 0 0 10.66667%;max-width: 10.66667%;" >
                        <h4 class="text-themecolor"><?=AR_LEDGER_CODE_PAGE_TITLE?></h4>
                    </div>
					
					<div class="col-md-1 align-self-center text-right" style="flex: 0 0 14.33333%;max-width: 14.33333%;" >
                        <select id="TYPE" name="TYPE" class="form-control" onchange="doSearch()">
							<option value=""><?=TYPE?></option>
							<option value="1" <? if($TYPE == 1) echo "selected"; ?> >Award</option>
							<option value="2" <? if($TYPE == 2) echo "selected"; ?> >Fee</option>
						</select>
					</div>  
					
					<div class="col-md-1 align-self-center">
						<input type="checkbox" id="ACTIVE" value="1" onclick="doSearch()" > <?=ACTIVE?>
						<br />
						<input type="checkbox" id="INVOICE" value="1" onclick="doSearch()" > <?=INVOICE?>
					</div>
					<div class="col-md-1 align-self-center" style="flex: 0 0 10%;max-width: 10%;" >
						<input type="checkbox" id="OFFER_LETTER" value="1" onclick="doSearch()" > <?=OFFER_LETTER?>
						<br />
						<input type="checkbox" id="TITLE_IV" value="1" onclick="doSearch()" > <?=TITLE_IV?>
					</div>
					<div class="col-md-1 align-self-center" style="flex: 0 0 10%;max-width: 10%;" >
						<input type="checkbox" id="QUICK_PAYMENT" value="1" onclick="doSearch()" > <?=QUICK_PAYMENT?><br /><br />
					</div>
					
					<div class="col-md-3 align-self-center text-right" style="flex: 0 0 23%;max-width: 23%;" >
                        <input type="text" class="form-control" id="SEARCH" name="SEARCH" placeholder="&#xF002; <?=SEARCH?>" onkeypress="search(event)" style="font-family: FontAwesome;margin-top:-8px" >
					</div>  
					
                    <div class="col-md-3 align-self-center text-right" style="flex: 0 0 19%;max-width: 19%;" >
                        <div class="d-flex justify-content-end align-items-center">
                            <a href="ar_leder_code" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> <?=CREATE_NEW?></a>
							
							<a href="ar_leder_code_upload" class="btn btn-info d-none d-lg-block m-l-15"><i class="fas fa-upload"></i> <?=UPLOAD?></a>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
								<div class="row">
									<div class="col-md-12">
										<table id="tt" striped = "true" class="easyui-datagrid table table-bordered table-striped" url="grid_ar_leder_code"
										toolbar="#tb" pagination="true" pageSize = 25 >
											<thead>
												<tr>
													<th field="PK_AR_LEDGER_CODE" width="150px" hidden="true" sortable="true" ></th>
													<th field="CODE" width="150px" align="left" sortable="true" ><?=LEDGER_CODE?></th>
													<th field="LEDGER_DESCRIPTION" width="170px" align="left" sortable="true" ><?=LEDGER_DESCRIPTION?></th>
													<th field="INVOICE_DESCRIPTION" width="170px" align="left" sortable="true" ><?=INVOICE_DESCRIPTION?></th>
													<th field="TYPE" width="70px" align="left" sortable="true" ><?=TYPE?></th>
													<th field="GL_CODE_DEBIT" width="130px" align="left" sortable="true" ><?=GL_CODE_DEBIT?></th>
													<th field="GL_CODE_CREDIT" width="135px" align="left" sortable="true" ><?=GL_CODE_CREDIT?></th>
													<th field="DIAMOND_PAY" width="135px" align="left" sortable="true" ><?=DIAMOND_PAY?></th>
													<th field="INVOICE_1" width="70px" align="left" sortable="true" ><?=INVOICE?></th>
													<th field="AWARD_LETTER_1" width="100px" align="left" sortable="true" ><?=OFFER_LETTER?></th>											
													<th field="QUICK_PAYMENT" width="135px" align="left" sortable="true" ><?=QUICK_PAYMENT?></th>
													<th field="TITLE_IV_1" width="70px" align="left" sortable="true" ><?=TITLE_IV?></th>
													<th field="ACTIVE_1" width="80px" align="left" sortable="false" ><?=OPTION?></th>
												</tr>
											</thead>
										</table>
									</div>
								</div>
                            </div>
                        </div>
					</div>
				</div>
				
            </div>
        </div>

        <? require_once("footer.php"); ?>
		<div class="modal" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title" id="exampleModalLabel1"><?=DELETE_CONFIRMATION?></h4>
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<?=DELETE_MESSAGE_GENERAL?>
							<input type="hidden" id="DELETE_ID" value="0" />
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" onclick="conf_delete(1)" class="btn waves-effect waves-light btn-info"><?=YES?></button>
						<button type="button" class="btn waves-effect waves-light btn-dark" onclick="conf_delete(0)" ><?=NO?></button>
					</div>
				</div>
			</div>
		</div>
    </div>
	<? require_once("js.php"); ?>
	
	<script src="../backend_assets/dist/js/jquery-migrate-1.0.0.js"></script>
	<script type="text/javascript" src="../backend_assets/dist/js/jquery.easyui.min.js"></script>
	<script src="../backend_assets/dist/js/jquery-ui.js"></script> 
	<script type="text/javascript">
	function doSearch(){
		jQuery(document).ready(function($) {
			$('#tt').datagrid('load',{
				SEARCH  : $('#SEARCH').val(),
				TYPE    : $('#TYPE').val(),
				
				ACTIVE			: $('#ACTIVE').is(":checked"),
				INVOICE			: $('#INVOICE').is(":checked"),
				OFFER_LETTER	: $('#OFFER_LETTER').is(":checked"),
				TITLE_IV		: $('#TITLE_IV').is(":checked"),
				QUICK_PAYMENT	: $('#QUICK_PAYMENT').is(":checked"),
			});
		});	
	}
	function search(e){
		if (e.keyCode == 13) {
			doSearch();
		}
	}
	$(function(){
		jQuery(document).ready(function($) {
			$('#tt').datagrid({
				onClickCell: function(rowIndex, field, value){
					$('#tt').datagrid('selectRow',rowIndex);
					if(field != 'ACTION' ){
						var selected_row = $('#tt').datagrid('getSelected');
						window.location.href='ar_leder_code?id='+selected_row.PK_AR_LEDGER_CODE;
					}
				}
			});
			
			$('#tt').datagrid({
				view: $.extend(true,{},$.fn.datagrid.defaults.view,{
					onAfterRender: function(target){
						$.fn.datagrid.defaults.view.onAfterRender.call(this,target);
						$('.datagrid-header-inner').width('100%') 
						$('.datagrid-btable').width('100%') 
						$('.datagrid-body').css({'overflow-y': 'hidden'});
					}
				})
			});

		});
	});
	jQuery(document).ready(function($) {
		$(window).resize(function() {
			$('#tt').datagrid('resize');
			$('#tb').panel('resize');
		}) 
	});
	function delete_row(id){
		jQuery(document).ready(function($) {
			var data  = 'id='+id+'&type=ledger_code';
			var value = $.ajax({
				url: "ajax_check_delete",	
				type: "POST",		 
				data: data,		
				async: false,
				cache: false,
				success: function (data) {	
					if(data == "a") {
						$("#deleteModal").modal()
						$("#DELETE_ID").val(id)
					} else {
						alert("Cannot Delete as this Data is Used on Other Tables")
						$("#DELETE_ID").val('')
					}
				}		
			}).responseText;
		});
	}
	function conf_delete(val,id){
		if(val == 1)
			window.location.href = 'manage_ar_leder_code?act=del&id='+$("#DELETE_ID").val();
		else
			$("#deleteModal").modal("hide");
	}
	</script>

</body>

</html>