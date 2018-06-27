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

	window.magicfield = [];

// sleep function
function sleep (time) {
	return new Promise((resolve) => setTimeout(resolve, time));
}

// get WP jquery
$ = jQuery.noConflict();

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
					$(".need-tokens").show("fold");
					window.scrollTo(0,0);
					return false;
				} else {
					$(".need-tokens").hide("fold");
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
					data: {path: encodeURIComponent(path)},
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
var crawlUrlExists = function(urlX, onlyCheckURL = false) {
	
	// verify whitelist
	var pass = [];
	var whitelist = $('#whitelist').val().split("\n");
	for (key in whitelist) {
		if (urlX.indexOf($.trim(whitelist[key])) != -1) pass.push(true);
	}
	if (pass.length != whitelist.length) {
		window.crawledList.push(urlX);
		$("#urls-errors").append(urlX+"\n");
		return false;
	}
	
	/* verify blacklist */
	var pass_black = true;
	var blacklist = $('#blacklist').val().split("\n");
	for (key in blacklist) {
		if (urlX.indexOf($.trim(blacklist[key])) != -1) pass_black = false;
	}
	if (pass_black == false) {
		window.crawledList.push(urlX);
		$("#urls-errors").append(urlX+"\n");
		return false;
	}
	
	if (onlyCheckURL) {
		return true;
	}
	
	var urlMod = urlX.replace("http://", "");
	urlMod = urlMod.replace("https://", "");
	var urlCached = urlMod.split("/");
	var last = parseInt(urlCached.length - 1);

	if ((urlCached[last].indexOf(".") == -1) || (urlCached.length <= 1)) {
		urlCached.push("index.html");
	}
	urlCached = urlCached.join("/");

/*
	frontend fixes to match backend
*/
	// prevent path hacks
	urlCached = urlCached.replace(/\.\.\//g, "");
	// fix filenames for get query parameters
	urlCached = urlCached.replace(/\?/g, "_");
	// fix filenames for php files
	var forbidden = [".php", ".php3", ".php4", ".php5", ".phtml"];
	for (key in forbidden) {
		urlCached = urlCached.replace(forbidden[key], ".html");
	}
	
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
		container.push($(this).find(".inputmethod").val()); // post type
		container.push($(this).find(".postType").val()); // post type
		container.push($(this).find(".containerInstance").val());
		container.push($(this).find(".containerop").val());
		container.push($(this).find(".containeropeq").val());
		container.push($(this).find(".validator").val());
		container.push($(this).find(".op").val());
		container.push($(this).find(".opeq").val());
		container.push($(this).find(".idsel").val());
		container.push($(this).find(".idop").val());
		container.push($(this).find(".idopeq").val());
		
		var fields = [];
		$(this).find(".field-sub-wrap:visible").each(function() {
			var field = [];
			field.push($(this).find(".field-map").val());
			field.push($(this).find(".fieldsel").val());
			field.push($(this).find(".fieldop").val());
			field.push($(this).find(".fieldopeq").val());
			
			fields.push(field);
		});
		
		container.push(fields);
		
		c_array.push(container)
		
	});
	var $stringify = c_array;
	
	var ret = JSON.stringify($stringify);
	
	$.post(WB_PLUGIN_URL+"wp-admin/admin-post.php?action=wb_save_hook",
			  {
				mappings: ret,
				quicksave: "true"
			  },
			  function() {
					window.scrollTo(0,0);
					$(".wbmsg").fadeIn();
			  }
	)
	
	return ret;
}

function decompileMappings($stringify) {
	
	var json = JSON.parse($stringify);
	
	// start from scratch
	$(".box-map").html("");
	
	// load data
	$.each(json, function(k, main) { // main
		var boxMap = $(".box-map");
		$(".box-map").append($(".box-container-wrapper").clone().html());
		var $this_element = $(".box-map").find(".box-container").last();
		var inc = 0;
		$.each(main, function(kkk, field) { // field
			inc++;
			
			switch (inc) {
				case 1:
					$this_element.find(".inputmethod").val(field); // post type
					break;
				case 2:
					$this_element.find(".postType").val(field);
					break;
				case 3:
					$this_element.find(".containerInstance").val(field);
					break;
				case 4:
					$this_element.find(".containerop").val(field);
					break;
				case 5:
					$this_element.find(".containeropeq").val(field);
					break;
				case 6:
					$this_element.find(".validator").val(field);
					break;
				case 7:
					$this_element.find(".op").val(field);
					break;
				case 8:
					$this_element.find(".opeq").val(field);
					break;
				case 9:
					$this_element.find(".idsel").val(field);
					break;
				case 10:
					$this_element.find(".idop").val(field);
					break;
				case 11:
					$this_element.find(".idopeq").val(field);
					break;
			}
			if (inc > 11) {
				if (Array.isArray(field)) {
					$.each(field, function(ka, dig) { // row
						$this_element.find(".fold").append(
							$this_element.find(".field-wrap").clone().html()
						);
						
						var row_counter = 0;
						$.each(dig, function(ka, row) { // row
							row_counter++;
							switch (row_counter) {
								case 1:
									$this_element.find(".fold .field-sub-wrap").last().find(".field-map").val(row);
									break;
								case 2:
									$this_element.find(".fold .field-sub-wrap").last().find(".fieldsel").val(row);
									break;
								case 3:
									$this_element.find(".fold .field-sub-wrap").last().find(".fieldop").val(row);
									break;
								case 4:
									$this_element.find(".fold .field-sub-wrap").last().find(".fieldopeq").val(row);
									$this_element.find(".fold .field-sub-wrap").last().show();
									break;
							}
						});
					});
				}
			}
		});
	});
}

// Add Content type button click
$(document).on("click", ".add-ct.add-ct-click", function() {
	$(".box-map").append($(".box-container-wrapper").clone().html().replace("%ptype%", $("#ctt").val()));
	return false;
});

// Delete Content type button click
$(document).on("click", ".del-field", function() {
	$(this).parents(".box-container").slideUp(400, function() {$(this).remove()});
	return false;
});


// Add new field button click
$(document).on("click", ".add-ct.add-field", function() {
	$(this).parents(".box-container").find(".fold").append(
		$(this).parents(".box-container").find(".field-wrap").clone().html()
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

$(document).on("click", ".wiz-pick", function() {
	
	window.magicfield = $(this).parents(".body").first().find("input.selector").first();
	
	$( ".magic-pick #tag" ).val($(this).parents(".body").first().find("input.selector").first().val());
	
	$(".output-picked").html("");
	$(".output-picked-code").html("");
	$("#magicframe").attr("src","");

	$( ".magic-pick" ).dialog({
		title: "Magic Selection",
		width: ($(window).width() * .8),
		height: ($(window).height() * .9),
		modal: true,
		resizable: false,
		draggable: false,
		beforeClose: function() {
			$("#magicfile").val("");
			$(".test-select-step").hide();
			$(".sample-step").hide();
			$(".filter-select-step").hide();
			$(".frame-step").hide();
			$("#taglist").val("");
			$(".output-tabs a:first").click();
		}
	});
	
	return false;
});

$(document).on("click", "#savefilter", function() {
	window.magicfield.val($("#tag").val());
	$( ".magic-pick" ).dialog("close");
	$("#magicfile").val("");
	$(".test-select-step").hide();
	$(".sample-step").hide();
	$(".filter-select-step").hide();
	$(".frame-step").hide();
	$("#taglist").val("");
	$(".output-tabs a:first").click();
});



function magicgo() {
	$("#magicframe").attr("src", WB_PLUGIN_URL + "wp-content/plugins/content.wizard.build/cache/" + 	$("#magicfile").val());
	$(".frame-step").fadeIn("fast");
	$(".step-indicator").fadeIn("fast");

	setFrames();
}

function setFrames() {

	setTimeout( function() {
	$(".output-picked").html("");
	$(".output-picked-code").html("");
		var doc = $("iframe").first()[0].contentWindow.document;
		var $body = $('body', doc);
		$body.on("click", function(e) { // assign a handler
		
			$("#combo-wrap .options", window.top.document).html("");
		
			$(e.target).each(function(ii,el) {
				var str = "";
				
				var tag = $(el).prop("tagName").toLowerCase();
				if (typeof tag != "undefined") { str += tag}

				var id = $(el).attr('id');
				if (typeof id != "undefined") { str += "#" + id}

				var cl = $(el).attr('class');
				if (typeof cl != "undefined") { str += "." + cl.split(" ").join(".")}

				$("#combo-wrap .options", window.top.document).append('<a href="#" onclick="comboclick(this);return false;">{{'+str+"}}</a>");
				$("#taglist", window.top.document).val('{{'+str+'}}');
			})
			$(e.target).parents().each(function(ii, el) {
				var str = "";
				
				var tag = $(el).prop("tagName").toLowerCase();
				if (typeof tag != "undefined") { str += tag}

				var id = $(el).attr('id');
				if (typeof id != "undefined") { str += "#" + id}

				var cl = $(el).attr('class');
				if (typeof cl != "undefined") { str += "." + cl.split(" ").join(".")}

				$("#combo-wrap .options", window.top.document).append('<a href="#" onclick="comboclick(this);return false;">{{'+str+"}}</a>");
			});
			
			$("#taglist", window.top.document).show();
			$("#wizsetter", window.top.document).show();
			$("#combo-wrap .options", window.top.document).hide("slide","swing",100);
			$(".drop-select", window.top.document).css('display', 'inline-block');
			$(".test-select-step").fadeIn("fast");
			$(".step-indicator").hide();

			return false;
		});
	}, 1000);
}


function setTag() {
	//var doc = $("iframe").first()[0].contentWindow.document;
	var tested_output = parseEntry($("#taglist").val(), document.getElementById("magicframe").contentWindow.location.href, document.getElementById('magicframe').contentWindow.document.body.innerHTML);

	$("#tag").val($("#taglist").val());

	$(".output-picked").html(tested_output);
	$(".output-picked-code").text(tested_output);
	$(".filter-select-step").fadeIn("fast");
	$(".sample-step").fadeIn("fast");

}

function comboclick($this) {
	$($this).parents(".combo-wrap").find(".combo-input").val($($this).html());
	$($this).parents(".combo-wrap").find(".options").hide("fold","swing",100);
	
	$(".combo-wrap").find(".options").removeClass("fold").hide().parents(".combo-wrap").find(".drop-select").html("&darr;");
}

function toggleSelOptions($this) {
	$(".combo-wrap").not($($this).parents(".combo-wrap")).find(".options").removeClass("fold").hide().parents(".combo-wrap").find(".drop-select").html("&darr;");
	$($this).parents(".combo-wrap").find(".options").toggle("fold","swing",100).toggleClass("fold");
	if ($($this).parents(".combo-wrap").find(".options").hasClass("fold")) {
		$($this).parents(".combo-wrap").find(".drop-select").html("&uarr;");
	} else {
		$($this).parents(".combo-wrap").find(".drop-select").html("&darr;");
	}
}

function parseEntry(query, url, ht, isContainer = false) {
	
	var container_array = [];
	
	// parse regex expressions (triple brackets)
	var re = new RegExp('{{{(.*)}}}', 'g');
	q = query.match(re);
	for (qq in q) {
		var newregex = q[qq].replace("{{{", '').replace("}}}", '');
		newregex = new RegExp(newregex, 'g');
		newq = ht.match(newregex).join("");
		
		if (isContainer) {
			var matches = ht.match(newregex);
			for (found_match in matches) {
				container_array.push(matches[found_match]);
			}
		}
		
		query = query.replace(q[qq], newq);
	}

	// parse jquery expressions (double brackets)
	re = new RegExp('{{(.*)}}', 'g');
	q = query.match(re);
	for (qq in q) {
		var newjq = q[qq].replace("{{", '').replace("}}", '');
		code = $('<div>'+ht+'</div>').find(newjq);
		appendHTML = '';
		code.each(function() {
			var to_push = $(this)[0].outerHTML;
			if (isContainer) {
				container_array.push(to_push);
			} else {
				appendHTML += to_push;
			}
		});
		query = query.replace(q[qq], appendHTML);
	}
	

	// parse %url%
	ret = query.replace("%url%", url);
	
	// ret remaining
	if (isContainer) {
		return container_array;
	}
	return ret;
}

$(document).on("click", ".output-tabs a", function() {
	$(".output-tabs a").removeClass("selected");
	$(this).addClass("selected");
	if ($(this).hasClass("code")) {
		$(".output-picked-code").show();
		$(".output-picked").hide();
	} else {
		$(".output-picked-code").hide();
		$(".output-picked").show();
	}
	return false;
});

function mappings_run(offset) {
	$.post(WB_PLUGIN_URL+"wp-admin/admin-post.php?action=wb_mappings_hook", {
			config: compileMappings(),
			runmappings: true,
			offset: offset
		},
		function(data) {
			// exit code
			if ($.trim(data) == "EOQ") return;
			
			// else, continue
			offset = offset + 15;
			sleep(window.delay).then(() => {
				mappings_run(offset);
			});
		}
	);
	
	return false;
}

