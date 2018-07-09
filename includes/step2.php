<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
?><div class="card urls" style="display:none;">
	<h2>Step 1 - Upload files manually</h2>
	<p>HTML? CSV? XLS? XLSX?</p>
	<form action="?page=content-wizard-build&action=wb_upload_hook" method="post" enctype="multipart/form-data"> 
		<input type="file" name="fileupload[]" multiple="multiple" />
		<input type="submit" name="uploadfield" value="Upload Files" style="color: #fff;padding:20px;font-size:16px;background: #00aa00;"/>
	</form>
	
	<h2>Step 2 - Download data with crawler</h2>
	<p>Crawl start entry URLs</p>

	<div style="width:90%;">
		<div style="margin:20px 0;">URLs To <strong>Crawl</strong> (one URL per line):</div>
		<div>
	<textarea id="urls" type="text" name="starturl" placeholder="i.e. LucLaverdure.com" wrap="off" style="height:250px;width:90%;"><?php
	$file = ABSPATH . 'wp-content/plugins/content.wizard.build/crawl.me.txt';
	if (file_exists($file)) {
		include_once $file;
	}
	?></textarea>
		</div>
	</div>

	<div style="width:90%;">
		<div style="margin:20px 0;"><strong>Whitelist</strong> (url must match all lines):</div>
		<div>
	<textarea id="whitelist" type="text" name="starturl" placeholder="i.e. mydomain.com" wrap="off" style="height:250px;width:90%;"><?php $opt = get_option('wb_whitelist', null); if ($opt !==  null) { echo unserialize($opt); $_POST["whitelist"] = unserialize($opt);} ?></textarea>
		</div>
	</div>
		
	<div style="width:90%;">
		<div style="margin:20px 0;"><strong>Blacklist</strong> (url must NOT match all lines):</div>
		<div>
	<textarea id="blacklist" type="text" name="blacklist" placeholder="i.e. zip" wrap="off" style="height:250px;width:90%;"><?php $opt = get_option('wb_blacklist', null); if ($opt !==  null) { echo unserialize($opt); $_POST["blacklist"] = unserialize($opt); } ?></textarea></span>
		</div>
	</div>
	
	<div style="clear:left;padding-top:20px;">
	<p>
		<label style="font-size:20px;"><input type="checkbox" name="removegets" value="Y"
		<?php $opt = unserialize(get_option('wb_RemoveGets', 'N')); if ($opt != 'N') { echo 'checked="checked"'; } ?>
		/> Remove GET parameters from URLs (?get=param)</label>
	</p>
	</div>

	<div style="clear:left;padding-top:0;">
	<p>
		<label style="font-size:20px;"><input type="checkbox" name="removehashes" value="Y"
		<?php $opt = unserialize(get_option('wb_RemoveHashes', 'N')); if ($opt != 'N') { echo 'checked="checked"'; } ?>
		/> Remove Hash parameters from URLs (#hash_state)</label>
	</p>
	</div>

	<div style="clear:left;padding-top:0;">
	<p>
		<label style="font-size:20px;"><input type="checkbox" name="jsenabled" value="Y"
		<?php $opt = unserialize(get_option('wb_PostJS', 'N')); if ($opt != 'N') { echo 'checked="checked"'; } ?>
		/> Enable Post Javascript Crawl (Slows Performance)</label>
	</p>
	</div>
	
	<div class="save-wrapper tostep2">
		<p>
			<input type="submit" name="save" value="Delete ALL cached data" style="color: #fff;padding:20px;font-size:16px;" class="red"/>
		</p>
		<p>
		<input class="crawlnow" type="button" name="save" value="Crawl URLs" onclick="return initCrawler();" style="color: #fff;padding:20px;font-size:16px;background: #00aa00;"/>
		<input class="crawlstop" type="button" name="save" value="Stop crawler" onclick="window.location='';" style="color: #fff;padding:20px;font-size:16px;background: #00aa00;"/>
		</p>
	</div>
	<div class="">
		<p>* Files &amp; URLs will be saved to</p>
		<p><?php echo WIZBUI_PLUGIN_PATH; ?>cache</p>
	</div>

</div>