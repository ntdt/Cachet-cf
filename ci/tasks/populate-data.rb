#!/usr/bin/env ruby
require 'yaml'
pcf_components=ENV['pcf_components']
puts pcf_components
components=YAML.load(pcf_components)
components.inspect
