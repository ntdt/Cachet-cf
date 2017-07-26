#!/usr/bin/env ruby
require 'json'
require 'uri'
require 'net/http'

#$pcf_components = ENV['pcf_components']
$api_token = ENV['app_admin_api_key']
$api_endpoint ="https://#{ENV['app_name']}.apps.#{ENV['pcf_ert_domain']}/api/v1"
$components = {"cf"         => "Pivotal Elastic Runtime",
               "p-mysql"    => "MySQL for PCF",
               "p-redis"    => "Redis for PCF",
               "p-rabbitmq" => "RabbitMQ for PCF",
               "apm"        => "PCF Metrics",
               "p-spring-cloud-services"        => "Spring Cloud Services",
               "Pivotal_Single_Sign-On_Service" => "Single Sign-On"}

def get_component_id_from_description(description)
  url = URI("#{$api_endpoint}/components")
  http = Net::HTTP.new(url.host, url.port)
  http.use_ssl = true
  request = Net::HTTP::Get.new(url)
  request['Content-Type'] = 'application/json'
  request['X-Cachet-Token'] = "#{$api_token}"
  response = http.request(request)
  res = JSON.load(response.read_body)
  #puts res['data']
  res['data'].each do |component|
    if component['description'] == description
      return component['id'].to_i
    end
  end
  return 0
end

def update_component_version(component_id, description, version)
  url = URI("#{$api_endpoint}/components/#{component_id}")
  http = Net::HTTP.new(url.host, url.port)
  http.use_ssl = true
  request = Net::HTTP::Put.new(url)
  request['Content-Type'] = 'application/json'
  request['X-Cachet-Token'] = "#{$api_token}"
  name = "#{$components[description]} #{version}"
  puts name
  request.body = "{\"name\":\"#{name}\", \"status\":1}"
  response = http.request(request)
  response.read_body
end

tile = ARGV[0]
version = ARGV[1]
tile_id = get_component_id_from_description(tile)
update_component_version(tile_id, tile, version)
