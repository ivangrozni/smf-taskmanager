$(function () {
	moment.locale("sl-SI");
	// Relative time output with moment.js
	$('span.relative-time').each(function () {
		var time = this.innerHTML;
		var newTime = moment(time, "YYYY-MM-DD").fromNow();
		this.innerHTML = newTime;
	});
	$('span.format-time').each(function () {
		var time = this.innerHTML;
		var newTime = moment(time, "YYYY-MM-DD").format("D. M. YYYY");
		this.innerHTML = newTime;
	});
});
