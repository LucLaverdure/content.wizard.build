<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
?><div class="card urls" style="display:none;">
	<h2>Step 2 - Get/Provide URLs list</h2>
	<p>Crawl start entry URLs</p>


	<div>
		<p>
		<span class="head">Domains allowed:</span>
		<span class="body">
	<textarea id="domains" type="text" name="starturl" placeholder="mydomain.com" wrap="off" style="height:200px;"><?php $opt = get_option('wb_domains', null); if ($opt !==  null) { echo unserialize($opt); } ?></textarea></span>
		</p>
	</div>

	<div>
		<p>
		<span class="head">URLs To Crawl:</span>
		<span class="body">
	<textarea id="urls" type="text" name="starturl" placeholder="http://target-website-to-crawl.com" wrap="off" style="height:500px;"><?php 
	$path_to_crawl_me_file = WIZBUI_PLUGIN_PATH. "cache/crawl.me.txt";
	if (file_exists($path_to_crawl_me_file)) echo file_get_contents($path_to_crawl_me_file);
?></textarea></span>
		</p>
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
	
	<p>* One URL per line.</p>
	<p>
		<label style="font-size:20px;"><input type="checkbox" name="jsenabled" value="Y"
		<?php $opt = get_option('wb_jsenabled', null); if (($opt !==  null) && (unserialize($opt) == "Y")) { echo 'checked="checked"'; } ?>
		/> Enable Post Javascript Crawl (Slows Performance)</label>
	</p>
	
	<div class="save-wrapper tostep2">
		<input type="submit" name="save" value="Delete ALL cached data" style="color: #fff;padding:20px;font-size:16px;" class="red"/>
		<input class="crawlnow" type="button" name="save" value="Crawl URLs" onclick="initCrawler();" style="color: #fff;padding:20px;font-size:16px;"/>
		<input class="crawlstop" type="button" name="save" value="Stop crawler" onclick="stopCrawler();" style="color: #fff;padding:20px;font-size:16px;"/>
	</div>
	<div class="">
		<p>* Files &amp; URLs will be saved to</p>
		<p><?php echo WIZBUI_PLUGIN_PATH; ?>/cache</p>
	</div>

</div>