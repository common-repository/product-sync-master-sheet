<?php
namespace PSSG_Sync_Sheet\App\Core;

use PSSG_Sync_Sheet\App\Core\Base;

class Admin_Base extends Base
{

    public $main_slug = 'pssg-sync';

    public $admin_dir = PSSG_BASE_DIR . 'admin/';
    public $base_file = PSSG_BASE_FILE;

    //Now it's on Setting wizard or main menu part of database.
    public $setting_key = 'pssg_settings';
    public $settings;

    //It's actually config key for Setting page. 
    //asole age theke user chilo bole ager nam ta change kora hoyoni.
    public $config_key = 'pssg_config';
    public $configs;

    public $submit_errors_key = '_submit_errors';

    public $is_premium = false;

    public function __construct()
    {
       $this->is_premium = class_exists('PSSGP_Init');
       $this->settings = get_option( $this->setting_key, [] );
       $this->configs = get_option( $this->config_key, [] );
       $this->submit_errors_key = $this->plugin_prefix . $this->submit_errors_key;
    }

    public function get_form_submited_errors(){
        if( empty( $this->settings ) ) return ['setup_error' => __( 'Configuration Setup is not started.', 'product-sync-master-sheet' )]; 
        return get_option( $this->submit_errors_key, [] );
    }

    /**
     * Update Error when submit message 
     * on main form actually.
     * used at steps/form-submit.php file
     *
     * @author Saiful <codersaiful@gmail.com>
     * @param array $errors
     * @return void
     */
    public function update_submit_errors( $errors = [] ){
        update_option( $this->submit_errors_key, $errors );
    }
}