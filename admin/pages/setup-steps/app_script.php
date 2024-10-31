<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly  
$APPSCRIPT_VERSION = '1.0.0';
$appscript_link = $saved_data['appscript_link'] ?? '';
$app_script = $saved_data['app_script'] ?? '';
$checked_app_script = $app_script == 'on' ? 'checked' : '';
?>
<div class="form-field-wrapper">
    <div class="field-wrapper-inside">
        <div class="form-field col-lg-8">
            <div class="appscript-wrapper">
                <a id="pssg-appscript-copy-button" class="pssg-btn pssg-has-icon pssg-btn-small reset reverse">
                    <span><i class="pssg_icon-note"></i></span>
                    <strong class="sync-btn-txt"><?php echo esc_html__( 'Copy AppsScript', 'product-sync-master-sheet' ); ?><i>V<?php echo esc_html( $APPSCRIPT_VERSION ); ?></i></strong>
                </a>
                <!-- display: none;visibility:hidden;height:0;width:0;     -->
<textarea id="appscript-code-area" rows="1" style="display: none;visibility:hidden;height:0;width:0;">
<?php 

/**
 * ########################## 
 * IMPORTANT - DON'T FORMATE CODE FOR THIS FOLLOWING PHP CODE
 * ##########################
 * Don't organize this code,
 * It's important for AppScript
 * 
 * asole code sajale AppScript copy korle dekha zabe surute onek space ache.
 * tai ei code sajaben na. 
 * OK??
 */
$website_routespace = $this->Api->route_namespace . '/' . $this->Api->product_route;//'pssg_gsheet_sync/v1/product'
$website_routespace_multiple = $this->Api->route_namespace . '/' . $this->Api->multiple_product_route;//'pssg_gsheet_sync/v1/multiples'
$replacements = [
    'pssg_namespace_route' => $website_routespace,
    'multiple_product_route' => $website_routespace_multiple,
    'pssg_json_website' => trailingslashit( get_rest_url() )
];
//https://www.toptal.com/developers/javascript-minifier
$script_file = PSSG_BASE_DIR . 'assets/js/script.js';
if( is_file( $script_file ) ){
    $code = pssg_file_get_content( $script_file );
    // Remove single-line comments
    $code = preg_replace('/\/\/(.+?)\n/', '', $code);

    // Remove multi-line comments
    $code = preg_replace('/\/\*.*?\*\//s', '', $code);

    // Remove empty lines
    $code = preg_replace('/^\s*[\r\n]/m', '', $code);
    $code = str_replace( array_keys($replacements), array_values($replacements), $code );
    $appsV = 'var AppsScriptVersion = "' . $APPSCRIPT_VERSION . '";';

    //$AppsScriptAccessCode this is for access code and decleared at setup-wizard.php file
    $appsAccessCode = 'var AppsScriptAccessCode = "' . $AppsScriptAccessCode . '";';
    $extraCode = $appsV . $appsAccessCode;
    $code = $extraCode . $code;
    $code = 'var YourSheetTabName = "";' . $code;
    echo esc_js( $code );
}
?>
</textarea>


                <p class="script-copy-msg"></p>
            </div>
            
             
            <div class="pssg-form-info">
                <p>
                Open 
                <a href="<?php echo esc_url( $checkout_sheet_url ); ?>" class="checkout-sheet-url" target="_blank"><i class="pssg_icon-doc-text-inv-1"></i><?php echo esc_html__( 'Your Sheet', 'product-sync-master-sheet' ); ?></a>
                and Goto Extension(menu)->App Script -> Paste script -> Save.
                </p>
                
            </div>

            <div class="pssg-checkbox-confirmation-wrapper">
                <h3>Have you got message <span>"Your Sheet is Connected"</span>?</h3>
                <div class="pssg-field-wrapper-single">
                    <label class="switch">
                        <input name="app_script" type="checkbox" id="table_view_switcher_on_of" <?php echo esc_attr( $checked_app_script ); ?>>
                        <div class="slider round"><!--ADDED HTML -->
                            <span class="on">Yes</span><span class="off">No</span><!--END-->
                        </div>
                    </label>
                </div>
                
            </div>
        </div>
    </div>
    <?php pssg_get_next_button(true, __('Schreenshot and Video', 'product-sync-master-sheet')); ?>
</div>

<div class="step-wizard-details-tuts">
    <div class="step-details-inside">
        <div class="col-md-7">
            <?php pssg_tuts_img_render( 'appscript.jpg', __('', 'product-sync-master-sheet') ); ?>
            <?php pssg_tuts_img_render( 'appscript_1.jpg', __('', 'product-sync-master-sheet') ); ?>
            <?php pssg_tuts_img_render( 'appscript_2.jpg', __('', 'product-sync-master-sheet') ); ?>
            <?php pssg_tuts_img_render( 'appscript_3.jpg', __('', 'product-sync-master-sheet') ); ?>
            <?php pssg_tuts_img_render( 'appscript_4.jpg', __('', 'product-sync-master-sheet') ); ?>
            <?php pssg_tuts_img_render( 'appscript_5.jpg', __('', 'product-sync-master-sheet') ); ?>
            <?php pssg_tuts_img_render( 'appscript_6.jpg', __('', 'product-sync-master-sheet') ); ?>
        </div>
        <?php pssg_youtube_video( 'pwk_o0TS1VY', ['title' => 'AppScript', 'class_col' => 'col-md-5']) ?>
    </div>
</div>
