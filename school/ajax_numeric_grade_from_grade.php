<? require_once("../global/config.php"); 

$PK_GRADE = $_REQUEST['val'];

$res = $db->Execute("SELECT NUMBER_GRADE FROM S_GRADE WHERE PK_GRADE = '$PK_GRADE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 

$res_json = array();
$res_json['NUMBER_GRADE']  	= $res->fields['NUMBER_GRADE'];

$res_json1 = json_encode($res_json);
echo $res_json1;