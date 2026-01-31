<div class="card-body inbox-panel">
	<a href="compose_mail" class="btn btn-danger m-b-20 p-10 btn-block waves-effect waves-light"><?=COMPOSE?></a>
	<ul class="list-group list-group-full">
		<li class="list-group-item <? if($_GET['type'] == '') echo "active"; ?> "> 
			<a href="my_mails"><i class="fas fa-envelope"></i> <?=INBOX?> </a>
			<? if($menu_ib_count->RecordCount() > 0){ ?>
			<span class="badge badge-success ml-auto"><?=$menu_ib_count->RecordCount()?></span>
			<? } ?>
		</li>
		<li class="list-group-item <? if($_GET['type'] == 'starred') echo "active"; ?> ">
			<a href="my_mails?type=starred"> <i class="mdi mdi-star"></i> <?=STARRED?> </a>
		</li>
		<li class="list-group-item <? if($_GET['type'] == 'draft') echo "active"; ?> ">
			<? $menu_ib_count = $db->Execute("SELECT PK_INTERNAL_EMAIL FROM Z_INTERNAL_EMAIL WHERE CREATED_BY = '$_SESSION[PK_USER]' AND DRAFT = 1 "); ?>
			<a href="my_mails?type=draft"> <i class="mdi mdi-send"></i> <?=DRAFT?> </a>
			<? if($menu_ib_count->RecordCount() > 0){ ?>
			<span class="badge badge-danger ml-auto"><?=$menu_ib_count->RecordCount()?></span>
			<? } ?>
		</li>
		<li class="list-group-item <? if($_GET['type'] == 'sent') echo "active"; ?> ">
			<a href="my_mails?type=sent"> <i class="mdi mdi-file-document-box"></i> <?=SENT_MAIL?> </a>
		</li>
		<!--<li class="list-group-item <? if($_GET['type'] == 'trash') echo "active"; ?> ">
			<a href="my_mails?type=trash"> <i class="mdi mdi-delete"></i> <?=TRASH?> </a>
		</li>-->
	</ul>
</div>