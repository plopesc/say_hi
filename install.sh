# Install the site and set the main user password.
drush site-install --yes
drush user:password admin admin

# Enable the SayHi module
drush en say_hi --yes
