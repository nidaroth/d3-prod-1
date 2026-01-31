<? require_once("../global/config.php"); 

	if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == ''){ 
		header("location:../index");
		exit;
	} 

	 $PK_PREREQUISITE  	= explode(',',$_REQUEST['pk_prerequisite']);

	
   /* Ticket #1697  */
	$res_type = $db->Execute("SELECT PK_COURSE, CONCAT(COURSE_CODE,' - ',TRANSCRIPT_CODE,' - ',COURSE_DESCRIPTION) as TRANSCRIPT_CODE, ACTIVE from S_COURSE WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ORDER BY ACTIVE DESC, TRANSCRIPT_CODE ASC"); 
	while (!$res_type->EOF) { 
		$selected = "";
		foreach($PK_PREREQUISITE as $PK_PREREQUISITE1) {
			if($PK_PREREQUISITE1 == $res_type->fields['PK_COURSE']) {
				$selected = "selected";
				break;
			}
		} 
		$option_label = $res_type->fields['TRANSCRIPT_CODE'];
		if($res_type->fields['ACTIVE'] == 0)
			$option_label .= " (Inactive)"; ?>
		<option value="<?=$res_type->fields['PK_COURSE'] ?>" <?=$selected ?> <? if($res_type->fields['ACTIVE'] == 0) echo "class='option_red'"; ?> ><?=$option_label ?></option>
	<?	$res_type->MoveNext();
	} /* Ticket #1697  */ 
	?>
