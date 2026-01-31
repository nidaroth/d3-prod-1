<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/term_block.php");
require_once("check_access.php");

if(check_access('SETUP_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}
if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	$TERM_BLOCK = $_POST;
	
	if($TERM_BLOCK['BEGIN_DATE'] != '')
		$TERM_BLOCK['BEGIN_DATE'] = date("Y-m-d",strtotime($TERM_BLOCK['BEGIN_DATE']));
		
	if($TERM_BLOCK['END_DATE'] != '')
		$TERM_BLOCK['END_DATE'] = date("Y-m-d",strtotime($TERM_BLOCK['END_DATE']));
		
	if($_GET['id'] == ''){
		$TERM_BLOCK['PK_ACCOUNT']  = $_SESSION['PK_ACCOUNT'];
		$TERM_BLOCK['CREATED_BY']  = $_SESSION['PK_USER'];
		$TERM_BLOCK['CREATED_ON']  = date("Y-m-d H:i");
		db_perform('S_TERM_BLOCK', $TERM_BLOCK, 'insert');
	} else {
		$TERM_BLOCK['EDITED_BY'] = $_SESSION['PK_USER'];
		$TERM_BLOCK['EDITED_ON'] = date("Y-m-d H:i");
		db_perform('S_TERM_BLOCK', $TERM_BLOCK, 'update'," PK_TERM_BLOCK = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'");
	}

	if($_GET['id'] != '')
	{
		header("location:term_block?id=".$_GET['id']);
	}
	else{
		header("location:manage_term_block");
	}
	
}
if($_GET['id'] == ''){
	$BEGIN_DATE 	= '';
	$END_DATE	 	= '';	
	$DESCRIPTION	= '';
	$DAYS	 		= '';
	$JAN	 		= '';
	$FEB	 		= '';
	$MAR	 		= '';
	$APR	 		= '';
	$MAY	 		= '';
	$JUN	 		= '';
	$JUL	 		= '';
	$AUG	 		= '';
	$SEP	 		= '';
	$OCT	 		= '';
	$NOV	 		= '';
	$DECEMBER	 	= '';
	$ACTIVE	 		= '';
} else {
	$res = $db->Execute("SELECT * FROM S_TERM_BLOCK WHERE PK_TERM_BLOCK = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	if($res->RecordCount() == 0){
		header("location:manage_term_block");
		exit;
	}
	$BEGIN_DATE 	= $res->fields['BEGIN_DATE'];
	$END_DATE  		= $res->fields['END_DATE'];
	$DESCRIPTION  	= $res->fields['DESCRIPTION'];
	$DAYS  			= $res->fields['DAYS'];
	$JAN  			= $res->fields['JAN'];
	$FEB  			= $res->fields['FEB'];
	$MAR  			= $res->fields['MAR'];
	$APR  			= $res->fields['APR'];
	$MAY  			= $res->fields['MAY'];
	$JUN  			= $res->fields['JUN'];
	$JUL  			= $res->fields['JUL'];
	$AUG  			= $res->fields['AUG'];
	$SEP  			= $res->fields['SEP'];
	$OCT  			= $res->fields['OCT'];
	$NOV  			= $res->fields['NOV'];
	$DECEMBER  		= $res->fields['DECEMBER'];
	$ACTIVE  		= $res->fields['ACTIVE'];
	
	if($BEGIN_DATE == '0000-00-00')
		$BEGIN_DATE = '';
	else
		$BEGIN_DATE = date("m/d/Y",strtotime($BEGIN_DATE));
		
	if($END_DATE == '0000-00-00')
		$END_DATE = '';
	else
		$END_DATE = date("m/d/Y",strtotime($END_DATE));
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
	<title><?=TERM_BLOCK_PAGE_TITLE?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-12 align-self-center">
                        <h4 class="text-themecolor"><? if($_GET['id'] == '') echo ADD; else echo EDIT; ?> <?=TERM_BLOCK_PAGE_TITLE?> </h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <form class="floating-labels m-t-40" method="post" name="form1" id="form1" >
									<div class="row">
                                        <div class="col-md-3">
											<div class="form-group m-b-40">
												<input type="text" class="form-control required-entry date1" id="BEGIN_DATE" name="BEGIN_DATE" value="<?=$BEGIN_DATE?>" onchange="calc_month_days()"  >
												<span class="bar"></span>
												<label for="BEGIN_DATE"><?=BEGIN_DATE?></label>
											</div>
										</div>
                                   
                                        <div class="col-md-3">
											<div class="form-group m-b-40">
												<input type="text" class="form-control required-entry date2" id="END_DATE" name="END_DATE" value="<?=$END_DATE?>" onchange="calc_month_days()"  >
												<span class="bar"></span>
												<label for="END_DATE"><?=END_DATE?></label>
											</div>
										</div>
                                   
                                        <div class="col-md-3">
											<div class="form-group m-b-40">
												<input type="text" class="form-control required-entry" id="DESCRIPTION" name="DESCRIPTION" maxlength="50" value="<?=$DESCRIPTION?>" >
												<span class="bar"></span>
												<label for="DESCRIPTION"><?=DESCRIPTION?></label>
											</div>
										</div>
                                    </div>
									
									<div class="row">
                                        <div class="col-md-2" id="JAN_DIV" >
											<div class="form-group m-b-40">
												<input type="text" class="form-control" id="JAN" name="JAN" value="<?=$JAN?>" onkeypress="return check_number_validation(event);" onchange="calc_day();check_number_val(this);" >
												<span class="bar"></span>
												<label for="JAN"><?=JAN?></label>
											</div>
										</div>
                                   
                                        <div class="col-md-2" id="FEB_DIV">
											<div class="form-group m-b-40" >
												<input type="text" class="form-control" id="FEB" name="FEB" value="<?=$FEB?>" onkeypress="return check_number_validation(event);" onchange="calc_day();check_number_val(this);" >
												<span class="bar"></span>
												<label for="FEB"><?=FEB?></label>
											</div>
										</div>
                                  
                                        <div class="col-md-2" id="MAR_DIV">
											<div class="form-group m-b-40" >
												<input type="text" class="form-control" id="MAR" name="MAR" value="<?=$MAR?>" onkeypress="return check_number_validation(event);" onchange="calc_day();check_number_val(this);" >
												<span class="bar"></span>
												<label for="MAR"><?=MAR?></label>
											</div>
										</div>
                                  
                                        <div class="col-md-2" id="APR_DIV">
											<div class="form-group m-b-40" >
												<input type="text" class="form-control" id="APR" name="APR" value="<?=$APR?>" onkeypress="return check_number_validation(event);" onchange="calc_day();check_number_val(this);" >
												<span class="bar"></span>
												<label for="APR"><?=APR?></label>
											</div>
										</div>
                                   
                                        <div class="col-md-2" id="MAY_DIV">
											<div class="form-group m-b-40" >
												<input type="text" class="form-control" id="MAY" name="MAY" value="<?=$MAY?>" onkeypress="return check_number_validation(event);" onchange="calc_day();check_number_val(this);" >
												<span class="bar"></span>
												<label for="MAY"><?=MAY?></label>
											</div>
										</div>
                                   
                                        <div class="col-md-2" id="JUN_DIV">
											<div class="form-group m-b-40" >
												<input type="text" class="form-control" id="JUN" name="JUN" value="<?=$JUN?>" onkeypress="return check_number_validation(event);" onchange="calc_day();check_number_val(this);" >
												<span class="bar"></span>
												<label for="JUN"><?=JUN?></label>
											</div>
										</div>
                                    </div>
									
									<div class="row">
                                        <div class="col-md-2" id="JUL_DIV">
											<div class="form-group m-b-40" >
												<input type="text" class="form-control" id="JUL" name="JUL" value="<?=$JUL?>" onkeypress="return check_number_validation(event);" onchange="calc_day();check_number_val(this);" >
												<span class="bar"></span>
												<label for="JUL"><?=JUL?></label>
											</div>
										</div>
                                    
                                        <div class="col-md-2" id="AUG_DIV" >
											<div class="form-group m-b-40" >
												<input type="text" class="form-control" id="AUG" name="AUG" value="<?=$AUG?>" onkeypress="return check_number_validation(event);" onchange="calc_day();check_number_val(this);" >
												<span class="bar"></span>
												<label for="AUG"><?=AUG?></label>
											</div>
										</div>
                                    
                                        <div class="col-md-2" id="SEP_DIV">
											<div class="form-group m-b-40" >
												<input type="text" class="form-control" id="SEP" name="SEP" value="<?=$SEP?>" onkeypress="return check_number_validation(event);" onchange="calc_day();check_number_val(this);" >
												<span class="bar"></span>
												<label for="SEP"><?=SEP?></label>
											</div>
										</div>
                                  
                                        <div class="col-md-2" id="OCT_DIV" >
											<div class="form-group m-b-40" >
												<input type="text" class="form-control" id="OCT" name="OCT" value="<?=$OCT?>" onkeypress="return check_number_validation(event);" onchange="calc_day();check_number_val(this);" >
												<span class="bar"></span>
												<label for="OCT"><?=OCT?></label>
											</div>
										</div>
                                   
                                        <div class="col-md-2" id="NOV_DIV">
											<div class="form-group m-b-40" >
												<input type="text" class="form-control" id="NOV" name="NOV" value="<?=$NOV?>" onkeypress="return check_number_validation(event);" onchange="calc_day();check_number_val(this);" >
												<span class="bar"></span>
												<label for="NOV"><?=NOV?></label>
											</div>
										</div>
                                    
                                        <div class="col-md-2" id="DECEMBER_DIV">
											<div class="form-group m-b-40" >
												<input type="text" class="form-control" id="DECEMBER" name="DECEMBER" value="<?=$DECEMBER?>" onkeypress="return check_number_validation(event);" onchange="calc_day();check_number_val(this);" >
												<span class="bar"></span>
												<label for="DEC"><?=DEC?></label>
											</div>
										</div>
                                    </div>
									<?
										$readonly = '';
										if($_GET['id'] != '')
										{
											$readonly = 'readonly';
										}
									?>
									<div class="row">
                                        <div class="col-md-2">
											<div class="form-group m-b-40" id="DAYS_LABEL">
												<input type="text" onkeypress="return check_number_validation(event);" class="form-control" id="DAYS" name="DAYS" value="<?=$DAYS?>" <?=$readonly?> >
												<span class="bar"></span>
												<label for="DAYS"  ><?=EARNINGS_DAYS?></label>
											</div>
										</div>
                                    </div>
								
									<? if($_GET['id'] != ''){ ?>
									<div class="row">
										<div class="col-md-6">
											<div class="row form-group">
												<div class="custom-control col-md-2"><?=ACTIVE?></div>
												<div class="custom-control custom-radio col-md-1">
													<input type="radio" id="customRadio11" name="ACTIVE" value="1" <? if($ACTIVE == 1) echo "checked"; ?> class="custom-control-input">
													<label class="custom-control-label" for="customRadio11"><?=YES?></label>
												</div>
												<div class="custom-control custom-radio col-md-1">
													<input type="radio" id="customRadio22" name="ACTIVE" value="0" <? if($ACTIVE == 0) echo "checked"; ?>  class="custom-control-input">
													<label class="custom-control-label" for="customRadio22"><?=NO?></label>
												</div>
											</div>
										</div>
									</div>
									<? } ?>
									
									<div class="row">
                                        <div class="col-md-6">
											<div class="form-group m-b-5"  style="text-align:right" >
												<button type="submit" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
												
												<button type="button" class="btn waves-effect waves-light btn-dark" onclick="window.location.href='manage_term_block'" ><?=CANCEL?></button>
												
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
	<script src="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.js"></script>
	<link href="../backend_assets/node_modules/bootstrap-datepicker/bootstrap-datepicker.min.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript">
		jQuery(document).ready(function($) { 

			// DIAM - 21
			<? if($_GET['id'] != ''){ ?>
				calc_month_days();
			<? } ?>
			// End DIAM - 21

			jQuery('.date').datepicker({
				todayHighlight: true,
				orientation: "bottom auto"
			});
			
			jQuery('.date1').datepicker({
				todayHighlight: true,
				orientation: "bottom auto",
				autoclose: true,
			});
			
			$('.date1').datepicker().on('hide', function(e) {
				if(document.getElementById('BEGIN_DATE').value != '') {
					var minDate = $("#BEGIN_DATE").val();
					$('#END_DATE').datepicker('setStartDate', minDate);
					document.getElementById('END_DATE').focus();
					$("#BEGIN_DATE").parent().addClass("focused")
				} else
					$("#BEGIN_DATE").parent().removeClass("focused")
			});
			
			jQuery('.date2').datepicker({
				todayHighlight: true,
				orientation: "bottom auto"
			});
			
			<? if($BEGIN_DATE != ''){ ?>
				var minDate = $("#BEGIN_DATE").val();
				$('#END_DATE').datepicker('setStartDate', minDate);
			<? } ?>
			
			dateRange()
		});
	</script>
	
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	<script type="text/javascript">
		var form1 = new Validation('form1');

		function check_number_val(e)
		{
			const regex  = /[^\d]|\.(?=.*\.)/g;
			const subst  = '';
			const str    = e.value;
			const result = str.replace(regex, subst);
			e.value      = result;
		}

		function check_number_validation(e)
		{
			const pattern = /^[0-9]$/;
            return pattern.test(e.key);
		}
		
		function calc_day(){
			var DAYS = 0;
			if(document.getElementById('JAN').value != '')
				DAYS += parseInt(document.getElementById('JAN').value);
				
			if(document.getElementById('FEB').value != '')
				DAYS += parseInt(document.getElementById('FEB').value);
				
			if(document.getElementById('MAR').value != '')
				DAYS += parseInt(document.getElementById('MAR').value);
				
			if(document.getElementById('APR').value != '')
				DAYS += parseInt(document.getElementById('APR').value);
				
			if(document.getElementById('MAY').value != '')
				DAYS += parseInt(document.getElementById('MAY').value);
				
			if(document.getElementById('JUN').value != '')
				DAYS += parseInt(document.getElementById('JUN').value);
				
			if(document.getElementById('JUL').value != '')
				DAYS += parseInt(document.getElementById('JUL').value);
				
			if(document.getElementById('AUG').value != '')
				DAYS += parseInt(document.getElementById('AUG').value);
				
			if(document.getElementById('SEP').value != '')
				DAYS += parseInt(document.getElementById('SEP').value);
				
			if(document.getElementById('OCT').value != '')
				DAYS += parseInt(document.getElementById('OCT').value);
				
			if(document.getElementById('NOV').value != '')
				DAYS += parseInt(document.getElementById('NOV').value);
				
			if(document.getElementById('DECEMBER').value != '')
				DAYS += parseInt(document.getElementById('DECEMBER').value);
				
			document.getElementById('DAYS').value = DAYS
			document.getElementById('DAYS_LABEL').classList.add("focused");
		}
		
		function calc_month_days(){
			if(document.getElementById('BEGIN_DATE').value != '' && document.getElementById('END_DATE').value != '') {
				jQuery(document).ready(function($) { 

					var data  = 'BEGIN_DATE='+document.getElementById('BEGIN_DATE').value+'&END_DATE='+document.getElementById('END_DATE').value
					var value = $.ajax({
						url: "ajax_month_days",	
						type: "POST",		 
						data: data,		
						async: false,
						cache: false,
						success: function (data) {	
							//alert(data)
							data = data.split('|||');
							
							document.getElementById('JAN').value = data[0];
							$("#JAN").parent().addClass('focused');
							
							document.getElementById('FEB').value = data[1];
							$("#FEB").parent().addClass('focused');
							
							document.getElementById('MAR').value = data[2];
							$("#MAR").parent().addClass('focused');
							
							document.getElementById('APR').value = data[3];
							$("#APR").parent().addClass('focused');
							
							document.getElementById('MAY').value = data[4];
							$("#MAY").parent().addClass('focused');
							
							document.getElementById('JUN').value = data[5];
							$("#JUN").parent().addClass('focused');
							
							document.getElementById('JUL').value = data[6];
							$("#JUL").parent().addClass('focused');
							
							document.getElementById('AUG').value = data[7];
							$("#AUG").parent().addClass('focused');
							
							document.getElementById('SEP').value = data[8];
							$("#SEP").parent().addClass('focused');
							
							document.getElementById('OCT').value = data[9];
							$("#OCT").parent().addClass('focused');
							
							document.getElementById('NOV').value = data[10];
							$("#NOV").parent().addClass('focused');
							
							document.getElementById('DECEMBER').value = data[11];
							$("#DECEMBER").parent().addClass('focused');
							
							calc_day()
							dateRange()
						}		
					}).responseText;
				});
			}
		}
		
		function dateRange() {
			var startDate 	= document.getElementById('BEGIN_DATE').value
			var endDate 	= document.getElementById('END_DATE').value
			
			document.getElementById('JAN_DIV').style.display 		= "none"
			document.getElementById('FEB_DIV').style.display 		= "none"
			document.getElementById('MAR_DIV').style.display 		= "none"
			document.getElementById('APR_DIV').style.display 		= "none"
			document.getElementById('MAY_DIV').style.display 		= "none"
			document.getElementById('JUN_DIV').style.display 		= "none"
			document.getElementById('JUL_DIV').style.display 		= "none"
			document.getElementById('AUG_DIV').style.display 		= "none"
			document.getElementById('SEP_DIV').style.display 		= "none"
			document.getElementById('OCT_DIV').style.display 		= "none"
			document.getElementById('NOV_DIV').style.display 		= "none"
			document.getElementById('DECEMBER_DIV').style.display 	= "none"
			
			if(startDate != '' && endDate != '') {
				var start      = startDate.split('/');
				var end        = endDate.split('/');
				var startYear  = parseInt(start[2]);
				var endYear    = parseInt(end[2]);
				var dates      = [];
				
				for(var i = startYear; i <= endYear; i++) {
					var endMonth = i != endYear ? 11 : parseInt(end[0]) - 1;
					var startMon = i === startYear ? parseInt(start[0])-1 : 0;
					for(var j = startMon; j <= endMonth; j = j > 12 ? j % 12 || 11 : j+1) {
						var month = j+1;
						var displayMonth = month < 10 ? '0'+month : month;
						//dates.push([i, displayMonth, '01'].join('-'));
						
						if(displayMonth == 1)
							document.getElementById('JAN_DIV').style.display = "block"
						else if(displayMonth == 2)
							document.getElementById('FEB_DIV').style.display = "block"
						else if(displayMonth == 3)
							document.getElementById('MAR_DIV').style.display = "block"
						else if(displayMonth == 4)
							document.getElementById('APR_DIV').style.display = "block"
						else if(displayMonth == 5)
							document.getElementById('MAY_DIV').style.display = "block"
						else if(displayMonth == 6)
							document.getElementById('JUN_DIV').style.display = "block"
						else if(displayMonth == 7)
							document.getElementById('JUL_DIV').style.display = "block"
						else if(displayMonth == 8)
							document.getElementById('AUG_DIV').style.display = "block"
						else if(displayMonth == 9)
							document.getElementById('SEP_DIV').style.display = "block"
						else if(displayMonth == 10)
							document.getElementById('OCT_DIV').style.display = "block"
						else if(displayMonth == 11)
							document.getElementById('NOV_DIV').style.display = "block"
						else if(displayMonth == 12)
							document.getElementById('DECEMBER_DIV').style.display = "block"
					}
				}
				//alert(dates)
			}
		}
	</script>

</body>

</html>