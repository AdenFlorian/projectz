<?php
define('DS', "\\");
// Directories
define('WORK', getcwd());
define('PARENT',dirname(getcwd()));
define('TMP', WORK . DS . "tmp");
define('DATA', WORK . DS . 'data');
define('XAMPP', "D:\\xampp");
define('HTDOCS', XAMPP . DS . "htdocs");
define('HTTPDVHOSTS', XAMPP . DS . "apache\\conf\\extra\\httpd-vhosts.conf");
define('HOSTS', "C:\\Windows\\System32\\drivers\\etc\\hosts");
define('PROJECTS', $projectsPath);
// Zip files
define('UNITY_BUILDS_ZIP', DATA . DS . "BuildsFolders.zip");
define('UNITY_INNER_ZIP', DATA . DS . "InnerUnityFolders.zip");
define('WWW_ZIP', DATA . DS . "www.zip");

// Project types
$types = array(
	'0' => 'Unity',
	'1' => 'Website'
);

// Util functions

function readLine($prompt)
{
	echo $prompt;
	return stream_get_line(STDIN, 1024, PHP_EOL);
}

function unzip($inputZip, $outputFolder)
{
	$zip = new ZipArchive;
	if ($zip->open($inputZip)) {
		$zip->extractTo($outputFolder);
		$zip->close();
		return true;
	} else {
		return false;
	}
}

// Core functions

function unityproject($name)
{
	$projectPath = PROJECTS . DS . $name;
	//$unityPath = $projectPath . DS . "$name Unity Project";
	//$buildsPath = $projectPath . DS . 'Builds';

	// Make main project folder
	mkdir($projectPath);

	// Create Unity project folder
	//mkdir($unityPath);

	// Extract Builds folder stuff
	unzip(UNITY_BUILDS_ZIP, $projectPath);

	// Extract Unity Folder stuff
	unzip(UNITY_INNER_ZIP, $projectPath);

	// Edit readme.md
	editReadme($name, $projectPath);

	// Change company folder is assets folder to game name
	renameCompanyFolder($projectPath . DS . 'Assets' . DS . $name, $projectPath . DS . 'Assets' . DS . 'Company_Name');
	
	chdir($projectPath);
	
	// git init
	exec('git init');
	$buildscriptsDirName = '_buildscripts';
	
	exec('git clone --depth=1 https://github.com/AdenFlorian/unity_build_scripts.git ' . $buildscriptsDirName);
	
	echo 'Removing .git dir...';
	
	exec('rmdir /S /Q ' . $buildscriptsDirName . DS . '.git');
}

function webproject($name, $domain)
{
	$projectPath = PARENT . DS . $name;

	// Make main project folder
	//mkdir($projectPath);
	
	// Download h5bp master zip
	echo "downloading html5 boilerplate...\n";
	exec('wget -O "' . TMP . DS . $name . '.zip" https://github.com/h5bp/html5-boilerplate/archive/master.zip --no-check-certificate');

	// Extract www folder stuff
	echo "unzipping html5 boilerplate...\n";
	unzip(TMP . DS . $name . '.zip', TMP);
	
	// Delete zip
	echo "deleting zip file...\n";
	unlink(TMP . DS . $name . '.zip');
	
	// Move src dir contents to $projectPath
	echo "moving html5 boilerplate...\n";
	//rename(TMP . DS . 'html5-boilerplate-master\src', $projectPath);
	exec('move "' . TMP . DS . 'html5-boilerplate-master\src" "' . $projectPath . '"');

	// Edit readme.md
	editReadme($name, $projectPath);
	
	// Make symbolic link to htdocs folder
	echo "making symbolic link of project folder to htdocs folder...\n";
	//mkdir(HTDOCS . DS . $name);
	//symlink($projectPath, HTDOCS . DS . $name);
	exec("mklink /j \"" . HTDOCS . DS . $name . "\" \"" . $projectPath . "\"");
	
	// Add VirtualHost entry in httpd.conf
	echo "creating virtual host entry for apache...\n";
	file_put_contents(HTTPDVHOSTS, "\r\n\r\n<VirtualHost *:80>" .
									   "\r\n\tServerName local." . $domain .
									   "\r\n\tDocumentRoot \"" . HTDOCS . DS . $name . "\"" .
								   "\r\n</VirtualHost>", FILE_APPEND);
	
	// Add entry to hosts file
	echo "adding local domain to hosts file...\n";
	file_put_contents(HOSTS, "\n127.0.0.1 local." . $domain, FILE_APPEND);
	
	//restart apache
	echo "restarting apache...\n";
	exec("net stop Apache2.4 && net start Apache2.4");
	
	// open new site in chrome
	exec('start chrome "local.' . $domain . '"');
}

// Other functions

function editReadme($name, $readmeFolder)
{
	$str = file_get_contents(DATA . DS . 'README.md');
	$str = str_replace("_Project_", "_" . $name . "_", $str);
	file_put_contents($readmeFolder . DS . 'README.md', $str);
}

function renameCompanyFolder($newName, $folder)
{
	rename($folder, $newName);
}