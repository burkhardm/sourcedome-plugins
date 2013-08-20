<?php
/*
 Description: Utilizes D3.js to visualize nodes & links as m-by-n matrix.
 Version: 0.1
 Author: Martin Burkhard
 Author URI: http://www.sourcedome.de
 License: The MIT License (MIT)
 Source: Les Misérables Co-occurrence, (C) 2013 Mike Bostock (http://bost.ocks.org/mike/miserables/)
*/
?>
<div id="matrixviz_svg">
	<style>

.background {
  fill: #eee;
}

line {
  stroke: #fff;
}

text.active {
  fill: red;
}

svg {
  font: 10px sans-serif;
}

</style>
<script src="http://d3js.org/d3.v3.min.js"></script>

<script>
var margin = {top: 100, right: 0, bottom: 100, left: 100},
	cell_width = 20,
	cell_height = 20;

/* read json data */
d3.json('<?php echo $data_path ?>', function(data) {

	// initialize matrix and nodes
	var matrix = [],
    nodes = data.nodes,
    n = nodes.length,
    nodes2 = data.nodes2,
    m = nodes2.length;

	// Append SVG to div element
    var width = n*cell_width,
    height = m*cell_height;

	// Define D3.js range functions
	var x = d3.scale.ordinal().rangeBands([0, width]),
    z = d3.scale.linear().domain([0, 4]).clamp(true),
    c = d3.scale.category20().domain(d3.range(20));

	//  Append SVG to div element
	var svg = d3.select("#matrixviz_svg").append("svg")
    .attr("width", width + margin.left + margin.right)
    .attr("height", height + margin.top + margin.bottom)
    .append("g")
    .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

	// Compute index per node.
  nodes.forEach(function(node, i) {
    node.index = i;
    node.count = 0;
    matrix[i] = d3.range(m).map(function(j) { return {x: i, y: j, z: 0, group: node.group}; });
  });
  
  // Convert links to matrix; count character occurrences.
  data.links.forEach(function(link) {
    matrix[link.source][link.target].z += link.value;
  });

  // Precompute the orders.
  var orders = {
    name: d3.range(m).sort(function(a, b) { return d3.ascending(nodes2[a].name, nodes2[b].name); }),
    count: d3.range(n).sort(function(a, b) { return nodes[b].count - nodes[a].count; }),
    group: d3.range(n).sort(function(a, b) { return nodes[b].group - nodes[a].group; })
  };

  // The default sort order.
  x.domain(orders.group);

  // Create background
  svg.append("rect")
      .attr("class", "background")
      .attr("width", width)
      .attr("height", height);

  // Show agenda
  showAgenda();
  
  // Visualize Column
  var column = svg.selectAll(".column")
      .data(matrix)
      .enter()
      .append("g")
      .attr("class", "column")
      .attr("transform", function(d, i) { return "translate(" + width/n*i + ")rotate(-90)"; })
      .each(column);

  column.append("line")
      .attr("x1", -height);

  
  column.append("text")
      .attr("x", 6)
      .attr("y", x.rangeBand() / 2)
      .attr("dy", ".32em")
      .attr("text-anchor", "start")
      .text(function(d, i) { if(nodes[i]) return nodes[i].name; });

  // Visualize Row
  var row = svg.selectAll(".row")
      .data(matrix)
      .enter()
      .append("g")
      .attr("class", "row")
      .attr("transform", function(d, i) { return "translate(0," + height/m*i + ")"; });
      
  row.append("line")
      .attr("x2", width);

  row.append("svg:a")
      .attr("xlink:href", function(d, i){  if(nodes2[i]) return nodes2[i].link;})
      .attr("target", "_blank")
    .append("text")
      .attr("x", -6)
      .attr("y", x.rangeBand() / 2)
      .attr("dy", ".32em")
      .attr("text-anchor", "end")
      .attr("text-decoration", "underline")
      .text(function(d, i) { if(nodes2[i]) return nodes2[i].name; });

  function row(row) {

    var cell = d3.select(this).selectAll(".cell")
        .data(row.filter(function(d) { return d.z; }))
        .enter().append("rect")
        .attr("class", "cell")
        .attr("x", function(d) { return height/m*d.x; })
        .attr("width", x.rangeBand())
        .attr("height", x.rangeBand())
        .style("fill-opacity", function(d) { return z(d.z); })
        .style("fill", function(d) { return c(d.group); })
        .on("mouseover", mouseover)
        .on("mouseout", mouseout);
  }

  function column(column) {

    var cell = d3.select(this).selectAll(".cell")
        .data(column.filter(function(d) { return d.z; }))
        .enter().append("rect")
        .attr("class", "cell")
        .attr("x", function(d) { return -height/m*d.y-x.rangeBand(); })
        .attr("width", x.rangeBand())
        .attr("height", x.rangeBand())
        .style("fill-opacity", function(d) { return z(d.z); })
        .style("fill", function(d) { return c(d.group); })
        .on("mouseover", mouseover)
        .on("mouseout", mouseout);
  }

  function mouseover(p) {
    d3.selectAll(".row text").classed("active", function(d, i) { return i == p.y; });
    d3.selectAll(".column text").classed("active", function(d, i) { return i == p.x; });
  }

  function mouseout() {
    d3.selectAll("text").classed("active", false);
  }
  
  function showAgenda() {
  	svg.append("rect")
        .attr("class", "cell")
        .attr("x", x.rangeBand())
        .attr("y", height + x.rangeBand())
        .attr("width", x.rangeBand()-1)
        .attr("height", x.rangeBand()-1)
        .style("fill-opacity", function(d) { return z(10); })
        .style("fill", function() { return c(9);});

  svg.append("text")
      .attr("x", x.rangeBand()*2.5)
      .attr("y", height + x.rangeBand()*1.5)
      .attr("dy", ".32em")
      .attr("text-anchor", "start")
      .text("full color = full support");

  svg.append("rect")
        .attr("class", "cell")
        .attr("x", x.rangeBand())
        .attr("y", height + x.rangeBand()*2)
        .attr("width", x.rangeBand()-1)
        .attr("height", x.rangeBand()-1)
        .style("fill-opacity", function(d) { return z(2); })
        .style("fill", function() { return c(9);});

  svg.append("text")
      .attr("x", x.rangeBand()*2.5)
      .attr("y", height + x.rangeBand()*2.5)
      .attr("dy", ".32em")
      .attr("text-anchor", "start")
      .text("light color = partial / library support");

  svg.append("rect")
        .attr("class", "background")
        .attr("x", x.rangeBand())
        .attr("y", height + x.rangeBand()*3)
        .attr("width", x.rangeBand()-1)
        .attr("height", x.rangeBand()-1);

  svg.append("text")
      .attr("x", x.rangeBand()*2.5)
      .attr("y", height + x.rangeBand()*3.5)
      .attr("dy", ".32em")
      .attr("text-anchor", "start")
      .text("grey color = no support");
  }

});

</script>
</div>