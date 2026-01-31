<? require_once("../global/config.php"); 
require_once("../global/image_fun.php");
require_once("../language/school_profile.php"); 
require_once("../language/campus.php"); 
require_once("../language/common.php");
require_once("check_access.php");
require_once("../global/s3-client-wrapper/s3-client-wrapper.php");

if(check_access('SETUP_SCHOOL') == 0 ){
	header("location:../index");
	exit;
}

$campus_cond = "";
if($_SESSION['PK_ROLES'] == 3){
	if($_GET['id'] == '') {
		header("location:campus?id=".$_SESSION['PK_CAMPUS']);
		exit;
	}
	
	$campus_cond = " AND PK_CAMPUS = '$_SESSION[PK_CAMPUS]' ";
}

if($_GET['act'] == 'pdf_logo')	{
	$res = $db->Execute("SELECT CAMPUS_PDF_LOGO FROM S_CAMPUS WHERE PK_CAMPUS = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $campus_cond ");
	unlink($res->fields['CAMPUS_PDF_LOGO']);
	$db->Execute("UPDATE S_CAMPUS SET CAMPUS_PDF_LOGO = '' WHERE PK_CAMPUS = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $campus_cond ");
		
	header("location:campus?id=".$_GET['id']);
} else if($_GET['act'] == 'user')	{
	$db->Execute("DELETE FROM S_EMPLOYEE_CAMPUS WHERE PK_EMPLOYEE_MASTER = '$_GET[iid]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS = '$_GET[id]' $campus_cond");
		
	header("location:campus?id=".$_GET['id'].'&tab=usersTab');
}

if(!empty($_POST)){
	//echo "<pre>";print_r($_POST);exit;
	
	$SAVE_CONTINUE = $_POST['SAVE_CONTINUE'];
	$current_tab   = $_POST['current_tab'];
	unset($_POST['SAVE_CONTINUE']);
	unset($_POST['current_tab']);
	
	$CAMPUS = $_POST;
	$CAMPUS['PRIMARY_CAMPUS'] = $_POST['PRIMARY_CAMPUS'];
	
	// $file_dir_1 = '../backend_assets/school/school_'.$_SESSION['PK_ACCOUNT'].'/other/';
	$file_dir_1 = '../backend_assets/tmp_upload/';
	if(!empty($_FILES['PDF_LOGO'])){
		$name     = $_FILES['PDF_LOGO']['name'];
		$name	  = str_replace("#","_",$name);
		$name	  = str_replace("&","_",$name);
		$tmp_name = $_FILES['PDF_LOGO']['tmp_name'];
		$tmp_name = $_FILES['PDF_LOGO']['tmp_name'];
		if (trim($name)!=""){				
			$extn   = explode(".",$name);
			$iindex	= count($extn) - 1;
			$rand_string = time().'_'.rand(10000,99999);
			$name1 = str_replace(".".$extn[$iindex],"",$name);
			$file11 = 'CAMPUS_PDF_LOGO_'.$_SESSION['PK_USER'].$rand_string.".".$extn[$iindex];						
			$newfile1 = $file_dir_1.$file11;	

			if(strtolower($extn[$iindex]) != 'php' || strtolower($extn[$iindex]) != 'js' || strtolower($extn[$iindex]) != 'html'){
			
				$newfile1    = $file_dir_1.$file11;
				$image_path  = $newfile1;
						
				move_uploaded_file($tmp_name, $image_path);
				
				$size = getimagesize($file_dir_1.$file11);
				$new_w = 400;
				$new_h = 400;
				
				if($size['0'] > $new_w || $size['1'] >  $new_h) {
					thumb_gallery($file11,$file11,$new_w,$new_h,$file_dir_1,1);
				}

				// Upload file to S3 bucket
				$key_file_name = 'backend_assets/school/school_'.$_SESSION['PK_ACCOUNT'].'/other/'.$file11;
				$s3ClientWrapper = new s3ClientWrapper();
				$url = $s3ClientWrapper->uploadFile($key_file_name, $image_path);
				
				// $CAMPUS['CAMPUS_PDF_LOGO'] = $image_path ;
				$CAMPUS['CAMPUS_PDF_LOGO'] = $url;

				// delete tmp file
				unlink($image_path);
			}
		}
	}
	
	if($CAMPUS['PRIMARY_CAMPUS'] == 1) {
		$CAMPUS1['PRIMARY_CAMPUS'] = 0;
		db_perform('S_CAMPUS', $CAMPUS1, 'update'," PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");
	}
	
	if($_GET['id'] == ''){
		$CAMPUS['PK_ACCOUNT']  = $_SESSION['PK_ACCOUNT'];
		$CAMPUS['CREATED_BY']  = $_SESSION['PK_USER'];
		$CAMPUS['CREATED_ON']  = date("Y-m-d H:i");
		db_perform('S_CAMPUS', $CAMPUS, 'insert');
		
		$PK_CAMPUS = $db->insert_ID();
	} else {
		$PK_CAMPUS = $_GET['id'];
		$CAMPUS['EDITED_BY'] = $_SESSION['PK_USER'];
		$CAMPUS['EDITED_ON'] = date("Y-m-d H:i");
		db_perform('S_CAMPUS', $CAMPUS, 'update'," PK_CAMPUS = '$PK_CAMPUS' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $campus_cond ");
	}
	$_SESSION['CAMPUS_NAME'] = $_POST['CAMPUS_NAME'];
	
//echo "<pre>";print_r($CAMPUS);exit;	
	if($SAVE_CONTINUE == 0) {
		if($_SESSION['PK_ROLES'] == 3)
			header("location:index");
		else
			header("location:school_profile?id=&tab=campusTab");
	} else
		header("location:campus?id=".$PK_CAMPUS."&tab=".str_replace("#","",$current_tab));
}

if($_GET['id'] == ''){
	$OFFICIAL_CAMPUS_NAME		= '';
	$CAMPUS_NAME 				= '';
	$CAMPUS_CODE 				= '';
	$SCHOOL_CODE	 			= '';
	$INSTITUTION_CODE	 		= '';
	$FEDERAL_SCHOOL_CODE  		= '';
	$FA_SCHOOL_CODE  			= '';
	$AMBASSADOR_SCHOOL_CODE  	= '';
	$COSMO_LICENSE  			= '';
	$ADDRESS	 				= '';
	$ADDRESS_1	 				= '';
	$CITY	 					= '';
	$PK_STATES 					= '';
	$ZIP	 					= '';
	$PK_COUNTRY					= '';
	$PHONE	 					= '';
	$FAX	 					= '';
	$PRIMARY_CAMPUS	 			= '';
	$ACCSC_SCHOOL_NUMBER	 	= '';
	$ACICS_SCHOOL_NUMBER	 	= '';
	$NACCAS_SCHOOL_NUMBER	 	= '';
	$PK_REGION					= '';
	$PK_TIMEZONE				= '';
	$CAMPUS_EMAIL				= '';
	$CAMPUS_WEBSITE				= '';
	$CAMPUS_PDF_LOGO			= '';
} else {
	$res = $db->Execute("SELECT * FROM S_CAMPUS WHERE PK_CAMPUS = '$_GET[id]' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' $campus_cond "); 
	if($res->RecordCount() == 0){
		header("location:school_profile?id=&tab=campusTab");
		exit;
	}
	
	$OFFICIAL_CAMPUS_NAME 		= $res->fields['OFFICIAL_CAMPUS_NAME'];
	$CAMPUS_NAME 				= $res->fields['CAMPUS_NAME'];
	$CAMPUS_CODE 				= $res->fields['CAMPUS_CODE'];
	$SCHOOL_CODE  				= $res->fields['SCHOOL_CODE'];
	$INSTITUTION_CODE  			= $res->fields['INSTITUTION_CODE'];
	
	$FEDERAL_SCHOOL_CODE  		= $res->fields['FEDERAL_SCHOOL_CODE'];
	$FA_SCHOOL_CODE  			= $res->fields['FA_SCHOOL_CODE'];
	$AMBASSADOR_SCHOOL_CODE  	= $res->fields['AMBASSADOR_SCHOOL_CODE'];
	$COSMO_LICENSE  			= $res->fields['COSMO_LICENSE'];
	
	$ADDRESS  					= $res->fields['ADDRESS'];
	$ADDRESS_1  				= $res->fields['ADDRESS_1'];
	$CITY  						= $res->fields['CITY'];
	$PK_STATES  				= $res->fields['PK_STATES'];
	$ZIP  						= $res->fields['ZIP'];
	$PK_COUNTRY  				= $res->fields['PK_COUNTRY'];
	$PHONE  					= $res->fields['PHONE'];
	$FAX  						= $res->fields['FAX'];
	$PRIMARY_CAMPUS  			= $res->fields['PRIMARY_CAMPUS'];
	$ACCSC_SCHOOL_NUMBER  		= $res->fields['ACCSC_SCHOOL_NUMBER'];
	$ACICS_SCHOOL_NUMBER  		= $res->fields['ACICS_SCHOOL_NUMBER'];
	$NACCAS_SCHOOL_NUMBER  		= $res->fields['NACCAS_SCHOOL_NUMBER'];
	$PK_REGION					= $res->fields['PK_REGION'];
	$PK_TIMEZONE				= $res->fields['PK_TIMEZONE'];
	$ACTIVE						= $res->fields['ACTIVE'];
	$CAMPUS_EMAIL				= $res->fields['CAMPUS_EMAIL'];
	$CAMPUS_WEBSITE				= $res->fields['CAMPUS_WEBSITE'];
	$CAMPUS_PDF_LOGO			= $res->fields['CAMPUS_PDF_LOGO'];
	//echo $PRIMARY_CAMPUS;exit;
}

if($_GET['tab'] == '' || $_GET['tab'] == 'homeTab' )
	$home_tab = 'active';
else if($_GET['tab'] == 'usersTab')
	$user_tab = 'active';
else if($_GET['tab'] == 'reportingTab')
	$reporting_tab = 'active';
else
	$home_tab = 'active';
	
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
	<title><?=CAMPUS_PAGE_TITLE?> | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor"><?=CAMPUS_PAGE_TITLE?></h4>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
							<ul class="nav nav-tabs customtab" role="tablist">
                                <li class="nav-item"> <a class="nav-link <?=$home_tab?>" data-toggle="tab" href="#homeTab" role="tab"><span class="hidden-sm-up"><i class="ti-homeTab"></i></span> <span class="hidden-xs-down"><?=TAB_GENERAL?></span></a> </li>
								<? if($_GET['id'] != ''){ ?>
								<li class="nav-item"> <a class="nav-link <?=$reporting_tab?>" data-toggle="tab" href="#reportingTab" role="tab"><span class="hidden-sm-up"><i class="ti-email"></i></span> <span class="hidden-xs-down"><?=TAB_REPORTING?></span></a> </li>
                                <li class="nav-item"> <a class="nav-link <?=$user_tab?>" data-toggle="tab" href="#usersTab" role="tab"><span class="hidden-sm-up"><i class="ti-email"></i></span> <span class="hidden-xs-down"><?=TAB_USER?></span></a> </li>
								<? } ?>
                            </ul>
                            <!-- Tab panes -->
							<form class="floating-labels m-t-40" method="post" name="form1" id="form1" enctype="multipart/form-data" autocomplete="off" >
								<div class="tab-content">
									<div class="tab-pane <?=$home_tab?>" id="homeTab" role="tabpanel">
										<div class="p-20">
                                        	<div class="d-flex">
		                                    	<div class="col-12 col-sm-3 form-group">
		                                    		<input id="OFFICIAL_CAMPUS_NAME" name="OFFICIAL_CAMPUS_NAME" type="text" class="form-control" value="<?=$OFFICIAL_CAMPUS_NAME?>">
			                                        <span class="bar"></span> 
			                                        <label for="OFFICIAL_CAMPUS_NAME"><?=OFFICIAL_CAMPUS_NAME?></label>
		                                    	</div>
												
		                                    	<div class="col-12 col-sm-3 form-group">
		                                    		<input id="CAMPUS_NAME" name="CAMPUS_NAME" type="text" class="form-control" value="<?=$CAMPUS_NAME?>">
			                                        <span class="bar"></span> 
			                                        <label for="CAMPUS_NAME"><?=CAMPUS_NAME?></label>
		                                    	</div>
		                                    	<div class="col-12 col-sm-3 form-group">
		                                    		<input id="CAMPUS_CODE" name="CAMPUS_CODE" type="text" class="form-control" value="<?=$CAMPUS_CODE?>">
			                                        <span class="bar"></span> 
			                                        <label for="CAMPUS_CODE"><?=CAMPUS_CODE?></label>
		                                    	</div>
												<div class="col-12 col-sm-3 form-group">
													<div class="custom-control custom-checkbox mr-sm-2">
														<input type="checkbox" class="custom-control-input" id="PRIMARY_CAMPUS" name="PRIMARY_CAMPUS" value="1" <? if($PRIMARY_CAMPUS == 1) echo "checked"; ?> >
														<label class="custom-control-label" for="PRIMARY_CAMPUS"><?=PRIMARY_CAMPUS?>?</label>
													</div>
												</div>
												
											</div>
											
											<div class="row">
		                                    	<div class="col-sm-6 pt-25">
													<div class="d-flex">
														<div class="col-12 col-sm-12 form-group">
															<input id="ADDRESS" name="ADDRESS" type="text" class="form-control" value="<?=$ADDRESS?>">
															<span class="bar"></span>
															<label for="ADDRESS"><?=ADDRESS?></label>
														</div>
													</div>
													<div class="d-flex">
														<div class="col-12 col-sm-12 form-group">
															<input id="ADDRESS_1" name="ADDRESS_1" type="text" class="form-control" value="<?=$ADDRESS_1?>">
															<span class="bar"></span>
															<label for="ADDRESS_1"><?=ADDRESS_1?></label>
														</div>
													</div>
													
													<div class="d-flex">
														<div class="col-12 col-sm-6 form-group">
															<input id="CITY" name="CITY" type="text" class="form-control" value="<?=$CITY?>">
															<span class="bar"></span> 
															 <label for="CITY"><?=CITY?></label>
														</div>
														<div class="col-12 col-sm-6 form-group">
															<select id="PK_STATES" name="PK_STATES" class="form-control"  > <!-- onchange="get_country(this.value,'PK_COUNTRY')" -->
																<option selected></option>
																 <? $res_type = $db->Execute("select PK_STATES, STATE_NAME from Z_STATES WHERE ACTIVE = '1' ORDER BY STATE_NAME ASC ");
																while (!$res_type->EOF) { ?>
																	<option value="<?=$res_type->fields['PK_STATES'] ?>" <? if($PK_STATES == $res_type->fields['PK_STATES']) echo "selected"; ?> ><?=$res_type->fields['STATE_NAME']?></option>
																<?	$res_type->MoveNext();
																} ?>
															</select>
															<span class="bar"></span> 
															<label for="PK_STATES"><?=STATE?></label>
														</div>
													</div>
													
													<div class="d-flex">
														<div class="col-12 col-sm-6 form-group">
															<input id="ZIP" name="ZIP" type="text" class="form-control" value="<?=$ZIP?>">
															<span class="bar"></span> 
															 <label for="ZIP"><?=ZIP?></label>
														</div>	
														<div class="col-12 col-sm-6 form-group" id="PK_COUNTRY_LABEL" >
															<div id="PK_COUNTRY_DIV" >
																<select id="PK_COUNTRY" name="PK_COUNTRY" class="form-control">
																	<option selected></option>
																	   <?$res_type1 = $db->Execute("select PK_COUNTRY, NAME from Z_COUNTRY WHERE ACTIVE = '1' ORDER BY NAME ASC ");
																        while (!$res_type1->EOF) { ?>
																	    <option value="<?=$res_type1->fields['PK_COUNTRY'] ?>" <? if($PK_COUNTRY == $res_type1->fields['PK_COUNTRY']) echo "selected"; ?> ><?=$res_type1->fields['NAME']?></option>
																	    <?	$res_type1->MoveNext();
																	    }
																	    ?>
																</select>
															</div>
															<span class="bar"></span> 
															<label for="PK_COUNTRY"><?=COUNTRY?></label>
														</div>	                                        
													</div>
													
													<? /* Ticket # 1629, Ticket # 1623
													<div class="d-flex">
														<div class="col-sm-6 form-group">
															<select id="PK_TIMEZONE" name="PK_TIMEZONE" class="form-control required-entry"  >
																<option selected></option>
																<? $res_type = $db->Execute("select PK_TIMEZONE, NAME from Z_TIMEZONE WHERE ACTIVE = '1' ORDER BY NAME ASC ");
																while (!$res_type->EOF) { ?>
																	<option value="<?=$res_type->fields['PK_TIMEZONE'] ?>" <? if($PK_TIMEZONE == $res_type->fields['PK_TIMEZONE']) echo "selected"; ?> ><?=$res_type->fields['NAME']?></option>
																<?	$res_type->MoveNext();
																} ?>
															</select>
															<span class="bar"></span> 
															<label for="PK_TIMEZONE"><?=TIMEZONE?></label>
														</div>
														
														<div class="col-sm-6 form-group">
															<select id="PK_REGION" name="PK_REGION" class="form-control"  >
																<option selected></option>
																 <? $res_type = $db->Execute("select PK_REGION, REGION from M_REGION WHERE ACTIVE = '1' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ORDER BY REGION ASC ");
																while (!$res_type->EOF) { ?>
																	<option value="<?=$res_type->fields['PK_REGION'] ?>" <? if($PK_REGION == $res_type->fields['PK_REGION']) echo "selected"; ?> ><?=$res_type->fields['REGION']?></option>
																<?	$res_type->MoveNext();
																} ?>
															</select>
															<span class="bar"></span> 
															<label for="PK_REGION"><?=REGION?></label>
														</div>
													</div>
													*/ ?>
												</div>
												<div class="col-sm-6 pt-25 theme-v-border">
													<div class="d-flex">
														<div class="col-12 col-sm-6 form-group">
															<input id="PHONE" name="PHONE" type="text" class="form-control phone-inputmask" value="<?=$PHONE?>">
															<span class="bar"></span> 
															 <label for="PHONE"><?=PHONE?></label>
														</div>
														<div class="col-12 col-sm-6 form-group">
															<input id="FAX" name="FAX" type="text" class="form-control phone-inputmask" value="<?=$FAX?>">
															<span class="bar"></span> 
															 <label for="FAX"><?=FAX?></label>
														</div>
													</div>
													
													<div class="d-flex">
														<div class="col-12 col-sm-12 form-group">
															<input id="CAMPUS_EMAIL" name="CAMPUS_EMAIL" type="text" class="form-control" value="<?=$CAMPUS_EMAIL?>">
															<span class="bar"></span>
															<label for="CAMPUS_EMAIL"><?=EMAIL?></label>
														</div>
													</div>
													
													<div class="d-flex">
														<div class="col-12 col-sm-12 form-group">
															<input id="CAMPUS_WEBSITE" name="CAMPUS_WEBSITE" type="text" class="form-control" value="<?=$CAMPUS_WEBSITE?>">
															<span class="bar"></span>
															<label for="CAMPUS_WEBSITE"><?=WEBSITE?></label>
														</div>
													</div>
													
													<div class="form-group col-12">
														<? if($CAMPUS_PDF_LOGO == '') { ?>
														<label><?=PDF_LOGO?></label>
														<div class="input-group">
															<div class="input-group-prepend" style="margin-top: 5px;" >
																<span class="input-group-text"><?=PDF_LOGO?></span>
															</div>
															<div class="custom-file">
																<input type="file" name="PDF_LOGO" id="PDF_LOGO" class="custom-file-input" id="inputGroupFile01">
																<label class="custom-file-label" for="inputGroupFile01"><?=CHOOSE_FILE?></label>
															</div>
														</div>
														<? } else { ?>
														<table>
															<tr>
																<td valign="top"><?=PDF_LOGO?>&nbsp;</td>
																<td><img src="<?=$CAMPUS_PDF_LOGO?>" style="height:80px;" /></td>
																<td>
																	<a href="javascript:void(0)" onclick="delete_row('','pdf_logo')" >
																		<i class="icon-trash round_red icon_size" title="Delete"></i>
																	</a>
																</td>
															</tr>
														</table>
														<? } ?>
													</div>
													
													<? if($_GET['id'] != ''){ ?>
													<div class="d-flex">
														<div class="col-md-12">
															<div class="row form-group">
																<div class="custom-control col-md-4"><?=ACTIVE?></div>
																<div class="custom-control custom-radio col-md-2">
																	<input type="radio" id="customRadio11" name="ACTIVE" value="1" <? if($ACTIVE == 1) echo "checked"; ?> class="custom-control-input">
																	<label class="custom-control-label" for="customRadio11"><?=YES?></label>
																</div>
																<div class="custom-control custom-radio col-md-2">
																	<input type="radio" id="customRadio22" name="ACTIVE" value="0" <? if($ACTIVE == 0) echo "checked"; ?>  class="custom-control-input">
																	<label class="custom-control-label" for="customRadio22"><?=NO?></label>
																</div>
															</div>
														</div>
													</div>
													<? } ?>
												</div>
											</div>
											
		                                    <div class="row">
		                                    	<div class="col-sm-6 pt-25"></div>
												<div class="col-sm-6 pt-25 theme-v-border">
				                                    <div class="d-flex submit-button-sec">
														<input type="hidden" name="SAVE_CONTINUE" id="SAVE_CONTINUE" value="0" />
														<input type="hidden" id="current_tab" name="current_tab" value="0" >
									
														<button onclick="validate_form(1)" type="button" class="btn waves-effect waves-light btn-info"><?=SAVE_CONTINUE?></button>
														
														<button onclick="validate_form(0)" type="button" class="btn waves-effect waves-light btn-info"><?=SAVE_EXIT?></button>
														
														<? if($_SESSION['PK_ROLES'] == 3) 
															$URL = "index";
														else
															$URL = "school_profile?tab=campusTab"; ?>
														<button type="button" onclick="window.location.href='<?=$URL?>'"  class="btn waves-effect waves-light btn-dark"><?=CANCEL?></button>
													</div>
												</div>
											</div>
		                                    <div class="row">
												<div class="col-3 col-sm-3">
												</div>
		                                    </div>
										</div>
									</div>
									<? if($_GET['id'] != ''){ ?>
									<div class="tab-pane <?=$reporting_tab?>" id="reportingTab" role="tabpanel">
										<div class="row">
											<div class="col-sm-4 pt-25">
												<div class="row">
													<div class="col-md-12">
														<h4><b><?=ACCREDITATION_1?></b></h4><br />
													</div>
												</div>
												
												<div class="d-flex">
													<div class="col-12 col-sm-12 form-group">
														<input id="ACCSC_SCHOOL_NUMBER" name="ACCSC_SCHOOL_NUMBER" type="text" class="form-control" value="<?=$ACCSC_SCHOOL_NUMBER?>">
														<span class="bar"></span> 
														<label for="ACCSC_SCHOOL_NUMBER"><?=ACCSC_SCHOOL_NUMBER?></label>
													</div>
												</div>
												<div class="d-flex">
													<div class="col-12 col-sm-12 form-group">
														<input id="ACICS_SCHOOL_NUMBER" name="ACICS_SCHOOL_NUMBER" type="text" class="form-control" value="<?=$ACICS_SCHOOL_NUMBER?>">
														<span class="bar"></span> 
														<label for="ACICS_SCHOOL_NUMBER"><?=ACICS_SCHOOL_NUMBER?></label>
													</div>
												</div>
												<div class="d-flex">
													<div class="col-12 col-sm-12 form-group">
														<input id="NACCAS_SCHOOL_NUMBER" name="NACCAS_SCHOOL_NUMBER" type="text" class="form-control" value="<?=$NACCAS_SCHOOL_NUMBER?>">
														<span class="bar"></span> 
														<label for="NACCAS_SCHOOL_NUMBER"><?=NACCAS_SCHOOL_NUMBER?></label>
													</div>
												</div>
											</div>
											<div class="col-sm-4 pt-25 theme-v-border">
												<div class="row">
													<div class="col-md-12">
														<h4><b><?=CODES?></b></h4><br />
													</div>
												</div>
												
												<div class="d-flex">
													<div class="col-12 col-sm-12 form-group">
														<input id="FA_SCHOOL_CODE" name="FA_SCHOOL_CODE" type="text" class="form-control" value="<?=$FA_SCHOOL_CODE?>">
														<span class="bar"></span> 
														<label for="FA_SCHOOL_CODE"><?=FA_SCHOOL_CODE?></label>
													</div>	
												</div>
												<div class="d-flex">
													<div class="col-12 col-sm-12 form-group">
														<input id="FEDERAL_SCHOOL_CODE" name="FEDERAL_SCHOOL_CODE" type="text" class="form-control" value="<?=$FEDERAL_SCHOOL_CODE?>">
														<span class="bar"></span> 
														<label for="FEDERAL_SCHOOL_CODE"><?=FEDERAL_SCHOOL_CODE?></label>
													</div>
												</div>
												<div class="d-flex">
													<div class="col-12 col-sm-12 form-group">
														<input id="INSTITUTION_CODE" name="INSTITUTION_CODE" type="text" class="form-control" value="<?=$INSTITUTION_CODE?>">
														<span class="bar"></span> 
														<label for="INSTITUTION_CODE"><?=INSTITUTION_CODE?></label>
													</div>
												</div>
												<div class="d-flex">
													<div class="col-12 col-sm-12 form-group">
														<input id="SCHOOL_CODE" name="SCHOOL_CODE" type="text" class="form-control" value="<?=$SCHOOL_CODE?>">
														<span class="bar"></span> 
														<label for="SCHOOL_CODE"><?=SCHOOL_CODE?></label>
													</div>
												</div>
											</div>
											<div class="col-sm-4 pt-25 theme-v-border">
												<div class="row">
													<div class="col-md-12">
														<h4><b><?=MISCELLANEOUS?></b></h4><br />
													</div>
												</div>
												
												<div class="d-flex">
													<div class="col-12 col-sm-12 form-group">
														<input id="AMBASSADOR_SCHOOL_CODE" name="AMBASSADOR_SCHOOL_CODE" type="text" class="form-control" value="<?=$AMBASSADOR_SCHOOL_CODE?>">
														<span class="bar"></span> 
														<label for="AMBASSADOR_SCHOOL_CODE"><?=AMBASSADOR_SCHOOL_CODE?></label>
													</div>
												</div>
												
												<div class="d-flex">	
													<div class="col-12 col-sm-12 form-group">
														<input id="COSMO_LICENSE" name="COSMO_LICENSE" type="text" class="form-control" value="<?=$COSMO_LICENSE?>">
														<span class="bar"></span> 
														<label for="COSMO_LICENSE"><?=COSMO_LICENSE?></label>
													</div>
												</div>
												
												<div class="row">
													<div class="col-sm-12 pt-25 ">
														<div class="d-flex submit-button-sec">
															<button onclick="validate_form(1)" type="button" class="btn waves-effect waves-light btn-info"><?=SAVE_CONTINUE?></button>
															<button onclick="validate_form(0)" type="button" class="btn waves-effect waves-light btn-info"><?=SAVE_EXIT?></button>
															
															<? if($_SESSION['PK_ROLES'] == 3) 
																$URL = "index";
															else
																$URL = "school_profile?tab=campusTab"; ?>
															<button type="button" onclick="window.location.href='<?=$URL?>'"  class="btn waves-effect waves-light btn-dark"><?=CANCEL?></button>
														</div>
													</div>
												</div>
											</div>
										</div>
									</div>
									
									<div class="tab-pane <?=$user_tab?>" id="usersTab" role="tabpanel">
										<div class="row">
											<div class="col-md-10 align-self-center">
											</div>  
											<div class="col-md-2 align-self-center text-right">
												<div class="d-flex justify-content-end align-items-center">
													<a href="employee?cid=<?=$_GET['id']?>&t=1" class="btn btn-info d-none d-lg-block m-l-15"><i class="fa fa-plus-circle"></i> <?=CREATE_NEW?></a>&nbsp;&nbsp;
												</div>
											</div>
										</div>
										
										<div class="table-responsive p-20">
											<table class="table table-hover">
												<thead>
													<tr>
														<th>#</th>
														<th><?=NAME?></th>
														<th><?=ROLE?></th>
														<th><?=EMAIL?></th>
														<th><?=CELL_PHONE?></th>
														<th><?=HAS_LOGIN?></th>
														<th><?=OPTIONS?></th>
													</tr>
												</thead>
												<tbody>
												
													<? $res_type = $db->Execute("SELECT CONCAT(FIRST_NAME,' ',LAST_NAME) AS NAME ,EMAIL,CELL_PHONE, USER_ID,ROLES, S_EMPLOYEE_MASTER.ACTIVE ,S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER, IF(LOGIN_CREATED = 1,'Yes','No') as LOGIN_CREATED , IS_FACULTY FROM 
													S_EMPLOYEE_MASTER 
													LEFT JOIN Z_USER ON Z_USER.ID = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER AND PK_USER_TYPE = 2 
													LEFT JOIN Z_ROLES ON Z_ROLES.PK_ROLES = Z_USER.PK_ROLES 
													, S_EMPLOYEE_CONTACT 
													, S_EMPLOYEE_CAMPUS 
													WHERE 
													S_EMPLOYEE_MASTER.PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND 
													S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = S_EMPLOYEE_CONTACT.PK_EMPLOYEE_MASTER AND 
													S_EMPLOYEE_CAMPUS.PK_CAMPUS = '$_GET[id]' AND 
													S_EMPLOYEE_CAMPUS.PK_EMPLOYEE_MASTER = S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER AND LOGIN_CREATED = 1");
													$i = 0;
													while (!$res_type->EOF) { 
														if($res_type->fields['IS_FACULTY'] == 1)
															$t = 2;
														else
															$t = 1;
														$i++; ?>
														<tr>
															<td><?=$i?></td>
															<td><?=$res_type->fields['NAME']?></td>
															<td><?=$res_type->fields['ROLES']?></td>
															<td><?=$res_type->fields['EMAIL']?></td>
															<td><?=$res_type->fields['CELL_PHONE']?></td>
															<td><?=$res_type->fields['LOGIN_CREATED']?></td>
															<td>
																<a href="employee?id=<?=$res_type->fields['PK_EMPLOYEE_MASTER']?>&t=<?=$t?>" target="_blank" title="<?=EDIT?>" class="btn edit-color btn-circle"><i class="far fa-edit"></i> </a>
																<a href="javascript:void(0);" onclick="delete_row('<?=$res_type->fields['PK_EMPLOYEE_MASTER']?>','user')" title="<?=DELETE?>" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i> </a>
															</td>
														</tr>
													<?	$res_type->MoveNext();
													} ?>
												</tbody>
											</table>
										</div>
									</div>
									<? } ?>
								</div>
							</form>
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
						<div class="form-group" id="delete_message" ></div>
						<input type="hidden" id="DELETE_ID" value="0" />
						<input type="hidden" id="DELETE_TYPE" value="0" />
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
	
	<script type="text/javascript">
	<? if($_GET['tab'] != '') { ?>
		current_tab = '<?=$_GET['tab']?>';
	<? } else { ?>
		current_tab = 'homeTab';
	<? } ?>
	jQuery(document).ready(function($) {
		$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
			current_tab = $(e.target).attr("href") // activated tab
			//alert(current_tab)
		});
		
		<? if($_GET['id'] != ''){ ?>
			//get_country(<?=$PK_STATES?>,'PK_COUNTRY')
		<? } ?>
	});
	</script>
	
	<script src="../backend_assets/dist/js/validation_prototype.js"></script>
	<script src="../backend_assets/dist/js/validation.js"></script>
	
	<script type="text/javascript">
		
		function validate_form(val){
			document.getElementById('current_tab').value   = current_tab;
			document.getElementById("SAVE_CONTINUE").value = val;
			
			var valid = new Validation('form1', {onSubmit:false});
			var result = valid.validate();
			if(result == true)
				document.form1.submit();
		}
		
		function get_country(val,id){
			jQuery(document).ready(function($) { 
				var data  = 'state='+val+'&id='+id;
				var value = $.ajax({
					url: "../super_admin/ajax_get_country_from_state",	
					type: "POST",		 
					data: data,		
					async: false,
					cache: false,
					success: function (data) {	
						//alert(data)
						document.getElementById(id+'_LABEL').classList.add("focused");
						document.getElementById(id).innerHTML = data;
					}		
				}).responseText;
			});
		}
		
		function delete_row(id,type){
			jQuery(document).ready(function($) {
				if(type == 'logo')
					document.getElementById('delete_message').innerHTML = '<?=DELETE_MESSAGE.LOGO?>?';
				else if(type == 'user')
					document.getElementById('delete_message').innerHTML = '<?=DELETE_MESSAGE_USER?>';	
				else if(type == 'pdf_logo')
					document.getElementById('delete_message').innerHTML = '<?=DELETE_MESSAGE.PDF_LOGO ?>?';		
					
				$("#deleteModal").modal()
				$("#DELETE_ID").val(id)
				$("#DELETE_TYPE").val(type)
			});
		}
		function conf_delete(val,id){
			jQuery(document).ready(function($) {
				if(val == 1) {
					if($("#DELETE_TYPE").val() == 'logo')
						window.location.href = 'campus?act=logo&id=<?=$_GET['id']?>';
					else if($("#DELETE_TYPE").val() == 'user')
						window.location.href = 'campus?act='+$("#DELETE_TYPE").val()+'&id=<?=$_GET['id']?>&iid='+$("#DELETE_ID").val();
					else if($("#DELETE_TYPE").val() == 'pdf_logo')
						window.location.href = 'campus?id=<?=$_GET['id']?>&act=pdf_logo';
				} else
					$("#deleteModal").modal("hide");
			});
		}
	</script>

</body>

</html>