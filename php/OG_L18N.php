<?php

# TODO: review best practice for l18n of PHP doc strings, and setup 
# so these can be translated and accessed.

$labels = array( 'BAD_TYPE_CHARS_FAIL' => 'Poor type name found. Please avoid capitals and punctuation except ":" and "_".' ,
	'NONDIGIT_APPID_CHARS_FAIL' => 'fb:app_id contains non-numeric characters (perhaps api key instead?).' 

	);

function label($l) {
  return($labels[$l]); # monolingual for now.
}
