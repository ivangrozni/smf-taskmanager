$(function () {
	// Relative time output with moment.js
	$('span.relative-time').each(function () {
		var time = this.innerHTML;
		var newTime = moment(time, "YYYY-MM-DD").fromNow();
		this.innerHTML = newTime;
	});
});
