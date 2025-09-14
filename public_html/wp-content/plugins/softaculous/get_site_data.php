<?php

if (!defined('ABSPATH')){
    exit;
}

function softaculous_get_site_data(){
	global $softaculous_lang, $softaculous_wp_config, $softaculous_error;
	
	$return = array();

	$type = softaculous_optGET('type');

	$return['wordpress_current_version'] = softaculous_version_wp();

	if($type == 'plugins'){
		$return['active_plugins'] = softaculous_get_option('active_plugins');
		$all_plugins = softaculous_get_plugins();

		foreach($all_plugins as $pk => $pv){
			$installed_version = $pv['Version'];
		}

		$outdated_plugins = softaculous_get_outdated_plugins();

		$outdated_plugins_keys = array_keys($outdated_plugins);
		foreach($all_plugins as $allk => $allv){
			if(in_array($allk, $outdated_plugins_keys)){
				$all_plugins[$allk]['new_version'] = $outdated_plugins[$allk]->new_version;
			}
		}

		$return['all_plugins'] = $all_plugins;
		
	}elseif($type == 'themes'){
		
		$return['active_theme'] = array_keys(softaculous_get_active_theme());
		$return['all_themes'] = softaculous_get_installed_themes();
		
	}elseif($type == 'posts'){
		
		$post_id = softaculous_optGET('post_id');
		$args = array('post_status' => 'any', 'numberposts' => -1);
		
		if(!empty($post_id)){
			$args['post__in'] = array($post_id);
		}

		$all_posts = get_posts($args);

		foreach($all_posts as $postk => $postv){
			if(empty($post_id)){
				unset($postv->post_content);
			}
			$user_data = get_user_by('id', $postv->post_author);
			$all_posts[$postk]->post_author = $user_data->data->display_name;
			$all_posts[$postk]->post_featured_image = get_the_post_thumbnail_url($all_posts[$postk]->ID, 'full');
		}

		$return['all_posts'] = $all_posts;

	}else{
		$return['error'] = $softaculous_lang['invalid_params'];
	}

	echo wp_json_encode($return);

}
