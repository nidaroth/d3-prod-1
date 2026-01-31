<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/tuition_batch.php");
require_once("function_student_ledger.php");
require_once("function_update_estimate_fee_status.php");
require_once("check_access.php");

$PK_ACCOUNT="96";

$result_data = $db->Execute("SELECT PK_TUITION_BATCH_MASTER,PK_ACCOUNT FROM `S_TUITION_BATCH_MASTER` WHERE `CREATED_ON` >= '2024-02-06 00:00:59' AND `CREATED_ON` <= '2024-02-13 00:00:59' AND TYPE=1 AND PK_ACCOUNT=$PK_ACCOUNT "); // AND PK_TUITION_BATCH_MASTER=83382

while (!$result_data->EOF)
{

		$batch_id=$result_data->fields['PK_TUITION_BATCH_MASTER'];

		echo "<br><hr>";
    echo "select TUITION_BATCH_DETAIL_PK_CAMPUS_PROGRAM,PK_TUITION_BATCH_DETAIL, PK_STUDENT_FEE_BUDGET, PK_STUDENT_ENROLLMENT, PK_STUDENT_MASTER from S_TUITION_BATCH_DETAIL WHERE PK_ACCOUNT =$PK_ACCOUNT AND PK_TUITION_BATCH_MASTER =$batch_id AND TUITION_BATCH_DETAIL_PK_CAMPUS_PROGRAM!=0 ";

		$res_det = $db->Execute("select TUITION_BATCH_DETAIL_PK_CAMPUS_PROGRAM,PK_TUITION_BATCH_DETAIL, BATCH_DETAIL_DESCRIPTION from S_TUITION_BATCH_DETAIL WHERE PK_ACCOUNT =$PK_ACCOUNT AND PK_TUITION_BATCH_MASTER =$batch_id AND TUITION_BATCH_DETAIL_PK_CAMPUS_PROGRAM!=0 ");

		while (!$res_det->EOF)
        {
            if($res_det->fields['TUITION_BATCH_DETAIL_PK_CAMPUS_PROGRAM']!="" && $res_det->fields['PK_TUITION_BATCH_DETAIL']!="")
            {
                        echo "<br><hr>";

                        $str=str_replace(array('AY : 1 AP :1','AY: 1 AP: 1','AY :1 AP :1','AY :1 AP :2','AY: 1 AP: 2','AY : 1 AP :2'),'',$res_det->fields['BATCH_DETAIL_DESCRIPTION']);  
                        echo $sql_ledger="
                        SELECT DISTINCT M_CAMPUS_PROGRAM_FEE.PK_AR_LEDGER_CODE,
                        PK_CAMPUS_PROGRAM
                        FROM M_CAMPUS_PROGRAM_FEE 
                        JOIN M_AR_LEDGER_CODE 
                        ON M_CAMPUS_PROGRAM_FEE.PK_AR_LEDGER_CODE = M_AR_LEDGER_CODE.PK_AR_LEDGER_CODE  AND TYPE = 2
                        WHERE PK_CAMPUS_PROGRAM='".$res_det->fields['TUITION_BATCH_DETAIL_PK_CAMPUS_PROGRAM']."' 
                        AND M_AR_LEDGER_CODE.LEDGER_DESCRIPTION = '".trim($str)."' AND M_CAMPUS_PROGRAM_FEE.PK_ACCOUNT=".$PK_ACCOUNT;
                        $fees_lr_code=$db->Execute($sql_ledger);
                        $PK_AR_LEDGER_CODE=$fees_lr_code->fields['PK_AR_LEDGER_CODE'];
                        echo "<br>";
                        if($PK_AR_LEDGER_CODE != '')
                        {
                          echo $update="UPDATE S_TUITION_BATCH_DETAIL SET PK_AR_LEDGER_CODE='".$PK_AR_LEDGER_CODE."' WHERE PK_TUITION_BATCH_DETAIL='".$res_det->fields['PK_TUITION_BATCH_DETAIL']."' AND PK_ACCOUNT='".$PK_ACCOUNT."' ";
                          $db->Execute($update);
                          echo "<br><hr>";

                          echo $LEDGER_SQL=" UPDATE S_STUDENT_LEDGER SET PK_AR_LEDGER_CODE='".$PK_AR_LEDGER_CODE."' WHERE PK_TUITION_BATCH_DETAIL='".$res_det->fields['PK_TUITION_BATCH_DETAIL']."' AND PK_ACCOUNT='".$PK_ACCOUNT."'";
                          $db->Execute($LEDGER_SQL);
                          echo "<br><hr>";
                        }
                        
                        
            }
			    $res_det->MoveNext();
		}

    $result_data->MoveNext();
}

?>
