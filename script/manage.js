// JavaScript Document

const btnUp = document.getElementById('btnUp');
const btnDown = document.getElementById('btnDown');
const btnQuantity = document.getElementById('btnQuantity');
const check = document.getElementById('check');
const quantity = document.getElementById('quantity');
const amount = document.getElementById('amount');
const final = document.getElementById('final');
const btnBack = document.getElementById('btnBack');
const cash = document.getElementById('cash');

let qty = 1;

btnUp.addEventListener('click', () => {
	if(qty == 20) {
		return;
	}
	qty++;
	amount.value = qty;
})

btnDown.addEventListener('click', () => {
	if(qty == 1) {
		return;
	}
	qty--;
	amount.value = qty;
})

btnQuantity.addEventListener('click', () => {
	final.classList.remove('hidden');
	quantity.classList.add('hidden');
    if(cash.checked) {
        check.innerHTML = `現金購入 ${qty}個`;
    }else {
        check.innerHTML = `招待状 ${qty}個`;
    }
})

btnBack.addEventListener('click', () => {
	quantity.classList.remove('hidden');
	final.classList.add('hidden');
})