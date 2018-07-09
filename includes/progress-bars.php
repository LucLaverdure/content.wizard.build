<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
?><div class="progress-box">
	<div class="key">
		<span class="head-f">My Account Type:</span>
		<span class="tokens-count">Free</span>
		<span style="display:none;" class="tokens-count" id="counter">0</span>
	</div>
	<div class="crawled">
		<div class="head-f">Sanitized Downloads:</div>
		<span class="crawled-count">
			<?php echo count(get_real_dirs()); ?>
		</span>
<img src="http://content.wizard.build/wp-content/plugins/content.wizard.build/includes/../spinner.gif" style="width:25px;margin-left:10px;display:inline-block;display:none;" class="crawlspin">
	</div>
	<div class="mapped">
		<span class="head-f">Mapped content:</span>
		<span class="mapped-count">
<?php
							$args = array(
								'posts_per_page'   => -1,
								'post_type'  => 'any',
								'meta_query'    => array(
									array(
										'key'       => 'wizard_build_id',
										'value'     => 'any',
										'compare'   => '!='
									)
								)
							);
							$the_query = new WP_Query($args);
							echo $the_query->post_count;
?>	
		</span>
		<img src="http://content.wizard.build/wp-content/plugins/content.wizard.build/includes/../spinner.gif" style="width:25px;margin-left:10px;display:inline-block;display:none;" class="mapspin">
	</div>
</div>
<div class="need-tokens" style="clear:left;display:none;color:#990000;padding:40px 0 20px  0;width:800px;max-width:100%;">
You need to purchase a licence (key) that provides you with download tokens before crawling... The key is either invalid or you may not have enough tokens to proceed...<br><br>
<a target="_blank" href="http://content.wizard.build">Click Here</a> to purchase a new key.
</div>
		<div style="display:none;">
			<span class="total"><span class="progress"></span></span>
			<span class="crawled-count">0</span>
			/ 
			<span class="crawled-total">0</span>
			(<span class="crawled-percent">0</span>%)
		</div>
		<div style="display:none;">
			<span class="total"><span class="progress"></span></span>
			<span class="mapped-count">0</span>
			/ 
			<span class="mapped-total">0</span>
			(<span class="mapped-percent">0</span>%)
		</div>
