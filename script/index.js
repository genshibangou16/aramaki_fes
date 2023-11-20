// JavaScript Document

const email1 = document.getElementById('email1');
const email2 = document.getElementById('email2');
const mailRegex = '^[a-zA-Z0-9_+-]+(.[a-zA-Z0-9_+-]+)*@([a-zA-Z0-9][a-zA-Z0-9-]*[a-zA-Z0-9]*\.)+[a-zA-Z]{2,}$';

function onSubmit1(token) {
    if(!email1.value.match(mailRegex)) {
        email1.classList.add('error');
        return false;
    }
    document.getElementById("form1").submit();
}

function onSubmit2(token) {
    if(!email2.value.match(mailRegex)) {
        email2.classList.add('error');
        return false;
    }
    document.getElementById("form2").submit();
}

email1.addEventListener('click', () => {
    email1.classList.remove('error');
})

email2.addEventListener('click', () => {
    email2.classList.remove('error');
})