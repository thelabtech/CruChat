/**
* tour.js
* Sets up the site tour feature
* @author Cru
* @license GNU Public License
*/

jQuery(function($) {

	var viewportwidth = window.innerWidth;
	
	var tour = new Tour();
	
	tour.addStep({
		element: "#mainTitle",
		placement: "bottom",
		title: "Welcome to Cru Chat!",
		content: "We're excited for you to be a part of our customer service for God. Continue on this tour to learn how to use this app."
	});
	if (viewportwidth <= 768) {
		tour.addStep({
			element: "#btnMenu",
			placement: "right",
			title: "Show menu",
			content: "Click this button to show/hide the menu."
		});
	}
	tour.addStep({
		element: "#linkTimeline",
		placement: "right",
		title: "Home Tab",
		content: "View the Twitter timeline and mentions for @Cru_Chat."
	});
	tour.addStep({
		element: "#linkConversations",
		placement: "right",
		title: "My Conversations Tab",
		content: "View all of the tweets that you have personally replied to."
	});
	tour.addStep({
		element: "#linkAllConversations",
		placement: "right",
		title: "All Conversations Tab",
		content: "View all of the tweets that every Cru Chat user has replied to."
	});
	tour.addStep({
		element: "#pray_for_me",
		placement: "right",
		title: "Search Tab",
		content: "View a list of tweets that contain the phrase \"pray for me\" as well as the hashtag #prayforme. Your will be able to view those tweets as well as reply to them here."
	});
	tour.addStep({
		element: "#tabContent",
		placement: "top",
		title: "Content",
		content: "After selecting a tab all of the content will show up here. Click on the refresh button to make sure that you have the latest content."
	});
	tour.addStep({
		element: "#feedback",
		placement: "top",
		title: "Feedback",
		content: "Have some ideas to improve Cru Chat? Is something not working the way you think it should? Click here to give us your feedback."
	});
	
	tour.start();

	$("#tourStart").click(function (e) {
		e.preventDefault();
		if (viewportwidth <= 768) {
			if ($('#listContent').is(":hidden")) {
				$('#listContent').toggle('slide', { direction: 'left' }, 250);
			}
		}
		tour.restart();
	});
	
});