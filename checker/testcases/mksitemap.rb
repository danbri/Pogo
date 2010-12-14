#!/usr/bin/ruby

# usage testcases/mksitemap.rb > testcases/_all.xml
# run from the main directory above testcases/
# since we need the foldername in our paths.

puts '<?xml version="1.0" encoding="UTF-8"?>'
puts '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'



files = ` find . -name \*.meta`
files.each do |f|
  f.chomp!
  f.gsub!(/^\.\//,'')
  print "<url><loc>file:#{f}</loc></url>\n";
end
puts "</urlset>\n"
