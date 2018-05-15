/*   Globals	*/

	window.tim = 0;
	
// crawler
	window.delay = 2000;
	window.batch = 15;
	window.crawlerIO = false;
	window.crawlerBatched = 0;
	window.crawlList = [];
	window.crawledList = [];
// mapper
	window.MappedURLsCount = 0;
	window.MappedURLsTotal = 0;


// sleep function
function sleep (time) {
	return new Promise((resolve) => setTimeout(resolve, time));
}

// get WP jquery
$ = jQuery;

// on window finished loading
$(function() {

	// navigation tabs
	$(".nav a").on("click", function() {
		$(".nav a").removeClass("selected");
		$(this).addClass("selected");
		$(".card").hide();
		$(".card." + $(this).data('tab')).fadeIn();
		return false;
	});

	// test api key and colorize buttons based on key validity
	$("#apikey").on("input click", function() {
		$this = $(this);
		
		$.ajax({
			url: "http://content.wizard.build/authme.php?key="+$.trim($this.val()),
			dataType: 'text',
			context: document.body,
			success: function(data, textStatus, jqXHR) {
				if (data <= 0) {
					$('.tostep2 input').css('background', '#990000');
					$('#counter').css('color', '#990000');
				} else if (data < 100) {
					$('.tostep2 input').css('background', '#990000');
					$('#counter').css('color', '#990000');
				} else if (data < 1000) {
					$('.tostep2 input').css('background', '#000099');
					$('#counter').css('color', '#000099');
				} else if (data >= 1000) {
					$('.tostep2 input').css('background', '#009900');
					$('#counter').css('color', '#009900');
				}
				$('#counter').html(parseFloat(data).toLocaleString('en'));
			}
		});
	
	});
	
	$("#apikey").click();
	
	// update progress bars
	ini_crawl_stats();
	update_progress();
});

// update progress bars
function update_progress() {
	
	$('.crawled-count').html(window.crawledList.length);
	var total = window.crawlList.length + window.crawledList.length;
	$('.crawled-total').html(total);
	var progress = parseInt(window.crawledList.length / total * 100);
	$('.crawled-percent').html(progress);
	$('.crawled .progress').css('width', (progress / 100) * parseInt($('.crawled .total').width()));
}


// Crawl & Download

var crawlUrl = function(url, path) {
	
	var new_url = "http://content.wizard.build/authme.php?run&key=" +
	$("#apikey").val() + "&url=" + encodeURIComponent(url);
	
	$.ajax({
		dataType: 'html',
		url: new_url,
		success: function(data, textStatus, jqXHR) {
			$(".crawlspin").show();
			$.post("/wp-admin/admin-post.php?action=wb_save_hook",
			  {
				data: data,
				path: path,
				whitelist: $("#domains").val()
			  },
			  function() {
				crawledList.push(url);
				$("#urls-done").append(url+"\n");
				update_progress();
				$.ajax({
					url: "/wp-admin/admin-post.php?action=wb_get_hook",
					dataType: 'text',
					context: document.body,
					success: function(data, textStatus, jqXHR) {
						if ($.trim(data) != "") {
							$('#urls').val(data);
							window.crawlList = $.trim(data).split("\n");
							update_progress();
						}
						$('#urls').prop('disabled', false);
						$('.crawlnow').prop('disabled', false);
						$('.crawlstop').prop('disabled', true);
						$(".crawlspin").hide();
					},
					error: function() {
						$('#urls').val("");
						$('#urls').prop('disabled', false);
						$('.crawlnow').prop('disabled', false);
						$('.crawlstop').prop('disabled', true);
						$(".crawlspin").hide();						
					}
				});
			  }
			);
		},
		error: function() {
			crawledList.push(url);
			$("#urls-errors").append(url+"\n");
			console.log("error crawling url: "+url)
			update_progress();
		}
	});
}

// verify if url is already cached, if not add to crawl list
// crawlUrl(urlX, urlCached);
var crawlUrlExists = function(urlX) {
	
	// verify domain whitelist
	var pass = false;
	var domains = $.trim($('#domains').val()).split("\n");
	for (key in domains) {
		if (urlX.indexOf(domains[key]) != -1) pass = true;
	}
	if (!pass) {
		$("#urls-errors").append(urlX+"\n");
		console.log("Item Not Found: " + urlModded + "\n");
		return;
	}
	
	var urlMod = urlX.replace("http://", "");
	urlMod = urlMod.replace("https://", "");
	var urlCached = urlMod.split("/");
	var last = parseInt(urlCached.length - 1);

	if ((urlCached[last].indexOf(".") == -1) || (urlCached.length <= 1)) {
		urlCached.push("index.html");
	}
	urlCached = urlCached.join("/");
	var urlModded = PLUGIN_CACHE_URL + urlCached;
	$.ajax({
		type: 'HEAD',
		url: urlModded,
		success: function() {
			console.log("Item Cached: " + urlModded + "\n");
			crawledList.push(urlX);
			update_progress();
			window.crawlerBatched--;
			/* processCrawler(); */
		},
		error: function() {
			console.log("Item Not Found: " + urlModded + "\n");
			sleep(window.delay).then(() => {
				crawlUrl(urlX, urlCached);
			});
		}            
	});
}
	
function processCrawler() {

		for (var i = 0; i < window.batch; ++i) {
			if (window.crawlList.length <= 0) {
				stopCrawler();
				return;
			}

			window.crawlerBatched++;
			var item = window.crawlList.shift();
			if (item.indexOf("http") == -1) item = "http://" + item;
			console.log("Start Item: " + item + "\n");
			if ($.trim(item) != "") crawlUrlExists(item);
			update_progress();
		}
	update_progress();
}

// init stats
function ini_crawl_stats() {
	window.crawlList = $.trim($('#urls').val()).split("\n");
	var switcharoo = [];
	$.each(window.crawlList, function(i, el){
		if($.inArray(el, switcharoo) === -1) switcharoo.push(el);
	});
	window.crawlList = switcharoo;
	window.crawlList.sort();
	update_progress();
}
	
// init crawl
function initCrawler() {
	$(".crawlspin").show();
	
	window.crawlerIO = true;
	
	window.crawlList = $.trim($('#urls').val()).split("\n");
	var switcharoo = [];
	$.each(window.crawlList, function(i, el){
		if($.inArray(el, switcharoo) === -1) switcharoo.push(el);
	});
	window.crawlList = switcharoo;
	window.crawlList.sort();
	$('#urls').val(window.crawlList.join("\n"));
	
	$('#urls').prop('disabled', true);
	$('.crawlnow').prop('disabled', true);
	$('.crawlstop').prop('disabled', false);
	
	window.crawlerBatched = 0;
	
	window.tim = setInterval(function(){ processCrawler(); }, window.delay);
	
	update_progress();
	
	window.scrollTo(0,0);
}

function stopCrawler() {
	window.crawlerIO = false;
	clearInterval(window.tim);
	update_progress();
}

// Add Content type button click
$(document).on("click", ".add-ct-click", function() {
	$(".box-map").append($(".box-container-wrapper").clone().html().replace("%ptype%", $("#ctt").val()));
	return false;
});

// Add new field button click
$(document).on("click", ".add-field", function() {
	$(this).parents(".box-container .fold").append(
		$(this).parents(".box-container").find(".field-wrap").html()
	);
	return false;
});

// Delete field button click
$(document).on("click", ".del", function() {
	$(this).parents(".field-sub-wrap").remove();
	return false;
});

// Expand / Retract group container
$(document).on("click", ".box-container h2", function() {
	$(this).parents(".box-container").find(".fold").toggle("slide");
	$(this).parents(".box-container").find(".arrow-point").toggle();
	return false;
});
