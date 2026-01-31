<? require_once("../global/config.php");
require_once("../language/common.php");
require_once("../language/help.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == '' ){ 
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
	<title><?=HELP_PAGE_TITLE?> | <?=$title?></title>
	<style>
	.accordion {
		background-color: #022561;
		color: #FFF;
		cursor: pointer;
		padding: 5px;
		width: 100%;
		border: none;
		text-align: left;
		outline: none;
		font-size: 15px;
		transition: 0.4s;
	}

	.acc_active, .accordion:hover {
		background-color: #022561;
	}

	.accordion:after {
		content: '\002B';
		color: #FFF;
		font-weight: bold;
		float: right;
		margin-left: 5px;
		font-size: 20px;
	}

	.acc_active:after {
		content: "\2212";
		font-size: 20px;
	}

	.panel {
		padding: 0 18px;
		background-color: white;
		max-height: 0;
		overflow: hidden;
		transition: max-height 0.2s ease-out;
		margin-bottom: 1px;
		
	}
</style>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor"><?=HELP_PAGE_TITLE?></h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
								<div class="row" >
									<div class="col-md-12">
										<form name="form1" id="form1" method="get" >
											<input class="form-control" type="text" id="SEARCH" name="SEARCH" placeholder="&#xF002; <?=SEARCH?>" style="font-family:Helvetica Neue, FontAwesome;margin-bottom: 10px;" onkeypress = "search(event)" value="<?=$_GET['SEARCH']?>" />
										</form>
									</div>
								</div>
								
								<div class="row" >
									<div class="col-md-12">
										<? $cond = '';
										if($_SESSION['PK_LANGUAGE'] == 1){
											$NAME_FIELD 	= "NAME_ENG";
											$CONTENT_FIELD  = "CONTENT_ENG";
										} else if($_SESSION['PK_LANGUAGE'] == 2){
											$NAME_FIELD 	= "NAME_SPA";
											$CONTENT_FIELD  = "CONTENT_SPA";
										} 
										if($_GET['SEARCH'] != '')
											$cond = " AND ($NAME_FIELD LIKE '%$_GET[SEARCH]%' OR $CONTENT_FIELD LIKE '%$_GET[SEARCH]%') ";
											
										$res_type = $db->Execute("select * from Z_HELP WHERE ACTIVE = 1 $cond ORDER BY $NAME_FIELD ASC");
										while (!$res_type->EOF) { 
											$PK_HELP = $res_type->fields['PK_HELP']; ?>
											<button class="accordion"><?=$res_type->fields[$NAME_FIELD]?></button>
											<div class="panel">
												<p><?=nl2br($res_type->fields[$CONTENT_FIELD])?></p>
												<? $res_att = $db->Execute("select PK_HELP_FILES,FILE_NAME,FILE_LOCATION from Z_HELP_FILES WHERE ACTIVE = 1 AND PK_HELP = '$PK_HELP' ");
												while (!$res_att->EOF) { ?>
													<br /><a href="<?=$res_att->fields['FILE_LOCATION']?>" target="_blank" ><?=$res_att->fields['FILE_NAME']?></a>
												<?	$res_att->MoveNext();
												} ?>
											</div>
										<?	$res_type->MoveNext();
										} ?>
										<br />
									</div>
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
	<script type="text/javascript">
	var acc = document.getElementsByClassName("accordion");
	var i;

	for (i = 0; i < acc.length; i++) {
		acc[i].addEventListener("click", function() {
			this.classList.toggle("acc_active");
			var panel = this.nextElementSibling;
			if (panel.style.maxHeight){
				panel.style.maxHeight = null;
			} else {
				panel.style.maxHeight = panel.scrollHeight + "px";
			} 
		});
	}
	function search(e){
		if (e.keyCode == 13) {
			document.form1.submit()
		}
	}
	</script>

</body>

</html>