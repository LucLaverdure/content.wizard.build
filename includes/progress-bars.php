<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
if (!current_user_can('administrator')) die( 'No script kiddies please!' );
?><div class="progress-box">
	<div class="key">
		<span class="head-f">Plugin Cost:</span>
		<span class="tokens-count"><a target="_blank" href="https://shop.wizard.build">Free <br><span class="small"><sup>Donate Now!</sup></span></a></span>
		<span style="display:none;" class="tokens-count" id="counter">0</span>
	</div>
	<div class="crawled">
		<div class="head-f">Cached Files:</div>
		<span class="crawled-count">
			<?php echo count(get_real_dirs()); ?>
		</span>
<img src="<?php echo plugins_url(); ?>/content.wizard.build/spinner.gif" style="width:25px;margin-left:10px;display:inline-block;display:none;" class="crawlspin">
<div class="stop-button-crawl" style="display:none;"><a class="button" href="">Stop Crawling</a></div>
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
		<img src="<?php echo plugins_url(); ?>/content.wizard.build/spinner.gif" style="width:25px;margin-left:10px;display:inline-block;display:none;" class="mapspin">
		<div class="stop-button-map" style="display:none;"><a class="button" href="">Stop Mapping</a></div>
	</div>
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
