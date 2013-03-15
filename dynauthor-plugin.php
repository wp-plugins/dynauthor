<?php
/*
Plugin Name: Dynauthor - Dynamic Author Filter
Plugin URI: http://www.engageweb.co.uk/dynauthor-wordpress-plugin-6767.html
Description: Dynamic filtering of authors
Author: Engage Web - Steven Morris
Version: 1.2
Author URI: http://www.engageweb.co.uk/about-us/meet-the-team#Steven
License: GPL2
*/

/*************************************************************/
	

	add_action( 'init', 'create_theme_taxonomy', 0 );
	
	add_action('wp_ajax_check_author', 'dynauthor_ajax_check_author');
	
	function dynauthor_ajax_check_author()
		{	global $wpdb;
			$query = $_POST['data'];
			echo '<ul class="" id="" name="">';
			$authorsname = $wpdb->get_results("SELECT ID, display_name from $wpdb->users WHERE display_name LIKE '%{$query}%' ORDER BY ID LIMIT 0,30 ");
			foreach ($authorsname as $author) {
			  echo "<li class='theme-option'><a class=\"authorlink\" style=\"cursor:pointer;\" authname='" . $author->display_name . "' authid='" . $author->ID . "'>" . $author->display_name . "</a></li>\n";  
			}
			echo'</ul>
			<script language="javascript" type="text/javascript">
					jQuery(".authorlink").bind(\'click\', function() {
					var authid = jQuery(this).attr(\'authid\');
					var authname = jQuery(this).attr(\'authname\');
					jQuery(this).css(\'font-weight\',900);
					jQuery(".authorlink").not(this).hide("slow");
					jQuery("#filbox").val(authname);
					jQuery("#post_author_override").val(authid);
					});
			</script>			
			';
			die();
		}
	
	function create_theme_taxonomy() {
		if (!taxonomy_exists('theme')) {
			register_taxonomy( 'theme', 'post', array( 'hierarchical' => false, 'label' => __('Theme'), 'query_var' => 'theme', 'rewrite' => array( 'slug' => 'theme' ) ) );
		}
	}
	
	function add_theme_box() {
		add_meta_box('authordiv', __('Author'), 'dynauthor_box', 'post', 'normal', 'core');
	}  
	 
	function add_theme_menus() {
		if ( ! is_admin() )
			return;
		add_action('admin_menu', 'add_theme_box');
	}
	 
	add_theme_menus();
	   
	function dynauthor_box($post) {
		echo '<input type="hidden" name="taxonomy_noncename" id="taxonomy_noncename" value="' .
					wp_create_nonce( 'taxonomy_theme' ) . '" />';
		 
		$pluginurl = plugins_url( '/' , __FILE__ );	
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script('jquery-effects-core');
			
		?>
			<script type="text/javascript">
				var $j = jQuery.noConflict();
			</script>
			
		<?php
			global $post;
			$id = get_the_ID();
			$post_tmp = get_post($id);
			$user_id = $post_tmp->post_author;
			$first_name = get_the_author_meta('user_login',$user_id);
		?> 	
			
		<label><strong>Current Author:</strong></label>
		<div><?php echo $first_name; ?></div>
		<label><strong>New Author:</strong></label><br>
		<input type="text" name="filterbox" id="filbox" autocomplete="off">	<input type="hidden" name="post_author_override" id="post_author_override" readonly="readonly" value="<?php echo $user_id; ?>">	
		<div id="result"></div>
		
		<script language="javascript" type="text/javascript">
			jQuery(document).ready(function()
				{
					jQuery("#filbox").keyup(function()
						{
					jQuery.post(
							ajaxurl, 
							{
							   'action':'check_author',
							   'data':jQuery("#filbox").val()
							}, 
							function(response){
							   jQuery( "#result" ).empty().append( response );
							}
						 );
						});		
					jQuery(".authorlink").click(function() {
					var authid = jQuery(this).attr('authid');
					var authname = jQuery(this).attr('authname');
					jQuery("#filbox").val(authname);
					jQuery("#post_author_override").val(authid);
				});

			});
		</script>
	<?php
	}   
	   
	 