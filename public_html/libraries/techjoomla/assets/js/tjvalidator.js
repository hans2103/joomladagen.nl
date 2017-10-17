techjoomla.jQuery(window).load(function(){
	document.formvalidator.setHandler('natural-number', function(value, element) {
		value = punycode.toASCII(value);
		var regex = /^[1-9]\d*$/;
		return regex.test(value);
	});

	document.formvalidator.setHandler('whole-number', function(value, element) {
		value = punycode.toASCII(value);
		var regex = /^[0-9]{1,9}$/;
		return regex.test(value);
	});

	document.formvalidator.setHandler('ymd-date', function(value, element) {
		//value = punycode.toASCII(value);
		var regex = /^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/;
		return regex.test(value);
	});

	document.formvalidator.setHandler('datetime', function(value, element) {
		//value = punycode.toASCII(value);
		var regex = /^(\d{4})(\/|\-)(\d{1,2})(\/|\-)(\d{1,2})\s(\d{1,2})(\/|\:)(\d{1,2})(\/|\:)(\d{1,2})$/;
		return regex.test(value);
	});

	document.formvalidator.setHandler('positive-number', function(value, element) {
		//value = punycode.toASCII(value);
		var regex = /^[+]?([0-9]+(?:[\.][0-9]*)?|\.[0-9]+)$/;
		return regex.test(value);
	});
});
