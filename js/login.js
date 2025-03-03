$(document).ready(function() {
    // Captcha refresh functionality
    $(document).on('click', '.refresh-captcha, img[alt="Refresh"]', function() {
        var button = $(this);
        $.ajax({
            url: 'refresh_captcha.php',
            method: 'GET',
            cache: false, // Prevent caching
            beforeSend: function() {
                // Add loading state to the button
                button.css('opacity', '0.5').prop('disabled', true);
            },
            success: function(response) {
                // Update the captcha text with new code
                $('.captcha-text').html(response);
                // Clear the input field
                $('input[name="captcha"]').val('');
            },
            error: function() {
                // Show error message if refresh fails
                alert('Failed to refresh captcha. Please try again.');
            },
            complete: function() {
                // Remove loading state
                button.css('opacity', '1').prop('disabled', false);
            }
        });
    });

    // Form submission handling
    $('form').on('submit', function() {
        const submitBtn = $(this).find('button[type="submit"]');
        const spinnerWrapper = submitBtn.find('.spinner-wrapper');
        
        // Show loading state
        submitBtn.addClass('loading');
        spinnerWrapper.removeClass('d-none');
        submitBtn.prop('disabled', true);
        
        // Add text after spinner
        spinnerWrapper.append('<span class="ms-2 text-light">Logging in...</span>');
        
        // Reset button after timeout (10 seconds)
        setTimeout(function() {
            if (!submitBtn.prop('disabled')) return;
            submitBtn.removeClass('loading');
            spinnerWrapper.addClass('d-none');
            spinnerWrapper.find('span:not(.visually-hidden)').remove();
            submitBtn.prop('disabled', false);
        }, 10000);
    });
}); 