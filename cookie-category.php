<?php
/*
Plugin Name: cookie-category 
Plugin URI: https://www.bobbingwide.com/blog/oik-plugins/cookie-category
Description: cookie categorisation 
Depends: oik base plugin
Version: 1.2.0
Author: bobbingwide
Author URI: https://www.bobbingwide.com/blog/author/bobbingwide
License: GPL2

    Copyright 2012-2017 Bobbing Wide (email : herb@bobbingwide.com )

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2,
    as published by the Free Software Foundation.

    You may NOT assume that you can use any other version of the GPL.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    The license for this software can likely be found here:
    http://www.gnu.org/licenses/gpl-2.0.html

*/

//register_activation_hook( __FILE__, "flush_rewrite_rules" );

//add_action( 'admin_menu', "flush_rewrite_rules" );

add_action( 'oik_fields_loaded', 'cookie_category_init' );

function cookie_category_init( ) {
  oik_register_cookie_category();
  oik_register_plugin();
  oik_register_cc_mapping();
  oik_register_cc_log();
  oik_cc_mapping_add_feed();
}


/** 
 * Register custom post type "cookie_category" 
 *
 */
function oik_register_cookie_category() {
  $post_type = 'cookie_category';
  $post_type_args = array();
  $post_type_args['label'] = 'Cookies';
  
  $post_type_args['description'] = 'Cookie category';
  bw_register_post_type( $post_type, $post_type_args );
  
  // The description is the content field
  // The title should contain the name 
  
  bw_register_field( "_cookie_category_name", "text", "Simplified cookie name" ); 
  
  $cats = array( 0 => 'None', 1=>'1. Strictly necessary', 2=> '2. Performance', 3=> '3. Functionality', 4=> '4. Targeting/Advertising' );
  bw_register_field( "_cookie_category", "select", "Cookie category", array( '#options' => $cats, '#hint' => " (ICC UK Cookie Guide)" ) ); 
  bw_register_field( "_cookie_category_sess", "checkbox",  "Session cookie?" );
  bw_register_field( "_cookie_category_duration", "text", "Duration", array( '#hint' => "if not a session cookie: nnnnn period" ) ); 
  
  bw_register_field_for_object_type( "_cookie_category", $post_type );
  bw_register_field_for_object_type( "_cookie_category_sess", $post_type );
  bw_register_field_for_object_type( "_cookie_category_duration", $post_type );
  

  add_filter( "manage_edit-${post_type}_columns", "cookie_category_columns", 10 );
  add_action( "manage_${post_type}_posts_custom_column", "bw_custom_column_admin", 10, 2 );
  
  add_filter( "oik_table_fields_${post_type}", "cookie_category_fields", 10, 2 );
  add_filter( "oik_table_titles_${post_type}", "cookie_category_titles", 10, 3 ); 
   
}

/**
 * Returns custom columns for cookie_category post type
 */
function cookie_category_columns( $columns ) {
  $columns["_cookie_category"] = __("Category"); 
  $columns['_cookie_category_sess'] = __("Session Cookie?" );
  $columns['_cookie_category_duration'] = __("Duration" );
  return( $columns ); 
} 

function cookie_category_fields( $fields, $arg2 ) {
  $fields['_cookie_category'] = '_cookie_category';
  $fields['_cookie_category_sess'] = '_cookie_category_sess';
  $fields['_cookie_category_duration'] = '_cookie_category_duration' ;
  return( $fields );
}

/**
 * Titles are remarkably similar to columns for the admin pages
 */
function cookie_category_titles( $titles, $arg2, $fields ) {
  $titles['_cookie_category'] = "Category";
  $titles['_cookie_category_sess'] = "Session?";
  $titles['_cookie_category_duration'] = "Duration";
  return( $titles ); 
}

/**
 * This may not be necessary 
if ( !function_exists( "bw_custom_column_admin" )) {
  oik_require2( "includes/bw_fields.inc", "oik-fields" );
  if ( !function_exists( "bw_custom_column_admin" ) ) {
    bw_trace2( "Please upgrade oik-fields" );
    p( "oik-fields plugin version must be version 1.31 or higher" );
    return;
  }
}  
 */
 
function _bw_theme_field_default__cookie_category( $key, $value ) {
  e( $value[0] );
}

function _bw_theme_field_default__cookie_category_sess( $key, $value ) {
  e( $value[0] );
}

function _bw_theme_field_default__cookie_category_duration( $key, $value ) {
  //bw_trace2();
  e( $value[0] );
}


function _bw_theme_field_default__cc_plugin_type( $key, $value ) {
  e( $value[0] );
}




/** 
 * Register custom post type "plugin"
 *
 */
function oik_register_plugin() {
  $post_type = 'cc_plugin';
  $post_type_args = array();
  $post_type_args['label'] = 'Plugins';
  $post_type_args['description'] = 'Plugins, themes or modules';
  bw_register_post_type( $post_type, $post_type_args );

  /* This is the external plugin name - e.g. google-analytics-for-wordpress
     The post itself is given the internal plugin name e.g. googleanalytics
     
  */
  bw_register_field( "_cc_plugin_name", "text", "Host plugin name" ); 

  $plugin_type = array( 0 => "None"
                      , 1 => "WordPress plugin"
                      , 2 => "WordPress theme"
                      , 3 => "Drupal module"
                      , 4 => "Drupal theme"
                      , 5 => "Other"
                      );

  bw_register_field( "_cc_plugin_type", "select", "Plugin type", array( '#options' => $plugin_type ) ); 
  bw_register_field( "_cc_plugin_cookie_free", "checkbox", "Cookie free?", array( '#hint' => "Check if this plugin DOES NOT USE cookies." ) ); 

  bw_register_field_for_object_type( "_cc_plugin_name", $post_type );
  bw_register_field_for_object_type( "_cc_plugin_type", $post_type );
  bw_register_field_for_object_type( "_cc_plugin_cookie_free", $post_type );
  

  add_filter( "manage_edit-${post_type}_columns", "cc_plugin_columns", 10 );
  add_action( "manage_${post_type}_posts_custom_column", "bw_custom_column_admin", 10, 2 );
  

  add_filter( "oik_table_titles_${post_type}", "cc_plugin_columns", 10, 3 ); 
  

}

/**
 * Returns columns for cc_plugin post type
 */
function cc_plugin_columns( $columns ) {
  $columns["_cc_plugin_type"] = __("Type"); 
  $columns["_cc_plugin_cookie_free"] = __("Cookie free");
  
  //bw_trace2();
  return( $columns ); 
}

 
function _bw_theme_field_default__cc_plugin_cookie_free( $key, $value ) {
  $cb = bw_array_get( $value, 0, "0" );
  if ( $cb == "0" ) {
    e( "&nbsp;" );
  } else {
    e( "Yes" );
  }  
}


/**
 * Register custom post type "mapping" - Mapping cookies to plugins
 *
 */
function oik_register_cc_mapping() {
  $post_type = 'cc_mapping';
  $post_type_args = array();
  $post_type_args['label'] = 'Cookie mappings';
  $post_type_args['description'] = 'Cookie mappings';
  bw_register_post_type( $post_type, $post_type_args );
  
  bw_register_field( "_cc_mapping_cookie", "noderef", "Cookie", array( '#type' => 'cookie_category') );
  bw_register_field( "_cc_mapping_plugin", "noderef", "Plugin", array( '#type' => 'cc_plugin') );   
  bw_register_field( "_cc_mapping_3rd_party", "checkbox", "Third party" ); 

  bw_register_field_for_object_type( "_cc_mapping_cookie", $post_type );
  bw_register_field_for_object_type( "_cc_mapping_plugin", $post_type );
  bw_register_field_for_object_type( "_cc_mapping_3rd_party", $post_type );
  

  add_filter( "manage_edit-${post_type}_columns", "cc_mapping_columns", 10 );
  add_action( "manage_${post_type}_posts_custom_column", "bw_custom_column_admin", 10, 2 );
 
  add_post_type_support( $post_type, 'title' );
  add_filter( 'title_save_pre', 'oik_cc_mapping_title_save_pre' ); //if no post title set it
  
  add_filter( "oik_table_titles_${post_type}", "cc_mapping_columns", 10, 3 ); 

  
} 


function _bw_theme_field_default__cc_mapping_3rd_party( $key, $value ) {
  //bw_trace2();
  e ( bw_array_get( $value, 0, "" ));
  //e( $value[0] );
}




function cc_mapping_columns( $columns ) {
  $columns["_cc_mapping_cookie"] = __("Cookie"); 
  $columns["_cc_mapping_plugin"] = __("Plugin"); 
  $columns["_cc_mapping_3rd_party"] = __("3rd party"); 
  
  //bw_trace2();
  return( $columns ); 
} 

/** 
 * Add the feed for cc_mapping 
 */
function oik_cc_mapping_add_feed() {
 $hook = add_feed( 'cc_mapping', "oik_cc_mapping_feed");
 //bw_trace2( $hook );
}

function oik_cc_mapping_feed() {
  oik_require( "feed/cookie-category-feed.php", "cookie-category" );
  oik_lazy_cc_mapping_feed();
}

/**
 * Set a default title from the fields of a custom_post type that does not support 'title' 
 *
 * @param string $post_title - the current value of the title 
 * **?** Do we need to do this if the post type DOES support 'content'
 * This code checks for "Auto Draft" for situations where the post has already been auto saved as a draft. 
 * Perhaps it should check for post_status == 'auto-draft'
 
 * **?** Bug - this is setting the title during menu saves!  
 *
 */
function oik_cc_mapping_title_save_pre( $post_title = NULL ) {
  //bw_trace2( $_POST );
  if ( !$post_title || $post_title == "Auto Draft" ) {
    global $post;
    if ( $post ) {
      $post_type = $post->post_type;
      
      //if ( !post_type_supports( $post->post_type, 'title' ) )         
      if ( $post_type == "cc_mapping" ) {
        oik_require( "includes/bw_posts.inc" );
        $cc_mapping_cookie = bw_array_get( $_POST, "_cc_mapping_cookie", null );
        bw_trace2( $cc_mapping_cookie, "cc_mapping_cookie" ) ;
        if ( $cc_mapping_cookie ) {
          $cookie = bw_get_post( $cc_mapping_cookie, "cookie_category" );
          $cc_mapping_cookie = $cookie->post_title;
          if ( !$cc_mapping_cookie ) gobang();
        }   
        $cc_mapping_plugin = bw_array_get( $_REQUEST, "_cc_mapping_plugin", null );
        if ( $cc_mapping_plugin ) {
          $plugin = bw_get_post( $cc_mapping_plugin, "cc_plugin" );
          $cc_mapping_plugin = $plugin->post_title;
        }  
        $post_title = "$cc_mapping_cookie used by $cc_mapping_plugin";
        //gobang();
      }
    }  
  }
  return $post_title;
}

/**
 * Register custom post type "cc_log" - The log of cookie-cat requests from "external" sites
 *
 */
function oik_register_cc_log() {
  $post_type = 'cc_log';
  $post_type_args = array();
  $post_type_args['label'] = 'Request logs';
  $post_type_args['description'] = 'Request logs';
  bw_register_post_type( $post_type, $post_type_args );
  add_post_type_support( $post_type, 'title' );
}
