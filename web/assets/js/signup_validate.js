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
        alert('Заполните email правильно!');
        return false;
    }
}

function validateEmail(email) {
    var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
}