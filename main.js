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
				if (parseInt($.trim($(".progress-box #counter").html())) <= 0) {
					$(".need-tokens").show("slide");
					window.scrollTo(0,0);
					return false;
				} else {
					$(".need-tokens").hide("slide");
				}
				
			}
		});
	
	});
	
	$("#apikey").click();
	
	// update progress bars
	ini_crawl_stats();
	update_progress();
	
	// select a tab
	get_tab();
	
});

function get_tab() {
	if (parseInt($(".progress-box #counter").html()) >= 1) {
		if (parseInt($(".progress-box .crawled-count").html()) >= 1) {
			$('.nav a[data-tab=map]').click();
		} else {
			$('.nav a[data-tab=urls]').click();
		}
	} // else first tab
}

function quicksave_call() {
	$.post(WB_PLUGIN_URL+"wp-admin/admin-post.php?action=wb_save_hook",
		{
			whitelist: $("#whitelist").val(),
			blacklist: $("#blacklist").val(),
			apikey: $("#apikey").val(),
			quicksave: "true"
		},
		function() {
			get_tab();
		}
	);
return false;
}

function setInProgress() {
	$(".crawlspin").show();
	$('#urls').prop('disabled', true);
	$('#whitelist').prop('disabled', true);
	$('.crawlnow').prop('disabled', true);
	$('.crawlstop').prop('disabled', false);
	$('#blacklist').prop('disabled', true);
	
}

function setStopped() {
	$("#apikey").click();
	$(".crawlspin").hide();
	$('#urls').prop('disabled', false);
	$('#whitelist').prop('disabled', false);
	$('.crawlnow').prop('disabled', false);
	$('.crawlstop').prop('disabled', true);
	$('#blacklist').prop('disabled', false);
}

function filter_array(test_array) {
    var index = -1,
        arr_length = test_array ? test_array.length : 0,
        resIndex = -1,
        result = [];

    while (++index < arr_length) {
        var value = test_array[index];

        if (value) {
            result[++resIndex] = value;
        }
    }

    return result;
}

// update progress bars
function update_progress() {
	var count = filter_array(window.crawledList).length;
	$('.crawled-count').html(count);
	var total = filter_array(window.crawlList).length + count;
	$('.crawled-total').html(total);
	var progress = parseInt(count / total * 100);
	$('.crawled-percent').html(progress);
	$('.crawled .progress').css('width', (progress / 100) * parseInt($('.crawled .total').width()));
}


// Crawl & Download

var crawlUrl = function(url, path) {
	
	var new_url = "http://content.wizard.build/authme.php?run&key=" +
	$("#apikey").val() + "&url=" + encodeURIComponent(url);
	setInProgress();
	
	$.ajax({
		dataType: 'html',
		url: new_url,
		success: function(data, textStatus, jqXHR) {
			setInProgress();
			$(".crawlspin").show();
			$.post(WB_PLUGIN_URL+"wp-admin/admin-post.php?action=wb_save_hook",
			  {
				url: url,
				data: data,
				path: path,
				whitelist: $("#whitelist").val(),
				blacklist: $("#blacklist").val(),
				apikey: $("#apikey").val()
			  },
			  function() {
				window.crawledList.push(url);
				$("#urls-done").append(url+"\n");
				update_progress();
				$.ajax({
					url: WB_PLUGIN_URL+"wp-admin/admin-post.php?action=wb_get_hook",
					dataType: 'json',
					context: document.body,
					success: function(data, textStatus, jqXHR) {
						if (data.to_crawl.length > 0) {
							$('#urls').val(data.to_crawl.join("\n"));
							window.crawlList = data.to_crawl;
						} else {
							setStopped();							
						}
						if (data.crawled.length > 0) {
							$('#urls-done').val(data.crawled.join("\n"))
							window.crawledList = data.crawled;
						}
						setStopped();							
						update_progress();
					},
					error: function() {
						$('#urls').val("");
						setStopped();
					}
				});
			  }
			);
		},
		error: function() {
			window.crawledList.push(url);
			$("#urls-errors").append(url+"\n");
			console.log("error crawling url: "+url)
			update_progress();
		}
	});
}

// verify if url is already cached, if not add to crawl list
// crawlUrl(urlX, urlCached);
var crawlUrlExists = function(urlX) {
	
	// verify whitelist
	var pass = [];
	var whitelist = $('#whitelist').val().split("\n");
	for (key in whitelist) {
		if (urlX.indexOf($.trim(whitelist[key])) != -1) pass.push(true);
	}
	if (pass.length != whitelist.length) {
		window.crawledList.push(urlX);
		$("#urls-errors").append(urlX+"\n");
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
	setInProgress();
	$.ajax({
		type: 'HEAD',
		url: urlModded,
		success: function() {
			console.log("Item Cached: " + urlModded + "\n");
			window.crawledList.push(urlX);
			update_progress();
			window.crawlerBatched--;
			setStopped();
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
		setInProgress();
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
	$.each(window.crawlList, function(i, el){
		if ($.inArray(el, completed) == -1) {
			window.crawlList.splice(i, 1);
		}
	});
	var completed = $.trim($('#urls-done').val()).split("\n");
	var switcharoo = [];
	$.each(window.crawlList, function(i, el){
		if (($.inArray(el, switcharoo) == -1) && ($.inArray(el, completed) == -1)) {
			switcharoo.push(el);
		}
	});

	window.crawlList = switcharoo;
	window.crawlList.sort();
	
	$('#urls').val(window.crawlList.join("\n"));
	
	window.crawledList = $("#urls-done").val().split("\n");
	
	update_progress();
	setStopped();
	
}
	
// init crawl
function initCrawler() {
	quicksave_call();
	if (parseInt($.trim($(".progress-box #counter").html())) <= 0) {
		$(".need-tokens").show("slide");
		window.scrollTo(0,0);
		return false;
	} else {
		$(".need-tokens").hide("slide");
	}
	
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
	
	setInProgress();
	
	window.crawlerBatched = 0;
	
	window.tim = setInterval(function(){ processCrawler(); }, window.delay);
	
	update_progress();
	
	window.scrollTo(0,0);
}

function stopCrawler() {
	window.crawlerIO = false;
	clearInterval(window.tim);
	update_progress();
	setStopped();
}

function compileMappings() {
	
	var c_array = [];
	$(".box-map .box-container").each(function() {
		var container = [];
		// content type header
		container.push("POSTYPE"); // post type
		container.push($(this).find(".instance_container").val());
		container.push($(this).find(".validator").val());
		container.push($(this).find(".op").val());
		container.push($(this).find(".opeq").val());
		container.push($(this).find(".idsel").val());
		container.push($(this).find(".idop").val());
		container.push($(this).find(".idopeq").val());
		
		var fields = [];
		$(this).find(".field-sub-wrap").each(function() {
			var field = [];
			field.push($(this).find(".field-map").val());
			field.push($(this).find(".fieldsel").val());
			field.push($(this).find(".fieldop").val());
			field.push($(this).find(".fieldopeq").val());
			
			fields.push(field.join(";;;"));
		});
		
		container.push(fields.join(">>>"));
		
		c_array.push(container.join("|||"))
		
	});
	$stringify = c_array.join("<<<");

	return $stringify;
	
}

function decompileMappings($stringify) {
	$stringify="POSTYPE|||{{.post}}|||{{.title}}|||Is not null/empty||||||%url%|||Text (Strip HTML Tags)|||String (Text)|||post_title;;;{{.title}};;;Text (Strip HTML Tags);;;String (Text)>>>post_title;;;{{.title}};;;Text (Strip HTML Tags);;;String (Text)>>>post_title;;;{{.title}};;;Text (Strip HTML Tags);;;String (Text)>>>post_title;;;{{.title}};;;Text (Strip HTML Tags);;;String (Text)<<<POSTYPE|||{{.post}}|||{{.title}}|||Is not null/empty||||||%url%|||Text (Strip HTML Tags)|||String (Text)|||post_title;;;{{.title}};;;Text (Strip HTML Tags);;;String (Text)>>>post_title;;;{{.title}};;;Text (Strip HTML Tags);;;String (Text)";
	var ctype = $stringify.split("<<<");
	
	$.each(ctype, function(k, v) {
		
		var head = v.split("|||");
		
		$.each(head, function(kk, vv) {
			
			var field = vv.split(">>>");
			
			$.each(field, function(kkk, vvv) {
				
				var singleton = vvv.split(";;;");
				
				$.each(singleton, function(kkkk, vvvv) {
					
					console.log(vvvv);
					
				});
				
			});
		});
		
	});
}

// Add Content type button click
$(document).on("click", ".add-ct-click", function() {
	$(".box-map").append($(".box-container-wrapper").clone().html().replace("%ptype%", $("#ctt").val()));
	return false;
});

// Delete Content type button click
$(document).on("click", ".del-field", function() {
	$(this).parents(".box-container").slideUp(400, function() {$(this).remove()});
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
