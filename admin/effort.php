<?php
/**
 * Administration pages for Effort
 * Submenu
 * Effort to date
 * Add Effort
 * Add Lots of Effort
 *
 * This file is invoked when is_admin()
 
 */


add_action('admin_menu', 'bw_effort_add_pages');

register_activation_hook( __FILE__, 'flush_rewrite_rules' );
register_deactivation_hook( __FILE__, 'flush_rewrite_rules' );  
 

/** 
 * Add Admin menu for the Effort post type
 * Note: To avoid getting the Effort menu duplicated the name of the first submenu item needs to be the same
 * as the main menu item. see http://geekpreneur.blogspot.com/2009/07/getting-unwanted-sub-menu-item-in.html
 * In most "normal" WP menus the main menu gives you the full list
 * 
 */ 
function bw_effort_add_pages() {
  // bw_trace2();
  add_menu_page( 'Effort', 'Effort', 'manage_options', 'bw_effort_menu', 'bw_effort_menu', '' );
  
  add_submenu_page( 'bw_effort_menu', 'Effort to date', 'Effort to date', 'manage_options', 'bw_effort_menu', 'bw_effort_menu');
  add_submenu_page( 'bw_effort_menu', 'Add Effort', 'Add effort', 'manage_options', 'bw_effort_add', 'bw_effort_do_page');
  add_submenu_page( 'bw_effort_menu', 'Add lots of Effort', 'Add lots of effort', 'manage_options', 'bw_effort_add_lots', 'bw_effort_do_lots');
  
  oik_enqueue_stylesheets();
}

/**
 * Find the taxonomy for this effort from the $task
 */
function bw_get_taxonomy( $post_id, $taxonomy="task_type" ) {
  $terms = get_the_terms( $post_id, $taxonomy  );
  $term_name = bw_default_value( $terms[0]->name, "Unknown" );
  bw_trace2( $term_name ); 
  return( $term_name );
} 

if ( !function_exists( "bw_array_add" )) {
function bw_array_add( &$array, $index, $amount ) {
  if ( ! isset($array[$index]) ) {
    $value = $amount;
  } else {
    $value = $array[$index] + $amount;
  }
  return( $value );  
}
}

function bw_array_add2( &$array, $index, $index2, $amount ) {
  if ( ! isset($array[$index][$index2]) ) {
    $value = $amount;
  } else {
    $value = $array[$index][$index2] + $amount;
  }
  return( $value );  
}

   

/* Summarise the effort for this set of posts
*/
function bw_effort_summary( $posts ) {
 
 $effort = array();
 $dates = array();
 $terms = array();
 $months = array();
 $groups = array( );
 foreach ( $posts as $post ) {
   $date = get_post_meta( $post->ID, "_effort_date", TRUE );
   $hrs = get_post_meta( $post->ID, "_effort_hrs", TRUE );
   $task = get_post_meta( $post->ID, "_effort_task_ref", TRUE );
   $date = bw_format_date( $date );
   $month = bw_format_date( $date, "Y-m" );
   
   $effort[] = array( $date, $task, $hrs ); 
     
   $dates[$date] = bw_array_add( $dates, $date, $hrs );

   
   $term = bw_get_taxonomy( $task );
   
   //$terms[$month] = bw_array_add( $terms, $month, $hrs );
   $terms[$month][$term] = bw_array_add2( $terms, $month, $term, $hrs );
   

   //$terms[$date] = bw_array_add( $terms, $date, $hrs );
   $terms[$date][$term] = bw_array_add2( $terms, $date, $term, $hrs );
   
   // $terms[$date][$term] += $hrs;
   
   //bw_array_add( $terms, $month, $hrs );
   //bw_array_add( $terms[$month], $term, $hrs );
   
   //$terms[$month][$term] += $hrs; 
   
   //bw_array_add( $months, $month, $hrs );
   $months[$month] = bw_array_add( $months, $month, $hrs );
   
   $groups[$term] = bw_array_add( $groups, $term, $hrs );
   $groups["Total"] = bw_array_add( $groups, "Total", $hrs );
    
 }
 ksort( &$effort );
 ksort( &$dates );
 ksort( &$months );
 bw_trace2( $effort, "effort array" );
 //p( print_r( $effort ));
 
 stag( "table", "effort" );
 

 stag( "tr" );
 th( "Total" );
 th( "Hours" );
 th( "Paid work" );
 th( "Unpaid work" );
 th( "Overhead" );
 th( "Life" );
 th( "Unknown" );
 etag( "tr" );
 
 stag( "tr" );
 td( "Total" );
 td( bw_array_get( $groups, "Total", "-" ));
 td( bw_array_get( $groups, "Paid work", "-" ));
 td( bw_array_get( $groups, "Unpaid work", "-" ));
 td( bw_array_get( $groups, "Overhead", "-" ));
 td( bw_array_get( $groups, "Life", "-" ));
 td( bw_array_get( $groups, "Unknown", "-" ));
 etag( "tr" );
 

 foreach ( $months as $date => $hrs ) {
   stag( "tr" );
   td( $date, "effort_date" );
   td( $hrs );
   td( bw_array_get( $terms[$date], "Paid work", "-" ));
   td( bw_array_get( $terms[$date], "Unpaid work", "-" ));
   td( bw_array_get( $terms[$date], "Overhead", "-" ));
   td( bw_array_get( $terms[$date], "Life", "-" ));
   td( bw_array_get( $terms[$date], "Unknown", "-" ));
   etag( "tr" );
   
 } 
 
 
 foreach ( $dates as $date => $hrs ) {
   stag( "tr" );
   td( $date, "effort_date" );
   td( $hrs );
   td( bw_array_get( $terms[$date], "Paid work", "-" ));
   td( bw_array_get( $terms[$date], "Unpaid work", "-" ));
   td( bw_array_get( $terms[$date], "Overhead", "-" ));
   td( bw_array_get( $terms[$date], "Life", "-" ));
   td( bw_array_get( $terms[$date], "Unknown", "-" ));
   etag( "tr" );
   
 } 
 
 etag( "table" );
 
}

/**
 *
 */
function bw_effort_menu() {
 p( "Here we will show a summary of the Effort to date... since 1st Jan 2011" );
 $args = array( 'post_type' => 'bw_effort' );
 $posts = bw_get_posts( $args );
 
 bw_effort_summary( $posts );
 
 
 bw_flush();
}

/**
 * When the form action is empty then the same page as before is processed
 * If the user has clicked on the 'submit' button then the field name in the $_REQUEST
 * is set to the value of the button.
 * Q. How does WordPress make this easy for us to deal with? 
 * A. Don't know **?**
 */
function bw_effort_do_page() {
  oik_require( "bobbforms.inc" );
  
  if ( !empty( $_REQUEST['add_effort'] ) ) {
    _bw_add_effort();
  }
  
  sdiv( "column wrap" );
  h2( "Effort" );
  // e( '<form method="post" action="'.plugin_dir_url(__FILE__).'bw_effort.php">' ); 
  e( '<form method="post" action="">' ); 
  
  stag( 'table class="form-table"' );
  //bw_flush();
  //settings_fields('oik_options_options'); 
  
  //bw_textfield( "task", 50, "Task", NULL  );
  //bw_textfield( "date", strlen( "yyyy-mm-dd" ), "Date", "2012-01-02" );
  //bw_textfield( "hrs", "4", "Hours", 0 );
  
  bw_form_field( "task", "noderef", "Task", NULL, array( '#type' => 'bw_task' ));
  $default_date = bw_array_get_dcb( $_POST, 'date', 'Y-m-d', 'date' );
  bw_form_field( "date", "date", "Date", $default_date );
  bw_form_field( "hrs", "numeric", "Hours", 0 );
  
  //  bw_form_field( $field, $data['#field_type'], $data['#title'], $value, $data['#args'] );

  
  bw_tablerow( array( "", "<input type=\"submit\" name=\"add_effort\" value=\"Add Effort\" />") ); 

  etag( "table" ); 			
  etag( "form" );
  
  ediv(); 
  bw_flush();
}


/**
 * Tasks by task_type 
 */
function bw_load_tasks( $task_type="paid,unpaid,overhead,life" ) {
 
  $tasks = bw_load_noderef( array( '#type' => "bw_task", 'task_type' => $task_type )  );
  
  // bw_trace2( $tasks );
  
  stag( 'table class="column span-8"' );
  bw_tablerow( array( "<b>$task_type</b>" ) );
  
  foreach ( $tasks as $noderef => $task ) {
    bw_form_field( "hrs[$noderef]", "numeric", "$task hours ", 0 );
  }
  etag("table" );
}  


/**
 * Process a whole array of Effort for a particular day
 */
function bw_effort_do_lots() {
  oik_require( "bobbforms.inc" );
  
  
  if ( !empty( $_REQUEST['add_lots'] ) ) {
    _bw_add_lots_of_effort();
  }
  wp_enqueue_script( "jquery-ui-datepicker" );
  
  sdiv( "column wrap" );
  h2( "Add Lots of Effort" );
  e( '<form method="post" action="">' ); 
  
  
  // bw_form_field( "task", "noderef", "Task", NULL, array( '#type' => 'bw_task' ));
  $default_date = bw_array_get_dcb( $_POST, 'date', 'Y-m-d', 'date' );
  
  stag( 'table class="column span-6"' );
  
  bw_form_field( "date", "date", "Date", $default_date );
  etag( 'table' );
  
  sediv( "cleared clear" );
  
  
  bw_load_tasks( "paid" );
  bw_load_tasks( "unpaid" );
  bw_load_tasks( "overhead" );
  bw_load_tasks( "life" );
  

  // _bw_sum_stuff();
  sediv( "cleared clear" );
  
  
  e( "<input type=\"submit\" name=\"add_lots\" value=\"Add Effort\" />" );

  etag( "form" );
  
  ediv(); 
  bw_flush();
}


function _bw_sum_stuff() {

  wp_enqueue_script( "calculation", plugin_dir_url( __FILE__)."jquery.calculation.js" );
  wp_enqueue_script( "sumhours", plugin_dir_url( __FILE__)."jquery.sumhours.js" ); 

  bw_form_field( "hrs1", "numeric", "Test Hrs1", 0 );
  bw_form_field( "hrs2", "numeric", "Test Hrs2", 0 );
  bw_form_field( "total_sum", "numeric", "Sum", 0, array( '#readonly' => 'readonly' ) );
}


/**
 * Create an Effort record programmatically
 *
 * @param string $hrs - Hours (rounded to nearest half hour
 * @param string $task - the 'Noderef' to the task
 * @param string $date - the date of the task
 * @returns integer $post_id - the ID of the poist inserted OR a WP_error
 *
 * We convert the date field into a UNIX time() before storing as the metadata
 * This may not happen with Add New or Edit Effort **?**
 * 
 */
function _bw_insert_effort( $hrs, $task, $date ) {
  $date = bw_format_date( $date );
  $title = "Task: $task Date: $date";
  $post = array( 'post_type' => 'bw_effort',
     'post_status' => 'publish',
     'post_title' => $title,
     );
  /* Set metadata fields */
  $_POST['_effort_task_ref'] = $task;
  $_POST['_effort_date'] = strtotime( $date );
  $_POST['_effort_hrs' ] = $hrs;
  $post_id = wp_insert_post( $post, TRUE );
 
  bw_trace2( $post_id );
  return( $post_id );
}
 
/** 
 * Add a custom post: Effort
 *

    C:\apache\htdocs\wordpress\wp-content\plugins\oik\oik-bwtrace.php(112:0) 2012-01-02T22:40:34+00:00 4 bw_trace_plugin_startup _REQUEST Array
    (
        [page] => bw_effort_add
        [task] => 1122
        [date] => 2012-01-02
        [hrs] => 5.5
        [add_effort] => Add Effort
    )  

*/

function _bw_add_effort() {
  $hrs = bw_array_get( $_REQUEST, 'hrs', 0 );
  $task = bw_array_get( $_REQUEST, 'task', NULL );
  $date = bw_array_get( $_REQUEST, 'date', NULL );
  
  $post_id = _bw_insert_effort( $hrs, $task, $date );
  
  bw_trace2( $post_id );
  if ( is_wp_error( $post_id ) ) {
    p( "Error occurred creating Effort" );
       
  } else {
    p( "Added an Effort post: $post_id, task: $task, hours: $hrs, date: $date" );
  }
} 

/**
 * Process the "Add Lots of Effort" page
 * 
 */
function _bw_add_lots_of_effort() {
  $done = 0;
  $tothrs = 0;
  bw_trace2( $_REQUEST );
  $date = bw_array_get( $_REQUEST, 'date', NULL );
  foreach ( $_REQUEST['hrs'] as $task => $hrs ) {
    if ( $hrs > 0 ) {
      $post_id = _bw_insert_effort( $hrs, $task, $date );
      $done++;
      $tothrs += $hrs;
    }
  }
  
  p( "Added Effort for $date: $done time(s). Hours: $tothrs" );
}

