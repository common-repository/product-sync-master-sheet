<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly  

$errors = [];
$saved_data = get_option( $this->config_key, [] );
include $this->topbar_file;

?>





<div class="wrap pssg_wrap pssg-content">

    <h1 class="wp-heading "></h1>
    <div class="fieldwrap">
        

        
        <form class="" action="" method="POST" id="pssg-other-settings-form">
        <input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( plugin_basename( PSSG_BASE_FILE ) ) ); ?>">
        <input class="" type="hidden" name="form_type" value="settings">
        <div class="pssg-configure-tab-wrapper pssg-section-panel no-background"></div>

            <?php
            $pages = [
                
                'settings' =>  [
                    'icon'  => 'pssg_icon-cog-alt',
                    'file'  => ''
                ],
                // 'getting-start' => [
                //     'icon'  => 'pssg_icon-home',
                //     'file'  => '' //Actually if file empty, then it will search from steps folder
                // ],
                
            ];
            $pages = apply_filters( 'pssg_admin_pages_arr', $pages, $this );
            foreach( $pages as $page_key => $page ){
                $icon = ! empty( $page['icon'] ) && is_string( $page['icon'] ) ? $page['icon'] : 'pssg_icon-link-ext';
                $file_loc = ! empty( $page['file'] ) && is_file( $page['file'] )  ? $page['file'] : __DIR__ . '/incs/' . $page_key . '.php' ;
                
                if( is_file( $file_loc ) ){
                ?>
                <div class="pssg-section-panel <?php echo esc_attr( $page_key ); ?>-settings" id="pssg-<?php echo esc_attr( $page_key ); ?>-settings" data-icon="<?php echo esc_attr( $icon ); ?>">
                    <?php include $file_loc; ?>
                </div>
                <?php
                }else{
                    printf(
                        /* translators: %s: file location uri */
                        esc_html__( '%s file is not founded!', 'product-sync-master-sheet' ),
                        esc_html( $file_loc )
                    );
                }

            }
            ?>

        
            
            
        
            <?php 

            /**
             * @Hook Action: pssg_form_panel
             * To add new panel in Forms
             * @since 1.8.6
             */
            do_action( 'pssg_form_panel', $saved_data );
            ?>
            
            
            

            <div id="pssg-form-submit-button" class="pssg-section-panel no-background pssg-full-form-submit-wrapper">
                
                <button name="configure_submit" type="submit"
                    class="pssg-btn pssg-has-icon configure_submit">
                    <span><i class="pssg_icon-floppy"></i></span>
                    <strong class="form-submit-text">
                    <?php echo esc_html__('Save Change','product-sync-master-sheet');?>
                    </strong>
                </button>

                
            </div>

            

                    
        </form>

    </div>
</div> 