//--------------------------------
// START - Document Ready Functions
//--------------------------------
$(document).ready(function(){
    $('.colorpicker').colpick({
        layout:'rgbhex',
        
        onBeforeShow: function () {
            $(this).colpickSetColor(this.value);
        },        
        
        onSubmit: function(hsb, hex, rgb, ele) {
            $(ele).val(hex.toUpperCase());
            $(ele).next('.colorpicker-preview').css('background-color', '#' + hex);
            $(ele).colpickHide();
        }, 
        onChange: function (hsb, hex, rgb, ele) {
            $(ele).val(hex.toUpperCase());
            $(ele).next('.colorpicker-preview').css('background-color', '#' + hex);
        }
    })
    .off('click')
    .after(function() {
        var colorPreview = $('<span class="colorpicker-preview"></span>');
        colorPreview.css('background-color', '#' + $(this).val());
        colorPreview.click(function() {
            $(this).prev('.colorpicker').colpickShow();
        });
        return colorPreview;
    })
    .change(function() {
        var val = $(this).val();
        if (val == '') {
            val = 'transparent';
        } else {
            val = '#' + val;
        }
        $(this).next('.colorpicker-preview').css('background-color', val);
    })
    .keydown(function() {
        $(this).change();
    })
    .keyup(function() {
        $(this).change();
    })
    .focusout(function() {
        $(this).change();
    });
});


//--------------------------------
// END - Document Ready Functions
//--------------------------------

function getCorrectHex (wrongVal) {
    wrongVal = wrongVal.toUpperCase();
    if (wrongVal.length == 3) {
        var first = wrongVal.charAt(0);
        var second = wrongVal.charAt(1);
        var third = wrongVal.charAt(2);
        return first+first+second+second+third+third;
    } else {
        var full = wrongVal+"000000";
        return full.substr(0,6);
    }
}
