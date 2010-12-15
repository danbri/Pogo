<?php
header("HTTP/1.1 200 OK");
header("Content-type: text/html"); # half-hearted attempt to do NPH
?><html>
<head><title>OpenGraph checker</title><link rel="stylesheet" href="style.css" type="text/css" />
<?php 
require_once 'page_top.php';
require_once 'OGDataGraph.php'; 
require_once 'OG_Checklist.php';
require_once 'CheckUI.php';
require_once 'cfg.php';
$msg = Label::$messages;
$base = OGDataGraph::$my_base_uri;
print '<body>';
#$map = 'testcases/approved.xml';
$map = 'testcases/fb_tests.xml';
#$map = 'testcases/_all.xml';
#$map = 'testcases/_todo.xml';

try { 
$tests = OGDataGraph::getTests($map);
} catch (Exception $e) { print "Failed to load configured testcases sitemap file."; }

foreach ($tests as $tc) {
  print CheckUI::checkFromTest($tc);
}

?>

<hr />
[pogo checker] status: v1.0, <em>experimental release.</em>
</body>
</html>
