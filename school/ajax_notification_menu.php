<? require_once("../global/config.php"); 
require_once("../language/menu.php");

/* Ticket # 1241 */
if($_SESSION['ADMIN_PK_ROLES'] != 1){
	$res_login_session = $db->Execute("SELECT LOGIN_SESSION_ID FROM Z_USER WHERE PK_USER = '$_SESSION[PK_USER]'  AND PK_ACCOUNT = '$_SESSION[PK_ACCOUNT]' "); 
	if($res_login_session->fields['LOGIN_SESSION_ID'] != $_SESSION['LOGIN_SESSION_ID'] && $_SESSION['PK_ROLES'] != 1 ){
		echo "a|||";
	} else
		echo "b|||";
}
/* Ticket # 1241 */

$res_noti = $db->Execute("select PK_NOTIFICATION_RECIPIENTS,TEXT,LINK,EVENT_TYPE from Z_NOTIFICATION LEFT JOIN S_EVENT_TEMPLATE ON S_EVENT_TEMPLATE.PK_EVENT_TEMPLATE = Z_NOTIFICATION.PK_EVENT_TEMPLATE LEFT JOIN Z_EVENT_TYPE ON Z_EVENT_TYPE.PK_EVENT_TYPE = S_EVENT_TEMPLATE.PK_EVENT_TYPE, Z_NOTIFICATION_RECIPIENTS WHERE NOTIFICATION_TO_ACCOUNT = '$_SESSION[PK_ACCOUNT]' AND Z_NOTIFICATION.PK_NOTIFICATION = Z_NOTIFICATION_RECIPIENTS.PK_NOTIFICATION AND Z_NOTIFICATION_RECIPIENTS.PK_EMPLOYEE_MASTER = '$_SESSION[PK_EMPLOYEE_MASTER]' AND NOTI_READ = 0 ORDER BY PK_NOTIFICATION_RECIPIENTS DESC"); ?>
		
<a class="nav-link dropdown-toggle waves-effect waves-dark" href="" id="2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
	<i class="icon-note"></i>
	<div class="notify"> 
		<? if($res_noti->RecordCount() > 0) { ?> 
			<span class="heartbit" ></span> <span class="point"></span>
		<? } ?>
	</div>
</a>
<div class="dropdown-menu mailbox dropdown-menu-right animated bounceInDown" aria-labelledby="2">
	<ul>
		<li>
			<div class="drop-title"><?=MNU_NOTIFICATIONS?></div>
		</li>
		<li>
			<div class="message-center">
			<? if($res_noti->RecordCount() == 0) { ?>
				<a href="javascript:void(0)">
					<div class="mail-contnet">
						<h5><?=NO_NOTIFICATION?></h5>
					</div>
				</a>
			<? } else {
				while (!$res_noti->EOF) { ?>
					<a href="set_notification_as_read?id=<?=$res_noti->fields['PK_NOTIFICATION_RECIPIENTS']?>">
						<div class="mail-contnet">
							<h5><?=$res_noti->fields['EVENT_TYPE']?></h5>
							<span class="mail-desc" style="overflow:none" ><?=$res_noti->fields['TEXT']?></span>
						</div>
					</a>
				<? $res_noti->MoveNext();
				} 
			} ?>
			</div>
		</li>
		<!--<li>
			<a class="nav-link text-center link" href="javascript:void(0);"> <strong>See all Notifications</strong> <i class="fa fa-angle-right"></i> </a>
		</li>-->
	</ul>
</div>