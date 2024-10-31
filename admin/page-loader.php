<?php
namespace PSSG_Sync_Sheet\Admin;

use PSSG_Sync_Sheet\App\Core\Base;
use PSSG_Sync_Sheet\App\Core\Admin_Base;
use PSSG_Sync_Sheet\App\Service\Products;
use PSSG_Sync_Sheet\App\Http\Sheet;
use PSSG_Sync_Sheet\App\Http\Api;
use PSSG_Sync_Sheet\App\Handle\Form_Submit;
use PSSG_Sync_Sheet\App\Handle\Quick_Table;
use PSSG_Sync_Sheet\App\Handle\Setup_Wizard;
use PSSG_Sync_Sheet\App\Handle\Inactive_Element;
use PSSG_Sync_Sheet\App\Service\Standalone;

class Page_Loader extends Admin_Base
{

    use Standalone;
    //Has transferred to Admin_Base
    // public $main_slug = 'pssg-sync';
    public $page_folder_dir;
    public $topbar_file;
    public $topbar_sub_title;


    public $Products;
    public $Sheet;
    public $Api;
    public $Quick_Table;

    public $all_enequeue_loaded = false;

    public function __construct()
    {
        parent::__construct();

        
        $this->Products = Products::init();
        $this->Sheet = Sheet::init();
        $this->Api = Api::init();

        $this->page_folder_dir = $this->base_dir . 'admin/pages/';
        $this->topbar_file = $this->page_folder_dir . 'topbar.php';
        $this->topbar_sub_title = __("Next door to Google Sheets", 'product-sync-master-sheet');
    }

    public function run()
    {

        add_action('admin_menu', [$this,'admin_menu']);
        add_action('admin_notices', [$this, 'premium_version_update_notice']);

        add_action( 'admin_enqueue_scripts', [$this, 'admin_enqueue_scripts'] );

        add_action('wp_ajax_pssg_setting_wizard_submit', [$this, 'form_submit']);
        add_action('wp_ajax_pssg_configure_form_submit', [$this, 'form_submit']);
        add_action('wp_ajax_handle_json_file_upload', [$this, 'handle_json_file_upload']);

        if(!$this->is_premium){
            Inactive_Element::init();
        }
    }

    public function admin_menu()
    {

        $capability = apply_filters( 'pssg_menu_capability', 'manage_woocommerce' );

        $min_max_img = $this->base_url . 'assets/images/logo.png';
        $page_title = __( 'Sync Master Sheet', 'product-sync-master-sheet' );
        $menu_title = __( 'Sync Master Sheet', 'product-sync-master-sheet' ); 
        $menu_slug = $this->main_slug;
        $callback = [$this,'main_page']; 
        $icon_url = 'dashicons-media-spreadsheet';
        $position = 55.5;


        $sw_page_title = __( 'Setting - Sync Master Sheet', 'product-sync-master-sheet' );
        $sw_menu_title = __( 'Settings', 'product-sync-master-sheet' ); 

        $qt_page_title = __( 'Product Quick Edit', 'product-sync-master-sheet' );
        $qt_menu_title = __( 'Product Quick Edit', 'product-sync-master-sheet' ); 

        //Adding premium word at the title actually
        if( $this->is_premium ){
            $page_title .=  __( ' Premium', 'product-sync-master-sheet' );
            $qt_page_title .=  __( ' Premium', 'product-sync-master-sheet' );
            $sw_page_title .=  __( ' Premium', 'product-sync-master-sheet' );
        }

        add_menu_page($page_title, $menu_title, $capability, $menu_slug, $callback, $icon_url, $position);
        add_submenu_page($menu_slug, $sw_page_title, $sw_menu_title, $capability, 'pssg-setup-wizard', [$this,'config_settings_page'] );
        add_submenu_page($menu_slug, $qt_page_title, $qt_menu_title, $capability, 'pssg-quick-edit', [$this,'quick_edit_page'] );

        if( ! $this->is_premium ){
            $get_pro_title = __( 'Premium', 'product-sync-master-sheet' );
            add_submenu_page($menu_slug, $get_pro_title, $get_pro_title, 'read', 'https://codeastrology.com/downloads/product-sync-master-sheet-premium/' );
        }
    }

    public function main_page()
    {
        $this->topbar_sub_title = __("Installation Wizard", 'product-sync-master-sheet');
        $main_page_file = $this->page_folder_dir . 'setup-wizard.php';
        if( ! is_file( $main_page_file ) ) return;
        include $main_page_file;
    }

    public function config_settings_page()
    {

        $this->topbar_sub_title = __("Settings", 'product-sync-master-sheet');
        // $setting_page = $this->page_folder_dir . 'setup-wizard.php';
        $setting_page =  $this->page_folder_dir . 'settings.php';;
        if( ! is_file( $setting_page ) ) return;
        include $setting_page;
    }

    public function quick_edit_page()
    {
        $this->topbar_sub_title = __("Quick Edits", 'product-sync-master-sheet');
        $this->Quick_Table = Quick_Table::init();
        $quick_edit = $this->page_folder_dir . 'quick-edit.php';
        if( ! is_file( $quick_edit ) ) return;
        include $quick_edit;
    }
    
    public function premium_version_update_notice()
    {
        
        if( ! defined('PSSGP_DEV_VERSION') ) return;
        if( ! defined('PSSGP_PLUGIN_NAME') ) return;
        if( ! $this->is_premium ) return;
        global $current_screen;
        $s_id = isset( $current_screen->id ) ? $current_screen->id : '';
        if( strpos( $s_id, $this->plugin_prefix ) === false ) return;
        $min_version = '1.0.4.0';
        if ( version_compare( PSSGP_DEV_VERSION, $min_version, '<' ) ){ ?>
            <div class="notice notice-error">
                <p>Your <b><?php echo PSSGP_PLUGIN_NAME; ?></b> version is old. Please update it.</p>
            </div>
            <?php
        }
        
    }

    public function admin_enqueue_scripts()
    {
        global $current_screen;


        wp_register_style( $this->plugin_prefix . '-common', $this->base_url . 'assets/css/sync-master-common.css', false, $this->dev_version );
        wp_enqueue_style( $this->plugin_prefix . '-common' );

        $s_id = isset( $current_screen->id ) ? $current_screen->id : '';
        if( strpos( $s_id, $this->plugin_prefix ) === false ) return;
        add_filter('admin_footer_text',[$this, 'admin_footer_text']);
        //For media upload
        wp_enqueue_media();

        

       $this->all_enequeue_load();

    }
    public function all_enequeue_load()
    {
        if( $this->all_enequeue_loaded ) return;

        //jquery
        wp_enqueue_script('jquery');

        $backend_js_name = $this->plugin_prefix . '-admin';
        wp_register_script( $backend_js_name, $this->base_url . 'assets/js/backend.js', false, $this->dev_version );
        wp_enqueue_script( $backend_js_name );

       $ajax_url = admin_url( 'admin-ajax.php' );
       $PSSG_DATA = array( 
           'ajaxurl'        => $ajax_url,
           'ajax_url'       => $ajax_url,
           'site_url'       => site_url(),
           'plugin_url'     => plugins_url(),
           'content_url'    => content_url(),
           'include_url'    => includes_url(),
           'text' => [
                'create_sheet'                  => __( 'Create Sheet','product-sync-master-sheet' ),
                'checkout_sheet'                => __( 'Your Sheet','product-sync-master-sheet' ),
                'invalid_file_type'             => __( 'Invalid file type. Please select a JSON file.','product-sync-master-sheet' ),
                'all_syncronized_msg'           => __( 'All Syncronized','product-sync-master-sheet' ),
                'sync_done_msg'                 => __( 'Sync Done','product-sync-master-sheet' ),
                'syncing_msg'                   => __( 'Syncronizing...','product-sync-master-sheet' ),
                'paused'                   => __( 'Paused','product-sync-master-sheet' ),
                'pause'                   => __( 'Pause','product-sync-master-sheet' ),
                'sync_limit_crossed'            => __( 'Done and Limit Crossed!','product-sync-master-sheet' ),
                'check_error_msg'               => __( 'Check following Error','product-sync-master-sheet' ),
                'response_error_code'           => __( 'Error Code: ','product-sync-master-sheet' ),
                'check_connection_msg'          => __( 'Please check connection again!','product-sync-master-sheet' ),
                'sync_success_msg'              => __( 'Wow Sync: total currentCount products','product-sync-master-sheet' ),
                'delete_sheet'                  => __( "Are you sure!\nWant to Destroy Sheet data.\n OK or Cancel.",'product-sync-master-sheet' ),
                'sheet_clear'                   => __( 'Sheet Cleared!','product-sync-master-sheet' ),
                'sheet_clear_msg'               => __( 'Sheet has Cleared!','product-sync-master-sheet' ),
                'deleting_msg'                  => __( 'Deleteing..','product-sync-master-sheet' ),
                'saving_msg'                    => __( 'Saving..','product-sync-master-sheet' ),
                'json_file_delating'            => __( "Are you sure!\nDelete Service JSON file.\n OK or Cancel.",'product-sync-master-sheet' ),
                'file_selecting_msg'            => __( 'File selected: ','product-sync-master-sheet' ),
                'email_copied_msg'              => __( "Copied, Add this email as your Sheet's Editor",'product-sync-master-sheet' ),
                'script_copied_msg'             => __( 'Script Copied! - Now add to your Sheet.','product-sync-master-sheet' ),
                'error_in_copy'                 => __( 'Unable to copy to clipboard','product-sync-master-sheet' ),
                'show_all'                 => __( 'Show All','product-sync-master-sheet' ),
            ],
            'nonce' => wp_create_nonce( plugin_basename( PSSG_BASE_FILE ) ),
            'dev_version' => $this->dev_version,
            'plugin_version' => $this->plugin_version,  
            'sync_btn_interval' => 2500, //in milliseconds - will change in premeium version

           );
       $PSSG_DATA = apply_filters( 'pssg_localize_data', $PSSG_DATA );
       wp_localize_script( $backend_js_name, 'PSSG_DATA', $PSSG_DATA );

        wp_register_style( $this->plugin_prefix . '-icon-font', $this->base_url . 'assets/fontello/css/pssg-icon.css', false, $this->dev_version );
        wp_enqueue_style( $this->plugin_prefix . '-icon-font' );

        
        wp_register_style( $this->plugin_prefix . '-icon-animation', $this->base_url . 'assets/fontello/css/animation.css', false, $this->dev_version );
        wp_enqueue_style( $this->plugin_prefix . '-icon-animation' );




        wp_register_style( $this->plugin_prefix . '-admin', $this->base_url . 'assets/css/dashboard.css', false, $this->dev_version );
        wp_enqueue_style( $this->plugin_prefix . '-admin' );

        //For all type css editing, which is not related with dashboard Design
        //And also this is not common which need to whole wp
        wp_register_style( $this->plugin_prefix . '-backend', $this->base_url . 'assets/css/backend.css', false, $this->dev_version );
        wp_enqueue_style( $this->plugin_prefix . '-backend' );

        wp_register_style( $this->plugin_prefix . '-quick-table', $this->base_url . 'assets/css/quick-edit-table.css', false, $this->dev_version );
        wp_enqueue_style( $this->plugin_prefix . '-quick-table' );

        $this->all_enequeue_loaded = true;
    }

    public function form_submit()
    {
        $form = Form_Submit::init();
        $form->run();
    }

    public function handle_json_file_upload() {

        $nonce = sanitize_text_field( wp_unslash( $_POST['nonce'] ?? '' ) );
        if( empty( $nonce ) || ! wp_verify_nonce( $nonce, plugin_basename( PSSG_BASE_FILE ) ) ) {
            wp_send_json(['status' => 'failed','message' => '', 'error' => __( 'Nonce not founded', 'product-sync-master-sheet' )]);
            wp_die();
        }

        $this->sheet = new Sheet();
        $service_json_key = $this->sheet->option_key; //pssg_service_json_data
        $akses_tokn_key = $this->sheet->tokn_key; //pssg_eta_holo_akses_tokn
        $response = $json_data = [];
        $response['client_email'] = '';
        if (isset($_FILES['json_file'])) {
            $file = $_FILES['json_file'];
            
            //working for save to database
            $temp_file = $file['tmp_name'] ?? '';
            if(! empty( $temp_file )){
                $service_fileArray = [];
                $json_content = pssg_file_get_content( $temp_file );
                if( json_decode( $json_content, true ) ){
                    $service_fileArray = json_decode( $json_content, true );
                    if(! empty( $service_fileArray['type'] ) && $service_fileArray['type'] == 'service_account' && ! empty( $service_fileArray['private_key'] )  && ! empty( $service_fileArray['client_email'] ) ){
                        $response['message'][] = __( 'Service file is OK', 'product-sync-master-sheet' );
                        $response['status'] = 'success';
    
                        $json_data = $service_fileArray;
                    }else{
                        $response['errors'][] = __( 'Service file content is not right', 'product-sync-master-sheet' );
                        $response['status'] = 'failed';
                    }
                }else{
                    $response['errors'][] = __( 'File content is not json!', 'product-sync-master-sheet' );
                    $response['status'] = 'failed';
                }
    
            }
    
            if( ! empty( $response['status'] ) && $response['status'] == 'success'){
                // Handle the file upload (move it to the desired directory, etc.)
                $upload_dir = wp_upload_dir(); // You can change this to your desired directory
                $upload_path = $upload_dir['path'] . '/' . time() . '-' . $file['name'];
                $response['json_fil_path'] = $upload_path;
                $json_data['file_name'] = $file['name'];
                $json_data['upload_path'] = $upload_path;
                $response['client_email'] = $json_data['client_email'];
                $response['message'][] = 'File has upload to server';
                update_option( $service_json_key, $json_data );
                
            }else{
                // $response['errors'][] = 'Old Data has been removed';
                $response['errors'][] = __( 'Wrong file is not uploaded to server', 'product-sync-master-sheet' );

            }
    
            
    
        }elseif( isset($_POST['delete']) && sanitize_key( $_POST['delete'] ) == 'json' ){
            $response['client_email'] = $response['json_fil_path'] = '';
            $response['status'] = 'success';
            $response['message'] = __( "Service json file has removed.", 'product-sync-master-sheet');
            update_option( $service_json_key, [] );
            $errors['upload_path'] = __( 'Service json file not founded.', 'product-sync-master-sheet' );
            
        }else{
            $response['errors'][] = __( 'Sevcie file is not founded.', 'product-sync-master-sheet' );
            $response['status'] = 'failed';
        }
        set_transient( $akses_tokn_key, null, 0 );//Akses tokn asole remove hobe jodi file upload hoy.
        wp_send_json( $response );
        wp_die(); // Always use wp_die() to end AJAX requests in WordPress
    }
    public function admin_footer_text($text)
    {
        $rev_link = 'https://www.trustpilot.com/review/codeastrology.com';
        $text = sprintf(
			__( 'Thank you for using our Plugin <a href="%s" target="_blank">%sPlease review us</a>.', 'product-sync-master-sheet' ),
			$rev_link,
            '<i class="pssg-star-filled"></i><i class="pssg-star-filled"></i><i class="pssg-star-filled"></i><i class="pssg-star-filled"></i><i class="pssg-star-filled"></i>'
		);
        return '<span id="footer-thankyou" class="pssg-footer-thankyou">' . $text . '</span>';
    }
}