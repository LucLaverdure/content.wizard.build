<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
?><div class="card licence">
	<h2>Step 1 - Account Settings and Licence</h2>
	<p>Migrate content to this wordpress installation.</p>

	<div>
		<span class="head">Key (Licence):</span>
		<span class="body"><input id="apikey" type="text" name="apikey" placeholder="ABCD-EFGH-IJKL-MNOP" value="<?php $opt = get_option('wb_apikey', null); if ($opt !==  null) { echo unserialize($opt); } ?>" /></span>
	</div>
	
	<p>* You can purchase new keys at <a target="_blank" href="http://content.wizard.build">Content.Wizard.Build</a>.</p>

	<div class="save-wrapper tostep2">
		<input type="button" name="save" value="Save And Continue > Step 2" style="color: #fff;padding:20px;font-size:16px;" onclick="return quicksave_call();"/>
	</div>
</div>