<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly  

$min_max_img = PSSG_BASE_URL . 'assets/images/brand/social/sync-master.png';



$topbar_sub_title = __( 'Syncronize Dashboard', 'product-sync-master-sheet' );
if( isset( $this->topbar_sub_title ) && ! empty( $this->topbar_sub_title ) ){
    $topbar_sub_title = $this->topbar_sub_title;
}
?>
<div class="pssg-header pssg-clearfix">
    <div class="container-flued">
        <div class="col-lg-7">
            <div class="pssg-logo-wrapper-area">
                <div class="pssg-logo-area">
                    <img src="<?php echo esc_url( $min_max_img ); ?>" class="pssg-brand-logo">
                </div>
                <div class="pssg-main-title">
                    <h2 class="pssg-ntitle">
                        <?php esc_html_e("Sync Master Sheet", 'product-sync-master-sheet');?>
                        
                        <?php
                        if( $this->is_premium ){
                        ?>
                        <span><?php esc_html_e( "Premium", 'product-sync-master-sheet' ); ?></span>
                        <?php
                        }
                        ?>
                    </h2>
                </div>
                
                <div class="pssg-main-title pssg-main-title-secondary">
                    <h2 class="pssg-ntitle"><?php echo esc_html( $topbar_sub_title );?></h2>
                </div>

            </div>
        </div>
        <div class="col-lg-5">
            <div class="header-button-wrapper">
                <?php if( ! $this->is_premium){ ?>
                    <a class="pssg-button reverse" 
                        href="https://codeastrology.com/downloads/product-sync-master-sheet-premium/" 
                        target="_blank">
                        <i class="pssg_icon-heart-filled"></i>
                        <?php echo esc_html__( 'Get Premium Version', 'product-sync-master-sheet' ); ?>
                    </a>
                <?php } ?>
                
                <a class="pssg-button reset" 
                    href="https://codeastrology.com/docs/plugin/product-sync-master-sheet/" 
                    target="_blank">
                    <i class="pssg_icon-note"></i><?php echo esc_html__( 'Documentation', 'product-sync-master-sheet' ); ?>
                </a>
            </div>
        </div>
    </div>
</div>