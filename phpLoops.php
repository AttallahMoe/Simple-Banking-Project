<?php

/*
1. Create an array/collection of numbers (initialize it with any number of numbers (more than 1)
 in numerical order, with or without duplicates)

2. Create a loop that loops over each number and shows their value.

3. Have the loop output only even numbers regardless of how long the array/collection is.

4. Briefly explain how you achieved the correct output.

5. Add screenshot of the output

6. upload the code/file to github and submit the github link
*/



function newline(){

	echo "<br>\n";
}
//1

$nums = array(1, 2, 4, 5, 9);

//2

for($i = 0; $i < count($nums); $i++){
	echo $nums[$i];
        newline();
}
//3
for($i = 0; $i < count($nums); $i++){
        if($nums[$i]%2 == 0){
        echo $nums[$i];
        echo "\n";
       }
}

//4
/*
The way I acheived the correct output is by using my past knowledge of java. I created a for loop
that incremented by 1 and maxed out by the length of the array I created in step 1. To make it 
only output the even numbers i used the % and if number in the arrary had a remainder of 0. then it
must be even. 

*/


?>

