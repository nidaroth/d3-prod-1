<?php 
$onchangefun = 'get_term_from_campus_by_date(' . $REPORT_TYPE . ')';
?>
	<div class="col-md-12" style="padding-bottom:30px;">
		<div class="row">
			<div class="col-md-2" id="PK_CAMPUS_DIV_1" style="max-width:180px !important;">
				<select id="PK_CAMPUS_1" name="PK_CAMPUS_1[]" multiple class="form-control" onchange="<?= $onchangefun ?>"> <? //get_term_from_campus() 
																															?>
					<? $res_type = $db->Execute("select CAMPUS_CODE,PK_CAMPUS,ACTIVE from S_CAMPUS WHERE PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]'  order by ACTIVE DESC,CAMPUS_CODE ASC");
					while (!$res_type->EOF) { ?>
						<option value="<?= $res_type->fields['PK_CAMPUS'] ?>" <? if ($res_type->RecordCount() == 1) echo "selected"; ?> <?php if ($res_type->fields['ACTIVE'] == '0') echo ' style="color : red" ' ?>><?= $res_type->fields['CAMPUS_CODE'] ?> <?php if ($res_type->fields['ACTIVE'] == '0') echo " (Inactive) " ?>
						</option>
					<? $res_type->MoveNext();
					} ?>
				</select>
			</div>
		</div>
	</div>

	<div class="col-md-12">
		<div class="row">
			<?php //if ($REPORT_TYPE == 1) { ?>
				<div class="" id="PK_TERM_MASTER_5_DIV"></div>
				<?php 
				$term_start_date = date('m/d/Y', strtotime("-3 months", strtotime(date('Y-m-d'))));
				$term_end_date = date('m/d/Y', strtotime("+3 months", strtotime(date('Y-m-d'))));
			
				echo  '<div class="col-md-6" style="bottom:21px;max-width:520px">
				<b  style="margin-bottom:5px">&nbsp;</b>
					<div class="d-flex " style="margin-bottom:5px">
					<input type="text" class="form-control date" name="term_begin_start_date" field="term_begin_start_date" id="term_begin_start_date"  placeholder="Course Term Start Date" value="" >
					<input type="text" class="form-control date" name="term_begin_end_date"  field="term_begin_end_date" id="term_begin_end_date"   placeholder="Course Term End Date" value="" >
				</div>
			</div>	
			<div class="col-md-3" style="bottom:21px;max-width:320px; display:none;">
			<b  style="margin-bottom:5px">Term End Date Range</b>
					<div class="d-flex ">
					<input type="text" class="form-control date" name="term_end_start_date" id="term_end_start_date"  placeholder="Start Date">
					<input type="text" class="form-control date" name="term_end_end_date" id="term_end_end_date"   placeholder="End Date">							
			
				</div>
			</div>'; ?>
			<?php //} ?>

			<div class="col-md-2 " id="PK_COURSE_OFFERING_1_DIV">
					<select id="PK_COURSE_OFFERING_1" name="PK_COURSE_OFFERING_1" class="form-control ">
						<option value=""><?= COURSE_OFFERING_PAGE_TITLE ?></option>
					</select>
			</div>
		</div>
	</div>
