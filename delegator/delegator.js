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
	$('input.kalender').pikaday({
		format: "YYYY-MM-DD",
		i18n: {
		    previousMonth : 'Levo',
		    nextMonth     : 'Naslednji mesec',
		    months        : ['Januar','Februar','Marec','April','Maj','Junij','Julij','Avgust','September','Oktober','November','December'],
		    weekdays      : ['Nedelja','Ponedeljek','Torek','Sreda','Četrtek','Petek','Sobota'],
		    weekdaysShort : ['Ned','Pon','Tor','Sre','Čet','Pet','Sob']
		}
	});
});
