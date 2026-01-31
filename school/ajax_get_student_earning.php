<? /*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/

require_once("../global/config.php"); 
require_once("../global/create_notification.php"); 
require_once("../language/common.php");
require_once("../language/student.php");
require_once("../language/student_contact.php");
require_once("get_department_from_t.php");

require_once("check_access.php");
$ACCOUNTING_ACCESS 	= check_access('ACCOUNTING_ACCESS');
if($ACCOUNTING_ACCESS == 0) {
	header("location:../index");
	exit;
}

$PROGRAM 	= "";
$txt 		= "";

$STUD_TUITION 			= 0;
$STUD_PREVIOUS_EARNING 	= 0;
$STUD_EARNINGS_AMOUNT 	= 0;
$STUD_UNEARNED_AMOUNT 	= 0;
//echo $_POST['SELECTED_PK_STUDENT_MASTER']."<br />CALL ACCT20011(".$_SESSION['PK_ACCOUNT'].", ".$_POST['PK_CAMPUS'].", ".$_POST['YEAR'].",0 , ".$PK_STUDENT_MASTER.",'STUDENT EARNINGS')";exit;
//echo "CALL ACCT20011(".$_SESSION['PK_ACCOUNT'].", ".$_REQUEST['camp_id'].", ".date("Y").",0 , ".$_REQUEST['stud_id'].",'STUDENT EARNINGS')";exit;
$count   				= 0;
$prog_tot_displayed   	= 0;
$res = $db->Execute("CALL ACCT20011(".$_SESSION['PK_ACCOUNT'].", ".$_REQUEST['camp_id'].", ".date("Y").",0 , ".$_REQUEST['stud_id'].",'STUDENT EARNINGS')");
if($res->RecordCount() == 0) {
	$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
				<thead>
					<tr>
						<th colspan="15" align="left" ><b style="font-size:15px" >'.$PROGRAM.'</b><br /></th>
					</tr>
					<tr>
						<th width="7%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
							<b><i>Earnings Year/Month</i></b>
						</th>
						<th width="8%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
							<b><i>Status</i></b>
						</th>
						<th width="6%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
							<b><i>First Term</i></b>
						</th>
						<th width="7%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
							<b><i>End Date</i></b>
						</th>
						<th width="7%" style="border-bottom:1px solid #000;border-top:1px solid #000;text-align:right;" align="right" >
							<b><i>Tuition Charged</i></b>
						</th>
						<th width="7%" style="border-bottom:1px solid #000;border-top:1px solid #000;text-align:right;" align="right" >
							<b><i>Previous Earnings</i></b>
						</th>
						<th width="7%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
							<b><i>Calculation Status/Type</i></b>
						</th>
						<th width="7%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
							<b><i>Prorated Reason</i></b>
						</th>
						<th width="6%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
							<b><i>Calculation Date</i></b>
						</th>
						<th width="6%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
							<b><i>Finalized Date</i></b>
						</th>
						<th width="6%" style="border-bottom:1px solid #000;border-top:1px solid #000;text-align:right;" align="right" >
								<b><i>Earnings Days</i></b>
						</th>
						<th width="5%" style="border-bottom:1px solid #000;border-top:1px solid #000;text-align:right;"  align="right" >
							<b><i>Month Days</i></b>
						</th>
						<th width="7%" style="border-bottom:1px solid #000;border-top:1px solid #000;text-align:right;" align="right" >
							<b><i>Earnings Ratio</i></b>
						</th>
						<th width="7%" style="border-bottom:1px solid #000;border-top:1px solid #000;text-align:right;" align="right" >
							<b><i>Earnings Amount</i></b>
						</th>
						<th width="7%" style="border-bottom:1px solid #000;border-top:1px solid #000;text-align:right;" align="right" >
							<b><i>Unearned Amount</i></b>
						</th>
					</tr>
				</thead>';
} else { ?>
	<div class="row">
		<div class="col-12 col-sm-12 text-right">
			<button  type="button" onclick="window.location.href='earnings_report?rt=3&YEAR=<?=date("Y")?>&sid=<?=$_REQUEST['stud_id']?>&camp_id=<?=$_REQUEST['camp_id']?>&format=1'" class="btn waves-effect waves-light btn-info"><?=PDF ?></button>
			<button  type="button" onclick="window.location.href='earnings_report?rt=3&YEAR=<?=date("Y")?>&sid=<?=$_REQUEST['stud_id']?>&camp_id=<?=$_REQUEST['camp_id']?>&format=2'" class="btn waves-effect waves-light btn-info"><?=EXCEL ?></button>
		</div>
	</div>
<? }

//print_r($res);exit;
while (!$res->EOF) {
	if($PROGRAM != $res->fields['PROGRAM']) {
		
		if($txt != '' && $count > 0) {
			if($prog_tot_displayed == 0) {
				$prog_tot_displayed = 1;
				$txt .= '<tr>
							<td  ></td>
							<td  ></td>
							<td  ></td>
							<td  ></td>
							<td  ></td>
							<td  ></td>
							<td  ></td>
							<td  ></td>
							<td  ></td>
							<td  ></td>
							<td  ></td>
							<td  ></td>
							<td  ><b>Totals</b></td>
							<td  align="right" ><b>$'.number_format_value_checker($STUD_EARNINGS_AMOUNT, 2).'</b></td>
							<td  ></td>
						</tr>';
			}
			$txt .= '</table>';
		}
		
		$count   = 0;
		$PROGRAM = $res->fields['PROGRAM'];
		
		$PROG_TUITION 			= 0;
		$PROG_PREVIOUS_EARNING 	= 0;
		$PROG_EARNINGS_AMOUNT 	= 0;
		$PROG_UNEARNED_AMOUNT 	= 0;
		$prog_tot_displayed		= 0;
		
		$txt .= '<table border="0" cellspacing="0" cellpadding="3" width="100%">
					<thead>
						<tr>
							<th colspan="15" align="left" ><b style="font-size:15px" >'.$res->fields['PROGRAM'].' — '.$res->fields['PROGRAM_DESCRIPTION'].' — '.$res->fields['PROGRAM_MONTHS'].' — Earnings Type : '.$res->fields['EARNINGS_TYPE'].'</b><br /></th>
						</tr>
						<tr>
							<th width="7%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
								<b><i>Earnings Year/Month</i></b>
							</th>
							<th width="8%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
								<b><i>Status</i></b>
							</th>
							<th width="6%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
								<b><i>First Term</i></b>
							</th>
							<th width="7%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
								<b><i>End Date</i></b>
							</th>
							<th width="7%" style="border-bottom:1px solid #000;border-top:1px solid #000;text-align:right;" align="right" >
								<b><i>Tuition Charged</i></b>
							</th>
							<th width="7%" style="border-bottom:1px solid #000;border-top:1px solid #000;text-align:right;" align="right" >
								<b><i>Previous Earnings</i></b>
							</th>
							<th width="7%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
								<b><i>Calculation Status/Type</i></b>
							</th>
							<th width="7%" style="border-bottom:1px solid #000;border-top:1px solid #000;" align="left" >
								<b><i>Prorated Reason</i></b>
							</th>
							<th width="6%" style="border-bottom:1px solid #000;border-top:1px solid #000;text-align:right;"  align="right" >
								<b><i>Calculation Date</i></b>
							</th>
							<th width="6%" style="border-bottom:1px solid #000;border-top:1px solid #000;text-align:right;" align="right" >
								<b><i>Finalized Date</i></b>
							</th>
							<th width="6%" style="border-bottom:1px solid #000;border-top:1px solid #000;text-align:right;" align="right" >
								<b><i>Earnings Days</i></b>
							</th>
							<th width="5%" style="border-bottom:1px solid #000;border-top:1px solid #000;text-align:right;"  align="right" >
								<b><i>Month Days</i></b>
							</th>
							<th width="7%" style="border-bottom:1px solid #000;border-top:1px solid #000;text-align:right;" align="right" >
								<b><i>Earnings Ratio</i></b>
							</th>
							<th width="7%" style="border-bottom:1px solid #000;border-top:1px solid #000;text-align:right;" align="right" >
								<b><i>Earnings Amount</i></b>
							</th>
							<th width="7%" style="border-bottom:1px solid #000;border-top:1px solid #000;text-align:right;" align="right" >
								<b><i>Unearned Amount</i></b>
							</th>
						</tr>
					</thead>';
	} 

	//else {
		//echo "strindsadasdg";
		$count++;

		$ENROLLMENT_BEGIN_DATE = '';
		if($res->fields['ENROLLMENT_BEGIN_DATE'] != '' && $res->fields['ENROLLMENT_BEGIN_DATE'] != '0000-00-00')
			$ENROLLMENT_BEGIN_DATE = date("m/d/Y", strtotime($res->fields['ENROLLMENT_BEGIN_DATE']));
			
		$ENROLLMENT_END_DATE = '';
		if($res->fields['ENROLLMENT_END_DATE'] != '' && $res->fields['ENROLLMENT_END_DATE'] != '0000-00-00')
			$ENROLLMENT_END_DATE = date("m/d/Y", strtotime($res->fields['ENROLLMENT_END_DATE']));
			
		$CALCULATION_DATE = '';
		if($res->fields['CALCULATION_DATE'] != '' && $res->fields['CALCULATION_DATE'] != '0000-00-00')
			$CALCULATION_DATE = date("m/d/Y", strtotime($res->fields['CALCULATION_DATE']));
			
		$FINALIZED_DATE = '';
		if($res->fields['FINALIZED_DATE'] != '' && $res->fields['FINALIZED_DATE'] != '0000-00-00')
			$FINALIZED_DATE = date("m/d/Y", strtotime($res->fields['FINALIZED_DATE']));
		
		$EARNING_RATIO = ($res->fields['MONTH_EARNING_DAYS'] / $res->fields['DAYS_IN_MONTH']) * 100;

		$txt .= '<tr>
					<td >'.$res->fields['EARNINGS_YEAR_MONTH'].'</td>
					<td >'.$res->fields['STUDENT_STATUS'].'</td>
					<td >'.$ENROLLMENT_BEGIN_DATE.'</td>
					<td >'.$ENROLLMENT_END_DATE.'</td>
					<td align="right" >$'.number_format_value_checker($res->fields['TUITION_CHARGED'], 2).'</td>
					<td align="right" >$'.number_format_value_checker($res->fields['PREVIOUS_EARNINGS'], 2).'</td>
					<td align="center">'.$res->fields['CALCULATION'].'</td>
					<td >'.$res->fields['PRORATED_REASON'].'</td>
					<td align="right">'.$CALCULATION_DATE.'</td>
					<td align="right">'.$FINALIZED_DATE.'</td>
					<td align="right">'.$res->fields['MONTH_EARNING_DAYS'].'</td>
					<td align="right">'.$res->fields['DAYS_IN_MONTH'].'</td>
					<td align="right">'.number_format_value_checker($EARNING_RATIO, 2).'%</td>
					<td align="right" >$'.number_format_value_checker($res->fields['EARNINGS_AMOUNT'],2).'</td>
					<td align="right" >$'.number_format_value_checker($res->fields['UNEARNED_TUITION'],2).'</td>
				</tr>';
	//}
	//echo $count;
	$PROG_TUITION 			+= $res->fields['TUITION_CHARGED'];
	$PROG_PREVIOUS_EARNING 	+= $res->fields['PREVIOUS_EARNINGS'];
	$PROG_EARNINGS_AMOUNT 	+= $res->fields['EARNINGS_AMOUNT'];
	$PROG_UNEARNED_AMOUNT 	+= $res->fields['UNEARNED_TUITION'];
	
	$STUD_TUITION 			+= $res->fields['TUITION_CHARGED'];
	$STUD_PREVIOUS_EARNING 	+= $res->fields['PREVIOUS_EARNINGS'];
	$STUD_EARNINGS_AMOUNT 	+= $res->fields['EARNINGS_AMOUNT'];
	$STUD_UNEARNED_AMOUNT 	+= $res->fields['UNEARNED_TUITION'];
	
	$res->MoveNext();
}

if($prog_tot_displayed == 0) {
	$prog_tot_displayed = 1;
	$txt .= '<tr>
				<td  ></td>
				<td  ></td>
				<td  ></td>
				<td  ></td>
				<td  ></td>
				<td  ></td>
				<td  ></td>
				<td  ></td>
				<td  ></td>
				<td  ></td>
				<td  ></td>
				<td  ></td>
				<td  ><b>Totals</b></td>
				<td  align="right" ><b>$'.number_format_value_checker($STUD_EARNINGS_AMOUNT,2).'</b></td>
				<td  ></td>
			</tr>';
}

$txt .= '<tr>
			<th colspan="15" align="left" ><br /><br /></th>
		</tr>
		<tr>
			<td style="border-bottom:1px solid #000;border-top:1px solid #000;" ></td>
			<td style="border-bottom:1px solid #000;border-top:1px solid #000;" ></td>
			<td style="border-bottom:1px solid #000;border-top:1px solid #000;" ></td>
			<td style="border-bottom:1px solid #000;border-top:1px solid #000;" ></td>
			<td style="border-bottom:1px solid #000;border-top:1px solid #000;" ></td>
			<td style="border-bottom:1px solid #000;border-top:1px solid #000;" ></td>
			<td style="border-bottom:1px solid #000;border-top:1px solid #000;" ></td>
			<td style="border-bottom:1px solid #000;border-top:1px solid #000;" ></td>
			<td style="border-bottom:1px solid #000;border-top:1px solid #000;" ></td>
			<td style="border-bottom:1px solid #000;border-top:1px solid #000;" ></td>
			<td style="border-bottom:1px solid #000;border-top:1px solid #000;" ></td>
			<td style="border-bottom:1px solid #000;border-top:1px solid #000;" ></td>
			<td style="border-bottom:1px solid #000;border-top:1px solid #000;" ><b>Grand Totals</b></td>
			<td style="border-bottom:1px solid #000;border-top:1px solid #000;" align="right" ><b>$'.number_format_value_checker($STUD_EARNINGS_AMOUNT,2).'</b></td>
			<td style="border-bottom:1px solid #000;border-top:1px solid #000;" ></td>
		</tr>';

if($txt != '')
{
	$txt .= '</table>';
}
$txt .= '</br></br></br>';

// DIAM-446
$conn = mysqli_connect($DB_HOST,$DB_USER,$DB_PASS,$DB_DATABASE);

$sQuery = "SELECT E.PROGRAM
			,E.PROGRAM_DESCRIPTION
			,E.PK_STUDENT_MASTER    
			,E.PK_STUDENT_ENROLLMENT
			,E.STUDENT
			,E.STUDENT_ID
			,E.CAMPUS_CODE
			,E.EARNINGS_YEAR
			,E.EARNINGS_MONTH
			,E.EARNINGS_TYPE
			,E.MONTH_EARNING_DAYS
			,E.DAYS_IN_MONTH
			,CONCAT(E.EARNINGS_YEAR, '  ', UPPER(DATE_FORMAT(CONCAT_WS('-', E.EARNINGS_YEAR, E.EARNINGS_MONTH, 1), '%b'))) AS EARNINGS_YEAR_MONTH    
			,E.CALCULATION
			,E.CALCULATION_DATE    
			,E.FINALIZED_DATE    
			,E.ENROLLMENT_BEGIN_DATE
			,E.EXPECTED_GRAD_DATE
			,CASE WHEN E.ENROLLMENT_END_DATE = '2222-02-02' THEN '' ELSE E.ENROLLMENT_END_DATE END AS ENROLLMENT_END_DATE 
			,CONCAT(IFNULL(STB.BEGIN_DATE,''),' - ',IFNULL(STB.END_DATE,'')) AS TERM_BLOCK
			,CASE WHEN STB.DESCRIPTION is NULL THEN '' ELSE STB.DESCRIPTION END AS TERM_BLOCK_DESCRIPTION   
			,E.STUDENT_STATUS
			,(E.DAILY_EARNING_RATE) AS DAILY_AMOUNT
			,(E.TUITION_CHARGED) AS TUITION_CHARGED
			,E.PREVIOUS_EARNINGS
			,(E.MONTH_EARNINGS_AMOUNT) AS CURRENT_EARNINGS
			,(E.EARNINGS_AMOUNT) AS TOTAL_EARNED
			,(E.TUITION_CHARGED-(E.PREVIOUS_EARNINGS+E.EARNINGS_AMOUNT)) AS UNEARNED_TUITION
			FROM S_STUDENT_EARNINGS_TERM_BLOCK AS E
			LEFT JOIN S_TERM_BLOCK AS STB ON E.PK_TERM_BLOCK = STB.PK_TERM_BLOCK
			WHERE E.PK_ACCOUNT = ".$_SESSION['PK_ACCOUNT']."
			AND E.PK_TERM_BLOCK <> 0
			AND E.PK_CAMPUS = ".$_POST['camp_id']."    
			AND FIND_IN_SET(E.PK_STUDENT_MASTER, ".$_REQUEST['stud_id'].")
			GROUP BY E.EARNINGS_YEAR,E.EARNINGS_MONTH,TERM_BLOCK_DESCRIPTION
			ORDER BY TERM_BLOCK_DESCRIPTION, E.EARNINGS_YEAR, E.EARNINGS_MONTH ASC";
			//echo $sQuery;
			//$result = $db->Execute($sQuery);
			$result = mysqli_query($conn,$sQuery);

			// $row = mysqli_fetch_assoc($result);

			// $ENROLLMENT_BEGIN_DATE = '';
			// if($row['ENROLLMENT_BEGIN_DATE'] != '' && $row['ENROLLMENT_BEGIN_DATE'] != '0000-00-00')
			// {
			// 	$ENROLLMENT_BEGIN_DATE = date("m/d/Y", strtotime($row['ENROLLMENT_BEGIN_DATE']));
			// }
			// $EXPECTED_GRAD_DATE = '';
			// if($row['EXPECTED_GRAD_DATE'] != '' && $row['EXPECTED_GRAD_DATE'] != '0000-00-00')
			// {
			// 	$EXPECTED_GRAD_DATE = date("m/d/Y", strtotime($row['EXPECTED_GRAD_DATE']));
			// }

			$STUD_TUITION 			= 0;
			$STUD_PREVIOUS_EARNING 	= 0;
			$STUD_EARNINGS_AMOUNT 	= 0;
			$STUD_UNEARNED_AMOUNT 	= 0;
			$TOTAL_EARNINGS_AMOUNT  = 0;
			
			$data=[];
			$terms=[];

			while ($row = mysqli_fetch_assoc($result)) 
			{
				$data[$row['TERM_BLOCK']][]=$row;
				$terms[$row['TERM_BLOCK']]=array('TERM_BLOCK'=>$row['TERM_BLOCK'],'EARNINGS_TYPE'=>$row['EARNINGS_TYPE'],'TERM_BLOCK_DESCRIPTION'=>$row['TERM_BLOCK_DESCRIPTION']);
				
			}
			// echo "<pre>";
			// print_r($data);exit;

			if(count($terms) > 0)
			{
				$pdf_url = "onclick=window.location.href='earnings_report_term_block?rt=3&YEAR=".date('Y')."&sid=".$_REQUEST['stud_id']."&camp_id=".$_REQUEST['camp_id']."&format=1'";
				//$excel_url = "onclick=window.location.href='earnings_report_term_block?rt=3&YEAR=".date('Y')."&sid=".$_REQUEST['stud_id']."&camp_id=".$_REQUEST['camp_id']."&format=2'"; // <button  type="button" '.$excel_url.' class="btn waves-effect waves-light btn-info">EXCEL</button>
				$txt .= '<div class="row">
					<div class="col-12 col-sm-12 text-right">
						<button type="button" '.$pdf_url.' class="btn waves-effect waves-light btn-info">PDF</button>
						
					</div>
				</div>';
			}

			// TERM BLOCK DATE AND DESC
			foreach($terms as $key=>$val) 
			{
				$txt .= '
						<b>'.$val['EARNINGS_TYPE'].' :</b> '.$val['TERM_BLOCK'].' '.$val['TERM_BLOCK_DESCRIPTION'].'
						<style>
							
							.table_row {margin-bottom : 15px;margin-top : 16px;}
						</style>
						<table border="1" class="table_row" cellspacing="0" cellpadding="3" width="100%">
							<thead >
									<tr class="table_row">
										<th>
											<b><i>Earnings Year/Month</i></b>
										</th>
										<th>
											<b><i>Calculation Date</i></b>
										</th>
										<th>
											<b><i>Calculation Status</i></b>
										</th>
										<th>
											<b><i>Finalized Date</i></b>
										</th>
										<th>
											<b><i>Daily Amount</i></b>
										</th>
										<th>
											<b><i>Month Days</i></b>
										</th>
										<th style="text-align: right;">
											<b><i>Current Earnings</i></b>
										</th>
										<th style="text-align: right;">
											<b><i>Total Earnings</i></b>
										</th>
										<th style="text-align: right;">
											<b><i>Unearned Amount</i></b>
										</th>
										<th style="text-align: right;">
											<b><i>Total Tuition</i></b>
										</th>										
									</tr>
							</thead>
							<tbody>
							';

							$PROG_TUITION 			= 0;
							$PROG_PREVIOUS_EARNING 	= 0;
							$TOTAL_EARNINGS_AMOUNT  = 0;
							$PROG_EARNINGS_AMOUNT 	= 0;
							$PROG_UNEARNED_AMOUNT 	= 0;
							$STUD_EARNINGS_AMOUNT   = 0;	

							foreach ($data[$val['TERM_BLOCK']] as $k => $results)
							{

								$ENROLLMENT_END_DATE = '';
								if($results['ENROLLMENT_END_DATE'] != '' && $results['ENROLLMENT_END_DATE'] != '0000-00-00')
								{
									$ENROLLMENT_END_DATE = date("m/d/Y", strtotime($results['ENROLLMENT_END_DATE']));
								}
									
								$CALCULATION_DATE = '';
								if($results['CALCULATION_DATE'] != '' && $results['CALCULATION_DATE'] != '0000-00-00')
								{
									$CALCULATION_DATE = date("m/d/Y", strtotime($results['CALCULATION_DATE']));
								}
									
								$FINALIZED_DATE = '';
								if($results['FINALIZED_DATE'] != '' && $results['FINALIZED_DATE'] != '0000-00-00')
								{
									$FINALIZED_DATE = date("m/d/Y", strtotime($results['FINALIZED_DATE']));
								}
								$TOTAL_EARNINGS_AMOUNT 	+= $results['CURRENT_EARNINGS'];   
								$FINAL_UNEARNED_TUITION = $results['TUITION_CHARGED'] - $TOTAL_EARNINGS_AMOUNT;
								if($FINAL_UNEARNED_TUITION < 0)
								{
									$FINAL_UNEARNED_TUITION = '0.00';
								}

								$TEMP_TUITION_TOTAL 	= $results['TUITION_CHARGED'];
								if($TOTAL_EARNINGS_AMOUNT >= $TEMP_TUITION_TOTAL)
								{
									$TOTAL_EARNINGS_AMOUNT = $TEMP_TUITION_TOTAL;
								}

								$sMONTH_EARNING_DAYS = $results['MONTH_EARNING_DAYS'];
								$sCURRENT_EARNINGS = $results['CURRENT_EARNINGS'];
								// if($sMONTH_EARNING_DAYS != 0 && $sCURRENT_EARNINGS != 0.00)
								// {
								
									$txt .= '<tr>
													<td align="center">'.$results['EARNINGS_YEAR_MONTH'].'</td>
													<td align="center">'.$CALCULATION_DATE.'</td>
													<td align="center">'.$results['CALCULATION'].'</td>
													<td align="center">'.$FINALIZED_DATE.'</td>
													<td align="right">$'.number_format($results['DAILY_AMOUNT'], 2).'</td>
													<td align="right">'.$results['MONTH_EARNING_DAYS'].'</td>
													<td align="right" >$'.number_format($results['CURRENT_EARNINGS'], 2).'</td>
													<td align="right" >$'.number_format($TOTAL_EARNINGS_AMOUNT, 2).'</td>
													<td align="right" >$'.number_format($FINAL_UNEARNED_TUITION, 2).'</td>
													<td align="right" >$'.number_format($results['TUITION_CHARGED'], 2).'</td>
												</tr>';
								// }
								

								$PROG_TUITION 			+= $results['TUITION_CHARGED'];
								$PROG_PREVIOUS_EARNING 	+= $results['PREVIOUS_EARNINGS'];
								$PROG_EARNINGS_AMOUNT 	+= $results['CURRENT_EARNINGS'];
								$PROG_UNEARNED_AMOUNT 	+= $results['UNEARNED_TUITION'];
								
								$STUD_TUITION 			+= $results['TUITION_CHARGED'];
								$STUD_PREVIOUS_EARNING 	+= $results['PREVIOUS_EARNINGS'];
								$STUD_EARNINGS_AMOUNT 	+= $results['CURRENT_EARNINGS'];
								$STUD_UNEARNED_AMOUNT 	+= $results['UNEARNED_TUITION'];

								if($STUD_EARNINGS_AMOUNT >= $TEMP_TUITION_TOTAL)
								{
									$STUD_EARNINGS_AMOUNT = $TEMP_TUITION_TOTAL;
								}

							}

							$txt .= '<tr>
											<td></td>
											<td></td>
											<td></td>
											<td></td>
											<td align="center" colspan="2"><b>Term Block Totals:</b></td>
											<td align="right" ><b>$'.number_format($STUD_EARNINGS_AMOUNT, 2).'</b></td>
											<td></td>
											<td></td>
											<td></td>
										</tr>';

				$txt .= "</tbody></table>";
				
			}
			// TERM BLOCK DATE AND DESC

// End DIAM-446
	
echo $txt;