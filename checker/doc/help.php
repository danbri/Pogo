<html>
<head>
<title>OpenGraph checker: help </title><link rel="stylesheet" href="../style.css" type="text/css" />
</head>
<body>

<div class="headblock rc" style="margin: 1em"> 
 <div class="headlogo"> 
   <a href="http://og.danbri.org/pogo/Pogo/checker/"><img class="ogp-logo" src="ogp-translogo.png" alt="OGP logo"  width="85"/></a> 
 </div> 
 <div class="headtext"> 
  <div class="OGPtop"><a href="http://og.danbri.org/pogo/Pogo/checker/">Open Graph checker</a></div> 
  <span class="tagline">Check your Open Graph protocol markup</span> 
 </div> 
 <div class="helplink"> 
  <a href="../">back</a> 
 </div> 
</div> 

<p class="clearboth"><br/></p>

<div class="helpdoc">

<p>Help with the Open Graph protocol checker.</p>

<p>
Use of the checker should be straightforward: you give it the URL of a page containing OGP markup, and it
will (after a short delay...) give a report. If OGP data was found, it will print a summary. If problems 
were found, it will describe them.
</p>


<dl>
<dt>What is this?</dt>
<dd>This Open Graph checker, 'Pogo', is a simple tool to help publishers and consumers of 
<a href="http://ogp.me/">Open Graph protocol</a> markup identify common problems with the data.</dd>
</dl>

<dl>
<dt>What are the "lite" and "full" parsers?</dt>
<dd>OGP uses a subset of W3C's RDFa standard. We include a "lite" parser that targets this subset, and 
is more 'forgiving' of certain errors. The "full" parser follows the standard more closely. While this can 
make it less forgiving, it does permit many more ways of expressing your data (for example within page body). 
</dd>
</dl>

<dl>
<dt>What does the 'full details' view show?</dt>
<dd>The 'full details' view will show how your page content looks to both parsers. It is worth also 
testing with Facebook's <a href="http://developers.facebook.com/tools/lint/">linter</a>. Their parser is 
quite permissive, and will show you how your data looks to the Facebook platform.
</dd>
</dl>

<dl>
<dt>Working examples?</dt>
<dd>
Live examples: 
  <a href="<?php require_once '../OGDataGraph.php'; echo OGDataGraph::$my_base_uri; ?>?url=http://www.imdb.com/title/tt0083658/">bladerunner</a> 
| <a href='<?php require_once '../OGDataGraph.php'; echo OGDataGraph::$my_base_uri; ?>?url=http://graph.danbri.org/Pogo/checker/testcases/cafe.com/hiddenwords.cache'>cafe.com</a>  
| <a href='<?php require_once '../OGDataGraph.php'; echo OGDataGraph::$my_base_uri; ?>?url=http://www.slideshare.net/slidarko/problemsolving-using-graph-traversals-searching-scoring-ranking-and-recommendation'>slideshare.com</a> 
| <a href='<?php require_once '../OGDataGraph.php'; echo OGDataGraph::$my_base_uri; ?>?url=http://r.gnavi.co.jp/g363600/'>gnavi.co.jp (known charset issue)</a> 
| <a href='<?php require_once '../OGDataGraph.php'; echo OGDataGraph::$my_base_uri; ?>?url=http://ekstrabladet.dk/112/article1469733.ece'>ekstrabladet.dk</a> 
| <a href='<?php require_once '../OGDataGraph.php'; echo OGDataGraph::$my_base_uri; ?>?url=http://www.rottentomatoes.com/m/matrix/'>rottentomatoes.com</a> 
| <a href='<?php require_once '../OGDataGraph.php'; echo OGDataGraph::$my_base_uri; ?>?url=http://developers.facebook.com/tools/lint/&mode=auto'>developers.facebook.com</a>
| <a href="<?php require_once '../OGDataGraph.php'; echo OGDataGraph::$my_base_uri; ?>?url=<?php require_once '../OGDataGraph.php'; echo OGDataGraph::$my_base_uri; ?>/testcases/imdb/legend_guardians.cache">legend_guardians</a> (local copy)
| <a href='<?php require_once '../OGDataGraph.php'; echo OGDataGraph::$my_base_uri; ?>?url=http://ehandel.blocket.se/Philips_HD2383_1115754.htm'>blocket.se</a> 
| <a href="<?php require_once '../OGDataGraph.php'; echo OGDataGraph::$my_base_uri; ?>?url=http://graph.danbri.org/Pogo/checker/testcases/ogp/geo1.cache">california</a> (with map example)
</dd>
</dl>

<dl>
<dt>Bad examples</dt>
<a href='<?php require_once '../OGDataGraph.php'; echo OGDataGraph::$my_base_uri; ?>?url=http://developers.facebook.com/tools/lint/examples/bad_app_id'>bad_app_id</a>
 | <a href='<?php require_once '../OGDataGraph.php'; echo OGDataGraph::$my_base_uri; ?>?url=http://photobucket.com/images/color%20splash/'>photobucket.com</a> 
 | <a href='<?php require_once '../OGDataGraph.php'; echo OGDataGraph::$my_base_uri; ?>?url=http://www.playlist.com/'>playlist.com</a>  
</dt>
</dl>

<dl>
<dt>What is the item count? Why do the numbers vary?</dt>
<dd>The count is the number of 'facts' or properties found in each document, matching Open Graph or other RDFa syntax. The Full parser 
will include other vocabularies that are deployed alongside Open Graph markup, as well as certain typed hyperlinks. 
Currently we only display the most basic Open Graph properties.
</dd>
</dl>

<dl>
<dt>Can I see the rest of the parsed data?</dt>
<dd>A future revision will show the raw parser output. For now, you can also see an <em>experimental</em> visualization, by appending '&amp;mode=viz' to any URL.</dd>
</dl>

<dl>
<dt>Can you make it faster?</dt>
<dd>The current version fetches everything twice, once for each parser. This could likely be improved in a future revision.</dd>
</dl>

<dl>
<dt>Is the code available?</dt>
<dd>Yes, in the <a href="http://github.com/danbri/Pogo">Pogo</a> repository on Github (Apache licensed, or compatible for dependencies)</dd>
</dl>


<dl>How does it work?</dl>
<dd>
The checker's target behaviour is based on a collection of <a href="https://github.com/danbri/Pogo/tree/master/checker/testcases">test cases</a>. 
Each has metadata expressed in JSON, and is grouped into collections (described using sitemap files). See the Git repository for details.
</dd>

<h2>Error Codes</h2>

<p>The following error conditions are understood.</p>

<?php 
require_once '../OG_Checklist.php';
echo "<dl>";
$msgs = Label::$messages;

foreach ($msgs as $key => $value)
 echo "<dt>" . $key.'</dt><dd>'.$value."<br/><br/></dd>\n";
?>

</div>

<br/><br/><div class="footer">
<hr/>Open Graph checker, 2011
</div>

</body>
</html>
