#!/usr/bin/ruby

# usage mksitemap.rb > testcases/_all.xml

puts '<?xml version="1.0" encoding="UTF-8"?>'
puts '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'



files = ` find . -name \*.meta`
files.each do |f|
  f.chomp!
  f.gsub!(/^\.\//,'')
  print "<url><loc>file:#{f}</loc></url>\n";
end
puts "</urlset>\n"
