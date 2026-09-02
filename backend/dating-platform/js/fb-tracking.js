//facbook pixel tracking events

function FB_Lead(){
	fbq('track', 'Lead');	
}

function FB_Trial(){
	fbq('track', 'StartTrial');
}

function FB_Purchase(price){
	fbq('track', 'Purchase', {value: price, currency: 'EUR'});
}

function FB_Checkout(){
	fbq('track', 'InitiateCheckout');
}