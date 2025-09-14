<?php

if (!defined('ABSPATH')){
    exit;
}

function softaculous_page_settings($title = 'Softaculous Dashboard'){
	global $softaculous_lang, $softaculous_error, $softaculous_msg;
	
	softaculous_admin_notice(1);
	
	if(!empty($softaculous_error)){
		echo '<div id="message" class="error"><p>'.esc_html($softaculous_error).'</p></div>';
	}
	
	if(!empty($softaculous_msg)){
		echo '<div id="message" class="updated"><p>'.esc_html($softaculous_msg).'</p></div>';
	}
	echo '<div style="margin: 10px 20px 0 2px;">	
			<div class="metabox-holder columns-2">
			<div class="postbox-container">	
			<div class="wrap">
				<h1><!--This is to fix promo--></h1>
				<table cellpadding="2" cellspacing="1" width="100%" class="fixed" border="0">
					<tr>
						<td valign="top"><h1>'.esc_html($title).'</h1>
						</td>
						<td align="right" width="40"><a target="_blank" href="https://twitter.com/softaculous"><img src="'.esc_attr(SOFTACULOUS_PLUGIN_URL).'assets/images/twitter.png" /></a></td>
						<td align="right" width="40"><a target="_blank" href="https://www.facebook.com/softaculous"><img src="'.esc_attr(SOFTACULOUS_PLUGIN_URL).'assets/images/facebook.png" /></a></td>
					</tr>
				</table>
				<hr/>
				<br/>
				<div class="postbox">
				<div class="postbox-header">
					<h2 class="hndle ui-sortable-handle">
						<span>'.esc_html__('General Settings', 'softaculous').'</span>
					</h2>
				</div>
				<div class="inside">
				<!--Main Table-->
				<table cellpadding="8" cellspacing="1" width="100%" class="form-table">
					<form action="" method="post">
						<tr>
							<th valign="top"><label for="soft_conn_key">'.esc_html__('Softaculous Connection Key', 'softaculous').'</label></th>
							<td>
								<input type="text" name="softaculous_conn_key" id="soft_conn_key" value="'.esc_attr(softaculous_get_connection_key()).'" size="60" readonly>
								<input type="submit" name="reset_conn_key" class="button button-primary action" value="'.esc_html__('Reset Key', 'softaculous').'" >
							</td>
						</tr>
						<tr>
							<th valign="top"><label for="soft_allowed_ips">'.esc_html__('Allowed IP(s)', 'softaculous').'</label></th>
							<td>
								<input type="text" name="softaculous_allowed_ips" id="soft_allowed_ips" value="'.(isset($_POST['softaculous_allowed_ips']) ? esc_html(sanitize_text_field($_POST['softaculous_allowed_ips'])) : esc_attr(implode(',' , softaculous_get_allowed_ips()))).'"><br />
								<span>'.esc_html__('Please enter comma separated IP address(s) of panels which will be allowed to make API calls', 'softaculous').'</span>
								<br/>
								</br/>
								<input type="submit" name="softaculous_save" class="button button-primary action" value="'.esc_html__('Save Settings', 'softaculous').'" >
							</td>
						</tr>
						'.wp_nonce_field('softaculous-options').'
					</form>
				</table>				
				</div>
				</div>

				<br/>
				<br/>
				<div style="width:45%;background:#FFF;padding:15px; margin:auto">
					<b>'.esc_html__('Let your followers know that you are managing WordPress websites like a Pro using Softaculous', 'softaculous').' :</b>
					<form method="get" action="https://twitter.com/intent/tweet" id="tweet" onsubmit="return dotweet(this);">
						<textarea name="text" cols="45" row="3" style="resize:none;">'.esc_html__('I easily manage my #WordPress #site using @softaculous', 'softaculous').'</textarea>
						&nbsp; &nbsp; <input type="submit" value="Tweet!" class="button button-primary" onsubmit="return false;" id="twitter-btn" style="margin-top:20px;"/>
					</form>
			
				</div>
				<br />
			<hr />
			<a href="'.esc_attr(SOFTACULOUS_WWW_URL).'" target="_blank">Softaculous</a> v'.esc_attr(SOFTACULOUS_VERSION).' You can report any bugs <a href="https://wordpress.org/plugins/softaculous/" target="_blank">here</a>.
		</div>
		</div>
		</div>
	</div>';
}
	
if(isset($_POST['reset_conn_key'])){
	
	global $softaculous_lang, $softaculous_error, $softaculous_msg;
	
	/* Make sure post was from this page */
	check_admin_referer('softaculous-options');
	
	if(!current_user_can('manage_options')){
		wp_die('Sorry, but you do not have permissions to reset connection key.');
	}
	
	softaculous_get_connection_key(1);
	
	if(empty($softaculous_error)){
		$softaculous_msg = __('Connection key reset successfully', 'softaculous');
	}
}

if(isset($_POST['softaculous_save'])){
	global $softaculous_lang, $softaculous_error, $softaculous_msg;
	
	/* Make sure post was from this page */
	check_admin_referer('softaculous-options');
	
	if(!current_user_can('manage_options')){
		wp_die('Sorry, but you do not have permissions to save settings.');
	}

	if(isset($_REQUEST['softaculous_allowed_ips'])){
		
		$_allowed_ips = array();
		
		$allowed_ips = softaculous_optPOST('softaculous_allowed_ips');
		$_a_ips = explode(',', $allowed_ips);
		
		foreach($_a_ips as $aip){
			$aip = trim($aip);
			if(empty($aip)){
				continue;
			}
			
			if(!softaculous_valid_ip($aip)){
				$softaculous_error = __('Please enter valid IP(s)', 'softaculous');
				continue;
			}
			
			$_allowed_ips[] = $aip;
		}
		
		if(empty($_allowed_ips)){
			$softaculous_error = $softaculous_lang['empty_allowed_ips'];
		}
		
		if(empty($softaculous_error)){
			update_option('softaculous_allowed_ips', $_allowed_ips);
		}
	}
	
	if(empty($softaculous_error)){
		$softaculous_msg = __('Settings saved successfully', 'softaculous');
	}
}

softaculous_page_settings();
