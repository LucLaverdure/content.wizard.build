
<div class="magic-pick" style="display:none;">

	<div class="right-col sample-step" style="float:right;width:700px;max-width:100%;display:none;">
		<div class="output-tabs">
			<a href="#" class="selected prev">Preview</a>
			<a href="#" class="code">Code View</a>
		</div>
		<h3>Sample Output</h3>
		<div style="margin:20px;border:3px solid #000;max-height:270px;height:270px;padding:20px;box-sizing:border-box;overflow:scroll;">
			<div class="output-picked-code" style="display:none;">
			</div>
			<div class="output-picked">
			</div>
		</div>
	</div>

	<div class="left-col" style="float:left;width:600px;max-width:100%;">
		<div class="page-select-step">
			<h3>Select a sample page</h3>
			<div style="margin: 20px 0;">
				<select id="magicfile" style="width:400px;max-width:100%;" onchange="magicgo();">
					<option value=""></option>
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
		</div>
		
		<div class="step-indicator" style="display:none;">
			<h3>Click an element on the page below to get options.</h3>
		</div>
		
		<div class="test-select-step" style="display:none;">
			<h3>Clicked Selection</h3>
			<div id="combo-wrap" class="combo-wrap" style="max-width:400px;display:inline-block;width:100%;position:relative;">
			
				<input type="text" id="taglist" onclick="toggleSelOptions(this);" style="width:350px;" class="combo-input" />
				
				<a class="drop-select fold" style="width:40px;height:40px;border:1px solid #000;font-size:30px;line-height:30px;text-align:center;cursor:pointer;" onclick="toggleSelOptions(this);">&darr;</a>
				
				<div class="options" style="position:absolute;top:43px;left:0;display:none;">
				</div>
			</div>
			<input type="button" class="button button-primary" onclick="setTag();" id="wizsetter" value="Test Filter" />
		</div>
		
		<div class="filter-select-step" style="display:none;">
			<h3>Filter</h3>
			<input id="tag" type="text" style="width:400px;margin-bottom:20px;">
			<input type="button" class="button button-primary" id="savefilter" value="Save" />
		</div>
	</div>
	<div class="frame-step" style="display:none;">
		<iframe id="magicframe" src="" style="width:100%;height:420px;"></iframe>
	</div>
	
</div>