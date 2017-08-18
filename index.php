<?php

?>
<!DOCTYPE html>
<html>
<head>
	<title>Spotergy</title>
	<link href="https://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet">
	<link rel="stylesheet" type="text/css" href="bootstrap/bootstrap.css">
	<link rel="stylesheet" type="text/css" href="app.css">
</head>
<body>
    <div class="text-center mt-5">
    	<h1>Synergy Studio Radio</h1>
    </div>
    <div class="slackcheck">
    	Post to slack? <input type="checkbox" class="slackcheckbox" name="slackcheck" value="0" />
    </div>
	<div class="container">
		<div class="login-container hidden" id="js-login-container">
	    	<button class="btn btn-outline-success" id="js-btn-login">Login with Spotify</button>
	    </div>
	    <div class="main-container hidden" id="js-main-container"></div>
	</div>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.js" type="text/javascript" charset="utf-8"></script>
	<script src="spotify-player.js"></script>
	<script>
		var mainContainer = document.getElementById('js-main-container'),
		    loginContainer = document.getElementById('js-login-container'),
		    loginButton = document.getElementById('js-btn-login'),
		    background = document.getElementById('js-background');

		var spotifyPlayer = new SpotifyPlayer();
		var currentlyPlaying = null;

		function millisToMinutesAndSeconds(millis) {
			var minutes = Math.floor(millis / 60000);
			var seconds = ((millis % 60000) / 1000).toFixed(0);
			return minutes + ":" + (seconds < 10 ? '0' : '') + seconds;
		}

		var template = function (data) {
			var current = millisToMinutesAndSeconds(data.progress_ms);
			var total = millisToMinutesAndSeconds(data.item.duration_ms);
			return `
			<div class="main-wrapper">
				<div class="now-playing__img">
					<img src="${data.item.album.images[0].url}">
				</div>
				<div class="now-playing__side">
					<div class="now-playing__name">${data.item.name}</div>
					<div class="now-playing__artist">${data.item.artists[0].name}</div>
					<div class="now-playing__addedby">Added by: ${data.addedby}</div>
					<div class="now-playing__time">${current} / ${total}</div>
					<div class="progress">
						<div class="progress-bar" style="width:${data.progress_ms * 100 / data.item.duration_ms}%"></div>
					</div>
				</div>
			</div>
			<div class="background" style="background-image:url(${data.item.album.images[0].url})"></div>
			`;
		};

		spotifyPlayer.on('update', response => {
			var songid = response.item.id
			spotifyPlayer.getPlaylist('iamandrewd', '4NOIVhPSgdPPvIaT1LE5at')
			.then(function(data) {
				for (var i = 0; i < data.tracks.items.length; i++) {
					var trackid = data.tracks.items[i].track.id;
					if (trackid == songid) {
						var addedby = data.tracks.items[i].added_by.id;
						spotifyPlayer.getUser(addedby)
						.then(function(data) {
							var user = data.display_name;
							if (user == null) {
								user = data.id;
							}
							response.addedby = user;
		 					mainContainer.innerHTML = template(response);

		 					if ($('input.slackcheckbox').is(':checked')) {
			 					if (songid != currentlyPlaying) {
			 						currentlyPlaying = songid;
			 						var url = 'https://hooks.slack.com/services/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';
			 						var jsonstring = JSON.stringify({
			 						        "channel": "#fridayradio",
			 								"mrkdwn": true,
			 								"icon_emoji": ":notes:",
			 								"username": 'Friday Radio',
			 								"attachments": [{
			 									"pretext": 'Now Playing :radio:',
			 									"text": response.item.name + ' - ' + response.item.artists[0].name,
			 									"fields": [{'value': 'Added by: '+user}]
			 								}]
			 						    });
			 						$.ajax({
			 						    data: 'payload=' + jsonstring,
			 						    dataType: 'json',
			 						    processData: false,
			 						    type: 'POST',
			 						    url: url
			 						});
			 					}
			 				}

						});

					}
				}
			});
		});

		spotifyPlayer.on('login', user => {
		  if (user === null) {
		    loginContainer.style.display = 'block';
		    mainContainer.style.display = 'none';
		  } else {
		    loginContainer.style.display = 'none';
		    mainContainer.style.display = 'block';
		  }
		});

		loginButton.addEventListener('click', () => {
		    spotifyPlayer.login();
		});

		spotifyPlayer.init();

	</script>
</body>
</html>