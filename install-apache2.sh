#!/bin/bash

set -e

DIR=$(cd -P -- "$(dirname -- "${BASH_SOURCE[0]}")" && pwd -P)
SITE_FILE="/etc/apache2/sites-available/000-torrent-machine.conf"

echo "writing '$SITE_FILE'"
cat <<EOF > "$SITE_FILE"
<Directory ${DIR}/www/>
	Options Indexes FollowSymLinks
	AllowOverride None
	Require all granted

	<IfModule mod_rewrite.c>
		RewriteEngine On
		RewriteCond %{REQUEST_FILENAME} !-d
		RewriteCond %{REQUEST_FILENAME} !-f
		RewriteRule .? index.php [L]
	</IfModule>
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
