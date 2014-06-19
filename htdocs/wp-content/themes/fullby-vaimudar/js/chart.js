var $charts = function() {
	
	var options = {
		series: {
	        lines: { show: true },
	        points: { show: true }
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
		
		jQuery.plot('#chart', $chart_data, options);
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
	$data 	= jQuery.parseJSON(jQuery('#chart_data').attr('value'));
	
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