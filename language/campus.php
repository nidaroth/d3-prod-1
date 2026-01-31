<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("CAMPUS_PAGE_TITLE", "Campus");
	define("TAB_GENERAL", "General");
	define("TAB_USER", "User");
	define("OFFICIAL_CAMPUS_NAME", "Official Campus Name");
	define("CAMPUS_NAME", "Campus Name");
	define("CAMPUS_CODE", "Campus Code");
	
	define("INSTITUTION_CODE", "Institution Code(OPEID)");
	define("FEDERAL_SCHOOL_CODE", "Federal School Code");
	define("FA_SCHOOL_CODE", "FA School Code");
	define("AMBASSADOR_SCHOOL_CODE", "Ambassador School Code");
	define("COSMO_LICENSE", "Cosmo License");
	define("REGION", "Region");
	define("ACCSC_SCHOOL_NUMBER", "ACCSC School Number");
	define("ACICS_SCHOOL_NUMBER", "ACICS School Number");
	define("NACCAS_SCHOOL_NUMBER", "NACCAS School Number");
	define("PRIMARY_CAMPUS", "Primary Campus");
	define("DELETE_MESSAGE_USER", "Are you sure you want to delete this user?");
	
	define("TAB_REPORTING", "Reporting");
	define("ACCREDITATION_1", "Accreditation");
	define("CODES", "Codes");
	define("MISCELLANEOUS", "Miscellaneous");
	
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("CAMPUS_PAGE_TITLE", "Instalaciones");
	define("TAB_GENERAL", "General");
	define("TAB_USER", "User");
	define("OFFICIAL_CAMPUS_NAME", "Oficial Instalaciones nombre");
	define("CAMPUS_NAME", "Instalaciones Name");
	define("CAMPUS_CODE", "Instalaciones Código");
	
	define("INSTITUTION_CODE", "Institution Code(OPEID)");
	define("FEDERAL_SCHOOL_CODE", "Federal colegio Código");
	define("FA_SCHOOL_CODE", "FA colegio Code");
	define("AMBASSADOR_SCHOOL_CODE", "Ambassador colegio Código");
	define("COSMO_LICENSE", "Cosmo License");
	define("REGION", "Region");
	define("ACCSC_SCHOOL_NUMBER", "ACCSC colegio Número");
	define("ACICS_SCHOOL_NUMBER", "ACICS colegio Número");
	define("NACCAS_SCHOOL_NUMBER", "NACCAS colegio Número");
	define("PRIMARY_CAMPUS", "Primary Instalaciones");
	define("DELETE_MESSAGE_USER", "Are you sure you want to delete this user?");
}