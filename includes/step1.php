<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
?><div class="card licence">
	<h2>Step 1 - Account Settings</h2>
	<p>Migrate content to this wordpress installation.</p>

	<div>
		<span class="head">My Key (Licence):</span>
		<span class="body"><input id="apikey" type="text" name="apikey" placeholder="ABCD-EFGH-IJKL-MNOP" value="<?php $opt = get_option('wb_apikey', null); if ($opt !==  null) { echo unserialize($opt); } ?>" /></span>
	</div>
	
	<p>* You can upgrade your account @ <a target="_blank" href="http://shop.wizard.build">Shop.Wizard.Build</a>.</p>

	<div class="save-wrapper tostep2">
		<input type="button" name="save" value="Save And Continue > Step 2" style="font-size:20px;padding:10px 20px;height:40px;line-height:20px;" class="button button-primary" onclick="return quicksave_call();"/>
	</div>
</div>