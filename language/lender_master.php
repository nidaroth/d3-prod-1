<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("LENDER_MASTER_PAGE_TITLE", "Lender");
	define("LENDER", "Lender");
	define("CONTACT", "Contact");
	define("ADDRESS", "Address");
	define("ADDRESS1", "Address 2nd Line");
	define("PHONE", "Phone");
	define("EMAIL", "Email");
	define("CITY", "City");
	define("STATE", "State");
	define("ZIP", "Zip");
	define("COUNTRY", "Country");
	define("LOAN_FEE", "Loan Fee %");
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("LENDER_MASTER_PAGE_TITLE", "Lender");
	define("LENDER", "Lender");
	define("CONTACT", "Contact");
	define("ADDRESS", "Address");
	define("ADDRESS1", "Address 2nd Line");
	define("PHONE", "Phone");
	define("EMAIL", "Email");
	define("CITY", "City");
	define("STATE", "State");
	define("ZIP", "Zip");
	define("COUNTRY", "Country");
	define("LOAN_FEE", "Loan Fee %");  
}