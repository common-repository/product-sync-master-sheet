<?php
namespace PSSG_Sync_Sheet\App\Handle;



use PSSG_Sync_Sheet\App\Http\Api;
use PSSG_Sync_Sheet\App\Http\Sheet;
use PSSG_Sync_Sheet\App\Core\Admin_Base;
use PSSG_Sync_Sheet\App\Service\Products;
use PSSG_Sync_Sheet\App\Service\Standalone;

class Form_Submit extends Admin_Base 
{

    use Standalone;

    //Getting action from POST
    public $action;
    //Naming for config and wizard
    private $action_config = 'pssg_configure_form_submit';//Configure/Setting Page action for form submission
    private $action_wizard = 'pssg_setting_wizard_submit';//Setup wizard or Setup integration page's action for form submission

    public $SUBMIT_POST = [];
    public $database_key;
    
    public $Sheet;

    public $response = [];
    public $errors = [];

    public function __construct()
    {
        parent::__construct();
        
        // $this->Admin_Base_Settings = $this->Admin_Base->settings ?? [];
        
        // $this->Products = Products::init();
        $this->Sheet = Sheet::init();
        // $this->Admin_Base = $this->Sheet->Admin_Base;
        // $this->Api = Api::init();

        //Step 1 : Check action request from $_POST
        $this->action = isset( $_POST['action'] ) ? sanitize_text_field( $_POST['action'] ) : false;

        //Step 2 : Sanitize $_POST 
        $this->SUBMIT_POST = $this->post_req_sanitize();

        //Step 3 : Pass nonce at the begining
        $this->pass_nonce();
    }

    public function run()
    {
        if( empty( $this->action ) ){
            $this->send_json(['status' => 'no-action','message' => __( 'Action req not founded!', 'product-sync-master-sheet' )]);
        }
        switch( $this->action ){
            case $this->action_config:
                $this->database_key = $this->config_key;
                $this->default_settings = get_option( $this->database_key, [] );
                
                break;
            case $this->action_wizard:
                $this->database_key = $this->setting_key;
                $this->default_settings = get_option( $this->database_key, [] );
                
                break;
            default:
                $this->send_json(['status' => 'no-match-action','message' => __( 'Action req not founded!', 'product-sync-master-sheet' )]);
        }
        
        $this->form_submit();
    }


    



    public function form_submit()
    {
        
        $errors = [];

        if( ! empty( $this->SUBMIT_POST ) ){

            switch( $this->action ){
                case $this->action_config:
                    $this->config_form_submit();
                    break;
                case $this->action_wizard:
                    $this->wizard_form_submit();
                    break;
                default:
                    $this->send_json(['status' => 'no-match-action','message' => __( 'Action req not founded!', 'product-sync-master-sheet' )]);    
                    break;

            }

            $this->response['message'][] = 'Successfully Updated';
            $this->response['status'][] = 'success';
            
        }else{
            $this->errors['failed'] = __( 'Form is not submitted.', 'product-sync-master-sheet' );
            $this->response['status'] = 'failed';
            $this->response['error'] = __( 'Form submission error!', 'product-sync-master-sheet' );
        }
        $this->response['errors'] = $this->errors;
        //We can modify the response here using @hook pssg_form_submit_response
        $this->response = apply_filters( 'pssg_form_submit_response', $this->response, $this->SUBMIT_POST );
        $this->send_response();
    }

    protected function wizard_form_submit()
    {
        
        //configure_submit
        $values = ( is_array( $this->SUBMIT_POST ) ? $this->SUBMIT_POST : [] ); 
        
        $data = $final_data = array();
        if( is_array( $values ) && count( $values ) > 0 ){
            foreach( $values as $key=>$value ){
                if( empty( $value ) ){
                $data[$key] = ''; 
                }else{
                $data[$key] = $value;  
                }
            }
        }else{
            $data = $this->default_settings;
        }
        
        
        if(is_array( $data ) && count( $data ) > 0 ){
            foreach($data as $key=>$value){
                if( is_string( $value ) ){
                    $val = str_replace('\\', '', $value );
                }else{
                    $val = $value;
                }
                
                $final_data[$key] = $val;
            }
        }
        

        //Fixing Issue if found in data
        $sheet_link = $this->SUBMIT_POST['sheet_url'] ?? '';
        if ( strpos($sheet_link, 'https://docs.google.com/spreadsheets') !== false && strpos( $sheet_link, 'gid=') !== false) {
            // Extract the document ID using regular expression
            preg_match('/\/d\/(.+?)\//', $sheet_link, $matches);
        
            if (isset($matches[1])) {
                $documentId = $matches[1] ?? '';
                $final_data['spreadsheet_id'] = $documentId;
            } else {
                $final_data['sheet_url'] = $final_data['spreadsheet_id'] = '';
                $this->errors['sheet_url'] = __( 'Sheet ID not founded', 'product-sync-master-sheet' );
            }

            $gid_pattern = '/gid=([0-9]+)/';
            preg_match($gid_pattern, $sheet_link, $gid_matches);
            if (isset($matches[1])) {
                $gid = $gid_matches[1] ?? '0';
                $final_data['gid'] = $gid;
            }
        } else {
            $final_data['sheet_url'] = $final_data['spreadsheet_id'] = '';
            $this->errors['sheet_url'] = __( 'Invalid/Empty Google Sheets link.', 'product-sync-master-sheet' );
            $this->errors['spreadsheet_id'] = __( 'There is an error on your Spreadsheet URL.', 'product-sync-master-sheet' );
            // $this->errors['gid'] = __( 'There is an error on your Spreadsheet URL.', 'product-sync-master-sheet' );
        }

        //handle Sheet name
        $sheet_name = $this->SUBMIT_POST['sheet_name'] ?? '';
        if( empty( $sheet_name ) ){
            $this->errors['sheet_name'] = __( 'Empty Sheet Name.', 'product-sync-master-sheet' );
        }
        $api_key = $this->SUBMIT_POST['api_key'] ?? '';
        if( empty( $api_key ) ){
            $this->errors['api_key'] = __( 'Empty API_KEY.', 'product-sync-master-sheet' );
        }

        
        $service_data = $this->Sheet->service_data;

        $upload_path = $service_data['upload_path'] ?? '';
        if( empty( $upload_path ) ){
            $this->errors['upload_path'] = __( 'Service json file not founded.', 'product-sync-master-sheet' );
        }

        
        $final_data = apply_filters( 'pssg_data_before_save', $final_data, $this, $this->default_settings );
        if( ! empty( $final_data['reset'] ) && $final_data['reset'] == 'reset' ){
            $final_data['reset'] = 'reset';
        }

        $this->update_data( $final_data );
        $this->response['data'] = $final_data;

        if( empty( $this->errors ) ){
            set_transient( $this->Sheet->tokn_key, null, 0 );//Akses tokn asole remove hobe jodi file upload hoy.
        }
        // $this->errorsGenerate([
        //     ['enable_sheet_api' => 'Enable Sheet API.'],
        //     ['create_project' => 'You have to create Project'],
        // ]);
        $this->errorGenerate('enable_sheet_api','Enable Sheet API.');
        $this->errorGenerate('create_project','Check Create Project');
        $this->errorGenerate('editor_email','Check Editor Email Permission');
        $this->errorGenerate('app_script','Follow Appscript Step');
        $this->errorGenerate('add_trigger','You have to setup trigger');
        $this->update_submit_errors( $this->errors );
    }
    protected function config_form_submit()
    {
        if( ! empty( $this->SUBMIT_POST['terms'] ) ){
            $this->SUBMIT_POST['terms'] = array_filter( $this->SUBMIT_POST['terms']  );
        }
        //configure_submit
        $values = ( is_array( $this->SUBMIT_POST ) ? $this->SUBMIT_POST : [] );
            
        $data = $final_data = array();
        if( is_array( $values ) && count( $values ) > 0 ){
            foreach( $values as $key=>$value ){
                if( empty( $value ) ){
                $data[$key] = ''; 
                }else{
                $data[$key] = $value;  
                }
            }
        }else{
            $data = $this->default_settings;
        }
        
        
        if(is_array( $data ) && count( $data ) > 0 ){
            foreach($data as $key=>$value){
                if( is_string( $value ) ){
                    $val = str_replace('\\', '', $value );
                }else{
                    $val = $value;
                }
                
                $final_data[$key] = $val;
            }
        }
        

        
        //Merge with existing data
        // if( empty( $final_data['setup_wizard'] ) ){
        //     $final_data = array_merge( $final_data, $default_settings );
        // }
        
        $final_data = apply_filters( 'pssg_data_before_save', $final_data, $this, $this->default_settings );
        if( ! empty( $final_data['reset'] ) && $final_data['reset'] == 'reset' ){
            $final_data['reset'] = 'reset';
        }

        $this->update_data( $final_data );

        $this->response['data'] = $final_data;
    }

    /**
     * Multiple Errors Generate
     * 
     *
     * @param array $error_args Example: [['request_key' => 'message'],['request_key' => 'message']]
     * @return void
     */
    protected function errorsGenerate($error_args = [])
    {
        if( is_array( $error_args ) && count( $error_args ) > 0 ){
            foreach( $error_args as $key => $value ){
                $this->errorGenerate( $key, $value );
            }
        }
    }
    protected function errorGenerate( $request_key = false, $message = false )
    {
        if(empty( $request_key ) || ! is_string( $request_key )) return;
        if(empty( $this->SUBMIT_POST[$request_key] )){
            if(empty( $message )){
                $message = __( 'Empty '.$request_key, 'product-sync-master-sheet' );
            }
            $this->errors[$request_key] = $message;
        }
    }


    /**
     * Sanitizes the $_POST array by iterating over its 'data' key and sanitizing each value.
     * If 'data' is not an array or is not set, it sends a JSON response with an error message.
     * 
     * DAta index will increase upto 3 level
     *
     * @return array The sanitized $_POST array.
     */
    protected function post_req_sanitize()
    {
        //Sanitizing Whole $_POST Here
        $SUBMIT_POST = [];
        if( isset( $_POST['data'] ) && is_array( $_POST['data'] ) ){
            foreach( $_POST['data'] as $key => $value ){
                if( is_string( $value ) ){
                    $SUBMIT_POST[$key] = sanitize_text_field( $value );
                }else if( is_array( $value ) ){
                    foreach($value as $v_key => $v_val){
                        if( is_string( $v_val ) ){
                            $SUBMIT_POST[$key][$v_key] = sanitize_text_field( $v_val );
                        }else if( is_array( $v_val ) ){
                            foreach( $v_val as $vv_key => $vv_val ){
                                $SUBMIT_POST[$key][$v_key][$vv_key] = sanitize_text_field( $vv_val );

                            }
                        }
                        
                    }
                }
                
            }
        }else{
            $this->send_json(['status' => 'failed','message' => '', 'error' => __( 'Form submission error!', 'product-sync-master-sheet' )]);
        }

        return $SUBMIT_POST;
    }

    /**
     * Updating final data, here required $this->database_key
     * which is called in $this->run() method.
     *
     * @param array $final_data
     * @return void
     */
    protected function update_data( $final_data )
    {
        if(empty( $this->database_key )){
            $this->send_json(['status' => 'failed','message' => '$this->database_key not founed', 'error' => __( 'Database key not founded', 'product-sync-master-sheet' )]);
        }
        update_option( $this->database_key, $final_data);
    }
    /**
     * Passes the nonce to the response.
     * If faild, sends a JSON response with an error message.
     * 
     * @author Saiful Islam <codersaiful@gmail.com>
     * @since 1.0.1.1
     *
     * @return void
     */
    protected function pass_nonce()
    {
        /*
        * We need to verify this came from our screen and with proper authorization,
        * because the save_post action can be triggered at other times.
        * verify this came from the our screen and with proper authorization,
        * because save_post can be triggered at other times
        */ 
        if( ! isset( $_POST['data']['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['data']['nonce'] ) ), plugin_basename( PSSG_BASE_FILE ) ) ) {
            $this->send_json(['status' => 'failed','message' => '', 'error' => __( 'Nonce not founded', 'product-sync-master-sheet' )]);
        }
    }

    protected function send_response()
    {
        $this->send_json($this->response);
    }

    /**
     * Sends a JSON response with from the given data.
     * 
     * If 'data' is not an array or is not set, it sends a JSON response with an error message.
     * 
     * @param array $arr An array of data to send in the response.
     *
     * @author Saiful Islam <codersaiful@gmail.com>
     * @since 1.0.1.1
     *
     * @return void
     */
    protected function send_json( $arr = [] )
    {
        if( ! is_array( $arr ) || empty( $arr ) ){
            $arr = ['status' => 'internal_error','message' => '', 'error' => __( 'Data not set', 'product-sync-master-sheet' )];
        }
        wp_send_json( $arr );
        wp_die();
    }
    
}