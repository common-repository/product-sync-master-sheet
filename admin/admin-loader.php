<?php
namespace PSSG_Sync_Sheet\Admin;

use PSSG_Sync_Sheet\App\Core\Admin_Base;
use PSSG_Sync_Sheet\App\Core\Base;
use PSSG_Sync_Sheet\App\Handle\Quick_Table;
use PSSG_Sync_Sheet\App\Http\Api as API_Request;
use PSSG_Sync_Sheet\App\Handle\Api_Request_Handle as API_Handle;
use PSSG_Sync_Sheet\App\Http\Sheet;
use PSSG_Sync_Sheet\Admin\Page_Loader;
class Admin_Loader extends Admin_Base
{
    public function __construct()
    {
        
    }

    public function init()
    {
        $sheet = new Sheet();
        $sheet->run();

        API_Request::init();

        //Need run() method, otherwise, it will not work
        API_Handle::init()->run();

        $page_loader = new Page_Loader();
        $page_loader->run();


        //Plugin Page menu handle
        add_filter('plugin_action_links_' . $this->base_file, [$this,'plugin_action_links']);

        //pssg_quick_table_update
        add_action('wp_ajax_pssg_quick_table_update', [$this,'quick_table_update']);
        add_action('wp_ajax_noprev_pssg_quick_table_update', [$this,'quick_table_update']);
    }

    public function plugin_action_links( $links )
    {
        $my_links = [];
        $setting_link = admin_url( 'admin.php?page=' . $this->main_slug );
        $my_links[] =  '<a href="' . esc_url( $setting_link ) . '" title="' . esc_attr__( 'Settings', 'product-sync-master-sheet' ) . '" target="_blank">' . esc_html__( 'Settings','product-sync-master-sheet' ).'</a>';
        return array_merge( $my_links, $links );
    }

    public function quick_table_update()
    {
        $nonce = sanitize_text_field( wp_unslash( $_POST['nonce'] ?? '' ) );
        if( empty( $nonce ) || ! wp_verify_nonce( $nonce, plugin_basename( PSSG_BASE_FILE ) ) ) {
            echo wp_json_encode(['status' => 'failed','message' => '', 'error' => __( 'Nonce not founded', 'product-sync-master-sheet' )]);
            wp_die();
        }

        $params = [];

        if( is_array( $_POST['params'] ) ){
            foreach( $_POST['params'] as $param_key => $param_value ){
                $params[ $param_key ] = sanitize_text_field( $param_value );
            }
        }

        if( empty( $params ) ){ //Fully sanitized(all key and value) above inside foreach using sanitize_text_field
            echo wp_json_encode(['status' => 'failed','message' => '', 'error' => __( 'Params empty or somethig went wrong', 'product-sync-master-sheet' )]);
            wp_die();
        }

        
        $quickTable = Quick_Table::init();
        $response = $quickTable->single_product_update( $params );

        echo wp_json_encode( $response );
        wp_die();
    }
}