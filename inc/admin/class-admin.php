<?php

namespace Menu_Generator\Inc\Admin;


class Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The text domain of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_text_domain    The text domain of this plugin.
	 */
	private $plugin_text_domain;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    string $plugin_name	The name of this plugin.
	 * @param    string $version	The version of this plugin.
	 * @param	 string $plugin_text_domain	The text domain of this plugin
	 */
	public function __construct( $plugin_name, $version, $plugin_text_domain ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->plugin_text_domain = $plugin_text_domain;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/nds-admin-form-demo-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		$params = array ( 'ajaxurl' => admin_url( 'admin-ajax.php' ) );
		wp_enqueue_script( 'nds_ajax_handle', plugin_dir_url( __FILE__ ) . 'js/nds-admin-form-demo-ajax-handler.js', array( 'jquery' ), $this->version, false );
		wp_localize_script( 'nds_ajax_handle', 'params', $params );

	}

	/**
	 * Callback for the admin menu
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {

		add_menu_page(	__( 'Menu Generator', $this->plugin_text_domain ), //page title
						__( 'Menu Generator', $this->plugin_text_domain ), //menu title
						'manage_options', //capability
						$this->plugin_name . '-ajax', //menu_slug
						array( $this, 'ajax_form_page_content' ) //callback for page content
					);


		// Add a submenu page and save the returned hook suffix.
		$ajax_form_page_hook = add_submenu_page(
									$this->plugin_name, //parent slug
									__( 'Menu Generator', $this->plugin_text_domain ), //page title
									__( 'Generate menu', $this->plugin_text_domain ), //menu title
									'manage_options', //capability
									$this->plugin_name . '-ajax', //menu_slug
									array( $this, 'ajax_form_page_content' ) //callback for page content
									);

		/*
		 * The $page_hook_suffix can be combined with the load-($page_hook) action hook
		 * https://codex.wordpress.org/Plugin_API/Action_Reference/load-(page)
		 *
		 * The callback below will be called when the respective page is loaded
		 */

		add_action( 'load-'.$ajax_form_page_hook, array( $this, 'loaded_ajax_form_submenu_page' ) );
	}

	/*
	 * Callback for the add_submenu_page action hook
	 *
	 * The plugin's HTML form is loaded from here
	 *
	 * @since	1.0.0
	 */
	public function html_form_page_content() {
		//show the form
		include_once( 'views/partials-html-form-view.php' );
	}

	/*
	 * Callback for the add_submenu_page action hook
	 *
	 * The plugin's HTML Ajax is loaded from here
	 *
	 * @since	1.0.0
	 */
	public function ajax_form_page_content() {
		include_once( 'views/partials-ajax-form-view.php' );
	}

	/*
	 * Callback for the load-($html_form_page_hook)
	 * Called when the plugin's submenu HTML form page is loaded
	 *
	 * @since	1.0.0
	 */
	public function loaded_html_form_submenu_page() {
		// called when the particular page is loaded.
	}

	/*
	 * Callback for the load-($ajax_form_page_hook)
	 * Called when the plugin's submenu Ajax form page is loaded
	 *
	 * @since	1.0.0
	 */
	public function loaded_ajax_form_submenu_page() {
		// called when the particular page is loaded.
	}

	/**
	 *
	 * @since    1.0.0
	 */
	public function the_form_response() {

		if( isset( $_POST['nds_add_user_meta_nonce'] ) && wp_verify_nonce( $_POST['nds_add_user_meta_nonce'], 'nds_add_user_meta_form_nonce') ) {

			$nds_menu_name = sanitize_text_field( $_POST['menu_name'] );
			//$nds_user_meta_value = sanitize_text_field( $_POST['nds']['user_meta_value'] );
			//$nds_user =  get_user_by( 'login',  $_POST['nds']['user_select'] );
			//$nds_user_id = absint( $nds_user->ID ) ;

			// server processing logic
			$answer = $this->generate_menu($nds_menu_name);
			//$answer = true;
			if( isset( $_POST['ajaxrequest'] ) && $_POST['ajaxrequest'] === 'true' ) {
				// server response
				$site_url = get_option('siteurl');
				if ($answer){
				    echo "<h3> Menu with name ".
				    $nds_menu_name." has been generated</h3><br><a href='"
				    .$site_url."/wp-admin/nav-menus.php?action=edit&menu=".$answer."'>Edit new menu yourself!</a>";
				}else{
				    echo '<h3> Error! Menu with name '.$nds_menu_name.' has been created before!</h3>';
				}

				echo '<pre>';
					print_r( $_POST );
				echo '</pre>';
				wp_die();
            }

			// server response
			$admin_notice = "success";
			$this->custom_redirect( $admin_notice, $_POST );
			exit;
		}
		else {
			wp_die( __( 'Invalid nonce specified', $this->plugin_name ), __( 'Error', $this->plugin_name ), array(
						'response' 	=> 403,
						'back_link' => 'admin.php?page=' . $this->plugin_name,

				) );
		}
	}

	private function add_item_to_menu($category_id, $parent_id, $parent_item, $menu_id){

	    if ($parent_id === 0){

	        $item = wp_update_nav_menu_item($menu_id, 0, array(
                        'menu-item-object-id' => $category_id,
                        'menu-item-object' => 'product_cat',
                        'menu-item-type' => 'taxonomy',
                        'menu-item-status' => 'publish'
                    ));

            return $item;
	    }else{
	        $item = wp_update_nav_menu_item($menu_id, 0, array(
                    			'menu-item-object-id' => $category_id,
								'menu-item-parent-id' => $parent_item,
                    			'menu-item-object' => 'product_cat',
                    			'menu-item-type' => 'taxonomy',
                    			'menu-item-status' => 'publish'
        	    ));

        	return $item;

	    }


	}

	private function build_category_tree(array &$elements, $parentId = 0) {

        $branch = array();

        foreach ($elements as &$element) {

            if ($element['parent_id'] == $parentId) {

                $children = $this->build_category_tree($elements, $element['id']);
                if ($children) {
                    $element['children'] = $children;
                }

                $branch[$element['id']] = $element;

                unset($element);
            }
        }

        return $branch;
    }

    private function set_menu_structure($menu_id, $parent_node, $items = [])
    {

        foreach ($items as $item) {

                $parent_node_new = $this->add_item_to_menu($item['id'], $item['parent_id'], $parent_node, $menu_id);

                if ( !empty($item['children']) ){

                    $this->set_menu_structure($menu_id, $parent_node_new, $item['children']);
                }


        }

    }


    private function generate_menu($menu_name){

    	$menu_exists = wp_get_nav_menu_object( $menu_name );

    	if( !$menu_exists){


    		$args = array(
             'taxonomy'     => 'product_cat',
             //'orderby'      => 'term_id',
             'show_count'   => 0,
             'pad_counts'   => 0,
             'hierarchical' => 1,
             'title_li'     => '',
             'hide_empty'   => 0
      		);

    		$category_elements = array();
     		$all_categories = get_categories( $args );

            foreach ($all_categories as $cat) {


    		    $element['id'] = $cat->term_id;
    		    $element['parent_id'] = $cat->category_parent;
    		    $element['name'] = $cat->name;

    			$category_elements[] = $element;

            }

            $menu_id = wp_create_nav_menu($menu_name);
            $category_tree = $this->build_category_tree($category_elements);

            $parent_node = null;
            $this->set_menu_structure($menu_id, $parent_node, $category_tree);

            return $menu_id;

    	 }else{

    	     return false;
    	 }
    }

	/**
	 * Redirect
	 *
	 * @since    1.0.0
	 */
	public function custom_redirect( $admin_notice, $response ) {
		wp_redirect( esc_url_raw( add_query_arg( array(
									'nds_admin_add_notice' => $admin_notice,
									'nds_response' => $response,
									),
							admin_url('admin.php?page='. $this->plugin_name )
					) ) );

	}


	/**
	 * Print Admin Notices
	 *
	 * @since    1.0.0
	 */
	public function print_plugin_admin_notices() {
		  if ( isset( $_REQUEST['nds_admin_add_notice'] ) ) {
			if( $_REQUEST['nds_admin_add_notice'] === "success") {
				$html =	'<div class="notice notice-success is-dismissible">
							<p><strong>The request was successful. </strong></p><br>';
				$html .= '<pre>' . htmlspecialchars( print_r( $_REQUEST['nds_response'], true) ) . '</pre></div>';
				echo $html;
			}

			// handle other types of form notices

		  }
		  else {
			  return;
		  }

	}


}
