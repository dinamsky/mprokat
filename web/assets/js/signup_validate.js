$( document ).ready(function() {
    $('input[name="oferta"]').on('change', function () {
        var ok = $(this).prop('checked');
        if(ok) {
            $('#signUpForm').find('button').removeAttr('disabled');
        } else {
            $('#signUpForm').find('button').attr('disabled',true);
        }
    });

    $('input[name="oferta2"]').on('change', function () {
        var ok = $(this).prop('checked');
        if(ok) {
            $('#new_password').find('button').removeAttr('disabled');
        } else {
            $('#new_password').find('button').attr('disabled',true);
        }
    });
});

function signup_validate(){
    var email = $('#signUpForm').find('input[name="email"]').val();
    var password = $('#signUpForm').find('input[name="password"]').val();

    var message = [];
    if (!email) message.push('\nemail');
    if (!password) message.push('\nПароль');


    if (message.length > 0){
        alert('Заполните поля:\n'+message);
        return false;
    }

    if (!validateEmail(email)) {
        alert('Заполните email правильно! Допустимы: a-z 0-9 точка дефис @');
        return false;
    }
}

function validateEmail(email) {
    if(email.indexOf('+') + 1) return false;
    var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
}