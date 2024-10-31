<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly  

$file_name = $service_data['file_name'] ?? '';
$service_div_class = 'no-file-founded';

if( ! empty( $file_name ) ){
    $service_div_class = 'success-on-json';
}
?>
<div class="form-field-wrapper">
    <div class="field-wrapper-inside">
        <div class="form-field col-lg-8">
            <div class="pssg-form-info">
                Please <?php pssg_doc_link( 'https://console.cloud.google.com/apis/credentials', __( "Create a Service JSON file", 'product-sync-master-sheet' ) ); ?>
                
            </div>
            <div class="pssg-form-control">
                <div class="form-field col-lg-12">
                    <label for=""> <?php echo esc_html__( 'Upload your service file', 'product-sync-master-sheet' ); ?></label>
                    <div class="pssg-field-wrapper-single"><input id="json-upload-container-input" type="hidden" name="service_file" value="<?php echo esc_attr( $file_name ); ?>"></div>
                    <div class="json-uploader-all-wrapper">
                        <div id="json-upload-container" class="json-upload-container <?php echo esc_attr( $service_div_class ); ?>"  data-nonce="<?php echo esc_attr( wp_create_nonce( plugin_basename( PSSG_BASE_FILE ) ) ); ?>">
                            <p class="old-file-data">
                                <i class="pssg_icon-link"></i>
                                <span><?php echo esc_html( $file_name ); ?></span>
                                <i class="pssg_icon-trash delete-old-file-data"></i>
                            </p>
                            <p class="drag-drop-text"><?php echo esc_html__( 'Drag and drop your Sevice JSON file here or click to select.', 'product-sync-master-sheet' ); ?></p>
                            <input type="file" id="json-file-input" accept=".json" />
                            <label for="json-file-input"><i class="pssg_icon-bag"></i><?php echo esc_html__( 'Select File', 'product-sync-master-sheet' ); ?></label>
                            <div id="json-upload-message"></div>
                            
                        </div>
                        <div id="json-upload-progress-bar"></div>
                        <div class="json-all-message-wrapper">
                            
                            <div id="json-upload-message-success"></div>
                            <div id="json-upload-message-errors"></div>
                            <div class="extra pssg-animate-wrapper"><i class="pssg_icon-spin5 animate-spin"></i></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    <?php pssg_get_next_button(true, "Screenshots and Video"); ?>
</div>

<div class="step-wizard-details-tuts">
    <div class="step-details-inside">
        <div class="col-md-7">
            <?php pssg_tuts_img_render( 'service_file_1.jpg', 'Click on CREATE CREDENTIALS button' ); ?>
            <?php pssg_tuts_img_render( 'service_file_2.jpg', 'Provide a Service file name.' ); ?>
            <?php pssg_tuts_img_render( 'service_file_3.jpg', 'Click on this type Email address for next step of Service file creation.' ); ?>
            <?php pssg_tuts_img_render( 'service_file_4.jpg' ); ?>
            <?php pssg_tuts_img_render( 'service_file_5.jpg', 'Create your json file.' ); ?>

        </div>
        <?php pssg_youtube_video( 'nitVCIk6ods', ['class_col' => 'col-md-5']); ?>
    </div>
</div>
