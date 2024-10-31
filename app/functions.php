<?php

use PSSG_Sync_Sheet\App\Service\Products;
use PSSG_Sync_Sheet\App\Http\Sheet;
use PSSG_Sync_Sheet\App\Http\Api;

/**
 * Only fir developer 
 * @author Saiful Islam <codersaiful@gmail.com>
 */
if( ! function_exists( 'pssg_dd' ) ){
    function pssg_dd( ...$vals ){
        foreach( $vals as $val ){
            echo '<pre>';
            var_dump($val);
            echo '</pre>';
        }
    }
}

/**
 * Only fir developer 
 * @author Saiful Islam <codersaiful@gmail.com>
 */
if( ! function_exists( 'dd' ) ){
    function dd( ...$vals ){
        foreach( $vals as $val ){
            echo '<pre>';
            var_dump($val);
            echo '</pre>';
        }
    }
}

if( ! function_exists('pssg_doc_link') ){
    /**
     * This function will add helper doc
     * @since 1.0.0
     * @author Saiful Islam<codersaiful@gmail.com>
     */
    function pssg_doc_link( $url, $title = '', $icon = 'pssg_icon-link' ){
        if( empty( $title ) ){
            $title = __( 'Helper doc', 'product-sync-master-sheet' );
        }
        ?>
            <a href="<?php echo esc_url( $url ); ?>" target="_blank" class="pssg-doc-link">
                <i class="<?php echo esc_attr( $icon ); ?>"></i>
                <?php echo esc_html( $title ); ?>
            </a>
        <?php
    }
}


/**
 * Depreciated
 *
 * @param string $error_key
 * @param array $errors
 * @return void
 */
function pssg_error_display($error_key,&$errors)
{
    if( ! is_array( $errors ) ) return;
    if( ! empty( $errors[$error_key] ) && is_string( $errors[$error_key] ) ){
?>
<p class="pssg-error-msg"><?php echo esc_html( $errors[$error_key] ); ?></p>
<?php 
    }
}

/**
 * Error will handle by jQuery
 * we will add here a p tag just to insert error here
 *
 * @param string $error_key
 * @return string 
 */
function pssg_error_msg($error_key = '')
{
    if( empty( $error_key ) ) return;

?>
<div class="pssg-error-message pssg-error-message-<?php echo esc_attr( $error_key ); ?>"></div>
<?php 

}

/**
 * Actually If we need to load any where local file content,
 * We will include it and return as ob_get_clean(); 
 *
 * @param string $local_file_dir
 * @return string
 */
function pssg_file_get_content( $local_file_dir )
{
    if( ! is_file( $local_file_dir ) ) return '';
    ob_start();
    include $local_file_dir;
    return ob_get_clean();
}

/**
 * Generates the HTML for a "Next Step" button and an optional "Learn More" button.
 *
 * @param bool $learn_btn Whether to display the "Learn More" button. Default is true.
 * @param string $next_text The text to display on the "Next Step" button. If empty, the default text "Next Step" will be used.
 * @param string $learn_text The text to display on the "Learn More" button. If empty, the default text "Learn More" will be used.
 * @return void
 */
function pssg_get_next_button( $learn_btn = true,$learn_text = '', $next_text = '' )
{
    $next_text = ! empty( $next_text ) ? $next_text : __( 'Next Step', 'product-sync-master-sheet' );
    $learn_text = ! empty( $learn_text ) ? $learn_text : __( 'Screenshot', 'product-sync-master-sheet' );
    ?>
    <div class="pssg-extra-section">
        <div class="pssg-button-group">
            <a href="#show-next" class="pssg-btn pssg-has-icon pssg-btn-tiny pssg-setup-wizard-next-button">
                <span><i class="pssg_icon-ok"></i></span>
                <?php echo esc_html( $next_text ); ?>
            </a>
            <?php
            if( $learn_btn ){ ?>
            <a href="#" class="pssg-show-tips pssg-btn pssg-has-icon pssg-btn-tiny"><span><i class="pssg_icon-down-open"></i></span><?php echo esc_html( $learn_text ); ?></a>
            <?php  } ?>
        </div>
    </div>
    <?php 
}

/**
 * Renders a YouTube video embed with customizable attributes.
 *
 * @param string $video_id_or_link The YouTube video ID to embed. Default is 'n_ea3devnlg'. You also can input Video URL here.
 * @param array $attr_args An array of attributes for the video embed. Default is ['class_col' => 'col-md-6'].
 *                         The 'class_col' attribute sets the CSS class for the video section.
 *                         The 'q' attribute sets the search query for the embed.
 *                         The 'width' attribute sets the width of the embed.
 *                         The 'height' attribute sets the height of the embed.
 * @return void
 */
function pssg_youtube_video( $video_id_or_link = 'n_ea3devnlg', $attr_args = ['class_col' => 'col-md-6'] )
{
    if( empty( $video_id_or_link ) ) return;
    // Check if the input is a full URL
    if (filter_var($video_id_or_link, FILTER_VALIDATE_URL)) {
        // Use a regular expression to extract the video ID
        preg_match('/v=([^&]+)/', $video_id_or_link, $matches);
        if (isset($matches[1])) {
            $video_id = $matches[1];
        } else {
            // Handle error case if ID is not found in URL
            $video_id = null;
        }
    } else {
        // Assume the input is a video ID directly
        $video_id = $video_id_or_link;
    }
    if( empty( $video_id ) ) return;
    ?>
    <div class="<?php echo esc_attr( $attr_args['class_col'] ?? 'col-md-6' ); ?> pssg-video-section">
            <div class="pssg-youtube-video-wrap">
                <iframe 
                src="https://www.youtube-nocookie.com/embed/<?php echo esc_attr( $video_id ); ?>?vq=hd1080&autoplay=1&loop=1&rel=0&cc_load_policy=1&iv_load_policy=3&fs=0&color=white&controls=0&disablekb=1&q=<?php echo esc_attr( $attr_args['q'] ?? 'codeastrology' ); ?>" 
                width="<?php echo esc_attr( $attr_args['width'] ?? '560' ); ?>" 
                height="<?php echo esc_attr( $attr_args['height'] ?? '315' ); ?>" 
                frameborder="0"></iframe>
            </div>
        </div>
    <?php
}

function pssg_tuts_img_render( $img_file, $alt = '', $base_folder_url = PSSG_ASSETS_URL . 'images/tuts/' )
{
    $full_img_url = $base_folder_url . $img_file;
    if( ! filter_var( $full_img_url, FILTER_VALIDATE_URL ) ) return;
    ?>
    <img title="<?php echo esc_attr( $alt ); ?>" 
    alt="<?php echo esc_attr( $alt ); ?>" 
    class="pssg-tuts-img" 
    src="<?php echo esc_url( $full_img_url ); ?>">
    <?php 
}   


/**
 * Render Google Sheet URL
 *
 * @param string $sheet_url
 * @return void
 */
function pssg_sheet_url_render( $sheet_url ){
    if ( ! empty( $sheet_url ) && filter_var($sheet_url, FILTER_VALIDATE_URL) == true ) { 
    ?>
    <a href="<?php echo esc_url( $sheet_url ); ?>" class="pssg-btn pssg-has-icon checkout-sheet-url pssg-btn-small" target="_blank">
        <span><i class="pssg_icon-doc-text-inv-1"></i></span>
        <strong class="form-submit-text">
            <?php echo esc_html__( 'Google Sheet', 'product-sync-master-sheet' ); ?>
        </strong>
    </a>
    <?php 
    }
}

function pssg_sheet_dropdown_render( $sheet_name = '' ){
    $Sheet = Sheet::init();
    $sheet_details_error = $Sheet->get_online_sheet_details_error();
    $sheet_details = $Sheet->get_online_sheet_details();
    if( $sheet_details_error && isset( $sheet_details_error['error_status'] ) && $sheet_details_error['error_status'] == 'INVALID' ){
        $status = $sheet_details_error['status'] ?? '';
        $message = '';
        switch( $status ){
            case 'PERMISSION_DENIED':
                $message = __( 'Permission denied. Please check Enable Sheet API.', 'product-sync-master-sheet' );
                break;
            case 'INVALID_ARGUMENT':
                $message = __( 'Invalid argument. Please check API_KEY.', 'product-sync-master-sheet' );
                break;

        }
        echo '<p class="pssg-warning-msg">'. esc_html( $message ) .'</p>';
        echo '<p class="pssg-error-msg">'. esc_html( $sheet_details_error['message'] ) .'</p>';
        return;
    }
// $sheet_details = $Sheet->get_online_sheet_details();

    $seetings = $Sheet->Admin_Base_Settings;
    $current_sheet_name = $seetings['sheet_name'] ?? '';

    if( ! empty( $sheet_name ) ){
        $current_sheet_name = $sheet_name;
    }

    $sheet_names = $Sheet->get_online_sheets_name();
    if( is_array( $sheet_names ) && count( $sheet_names ) > 0 ){ ?>
        <div class="pssg-field-wrapper-single">
            <select name="sheet_name" class="ua_select ua_input_select " id="pssg-input-sheet_name">
                <option><?php echo esc_html( __( 'Select Sheet', 'product-sync-master-sheet' ) ); ?></option>
                <?php
                foreach( $sheet_names as $sheet_name => $details ){
                    $gid = $details['gid'] ?? '';
                    $rowCount = $details['rowCount'] ?? '';
                ?>
                    <option 
                    value="<?php echo esc_attr( $sheet_name ); ?>" <?php selected( $sheet_name, $current_sheet_name ); ?>>
                        <?php echo esc_html( $sheet_name ); ?> | #gid=<?php echo esc_attr( $gid ); ?> | rowCount:<?php echo esc_html( $rowCount ); ?>
                    </option>
                <?php
                }
                ?>
            </select>
        </div>
    <?php }
}