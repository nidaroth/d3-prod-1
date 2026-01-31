<? require_once("../global/config.php"); 
//require_once("../language/common.php");
require_once("../language/program.php");

if($_SESSION['PK_USER'] == 0 || $_SESSION['PK_USER'] == ''){ 
	header("location:../index");
	exit;
} 

	$PK_CAMPUS_PROGRAM_GE = $_REQUEST['id'];
	$res_ge = $db->Execute("SELECT PK_CAMPUS_PROGRAM_GAINFUL_EMPLOYMENT, PK_CAMPUS_PROGRAM, PK_ACCOUNT, CONVERSION_ID, ACCREDITING_AGENCY_NAME, APPROVED_PE_PROGRAM_INDICATOR, CTP_AND_POSTSECONDARY_PROGRAM, ARTS_BACHELORS_DEGREE_PROGRAM, PROG_ACCREDITED_INDICATOR, WEEKS_IN_TITLE_IV_AY, LENGTH_OF_PROGRAM_MEASUREMENT, LENGTH_OF_PROGRAM, ALLOWANCE_FOR_BOOKS_SUPPLIES_EQUIPEMENT, ALLOWANCE_FOR_HOUSING_AND_FOOD, ANNUAL_COA, TUITION_AND_FEES_AMOUNT_AY, STATE_OF_MAIN_CAMPUS, PROGRAM_PS_FOR_LSMC, STATE_TWO_IN_MSA_MC, PROGRAM_PS_FOR_LICENSURE_MSA_STATE_TWO, STATE_THREE_IN_MSA_MC, PROGRAM_PS_FOR_LICENSURE_MSA_STATE_THREE, STATE_FOUR_IN_MSA_MC, PROGRAM_PS_FOR_LICENSURE_MSA_STATE_FOUR, STATE_FIVE_IN_MSA_MC, PROGRAM_PS_FOR_LICENSURE_MSA_STATE_FIVE, QUALIFYING_GRADUATE_PROGRAM_INDICATOR FROM M_CAMPUS_PROGRAM_GAINFUL_EMPLOYMENT WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND PK_CAMPUS_PROGRAM = '$PK_CAMPUS_PROGRAM_GE' ");

	if($res_ge->RecordCount()>0){
		
		$PK_CAMPUS_PROGRAM_GAINFUL_EMPLOYMENT = $res_ge ->fields['PK_CAMPUS_PROGRAM_GAINFUL_EMPLOYMENT'];
		$GE_ACCREDITING_AGENCY_NAME = $res_ge ->fields['ACCREDITING_AGENCY_NAME'];
		$GE_APPROVED_PE_PROGRAM_INDICATOR = $res_ge ->fields['APPROVED_PE_PROGRAM_INDICATOR'];
		$GE_CTP_AND_POSTSECONDARY_PROGRAM = $res_ge ->fields['CTP_AND_POSTSECONDARY_PROGRAM'];
		$GE_ARTS_BACHELORS_DEGREE_PROGRAM = $res_ge ->fields['ARTS_BACHELORS_DEGREE_PROGRAM'];
		$GE_PROG_ACCREDITED_INDICATOR = $res_ge ->fields['PROG_ACCREDITED_INDICATOR'];
		$GE_QUALIFYING_GRADUATE_PROGRAM_INDICATOR = $res_ge ->fields['QUALIFYING_GRADUATE_PROGRAM_INDICATOR'];
		$GE_WEEKS_IN_TITLE_IV_AY = $res_ge ->fields['WEEKS_IN_TITLE_IV_AY'];
		$GE_LENGTH_OF_PROGRAM_MEASUREMENT = $res_ge ->fields['LENGTH_OF_PROGRAM_MEASUREMENT'];
		$GE_LENGTH_OF_PROGRAM = $res_ge ->fields['LENGTH_OF_PROGRAM'];
		$GE_ALLOWANCE_FOR_BOOKS_SUPPLIES_EQUIPEMENT = $res_ge ->fields['ALLOWANCE_FOR_BOOKS_SUPPLIES_EQUIPEMENT'];
		$GE_ALLOWANCE_FOR_HOUSING_AND_FOOD = $res_ge ->fields['ALLOWANCE_FOR_HOUSING_AND_FOOD'];
		$GE_ANNUAL_COA = $res_ge ->fields['ANNUAL_COA'];
		$GE_TUITION_AND_FEES_AMOUNT_AY = $res_ge ->fields['TUITION_AND_FEES_AMOUNT_AY'];
		$GE_STATE_OF_MAIN_CAMPUS = $res_ge ->fields['STATE_OF_MAIN_CAMPUS'];
		$GE_PROGRAM_PS_FOR_LSMC = $res_ge ->fields['PROGRAM_PS_FOR_LSMC'];
		$GE_STATE_TWO_IN_MSA_MC = $res_ge ->fields['STATE_TWO_IN_MSA_MC'];
		$GE_PROGRAM_PS_FOR_LICENSURE_MSA_STATE_TWO = $res_ge ->fields['PROGRAM_PS_FOR_LICENSURE_MSA_STATE_TWO'];
		$GE_STATE_THREE_IN_MSA_MC = $res_ge ->fields['STATE_THREE_IN_MSA_MC'];
		$GE_PROGRAM_PS_FOR_LICENSURE_MSA_STATE_THREE = $res_ge ->fields['PROGRAM_PS_FOR_LICENSURE_MSA_STATE_THREE'];
		$GE_STATE_FOUR_IN_MSA_MC = $res_ge ->fields['STATE_FOUR_IN_MSA_MC'];
		$GE_PROGRAM_PS_FOR_LICENSURE_MSA_STATE_FOUR = $res_ge ->fields['PROGRAM_PS_FOR_LICENSURE_MSA_STATE_FOUR'];
		$GE_STATE_FIVE_IN_MSA_MC = $res_ge ->fields['STATE_FIVE_IN_MSA_MC'];
		$GE_PROGRAM_PS_FOR_LICENSURE_MSA_STATE_FIVE = $res_ge ->fields['PROGRAM_PS_FOR_LICENSURE_MSA_STATE_FIVE'];
	
	}else{

		$PK_CAMPUS_PROGRAM_GAINFUL_EMPLOYMENT ='';
		$GE_ACCREDITING_AGENCY_NAME ='';
		$GE_APPROVED_PE_PROGRAM_INDICATOR ='';
		$GE_CTP_AND_POSTSECONDARY_PROGRAM ='';
		$GE_ARTS_BACHELORS_DEGREE_PROGRAM ='';
		$GE_PROG_ACCREDITED_INDICATOR ='';
		$GE_QUALIFYING_GRADUATE_PROGRAM_INDICATOR ='';
		$GE_WEEKS_IN_TITLE_IV_AY ='';
		$GE_LENGTH_OF_PROGRAM_MEASUREMENT ='';
		$GE_LENGTH_OF_PROGRAM ='';
		$GE_ALLOWANCE_FOR_BOOKS_SUPPLIES_EQUIPEMENT ='';
		$GE_ALLOWANCE_FOR_HOUSING_AND_FOOD ='';
		$GE_ANNUAL_COA ='';
		$GE_TUITION_AND_FEES_AMOUNT_AY ='';
		$GE_STATE_OF_MAIN_CAMPUS ='';
		$GE_PROGRAM_PS_FOR_LSMC ='';
		$GE_STATE_TWO_IN_MSA_MC ='';
		$GE_PROGRAM_PS_FOR_LICENSURE_MSA_STATE_TWO ='';
		$GE_STATE_THREE_IN_MSA_MC ='';
		$GE_PROGRAM_PS_FOR_LICENSURE_MSA_STATE_THREE ='';
		$GE_STATE_FOUR_IN_MSA_MC ='';
		$GE_PROGRAM_PS_FOR_LICENSURE_MSA_STATE_FOUR ='';
		$GE_STATE_FIVE_IN_MSA_MC ='';
		$GE_PROGRAM_PS_FOR_LICENSURE_MSA_STATE_FIVE ='';
		
	}

//}

?>
<div class="row m-t-10" id="GEemployment">  
	<div class="col-sm-4"  style="padding-top: 25px;"> 
		<div class="row">
			<div class="col-sm-12 form-group">			
				<input id="GE_ACCREDITING_AGENCY_NAME" name="GE_ACCREDITING_AGENCY_NAME" value="<?=$GE_ACCREDITING_AGENCY_NAME?>" type="text" class="form-control" />
				<span class="bar"></span> 
				<label for="GE_ACCREDITING_AGENCY_NAME"><?=GE_ACCREDITING_AGENCY_NAME?></label> 
			</div>
		
			<div class="col-12 col-sm-12 form-group"> 
			<select id="GE_APPROVED_PE_PROGRAM_INDICATOR" name="GE_APPROVED_PE_PROGRAM_INDICATOR" class="form-control">	
					<option value=""></option>				
					<option value="Y" <? if ($GE_APPROVED_PE_PROGRAM_INDICATOR == 'Y') echo "selected"; ?>>Yes</option>
					<option value="N" <? if ($GE_APPROVED_PE_PROGRAM_INDICATOR == 'N') echo "selected"; ?>>No</option>					
				</select>
				<span class="bar"></span>
				<label for="GE_APPROVED_PE_PROGRAM_INDICATOR"><?=GE_APPROVED_PE_PROGRAM_INDICATOR?></label>
			</div>
		
			<div class="col-12 col-sm-12 form-group"> 
			<select id="GE_CTP_AND_POSTSECONDARY_PROGRAM" name="GE_CTP_AND_POSTSECONDARY_PROGRAM" class="form-control">	
					<option value=""></option>					
					<option value="Y" <? if ($GE_CTP_AND_POSTSECONDARY_PROGRAM == 'Y') echo "selected"; ?>>Yes</option>
					<option value="N" <? if ($GE_CTP_AND_POSTSECONDARY_PROGRAM == 'N') echo "selected"; ?>>No</option>					
				</select>
				<span class="bar"></span>
				<label for="GE_CTP_AND_POSTSECONDARY_PROGRAM"><?=GE_CTP_AND_POSTSECONDARY_PROGRAM?></label>
			</div>

			<div class="col-12 col-sm-12 form-group"> 
			<select id="GE_ARTS_BACHELORS_DEGREE_PROGRAM" name="GE_ARTS_BACHELORS_DEGREE_PROGRAM" class="form-control">		
					<option value=""></option>				
					<option value="Y" <? if ($GE_ARTS_BACHELORS_DEGREE_PROGRAM == 'Y') echo "selected"; ?>>Yes</option>
					<option value="N" <? if ($GE_ARTS_BACHELORS_DEGREE_PROGRAM == 'N') echo "selected"; ?>>No</option>				
				</select>
				<span class="bar"></span>
				<label for="GE_ARTS_BACHELORS_DEGREE_PROGRAM"><?=GE_ARTS_BACHELORS_DEGREE_PROGRAM?></label>
			</div>

			<div class="col-12 col-sm-12 form-group"> 
			<select id="GE_PROG_ACCREDITED_INDICATOR" name="GE_PROG_ACCREDITED_INDICATOR" class="form-control">
					<option value=""></option>						
					<option value="Y" <? if ($GE_PROG_ACCREDITED_INDICATOR == 'Y') echo "selected"; ?>>Yes</option>
					<option value="N" <? if ($GE_PROG_ACCREDITED_INDICATOR == 'N') echo "selected"; ?>>No</option>					
				</select>
				<span class="bar"></span>
				<label for="GE_PROG_ACCREDITED_INDICATOR"><?=GE_PROG_ACCREDITED_INDICATOR?></label>
			</div>

			<div class="col-12 col-sm-12 form-group"> 
			<select id="GE_QUALIFYING_GRADUATE_PROGRAM_INDICATOR" name="GE_QUALIFYING_GRADUATE_PROGRAM_INDICATOR" class="form-control">
					<option value=""></option>						
					<option value="Y" <? if ($GE_QUALIFYING_GRADUATE_PROGRAM_INDICATOR == 'Y') echo "selected"; ?>>Yes</option>
					<option value="N" <? if ($GE_QUALIFYING_GRADUATE_PROGRAM_INDICATOR == 'N') echo "selected"; ?>>No</option>					
				</select>
				<span class="bar"></span>
				<label for="GE_QUALIFYING_GRADUATE_PROGRAM_INDICATOR"><?=GE_QUALIFYING_GRADUATE_PROGRAM_INDICATOR?></label>
			</div>

		

			

		</div>
	</div>

	<!--- Second column -->
	<div class="col-sm-4 theme-v-border" style="padding-top: 25px;" >

		<div class="row">
			<div class="col-12 col-sm-12 form-group"> 
				<input id="GE_WEEKS_IN_TITLE_IV_AY" name="GE_WEEKS_IN_TITLE_IV_AY" value="<?=($GE_WEEKS_IN_TITLE_IV_AY=='') ? ' ' : $GE_WEEKS_IN_TITLE_IV_AY ?>" type="number" min="0" oninput="validity.valid||(value='');" class="form-control" placeholder="" onblur="this.placeholder = ''" pattern="/^-?\d+\.?\d*$/" onKeyPress="if(this.value.length==6) return false;"/>
				<label for="GE_WEEKS_IN_TITLE_IV_AY"><?=GE_WEEKS_IN_TITLE_IV_AY?></label> 
			</div>
			
			<div class="col-12 col-sm-12 form-group"> 
				<label for="GE_LENGTH_OF_PROGRAM_MEASUREMENT"><?=GE_LENGTH_OF_PROGRAM_MEASUREMENT?></label> 
			</div>
			
			<div class="col-6 col-sm-6  m-t-15 form-group"> 
			<select id="GE_LENGTH_OF_PROGRAM_MEASUREMENT" name="GE_LENGTH_OF_PROGRAM_MEASUREMENT" class="form-control" onchange="get_selectedval(this.value,['','<?=$GE_WEEKS?>','<?=$GE_MONTHS?>','<?=$GE_YEARS?>'])">
				<option value=""></option>						
				<option value="W" <? if ($GE_LENGTH_OF_PROGRAM_MEASUREMENT == 'W') echo "selected"; ?>>Weeks</option>
				<option value="M" <? if ($GE_LENGTH_OF_PROGRAM_MEASUREMENT == 'M') echo "selected"; ?>>Months</option>	
				<option value="Y" <? if ($GE_LENGTH_OF_PROGRAM_MEASUREMENT == 'Y') echo "selected"; ?>>Years</option>					
			</select>
			<label for="GE_LENGTH_OF_PROGRAM_MEASUREMENT"><?=GE_LENGTH_OF_PROGRAM?></label> 
			</div>
			<div class="col-6 col-sm-6  m-t-15 form-group"> 
				<input id="GE_LENGTH_OF_PROGRAM" name="GE_LENGTH_OF_PROGRAM" type="number"  class="form-control" value="<?=($GE_LENGTH_OF_PROGRAM=='') ? ' ' : $GE_LENGTH_OF_PROGRAM ?>" type="number" min="0" oninput="validity.valid||(value='');" class="form-control" placeholder="" onfocus="this.placeholder = '0.00'" onblur="this.placeholder = ''" readonly/>
			</div>

			<div class="col-12 col-sm-12 form-group"> 
				<input id="GE_ALLOWANCE_FOR_BOOKS_SUPPLIES_EQUIPEMENT" name="GE_ALLOWANCE_FOR_BOOKS_SUPPLIES_EQUIPEMENT" value="<?=$GE_ALLOWANCE_FOR_BOOKS_SUPPLIES_EQUIPEMENT?>"  type="number" min="0" oninput="validity.valid||(value='');" class="form-control" placeholder="" onblur="this.placeholder = ''" pattern="/^-?\d+\.?\d*$/" onKeyPress="if(this.value.length==6) return false;"/>
				<label for="GE_ALLOWANCE_FOR_BOOKS_SUPPLIES_EQUIPEMENT"><?=GE_ALLOWANCE_FOR_BOOKS_SUPPLIES_EQUIPEMENT?></label> 
			</div>

			<div class="col-12 col-sm-12 form-group"> 
				<input id="GE_ALLOWANCE_FOR_HOUSING_AND_FOOD" name="GE_ALLOWANCE_FOR_HOUSING_AND_FOOD" value="<?=$GE_ALLOWANCE_FOR_HOUSING_AND_FOOD?>" type="number" min="0" oninput="validity.valid||(value='');" class="form-control" placeholder="" onblur="this.placeholder = ''" pattern="/^-?\d+\.?\d*$/" onKeyPress="if(this.value.length==6) return false;"/>
				<label for="GE_ALLOWANCE_FOR_HOUSING_AND_FOOD"><?=GE_ALLOWANCE_FOR_HOUSING_AND_FOOD?></label> 
			</div>
			<div class="col-12 col-sm-12 form-group"> 
				<input id="GE_ANNUAL_COA" name="GE_ANNUAL_COA" value="<?=$GE_ANNUAL_COA?>"  type="number" min="0" oninput="validity.valid||(value='');" class="form-control" placeholder="" onblur="this.placeholder = ''" pattern="/^-?\d+\.?\d*$/" onKeyPress="if(this.value.length==6) return false;"/>
				<label for="GE_ANNUAL_COA"><?=GE_ANNUAL_COA?></label> 
			</div>

			<div class="col-12 col-sm-12 form-group"> 
				<input id="GE_TUITION_AND_FEES_AMOUNT_AY" name="GE_TUITION_AND_FEES_AMOUNT_AY" value="<?=$GE_TUITION_AND_FEES_AMOUNT_AY?>"  type="number" min="0" oninput="validity.valid||(value='');" class="form-control" placeholder="" onblur="this.placeholder = ''" pattern="/^-?\d+\.?\d*$/" onKeyPress="if(this.value.length==6) return false;"/>
				<label for="GE_TUITION_AND_FEES_AMOUNT_AY"><?=GE_TUITION_AND_FEES_AMOUNT_AY?></label> 
			</div>


		</div>
	</div>  
	
<!--- End Second column -->

<!-- Begin third column -->
	<div class="col-sm-4 theme-v-border" style="padding-top: 25px;" >
		<div class="row">
			<div class="col-12 col-sm-12 form-group"> 
				
				<!-- <input id="GE_STATE_OF_MAIN_CAMPUS" name="GE_STATE_OF_MAIN_CAMPUS" value="<?//=$GE_STATE_OF_MAIN_CAMPUS?>" type="text"  class="form-control" /> -->
				<select id="GE_STATE_OF_MAIN_CAMPUS" name="GE_STATE_OF_MAIN_CAMPUS" class="form-control">
					<option selected></option>
					<?$res_type1 = $db->Execute("SELECT * FROM `Z_STATES` WHERE `PK_COUNTRY` = 1 AND `ACTIVE` = 1 ORDER BY STATE_NAME ASC ");
					while (!$res_type1->EOF) { ?>
					<option value="<?=$res_type1->fields['STATE_CODE'] ?>" <? if($GE_STATE_OF_MAIN_CAMPUS == $res_type1->fields['STATE_CODE']) echo "selected"; ?>><?=$res_type1->fields['STATE_NAME']?></option>
					<?	$res_type1->MoveNext();
					}
					?>
				</select>
				<label for="GE_STATE_OF_MAIN_CAMPUS"><?=GE_STATE_OF_MAIN_CAMPUS?></label> 
			</div>

			<div class="col-12 col-sm-12 form-group"> 
			<select id="GE_PROGRAM_PS_FOR_LSMC" name="GE_PROGRAM_PS_FOR_LSMC" class="form-control">
				<option value=""></option>						
				<option value="Y" <? if ($GE_PROGRAM_PS_FOR_LSMC == 'Y') echo "selected"; ?>>Yes</option>
				<option value="N" <? if ($GE_PROGRAM_PS_FOR_LSMC == 'N') echo "selected"; ?>>No</option>	
				<option value="X" <? if ($GE_PROGRAM_PS_FOR_LSMC == 'X') echo "selected"; ?>>Not Applicable</option>					
			</select>
				<label for="GE_PROGRAM_PS_FOR_LSMC"><?=GE_PROGRAM_PS_FOR_LSMC?></label> 
			</div>

			<div class="col-12 col-sm-12 form-group"> 
				<select id="GE_STATE_TWO_IN_MSA_MC" name="GE_STATE_TWO_IN_MSA_MC" class="form-control">
					<option selected></option>
					<?$res_type2 = $db->Execute("SELECT * FROM `Z_STATES` WHERE `PK_COUNTRY` = 1 AND `ACTIVE` = 1 ORDER BY STATE_NAME ASC ");
					while (!$res_type2->EOF) { ?>
					<option value="<?=$res_type2->fields['STATE_CODE'] ?>" <? if($GE_STATE_TWO_IN_MSA_MC == $res_type2->fields['STATE_CODE']) echo "selected"; ?>><?=$res_type2->fields['STATE_NAME']?></option>
					<?	$res_type2->MoveNext();
					}
					?>
				</select>
				<label for="GE_STATE_TWO_IN_MSA_MC"><?=GE_STATE_TWO_IN_MSA_MC?></label> 
			</div>
			<div class="col-12 col-sm-12 form-group"> 
			<select id="GE_PROGRAM_PS_FOR_LICENSURE_MSA_STATE_TWO" name="GE_PROGRAM_PS_FOR_LICENSURE_MSA_STATE_TWO" class="form-control">
				<option value=""></option>						
				<option value="Y" <? if ($GE_PROGRAM_PS_FOR_LICENSURE_MSA_STATE_TWO == 'Y') echo "selected"; ?>>Yes</option>
				<option value="N" <? if ($GE_PROGRAM_PS_FOR_LICENSURE_MSA_STATE_TWO == 'N') echo "selected"; ?>>No</option>	
				<option value="X" <? if ($GE_PROGRAM_PS_FOR_LICENSURE_MSA_STATE_TWO == 'X') echo "selected"; ?>>Not Applicable</option>					
			</select>
				<label for="GE_PROGRAM_PS_FOR_LICENSURE_MSA_STATE_TWO"><?=GE_PROGRAM_PS_FOR_LICENSURE_MSA_STATE_TWO?></label> 
			</div>


			<div class="col-12 col-sm-12 form-group"> 
				<select id="GE_STATE_THREE_IN_MSA_MC" name="GE_STATE_THREE_IN_MSA_MC" class="form-control">
					<option selected></option>
					<?$res_type3 = $db->Execute("SELECT * FROM `Z_STATES` WHERE `PK_COUNTRY` = 1 AND `ACTIVE` = 1 ORDER BY STATE_NAME ASC ");
					while (!$res_type3->EOF) { ?>
					<option value="<?=$res_type3->fields['STATE_CODE'] ?>" <? if($GE_STATE_THREE_IN_MSA_MC == $res_type3->fields['STATE_CODE']) echo "selected"; ?>><?=$res_type3->fields['STATE_NAME']?></option>
					<?	$res_type3->MoveNext();
					}
					?>
				</select>

				<label for="GE_STATE_THREE_IN_MSA_MC"><?=GE_STATE_THREE_IN_MSA_MC?></label> 
			</div>
			<div class="col-12 col-sm-12 form-group"> 
			<select id="GE_PROGRAM_PS_FOR_LICENSURE_MSA_STATE_THREE" name="GE_PROGRAM_PS_FOR_LICENSURE_MSA_STATE_THREE" class="form-control">
				<option value=""></option>						
				<option value="Y" <? if ($GE_PROGRAM_PS_FOR_LICENSURE_MSA_STATE_THREE == 'Y') echo "selected"; ?>>Yes</option>
				<option value="N" <? if ($GE_PROGRAM_PS_FOR_LICENSURE_MSA_STATE_THREE == 'N') echo "selected"; ?>>No</option>	
				<option value="X" <? if ($GE_PROGRAM_PS_FOR_LICENSURE_MSA_STATE_THREE == 'X') echo "selected"; ?>>Not Applicable</option>					
			</select>
				<label for="GE_PROGRAM_PS_FOR_LICENSURE_MSA_STATE_THREE"><?=GE_PROGRAM_PS_FOR_LICENSURE_MSA_STATE_THREE?></label> 
			</div>

			<div class="col-12 col-sm-12 form-group"> 				
				<select id="GE_STATE_FOUR_IN_MSA_MC" name="GE_STATE_FOUR_IN_MSA_MC" class="form-control">
					<option selected></option>
					<?$res_type4= $db->Execute("SELECT * FROM `Z_STATES` WHERE `PK_COUNTRY` = 1 AND `ACTIVE` = 1 ORDER BY STATE_NAME ASC ");
					while (!$res_type4->EOF) { ?>
					<option value="<?=$res_type4->fields['STATE_CODE'] ?>" <? if($GE_STATE_FOUR_IN_MSA_MC == $res_type4->fields['STATE_CODE']) echo "selected"; ?>><?=$res_type4->fields['STATE_NAME']?></option>
					<?	$res_type4->MoveNext();
					}
					?>
				</select>
				<label for="GE_STATE_FOUR_IN_MSA_MC"><?=GE_STATE_FOUR_IN_MSA_MC?></label> 
			</div>
			<div class="col-12 col-sm-12 form-group"> 
			<select id="GE_PROGRAM_PS_FOR_LICENSURE_MSA_STATE_FOUR" name="GE_PROGRAM_PS_FOR_LICENSURE_MSA_STATE_FOUR" class="form-control">
				<option value=""></option>						
				<option value="Y" <? if ($GE_PROGRAM_PS_FOR_LICENSURE_MSA_STATE_FOUR == 'Y') echo "selected"; ?>>Yes</option>
				<option value="N" <? if ($GE_PROGRAM_PS_FOR_LICENSURE_MSA_STATE_FOUR == 'N') echo "selected"; ?>>No</option>	
				<option value="X" <? if ($GE_PROGRAM_PS_FOR_LICENSURE_MSA_STATE_FOUR == 'X') echo "selected"; ?>>Not Applicable</option>					
			</select>
				<label for="GE_PROGRAM_PS_FOR_LICENSURE_MSA_STATE_FOUR"><?=GE_PROGRAM_PS_FOR_LICENSURE_MSA_STATE_FOUR?></label> 
			</div>

			<div class="col-12 col-sm-12 form-group"> 
			
				<select id="GE_STATE_FIVE_IN_MSA_MC" name="GE_STATE_FIVE_IN_MSA_MC" class="form-control">
					<option selected></option>
					<?$res_type5= $db->Execute("SELECT * FROM `Z_STATES` WHERE `PK_COUNTRY` = 1 AND `ACTIVE` = 1 ORDER BY STATE_NAME ASC ");
					while (!$res_type5->EOF) { ?>
					<option value="<?=$res_type5->fields['STATE_CODE'] ?>" <? if($GE_STATE_FIVE_IN_MSA_MC == $res_type5->fields['STATE_CODE']) echo "selected"; ?>><?=$res_type5->fields['STATE_NAME']?></option>
					<?	$res_type5->MoveNext();
					}
					?>
				</select>
				<label for="GE_STATE_FIVE_IN_MSA_MC"><?=GE_STATE_FIVE_IN_MSA_MC?></label> 
			</div>
			<div class="col-12 col-sm-12 form-group"> 
			<select id="GE_PROGRAM_PS_FOR_LICENSURE_MSA_STATE_FIVE" name="GE_PROGRAM_PS_FOR_LICENSURE_MSA_STATE_FIVE" class="form-control">
				<option value=""></option>						
				<option value="Y" <? if ($GE_PROGRAM_PS_FOR_LICENSURE_MSA_STATE_FIVE == 'Y') echo "selected"; ?>>Yes</option>
				<option value="N" <? if ($GE_PROGRAM_PS_FOR_LICENSURE_MSA_STATE_FIVE == 'N') echo "selected"; ?>>No</option>	
				<option value="X" <? if ($GE_PROGRAM_PS_FOR_LICENSURE_MSA_STATE_FIVE == 'X') echo "selected"; ?>>Not Applicable</option>					
			</select>
				<label for="GE_PROGRAM_PS_FOR_LICENSURE_MSA_STATE_FIVE"><?=GE_PROGRAM_PS_FOR_LICENSURE_MSA_STATE_FIVE?></label> 
			</div>
			
		</div>		
	</div>
<!--- End third column here -->

</div>
<script type="text/javascript">
// function calculate_days(){
// const startDate = new Date (document.getElementById('DEFAULT_DELINQUENT_START_DATE').value);
// let today = new Date().toISOString().slice(0, 10);
// const endDate    = today;
// const diffInMs   = new Date(endDate) - new Date(startDate)
// const diffInDays = Math.floor(diffInMs / (1000 * 60 * 60 * 24));
// //console.log(diffInDays);
// if(diffInDays >= 0){
// document.getElementById('DEFAULT_DAYS_DELINQUENT').value=(diffInDays + 1);
// document.getElementById('DEFAULT_DAYS_DELINQUENT').focus();
// }else{
// 	//alert("Date Should be less than today's date."+diffInDays);
// 	document.getElementById('DEFAULT_DAYS_DELINQUENT').value='0';
// 	document.getElementById('DEFAULT_DAYS_DELINQUENT').focus();

// }
// }

function get_selectedval(val, ar){
	
		if(val=='W'){
		 var prog_len = ar[1];
		}else if(val=='M'){
		 var prog_len = ar[2];
		}else if(val=='Y'){
		 var prog_len = ar[3];
		}else{
		 var prog_len = ar[0];
		}

		document.getElementById('GE_LENGTH_OF_PROGRAM').value=prog_len;
}
</script>