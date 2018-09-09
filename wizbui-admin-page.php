<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
if (!current_user_can('administrator')) die( 'No script kiddies please!' );

include_once "includes/helper.functions.php";

?>

<style>
	<?php
		// CSS file only for this page
		include "main.css";
	?>
</style>

<script type="text/javascript">
	var PLUGIN_CACHE_URL = "<?php echo plugins_url(); ?>/cache/";
	var WB_PLUGIN_URL = "<?php echo site_url(); ?>/";
	var mappings = "<?php $opt = get_option('wb_mappings', null); if ($opt !==  null) { echo unserialize($opt);	} ?>";
	<?php
	// JS file only for this page
	include "main.js";
	?>
	$(function() {
		if ($.trim(mappings) != "") {
			decompileMappings(mappings);
		}
	});
</script>


<div class="admin-wrap">

<?php
	// Small header of plugin
	include "includes/header.php";

	// Crawler and map progress bars
	include "includes/progress-bars.php";

	// Step 1 tab: Account and Key
	include "includes/step1.php";

	// Step 2 tab: Crawler
	include "includes/step2.php";

	// Step 3 tab: Mappings
	include "includes/step3.php";

	// Magic picker modal
	include "includes/magic-picker.php";
	
?>

</div>