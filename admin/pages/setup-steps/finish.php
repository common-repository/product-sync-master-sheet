<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 
// return; 
$limit = $this->Products->get_one_load_limit();
$per_page = $this->Products->posts_per_page;
$checkout_sheet_url = $saved_data['sheet_url'] ?? '';

$stats = $this->Products->get_stats();
$founded_posts = $stats['found_posts'] ?? false;

$spreadsheet_id = $saved_data['spreadsheet_id'] ?? '';
// $sheet_detailse = $this->Sheet->get_online_sheet_details_error();

// $sheet_details = $this->Sheet->get_online_sheet_details();
              
               
// $sheet_names = $this->Sheet->get_online_sheets_name();
$sheet_names = [];// $this->Sheet->get_online_sheets_name();

$current_sheet_name = $saved_data['sheet_name'] ?? '';
?>
<div class="form-field-wrapper">
    <div class="field-wrapper-inside finish-wrapper">
        <div class="pssg-form-control">
            <div class="pssg-form-control-inside">
                <div class="form-field col-lg-12">

                        <div class="pssg-field-wrapper-single" id="pssg-sheet-dropdown-wrapper">
                            <?php 
                            // pssg_sheet_dropdown_render(); 
                            ?>
                        </div>
                    
                </div>
            </div>
                    <div class="form-label col-lg-12">

                    <?php
                    // dd( $this->Sheet->get_current_sheet_name() );
                    

                    $sync_wrapper_class = '';
                    $submit_errors = $this->get_form_submited_errors();
                    
                    if( count( $submit_errors ) > 0){
                        $sync_wrapper_class = 'syncronize-submit-errors';
                    }
                    ?>
                        <div class="submit-errors-wrapper">
                        <?php
                        foreach($submit_errors as $key => $subm_error){
                        ?>
                        <p class="subm-errs-<?php echo esc_attr( $key ); ?>"><i class="pssg_icon-info-circled"></i> <?php echo esc_html( $subm_error ); ?></p>
                        <?php
                        }
                        ?>
                        </div>
                        <div class="syncronize-wrapper <?php echo esc_attr( $sync_wrapper_class ); ?>">
                            <a id="pssg-syncronize-button" class="pssg-btn pssg-has-icon" data-limit="<?php echo esc_attr( $limit ); ?>" data-per_page="<?php echo esc_attr( $per_page ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( plugin_basename( PSSG_BASE_FILE ) ) ); ?>">
                                <span><i class="pssg_icon-spin3"></i></span>
                                <strong class="sync-btn-txt"><?php echo esc_html__( 'Syncronize Sheet', 'product-sync-master-sheet' ); ?></strong>
                                <i class="product_count"></i>
                            </a>

                            
                            <a id="pssg-clear-sheet-button" class="pssg-btn pssg-has-icon">
                                <span><i class="pssg_icon-trash-empty"></i></span>
                                <strong class="delete-btn-txt"><?php echo esc_html__( 'Delete all from Sheet', 'product-sync-master-sheet' ); ?></strong>
                            </a>

                            <div class="message-showing-area">
                            </div>
                            <div class="errors-details-area">
                                <p class="error-code">
                                </p>
                                <div class="errors-lists"></div>
                            </div>
                            <div class="sheet-details-some-link">
                            <a href="<?php echo esc_url( $checkout_sheet_url ); ?>" class="checkout-sheet-url" target="_blank"><i class="pssg_icon-doc-text-inv-1"></i><?php echo esc_html__( 'Your Sheet', 'product-sync-master-sheet' ); ?></a>
                            </div>

                        </div>

                        <div class="pssg-basic-instruction-syncronise">
                            <div class="instruction-area-wrapper">
                                
                                <div class="col-md-8">
                                    <h1>Basic Information</h1>
                                    <div class="instruction-area">
                                        <?php 
                                        unset($stats['max_num_pages']);
                                        unset($stats['posts_per_page']);
                                        unset($stats['max_num_pages']);
                                        if(is_array( $stats )){
                                            foreach($stats as $key => $stat){
                                                echo '<div><strong>'.esc_html( $key ).'</strong>: '.esc_html( $stat ).'</div>';
                                            }
                                        }
                                        
                                        ?>
                                    </div>
                                    <div class="instruction-area">
                                        
                                        <?php 
         
                                        if(is_array( $sheet_names ) && count( $sheet_names ) > 0){
                                            echo wp_kses_post('<h1>Sheet:RowsCount on your Spreadsheet</h1>');
                                            foreach($sheet_names as $key => $my_sheet){
                                                if(! isset( $my_sheet['title'] )) continue;
                                                $style = '';
                                                if( $key == $current_sheet_name){
                                                    $style = 'style="font-weight:bold;color: green;"';
                                                }
                                                $title = $my_sheet['title'];
                                                $rowCount = $my_sheet['rowCount'];
                                                // $link = $my_sheet['gid'];
                                                echo '<div '.$style.'><strong>'.esc_html( $key ).'</strong>: '.esc_html( $rowCount ).'</div>';
                                            }
                                        }
                                        
                                        ?>
                                    </div>
                                    <?php
                                    if( ! $this->is_premium ){
                                    ?>
                                    <div class="instruction-area">
                                        <h3 style="color: #d00;margin: 10px 0;"><?php echo esc_html__( 'In Free Version, You able to Sync 150 Products.', 'product-sync-master-sheet' ); ?> | <strong>Current Products: <?php echo esc_html( $founded_posts ); ?><strong></h4>
                                        <a class="pssg-button reverse" 
                                            href="https://codeastrology.com/downloads/product-sync-master-sheet-premium/" 
                                            target="_blank">
                                            <i class="pssg_icon-heart-filled"></i>
                                            <?php echo esc_html__( 'Get Premium Version', 'product-sync-master-sheet' ); ?>
                                        </a>
                                    </div>
                                    <?php 
                                    }
                                    ?>
                                    
                                </div>
                            </div> <!-- instruction-area-wrapper -->
                        </div> <!-- pssg-basic-instruction-syncronise -->


                     </div> <!-- form-label col-lg-12 -->

                </div>
    </div>
    
</div>

