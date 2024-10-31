<?php
namespace PSSG_Sync_Sheet\App\Handle;

use PSSG_Sync_Sheet\Admin\Page_Loader;
use PSSG_Sync_Sheet\App\Http\Api;
use PSSG_Sync_Sheet\App\Core\Admin_Base;
use PSSG_Sync_Sheet\App\Http\Sheet;
use PSSG_Sync_Sheet\App\Service\Products;
use PSSG_Sync_Sheet\App\Handle\Api_Request_Handle;
use PSSG_Sync_Sheet\App\Handle\Request_Handle_Trait;
use PSSG_Sync_Sheet\App\Service\Standalone;

class Quick_Table extends Admin_Base 
{

    use Standalone;
    use Request_Handle_Trait;

    public $paged = 1;
    public $nonce;
    public $post_count;
    public $max_num_pages;
    public $found_posts;
    public $one_load_limit;

    public $product_id;

    public $requestedParams;
    public $requestedParamsSingle;
    public $requestedParamsMultiples;
    public $columns;
    public $columns_label;
    public $columns_param_label;
    public $columns_param_label_flip;

    public $columns_key_position;

    public $Products;
    public $Sheet;
    public $Admin_Base;
    public $Admin_Base_Settings;

    public $requested_data = [];
    public $columns_info = [];

    public function __construct()
    {
        parent::__construct();
        $this->Products = Products::init();
        $this->Sheet = Sheet::init();
        $this->Admin_Base = $this->Sheet->Admin_Base;
        $this->Admin_Base_Settings = $this->Admin_Base->configs ?? [];
        
        $this->columns = $this->Products->get_columns();
        $this->columns_label = $this->Products->get_columns_label();
        $this->columns_param_label = array_map( function($val){
            return str_replace(" ", "_", $val);
        }, $this->columns_label );
        $this->columns_param_label_flip = array_flip( $this->columns_param_label );

        //Position
        $this->columns_key_position = array_flip( array_keys( $this->columns_label ) );

        $this->one_load_limit = $this->Products->get_one_load_limit();
        $this->post_count = $this->Products->post_count;
        $this->found_posts = $this->Products->found_posts;
        $this->max_num_pages = $this->Products->max_num_pages;

        /**
         * Specially for load from other place
         * If load table from any other plugin,
         * so that all style can work
         * that's why, we added this method again here.
         * 
         * @since 1.0.0.27
         */
        $this->enqueue_style();

        $this->nonce = wp_create_nonce( plugin_basename( PSSG_BASE_FILE ) );
        
    }

    public function display_table_full( $query_args = [] )
    {
    ?>
    <div class="pssg-section-panel no-background quick-edit-section-wrapper">
        <div class="pssg-section-panel quick-edit-section" id="quick-edit-section" data-icon="pssg_icon-home">
        <?php $this->display_table( $query_args ); ?>
        </div>
    </div>
    <?php
    }
    
    public function display_table( $query_args = [] )
    {

        if( empty( $this->nonce ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $this->nonce ) ), plugin_basename( PSSG_BASE_FILE ) ) ) {
            return;
        }
        //Hidden Hook: pssg_posts_per_page_quick_table
        $posts_per_page = absint( $_GET['posts_per_page'] ?? apply_filters( 'pssg_posts_per_page_quick_table', $this->Products->posts_per_page ) ); //Sanitized at bellow

        
        $paged = absint( $_GET['paged'] ?? 1 );
        $this->paged = $paged;
        
        

        $current_query_aqrgs = ['posts_per_page' => $posts_per_page, 'paged' => $paged];
        if( isset( $_GET['s'] ) && ! empty( $_GET['s'] ) && is_string( $_GET['s'] ) ){
            $current_query_aqrgs['s'] = $_GET['s'];
        }
        $query_args = array_merge( $current_query_aqrgs, $query_args );

        $query_args['posts_per_page'] = $query_args['posts_per_page'] > $this->one_load_limit ? $this->one_load_limit : $query_args['posts_per_page'];
        $prods = $this->Products->get_sheet_row( $query_args );
        

        // $this->one_load_limit
        $limitMessage = '';
        $temps_pods = [];
        if( $paged > 1 && ( $posts_per_page * $paged ) >  $this->one_load_limit ){
            $limitMessage = 'limit_crossed';
            $temps_pods[0] = $prods[0];
            $prods = $temps_pods;
        }
        

        $table_title = $this->Admin_Base_Settings['edit_table_title'] ?? '';
        ?>
        <h1>
            <?php 
            if( ! empty( $table_title ) ){
                echo wp_kses_data( $table_title );
            }else{
                echo esc_html__( 'Product Quick Edit Table', 'product-sync-master-sheet' );
            }
            
            ?>
        <?php
        $sheet_url = $this->settings['sheet_url'] ?? '';
        pssg_sheet_url_render( $sheet_url );
        ?>
        </h1>
        <?php 
                
        /**
         * Action @hook for Quick Edit Page Top
         */
        do_action( 'pssg_admin_quick_edit_top', $this ); ?>

        <?php $this->display_pagination(); ?>
        <?php $this->show_stats(); ?>
        <div class="pssg-quick-table-wrapper">
            
            <table class="pssg-quick-edit-table" data-nonce="<?php echo esc_attr( wp_create_nonce( plugin_basename( PSSG_BASE_FILE ) ) ); ?>">
            
            <tbody>
        <?php 
        
        $this->table_row_products( $prods );
        
        ?>
            </tbody>
            </table>

            <?php
            if($limitMessage == 'limit_crossed'){
            ?>
            <p class="pssg-error pssg-limit-crossed"><?php echo esc_html__( 'Limit Crossed, Need Premium version.', 'product-sync-master-sheet' ); ?></p>
            <?php
            }
            ?>

        </div> <!-- ./pssg-quick-table-wrapper -->
        <?php $this->display_pagination(); ?>


        <?php 
                
        /**
         * Action @hook for Quick Edit Page Top
         */
        do_action( 'pssg_admin_quick_edit_bottom', $this ); ?>
        <?php
    }

    public function table_row_products( $prods )
    {
        if( empty( $prods ) || ! is_array( $prods ) ) return;

        $lables_keys = array_keys( $this->Products->get_columns_label() );
        $lables_values = array_values( $this->Products->get_columns_label() );
        $lables_values = array_map( function($val){
            return str_replace(" ", "_", $val);
        }, $lables_values );


        foreach($prods as $key => $prod){
            $class = $prod[1] ?? '';

            $stock_position = $this->columns_key_position['stock'] ?? 0;
            $class_stock = $prod[$stock_position] ?? '';
            $class_stock = str_replace(' ', '-', $class_stock);
            ?>
            <tr class="pssg-quick-table-tr type-<?php echo esc_attr( $class ); ?> row-num-<?php echo esc_attr( $key ); ?> stock-<?php echo esc_attr( $class_stock ); ?>">
            <?php
            foreach($prod as $td_key => $td){
                $keyword = $lables_keys[$td_key] ?? '';
                $keyword_label = $lables_values[$td_key] ?? '';
                $editCellBool = 'true';
                $attr_title = '';
                if($td_key < 2 || $key < 1 || ( $class == 'variation' && $keyword == 'title' ) ){
                    $attr_title = __( 'Unable to edit', 'product-sync-master-sheet' );
                    $editCellBool = 'false';
                }
            ?>
            <td 
            data-keyword="<?php echo esc_attr( $keyword ); ?>"  
            data-keyword_label="<?php echo esc_attr( $keyword_label ); ?>"  
            contenteditable="<?php echo esc_attr( $editCellBool ); ?>" 
            title="<?php echo esc_attr( $attr_title ); ?>"
            class='each-cell col-<?php echo esc_attr( $td_key ); ?> cell-<?php echo esc_attr( $lables_keys[$td_key] ?? '' ); ?>'><?php echo esc_html( $td ); ?></td>
            <?php 
            }
            ?> 
            </tr>
            <?php
        }
    }

    public function display_pagination()
    {
        if( empty( $this->Products->max_num_pages ) ) return;
        $paged = $this->paged;
        ?>
        <div class="pssg-pagination-wrapper">
        <?php
        $total_pages = $this->Products->max_num_pages;
        $pagination_args = array(
            'base'      => esc_url_raw(add_query_arg('paged', '%#%')),
            'format'    => '?paged=%#%',
            'current'   => max(1, $paged),
            'total'     => $total_pages,
            'prev_text' => '&laquo; ' . __( 'Previous', 'product-sync-master-sheet' ),
            'next_text' => __( 'Next', 'product-sync-master-sheet' ) . ' &raquo;',
            'prev_next'=> true,
        );
        $paginate_link = paginate_links( $pagination_args );
        // Output the pagination links
        echo wp_kses_post( $paginate_link ?? '' );

        ?>
        </div> <!-- ./pssg-pagination-wrapper -->
        <?php
    }

    public function show_stats()
    {

        $this->Products->post_count;
        $this->Products->found_posts;
        // $this->Products->max_num_pages;
        $stats = __( "Showing %post_count% out of %found_posts%", 'product-sync-master-sheet' );
        $stats_message = str_replace(['%post_count%','%found_posts%'],[$this->Products->post_count,$this->Products->found_posts], $stats);
        ?>
        <div class="pssg-stats">
            <p><?php echo wp_kses_data( $stats_message ); ?></p>
        </div>
        <?php
    }

    public function single_product_update($params = [], $updateSheet = true)
    {
        $this->requestedParamsSingle = $params;
        if( empty( $params ) ) return [ 'status' => 'failed', 'message' => __( 'Params not founed', 'product-sync-master-sheet' ) ];
        if( empty( $params['ID'] ) ) return [ 'status' => 'failed', 'message' => __( 'Product ID not founed', 'product-sync-master-sheet' ) ];
        $post_arr = $cf_update = $stock = [];
        $response = [];
        $product_id = $this->requestedParamsSingle['ID'] ?? '';

        if( is_numeric( $product_id ) && ! empty( $product_id ) ){
            $this->product_id = $product_id;
        }

        if( ! empty( $this->product_id ) && wc_get_product( $this->product_id ) ){
            $post_arr['ID'] = $this->product_id;
            $title_param_key = $this->columns_param_label['title'] ?? 'Product_Title';
            $post_arr['post_title'] = $this->requestedParamsSingle[$title_param_key] ?? '';

            $stock = $this->single_param_request_wise_stock_update();

            $cf_update = $this->request_wise_cf_update();


            $title_update = $updateSheet && ! empty( $post_arr ) ? wp_update_post( $post_arr ) : false;

            if( $updateSheet ){
                //At the End, It will again Update to sheet for sync
                $this->Sheet->product_to_sheet( $this->product_id );
            }
            
            $response['message'] = __( 'Product Updated Successfully', 'product-sync-master-sheet' );
            $response['cf_update'] = $cf_update;
            $response['stock'] = $stock;
            $this->requested_data = [
                'product_id' => $this->product_id,
                'param_value' =>$this->requestedParamsSingle
            ];
            $this->columns_info = [
                'param_label' =>$this->columns_param_label_flip,
                'columns' =>$this->columns
            ];

            /**
             * Product update from Quick Edit
             * and from API Request from Google Sheet.
             * Both are using same action hook
             * @Hook 'pssg_product_update_request' Action Hook
             * update product time action hook
             */
            do_action('pssg_product_update_request', $this->requested_data, $this->columns_info);
        }else{
            $response['status'] = 'failed';
            $response['message'] = __( 'No product ID matched', 'product-sync-master-sheet' );;

        }
        return $response;
    }


    /**
     * Edit Table er jon specially eta
     * jodi kono plugin er madhomeo on kora hoy tao jeno style pay.
     * tai eta
     * 
     * @since 1.0.0.26
     *
     * @return void
     */
    public function enqueue_style()
    {
        Page_Loader::init()->all_enequeue_load();
    }

    
}