<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
?><div class="card map" style="display:none;">
	<h2>Step 3 - Content Mappings</h2>
	<p>Associate data to content.</p>

<div class="box-map">

</div>
	
	<p>
	<a href="#" class="add-ct add-ct-click">Add Content Type</a>
	<select id="ctt" name="postType">
<?php
	$post_types = get_post_types();
	foreach($post_types as $post_type) {
?>		
		<option value="<?php echo $post_type; ?>"><?php echo ucfirst($post_type); ?></option>
<?php
	}
?>
	</select>
</p>

<div class="box-container-wrapper" style="display:none;">
<div class="box-container">

<h2>%ptype% <span class="arrow-point">&uArr;</span><span class="arrow-point" style="display:none;">&dArr;</span></h2>
<div class="fold">


<p>
<span class="head">Instance Container Tag:</span>
<span class="body">
	<input type="text" class="selector" name="selector[]" placeholder="{{#id .class element[attr=value]}}" value="{{.post}}" />
</span>
</p>

<p>
<span class="head">Validator</span>
<span class="body">
	<input type="text" class="selector" name="selector[]" placeholder="{{#id .class element[attr=value]}}" value="{{.title}}" />
	
	<select class="selector">
		<option selected="selected">Is not null/empty</option>
		<option>Contains</option>
		<option>Is equal to</option>
	</select>
	
	<input type="text" class="selector" name="selector[]" placeholder="" value="" />
</span>
</p>

<p>
<span class="head">ID (Must be unique):</span>
<span class="body">
	<input type="text" class="selector" name="selector[]" placeholder="{{#id .class element[attr=value]}}" value="%url%" />
	<select class="selector">
		<option selected="selected">Text (Strip HTML Tags)</option>
		<option>HTML (Keep HTML Tags)</option>
	</select>
	<select class="selector">
		<?php include "dropdown-data-type.php"; ?>
	</select>
</span>
</p>


<div class="field-wrap" style="display:none">
<div class="field-sub-wrap">
<p>
	<select name="field" class="field-map head">
		<?php include "dropdown-simple-fields.php"; ?>
	</select>
<span class="body">
	<input type="text" class="selector" name="selector[]" placeholder="{{#id .class element[attr=value]}}" value="{{.title}}" />
	<select class="selector">
		<option>Text (Strip HTML Tags)</option>
		<option>HTML (Keep HTML Tags)</option>
	</select>
	<select class="selector">
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

<p style="background:#cfc;padding:10px;">* Full selector sample: "static value %url% {{#id .class element[attribute=value]}}"</p>

	<div class="save-wrapper">
		<input type="submit" name="save" value="Save Mappings" style="background-color:#009900;color: #fff;padding:20px;font-size:16px;" />

		<input type="submit" name="run" value="Save Mappings & Run Import" style="background-color:#000099;color: #fff;padding:20px;font-size:16px;" />
	</div>
	
</div>