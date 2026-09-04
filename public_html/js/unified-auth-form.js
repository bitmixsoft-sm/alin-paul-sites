/* Unified email-first auth form (components/unified-auth-form.blade.php). Shared by
   layouts/layout.blade.php's Register/Login modals and index.blade.php's landing page - a
   single file so both stay in sync instead of drifting like the two copies of dating.js do. */
function unifiedAuthContinue(button) {
    var $root = $(button).closest('.unified-auth');
    var $input = $root.find('.unified-email-input');
    var email = $.trim($input.val());
    var $error = $root.find('.unified-auth-error');

    $error.hide().text('');

    if (!email) {
        $error.text('Please enter your email.').show();
        return;
    }

    var $button = $(button);
    $button.prop('disabled', true);

    $.ajax({
        url: '/auth/check-email',
        type: 'POST',
        dataType: 'JSON',
        data: {
            _token: $('input[name="_token"]').first().val(),
            email: email
        },
        success: function (data) {
            $button.prop('disabled', false);
            $root.find('.unified-email-mirror').val(email);
            $root.attr('data-step', data.exists ? 'login' : 'register');
        },
        error: function (xhr) {
            $button.prop('disabled', false);
            var message = (xhr.responseJSON && xhr.responseJSON.errors && xhr.responseJSON.errors.email)
                ? xhr.responseJSON.errors.email[0]
                : 'Something went wrong, please try again.';
            $error.text(message).show();
        }
    });
}

function unifiedAuthBack(link) {
    $(link).closest('.unified-auth').attr('data-step', 'email');
}
