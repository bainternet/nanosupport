<?php
/**
 * Responses meta box
 * 
 * Adding repeating fields as per the responses.
 *
 * @author      nanodesigns
 * @category    Metaboxes
 * @package     NanoSupport
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


function ns_responses_meta_box() {
    add_meta_box(
        'nanosupport-attachment',                   // metabox ID
        esc_html__( 'Attachment', 'nanosupport' ),  // metabox title
        'ns_attachment_specifics',                  // callback function
        'nanosupport',                              // post type (+ CPT)
        'normal',                                   // 'normal', 'advanced', or 'side'
        'high'                                      // 'high', 'core', 'default' or 'low'
    );

    add_meta_box(
        'nanosupport-responses',                    // metabox ID
        esc_html__( 'Responses', 'nanosupport' ),   // metabox title
        'ns_reply_specifics',                       // callback function
        'nanosupport',                              // post type (+ CPT)
        'normal',                                   // 'normal', 'advanced', or 'side'
        'high'                                      // 'high', 'core', 'default' or 'low'
    );

    if( ns_is_user('agent_and_manager') ) :

        add_meta_box(
            'nanosupport-internal-notes',           // metabox ID
            esc_html__( 'Internal Notes', 'nanosupport' ),  // metabox title
            'ns_internal_notes_specifics',          // callback function
            'nanosupport',                          // post type (+ CPT)
            'side',                                 // 'normal', 'advanced', or 'side'
            'default'                               // 'high', 'core', 'default' or 'low'
        );

    endif;

    /**
     * Remove Comment Meta Box
     * Remove the default Comment Meta Box if exists.
     */
    remove_meta_box( 'commentsdiv', 'nanosupport', 'normal' );
}

add_action( 'add_meta_boxes', 'ns_responses_meta_box' );


// Attachment Callback
function ns_attachment_specifics() {
    global $post;
    $meta_data = get_post_meta( $post->ID, '_ns_ticket_attachment' );
    ?>
    <div class="ns-row">
        <div class="ns-box">
            <div class="ns-field">
                <?php
                $number_attachment_allowed = 3; // MAKE IT DYNAMIC
                $meta_loop_count = 1;
                ?>
                <table class="ns-attachment-table">
                    <tbody>
                    <?php if( $meta_data ) { ?>

                        <?php foreach( $meta_data as $key => $attachment_file ) { ?>
                            <tr id="ns-attachment-holder-<?php echo $key; ?>" class="ns-attachment-holder">
                                <td>
                                    <a class="ns-attachment-link" href="<?php echo esc_url($attachment_file['url']); ?>" target="_blank" title="<?php esc_attr_e( 'Open or Download Ticket Attachment', 'nanosupport' ); ?>">
                                        <i class="dashicons dashicons-paperclip"></i> <?php echo basename($attachment_file['url']); ?>
                                    </a>
                                </td>
                                <td>
                                    <?php
                                    $del_attachment_link = add_query_arg( 'del_attachment', $attachment_file['file'], $_SERVER['REQUEST_URI'] );
                                    $del_attachment_link = wp_nonce_url( $del_attachment_link, 'delete-ticket-attachment' );
                                    ?>
                                    <a id="<?php echo $attachment_file['file']; ?>" class="ns-detach-attachment dashicons dashicons-dismiss" href="<?php echo esc_url($del_attachment_link); ?>" title="<?php esc_attr_e( 'Detach this Attachment', 'nanosupport' ); ?>"></a>
                                </td>
                            </tr>
                            <?php $meta_loop_count++; ?>
                        <?php } //endforeach ?>
                    
                    <?php } ?>

                    <?php for( $i = $meta_loop_count; $i <= $number_attachment_allowed; $i++ ) { ?>
                        <tr class="ns-attachment-holder">
                            <td colspan="2">
                                <label class="button button-default ns-btn-file">
                                    <span class="hide-if-no-js"><?php _e( 'Add an Attachment&hellip;', 'nanosupport' ); ?></span>
                                    <input type="file" name="ns_ticket_attachment[]">
                                </label>
                            </td>
                        </tr>
                    <?php } //endfor ?>

                    </tbody>
                </table>

                <div class="ns-conditional-notice hide-if-js"><?php _e( '<button class="button button-primary">Save the ticket</button> to detach the attachment and to add a new attachment', 'nanosupport' ); ?></div>
                
                <p class="description">
                    <?php _e( 'Add an attachment (optional).<br>Accepted file types: <code>.jpg</code>, <code>.jpeg</code>, <code>.png</code>, <code>.tiff</code>, <code>.bmp</code>, <code>.gif</code>, <code>.pdf</code>, <code>.doc</code>, <code>.docx</code>, <code>.rtf</code>, <code>.zip</code>', 'nanosupport' ); ?>
                </p>

            </div> <!-- /.ns-field -->
        </div> <!-- /.ns-box -->
    </div> <!-- /.ns-row -->
    <?php
}


// Responses Callback
function ns_reply_specifics() {
    global $post;

    // Use nonce for verification
    wp_nonce_field( basename( __FILE__ ), 'ns_responses_nonce' );

    $args = array(
        'post_id'   => $post->ID,
        'post_type' => 'nanosupport',
        'status'    => 'approve',
        'orderby'   => 'comment_date',
        'order'     => 'ASC'
    );
    $response_array = get_comments( $args ); ?>
    
    <div class="ns-row ns-holder">

        <?php if( $response_array ) {

            $counter = 1;

            foreach( $response_array as $response ) { ?>
                
                <div class="ns-cards ticket-response-cards">
                    <div class="ns-row">
                        <div class="response-user">
                            <div class="response-head">
                                <h3 class="ticket-head" id="response-<?php echo esc_attr($counter); ?>">
                                    <?php echo $response->comment_author .' &mdash; <small>'. ns_date_time( strtotime($response->comment_date) ) .'</small>'; ?>
                                </h3>
                            </div> <!-- /.response-head -->
                        </div> <!-- /.response-user -->
                        <?php
                        $del_response_link = add_query_arg( 'del_response', $response->comment_ID, $_SERVER['REQUEST_URI'] );
                        $del_response_link = wp_nonce_url( $del_response_link, 'delete-ticket-response' );
                        ?>
                        <div class="response-handle">
                            <?php
                            /* translators: counting number of the response */
                            printf( esc_html__( 'Response #%s', 'nanosupport' ), $counter ); ?>
                            <a id="<?php echo $response->comment_ID; ?>" class="delete-response dashicons dashicons-dismiss" href="<?php echo esc_url($del_response_link); ?>" title="<?php esc_attr_e( 'Delete this Response', 'nanosupport' ); ?>"></a>
                        </div> <!-- /.response-handle -->
                    </div> <!-- /.ns-row -->
                    <div class="ticket-response">
                        <?php echo wpautop( $response->comment_content ); ?>
                    </div>
                </div>
                
                <?php
            $counter++;
            } //endforeach ?>

        <?php } //endif ?>

        <?php global $current_user; ?>

        <?php $ticket_meta = ns_get_ticket_meta( $post->ID ); ?>

        <?php
        if( 'pending' === $ticket_meta['status']['value'] ) {

            echo '<div class="ns-alert ns-alert-info" role="alert">';
                echo '<i class="dashicons dashicons-info"></i>&nbsp;';
                echo wp_kses( __( 'You cannot add response to a pending ticket. <strong>Publish</strong> it first.', 'nanosupport' ), array('strong' => array()) );
            echo '</div>';

        } elseif( 'solved' === $ticket_meta['status']['value'] ) {

            echo '<div class="ns-alert ns-alert-success" role="alert">';
                echo '<i class="dashicons dashicons-info"></i>&nbsp;';
                echo wp_kses( __( 'Ticket is already solved. <strong>ReOpen</strong> it to add new response.', 'nanosupport' ), array('strong' => array()) );
            echo '</div>';

        } else { ?>

            <div class="ns-cards ns-feedback">
                <div class="ns-row">
                    <div class="response-user">
                        <div class="response-head">
                            <h3 class="ticket-head" id="new-response">
                                <?php printf( esc_html__( 'Responding as: %s', 'nanosupport' ), $current_user->display_name ); ?>
                            </h3>
                        </div> <!-- /.response-head -->
                    </div>
                    <div class="response-handle">
                        <?php echo ns_date_time( current_time('timestamp') ); ?>
                    </div>
                </div> <!-- /.ns-row -->
                <div class="ns-feedback-form">

                    <div class="ns-form-group">
                        <textarea class="ns-field-item" name="ns_new_response" id="ns-new-response" rows="6" aria-label="<?php esc_attr_e('Write down the response to the ticket', 'nanosupport'); ?>" placeholder="<?php esc_attr_e('Write down your response', 'nanosupport'); ?>"><?php echo isset($_POST['ns_new_response']) ? $_POST['ns_new_response'] : ''; ?></textarea>
                    </div> <!-- /.ns-form-group -->
                    <button id="ns-save-response" class="button button-large button-primary ns-btn"><?php esc_html_e( 'Save Response', 'nanosupport' ); ?></button>

                </div>
            </div> <!-- /.ns-feedback-form -->

        <?php
        } //endif( 'pending' === $ticket_meta['value'] ) { ?>

    </div> <!-- .ns-holder -->

    <?php
}

// Internal Notes Callback
function ns_internal_notes_specifics() {
    global $post;
    $meta_data = get_post_meta( $post->ID, 'ns_internal_note', true );
    ?>
    <div class="ns-row">
        <div class="ns-box">
            <div class="ns-field">
                <textarea class="ns-field-item" name="ns_internal_note" id="ns-internal-note" rows="5" placeholder="<?php esc_attr_e( 'Write down any internal note to pass to any Support Agent internally.', 'nanosupport' ); ?>"><?php echo isset($_POST['ns_internal_note']) ? $_POST['ns_internal_note'] : esc_html( $meta_data ); ?></textarea>
                <?php echo '<p class="description">'. esc_html__( 'Internal notes are not visible to Support Seekers. It&rsquo;s to pass important notes within the support team.', 'nanosupport' ) .'</p>'; ?>
            </div> <!-- /.ns-field -->
        </div> <!-- /.ns-box -->
    </div> <!-- /.ns-row -->
    <?php
}


/**
 * NS Ticket Control Meta Fields.
 *
 * Ticket controlling elements in a custom meta box, hooked on to the
 * admin edit post page, on the side meta widgets.
 *
 * @since  1.0.0
 * 
 * hooked: 'post_submitbox_misc_actions' (10)
 * -----------------------------------------------------------------------
 */
function ns_control_specifics() {
    global $post;

    if( 'nanosupport' === $post->post_type ) :

        // Use nonce for verification
        wp_nonce_field( basename( __FILE__ ), 'ns_control_nonce' );

        //get meta values from db
        $_ns_ticket_status   = get_post_meta( $post->ID, '_ns_ticket_status', true );
        $_ns_ticket_priority = get_post_meta( $post->ID, '_ns_ticket_priority', true );
        $_ns_ticket_agent    = get_post_meta( $post->ID, '_ns_ticket_agent', true );

        //set default values
        $_ns_ticket_status   = ! empty($_ns_ticket_status)    ? $_ns_ticket_status     : 'open';
        $_ns_ticket_priority = ! empty($_ns_ticket_priority)  ? $_ns_ticket_priority   : 'low';
        $_ns_ticket_agent    = ! empty($_ns_ticket_agent)     ? $_ns_ticket_agent      : '';
        ?>

        <div class="row ns-control-holder">

            <div class="ns-row misc-pub-section">
                <div class="ns-head-col">
                    <i class="dashicons dashicons-shield"></i> <?php esc_html_e( 'Ticket Status', 'nanosupport' );
                    echo ns_tooltip( 'ns-ticket-status-tooltip', esc_html__( 'Change the ticket status to track unsolved tickets separately.', 'nanosupport' ), 'left' );
                    ?>
                </div>
                <div class="ns-body-col">
                    <div class="ns-field">
                        <select name="ns_ticket_status" class="ns-field-item" id="ns-ticket-status" aria-describedby="ns-ticket-status-tooltip" required>
                            <option value="open" <?php selected( $_ns_ticket_status, 'open' ); ?>><?php esc_html_e( 'Open', 'nanosupport' ); ?></option>
                            <option value="inspection"<?php selected( $_ns_ticket_status, 'inspection' ); ?>><?php esc_html_e( 'Under Inspection', 'nanosupport' ); ?></option>
                            <option value="solved"<?php selected( $_ns_ticket_status, 'solved' ); ?>><?php esc_html_e( 'Solved', 'nanosupport' ); ?></option>
                        </select>
                    </div> <!-- /.ns-field -->                    
                </div>
            </div> <!-- /.ns-row -->

            <div class="ns-row misc-pub-section">
                <div class="ns-head-col">
                    <i class="dashicons dashicons-sort"></i> <?php esc_html_e( 'Priority', 'nanosupport' );
                    echo ns_tooltip( 'ns-ticket-priority-tooltip', esc_html__( 'Change the priority as per the content and urgency of the ticket.', 'nanosupport' ), 'left' );
                    ?>
                </div>
                <div class="ns-body-col">
                    <div class="ns-field">
                        <select name="ns_ticket_priority" class="ns-field-item" id="ns-ticket-priority" aria-describedby="ns-ticket-priority-tooltip" required>
                            <option value="low" <?php selected( $_ns_ticket_priority, 'low' ); ?>><?php esc_html_e( 'Low', 'nanosupport' ); ?></option>
                            <option value="medium" <?php selected( $_ns_ticket_priority, 'medium' ); ?>><?php esc_html_e( 'Medium', 'nanosupport' ); ?></option>
                            <option value="high" <?php selected( $_ns_ticket_priority, 'high' ); ?>><?php esc_html_e( 'High', 'nanosupport' ); ?></option>
                            <option value="critical" <?php selected( $_ns_ticket_priority, 'critical' ); ?>><?php esc_html_e( 'Critical', 'nanosupport' ); ?></option>
                        </select>
                    </div> <!-- /.ns-field -->                    
                </div>
            </div> <!-- /.ns-row -->

            <?php
            /**
             * Agent assignment is an administrative power.
             */
            if( ns_is_user( 'manager' ) ) : ?>

                <div class="ns-row misc-pub-section">
                    <div class="ns-head-col">
                        <i class="dashicons dashicons-businessman"></i> <?php esc_html_e( 'Agent', 'nanosupport' );
                        echo ns_tooltip( 'ns-ticket-agent-tooltip', esc_html__( 'Choose agent to assign the ticket. You can make an agent by editing the user from their user profile.', 'nanosupport' ), 'left' );
                        ?>
                    </div>
                    <div class="ns-body-col">
                        <?php
                        $agent_query = new WP_User_Query( array(
                                'meta_key'      => 'ns_make_agent',
                                'meta_value'    => 1,
                                'orderby'       => 'display_name'
                            ) );
                        ?>
                        <div class="ns-field">
                            <select name="ns_ticket_agent" class="ns-field-item ns-auto-select-search" id="ns-ticket-agent" aria-describedby="ns-ticket-agent-tooltip">
                                <?php
                                if ( ! empty( $agent_query->results ) ) {
                                    echo '<option value="">'. esc_html__( 'Assign an agent', 'nanosupport' ) .'</option>';
                                    foreach ( $agent_query->results as $user ) {
                                        echo '<option value="'. $user->ID .'" '. selected( $_ns_ticket_agent, $user->ID ) .'>'. $user->display_name .'</option>';
                                    }
                                } else {
                                    echo '<option value="">'. esc_html__( 'No agent found', 'nanosupport' ) .'</option>';
                                }
                                ?>
                            </select>
                        </div> <!-- /.ns-field -->                    
                    </div>
                </div> <!-- /.ns-row -->

            <?php endif; ?>

        </div> <!-- .ns-control-holder -->
        <?php

    endif;
}

add_action('post_submitbox_misc_actions', 'ns_control_specifics');


// Save the Data
function ns_save_nanosupport_meta_data( $post_id ) {
     
    // verify nonce
    if (! isset($_POST['ns_responses_nonce']) || ! wp_verify_nonce($_POST['ns_responses_nonce'], basename(__FILE__)))
        return $post_id;
    
    // check autosave
    if ( wp_is_post_autosave( $post_id ) )
        return $post_id;

    //check post revision
    if ( wp_is_post_revision( $post_id ) )
        return $post_id;
    
    // check permissions
    if ( 'nanosupport' === $_POST['post_type'] ) {
        if ( ! current_user_can( 'edit_nanosupport', $post_id ) ) {
            return $post_id;
        }
    }

    global $current_user;

    /**
     * Save Attachment.
     * ...
     */
    $has_new_attachment = false;
    $new_attachments    = array();
    
    // New attachments.
    if( isset($_FILES['ns_ticket_attachment']) ) {

        $allowed_mime_types = NSFileUploader()->allowed_attachment_mime_types();

        foreach( $_FILES['ns_ticket_attachment']['name'] as $key => $attachment_name ) {

            if( 0 == $_FILES['ns_ticket_attachment']['size'][$key] && 0 == $_FILES['ns_ticket_attachment']['error'][0] ) {
                // empty file.
            } else {
                if ( ! in_array( $_FILES['ns_ticket_attachment']['type'][$key], $allowed_mime_types ) ) {
                    $ns_errors[] = __( 'The file you are trying to upload is not a valid type. Please refer to the instruction', 'nanosupport' );
                } else {

                    // Make the wp_handle_upload() function available.
                    if ( ! function_exists( 'wp_handle_upload' ) ) {
                        require_once( ABSPATH .'wp-admin/includes/file.php' );
                    }

                    $upload_overrides = array( 'test_form' => false );

                    // Set custom setup.
                    add_filter( 'upload_dir',   array( 'NSFileUploader', 'ticket_attachment_dir' ) ); // Register NS path override.
                    add_filter( 'upload_mimes', array( 'NSFileUploader', 'allowed_attachment_mime_types' ) ); //Register NS accepted file types

                        // WordPress will move the file to 'uploads/nanosupport'.
                        $attachment_file = wp_upload_bits( $_FILES['ns_ticket_attachment']['name'][$key], null, file_get_contents($_FILES['ns_ticket_attachment']['tmp_name'][$key]) );

                    // Set everything back to normal.
                    remove_filter( 'upload_dir',    array( 'NSFileUploader', 'ticket_attachment_dir' ) );
                    remove_filter( 'upload_mimes',  array( 'NSFileUploader', 'allowed_attachment_mime_types' ) );

                    if( isset($attachment_file['error']) && $attachment_file['error'] != 0 ) {

                        // Display the error.
                        wp_die( printf( __( 'Error uploading your file. Error due to: %s', 'nanosupport' ), $upload['error'] ) );

                    } else {

                        // Prepare the attachment now.
                        $new_attachments[]  = $attachment_file;

                        $has_new_attachment = true;

                    }
                }
            }

        } //endforeach
        
    } //endif isset($_FILES['ns_ticket_attachment'])


    /**
     * Save NanoSupport Ticket Meta.
     * ...
     */
    $ns_ticket_status   = $_POST['ns_ticket_status'];
    $ns_ticket_priority = $_POST['ns_ticket_priority'];
    $ns_ticket_agent    = $_POST['ns_ticket_agent'];

    update_post_meta( $post_id, '_ns_ticket_status',   sanitize_text_field( $ns_ticket_status ) );
    update_post_meta( $post_id, '_ns_ticket_priority', sanitize_text_field( $ns_ticket_priority ) );
    if( ns_is_user('manager') ) {
        $existing_agent = (int) get_post_meta( $post_id, '_ns_ticket_agent', true );

        /**
         * -----------------------------------------------------------------------
         * HOOK : FILTER HOOK
         * nanosupport_notify_agent_assignment
         * 
         * @since  1.0.0
         *
         * @param boolean  True to send email notification on ticket assignment.
         * -----------------------------------------------------------------------
         */
        if( apply_filters( 'nanosupport_notify_agent_assignment', true ) ) {

            // Notify the support agent that, they're assigned for the first time
            // if there's no agent assigned already, but we're going to add a new one, or
            // if we're changing agent from existing to someone new
            if( (! empty($ns_ticket_agent) && empty($existing_agent)) || $existing_agent !== absint( $ns_ticket_agent ) ) {
                ns_notify_agent_assignment( $ns_ticket_agent, $post_id );
            }

        } //endif( apply_filters(...))

        // Add a ticket agent always, if assigned
        update_post_meta( $post_id, '_ns_ticket_agent', absint( $ns_ticket_agent ) );
    }

    if( $has_new_attachment ){
        $existing_data = get_post_meta( $post_id, '_ns_ticket_attachment' );
        foreach( $new_attachments as $attachment ) {
            if( ! in_array($existing_file, $existing_data) ) {
                add_post_meta( $post_id, '_ns_ticket_attachment', $attachment );
            }
        }
    }

    
    /**
     * Save Response.
     * ...
     */
    $new_response = isset($_POST['ns_new_response']) && ! empty($_POST['ns_new_response']) ? $_POST['ns_new_response'] : false;

    if( $new_response ) :

        /**
         * Sanitize ticket response content
         * @var string
         */
        $new_response = wp_kses( $new_response, ns_allowed_html() );

        //Insert new response as a comment and get the comment ID
        $commentdata = array(
            'comment_post_ID'       => absint( $post_id )   ,
            'comment_author'        => wp_strip_all_tags( $current_user->display_name ), 
            'comment_author_email'  => sanitize_email( $current_user->user_email ),
            'comment_author_url'    => esc_url( $current_user->user_url ),
            'comment_content'       => $new_response,
            'comment_type'          => 'nanosupport_response',
            'comment_parent'        => 0,
            'user_id'               => absint( $current_user->ID ),
        );

        $comment_id = wp_new_comment( $commentdata );

    endif;


    /**
     * Save Internal Notes.
     * ...
     */
    $internal_note          = $_POST['ns_internal_note'];
    $existing_internal_note = get_post_meta( $post_id, 'ns_internal_note', true );

    if( $internal_note && $internal_note != $existing_internal_note ) {
        // Sanitize internal note
        $internal_note = wp_kses( $internal_note, ns_allowed_html() );

        update_post_meta( $post_id, 'ns_internal_note', $internal_note );
    } elseif( '' == $internal_note && $existing_internal_note ) {
        delete_post_meta( $post_id, 'ns_internal_note', $existing_internal_note );
    }
}

add_action( 'save_post',        'ns_save_nanosupport_meta_data' );
add_action( 'new_to_publish',   'ns_save_nanosupport_meta_data' );
