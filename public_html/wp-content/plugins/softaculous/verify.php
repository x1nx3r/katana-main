<?php

if (!defined('ABSPATH')){
    exit;
}

function softaculous_verify($auth_key = ''){
	global $softaculous_lang, $wpdb, $wp_version, $softaculous_wp_config, $softaculous_error;
	
	$return = array();

	$site_settings = array();
	$site_settings['ver'] = $wp_version;
	$site_settings['softpath'] = rtrim(get_home_path(), '/');
	$site_settings['siteurl'] = get_option('siteurl');
	$site_settings['adminurl'] = admin_url();
	$site_settings['softdb'] = $softaculous_wp_config['softdb'];
	$site_settings['softdbuser'] = $softaculous_wp_config['softdbuser'];
	$site_settings['softdbhost'] = $softaculous_wp_config['softdbhost'];
	$site_settings['softdbpass'] = $softaculous_wp_config['softdbpass'];
	$site_settings['dbprefix'] = $softaculous_wp_config['dbprefix'];
	$site_settings['site_name'] = get_option('blogname');
	
	$site_settings['auth_key'] = (!empty($auth_key) ? $auth_key : '');

	//Fetch all the table names
	$sql = "SHOW TABLES FROM ".$softaculous_wp_config['softdb'];
	$results = $wpdb->get_results($sql);

	$site_settings['softdbtables'] = array();
	foreach($results as $index => $value) {
		foreach($value as $tableName) {
			$site_settings['softdbtables'][] = $tableName;
		}
	}
	
	$site_settings['wpc_backupdir'] = $softaculous_wp_config['uploads_dir'].'/softaculous_backups_'.(!empty($auth_key) ? $auth_key : softaculous_optREQ('auth_key'));
	
	$site_settings['createdir'] = softaculous_can_createdir($site_settings['wpc_backupdir']);
	
	$site_settings['wpc_ver'] = softaculous_fetch_version();

	$return['data'] = $site_settings;
	
	softaculous_connectok();

	echo wp_json_encode($return);

}