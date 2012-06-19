js:

	java -jar lib/google-compiler/compiler-20100616.jar --js www/javascript/airport.timer.js > www/javascript/airport.timer.min.js
	java -jar lib/google-compiler/compiler-20100616.jar --js www/javascript/jquery.stopwatch.js > www/javascript/jquery.stopwatch.min.js
templates:
	php -q bin/compile-templates.php