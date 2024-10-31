<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly  

$api_key = $saved_data['api_key'] ?? '';
?>
<div class="form-field-wrapper">
    <div class="field-wrapper-inside">
        <div class="form-field col-lg-8">
            
            <div class="pssg-field-wrapper-single">
                <input name="api_key" id="data-api_key" class="ua_input_number config_min_qty" value="<?php echo esc_attr( $api_key ); ?>"  type="text" placeholder="Enter your API Key">
            </div>
            

            <div class="pssg-form-info">
                Please go to <?php pssg_doc_link( 'https://console.cloud.google.com/apis/credentials', __( "Credentials – APIs & Services", 'product-sync-master-sheet' ) ); ?><br>
                + CREATE CREDENTIALS -> API Key -> API key created -> [Copy key]
                
            </div>

        </div>
    </div>
    <?php pssg_get_next_button(true, __("Screenshots", 'product-sync-master-sheet')); ?>
</div>
<div class="step-wizard-details-tuts">
    <div class="step-details-inside">
        <div class="col-md-6">
            <?php pssg_tuts_img_render( 'api_key.jpg', 'Click on CREATE CREDENTIALS button' ); ?>
        </div>
        <div class="col-md-6">
            <?php pssg_tuts_img_render( 'api_key_2.jpg', 'Copy your API Key' ); ?>
        </div>
    </div>
</div>