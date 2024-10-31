<?php
namespace PSSG_Sync_Sheet\App\Http;

use PSSG_Sync_Sheet\App\Service\Products;
use PSSG_Sync_Sheet\App\Core\Admin_Base;
use PSSG_Sync_Sheet\App\Service\Standalone;
/**
 * Whole Sheet manage and Create Access Tocket
 * update, edit Sheet related all things
 * will manage from here
 * 
 * 
 * @author Saiful Islam <codersaiful@gmail.com>
 */
class Sheet
{

    use Standalone;
    public $client_id;
    public $client_secret;

    //for tokn
    public $client_email;
    public $private_key;
    public $sheet_range; //it will generate before update method call

    //for update sheet
    public $spreadsheet_id;
    public $sheet_name;
    public $API_KEY;
    public $tokn_key = 'pssg_eta_holo_akses_tokn';

    public $Products;
    public $Admin_Base;
    public $Admin_Base_Settings;

    public $option_key = 'pssg_service_json_data';
    public $service_data;
    /**
     * Connection status or configuration
     * completion on Dashboard status
     * 
     * If false, No action will happend.
     * 
     *
     * @var boolean if setup complete and found all ok, then that value to be true, otherwise false by default
     */
    protected $configured = false;
    protected $errors = [];

    /**
     * Trigger event execution 
     * to be one time, that's why, I kept this 
     * property, so that, I can handle, If call one time, then I will not again call that same event trigger.
     *
     * @var boolean
     */
    public $event_triggered = false;
    public $order_event_triggered = false;
    protected $event_trigger_late = 2; //In second
    public $temp_stock_ids_key = 'pssg_temp_stock_ids';

    //Actually which product already order but not sync yet, that will be stock here
    public $temp_stock_ids = [];

    public function __construct()
    {
      $this->Products = new Products();
      $this->Admin_Base = new Admin_Base();
      $this->Admin_Base_Settings = $this->Admin_Base->settings ?? [];
      
      $this->service_data = get_option( $this->option_key );
      if( empty($this->service_data) || empty( $this->service_data['client_email'] ) || empty( $this->service_data['private_key'] ) ){
        $this->errors[] = __( 'Problem in srvice file.', 'product-sync-master-sheet' );
      }
      //Configure part Start Here *****************


      $this->client_email = $this->service_data['client_email'] ?? '';
      $this->private_key = $this->service_data['private_key'] ?? '';


      
      

      $sheet_url = $this->Admin_Base_Settings['sheet_url'] ?? '';
      if( empty( $sheet_url ) ){
        $this->errors[] = __( 'Sheet URL is not added', 'product-sync-master-sheet' );
      }

      preg_match('/\/d\/(.+?)\//', $sheet_url, $matches);

      //updateer jonno ja ja lagbe
      // $this->spreadsheet_id = $matches[1] ?? '';
      $this->spreadsheet_id = $this->Admin_Base_Settings['spreadsheet_id'] ?? '';
      if( empty( $this->spreadsheet_id ) ){
        $this->errors[] = __( 'Spreadsheet ID not found!', 'product-sync-master-sheet' );
      }
      $this->sheet_name = $this->Admin_Base_Settings['sheet_name'] ?? '';//'Sheet4'; //Sheet4 - for local and Sheet3 for Online
      //Sheet name is not compulsory for making connection
      if( empty( $this->sheet_name ) ){
        $this->errors[] = __( 'Sheet name not founded', 'product-sync-master-sheet' );
      }
      $this->API_KEY = $this->Admin_Base_Settings['api_key'] ?? '';//'AIzaSyCnpI1iNBZDRu7coOrEkwtXjo4H_dMc2kc';

      if( empty( $this->API_KEY ) ){
        $this->errors[] = __( 'API key not found', 'product-sync-master-sheet' );
      }

      if(count($this->errors) == 0){
        $this->configured = true;
      }

    }
    public function run()
    {

      //eta database ba dashboard theke configure complete howar por e tru oben noile false thakbe.
      

      // AJAX Callback to handle Google OAuth redirect
      add_action('wp_ajax_pssg_syncronize_products', [$this,'update_sheet']);
      add_action('wp_ajax_noprev_pssg_syncronize_products', [$this,'update_sheet']);
      // AJAX Callback to handle Google OAuth redirect
      add_action('wp_ajax_pssg_cleared_sheet', [$this,'clear_online_sheet']);
      add_action('wp_ajax_noprev_pssg_cleared_sheet', [$this,'clear_online_sheet']);




      if( ! $this->configured ) return;

      // add_action('woocommerce_update_product', [$this,'update_sheet_single_product'], 10, 2);
      add_action('save_post', [$this,'save_post'], 10, 2);

      add_action('delete_post', [$this,'delete_post']);
      
      add_action('woocommerce_update_product_variation', [$this,'variation_product_update']);

      //for stock of all update and so on type update
      add_action('woocommerce_product_set_stock', [$this,'updated_props']);
      add_action('woocommerce_variation_set_stock', [$this,'updated_props']);

      add_action( 'variations_event_trigger_hook', [$this,'variations_event_trigger'] );
      add_action( 'order_event_trigger_hook', [$this,'order_event_trigger'] );
      $this->temp_stock_ids = get_option( $this->temp_stock_ids_key, [] );
      if( ! empty( $this->temp_stock_ids ) && is_array( $this->temp_stock_ids ) ){
        add_action( 'init', [$this,'order_event_trigger'] );
      }
    }

    
    /**
     * Specially for stock update on Order
     * or ti will work for any situation also
     * such: stock, stock_status, etc
     * 
     * When call following @hook
     * * woocommerce_product_set_stock
     * * woocommerce_variation_set_stock
     * * OR: woocommerce_product_object_updated_props ( asole eta dui kkhetrei call hoy - ami use korini)
     * 
     * Then I will store these data to wp option and after complete full order, I will call
     * a event trigger after few second
     *
     * @param object|null $product
     * @return void
     */
    public function updated_props( $product )
    {
      if( is_null( $product ) ) return;
      $product_id = $product->get_id();
      $temp_stocks_ids = get_option( $this->temp_stock_ids_key, [] );
      $temp_stocks_ids[$product_id] = $product_id;
      update_option( $this->temp_stock_ids_key, $temp_stocks_ids );

      if( ! $this->order_event_triggered ){
        wp_schedule_single_event( time() + $this->event_trigger_late, 'order_event_trigger_hook', ['product_id']);
        $this->order_event_triggered = true;
      }
      
    }

    /**
     * When user order some product and woocommerce has update
     * stock using hook 
     * * woocommerce_product_set_stock
     * * woocommerce_variation_set_stock
     * * OR: woocommerce_product_object_updated_props ( asole eta dui kkhetrei call hoy - ami use korini)
     * 
     * 
     *
     * @return void
     */
    public function order_event_trigger($sample)
    {
      $ids = $temp_ids = get_option( $this->temp_stock_ids_key, [] );
      if( ! empty( $temp_ids ) && is_array( $temp_ids ) ){

        $stat = $this->multiple_products_update_online_sheet( array_keys( $temp_ids ) );
        if( ! empty( $stat['status'] ) && $stat['status'] == 'success' ){
          foreach($temp_ids as $product_id => $id){
            if( isset( $ids[$product_id] ) ){
              unset( $ids[$product_id] );
            }
          }
          update_option( $this->temp_stock_ids_key, $ids );
        }
        
        

        //Actually for Ajax actually, 
        //and die() available on this method, So No action will heppend after this method.
        // $this->multiple_products_update( array_keys( $temp_ids ) );

        //Nothing should here, because, die() func called on $this->multiple_products_update;
      }
      
    }

    /**
     * By $this->variation_product_update()
     * I hve called a event trigger with hook 'variations_event_trigger_hook'
     * AND to this method, I will $this->multiple_products_update($product_updates)
     *
     * @param int $product_id
     * @return void
     */
    public function variations_event_trigger($product_id)
    {
      $ids = $this->get_post__in( $product_id );

      if( ! empty( $ids ) ){
        $this->multiple_products_update( $ids );
      }
      
    }

    public function get_post__in( $product_id_or_parent_id )
    {
      $result = [];

      $product = wc_get_product( $product_id_or_parent_id );
      $type = $product->get_type();
      if ( $product && $type ==  'variation' ) {
        // Get the parent product ID
        $parent_id = $product->get_parent_id();
        // Add the parent product ID to the result array
        $result[] = $parent_id;

        $product = wc_get_product( $parent_id );
        // Get all child variations IDs
        $children_ids = $product->get_children();

        // Add child variation IDs to the result array
        $result = array_merge($result, $children_ids);
      }else if ( $product && $type ==  'variable' ) {
        $result[] = $product_id_or_parent_id;
        $children_ids = $product->get_children();
        $result = array_merge($result, $children_ids);
      }

      return $result;
    }


    public function sheet_clear()
    {


      if( ! $this->configured ){
        $response = [
          'status'  => 'failed',
          'error'   => 'configuredFailed',
          'errors'  => $this->errors,
        ];
        wp_send_json( $response );
        die();
      }
      if( empty( $product_ids ) || ! is_array( $product_ids ) ){
        $response = [
          'status'  => 'failed',
          'error'   => 'notFoundProductIDs',
          'errors'  => $this->errors,
        ];
        wp_send_json( $response );
        die();
      }

      $t_d = $this->get_token_data();
      if( empty( $t_d ) ){
        $response = [
          'status'  => 'failed',
          'error'   => 'AccessTokenFailed',
        ];
        wp_send_json( $response );
        die();
      }


      $SheetResponse = $this->clear_online_sheet();
      wp_send_json( $SheetResponse );

      die();

    }

    /**
     * This is a Batch Update
     * actually multiple product update to Online sheet using $product_ids array
     * 
     * ***************
     * IMPORTANT
     * ****************
     * DIE() available on this method, that's why, If call any where, bottom will not go
     * $product_ids TO BE AN ARRAY OF VARIATION'S ID OR ANY KIND OF PRODUCT ID.
     * 
     * 
     * eta mulot multiple product edit er kkhetre sheet a product reload korar jonno
     * tobe
     * ********************
     * Apatoto ei method use korbo na
     * ********************
     * karon reload korle thik moto reload hocche na. asole reload hocche but uporer dike ekoi jinis reload hocche sheet a
     * ejonno amora product update korar por ar reload korbo na.
     * asole dorkar e nai Sheet reload korar
     *
     * @param array $product_ids
     * @return array Return a array of value of product for Sheet converted data
     */
    public function multiple_products_update($product_ids = [])
    {

      if( ! $this->configured ){
        $response = [
          'status'  => 'failed',
          'error'   => 'configuredFailed',
          'errors'  => $this->errors,
        ];
        wp_send_json( $response );
        die();
      }
      if( empty( $product_ids ) || ! is_array( $product_ids ) ){
        $response = [
          'status'  => 'failed',
          'error'   => 'notFoundProductIDs',
          'errors'  => $this->errors,
        ];
        wp_send_json( $response );
        die();
      }

      $t_d = $this->get_token_data();
      if( empty( $t_d ) ){
        $response = [
          'status'  => 'failed',
          'error'   => 'AccessTokenFailed',
        ];
        wp_send_json( $response );
        die();
      }


      

      $SheetResponse = $this->multiple_products_update_online_sheet( $product_ids );
      wp_send_json( $SheetResponse );

      die();

    }

    /**
     * Getting sheet data for modify multiple product at a time
     * 
     * ek sathe onek gulo product edtit korte chaile
     * amora eta bebohar korbo
     *
     * @param array $product_ids
     * @return array Array of data which i would like to upload to sheet actually. Acuratley formatted data.
     */
    public function get_batch_data_multiple_row( $product_ids )
    {

      if( empty( $product_ids ) || ! is_array( $product_ids ) ) return [];
      $values = $this->Products->get_sheet_multiple_row( $product_ids );
      $sheet_name = $this->sheet_name;
      $data = [];
      foreach( $values as $value ){
        $product_id = $value[0] ?? 0;
        $row_range = $this->Products->get_sheet_range_by_product_id( $product_id );
        if( empty( $row_range ) ) continue;
        $data[] = [
          'range' => "$sheet_name!$row_range",
          'majorDimension' => 'ROWS',
          'values' => [$value],
          
        ];

      }

      return [
        'valueInputOption' => 'RAW',
        'data'  => $data,
      ];
    }

    /**
     * mone rakhte hobe eta ekhon testing mode ache
     * apatoto page id: 2 and 59(online) er kkhetrei ei method kaj korbe
     * 
     *
     * @return void
     */
    public function update_sheet()
    {

      if( ! $this->configured ){
        $response = [
          'status'  => 'failed',
          'error'   => 'configuredFailed',
          'errors'  => $this->errors,
        ];
        wp_send_json( $response );
        die();
      }

      $nonce = sanitize_text_field( wp_unslash( $_POST['nonce'] ?? '' ) );
      if( empty( $nonce ) || ! wp_verify_nonce( $nonce, plugin_basename( PSSG_BASE_FILE ) ) ) {
          wp_send_json(['status' => 'failed','message' => '', 'error' => __( 'Nonce not founded', 'product-sync-master-sheet' )]);
          wp_die();
      }

      $sheet_products_args = apply_filters( 'pssg_update_sheet_args', [] ); //array( 'posts_per_page' => 10, 'post_type' => 'product' )
      $this->Products = new Products( $sheet_products_args );
      $products = $this->Products;
      $paged = absint( $_POST['paged'] ?? 1 );
      if($paged == 1){
        $products->set_sheet_index([]);
      }

      if( is_array( $products->get_sheet_index() ) && count( $products->get_sheet_index() ) >= $products->get_one_load_limit() ){
        $response = [
          'status'  => 'failed',
          'error'   => 'LimitCross',
          'count' => count( $products->get_sheet_index() ),
        ];
        wp_send_json( $response );
        die();
      }


      $t_d = $this->get_token_data();
      if( empty( $t_d ) ){
        $response = [
          'status'  => 'failed',
          'error'   => 'AccessTokenFailed',
        ];
        wp_send_json( $response );
        die();
      }

      $paged = absint( $_POST['paged'] ?? 1 );
      
      $products->set_paged($paged);
      $products->update_sheet_index = true;
      $value = $products->get_sheet_row();

      if( empty( $value ) ){
        $response = [
          'status'  => 'failed',
          'error'   => 'ProductEmpty',
        ];
        wp_send_json( $response );
        die();
      }
      

      //It will need in update_online_sheet() method
      $this->sheet_range = $products->getSheetRang();

      
      $SheetResponse = $this->update_online_sheet( $value );
      $SheetResponse['count'] = count( $products->get_sheet_index() );
      wp_send_json( $SheetResponse );

      die();
    }

    public function delete_post( $product_id )
    {
      if ( get_post_type( $product_id ) !== 'product') return;
      $products = $this->Products;
      $value = $products->get_sheet_row_by_product_id( $product_id );
      $newValues = [];
      $newValues[0] = array_map(function(){
        return "";
      }, $value[0]);

      

      $newValues[0][0] = 'deleted'; //It's a keyword, no need translation
      $newValues[0][1] = $value[0][1];
      $this->update_sheet_single_product( $product_id, $newValues );
    }
    public function save_post( $product_id, $post )
    {
      
      if($post->post_status == "auto-draft") return;
      if ( get_post_type( $product_id ) !== 'product' && get_post_type( $product_id ) !== 'product_variation' ) return;

      // update_option('test_saiful_1234_called', [ 'ddd' => rand(33,4444), $post ]);
      //Actually we added this method $this->variation_product_update( $product_id ) later @1.0.0.20 actually if variable product actually
      $this->variation_product_update( $product_id );
      $this->update_sheet_single_product( $product_id );

      
    }



    /**
     * If a variation update actually
     * we will call a Event Trigger and we will update all variations
     * where we have used @hook 'variations_event_trigger_hook'
     *
     * @param int $product_id
     * @return void
     */
    public function variation_product_update( $product_id )
    {
      $product = wc_get_product( $product_id );
      if( empty( $product ) || is_null( $product ) || ! is_object( $product )) return;

      $type = $product->get_type();
      if( ! $this->event_triggered && $type == 'variable' || $type == 'variation' ){
        wp_schedule_single_event( time() + 3, 'variations_event_trigger_hook', [$product_id]);
        $this->event_triggered = true;
      }


      
    }


    /**
     * Specially for Action Hook 'pssg_update_product_to_sheet'
     * It's our custom Hook. Specially for API Request Object
     * 
     * If Call here, Sheet will update
     *
     * @param int $product_id
     * @return void
     */
    public function product_to_sheet( $product_id )
    {
      if ( get_post_type( $product_id ) !== 'product' && get_post_type( $product_id ) !== 'product_variation' ) return;

      $this->update_sheet_single_product( $product_id );
    }

    public function update_sheet_single_product($product_id,  $modified_value = [])
    {

      
      if( empty($product_id) ) return;

      //REturn null if not product
      if ( ! wc_get_product( $product_id ) ) return;

      $products = $this->Products;

      $this->sheet_range = $products->get_sheet_range_by_product_id( $product_id );
      if( empty( $this->sheet_range ) ){
        //If not found, it will return null;
        /**
         * ki ki karone sheet_range na paoya zete pare.
         * 1 limit sesh hoye gele
         * 2. 
         */
        return;
      }

      $value = $products->get_sheet_row_by_product_id( $product_id );

      if( ! empty( $modified_value ) && is_array( $modified_value ) ){
        $value = $modified_value;
      }

      // return; //Curently disable google sheet update
      $this->update_online_sheet( $value );
    }


    /**
     * Getting and generating access token
     *
     * @return void
     */
    public function get_token_data()
    {

      if( ! $this->configured ) return;

        $tokn_key = $this->tokn_key;//'pssg_eta_holo_akses_tokn';

        $current_token_data = get_transient( $tokn_key );

        if( ! empty( $current_token_data ) ) return $current_token_data;

        $client_email = $this->client_email;
        $private_key = $this->private_key;
        $now = time();
        $exp = $now + 3600; //Shold be 3600 = 1 hour actually
        $payload = wp_json_encode(
          [
            'iss' => $client_email,
            'aud' => 'https://oauth2.googleapis.com/token',
            'iat' => $now,
            'exp' => $exp,
            'scope' => 'https://www.googleapis.com/auth/spreadsheets',
          ]
        );

        $header = wp_json_encode([
          'alg' => 'RS256',
          'typ' => 'JWT',
        ]);


        $base64_url_header = str_replace([ '+', '/', '=' ], [ '-', '_', '' ], base64_encode($header));
        $base64_url_payload = str_replace([ '+', '/', '=' ], [ '-', '_', '' ], base64_encode($payload));
        
        $signature = '';
        openssl_sign($base64_url_header . '.' . $base64_url_payload, $signature, $private_key, 'SHA256');
        $base64_url_signature = str_replace([ '+', '/', '=' ], [ '-', '_', '' ], base64_encode($signature));

        $jwt = $base64_url_header . '.' . $base64_url_payload . '.' . $base64_url_signature;
        
        $token_url = 'https://oauth2.googleapis.com/token';
        $body = [
          'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
          'assertion' => $jwt,
        ];
        
        $response = wp_remote_post(
          $token_url, [
            'body' => $body,
          ]
        );

        if (is_wp_error($response)) {
          
          error_log('Webhook request failed: ' . $response->get_error_message());
          return;
        }else{
          $response_body = wp_remote_retrieve_body($response);
          
          $token_data = json_decode($response_body, true);
          set_transient( $tokn_key, $token_data, 3500);
          return $token_data;
        }
        return;

        
    }


    /**
     * Method post hotei hobe,
     * noile eta mane batch update 
     * kaj korobe na
     * 
     * VAlue array zemon hote hobe
      ["valueInputOption"]=>
        string(3) "RAW"
        ["data"]=>
        array(5) {
          [0]=>
          array(3) {
            ["range"]=>
            string(13) "Sheet13!A3:J3"
            ["majorDimension"]=>
            string(4) "ROWS"
            ["values"]=>
            array(1) {
              [0]=>
              array(10) {
                [0]=>
                int(17)
                [1]=>
                string(8) "variable"
                [2]=>
                string(6) "Hoodie"
                [3]=>
                string(0) ""
                [4]=>
                string(10) "woo-hoodie"
                [5]=>
                string(0) ""
                [6]=>
                string(2) "42"
                [7]=>
                string(2) "10"
                [8]=>
                string(3) "100"
                [9]=>
                string(2) "10"
              }
            }
          }
          
          [4]=>
          array(3) {
            ["range"]=>
            string(15) "Sheet13!A26:J26"
            ["majorDimension"]=>
            string(4) "ROWS"
            ["values"]=>
            array(1) {
              [0]=>
              array(10) {
                [0]=>
                int(40)
                [1]=>
                string(9) "variation"
                [2]=>
                string(18) "Hoodie - Blue, Yes"
                [3]=>
                float(20)
                [4]=>
                string(20) "woo-hoodie-blue-logo"
                [5]=>
                string(2) "45"
                [6]=>
                string(2) "45"
                [7]=>
                string(2) "10"
                [8]=>
                string(0) ""
                [9]=>
                string(2) "10"
              }
            }
          }
        }
     *
     * @param [type] $value
     * @return void
     */
    public function multiple_products_update_online_sheet( $product_ids )
    {
      if( ! empty( $this->Admin_Base->get_form_submited_errors() ) ) return ['status' => 'failed', 'error' => 'founded_form_submitted_error'];
        

        $value = $this->get_batch_data_multiple_row( $product_ids );

        if( empty( $value ) || ! is_array( $value ) ) return ['status' => 'failed', 'error' => 'sheet_value_empty_no_array'];

        $data = $value;


        if( ! $this->configured ) return ['error' => 'configured_failed']; //It's a error Id, no need translation


        $spreadsheet_id = $this->spreadsheet_id;

        $API_KEY = $this->API_KEY;
        // API endpoint to update values
        $api_url = "https://sheets.googleapis.com/v4/spreadsheets/$spreadsheet_id/values:batchUpdate?valueInputOption=RAW&key=$API_KEY";

        return $this->push_to_sheet( $api_url, $data, 'POST' );
    }

    /**
     * Before clear, I have added new row, if need.
     * 
     * To clear, of destroy everything from Sheet,
     * Just call this method, Sheet's all data will removed.
     *
     * @return array|json it will return's all data from sheet.
     */
    public function clear_online_sheet()
    {
      if( ! empty( $this->Admin_Base->get_form_submited_errors() ) ){
        $response =['status' => 'failed', 'error' => 'founded_form_submitted_error'];
        wp_send_json( $response );
        wp_die();
      }
      $spreadsheet_id = $this->spreadsheet_id;
      $sheet_name = $this->sheet_name;

      $data = array(
        'range' => $sheet_name,
      );

      $API_KEY = $this->API_KEY;
      $spread_fixer = $this->spreadsheet_row_fixer();
      // API endpoint to update values
      $api_url = "https://sheets.googleapis.com/v4/spreadsheets/$spreadsheet_id/values/$sheet_name:clear?key=$API_KEY";
      $clear_sheet_response = $this->push_to_sheet( $api_url, $data, 'POST', $spread_fixer );
      wp_send_json( $clear_sheet_response );
      wp_die();
    }



    /**
     * Updating to online sheet
     * 
     * ******************
     * COMPULSORY
     * ******************
     * * $this->sheet_range
     * * $this->configured
     * * $value data Row data/value for Online / GSheet
     * * $this->spreadsheet_id
     * * $this->sheet_name
     *
     * @param array $value Full array value of all rows for sheet 
     * @return array response array of online sheet api request.
     */
    public function update_online_sheet( $value )
    {
        if( ! empty( $this->Admin_Base->get_form_submited_errors() ) ) return ['status' => 'failed', 'error' => 'founded_form_submitted_error'];
        if( empty( $value ) || ! is_array( $value ) ) return ['status' => 'failed', 'error' => 'sheet_value_empty_no_array']; //It's a error Id, no need translation
        
        $data = array(
          'values' => $value,
        ); 


        if( ! $this->configured ) return ['error' => 'configured_failed']; //It's a error Id, no need translation

        if( empty( $this->sheet_range ) ){
          return ['status' => 'failed', 'error' => 'sheet_range_empty' ]; //It's a error Id, no need translation
        }
        // Replace 'your-sheet-id' with your actual Sheet Sheet ID
        $spreadsheet_id = $this->spreadsheet_id;
        $range = $this->sheet_range;
        $sheet_name = $this->sheet_name;


        
        $API_KEY = $this->API_KEY;
        // API endpoint to update values
        $api_url = "https://sheets.googleapis.com/v4/spreadsheets/$spreadsheet_id/values/$sheet_name!$range?valueInputOption=RAW&key=$API_KEY";

        return $this->push_to_sheet( $api_url, $data );
        
    }

    public function get_current_sheet_name()
    {
      $sheet_name = $this->sheet_name;
      $online_sheet_name = $this->get_online_sheets_name();

      return $online_sheet_name[$sheet_name] ?? [];
    }
    public function get_online_sheets_name()
    {
      $spreadsheet_details = $this->get_online_sheet_details();
      return $spreadsheet_details['sheet_name'] ?? [];
    }
    public function get_online_sheet_gid()
    {
      $spreadsheet_details = $this->get_online_sheet_details();
      return $spreadsheet_details['gid'] ?? [];
    }
    public function get_online_sheet_details_error()
    {
      
      $spreadsheet_details = $this->get_online_sheet_details();
      if(isset( $spreadsheet_details['error_status'] ) && $spreadsheet_details['error_status'] == 'INVALID' ) return $spreadsheet_details;
      return;
    }
     /**
     * Retrieves the details of the online sheet.
     *
     * This function makes a request to the Google Sheets API to fetch the details of the online sheet.
     * It constructs the API URL using the spreadsheet ID and API key.
     * The response from the API is processed to extract the sheet details and store them in a custom format.
     * If the response is empty or not an object with the 'sheets' property, the original response is returned.
     *
     * @return array The custom sheets details, including sheet name, sheet ID, row count, and title.
     */
    public function get_online_sheet_details()
    {
      $spreadsheet_id = $this->spreadsheet_id;
      $API_KEY = $this->API_KEY;
      $api_url = "https://sheets.googleapis.com/v4/spreadsheets/$spreadsheet_id?fields=sheets(properties(sheetId,title,gridProperties(rowCount)))&key=$API_KEY";

      $sheet_details = $this->get_from_sheet( $api_url );
      if( ! isset( $sheet_details['data_response']) ) return $sheet_details;
      $data_response = $sheet_details['data_response'];
      if( ! empty( $data_response ) && is_object( $data_response ) && property_exists( $data_response, 'sheets' ) ){
        $sheets = $data_response->sheets;
        $custom_sheets = [];
        foreach($sheets as $sheet){
          $custom_sheets['sheet_name'][ $sheet->properties->title ] = [
            'gid' => $sheet->properties->sheetId,
            'rowCount' => $sheet->properties->gridProperties->rowCount,
            'title' => $sheet->properties->title
          ];
          $custom_sheets['gid'][ $sheet->properties->sheetId ] = [
            'gid' => $sheet->properties->sheetId,
            'rowCount' => $sheet->properties->gridProperties->rowCount,
            'title' => $sheet->properties->title
          ];
          // $custom_sheets[ $sheet->properties->sheetId ] = $sheet->properties->title;

        }

        return $custom_sheets;
      }else if( ! empty( $data_response ) && is_object( $data_response ) && property_exists( $data_response, 'error' ) ){
        $error = [];
        $error['error_status'] = 'INVALID';
        $error['code'] = $data_response->error->code;
        $error['message'] = $data_response->error->message;
        
        $error['status'] = $data_response->error->status;
        $error['details'] = $data_response->error->details;
        return $error;
      }
      return $sheet_details;
    }
    /**
     * If found row count, from post count, we will fix it actually.
     *
     * @param integer $extra_rows
     * @return void
     */
    public function spreadsheet_row_fixer()
    {
      
      $fianl_output = [];
      if( ! empty( $this->Admin_Base->get_form_submited_errors() ) ){
        return;
      }

      $stats = $this->Products->get_stats();
      $post_count = $stats['post_count'] ?? 1000;

      $sheet_details = $this->get_current_sheet_name();
      $current_row_count = $sheet_details['rowCount'] ?? 1000;

      if( $current_row_count >= ($post_count + 2) ) return; //Added additinal 20 rows for safety

      $endIndex = $post_count - $current_row_count + 20;

      $current_sheet_name = $this->get_current_sheet_name();
      if(isset( $current_sheet_name['gid'] ) && ! empty( $current_sheet_name['gid'] )){
        $sheetId = $current_sheet_name['gid'];
      }else{
        $sheetId = $this->Admin_Base_Settings['gid'] ?? '0';
        $sheetId = ! empty( $sheetId ) ? $sheetId : '0';
      }
      
      
      $spreadsheet_id = $this->spreadsheet_id;
      $data = array(
        'requests' => array(
            array(
                'insertDimension' => array(
                    'range' => array(
                        'sheetId' => $sheetId, // Assuming you want to add rows to the first sheet. Change if necessary.
                        'dimension' => 'ROWS',
                        'startIndex' => $current_row_count - 1, // Adjust this if you need to start at a specific index.
                        'endIndex' => $endIndex
                    ),
                    'inheritFromBefore' => true
                )
            )
        )
    );

      $API_KEY = $this->API_KEY;
      $api_url = "https://sheets.googleapis.com/v4/spreadsheets/$spreadsheet_id:batchUpdate?key=$API_KEY";
      
      $fianl_output = $this->push_to_sheet( $api_url, $data, 'POST', [ 'data' => $data] );
      return $fianl_output;

    }
    

    /**
     * Push to Online sheet
     *
     * @param string $api_url
     * @param array $data
     * @param string $method
     * @return array Respnse of Data update as an array.
     */
    private function push_to_sheet( $api_url, $data, $method = 'PUT', $extra_msg_response = [] )
    {
      // Fetch access token from your secure storage (update_option, database, etc.)
      $access_data = $this->get_token_data();
      $access_token = $access_data['access_token'] ?? '';
      if( empty( $access_token ) ){
        return [ 'status' => 'failed', 'error' => __( 'Access Token not founded', 'product-sync-master-sheet' ) ];
      }

      // Set the request parameters
      $request_args = array(
          'headers'     => array(
              'Content-Type'  => 'application/json',
              'Authorization' => 'Bearer ' . $access_token,
          ),
          'body'        => wp_json_encode($data),
          'method'      => $method,//'PUT',
          'data_format' => 'body',
      );
      // return $api_url;
      if( $method == 'GET' || $method == 'get' ){
        unset($request_args['body']);
        unset($request_args['method']);
        unset($request_args['data_format']);
        //Make the request GET request
        $response = wp_remote_get($api_url, $request_args);
      }else{
        // Make the POST/PUT request
        $response = wp_remote_post($api_url, $request_args);
      }
      
  
      // Check for errors
      if ( is_wp_error( $response ) ) {
          // Handle error
          $error_message = $response->get_error_message();
          return [ 'status' => 'failed', 'error' => $error_message ];
      } else {
          // Process the response
          $body = wp_remote_retrieve_body($response);
          $data_response = json_decode($body);

          // Hide headers data on response, because we have used it in javascript
          $request_args['headers'] = 'hidden_headers';
           return [ 
            'status' => 'success', 
            'data_response' => $data_response, 
            'request_args' => $request_args, 
            'extra_msg_response' => $extra_msg_response,
            'other_info' => [
              'spreadsheet_id' => $this->spreadsheet_id,
              'sheet_name' => $this->sheet_name,
            ]
          ];
      }

      return [ 'status' => 'failed', 'error' => 'NothingFounded' ];
    }

    public function get_from_sheet( $api_url, $extra_msg_response = [] )
    {
      $access_data = $this->get_token_data();
      $access_token = $access_data['access_token'] ?? '';
      if( empty( $access_token ) ){
        return [ 'status' => 'failed', 'error' => __( 'API_Key/ServiceJSON File/SheetURL - any one or more of them is missing', 'product-sync-master-sheet' ) ];
      }

      // Set the request parameters
      $request_args = array(
        'headers'     => array(
            'Content-Type'  => 'application/json',
            'Authorization' => 'Bearer ' . $access_token,
        )
      );
      $response = wp_remote_get($api_url, $request_args);

      // Check for errors
      if ( is_wp_error( $response ) ) {
        // Handle error
        $error_message = $response->get_error_message();
        return [ 'status' => 'failed', 'error' => $error_message ];
      } else {
          // Process the response
          $body = wp_remote_retrieve_body($response);
          $data_response = json_decode($body);

          return [ 
            'status' => 'success', 
            'data_response' => $data_response, 
            'extra_msg_response' => $extra_msg_response,
            'other_info' => [
              'spreadsheet_id' => $this->spreadsheet_id,
              'sheet_name' => $this->sheet_name,
            ]
          ];
      }

      return [ 'status' => 'failed', 'error' => 'NothingFounded' ];
    }

    public function get_errors()
    {
      return $this->errors;
    }

}