<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly  

$sheet_name = $saved_data['sheet_name'] ?? '';
$spreadsheet_id = $saved_data['spreadsheet_id'] ?? '';
$create_project = $saved_data['create_project'] ?? '';
$checked = $create_project == 'on' ? 'checked' : '';
$sheet_url = $checkout_sheet_url = $saved_data['sheet_url'] ?? '';


$service_data = $this->Sheet->service_data;
$client_email = $service_data['client_email'] ?? '';
$appscript_link = $saved_data['appscript_link'] ?? '';
?>
<div class="form-field-wrapper">
    <div class="field-wrapper-inside">
        <div class="form-field col-lg-8">
            <div class="pssg-form-info">
                Please <?php pssg_doc_link( 'https://console.cloud.google.com/projectcreate?previousPage=/apis/credentials', __( "Create a project", 'product-sync-master-sheet' ) ); ?>
                And Choose your project name.
            </div>
            <h3>Have you created a Project yet?</h3>
            <div class="pssg-field-wrapper-single">
                <label class="switch">
                    <input name="create_project" type="checkbox" id="table_view_switcher_on_of" <?php echo esc_attr( $checked ); ?>>
                    <div class="slider round"><!--ADDED HTML -->
                        <span class="on">Yes</span><span class="off">No</span><!--END-->
                    </div>
                </label>
            </div>
            <div class="pssg-form-info">
                

            </div>
            
              
        </div>
    </div>
    <?php pssg_get_next_button(true,'Screenshot'); ?>
</div>
<div class="step-wizard-details-tuts">
    <div class="step-details-inside">
        <div class="col-md-6">
            <?php pssg_tuts_img_render( 'create_project_1.jpg', __( 'Submit project and click on CREATE button.', 'product-sync-master-sheet') ); ?>

        </div>
        <div class="col-md-6">
            <?php pssg_tuts_img_render( 'create_project_2.jpg', __( 'Or Choose an existing project.', 'product-sync-master-sheet') ); ?>
        </div>
    </div>
</div>
