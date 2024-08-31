<?php
/*
Plugin Name: Conatct No With Country Code Field In Contact 7 Form
Description: Add phone number with country code & country flag field in contact form 7.
Version: 1.0.0
Author: Softnoesis Pvt. Ltd.
Author URI: http://www.softnoesis.com
License: GPL3
License URI: http://www.gnu.org/licenses/gpl.html
Text Domain: sn-pcf
*/

class SN_CPF_Plugin{
	public function __construct(){
		add_action( 'plugins_loaded', array( $this, 'sn_load_plugin_textdomain' ) );
		if(class_exists('WPCF7')){
			$this->sn_plugin_constants();
			require_once SN_CPF_PATH . 'includes/autoload.php';
			add_action( 'admin_notices', array( $this, 'sn_affiliate_admin_notice' ) );
			add_action( 'admin_init', array( $this,  'sn_affiliate_notice_dismissed') );
		} else {
			add_action( 'admin_notices', array( $this, 'sn_admin_error_notice' ) );
		}
		
	}
	
	public function sn_load_plugin_textdomain() {
		load_plugin_textdomain( 'sn-pcf', false, basename( dirname( __FILE__ ) ) . '/languages/' );
	}
	
	/*
		register admin notice if contact form 7 is not active.
	*/
	public function sn_admin_error_notice(){
		$message = sprintf( esc_html__( 'Phone Number With Country Code In Contact 7 Form%2$s plugin requires %1$sContact form 7%2$s plugin active to run properly. Please install %1$scontact form 7%2$s and activate', 'sn-pcf' ),'<strong>', '</strong>');

		printf( '<div class="notice notice-error"><p>%1$s</p></div>', wp_kses_post( $message ) );
	}
	
	/*
		set plugin constants
	*/
	public function sn_plugin_constants(){
		
		if ( ! defined( 'SN_CPF_PATH' ) ) {
			define( 'SN_CPF_PATH', plugin_dir_path( __FILE__ ) );
		}
		if ( ! defined( 'SN_CPF_URL' ) ) {
			define( 'SN_CPF_URL', plugin_dir_url( __FILE__ ) );
		}
		
	}

	//Display admin notices 
	public function sn_affiliate_admin_notice() {
		//get the current screen
		$screen = get_current_screen();
		$user_id = get_current_user_id();
    
		
		//return if not plugin settings page 
		//To get the exact your screen ID just do var_dump($screen)
		if ( $screen->id === 'contact_page_cpf-settings' && !get_user_meta( $user_id, 'sn_affiliate_notice_dismissed' )){
		?>
		<div class="notice notice-success" style="position:relative;">
			<div class="text" style="clear:both; display:flex; flex-direction: row; justify-content: space-around;">
				<div class="img-box" style=" padding:10px;"><a href="https://www.softnoesis.com/" style="text-decoration:none;" target="_blank"><img src="https://www.softnoesis.com/images/logo.png" alt="seobuddy"/></a></div>
			</div>
			
			<a class="notice-dismiss" href="?page=cpf-settings&sn-affiliate-dismissed"></a>
		</div>
	<?php
		} else{
			return;
		}
	}

	public function sn_affiliate_notice_dismissed() {
		$user_id = get_current_user_id();
		if ( isset( $_GET['sn-affiliate-dismissed'] ) )
			add_user_meta( $user_id, 'sn_affiliate_notice_dismissed', 'true', true );
	}
}

// Instantiate the plugin class.
$sn_cpf_plugin = new SN_CPF_Plugin();
