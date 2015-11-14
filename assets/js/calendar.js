$(document).ready(function() {

    $( ".day-cell" ).equalHeights(150, 200);

    $( "#calendar .event a" ).fancybox({
        maxWidth  : 800,
        maxHeight : 600,
        autoSize  : true
    });

});