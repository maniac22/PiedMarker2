<?php
require_once("configPrototype.php"); // Include Global Configuration
//require_once("libPrototype.php");    // Include Library Functions

class program_file {

	public $path;       ///< Folder containing the file
	public $filename;   ///< Filename without extension
	public $extension;  ///< Extension, based on the language
	public $fullname;   ///< Absolute Path and Filename
	public $sourcefile; ///< Replaces sourcefile in commands
	public $id;         ///< Submission ID
	public $commands;   ///< Commands with Keywords Replaced
	public $tests;       ///< Tests with Keywords Replaced
	public $timelimit;   ///< Timelimit. Currently only used by the matlab marker
	public $firstname;
	public $lastname;
	public $userid;

	/**
	 * Constructor
	 * @param array $lang Array containing information about the Language
	 * @param string $sourcecode All the sourcecode to be written to the file
	 * @param string $input Optional Input data to be written to file
	 */
	function __construct($lang, $sourcecode, $markerid, $timelimit, $sourcepath = "", $extra_path = "", $firstname = "", $lastname = "",   $userid = "",$evaluator=false) {
		// Get filename extension from $lang
		$this->extension = $lang['extension']; //TODO allow override from student file
		// All files are called source
		$this->filename = "source";
		$this->sourcefile = "$this->filename.$this->extension";
		$this->timelimit=$timelimit;
		$this->markerid = $markerid;
		$this->firstname = $firstname;
		$this->lastname = $lastname;
		$this->userid = $userid;
		$this->evaluator=$evaluator;
		// Get the Submission ID
		$this->id = date("Ymd-His-") . uniqid("", $more_entropy = true);

		// Construct the path
		$this->path = settings::$temp;
		if(trim($extra_path) != "" and substr($extra_path, -1) != "/"){
			$extra_path .= "/";
		}
		$this->path = "$this->path/$this->extension/$extra_path$this->id";
		// Construct the full path/file
		$this->fullname = "$this->path/$this->filename.$this->extension";

		// Create the folder
		mkdir($this->path, 0777, $recursive = true);
		// Save the code
		file_put_contents($this->fullname, $sourcecode);

		error_log("cp -r \"$sourcepath/*\" \"" . $this->path . "\"");
		//$success = recurse_copy($sourcepath, $this->path);
		//system("cp -r \"$sourcepath/\" \"" . $this->path . "\"", $success);
		$success=true;
		if(!$success){
			$exception = new Exception("Marker Error" . $success);
			$exception->details = array(result_marker_error, -1, array("Marker Error: Unable to copy testcases."));
			throw $exception;
		}
		// setup commands
		$this->compile_commands = $lang['compile'];
		$this->compile_tests = $lang['compile_tests'];
		$this->commands = $lang['commands'];
	}

	/**
	 * Iterates through commands from the language description and replaces
	 * keywords with the relevant paths
	 * @param array $comm Array of commands
	 * @return Array of commands with keywords replaced
	 */
	function setup_commands($comm, $inputfile, $outputfile, $args = '') {
		$temp = $comm;
        $inputfilenoex = substr($inputfile, 0, strpos($inputfile, '.'));

		//if($args != ''){
		//	$args = '"'.$args.'"'; // Add quotes around args if it exists
		//}

		foreach ($temp as $key => $value) {
			$value = str_replace("~sourcefile~", $this->sourcefile, $value);
			$value = str_replace("~sourcefile_noex~", $this->filename, $value);
			$value = str_replace("~input~", $inputfile, $value);
			$value = str_replace("~input_noex~", $inputfilenoex, $value);

			$value = str_replace("~output~", $outputfile, $value);
			$value = str_replace("~markers~", getcwd(), $value);
			$value = str_replace("~path~", $this->path, $value);
			$value = str_replace("~args~", $args, $value);
			$value = str_replace("~timeout~", $this->timelimit, $value);
			$value = str_replace("~markerid~", $this->markerid, $value);
$firstname = preg_replace('/\s+/', '', $this->firstname);
$lastname = preg_replace('/\s+/', '', $this->lastname);
			$value = str_replace("~firstname~", $firstname, $value);
			$value = str_replace("~lastname~", $lastname, $value);
			$value = str_replace("~userid~", $this->userid, $value);
			$temp[$key] = $value;
		}

		return $temp;
	}

	/**
	 * Destructor deletes the relevant directory unless settings::$keep_files is
	 * set to true.
	 */
	function __destruct() {
		if (!settings::$keep_files) {
			deleteDirectory($this->path);
		}
	}

}

/**
 * Recursively delete a directory
 * @param string $dir Directory to Delete
 * @return boolean Success/Failure
 */
function deleteDirectory($dir) {
	// If the folder/file doesn't exist return
	if (!file_exists($dir))
		return true;
	// If it isn't a directory, remove and return
	if (!is_dir($dir) || is_link($dir))
		return unlink($dir);
	// For each item in the directory
	foreach (scandir($dir) as $item) {
		// Ignore special folders
		if ($item == '.' || $item == '..')
			continue;
		// Recursively delete items in the folder
		if (!deleteDirectory($dir . "/" . $item)) {
			//chmod($dir . "/" . $item, 0777);
			if (!deleteDirectory($dir . "/" . $item))
				return false;
		};
	}
	return rmdir($dir);
}

//https://hotexamples.com/examples/-/-/recurse_copy/php-recurse_copy-function-examples.html
function recurse_copy($source, $dest)
{
	// Check for symlinks
	if (is_link($source)) {
		return symlink(readlink($source), $dest);
	}
	// Simple copy for a file
	if (is_file($source)) {
		return copy($source, $dest);
	}
	// Make destination directory
	if (!is_dir($dest)) {
		mkdir($dest);
	}
	// Loop through the folder
	$dir = dir(getcwd());
	while (false !== ($entry = $dir->read())) {
		// Skip pointers
		if ($entry == '.' || $entry == '..') {
			continue;
		}
		// Deep copy directories
		recurse_copy("{$source}/{$entry}", "{$dest}/{$entry}");
	}
	// Clean up
	$dir->close();
	return true;
}


$inputJSON =<<<JSN
{"userid":"2","n":"5","language":"4","cpu_limit":"0.1","mem_limit":"0","pe_ratio":"0","callback":"1710409.ms.wits.ac.za/moodleDev/mod/assign/feedback/customfeedback/update_record.php?assign_id=66&question_id=0","testcase":{"url":"http://1710409.ms.wits.ac.za/opti.zip","contenthash":"371f4d2751056d8ee42faebc7e6e5f3e5c0a245b","pathnamehash":"31231126c5d3f8a617652fbe92b73dbf6cd8a98f"},"source":{"content":"cHJpbnQoImhlbGxvIik=","ext":"py"},"evaluator":{"content":"Yz1zdHIoaW5wdXQoKSkKcHJpbnQoYyxsZW4oYykp","ext":"py"},"mode":"0","customfeedback_token":"1e6947ac7fb3a9529a9726eb692c8cc5","markerid":"1","firstname":"mpho","lastname":"mpho"}
JSN;
//$inputJSON = file_get_contents('php://input');//Get input from the client
$input = json_decode($inputJSON, TRUE); // Decode the JSON object
$markerid = $input["markerid"];
$auth = $input["customfeedback_token"];
$grade=100;
$userid = $input["userid"];
$firstname = $input["firstname"];
$lastname = $input["lastname"];
$language = $input["language"];
$cpu_limit = $input["cpu_limit"];
$mem_limit = $input["mem_limit"];
$pe_ratio = $input["pe_ratio"];
$n=$input["n"];
$type =$input["mode"];
$callback = $input["callback"]; // Post back to this address after marking
$testcase = $input["testcase"]; // url, contenthash, pathnamehash
$source = base64_decode($input["source"]["content"]);     // Decode the Base64
settings::$temp .= "/$markerid";
// Start buffering output
//ob_start();
// var_dump(settings::$temp);

if(!isset($input["evaluator"]["content"]) and $type==1)die("{Evaluator source not provided}");
if($auth != settings::$auth_token['customfeedback_token']){
	error_log('{"status" : "Bad auth"}');
	die('{"status" : "Bad auth"}');
}

$tests=NULL;

//student's code! 
$evaluate=false;
$lang = NULL;
$prefix = $userid . "/";
$code = new program_file($lang, $source, $markerid, $cpu_limit, $tests["path"], $prefix, $firstname, $lastname, $userid,false);
echo $grade;

?>
