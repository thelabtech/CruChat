/**
* scripts.js
* Main js file
* @author Cru
* @license GNU Public License
*/

$(document).ready(function() {

	// ----- Begin: browser check script ----- //
	
	var BrowserDetect = {
		init: function () {
			this.browser = this.searchString(this.dataBrowser) || "An unknown browser";
			this.version = this.searchVersion(navigator.userAgent)
				|| this.searchVersion(navigator.appVersion)
				|| "an unknown version";
			this.OS = this.searchString(this.dataOS) || "an unknown OS";
		},
		searchString: function (data) {
			for (var i=0;i<data.length;i++)	{
				var dataString = data[i].string;
				var dataProp = data[i].prop;
				this.versionSearchString = data[i].versionSearch || data[i].identity;
				if (dataString) {
					if (dataString.indexOf(data[i].subString) != -1)
						return data[i].identity;
				}
				else if (dataProp)
					return data[i].identity;
			}
		},
		searchVersion: function (dataString) {
			var index = dataString.indexOf(this.versionSearchString);
			if (index == -1) return;
			return parseFloat(dataString.substring(index+this.versionSearchString.length+1));
		},
		dataBrowser: [
			{
				string: navigator.userAgent,
				subString: "Chrome",
				identity: "Chrome"
			},
			{ 	string: navigator.userAgent,
				subString: "OmniWeb",
				versionSearch: "OmniWeb/",
				identity: "OmniWeb"
			},
			{
				string: navigator.vendor,
				subString: "Apple",
				identity: "Safari",
				versionSearch: "Version"
			},
			{
				prop: window.opera,
				identity: "Opera",
				versionSearch: "Version"
			},
			{
				string: navigator.vendor,
				subString: "iCab",
				identity: "iCab"
			},
			{
				string: navigator.vendor,
				subString: "KDE",
				identity: "Konqueror"
			},
			{
				string: navigator.userAgent,
				subString: "Firefox",
				identity: "Firefox"
			},
			{
				string: navigator.vendor,
				subString: "Camino",
				identity: "Camino"
			},
			{	// for newer Netscapes (6+)
				string: navigator.userAgent,
				subString: "Netscape",
				identity: "Netscape"
			},
			{
				string: navigator.userAgent,
				subString: "MSIE",
				identity: "Explorer",
				versionSearch: "MSIE"
			},
			{
				string: navigator.userAgent,
				subString: "Gecko",
				identity: "Mozilla",
				versionSearch: "rv"
			},
			{ 	// for older Netscapes (4-)
				string: navigator.userAgent,
				subString: "Mozilla",
				identity: "Netscape",
				versionSearch: "Mozilla"
			}
		],
		dataOS : [
			{
				string: navigator.platform,
				subString: "Win",
				identity: "Windows"
			},
			{
				string: navigator.platform,
				subString: "Mac",
				identity: "Mac"
			},
			{
				string: navigator.userAgent,
				subString: "iPhone",
				identity: "iPhone/iPod"
			},
			{
				string: navigator.platform,
				subString: "Linux",
				identity: "Linux"
			}
		]
	
	};
	
	BrowserDetect.init();
	
	if (BrowserDetect.browser == "Explorer" & BrowserDetect.version <= 8) {
		window.location = "browserUpdate.php";
	}
	else {
	
		// console.log(BrowserDetect.browser);
		// console.log(BrowserDetect.version);
		// console.log(BrowserDetect.OS);
		
		// ----- End: browser check script ----- //
	
		$.ajax({
			url: 'retrieve.php?type=timeline',
			type: 'POST',
			beforeSend: function() {
				$('#spinner').show();
			},
			success: function(data) {
				$('#twitterUserTimeline').html(data);
				$.ajax({
					url: 'retrieve.php?type=mentions',
					type: 'POST',
					success: function(data) {
						$('#twitterUserMentions').html(data);
					},
					error: function() {
						var errorHtml = '<h2>We\'re sorry. It looks like something went wrong.</h2><h2><a href="index.php" rel="external">GO TO HOME PAGE</a></h2>';
						$('#twitterUserTimeline').html(errorHtml);
					}
				});
			},
			complete: function() {
				$('#spinner').hide();
			},
			error: function() {
				var errorHtml = '<h2>We\'re sorry. It looks like something went wrong.</h2><h2><a href="index.php" rel="external">GO TO HOME PAGE</a></h2>';
				$('#twitterUserTimeline').html(errorHtml);
			}
		});
	
		// ----- Begin: retrieve.php scripts ----- //
		
		$('#linkTimeline, .refreshTimeline').click(function() {
		
			$.ajax({	
				url: 'retrieve.php?type=timeline',
				type: 'POST',
				beforeSend: function() {
					$('#spinner').show();
				},
				success: function(data) {
					$('#twitterUserTimeline').html(data);
					$.ajax({
						url: 'retrieve.php?type=mentions',
						type: 'POST',
						success: function(data) {
							$('#twitterUserMentions').html(data);
						}
					});
				},
				complete: function() {
					$('#spinner').hide();
				},
				error: function() {
					var errorHtml = '<h2>We\'re sorry. It looks like something went wrong.</h2><h2><a href="index.php" rel="external">GO TO HOME PAGE</a></h2>';
					$('#twitterUserTimeline').html(errorHtml);
				}
			});
			
		});
		
		$('#linkConversations, #refreshConversations').click(function() {
		
			$.ajax({
				url: 'retrieve.php?type=myconversations',
				type: 'POST',
				beforeSend: function() {
					$('#spinner').show();
				},
				success: function(data) {
					$('#twitterMyConversations').html(data);
				},
				complete: function() {
					$('#spinner').hide();
				},
				error: function() {
					var errorHtml = '<h2>We\'re sorry. It looks like something went wrong.</h2><h2><a href="index.php" rel="external">GO TO HOME PAGE</a></h2>';
					$('#twitterUserTimeline').html(errorHtml);
				}
			});
			
		});
		
		$('#linkAllConversations, #refreshAllConversations').click(function() {
		
			$.ajax({
				url: 'retrieve.php?type=allconversations',
				type: 'POST',
				beforeSend: function() {
					$('#spinner').show();
				},
				success: function(data) {
					$('#twitterAllConversations').html(data);
				},
				complete: function() {
					$('#spinner').hide();
				},
				error: function() {
					var errorHtml = '<h2>We\'re sorry. It looks like something went wrong.</h2><h2><a href="index.php" rel="external">GO TO HOME PAGE</a></h2>';
					$('#twitterUserTimeline').html(errorHtml);
				}
			});
			
		});
		
		$('.linkSearch').click(function() {
		
			var searchTerm = $(this).attr('id');
		
			$.ajax({
				url: 'retrieve.php?type=search&term=' + searchTerm,
				type: 'POST',
				beforeSend: function() {
					$('#spinner').show();
				},
				success: function(data) {
					$('#twitterSearch-' + searchTerm).html(data);
				},
				complete: function() {
					$('#spinner').hide();
				},
				error: function() {
					var errorHtml = '<h2>We\'re sorry. It looks like something went wrong.</h2><h2><a href="index.php" rel="external">GO TO HOME PAGE</a></h2>';
					$('#twitterUserTimeline').html(errorHtml);
				}
			});
			
		});
		
		$('#refreshSearch-pray_for_me').click(function() {
		
			var searchTerm = $(this).attr('id').split('-')[1];
		
			$.ajax({
				url: 'retrieve.php?type=search&term=' + searchTerm,
				type: 'POST',
				beforeSend: function() {
					$('#spinner').show();
				},
				success: function(data) {
					$('#twitterSearch-' + searchTerm).html(data);
				},
				complete: function() {
					$('#spinner').hide();
				},
				error: function() {
					var errorHtml = '<h2>We\'re sorry. It looks like something went wrong.</h2><h2><a href="index.php" rel="external">GO TO HOME PAGE</a></h2>';
					$('#twitterUserTimeline').html(errorHtml);
				}
			});
			
		});
		
		// ----- End: retrieve.php scripts ----- //
		
		// ----- Begin: modal scripts ----- //
		
		$('.resourceInsert').live('click', function(event) {
			var id = $(this).attr("id").split('-')[2];
			var anchor = $(this).attr("name");
			$('#text-' + id).focus();
			$('#text-' + id).val($('#text-' + id).val() + anchor + ' ').focus();
		});
		
		$('.modal').live('hide', function() {
			var id = $(this).attr('id').split('-')[1];
			var user = $(this).find('span.username').children('small').text();
			$('#text-' + id).val(user);
		});
		
		$('.modal').live('show', function() {
			var id = $(this).attr('id').split('-')[1];
			setTimeout(function() { 
				$('#text-' + id).focus();
				$('#text-' + id).val($('#text-' + id).val() + ' ').focus();
			}, 5);
		});
		
		// ----- End: modal scripts ----- //
	
		// ----- Begin: tweet.php scripts ----- //
	
		$('.modal-form').live('submit', function(e) {
			
			e.preventDefault();
			
			var formTweetId = $(this).attr('id').split('-')[1];
			var formTweetUser = $('#tweet-' + formTweetId).children('.text').children('.username').text().split('@')[1];
			var formTweetUserImage = $('div#modal-' + formTweetId).children('div.modal-footer').children('div.tweet').children('span.profileImage').children('img').attr('src');
			var formTweetTime = $('#tweet-' + formTweetId).children('.text').children('.timeRaw').text();
			var formTweetText = $('#tweet-' + formTweetId).children('.text').children('.tweetText').text();
			var formReplyText = $('#text-' + formTweetId).val();
			
			$.ajax({	
				url: 'tweet.php',
				type: 'POST',
				data: {
					tweetId: 		formTweetId,
					tweetUser:		formTweetUser,
					tweetUserImage:	formTweetUserImage,
					tweetText:		formTweetText,
					tweetTime:		formTweetTime,
					replyText:		formReplyText,
				},
				beforeSend: function() {
					$('#spinner').show();
				},
				success: function(data) {
					alert(data);
					$('#text-' + formTweetId).val('');
					$('#modal-' + formTweetId).modal('hide');
					$('#tweet-' + formTweetId).find('.reply').css('display','none');
					$('#tweet-' + formTweetId).find('.replySuccess').text('You successfully replied to this tweet!');
					$('#tweet-' + formTweetId).find('.replySuccess').css('display','inline');
				},
				complete: function() {
					$('#spinner').hide();
				},
				error: function() {
					var errorHtml = '<h2>We\'re sorry. It looks like something went wrong.</h2><h2><a href="index.php" rel="external">GO TO HOME PAGE</a></h2>';
					$('#twitterUserTimeline').html(errorHtml);
				}
			});
			
		});
	
		// ----- End: tweet.php scripts ----- //
	
		// ----- Begin: Display scripts ----- //
		
		$('#btnMenu').click(function(){
			$('#listContent').toggle('slide', { direction: 'left' }, 250);
		});
		
		$('#linkTimeline, #linkConversations, #linkAllConversations, #pray_for_me').click(function(){
			
			var viewportwidth = window.innerWidth;
			
			if (viewportwidth <= 768){
				$('#listContent').hide('slide', { direction: 'left' }, 250);
			}
		});
		
		$(window).resize(function(){
			var viewportwidth = window.innerWidth;
			if (viewportwidth <= 750){
				$('#listContent').css('display','none');
			}
			else{
				$('#listContent').css('display','inline');
			}
		});
	
	}
	
	// ----- End: Display scripts ----- //

});

function parseTwitterDate(stamp) {
	var tweetDateRaw = Date.parse(stamp);
	var todayDateRaw = Date.parse(new Date());
	var timeDifference = (todayDateRaw - tweetDateRaw)/1000;
	
	if (timeDifference < 1) {
		var tweetTime = "Just now";
	}
	else if (timeDifference < 60) {
		var tweetTime = Math.round(timeDifference);
		if (tweetTime == 1) {
			tweetTime = tweetTime + " second ago";
		}
		else {
			tweetTime = tweetTime + " seconds ago";
		}
	}
	else if (timeDifference < (60*60)) {
		var tweetTime = Math.round(timeDifference/60);
		if (tweetTime == 1) {
			tweetTime = tweetTime + " minute ago";
		}
		else {
			tweetTime = tweetTime + " minutes ago";
		}
	}
	else if (timeDifference < (60*60*24)) {
		var tweetTime = Math.round(timeDifference/(60*60));
		if (tweetTime == 1) {
			tweetTime = tweetTime + " hour ago";
		}
		else {
			tweetTime = tweetTime + " hours ago";
		}
	}
	else {
		var tweetTime = Math.round(timeDifference/(60*60*24));
		if (tweetTime == 1) {
			tweetTime = tweetTime + " day ago";
		}
		else {
			tweetTime = tweetTime + " days ago";
		}
	}
	
	return tweetTime;
}