<?php

# Note: fb: prefix is used by Facebook and also Google's Freebase
# this list from prefix.cc public dataset.
# http://prefix.cc/popular/all.file.json

$ns = array();
function loadNamespaceArray() {
  $handle = fopen( 'all.file.json', 'r'); # todo: cache this!
  $contents = stream_get_contents($handle);
  fclose($handle);
  $ns = json_decode( $contents, true );
  return $ns;
}

function ns() {
  return $ns;
}

function shortname($s) {
  print "Looking up: $s<br/>";
  $matches = array();
  foreach ($ns as $key => $value) {
    print "Is $value substring in $s ? <br/>";
  }
  return $s;
}
