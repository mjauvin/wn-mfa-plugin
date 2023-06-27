$(function () {
	  var authPostURL = $.wn.backendUrl("studioazura/mfa/auth/signin");
	  $("form").attr("action", authPostURL);
});
