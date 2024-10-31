<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly  
$errors = $this->get_form_submited_errors();
$sheet_name = $saved_data['sheet_name'] ?? '';
$spreadsheet_id = $saved_data['spreadsheet_id'] ?? '';
$gid = $saved_data['gid'] ?? '';//It's actually sheet id like: #gid=1809683902
$sheet_url = $saved_data['sheet_url'] ?? '';
$checkout_sheet_url = $saved_data['sheet_url'] ?? 'https://docs.google.com/spreadsheets/';
$your_sheet_label = __('Your Google Sheet','product-sync-master-sheet');
if( empty($checkout_sheet_url)){
    $checkout_sheet_url = 'https://docs.google.com/spreadsheets/';
    $your_sheet_label = __('Google Sheet','product-sync-master-sheet');
}

$service_data = $this->Sheet->service_data;
$client_email = $service_data['client_email'] ?? '';
$appscript_link = $saved_data['appscript_link'] ?? '';
?>
<div class="form-field-wrapper">
    <div class="field-wrapper-inside">
        <div class="form-field col-lg-8">
            <div class="pssg-field-wrapper-single">
                <input name="sheet_url" 
                placeholder="Insert your Google Sheet URL"
                id="data-sheet_url" 
                class="ua_input_number config_min_qty" value="<?php echo esc_attr( $sheet_url ); ?>"  type="text" step=any>
                <input name="spreadsheet_id" class="pssg-hidden-input" value="<?php echo esc_attr( $spreadsheet_id ); ?>"  type="hidden">
                <input name="gid" class="pssg-hidden-input" value="<?php echo esc_attr( $gid ); ?>"  type="hidden">
            </div>    
            <div class="pssg-form-info sheet-url-info">
                Go to <?php pssg_doc_link($checkout_sheet_url, $your_sheet_label ); ?> | Create new sheet or Choose from 
                existing Sheet. 
            </div>
            
              
        </div>
    </div>
    <?php pssg_get_next_button(true,'Screenshot'); ?>
</div>
<div class="step-wizard-details-tuts">
    <div class="step-details-inside">
        <div class="col-md-6 col-md-offset-3 pssg-img-center">
            <?php pssg_tuts_img_render( 'sheet_url.jpg' ); ?>
        </div>
    </div>
</div>
