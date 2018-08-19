<?php 
function getSmallest($Arr){
	$max = max($Arr);
	$min = min($Arr);
	$missing = array();
	$smallest = $min;
	for ($i=1; $i < $max ; $i++) { 
		if(!in_array($i, $Arr)){
			     array_push($missing, $i);
		}
	}

	if(count($missing) == 0){
		$smallest = $max+1;
	}else{
		$smallest = min($missing);
	}

	return $smallest;
}


echo getSmallest([-1,0,2,4,2,9]);

?>