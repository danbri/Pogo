<?php

# TODO: review best practice for l18n of PHP doc strings, and setup 
# so these can be translated and accessed.

Class Label 
  { 
    public static $messages = array( 
        'BAD_TYPE_CHARS_FAIL' => 'Poor type name found. Please avoid capitals and punctuation except ":" and "_".' ,
	'NONDIGIT_APPID_CHARS_FAIL' => 'fb:app_id contains non-numeric characters (perhaps you used an api key instead?).' ,
	'FAILED_FBADMINS_REGEX' => "The fb:admins field has unexpected values.",
	'FAILED_BIG_NUMBER_IN_ADMINS' => 'fb:admins field has implausibly large number as value.',
        'MISSING_REQUIRED_PROPERTY' => 'One or more of the required properties (title, type, url, image) are missing.',
        'FAILED_PAGEID_NUMBERSONLY_REGEX' => 'An fb:pageid property contains something other than numbers.',
        'UNESCAPED_LESSTHAN_IN_CONTENT_VALUE' => 'Field value contains a markup symbol (less-than sign).',
#        'SURROUNDING_WHITESPACE_WARNING' => 'Field value begins and/or ends with whitespace.',
	);

  function label($l) {
    return($labels[$l]); # monolingual for now.
  }

}
