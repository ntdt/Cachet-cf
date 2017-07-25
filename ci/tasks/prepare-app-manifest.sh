#!/bin/bash

set -e
echo "Prepare Cachet app manifest..."
sed "s/app_key/${app_key}/; \
s/app_name/${app_name}/; \
s/app_admin_username/${app_admin_username}/; \
s/app_admin_password/${app_admin_password}/; \
s/app_admin_email/${app_admin_email}/g; \
s/app_admin_api_key/${app_admin_api_key}/; \
s/app_timezone/${app_timezone}/; \
s/app_locale/${app_locale}/; \
s/app_mail_address/${app_mail_address}/;" cachet-cf/ci/templates/manifest.yml > ./app-manifest-output/manifest.yml
echo "Done."
