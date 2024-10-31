<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly  

$client_email = $service_data['client_email'] ?? '';
$editor_email = $saved_data['editor_email'] ?? '';
$editor_email_checked = $editor_email == 'on' ? 'checked' : '';
$checkout_sheet_url = $saved_data['sheet_url'] ?? '';
?>
<div class="form-field-wrapper">
    <div class="field-wrapper-inside">
        <div class="form-field col-lg-8">
            <input id="" class="ua_input_number pssg-client-email" value="<?php echo esc_attr( $client_email ); ?>"  type="text" readonly>
            <div class="pssg-form-info">
                Please copy this email and Open 
                <a href="<?php echo esc_url( $checkout_sheet_url ); ?>" class="checkout-sheet-url" target="_blank"><i class="pssg_icon-doc-text-inv-1"></i><?php echo esc_html__( 'Your Sheet', 'product-sync-master-sheet' ); ?></a>
                and add permission as Editor.<br>
                Share -> Add people -> Add email -> Done
            </div>
            <div class="sheet-details-some-link">
                
            </div> 
              
            <h3>Have you added your email with Editor Permisions?</h3>
            <div class="pssg-field-wrapper-single">
                <label class="switch">
                    <input name="editor_email" type="checkbox" id="table_view_switcher_on_of" <?php echo esc_attr( $editor_email_checked ); ?>>
                    <div class="slider round"><!--ADDED HTML -->
                        <span class="on">Yes</span><span class="off">No</span><!--END-->
                    </div>
                </label>
            </div>
            
        </div>
    </div>
    <?php pssg_get_next_button(true, __('Screenshots and Video', 'product-sync-master-sheet')); ?>
</div>

<div class="step-wizard-details-tuts">
    <div class="step-details-inside">
        <div class="col-md-6">
            <?php pssg_tuts_img_render( 'editor_email_1.jpg', __( 'Click on Share button.', 'product-sync-master-sheet') ); ?>
            <hr>
            <?php pssg_tuts_img_render( 'editor_email.jpg', __( 'Click on Add people button and Add email with Editor Permisions.', 'product-sync-master-sheet') ); ?>
        </div>
        <?php pssg_youtube_video( '6uvmPJIN-So' ); ?>
    </div>
</div>

