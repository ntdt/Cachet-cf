#!/usr/bin/env ruby
require 'json'
require 'uri'
require 'net/http'
require 'uaa'

def get_component_id_from_description(api_url, api_token, description)
  url = URI("#{api_url}/components")
  http = Net::HTTP.new(url.host, url.port)
  http.use_ssl = true
  request = Net::HTTP::Get.new(url)
  request['Content-Type'] = 'application/json'
  request['X-Cachet-Token'] = "#{api_token}"
  response = http.request(request)
  res = JSON.load(response.read_body)
  res['data'].each do |component|
    if component['description'] == description
      return component['id'].to_i
    end
  end
  return 0
end

def update_component_version(api_url, components, component_id, description, version)
  url = URI("#{api_url}/components/#{component_id}")
  http = Net::HTTP.new(url.host, url.port)
  http.use_ssl = true
  request = Net::HTTP::Put.new(url)
  request['Content-Type'] = 'application/json'
  request['X-Cachet-Token'] = "#{$api_token}"
  name = "#{components[description]} #{version}"
  puts name
  request.body = "{\"name\":\"#{name}\", \"status\":1}"
  response = http.request(request)
  response.read_body
end


def get_auth_header(opsman_url, user, password)
  token_issuer = CF::UAA::TokenIssuer.new("#{opsman_url}/uaa","opsman")
  token = token_issuer.owner_password_credentials_grant(username: user, password: password)
  token.auth_header
end

def get_deployed_products(opsman_url,user, password)
  url = URI("#{opsman_url}/api/v0/deployed/products")
  http = Net::HTTP.new(url.host, url.port)
  http.use_ssl = true
  request = Net::HTTP::Get.new(url)
  request['Content-Type'] = 'application/json'
  request['Authorization'] = get_auth_header(opsman_url, user, password)
  response = http.request(request)
  res = JSON.load(response.read_body)

  res.map do |e|
    {"tile" => e["type"],
     "version" => e["product_version"]}
  end
end

pcf_components    = ENV['pcf_components']
api_token         = ENV['app_admin_api_key']
pcf_ert_domain    = ENV['pcf_ert_domain']
pcf_opsman_admin  = ENV['pcf_opsman_admin']
pcf_opsman_passwd = ENV['pcf_opsman_passwd']
api_endpoint      = "https://#{ENV['app_name']}.apps.#{pcf_ert_domain}/api/v1"
opsman_url        = "https://opsman.#{pcf_ert_domain}"
components        = {"cf"         => "Pivotal Elastic Runtime",
                     "p-mysql"    => "MySQL for PCF",
                     "p-redis"    => "Redis for PCF",
                     "p-rabbitmq" => "RabbitMQ for PCF",
                     "apm"        => "PCF Metrics",
                     "p-spring-cloud-services"        => "Spring Cloud Services",
                     "Pivotal_Single_Sign-On_Service" => "Single Sign-On"}
tiles_versions = get_deployed_products(opsman_url, pcf_opsman_admin, pcf_opsman_passwd)
tiles_versions.each do |tile, version|
  tile_id = get_component_id_from_description(api_endpoint, api_token, tile)
  update_component_version(api_endpoint, pcf_components, tile_id, tile, version)
end
