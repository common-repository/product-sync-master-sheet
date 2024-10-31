<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly  

?>
<table class="pssg-table universal-setting">
    <thead>
        <tr>
            <th class="pssg-inside">
                <div class="pssg-table-header-inside">
                    <h3>
                        <?php echo esc_html__( 'Settings', 'product-sync-master-sheet' ); ?>
                        <?php
                        $sheet_url = $this->settings['sheet_url'] ?? '';
                        pssg_sheet_url_render( $sheet_url );
                        ?>
                    </h3>
                </div>
                
            </th>
            <th>
            <div class="pssg-table-header-right-side"></div>
            </th>
        </tr>
    </thead>

    <tbody>
        

    <?php 
    /**
     * Action @hook for setting page
     */
    do_action( 'pssg_admin_section_top', $saved_data, 'settings' ); ?>

    <?php
    $edit_table_title = $saved_data['edit_table_title'] ?? '';
    ?>
    <tr>
        <td>
            <div class="pssg-form-control">
                <div class="form-label col-lg-4">
                    <label for="edit_table_title"> <?php echo esc_html__( 'Edit Table Title', 'product-sync-master-sheet' ); ?></label>
                </div>
                <div class="form-field col-lg-8">
                    <div class="pssg-field-wrapper-single">
                        <input name="edit_table_title" id="edit_table_title" class="ua_input_number config_min_qty" value="<?php echo esc_attr( $edit_table_title ); ?>"  type="text" step=any>
                    </div>
                    <?php pssg_error_msg( 'edit_table_title' ); ?>    
                </div>
            </div>
        </td>
        <td>
            <div class="pssg-form-info">
                <p>Title for Product Quick Edit Table</p>
                <?php pssg_doc_link( admin_url( 'admin.php?page=pssg-quick-edit' ), __( 'Checkout Quick Table', 'product-sync-master-sheet' ) ); ?>
            </div> 
        </td>
    </tr>
    <?php do_action( 'pssg_admin_section_bottom', $saved_data, 'settings' ); ?>

 </tbody>

    
</table>