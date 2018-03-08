#!/bin/bash

set -e

DIR=$(cd -P -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd -P)
SITE_FILE="/etc/apache2/sites-available/000-torrent-machine.conf"

echo "writing '$SITE_FILE'"
cat <<EOF > "$SITE_FILE"
<Directory ${DIR}/www/>
	ServerSignature Off
	DirectoryIndex index.php
	DirectorySlash On
	AcceptPathInfo Off
	RewriteEngine On
	Options -MultiViews +Indexes -Includes -ExecCGI +FollowSymLinks

	ErrorDocument 400 /
	ErrorDocument 403 /
	ErrorDocument 404 /
	ErrorDocument 500 /
	ErrorDocument 503 /

	AllowOverride None
	Require all granted

	RewriteCond %{REQUEST_FILENAME} !-d
	RewriteCond %{REQUEST_FILENAME} !-f
	RewriteRule .? index.php [L]
</Directory>

<Directory ${DIR}/www/content/>
	Require all denied
</Directory>

Listen 9000

<VirtualHost *:9000>
	DocumentRoot ${DIR}/www
	ErrorLog \${APACHE_LOG_DIR}/error-torrent-machine.log
	CustomLog \${APACHE_LOG_DIR}/access-torrent-machine.log combined
</VirtualHost>
EOF
cat "$SITE_FILE"

echo "running 'a2ensite 000-torrent-machine'"
a2ensite 000-torrent-machine
echo "running 'service apache2 reload'"
service apache2 reload
