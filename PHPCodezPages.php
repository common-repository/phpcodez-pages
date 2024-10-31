<?php
/**
* Plugin Name: PHPCodez Pages
* Plugin URI: http://phpcodez.com/
* Description: A Widget That Displays Pages
* Version: 0.1
* Author: Pramod T P
* Author URI: http://phpcodez.com/
*/

add_action( 'widgets_init', 'wpc_pages_widgets' );

function wpc_pages_widgets() {
	register_widget( 'wpcpagesWidget' );
}

class wpcpagesWidget extends WP_Widget {
	function wpcpagesWidget() {
		$widget_ops = array( 'classname' => 'wpcClass', 'description' => __('A Widget That Displays pages.', 'wpcClass') );
		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'wpc-pages' );
		$this->WP_Widget( 'wpc-pages', __('PHPCodez pages', ''), $widget_ops, $control_ops );
	}

	
	function widget( $args, $instance ) {
		extract( $args );
		global $wpdb;
		if($instance['page_count']) $pageLimit =" LIMIT 0,".$instance['page_count'];
		if($instance['page_random']){
				$pageOrderBy =" ORDER BY rand()";
		}elseif($instance['page_sort']) {
			$pageOrderBy =" ORDER BY ".$instance['page_sort'];
			if($instance['page_order']) $pageOrderBy .=" " .$instance['page_order'];
		}
	
		if($instance['page_exclude']) $pageExlucde .=" AND  ID NOT IN(".$instance['page_exclude'].")  ";
		
	   $pageQry		= " SELECT distinct(post_title) ,ID,post_title,comment_count FROM $wpdb->posts  
	   					WHERE post_type='page'  AND post_status='publish' AND post_parent='0'
						$pageExlucde $pageOrderBy $pageLimit";
	   $pageData 	= $wpdb->get_results($pageQry, OBJECT);
		
?>
	<div class="arch_box">
		<?php if($instance['page_title']) { ?>
		<div class="side_hd">
			<h2><?php echo $instance['page_title'] ?></h2>
		</div>
	<?php } ?>
		<div class="sider_mid">
			<ul>
				<?php foreach($pageData as $key=>$page) { $havePage=1; ?>
				<li>
					<a href="<?php echo get_permalink($page->ID); ?>">
						<?php echo $page->post_title; ?> <?php if($instance['page_comments']) { ?> (<?php echo empty($page->comment_count)?0:$page->comment_count; ?>)<?php }  ?>
					</a>
				</li>
				<?php  
					if($instance['page_parent']) continue;
					$pageSubQry		= " SELECT distinct(post_title) ,ID,post_title,comment_count FROM $wpdb->posts  
								WHERE post_type='page'  AND post_status='publish' AND post_parent='".$page->ID."'
								$pageExlucde $pageOrderBy ";
					$pageSubData 	= $wpdb->get_results($pageSubQry, OBJECT);
				?>	
				<?php if(sizeof($pageSubData)) {?>
				<ul>
					<?php foreach($pageSubData as $key=>$pageSub) {  ?>
					<li>
						<a href="<?php echo get_permalink($pageSub->ID); ?>">
							<?php echo $pageSub->post_title; ?> <?php if($instance['page_comments']) { ?> (<?php echo empty($pageSub->comment_count)?0:$pageSub->comment_count; ?>)<?php }  ?>
						</a>
					</li>
					<?php  }?>
				</ul>
				<?php } ?>
				<?php } ?>
				<?php if(!$havePage){ ?>
					<li>No pages Are Added Yet</li>
				<?php } ?>
			</ul>	
		</div>	
	</div>
<?php

}


function update( $new_instance, $old_instance ) {
	$instance = $old_instance;
	
	$instance['page_title']		=  $new_instance['page_title'] ;
	$instance['page_random']	=  $new_instance['page_random'] ;
	$instance['page_count'] 	=  $new_instance['page_count'] ;
	$instance['page_comments'] 	=  $new_instance['page_comments'] ;
	$instance['page_sort'] 		=  $new_instance['page_sort'] ;
	$instance['page_order'] 	=  $new_instance['page_order'] ;
	$instance['page_exclude'] 	=  $new_instance['page_exclude'] ;
	$instance['page_parent'] 	=  $new_instance['page_parent'] ;
	
	return $instance;
}

function form( $instance ) {?>
	<p>
		<label for="<?php echo $this->get_field_id( 'page_title' ); ?>"><?php _e('Title', 'wpclass'); ?></label>
		<input id="<?php echo $this->get_field_id( 'page_title' ); ?>" name="<?php echo $this->get_field_name( 'page_title' ); ?>" value="<?php echo $instance['page_title'] ?>"  type="text" width="99%" />
	</p>
	
	<p>
		<label for="<?php echo $this->get_field_id( 'page_random' ); ?>"><?php _e('Show Random Pages', 'wpclass'); ?></label>
		<input id="<?php echo $this->get_field_id( 'page_random' ); ?>" name="<?php echo $this->get_field_name( 'page_random' ); ?>" value="1" <?php if($instance['page_random']) echo 'checked="checked"'; ?> type="checkbox" />
	</p>
	
	<p>
		<label for="<?php echo $this->get_field_id( 'page_parent' ); ?>"><?php _e('Show only parent pages', 'wpclass'); ?></label>
		<input id="<?php echo $this->get_field_id( 'page_parent' ); ?>" name="<?php echo $this->get_field_name( 'page_parent' ); ?>" value="1" <?php if($instance['page_parent']) echo 'checked="checked"'; ?> type="checkbox" />
	</p>
	
	<p>
		<label for="<?php echo $this->get_field_name( 'page_sort' ); ?>"><?php _e('Order BY', 'wpclass'); ?></label>
		<select id="<?php echo $this->get_field_name( 'page_sort' ); ?>" name="<?php echo $this->get_field_name( 'page_sort' ); ?>">
			<option value="post_title"  <?php if($instance['page_sort']=="post_title") echo 'selected="selected"'; ?>>Name</option>
			<option value="ID"  <?php if($instance['page_sort']=="ID") echo 'selected="selected"'; ?>>ID</option>
			<option value="comment_count"  <?php if($instance['page_sort']=="comment_count") echo 'selected="selected"'; ?>>No Of Comments</option>
		</select>
		<select id="<?php echo $this->get_field_name( 'page_order' ); ?>" name="<?php echo $this->get_field_name( 'page_order' ); ?>">
			<option value="ASC" <?php if($instance['page_order']=="ASC") echo 'selected="selected"'; ?>>ASC</option>
			<option value="DESC" <?php if($instance['page_order']=="DESC") echo 'selected="selected"'; ?>>DESC</option>
		</select>
	</p>
	<p>
		<label for="<?php echo $this->get_field_id( 'page_count' ); ?>"><?php _e('Number of pages . for "0" or "No Value" It will list all the pages', 'wpclass'); ?></label>
		<input id="<?php echo $this->get_field_id( 'page_count' ); ?>" name="<?php echo $this->get_field_name( 'page_count' ); ?>" value="<?php echo $instance['page_count'] ?>"  type="text" />
	</p>
	<p>
		<label for="<?php echo $this->get_field_id( 'page_exclude' ); ?>"><?php _e('Exclude Pages - Enter post ids to be excluded (example 5,78,90)', 'wpclass'); ?></label>
		<input id="<?php echo $this->get_field_id( 'page_exclude' ); ?>" name="<?php echo $this->get_field_name( 'page_exclude' ); ?>" value="<?php echo $instance['page_exclude'] ?>"  type="text" />
	</p>
	<p>
		<label for="<?php echo $this->get_field_id( 'page_comments' ); ?>"><?php _e('Display Comment Count', 'wpclass'); ?></label>
		<input id="<?php echo $this->get_field_id( 'page_comments' ); ?>" name="<?php echo $this->get_field_name( 'page_comments' ); ?>" value="1" <?php if($instance['page_comments']) echo 'checked="checked"'; ?> type="checkbox" />
	</p>
<?php
	}
}

?>