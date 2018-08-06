<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
?><?php
	$fields = get_all_posts_fields();
	foreach($fields as $field) {
?>
		<option value="<?php echo $field; ?>"><?php echo ucfirst($field); ?></option>
<?php
	}
?>
<option value='ID'>
ID (int) The post ID. If equal to something other than 0, the post with that ID will be updated. Default 0.
</option>
<option value='post_author'>
post_author (int) The ID of the user who added the post. Default is the current user ID.
</option>
<option value='post_date'>
post_date (string) The date of the post. Default is the current time.
</option>
<option value='post_date_gmt'>
post_date_gmt (string) The date of the post in the GMT timezone. Default is the value of $post_date.
</option>
<option value='post_content'>
post_content (mixed) The post content. Default empty.
</option>
<option value='post_content_filtered'>
post_content_filtered (string) The filtered post content. Default empty.
</option>
<option value='post_title'>
post_title (string) The post title. Default empty.
</option>
<option value='post_excerpt'>
post_excerpt (string) The post excerpt. Default empty.
</option>
<option value='post_status'>
post_status (string) The post status. Default 'draft'.
</option>
<option value='post_type'>
post_type (string) The post type. Default 'post'.
</option>
<option value='comment_status'>
comment_status (string) Whether the post can accept comments. Accepts 'open' or 'closed'. Default is the value of 'default_comment_status' option.
</option>
<option value='ping_status'>
ping_status (string) Whether the post can accept pings. Accepts 'open' or 'closed'. Default is the value of 'default_ping_status' option.
</option>
<option value='post_password'>
post_password (string) The password to access the post. Default empty.
</option>
<option value='post_name'>
post_name (string) The post name. Default is the sanitized post title when creating a new post.
</option>
<option value='to_ping'>
to_ping (string) Space or carriage return-separated list of URLs to ping. Default empty.
</option>
<option value='pinged'>
pinged (string) Space or carriage return-separated list of URLs that have been pinged. Default empty.
</option>
<option value='post_modified'>
post_modified (string) The date when the post was last modified. Default is the current time.
</option>
<option value='post_modified_gmt'>
post_modified_gmt (string) The date when the post was last modified in the GMT timezone. Default is the current time.
</option>
<option value='post_parent'>
post_parent (int) Set this for the post it belongs to, if any. Default 0.
</option>
<option value='menu_order'>
menu_order (int) The order the post should be displayed in. Default 0.
</option>
<option value='post_mime_type'>
post_mime_type (string) The mime type of the post. Default empty.
</option>
<option value='guid'>
guid (string) Global Unique ID for referencing the post. Default empty.
</option>
<option value='post_category'>
post_category (array) Array of category names, slugs, or IDs. Defaults to value of the 'default_category' option.
</option>
<option value='tags_input'>
tags_input(array) Array of tag names, slugs, or IDs. Default empty.
</option>
<option value='tax_input'>
tax_input (array) Array of taxonomy terms keyed by their taxonomy name. Default empty.
</option>
<option value='meta_input'>
meta_input (array) Array of post meta values keyed by their post meta key. Default empty.
</option>