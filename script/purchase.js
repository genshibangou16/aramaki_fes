// JavaScript Document

const btnUp = document.getElementById('btnUp');
const btnDown = document.getElementById('btnDown');
const btnQuantity = document.getElementById('btnQuantity');
const btnPostal = document.getElementById('btnPostal');
const addressBox = document.getElementById('addressBox');
const quantity = document.getElementById('quantity');
const idBox = document.getElementById('idBox');
const messageBox1 = document.getElementById('messageBox1');
const messageBox2 = document.getElementById('messageBox2');
const csrfToken = document.getElementById('csrfToken');
const token = document.getElementById('token');
const messages = document.getElementById('messages');
const price = document.getElementById('price');
const email = document.getElementById('email');
const paypalBox = document.getElementById('paypal_wrapper');
const processing = document.getElementById('processing');
const postalCode = document.getElementById('postalCode');
const region = document.getElementById('region');
const locality = document.getElementById('locality');
const street = document.getElementById('street');
const final = document.getElementById('final');
const btnBack = document.getElementById('btnBack');

let qty = 1;

btnUp.addEventListener('click', () => {
	if(qty == 20) {
		return;
	}
	qty++;
	price.innerHTML = `デルニー${qty}個 ${qty * 190}円`;
})

btnDown.addEventListener('click', () => {
	if(qty == 1) {
		return;
	}
	qty--;
	price.innerHTML = `デルニー${qty}個 ${qty * 190}円`;
})

let postal_code = null;

btnQuantity.addEventListener('click', () => {
	addressBox.classList.remove('hidden');
	quantity.classList.add('hidden');
})

btnPostal.addEventListener('click', () => {
	if(!postalCode.value) {
		postalCode.style.backgroundColor = 'rgba(255,0,0,0.20)';
		return;
	}
	postalCode.style.backgroundColor = 'rgba(0,0,0,0.05)';
	addressBox.classList.add('hidden');
	paypalBox.classList.remove('hidden');
	postal_code = Number(postalCode.value.replace('-', ''));
	final.innerHTML = `デルニー${qty}個 ${qty * 190}円`;
})

btnBack.addEventListener('click', () => {
	quantity.classList.remove('hidden');
	paypalBox.classList.add('hidden');
})

////
// Below is PayPal SDK
////

const fundingSources = [
	paypal.FUNDING.PAYPAL,
	paypal.FUNDING.CARD
]

for (const fundingSource of fundingSources) {
	const paypalButtonsComponent = paypal.Buttons({
		fundingSource: fundingSource,

	// optional styling for buttons
	// https://developer.paypal.com/docs/checkout/standard/customize/buttons-style-guide/
	style: {
		shape: 'rect',
		height: 40,
	},

	// set up the transaction
	createOrder: (data, actions) => {
		// pass in any options from the v2 orders create call:
		// https://developer.paypal.com/api/orders/v2/#orders-create-request-body
		const createOrderPayload = {
			purchase_units: [
				{
					custom_id: token.value,
					amount: {
						currency_code: 'JPY',
						value: `${190 * qty}`,
					},
				},
			],
			payer: {
				email_address: email.value,
				address: {
					country_code: 'JP',
					postal_code: postal_code,
					admin_area_1: region.value,
					admin_area_2: `${locality.value}${street.value}`,
				},
			},
		}
		return actions.order.create(createOrderPayload)
	},

	// finalize the transaction
	onApprove: (data, actions) => {
		processing.classList.remove('hidden');
		paypalBox.classList.add('hidden');
		const captureOrderHandler = (details) => {
			const id = details.id;
			messages.classList.remove('hidden');
			idBox.innerHTML = `Order ID: ${id}`;
			let postData = new FormData();
			postData.append('csrfToken', csrfToken.value);
			postData.append('amount', 190 * qty);
			postData.append('id', id);
			const headers = {
				'Accept': 'application/json'
			}
			let httpResponse = null;
			let csrfToken2 = null;
			fetch(`https://${sub}.sorabi.jp/check.php`, {
				method: 'POST',
				headers: headers,
				credentials: 'same-origin',
				body: postData
			})
			.then((res) => {
				httpResponse = res.status;
				return res.json();
			})
			.then((data) => {
				csrfToken2 = data['csrfToken'];
				if(httpResponse == 200) {
					if(data['statusCode']) {
						processing.classList.add('hidden');
						alert('決済に失敗しました。Order IDとエラーメッセージを控えて管理者にご連絡ください。');
						messageBox1.innerHTML = `エラーメッセージ: ${data['status']} ${data['description']}`;
						messageBox2.innerHTML = '連絡先: sorabi.jp (at) gmail.com (at)->@';
					}else {
						window.location.href = `https://${sub}.sorabi.jp/complete.php?csrfToken=${csrfToken2}`;
					}
				}else {
					processing.classList.add('hidden');
					alert(`決済に失敗しました。\nOrder IDとエラーメッセージを控えて管理者までご連絡ください。`);
					messageBox1.innerHTML = 'エラーメッセージ: HTTP Response code is not 200';
					messageBox2.innerHTML = '連絡先: sorabi.jp (at) gmail.com (at)->@';
				}
			})
		}
		return actions.order.capture().then(captureOrderHandler)
	},

	// handle unrecoverable errors
	onError: (err) => {
		processing.classList.add('hidden');
		messageBox1.innerHTML = '決済に失敗しました。再試行してください。'
	},
})

if (paypalButtonsComponent.isEligible()) {
	paypalButtonsComponent
		.render('#paypal-button-container')
		.catch((err) => {
		console.error('PayPal Buttons failed to render')
	})
} else {
	console.log('The funding source is ineligible')
}
}