<?php

if (!defined('ABSPATH')){
    exit;
}

function softaculous_file_actions(){
	global $softaculous_error, $softaculous_lang;

	$return = array();

	$action = softaculous_optREQ('request');

	if(empty($action)){
		$return['error'] = $softaculous_lang['no_req_post'];
		echo wp_json_encode($return);
		die();
	}

	if($action == 'put'){
		$filename = urldecode(softaculous_optREQ('filename'));
		$putdata = base64_decode(softaculous_optREQ('putdata'));

		$func_response = softaculous_put($filename, $putdata);

		if($func_response){
			$return['done'] = 'done';
		}else{
			$return['error'] = $softaculous_lang['err_exec'];
		}

		echo wp_json_encode($return);
		die();
	}

	$str_args = urldecode(softaculous_optREQ('args'));
	$args = explode(',', $str_args);

	if(function_exists('softaculous_'.$action)){
		if(!empty($args)){
			if(count($args) > 1){
				$func_response = call_user_func_array('softaculous_'.$action, $args);
			}else{
				$func_response = call_user_func('softaculous_'.$action, $str_args);
			}
		}else{
			$func_response = call_user_func('softaculous_'.$action);
		}
		$return['func_response'] = $func_response;

		if($func_response){
			$return['done'] = $softaculous_lang['done'];
		}else{
			$return['error'] = $softaculous_lang['err_exec'];
		}

	}else{
		$return['error'] = $softaculous_lang['func_not_found'];
	}

	echo wp_json_encode($return);

}
