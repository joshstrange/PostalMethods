## PostalMethods PHP Library

PostalMethods is a service that allows you to send physical letters (Snail Mail) by using their SOAP API. This library allows you to easily send a letter and it has support for all of the error codes you may encounter.

##### [PostalMethods](http://postalmethods.com)

### Usage

As spelled out in example.php to send and letter you only need the following code:

	require("PostalMethods.php");
	
	$PM = new PostalMethods('joshstrange', '9pL2uLm3');
	
	$PM->setFile('test.doc'); //Body of letter
	$PM->setDescription('Test letter');
	$PM->setAddress("John Smith", "[Name Second Line]", "[Company]", "123 Main St", "[Address Second Line]", "New York", "NY", 12345);
	
	
	try {
    	$id = $PM->SendLetterAndAddress();
    	echo "ID: $id";
    } 
    catch (Exception $e) {
	    echo 'Caught exception: ',  $e->getMessage(), " (".$e->getCode().")\n";
	}

### About Me

My name is [Josh Strange](http://josh.vc) and I am a web and mobile developer living in Lexington, KY. I wrote this library because I wanted to interface with PostalMethods for a project I am currently working on and all they had were snippits of php code. 

### License

PostalMethods PHP Library is open-sourced software licensed under the MIT License.