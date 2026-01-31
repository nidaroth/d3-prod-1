<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/tuition_batch.php");
require_once("function_student_ledger.php");
require_once("function_update_estimate_fee_status.php");
require_once("check_access.php");

$PK_ACCOUNT=63;

echo $_SESSION['PK_ACCOUNT'];

echo "<br><br> <hr>";

$result_data = $db->Execute("SELECT S_PAYMENT_BATCH_DETAIL.PK_PAYMENT_BATCH_DETAIL, S_STUDENT_MASTER.PK_STUDENT_MASTER,S_STUDENT_MASTER.FIRST_NAME,S_STUDENT_MASTER.LAST_NAME,S_PAYMENT_BATCH_DETAIL.BATCH_DETAIL_DESCRIPTION,S_PAYMENT_BATCH_MASTER.CREATED_ON,S_PAYMENT_BATCH_MASTER.PK_PAYMENT_BATCH_MASTER FROM `S_PAYMENT_BATCH_MASTER` LEFT JOIN S_PAYMENT_BATCH_DETAIL ON S_PAYMENT_BATCH_MASTER.PK_PAYMENT_BATCH_MASTER = S_PAYMENT_BATCH_DETAIL.PK_PAYMENT_BATCH_MASTER LEFT JOIN S_STUDENT_MASTER ON S_STUDENT_MASTER.PK_STUDENT_MASTER = S_PAYMENT_BATCH_DETAIL.PK_STUDENT_MASTER WHERE S_PAYMENT_BATCH_MASTER.`PK_ACCOUNT` = $PK_ACCOUNT AND DATE(S_PAYMENT_BATCH_MASTER.CREATED_ON)='2024-02-15' AND S_PAYMENT_BATCH_MASTER.COMMENTS LIKE '%Automated Recurring CC Payments%' GROUP BY S_PAYMENT_BATCH_DETAIL.PK_PAYMENT_BATCH_DETAIL ORDER BY S_STUDENT_MASTER.PK_STUDENT_MASTER LIMIT 50;"); 
//AND S_PAYMENT_BATCH_MASTER.PK_PAYMENT_BATCH_MASTER=458875

while (!$result_data->EOF)
{

          $batch_id=$result_data->fields['PK_PAYMENT_BATCH_MASTER'];
          if($batch_id!="" && isset($_SESSION['PK_ACCOUNT']))
          {

             echo  $batch_delete_sql="DELETE FROM S_PAYMENT_BATCH_MASTER WHERE PK_PAYMENT_BATCH_MASTER = '$batch_id' AND PK_ACCOUNT = '$PK_ACCOUNT'";
             echo "<br><br> <hr>";
              $db->Execute($batch_delete_sql);
          
              echo $batch_details="select PK_PAYMENT_BATCH_DETAIL,PK_STUDENT_DISBURSEMENT from S_PAYMENT_BATCH_DETAIL WHERE PK_ACCOUNT = '$PK_ACCOUNT' AND PK_PAYMENT_BATCH_MASTER = '$batch_id'";
              echo "<br><br> <hr>";

              $res_det = $db->Execute($batch_details);


              while (!$res_det->EOF) 
              { 

                    $PK_PAYMENT_BATCH_DETAIL = $res_det->fields['PK_PAYMENT_BATCH_DETAIL'];
                    $PK_STUDENT_DISBURSEMENT = $res_det->fields['PK_STUDENT_DISBURSEMENT'];

                    if($PK_PAYMENT_BATCH_DETAIL!="" && $PK_ACCOUNT!="")
                    {
                      echo $batch_details_delete="DELETE FROM S_PAYMENT_BATCH_DETAIL WHERE PK_PAYMENT_BATCH_DETAIL = '$PK_PAYMENT_BATCH_DETAIL' AND PK_ACCOUNT = '$PK_ACCOUNT'";        
                      $db->Execute($batch_details_delete);
                      echo "<br><br> <hr>";
                    }

                    if($PK_PAYMENT_BATCH_DETAIL!="")
                    {
                      $ledger_data_del['PK_PAYMENT_BATCH_DETAIL'] = $PK_PAYMENT_BATCH_DETAIL;
                      delete_student_ledger($ledger_data_del);
                    }
                    
                    if($PK_STUDENT_DISBURSEMENT!="")
                    {
                      $STUDENT_DISBURSEMENT['PK_PAYMENT_BATCH_DETAIL'] = '';
                      $STUDENT_DISBURSEMENT['DEPOSITED_DATE'] 		 = '';
                      $STUDENT_DISBURSEMENT['PK_DISBURSEMENT_STATUS']  = 2;
                      db_perform('S_STUDENT_DISBURSEMENT', $STUDENT_DISBURSEMENT, 'update'," PK_STUDENT_DISBURSEMENT = '$PK_STUDENT_DISBURSEMENT' ");
                    }
                    
                    $res_det->MoveNext();
              }

    }
    //sleep(1);
    $result_data->MoveNext();
}

?>
