
<div class="magic-pick" style="display:none;">

	<h3>Select a sample page</h3>
	<div style="margin: 20px 0;">
		<select id="magicfile" style="width:400px;max-width:100%;" onchange="magicgo();">
			<?php
			$dirs = get_real_dirs();
			foreach ($dirs as $dir) {
			?>		
				<option value="<?php echo $dir; ?>"><?php echo $dir; ?></option>
			<?php
			}
			?>		
		</select>
	</div>

	<h3>Clicked Selection</h3>
	<select id="taglist" style="width:400px;max-width:100%;">
	</select>
	<input type="button" onclick="setTag();" id="wizsetter" value="Apply Filter" />
	
	<h3>Filter</h3>
	<input id="tag" type="text" style="width:400px;margin-bottom:20px;">
	<input type="button" id="savefilter" value="Save" />
	
	<iframe id="magicframe" src="" style="width:100%;height:600px;"></iframe>
	
</div>