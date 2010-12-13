<?php

# TODO: review best practice for l18n of PHP doc strings, and setup 
# so these can be translated and accessed.

Class Label 
  { 
    public static $messages = array( 
        'BAD_TYPE_CHARS_FAIL' => 'Poor type name found. Please avoid capitals and punctuation except ":" and "_".' ,
	'NONDIGIT_APPID_CHARS_FAIL' => 'fb:app_id contains non-numeric characters (perhaps you used an api key instead?).' 
# FAILED_PAGEID_NUMBERSONLY_REGEX
# add more from checker class...
	);

  function label($l) {
    return($labels[$l]); # monolingual for now.
  }

}
