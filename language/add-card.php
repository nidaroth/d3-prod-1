<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("ADD_CARD_TITLE", "Add/Edit Card");
	define("NAME_ON_CARD", "Name On Card");
	define("CARD_NO", "Card #");
	define("CARD_EXP", "Card Exp [MM/YY]");
	define("CVV", "CVV");
	define("CARD_TYPE", "Card Type");
	define("CARD_ON_FILE", "Card On File");
	define("DELETE_CARD", "Delete Card");
	define("DELETE_CONFIRMATION_MSG", "Are you sure you want to Delete this Card?");
	define("IS_PRIMARY", "Is Primary");
	define("SET_AS_PRIMARY", "Set as primary");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("ADD_CARD_TITLE", "Add/Edit Card");
	define("NAME_ON_CARD", "Name On Card");
	define("CARD_NO", "Card #");
	define("CARD_EXP", "Card Exp [MM/YY]");
	define("CVV", "CVV");
	define("CARD_TYPE", "Card Type");
	define("CARD_ON_FILE", "Card On File");
	define("DELETE_CONFIRMATION_MSG", "Are you sure you want to Delete this Card?");
	define("IS_PRIMARY", "Is Primary");
	define("SET_AS_PRIMARY", "Set as primary");
}