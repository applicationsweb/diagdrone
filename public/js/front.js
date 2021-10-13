$(document).ready(function() {
    // Lien du menu actif si sélectionné
    $('li.active').removeClass('active');
    $('a[href="' + location.pathname + '"]').closest('li').addClass('active'); 
});