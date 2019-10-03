<?php 
/**
* @package PiktoTables
*/

/* 
Plugin Name: Pikto Tables
Plugin URI: http://piktogram.rs/
Description: Wordpress plugin za kreiranje proizvoljnih tabela u bazi podataka.
Version: 1.0.5
Author: Piktogram Studio DOO
Author URI: http://piktogram.rs/ 
Licence: GPLv2 or later
Text domain: pikto-tables
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

Copyright 2005-2015 Automattic, Inc.
*/	
 
	if(!defined('ABSPATH')){
		die();
	}

	// Dodavanje sql funkcija
	require 'sqlfunctions.php';

	class PiktoTables{

		public $plugin;

		function __construct(){
			$this->plugin = plugin_basename( __FILE__ );
		}

		function register(){
			add_action('admin_enqueue_scripts',array($this, 'enqueue'));
			add_action('admin_menu',array($this,'add_admin_page'));
			add_action( 'admin_menu', array($this,'add_settings_page'));
			add_filter( "plugin_action_links_{$this->plugin}" , array($this,'settings_link') );			
		}

		public function settings_link($links){
			$settings_link = '<a href="options-general.php?page=pikto_settings">Settings</a>';
			array_push($links, $settings_link);
			return $links;
		}

		public function add_admin_page(){
			add_menu_page( __('Pikto Tables'), 'Pikto Tables', 'manage_options', 'pikto_tables', array($this,'admin_index'), 'dashicons-grid-view', null );
			add_submenu_page( null,'Edit Tables', 'Edit Tables', 'manage_options', 'edit_tables',array($this,'submenu_index'));
			add_submenu_page( 'pikto_tables', __('Create Table'), 'Create Table','manage_options', 'create_tables', array($this,'submenu_index2'));
			add_submenu_page(null,'Show Data' ,'Show Data' , 'manage_options', 'show_data' , array($this,'submenu_index1'));	
		}

		public function add_settings_page(){
			add_options_page( __('Pikto Tables'), 'Pikto Tables', 'manage_options', 'pikto_settings', array($this,'option_index') );
		}

		public function admin_index(){
			require_once plugin_dir_path( __FILE__ ) . 'tables.php';
		}

		public function submenu_index(){
			require_once plugin_dir_path( __FILE__ ) . 'edittables.php';
		}

		public function submenu_index1(){
			require_once plugin_dir_path( __FILE__ ) . 'showdata.php';
		}

		public function submenu_index2(){
			require_once plugin_dir_path( __FILE__ ) . 'createtables.php';
		}

		public function option_index(){
			require_once plugin_dir_path( __FILE__ ) . 'settings.php';
		}

		function activate(){
			flush_rewrite_rules();
		}

		function deactivate(){
			flush_rewrite_rules();
		}

		function enqueue(){
			wp_enqueue_style( 'mypluginstyle', plugins_url( '/assets/pikto-tables-style.css', __FILE__ ));
			wp_enqueue_script('mypluginscript', plugins_url( '/assets/pikto-tables-script.js', __FILE__ ),array('jquery'),null,true);
		}
                
                

		
	}

	if (class_exists('PiktoTables')) {
		$piktoTables = new PiktoTables();
		$piktoTables->register();
	}


// Aktivacija 
	register_activation_hook(__FILE__, array($piktoTables,'activate'));
	register_activation_hook( __FILE__, 'pikto_dbsettings' );
// Deaktivacija 
	register_deactivation_hook(__FILE__, array($piktoTables,'deactivate'));
	register_deactivation_hook(__FILE__, 'delete_pikto_dbsettings');


// Brisanje
	// register_uninstall_hook( __FILE__, array($piktoTables,'uninstall') );


