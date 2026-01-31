<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("NOTES_PAGE_TITLE", "Notes");
	define("EVENT_PAGE_TITLE", "Events");
	define("EVENT_TYPE", "Event Type");
	define("NOTES_TYPE", "Note Type");
	define("COMMENTS", "Comments");
	define("NOTE_STATUS", "Note Status");
	define("NOTES_PRIORITY", "Note Priority");
	define("FOLLOWUP_DATE", "Follow Up Date");
	define("IS_EVENT", "Is Event");
	define("ADD_ATTACHMENTS", "Add Attachments");
	define("DOCUMENT", "Document");
	define("EMPLOYEE", "Employee");
	define("COMPLETE", "Complete");
	define("NOTE_DATE", "Note Date");
	define("NOTE_TIME", "Note Time");
	define("EVENT_STATUS", "Event Status");
	define("EVENT_DATE", "Event Date");
	define("EVENT_TIME", "Event Time");
	define("EVENT_OTHER", "Event Other");
	define("SHOW_ON_ALL_DEP", "Show On All Departments");
	define("RECURRING_TYPE", "Recurring Type");
	define("NO_OF_TIMES", "No. of Times");
	
	define("FROM_NOTE_DATE", "From Note Date");
	define("TO_NOTE_DATE", "To Note Date");
	define("FROM_EVENT_DATE", "From Event Date");
	define("TO_EVENT_DATE", "To Event Date");
	define("FROM_FOLLOWUP_DATE", "From Follow Up Date");
	define("TO_FOLLOWUP_DATE", "To Follow Up Date");
	
	define("NOTE_COMPLETED", "Note Completed");
	define("EVENT_COMPLETED", "Event Completed");
	define("SELECTED_COUNT", "Select Count");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("NOTES_PAGE_TITLE", "Notes");
	define("EVENT_PAGE_TITLE", "Events");
	define("EVENT_TYPE", "Event Type");
	define("NOTES_TYPE", "Note Type");
	define("COMMENTS", "Comments");
	define("NOTE_STATUS", "Note Status");
	define("NOTES_PRIORITY", "Note Priority");
	define("FOLLOWUP_DATE", "Follow Up");
	define("IS_EVENT", "Is Event");
	define("ADD_ATTACHMENTS", "Add Attachments");
	define("DOCUMENT", "Document");
	define("EMPLOYEE", "Employee");
	define("COMPLETE", "Complete");
	define("NOTE_DATE", "Note Date");
	define("NOTE_TIME", "Note Time");
	define("EVENT_STATUS", "Event Status");
	define("EVENT_DATE", "Event Date");
	define("EVENT_TIME", "Event Time");
	define("EVENT_OTHER", "Event Other");
	define("SHOW_ON_ALL_DEP", "Show On All Departments");
	define("RECURRING_TYPE", "Recurring Type");
	define("NO_OF_TIMES", "No. of Times");
	define("FROM_NOTE_DATE", "From Note Date");
	define("TO_NOTE_DATE", "To Note Date");
	define("FROM_EVENT_DATE", "From Event Date");
	define("TO_EVENT_DATE", "To Event Date");
	
	define("FROM_FOLLOWUP_DATE", "From Follow Up Date");
	define("TO_FOLLOWUP_DATE", "To Follow Up Date");
	define("NOTE_COMPLETED", "Note Completed");
	define("EVENT_COMPLETED", "Event Completed");
	define("SELECTED_COUNT", "Select Count");
	
}