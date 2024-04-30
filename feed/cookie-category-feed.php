<?php // (C) Copyright Bobbing Wide 2012

/**
 *
 */
function oik_cc_mapping_xml2arr( $xmlstring ) {
  $xml = simplexml_load_string($xmlstring);
  $json = json_encode($xml);
  $array = json_decode($json,TRUE);
  return( $array );
}



/** 
 * Return an array of cookies by plugin
 *
 * @return array $plugin_cookies array keyed by the short plugin name pointing to an array of 'simplified' cookie names 
 */
function oik_default_plugin_cookies() {
  $plugin_cookies = array( 'oik-norty'        => "oik-norty1,oik-norty2"   // This is a dummy plugin 
                         , 'bbpress'          => "comment_author_,comment_author_email,comment_author_url" 
                         , "googleanalytics"  => "__utma,__utmb,__utmc,__utmz" 
                         , "newsletter"       => "newsletter"
                         , "ltw-testimonials" => "js_quicktags_extra"
                         
                         );
  return( $plugin_cookies ); 
}

/**
 * Format key value pairs as a string of ' key="value" key="value"'
 * @param $kvp array of key value pairs OR a string containing them already formatted 
 * @return $kvpairs - formatted string
 */
function kvp( $kvp ) {
  $kvpairs = null;
  if ( $kvp ) { 
    if ( is_array( $kvp ) ) {
      foreach ( $kvp as $key => $value ) {
        $kvpairs .= " " . kv( $key, $value );
      }  
    } else {
      $kvpairs = " " . $kvp;
    }
  }
  return( $kvpairs );  
}


/**
 * Create an XML tag containing content 
 */ 
function xmltag( $tag, $kvp, $content ) {
  $kvpairs = kvp( $kvp );  
  stag( $tag, null, null, $kvpairs );
  e( $content );
  etag( $tag );
  e( "\n" );
}

/**
 * Produce a feed tag

<feed
	xmlns="http://www.w3.org/2005/Atom"
	xml:lang="<?php bloginfo_rss( 'language' ); ?>"
	xmlns:thr="http://purl.org/syndication/thread/1.0"
	<?php do_action('atom_ns'); do_action('atom_comments_ns'); ?>
>   
 */
 

/**
 Sample response to request 
 http://cookie-cat.co.uk/feed/cc_mapping?plugins=oik-norty1,googleanalytics
 
   <cc_mapping>
     <plugin-name>oik-norty</plugin-name>
     <cookies>
       <cookie-name>oik-norty1</cookie-name>
       <cookie-name>oik-norty2</cookie-name>
     </cookies>  
   </cc_mapping>
   <cc_mapping>
     <plugin-name>googleanalytics</plugin-name>
     <cookies>
       <cookie-name>__utma</cookie-name>
       <cookie-name>__utmb</cookie-name>
       <cookie-name>__utmc</cookie-name>
       <cookie-name>__utmz</cookie-name>
     </cookies>  
   </cc_mapping> 
   <cookies>
     <cookie>
       <name>oik-norty1</name>
       <description type="html">a dummy cookie from oik-norty</description>
       <category>1</category>
       <session>No</session>
       <duration>permanent</duration>
     </cookie>  
     <cookie>
       <name>oik-norty2</name>
       <description type="html">another dummy cookie from oik-norty</description>
       <category>3</category>
       <session>No</session>
       <duration>permanent</duration>
     </cookie>  
    <cookies>
    
*/




/** 
 * get posts for metavalues
 */
function oik_cc_get_metavalues( $post_type, $meta_key, $meta_value ) {
  $atts['post_type'] = $post_type;
  $atts['meta_key'] = $meta_key;
  $atts['meta_value'] = $meta_value; 
  $posts = bw_get_posts( $atts );
  return( $posts );
}    


/** 
 * Find the cookies that this plugin uses from the cc_mapping 
 */
function oik_cc_get_mapping_for_plugin( $post_id ) {
  $posts = oik_cc_get_metavalues( 'cc_mapping', '_cc_mapping_plugin', $post_id );
  if ( $posts ) {
    foreach ( $posts as $post ) {
      $cookie_id = $post->ID;
      oik_get_cc_mapping_metadata( $cookie_id );
    }  
  }
}




/**
  bw_register_field( "_cc_mapping_cookie", "noderef", "Cookie", array( '#type' => 'cookie_category') );
  bw_register_field( "_cc_mapping_plugin", "noderef", "Plugin", array( '#type' => 'cc_plugin') );   
  bw_register_field( "_cc_mapping_3rd_party", "checkbox", "Third party" ); 
 */
 
function oik_get_cc_mapping_metadata( $post_id ) {
  $metadata = get_post_custom( $post_id );
  bw_trace2( $metadata );
  stag( "cookies" );
  foreach ( $metadata as $key => $value ) {
    if ( $key == "_cc_mapping_cookie" ) {
      $cookie_id = bw_array_get( $value, 0, null );
      if ( $cookie_id ) {
        $cookie_name = oik_cc_add_cookie( $cookie_id );
        xmltag( "cookie-name", kv( $key, $cookie_id ) , $cookie_name );
      } else {
        // Something's gone wrong! 
      }  
    }  
  }
  etag( "cookies" );
}


/**
 * Create a cc_plugin record programmatically
 *
 * @param string $plugin - plugin name
 * @param string $plugin_type - plugin type, defaults to "WordPress"
 * @param string $plugin_name - external plugin name
 * @returns integer $plugin_url - external plugin URL (if not wordpress.org )
 *
 * Note: When creating a "pending" post the post_name needs to be set as well as the post_title
 * AND the current user must also be able to "publish_posts" 
 * otherwise the post_name gets blanked out again
 */
function oik_cc_create_plugin( $plugin, $plugin_type=1, $plugin_name=null, $plugin_url=null ) {
  $post = array( 'post_type' => 'cc_plugin'
               , 'post_status' => 'pending'  
               , 'post_title' => $plugin
               , 'post_name' => $plugin
               , 'post_content' => "$plugin<!--more-->[bw_plug name=$plugin table=y] "
               );
  /* Set metadata fields */
  $_POST['_cc_plugin_type'] = $plugin_type;
  if ( $plugin_name ) {
    $_POST['_cc_plugin_name'] = $plugin_name;
  }
  if ( $plugin_url ) {
    $_POST['_cc_plugin_url'] = $plugin_url;
  }
  $post_id = wp_insert_post( $post, TRUE );
 
  bw_trace2( $post_id );
  return( $post_id );
}




function oik_cc_get_mapping( $plugin ) {       
  stag( "cc_mapping" );
    xmltag( "plugin-name", null, $plugin );
    $atts = array( "post_type" => "cc_plugin"
                 , "name" => $plugin 
                 , "numberposts" => 1 
                 , "post_status" => array( "publish", "pending" )
                 );
                 
    $posts = bw_get_posts( $atts  ); 
    $post = bw_array_get( $posts, 0, null );
    if ( $post ) {
      $post_id = $post->ID;
    } else {
      $post_id = oik_cc_create_plugin( $plugin );
    }    
    oik_cc_get_mapping_for_plugin( $post_id );
      
  etag( "cc_mapping" );
}

function oik_cc_add_cookie( $cookie ) {
  global $cookies;
  bw_trace2( $cookies );
  $cookie_name = bw_array_get( $cookies, $cookie, null );
  if ( !$cookie_name ) {
    $cookie_name = oik_cc_get_cookie( $cookie ); 
  }
  return( $cookie_name );  
}

function oik_cc_get_cookie( $cookie ) {
  global $cookies, $cookie_posts;
  bw_trace2();
  $post = bw_get_post( $cookie, "cookie_category" );
  if ( $post ) {
    $cookie_name = $post->post_title;
    
    bw_trace2( $cookie, "cookie" );
    bw_trace2( $cookie_name, "cookie_name" );
    $cookies[$cookie] = $cookie_name;
    $cookie_posts[$cookie] = $post;
  } else {
    $cookie_name = "unknown";
  } 
  return( $cookie_name ); 
}

function oik_cc_mapping_plugins( $plugins ) {
  $plugin_arr = explode( ",", $plugins );
  foreach ( $plugin_arr as $plugin ) {
     oik_cc_get_mapping( $plugin );
  }
}


function oik_cc_cookie_category_metadata( $post_id, $post_type="cookie_category" ) {
  global $bw_mapping;   
  $customfields = get_post_custom( $post_id );
  foreach ( $bw_mapping['field'][$post_type] as $field ) {
    bw_trace2( $field );
    $value = bw_array_get( $customfields, $field, null );
    $value0 = bw_array_get( $value, 0, null );
    xmltag( $field, null, $value0 );
  }
}


/** 

   <cookies>
     <cookie>
       <name>oik-norty1</name>
       <description type="html">a dummy cookie from oik-norty</description>
       <category>1</category>
       <session>No</session>
       <duration>permanent</duration>
     </cookie>  
     <cookie>
       <name>oik-norty2</name>
       <description type="html">another dummy cookie from oik-norty</description>
       <category>3</category>
       <session>No</session>
       <duration>permanent</duration>
     </cookie>  
    <cookies>

*/

function oik_cc_export_cookie( $cookie_id, $cookie_name ) {
  global $cookie_posts;
  bw_trace2( $cookie_posts );
  
  stag( "cookie" );
  
  xmltag( "name", kv( "id", $cookie_id ) , $cookie_name );
  $post = bw_array_get( $cookie_posts, $cookie_id, null );
  $excerpt = bw_excerpt( $post );
  xmltag( "description", kv("type","html"), $excerpt );
  oik_cc_cookie_category_metadata( $cookie_id );
  
  etag( "cookie" );  
}

function oik_cc_mapping_cookies() {
  global $cookies;
  bw_trace2( $cookies, "cookies", false );
  stag( "cookies" );
  if ( $cookies && count( $cookies ) ) {
    foreach ( $cookies as $cookie_id => $cookie_name ) {
      oik_cc_export_cookie( $cookie_id, $cookie_name );  
    } 
  }  
  etag( "cookies" );
} 

/**
 * In order to create a "pending" object with a "post_name" we have to 
 * be a user with the 'publish_posts' capability
 */

function oik_cc_set_current_user() {
  $wp_user = wp_set_current_user( 1 );
  
  //bw_trace2( $wp_user, "current user 1", false );
  
  $wp_user = wp_set_current_user( null, 'cookie-category' );
  //bw_trace2( $wp_user, "current user?", false );
  //if ( $wp_user->ID == 0 ) {
  //  $wp_user = wp_set_current_user( 2, "" );
  //  bw_trace2( $wp_user, "current user?", false );
  //}
}


/** 
 * @link http://php.net/manual/en/function.parse-url.php
 Specify one of 
 PHP_URL_SCHEME, 
 PHP_URL_HOST, 
 PHP_URL_PORT, 
 PHP_URL_USER, 
 PHP_URL_PASS, 
 PHP_URL_PATH, 
 PHP_URL_QUERY or 
 PHP_URL_FRAGMENT
  to retrieve just a specific URL component as a string (except when PHP_URL_PORT is given, in which case the return value will be an integer).
 */ 
function oik_cc_get_url( $furl, $remote_addr ) {
  $post_name = $remote_addr;
  $post_name .= " ";
  $post_name .= parse_url( $furl, PHP_URL_HOST );
  $post_name = sanitize_title( $post_name );
  return( $post_name );
}


/**
 * Log the request from the cookie-cat client
 * @param string $furl - the requester information
 * @param string $remote_addr - the IP address of the requester
 * @param string $plugins - the list of plugins passed
 * @param string $cookies - the list of cookies passed
 */
function oik_cc_log_request( $furl, $remote_addr, $plugins, $cookies ) {
  
  $content = "Url: " . $furl;
  $content .= "<br />";
  $content .= "Remote_addr: " . $remote_addr;
  $content .= "<!--more-->";
  $content .= "<br />";
  $content .= "Plugins: " . $plugins;
  $content .= "Cookies: " . $cookies;
  $content .= "<br />";
  $content = str_replace( ",", " ", $content );

  $post_name = oik_cc_get_url( $furl, $remote_addr );
  bw_trace2( $post_name, "post_name", true );
  $atts = array( "post_type" => "cc_log" 
               , "post_name" => $post_name
               , "post_title" => $post_name
               , "name" => $post_name
               , "numberposts" => 1
               , "post_status" => "private"
               );
  
  $posts = bw_get_posts( $atts );
   
  if ( $posts ) { 
    $post_id = $posts[0]->ID;
    $content .= $posts[0]->post_content;
    $post_u = array( "ID" => $post_id
                   , 'post_content' => $content 
                   );
    $post_id = wp_update_post( $post_u );               
  } else {
    $post = array( 'post_type' => 'cc_log'
                 , 'post_status' => 'private'  
                 , 'post_title' => $post_name
                 , 'post_name' => $post_name
                 , 'name' => $post_name
                 , 'post_content' => $content
                 );
    $post_id = wp_insert_post( $post, TRUE );
  }               
  /* Set metadata fields - if required */
  bw_trace2( $post_id );
  return( $post_id );
}

/**
 * Validate the request to come from a cookie-cat requester
 * The not very secret part of this is the &furl= parameter
 * which also contains the oik version and the timestamp of the request
 */
function oik_cc_validate_request() {  
  $valid = false;   
  $remote_addr = bw_array_get( $_SERVER, "REMOTE_ADDR", 0 );
  bw_trace2( $remote_addr, "REMOTE_ADDR", false );
  $furl = bw_array_get( $_REQUEST, "furl", null );
  $plugins = bw_array_get( $_REQUEST, "plugins", null );
  $cookies = bw_array_get( $_REQUEST, "cookies", null );
  
  if ( $furl ) { 
    $valid = true;
    wp_reset_query();
    oik_cc_log_request( $furl, $remote_addr, $plugins, $cookies );
  }  
  return( $valid );  
}

/**
 *  [REMOTE_ADDR] => 78.146.252.66
 */  
function oik_lazy_cc_mapping_feed() {
  oik_require( "includes/bw_posts.php" );
  oik_cc_set_current_user(); 
  $valid_request = oik_cc_validate_request();
  if ( $valid_request ) {
    $plugins = bw_array_get( $_REQUEST, "plugins", "wordpress" );
    bw_trace2( $plugins );

    // header('Content-Type: application/atom+xml' );
    
    header('Content-Type: ' . feed_content_type('rss-http') . '; charset=' . get_option('blog_charset'), true);
    
    e( '<?xml version="1.0" encoding="'.get_option('blog_charset').'"?'.'>' ); 
    stag( "feed" );
    xmltag( "title", array( "type" => "text" ), "Cookie-cat for plugins: " . bw_format_date( null, "Y/m/d H:i:s") );
    oik_cc_mapping_plugins( $plugins );
    oik_cc_mapping_cookies();
    etag( "feed" );
  } else {
      header('Content-Type: ' . feed_content_type('rss-http') . '; charset=' . get_option('blog_charset'), true);     
      xmltag( "invalid", array( "type" => "text" ), "invalid request" );
  }
  bw_flush();
}
