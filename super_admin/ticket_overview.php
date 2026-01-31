<? require_once("../global/config.php"); 
if($_SESSION['ADMIN_PK_USER'] == 0 || $_SESSION['ADMIN_PK_USER'] == '' || $_SESSION['ADMIN_PK_ROLES'] != 1 ){ 
	header("location:../index");
	exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
	<? require_once("css.php"); ?>
	<title>Ticket Overview | <?=$title?></title>
</head>

<body class="horizontal-nav boxed skin-megna fixed-layout">
   <? require_once("pre_load.php"); ?>
    <div id="main-wrapper">
       <? require_once("menu.php"); ?>
        <div class="page-wrapper">
            <div class="container-fluid">
                 <div class="row page-titles">
                    <div class="col-md-5 align-self-center">
                        <h4 class="text-themecolor">Ticket Overview </h4>
                    </div>
					<div class="col-md-7" style="text-align:right" >
						<a href="manage_ticket" class="btn btn-info m-l-15"> List</a>
					</div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                
								<div class="row">
									<? $res_status = $db->Execute("SELECT PK_TICKET_STATUS,TICKET_STATUS FROM Z_TICKET_STATUS WHERE ACTIVE = 1 ORDER BY FIELD(PK_TICKET_STATUS,1,8,2,9,10,3) "); 
									while (!$res_status->EOF){ 
										$PK_TICKET_STATUS = $res_status->fields['PK_TICKET_STATUS']; ?>
										<div class="col-md-3">
											<div class="card">
												<div class="card-header" style="background-color: #3A87AD;" >
													<h4 class="card-title" style="margin-bottom: 0;color:#FFF" ><?=$res_status->fields['TICKET_STATUS']?></h4>
												</div>
												<div class="card-body" style="background-color: #A3C1D0;">
													<? $total = 0;
													$cond = "";
													if($PK_TICKET_STATUS == 3)
														$cond = " AND CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,S_EMPLOYEE_MASTER.LAST_NAME) != '' ";
													$res_ticket = $db->Execute("SELECT COUNT(PK_TICKET) AS NO,Z_TICKET.CREATED_BY, CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME) AS NAME FROM Z_TICKET LEFT JOIN Z_USER ON Z_USER.PK_USER = Z_TICKET.CREATED_BY LEFT JOIN S_EMPLOYEE_MASTER ON S_EMPLOYEE_MASTER.PK_EMPLOYEE_MASTER = Z_USER.ID WHERE IS_PARENT = 1 AND PK_TICKET_STATUS = '$PK_TICKET_STATUS' $cond GROUP BY Z_TICKET.CREATED_BY ORDER BY CONCAT(S_EMPLOYEE_MASTER.FIRST_NAME,' ',S_EMPLOYEE_MASTER.LAST_NAME) ");
													while (!$res_ticket->EOF){ 
														$total += $res_ticket->fields['NO'] ?>
														<div class="row">
															<div class="col-md-9">
																<a href="manage_ticket?st=<?=$PK_TICKET_STATUS?>&c=<?=$res_ticket->fields['CREATED_BY']?>&n=<?=$res_ticket->fields['NAME']?>"style="color:#FFF" ><?=$res_ticket->fields['NAME']?></a>
															</div>
															<div class="col-md-3" style="text-align:right;" >
																<a href="manage_ticket?st=<?=$PK_TICKET_STATUS?>&c=<?=$res_ticket->fields['CREATED_BY']?>&n=<?=$res_ticket->fields['NAME']?>"style="color:#FFF" ><?=$res_ticket->fields['NO']?></a>
															</div>
														</div>
													<?	$res_ticket->MoveNext();
													} ?>
													<hr />
													<div class="row">
														<div class="col-md-9" style="font-weight:bold">
															<a href="manage_ticket?st=<?=$PK_TICKET_STATUS?>" style="color:#FFF" >Total</a>
														</div>
														<div class="col-md-3" style="text-align:right;font-weight:bold">
															<a href="manage_ticket?st=<?=$PK_TICKET_STATUS?>&c=<?=$res_ticket->fields['CREATED_BY']?>&n="style="color:#FFF" ><?=$total?></a>
														</div>
													</div>
												</div>
											</div>
										</div>
									<? $res_status->MoveNext();
									} ?>
								</div>
                            </div>
                        </div>
					</div>
				</div>
				
            </div>
        </div>
        <? require_once("footer.php"); ?>
    </div>
   
	<? require_once("js.php"); ?>
</body>

</html>