<?php 
$date_time="";
// batch history advanace loging
function batch_history($batch_array=array()){

  global $date_time;

  if($batch_array['OLD_VALUE']!=$batch_array['NEW_VALUE']){
    $batch_array['PK_ACCOUNT']=$_SESSION['PK_ACCOUNT'];
    $batch_array['CHANGED_BY'] = $_SESSION['PK_USER'];
	  $batch_array['CHANGED_ON'] = $date_time;
    $res = db_perform('S_BATCH_HISTORY_ADVANCE_LOGINING', $batch_array, 'insert');
  }
}
// batch payment history advanace logging
function payment_unpost_batch_history($PK_PAYMENT_BATCH_MASTER,$POST_ARRAY,$ID="", $STUDENT_DISBURSEMENT=array())
{ 
  global $db,$date_time;

  $date_time=date("Y-m-d H:i");

  $sql = "SELECT BATCH_NO,PAYMENT_BATCH_START_DATE,PAYMENT_BATCH_END_DATE,PK_BATCH_STATUS,BATCH_PK_CAMPUS,DATE_RECEIVED,POSTED_DATE,CHECK_NO,AMOUNT,PK_AR_LEDGER_CODE,COMMENTS,EDITED_BY,EDITED_ON FROM S_PAYMENT_BATCH_MASTER WHERE PK_PAYMENT_BATCH_MASTER=".$PK_PAYMENT_BATCH_MASTER;
  $res_det=$db->Execute($sql); 
  $PK_STUDENT_DISBURSEMENT="";
  if($PK_PAYMENT_BATCH_MASTER!="" && $ID=="")
  {
      // TRACK BATCH NO
      if(!empty($POST_ARRAY['BATCH_NO'])){
        $batch_array=array();
        $batch_array['OLD_VALUE']=$res_det->fields['BATCH_NO'];
        $batch_array['NEW_VALUE']=$POST_ARRAY['BATCH_NO'];
        $batch_array['FIELD_NAME']='BATCH_NO';
        $batch_array['PK_PAYMENT_BATCH_MASTER']=$PK_PAYMENT_BATCH_MASTER;
        batch_history($batch_array);
      }

      //PAYMENT_BATCH_START_DATE
      if(!empty($POST_ARRAY['PAYMENT_BATCH_START_DATE'])){
        $batch_array=array();
        $batch_array['OLD_VALUE']=$res_det->fields['PAYMENT_BATCH_START_DATE'];
        $batch_array['NEW_VALUE']=$POST_ARRAY['PAYMENT_BATCH_START_DATE'];
        $batch_array['FIELD_NAME']='PAYMENT_BATCH_START_DATE';

        $batch_array['PK_PAYMENT_BATCH_MASTER']=$PK_PAYMENT_BATCH_MASTER;
        batch_history($batch_array);
      }

       //PAYMENT_BATCH_START_DATE
       if(!empty($POST_ARRAY['PAYMENT_BATCH_END_DATE'])){
        $batch_array=array();
        $batch_array['OLD_VALUE']=$res_det->fields['PAYMENT_BATCH_END_DATE'];
        $batch_array['NEW_VALUE']=$POST_ARRAY['PAYMENT_BATCH_END_DATE'];
        $batch_array['FIELD_NAME']='PAYMENT_BATCH_END_DATE';
        $batch_array['PK_PAYMENT_BATCH_MASTER']=$PK_PAYMENT_BATCH_MASTER;
        batch_history($batch_array);
      }
    
    
    
      // TRACK BATCH_STATUS
      if(!empty($POST_ARRAY['PK_BATCH_STATUS'])){
        $batch_array=array();
        $batch_array['OLD_VALUE']=$res_det->fields['PK_BATCH_STATUS'];
        $batch_array['NEW_VALUE']=$POST_ARRAY['PK_BATCH_STATUS'];
        $batch_array['FIELD_NAME']='BATCH_STATUS';
        $batch_array['PK_PAYMENT_BATCH_MASTER']=$PK_PAYMENT_BATCH_MASTER;
        batch_history($batch_array);
      }

     // TRACK BATCH_PK_CAMPUS
     if(!empty($POST_ARRAY['BATCH_PK_CAMPUS'])){
      $batch_array=array();
      $batch_array['OLD_VALUE']=$res_det->fields['BATCH_PK_CAMPUS'];
      $batch_array['NEW_VALUE']=$POST_ARRAY['BATCH_PK_CAMPUS'];
      $batch_array['FIELD_NAME']='BATCH_CAMPUS';
      $batch_array['PK_PAYMENT_BATCH_MASTER']=$PK_PAYMENT_BATCH_MASTER;
      batch_history($batch_array);
    }

      // TRACK BATCH_DATE
      if(!empty($POST_ARRAY['DATE_RECEIVED'])){
      $batch_array=array();
      $batch_array['OLD_VALUE']=$res_det->fields['DATE_RECEIVED'];
      $batch_array['NEW_VALUE']=$POST_ARRAY['DATE_RECEIVED'];
      $batch_array['FIELD_NAME']='BATCH_DATE';
      $batch_array['PK_PAYMENT_BATCH_MASTER']=$PK_PAYMENT_BATCH_MASTER;
      batch_history($batch_array);
     }

      // TRACK POSTED_DATE
      if(!empty($POST_ARRAY['POSTED_DATE'])){
        $batch_array=array();
        $batch_array['OLD_VALUE']=$res_det->fields['POSTED_DATE'];
        $batch_array['NEW_VALUE']=$POST_ARRAY['POSTED_DATE'];
        $batch_array['FIELD_NAME']='POSTED_DATE';
        $batch_array['PK_PAYMENT_BATCH_MASTER']=$PK_PAYMENT_BATCH_MASTER;
        batch_history($batch_array);
      }

      
      // TRACK BATCH_CHECK_NO
      if(!empty($POST_ARRAY['CHECK_NO'])){
        $batch_array=array();
        $batch_array['OLD_VALUE']=$res_det->fields['CHECK_NO'];
        $batch_array['NEW_VALUE']=$POST_ARRAY['CHECK_NO'];
        $batch_array['FIELD_NAME']='BATCH_CHECK_NO';
        $batch_array['PK_PAYMENT_BATCH_MASTER']=$PK_PAYMENT_BATCH_MASTER;
        batch_history($batch_array);
      }

      // TRACK AMOUNT
      if(!empty($POST_ARRAY['AMOUNT'])){
        $batch_array=array();
        $batch_array['OLD_VALUE']=$res_det->fields['AMOUNT'];
        $batch_array['NEW_VALUE']=$POST_ARRAY['AMOUNT'];
        $batch_array['FIELD_NAME']='BATCH_TOTAL';
        $batch_array['PK_PAYMENT_BATCH_MASTER']=$PK_PAYMENT_BATCH_MASTER;
        batch_history($batch_array);
      }
      // TRACK CREDIT_TOTAL
      if(!empty($POST_ARRAY['AMOUNT'])){
        $batch_array=array();
        $batch_array['OLD_VALUE']=$res_det->fields['AMOUNT'];
        $batch_array['NEW_VALUE']=$POST_ARRAY['AMOUNT'];
        $batch_array['FIELD_NAME']='CREDIT_TOTAL';
        $batch_array['PK_PAYMENT_BATCH_MASTER']=$PK_PAYMENT_BATCH_MASTER;
        batch_history($batch_array);
      }
      // TRACK BATCH_LEDGER_CODES
      if(!empty($POST_ARRAY['PK_AR_LEDGER_CODE'])){
        $batch_array=array();
        $batch_array['OLD_VALUE']=$res_det->fields['PK_AR_LEDGER_CODE'];
        $batch_array['NEW_VALUE']=$POST_ARRAY['PK_AR_LEDGER_CODE'];
        $batch_array['FIELD_NAME']='BATCH_LEDGER_CODES';
        $batch_array['PK_PAYMENT_BATCH_MASTER']=$PK_PAYMENT_BATCH_MASTER;
        batch_history($batch_array);
      }

        // TRACK BATCH_COMMENTS
        if(!empty($POST_ARRAY['COMMENTS'])){
          $batch_array=array();
          $batch_array['OLD_VALUE']=$res_det->fields['COMMENTS'];
          $batch_array['NEW_VALUE']=$POST_ARRAY['COMMENTS'];
          $batch_array['FIELD_NAME']='BATCH_COMMENTS';
          $batch_array['PK_PAYMENT_BATCH_MASTER']=$PK_PAYMENT_BATCH_MASTER;
          batch_history($batch_array);
        }

        // TRACK EDITED_BY
        if(!empty($POST_ARRAY['EDITED_BY'])){
          $batch_array=array();
          $batch_array['OLD_VALUE']=$res_det->fields['EDITED_BY'];
          $batch_array['NEW_VALUE']=$POST_ARRAY['EDITED_BY'];
          $batch_array['FIELD_NAME']='UPDATED_BY';
          $batch_array['PK_PAYMENT_BATCH_MASTER']=$PK_PAYMENT_BATCH_MASTER;
          batch_history($batch_array);
        }
      
        // TRACK EDITED_ON
        if(!empty($POST_ARRAY['EDITED_ON'])){
          $batch_array=array();
          $batch_array['OLD_VALUE']=$res_det->fields['EDITED_ON'];
          $batch_array['NEW_VALUE']=$POST_ARRAY['EDITED_ON'];
          $batch_array['FIELD_NAME']='UPDATED_ON';
          $batch_array['PK_PAYMENT_BATCH_MASTER']=$PK_PAYMENT_BATCH_MASTER;
          batch_history($batch_array);
        }
  }

// BATCH DETAILS
if($PK_PAYMENT_BATCH_MASTER!="" && $ID!="")
{
      $sql = "SELECT * FROM S_PAYMENT_BATCH_DETAIL WHERE PK_PAYMENT_BATCH_MASTER=".$PK_PAYMENT_BATCH_MASTER." AND PK_PAYMENT_BATCH_DETAIL=".$ID;
      $res_detail=$db->Execute($sql); 
      // TRACK PK_STUDENT_MASTER
      if(!empty($POST_ARRAY['PK_STUDENT_MASTER'])){
        $batch_array=array();
        $batch_array['OLD_VALUE']=$res_detail->fields['PK_STUDENT_MASTER'];
        $batch_array['NEW_VALUE']=$POST_ARRAY['PK_STUDENT_MASTER'];
        $batch_array['PK_PAYMENT_BATCH_MASTER']=$PK_PAYMENT_BATCH_MASTER;
        $batch_array['FIELD_NAME']='PK_STUDENT_MASTER';
        $batch_array['ID']=$ID;
        batch_history($batch_array);
      }

      // TRACK DISBURSEMENT_TYPE
      if(!empty($POST_ARRAY['DISBURSEMENT_TYPE'])){
        $batch_array=array();
        $batch_array['OLD_VALUE']=$res_detail->fields['DISBURSEMENT_TYPE'];
        $batch_array['NEW_VALUE']=$POST_ARRAY['DISBURSEMENT_TYPE'];
        $batch_array['PK_PAYMENT_BATCH_MASTER']=$PK_PAYMENT_BATCH_MASTER;
        $batch_array['FIELD_NAME']='DISBURSEMENT_TYPE';
        $batch_array['ID']=$ID;
        batch_history($batch_array);
      }

   

      // TRACK TRANS_DATE
      if(!empty($POST_ARRAY['BATCH_TRANSACTION_DATE'])){
        $batch_array=array();
        $batch_array['OLD_VALUE']=$res_detail->fields['BATCH_TRANSACTION_DATE'];
        $batch_array['NEW_VALUE']=$POST_ARRAY['BATCH_TRANSACTION_DATE'];
        $batch_array['PK_PAYMENT_BATCH_MASTER']=$PK_PAYMENT_BATCH_MASTER;
        $batch_array['FIELD_NAME']='BATCH_TRANSACTION_DATE';
        $batch_array['ID']=$ID;
        batch_history($batch_array);
      }


      // TRACK AP
      if(!empty($POST_ARRAY['CHECK_NO'])){
        $batch_array=array();
        $batch_array['OLD_VALUE']=$res_detail->fields['CHECK_NO'];
        $batch_array['NEW_VALUE']=$POST_ARRAY['CHECK_NO'];
        $batch_array['PK_PAYMENT_BATCH_MASTER']=$PK_PAYMENT_BATCH_MASTER;
        $batch_array['FIELD_NAME']='CHECK_NO';
        $batch_array['ID']=$ID;
        batch_history($batch_array);
      }

      // TRACK RECEIPT_NO
      if(!empty($POST_ARRAY['RECEIPT_NO'])){
        $batch_array=array();
        $batch_array['OLD_VALUE']=$res_detail->fields['RECEIPT_NO'];
        $batch_array['NEW_VALUE']=$POST_ARRAY['RECEIPT_NO'];
        $batch_array['PK_PAYMENT_BATCH_MASTER']=$PK_PAYMENT_BATCH_MASTER;
        $batch_array['ID']=$ID;
        $batch_array['FIELD_NAME']='RECEIPT_NO';
        batch_history($batch_array);
      }

     

      // TRACK PK_STUDENT_ENROLLMENT
      if(!empty($POST_ARRAY['PK_STUDENT_ENROLLMENT'])){
      $batch_array=array();
      $batch_array['OLD_VALUE']=$res_detail->fields['PK_STUDENT_ENROLLMENT'];
      $batch_array['NEW_VALUE']=$POST_ARRAY['PK_STUDENT_ENROLLMENT'];
      $batch_array['PK_PAYMENT_BATCH_MASTER']=$PK_PAYMENT_BATCH_MASTER;
      $batch_array['ID']=$ID;
      $batch_array['FIELD_NAME']='PK_STUDENT_ENROLLMENT';

      batch_history($batch_array);
    }

    // TRACK PK_TERM_BLOCK
    if(!empty($POST_ARRAY['PK_TERM_BLOCK'])){
      $batch_array=array();
      $batch_array['OLD_VALUE']=$res_detail->fields['PK_TERM_BLOCK'];
      $batch_array['NEW_VALUE']=$POST_ARRAY['PK_TERM_BLOCK'];
      $batch_array['PK_PAYMENT_BATCH_MASTER']=$PK_PAYMENT_BATCH_MASTER;
      $batch_array['ID']=$ID;
      $batch_array['FIELD_NAME']='PK_TERM_BLOCK';
      batch_history($batch_array);
    }

     // TRACK PK_TERM_BLOCK
     if(!empty($POST_ARRAY['PRIOR_YEAR'])){
      $batch_array=array();
      $batch_array['OLD_VALUE']=$res_detail->fields['PRIOR_YEAR'];
      $batch_array['NEW_VALUE']=$POST_ARRAY['PRIOR_YEAR'];
      $batch_array['PK_PAYMENT_BATCH_MASTER']=$PK_PAYMENT_BATCH_MASTER;
      $batch_array['ID']=$ID; 
      $batch_array['FIELD_NAME']='PRIOR_YEAR';
      batch_history($batch_array);
    }

    // TRACK BATCH_DETAIL_DESCRIPTION
    if(!empty($POST_ARRAY['BATCH_DETAIL_DESCRIPTION'])){
      $batch_array=array();
      $batch_array['OLD_VALUE']=$res_detail->fields['BATCH_DETAIL_DESCRIPTION'];
      $batch_array['NEW_VALUE']=$POST_ARRAY['BATCH_DETAIL_DESCRIPTION'];
      $batch_array['PK_PAYMENT_BATCH_MASTER']=$PK_PAYMENT_BATCH_MASTER;
      $batch_array['ID']=$ID;
      $batch_array['FIELD_NAME']='BATCH_MESSAGE';
      batch_history($batch_array);
    }

    // TRACK EDITED_BY
    if(!empty($POST_ARRAY['EDITED_BY'])){
      $batch_array=array();
      $batch_array['OLD_VALUE']=$res_detail->fields['EDITED_BY'];
      $batch_array['NEW_VALUE']=$POST_ARRAY['EDITED_BY'];
      $batch_array['FIELD_NAME']='EDITED_BY';
      $batch_array['PK_PAYMENT_BATCH_MASTER']=$PK_PAYMENT_BATCH_MASTER;
      batch_history($batch_array);
    }
  
    // TRACK EDITED_ON
    if(!empty($POST_ARRAY['EDITED_ON'])){
      $batch_array=array();
      $batch_array['OLD_VALUE']=$res_detail->fields['EDITED_ON'];
      $batch_array['NEW_VALUE']=$POST_ARRAY['EDITED_ON'];
      $batch_array['FIELD_NAME']='EDITED_ON';
      $batch_array['PK_PAYMENT_BATCH_MASTER']=$PK_PAYMENT_BATCH_MASTER;
      batch_history($batch_array);
    }

   $PK_STUDENT_DISBURSEMENT=$POST_ARRAY['PK_STUDENT_DISBURSEMENT'];
} 


  if($PK_PAYMENT_BATCH_MASTER!="" && $ID!="" && $PK_STUDENT_DISBURSEMENT!="")
  {

    $sql = "SELECT * FROM S_STUDENT_DISBURSEMENT WHERE PK_PAYMENT_BATCH_DETAIL=".$PK_PAYMENT_BATCH_MASTER." AND PK_STUDENT_DISBURSEMENT=".$PK_STUDENT_DISBURSEMENT;
    $res_disbursement=$db->Execute($sql); 


      // TRACK RECEIPT_NO
      if(!empty($STUDENT_DISBURSEMENT['PK_DISBURSEMENT_STATUS'])){
        $batch_array=array();
        $batch_array['OLD_VALUE']=$res_disbursement->fields['PK_DISBURSEMENT_STATUS'];
        $batch_array['NEW_VALUE']=$STUDENT_DISBURSEMENT['PK_DISBURSEMENT_STATUS'];
        $batch_array['PK_PAYMENT_BATCH_MASTER']=$PK_PAYMENT_BATCH_MASTER;
        $batch_array['ID']=$ID;
        $batch_array['FIELD_NAME']='DISBURSEMENT_STATUS';

        batch_history($batch_array);
      }
     
       // TRACK DISBURSEMENT_DATE
      if(!empty($STUDENT_DISBURSEMENT['DISBURSEMENT_DATE'])){
        $batch_array=array();
        $batch_array['OLD_VALUE']=$res_disbursement->fields['DISBURSEMENT_DATE'];
        $batch_array['NEW_VALUE']=$STUDENT_DISBURSEMENT['DISBURSEMENT_DATE'];
        $batch_array['PK_PAYMENT_BATCH_MASTER']=$PK_PAYMENT_BATCH_MASTER;
        $batch_array['ID']=$ID;
        $batch_array['FIELD_NAME']='DISBURSEMENT_DATE';
        batch_history($batch_array);
      }


     // TRACK PAYMENT_TYPE
      if(!empty($STUDENT_DISBURSEMENT['DETAIL'])){
        $batch_array=array();
        $batch_array['OLD_VALUE']=$res_disbursement->fields['DETAIL'];
        $batch_array['NEW_VALUE']=$STUDENT_DISBURSEMENT['DETAIL'];
        $batch_array['PK_PAYMENT_BATCH_MASTER']=$PK_PAYMENT_BATCH_MASTER;
        $batch_array['ID']=$ID;
        $batch_array['FIELD_NAME']='PAYMENT_TYPE';

        batch_history($batch_array);
      }

       // TRACK PAYMENT_TYPE
       if(!empty($STUDENT_DISBURSEMENT['ACADEMIC_YEAR'])){
        $batch_array=array();
        $batch_array['OLD_VALUE']=$res_disbursement->fields['ACADEMIC_YEAR'];
        $batch_array['NEW_VALUE']=$STUDENT_DISBURSEMENT['ACADEMIC_YEAR'];
        $batch_array['PK_PAYMENT_BATCH_MASTER']=$PK_PAYMENT_BATCH_MASTER;
        $batch_array['ID']=$ID;
        $batch_array['FIELD_NAME']='AY';

        batch_history($batch_array);
      }

      // TRACK AP
      if(!empty($STUDENT_DISBURSEMENT['ACADEMIC_PERIOD'])){
        $batch_array=array();
        $batch_array['OLD_VALUE']=$res_disbursement->fields['ACADEMIC_PERIOD'];
        $batch_array['NEW_VALUE']=$STUDENT_DISBURSEMENT['ACADEMIC_PERIOD'];
        $batch_array['PK_PAYMENT_BATCH_MASTER']=$PK_PAYMENT_BATCH_MASTER;
        $batch_array['ID']=$ID;
        $batch_array['FIELD_NAME']='AP';
        batch_history($batch_array);
      }
    
  }


}


// batch payment history advanace logging
function misc_unpost_batch_history($PK_MISC_BATCH_MASTER,$POST_ARRAY,$ID="")
{
  global $db, $date_time;
  $date_time=date("Y-m-d H:i");

  $sql = "SELECT * FROM S_MISC_BATCH_MASTER WHERE PK_MISC_BATCH_MASTER=".$PK_MISC_BATCH_MASTER;
  $res_det=$db->Execute($sql);

  if($PK_MISC_BATCH_MASTER!="" && $ID=="")
  {
     
      // TRACK BATCH NO
      if(!empty($POST_ARRAY['BATCH_NO'])){
        $batch_array=array();
        $batch_array['OLD_VALUE']=$res_det->fields['BATCH_NO'];
        $batch_array['NEW_VALUE']=$POST_ARRAY['BATCH_NO'];
        $batch_array['FIELD_NAME']='BATCH_NO';
        $batch_array['PK_MISC_BATCH_MASTER']=$PK_MISC_BATCH_MASTER;
        batch_history($batch_array);
      }

      // TRACK BATCH_STATUS
      if(!empty($POST_ARRAY['PK_BATCH_STATUS'])){
        $batch_array=array();
        $batch_array['OLD_VALUE']=$res_det->fields['PK_BATCH_STATUS'];
        $batch_array['NEW_VALUE']=$POST_ARRAY['PK_BATCH_STATUS'];
        $batch_array['FIELD_NAME']='BATCH_STATUS';
        $batch_array['PK_MISC_BATCH_MASTER']=$PK_MISC_BATCH_MASTER;
        batch_history($batch_array);
      }

     // TRACK BATCH_PK_CAMPUS
     if(!empty($POST_ARRAY['MISC_BATCH_PK_CAMPUS'])){
      $batch_array=array();
      $batch_array['OLD_VALUE']=$res_det->fields['MISC_BATCH_PK_CAMPUS'];
      $batch_array['NEW_VALUE']=$POST_ARRAY['MISC_BATCH_PK_CAMPUS'];
      $batch_array['FIELD_NAME']='BATCH_CAMPUS';
      $batch_array['PK_MISC_BATCH_MASTER']=$PK_MISC_BATCH_MASTER;
      batch_history($batch_array);
    }

      // TRACK BATCH_DATE
      if(!empty($POST_ARRAY['BATCH_DATE'])){
      $batch_array=array();
      $batch_array['OLD_VALUE']=$res_det->fields['BATCH_DATE'];
      $batch_array['NEW_VALUE']=$POST_ARRAY['BATCH_DATE'];
      $batch_array['FIELD_NAME']='BATCH_DATE';
      $batch_array['PK_MISC_BATCH_MASTER']=$PK_MISC_BATCH_MASTER;
      batch_history($batch_array);
     }

      // TRACK POSTED_DATE
      if(!empty($POST_ARRAY['POSTED_DATE'])){
        $batch_array=array();
        $batch_array['OLD_VALUE']=$res_det->fields['POSTED_DATE'];
        $batch_array['NEW_VALUE']=$POST_ARRAY['POSTED_DATE'];
        $batch_array['FIELD_NAME']='POSTED_DATE';
        $batch_array['PK_MISC_BATCH_MASTER']=$PK_MISC_BATCH_MASTER;
        batch_history($batch_array);
      }



      // TRACK AMOUNT
      if(!empty($POST_ARRAY['DEBIT'])){
        $batch_array=array();
        $batch_array['OLD_VALUE']=$res_det->fields['DEBIT'];
        $batch_array['NEW_VALUE']=$POST_ARRAY['DEBIT'];
        $batch_array['FIELD_NAME']='DEBIT_TOTAL';
        $batch_array['PK_MISC_BATCH_MASTER']=$PK_MISC_BATCH_MASTER;
        batch_history($batch_array);
      }
      // TRACK CREDIT_TOTAL
      if(!empty($POST_ARRAY['CREDIT'])){
        $batch_array=array();
        $batch_array['OLD_VALUE']=$res_det->fields['CREDIT'];
        $batch_array['NEW_VALUE']=$POST_ARRAY['CREDIT'];
        $batch_array['FIELD_NAME']='CREDIT_TOTAL';
        $batch_array['PK_MISC_BATCH_MASTER']=$PK_MISC_BATCH_MASTER;
        batch_history($batch_array);
      }


        // TRACK BATCH_COMMENTS
        if(!empty($POST_ARRAY['DESCRIPTION'])){
          $batch_array=array();
          $batch_array['OLD_VALUE']=$res_det->fields['BATCH_DETAIL_DESCRIPTION'];
          $batch_array['NEW_VALUE']=$POST_ARRAY['DESCRIPTION'];
          $batch_array['FIELD_NAME']='BATCH_DESCRIPTION';
          $batch_array['PK_MISC_BATCH_MASTER']=$PK_MISC_BATCH_MASTER;
          batch_history($batch_array);
        }


        // TRACK BATCH_COMMENTS
        if(!empty($POST_ARRAY['COMMENTS'])){
          $batch_array=array();
          $batch_array['OLD_VALUE']=$res_det->fields['COMMENTS'];
          $batch_array['NEW_VALUE']=$POST_ARRAY['COMMENTS'];
          $batch_array['FIELD_NAME']='BATCH_COMMENTS';
          $batch_array['PK_MISC_BATCH_MASTER']=$PK_MISC_BATCH_MASTER;
          batch_history($batch_array);
        }

        

        // TRACK EDITED_BY
        if(!empty($POST_ARRAY['EDITED_BY'])){
          $batch_array=array();
          $batch_array['OLD_VALUE']=$res_det->fields['EDITED_BY'];
          $batch_array['NEW_VALUE']=$POST_ARRAY['EDITED_BY'];
          $batch_array['FIELD_NAME']='UPDATED_BY';
          $batch_array['PK_MISC_BATCH_MASTER']=$PK_MISC_BATCH_MASTER;
          batch_history($batch_array);
        }
      
        // TRACK EDITED_ON
        if(!empty($POST_ARRAY['EDITED_ON'])){
          $batch_array=array();
          $batch_array['OLD_VALUE']=$res_det->fields['EDITED_ON'];
          $batch_array['NEW_VALUE']=$POST_ARRAY['EDITED_ON'];
          $batch_array['FIELD_NAME']='UPDATED_ON';
          $batch_array['PK_MISC_BATCH_MASTER']=$PK_MISC_BATCH_MASTER;
          batch_history($batch_array);
        }
  }
  // 

  
// BATCH DETAILS
if($PK_MISC_BATCH_MASTER!="" && $ID!="")
{

 

      $sql = "SELECT * FROM S_MISC_BATCH_DETAIL WHERE PK_MISC_BATCH_MASTER=".$PK_MISC_BATCH_MASTER." AND PK_MISC_BATCH_DETAIL=".$ID;
      $res_detail=$db->Execute($sql); 

      // TRACK PK_STUDENT_MASTER
      if(!empty($POST_ARRAY['PK_STUDENT_MASTER'])){
        $batch_array=array();
        $batch_array['OLD_VALUE']=$res_detail->fields['PK_STUDENT_MASTER'];
        $batch_array['NEW_VALUE']=$POST_ARRAY['PK_STUDENT_MASTER'];
        $batch_array['FIELD_NAME']='PK_STUDENT_MASTER';
        $batch_array['PK_MISC_BATCH_MASTER']=$PK_MISC_BATCH_MASTER;
        $batch_array['ID']=$ID;
        batch_history($batch_array);
      }

      // TRACK LEDGER_CODE
      if(!empty($POST_ARRAY['PK_AR_LEDGER_CODE'])){
        $batch_array=array();
        $batch_array['OLD_VALUE']=$res_detail->fields['PK_AR_LEDGER_CODE'];
        $batch_array['NEW_VALUE']=$POST_ARRAY['PK_AR_LEDGER_CODE'];
        $batch_array['FIELD_NAME']='LEDGER_CODE';
        $batch_array['PK_MISC_BATCH_MASTER']=$PK_MISC_BATCH_MASTER;
        $batch_array['ID']=$ID;
        batch_history($batch_array);
      }

      // TRACK TRANS_DATE
      if(!empty($POST_ARRAY['TRANSACTION_DATE'])){
        $batch_array=array();
        $batch_array['OLD_VALUE']=$res_detail->fields['TRANSACTION_DATE'];
        $batch_array['NEW_VALUE']=$POST_ARRAY['TRANSACTION_DATE'];
        $batch_array['FIELD_NAME']='TRANS_DATE';
        $batch_array['PK_MISC_BATCH_MASTER']=$PK_MISC_BATCH_MASTER;
        $batch_array['ID']=$ID;
        batch_history($batch_array);
      }

      // TRACK DEBIT_AMOINT
      if(!empty($POST_ARRAY['DEBIT'])){
        $batch_array=array();
        $batch_array['OLD_VALUE']=$res_detail->fields['DEBIT'];
        $batch_array['NEW_VALUE']=$POST_ARRAY['DEBIT'];
        $batch_array['FIELD_NAME']='DEBIT_AMOINT';
        $batch_array['PK_MISC_BATCH_MASTER']=$PK_MISC_BATCH_MASTER;
        $batch_array['ID']=$ID;
        batch_history($batch_array);
      }

       // TRACK DEBIT_AMOINT
       if(!empty($POST_ARRAY['CREDIT'])){
        $batch_array=array();
        $batch_array['OLD_VALUE']=$res_detail->fields['CREDIT'];
        $batch_array['NEW_VALUE']=$POST_ARRAY['CREDIT'];
        $batch_array['FIELD_NAME']='CREDIT_AMOUNT';
        $batch_array['PK_MISC_BATCH_MASTER']=$PK_MISC_BATCH_MASTER;
        $batch_array['ID']=$ID;
        batch_history($batch_array);
      }

      // TRACK PAYMENT_TYPE
      if(!empty($POST_ARRAY['PK_AR_PAYMENT_TYPE'])){
        $batch_array=array();
        $batch_array['OLD_VALUE']=$res_detail->fields['PK_AR_PAYMENT_TYPE'];
        $batch_array['NEW_VALUE']=$POST_ARRAY['PK_AR_PAYMENT_TYPE'];
        $batch_array['FIELD_NAME']='FEE_PAYMENT_TYPE';
        $batch_array['PK_MISC_BATCH_MASTER']=$PK_MISC_BATCH_MASTER;
        $batch_array['ID']=$ID;
        batch_history($batch_array);
      }

      // TRACK PAYMENT_TYPE
      if(!empty($POST_ARRAY['AY'])){
        $batch_array=array();
        $batch_array['OLD_VALUE']=$res_detail->fields['AY'];
        $batch_array['NEW_VALUE']=$POST_ARRAY['AY'];
        $batch_array['FIELD_NAME']='AY';
        $batch_array['PK_MISC_BATCH_MASTER']=$PK_MISC_BATCH_MASTER;
        $batch_array['ID']=$ID;
        batch_history($batch_array);
      }

      // TRACK AP
      if(!empty($POST_ARRAY['AP'])){
        $batch_array=array();
        $batch_array['OLD_VALUE']=$res_detail->fields['AP'];
        $batch_array['NEW_VALUE']=$POST_ARRAY['AP'];
        $batch_array['FIELD_NAME']='AP';
        $batch_array['PK_MISC_BATCH_MASTER']=$PK_MISC_BATCH_MASTER;
        $batch_array['ID']=$ID;
        batch_history($batch_array);
      }

      if(!empty($POST_ARRAY['MISC_RECEIPT_NO'])){
        $batch_array=array();
        $batch_array['OLD_VALUE']=$res_detail->fields['MISC_RECEIPT_NO'];
        $batch_array['NEW_VALUE']=$POST_ARRAY['MISC_RECEIPT_NO'];
        $batch_array['FIELD_NAME']='RECEIPT_NO';
        $batch_array['PK_MISC_BATCH_MASTER']=$PK_MISC_BATCH_MASTER;
        $batch_array['ID']=$ID;
        batch_history($batch_array);
      }


     

      // TRACK PK_STUDENT_ENROLLMENT
      if(!empty($POST_ARRAY['PK_STUDENT_ENROLLMENT'])){
      $batch_array=array();
      $batch_array['OLD_VALUE']=$res_detail->fields['PK_STUDENT_ENROLLMENT'];
      $batch_array['NEW_VALUE']=$POST_ARRAY['PK_STUDENT_ENROLLMENT'];
      $batch_array['FIELD_NAME']='PK_STUDENT_ENROLLMENT';
      $batch_array['PK_MISC_BATCH_MASTER']=$PK_MISC_BATCH_MASTER;
      $batch_array['ID']=$ID;
      batch_history($batch_array);
    }

    // TRACK PK_TERM_BLOCK
    if(!empty($POST_ARRAY['PK_TERM_BLOCK'])){
      $batch_array=array();
      $batch_array['OLD_VALUE']=$res_detail->fields['PK_TERM_BLOCK'];
      $batch_array['NEW_VALUE']=$POST_ARRAY['PK_TERM_BLOCK'];
      $batch_array['FIELD_NAME']='PK_TERM_BLOCK';
      $batch_array['PK_MISC_BATCH_MASTER']=$PK_MISC_BATCH_MASTER;
      $batch_array['ID']=$ID;
      batch_history($batch_array);
    }

    // TRACK PK_TERM_BLOCK
    if(!empty($POST_ARRAY['PRIOR_YEAR'])){
      $batch_array=array();
      $batch_array['OLD_VALUE']=$res_detail->fields['PRIOR_YEAR'];
      $batch_array['NEW_VALUE']=$POST_ARRAY['PRIOR_YEAR'];
      $batch_array['FIELD_NAME']='PRIOR_YEAR';
      $batch_array['PK_MISC_BATCH_MASTER']=$PK_MISC_BATCH_MASTER;
      $batch_array['ID']=$ID;
      batch_history($batch_array);
    }

} 
// batch details end
}

// batch payment history advanace logging
function tuition_unpost_batch_history($PK_TUITION_BATCH_MASTER,$POST_ARRAY,$ID="")
{
    global $db, $date_time;
    $date_time=date("Y-m-d H:i");
    $sql = "SELECT * FROM S_TUITION_BATCH_MASTER WHERE PK_TUITION_BATCH_MASTER=".$PK_TUITION_BATCH_MASTER;
    $res_det=$db->Execute($sql);
  
    if($PK_TUITION_BATCH_MASTER!="" && $ID=="")
    {

        // TRACK BATCH NO
        if(!empty($POST_ARRAY['BATCH_NO'])){
          $batch_array=array();
          $batch_array['OLD_VALUE']=$res_det->fields['BATCH_NO'];
          $batch_array['NEW_VALUE']=$POST_ARRAY['BATCH_NO'];
          $batch_array['FIELD_NAME']='BATCH_NO';
          $batch_array['PK_TUITION_BATCH_MASTER']=$PK_TUITION_BATCH_MASTER;
          batch_history($batch_array);
        }
      
        // TRACK BATCH_STATUS
        if(!empty($POST_ARRAY['PK_BATCH_STATUS'])){
          $batch_array=array();
          $batch_array['OLD_VALUE']=$res_det->fields['PK_BATCH_STATUS'];
          $batch_array['NEW_VALUE']=$POST_ARRAY['PK_BATCH_STATUS'];
          $batch_array['FIELD_NAME']='BATCH_STATUS';
          $batch_array['PK_TUITION_BATCH_MASTER']=$PK_TUITION_BATCH_MASTER;
          batch_history($batch_array);
        }
  
       // TRACK BATCH_CAMPUS
       if(!empty($POST_ARRAY['TUITION_BATCH_PK_CAMPUS'])){
        $batch_array=array();
        $batch_array['OLD_VALUE']=$res_det->fields['TUITION_BATCH_PK_CAMPUS'];
        $batch_array['NEW_VALUE']=$POST_ARRAY['TUITION_BATCH_PK_CAMPUS'];
        $batch_array['FIELD_NAME']='BATCH_CAMPUS';
        $batch_array['PK_TUITION_BATCH_MASTER']=$PK_TUITION_BATCH_MASTER;
        batch_history($batch_array);
      }
  
        // TRACK BATCH_DATE
        if(!empty($POST_ARRAY['TRANS_DATE'])){
        $batch_array=array();
        $batch_array['OLD_VALUE']=$res_det->fields['TRANS_DATE'];
        $batch_array['NEW_VALUE']=$POST_ARRAY['TRANS_DATE'];
        $batch_array['FIELD_NAME']='BATCH_DATE';
        $batch_array['PK_TUITION_BATCH_MASTER']=$PK_TUITION_BATCH_MASTER;
        batch_history($batch_array);
       }
  
        // TRACK POSTED_DATE
        if(!empty($POST_ARRAY['POSTED_DATE'])){
          $batch_array=array();
          $batch_array['OLD_VALUE']=$res_det->fields['POSTED_DATE'];
          $batch_array['NEW_VALUE']=$POST_ARRAY['POSTED_DATE'];
          $batch_array['FIELD_NAME']='POSTED_DATE';
          $batch_array['PK_TUITION_BATCH_MASTER']=$PK_TUITION_BATCH_MASTER;
          batch_history($batch_array);
        }
  
        // TRACK CREDIT_TOTAL
        if(!empty($POST_ARRAY['DEBIT_TOTAL'])){
          $batch_array=array();
          $batch_array['OLD_VALUE']=$res_det->fields['DEBIT_TOTAL'];
          $batch_array['NEW_VALUE']=$POST_ARRAY['DEBIT_TOTAL'];
          $batch_array['FIELD_NAME']='DEBIT_TOTAL';
          $batch_array['PK_TUITION_BATCH_MASTER']=$PK_TUITION_BATCH_MASTER;
          batch_history($batch_array);
        }
  
        // TRACK CREDIT_TOTAL
        if(!empty($POST_ARRAY['BATCH_TOTAL'])){
          $batch_array=array();
          $batch_array['OLD_VALUE']=$res_det->fields['BATCH_TOTAL'];
          $batch_array['NEW_VALUE']=$POST_ARRAY['BATCH_TOTAL'];
          $batch_array['FIELD_NAME']='BATCH_TOTAL';
          $batch_array['PK_TUITION_BATCH_MASTER']=$PK_TUITION_BATCH_MASTER;
          batch_history($batch_array);
        }

          // TRACK BATCH_COURSE_TERM
          if(!empty($POST_ARRAY['PK_TERM_MASTER'])){
            $batch_array=array();
            $batch_array['OLD_VALUE']=$res_det->fields['PK_TERM_MASTER'];
            $batch_array['NEW_VALUE']=$POST_ARRAY['PK_TERM_MASTER'];
            $batch_array['FIELD_NAME']='BATCH_COURSE_TERM';
            $batch_array['PK_TUITION_BATCH_MASTER']=$PK_TUITION_BATCH_MASTER;
            batch_history($batch_array);
          }


          // TRACK BATCH_COURSE
          if(!empty($POST_ARRAY['PK_COURSE'])){
            $batch_array=array();
            $batch_array['OLD_VALUE']=$res_det->fields['PK_COURSE'];
            $batch_array['NEW_VALUE']=$POST_ARRAY['PK_COURSE'];
            $batch_array['FIELD_NAME']='BATCH_COURSE';
            $batch_array['PK_TUITION_BATCH_MASTER']=$PK_TUITION_BATCH_MASTER;
            batch_history($batch_array);
          }

          // TRACK BATCH_COURSE
          if(!empty($POST_ARRAY['PK_COURSE_OFFERING'])){
            $batch_array=array();
            $batch_array['OLD_VALUE']=$res_det->fields['PK_COURSE_OFFERING'];
            $batch_array['NEW_VALUE']=$POST_ARRAY['PK_COURSE_OFFERING'];
            $batch_array['FIELD_NAME']='BATCH_COURSE_OFFERING';
            $batch_array['PK_TUITION_BATCH_MASTER']=$PK_TUITION_BATCH_MASTER;
            batch_history($batch_array);
          }
  

           // TRACK BATCH_COURSE
           if(!empty($POST_ARRAY['PK_CAMPUS_PROGRAM'])){
            $batch_array=array();
            $batch_array['OLD_VALUE']=$res_det->fields['PK_CAMPUS_PROGRAM'];
            $batch_array['NEW_VALUE']=$POST_ARRAY['PK_CAMPUS_PROGRAM'];
            $batch_array['FIELD_NAME']='BATCH_PROGRAM';
            $batch_array['PK_TUITION_BATCH_MASTER']=$PK_TUITION_BATCH_MASTER;
            batch_history($batch_array);
          }

          // TRACK BATCH_COURSE
          if(!empty($POST_ARRAY['AY'])){
            $batch_array=array();
            $batch_array['OLD_VALUE']=$res_det->fields['AY'];
            $batch_array['NEW_VALUE']=$POST_ARRAY['AY'];
            $batch_array['FIELD_NAME']='BATCH_AY';
            $batch_array['PK_TUITION_BATCH_MASTER']=$PK_TUITION_BATCH_MASTER;
            batch_history($batch_array);
          }
          if(!empty($POST_ARRAY['AP'])){
            $batch_array=array();
            $batch_array['OLD_VALUE']=$res_det->fields['AP'];
            $batch_array['NEW_VALUE']=$POST_ARRAY['AP'];
            $batch_array['FIELD_NAME']='BATCH_AP';
            $batch_array['PK_TUITION_BATCH_MASTER']=$PK_TUITION_BATCH_MASTER;
            batch_history($batch_array);
          }
          // TRACK BATCH_COMMENTS
          if(!empty($POST_ARRAY['COMMENTS'])){
            $batch_array=array();
            $batch_array['OLD_VALUE']=$res_det->fields['COMMENTS'];
            $batch_array['NEW_VALUE']=$POST_ARRAY['COMMENTS'];
            $batch_array['FIELD_NAME']='BATCH_COMMENTS';
            $batch_array['PK_TUITION_BATCH_MASTER']=$PK_TUITION_BATCH_MASTER;
            batch_history($batch_array);
          }
    }
    // 
  
    
  // BATCH DETAILS
  if($PK_TUITION_BATCH_MASTER!="" && $ID!="")
  {

        $sql = "SELECT * FROM S_TUITION_BATCH_DETAIL WHERE PK_TUITION_BATCH_MASTER=".$PK_TUITION_BATCH_MASTER." AND PK_TUITION_BATCH_DETAIL=".$ID;
        $res_detail=$db->Execute($sql); 

        //print_r($POST_ARRAY);
        //exit;
        // TRACK PK_STUDENT_MASTER
        if(!empty($POST_ARRAY['PK_STUDENT_MASTER'])){
          $batch_array=array();
          $batch_array['OLD_VALUE']=$res_detail->fields['PK_STUDENT_MASTER'];
          $batch_array['NEW_VALUE']=$POST_ARRAY['PK_STUDENT_MASTER'];
          $batch_array['FIELD_NAME']='PK_STUDENT_MASTER';
          $batch_array['PK_TUITION_BATCH_MASTER']=$PK_TUITION_BATCH_MASTER;
          $batch_array['ID']=$ID;
          batch_history($batch_array);
        }
  
        // TRACK LEDGER_CODE
        if(!empty($POST_ARRAY['PK_AR_LEDGER_CODE'])){
          $batch_array=array();
          $batch_array['OLD_VALUE']=$res_detail->fields['PK_AR_LEDGER_CODE'];
          $batch_array['NEW_VALUE']=$POST_ARRAY['PK_AR_LEDGER_CODE'];
          $batch_array['FIELD_NAME']='LEDGER_CODE';
          $batch_array['PK_TUITION_BATCH_MASTER']=$PK_TUITION_BATCH_MASTER;
          $batch_array['ID']=$ID;
          batch_history($batch_array);
        }
  
        // TRACK TRANS_DATE
        if(!empty($POST_ARRAY['TRANSACTION_DATE'])){
          $batch_array=array();
          $batch_array['OLD_VALUE']=$res_detail->fields['TRANSACTION_DATE'];
          $batch_array['NEW_VALUE']=$POST_ARRAY['TRANSACTION_DATE'];
          $batch_array['FIELD_NAME']='TRANS_DATE';
          $batch_array['PK_TUITION_BATCH_MASTER']=$PK_TUITION_BATCH_MASTER;
          $batch_array['ID']=$ID;
          batch_history($batch_array);
        }
  
        // TRACK TRANS_DATE
        if(!empty($POST_ARRAY['AMOUNT'])){
          $batch_array=array();
          $batch_array['OLD_VALUE']=$res_detail->fields['AMOUNT'];
          $batch_array['NEW_VALUE']=$POST_ARRAY['AMOUNT'];
          $batch_array['FIELD_NAME']='DEBIT_AMOINT';
          $batch_array['PK_TUITION_BATCH_MASTER']=$PK_TUITION_BATCH_MASTER;
          $batch_array['ID']=$ID;
          batch_history($batch_array);
        }
  

  
        // TRACK PAYMENT_TYPE
        if(!empty($POST_ARRAY['TUITION_BATCH_DETAIL_AY'])){
          $batch_array=array();
          $batch_array['OLD_VALUE']=$res_detail->fields['TUITION_BATCH_DETAIL_AY'];
          $batch_array['NEW_VALUE']=$POST_ARRAY['TUITION_BATCH_DETAIL_AY'];
          $batch_array['FIELD_NAME']='AY';
          $batch_array['PK_TUITION_BATCH_MASTER']=$PK_TUITION_BATCH_MASTER;
          $batch_array['ID']=$ID;
          batch_history($batch_array);
        }
  
        // TRACK AP
        if(!empty($POST_ARRAY['TUITION_BATCH_DETAIL_AP'])){
          $batch_array=array();
          $batch_array['OLD_VALUE']=$res_detail->fields['TUITION_BATCH_DETAIL_AP'];
          $batch_array['NEW_VALUE']=$POST_ARRAY['TUITION_BATCH_DETAIL_AP'];
          $batch_array['FIELD_NAME']='AP';
          $batch_array['PK_TUITION_BATCH_MASTER']=$PK_TUITION_BATCH_MASTER;
          $batch_array['ID']=$ID;
          batch_history($batch_array);
        }
  
        // TRACK RECEIPT_NO
   
  
        // TRACK PK_STUDENT_ENROLLMENT
        if(!empty($POST_ARRAY['PK_STUDENT_ENROLLMENT'])){
            $batch_array=array();
            $batch_array['OLD_VALUE']=$res_detail->fields['PK_STUDENT_ENROLLMENT'];
            $batch_array['NEW_VALUE']=$POST_ARRAY['PK_STUDENT_ENROLLMENT'];
            $batch_array['FIELD_NAME']='AP';
            $batch_array['PK_TUITION_BATCH_MASTER']=$PK_TUITION_BATCH_MASTER;
            $batch_array['ID']=$ID;
            batch_history($batch_array);
          }
  
          // TRACK PK_TERM_BLOCK
          if(!empty($POST_ARRAY['PK_TERM_BLOCK'])){
            $batch_array=array();
            $batch_array['OLD_VALUE']=$res_detail->fields['PK_TERM_BLOCK'];
            $batch_array['NEW_VALUE']=$POST_ARRAY['PK_TERM_BLOCK'];
            $batch_array['FIELD_NAME']='PK_TERM_BLOCK';
            $batch_array['PK_TUITION_BATCH_MASTER']=$PK_TUITION_BATCH_MASTER;
            $batch_array['ID']=$ID;
            batch_history($batch_array);
          }
  
          // TRACK PK_TERM_BLOCK
          if(!empty($POST_ARRAY['TUITION_BATCH_DETAIL_PRIOR_YEAR'])){
            $batch_array=array();
            $batch_array['OLD_VALUE']=$res_detail->fields['TUITION_BATCH_DETAIL_PRIOR_YEAR'];
            $batch_array['NEW_VALUE']=$POST_ARRAY['TUITION_BATCH_DETAIL_PRIOR_YEAR'];
            $batch_array['FIELD_NAME']='PRIOR_YEAR';
            $batch_array['PK_TUITION_BATCH_MASTER']=$PK_TUITION_BATCH_MASTER;
            $batch_array['ID']=$ID;
            batch_history($batch_array);
          }
  } 
  // batch details end

}

?>