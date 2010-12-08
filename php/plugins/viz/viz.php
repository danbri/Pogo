<script type="text/javascript">
<!--
var redraw;
var height = 300;
var width = 500;

/* only do all this when document has finished loading (needed for RaphaelJS) */
window.onload = function() {

    var g = new Graph();

    st1 = {directed:true, label : "seeAlso"};
    st2 = {directed:true, label : "Label2"};
    g.addEdge("kiwi", "penguin",st2);
    g.addEdge("kiwi", "34",st1);

    g.addEdge("34", "cherry", { directed : true, label: "fruity" } );    /* a directed connection, using an arrow */

    /* customize the colors of that edge */
//    g.addEdge("id35", "apple", { stroke : "#bfa" , fill : "#56f", label : "Meat-to-Apple" });

    /* layout the graph using the Spring layout implementation */
    var layouter = new Graph.Layout.Spring(g);
    layouter.layout();

    /* draw the graph using the RaphaelJS draw implementation */
    var renderer = new Graph.Renderer.Raphael('canvas', g, width, height);
    renderer.draw();

    redraw = function() {
        layouter.layout();
        renderer.draw();
    };
};

-->
    </script>


<div id="canvas">

</div>
<button id="redraw" onclick="redraw();">redraw</button>
<?php 
#print "PHP...";
?>

