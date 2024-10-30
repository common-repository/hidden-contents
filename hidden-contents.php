<?php
/*
Plugin Name: Hidden Contents
Plugin URI: http://wpmart.com
Description: A handy plugin for hide contents and images from guest users.
Version: 1.0
Author: Morteza Geransayeh
Author URI: http://geransayeh.com
*/


/**
 * Load plugin textdomain.
**/
add_action( 'init', 'hncs_load_textdomain' );
function hncs_load_textdomain() {
  load_plugin_textdomain( 'hidden-contents', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
}

/**
 * Including CSS & JavaScript
**/
function hncs_enqueue_scripts() {
	wp_enqueue_script( 'hidden-contents', plugins_url('assets/js/hidden-contents.js', __FILE__) , array ( 'jquery' ), 1.1, false);
	wp_enqueue_style( 'hidden-contents', plugins_url('assets/css/hidden-contents-admin.css', __FILE__) );
}
add_action( 'admin_enqueue_scripts', 'hncs_enqueue_scripts' );
function hncs_enqueue_style() {
	wp_enqueue_style( 'hidden-contents', plugins_url('assets/css/hidden-contents.css', __FILE__) );
}
add_action( 'wp_head', 'hncs_enqueue_style' );

/**
 * Register settngs page
**/
function hncs_register_settngs_page() {
    add_menu_page(
        __( 'Hide Content', 'hidden-contents' ),
        __( 'Hide Content', 'hidden-contents' ),
        'manage_options',
        'hncs_settngs_page',
        'hncs_settngs_page',
        'dashicons-hidden'
    );
}
add_action( 'admin_menu', 'hncs_register_settngs_page' );

/**
 * Sanitizing Arrays in WordPress
**/
function hncs_sanitize_array( $input ) {
  return array_map( function( $val ) {
    return sanitize_text_field( $val );
  }, $input );
}

/**
 * Settngs page
**/
function hncs_settngs_page(){
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.', 'hidden-contents') );
	}
	if ( is_admin() ) {
		global $post_id;
		wp_enqueue_script('media-upload');
		wp_enqueue_script('post');
		wp_enqueue_media( array( 'post' => $post_id ) );
		wp_register_script('wpanel-upload', plugins_url('assets/js/wb-upload.js', __FILE__), array('jquery','media-upload','thickbox'));
		wp_enqueue_script('wpanel-upload');
	}

	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_script(
		'iris',
		admin_url( 'js/iris.min.js' ),
		array( 'jquery-ui-draggable', 'jquery-ui-slider', 'jquery-touch-punch' ),
		false,
		1
	);
?>
<div class="wrap">
	<h1><?php _e( 'Hide content settings', 'hidden-contents' ); ?></h1>
<?php
$tagify = '';
if( isset( $_POST['hncs_wnf_nonce'] ) ){
	if( wp_verify_nonce( $_POST['hncs_wnf_nonce'], 'hncs_wnf_action' ) || check_admin_referer( 'hncs_wnf_action', 'hncs_wnf_nonce' ) ){
		$tagify =  str_replace(array('[', ']', '\"', '\"'), array('', '', '', ''), $_POST['hncs_tags']);
		if(is_numeric($_POST['hncs_char_show'])){
			update_option("hncs_char_show", sanitize_text_field($_POST['hncs_char_show']));
		}
		update_option("hncs_logo", sanitize_text_field($_POST['hncs_logo']));
		update_option("hncs_tags", sanitize_textarea_field($tagify));
		if(!empty($_POST['hncs_post_type'])){
			$hncs_post_type = hncs_sanitize_array($_POST['hncs_post_type']);
		}else{
			$hncs_post_type = '';
		}
		update_option("hncs_post_type", $hncs_post_type);

?>
	<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> 
		<p><strong><?php _e( 'Settings Saved.', 'hidden-contents' ); ?></strong></p>
		<button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php _e( 'Close', 'hidden-contents' ); ?></span></button>
	</div>
<?php
	}else{
		wp_die( __('Sorry, your nonce did not verify.', 'hidden-contents') );
	}
}
?>
	<p><?php _e( "The following options are available to set the output of the 'Hide Content' plugin.", 'hidden-contents' ); ?></p>
	<form method="post">
		<table class="widefat importers striped">
			<tbody>
				<tr class="importer-item">
					<td class="import-system">
						<span class="importer-title"><label for="hncs_tags"><?php _e('Post Type to hide content', 'hidden-contents'); ?></label></span>
					</td>
					<td class="desc">
					  <select data-placeholder="<?php _e('Post Type', 'hidden-contents'); ?>" class="chosen-select" name="hncs_post_type[]" multiple="">
						<?php
						if(get_option('hncs_post_type'))
							$all_post_type = get_option('hncs_post_type');
						else
							$all_post_type = array();
						
						$post_types = get_post_types( array('public' => true), "objects", 'and' );
						foreach ($post_types as $post_type) {
						?>
						   <option value="<?php echo esc_html($post_type->name); ?>"<?php if(in_array(esc_html($post_type->name), $all_post_type)){echo " selected='selected'";}else{ echo ""; } ?>><?php echo esc_html($post_type->labels->singular_name); ?></option>
						<?php } ?>
					  </select>

					  <br><small><?php _e("Select Post Type to hide ", 'hidden-contents'); ?></small>
					</td>
				</tr>
				<tr class="importer-item">
					<td class="import-system">
						<span class="importer-title"><label><?php _e( 'Change content dump image', 'hidden-contents' ); ?></label></span>
					</td>
					<td class="desc">
						<input type="hidden" name="hncs_logo" id="wpanel_logo" value="<?php echo esc_url(get_option('hncs_logo')); ?>" placeholder="<?php _e('Upload image', 'hidden-contents'); ?>"/>
						<span class="wpanel-uploader"><i class="dashicons dashicons-format-image"></i> <?php _e( 'Change default image', 'hidden-contents' ); ?></span>
						<br><small><?php _e( "You can change default image holder from here.", 'hidden-contents' ); ?></small>
					</td>
				</tr>
				<tr class="importer-item">
					<td class="import-system">
						<span class="importer-title"><label for="hncs_char_show"><?php _e( 'Character to show', 'hidden-contents' ); ?></label></span>
					</td>
					<td class="desc">
					  <input name="hncs_char_show" id="hncs_char_show" type="number" value="<?php echo esc_html(get_option('hncs_char_show')); ?>">
					  <br><small><?php _e( "Enter the number of characters you want to be visible to the user.", 'hidden-contents' ); ?></small>
					</td>
				</tr>
				<tr class="importer-item">
					<td class="import-system">
						<span class="importer-title"><label for="hncs_tags"><?php _e('Content phars list to show', 'hidden-contents'); ?></label></span>
					</td>
					<td class="desc">
					  <textarea class="regular-text" name="hncs_tags" id="hncs_tags" placeholder="<?php _e('write some phars', 'hidden-contents'); ?>" autofocus><?php echo esc_html(str_replace(array('[', ']', '\"', '\"'), array('', '', '', ''), get_option('hncs_tags'))); ?></textarea>
					  <br><small><?php _e("The exlude list is for creating a list of words that is visible to the user in your contents. <br>Example phars: we, was, or, that,... ", 'hidden-contents'); ?></small>
					</td>
				</tr>
			</tbody>
		</table>
		<?php wp_nonce_field( 'hncs_wnf_action', 'hncs_wnf_nonce'); ?>
		<p class="submit"><input name="submit" id="submit" class="button button-primary" value="<?php _e( 'Save Settings', 'hidden-contents' ); ?>" type="submit"></p>
	</form>
</div>
<script>
	document.forms[0].reset();
	var hncsTextArea = jQuery('textarea[name=hncs_tags]').tagify();
	hncsTextArea.tagify('inputField');
	
	jQuery(".chosen-select").chosen({rtl: true});
</script>
<?php
}

/**
 * Main functionality
**/
function hncs_hide_content($content){
	
	if(esc_html(get_option("hncs_for_users"))=='no'){
		if(is_user_logged_in())
			return $content;
	}
	$newcontent=$content;
	$excluded_content_to_show = '';
	if(is_numeric(get_option("hncs_char_show"))){
		if(get_option("hncs_char_show")!=''){
			$excluded_content_to_show = substr($newcontent, 0, esc_html(get_option("hncs_char_show")));
			$newcontent = substr($newcontent, esc_html(get_option("hncs_char_show")));
		}
	}
	
	$filtered_content = preg_replace('/\s\s+/', ' (enter) ', $newcontent);
	$filtered_content = strip_tags(preg_replace("/<img[^>]+\>/i", " (image) ", $filtered_content)); 
	if(esc_html(get_option('hncs_tags'))!=''){
		$excludes = esc_html(get_option('hncs_tags')).",(image),(showimage),(enter)";
		$excludes = explode(',', $excludes);
	}else{
		$excludes = array('(image)','(showimage)','(enter)');
	}
	
	$dump_content     = array_filter(explode(' ', $filtered_content));
	$new_content      = array();
	
	foreach($dump_content as $chars){
		if(in_array($chars, $excludes)){
			if($chars == '(image)'){
				if(esc_url(get_option('hncs_logo'))!=''){
					$new_chars = "<img src='".esc_url(get_option('hncs_logo'))."'>";
				}else{
					$new_chars = "<img src='".plugin_dir_url( __FILE__ )."assets/images/default.jpg'>";
				}
			}elseif( $chars == '(enter)'){
				$new_chars = "<br>";
			}else{
				$new_chars = "&nbsp;".$chars."&nbsp;";
			}
		}else{
			$w = strlen($chars)*5;
			$new_chars = "<span class='hide-block' style='width:".$w."px'></span>";

		}
		array_push($new_content, $new_chars);
	}
	$outContent =  implode(' ', $new_content);
	
	$post_types = array();
	if(get_option('hncs_post_type'))
		$post_types = get_option('hncs_post_type');
	
	$current_post_type = '';
	if(get_the_ID()!='')
		$current_post_type = get_post_type( get_the_ID() );
	
	if(in_array($current_post_type, $post_types)){
		return $excluded_content_to_show.$outContent;
	}
	else{
		return $content;
	}
	
}
add_filter("the_content", "hncs_hide_content");