<?php

	require("PostalMethods.php");
	
	$PM = new PostalMethods('USERNAME', 'PASSWORD');
	
	$PM->setFile('test.doc'); //Body of letter
	$PM->setDescription('Test letter');
	$PM->setAddress("John Smith", "", "", "123 Main St", "", "New York", "NY", 12345);
	
	
	try {
    	$id = $PM->SendLetterAndAddress();
    	echo "ID: $id";
    } 
    catch (Exception $e) {
	    echo 'Caught exception: ',  $e->getMessage(), " (".$e->getCode().")\n";
	}
	
	



