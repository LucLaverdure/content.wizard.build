<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
?><div class="card urls" style="display:none;">
	<h2>Step 2 - Download data with crawler</h2>
	<p>Crawl start entry URLs</p>


	<div style="float:left;margin-right:50px;">
		<div style="margin-bottom:10px;"><strong>Whitelist</strong> (url must match all lines):</div>
		<div>
	<textarea id="whitelist" type="text" name="starturl" placeholder="i.e. mydomain.com" wrap="off" style="height:500px;"><?php $opt = get_option('wb_whitelist', null); if ($opt !==  null) { echo unserialize($opt); } ?></textarea>
		</div>
	</div>
		
	<div style="float:left;margin-right:50px;">
		<div style="margin-bottom:10px;"><strong>Blacklist</strong> (url must NOT match all lines):</div>
		<div>
	<textarea id="blacklist" type="text" name="blacklist" placeholder="i.e. zip" wrap="off" style="height:500px;"><?php $opt = get_option('wb_blacklist', null); if ($opt !==  null) { echo unserialize($opt); } ?></textarea></span>
		</div>
	</div>

	<div style="float:left;">
		<div style="margin-bottom:10px;">URLs To <strong>Crawl</strong> (one URL per line):</div>
		<div>
	<textarea id="urls" type="text" name="starturl" placeholder="i.e. LucLaverdure.com" wrap="off" style="height:500px;width:600px;"><?php 
	$path_to_crawl_me_file = WIZBUI_PLUGIN_PATH. "cache/crawl.me.txt";
	if (file_exists($path_to_crawl_me_file)) echo file_get_contents($path_to_crawl_me_file);
?></textarea>
		</div>
	</div>
		

	<div style="display:none">
		<p>
		<span class="head">Crawled URLs:</span>
		<span class="body">
			<textarea id="urls-done" type="text" name="starturl" wrap="off" style="height:500px;" disabled="disabled"><?php 
	
	echo (isset($_REQUEST["starturl"])) ? $_REQUEST["starturl"]."\n" : "";

	$path_init = WIZBUI_PLUGIN_PATH . "cache";
	if(!file_exists(dirname($path_init))) {
		@mkdir(dirname($path_init), 0777, true);
	}
	$dirs = getDirContents($path_init);
	
	foreach($dirs as $dirX) {
		echo "http://".str_replace(WIZBUI_PLUGIN_PATH."cache/", '', $dirX."\n");
	}
?></textarea></span>
		</p>
	</div>

	<div style="display:none">
		<p>
		<span class="head">Error URLs:</span>
		<span class="body">
			<textarea id="urls-errors" type="text" name="starturl" wrap="off" style="height:500px;" disabled="disabled"></textarea></span>
		</p>
	</div>
	
	<div style="clear:left;padding-top:20px;">
	<p>
		<label style="font-size:20px;"><input type="checkbox" name="jsenabled" value="Y"
		<?php $opt = get_option('wb_jsenabled', null); if (($opt !==  null) && (unserialize($opt) == "Y")) { echo 'checked="checked"'; } ?>
		/> Enable Post Javascript Crawl (Slows Performance)</label>
	</p>
	</div>
	
	<div class="save-wrapper tostep2">
		<input type="submit" name="save" value="Delete ALL cached data" style="color: #fff;padding:20px;font-size:16px;" class="red"/>
		<input class="crawlnow" type="button" name="save" value="Crawl URLs" onclick="initCrawler();" style="color: #fff;padding:20px;font-size:16px;"/>
		<input class="crawlstop" type="button" name="save" value="Stop crawler" onclick="stopCrawler();" style="color: #fff;padding:20px;font-size:16px;"/>
	</div>
	<div class="">
		<p>* Files &amp; URLs will be saved to</p>
		<p><?php echo WIZBUI_PLUGIN_PATH; ?>cache</p>
	</div>

</div>