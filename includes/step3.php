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

<h2>Mappings Group <span class="ptype" style="display:none;">1</span> <span class="arrow-point" style="transform: rotate(90deg);display:inline-block;zoom:0.8;">&#10148;</span><span class="arrow-point" style="display:none;">&#10148;</span></h2>

<div class="fold">

<p>

	<span class="head">Input Method <span title="The only available option is 'Scraper' at the moment." class="info-ico">&#8505;</span></span>
	<span class="body">
	
	<select name="inputmethod" class="selector input_type inputmethod" onchange="$(this).val('scraper');">
		<option selected="selected" value="scraper" style="background: #ddffdd;">Scraper</option>
		<option value="csv" style="background: #ffdddd;">CSV <i>(Coming Soon!)</i></option>
		<option value="xlsx" style="background: #ffdddd;">Excel <i>(Coming Soon!)</i></option>
		<option value="sql" style="background: #ffdddd;">SQL <i>(Coming Soon!)</i></option>
	</select>
	</span>
</p>

<p>

	<span class="head">Content Type <span title="Migrate as pages, posts, products, etc." class="info-ico">&#8505;</span></span>
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
<span class="head">Validator <span title="Only data matching the Validator rule will be ingested." class="info-ico">&#8505;</span></span>
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
<span class="head">ID (Must be unique): <span title="When ID doesn't exist, a new item is created. Otherwise, the item with the same id gets updated." class="info-ico">&#8505;</span></span>
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

<div class="combo-wrap head" style="display:inline-block;max-width:100%;width:240px;position:relative;">

	<input type="text" name="field" class="field-map combo-input autome" onclick="toggleSelOptions(this);" />
	
	<a class="drop-select" onclick="toggleSelOptions(this);">&darr;</a>
	
	<div class="options" style="position:absolute;top:34px;left:0;display:none;width:400px;max-height:300px;overflow-y:scroll;">
<?php
	$arr_data = array();
	$arr_ret = array();
	$arr_data["post_title"] = "Title";
	$arr_data["post_excerpt"] = "Short Content (Excerpt)";
	$arr_data["post_content"] = "Content";
	$arr_data["post_category"] = "Categories (Array and/or separated with commas)";
	
	$arr_ret = array_merge($arr_ret, $arr_data);
?>
		<h3>Popular Fields</h3>
<?php foreach($arr_data as $key => $val) { ?>
		<a href="#" onclick="comboclick(this);return false;" data-val="<?php echo $key; ?>"><?php echo $val; ?></a>
<?php } ?>

		<h3>Woo Commerce Fields</h3>
<?php 
	$arr_data = array();
	$arr_data["_visibility"] = "Visibility";
	$arr_data["_stock_status"] = "In Stock";
	$arr_data["total_sales"] = "Total Sales";
	$arr_data["_downloadable"] = "Downloadable";
	$arr_data["_virtual"] = "Virtual";
	$arr_data["_regular_price"] = "Regular Price";
	$arr_data["_sale_price"] = "Sale Price";
	$arr_data["_purchase_note"] = "Purchase Note";
	$arr_data["_featured"] = "Featured";
	$arr_data["_weight"] = "Weight";
	$arr_data["_length"] = "Length";
	$arr_data["_width"] = "Width";
	$arr_data["_height"] = "Height";
	$arr_data["_sku"] = "SKU";
	$arr_data["_product_attributes"] = "Product Attributes";
	$arr_data["_sale_price_dates_from"] = "Sales Price Date - From";
	$arr_data["_sale_price_dates_to"] = "Sales Price Date - To";
	$arr_data["_price"] = "Price";
	$arr_data["_sold_individually"] = "Sold Individually";
	$arr_data["_manage_stock"] = "Manage Stock";
	$arr_data["_backorders"] = "Back Orders";
	$arr_data["_stock"] = "Stock";

	$arr_ret = array_merge($arr_ret, $arr_data);	
 ?>
<?php foreach($arr_data as $key => $val) { ?>
		<a href="#" onclick="comboclick(this);return false;" data-val="<?php echo $key; ?>"><?php echo $val; ?></a>
<?php } ?>

<?php
	$arr_ret = array_merge($arr_ret, $arr_data);
?>

		<h3>Other Fields</h3>
<?php
	$fields = get_all_posts_fields();
	foreach($fields as $key => $val) {
		if ((!in_array($val, $arr_ret)) && (!in_array($val, array_flip($arr_ret)))) {
?>
		<a href="#" onclick="comboclick(this);return false;" data-val="<?php echo $val; ?>"><?php echo $val; ?></a>
<?php
		}
	}
?>

	</div>
</div>

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

