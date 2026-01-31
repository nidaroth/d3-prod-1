This is the DiamondSiS repository.

# Changes for Dev env site set-up
- Modify the index.php to redirect the URL to relative path
   e.g. header('Location: /signin');
- Update the global/config.php to check for domain name and add the database credentials as required.
- Update the global/mail.php and global/texting.php files at the very top, to match the paths for document root. 
    e.g. if block ...
        else {
        	$path1 = '/var/www/html/DSIS/global/'; /*Your document root*/
        	$path2 = '/var/www/html/DSIS/global/'; /*Your document root*/
        }
