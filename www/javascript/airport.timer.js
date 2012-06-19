function airport_timer(){
    
	// TO DO: this blocks when mobile safari switches
	// tabs or is put in background mode

	var sw = $("#stopwatch").stopwatch();

	var start = null;
	var stop = null;

	var reset = function(){

		start = null;
		stop = null;

		$("#reset").hide();

		$("#airport_wrapper").show();
		$("#airport").removeAttr("disabled");
		$("#airport").val("");
		$("#airport").focus();

		$("#terminal_wrapper").hide();
		$("#terminal").removeAttr("disabled");
		$("#terminal").val("");

		$("#checkpoint_wrapper").hide();
		$("#checkpoint").removeAttr("disabled");
		$("#checkpoint").val("");

		$("#timer_wrapper").hide();
		$("#timer_stop").hide();
		$("#timer_start").removeAttr("disabled");
		$("#timer_stop").removeAttr("disabled");

		$("#stopwatch").hide();
		$("#commentary").hide();

		$("#submit_wrapper").hide();

		$("#stopwatch").hide();
		$("#confirm").hide();

		if (sw){
			sw.stopwatch('reset');
		}
	};

	var prettyprint_duration = function(start, stop){

		var time = (stop - start);

		var seconds = Math.floor(time % 60);
		time /= 60;

		var minutes = Math.floor(time % 60);
		time /= 60;

		var hours = Math.floor(time % 24);

		/*
		console.log("hours " + hours);
		console.log("minutes " + minutes);
		console.log("seconds " + seconds);
		*/

		var duration = new Array();

		if (hours==1){

			if (minutes > 20){
				minutes = parseInt(minutes / 10) * 10;
				duration.push("an hour and " + minutes + " minutes");
			}

			else {
				duration.push("an hour");			
		    	}
		}

		else if (hours > 1){

			if (minutes > 20){
			    minutes = parseInt(minutes / 10) * 10;
			    duration.push(hours + " hours and " + minutes + " minutes");
			}

			else {
			    duration.push(hours + " hours");			    
			}
		}

		else if ((minutes == 1) && (seconds > 20)){
			duration.push("a minute and change");
		}

		else if (minutes == 1){
			duration.push("a minute");
		}

		else if (minutes > 1){

			if ((seconds > 30) && ((minutes + 1) < 60)){
				minutes = minutes + 1;			    
			}

			duration.push("about " + minutes + " minutes");
		}

		if ((! hours) && (! minutes)){
			duration.push("less than a minute");
		}

		duration = duration.join(" and ");
		return duration;
	};

	if ($("#airport").val() != ''){
		reset();
	}

	$("#reset").click(function(){
		reset();
		return false;
	});

	$("#airport").change(function(){

		var airport = $("#airport").val();
		airport = airport.toUpperCase();

		var re = new RegExp("^[A-Z]{3}$");

		if (! re.exec(airport)){
			alert("Hey! That's not a valid 3-letter airport code.");
			$("#airport").val("");
			$("#airport").focus();
			return false;
		}

		$("#airport").val(airport);

		$("#terminal_wrapper").show();
		$("#checkpoint_wrapper").show();
		$("#terminal").focus();

		$("#reset").show();
	});

	$("#terminal").change(function(){
		// check to see if this is a value terminal for #airport

		var terminal = $("#terminal").val();
		terminal = terminal.toUpperCase();

		var checkpoint = $("#checkpoint").val();
		checkpoint = checkpoint.toUpperCase();

		var re = new RegExp("[A-Za-z0-9-_\s]+");

		if (! re.exec(terminal)){
			alert("Hey! that's not a valid terminal name");
			$("#terminal").val("");
			$("#terminal").focus();
			return false;
		}

		if ((checkpoint) && (! re.exec(checkpoint))){
			alert("Hey! that's not a valid checkpoint name");
			$("#checkpoint").val("");
			$("#checkpoint").focus();
			return false;
		}

		$("#terminal").val(terminal);
		$("#timer_wrapper").show();
	});

	$("#timer_start").click(function(){

		start = Math.round(new Date().getTime() / 1000);

		$("#airport").attr("disabled", "disabled");
		$("#terminal").attr("disabled", "disabled");
		$("#checkpoint").attr("disabled", "disabled");

		$("#airport_wrapper").hide();
		$("#terminal_wrapper").hide();
		$("#checkpoint_wrapper").hide();

		var airport = $("#airport").val();
		var terminal = $("#terminal").val();
		var checkpoint = $("#checkpoint").val();

		var msg = "You're waiting in the security line at " + airport + "'s Terminal " + terminal;

		if (checkpoint){
			msg += " (" + checkpoint + ")";
		}

		$("#timer_start").attr("disabled", "disabled");
		$("#timer_stop").show();

		$("#commentary").html(msg);
		$("#commentary").show();

		$("#stopwatch").show();
		sw.stopwatch("start");

		return false;
	});

	$("#timer_stop").click(function(){

		stop = Math.round(new Date().getTime() / 1000);

		sw.stopwatch("stop");

		$("#stopwatch").hide();

		$("#timer_stop").attr("disabled", "disabled");

		$("#timer_wrapper").hide();
		$("#submit_wrapper").show();

		var airport = $("#airport").val();
		var terminal = $("#terminal").val();
		var checkpoint = $("#checkpoint").val();

		var duration = prettyprint_duration(start, stop);

		var msg = "You spent " + duration + " in security at ";
		msg += "<span class=\"airport_name\">" + airport + "</span>'s ";
		msg += "terminal <span class=\"terminal_name\">" + terminal + "</span>, yeah?";

		$("#airport_wrapper").hide();
		$("#terminal_wrapper").hide();
		$("#checkpoint_wrapper").hide();

		$("#commentary").html(msg)

		return false;
	});

	$("#airport_timer").submit(function(){

		var airport = $("#airport").val();
		var terminal = $("#terminal").val();
		var checkpoint = $("#checkpoint").val();

		var duration = stop - start;
		var str_duration = prettyprint_duration(start, stop);

		var api_args = {
			'method': 'airports.recordWaitTime',
			'airport': airport,
			'terminal': terminal,
			'checkpoint': checkpoint,
			'start': start,
			'stop': stop,
			'duration': duration
		};

		var tweet = "I just spent " + str_duration + " in security at #" + airport +", terminal " + terminal + " #airporttimer";

		var api_rsp = function(rsp){

			// note the change in tense, now that the API has done something

			var broadcast = "Would you like to <a href=\"https://twitter.com/intent/tweet?text=" + encodeURIComponent(tweet) + "\">";
			broadcast += "tell the Tweeterverse</a>?";

			$("#tweet").html(broadcast);

			if (rsp['stat'] == 'ok'){

				var feed_url = "https://pachube.com/feeds/" + rsp['airport_id'];
				var status = "Okay! We've <a href=\"" + feed_url + "\" target=\"_pachube\">recorded</a> your wait time (" + str_duration + ").";

				$("#status").html(status);
				return;
 			}

			$("#status").html("Blargh! Something went wrong. The robots report :" + rsp['error']);
		};

		$.ajax({
			'url': "/api",
			'type': "POST",
			'data': api_args,
			'success': api_rsp
		});

		$("#submit_wrapper").hide();

		// see also: https://dev.twitter.com/docs/anywhere/tweet-box

		var broadcast = "<span id=\"tweet\">";
		broadcast += "Would you like to <a href=\"https://twitter.com/intent/tweet?text=" + encodeURIComponent(tweet) + "\">";
		broadcast += "tell the Tweeterverse";
		broadcast += "</a> while you wait?";
		broadcast += "</span>";

		var status = "<span id=\"status\">Okay. We're recording your wait time, now.</span> " + broadcast;

		$("#commentary").html(status);
		return false;
	});

}
