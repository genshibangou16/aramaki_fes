// JavaScript Document

const email = document.getElementById('email');
const mailRegex = '^[a-zA-Z0-9_+-]+(.[a-zA-Z0-9_+-]+)*@([a-zA-Z0-9][a-zA-Z0-9-]*[a-zA-Z0-9]*\.)+[a-zA-Z]{2,}$';

function onSubmit(token) {
    if(!email.value.match(mailRegex)) {
        email.classList.add('error');
        return false;
    }
    document.getElementById("form").submit();
}

email.addEventListener('click', () => {
    email.classList.remove('error');
})