<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
?><div class="card licence" style="display:none;">
	<h2>Data Browser</h2>
	<p>Browse and review uploaded content.</p>

	<div id="filesNfolders"></div>

	<div id="selectedFile" style="display:none; margin-top:20px;padding:10px;">
	
		<span class="filename" style="background: #BDF;padding:10px;border: 1px solid #000;"></span>
		
		&mdash;
		
		<a href="" style="color:#a00;margin-right:20px;display:inline-block;">Delete</a>
		
		<a href="" target="_blank" class="button button-primary download" style="display:inline-block;">Download</a> 
		
	</div>
	
	<div class="save-wrapper tostep2">
		<input type="button" name="save" value="Save And Continue > Step 2" style="font-size:20px;padding:10px 20px;height:40px;line-height:20px;" class="button button-primary" onclick="return quicksave_call();"/>
	</div>
</div>