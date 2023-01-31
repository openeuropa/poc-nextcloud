# These instructions run every time the container is started.
# At this time, Nextcloud will be already installed.

set -x

cd /var/www/html

# Enable apps that were downloaded when the image was built.
# Use `set +e` to ignore failure exit code if the apps are already enabled.
set +e
sudo -E -u www-data ./occ app:enable richdocuments
sudo -E -u www-data ./occ app:enable groupfolders
sudo -E -u www-data ./occ app:enable -f user_cas
set -e

# Allow API requests to 'http://nextcloud/' from 'web' container.
sudo -E -u www-data ./occ config:system:set --value=nextcloud trusted_domains 1

# Make user_cas use EU Login.
sudo -E -u www-data ./occ config:app:set --value="ecas.ec.europa.eu" user_cas cas_server_hostname
sudo -E -u www-data ./occ config:app:set --value="443" user_cas cas_server_port
sudo -E -u www-data ./occ config:app:set --value="/cas" user_cas cas_server_path
# Change CAS login button label.
sudo -E -u www-data ./occ config:app:set --value="EU Login" user_cas cas_login_button_label
