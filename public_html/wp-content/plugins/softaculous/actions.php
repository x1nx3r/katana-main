<?php

if (!defined('ABSPATH')){
    exit;
}

function softaculous_site_actions(){
	global $softaculous_lang, $softaculous_error, $softaculous_wp_config;

	$return = array();

	$request = softaculous_optREQ('request');

	if(empty($request)){
		$return['error'] = $softaculous_lang['no_req_post'];
		echo wp_json_encode($return);
		die();
	}
	
	if($request == 'update_website'){
		$source = urldecode(softaculous_optREQ('source'));

		include_once(ABSPATH.'wp-admin/includes/class-wp-upgrader.php');
		include_once(ABSPATH.'wp-admin/includes/update.php');
		include_once(ABSPATH.'wp-admin/includes/misc.php');

		global $wp_filesystem;

		$upgrade_error = array();

		$wp_upgrader_skin = new WP_Upgrader_Skin();
		$wp_upgrader_skin->done_header = true;

		$wp_upgrader = new WP_Upgrader($wp_upgrader_skin);

		$res = $wp_upgrader->fs_connect(array(get_home_path(), WP_CONTENT_DIR));
		if (!$res || is_wp_error($res)){
			$upgrade_error[] = $res;
		}

		$download = $wp_upgrader->download_package($source);
		if (is_wp_error($download)){
			$upgrade_error[] = $download;
		}

		$working_dir = $wp_upgrader->unpack_package($download);
		if (is_wp_error($working_dir)){
			$upgrade_error[] = $working_dir;
		}

		$wp_dir = trailingslashit($wp_filesystem->abspath());

		if (!$wp_filesystem->copy($working_dir.'/wordpress/wp-admin/includes/update-core.php', $wp_dir.'wp-admin/includes/update-core.php', true)){
			$wp_filesystem->delete($working_dir, true);
			
			$upgrade_error[] = $softaculous_lang['copy_fail'];
		}

		$wp_filesystem->chmod($wp_dir.'wp-admin/includes/update-core.php', FS_CHMOD_FILE);
		include_once(get_home_path().'wp-admin/includes/update-core.php');

		if(!function_exists('update_core')){
			$upgrade_error[] = $softaculous_lang['call_update_fail'];
		}

		$result = update_core($working_dir, $wp_dir);
		if(is_wp_error($result)){
			$upgrade_error[] = $result->get_error_code();
		}

		if(!empty($upgrade_error)){
			$return['error'] = 'error: '.implode("\n", $upgrade_error);
		}
		
		$return['updatedto'] = softaculous_version_wp();
	}
	
	if($request == 'create_post'){
		// Create post object
		$my_post = array(
			'post_title'    => $_POST['post_title'], //WP handles sanitization in wp_insert_post fn
			'post_content'  => $_POST['post_content'],
			'post_status'   => 'publish',
			'post_author'   => 1
		);

		// Insert the post into the database
		$create_post_response = wp_insert_post($my_post);
		
		$post_featured_image = softaculous_optPOST('featured_image');
	
		if(!empty($create_post_response) && !empty($post_featured_image)){
			
			$image_id = media_sideload_image($post_featured_image, $create_post_response, null, 'id');

			if (!is_wp_error($image_id)) {
				set_post_thumbnail($create_post_response, $image_id);
			}
		    
		}
		
		$return['create_post_response'] = $create_post_response;
	}
	
	if($request == 'delete_post'){
		
		$post_id = softaculous_optREQ('del_post');

		// Delete the post from the database
		$return['delete_post_response'] = wp_delete_post($post_id);
	}
	
	if($request == 'publish_post'){
		
		$post_id = softaculous_optREQ('post_id');
		
		$post_data = array('ID' => $post_id, 'post_status' => 'publish');

		// Delete the post from the database
		$return['publish_post_response'] = wp_update_post($post_data);
	}
		
	if(softaculous_optGET('plugins') || softaculous_optGET('plugin')){
		$plugins = urldecode(softaculous_optREQ('plugins'));
		$arr_plugins = explode(',', $plugins);

		if($request == 'activate'){//Activate
			
			$res = softaculous_activate_plugin($arr_plugins);
			if(!$res){
				$return['error'] = $softaculous_lang['err_activating_pl'];
			}        
		}elseif($request == 'deactivate'){//Deactivate

			$res = softaculous_deactivate_plugin($arr_plugins);
			if(!$res){
				$return['error'] = $softaculous_lang['err_deactivating_pl'];
			}        
		}elseif($request == 'delete'){//Deactivate and then Delete

			$act_res = softaculous_deactivate_plugin($arr_plugins);        
			if(!$act_res){
				$return['error'] = $softaculous_lang['err_deactivating_del_pl'];
			}
			
			// Delete the old list of installed plugins (otherwise delete fails)
			wp_cache_delete('plugins', 'plugins');
			
			$result = delete_plugins($arr_plugins);
			if(is_wp_error($result)) {
				$return['error'] = $result->get_error_message();
			}elseif($result === false) {
				$return['error'] = $softaculous_lang['err_deleting_pl'];
			}
		}elseif($request == 'install'){//Install Plugins
			
			$sources = urldecode(softaculous_optREQ('sources'));
			$arr_sources = explode(',', $sources);
			
			$all_installed_plugins = array();
			
			foreach($arr_plugins as $plk => $plval){
				
				//Skip if the plugin is already installed
				if(softaculous_is_plugin_installed($plval)){
					continue;
				}
				
				$filename = basename(parse_url($arr_sources[$plk], PHP_URL_PATH));

				$download_dest = $softaculous_wp_config['uploads_dir'].'/'.$filename;
				$unzip_dest = $softaculous_wp_config['plugins_root_dir'];

				softaculous_get_web_file($arr_sources[$plk], $download_dest);

				if(softaculous_sfile_exists($download_dest)){
					$res = softaculous_unzip($download_dest, $unzip_dest);
				}

				@softaculous_sunlink($download_dest);

				//Activate the installed plugin(s)
				$pl_slug = $plval;
				if(preg_match('/(.*?)\/(.*?)\.php/is', $plval)){
				    softaculous_preg_replace('/(.*?)\/(.*?)\.php/is', $plval, $pl_slug, 1, 1);
				}
				
				if(empty($pl_slug)){//This is the case for the default Hello Dolly plugin that comes installed with the initial WP package
					continue;
				}
				
				$all_installed_plugins[] = softaculous_get_plugin_path(ABSPATH.'wp-content/plugins/'.$pl_slug, $pl_slug);
			}
			
			// Delete the old list of installed plugins (otherwise activate fails)
			wp_cache_delete('plugins', 'plugins');
			
			//Activate the installed plugins
			softaculous_activate_plugin($all_installed_plugins);

			if(!empty($softaculous_error)){
				$return['error'] = $softaculous_error;
			}
		}elseif($request == 'update'){
			
			$plugin_name = urldecode(softaculous_optREQ('plugin'));
			$download_link = urldecode(softaculous_optREQ('source'));
			
			//For backward compatibility
			if(!is_array($plugin_name)) $plugin_name = array($plugin_name);
			if(!is_array($download_link)) $download_link = array($download_link);
			
			$sources = urldecode(softaculous_optREQ('sources'));
			$arr_sources = explode(',', $sources);
			
			$arr_plugins = array_merge($plugin_name, $arr_plugins);
			$arr_sources = array_merge($download_link, $arr_sources);
			
			$site_url = urldecode(softaculous_optREQ('siteurl'));
			
			foreach($arr_plugins as $plk => $plval){			
				$filename = basename(parse_url($arr_sources[$plk], PHP_URL_PATH));
				
				$download_dest = $softaculous_wp_config['uploads_dir'].'/'.$filename;
				$unzip_dest = $softaculous_wp_config['plugins_root_dir'];
				
				softaculous_get_web_file($arr_sources[$plk], $download_dest);
				
				if(softaculous_sfile_exists($download_dest)){
					$res = softaculous_unzip($download_dest, $unzip_dest);
				}
				
				@softaculous_sunlink($download_dest);
			}
			
			// Lets visit the installation once to make the changes in the database
			$resp = wp_remote_get($site_url);
			
			if(!empty($softaculous_error)){
				$return['error'] = $softaculous_error;
			}
		}
	}elseif(softaculous_optGET('themes') || softaculous_optGET('theme')){
		
		$themes = urldecode(softaculous_optREQ('themes'));
		$arr_themes = explode(',', $themes);

		$active_theme = array_keys(softaculous_get_active_theme());		
		
		if($request == 'activate' && count($arr_themes) == 1){//Activate
			
			//Do not activate/delete the theme if it is active
			if($active_theme[0] != $arr_themes[0]){
				$res = softaculous_activate_theme($arr_themes);
				if(!empty($softaculous_error)){
					$return['error'] = $softaculous_error;
				}
				if(!$res){
					$return['error'] = $softaculous_lang['err_activating_theme'];
				}
			}
			
		}elseif($request == 'delete'){//Delete
			
			//Do not delete the theme if it is active
			foreach($arr_themes as $tk => $tv){
				if($active_theme[0] == $tv){
					unset($arr_themes[$tk]);
				}
			}
			
			$res = softaculous_delete_theme($arr_themes);
			if(!empty($softaculous_error)){
				$return['error'] = $softaculous_error;
			}
			if(!$res){
				$return['error'] = $softaculous_lang['err_deleting_theme'];
			}
			
		}elseif($request == 'install'){//Install Themes
			
			$sources = urldecode(softaculous_optREQ('sources'));
			$arr_sources = explode(',', $sources);
			
			foreach($arr_themes as $thk => $thval){
				
				//Skip if the theme is already installed
				if(softaculous_is_theme_installed($thval)){
					continue;
				}
			
				$filename = basename(parse_url($arr_sources[$thk], PHP_URL_PATH));
				
				$download_dest = $softaculous_wp_config['uploads_dir'].'/'.$filename;
				$unzip_dest = $softaculous_wp_config['themes_root_dir'].'/';
				
				softaculous_get_web_file($arr_sources[$thk], $download_dest);
				
				if(softaculous_sfile_exists($download_dest)){
					$res = softaculous_unzip($download_dest, $unzip_dest);
				}
				
				@softaculous_sunlink($download_dest);
			}
			
			if(!empty($softaculous_error)){
				$return['error'] = $softaculous_error;
			}
		}elseif($request == 'update'){//Update Theme
		
			$theme_name = urldecode(softaculous_optREQ('theme'));
			$download_link = urldecode(softaculous_optREQ('source'));
			
			//For backward compatibility
			if(!is_array($theme_name)) $theme_name = array($theme_name);
			if(!is_array($download_link)) $download_link = array($download_link);
			
			$sources = urldecode(softaculous_optREQ('sources'));
			$arr_sources = explode(',', $sources);
			
			$arr_themes = array_merge($theme_name, $arr_themes);
			$arr_sources = array_merge($download_link, $arr_sources);
			
			$site_url = urldecode(softaculous_optREQ('siteurl'));
			
			foreach($arr_themes as $thk => $thval){			
				$filename = basename(parse_url($arr_sources[$thk], PHP_URL_PATH));
				
				$download_dest = $softaculous_wp_config['uploads_dir'].'/'.$filename;
				$unzip_dest = $softaculous_wp_config['themes_root_dir'];
				
				softaculous_get_web_file($arr_sources[$thk], $download_dest);
				
				if(softaculous_sfile_exists($download_dest)){
					$res = softaculous_unzip($download_dest, $unzip_dest);
				}
				
				@softaculous_sunlink($download_dest);
			}
			
			// Lets visit the installation once to make the changes in the database
			$resp = wp_remote_get($site_url);
			
			if(!empty($softaculous_error)){
				$return['error'] = $softaculous_error;
			}
		}
	}

	if(empty($return['error'])){
		$return['result'] = 'done';
	}

	//Using serialize here as all_plugins contains class object which are not json_decoded in Softaculous.
	echo wp_json_encode($return);

}