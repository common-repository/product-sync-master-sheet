<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly  

$enable_sheet_api = $saved_data['enable_sheet_api'] ?? '';
$checked = $enable_sheet_api == 'on' ? 'checked' : '';
$checkout_sheet_url = $saved_data['sheet_url'] ?? '';
?>
<div class="form-field-wrapper">
    <div class="field-wrapper-inside">
        <div class="form-field col-lg-8">
            <div class="pssg-enable-api-extra-link-text">
                <p class="pssg-additional-tips">You have to Enable Sheet API.
                Open 
                <a href="https://console.cloud.google.com/apis/library/sheets.googleapis.com" target="_blank" class="pssg-doc-link">
                    <i class="pssg_icon-link"></i>
                    Sheet API            
                </a>   
                And Click on <b>Enable</b> button.
                </p>
                <p class="pssg-additional-tips">Remember: There should your selected project on dropdown.</p>
            </div>
            <h3>Have you enable your Sheet API?</h3>
            <div class="pssg-field-wrapper-single">
                <label class="switch">
                    <input name="enable_sheet_api" type="checkbox" id="table_view_switcher_on_of" <?php echo esc_attr( $checked ); ?>>
                    <div class="slider round"><!--ADDED HTML -->
                        <span class="on">Yes</span><span class="off">No</span><!--END-->
                    </div>
                </label>
            </div>
            

             
            
              
        </div>
    </div>
    <?php pssg_get_next_button(); ?>
</div>
<div class="step-wizard-details-tuts">
    <div class="step-details-inside">
        <div class="col-md-6 col-md-offset-3">
            <?php pssg_tuts_img_render( 'enable_sheet_api.jpg' ); ?>
        </div>
    </div>
</div>
