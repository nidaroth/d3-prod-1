<footer class="footer">
	&copy; <?=date("Y") ?> DiamonD SIS
</footer>
<!-- DIAM-2273 -->
<script type="text/javascript">
    window.addEventListener('error', function(event) {
    console.error('Caught an error:', event.message);
    return false;
});
</script>
<!-- DIAM-2273 -->
<div class="modal" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabelLogout">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="exampleModalLabelLogout"><?=LOGOUT?></h4>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body" >
				<?=LOGOUT_MESSAGE?>
			</div>
			<div class="modal-footer">
				<button type="button" onclick="close_logout_popup()" class="btn waves-effect waves-light btn-info"  ><?=I_AM_IN?></button>
			</div>
		</div>
	</div>
</div>
