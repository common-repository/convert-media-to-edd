<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Reference: http://stackoverflow.com/a/3307762/2706988
 *
 * @param $args array
 * @return array
 */
function cmedd_get_images( $args = null ) {
	$defaults = array(
		'post_type'             => 'attachment',
		'posts_per_page'        => 10,
		'offset'                => 0,
		'post_status'           => 'inherit',
		'post__in'              => array(),
		'post_mime_filter'      => 'image', // @see cmedd_post_mime_filter()
		'post_parent'           => null,
		'suppress_filters'      => false,
		'no_found_rows'         => true,
		'ignore_sticky_posts'   => true
	);

	$args = wp_parse_args( $args, $defaults );

	/**
	 * We are checking for post__in here in case of array is empty,
	 * because according to Ticket #28099: Passing an empty array to
	 * post__in will return has_posts() as true (and all posts will be returned).
	 * Logic should be used before hand to determine if WP_Query
	 * should be used in the event that the array being passed to
	 * post__in is empty.
	 */
	if ( empty( $args['post__in'] ) ) {
		return array();
	}

	$attachment = new WP_Query;
	return $attachment->query( $args );
}

/**
 * Reference: http://wordpress.stackexchange.com/a/96446/36349
 *
 * @param $where string
 * @param $wp_query WP_Query
 * @return string
 */
function cmedd_post_mime_filter( $where, &$wp_query ) {
	global $wpdb;

	if ( $mime_term = $wp_query->get( 'post_mime_filter' ) ) {
		$where .= ' AND ' . $wpdb->posts . '.post_mime_type LIKE \'' . $wpdb->esc_like( $mime_term ) . '%\'';
	}
	return $where;
}
add_filter( 'posts_where', 'cmedd_post_mime_filter', 10, 2 );

/**
 * @return number
 */
function cmedd_total_images() {
	$posts = wp_count_attachments( 'image%' );
	return array_sum( (array) $posts);
}

/**
 * Parse media ids each separated on new lines to array
 * @param string $ids
 * @return array
 */
function cmedd_get_attach_ids_array( $ids = '' ) {

	/**
	 * Reference: http://stackoverflow.com/a/29471912/2706988
	 */
	$ids = preg_split( "/\\r\\n|\\r|\\n/" , $ids );
	$ids = array_map( 'intval', $ids );
	$ids = array_filter( $ids );

	return $ids;
}

/**
 * Hook method for task runner
 */
add_action( 'convert_media_edd', array ( 'CMEDD_Cron', 'start' ) );

/**
 * New file contents
 */
add_action( 'cmedd_cron_started', array( 'CMEDD_Log', '_new' ) );

/**
 * Close open file pointer log file
 */
add_action( 'cmedd_cron_completed', array( 'CMEDD_Log', 'close' ) );
add_action( 'cmedd_cron_before_stop', array( 'CMEDD_Log', 'close' ) );

/**
 * Plugin Help Tab
 */
function add_help_screen_to_cmedd_options(){

    //get the current screen object
    $current_screen = get_current_screen();
    //show only on book listing page
    if( $current_screen->base == 'toplevel_page_cmedd-options' ) {

    	$content = '';
        $content .= '<p>This plugin will create an <strong>EDD Download</strong> from every image added via Media Attachment.</p>';
       
        $current_screen->add_help_tab( array(
                'id'        => 'cmedd-options_Overview',
                'title'     => __('Overview'),
                'content'   => $content
            )
        );

        $content = '';
        $content .= '<p>Once clicked, it will send request to execute cron job immediately.</p>';
        $content .= '<p>Any errors before processing cron job (invalid nonce) will be displayed on top of the page as notice.</p>';
        $content .= '<p>After Cron successfully executed, the updated job status will be displayed on top of the page as notice.</p>';
        
        $current_screen->add_help_tab( array(
                'id'        => 'cmedd-options_Execute_btn',
                'title'     => __('Execute Button'),
                'content'   => $content
            )
        );

        $content = '';
        $content .= '<p>This button is important when you want to run the cron job again and clear out the previous pending/completed job.</p>';
        $content .= '<p>The reset button will only be clickable when any previous job was scheduled.</p>';
        $content .= '<p>When you Reset the previous job, it\'s status entries (total/processed/status) will be removed (not the downloads that\'s been created), which is used to track running job.This allows to schedule new job when clicked on Execute</p>';
        
        $current_screen->add_help_tab( array(
                'id'        => 'cmedd-options_Reset_btn',
                'title'     => __('Reset Button'),
                'content'   => $content
            )
        );
    }
}
add_action('admin_head', 'add_help_screen_to_cmedd_options');
