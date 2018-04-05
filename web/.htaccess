# use mod_rewrite for pretty URL support
RewriteEngine on
# if a directory or a file exists, use the request directly
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
# otherwise forward the request to index.php
RewriteRule . index.php
