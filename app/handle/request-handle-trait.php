<?php 
namespace PSSG_Sync_Sheet\App\Handle;

/**
 * Specially for two Class
 * PSSG_Sync_Sheet\App\Handle\Api_Request_Handle;
 * PSSG_Sync_Sheet\App\Handle\Quick_Table
 */
trait Request_Handle_Trait
{
    /**
     * Update post meta for product, where obviously available $this->product_id
     * 
     * ****************
     * Compolsury
     * ****************
     * $this->product_id
     *
     * @param string $meta_key
     * @param string|array $meta_value
     * @return void
     */
    public function update_meta( $meta_key, $meta_value )
    {
        if( ! $this->product_id || empty( $meta_key ) ) return;
        update_post_meta( $this->product_id, $meta_key, $meta_value );
    }

    /**
     * Stock Update Based on normal or simple Param Rquest
     * not for mulptole actually.
     * Compolsury:
     * $this->columns = $API->columns;
        $this->columns_label = $API->columns_label;
        $this->columns_param_label = $API->columns_param_label;
        $this->columns_param_label_flip = $API->columns_param_label_flip;
     *
     * @return void
     */
    public function single_param_request_wise_stock_update()
    {

        $stock_param_key = $this->columns_param_label['stock'] ?? 'Stock';
        $request_stock_val = $this->requestedParamsSingle[$stock_param_key] ?? '';
        // if( ! is_numeric( $request_stock_val ) ){
        //     return ['status' => 'failed', 'message' => __( 'Stock amount should be numeric!', 'product-sync-master-sheet' )];
        // }
        return $this->stock_update( $request_stock_val );
        
    }

    /**
     * Obviously should $this->product_id because 
     * we have used $this->update_meta, where need $this->product_id
     * 
     * ********************
     * COMPOLSURY
     * ********************
     * $this->product_id
     *
     * @param string|mixed $request_stock_val
     * @return array response report as array.
     */
    public function stock_update( $request_stock_val )
    {
        if( is_numeric( $request_stock_val ) || empty( $request_stock_val ) ){
            $request_stock_val = empty( $request_stock_val ) ? 0 : $request_stock_val;
            $this->update_meta('_manage_stock', 'yes');
            $this->update_meta('_stock', $request_stock_val);

            $stock_status = $this->get_meta( '_stock_status' );
            if($request_stock_val == 0 || $request_stock_val < 0){
                $this->update_meta('_stock_status', 'outofstock');
            }elseif( $stock_status == 'outofstock' ){
                $this->update_meta('_stock_status', 'instock');
            }

            
            

            return ['status' => 'updated','product_id' => $this->product_id, 'manage_stock' => true, '_stock'=>$request_stock_val];
        }else if( is_string( $request_stock_val ) ){
            $request_stock_val = strtolower( $request_stock_val );
            $stock_status = '';
            switch($request_stock_val){
                
                case 'back order':
                case 'backorder':
                case 'back':
                    $stock_status = 'backorder';
                    $this->update_meta('_stock_status', 'backorder');
                break;

                case 'outofstock':
                case 'out of stock':
                case 'out ofstock':
                case 'out':
                    $stock_status = 'outofstock';
                    $this->update_meta('_stock_status', 'outofstock');
                break;

                case 'in stock':
                case 'instock':
                case 'stock':
                default:
                    $stock_status = 'instock';
                    $this->update_meta('_stock_status', 'instock');
                break;
            }

            $this->update_meta('_manage_stock','no');
            $this->update_meta('_stock', '');
            return ['status' => 'updated','product_id' => $this->product_id, 'manage_stock' => false, 'stock_status'=> $stock_status, '_stock' => 1];
        }


        return ['status' => 'failed','product_id' => $this->product_id,];
    }

    public function get_meta( $meta_key )
    {
        
        if( ! $this->product_id || empty( $meta_key ) ) return;
        return get_post_meta( $this->product_id, $meta_key, true );
    }

    public function update_product_type()
    {
        $type_param_key = $this->columns_param_label['type'] ?? 'Type';
        $product_type = $this->requestedParamsSingle[$type_param_key] ?? '';
        $this->update_meta( '_product_type', $product_type );
    }

    /**
     * Request Param wise
     * we will update all custom field
     *
     * @return array updated cf will here as response.
     */
    public function request_wise_cf_update()
    {
        $update_cf = [];
        if( ! is_array( $this->requestedParamsSingle ) ) return;
        foreach( $this->requestedParamsSingle as $param => $param_value ){
            $req_key = $this->columns_param_label_flip[$param] ?? '';
            $key_type = $this->columns[$req_key]['type'] ?? '';
            if($key_type == 'cf'){
                $update_cf[$req_key] = $param_value;
                $this->update_meta( $req_key, $param_value );
            }
        }

        return $update_cf;
    }
}