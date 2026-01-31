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
                    <div class="col-md-9 align-self-center">
                        <h4 class="text-themecolor"><?=HELP_PAGE_TITLE?></h4>
                    </div>
					<div class="col-md-3 align-self-center">
						<form name="form1" id="form1" method="get" >
							<input type="text" class="form-control" id="SEARCH" name="SEARCH" placeholder="&#xF002; <?=SEARCH?>"  value="<?=$_GET['SEARCH']?>" style="font-family: FontAwesome;margin-top: -11px" onkeypress="search(event)">
						</form>
					</div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
						
								<div class="row" >
									<? if($_SESSION['PK_LANGUAGE'] == 1){
										$NAME_FIELD 	= "NAME_ENG";
										$CONTENT_FIELD  = "CONTENT_ENG";
									} else if($_SESSION['PK_LANGUAGE'] == 2){
										$NAME_FIELD 	= "NAME_SPA";
										$CONTENT_FIELD  = "CONTENT_SPA";
									} 
									if($_GET['id'] == ''){ ?>
									<div class="col-md-3">
										<? if($_GET['SEARCH'] == '')
											$res_cat = $db->Execute("select * from M_HELP_CATEGORY WHERE ACTIVE = 1 ORDER BY DISPLAY_ORDER ASC");
										else
											$res_cat = $db->Execute("SELECT * FROM (
											select M_HELP_CATEGORY.* from M_HELP_CATEGORY, Z_HELP  WHERE Z_HELP.ACTIVE = 1 AND M_HELP_CATEGORY.ACTIVE = 1 AND M_HELP_CATEGORY.PK_HELP_CATEGORY = Z_HELP.PK_HELP_CATEGORY AND (HELP_CATEGORY LIKE '%$_GET[SEARCH]%' OR $NAME_FIELD LIKE '%$_GET[SEARCH]%' OR $CONTENT_FIELD LIKE '%$_GET[SEARCH]%' ) 
											UNION
											select M_HELP_CATEGORY.* from M_HELP_CATEGORY,M_HELP_SUB_CATEGORY, Z_HELP  WHERE Z_HELP.ACTIVE = 1 AND M_HELP_CATEGORY.ACTIVE = 1 AND M_HELP_CATEGORY.PK_HELP_CATEGORY = M_HELP_SUB_CATEGORY.PK_HELP_CATEGORY AND M_HELP_SUB_CATEGORY.PK_HELP_SUB_CATEGORY = Z_HELP.PK_HELP_SUB_CATEGORY AND (HELP_CATEGORY LIKE '%$_GET[SEARCH]%' OR $NAME_FIELD LIKE '%$_GET[SEARCH]%' OR $CONTENT_FIELD LIKE '%$_GET[SEARCH]%' OR HELP_SUB_CATEGORY LIKE '%$_GET[SEARCH]%') 
											) AS TEMP WHERE ACTIVE = 1 GROUP BY PK_HELP_CATEGORY ORDER BY DISPLAY_ORDER ASC ");
											
										while (!$res_cat->EOF) { 
											$PK_HELP_CATEGORY = $res_cat->fields['PK_HELP_CATEGORY']; ?>
											<div class="row" >
												<div class="col-md-12">
													<a href="javascript:void(0)" onclick="show_sub_category(<?=$PK_HELP_CATEGORY?>)" ><i id="cat_<?=$PK_HELP_CATEGORY?>" class="fas fa-folder"></i> <?=$res_cat->fields['HELP_CATEGORY']?></a>
												</div>
											</div>	
											
											<div style="display:none" id="sub_cat_<?=$PK_HELP_CATEGORY?>">
												<div class="row" style="width: 100%;" >
													<div class="col-md-1">&nbsp;</div>
													<div class="col-md-11">
														<? if($_GET['SEARCH'] == '')
															$res_sub_cat = $db->Execute("select * from M_HELP_SUB_CATEGORY WHERE ACTIVE = 1 AND PK_HELP_CATEGORY = '$PK_HELP_CATEGORY' ORDER BY DISPLAY_ORDER ASC");
														else
															$res_sub_cat = $db->Execute("SELECT * FROM (
															select * from M_HELP_SUB_CATEGORY WHERE ACTIVE = 1 AND PK_HELP_CATEGORY = '$PK_HELP_CATEGORY' AND HELP_SUB_CATEGORY LIKE '%$_GET[SEARCH]%'
															UNION
															select M_HELP_SUB_CATEGORY.* from M_HELP_SUB_CATEGORY,Z_HELP  WHERE Z_HELP.ACTIVE = 1 AND M_HELP_SUB_CATEGORY.ACTIVE = 1 AND M_HELP_SUB_CATEGORY.PK_HELP_CATEGORY = '$PK_HELP_CATEGORY' AND M_HELP_SUB_CATEGORY.PK_HELP_SUB_CATEGORY = Z_HELP.PK_HELP_SUB_CATEGORY AND (HELP_SUB_CATEGORY LIKE '%$_GET[SEARCH]%' OR $NAME_FIELD LIKE '%$_GET[SEARCH]%' OR $CONTENT_FIELD LIKE '%$_GET[SEARCH]%') 
															) AS TEMP WHERE ACTIVE = 1 GROUP BY PK_HELP_SUB_CATEGORY ORDER BY DISPLAY_ORDER ASC ");
														
														while (!$res_sub_cat->EOF) { 
															$PK_HELP_SUB_CATEGORY = $res_sub_cat->fields['PK_HELP_SUB_CATEGORY'];?>
															<div class="col-md-12">
																<a href="javascript:void(0)" onclick="show_help(<?=$PK_HELP_SUB_CATEGORY?>)" ><i class="fas fa-folder" id="inner_folder_<?=$PK_HELP_SUB_CATEGORY?>" ></i> <?=$res_sub_cat->fields['HELP_SUB_CATEGORY']?></a>
															</div>
															
															<div style="display:none" id="sub_cat_help_<?=$PK_HELP_SUB_CATEGORY?>">
																<div class="row" style="width: 100%;" >
																	<div class="col-md-2">&nbsp;</div>
																	<div class="col-md-9">
																		<? if($_GET['SEARCH'] == '')
																			$res_sub_help = $db->Execute("select PK_HELP,$NAME_FIELD from Z_HELP WHERE ACTIVE = 1 AND PK_HELP_SUB_CATEGORY = '$PK_HELP_SUB_CATEGORY' ORDER BY DISPLAY_ORDER ASC ");
																		else 
																			$res_sub_help = $db->Execute("select PK_HELP,$NAME_FIELD from Z_HELP WHERE ACTIVE = 1 AND PK_HELP_SUB_CATEGORY = '$PK_HELP_SUB_CATEGORY'  AND ($NAME_FIELD LIKE '%$_GET[SEARCH]%' OR $CONTENT_FIELD LIKE '%$_GET[SEARCH]%') ORDER BY DISPLAY_ORDER ASC ");
																		while (!$res_sub_help->EOF) { 
																			$PK_HELP = $res_sub_help->fields['PK_HELP']; ?>
																			<div class="col-md-12">
																				<a href="javascript:void(0)" onclick="show_help_content(<?=$PK_HELP?>)" id="link_<?=$PK_HELP?>" class="link1" ><i class="fas fa-file"></i> <?=$res_sub_help->fields[$NAME_FIELD]?></a>
																			</div>
																		<?	$res_sub_help->MoveNext();
																		} ?>
																	</div>
																</div>
															</div>
												
														<?	$res_sub_cat->MoveNext();
														} ?>
													</div>
												</div>
												
												<div class="row" style="width: 100%;" >
													<div class="col-md-1">&nbsp;</div>
													<div class="col-md-9">
														<? if($_GET['SEARCH'] == '')
															$res_sub_help = $db->Execute("select PK_HELP,$NAME_FIELD from Z_HELP WHERE ACTIVE = 1 AND PK_HELP_CATEGORY = '$PK_HELP_CATEGORY' AND PK_HELP_SUB_CATEGORY = 0 ORDER BY DISPLAY_ORDER ASC ");
														else 
															$res_sub_help = $db->Execute("select PK_HELP,$NAME_FIELD from Z_HELP WHERE ACTIVE = 1 AND PK_HELP_CATEGORY = '$PK_HELP_CATEGORY' AND PK_HELP_SUB_CATEGORY = 0 AND ($NAME_FIELD LIKE '%$_GET[SEARCH]%' OR $CONTENT_FIELD LIKE '%$_GET[SEARCH]%') ORDER BY DISPLAY_ORDER ASC ");
														while (!$res_sub_help->EOF) { 
															$PK_HELP = $res_sub_help->fields['PK_HELP']; ?>
															<div class="col-md-12">
																<a href="javascript:void(0)" class="link1" onclick="show_help_content(<?=$PK_HELP?>)" id="link_<?=$PK_HELP?>"><i class="fas fa-file"></i> <?=$res_sub_help->fields[$NAME_FIELD]?></a>
															</div>
														<?	$res_sub_help->MoveNext();
														} ?>
													</div>
												</div>
												
											</div>	
										<?	$res_cat->MoveNext();
										} ?>
									</div>
									<? } ?>
									<div <? if($_GET['id'] == ''){ ?> class="col-md-9" style="border-left: 1px solid #000;" <? } else { ?> class="col-md-12" <? } ?> >
										<? $cond111 = "";
										 if($_GET['id'] != '')
											$cond111 = " AND PK_HELP = '$_GET[id]' ";
										$res_sub_help = $db->Execute("select PK_HELP,$NAME_FIELD,$CONTENT_FIELD from Z_HELP WHERE ACTIVE = 1 $cond111 ");
										while (!$res_sub_help->EOF) { 
											$PK_HELP = $res_sub_help->fields['PK_HELP'];?>
											<div id="page_<?=$PK_HELP?>" class="page" <? if($_GET['id'] == ''){ ?> style="display:none" <? } ?> >
												<div class="row" style="width: 100%;" >
													<div class="col-md-12" style="text-align:right" >
														<a href="javascript:void(0)" onclick="print_help('page_<?=$PK_HELP?>')" ><i class="mdi mdi-printer-settings" style="font-size: 25px;" ></i></a>
													</div>
													<div class="col-md-12">
														<? 
														/*$PAGE_CONTENT = str_replace("<p>","",$res_sub_help->fields[$CONTENT_FIELD]); 
														$PAGE_CONTENT = str_replace("</p>","<br />",$PAGE_CONTENT); 
														$PAGE_CONTENT = str_replace("<br><br>","<br />",$PAGE_CONTENT);*/
														$PAGE_CONTENT = $res_sub_help->fields[$CONTENT_FIELD];
														//$PAGE_CONTENT = nl2br($PAGE_CONTENT);
														  ?>
														<p><?=$PAGE_CONTENT?></p>
														<? $res_att = $db->Execute("select PK_HELP_FILES,FILE_NAME,FILE_LOCATION from Z_HELP_FILES WHERE ACTIVE = 1 AND PK_HELP = '$PK_HELP' ");
														while (!$res_att->EOF) { ?>
															<br /><a href="<?=$res_att->fields['FILE_LOCATION']?>" target="_blank" ><?=$res_att->fields['FILE_NAME']?></a>
														<?	$res_att->MoveNext();
														} ?>
													</div>
												</div>
											</div>
										<?	$res_sub_help->MoveNext();
										} ?>
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
	
	function show_sub_category(id){
		if(document.getElementById('sub_cat_'+id).style.display == 'block') {
			document.getElementById('sub_cat_'+id).style.display 	= 'none';
			document.getElementById('cat_'+id).className 			= 'fas fa-folder';
		} else {
			document.getElementById('sub_cat_'+id).style.display = 'block';
			document.getElementById('cat_'+id).className 		 = 'fas fa-folder-open';
		}
	}
	
	function show_help(id){
		if(document.getElementById('sub_cat_help_'+id).style.display == 'block') {
			document.getElementById('sub_cat_help_'+id).style.display 	= 'none';
			document.getElementById('inner_folder_'+id).className 		= 'fas fa-folder';
		} else {
			document.getElementById('sub_cat_help_'+id).style.display = 'block';
			document.getElementById('inner_folder_'+id).className 	  = 'fas fa-folder-open';
		}
	}
	function show_help_content(id){
		var x = document.getElementsByClassName("page");
		for (var i = 0; i < x.length; i++) {
		  x[i].style.display = "none";
		} 
		document.getElementById('page_'+id).style.display = 'block';
		
		var x = document.getElementsByClassName("link1");
		for (var i = 0; i < x.length; i++) {
		  x[i].style.color = null;
		} 
		
		document.getElementById('link_'+id).style.color = '#000';
		
	}
	
	function search(e){
		if (e.keyCode == 13) {
			document.form1.submit();
		}
	}
	
	function print_help(id){
		var printContents = document.getElementById(id).innerHTML;
		var originalContents = document.body.innerHTML;

		document.body.innerHTML = printContents;

		window.print();

		document.body.innerHTML = originalContents;
	}
	</script>

</body>

</html>