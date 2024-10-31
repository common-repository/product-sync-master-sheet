<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly  
$errors = $this->get_form_submited_errors();
$sheet_name = $saved_data['sheet_name'] ?? '';
$spreadsheet_id = $saved_data['spreadsheet_id'] ?? '';
$sheet_url = $checkout_sheet_url = $saved_data['sheet_url'] ?? '';


$service_data = $this->Sheet->service_data;
$client_email = $service_data['client_email'] ?? '';
$appscript_link = $saved_data['appscript_link'] ?? '';
?>
<div class="form-field-wrapper">
    <div class="field-wrapper-inside">
        <div class="form-field col-lg-8">
            <div class="pssg-field-wrapper-single">
                <input name="sheet_name" 
                placeholder="Enter your sheet name (e.g., Sheet1 or Sheet2)"
                id="data-sheet_name" 
                class="ua_input_number config_min_qty" 
                value="<?php echo esc_attr( $sheet_name ); ?>"  type="text" step=any>
            </div>
            <div class="pssg-form-info">
                Spreadsheet's tab name | 
                <a href="<?php echo esc_url( $checkout_sheet_url ); ?>" class="checkout-sheet-url" target="_blank"><i class="pssg_icon-doc-text-inv-1"></i><?php echo esc_html__( 'Your Sheet', 'product-sync-master-sheet' ); ?></a>. 
                (e.g., Sheet1 or Sheet2)
            </div>
            
              
        </div>
    </div>
    <?php pssg_get_next_button(); ?>
</div>
<div class="step-wizard-details-tuts">
    <div class="step-details-inside">
        <div class="col-md-6 col-md-offset-3 pssg-img-center">
            <?php pssg_tuts_img_render( 'sheet_name.jpg' ); ?>
        </div>
    </div>
</div>