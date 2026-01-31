<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("MAIL_TITLE", "My Emails");
	define("EMAIL_TITLE", "Email Details");
	define("MAILBOX", "Internal Messages");
	define("COMPOSE", "Compose");
	define("INBOX", "Inbox");
	define("STARRED", "Starred");
	define("DRAFT", "Draft");
	define("SENT_MAIL", "Sent");
	define("TRASH", "Trash");
	define("COMPOSE", "Compose");
	define("COMPOSE_TITLE", "Compose Mail");
	define("MORE", "More");
	define("MARK_AS_ALL_READ", "Mark as all read");
	define("MARK_AS_ALL_UNREAD", "Mark as all unread");
	define("COMPOSE_NEW_MAIL", "New Message");
	define("TO", "To:");
	define("SUBJECT", "Subject:");
	define("ATTACHMENT", "Attachment");
	define("DISCARD", "Discard");
	define("MARK_AS_READ", "Mark as read");
	define("MARK_AS_UNREAD", "Mark as unread");
	define("REPLY", "Reply");
	define("FORWARD", "Forward");
	define("SAVE_AS_DRAFT", "Save as Draft");
	define("MOVE_TO_INVOICE", "Move To Inbox");
	define("DELETE_MESSAGE_MAIL", "Are you sure you want to Delete this Mail?");
	define("FROM", "From");
	define("TO_1", "To");
	define("DATE_TIME", "Date and Time");
	
	define("STUDENT_STATUS", "Student Status");
	define("FIRST_TERM_DATE", "First Term Date");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("MAIL_TITLE", "My Emails");
	define("EMAIL_TITLE", "Email Details");
	define("MAILBOX", "Internal Messages");
	define("COMPOSE", "Compose");
	define("INBOX", "Inbox");
	define("STARRED", "Starred");
	define("DRAFT", "Draft");
	define("SENT_MAIL", "Sent");
	define("TRASH", "Trash");
	define("COMPOSE", "Compose");
	define("COMPOSE_TITLE", "Compose Mail");
	define("MORE", "More");
	define("MARK_AS_ALL_READ", "Mark as all read");
	define("MARK_AS_ALL_UNREAD", "Mark as all unread");
	define("COMPOSE_NEW_MAIL", "New Message");
	define("TO", "To:");
	define("SUBJECT", "Subject:");
	define("ATTACHMENT", "Attachment");
	define("DISCARD", "Discard");
	define("MARK_AS_READ", "Mark as read");
	define("MARK_AS_UNREAD", "Mark as unread");
	define("REPLY", "Reply");
	define("FORWARD", "Forward");
	define("SAVE_AS_DRAFT", "Save as Draft");
	define("MOVE_TO_INVOICE", "Move To Inbox");
	define("DELETE_MESSAGE_MAIL", "Are you sure you want to Delete this Mail?");
	define("FROM", "From");
	define("TO_1", "To");
	define("DATE_TIME", "Date and Time");
	define("STUDENT_STATUS", "Student Status");
	define("FIRST_TERM_DATE", "First Term Date");
}