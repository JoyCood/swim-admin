;(function() {
$.fn.setCursorPosition = function (pos) { 
	return this.each(function(index, elem) { 
		if(pos == undefined) {
			pos = this.value.length;
		}
		if(elem.setSelectionRange) { 
			elem.setSelectionRange(pos, pos); 
		} else if (elem.createTextRange) {
			var range = elem.createTextRange();
			range.collapse(true);
			range.moveEnd('character', pos);
			range.moveStart('character' , pos); 
			range.select(); 
		} 
	}); 
}

$.fn._focus = $.fn.focus;
$.fn.focus = function() {
	return this._focus().setCursorPosition();
}
})();