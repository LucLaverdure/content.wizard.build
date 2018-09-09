<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
if (!current_user_can('administrator')) die( 'No script kiddies please!' );
?><div class="card licence" style="display:none;">
	<h2>Data Browser</h2>
	<p>Browse and review uploaded content.</p>

	<input name="refresh" value="Refresh Cached Files" style="font-size:16px;padding:10px 20px;height:40px;line-height:20px;" class="button button-primary with-sel-confirm" type="button" onclick="return refresh_FNF();">

	<div id="filesNfolders"></div>

	<div id="selectedFile" style="padding:10px;">
	
		<p style="line-height:20px;">
		With All Selected:
		<select class="with-sel">
			<option value="del">Delete</option>
		</select>

		<input type="button" name="save" value="Confirm" style="font-size:16px;padding:10px 20px;height:40px;line-height:20px;" class="button button-primary with-sel-confirm" onclick="return quicksave_call();"/>
		</p>
	</div>

	<input name="refresh" value="Refresh Logs (Last 500 lines)" style="font-size:16px;padding:10px 20px;height:40px;line-height:20px;" class="button button-primary with-sel-confirm" type="button" onclick="return refresh_logs();">
	<div id="logs" style="border: 1px solid #000;padding:10px;box-sizing:border-box;max-height:300px;overflow-y:scroll;"><pre><?php
				include_once(WIZBUI_PLUGIN_PATH . "lib/tailcustom.php");
				$logs = WIZBUI_PLUGIN_PATH . "logs.txt";
				if (file_exists($logs)) {
					echo tailCustom($logs, 500);
				}
	?></pre></div>

	<script type="text/javascript">
	$(function() {
		var objDiv = document.getElementById("logs");
		objDiv.scrollTop = objDiv.scrollHeight;
	});
	</script>

	<div class="save-wrapper tostep2">
		<input type="button" name="save" value="Continue to next step" style="font-size:20px;padding:10px 20px;height:40px;line-height:20px;" class="button button-primary" onclick="return quicksave_call();"/>
	</div>
</div>