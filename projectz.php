<?php
require 'config.php';

// Get project name from user
$name = readLine('Enter new project name: ');

// Capitalize first letter
//$name = ucfirst($name);

echo "\r\nProject types:\r\n";

for ($i=0; $i < count($types); $i++) {
	echo $i . ': ' . $types[$i] . "\r\n";
}

echo "\r\n";

// Get project type from user
$type = readLine('What kind of project is it? (Enter a number): ');

switch ($type) {
	case '0':
		unityproject($name);
		break;
	case '1':
		$domain = readLine('Enter short domain (example.com): ');
		webproject($name, $domain);
		break;
	default:
		echo 'Error: type does not exist';
		break;
}

echo "Done!\n";