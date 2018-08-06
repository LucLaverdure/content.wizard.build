<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
?><div class="card licence" style="display:none;">
	<h2>Data Browser</h2>
	<p>Browse and review uploaded content.</p>

	<div id="filesNfolders"></div>

	<div id="selectedFile" style="padding:10px;">
	
		<p style="line-height:20px;">
		With All Selected:
		<br /><br />
		<select class="with-sel">
			<option value="del">Delete</option>
		</select>

		<input type="button" name="save" value="Confirm" style="font-size:16px;padding:10px 20px;height:40px;line-height:20px;" class="button button-primary with-sel-confirm" onclick="return quicksave_call();"/>
		</p>
	</div>
	
	<div class="save-wrapper tostep2">
		<input type="button" name="save" value="Continue to next step" style="font-size:20px;padding:10px 20px;height:40px;line-height:20px;" class="button button-primary" onclick="return quicksave_call();"/>
	</div>
</div>