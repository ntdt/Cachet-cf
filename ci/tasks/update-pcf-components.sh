#!/bin/bash
set -e

sudo cp tool-om/om-linux /usr/local/bin
sudo chmod 755 /usr/local/bin/om-linux

echo "=============================================================================================="
echo " Get deployed products @ https://opsman.$pcf_ert_domain ..."
echo "=============================================================================================="

om-linux --target https://opsman.$pcf_ert_domain -k \
         --username "$pcf_opsman_admin" \
	 --password "$pcf_opsman_admin_passwd" \
	 deployed-products | grep -v -e '+-' -e NAME|tr -d '[:blank:]'|cut -d'|' -f 2,3  > tiles-versions
cat tiles-versions

for tile in cf p-rabbitmq Pivotal_Single_Sign-On_Service p-redis apm p-mysql p-spring-cloud-services
do
    version=$(grep $tile tiles-versions| cut -d '|' -f 2)
    exec update-pcf-component.rb $tile $version
done
