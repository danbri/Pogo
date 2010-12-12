#!/usr/bin/ruby

# usage mksitemap.rb > testcases/_all.xml

puts '<?xml version="1.0" encoding="UTF-8"?>'
puts '<urlset xmlns="http://www.google.com/schemas/sitemap/0.84">'



files = ` find . -name \*.meta`
files.each do |f|
  f.chomp!
  f.gsub!(/^\.\//,'')
  print "<url><loc>file:#{f}</loc></url>\n";
end
puts "</urlset>\n"
