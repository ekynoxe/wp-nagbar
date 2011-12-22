<?php
/* 
Plugin Name: WP-Nagbar
Plugin URI: http://www.ekynoxe.com/
Version: v1.0
Author: <a href="http://mathieudavy.com/">Mathieu Davy</a>
Description: A plugin for developers to integrate a nagbar into sites they create for clients.
Changelog:
	V1.0 This plugin allows only to create a single, non-closable, and permanent nagbar with any rich text and links to posts
Requires Wordpress 3.3+ (using wp_editor)
 
Copyright 2011  Mathieu Davy  (email : mat t [a t] ek yn o xe [d ot] c om)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * Defining plugin constants
 */
if ( ! defined( 'WP_NAGBAR_PLUGIN_BASENAME' ) )
	define( 'WP_NAGBAR_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

if ( ! defined( 'WP_NAGBAR_PLUGIN_NAME' ) )
	define( 'WP_NAGBAR_PLUGIN_NAME', trim( dirname( WP_NAGBAR_PLUGIN_BASENAME ), '/' ) );

if ( ! defined( 'WP_NAGBAR_PLUGIN_URL' ) )
	define( 'WP_NAGBAR_PLUGIN_URL', WP_PLUGIN_URL . '/' . WP_NAGBAR_PLUGIN_NAME );

if ( ! defined( 'WP_NAGBAR_PLUGIN_TEXT_FIELD_NAME' ) )
	define( 'WP_NAGBAR_PLUGIN_TEXT_FIELD_NAME', 'WP_NAGBAR_PLUGIN_TEXT_FIELD' );

/**
 * Class declaration
 */
if (!class_exists("WPNagbar")) {
	class WPNagbar {
		/**
		 * Options field name in which all wp-nagbar options will be serialised and saved
		 */
		var $admin_options_name = "WPNagbarAdminOptions";
		
		/**
		 * wp_editor settings, including tiny_mce settings, for the text field
		 */
		var $wp_editor_settings = array(
			'wpautop' => false,
			'media_buttons' => false,
			'teeny' => false,
			'dfw' => false,
			"textarea_rows"=>1,
			'tinymce' => array(
				'theme_advanced_buttons1' => 'bold,italic,underline,|,undo,redo,|,link,unlink',
				'theme_advanced_buttons2' => '',
				'theme_advanced_buttons3' => '',
				'theme_advanced_buttons4' => '',
				'wpautop' => false,
				'forced_root_block' => false,
				'force_br_newlines' => false,
				'force_p_newlines' => false,
				'convert_newlines_to_brs' => false
				),
			'quicktags' => false
		);
		
		/**
		 * Constructor, nothing special here
		 */
		function WPNagbar() { 
		}
		
		/**
		 * Initializer for install, nothing special here
		 */
		function init() {
		}
		
		/**
		 * Public function to call in the theme to print the nagbar
		 * Options is an array containing the following:
		 *	=> before: content to display before the nagbar text. Defaults to '<p>'
		 *	=> after: content to display after the nagbar text. Defaults to '</p>'
		 * If options are passed, they will be merged with the default and override where appropriate
		 * If the text for the nagbar is empty, nothing will be displayed.
		 */
		public function print_nag_bar($display_settings=array()){
			$default_settings = array(
				"before" => '<p>',
				"after" => '</p>'
				);
			$display_settings = array_merge($default_settings, $display_settings);
			$the_options = get_option($this->admin_options_name);
			$content = stripslashes_deep($the_options[WP_NAGBAR_PLUGIN_TEXT_FIELD_NAME]);
			if(!empty($content)) {
				echo '<div id="wp-nagbar">' . $display_settings['before'] . $content . $display_settings['after'] . '</div>';
			}
		}
		
		/**
		 * Print the admin page settings
		 */
		function printAdminPage() {
			if(!is_admin()){
				return;
			}
			$the_options = get_option($this->admin_options_name);
			if (isset($_POST['update_WPNagbar_settings'])) {
				if (isset($_POST[WP_NAGBAR_PLUGIN_TEXT_FIELD_NAME])) {
					$the_options[WP_NAGBAR_PLUGIN_TEXT_FIELD_NAME] = stripslashes_deep($_POST[WP_NAGBAR_PLUGIN_TEXT_FIELD_NAME]);
				}
				update_option($this->admin_options_name, $the_options);
		?>
		<div class="updated"><p><strong><?php _e("Settings Updated.", "WPNagbar");?></strong></p></div>
		<?php
			}
		?>
		<div class="wrap">
		<h2>WP Nagbar Options Page</h2>
		<form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
		<?php wp_nonce_field('update-options'); ?>
		
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th>Text for the nag bar</th>
					<td><?php wp_editor(stripslashes_deep($the_options[WP_NAGBAR_PLUGIN_TEXT_FIELD_NAME]), WP_NAGBAR_PLUGIN_TEXT_FIELD_NAME, $this->wp_editor_settings ); ?></td>
				</tr>
			</tbody>
		</table>
		
		<p><input type="submit" name="Submit" value="Update Settings" /></p>
		
		<input type="hidden" name="update_WPNagbar_settings" value="update" />
		</form>
		</div>
		<?php
		}
	}
}
//End Class WPNagbar

/**
 * Create a new instance of WPNagbar
 */
if (class_exists("WPNagbar")) {
	$wp_nagbar = new WPNagbar();
}

/**
 * Registers the admin panel creation function
 */
if (!function_exists("WPNagbar_admin_panel")) {
	function WPNagbar_admin_panel() {
		global $wp_nagbar;
		if (!isset($wp_nagbar)) {
			return;
		}
		if (function_exists('add_options_page')) {
			add_options_page('WP Nagbar', 'WP Nagbar', 9, basename(__FILE__), array(&$wp_nagbar, 'printAdminPage'));
		}
	}	
}

/**
 * Registers actions to:
 *	- initialize the plugin on activation.
 *	- initialize the admin panel by adding the wp nagbar options page to the left hand menu
 */
if (isset($wp_nagbar)) {
	//Actions
	add_action('admin_menu', 'WPNagbar_admin_panel');
	add_action('activate_wp-nagbar/wp-nagbar.php',  array(&$wp_nagbar, 'init'));
}

/**
 * Insert the default wp nagbar css in the site <head>
 */
function WPNagbar_register_head() {
	$siteurl = get_option('siteurl');
	$url = $siteurl . '/wp-content/plugins/' . basename(dirname(__FILE__)) . '/css/wp-nagbar.css';
	echo "<link rel='stylesheet' type='text/css' href='$url' />\n";
}
add_action('wp_head', 'WPNagbar_register_head');
?>