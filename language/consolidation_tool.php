<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("SELECT_CONSOLIDATION_TOOL", "Select Consolidation Type");
	define("OLD_VALUE", "Old Value(To Be Deleted)");
	define("NEW_VALUE", "New Value(To Keep)");
	define("WARNING", "<span style='color:red'>WARNING: All tables with the old value selected will be replaced with the new value selected including historic records. This cannot be undone.</span><br /><br />Number of records to be consolidated and deleted: {number of records}<br /><br />To continue, type the number of records to be consolidated and deleted, then click Consolidate.");
	define("CONTINUE1", "Continue");
	define("CONSOLIDATE", "Consolidate");
	
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	define("SELECT_CONSOLIDATION_TOOL", "Select Consolidation Type");
	define("OLD_VALUE", "Old Value(To Be Deleted)");
	define("NEW_VALUE", "New Value(To Keep)");
	define("WARNING", "WARNING: All tables with the old value selected will be replaced with the new value selected including historic records. This cannot be undone.<br /><br />Number of records to be consolidated and deleted: {number of records}<br /><br />To continue, type the number of records to be consolidated and deleted, then click Consolidate.");
	define("CONTINUE1", "Continue");
	define("CONSOLIDATE", "Consolidate");
}