<style>

body {
  font-family: "Helvetica Neue", Helvetica, sans-serif;
  margin: 30px auto;
  width: 1280px;
  position: relative;
}

header {
  padding: 6px 0;
}

.group {
  margin-bottom: 1em;
}

.axis {
  font: 10px sans-serif;
  position: fixed;
  pointer-events: none;
  z-index: 2;
}

.axis text {
  -webkit-transition: fill-opacity 250ms linear;
}

.axis path {
  display: none;
}

.axis line {
  stroke: #000;
  shape-rendering: crispEdges;
}

.axis.top {
  background-image: linear-gradient(top, #fff 0%, rgba(255,255,255,0) 100%);
  background-image: -o-linear-gradient(top, #fff 0%, rgba(255,255,255,0) 100%);
  background-image: -moz-linear-gradient(top, #fff 0%, rgba(255,255,255,0) 100%);
  background-image: -webkit-linear-gradient(top, #fff 0%, rgba(255,255,255,0) 100%);
  background-image: -ms-linear-gradient(top, #fff 0%, rgba(255,255,255,0) 100%);
  top: 0px;
  padding: 0 0 24px 0;
}

.axis.bottom {
  background-image: linear-gradient(bottom, #fff 0%, rgba(255,255,255,0) 100%);
  background-image: -o-linear-gradient(bottom, #fff 0%, rgba(255,255,255,0) 100%);
  background-image: -moz-linear-gradient(bottom, #fff 0%, rgba(255,255,255,0) 100%);
  background-image: -webkit-linear-gradient(bottom, #fff 0%, rgba(255,255,255,0) 100%);
  background-image: -ms-linear-gradient(bottom, #fff 0%, rgba(255,255,255,0) 100%);
  bottom: 0px;
  padding: 24px 0 0 0;
}

.horizon {
  border-bottom: solid 1px #000;
  overflow: hidden;
  position: relative;
}

.horizon {
  border-top: solid 1px #000;
  border-bottom: solid 1px #000;
}

.horizon + .horizon {
  border-top: none;
}

.horizon canvas {
  display: block;
}

.horizon .title,
.horizon .value {
  bottom: 0;
  line-height: 30px;
  margin: 0 6px;
  position: absolute;
  text-shadow: 0 1px 0 rgba(255,255,255,.5);
  white-space: nowrap;
}

.horizon .title {
  left: 0;
}

.horizon .value {
  right: 0;
}

.line {
  background: #000;
  z-index: 2;
}

</style>
<script src="https://d3js.org/d3.v4.min.js"></script>
<script src="https://square.github.io/cubism/cubism.v1.min.js"></script>
<script>

var context = cubism.context()
    .serverDelay(new Date(2012, 4, 2) - Date.now())
    .step(864e5)
    .size(1280)
    .stop();

d3.select("#demo").selectAll(".axis")
    .data(["top", "bottom"])
  .enter().append("div")
    .attr("class", function(d) { return d + " axis"; })
    .each(function(d) { d3.select(this).call(context.axis().ticks(12).orient(d)); });

d3.select("body").append("div")
    .attr("class", "rule")
    .call(context.rule());

d3.select("body").selectAll(".horizon")
    .data(["N", "NNE",].map(dir))
  .enter().insert("div", ".bottom")
    .attr("class", "horizon")
  .call(context.horizon()
    .format(d3.format("+,.2p")));

context.on("focus", function(i) {
  d3.selectAll(".value").style("right", i == null ? null : context.size() - i + "px");
});

// Replace this with context.graphite and graphite.metric!
function dir(dir) {
  var format = d3.time.format("%d-%b-%y");
  return context.metric(function(start, stop, step, callback) {
    d3.csv("?csv=&dir=" + dir + "&m=<?php echo $m;?>", function(rows) {
      rows = rows.map(function(d) { return [format.parse(d.Date), +d.Open]; }).filter(function(d) { return d[1]; }).reverse();
      var date = rows[0][0], compare = rows[400][1], value = rows[0][1], values = [value];
      rows.forEach(function(d) {
        while ((date = d3.time.day.offset(date, 1)) < d[0]) values.push(value);
        values.push(value = (d[1] - compare) / compare);
      });
      callback(null, values.slice(-context.size()));
    });
  }, name);
}

</script>
