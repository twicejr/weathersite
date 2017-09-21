<script>
    $(document).ready(function ()
    {
        dygraphs();
    });

    $(document).on("ajaxupdate", function ()
    {
        dygraphs();
    });

    var ajaxInterval;
    function createPeriodicUpdate(graphs, ajaxTimeout)
    {
        return setInterval(function () {

            var time = new Date().getMilliseconds();

            //Ajakkes
            $.ajax({
                url: '<?php echo Uri::get('weatherupdate'); ?>',
                type: 'POST',
                success: function (response)
                {
                    var graphdata = JSON.parse(response);
                    var createdInterval = false;
                    //todo: ajax request latest values / check if new date: then add it
                    graphs.forEach(function (graph)
                    {
                        var newdata_ = $.map(graphdata[graph[0]], function (el) {
                            return el;
                        });

                        var newdate = new Date(newdata_[0]);
console.log(newdate, graph[2],newdata_);
                        var newdata = [newdate];
                        var lastdate = graph[2][graph[2].length - 1][0];
                        if (newdata_.length >= 2 && lastdate.getTime() !== newdate.getTime())
                        {
                            newdata_.shift();
                            newdata_.forEach(function (value) {
                                newdata.push(value);
                            });

                            graph[2].push(newdata);

                            console.log(graph[0], newdata);
                            graph[1].updateOptions({'file': graph[2]});
                        }
                    });

                    loadingEffectFinished(true);

                }
            });
        }, ajaxTimeout);
    }

    function dygraphs()
    {
        clearInterval(ajaxInterval);
        var graphs = [];
        var i = 0;
        $(".dygraph[data-initialized!='1']").each(function ()
        {
            $(this).attr('id', 'i' + ++i);
            $(this).data('initialized', 1);
            var that = $(this);

            if (that.data('stacked-graph'))
            {
                var plotter = [
                    Dygraph.Plotters.fillPlotter,
                    Dygraph.Plotters.errorPlotter,
                    Dygraph.Plotters.linePlotter
                            //Dygraph.Plotters.linePlotter,
                ];
            } else
            {
                smoothPlotter.smoothing = 3 / 9;
                var plotter = [
                    //Dygraph.Plotters.fillPlotter,
                    Dygraph.Plotters.errorPlotter,
                    smoothPlotter
                            //Dygraph.Plotters.linePlotter,
                ];
            }

            var yAxis = {
                labelsKMB: true,
                includeZero: $(this).hasClass('dustbars')
            };


            if ($(this).data('ymax'))
            {
                yAxis.valueRange = [0, $(this).data('ymax') + 1];
            }
            var data_ = $(this).data('graph');
            var data__ = data_.split("\n");
            var data = [];
            var ss;
            data__.reverse(); //bummer.. todo in mysql
            data__.forEach(function (entry)
            {
                ss = entry.split(",");
                if (ss.length >= 2)
                {
                   var ddd = new Date(ss.shift());
                  if(isNaN(ddd.getTime()))
                  {
                   return;
                   }
                    var subdata = [ddd];
                    ss.forEach(function (subentry)
                    {
                        subdata.push(subentry);
                    });
                    data.push(subdata);
                }
            });

            var g = new Dygraph
            (
                    $(this).attr('id'),
                    $(this).data('graph'),
                    {
                        fillGraph: false,
                        stackedGraph: that.data('stacked-graph'),
                        stackedGraphNaNFill: 'inside',
                        connectSeparatedPoints: true,
                        showRoller: true,
                        axes:
                        {
                            y: yAxis
                        },
                        fillAlpha: .35,
                        digitsAfterDecimal: 4,
                        maxNumberWidth: 8,
                        // The ordering here ensures that central lines always appear above any
                        // fill bars/error bars.
                        plotter: plotter,
                        rollPeriod: $(this).data('rollperiod'),
                        drawPoints: true,
                        showRangeSelector: true,
                        strokeWidth: 1,
                        strokeBorderWidth: that.data('stacked-graph') ? null : 1,
                        legend: 'onmouseover',
                        pointSize: 1,
                        highlightCircleSize: 3,
                        title: $(this).data('title'),
                        ylabel: $(this).data('ylabel'),
                        highlightSeriesOpts: {
                            strokeWidth: 3,
                            strokeBorderWidth: 1,
                            highlightCircleSize: 5
                        },
                        underlayCallback: function (canvas, area, dygraph)
                        {

                            if (that.hasClass('dustbars'))
                            {
                                var normCoords0 = dygraph.toDomCoords(0, 12);
                                var normCoords1 = dygraph.toDomCoords(0, 35.4);
                                var normCoords2 = dygraph.toDomCoords(0, 55.4);
                                var normCoords3 = dygraph.toDomCoords(0, 150.4);
                                var normCoords4 = dygraph.toDomCoords(0, 250.4);
                                var normCoords5 = dygraph.toDomCoords(0, 9999.9);

                                canvas.fillStyle = 'rgba(126,0,35,.5)';
                                canvas.fillRect(area.x, normCoords4[1], area.w, normCoords5[1]);
                                canvas.fillStyle = 'rgba(102,0,153,.5)';
                                canvas.fillRect(area.x, normCoords3[1], area.w, normCoords4[1] - normCoords3[1]);
                                canvas.fillStyle = 'rgba(204,0,51,.5)';
                                canvas.fillRect(area.x, normCoords2[1], area.w, normCoords3[1] - normCoords2[1]);
                                canvas.fillStyle = 'rgba(255,153,51,.5)';
                                canvas.fillRect(area.x, normCoords1[1], area.w, normCoords2[1] - normCoords1[1]);
                                canvas.fillStyle = 'rgba(255,222,51,.5)';
                                canvas.fillRect(area.x, normCoords0[1], area.w, normCoords1[1] - normCoords0[1]);
                                canvas.fillStyle = 'rgba(0,153,102,.5)';
                                canvas.fillRect(area.x, normCoords0[1], area.w, normCoords0[1]);

                            }

                        }
                    }
            );

            graphs.push([$(this).data('name'), g, data]); //our name.
        });
/*        createPeriodicUpdate(graphs, 5000);*/

    }
