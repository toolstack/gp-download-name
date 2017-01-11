<?php
/*
Plugin Name: GP Download Name
Plugin URI: http://glot-o-matic.com/gp-download-name
Description: Use a customizable template for the download file name.
Version: 0.5
Author: Greg Ross
Author URI: http://toolstack.com
Tags: glotpress, glotpress plugin 
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

class GP_Download_Name {
	public $id = 'download-name';
	
	public function __construct() {

		// Add the the filter action to GP.
		add_action( 'gp_export_translations_filename', array( $this, 'gp_export_translations_filename' ), 10, 5 );

		// Add the admin page to the WordPress settings menu.
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 10, 1 );
	}

	// This function adds the admin settings page to WordPress.
	public function admin_menu() {
		add_options_page( __('GP Donwload Name'), __('GP Download Name'), 'manage_options', basename( __FILE__ ), array( $this, 'admin_page' ) );
	}

	// This function displays the admin settings page in WordPress.
	public function admin_page() {
		// If the current user can't manage options, display a message and return immediately.
		if( ! current_user_can( 'manage_options' ) ) { _e('You do not have permissions to this page!'); return; }

		$message = '';
		
		if( array_key_exists( 'gp-download-name', $_POST ) ) {
			$template = trim( stripslashes( $_POST['gp-download-name'] ) );
			
			if( strlen( $template ) > 0 ) {
				update_option( 'gp-download-name', trim( $_POST['gp-download-name'] ) );
				$message = '<div class="notice notice-success is-dismissible"><p>' . __('Settings saved.' ) . '</p></div>';
			} else {
				$message = '<div class="notice notice-error is-dismissible"><p>' . __('Blank templates are not allowed!.' ) . '</p></div>';
			}
		}

	$template = get_option( 'gp-download-name' );
	
	if( strlen( $template ) == 0 ) {
		$template = '%project-name%-%language-code%';
		update_option( 'gp-download-name', $template );
	}
	
	?>	
<div class="wrap">
	<?php echo $message; ?>

	<h2><?php _e('GP Download Name Settings');?></h2>

	<br />
	
	<form method="post" action="options-general.php?page=gp-download-name.php" >	
		<?php _e( 'Tempate:' ); ?> <input type="text" name="gp-download-name" id="gp-download-name" size="45" value="<?php echo esc_attr( $template );?>"></input>
		<?php echo get_submit_button( __('Save'), 'primary', 'save', false ); ?>
	</form>
	
	<p><?php _e( 'The following tags are available to use as part of the template:' ); ?></p>
	
	<ul>
		<li><?php _e( '%project-name% - The project name (may include spaces and mixed case)' ); ?></li>
		<li><?php _e( '%project-name-underscores% - The project name, spaces replaced with underscores (mixed case)' ); ?></li>
		<li><?php _e( '%project-name-dashes% - The project name, spaces replaced with dashes (mixed case)' ); ?></li>
		<li><?php _e( '%project-slug% - The project slug (no spaces, all lower case)' ); ?></li>
		<li><?php _e( '%language-code% - The language code for the current request (ie "en" for english or "fr_CA" for French Canadian)' ); ?></li>
	</ul>

	<p><?php _e( 'Note: The output file extension is automatically added, do not include a trailing period or other file extension.' ); ?></p>

</div>
<?php		
	}

	public function gp_export_translations_filename( $filename, $format, $locale, $project, $translation_set ) {

		$template = get_option( 'gp-download-name' );
		
		if( strlen( $template ) == 0 ) {
			$template = '%project-name%-%language-code%';
			update_option( 'gp-download-name', $template );
		}
		
		$fudge = new GP_Download_Name_Format;

		$replacements = array( 
								'project-name' => $project->name,
								'project-name-underscores' => str_replace( ' ', '_', $project->name ),
								'project-name-dashes' => str_replace( ' ', '-', $project->name ),
								'project-slug' => $project->slug,
								'language-code' => $fudge->get_language_code_string( $locale ),
							);

		foreach( $replacements as $key => $rep ) {
			$template = str_replace( '%' . $key . '%', $rep, $template );
		}

		$filename = $template . '.' . $format->extension;
		
		return $filename;
	}

}

class GP_Download_Name_Format extends GP_Format {
	public function get_language_code_string( $locale ) {
		return $this->get_language_code( $locale );
	}
	
	/*
	 * Fudge the next two functions as they're required to extend the GP_Format class but we won't use them.
	 */
	public function print_exported_file( $project, $locale, $translation_set, $entries ) {
	}
	
	public function read_originals_from_file( $file_name ) {
	}
}

// Add an action to WordPress's init hook to setup the plugin.  Don't just setup the plugin here as the GlotPress plugin may not have loaded yet.
add_action( 'gp_init', 'gp_download_name_init' );

// This function creates the plugin.
function gp_download_name_init() {
	GLOBAL $gp_download_name;
	
	$gp_download_name = new GP_Download_Name;
}