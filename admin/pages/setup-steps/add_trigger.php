<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly  

$add_trigger = $saved_data['add_trigger'] ?? '';
$checked = $add_trigger == 'on' ? 'checked' : '';

?>
<div class="form-field-wrapper">
    <div class="field-wrapper-inside">
        <div class="form-field col-lg-8">
            <div class="pssg-form-info">
                <p>
                <a href="<?php echo esc_url( $checkout_sheet_url ); ?>" class="checkout-sheet-url" target="_blank"><i class="pssg_icon-doc-text-inv-1"></i><?php echo esc_html__( 'Your Sheet', 'product-sync-master-sheet' ); ?></a>
                -> Extension(Menu) -> App Script -> Trigger -> Add Trigger
                </p>
                <p class="pssg-additional-tips">
                Run <code>doEdit()</code> function <code>on edit</code> event. See screenshot below.
                </p>
            </div>
            <h3>Have you added Trigger?</h3>
            <div class="pssg-field-wrapper-single">
                <label class="switch">
                    <input name="add_trigger" type="checkbox" id="table_view_switcher_on_of" <?php echo esc_attr( $checked ); ?>>
                    <div class="slider round"><!--ADDED HTML -->
                        <span class="on">Yes</span><span class="off">No</span><!--END-->
                    </div>
                </label>
            </div>
              
        </div>
    </div>
    <?php pssg_get_next_button(true, __('Schreenshot and Video', 'product-sync-master-sheet')); ?>
</div>
<div class="step-wizard-details-tuts">
    <div class="step-details-inside">
        <div class="col-md-7">
            <?php pssg_tuts_img_render( 'appscript.jpg', __('', 'product-sync-master-sheet') ); ?>
            <?php pssg_tuts_img_render( 'trigger_1.jpg', __('', 'product-sync-master-sheet') ); ?>
            <?php pssg_tuts_img_render( 'trigger_2.jpg', __('', 'product-sync-master-sheet') ); ?>
            <?php pssg_tuts_img_render( 'trigger_3.jpg', __('', 'product-sync-master-sheet') ); ?>
        </div>
        <?php pssg_youtube_video( '9y0wJ7rCvyk', [ 'class_col' => 'col-md-5']) ?>
    </div>
</div>
