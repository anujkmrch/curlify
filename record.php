<?php

	# I wanted to do it for cli only,
	# so I checked for  server api
	if (php_sapi_name()=== 'cli'):
		# Include curlify class
		require("curlify.php");
		
		#initialize curlify object
		$c = new curlify();

		# I am taking option n a domain name
		# you can run the script by typing
		// php record.php -n "https://example.com"
		#uncomment the initial from the above line
		# php record.php -n domainname

		$opt = getopt("n:");

		$c->isDebug();
		
		$c->setUrl($opt["n"]);
		$c->setData("a","n");
		// $c->setData("a","u");
		// $c->setData("a","j");
		// $c->setData("a","kumar","surname");
		$c->requestNow(false,true);
	
	endif;
?>