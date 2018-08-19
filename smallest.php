<?php 
function getSmallest($Arr){
	$max = max($Arr);
	$min = min($Arr);
	$missing = array();
	$smallest = $min;
	
	// if no poisitive value in the input array , stop execution and return 1
	if($max < 0){
		return 1;
	}
 
	// go through the array and get values not in the array. Stack them into another array MISSING
	for ($i=1; $i < $max ; $i++) { 
		if(!in_array($i, $Arr)){
			     array_push($missing, $i);
		}
	}
    
    //if there is no missing value between the smallest and the highest value , then the next smallest value is definitely the next to the highest i.e HIGHEST + 1
	if(count($missing) == 0){
		$smallest = $max+1;
	}else{
		$smallest = min($missing);
	}

	return $smallest;
}

//TEST
echo getSmallest([-1,-34,-4,1,2,3,44,5,5,555,6,6,67,7,7,8,8,9]);

?>