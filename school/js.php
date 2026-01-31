<script src="../backend_assets/node_modules/jquery/jquery-3.2.1.min.js"></script>
<!-- Bootstrap popper Core JavaScript -->
<script src="../backend_assets/node_modules/popper/popper.min.js"></script>
<script src="../backend_assets/node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
<!-- slimscrollbar scrollbar JavaScript -->
<script src="../backend_assets/dist/js/perfect-scrollbar.jquery.min.js"></script>
<!--Wave Effects -->
<script src="../backend_assets/dist/js/waves.js"></script>
<!--Menu sidebar -->
<script src="../backend_assets/dist/js/sidebarmenu.js"></script>
<!--Custom JavaScript -->
<script src="../backend_assets/dist/js/custom.min.js"></script>
<script src="../backend_assets/node_modules/toast-master/js/jquery.toast.js"></script>

<script src="../backend_assets/node_modules/inputmask/dist/min/jquery.inputmask.bundle.min.js"></script>
<script src="../backend_assets/dist/js/pages/mask.init.js"></script>

<script type="text/javascript">
function set_available(){
	jQuery(document).ready(function($) { 
		var TURN_OFF_ASSIGNMENTS_MASTER = '';
		if(document.getElementById('TURN_OFF_ASSIGNMENTS_MASTER').checked == true)
			TURN_OFF_ASSIGNMENTS_MASTER = 0;
		else
			TURN_OFF_ASSIGNMENTS_MASTER = 1;
			
		var data  = 'TURN_OFF_ASSIGNMENTS_MASTER='+TURN_OFF_ASSIGNMENTS_MASTER
		var value = $.ajax({
			url: "ajax_set_available",	
			type: "POST",		 
			data: data,		
			async: false,
			cache: false,
			success: function (data) {	
				//alert(data)
			}		
		}).responseText;
	});
}

//the interval 'timer' is set as soon as the page loads
var timer  = setInterval(function(){ auto_logout_warning() }, 1200000);
var timer1 = setInterval(function(){ auto_logout_redirect() }, 1260000);
 
// the figure '20000' (20 seconds) indicates how many milliseconds the timer be set to.
//e.g. if you want it to set 5 mins, calculate 5min= 5x60=300 sec => 300,000 milliseconds.
function reset_interval(){
	//first step: clear the existing timer
	clearInterval(timer);
	clearInterval(timer1);
	
	//second step: implement the timer again
	timer  = setInterval(function(){ auto_logout_warning() }, 1200000);
	timer1 = setInterval(function(){ auto_logout_redirect() }, 1260000);
 
}
 
function auto_logout_warning(){
	jQuery(document).ready(function($) {
		$("#logoutModal").modal()
	});
}

function auto_logout_redirect(){
	window.location.href = "../logout?s=1";
}

function close_logout_popup(){
	jQuery(document).ready(function($) {
		$("#logoutModal").modal("hide");
		reset_interval()
	});
}
 
jQuery(document).ready(function($) {
	$("body").mouseover(function() {
		reset_interval()
	});
	
	$("body").click(function() {
		reset_interval()
	});
	
	$("body").dblclick(function() {
		reset_interval()
	});
	
	$("body").keypress(function() {
		reset_interval()
	});
	
	$("body").keypress(function() {
		scroll()
	});
});
/***********notification***********/
var set_notification=true;
var timer_notification  = setInterval(function(){ if(set_notification){ get_new_notification()} }, 60000);
function get_new_notification(){
	jQuery(document).ready(function($) { 	
		var data  = ''
		var value = $.ajax({
			url: "ajax_notification_menu",	
			type: "POST",		 
			data: data,		
			async: true,
			cache: false,
			success: function (data) {	
				/* Ticket # 1241 */
				data = data.split("|||")
				if(data[0] == "a"){
					window.location.href = '../index';
					return false;
				}
				
				document.getElementById('NOTIFICATIONS_li_id').innerHTML = data[1]
				/* Ticket # 1241 */
				
				//alert(data)
			}		
		}).responseText;
	});
}
/***********notification***********/
</script>