<?php

# Note: fb: prefix is used by Facebook and also Google's Freebase
# this list from prefix.cc public dataset.
# http://prefix.cc/popular/all.file.json

$ns = array();
function loadNamespaceList() {
  # ok :) print "<blink>LOADING JSON</blink>";
  $handle = fopen('plugins/viz/all.file.json', 'r');
  $contents = stream_get_contents($handle);
  fclose($handle);
  $ns = json_decode( $contents, true );
  return $ns;
}

function ns() {
  return $ns;
}

function shortname($s) {
  print "shorting: $s using '$ns'<br/>";
  $matches = array();
  
  foreach (ns() as $key => $value) {
    print "Is $value substring in $s ? <br/>";
  }
  return $s;
}
