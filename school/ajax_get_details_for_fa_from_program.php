<? require_once("../global/config.php"); 

require_once("function_get_details_for_fa_from_program.php");
$data123 = get_details_for_fa_from_program($_REQUEST['FA_PK_ENROLLMENT']);
echo $data123['PROGRAM_LENGTH']."|||".$data123['PROGRAM_COST'];