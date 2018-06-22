
<div class="magic-pick" style="display:none;">

	<div class="right-col" style="float:right;width:800px;max-width:100%;">
		<h3>Sample Output</h3>
		<div style="margin:20px;border:3px solid #000;max-height:270px;height:270px;padding:20px;box-sizing:border-box;overflow:scroll;">
			<div class="output-picked">
			
			</div>
		</div>
	</div>

	<div class="left-col" style="float:left;width:600px;max-width:100%;">
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
		<div id="combo-wrap" style="max-width:400px;display:inline-block;width:100%;position:relative;">
		
			<input type="text" id="taglist" onclick="toggleSelOptions();" style="display:none;width:350px;"/>
			
			<a class="drop-select" style="width:40px;height:40px;border:1px solid #000;display:none;font-size:30px;line-height:30px;text-align:center;cursor:pointer;" onclick="toggleSelOptions();">&darr;</a>
			
			<div class="options" style="position:absolute;top:43px;left:0;display:none;">
			</div>
		</div>
		<input style="display:none;" type="button" onclick="setTag();" id="wizsetter" value="Apply Filter" />
		
		<h3>Filter</h3>
		<input id="tag" type="text" style="width:400px;margin-bottom:20px;">
		<input type="button" id="savefilter" value="Save" />
	</div>
	
	<iframe id="magicframe" src="" style="width:100%;height:450px;"></iframe>
	
</div>