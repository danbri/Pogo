<?php

# TODO: review best practice for l18n of PHP doc strings, and setup so these can be translated and accessed.

Class Label 
  { 
    public static $messages = array( 

        'BAD_TYPE_CHARS_FAIL' => 			'Poor type name found. Please avoid capitals and punctuation except ":" and "_".' ,

	'NONDIGIT_APPID_CHARS_FAIL' => 			'fb:app_id contains non-numeric characters (perhaps you used an api key instead?).' ,

	'FAILED_FBADMINS_REGEX' => 			'The fb:admins field has unexpected values.',

	'FAILED_BIG_NUMBER_IN_ADMINS' => 		'fb:admins field has implausibly large number as value.',

        'MISSING_REQUIRED_PROPERTY' => 			'One or more required properties (og:title, og:type, og:url, og:image) are missing.',

        'FAILED_PAGEID_NUMBERSONLY_REGEX' => 		'An fb:pageid property contains something other than numbers.',

        'UNESCAPED_LESSTHAN_IN_CONTENT_VALUE' =>	'Field value contains a markup symbol (less-than sign).',

        'SURROUNDING_WHITESPACE_WARNING' => 		'Field value begins and/or ends with whitespace.',

        'OG_NAMESPACE_UNDECLARED' => 			'Document uses but does not declare the og: namespace.',

        'FB_NAMESPACE_UNDECLARED' => 			'Document uses but does not declare the fb: namespace.',

        'NAMESPACE_UNDECLARED' => 			'Document uses but does not declare an extension namespace.',

	'METANAME_ATTRIBUTE_NOT_PROPERTY' =>		'Incorrect syntax; using meta name attribute instead of property attribute.',

	'OG_TITLE_HIGHLY_PUNCTUATED' =>			'Style warning: og:title contains a lot of punctuation. Titles should be simple text, no need for navigation info.',

	'BAD_HTML' => 					'HTML markup is exceptionally bad, consider visiting validator.w3.org.',

	'MISC_FB_FAIL' =>				'Something wrong in one of the fb: fields, sorry no more details available. Try the Facebook linter.',

	'NO_PAGE_CONTENT' => 				'Page seems to be empty.',


        'FAILED_READ_URL' =>				'Failure reading from URL',

	'NONNUMERIC_PAGE_ID' =>				'The page_id property is non-numeric.',

	);



  function label($l) {
    return($labels[$l]); # monolingual for now. Translation would be in different PHP files.
  }

}
