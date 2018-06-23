<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
?><div class="card map" style="display:none;">
	<h2>Step 3 - Content Mappings</h2>
	<p class="wbmsg" style="color:#fff;background:#00cc00;display:none;padding:10px;">Saved content mappings.</p>
	<p>Associate data to content.</p>

<div class="box-map">

</div>
	
<p>
	<a href="#" class="add-ct add-ct-click">Add Content Type</a>
</p>

<div class="box-container-wrapper" style="display:none;">
<div class="box-container">

<h2>Mappings Group <span class="ptype">1</span> <span class="arrow-point">&uArr;</span><span class="arrow-point" style="display:none;">&dArr;</span></h2>

<div class="fold">

<p>

	<span class="head">Input Method</span>
	<span class="body">
	
	<select name="inputmethod" class="selector input_type inputmethod">
		<option selected="selected" value="scraper" style="background: #ddffdd;">Scraper</option>
		<option value="csv" style="background: #ffdddd;">CSV <i>(Coming Soon!)</i></option>
		<option value="xlsx" style="background: #ffdddd;">Excel <i>(Coming Soon!)</i></option>
		<option value="sql" style="background: #ffdddd;">SQL <i>(Coming Soon!)</i></option>
	</select>
	</span>
</p>

<p>

	<span class="head">Content Type</span>
	<span class="body">
	
	<select name="postType" class="selector postType">
<?php
	$post_types = get_post_types();
	foreach($post_types as $post_type) {
?>		
		<option value="<?php echo $post_type; ?>"><?php echo ucfirst($post_type); ?></option>
<?php
	}
?>
	</select>
	</span>
</p>

<p>
<span class="head">Validator</span>
<span class="body">
	<input type="text" class="selector validator" name="selector[]" placeholder="{{#id .class element[attr=value]}}" value="{{.title}}" />
	
	<a href="#" class="wiz-pick"><img src="<?php echo plugin_dir_url( __FILE__ )."../edit.png"; ?>" /></a>

	
	<select class="selector op">
		<option value="notnull" selected="selected">Is not null/empty</option>
		<option value="contains">Contains</option>
		<option value="equals">Is equal to</option>
		<option value="numgt">(to numeric) Is greater than</option>
		<option value="numlt">(to numeric) Is less than</option>
	</select>
	
	<input type="text" class="selector opeq" name="selector[]" placeholder="" value="" />
</span>
</p>

<p>
<span class="head">ID (Must be unique):</span>
<span class="body">
	<input type="text" class="selector idsel" name="selector[]" placeholder="{{#id .class element[attr=value]}}" value="%url%" />
	
	<a href="#" class="wiz-pick"><img src="<?php echo plugin_dir_url( __FILE__ )."../edit.png"; ?>" /></a>

	
	<select class="selector idop">
		<option value="text" selected="selected">Text (Strip HTML Tags)</option>
		<option value="html">HTML (Keep HTML Tags)</option>
		<option value="imgsrc">Image (get IMG SRC attribute)</option>
		<option value="imgcss">Image (get CSS background attribute)</option>
	</select>
	
	<select class="selector idopeq">
		<?php include "dropdown-data-type.php"; ?>
	</select>
</span>
</p>


<div class="field-wrap" style="display:none">
<div class="field-sub-wrap">
<p>
	<select name="field" class="field-map head">
		<?php include "dropdown-full-fields.php"; ?>
	</select>
<span class="body">
	<input type="text" class="selector fieldsel" name="selector[]" placeholder="{{#id .class element[attr=value]}}" value="{{.title}}" />
	
	<a href="#" class="wiz-pick"><img src="<?php echo plugin_dir_url( __FILE__ )."../edit.png"; ?>" /></a>

	
	<select class="selector fieldop">
		<option value="text" selected="selected">Text (Strip HTML Tags)</option>
		<option value="html">HTML (Keep HTML Tags)</option>
		<option value="imgsrc">Image (get IMG SRC attribute)</option>
		<option value="imgcss">Image (get CSS background attribute)</option>
	</select>
	
	<select class="selector fieldopeq">
		<?php include "dropdown-data-type.php"; ?>
	</select>
	<a href="#" class="del">Delete</a>
</span>
</p>

</div>
</div>
	<div class="ct-opt">
		<a href="#" class="add-ct add-field">Add Field</a>
		<a href="#" class="add-ct del-field">Delete Content Type</a>
	</div>
</div>
</div>
</div>

<p style="background:#cfc;padding:10px;">* Full selector sample: "static value %url% {{#id .class element[attribute=value]}} {{{regex}}}"</p>

	<div class="save-wrapper">
		<input type="button" name="save" value="Save Mappings" style="background-color:#009900;color: #fff;padding:20px;font-size:16px;" onclick="compileMappings();return false;"/>

		<input type="button" name="run" value="Save Mappings & Run Import" style="background-color:#000099;color: #fff;padding:20px;font-size:16px;" />
	</div>
	
</div>

