<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
if (!current_user_can('administrator')) die( 'No script kiddies please!' );
?>
<option value='post_content'>
post_content (mixed) The post content. Default empty.
</option>
<option value='post_title' selected="selected">
post_title (string) The post title. Default empty.
</option>
<option value='post_excerpt'>
post_excerpt (string) The post excerpt. Default empty.
</option>
<option value='post_category'>
post_category (array) Array of category names, slugs, or IDs. Defaults to value of the 'default_category' option.
</option>
<option value='tags_input'>
tags_input(array) Array of tag names, slugs, or IDs. Default empty.
</option>