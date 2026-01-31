

<div class="col-md-2 align-self-center" style="flex: 0 0 12.66667%;max-width: 12.66667%;" >
	<select id="FILTER_ACTIVE_SATATUS" name="FILTER_ACTIVE_SATATUS" class="form-control" onchange="doFilterSearch()">
		<option value="2">Active</option>
		<option value="1">Yes</option>
		<option value="0">No</option>
	</select>
</div>
<div class="col-md-2 align-self-center" >
	<input type="text" class="form-control" id="SEARCH" placeholder=" <?=SEARCH?>" onkeypress="do_search(event)" >
</div>
<div class="col-md-1 align-self-center" >		

	<button type="submit" name="btn" class="btn waves-effect waves-light btn-info"><?=SAVE?></button>
</div>