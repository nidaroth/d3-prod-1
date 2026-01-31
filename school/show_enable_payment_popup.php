<div class="modal" id="showEnablePaymentPopup" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel1">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="exampleModalLabel1">Diamond Pay</h4>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<div class="col-12 col-sm-12 form-group">
						You've selected Diamond Pay which is an option for your school to add to your Diamond SIS.  If you would like to learn more about Diamond Pay, please click on the following link, which will take you to an informational website. <a href="http://diamondpay.diamondsis.com" target="_blank">Learn More</a>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn waves-effect waves-light btn-dark" onclick="close_enable_payment_popup()" ><?=CLOSE?></button>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
	function show_enable_payment_popup(){
		jQuery(document).ready(function($) {
			$("#showEnablePaymentPopup").modal()
		});
	}
		
	function close_enable_payment_popup(){
		jQuery(document).ready(function($) {
			$("#showEnablePaymentPopup").modal("hide");
		});
	}
</script>