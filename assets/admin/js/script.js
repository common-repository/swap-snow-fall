(function( $ ) {
 
    // Add Color Picker to all inputs that have 'color-field' class
    $(function() {
        $('.ssf-color-picker').wpColorPicker();
    });
    
    var allRanges = document.querySelectorAll(".range-wrap");
    allRanges.forEach((wrap) => {
        var range = wrap.querySelector(".range");
        var bubble = wrap.querySelector(".bubble");

        range.addEventListener("input", () => {
            setBubble(range, bubble);
        });
        // setting bubble on DOM load
        setBubble(range, bubble);
    });

    function setBubble(range, bubble) {
        var val = range.value;
        var min = range.min || 0;
        var max =  range.max || 100;
        var offset = Number(((val - min) * 100) / (max - min));

        bubble.textContent = val;
        // yes, 14px is a magic number
        bubble.style.left = `calc(${offset}% - 14px)`;
    }

    $('#ssf-display-rules').on('change', function() {
        if ( 'specifics' === this.value ) {
            $('.ssf-display-rule-ids').show();
        } else {
            $('.ssf-display-rule-ids').hide();
        }
    });

    $('#ssf-position').on('change', function() {
        if ( 'above-content' === this.value ) {
            $('.ssf-enable-mobile-row').show();
        } else {
            $('.ssf-enable-mobile-row').hide();
        }
    });
     
})( jQuery );