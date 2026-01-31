<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/tuition_batch.php");
require_once("function_student_ledger.php");
require_once("function_update_estimate_fee_status.php");
require_once("check_access.php");

		$PK_ACCOUNT="84";
		$master_sql="SELECT PK_TUITION_BATCH_MASTER FROM `S_TUITION_BATCH_MASTER` WHERE PK_ACCOUNT=$PK_ACCOUNT AND TYPE=9 AND CREATED_ON >= '2024-02-06 00:00:59' AND CREATED_ON <= '2024-02-13 00:00:59'";
		$result_query=$db->Execute($master_sql);
		
		while (!$result_query->EOF) 
		{

					$batch_id=$result_query->fields['PK_TUITION_BATCH_MASTER'];


					echo "select PK_TUITION_BATCH_DETAIL, PK_STUDENT_FEE_BUDGET, PK_STUDENT_ENROLLMENT, PK_STUDENT_MASTER from S_TUITION_BATCH_DETAIL WHERE PK_ACCOUNT =$PK_ACCOUNT AND PK_TUITION_BATCH_MASTER =$batch_id AND PK_STUDENT_FEE_BUDGET!=''";
					echo "<br><hr>";
					$res_det = $db->Execute("select PK_TUITION_BATCH_DETAIL, PK_STUDENT_FEE_BUDGET, PK_STUDENT_ENROLLMENT, PK_STUDENT_MASTER from S_TUITION_BATCH_DETAIL WHERE PK_ACCOUNT =$PK_ACCOUNT AND PK_TUITION_BATCH_MASTER =$batch_id AND PK_STUDENT_FEE_BUDGET!=''");

						while (!$res_det->EOF)
						{
							if($res_det->fields['PK_STUDENT_FEE_BUDGET']!="")
							{
								echo "<br><hr>";

								echo $sql_ledger="SELECT PK_STUDENT_FEE_BUDGET,PK_AR_LEDGER_CODE FROM S_STUDENT_FEE_BUDGET WHERE PK_STUDENT_FEE_BUDGET='".$res_det->fields['PK_STUDENT_FEE_BUDGET']."' AND PK_ACCOUNT=".$PK_ACCOUNT;
								$fees_lr_code=$db->Execute($sql_ledger);
								$PK_AR_LEDGER_CODE=$fees_lr_code->fields['PK_AR_LEDGER_CODE'];
								echo "<br>";
								echo $update="UPDATE S_TUITION_BATCH_DETAIL SET PK_AR_LEDGER_CODE='".$PK_AR_LEDGER_CODE."' WHERE PK_TUITION_BATCH_DETAIL='".$res_det->fields['PK_TUITION_BATCH_DETAIL']."' AND PK_ACCOUNT='".$PK_ACCOUNT."' AND PK_STUDENT_FEE_BUDGET=".$res_det->fields['PK_STUDENT_FEE_BUDGET'];
								$db->Execute($update);
								echo "<br><hr>";


								echo $LEDGER_SQL=" UPDATE S_STUDENT_LEDGER SET PK_AR_LEDGER_CODE='".$PK_AR_LEDGER_CODE."' WHERE PK_TUITION_BATCH_DETAIL='".$res_det->fields['PK_TUITION_BATCH_DETAIL']."' AND PK_ACCOUNT='".$PK_ACCOUNT."'";
								$db->Execute($LEDGER_SQL);
								echo "<br><hr>";
							}

						$res_det->MoveNext();
					}

		 	$result_query->MoveNext();
		}


?>
