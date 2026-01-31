<? require_once("../global/config.php"); 

$PK_COURSE = $_REQUEST['val'];

$res = $db->Execute("SELECT * FROM S_COURSE WHERE PK_COURSE = '$PK_COURSE' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 

$res_json = array();
$res_json['COURSE_DESCRIPTION']  	= $res->fields['COURSE_DESCRIPTION'];
$res_json['HOURS']  				= $res->fields['HOURS'];
$res_json['PREP_HOURS']  			= $res->fields['PREP_HOURS'];
$res_json['FA_UNITS']  				= $res->fields['FA_UNITS'];
$res_json['UNITS']  				= $res->fields['UNITS'];

$res_json1 = json_encode($res_json);
echo $res_json1;