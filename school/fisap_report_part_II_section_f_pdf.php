<?php
// echo "CALL COMP20100(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', ".$_POST['PK_AWARD_YEAR'].",  '".$SP_REPORT_TYPE."', 'summary')";
// exit;
$res = $db->Execute("CALL COMP20100(".$_SESSION['PK_ACCOUNT'].", '".$campus_id."', ".$_POST['PK_AWARD_YEAR'].",  '".$SP_REPORT_TYPE."', 'summary')");
$data=[];
$top_header=[];
while (!$res->EOF) { 
	$data[$res->fields['AUTOMATIC_ZERO_EFC']][$res->fields['DEPENDENT_STATUS']][]=$res->fields;
	
	$top_header[$res->fields['AUTOMATIC_ZERO_EFC']]=array('award_year'=>$res->fields['AWARD_YEAR'],'AUTOMATIC_ZERO_EFC'=>$res->fields['AUTOMATIC_ZERO_EFC'],'total_student'=>0);
	$res->MoveNext();
}

 foreach ($top_header as $key => $value) {
	$no_count=0;

	foreach ($data[$value['AUTOMATIC_ZERO_EFC']] as $k => $v) 
	{		
		foreach ($v as $a => $b) 
		{
			if($b['RECORD_TYPE']=="HEADER_1")
			{
				$no_count +=$b['REPORT_GROUP_COUNT'];
			}	
			
		}
			
	}
	$top_header[$key]['total_student']=$no_count;
 }


	foreach ($top_header as $key => $value) 
	{
	$mpdf->AddPage();

	$txt1="";
	$txt1 .='<table border="0" cellspacing="0" cellpadding="4" width="100%">
		<tr>
		<td style="font-size:18px"><b>Student FA Info for Automatic Zero EFC: '.($value['AUTOMATIC_ZERO_EFC']=="No"?"N":"Y").' </b></td>
		</tr>
		<tr>
		<td  style="border-bottom:1px solid #000;font-size:18px" ><b>Award Year: '.$value['award_year'].' - '.$value['total_student'].' Total Students </b></td>
		</tr>
		</table><br>';
	$j=0;
	$txt1 .= '<table border="0" cellspacing="0" cellpadding="4" width="100%">';
	foreach($data[$value['AUTOMATIC_ZERO_EFC']] as $l => $deptstatus){
		
		$j++;

		
		$total_student=0;
		foreach ($deptstatus as $header_1) {

			if($header_1['RECORD_TYPE'] == "HEADER_1") {

				$total_student=$header_1['REPORT_GROUP_COUNT'];
			}
		}

$txt1 .= '
				<thead>
					<tr>
						<td colspan="2" ><b style="font-size:15px" >Dependent Status: '.$l.'</b></td>
						<td colspan="3" align="center" ><b style="font-size:15px" > '.$total_student.' Student</b></td>
						<td colspan="3" align="right" ><b style="font-size:15px" >Automatic Zero EFC: '.($value['AUTOMATIC_ZERO_EFC']=="No"?"N":"Y").'</b></td>
					</tr>
					<tr>
						<td width="20%" style="border-bottom:1px solid #000;" >
							<b><i>Student</i><br /></b>
						</td>
						<td width="12.5%" style="border-bottom:1px solid #000;" >
							<b><i>Student ID</i><br /></b>
						</td>
						<td width="12.5%" style="border-bottom:1px solid #000;" align="right"  >
							<b><i>Student<br />Income</i><br /></b>
						</td>
						<td width="12.5%" style="border-bottom:1px solid #000;" align="right"  >
							<b><i>Parent<br />Income</i><br /></b>
						</td>
						<td width="9%" style="border-bottom:1px solid #000;"align="right"   >
							<b><i>EFC No.</i><br /></b>
						</td>
						<td width="11%" style="border-bottom:1px solid #000;" align="right"  >
							<b><i>Automatic<br />Zero EFC</i><br /></b>
						</td>
						<td width="12.5%" style="border-bottom:1px solid #000;" align="right"  >
							<b><i>Income<br />Level</i><br /></b>
						</td>
						<td width="10%" style="border-bottom:1px solid #000;" align="right"  >
							<b><i>Dependent<br />Status</i><br /></b>
						</td>
						<td width="10%" style="border-bottom:1px solid #000;" align="right"  >
							<b><i>Award<br />Year</i><br /></b>
						</td>
					</tr>
				</thead>';
		foreach ($deptstatus as $k=>$department_data) {
		if($department_data['RECORD_TYPE'] == "HEADER_2") {
			$txt1 .= '<tr>
						<td colspan="4"  style="border-top:1px solid #000;font-size:15px;" >'.$department_data['DEPENDENT_STATUS'].' - Income Group: '.$department_data['INCOME_GROUP'].'</td>
						<td colspan="4" style="border-top:1px solid #000;font-size:15px;" align="right" >Student Count: '.$department_data['REPORT_GROUP_COUNT'].'</td>
			</tr>';
		}
		if($department_data['RECORD_TYPE'] == "DETAIL") {
			$txt1 .= '<tr>
						<td >'.$department_data['STUDENT'].'</td>
						<td  >'.$department_data['STUDENT_ID'].'</td>
						<td align="right" >$ '.number_format_value_checker($department_data['STUDENT_INCOME'],2).'</td>
						<td align="right" >$ '.number_format_value_checker($department_data['PARENT_INCOME'],2).'</td>
						<td align="right" >'.$department_data['EFC_NO'].'</td>
						<td align="right" >'.($department_data['AUTOMATIC_ZERO_EFC']=="No"?"N":"Y").'</td>
						<td align="right" >'.$department_data['INCOME_LEVEL'].'</td>
						<td align="right" >'.$department_data['DEPENDENT_STATUS'].'</td>
						<td align="right" >'.$department_data['AWARD_YEAR'].'</td>
					</tr>';
		}

	}
	}

	$txt1 .="</table>";
	$mpdf->WriteHTML($txt1);

}


?>