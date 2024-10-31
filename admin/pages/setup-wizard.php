<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly  

$errors = $this->get_form_submited_errors();
$saved_data = get_option( $this->setting_key, [] );

$AppsScriptAccessCode = $saved_data['AppsScriptAccessCode'] ?? '';
if( empty($AppsScriptAccessCode) ){
    $str = 'AabcdefFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
    $AppsScriptAccessCode = str_shuffle($str);
}
$AppsScriptAccessCode = substr( $AppsScriptAccessCode, 0, 25 );

include $this->topbar_file;

$current_page = $this->settings['current_page'] ?? '1';
$last_page = $this->settings['last_page'] ?? '1';
if( $current_page > $last_page ){
    $current_page = $last_page;
}

$show_tutorial = $saved_data['show_tutorial'] ?? '';
$show_tutorial_checked = $show_tutorial == 'on' ? 'checked' : '';
$show_tutorial_class = $show_tutorial == 'on' ? 'pssg-tutorial-show' : 'pssg-tutorial-hide';


$setup_pages = [
    'sheet_url' => [
        'icon'  => 'pssg_icon-home',
        'file'  => '', //Actually if file empty, then it will search from steps folder
        'title' => __('Sheet URL', 'product-sync-master-sheet' ),
    ],
    'sheet_name' => [
        'icon'  => 'pssg_icon-home',
        'title' => __('Sheet Name', 'product-sync-master-sheet' ),
        'file'  => '' //Actually if file empty, then it will search from steps folder
    ],
    'create_project' => [
        'icon'  => 'pssg_icon-home',
        'title' => __('Create Project', 'product-sync-master-sheet' ),
        'file'  => '' //Actually if file empty, then it will search from steps folder
    ],
    'service_file' => [
        'icon'  => 'pssg_icon-home',
        'title' => __('Service File', 'product-sync-master-sheet' ),
        'file'  => '' //Actually if file empty, then it will search from steps folder
    ],
    'api_key' => [
        'icon'  => 'pssg_icon-home',
        'title' => __('API Key', 'product-sync-master-sheet' ),
        'file'  => '' //Actually if file empty, then it will search from steps folder
    ],

    'enable_sheet_api' => [
        'icon'  => 'pssg_icon-home',
        'title' => __('Enable Sheet API', 'product-sync-master-sheet' ),
        'file'  => '' //Actually if file empty, then it will search from steps folder
    ],
    
    'editor_email' => [
        'icon'  => 'pssg_icon-home',
        'title' => __('Editor Email', 'product-sync-master-sheet' ),
        'file'  => '' //Actually if file empty, then it will search from steps folder
    ],
    
    'app_script' => [
        'icon'  => 'pssg_icon-home',
        'title' => __('AppScript', 'product-sync-master-sheet' ),
        'file'  => '' //Actually if file empty, then it will search from steps folder
    ],
    'add_trigger' => [
        'icon'  => 'pssg_icon-home',
        'title' => __('Trigger', 'product-sync-master-sheet' ),
        'file'  => '' //Actually if file empty, then it will search from steps folder
    ],
    //make an another as finish step
    'finish' => [
        'icon'  => 'pssg_icon-home',
        'title' => __('Finish', 'product-sync-master-sheet' ),
        'file'  => '' //Actually if file empty, then it will search from steps folder
    ],
    
    
];
$setup_pages = apply_filters( 'pssg_admin_setup_pages_arr', $setup_pages, $this );

$setup_pages_key = array_keys( $setup_pages );
$current_page_key = $setup_pages_key[ $current_page - 1 ] ?? 'sheet_url';
$last_page_key = $setup_pages_key[ $last_page - 1 ] ?? 'sheet_url';

?>





<div class="wrap pssg_wrap pssg-content">

    <h1 class="wp-heading "></h1>
    <div class="fieldwrap">
        

        
        <div 
        class="pssg-section-panel no-background setup-wizard-section-wrapper <?php echo esc_attr( $show_tutorial_class ); ?>" 
        data-sync_status="waiting"
        data-last_page_key="<?php echo esc_attr( $last_page_key ); ?>"
        data-current_page_key="<?php echo esc_attr( $current_page_key ); ?>"
        data-current_page="<?php echo esc_attr( $current_page ); ?>"
        id="setup-wizard-section-wrapper"
        >
            <div class="pssg-section-panel setup-wizard-section" id="setup-wizard-section" data-icon="pssg_icon-home">
                <?php
                
                do_action( 'pssg_admin_setup_wizard_before' );

                
                if( is_array( $setup_pages ) && count( $setup_pages ) > 0 ){
                    $count = count( $setup_pages );
                    $divided_count = $count - 1;
                    
                    if($current_page > $count){
                        $current_page = $count;
                    }
                    $progress_percent = ( 100 / $divided_count ) * ($current_page - 1);
                    $display_percent = ( 100 / $count ) * ($last_page);
                    
                ?>
                
                <div 
                class="pssg_progress_container" 
                data-count="<?php echo esc_attr( $count ); ?>"
                >
                    <div class="pssg_progress" style="width: <?php echo esc_attr( $progress_percent ); ?>%;"></div>

                    <?php
                    $serial = 1;
                    foreach( $setup_pages as $key => $page ){
                        $active = ( $serial <= $current_page ) ? 'active' : 'not-active';
                        //to find already one time activated tab
                        $actived = ( $serial <= $last_page ) ? 'already-activated' : 'not-activated-yet';
                        $title = ! empty( $page['title'] ) && is_string( $page['title'] ) ? $page['title'] : ucfirst( $key );
                    ?>
                    <div 
                    data-index="<?php echo esc_attr( $serial ); ?>" 
                    data-page_key="<?php echo esc_attr( $key ); ?>"
                    class="pssg_circle <?php echo esc_attr( $active ); ?> <?php echo esc_attr( $actived ); ?>">
                        <?php echo esc_attr( $serial ); ?>
                        <a class="pssg_circle_title" href="#<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $title ); ?></a>
                    </div>
                    <?php
                    $serial++;
                    }
                    ?>

                </div>
                <div class="modify-setup-wizard-wrapper">
                    <a class="pssg_modify_setup_wizard" href="javascript:void(0);">Modify Setup</a>
                </div>
                <div class="pssg-step-content-wrapper">
                    
                    <form class="setup-wizard-form" action="" method="POST" id="pssg-main-configuration-form">
                        <input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( plugin_basename( PSSG_BASE_FILE ) ) ); ?>">
                        <input type="hidden" name="AppsScriptAccessCode" value="<?php echo esc_attr( $AppsScriptAccessCode ); ?>">
                        <!-- It's also need for Setting wizard special version.  -->
                        <input class="" type="hidden" name="setup_wizard" value="setup_wizard">
                        <input class="" type="hidden" name="form_type" value="setup_wizard">
                        <!-- Need for Reset Form  -->
                        <input class="pssg-hidden-reset-input" type="hidden" name="" value="">
                        <input name="current_page" class="pssg-hidden-input pssg-current-page-input" value="<?php echo esc_attr( $current_page ); ?>"  type="hidden">
                        <input name="last_page" class="pssg-hidden-input pssg-last-page-input" value="<?php echo esc_attr( $last_page ); ?>"  type="hidden">

                        <!-- <button name="configure_submit" type="button" class="pssg-btn pssg-has-icon pssg-btn-tiny">
                            <span><i class="pssg_icon-ok"></i></span>
                            <strong class="">Next</strong>
                        </button> -->



                    <div class="inside-content-page-wrapper">
                        <?php
                        $n_serial = 1;
                        foreach( $setup_pages as $page_key => $page ){
                            $icon = ! empty( $page['icon'] ) && is_string( $page['icon'] ) ? $page['icon'] : 'pssg_icon-link-ext';
                            $file_loc = ! empty( $page['file'] ) && is_file( $page['file'] )  ? $page['file'] : __DIR__ . '/setup-steps/' . $page_key . '.php' ;
                            $title = ! empty( $page['title'] ) && is_string( $page['title'] ) ? $page['title'] : ucfirst( $page_key );
                            // if( $n_serial > $last_page ) continue;
                            
                            if( is_file( $file_loc ) ){
                            $active_class = ( $n_serial == $current_page ) ? 'active' : 'not-active';    
                            ?>
                            <div 
                            data-page_key="<?php echo esc_attr( $page_key ); ?>"
                            data-index="<?php echo esc_attr( $n_serial ); ?>"
                            class="pssg-setup-wizard-panel <?php echo esc_attr( $page_key ); ?>-setup-section <?php echo esc_attr( $active_class ); ?>" 
                            id="pssg-<?php echo esc_attr( $page_key ); ?>-setup-wizard" 
                            data-icon="<?php echo esc_attr( $icon ); ?>">
                                <?php include $file_loc; ?>
                            </div>
                            <?php
                            }
                            $n_serial++;
                        }
                        ?>
                    </div> <!-- inside-content-page-wrapper -->

                    <div class="setup-wizard-final">
                        <div class="pssg-setup-wizard-footer">
                            <p>
                                <?php echo esc_html__('Please complete all the steps before Sync.','product-sync-master-sheet'); ?>
                                <a href="#reset-all" class="pssg-reset-all-setting-wizard">Reset All</a>
                            </p>
                            <div class="pssg-stats-wrapper">
                                <span class="total-percent-n"><?php echo esc_html( $display_percent ); ?>%</span>
                                <span class="error-count-n"></span>
                            </div>
                            <div class="tutorial-switch-wrapper">
                                Display Tutorial  
                                <label class="switch small">
                                    <input name="show_tutorial" type="checkbox" id="pssg-show-tutorial-checkbox" <?php echo esc_attr( $show_tutorial_checked ); ?>>
                                    <div class="slider round"><!--ADDED HTML -->
                                        <span class="on">On</span><span class="off">Off</span><!--END-->
                                    </div>
                                </label>
                            </div>
                        </div>
                        <!-- <button name="configure_submit" type="submit"
                            class="pssg-btn pssg-has-icon configure_submit">
                            <span><i class="pssg_icon-floppy"></i></span>
                            <strong class="form-submit-text">
                            <?php echo esc_html__('Save Change','product-sync-master-sheet');?>
                            </strong>
                        </button> -->
                    </div> <!-- setup-wizard-final -->

                    </form> <!-- pssg-main-configuration-form -->
                </div> <!-- pssg-step-content-wrapper -->
                    

                <?php 
                }
                


                
                do_action( 'pssg_admin_setup_wizard_after' );
                ?>     
                
            </div>
        </div>

    </div>
</div> 