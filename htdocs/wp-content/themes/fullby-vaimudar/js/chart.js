var $charts = function() {
	var choiceContainer = $("#overviewLegend");
	var options = {
		    legend: {
		        show: true
		    },
		    series: {
		        points: {
		            show: true
		        },
		        lines: {
		            show: true
		        }
		    },
		    grid: {
		        hoverable: true
		    }
		};
	
//	Gráfico de linhas partidos x ano x repasse
	function party_year_value($data) {
		$chart_data = new Array();
		for( key in $data ) {
			$final_data = new Array();
			
			for( $i=0; $i < $data[key].length; $i++ ) {
				if( key != 0 ) {
					$final_data.push( [ $data[key][$i]['ano'], $data[key][$i]['valor'] ] );	
				}
			}
			
			if( key != 0 ) {
				$chart_data.push( { label: key, data: $final_data } );	
			}
		}
		
		var i = 0;
		jQuery.each($chart_data, function(key, val) {
		    val.color = i;
		    ++i;
		    l = val.label;
		    var li = $('<li />').appendTo(choiceContainer);
		    
		    $('<input name="' + l + '" id="' + l + '" type="checkbox" checked="checked" />').appendTo(li);
		    $('<label>', {
		        text: l, 
		        'for': l
		    }).appendTo(li);
		});
		
		$('.legendColorBox > div').each(function(i){
			$(this).clone().prependTo(choiceContainer.find("li").eq(i));
		});
		
		var previousPoint = null;
		$("#chart").bind("plothover", function(event, pos, item) {
		    $("#x").text(pos.x.toFixed(2));
		    $("#y").text(pos.y.toFixed(2));

		    if (item) {
		        if (previousPoint != item.datapoint) {
		            previousPoint = item.datapoint;

		            $("#tooltip").remove();
		            var x = item.datapoint[0].toFixed(2),
		                y = item.datapoint[1].toFixed(2);

		            showTooltip(item.pageX, item.pageY, item.series.label + " R$ " + y);
		        }
		    } else {
		        $("#tooltip").remove();
		        previousPoint = null;
		    }
		});
		
		plotAccordingToChoices('chart', choiceContainer, $chart_data);
		
		choiceContainer.find("input").change(function(){
				var $d = plotAccordingToChoices('chart', choiceContainer, $chart_data);
				jQuery.plot('#chart', $d, options);
		});
		
		jQuery.plot('#chart', $chart_data, options);
	}
	
	function plotAccordingToChoices(placeholder, choiceContainer, results) {
	    var data = [];
	    choiceContainer.find("input:checked").each(function() {
	        var key = this.name;

	        for (var i = 0; i < results.length; i++) {
	            if (results[i].label === key) {
	                data.push(results[i]);
	                
	                return data;
	            }
	        }
	    });
	    
	    return data;
	}
	
	function showTooltip(x, y, contents) {
	    $('<div id="tooltip">' + contents + '</div>').css({
	        position: 'absolute',
	        display: 'none',
	        top: y + 5,
	        left: x + 15,
	        border: '1px solid #fdd',
	        padding: '2px',
	        backgroundColor: '#fee',
	        opacity: 0.80
	    }).appendTo("body").fadeIn(200);
	}
	
//	Gráfico de linhas de repasse total por ano
	function lines_by_year($total_year) {
		$chart_year = new Array();
		$final_data = new Array();
		
		for( $i=0; $i < $total_year.length; $i++ ) {
			$final_data.push( [ $total_year[$i]['ano'], $total_year[$i]['max_year'] ] );
		}
		
		$chart_year.push( { data: $final_data } );
		jQuery.plot('#chart_year', $chart_year, options);
	}
	
//	Gráfico de barras horizontais, distribuição total por partido
	function total_by_party($total_party) {
		$chart_party 	= new Array();
		$final_data 	= new Array();
		$tick_labels 	= new Array();
		
		for( $i=0; $i < $total_party.length; $i++ ) {
			$final_data.push( [ $total_party[$i]['max_party'], $i ] );
			$tick_labels.push( [$i, $total_party[$i]['partido']] );
		}
		$chart_party.push( { data: $final_data } );
		
		jQuery.plot($('#chart_party'), [  
				{  
				    data: $final_data,  
				    bars: {  
				        show: true,  
				        horizontal: true  
				    }  
				}  
			],  
			{ yaxis: { ticks: $tick_labels } }  
		);
	}
	
	return {
		party_year_value: party_year_value,
		lines_by_year: lines_by_year,
		total_by_party: total_by_party
	}
}();

jQuery(document).ready(function() {
	var $data 			= jQuery.parseJSON(jQuery('#chart_data').attr('value'));
	
	if( $data ) {
		$charts.party_year_value($data);
		
		if($data[0]) {
			var $total_year		= $data[0]['total_ano'];
			
			if($total_year) {
				$charts.lines_by_year($total_year);
			}
			
			var $total_party 	= $data[0]['total_partido'];
			
			if($total_party) {
				$charts.total_by_party($total_party);
			}
		}
	}
});