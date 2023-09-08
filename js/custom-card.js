jQuery(document).ready(function($) {
    // Event delegation for main card click
    $(document).on('click', '.kent-main-card[data-card-id]', function(event) {
        event.preventDefault();

        var cardId = $(this).data('card-id');
        var subCards = $('.kent-sub-cards-container[data-card-id="' + cardId + '"]');

        // Hide other sub card containers
        $('.kent-sub-cards-container').not(subCards).slideUp();

        // Hide all sub card descriptions
        $('.kent-sub-card-description').hide();

        // Toggle visibility of the clicked sub card containers
        subCards.slideToggle();

        // Scroll to the clicked sub card container
        if (subCards.is(':visible')) {
            $('html, body').animate({
                scrollTop: subCards.offset().top - 150
            }, 800);
        }
    });

    // Add clicked style to main cards
    $(document).on('click', '.kent-main-card', function() {
        $('.kent-main-card').removeClass('kent-main-card-clicked');
        $(this).addClass('kent-main-card-clicked');
    });

    // Event delegation for sub card click
    $(document).on('click', '.kent-sub-card', function() {
        var cardId = $(this).data('card-id');
        var subCardDescription = $('.kent-sub-card-description[data-card-id="' + cardId + '"]');
        // Hide all sub card descriptions
        $('.kent-sub-card-description').hide();
        subCardDescription.slideToggle();

        // Scroll to the clicked sub card container
        if (subCardDescription.is(':visible')) {
            $('html, body').animate({
                scrollTop: subCardDescription.offset().top - 150
            }, 800);
        }
        // Show the description of the clicked sub card
        //$('.kent-sub-card-description[data-card-id="' + cardId + '"]').show();
    });

    // Event delegation for sub card container click
    $(document).on('click', '.kent-sub-cards-container', function() {
        // Remove the 'kent-sub-cards-container-clicked' class from all elements
        $('.kent-sub-cards-container').removeClass('kent-sub-cards-container-clicked');

        // Add the 'kent-sub-cards-container-clicked' class to the clicked element
        $(this).addClass('kent-sub-cards-container-clicked');
    });
});
