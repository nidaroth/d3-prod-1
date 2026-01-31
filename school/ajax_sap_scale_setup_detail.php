<? require_once("../global/config.php"); 
require_once("../language/common.php");
require_once("../language/sap_scale.php");
require_once("../language/menu.php");
require_once("check_access.php");

if(check_access('SETUP_REGISTRAR') == 0 ){
	header("location:../index");
	exit;
}

$count_id 				= $_REQUEST['count_id'];
$TYPE 					= $_REQUEST['TYPE'];
$table_id				= $_REQUEST['table_id'];
$PK_SAP_SCALE_DETAIL 	= $_REQUEST['PK_SAP_SCALE_DETAIL'];

if($PK_SAP_SCALE_DETAIL == ''){
	$PERIOD  								= '';
	$PROGRAM_PERCENTAGE  					= '';
	$PK_SAP_WARNING  						= '';

	$HOURS_COMPLETED_HOURS_SCHEDULED  		= '';
	$HOURS_COMPLETED_PROGRAM_HOURS  		= '';
	$HOURS_SCHEDULED_PROGRAM_HOURS  		= '';
    
    $SCHEDULE_HOURS_UNITS = '';
    $ABSENT_HOURS_UNITS = '';

	$FA_UNITS_COMPLETED_PROGRAM_ATTEMPTED  	= '';
	$FA_UNITS_COMPLETED_PROGRAM_FA  	    = '';
	$FA_UNITS_ATTEMPTED_PROGRAM_FA  	    = '';

	$STD_UNITS_COMPLETED_ATTEMPTED_UNITS  	= '';
	$STD_UNITS_COMPLETED_PROGRAM_UNITS  	= '';
	$STD_UNITS_ATTEMPTED_PROGRAM_UNITS  	= '';

	$GPA_CUMULATIVE_UNITS  				    = '';

	$PERIOD_HOURS_COMPLETED_SCHEDULED_INC  		    = '0';
	$PERIOD_STANDARD_UNITS_COMPLETED_ATTEMPTED_INC  = '0';
	$PERIOD_FA_UNITS_COMPLETED_ATTEMPTED_INC  		= '0';
	$PERIOD_GPA_INC  			                    = '0';
} else {
	$res_det1 = $db->Execute("select * from S_SAP_SCALE_SETUP_DETAIL WHERE PK_SAP_SCALE_DETAIL = '$PK_SAP_SCALE_DETAIL' AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' ");	
	$PERIOD  								= $res_det1->fields['PERIOD'];
	$PROGRAM_PERCENTAGE  					= $res_det1->fields['PROGRAM_PACE_PERCENTAGE'];
	$PK_SAP_WARNING  						= $res_det1->fields['PK_SAP_WARNING'];

	$HOURS_COMPLETED_HOURS_SCHEDULED  		= $res_det1->fields['CUMULATIVE_HOURS_COMPLETED_SCHEDULED'];
	$HOURS_COMPLETED_PROGRAM_HOURS  		= $res_det1->fields['CUMULATIVE_HOURS_COMPLETED_PROGRAM'];
	$HOURS_SCHEDULED_PROGRAM_HOURS  		= $res_det1->fields['CUMULATIVE_HOURS_SCHEDULED_PROGRAM'];
    
    $SCHEDULE_HOURS_UNITS = $res_det1->fields['CUMULATIVE_SCHEDULE_HOURS'];
    $ABSENT_HOURS_UNITS = $res_det1->fields['CUMULATIVE_ABSENT_HOURS'];


	$FA_UNITS_COMPLETED_PROGRAM_ATTEMPTED  	= $res_det1->fields['CUMULATIVE_FA_UNITS_COMPLETED_ATTEMPTED'];
	$FA_UNITS_COMPLETED_PROGRAM_FA  	    = $res_det1->fields['CUMULATIVE_FA_UNITS_COMPLETED_PROGRAM'];
	$FA_UNITS_ATTEMPTED_PROGRAM_FA  	    = $res_det1->fields['CUMULATIVE_FA_UNITS_ATTEMPTED_PROGRAM'];

	$STD_UNITS_COMPLETED_ATTEMPTED_UNITS  	= $res_det1->fields['CUMULATIVE_UNITS_COMPLETED_ATTEMPTED'];
	$STD_UNITS_COMPLETED_PROGRAM_UNITS 		= $res_det1->fields['CUMULATIVE_UNITS_COMPLETED_PROGRAM'];
	$STD_UNITS_ATTEMPTED_PROGRAM_UNITS      = $res_det1->fields['CUMULATIVE_UNITS_ATTEMPTED_PROGRAM'];

	$GPA_CUMULATIVE_UNITS  				    = $res_det1->fields['CUMULATIVE_GPA'];
    
	$PERIOD_HOURS_COMPLETED_SCHEDULED_INC  		    = $res_det1->fields['PERIOD_HOURS_COMPLETED_SCHEDULED'];
	$PERIOD_STANDARD_UNITS_COMPLETED_ATTEMPTED_INC  = $res_det1->fields['PERIOD_UNITS_COMPLETED_ATTEMPTED'];
	$PERIOD_FA_UNITS_COMPLETED_ATTEMPTED_INC  	    = $res_det1->fields['PERIOD_FA_UNITS_COMPLETED_ATTEMPTED'];
	$PERIOD_GPA_INC  			                    = $res_det1->fields['PERIOD_GPA'];
}
?>
<tr id="detail_table_<?=$count_id?>" >
	<td >
		<input type="hidden" name="PK_SAP_SCALE_DETAIL[]" id="PK_SAP_SCALE_DETAIL" value="<?=$PK_SAP_SCALE_DETAIL?>" />
		<input type="text" readonly class="form-control <?=$table_id?>" placeholder="" name="PERIOD[]" id="PERIOD_<?=$count_id?>" value="<?=$PERIOD?>"  />
	</td>
	<td>
		<input type="text" class="form-control" placeholder="" name="PROGRAM_PERCENTAGE[]" id="PROGRAM_PERCENTAGE_<?=$count_id?>" value="<?=$PROGRAM_PERCENTAGE?>" onfocusout="higherThanBefore('PROGRAM_PERCENTAGE_<?=$count_id?>','<?=$count_id?>')" onblur="format_number_1('PROGRAM_PERCENTAGE_<?=$count_id?>')" />
	</td>
	<td>
		<select id="PK_SAP_WARNING_<?=$count_id?>" name="PK_SAP_WARNING[]" class="form-control">
			<option></option>
			<? $res_type = $db->Execute("select PK_SAP_WARNING,SAP_WARNING from S_SAP_WARNING WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND ACTIVE = 1 order by SAP_WARNING ASC");
			while (!$res_type->EOF) { ?>
				<option value="<?=$res_type->fields['PK_SAP_WARNING']?>" <? if($res_type->fields['PK_SAP_WARNING'] == $PK_SAP_WARNING) echo "selected"; ?> ><?=$res_type->fields['SAP_WARNING'] ?></option>
			<?	$res_type->MoveNext();
			} ?>
		</select>
	</td>
	
	<td>
		<div class="col-md-12 input-group" style="padding:0px 7px 0px 8px;" >
			<input type="text" class="form-control" placeholder="" name="HOURS_COMPLETED_HOURS_SCHEDULED[]" id="HOURS_COMPLETED_HOURS_SCHEDULED_<?=$count_id?>" value="<?=$HOURS_COMPLETED_HOURS_SCHEDULED?>"  onblur="format_number_1('HOURS_COMPLETED_HOURS_SCHEDULED_<?=$count_id?>')" />
			<div class="input-group-prepend">
				<span class="input-group-text" style="padding: 5px 7px 5px 4px;height: 38px;border-top-right-radius: 0.25rem;border-bottom-right-radius: 0.25rem;">%</span>
			</div>
		</div>
	</td>
	<td>
		<div class="col-md-12 input-group" style="padding:0px 7px 0px 8px;" >
			<input type="text" class="form-control" placeholder="" name="HOURS_COMPLETED_PROGRAM_HOURS[]" id="HOURS_COMPLETED_PROGRAM_HOURS_<?=$count_id?>" value="<?=$HOURS_COMPLETED_PROGRAM_HOURS?>" onblur="format_number_1('HOURS_COMPLETED_PROGRAM_HOURS_<?=$count_id?>')" />
			<div class="input-group-prepend">
				<span class="input-group-text" style="padding: 5px 7px 5px 4px;height: 38px;border-top-right-radius: 0.25rem;border-bottom-right-radius: 0.25rem;">%</span>
			</div>
		</div>
	</td>
	
	<td>
		<div class="col-md-12 input-group" style="padding:0px 7px 0px 8px;" >
			<input type="text" class="form-control" placeholder="" name="HOURS_SCHEDULED_PROGRAM_HOURS[]" id="HOURS_SCHEDULED_PROGRAM_HOURS_<?=$count_id?>" value="<?=$HOURS_SCHEDULED_PROGRAM_HOURS?>"  onblur="format_number_1('HOURS_SCHEDULED_PROGRAM_HOURS_<?=$count_id?>')" />
			<div class="input-group-prepend">
				<span class="input-group-text" style="padding: 5px 7px 5px 4px;height: 38px;border-top-right-radius: 0.25rem;border-bottom-right-radius: 0.25rem;">%</span>
			</div>
		</div>
	</td>

    <td>
        <div class="col-md-12" style="padding:0px 7px 0px 8px;">
            <input type="text" class="form-control" placeholder=""
                   name="SCHEDULE_HOURS_UNITS[]"
                   id="SCHEDULE_HOURS_UNITS_<?=$count_id?>"
                   value="<?=$SCHEDULE_HOURS_UNITS?>"
                   onblur="format_number_1('SCHEDULE_HOURS_UNITS_<?=$count_id?>')" />
        </div>
    </td>
    <td style="padding: 5px 5px 5px 5px;">
        <div class="col-md-12" style="padding:0px 7px 0px 8px;">
            <input type="text" class="form-control" placeholder=""
                   name="ABSENT_HOURS_UNITS[]"
                   id="ABSENT_HOURS_UNITS_<?=$count_id?>"
                   value="<?=$ABSENT_HOURS_UNITS?>"
                   onblur="format_number_1('ABSENT_HOURS_UNITS_<?=$count_id?>')" />
        </div>
    </td>
	<td>
        <?
        if($PERIOD_HOURS_COMPLETED_SCHEDULED_INC == 1)
        {
            ?>
                <div class="include_hours" style="text-align: center;">Yes</div>
            <?
        }
        else {
            ?>
            <div class="include_hours" style="text-align: center;">No</div>
            <?
        }
        ?>
		<input type="hidden" class="form-control include_hours_chk" name="PERIOD_HOURS_COMPLETED_SCHEDULED_INC[]" id="PERIOD_HOURS_COMPLETED_SCHEDULED_INC_<?=$count_id?>" value="<?=$PERIOD_HOURS_COMPLETED_SCHEDULED_INC?>" />
	</td>
	<td>
        <div class="col-md-12 input-group" style="padding:0px 7px 0px 8px;" >
		    <input type="text" class="form-control" placeholder="" name="FA_UNITS_COMPLETED_PROGRAM_ATTEMPTED[]" id="FA_UNITS_COMPLETED_PROGRAM_ATTEMPTED_<?=$count_id?>" value="<?=$FA_UNITS_COMPLETED_PROGRAM_ATTEMPTED?>" onblur="format_number_1('FA_UNITS_COMPLETED_PROGRAM_ATTEMPTED_<?=$count_id?>')" />
			<div class="input-group-prepend">
				<span class="input-group-text" style="padding: 5px 7px 5px 4px;height: 38px;border-top-right-radius: 0.25rem;border-bottom-right-radius: 0.25rem;">%</span>
			</div>
        </div>
	</td>
	<td>
        <div class="col-md-12 input-group" style="padding:0px 7px 0px 8px;" >
		    <input type="text" class="form-control" placeholder="" name="FA_UNITS_COMPLETED_PROGRAM_FA[]" id="FA_UNITS_COMPLETED_PROGRAM_FA_<?=$count_id?>" value="<?=$FA_UNITS_COMPLETED_PROGRAM_FA?>" onblur="format_number_1('FA_UNITS_COMPLETED_PROGRAM_FA_<?=$count_id?>')" />
			<div class="input-group-prepend">
				<span class="input-group-text" style="padding: 5px 7px 5px 4px;height: 38px;border-top-right-radius: 0.25rem;border-bottom-right-radius: 0.25rem;">%</span>
			</div>
        </div>
	</td>
	<td>
		<div class="col-md-12 input-group" style="padding:0px 7px 0px 8px;" >
			<input type="text" class="form-control" placeholder="" name="FA_UNITS_ATTEMPTED_PROGRAM_FA[]" id="FA_UNITS_ATTEMPTED_PROGRAM_FA_<?=$count_id?>" value="<?=$FA_UNITS_ATTEMPTED_PROGRAM_FA?>" onblur="format_number_1('FA_UNITS_ATTEMPTED_PROGRAM_FA_<?=$count_id?>')" />
			<div class="input-group-prepend">
				<span class="input-group-text" style="padding: 5px 7px 5px 4px;height: 38px;border-top-right-radius: 0.25rem;border-bottom-right-radius: 0.25rem;">%</span>
			</div>
		</div>
	</td>	
	<td>
        <?
        if($PERIOD_FA_UNITS_COMPLETED_ATTEMPTED_INC == 1)
        {
            ?>
                <div class="include_fa" style="text-align: center;">Yes</div>
            <?
        }
        else {
            ?>
           <div class="include_fa" style="text-align: center;">No</div>
            <?
        }
        ?>
		<input type="hidden" class="form-control include_fa_chk" name="PERIOD_FA_UNITS_COMPLETED_ATTEMPTED_INC[]" id="PERIOD_FA_UNITS_COMPLETED_ATTEMPTED_INC_<?=$count_id?>" value="<?=$PERIOD_FA_UNITS_COMPLETED_ATTEMPTED_INC?>" />
	</td>
	<td>
        <div class="col-md-12 input-group" style="padding:0px 7px 0px 8px;" >
		    <input type="text" class="form-control" placeholder="" name="STD_UNITS_COMPLETED_ATTEMPTED_UNITS[]" id="STD_UNITS_COMPLETED_ATTEMPTED_UNITS_<?=$count_id?>" value="<?=$STD_UNITS_COMPLETED_ATTEMPTED_UNITS?>" onblur="format_number_1('STD_UNITS_COMPLETED_ATTEMPTED_UNITS_<?=$count_id?>')" />
			<div class="input-group-prepend">
				<span class="input-group-text" style="padding: 5px 7px 5px 4px;height: 38px;border-top-right-radius: 0.25rem;border-bottom-right-radius: 0.25rem;">%</span>
			</div>
        </div>
	</td>
	<td>
        <div class="col-md-12 input-group" style="padding:0px 7px 0px 8px;" >
		    <input type="text" class="form-control" placeholder="" name="STD_UNITS_COMPLETED_PROGRAM_UNITS[]" id="STD_UNITS_COMPLETED_PROGRAM_UNITS_<?=$count_id?>" value="<?=$STD_UNITS_COMPLETED_PROGRAM_UNITS?>" onblur="format_number_1('STD_UNITS_COMPLETED_PROGRAM_UNITS_<?=$count_id?>')" />
			<div class="input-group-prepend">
				<span class="input-group-text" style="padding: 5px 7px 5px 4px;height: 38px;border-top-right-radius: 0.25rem;border-bottom-right-radius: 0.25rem;">%</span>
			</div>
        </div>
	</td>
	<td>
        <div class="col-md-12 input-group" style="padding:0px 7px 0px 8px;" >
		    <input type="text" class="form-control" placeholder="" name="STD_UNITS_ATTEMPTED_PROGRAM_UNITS[]" id="STD_UNITS_ATTEMPTED_PROGRAM_UNITS_<?=$count_id?>" value="<?=$STD_UNITS_ATTEMPTED_PROGRAM_UNITS?>" onblur="format_number_1('STD_UNITS_ATTEMPTED_PROGRAM_UNITS_<?=$count_id?>')" />
			<div class="input-group-prepend">
				<span class="input-group-text" style="padding: 5px 7px 5px 4px;height: 38px;border-top-right-radius: 0.25rem;border-bottom-right-radius: 0.25rem;">%</span>
			</div>
        </div>
	</td>	
    <td>
        <?
        if($PERIOD_STANDARD_UNITS_COMPLETED_ATTEMPTED_INC == 1)
        {
            ?>
                <div class="include_stand" style="text-align: center;">Yes</div>
            <?
        }
        else {
            ?>
           <div class="include_stand" style="text-align: center;">No</div>
            <?
        }
        ?>
		<input type="hidden" class="form-control include_stand_chk" name="PERIOD_STANDARD_UNITS_COMPLETED_ATTEMPTED_INC[]" id="PERIOD_STANDARD_UNITS_COMPLETED_ATTEMPTED_INC_<?=$count_id?>" value="<?=$PERIOD_STANDARD_UNITS_COMPLETED_ATTEMPTED_INC?>" />
	</td>
    <td>
        <div class="col-md-12 input-group" style="padding:0px 7px 0px 8px;" >
		    <input type="text" class="form-control" placeholder="" name="GPA_CUMULATIVE_UNITS[]" id="GPA_CUMULATIVE_UNITS_<?=$count_id?>" value="<?=$GPA_CUMULATIVE_UNITS?>" onblur="format_number_1('GPA_CUMULATIVE_UNITS_<?=$count_id?>')" />
			<div class="input-group-prepend">
				<span class="input-group-text" style="padding: 5px 7px 5px 4px;height: 38px;border-top-right-radius: 0.25rem;border-bottom-right-radius: 0.25rem;">%</span>
			</div>
        </div>
	</td>
	<td>
        <?
        if($PERIOD_GPA_INC == 1)
        {
            ?>
                <div class="include_gpa" style="text-align: center;">Yes</div>
            <?
        }
        else {
            ?>
           <div class="include_gpa" style="text-align: center;">No</div>
            <?
        }
        ?>
		<input type="hidden" class="form-control include_gpa_chk" name="PERIOD_GPA_INC[]" id="PERIOD_GPA_INC_<?=$count_id?>" value="<?=$PERIOD_GPA_INC?>" />
	</td>
	<td>
		<div style="width:80px;" >
			<a href="javascript:void(0);" onclick="delete_row('<?=$count_id?>')" title="<?=DELETE?>" class="btn delete-color btn-circle"><i class="far fa-trash-alt"></i> </a>
		</div>
	</td>
</tr>
