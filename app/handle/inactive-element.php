<?php
namespace PSSG_Sync_Sheet\App\Handle;

use PSSG_Sync_Sheet\App\Core\Admin_Base;
use PSSG_Sync_Sheet\App\Service\Products;
use PSSG_Sync_Sheet\App\Service\Standalone;

class Inactive_Element extends Admin_Base
{

    use Standalone;
    public $configs;

    public $cf = [];
    public $cf_count = 0;

    public $columns = [];
    public $Products;
    
    /**
     * after get_columns and unset based on column_setting
     * it will be saved in this array
     * it will be used in config - setting page
     *
     * @var array
     */
    public $final_columns = [];
    public $inactive_column_keys = []; //Array keys of Active columns. such: ['sku','stock']

    public function __construct()
    {
        $this->Products = Products::init();
        add_action( 'pssg_admin_section_top', [$this, 'row_render'], 100, 2 ); 

        $this->columns = $this->Products->get_columns();
        $this->final_columns = $this->columns;

        
        if( ! empty( $this->configs['hide_columns'] ) && is_array( $this->configs['hide_columns'] ) ){
            $hide_columns = $this->configs['hide_columns'] ?? [];
            $this->inactive_column_keys = array_keys( $hide_columns );

            foreach( $this->inactive_column_keys as $column_key ){
                unset( $this->final_columns[$column_key] );
            }
        }

       

    }

    public function row_render($saved_data, $page_name)
    {
        if( $page_name !== 'settings' ) return;

        ?>

        
        <tr class="pssg-premium-row">
            <td>
                <div class="pssg-form-control">
                    <div class="form-label col-lg-4">
                        <label for="pssg-choose-category"> <?php echo esc_html__( 'Choose Category', 'product-sync-master-sheet' ); ?></label>
                    </div>
                    <div class="form-field col-lg-8">
                    
                        <?php
                        $terms = $saved_data['terms'] ?? [];
                        $term_key = apply_filters( 'pssg_query_term_key', 'product_cat' );
                        $multiple_vali = apply_filters( 'pssg_query_term_multiple', false );
                        $multiple_attr = '';
                        if( $multiple_vali ){
                            $multiple_attr = 'multiple';
                        }
                        $args = array(
                            'hide_empty'    => false, 
                            'orderby'       => 'count',
                            'order'         => 'DESC',
                        );
                        $term_obj = get_terms( $term_key, $args );
                        $none = esc_html( 'None', 'product-sync-master-sheet' );
                        $options_item = "<option value='0'>{$none}</option>";
                        $options_item .= ""; 
                        if( is_array( $term_obj ) && count( $term_obj ) > 0 ){
                            $selected_term_ids = $terms;
                            //ONly for old user, where cat data was stored as product_cat_ids
                            if( empty( $selected_term_ids ) && 'product_cat' ==  $term_key){
                                $selected_term_ids = isset( $meta_basics['product_cat_ids'] ) ? $meta_basics['product_cat_ids'] : array();
                            }
                            foreach ( $term_obj as $terms ) {
                                $extra_message = '';                            
                                $selected = is_array( $selected_term_ids ) && in_array( $terms->term_id,$selected_term_ids ) ? 'selected' : false;
                                $options_item .= "<option value='{$terms->term_id}' {$selected}>{$extra_message} {$terms->name} ({$terms->count})</option>";
                            }
                        }
                        ?>

                        <div class="pssg-field-wrapper-single">
                            <select 
                            data-placeholder="Choose Category..."
                            placeholder="Choose Category..."
                            id="pssg-choose-category" 
                            class="ua_input_select" 
                            name="terms[]" <?php echo esc_attr( $multiple_attr ); ?>>
                                <?php echo $options_item; ?>
                            </select>
                        </div>  
                    </div>
                </div>
            </td>
            <td>
                <div class="pssg-form-info">
                    <p>For multiple category, use filter: <br/><code>add_filter( 'pssg_query_term_multiple', '__return_true' );</code></p>
                    
                </div> 
            </td>
        </tr>
        
        <?php 
            $this->cf = $saved_data['added_cf'] ?? [];
            $this->cf = array_filter( $this->cf,function($cf) {
                return ! empty( $cf['keyword'] ) && ! empty( $cf['title'] );;
            });
            if(! is_array( $this->cf ) || empty($this->cf)){
                $this->cf = [
                    [
                    'keyword' => '',
                    'title' => ''
                    ]
                ];
            }elseif( is_array( $this->cf ) && ! empty($this->cf) ){
                $this->cf[] = [
                    'keyword' => '',
                    'title' => ''
                ];
            }
            
            $this->cf_count = count( $this->cf );
            $serial = 0;
        ?>
        <tr class="pssg-premium-row">
            <td>
                <div class="pssg-form-control pssg-field-wrapper-single" style="max-width:100%;">
                    <div class="form-label col-lg-4">
                        <label for="cf_setting-all"> <?php echo esc_html__( 'Custom Fields', 'sync-master-sheet' ); ?></label>    
                    </div>
                    <div class="form-field col-lg-8">
                    
                        <div class="pssg-cf-group pssg-cf-group-setting ">
                        <?php foreach( $this->cf as $cf_key => $cf_value ){ 

                            if( ! is_array( $cf_value ) ) continue;
                            $cf_keyword = $cf_value['keyword'] ?? '';
                            $cf_title = $cf_value['title'] ?? '';
                            $cose_button = true;// ! empty( $cf_keyword ) && !empty( $cf_title ) && 2 == $cf_keyword;
                            ?>
                            <div class="pssg-cf-wrapper pssg-cf-wrapper-single">
                                <div class="added-cf-single">
                                    <input placeholder="Custom Field Keyword" type="text" name="added_cf[<?php echo esc_attr( $serial ); ?>][keyword]" value="<?php echo esc_attr( $cf_keyword ); ?>" id="cf-keyword-<?php echo esc_attr( $serial ); ?>">
                                </div>
                                <div class="added-cf-single">
                                    <input  placeholder="Title for Row Column" type="text" name="added_cf[<?php echo esc_attr( $serial ); ?>][title]" value="<?php echo esc_attr( $cf_title ); ?>" id="cf-title-<?php echo esc_attr( $serial ); ?>">
                                </div>
                                <span class="pssg-config-close-btn"><i class="dashicons dashicons-no-alt"></i></span>    
                            </div> 
                        <?php  
                        $serial++;
                        } ?>
                        
                    
                            <?php 
                            
                            ?>
                        </div>
                          
                    </div>
                </div>
            </td>
            <td>
                <div class="pssg-form-info">
                <p><?php echo esc_html__( 'Add Custom Fields one be one.', 'sync-master-sheet' ); ?></p>
                </div> 
            </td>
        </tr>


        <?php
            $column_setting = $saved_data['hide_columns'] ?? '';
            // dd($column_setting);
            unset($this->columns['ID']);
            unset($this->columns['type']);
            $this->columns['sku'] = [
                'title' => 'SKU',
                'type' => 'cf'
            ];
            $this->columns['_desc'] = [
                'title' => 'Description',
                'type' => 'normal'
            ];
            $this->columns['_url'] = [
                'title' => 'URL',
                'type' => 'normal'
            ];
        ?>
        <tr class="pssg-premium-row">
            <td>
                <div class="pssg-form-control pssg-field-wrapper-single">
                    <div class="form-label col-lg-4">
                        <label for="column_setting-all"> <?php echo esc_html__( 'Hide Columns', 'sync-master-sheet' ); ?></label>
                    </div>
                    <div class="form-field col-lg-8">
                        <p><?php echo esc_html__( 'Notice: If you change Columns option, you have to Syncronize Again.', 'sync-master-sheet' ); ?></p>
                        <div class="pssg-checkbox-group pssg-checkbox-group-columns-setting ">
                            <?php 
                            foreach( $this->columns as $col_key => $column ){
                                $title = $column['title'] ?? $col_key;
                                $checked = ! empty( $column_setting[$col_key] ) ? 'checked' : '';
                            ?>
                            <p class="each-checkbox">
                                <input type="checkbox" 
                                id="checkbox-for-col-<?php echo esc_attr( $col_key ); ?>" 
                                name="hide_columns[<?php echo esc_attr( $col_key ); ?>]" 
                                <?php echo esc_attr( $checked ); ?>
                                value="on">
                                <label for="checkbox-for-col-<?php echo esc_attr( $col_key ); ?>"> <?php echo esc_html( $title ); ?></label><br>
                            </p>
                            <?php 
                            }
                            ?>
                        </div>
                        
                        <?php pssg_error_msg( 'column_setting' ); ?>    
                    </div>
                </div>
            </td>
            <td>
                <div class="pssg-form-info">
                <p><?php echo esc_html__( 'Notice: If you change Columns option, you have to Syncronize Again.', 'sync-master-sheet' ); ?></p>
                </div> 
            </td>
        </tr>
        <?php 
    }
    
}