<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly  

$section_title = esc_html__( 'Pro Features', 'product-sync-master-sheet' );
$total = count( $this->Products->get_sheet_index() ); //Total Sync Products

?>
<table class="pssg-table universal-setting">
    <thead>
        <tr>
            <th class="pssg-inside">
                <div class="pssg-table-header-inside">
                    <h3><?php echo esc_html( $section_title ); ?></h3>
                </div>
                
            </th>
            <th class="pssg-emty-element">
            </th>
        </tr>
    </thead>

    <tbody>
        

    <?php 
    /**
     * Action @hook for getting-start page
     */
    do_action( 'pssg_admin_section_top', $saved_data, 'getting-start' ); ?>
        <tr>
            <td>
                <div class="pssg-form-control">
                    <div class="form-label col-lg-12">
                        <div class="getting-start-part-wrapper">
                            <div class="getting-header">
                                
                            </div>
                            <div class="getting-body">
                                <?php if( ! empty( $total ) ){ ?>
                                <h3><?php echo esc_html__( 'Total Sync:', 'product-sync-master-sheet' ); ?> <?php echo esc_html( $total ); ?></h3>  
                                <?php } ?>

                                <h3>See <a href="https://codeastrology.com/how-to-setup-product-sync-master-plugin/" target="_blank"><?php echo esc_html__( 'Setup guide - step by step', 'product-sync-master-sheet' ); ?></a></h3>
                            </div>

                        </div>
                    </div>
                </div>
            </td>
            <td class="pssg-emty-element"></td>
        </tr>
    <?php 
    /**
     * Action @hook for getting-start page
     */
    do_action( 'pssg_admin_section_bottom', $saved_data, 'getting-start' ); ?>

        
    </tbody>

    
</table>