<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("TICKET_PAGE_TITLE", "My Tickets");
	define("TICKET", "Ticket");
	define("TICKET_NO", "Ticket #");
	define("VIEW_TICKET", "View Ticket");
	define("SUBJECT", "Subject");
	define("PRIORITY", "Priority");
	define("STATUS", "Status");
	define("CHANGE_STATUS", "Change Status");
	define("LAST_UPDATE_ON", "Last Update On");
	define("LAST_UPDATE_BY", "Last Update By");
	define("CREATED_ON", "Created On");
	define("CREATED_BY", "Created By");
	define("REPLY_TO", "Reply To");
	define("REPLY", "Reply");
	define("CREATE", "Create");
	define("TICKET_PRIORITY", "Priority");
	define("MESSAGE", "Message");
	define("CLOSE_DATE", "Close Dt");
	define("FROM", "From");
	define("ATTACHMENTS", "Attachments");
	define("ADD_ATTACHMENTS", "Add Attachments");
	define("ALL_ATTACHMENTS", "All Attachments");
	define("STATUS_HISTORY", "Status History");
	define("SEE_BELOW_FOR_FULL_DEATIL", "See Below the Textbox for Full Details of the Ticket");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("TICKET_PAGE_TITLE", "My Tickets");
	define("TICKET", "Ticket");
	define("TICKET_NO", "Ticket #");
	define("VIEW_TICKET", "View Ticket");
	define("SUBJECT", "Subject");
	define("PRIORITY", "Priority");
	define("STATUS", "Status");
	define("CHANGE_STATUS", "Change Status");
	define("LAST_UPDATE_ON", "Last Update On");
	define("LAST_UPDATE_BY", "Last Update By");
	define("CREATED_ON", "Created On");
	define("CREATED_BY", "Created By");
	define("REPLY_TO", "Reply To");
	define("REPLY", "Reply");
	define("CREATE", "Create");
	define("TICKET_PRIORITY", "Priority");
	define("MESSAGE", "Message");
	define("CLOSE_DATE", "Close Dt");
	define("FROM", "From");
	define("ATTACHMENTS", "Attachments");
	define("ADD_ATTACHMENTS", "Add Attachments");
	define("ALL_ATTACHMENTS", "All Attachments");
	define("STATUS_HISTORY", "Status History");
	define("SEE_BELOW_FOR_FULL_DEATIL", "See Below the Textbox for Full Details of the Ticket");
}