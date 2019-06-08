$("#password").on('change', function () {
    let estimate = zxcvbn($(this).val(), [$('#login').val()]);
    if (estimate.score < 4) {
        $("#passwordHelp").html(estimate.feedback.warning);
    } else {
        $("#passwordHelp").html('');
    }
    console.log('changed');
});

$("#password-form").submit(function(e) {
    console.log('prevent');
    let estimate = zxcvbn($('#password').val(), [$('#login').val()]);
    if (estimate.score < 3) {
        e.preventDefault();
        return false;
    }
});
