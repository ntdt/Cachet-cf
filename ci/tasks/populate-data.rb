#!/usr/bin/env ruby
require 'yaml'
require 'json'
require 'uri'
require 'net/http'

$pcf_components = ENV['pcf_components']
$lcd_components = ENV['lcd_components']
$api_token = ENV['app_admin_api_key']
$api_endpoint="https://#{ENV['app_name']}.apps.#{ENV['pcf_ert_domain']}/api/v1"

def create_component_group(group_name)
  url = URI("#{$api_endpoint}/components/groups")
  http = Net::HTTP.new(url.host, url.port)
  http.use_ssl = true
  request = Net::HTTP::Post.new(url)
  request['Content-Type'] = 'application/json'
  request['X-Cachet-Token'] = "#{$api_token}"
  request.body = "{\"name\":\"#{group_name}\",\"collapsed\":2}"
  response = http.request(request)
  response.read_body
end

def create_component(group_id, name, description, link)
  url = URI("#{$api_endpoint}/components")
  http = Net::HTTP.new(url.host, url.port)
  http.use_ssl = true
  request = Net::HTTP::Post.new(url)
  request['Content-Type'] = 'application/json'
  request['X-Cachet-Token'] = "#{$api_token}"
  request.body = "{\"name\":\"#{name}\",\"description\":\"#{description}\",\"status\":1,\"link\":\"#{link}\",\"group_id\":#{group_id}}"
  response = http.request(request)
  response.read_body
end

puts "Create Component Group: Pivotal Cloud Foundry..."
pcf = JSON.load(create_component_group("Pivotal Cloud Foundry"))
pcf_id = pcf['data']['id']
puts "done."

puts "Create Component Group: Livraison Continue..."
lcd = JSON.load(create_component_group("Livraison Continue"))
lcd_id = lcd['data']['id']
puts "done."

# Create PCF components
p_components=YAML.load($pcf_components)
p_components.each do |comp|
  puts "Create Component #{comp['name']}..."
  create_component(pcf_id, comp['name'], comp['description'], comp['link'])
  puts "done."
end

# Create LCD components
l_components = YAML.load($lcd_components)
l_components.each do |comp|
  puts "Create Component #{comp['name']}..."
  create_component(lcd_id, comp['name'], comp['description'], comp['link'])
  puts "done."
end

