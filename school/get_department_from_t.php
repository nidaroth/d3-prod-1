<? function get_department_from_t($master_id){
	global $db;
	
	if($master_id == 1)
		$PK_DEPARTMENT_MASTER = 2;
	else if($master_id == 2)
		$PK_DEPARTMENT_MASTER = 7;
	else if($master_id == 3)
		$PK_DEPARTMENT_MASTER = 4;	
	else if($master_id == 5)
		$PK_DEPARTMENT_MASTER = 1;
	else if($master_id == 6)
		$PK_DEPARTMENT_MASTER = 6;
		
	$res = $db->Execute("SELECT PK_DEPARTMENT FROM M_DEPARTMENT WHERE PK_DEPARTMENT_MASTER = '$PK_DEPARTMENT_MASTER' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  ");
	return $res->fields['PK_DEPARTMENT'];
}