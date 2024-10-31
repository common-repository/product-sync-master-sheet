<?php
namespace PSSG_Sync_Sheet\App\Handle;

use PSSG_Sync_Sheet\App\Http\Api;
use PSSG_Sync_Sheet\App\Http\Sheet;
use PSSG_Sync_Sheet\App\Service\Products;
use PSSG_Sync_Sheet\App\Service\Standalone;
use PSSG_Sync_Sheet\App\Handle\Request_Handle_Trait;

class Api_Request_Handle
{
    use Standalone;
    use Request_Handle_Trait;



    public $product_id;

    public $requestedParams;
    public $requestedParamsSingle;
    public $requestedParamsMultiples;
    public $columns;
    public $columns_label;
    public $columns_param_label;
    public $columns_param_label_flip;

    public $Products;
    public $Sheet;

    public $requested_data = [];
    public $columns_info = [];

    public function run()
    {
        add_action( 'pssg_api_request_handle', [$this, 'handle_request'], 10 );
        add_action( 'pssg_api_multiple_request_handle', [$this, 'multiple_handle_request'], 10 );
    }

    /**
     * API Object ( PSSG_Sync_Sheet\App\Http\Api )
     * over there  available, root object. 
     * 
     * public $product_updated_stats = false;
     * public $Products;
     * public $Sheet;
     * public $request; \WP_REST_Request $request Request will come using \WP_REST_Request class
     * ## some example of $request ##
     * * $this->request->get_param('product_id')
     * * $this->request->get_param('title')
     * 
     * public $requestedParams;
     * public $api_response = [];
     * 
     * public $http_response_code = 200;
     * public $columns; by $Products->get_columns()
     * public $columns_label; like ['sku'=> 'SKU', 'title'=> 'Product Title']
     * public $columns_param_label; like ['sku'=> 'SKU', 'title'=> 'Product_Title']
     * public $columns_param_label_flip; just flip of like ['sku'=> 'SKU', 'title'=> 'Product Title']
     *
     * @param object $api
     * @return void
     */
    public function handle_request( $API )
    {

        $this->Sheet = $API->Sheet;
        $this->Products = $API->Products;

        $this->requestedParamsSingle = $API->requestedParams;

        $product_id = $this->requestedParamsSingle['ID'] ?? '';
        if( empty( $product_id ) ){
            $reply = $this->insertNewProduct( $API );
            
        }else{
            //Single product update, so second param should true, because, I will reload, if change anything of a product.
            $reply = $this->singleProductUpdate( $API, true );

        }

        

        
        $API->api_response = [
            'reply'   => $reply,
            'api'   => $API->requestedParams,
            'columns_label'  => $this->columns_label,
            'columns_param_label'  => $this->columns_param_label,
            'columns_label_flip'  => $this->columns_param_label_flip,
            'columns' => $this->columns,
            'prev_args' => $API->api_response,
            
            'columns'   => $API->Products->get_columns(),
        ];

    }


    public function multiple_handle_request(  $API )
    {
        
        $this->Sheet = $API->Sheet;
        $this->Products = $API->Products;
        $this->requestedParams = $API->requestedParams;

        

        if( ! is_array( $this->requestedParams ) ){
            $API->http_response_code = 301;
            $API->api_response = [
                'message' => 'failed',
            ];
            return false;
        }

        $replies = [];
        $product_ids = [];
        foreach( $this->requestedParams as $id => $reqParams ){
            if( ! is_array( $reqParams ) ) continue;
            $this->requestedParamsSingle = $reqParams;
            $product_ids[] = $id;
            $replies[$id] = $this->singleProductUpdate( $API );
        }

        $response = $this->Sheet->multiple_products_update_online_sheet( $product_ids );
        

        $API->api_response = [
            'response' => $response,
            'hello'   => $replies,
            'api'   => $this->requestedParams,
            // 'columns_label'  => $this->columns_label,
            // 'columns_param_label'  => $this->columns_param_label,
            // 'columns_label_flip'  => $this->columns_param_label_flip,
            // 'columns' => $this->columns,
            // 'prev_args' => $API->api_response,
            
            // 'columns'   => $API->Products->get_columns(),
        ];

        //As I didn't reload sheet, I will not use this $this->Sheet->update_sheet_multiple_product([]) actually
        // $test = $this->Products->get_sheet_range_by_ids( $product_ids );
        // $this->Sheet->update_sheet_multiple_product( $product_ids );
    }

    /**
     * First set compolsury property for this method actually. Otherwise, it will not work properly
     * 
     * ***********************
     * Compolsury Property
     * ***********************
     * $this->requestedParamsSingle = $API->requestedParams;
     *
     * @param object $API $API original or root Object, which is come from API class. Otherwise it will not work.
     * @return array response of different type response.
     */
    protected function singleProductUpdate( $API, $updateSheet = false )
    {

        if( empty( $this->requestedParamsSingle ) ) return ['status' => 'failed'];
        $this->columns = $API->columns;
        $this->columns_label = $API->columns_label;
        $this->columns_param_label = $API->columns_param_label;
        $this->columns_param_label_flip = $API->columns_param_label_flip;

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
                $API->Sheet->product_to_sheet( $this->product_id );
            }
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
            $response['message'] = __( 'Product Updated Successfully', 'product-sync-master-sheet' );
            $response['cf_update'] = $cf_update;
            $response['stock'] = $stock;
        }
        return $response;
    }


    /**
     * Compolsury:
     * $this->requestedParamsSingle() 
     *
     * @param object $API
     * @return response
     */
    public function insertNewProduct( $API ){

        $this->columns = $API->columns;
        $this->columns_label = $API->columns_label;
        $this->columns_param_label = $API->columns_param_label;
        $this->columns_param_label_flip = $API->columns_param_label_flip;

        $post_args = [];
        $title_param_key = $this->columns_param_label['title'] ?? 'Product_Title';
        $post_args['post_title'] = $this->requestedParamsSingle[$title_param_key] ?? __( 'New Dummy Product', 'product-sync-master-sheet' );
        $post_args['post_type'] = 'product';
        $post_args['post_status'] = 'publish';
        


        $this->product_id = wp_insert_post( $post_args );
        if( ! empty( $this->product_id ) ){
            $post_args['product_id'] = $this->product_id;// Only for tetting status, not for insert post
            $post_args['status'] = __('Success', 'product-sync-master-sheet');// Only for tetting status, not for insert post
            update_post_meta( $this->product_id, '_product_type', 'simple' ); //Important to make
            

            $post_args['stock'] = $this->single_param_request_wise_stock_update();

            $post_args['cf_update'] = $this->request_wise_cf_update();
            $API->Sheet->product_to_sheet( $this->product_id );
        }else{
            $post_args['status'] = __('Failed to create new product.', 'product-sync-master-sheet');
        }
        return $post_args;
        
    }


    
}