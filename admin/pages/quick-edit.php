<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly  

include $this->topbar_file;

?>





<div class="wrap pssg_wrap pssg-content">

    <h1 class="wp-heading "></h1>
    <div class="fieldwrap">
        

        
        <div class="pssg-section-panel no-background quick-edit-section-wrapper">
        <div class="pssg-section-panel quick-edit-section" id="quick-edit-section" data-icon="pssg_icon-home">
            <?php
            
            do_action( 'pssg_admin_quick_edit_before' );
            // PSSG_Sync_Sheet\App\Handle\Quick_Table::init()->display_table(['posts_per_page' => $posts_per_page, 'paged' => $paged]);
            $this->Quick_Table->display_table();
            do_action( 'pssg_admin_quick_edit_after' );
            ?>     
            
        </div>
        </div>

    </div>
</div> 