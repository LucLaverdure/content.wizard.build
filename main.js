/*   Globals	*/

	window.tim = 0;
	
// crawler
	window.delay = 2000;
	window.batch = 15;
	window.crawlerIO = false;
	window.crawlerBatched = 0;
	window.crawlList = [];
	window.crawledList = [];
	window.crawlcount = 0;
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

	// filetree - Data Browser
	$('#filesNfolders').fileTree({
		root: "../wp-content/plugins/content.wizard.build/cache/",
		script: WB_PLUGIN_URL + "wp-admin/admin-post.php?action=wb_browseme_hook",
		expandSpeed: 100
		}, function(file) { 
			$("#selectedFile").show();
			$("#selectedFile .download").attr('href', file);
		}
	);

	// navigation tabs
	$(".nav a").on("click", function() {
		$(".nav a").removeClass("selected");
		$(this).addClass("selected");
		$(".card").hide();
		$(".card." + $(this).data('tab')).fadeIn();
	});

	// test api key and colorize buttons based on key validity
	$("#apikey").on("input click", function() {
		$this = $(this);
		/*
		// check wizard package
		$.ajax({
			url: "http://content.wizard.build/authme.php?key="+$.trim($this.val()),
			dataType: 'text',
			context: document.body,
			success: function(data, textStatus, jqXHR) {
				if (parseInt($.trim($(".progress-box #counter").html())) <= 0) {
					// TODO: set wizard level
					window.scrollTo(0, 0);
					return false;
				}
			}
		});
		*/
	});
	
	$("#apikey").click();
	
	// select a tab
	get_tab();
	
});

function get_tab() {
	var cur_hash = window.location.hash;
	if (cur_hash == "#browse") {
		$(".nav .b").click();
	} else if (cur_hash == "#map") {
		$(".nav .m").click();
	}
}

function quicksave_call() {
	$.post(WB_PLUGIN_URL+"wp-admin/admin-post.php?action=wb_save_hook",
		{
			whitelist: $("#whitelist").val(),
			blacklist: $("#blacklist").val(),
			apikey: $("#apikey").val(),
			quicksave: "true",
			paramRemoveGets: ($("input[name=removegets]").is(":checked")) ? "Y" : "N",
			paramRemoveHashes: ($("input[name=removehashes]").is(":checked")) ? "Y" : "N",
			paramPostJS: ($("input[name=jsenabled]").is(":checked")) ? "Y" : "N"
		},
		function() {
			get_tab();
		}
	);
	return false;
}

function crawlUrl() {
	
		// no post js crawl
		$(".crawlspin").show();
		$.post(WB_PLUGIN_URL+"wp-admin/admin-post.php?action=wb_save_hook",
		  {
			url: $("#urls").val(),
			whitelist: $("#whitelist").val(),
			blacklist: $("#blacklist").val(),
			apikey: $("#apikey").val(),
			paramRemoveGets: ($("input[name=removegets]").is(":checked")) ? "Y" : "N",
			paramRemoveHashes: ($("input[name=removehashes]").is(":checked")) ? "Y" : "N",
			paramPostJS: ($("input[name=jsenabled]").is(":checked")) ? "Y" : "N"
		  },
		  function(dataToCrawl) {
			$.ajax({
				dataType: 'html',
				url: WB_PLUGIN_URL + "wp-content/plugins/content.wizard.build/crawl.me.txt",
				success: function(data, textStatus, jqXHR) {
					if ($.trim(data) != "") {
						$("#urls").val(data);
						sleep(window.delay).then(() => {
							window.crawlcount += 15;
							$(".crawled-count").html(window.crawlcount);
							crawlUrl($("#urls").val());
						});
					} else {
						$(".crawlspin").hide();
					}
				}
			});
		  }
		);		

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
		
		// db
		container.push($(this).find(".dbhost").val());
		container.push($(this).find(".dbuser").val());
		container.push($(this).find(".dbpass").val());
		container.push($(this).find(".dbname").val());
		container.push($(this).find(".dbquery").val());

		// csv || xlsx
		if ($('.line1parsed').is(':checked')) {
			container.push("Y");
		} else {
			container.push("N");
		}
		container.push($(this).find(".fielddelimiter").val());
		container.push($(this).find(".enclosure").val());
		
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
					$(".wbmsg").html("Saved content mappings.").fadeIn();
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
				case 12:
					$this_element.find(".dbhost").val(field);
					break;
				case 13:
					$this_element.find(".dbuser").val(field);
					break;
				case 14:
					$this_element.find(".dbpass").val(field);
					break;
				case 15:
					$this_element.find(".dbname").val(field);
					break;
				case 16:
					$this_element.find(".dbquery").val(field);
					break;
				case 17:
					if (field=="Y") {
						$this_element.find(".line1parsed").prop("checked", true);
					} else {
						$this_element.find(".line1parsed").prop("checked", false);
					}
					break;
				case 18:
					$this_element.find(".fielddelimiter").val(field);
					break;
				case 19:
					$this_element.find(".enclosure").val(field);
					break;
			}
			
			if (inc > 19) {
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
		// show / hide fields based on input method
		input_change($this_element.find(".inputmethod"));
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
	
	var ext = $("#magicfile").val().split('.').pop();
	
	if (ext=="xlsx") {
		$("#magicframe").attr("src", WB_PLUGIN_URL+"wp-admin/admin-post.php?action=wb_xlsx_hook&file=" + $("#magicfile").val());
	} else if (ext=="csv") {
		
	} else {
		$("#magicframe").attr("src", WB_PLUGIN_URL + "wp-content/plugins/content.wizard.build/cache/" + $("#magicfile").val());
	}
	$(".frame-step").fadeIn("fast");
	$(".step-indicator").fadeIn("fast");

	setFrames();
}

function setFrames() {

	setTimeout( function() {
	$(".output-picked").html("");
	$(".output-picked-code").html("");
		var doc = $("iframe").first()[0].contentWindow.document;
		var $body = $('*', doc);
		$body.on("click", function(e) { // assign a handler
			var ext = $("#magicfile").val().split('.').pop();
			$("#combo-wrap .options", window.top.document).html("");
			if (ext=="xlsx") {
				$(e.target).each(function(ii,el) {
					// by sheet letters
					
					if (typeof $(el).data("sheetname") != "undefined") {
						$("#taglist", window.top.document).val($.trim($(el).data("sheetname").toLowerCase()));
						$("#combo-wrap .options", window.top.document).append('<a href="#" onclick="comboclick(this);return false;">'+$.trim($(el).data("sheetname").toLowerCase())+"</a>");
					} else {
						
						$("#combo-wrap .options", window.top.document).append('<a href="#" onclick="comboclick(this);return false;">{'+$(el).data("letterscol")+"}</a>");
						
						// by first row, col name
						$("#combo-wrap .options", window.top.document).append('<a href="#" onclick="comboclick(this);return false;">{'+$.trim($(el).data("colname").toLowerCase())+"}</a>");
						$("#taglist", window.top.document).val('{'+$.trim($(el).data("colname").toLowerCase())+'}');
						
						// by first row, col num
						$("#combo-wrap .options", window.top.document).append('<a href="#" onclick="comboclick(this);return false;">{'+$(el).data("colnum")+"}</a>");
					}
				});
			} else if (ext=="csv") {
				
			} else {
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
			}

		

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
	/*
	var tested_output = 
	parseEntry($("#taglist").val(),
	document.getElementById("magicframe").contentWindow.location.href, document.getElementById('magicframe').contentWindow.document.body.innerHTML);
	*/
	
	$.get( document.getElementById("magicframe").contentWindow.location.href, function( data ) {
		
		var xml = data.outerHTML || new XMLSerializer().serializeToString(data);
		
		// parseEntry(query, url, ht, isContainer = false) {
		var tested_output = parseEntry($("#taglist").val(),
		document.getElementById("magicframe").contentWindow.location.href,
		xml);

		$("#tag").val($("#taglist").val());

		$(".output-picked").html(tested_output);
		$(".output-picked-code").text(tested_output);
		$(".filter-select-step").fadeIn("fast");
		$(".sample-step").fadeIn("fast");
		
	});	
}

function comboclick($this) {
	
	if ($.trim($($this).data("val")) != "") {
		$($this).parents(".combo-wrap").find(".combo-input").val($($this).data("val"));
	} else {
		$($this).parents(".combo-wrap").find(".combo-input").val($($this).html());
	}
	
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

		var code = $('<div>'+ht+'</div>').find(newjq);

		appendHTML = '';
		code.each(function() {
			//var to_push = $(this)[0].outerHTML;
			var to_push = $(this)[0].outerHTML || new XMLSerializer().serializeToString($(this)[0]);

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
$(document).on("click", ".with-sel-confirm", function() {
	switch ($(".with-sel").val()) {
		case "del":
			
			var filenames = [];
			
			$(".chkfile:checked").each(function() {
				var $this = $(this);
				filenames.push($this.val());
			});

			$.post(WB_PLUGIN_URL+"wp-admin/admin-post.php?action=wb_delcache_hook", {
				killcache: filenames
			},
			function(data) {
				$('#filesNfolders').html("");
				$('#filesNfolders').fileTree({
					root: "../wp-content/plugins/content.wizard.build/cache/",
					script: WB_PLUGIN_URL + "wp-admin/admin-post.php?action=wb_browseme_hook",
					expandSpeed: 100
					}, function(file) { 
						$("#selectedFile").show();
						$("#selectedFile .download").attr('href', file);
					}
				);

			});
			
			break;
	}
});

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

function mappings_run(offset, mapped = false) {
	$(".mapspin").show();
	$(".mapped-count").html(offset);
	var mappings = "";
	if (mapped == false) {
		mappings = compileMappings();
	} else {
		mappings = mapped;
	}
	$.post(WB_PLUGIN_URL+"wp-admin/admin-post.php?action=wb_mappings_hook", {
			config: mappings,
			runmappings: true,
			offset: offset
		},
		function(data) {
			// exit code
			if ($.trim(data).indexOf("EOQ") !== -1) {
				$(".mapspin").hide();
				$(".wbmsg").html("All content migrated!").fadeIn();
				return;
			}
			
			// else, continue
			offset = offset + 35;
			sleep(window.delay).then(() => {
				mappings_run(offset, mappings);
			});
		}
	);
	
	return false;
}

function initCrawler() {
	crawlUrl();
	window.scrollTo(0, 0);
	return false;
}

function input_change($this) {
	// csv selected
	if ($($this).val() == "csv") {
		$($this).parents(".fold").find(".csv-show").show();
		$($this).parents(".fold").find(".csv-hide").hide();
	}
	
	if ($($this).val() == "xlsx") {
		$($this).parents(".fold").find(".xlsx-show").show();
		$($this).parents(".fold").find(".xlsx-hide").hide();
	}

	if ($($this).val() == "sql") {
		$($this).parents(".fold").find(".db-show").show();
		$($this).parents(".fold").find(".db-hide").hide();
	}

	if ($($this).val() == "scraper") {
		$($this).parents(".fold").find(".scraper-show").show();
		$($this).parents(".fold").find(".scraper-hide").hide();
	}

}
