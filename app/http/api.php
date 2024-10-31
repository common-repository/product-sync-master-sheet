<?php
namespace PSSG_Sync_Sheet\App\Http;

use PSSG_Sync_Sheet\App\Http\Sheet;
use PSSG_Sync_Sheet\App\Service\Products;
use PSSG_Sync_Sheet\App\Service\Standalone;
/**
 * Product update using 
 * api request
 * 
 */
class Api
{

    use Standalone;
    public $route_namespace = 'pssg_gsheet_sync/v1';
    public $product_route = 'product';
    public $multiple_product_route = 'multiples';
    public $product_id;


    public $product_updated_stats = false;
    public $Products;
    public $Sheet;
    public $request;
    public $requestedParams;
    public $api_response = [];
    public $http_response_code = 200;

    public $columns; //by $Products->get_columns()
    public $columns_label; //like ['sku'=> 'SKU', 'title'=> 'Product Title']
    public $columns_param_label; //like ['sku'=> 'SKU', 'title'=> 'Product_Title']

    /**
     * just flip of like ['sku'=> 'SKU', 'title'=> 'Product_Title']
     * Actually if found any array, we will replace with _ with space
     *
     * @var array
     */
    public $columns_param_label_flip;

    public function __construct()
    {
        $this->Products = Products::init();
        $this->Sheet = Sheet::init();

        $this->columns = $this->Products->get_columns();
        $this->columns_label = $this->Products->get_columns_label();
        $this->columns_param_label = array_map( function($val){
            return str_replace(" ", "_", $val);
        }, $this->columns_label );
        $this->columns_param_label_flip = array_flip( $this->columns_param_label );

        add_action('rest_api_init', [$this,'register_endpoint']);
    }


    public function register_endpoint() {
        register_rest_route( $this->route_namespace, '/' . $this->product_route, array(
            'methods' => 'POST',
            'callback' => [$this,'handle_request'],
            'permission_callback'   => [$this,'permission_callback'],
        ));

        register_rest_route( $this->route_namespace, '/' . $this->multiple_product_route, array(
            'methods' => 'POST',
            'callback' => [$this,'multiple_handle_request'],
            'permission_callback'   => [$this,'permission_callback'],
        ));
    }
    
    /**
     * Function to handle the POST request and update the option.
     * 
     *
     * @test new \WP_REST_Request();
     * @param \WP_REST_Request $request Request will come using \WP_REST_Request class
     * @return \WP_REST_Response
     */
    public function handle_request($request)
    {
        $this->request = $request;
        // Get the option name and value from the request.
        $this->requestedParams = $request->get_params();


        //Depend on this property, Response will display and It's able to handle from other Class/ even from other Plugin
        //using action hook 'pssg_api_request_handle'
        $this->product_updated_stats = true;

        /**
         * Action Hook: 'pssg_api_request_handle'
         * @hook pssg_api_request_handle 
         * @desc Control API Request's Object using 
         */
        do_action( 'pssg_api_request_handle', $this );



        
        /**
         * eta ejjonno kora hoyeche jeno
         * action hook er madhome amra eta change korte pari.
         * true and false value.
         * 
         * that's why, I decrered it true before action hook (pssg_product_update_requested)
         */
        if( $this->product_updated_stats ){
            return new \WP_REST_Response( $this->api_response , $this->http_response_code );
        }


        // Return a success response.
        return new \WP_REST_Response( $this->api_response, $this->http_response_code);
        
    }

    /**
     * For handle Multiple product update request
     * can be remove actually
     *
     * @param \WP_REST_Request $request Request will come using \WP_REST_Request class
     * @return \WP_REST_Response
     */
    public function multiple_handle_request( $request )
    {
        $this->request = $request;
        // Get the option name and value from the request.
        $this->requestedParams = $request->get_params();


        //Depend on this property, Response will display and It's able to handle from other Class/ even from other Plugin
        //using action hook 'pssg_api_request_handle'
        $this->product_updated_stats = true;

        /**
         * Action Hook: 'pssg_api_request_handle'
         * @hook pssg_api_request_handle 
         * @desc Control API Request's Object using 
         */
        do_action( 'pssg_api_multiple_request_handle', $this );


        // Return a success response.
        return new \WP_REST_Response( $this->api_response, $this->http_response_code);
        
    }

    
    public function permission_callback( $request )
    {
        return true;
    }

}