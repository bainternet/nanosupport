<?php
/**
 * System Status Page
 *
 * Showing a system status page for the plugin debugging.
 *
 * @author  	nanodesigns
 * @category 	Debug
 * @package 	NanoSupport
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * System Status submenu page.
 *
 * @since  1.0.0
 * -----------------------------------------------------------------------
 */
function ns_system_status_page() {
    add_submenu_page(
        'edit.php?post_type=nanosupport',           //$parent_slug
        __( 'System Status', 'nanosupport' ),       //$page_title
        __( 'System Status', 'nanosupport' ),       //$menu_title
        'manage_nanosupport',                       //$capability
        'nanosupport-system-status',                //$menu_slug
        'nanosupport_system_status_page_callback'   //callback function
    );
}

add_action( 'admin_menu', 'ns_system_status_page' );


/**
 * Callback: System Status Page
 * 
 * Showing the complete system status page.
 * ...
 */
function nanosupport_system_status_page_callback() {
    global $plugin_page; ?>

    <div class="wrap">
        <h1><i class="ns-icon-nanosupport"></i> <?php esc_html_e( 'NanoSupport System Status', 'nanosupport' ); ?></h1>

        <?php settings_errors(); ?>

        <div class="nanosupport-left-column ns-system-status">

            <?php
            $ns_system_status = new NSSystemStatus();

            $theme          = $ns_system_status->active_theme();
            $parent_theme   = $ns_system_status->active_theme_parent();
            $memory_limit   = $ns_system_status->memory_limit();
            $phpversion     = phpversion();
            $mysql_version  = $ns_system_status->mysql_version();
            $active_plugins = $ns_system_status->get_active_plugins();
            ?>

            <div class="metabox-holder">

                <!-- NANOSUPPORT ENVIRONMENT -->
                <div id="ns-environment" class="postbox">
                    <table class="widefat ns-status-table">
                        <thead>
                            <tr>
                                <th colspan="2"><?php esc_html_e( 'NanoSupport Environment', 'nanosupport' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>

                            <tr>
                                <th><?php esc_html_e( 'WordPress version', 'nanosupport' ); ?></th>
                                <td><?php echo $ns_system_status->wp_version(); ?></td>
                            </tr>

                            <tr>
                                <th><?php esc_html_e( 'NanoSupport version', 'nanosupport' ); ?></th>
                                <td><?php echo NS()->version; ?></td>
                            </tr>

                            <tr>
                                <th><?php esc_html_e( 'Active Theme', 'nanosupport' ); ?></th>
                                <td><?php echo '<strong>'. $theme['theme'] .'</strong> &ndash; '. $theme['version']; ?></td>
                            </tr>

                            <?php if( $parent_theme !== false ) { ?>
                                <tr>
                                    <th><?php esc_html_e( 'Active Theme Parent', 'nanosupport' ); ?></th>
                                    <td><?php echo '<strong>'. $parent_theme['theme'] .'</strong> &ndash; '. $parent_theme['version']; ?></td>
                                </tr>
                            <?php } //endif ?>

                            <tr>
                                <th><?php esc_html_e( 'Debug mode', 'nanosupport' ); ?></th>
                                <td><?php echo $ns_system_status->debug_status(); ?></td>
                            </tr>

                            <tr>
                                <th><?php esc_html_e( 'Multisite?', 'nanosupport' ); ?></th>
                                <td><?php echo $ns_system_status->is_multisite(); ?></td>
                            </tr>

                            <tr>
                                <th><?php esc_html_e( 'Site Language', 'nanosupport' ); ?></th>
                                <td><?php echo get_locale(); ?></td>
                            </tr>

                            <tr>
                                <th><?php esc_html_e( 'Server', 'nanosupport' ); ?></th>
                                <td><?php echo $_SERVER['SERVER_SOFTWARE']; ?></td>
                            </tr>

                            <tr>
                                <th><?php esc_html_e( 'PHP Memory Limit', 'nanosupport' ); ?></th>
                                <td>
                                <?php
                                if( $memory_limit < 67108864 ) {
                                    /* translators: 1. existing PHP memory limit 2. link to increasing PHP memory limit in WordPress */
                                    echo '<mark class="ns-text-danger"><i class="dashicons dashicons-warning"></i>'. sprintf( esc_html__('%1$s &mdash; We recommend setting memory to at least 64MB. See: %2$s', 'nanosupport'), size_format( $memory_limit ), '<a href="https://codex.wordpress.org/Editing_wp-config.php#Increasing_memory_allocated_to_PHP" target="_blank">'. esc_html__( 'Increasing memory allocated to PHP', 'nanosupport' ) .'</a>' ) .'</mark>';
                                } else {
                                    echo '<span class="ns-text-success"><i class="dashicons dashicons-yes"></i>'. size_format( $memory_limit ) .'</span>';
                                }
                                ?>
                                </td>
                            </tr>

                            <tr>
                                <th><?php esc_html_e( 'PHP version', 'nanosupport' ); ?></th>
                                <td>
                                <?php
                                if ( version_compare( $phpversion, '5.6', '<' ) ) {
                                    /* translators: 1. existing PHP version 2. link to WordPress minimum requirements */
                                    echo '<mark class="ns-text-danger"><i class="dashicons dashicons-warning"></i> '. sprintf( esc_html__('%1$s &mdash; We recommend a minimum PHP version of 5.6. See: %2$s', 'nanosupport'), $phpversion, '<a href="https://wordpress.org/about/requirements/" target="_blank">'. esc_html__( 'WordPress minimum requirements', 'nanosupport' ) .'</a>' ) .'</mark>';
                                } else {
                                    echo '<span class="ns-text-success"><i class="dashicons dashicons-yes"></i>'. $phpversion .'</span>';
                                }
                                ?>
                                </td>
                            </tr>

                            <tr>
                                <th><?php esc_html_e( 'MySQL version', 'nanosupport' ); ?></th>
                                <td>
                                <?php
                                if ( version_compare( $mysql_version, '5.6', '<' ) ) {
                                    /* translators: 1. existing MySQL version 2. link to WordPress minimum requirements */
                                    echo '<mark class="ns-text-danger"><i class="dashicons dashicons-warning"></i> '. sprintf( esc_html__('%1$s &mdash; We recommend a minimum MySQL version of 5.6. See: %2$s', 'nanosupport'), $mysql_version, '<a href="https://wordpress.org/about/requirements/" target="_blank">'. esc_html__( 'WordPress minimum requirements', 'nanosupport' ) .'</a>' ) .'</mark>';
                                } else {
                                    echo '<span class="ns-text-success"><i class="dashicons dashicons-yes"></i>'. $mysql_version .'</span>';
                                }
                                ?>
                                </td>
                            </tr>

                        </tbody>
                    </table>
                </div>
                <!-- /#ns-environment -->

                <!-- ACTIVE PLUGINS -->
                <div id="ns-active-plugins" class="postbox">
                    <table class="widefat ns-status-table">
                        <thead>
                            <tr>
                                <th colspan="2">
                                    <?php
                                    /* translators: Active plugin count */
                                    printf( esc_html__( 'Active Plugins (%d)', 'nanosupport' ), $ns_system_status->get_active_plugins_count() );
                                    ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            
                            <?php foreach( $active_plugins as $plugin ) { ?>
                                <tr>
                                    <th>
                                        <?php echo '<strong>'. $plugin['Title'] .'</strong> ('. $plugin['Version'] .')'; ?>
                                    </th>
                                    <td>
                                        <?php
                                        /* translators: Plugin author */
                                        printf( __( 'by <em>%s</em>', 'nanosupport' ), $plugin['Author'] ); ?>
                                    </td>
                                </tr>
                            <?php } //endforeach ?>

                        </tbody>
                    </table>
                </div>
                <!-- /#ns-active-plugins -->

            </div>
            <!-- /.metabox-holder -->

        </div> <!-- /.nanosupport-left-column ns-tools -->
        <div class="nanosupport-right-column">

            <?php require_once '__nanodesigns.php'; ?>

        </div> <!-- /.nanosupport-right-column -->
        <div class="ns-clearfix"></div>

    </div> <!-- /.wrap -->
<?php
}
