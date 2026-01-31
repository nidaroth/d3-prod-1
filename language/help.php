<? require_once("../global/config.php");
if($_SESSION['PK_LANGUAGE'] == 1){
	//English
	define("HELP_PAGE_TITLE", "Knowledge Base");
	define("IMAGE", "Profile Image");
	define("IMAGE_DELETE", "Are you sure want to Delete this Profile Image?");
	define("LANGUAGE", "Language");
	define("PREFERRED_LANGUAGE", "Preferred Language");
	
} else if($_SESSION['PK_LANGUAGE'] == 2){
	//Spanish
	
	define("PROFILE_PAGE_TITLE", "Perfil");
	define("IMAGE", "Perfil Imagen");
	define("IMAGE_DELETE", "¿Estás segura de que quieres eliminar esto Perfil Imagen?");
	define("LANGUAGE", "Idioma");
	define("PREFERRED_LANGUAGE", "Preferred Language");
}