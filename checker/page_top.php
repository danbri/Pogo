
<link rel="shortcut icon" type="image/x-icon" href="doc/favicon.ico">
    <script type="text/javascript">

// <![CDATA[
    function showSection() {

      var mappings = getElementsByClass('showmore');
      for (i = 0; i < mappings.length; i++) { mappings[i].style.display="none"; }

      var mappings = getElementsByClass('detail');
      for (i = 0; i < mappings.length; i++) { mappings[i].style.display="block";}

      var mappings = getElementsByClass('hidedetail');
      for (i = 0; i < mappings.length; i++) { mappings[i].style.display="block";}

    }


    function hideSection() {
      var mappings = getElementsByClass('detail');
      for (i = 0; i < mappings.length; i++) { mappings[i].style.display="none"; }

      var mappings = getElementsByClass('showmore');
      for (i = 0; i < mappings.length; i++) { mappings[i].style.display="block"; }


      var mappings = getElementsByClass('hidedetail');
      for (i = 0; i < mappings.length; i++) { mappings[i].style.display="none";}

    }

    function getElementsByClass(searchClass,node,tag) {
          var classElements = new Array();
          if ( node == null )
                  node = document;
          if ( tag == null )
                tag = '*';
          var els = node.getElementsByTagName(tag);
          var elsLen = els.length;
          //alert("Scanning "+searchClass);
          // note: classes regex assumes one class per attribute, currently.
          var pattern = new RegExp('(^|\\\\s)'+searchClass+'(\\\\s|$)');
          for (i = 0, j = 0; i < elsLen; i++) {
                  if ( pattern.test(els[i].className) ) {
                        classElements[j] = els[i];
			// alert("Got it! " + els[i] );
                        j++;
                  }
          }
          return classElements;
    }
// ]]>
</script>

</head>
<body class="center">

<?php

if ($_GET['url']) {
#print "<p style=\"float: right\">show details? [<big><b><a href=\"#\" onclick=\"javascript:showSection();return false;\">+</a></b></big>] |";
#print "[<big><b><a href=\"#\" onclick=\"javascript:hideSection();return false;\">-</a></b></big>]  </p>";
}
?>



<div class="headblock rc">
 <div class="headlogo">
   <a href="<?php require_once 'OGDataGraph.php'; echo OGDataGraph::$my_base_uri; ?>"><img border="0" class="ogp-logo" src="doc/ogp-translogo.png" alt="OGP logo"  width="85"/></a>
 </div>
 <div class="headtext">
  <div class="OGPtop"><a href="<?php require_once 'OGDataGraph.php'; echo OGDataGraph::$my_base_uri; ?>">Open Graph checker</a></div>
  <span class="tagline">Check your Open Graph protocol markup</span>
 </div>
 <div class="helplink">
  <a href="doc/help.php">help</a>
 </div>
</div>


<div class="posthead" style="clear: both"></div>


<?php
if (! $_GET['url']) {
print "<h2 class=\"checkmarkup\">Check your markup for common mistakes</h2>";
print "<p class=\"howto\">Enter a URL below to check your page and get feedback on your Open Graph markup.</p>";


}
#print "<p>Learn more about the Open Graph protocol from <a href=\"http://ogp.me\">ogp.me</a>, 
#<a href=\"http://en.wikipedia.org/wiki/Facebook_Platform#Open_Graph_protocol\">Wikipedia</a> or <a href=\"http://developers.facebook.com/docs/opengraph/\">Facebook</a>.</p>";
?>


<!--  [<a href="index.php">check url</a>] [<a href="sitemap.php">raw testcases</a>] [<a href="http://developers.facebook.com/tools/lint/">Facebook's linter</a>] -->
