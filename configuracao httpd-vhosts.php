<?php

/*

Texto a inserir no final do ficheiro http-vhosts em

C:\xampp\apache\conf\extra\http-vhosts.conf






<VirtualHost *:80>
    ServerAdmin webmaster@hcode.com
    DocumentRoot "C:/ecommerce"
    ServerName www.hcodecommerce.com
    ErrorLog "logs/dummy-host2.example.com-error.log"
    CustomLog "logs/dummy-host2.example.com-access.log" common
    <Directory "C:/ecommerce">
        Require all granted

        RewriteEngine On

        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteRule ^ index.php [QSA,L]
    </Directory>
</VirtualHost>



<VirtualHost *:80>
    ServerAdmin webmaster@hcode.com
    DocumentRoot "C:/xampp/htdocs/raintpl"
    ServerName www.raintpl.com
    ErrorLog "logs/dummy-host2.example.com-error.log"
    CustomLog "logs/dummy-host2.example.com-access.log" common
    <Directory "C:/xampp/htdocs/raintpl">
        Require all granted

        RewriteEngine On

        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteRule ^ index.php [QSA,L]
    </Directory>
</VirtualHost>






*/


?>