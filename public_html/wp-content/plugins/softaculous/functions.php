<?php

if (!defined('ABSPATH')){
    exit;
}

/* function softaculous_died(){
	print_r(error_get_last());
}
register_shutdown_function('softaculous_died'); */

include_once('soft_lang.php');
 
/*
 * Fetch the value of option name from options table
 *
 * @param		string $option_name option_name to retrieve from table.
 * @param		mixed $default_value Default value to return when the option does not exist.
 * @param		int $site_id Site ID to update. Used for multisite installations only.
 * @param		bool $use_cache Whether to use cache or not. Used for multisite installations only.
 * @returns		string The option value based on $option_name
 * @since		1.0
 *
 * @refer		get_option()
 * @link			https://developer.wordpress.org/reference/functions/get_option/
 */
function softaculous_get_option($option_name, $default_value = false, $site_id = null){
	
    if($site_id !== null && is_multisite()){
        return get_site_option($option_name, $default_value);
    }
    return get_option($option_name, $default_value);
}

/*
 * Generate a random string for the given length
 *
 * @param		int $length The number of charactes that should be returned
 * @return		string Randomly geterated string of the given number of charaters
 * @since		1.0
 */
function softaculous_srandstr($length){
	$randstr = "";	
	for($i = 0; $i < $length; $i++){	
		$randnum = mt_rand(0,61);		
		if($randnum < 10){		
			$randstr .= chr($randnum+48);			
		}elseif($randnum < 36){		
			$randstr .= chr($randnum+55);			
		}else{		
			$randstr .= chr($randnum+61);			
		}		
	}	
	return strtolower($randstr);
}

/*
 * A function to display preformatted array. Basically adds the <pre> before and after the print_r() output.
 *
 * @param        array $array
 * @return       string Best for HTML dump of an array.
 * @since     	 1.0
 */
function softaculous_print($array){
	echo '<pre>';
	print_r($array);
	echo '</pre>';
}

/**
 * A function to return all the installed plugins and their description.
 *
 * @since     	 1.0
 */
function softaculous_get_plugins(){
	return get_plugins();
}

/**
 * A function to check if the plugin is active or not.
 *
 * @param        string $pluginBasename slug of the plugin
 * @since     	 1.0
 */
function softaculous_is_plugin_active($pluginBasename){
	return is_plugin_active($pluginBasename);
}

/**
 * A function to check if the plugin is installed.
 *
 * @param        string $plslug slug of the plugin
 * @since     	 1.0
 */
function softaculous_is_plugin_installed($plslug){
	
	$all_installed_plugins = softaculous_get_plugins();
	$slugs = array_keys($all_installed_plugins);

	$is_installed = 0;
	foreach($all_installed_plugins as $key => $val){
		if(strpos($key, $plslug) !== false){
			$is_installed = true;
			break;
		}
	}

	return $is_installed;
}

/**
 * Activates the plugins on the website
 *
 * @param        array $plugin_slug array of plugin's slug values
 * @since     	 1.0
 */
function softaculous_activate_plugin($plugin_slugs = array()){
	
	$result = activate_plugins($plugin_slugs);
	
	if(is_wp_error($result)){
		return false;
	}
	
	return true;
	
}

/**
 * Fetch the plugin's main file to generate the plugin slug
 *
 * @param		string $path path of the plugin directory
 * @param		string $slug directory name of the plugin
 * @since		1.3
 */
function softaculous_get_plugin_path($path, $slug){
	
	$path = softaculous_cleanpath($path);
	
	if(softaculous_sfile_exists(ABSPATH.'wp-content/plugins/'.$slug.'/'.$slug.'.php')){
		return $plugin_path = $slug.'/'.$slug.'.php';
	}
	
	$list = softaculous_filelist($path, 0);
	$plugin_files = array();
	
	foreach($list as $lk => $lv){
		if(!is_dir($lk)){
			$plugin_files[basename($lk)] = $lk;
		}
	}
	
	foreach($plugin_files as $pk => $pv){
		$data = softaculous_sfile($pv);
		if(preg_match('/\n(\s*?)(\*?)(\s*?)Plugin(\s*?)Name:(.*?)\n/is',$data)){
			return $plugin_path = $slug.'/'.$pk;
			break;
		}
	}
	
	return $plugin_path = $slug.'/'.$slug.'.php';
}

/**
 * Replaces '\\' with '/' and Truncates / at the end.
 * e.g. E:\\path is converted to E:/path
 *
 * @param		string $path
 * @returns		string The new path which works everywhere !
 * @since		1.3
 */
function softaculous_cleanpath($path){
	
	$path = str_replace('\\\\', '/', $path);
	$path = str_replace('\\', '/', $path);
	return rtrim($path, '/');
}

/* The below function will list all folders and files within a directory
 *
 * @param		string $startdir specify the directory to start from; format: must end in a "/"
 * @param		bool $searchSubdirs True if you want to search subdirectories
 * @param		bool $directoriesonly True if you want to only return directories
 * @param		$maxlevel "all" or a number; specifies the number of directories down that you want to search
 * @param		integer $level directory level that the function is currently searching
 * @since		1.3
*/
function softaculous_filelist($startdir="./", $searchSubdirs=1, $directoriesonly=0, $maxlevel="all", $level=1, $reset = 1) {
	//list the directory/file names that you want to ignore
	$ignoredDirectory = array();
	$ignoredDirectory[] = ".";
	$ignoredDirectory[] = "..";
	$ignoredDirectory[] = "_vti_cnf";
	global $softaculous_directorylist;    //initialize global array

	if(substr($startdir, -1) != '/'){
		$startdir = $startdir.'/';
	}
   
	if (is_dir($startdir)) {
		if ($dh = opendir($startdir)) {
			while (($file = readdir($dh)) !== false) {
				if (!(array_search($file,$ignoredDirectory) > -1)) {
					if (@filetype($startdir . $file) == "dir") {

						//build your directory array however you choose;
						//add other file details that you want.

						$softaculous_directorylist[$startdir . $file]['level'] = $level;
						$softaculous_directorylist[$startdir . $file]['dir'] = 1;
						$softaculous_directorylist[$startdir . $file]['name'] = $file;
						$softaculous_directorylist[$startdir . $file]['path'] = $startdir;
						if ($searchSubdirs) {
							if ((($maxlevel) == "all") or ($maxlevel > $level)) {
								softaculous_filelist($startdir . $file . "/", $searchSubdirs, $directoriesonly, $maxlevel, ($level + 1), 0);
							}
						}


					} else {
						if (!$directoriesonly) {

							//  echo substr(strrchr($file, "."), 1);
							//if you want to include files; build your file array 
							//however you choose; add other file details that you want.
							$softaculous_directorylist[$startdir . $file]['level'] = $level;
							$softaculous_directorylist[$startdir . $file]['dir'] = 0;
							$softaculous_directorylist[$startdir . $file]['name'] = $file;
							$softaculous_directorylist[$startdir . $file]['path'] = $startdir;
						}
					}
				}
			}
			closedir($dh);
		}
	}

	if(!empty($reset)){
		$r = $softaculous_directorylist;
		$softaculous_directorylist = array();
		return($r);
	}
}

/**
 * De-activates the plugins on the website
 *
 * @param        array $plugin_slug array of plugin's slug values
 * @since     	 1.0
 */
function softaculous_deactivate_plugin($plugin_slugs = array()){
	
	$result = deactivate_plugins($plugin_slugs);
	
	if(is_wp_error($result)){
		return false;
	}
	
	return true;
}

/**
 * Fetch the list of outdated plugins on the website
 *
 * @since     	 1.0
 */
function softaculous_get_outdated_plugins(){
	global $softaculous_wp_config, $softaculous_error;
	
	// Get the list of active plugins
	$squery = 'SELECT `option_value` FROM `'.$softaculous_wp_config['dbprefix'].'options` WHERE `option_name` = "active_plugins";';	
	$sresult = softaculous_sdb_query($squery, $softaculous_wp_config['softdbhost'], $softaculous_wp_config['softdbuser'], $softaculous_wp_config['softdbpass'], $softaculous_wp_config['softdb']);
	
	$active = array();
	$active = unserialize($sresult[0]['option_value']);

	foreach($active as $plugin_file){
		$plugin_data = array();
		if (!softaculous_sfile_exists($softaculous_wp_config['plugins_root_dir'].'/'.$plugin_file)){
			continue;
		}

		$plugin_data = softaculous_get_plugin_data($softaculous_wp_config['plugins_root_dir'].'/'.$plugin_file);

		if(empty($plugin_data['Plugin Name'])){
			continue;
		}else{
			$plugin_data['Name'] = $plugin_data['Plugin Name'];
		}

		$plugins[$plugin_file] = $plugin_data;
	}
	
	uasort($plugins, 'softaculous_sort_uname_callback');
	
	$to_send = (object) compact('plugins', 'active');
	$options = array('plugins' => serialize($to_send));
	
	// Check the WordPress API to get the list of outdated plugins
	$raw_response = wp_remote_post('http://api.wordpress.org/plugins/update-check/1.0/', array('body' => $options));
	$body = wp_remote_retrieve_body($raw_response);
	$outdated_plugins = unserialize($body);
	
	// We need the Plugin name to send via email
	foreach($outdated_plugins as $plugin_file => $p_data){
		if(!empty($plugins[$plugin_file]['Name'])){
			$outdated_plugins[$plugin_file]->Name = $plugins[$plugin_file]['Name'];
		}
	}
	
	return $outdated_plugins;
}

/**
 * This is to extract the plugin details from the plugin file
 *
 * @param        string $pluginPath directory path of the installed plugin
 * @since     	 1.0
 */
function softaculous_get_plugin_data($pluginPath = ''){
	global $softaculous_plugin_details;
	
	$softaculous_plugin_details = array();
	
	if(empty($pluginPath)){
		return false;
	}
	
	$tmp_data = array();
	$data = array();
	$data = softaculous_sfile($pluginPath);
	preg_replace_callback('/\n(\s*?)(\*?)(\s*?)Plugin(\s*?)Name:(.*?)\n/is', 'softaculous_plugin_callback', $data, 1);
	preg_replace_callback('/\n(\s*?)(\*?)(\s*?)Plugin(\s*?)URI:(.*?)\n/is', 'softaculous_plugin_callback', $data, 1);
	preg_replace_callback('/\n(\s*?)(\*?)(\s*?)Description:(.*?)\n/is', 'softaculous_plugin_callback', $data, 1);
	preg_replace_callback('/\n(\s*?)(\*?)(\s*?)Version:(.*?)\n/is', 'softaculous_plugin_callback', $data, 1);
	
	return $softaculous_plugin_details;
}

// This is a callback function for preg_replace in softaculous_get_plugin_data
function softaculous_plugin_callback($matches){
    global $softaculous_plugin_details;
	$tmp_data = explode(':', $matches[0], 2);
	$tmp_data[0] = str_replace('*', '', $tmp_data[0]);
	$key = trim($tmp_data[0]);
	$value = trim($tmp_data[1]);
	$softaculous_plugin_details[$key] = $value;
}

/**
 * A function to check if the theme is installed.
 *
 * @param        string $thslug slug of the theme
 * @since     	 1.0
 */
function softaculous_is_theme_installed($thslug){
	
	$all_installed_themes = wp_get_themes();
	$slugs = array_keys($all_installed_themes);
    
	if(isset($all_installed_themes[$thslug])){
		return true;
	}
	
	return false;
}

/**
 * Returns active theme for the website.
 *
 * @since     	 1.0
 */
function softaculous_get_active_theme(){
	$raw_list = wp_get_theme();
	return softaculous_get_themes_details(array($raw_list->stylesheet));
}

/**
 * A function to return all the installed themes and their description.
 *
 * @since     	 1.0
 */
function softaculous_get_installed_themes(){
	$raw_list = wp_get_themes();
	$theme_slugs = array_keys($raw_list);

	return softaculous_get_themes_details($theme_slugs);
}

/**
 * Returns details of the themes from wordPress.org.
 *
 * @param        array $themes array of themes
 * @since     	 1.0
 */
function softaculous_get_themes_details($themes = array()){
	global $softaculous_wp_config, $softaculous_error;
	
	$apiurl ='http://api.wordpress.org/themes/info/1.0/';
	$softaculous_theme_details = array();
	foreach($themes as $current_theme){
		$theme_data = softaculous_get_theme_data($softaculous_wp_config['themes_root_dir'].'/'.$current_theme.'/style.css');
	
		$post_data = array(
				'action' => 'theme_information',
				'request' => serialize( (object) array( 'slug' => $current_theme )));
		
		$raw_response = wp_remote_post($apiurl, array('body' => $post_data));
    		$body = wp_remote_retrieve_body($raw_response);
    		$api_data = unserialize($body);
		
		$softaculous_theme_details[$current_theme] = $api_data;
		$softaculous_theme_details[$current_theme]->installed_version = $theme_data['Version'];
		
		if(softaculous_sversion_compare($theme_data['Version'], $api_data->version, '<')){
			$softaculous_theme_details[$current_theme]->new_version = $api_data->version;
		}
	}
	return $softaculous_theme_details;
}

/**
 * This is to extract the theme details from the theme file
 *
 * @param        string $themePath directory path of the installed theme
 * @since     	 1.0
 */
function softaculous_get_theme_data($themePath = ''){

	global $softaculous_theme_details;
	
	$softaculous_theme_details = array();
	
	if(empty($themePath)){
		return false;
	}
	
	$tmp_data = array();
	$data = array();
	$data = softaculous_sfile($themePath);
	preg_replace_callback('/\n(\s*?)(\*?)(\s*?)Theme(\s*?)Name:(.*?)\n/is', 'softaculous_theme_callback', $data, 1);
	preg_replace_callback('/\n(\s*?)(\*?)(\s*?)Theme(\s*?)URI:(.*?)\n/is', 'softaculous_theme_callback', $data, 1);
	preg_replace_callback('/\n(\s*?)(\*?)(\s*?)Description:(.*?)\n/is', 'softaculous_theme_callback', $data, 1);
	preg_replace_callback('/\n(\s*?)(\*?)(\s*?)Version:(.*?)\n/is', 'softaculous_theme_callback', $data, 1);
	preg_replace_callback('/\n(\s*?)(\*?)(\s*?)Author:(.*?)\n/is', 'softaculous_theme_callback', $data, 1);
	preg_replace_callback('/\n(\s*?)(\*?)(\s*?)Author(\s*?)URI:(.*?)\n/is', 'softaculous_theme_callback', $data, 1);
	
	return $softaculous_theme_details;
}

// This is a callback function for preg_replace used in softaculous_get_theme_data
function softaculous_theme_callback($matches){
global $softaculous_theme_details;
	$tmp_data = explode(':', $matches[0], 2);
	$tmp_data[0] = str_replace('*', '', $tmp_data[0]);
	$key = trim($tmp_data[0]);
	$value = trim($tmp_data[1]);
	$softaculous_theme_details[$key] = $value;
}

/**
 * Activates the theme on the website
 *
 * @param        string $theme_slug slug of the theme
 * @since     	 1.0
 */
function softaculous_activate_theme($theme_slug = array()){
	global $softaculous_error, $softaculous_lang;
	
	$theme_root = $softaculous_wp_config['themes_root_dir'].'/'.$theme_slug[0];

	$res = switch_theme($theme_root, $theme_slug[0]);

	if(is_wp_error($res)) {
		$softaculous_error = $res->get_error_message();
	}elseif($res === false) {
		$softaculous_error = $softaculous_lang['action_failed'];
	}

	if(!empty($softaculous_error)){
		return false;
	}

	return true;
}

/**
 * Function to delete a theme
 *
 * @param        string $theme_slug slug of the theme
 * @since     	 1.0
 */
function softaculous_delete_theme($theme_slug = array()){
	global $softaculous_error, $softaculous_lang;
    
	foreach($theme_slug as $slug){
		$res = delete_theme($slug);
	}
    
	if(is_wp_error($res)) {
		$softaculous_error = $res->get_error_message();
		
	}elseif($res === false) {
		$softaculous_error = $softaculous_lang['action_failed'];
	}
    
	if(!empty($softaculous_error)){
		return false;
	}
	
	return true;
}

/**
 * Takes care of Slashes
 *
 * @param		string $string The string that will be processed
 * @return		string A string that is safe to use for Database Queries, etc
 * @since		1.0
 */
function softaculous_inputsec($string){

	//get_magic_quotes_gpc is depricated in php 7.4
	if(softaculous_sversion_compare(PHP_VERSION, '7.4', '<')){
		if(!get_magic_quotes_gpc()){
		
			$string = addslashes($string);
		
		}else{
		
			$string = stripslashes($string);
			$string = addslashes($string);
		
		}
	}else{
		$string = addslashes($string);
	}
	
	// This is to replace ` which can cause the command to be executed in exec()
	$string = str_replace('`', '\`', $string);
	
	return $string;

}

/**
 * Converts Special characters to html entities
 *
 * @param        string $string The string containing special characters
 * @return       string A string containing special characters replaced by html entities of the format &#ASCIICODE;
 * @since     	 1.0
 */
function softaculous_htmlizer($string){

	$string = htmlentities($string, ENT_QUOTES, 'UTF-8');
	
	preg_match_all('/(&amp;#(\d{1,7}|x[0-9a-fA-F]{1,6});)/', $string, $matches);
	
	foreach($matches[1] as $mk => $mv){		
		$tmp_m = softaculous_entity_check($matches[2][$mk]);
		$string = str_replace($matches[1][$mk], $tmp_m, $string);
	}
	
	return $string;
	
}

/**
 * Used in function htmlizer()
 *
 * @param        string $string
 * @return       string
 * @since     	 1.0
 */
function softaculous_entity_check($string){
	
	//Convert Hexadecimal to Decimal
	$num = ((substr($string, 0, 1) === 'x') ? hexdec(substr($string, 1)) : (int) $string);
	
	//Squares and Spaces - return nothing 
	$string = (($num > 0x10FFFF || ($num >= 0xD800 && $num <= 0xDFFF) || $num < 0x20) ? '' : '&#'.$num.';');
	
	return $string;
			
}

/**
 * OPTIONAL REQUEST of the given REQUEST Key
 *
 * @param        string $name The key of the $_REQUEST array i.e. the name of the input / textarea text 
 * @param        string $default The value to return if the $_REQUEST[$name] is NOT SET
 * @return       string Returns the string if the REQUEST is there otherwise the default value given.
 * @since     	 1.0
 */
function softaculous_optREQ($name, $default = ''){

global $softaculous_error;

	//Check the POSTED NAME was posted
	if(isset($_REQUEST[$name])){
	
		return trim(sanitize_text_field($_REQUEST[$name]));
		
	}else{
		return $default;
	}
}

/**
 * OPTIONAL POST of the given POST Key
 *
 * @param        string $name The key of the $_POST array i.e. the name of the input / textarea text 
 * @param        string $default The value to return if the $_POST[$name] is NOT SET
 * @return       string Returns the string if the POST is there otherwise the default value given.
 * @since		1.4.6
 */
function softaculous_optPOST($name, $default = ''){

global $softaculous_error;

	//Check the POSTED NAME was posted
	if(isset($_POST[$name])){
	
		return trim(sanitize_text_field($_POST[$name]));
		
	}else{
		return $default;
	}

}

/**
 * OPTIONAL GET of the given GET Key i.e. dont throw a error if not there
 *
 * @param        string $name The key of the $_GET array i.e. the name of the input / textarea text 
 * @param        string $default The value to return if the $_GET[$name] is NOT SET
 * @return       string Returns the string if the GET is there otherwise the default value given.
 * @since     	 1.0
 */
function softaculous_optGET($name, $default = ''){

global $softaculous_error;

	//Check the GETED NAME was GETed
	if(isset($_GET[$name])){
	
		return trim(sanitize_text_field($_GET[$name]));
		
	}else{
		return $default;
	}

}

/**
 * A function to load a file from the net
 *
 * @param        string $url The URL to read
 * @param        string $writefilename Instead of returning the data save it to the path given
 * @return       string The data fetched
 * @since     	 1.0
 */
function softaculous_get_web_file($url, $writefilename = ''){

	$response = wp_remote_get($url);
	$file = wp_remote_retrieve_body($response);
	
	//Are we to store the file
	if(empty($writefilename)){
		return $file;
	
	//Store the file
	}else{
		$fp = @fopen($writefilename, "wb"); //This opens the file
		
		//If its opened then proceed
		if($fp){
			if(@fwrite($fp, $file) === FALSE){
				return false;
			//Wrote the file
			}else{
				@fclose($fp);
				return true;
			}
		}
	}	
	return false;
}

/**
 * A Function to unzip a ZIP file 
 *
 * @param        string $file The ZIP File
 * @param        string $destination The Final destination where the file will be unzipped
 * @param        int $overwrite Whether to Overwrite existing files
 * @param        array $include include files of the given pattern
 * @param        array $exclude exclude files of the given pattern
 * @return       boolean
 * @since     	 1.0
 */
function softaculous_unzip($file, $destination, $overwrite = 0, $include = array(), $exclude = array()){

    include_once('soft_pclzip.php');
	$archive = new softaculous_pclzip($file);

	$result = $archive->_extract(PCLZIP_OPT_PATH, $destination, 
									PCLZIP_CB_PRE_EXTRACT, 'softaculous_inc_exc', 
									PCLZIP_OPT_REPLACE_NEWER);
	
	if($result == 0){
		return false;
	}
	return true;
}

/**
 * Process includes and excludes of function unzip
 *
 * @param        $p_event
 * @param        $v
 * @return       Returns boolean
 * @since     	 1.0
 */
function softaculous_inc_exc($p_event, &$v){
	return 1;
}

/**
 * Checks if a file is symlink or hardlink
 *
 * @returns 	 bool false if file is a symlink or a hardlink else true
 * @since     	 1.0
 */
function softaculous_is_safe_file($path){

	// Is it a symlink ?
	if(is_link($path)) return false;
	
	// Is it a file and is a link ?
	$stat = @stat($path);
	if(!is_dir($path) && $stat['nlink'] > 1) return false;
	
	return true;
}

/**
 * Read file contents from the DESTINATION. Should be used when an installations file is to be fetched. 
 * For local package file, use the PHP file() function. The main usage of sfile is for import or upgrade !
 *
 * @package		files
 * @param		string $path The path of the file
 * @returns		bool
 * @since     	 1.0
 */
function softaculous_sfile($path){
			
	// Is it safe to read this file ? 
	if(!softaculous_is_safe_file($path)){
		return false;
	}
	
	return @file_get_contents($path);
}

/**
 * Fetch website's configuration details from the config file
 *
 * @since     	 1.0
 */
function softaculous_fetch_wp_config(){
	
	global $wpdb;
	
	$r = array();
	
	$r['softdbhost'] = $wpdb->dbhost;
	$r['softdbuser'] = $wpdb->dbuser;
	$r['softdbpass'] = $wpdb->dbpassword;
	$r['softdb'] = $wpdb->dbname;
	$r['dbprefix'] = $wpdb->prefix;
	
	$r['ver'] = softaculous_version_wp();
	
	//No trailing slash
	$updir = wp_upload_dir();
	$r['uploads_dir'] = realpath($updir['basedir']);
	$r['themes_root_dir'] = realpath(get_theme_root());
	$r['plugins_root_dir'] = realpath(plugin_dir_path( __DIR__ ));
	
	return $r;	
}

/**
 * Fetch website's currently installed version
 *
 * @since     	 1.0
 */
function softaculous_version_wp(){

	$file = softaculous_sfile(get_home_path().'wp-includes/version.php');
	
	if(!empty($file)){
		softaculous_preg_replace('/\$wp_version(\s*?)=(\s*?)("|\')(.*?)("|\');/is', $file, $ver, 4);
	}
	
	return $ver;
}

/**
 * This function will preg_match the pattern and return the respective values in $var
 * 
 * @param		$pattern This should be the pattern to be matched
 * @param		$file This should have the data to search from
 * @param		$var This will be the variable which will have the preg matched data
 * @param		$valuenum This should be the no of regular expression to be returned in $var
 * @param		$stripslashes 0 or 1 depending upon whether the stripslashes function is to be applied (1) or not (0)
 * @return		string Will pass value by reference in $var
 * @since     	1.0
 */
function softaculous_preg_replace($pattern, $file, &$var, $valuenum){	
	preg_match($pattern, $file, $matches);
	$var = trim($matches[$valuenum]);
}

/**
 * Unserialize a string and also fixes any broken serialized string before unserializing
 *
 * @param        string $str
 * @return       array Returns an array if successful otherwise false 
 * @since     	 1.0
 */
function softaculous_unserialize($str){

	$var = @unserialize($str);
	
	if(empty($var)){
		
		preg_match_all('!s:(\d+):"(.*?)";!s', $str, $matches);
		foreach($matches[2] as $mk => $mv){
			$tmp_str = 's:'.strlen($mv).':"'.$mv.'";';
			$str = str_replace($matches[0][$mk], $tmp_str, $str);
		}
		$var = @unserialize($str);
	}
	
	//If it is still empty false
	if($var === false){
		return false;
	}else{
		return $var;
	}

}

////////////////////////////////////////////
// Custom MySQL functions for Softaculous
///////////////////////////////////////////

/**
 * Connect to mysqli if exists else mysql
 * 
 * @param        string $host database host to be connected
 * @param        string $user db username to be used to connect
 * @param        string $pass db password to be used to connect
 * @param        string $newlink create a new link (mysql only)
 * @returns 	 string $conn returns resource link on success or FALSE on failure
 * @since     	1.0
 */
function softaculous_mysql_connect($host, $user, $pass, $newlink = false){
	
	if(extension_loaded('mysqli')){
		//echo 'mysqli';
		//To handle connection if user passes a custom port along with the host as 127.0.0.1:6446.
		//For testing, use port 127.0.0.1 instead of localhost as 127.0.0.1:6446 http://php.net/manual/en/mysqli.construct.php#112328
		$exh = explode(':', $host);
		if(!empty($exh[1])){
			$sconn = @mysqli_connect($exh[0], $user, $pass, '', $exh[1]);
		}else{
			$sconn = @mysqli_connect($host, $user, $pass);
		}
	}else{
		//echo 'mysql';
		$sconn = @mysql_connect($host, $user, $pass, $newlink);
	}
	
	return $sconn;
}

/**
 * Set the database character set
 * 
 * @param        string $conn database connection string
 * @param        string $charset character set to convert to
 * @returns	    bool true if character set is set
 * @since     	1.0
 */
function softaculous_mysql_set_charset($conn, $charset){
	
	if(extension_loaded('mysqli')){
		//echo 'mysqli';
		$return = @mysqli_set_charset($conn, $charset);
	}else{
		//echo 'mysql';
		$return = @mysql_set_charset($charset, $conn);
	}
	
	return $return;
}

/**
 * Selects database mysqli if exists else mysql
 * 
 * @param        string $db database to be selected
 * @param        string $conn Resource Link
 * @returns 	 bool TRUE on success or FALSE on failure
 * @since     	 1.0
 */
function softaculous_mysql_select_db($db, $conn){
	
	if(extension_loaded('mysqli')){
		$return = @mysqli_select_db($conn, $db);
	}else{
		$return = @mysql_select_db($db, $conn);
	}
	
	return $return;
}

/**
 * Executes the query mysqli if exists else mysql
 * 
 * @param        string $db database to be selected
 * @param        string $conn Resource Link
 * @returns 	 bool TRUE on success or FALSE on failure
 * @since     	 1.0
 */
function softaculous_mysql_query($query, $conn){
	
	if(extension_loaded('mysqli')){
		$return = @mysqli_query($conn, $query);
	}else{
		$return = @mysql_query($query, $conn);
	}
	
	return $return;
}

/**
 * Fetches the result from a result link mysqli if exists else mysql
 * 
 * @param        string $result result to fetch the data from
 * @returns 	 mixed Returns an array of strings that corresponds to the fetched row, or FALSE if there are no more rows
 * @since     	 1.0
 */
function softaculous_mysql_fetch_array($result){
	
	if(extension_loaded('mysqli')){
		$return = @mysqli_fetch_array($result);
	}else{
		$return = @mysql_fetch_array($result);
	}
	
	return $return;
}

/**
 * Fetches the result into associative array from a result link mysqli if exists else mysql
 * 
 * @param        string $result result to fetch the data from
 * @returns 	 mixed Returns an associative array of strings that corresponds to the fetched row, or FALSE if there are no more rows
 * @since     	 1.0
 */
function softaculous_mysql_fetch_assoc($result){
	
	if(extension_loaded('mysqli')){
		$return = @mysqli_fetch_assoc($result);
	}else{
		$return = @mysql_fetch_assoc($result);
	}
	
	return $return;
}

/**
 * Get a result row as an enumerated array mysqli if exists else mysql
 * 
 * @param        string $result result to fetch the data from
 * @returns 	 mixed returns an array of strings that corresponds to the fetched row or FALSE if there are no more rows
 * @since     	 1.0
 */
function softaculous_mysql_fetch_row($result){
	
	if(extension_loaded('mysqli')){
		$return = @mysqli_fetch_row($result);
	}else{
		$return = @mysql_fetch_row($result);
	}
	
	return $return;
}

/**
 * Get column information from a result and return as an object
 * 
 * @param        string $result result to fetch the data from
 * @param        string $field The numerical field offset
 * @returns 	 object Returns the definition of one column of a result set as an object. 
 * @since     	 1.0
 */
function softaculous_mysql_fetch_field($result, $field){
	
	if(extension_loaded('mysqli')){
		$return = @mysqli_fetch_field_direct($result, $field);
	}else{
		$return = @mysql_fetch_field($result, $field);
	}
	
	return $return;
}

/**
 * Gets the fields meta
 * 
 * @param        string $result result to fetch the data from
 * @returns 	 	object returns object of fields meta 
 * @since     	 1.0
 */
function softaculous_getFieldsMeta($result){
	// Build an associative array for a type look up
	
	if(!defined('SOFTACULOUS_MYSQLI_TYPE_VARCHAR')){
		define('SOFTACULOUS_MYSQLI_TYPE_VARCHAR', 15);
	}
	
	$typeAr = array();
	$typeAr[MYSQLI_TYPE_DECIMAL]     = 'real';
	$typeAr[MYSQLI_TYPE_NEWDECIMAL]  = 'real';
	$typeAr[MYSQLI_TYPE_BIT]         = 'int';
	$typeAr[MYSQLI_TYPE_TINY]        = 'int';
	$typeAr[MYSQLI_TYPE_SHORT]       = 'int';
	$typeAr[MYSQLI_TYPE_LONG]        = 'int';
	$typeAr[MYSQLI_TYPE_FLOAT]       = 'real';
	$typeAr[MYSQLI_TYPE_DOUBLE]      = 'real';
	$typeAr[MYSQLI_TYPE_NULL]        = 'null';
	$typeAr[MYSQLI_TYPE_TIMESTAMP]   = 'timestamp';
	$typeAr[MYSQLI_TYPE_LONGLONG]    = 'int';
	$typeAr[MYSQLI_TYPE_INT24]       = 'int';
	$typeAr[MYSQLI_TYPE_DATE]        = 'date';
	$typeAr[MYSQLI_TYPE_TIME]        = 'time';
	$typeAr[MYSQLI_TYPE_DATETIME]    = 'datetime';
	$typeAr[MYSQLI_TYPE_YEAR]        = 'year';
	$typeAr[MYSQLI_TYPE_NEWDATE]     = 'date';
	$typeAr[MYSQLI_TYPE_ENUM]        = 'unknown';
	$typeAr[MYSQLI_TYPE_SET]         = 'unknown';
	$typeAr[MYSQLI_TYPE_TINY_BLOB]   = 'blob';
	$typeAr[MYSQLI_TYPE_MEDIUM_BLOB] = 'blob';
	$typeAr[MYSQLI_TYPE_LONG_BLOB]   = 'blob';
	$typeAr[MYSQLI_TYPE_BLOB]        = 'blob';
	$typeAr[MYSQLI_TYPE_VAR_STRING]  = 'string';
	$typeAr[MYSQLI_TYPE_STRING]      = 'string';
	$typeAr[SOFTACULOUS_MYSQLI_TYPE_VARCHAR]     = 'string'; // for Drizzle
	// MySQL returns MYSQLI_TYPE_STRING for CHAR
	// and MYSQLI_TYPE_CHAR === MYSQLI_TYPE_TINY
	// so this would override TINYINT and mark all TINYINT as string
	// https://sourceforge.net/p/phpmyadmin/bugs/2205/
	//$typeAr[MYSQLI_TYPE_CHAR]        = 'string';
	$typeAr[MYSQLI_TYPE_GEOMETRY]    = 'geometry';
	$typeAr[MYSQLI_TYPE_BIT]         = 'bit';

	$fields = mysqli_fetch_fields($result);

	// this happens sometimes (seen under MySQL 4.0.25)
	if (!is_array($fields)) {
		return false;
	}

	foreach ($fields as $k => $field) {
		$fields[$k]->_type = $field->type;
		$fields[$k]->type = $typeAr[$field->type];
		$fields[$k]->_flags = $field->flags;
		$fields[$k]->flags = softaculous_mysql_field_flags($result, $k);

		// Enhance the field objects for mysql-extension compatibilty
		//$flags = explode(' ', $fields[$k]->flags);
		//array_unshift($flags, 'dummy');
		$fields[$k]->multiple_key
			= (int) (bool) ($fields[$k]->_flags & MYSQLI_MULTIPLE_KEY_FLAG);
		$fields[$k]->primary_key
			= (int) (bool) ($fields[$k]->_flags & MYSQLI_PRI_KEY_FLAG);
		$fields[$k]->unique_key
			= (int) (bool) ($fields[$k]->_flags & MYSQLI_UNIQUE_KEY_FLAG);
		$fields[$k]->not_null
			= (int) (bool) ($fields[$k]->_flags & MYSQLI_NOT_NULL_FLAG);
		$fields[$k]->unsigned
			= (int) (bool) ($fields[$k]->_flags & MYSQLI_UNSIGNED_FLAG);
		$fields[$k]->zerofill
			= (int) (bool) ($fields[$k]->_flags & MYSQLI_ZEROFILL_FLAG);
		$fields[$k]->numeric
			= (int) (bool) ($fields[$k]->_flags & MYSQLI_NUM_FLAG);
		$fields[$k]->blob
			= (int) (bool) ($fields[$k]->_flags & MYSQLI_BLOB_FLAG);
	}
	return $fields;
}

/**
 * Returns the field flags of the field in text format
 * 
 * @param        string $result result to fetch the data from
 * @param        string $field The numerical field offset
 * @returns 	 string Returns the field flags of the field in text format
 * @since     	1.0
 */
function softaculous_mysql_field_flags($result, $i){
	
	if(!extension_loaded('mysqli')){
		return mysql_field_flags($result, $i);
	}
	
	$f = mysqli_fetch_field_direct($result, $i);
	$type = $f->type;
	$charsetnr = $f->charsetnr;
	$f = $f->flags;
	$flags = '';
	if ($f & MYSQLI_UNIQUE_KEY_FLAG) {
		$flags .= 'unique ';
	}
	if ($f & MYSQLI_NUM_FLAG) {
		$flags .= 'num ';
	}
	if ($f & MYSQLI_PART_KEY_FLAG) {
		$flags .= 'part_key ';
	}
	if ($f & MYSQLI_SET_FLAG) {
		$flags .= 'set ';
	}
	if ($f & MYSQLI_TIMESTAMP_FLAG) {
		$flags .= 'timestamp ';
	}
	if ($f & MYSQLI_AUTO_INCREMENT_FLAG) {
		$flags .= 'auto_increment ';
	}
	if ($f & MYSQLI_ENUM_FLAG) {
		$flags .= 'enum ';
	}
	// See http://dev.mysql.com/doc/refman/6.0/en/c-api-datatypes.html:
	// to determine if a string is binary, we should not use MYSQLI_BINARY_FLAG
	// but instead the charsetnr member of the MYSQL_FIELD
	// structure. Watch out: some types like DATE returns 63 in charsetnr
	// so we have to check also the type.
	// Unfortunately there is no equivalent in the mysql extension.
	if (($type == MYSQLI_TYPE_TINY_BLOB || $type == MYSQLI_TYPE_BLOB
		|| $type == MYSQLI_TYPE_MEDIUM_BLOB || $type == MYSQLI_TYPE_LONG_BLOB
		|| $type == MYSQLI_TYPE_VAR_STRING || $type == MYSQLI_TYPE_STRING)
		&& 63 == $charsetnr
	) {
		$flags .= 'binary ';
	}
	if ($f & MYSQLI_ZEROFILL_FLAG) {
		$flags .= 'zerofill ';
	}
	if ($f & MYSQLI_UNSIGNED_FLAG) {
		$flags .= 'unsigned ';
	}
	if ($f & MYSQLI_BLOB_FLAG) {
		$flags .= 'blob ';
	}
	if ($f & MYSQLI_MULTIPLE_KEY_FLAG) {
		$flags .= 'multiple_key ';
	}
	if ($f & MYSQLI_UNIQUE_KEY_FLAG) {
		$flags .= 'unique_key ';
	}
	if ($f & MYSQLI_PRI_KEY_FLAG) {
		$flags .= 'primary_key ';
	}
	if ($f & MYSQLI_NOT_NULL_FLAG) {
		$flags .= 'not_null ';
	}
	return trim($flags);
}

/**
 * Returns the text of the error message from previous MySQL/MySQLi operation
 * 
 * @param        string $conn MySQL/MySQLi connection
 * @returns 	 string Returns the error text from the last MySQL function
 * @since     	1.0
 */
function softaculous_mysql_error($conn){
	
	if(extension_loaded('mysqli')){
		$return = @mysqli_error($conn);
		
		// In mysqli if connection  is not made then we will get connection error using the following function.
		if(empty($conn)){
			$return = @mysqli_connect_error();
		}
		
	}else{
		$return = @mysql_error($conn);
	}
	
	return $return;
}

/**
 * Returns the numerical value of the error message from previous MySQL operation
 * 
 * @param        string $conn MySQL/MySQLi connection
 * @returns 	 int Returns the error number from the last MySQL function
 * @since     	1.0
 */
function softaculous_mysql_errno($conn){
	
	if(extension_loaded('mysqli')){
		$return = @mysqli_errno($conn);
	}else{
		$return = @mysql_errno($conn);
	}
	
	return $return;
}

/**
 * Retrieves the number of rows from a result set
 * 
 * @param        string $result result resource that is being evaluated
 * @returns 	 string The number of rows in a result set on success or FALSE on failure
 * @since     	1.0
 */
function softaculous_mysql_num_rows($result){
	
	if(extension_loaded('mysqli')){
		$return = @mysqli_num_rows($result);
	}else{
		$return = @mysql_num_rows($result);
	}
	
	return $return;
}

/**
 * Get number of affected rows in previous MySQL/MySQLi operation
 * 
 * @param        string $conn MySQL/MySQLi connection
 * @returns 	 int Returns the number of affected rows on success, Zero indicates that no records were updated and -1 if the last query failed.
 * @since     	1.0
 */
function softaculous_mysql_affected_rows($conn){
	
	if(extension_loaded('mysqli')){
		$return = @mysqli_affected_rows($conn);
	}else{
		$return = @mysql_affected_rows($conn);
	}
	
	return $return;
}

/**
 * Get the ID generated in the last query
 * 
 * @param        string $conn MySQL/MySQLi connection
 * @returns 	 int The ID generated for an AUTO_INCREMENT column by the previous query on success, 0 if the previous query does not generate an AUTO_INCREMENT value, or FALSE if * 				 no MySQL connection was established. 
 * @since     	1.0
 */
function softaculous_mysql_insert_id($conn){
	
	if(extension_loaded('mysqli')){
		$return = @mysqli_insert_id($conn);
	}else{
		$return = @mysql_insert_id($conn);
	}
	
	return $return;
}

/**
 * Get number of fields in result
 * 
 * @param        string $result result resource that is being evaluated (Required by MySQL)
 * @returns 	 string Returns the number of fields in the result set on success or FALSE on failure
 * @since     	1.0
 */
function softaculous_mysql_num_fields($result){
	
	if(extension_loaded('mysqli')){
		$return = @mysqli_num_fields($result);
	}else{
		$return = @mysql_num_fields($result);
	}
	
	return $return;
}

/**
 * Will free all memory associated with the result identifier 
 * 
 * @param        string $result result resource that is being evaluated
 * @returns 	 bool Returns TRUE on success or FALSE on failure
 * @since     	1.0
 */
function softaculous_mysql_free_result($result){
	
	if(extension_loaded('mysqli')){
		$return = @mysqli_free_result($result);
	}else{
		$return = @mysql_free_result($result);
	}
	
	return $return;
}

/**
 * Close MySQL/MySQLi connection 
 * 
 * @param        string $conn MySQL/MySQLi connection
 * @returns 	 bool Returns TRUE on success or FALSE on failure
 * @since     	1.0
 */
function softaculous_mysql_close($conn){
	
	if(extension_loaded('mysqli')){
		$return = @mysqli_close($conn);
	}else{
		$return = @mysql_close($conn);
	}
	
	return $return;
}

/**
 * Get MySQL/MySQLi client info
 * 
 * @returns 	 string Returns a string that represents the MySQL client library version
 * @since     	1.0
 */
function softaculous_mysql_get_client_info(){
	
	if(extension_loaded('mysqli')){
		$return = @mysqli_get_client_info();
	}else{
		$return = @mysql_get_client_info();
	}
	
	return $return;
}

/**
 * Get MySQL/MySQLi server info
 * 
 * @param        string $conn MySQL/MySQLi connection
 * @returns 	 string Returns a string that represents the MySQL server version
 * @since     	1.0
 */
function softaculous_mysql_get_server_info($conn){
	
	if(extension_loaded('mysqli')){
		$return = @mysqli_get_server_info($conn);
	}else{
		$return = @mysql_get_server_info($conn);
	}
	
	return $return;
}

/**
 * Execute Database queries
 *
 * @param        string $queries The Database Queries seperated by a SEMI COLON (;) 
 * @param        string $host The Database HOST
 * @param        string $db The Database Name
 * @param        string $user The Database User Name
 * @param        string $pass The Database User Password
 * @param        string $conn The Database Connection
 * @returns		bool
 * @since     	1.0
 */
function softaculous_sdb_query($queries, $host, $user, $pass, $db, $conn = '', $no_strict = 0){	
	return softaculous_mysqlfile($queries, $host, $user, $pass, $db, $conn);
	
}

/**
 * Dump SQL Data ($raw) into the given database.
 *
 * @param        string $raw The RAW SQL Data
 * @param        string $host The MySQL Host
 * @param        string $user The MySQL User
 * @param        string $pass The MySQL User password
 * @param        string $db The Database Name
 * @param        string $__conn Connection link to the database
 * @return       bool If there is an error $softaculous_error is filled with the error
 * @since     	 1.0
 */
function softaculous_mysqlfile($raw, $host, $user, $pass, $db, $__conn = ""){

global $softaculous_error, $softaculous_lang;
	
	$queries = softaculous_sqlsplit($raw);
	
	//Make the Connection
	if(empty($__conn)){
		$__conn = @softaculous_mysql_connect($host, $user, $pass, true);
	}
	
	//CHECK Errors and SELECT DATABASE
	if(!empty($__conn)){	
		if(!(@softaculous_mysql_select_db($db, $__conn))){
			$softaculous_error[] = $softaculous_lang['err_selectmy'].(!empty($_GET['debug']) ? softaculous_mysql_error($__conn) : '');
			return false;
		}
	}else{
		$softaculous_error[] = $softaculous_lang['err_myconn'].(!empty($_GET['debug']) ? softaculous_mysql_error($__conn) : '');
		return false;
	}
	
	$num = count($queries);
	
	//Start the Queries
	foreach($queries as $k => $q){	
		
		//PARSE the String and make the QUERY
		$result = softaculous_mysql_query($q, $__conn);
		
		//Looks like there was an error
		if(!$result){			
			$softaculous_error[] = $softaculous_lang['err_makequery'].' : '.$k.'<br />'.$softaculous_lang['err_mynum'].' : '.softaculous_mysql_errno($__conn).'<br />'.$softaculous_lang['err_myerr'].' : '.softaculous_mysql_error($__conn).(softaculous_sdebug('errquery') ? ' Query : '.$q : '');			
			return false;				
		}
		
		// Is there only one query ?
		if($num == 1){
			
			// Is it a SELECT query ?
			if(preg_match('/^(SELECT|SHOW|DESCRIBE)/is', $q)){ // CHECKSUM|OPTIMIZE|ANALYZE|CHECK|EXPLAIN
				
				// Accumulate the data !
				for($i = 0; $i < softaculous_mysql_num_rows($result); $i++){
					$return[] = softaculous_mysql_fetch_assoc($result);
				}
				
			}
	
			// Is it a INSERT query ? Then we will have to return insert id
			if(preg_match('/^INSERT/is', $q)){
				$return[] = softaculous_mysql_insert_id($__conn);
			}	
		}
	
	}
	
	// Are we to return the data ?
	if(!empty($return)){
		return $return;
	}
	
	return true;
	
}

/**
 * Is debugging ON for the given key ?
 *
 * @param        string $key The Key to search for debugging
 * @return       int True on success
 * @since     	1.0
 */
function softaculous_sdebug($key){
	if(@in_array($key, @$_GET['debug']) || @$_GET['debug'] == $key){
		return true;
	}
}

/**
 * phpMyAdmin SPLIT SQL function which splits the SQL data into seperate chunks that can be passed as QUERIES.
 *
 * @param        string $data The SQL RAW data
 * @returns 	 array The chunks of SQL Queries
 * @since     	 1.0
 */
function softaculous_sqlsplit($data){

	$ret = array();
	$buffer = '';
	// Defaults for parser
	$sql = '';
	$start_pos = 0;
	$i = 0;
	$len= 0;
	$big_value = 200000000;
	$sql_delimiter = ';';
	
	$finished = false;
	
	while (!($finished && $i >= $len)) {
	
		if ($data === FALSE) {
			// subtract data we didn't handle yet and stop processing
			//$offset -= strlen($buffer);
			break;
		} elseif ($data === TRUE) {
			// Handle rest of buffer
		} else {
			// Append new data to buffer
			$buffer .= $data;
			// free memory
			$data = false;
			// Do not parse string when we're not at the end and don't have ; inside
			if ((strpos($buffer, $sql_delimiter, $i) === FALSE) && !$finished)  {
				continue;
			}
		}
		// Current length of our buffer
		$len = strlen($buffer);
		
		// Grab some SQL queries out of it
		while ($i < $len) {
			$found_delimiter = false;
			// Find first interesting character
			$old_i = $i;
			// this is about 7 times faster that looking for each sequence i
			// one by one with strpos()
			if (preg_match('/(\'|"|#|-- |\/\*|`|(?i)DELIMITER)/', $buffer, $matches, PREG_OFFSET_CAPTURE, $i)) {
				// in $matches, index 0 contains the match for the complete 
				// expression but we don't use it
				$first_position = $matches[1][1];
			} else {
				$first_position = $big_value;
			}
			/**
			 * @todo we should not look for a delimiter that might be
			 *       inside quotes (or even double-quotes)
			 */
			// the cost of doing this one with preg_match() would be too high
			$first_sql_delimiter = strpos($buffer, $sql_delimiter, $i);
			if ($first_sql_delimiter === FALSE) {
				$first_sql_delimiter = $big_value;
			} else {
				$found_delimiter = true;
			}
	
			// set $i to the position of the first quote, comment.start or delimiter found
			$i = min($first_position, $first_sql_delimiter);
	
			if ($i == $big_value) {
				// none of the above was found in the string
	
				$i = $old_i;
				if (!$finished) {
					break;
				}
				// at the end there might be some whitespace...
				if (trim($buffer) == '') {
					$buffer = '';
					$len = 0;
					break;
				}
				// We hit end of query, go there!
				$i = strlen($buffer) - 1;
			}
	
			// Grab current character
			$ch = $buffer[$i];
	
			// Quotes
			if (strpos('\'"`', $ch) !== FALSE) {
				$quote = $ch;
				$endq = FALSE;
				while (!$endq) {
					// Find next quote
					$pos = strpos($buffer, $quote, $i + 1);
					// No quote? Too short string
					if ($pos === FALSE) {
						// We hit end of string => unclosed quote, but we handle it as end of query
						if ($finished) {
							$endq = TRUE;
							$i = $len - 1;
						}
						$found_delimiter = false;
						break;
					}
					// Was not the quote escaped?
					$j = $pos - 1;
					while ($buffer[$j] == '\\') $j--;
					// Even count means it was not escaped
					$endq = (((($pos - 1) - $j) % 2) == 0);
					// Skip the string
					$i = $pos;
	
					if ($first_sql_delimiter < $pos) {
						$found_delimiter = false;
					}
				}
				if (!$endq) {
					break;
				}
				$i++;
				// Aren't we at the end?
				if ($finished && $i == $len) {
					$i--;
				} else {
					continue;
				}
			}
	
			// Not enough data to decide
			if ((($i == ($len - 1) && ($ch == '-' || $ch == '/'))
			  || ($i == ($len - 2) && (($ch == '-' && $buffer[$i + 1] == '-')
				|| ($ch == '/' && $buffer[$i + 1] == '*')))) && !$finished) {
				break;
			}
	
			// Comments
			if ($ch == '#'
			 || ($i < ($len - 1) && $ch == '-' && $buffer[$i + 1] == '-'
			  && (($i < ($len - 2) && $buffer[$i + 2] <= ' ')
			   || ($i == ($len - 1)  && $finished)))
			 || ($i < ($len - 1) && $ch == '/' && $buffer[$i + 1] == '*')
					) {
				// Copy current string to SQL
				if ($start_pos != $i) {
					$sql .= substr($buffer, $start_pos, $i - $start_pos);
				}
				// Skip the rest
				$j = $i;
				$i = strpos($buffer, $ch == '/' ? '*/' : "\n", $i);
				// didn't we hit end of string?
				if ($i === FALSE) {
					if ($finished) {
						$i = $len - 1;
					} else {
						break;
					}
				}
				// Skip *
				if ($ch == '/') {
					// Check for MySQL conditional comments and include them as-is
					if ($buffer[$j + 2] == '!') {
						$comment = substr($buffer, $j + 3, $i - $j - 3);
						if (preg_match('/^[0-9]{5}/', $comment, $version)) {
							if ($version[0] <= 50000000) {
								$sql .= substr($comment, 5);
							}
						} else {
							$sql .= $comment;
						}
					}
					$i++;
				}
				// Skip last char
				$i++;
				// Next query part will start here
				$start_pos = $i;
				// Aren't we at the end?
				if ($i == $len) {
					$i--;
				} else {
					continue;
				}
			}
			// Change delimiter, if redefined, and skip it (don't send to server!)
			if (strtoupper(substr($buffer, $i, 9)) == "DELIMITER"
			 && ($buffer[$i + 9] <= ' ')
			 && ($i < $len - 11)
			 && strpos($buffer, "\n", $i + 11) !== FALSE) {
			   $new_line_pos = strpos($buffer, "\n", $i + 10);
			   $sql_delimiter = substr($buffer, $i + 10, $new_line_pos - $i - 10);
			   $i = $new_line_pos + 1;
			   // Next query part will start here
			   $start_pos = $i;
			   continue;
			}
	
			// End of SQL
			if ($found_delimiter || ($finished && ($i == $len - 1))) {
				$tmp_sql = $sql;
				if ($start_pos < $len) {
					$length_to_grab = $i - $start_pos;
	
					if (! $found_delimiter) {
						$length_to_grab++;
					}
					$tmp_sql .= substr($buffer, $start_pos, $length_to_grab);
					unset($length_to_grab);
				}
				// Do not try to execute empty SQL
				if (! preg_match('/^([\s]*;)*$/', trim($tmp_sql))) {
					$sql = $tmp_sql;
					$ret[] = $sql;
					
					$buffer = substr($buffer, $i + strlen($sql_delimiter));
					// Reset parser:
					$len = strlen($buffer);
					$sql = '';
					$i = 0;
					$start_pos = 0;
					// Any chance we will get a complete query?
					//if ((strpos($buffer, ';') === FALSE) && !$finished) {
					if ((strpos($buffer, $sql_delimiter) === FALSE) && !$finished) {
						break;
					}
				} else {
					$i++;
					$start_pos = $i;
				}
			}
		} // End of parser loop
	} // End of import loop

	return $ret;

}


/**
 * Read the SQL file in parts and Dump the data into the given database
 *
 * @param        string $file The Path to SQL file
 * @param        string $host The MySQL Host
 * @param        string $user The MySQL User
 * @param        string $pass The MySQL User password
 * @param        string $db The Database Name
 * @param        string $__conn Connection link to the database
 * @return       bool If there is an error $softaculous_error is filled with the error
 * @since		1.0
 */
function softaculous_softaculous_mysql_parts($file, $host, $user, $pass, $db, $__conn = "", $delimiter = ';', $replace_data = array()){

global $softaculous_error, $softaculous_lang;
	
	if(is_file($file) === true){
	
		//Make the Connection
		if(empty($__conn)){
			$__conn = @softaculous_mysql_connect($host, $user, $pass, true);
		}
		
		//CHECK Errors and SELECT DATABASE
		if(!empty($__conn)){
			if(!(@softaculous_mysql_select_db($db, $__conn))){
				$softaculous_error[] = $softaculous_lang['err_selectmy'].(!empty($_GET['debug']) ? softaculous_mysql_error($__conn) : '');
				return false;
			}
		}else{
			$softaculous_error[] = $softaculous_lang['err_myconn'].(!empty($_GET['debug']) ? softaculous_mysql_error($__conn) : '');
			return false;
		}
		
		$file = fopen($file, 'r');

		if(is_resource($file) === true){
			$query = array();
			$num = 0;
			while(feof($file) === false){

				if(is_string($query) === true){
					$query = array();
				}
				
				$query[] = fgets($file);

				if(preg_match('~' . preg_quote($delimiter, '~') . '\s*$~iS', end($query)) === 1){
					$query = trim(implode('', $query));
					
					if(!empty($replace_data)){
						$query = strtr($query, $replace_data);
					}
					
					$result = softaculous_mysql_query($query, $__conn);
					if(!$result){
						$softaculous_error[] = $softaculous_lang['err_makequery'].' <br />'.$softaculous_lang['err_mynum'].' : '.softaculous_mysql_errno($__conn).'<br />'.$softaculous_lang['err_myerr'].' : '.softaculous_mysql_error($__conn).(softaculous_sdebug('errquery') ? ' Query : '.$query : '');			
						return false;	
					}else{
						$num++;
					}
				}
			}
			
			fclose($file);
			
			// Is there only one query ?
			if($num == 1){
				
				// Is it a SELECT query ?
				if(preg_match('/^(SELECT|SHOW|DESCRIBE)/is', $query)){ // CHECKSUM|OPTIMIZE|ANALYZE|CHECK|EXPLAIN
					
					// Accumulate the data !
					for($i = 0; $i < softaculous_mysql_num_rows($result); $i++){
						$return[] = softaculous_mysql_fetch_assoc($result);
					}
					
				}
		
				// Is it a INSERT query ? Then we will have to return insert id
				if(preg_match('/^INSERT/is', $query)){
					$return[] = softaculous_mysql_insert_id($__conn);
				}	
			}
	
			// Are we to return the data ?
			if(!empty($return)){
				return $return;
			}
		}else{
			$softaculous_error['err_no_open_db_file'] = $softaculous_lang['err_no_open_db_file'];
			return false;
		}
	}else{
		$softaculous_error['err_no_db_file'] = $softaculous_lang['err_no_db_file'];
		return false;
	}
	
	return true;
}

/**
 * Check whether a file or directory exists at the INSTALLATION level ONLY, not in the PACKAGE
 *
 * @param        string $path The path of the file or directory
 * @returns 	 	bool
 * @since		1.0
 */ 
function softaculous_sfile_exists($path){	
	return file_exists($path);
}

/**
 * Softaculous Version Compare, fixes a bug with '+' as the last character
 *
 * @param        string $ver1 The first version
 * @param        string $ver2 The second version
 * @param        string $oper By default NULL or operators of the original version_compare() function
 * @param        string $vr By default Empty Array or An array which will contain alphabetic version and to be replace version (For handling alphabatic versions)
 * @return       int values as per the original version_compare() function
 * @since     	 1.0
 */
function softaculous_sversion_compare($ver1, $ver2, $oper = NULL, $vr = array()){
	
	if(!empty($vr)){
		$ver2 = str_replace($vr['search'], $vr['replace'], $ver2);
	}
	
	$last1 = substr($ver1, -1);
	$last2 = substr($ver2, -1);
	
	if(!preg_match('/[0-9a-zA-Z]/is', $last1)){
		$ver1 = substr($ver1, 0, -1).'.0'.$last1.'0';
	}
	
	if(!preg_match('/[0-9a-zA-Z]/is', $last2)){
		$ver2 = substr($ver2, 0, -1).'.0'.$last2.'0';
	}
	
	if(is_null($oper)){
		return version_compare($ver1, $ver2);
	}else{
		return version_compare($ver1, $ver2, $oper);
	}
}

/**
 * Deletes a file.
 *
 * @param		string $path
 * @returns		bool
 * @since		1.0
 */
function softaculous_sunlink($path){	
	return @unlink($path);
}

/**
 * Check whether the path is a directory
 *
 * @param		string $path The path of the file or directory
 * @returns		bool
 * @since		1.0
 */
function softaculous_is_dir($path){
    return is_dir($path);
}

/**
 * Creates a directory. This is meant to be used by install.php, upgrade.php, etc. 
 * NOTE : Folder permissions cannot exceed 0755 in MKDIR. You must use schmod if necessary to give 0777
 *
 * @param        string $path
 * @param        octal $mode
 * @param        bool $rec Recurse into the directory and apply the changes 
 * @returns 	 bool
 * @since     	 1.0
 */
function softaculous_mkdir($path, $mode = 0755, $rec = 0){
	if(!is_dir($path)){
		$ret = mkdir($path, $mode, $rec);
		return $ret;
	}
    return true;
}

/**
 * Changes the permissions (chmod) of the given path. This is meant to be used by install.php, upgrade.php, etc.
 *
 * @param		string $path The path of the file or directory
 * @param		octal $oct The new permission e.g. 0644
 * @returns		bool
 * @since		1.0
 */
function softaculous_chmod($path, $mode){
    return chmod($path, $mode);
}

/**
 * Writes data to a file
 *
 * @param		string $file The path of the file or directory
 * @param		string $data Data to write to the file
 * @returns		bool
 * @since		1.0
 */
function softaculous_put($file, $data){
	
	$fp = @fopen($file, "wb");
	if($fp){
		if(@fwrite($fp, $data) === FALSE){
			return false;
		}else{
			@fclose($fp);
			return true;
		}
	}
	return false;
}

/**
 * Deletes a file.
 *
 * @param		string $filename
 * @returns		bool
 * @since		1.0
 */
function softaculous_delete($filename){
	return softaculous_sunlink($filename);
}

/**
 * Removes a directory
 *
 * @param		string $directory path
 * @returns		bool
 * @since		1.4.3
 */
function softaculous_srm($path){
	
	if(is_dir($path)){
		return softaculous_rmdir_recursive($path);
	}	
	// So its a file !
	return softaculous_sunlink($path);
}

/**
 * Used in file_functions to perform backup
 *
 * @param		string $filename
 * @returns		bool
 * @since		1.0
 */
function softaculous_chdir($dir){
	return softaculous_is_dir($dir);
}

/**
 * Check whether a file or directory exists
 *
 * @param		string $file The path of the file or directory
 * @returns		bool
 * @since		1.0
 */ 
function softaculous_file_exists($file){
	return file_exists($file);
}

/**
 * Renames a file/folder.
 *
 * @param		string $from oldname
 * @param		string $to newname
 * @returns		bool
 * @since		1.4.2
 */ 
function softaculous_rename($from, $to){
	return rename($from, $to);
}

/**
 * Fetch the connection key for the Softaculous plugin to add the website in Softaculous panel
 *
 * @param		bool $force Whether to generate a new key forcefully or fetch the prev generated key
 * @returns		string $conn_key
 * @since		1.0
 */
function softaculous_get_connection_key($force = ''){

	$conn_key = softaculous_get_option('softaculous_auth_key');

	if(empty($conn_key) || (!empty($conn_key) && strlen($conn_key) != 128) || $force){
		$conn_key = softaculous_srandstr(128);
		update_option('softaculous_auth_key', $conn_key, true);
	}

	return $conn_key;
}

/**
 * Generate the connection key on plugin activation
 *
 * @returns		string $conn_key
 * @since		1.0
 */
function softaculous_activation_hook(){
	
	softaculous_get_connection_key(1);
	
	// Get Softaculous settings and convert to Softaculous settings
	$deprecated_settings = array('wpcentral_auth_key' => 'softaculous_auth_key', 'wpcentral_allowed_ips' => 'softaculous_allowed_ips', 'wpcentral_connected' => 'softaculous_connected', 'wpcentral_promo_time' => 'softaculous_promo_time', 'wpcentral_signonkey' => 'softaculous_signonkey', 'wpcentral_signonkey_time' => 'softaculous_signonkey_time', 'wpc_dismiss_notice_date' => 'softaculous_dismiss_notice_date');
	
	foreach($deprecated_settings as $old_name => $new_name){
		$cur = get_option($old_name, NULL);
		if(!is_null($cur)){
			update_option($new_name, $cur);
			delete_option($old_name);
		}
	}
	
	delete_option('wpcentral_version');
	
	return true;
}

/**
 * Generate the connection key when the plugin loads after its activation.
 *
 * @returns		string $conn_key
 * @since		1.2
 */
function softaculous_load_plugin(){
	
	// Enqueues scripts and styles
	add_action('admin_enqueue_scripts', 'softaculous_enqueue_scripts');
	
	// Show key details in Plugins data
	add_filter('plugin_row_meta', 'softaculous_add_connection_link', 10, 2);
	
	softaculous_update_check();
	
	// Set the key if not already set
	softaculous_get_connection_key();
	
	// All non-privilige actions including those called by cloud.softaculous.com
	// We must check with softaculous_authorize in these actions
	add_action('wp_ajax_nopriv_my_wpc_actions', 'softaculous_actions_init');
	
	// If we are to login the user
	// We must authorize with the softaculous_signonkey_authorization() function in these actions
	add_action('wp_ajax_nopriv_wpcentral_login_and_act', 'softaculous_login_and_act');
	
	// Are you the Admin ?
	if(current_user_can('administrator')){

		// If we are to login the user
		// We must authorize with the softaculous_signonkey_authorization() function in these actions
		add_action('wp_ajax_wpcentral_login_and_act', 'softaculous_login_and_act');
	
		if(softaculous_is_display_notice()){
			add_action('admin_notices', 'softaculous_admin_notice');
		}
		
		add_action('wp_ajax_softaculous_dismissnotice', 'softaculous_dismiss_notice');
		
		// This adds the left menu in WordPress Admin page
		add_action('admin_menu', 'softaculous_admin_menu', 5);
		
		add_action('wp_ajax_my_softaculous_fetch_authkey', 'softaculous_fetch_authkey');
		
		// Backward compatible. To be deprecated
		add_action('wp_ajax_my_wpc_fetch_authkey', 'softaculous_fetch_authkey');
	
		// Show Softaculous ratings notice
		softaculous_maybe_promo([
			'after' => 1,// In days
			'interval' => 120,// In days
			'rating' => 'https://wordpress.org/plugins/softaculous/#reviews',
			'twitter' => 'https://twitter.com/softaculous?status='.rawurlencode('I love #Softaculous for managing my #WordPress site - '.home_url()),
			'facebook' => 'https://www.facebook.com/softaculous',
			'website' => 'https://softaculous.com',
			'image' => SOFTACULOUS_PLUGIN_URL . 'assets/images/logo.gif',
			'support' => 'https://softaculous.deskuss.com'
		]);
		
	}
	
	return true;
}

// Shows the admin menu of Softaculous
function softaculous_admin_menu() {
	
	$capability = 'activate_plugins';// TODO : Capability for accessing this page

	// Add the menu page
	add_menu_page(__('Softaculous Manager'), __('Softaculous', 'softaculous'), $capability, 'softaculous', 'softaculous_page_handler', SOFTACULOUS_PLUGIN_URL .'assets/images/soft_logo_22.svg');
}

function softaculous_enqueue_scripts(){
	
	wp_enqueue_style('soft-style', SOFTACULOUS_PLUGIN_URL . '/assets/css/admin.css', [], SOFTACULOUS_VERSION);
	
	wp_enqueue_script('soft-script', SOFTACULOUS_PLUGIN_URL . '/assets/js/admin.js', [], SOFTACULOUS_VERSION, true);
	
	wp_localize_script('soft-script', 'soft_obj', array(
		'admin_url' => admin_url(),
		'nonce' => wp_create_nonce('softaculous_js_nonce'),
		'ajax_url' => admin_url('admin-ajax.php')
	));
}

// The Softaculous Settings Page
function softaculous_page_handler(){

	if(!current_user_can('manage_options')){
		wp_die('Sorry, but you do not have permissions to change settings.');
	}
	
	include_once(dirname(__FILE__).'/settings.php');
}

/**
 * Fetch the connection key and the site details when the plugin is installed and activated from Softaculous panel.
 *
 * @since		1.2
 */
function softaculous_fetch_authkey(){
    global $softaculous_lang, $softaculous_error, $softaculous_wp_config, $wpdb;
    
    //Fetch WP Configuration details
	$softaculous_wp_config = softaculous_fetch_wp_config();
    
    $softaculous_authkey = softaculous_get_connection_key();
    
    include_once('verify.php');
	softaculous_verify($softaculous_authkey);
}

/**
 * Deletes the connection key on plugin deactivation
 *
 * @returns		bool
 * @since		1.0
 */
function softaculous_deactivation_hook(){
	delete_option('softaculous_auth_key');
	delete_option('softaculous_connected');
	delete_option('softaculous_dismiss_notice_date');
	delete_option('softaculous_promo_time');
	return true;
}

/**
 * Add plugin's metadata in the plugin list table
 *
 * @param		string $links
 * @param		string $slug plugin's slug value
 * @returns		array $links
 * @since		1.0
 */
function softaculous_add_connection_link($links, $slug) {

	if(is_multisite() && is_network_admin()){
		return $links;
	}

	if ($slug !== SOFTACULOUS_BASE) {
		return $links;
	}

	if(!current_user_can('activate_plugins')){
		return $links;
	}
	
	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-ui-core');
	wp_enqueue_script('jquery-ui-dialog');
	wp_enqueue_style('wp-jquery-ui');
	wp_enqueue_style('wp-jquery-ui-dialog');
	
	$mdialog = '
	<div id="soft_connection_key_dialog" style="display: none;">
		
		<p style="display:inline-block; padding:0 20px; font-size:13px;"><a class="button button-primary action" href="'.softaculous_get_addsite_link().'" target="_blank">1-Click Connect Website</a></p>
		
		<h2 style="margin-left:5%;">OR</h2>
		<p>Follow the steps here to connect your website to Softaculous dashboard:</p>
		<ol>
			<li>Copy the connection key below</li>
			<li>Log into your <a href="https://cloud.softaculous.com/" target="_blank">Softaculous</a> account</li>
			<li>Click on <a href="'.softaculous_get_addsite_link().'" target="_blank">Add Website</a> to add your website to the Softaculous panel</li>
			<li>Enter this website\'s URL and paste the Connection key given below</li>
			<li>You can also follow our guide for the same <a href="https://www.softaculous.com/docs/wordpress-plugin/adding-website-in-panel/" target="_blank">here</a></li>
		</ol>

		<div style="text-align:center; font-weight:bold;"><p style="margin-bottom: 4px;margin-top: 10px;">Softaculous Connection Key</p></div>
		<div class="display_connection_key" style="padding: 10px;background-color: #fafafa;border: 1px solid black;border-radius: 10px;font-weight: bold;font-size: 14px;text-align: center;">'.softaculous_get_connection_key().'</div>
		
		<p style="font-weight:bold;">Note: Contact Softaculous Team at <a href="mailto:support@softaculous.com">support@softaculous.com</a> for any issues</p>
	</div>';
	
	$new_links = array(
		'doc' => '<a href="#" id="soft_connection_key">View Connection Key</a>'.$mdialog,
		'settings' => '<a href="admin.php?page=softaculous">Settings</a>'
	);

	$links = array_merge($links, $new_links);

	return $links;
}

/**
 * Deletes the connection key on plugin deactivation
 *
 * @returns		bool
 * @since		1.0
 */
function softaculous_deactivate(){
	delete_option('softaculous_auth_key');
	delete_option('softaculous_connected');
	delete_option('softaculous_dismiss_notice_date');
}

// Get the client IP
function softaculous_getip(){
	if(isset($_SERVER["REMOTE_ADDR"])){
		return sanitize_text_field($_SERVER["REMOTE_ADDR"]);
	}elseif(isset($_SERVER["HTTP_X_FORWARDED_FOR"])){
		return sanitize_text_field($_SERVER["HTTP_X_FORWARDED_FOR"]);
	}elseif(isset($_SERVER["HTTP_CLIENT_IP"])){
		return sanitize_text_field($_SERVER["HTTP_CLIENT_IP"]);
	}
}

/**
 * Check for the authorization of the request using the auth key
 *
 * @returns		bool
 * @since		1.0
 */
function softaculous_authorize(){
    global $softaculous_lang, $softaculous_error;
	
	$return = array(); 
    
    $auth_key = softaculous_optREQ('auth_key');
	if(empty($auth_key)){
		$return['error'] = 'Unauthorized Access!!';
		echo wp_json_encode($return);
		die();
	}
	
	$verify_authkey = softaculous_get_option('softaculous_auth_key');
	if($auth_key != $verify_authkey){
		$return['error'] = $softaculous_lang['invalid_auth_key'];
		echo wp_json_encode($return);
		die();
	}
	
	$allowed_ips = softaculous_get_allowed_ips();
	$remote_ip = softaculous_getip();
	
	// We allow requests from allowed panels only
	if(!in_array($remote_ip, $allowed_ips)){
		$return['error'] = 'Unauthorized Access from an unknown IP '.esc_html($remote_ip).'. Allowed IPs - '.esc_html(implode(',', $allowed_ips)).'!!';
		echo wp_json_encode($return);
		die();
	}
}

// Gets the list of allowed IPs
function softaculous_get_allowed_ips(){
	
	$ips = get_option('softaculous_allowed_ips');
	
	if(empty($ips)){
		update_option('softaculous_allowed_ips', array(SOFTACULOUS_PANEL_IP));
		$ips = get_option('softaculous_allowed_ips');
	}
	
	foreach($ips as $k => $ip){
		if(empty($ip)){
			unset($ips[$k]);
		}
	}
	
	return $ips;
	
}

/**
 * Update the database if the website is added in Softaculous panel
 *
 * @returns		bool
 * @since		1.1
 */
function softaculous_connectok(){
    $connected = softaculous_get_option('softaculous_connected');

	if(empty($connected)){
		update_option('softaculous_connected', '1', true);
	}

	return true;
}

/**
 * Deactivate Softaculous plugin and delete the database entries if the website is removed in Softaculous panel
 *
 * @returns		bool
 * @since		1.1
 */
function softaculous_self_deactivate(){
    
    softaculous_deactivate_plugin(array(SOFTACULOUS_BASE));
    softaculous_deactivate();
    
    return true;
}

/**
 * Test if we are able to create a directory
 *
 * @returns		bool
 * @since		1.4
 */
function softaculous_test_createdir($path){
	
	softaculous_mkdir($path);
	
	if(softaculous_is_dir($path)){
		return true;
	}
	
	return true;
}

/**
 * Softaculous action handler when we need to LOGIN and then redirect
 *
 * @since		1.0
 */
function softaculous_login_and_act(){
	global $softaculous_lang, $softaculous_error;
	
	// Authorize with a TEMP KEY or DIE !
	softaculous_signonkey_authorization();
	
	// At this point we are authorized and we can login
	softaculous_signon();
	
	//Execute the Request
	$softaculous_act = softaculous_optREQ('wpc_act');
	
	switch($softaculous_act){
		
		// Edit or Preview Post
		case 'edit_post':
		case 'preview_post':
			softaculous_post_redirect($softaculous_act);
			break;
		
		// Default case to login
		default:
		case '':
			$redirect_to = user_admin_url();
			wp_safe_redirect($redirect_to);
			exit();
			break;
	}
	
}
	
/**
 * Softaculous action handler
 *
 * @since		1.0
 */
function softaculous_actions_init(){
	global $softaculous_lang, $softaculous_error, $softaculous_wp_config;
	
	//Authorize
	softaculous_authorize();
	
	//Fetch WP Configuration details
	$softaculous_wp_config = softaculous_fetch_wp_config();
	
	//Execute the Request
	$softaculous_act = softaculous_optREQ('wpc_act');
	
	switch($softaculous_act){
		
		case 'verifyconnect':
			include_once('verify.php');
			softaculous_verify();
			break;
		
		case 'wpcdeactivate':
			softaculous_self_deactivate();
			break;
			
		case 'getsitedata':
			include_once('get_site_data.php');
			softaculous_get_site_data();
			break;
			
		case 'siteactions':
			include_once('actions.php');
			softaculous_site_actions();
			break;
			
		case 'fileactions':
			include_once('file_actions.php');
			softaculous_file_actions();
			break;
			
		case 'connectok':
			softaculous_connectok();
			break;
			
		case 'can_createdir':
			softaculous_can_createdir();
			break;
			
		case 'wpcentral_version':
			softaculous_fetch_version();
			break;
		
		case 'getsignonkey':
			softaculous_getsignonkey();
			break;
	
		//The DEFAULT Page
		default:
			// Nothing to do
			break;
	}
}

/**
 * Directs to the edit/preview post page of the specified post
 *
 * @since		1.4.6
 */
function softaculous_post_redirect($post_act){
    global $softaculous_lang, $softaculous_error;
	
	$post_id = softaculous_optREQ('post_id');
		
	if($post_act == 'edit_post'){
		$redirect_to = get_edit_post_link($post_id, '');
		
	}elseif($post_act == 'preview_post'){
		$redirect_to = get_preview_post_link($post_id);
	}
	
	wp_safe_redirect($redirect_to);

	exit();
}

/**
 * Check if we have the permission to create a directory on the user server
 *
 * @returns		bool
 * @since		1.4.1
 */
function softaculous_can_createdir($path = ''){
	global $softaculous_wp_config;
	
	if(empty($path)){
		$path = $softaculous_wp_config['plugins_root_dir'].'/softaculous/'.softaculous_optREQ('testpath');
	}
	
	if(softaculous_is_dir($path)){
		$path = $softaculous_wp_config['plugins_root_dir'].'/softaculous/'.softaculous_srandstr(16);
	}
	
	softaculous_mkdir($path, 0700, 1);
	
	$resp = 0;
	if(softaculous_is_dir($path)){
		$resp = 1;
	}
	
	@rmdir($path);
	
	if(isset($_GET['testpath'])){
		$return = array();
		$return['can_create'] = $resp;
		
		echo wp_json_encode($return);
	}
	
	return $resp;
}

/**
 * Fetch the installed version of softaculous plugin in the website
 *
 * @returns		string softaculous version number
 * @since		1.4.3
 */
function softaculous_fetch_version(){
	global $softaculous_wp_config;
	
	$plugin_data = softaculous_get_plugin_data($softaculous_wp_config['plugins_root_dir'].'/softaculous/softaculous.php');
	
	$softaculous_version = $plugin_data['Version'];
	
	if(isset($_GET['callfetch'])){
		$return = array();
		$return['wpc_ver'] = $softaculous_version;
		
		echo wp_json_encode($return);
	}
	
	return $softaculous_version;	
}

/**
 * Provides access to the website's admin panel
 *
 * @returns		bool
 * @since		1.0
 */
function softaculous_signon(){
	
    global $softaculous_lang, $softaculous_error;
	
	// Query the users
	$users_query = new WP_User_Query( array(
		'role' => 'administrator',
		'orderby' => 'ID',
		'number' => 1
	) );
	
	// Get the results and the row
	$results = $users_query->get_results();
	$tmp = current($results);
	
	$user_info = get_userdata($tmp->ID);
		
	// Automatic login //
	$username = $user_info->user_login;
	$user = get_user_by('login', $username );
	
	// Redirect URL //
	if (!is_wp_error($user)){
		wp_clear_auth_cookie();
		wp_set_current_user($user->ID);
		wp_set_auth_cookie($user->ID);
	}
}

/**
 * Return the role of the current user.
 *
 * @since		1.1
 */
function softaculous_get_curr_user_role(){
    return wp_get_current_user()->roles[0];
}

/**
 * Dismiss Softaculous notice and save the dismiss date.
 *
 * @since		1.4.3
 */
function softaculous_dismiss_notice(){
	
	if(!isset($_POST['softaculous_security']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['softaculous_security'])), 'softaculous_js_nonce')){
		wp_send_json_error('Security Check Failed!');
	}
	
	$soft_dismissable_notice_date = get_option('softaculous_dismiss_notice_date');
	
	if(empty($soft_dismissable_notice_date)){
		add_option('softaculous_dismiss_notice_date', date('Y-m-d'));
	}else{
		update_option('softaculous_dismiss_notice_date', date('Y-m-d'));
	}

}

/**
 * Display Softaculous notice on the basis of last dismiss date. When user manually dismisses the notice, it remains for 1 month
 *
 * @since		1.4.3
 */
function softaculous_is_display_notice(){
	
	$soft_dismissable_notice_date = get_option('softaculous_dismiss_notice_date');
	
	if(empty($soft_dismissable_notice_date)){
		return true;
	}
	
	$soft_dismissable_notice_date2 = new DateTime($soft_dismissable_notice_date);
	$current_date = new DateTime(date('Y-m-d'));
	$date_diff_month = $soft_dismissable_notice_date2->diff($current_date);

	//Do not display notice again for a month
	if($date_diff_month->m < 1){
		return false;
	}
	
	return true;
}
/**
 * Display Softaculous notice in dashboard
 *
 * @since		1.0
 */
function softaculous_admin_notice($force = 0){
	
	if(!empty($_GET['page']) && $_GET['page'] == 'softaculous' && empty($force)){
		return '';
	}
    
    $role = softaculous_get_curr_user_role();
    
    if($role == 'administrator' && !softaculous_get_option('softaculous_connected')){

		echo '<div class="soft_notice notice notice-success my-soft-dismiss-notice is-dismissible" style="padding-bottom:10px;">
    		<div style="width:100%; padding-top:10px;">
				<table cellpadding="1" cellspacing="5">
					<tr>
						<td colspan="3" class="soft_sitehead">
						<span class="">Connect your site with Softaculous Cloud panel for easy management</span>
						</td>
					</tr>
					<tr>
						<td style="padding: 0 10px;">
							<img src="'.esc_attr(SOFTACULOUS_PLUGIN_URL) .'assets/images/logo.gif" alt="softaculous" title="softaculous" style="height:50px; padding:5px 0;">
						</td>
						<td>
							<table cellpadding="1" cellspacing="5">
								<tr>
									<td><b>Website URL</b></td>
									<td><span class="soft_siteurl">'.esc_html(get_option('siteurl')).'</span></td>
								</tr>
								<tr>
									<td><b>Softaculous Connection Key</b></td>
									<td><span class="soft_siteurl">'.esc_html(softaculous_get_connection_key()).'</span></td>
								</tr>
								<tr>
									<td colspan="2">
										<p style="display:inline-block; padding-right:20px; font-size:13px;"><a class="soft_button soft_button1" href="'.esc_attr(softaculous_get_addsite_link()).'" target="_blank">1-Click Connect</a></p>

										<p style="display:inline-block; padding:0 10px; font-size:13px;"><a class="soft_button soft_button3" href="https://www.softaculous.com/docs/wordpress-plugin/1-click-website-connection/" target="_blank" style="padding: 6px 12px; font-size:14px;">1-Click Connection Guide</a></p>

										<p style="display:inline-block; padding-right:20px; font-size:13px;"><a class="soft_button soft_button3" href="https://www.softaculous.com/docs/wordpress-plugin/adding-website-in-panel/" target="_blank" style="padding: 6px 12px; font-size:14px;">Connect Website Guide</a></p>

										<p style="display:inline-block; padding:0 10px; font-size:13px;"><a class="soft_button soft_button4" href="mailto:support@softaculous.com" target="_blank" style="padding: 6px 12px; font-size:14px;">Contact Us</a></p>

										<p style="display:inline-block; padding-right:20px; font-size:13px;"><a class="soft_button soft_button4" href="https://www.softaculous.com/" target="_blank" style="padding: 6px 12px; font-size:14px;">Visit Website</a></p>
									</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>
    			
    			<div style="text-align: left;">
    			</div>
    		</div>
    	</div>';
    }
}

/* Removes a Directory Recursively
 *
 * @param        string $path The path of the folder to be removed
 * @return       boolean
 * @since		1.4.3
*/
function softaculous_rmdir_recursive($path){
	if(!softaculous_is_safe_file($path)) return false;
	
	$path = (substr($path, -1) == '/' || substr($path, -1) == '\\' ? $path : $path.'/');
	
	softaculous_resetfilelist();
	
	$files = softaculous_filelist($path, 1, 0, 'all');
	$files = (!is_array($files) ? array() : $files);
	
	//First delete the files only
	foreach($files as $k => $v){
		if(softaculous_is_safe_file($k)){
			@chmod($k, 0777);
		}
		if(file_exists($k) && is_file($k) && @filetype($k) == "file"){
			@unlink($k);
		}
	}
	
	@clearstatcache();
	
	$folders = softaculous_filelist($path, 1, 1, 'all');
	$folders = (!is_array($folders) ? array() : $folders);
	@krsort($folders);

	//Now Delete the FOLDERS
	foreach($folders as $k => $v){
		if(softaculous_is_safe_file($k)){
			@chmod($k, 0777);
		}
		if(is_dir($k)){
			@rmdir($k);
		}
	}
	
	@rmdir($path);
	
	@clearstatcache();
	
	return true;
}

/* A Function that reset lists files
 *
 * @since		1.4.3
*/
function softaculous_resetfilelist(){
global $softaculous_directorylist;
	$softaculous_directorylist = array();
}

/* Ratings Notice HTML
 *
 * @since		1.4.4
*/
function softaculous_show_promo(){	
	global $softaculous_promo_opts;
	$opts = $softaculous_promo_opts;
	
	echo '<div class="notice notice-success" id="soft_promo" style="min-height:90px">
		<a class="soft_promo-close" href="javascript:" aria-label="Dismiss this Notice">
			<span class="dashicons dashicons-dismiss"></span> Dismiss
		</a>';
		
		if(!empty($opts['image'])){
			echo '<a href="'.esc_attr($opts['website']).'"><img src="'.esc_attr($opts['image']).'" style="float:left; margin:15px 20px 10px 10px" width="150" /></a>';
		}
		
		echo '
		<p style="font-size:13px">We are glad you like <a href="'.esc_attr($opts['website']).'"><b>Softaculous</b></a> and have been using it since the past few days. It is time to take the next step !</p>
		<p>
			'.(empty($opts['rating']) ? '' : '<a class="soft_promo_button soft_promo_button2" target="_blank" href="'.esc_attr($opts['rating']).'">Rate it 5★\'s</a>').'
			'.(empty($opts['facebook']) ? '' : '<a class="soft_promo_button soft_promo_button3" target="_blank" href="'.esc_attr($opts['facebook']).'"><span class="dashicons dashicons-thumbs-up"></span> Facebook</a>').'
			'.(empty($opts['twitter']) ? '' : '<a class="soft_promo_button soft_promo_button4" target="_blank" href="'.esc_attr($opts['twitter']).'"><span class="dashicons dashicons-twitter"></span> Tweet</a>').'
			'.(empty($opts['website']) ? '' : '<a class="soft_promo_button soft_promo_button4" target="_blank" href="'.esc_attr($opts['website']).'">Visit our website</a>').'
			'.(empty($opts['support']) ? '' : '<a class="soft_promo_button soft_promo_button4" target="_blank" href="'.esc_attr($opts['support']).'">Softaculous Support</a>').'
		</p>
	</div>';
}

/* Show Ratings Notice
 *
 * @since		1.4.4
*/
function softaculous_maybe_promo($opts){
	
	global $softaculous_promo_opts;
	
	// There must be an interval after which the notice will appear again
	if(empty($opts['interval'])){
		return false;
	}
	
	// Are we to show a promo	
	$opt_name = 'softaculous_promo_time';
	$promo_time = softaculous_get_option($opt_name);
	
	//Check if it is connected to Softaculous panel
	$connected = softaculous_get_option('softaculous_connected');
	
	//Display only if the website is connected to Softaculous panel and a day has passed since the connection or 3 months have passed since the last dismissal
	if(!empty($connected)){
		if(empty($promo_time)){
			update_option($opt_name, time() + (!empty($opts['after']) ? $opts['after'] * 86400 : 0));
			$promo_time = softaculous_get_option($opt_name);
		}
		
		// Is there interval elapsed
		if(time() > $promo_time){
			$softaculous_promo_opts = $opts;
			add_action('admin_notices', 'softaculous_show_promo');
		}
	}
	
	// Are we to disable the promo
	
	
	if(isset($_GET['softaculous_promo']) && (int)$_GET['softaculous_promo'] == 0 && isset($_POST['softaculous_security']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['softaculous_security'])), 'softaculous_js_nonce')){
		update_option($opt_name, time() + ($opts['interval'] * 86400));
		die('DONE');
	}
} 

// Update check
function softaculous_update_check(){
	
	$current_version = get_option('softaculous_version');
	$version = (int) str_replace('.', '', $current_version);
	
	if($current_version == SOFTACULOUS_VERSION){
		return true;
	}

	// Save the new Version
	update_option('softaculous_version', SOFTACULOUS_VERSION);
}

function softaculous_reset_conn_key(){
	$return['conn_key'] = softaculous_get_connection_key(1);
	delete_option('softaculous_connected');
	return $return;
}


/**
 * Generate validation key to allow user to sign in 
 *
 * @returns		bool
 * @since		1.1
 */
function softaculous_getsignonkey(){

	$signon_key = softaculous_srandstr(64);
	update_option('softaculous_signonkey', $signon_key);
	update_option('softaculous_signonkey_time', time());
	
	if(isset($_GET['get_wpcsignonkey'])){
		$return = array();
		$return['softaculous_signonkey'] = $signon_key;
		echo wp_json_encode($return);
	}

	return $signon_key;
}

/**
 * Check for the authorization of the request using the temporary validation key
 *
 * @returns		bool
 * @since		1.0
 */
function softaculous_signonkey_authorization(){
	global $softaculous_lang, $softaculous_error;
	
	$return = array(); 
    
	$validate_key = softaculous_optREQ('softaculous_signonkey');
	if(empty($validate_key)){
		$return['error'] = 'Unauthorized Access!!';
		echo wp_json_encode($return);
		die();
	}
	
	$verify_authkey = softaculous_get_option('softaculous_signonkey');
	if($validate_key !== $verify_authkey){
		$return['error'] = $softaculous_lang['invalid_signon_key'];
		echo wp_json_encode($return);
		die();
	}
	
	// Is the key within 5 minutes of creation ?
	if((time() - softaculous_get_option('softaculous_signonkey_time')) > 300){
		$return['error'] = 'The Authorization Key has expired';
		echo wp_json_encode($return);
		die();
	}
	
	delete_option('softaculous_signonkey');
	delete_option('softaculous_signonkey_time');
	
}

function softaculous_sort_uname_callback( $a, $b ) {
	return strnatcasecmp( $a['Name'], $b['Name'] );
}

// Validates an IP
function softaculous_valid_ip($ip){
	if(!preg_match('/^(\d){1,3}\.(\d){1,3}\.(\d){1,3}\.(\d){1,3}$/is', $ip) || substr_count($ip, '.') != 3){			
		return false;
	}
	
	$r = explode('.', $ip);
	
	foreach($r as $v){
		$v = (int) $v;
		if($v > 255 || $v < 0){
			return false;
		}
	}
	
	return true;
	
}

// get add site link
function softaculous_get_addsite_link(){
	return SOFTACULOUS_ADDSITE.'&siteurl='.get_option('siteurl').'&conn_key='.softaculous_get_connection_key();
}