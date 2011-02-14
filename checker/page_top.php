
<link rel="shortcut icon" type="image/x-icon" href="doc/favicon.ico">
    <script type="text/javascript">
// <![CDATA[
    function showSection() {
      var mappings = getElementsByClass('detail');
      for (i = 0; i < mappings.length; i++) { mappings[i].style.display="block";}
    }
    function hideSection() {
      var mappings = getElementsByClass('detail');
      for (i = 0; i < mappings.length; i++) { mappings[i].style.display="none"; }
    }

    function getElementsByClass(searchClass,node,tag) {
          var classElements = new Array();
          if ( node == null )
                  node = document;
          if ( tag == null )
                tag = '*';
          var els = node.getElementsByTagName(tag);
          var elsLen = els.length;
          var pattern = new RegExp('(^|\\\\s)'+searchClass+'(\\\\s|$)');
          for (i = 0, j = 0; i < elsLen; i++) {
                  if ( pattern.test(els[i].className) ) {
                        classElements[j] = els[i];
                        j++;
                  }
          }
          return classElements;
    }
// ]]>
</script>

</head>
<body>

<?php

if ($_GET['url']) {
print "<p style=\"float: right\">show details? [<big><b><a href=\"#\" onclick=\"javascript:showSection();return false;\">+</a></b></big>] |";
print "[<big><b><a href=\"#\" onclick=\"javascript:hideSection();return false;\">-</a></b></big>]  </p>";
}
?>


<hr style="clear: both"/>

<div class="navlinks">
<a href="http://ogp.me/"><img class="ogp-logo" src="doc/ogp-logo.jpg" alt="OGP logo"  width="85"/></a>
[<a href="index.php">check url</a>] 
[<a href="sitemap.php">raw testcases</a>] 
[<a href="http://developers.facebook.com/tools/lint/">Facebook's linter</a>]</div>

<h2><a href="<?php require_once 'OGDataGraph.php'; echo OGDataGraph::$my_base_uri; ?>">OpenGraph checker</a></h2>



