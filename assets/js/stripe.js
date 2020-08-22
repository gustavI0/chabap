window.onload = () => {

    // On instancie Stripe et on lui passe notre clé publique
    let stripe = Stripe('pk_test_51HB0wcEUBXiqdgnEmSbUGmb9TDKMwKemhhq94I5aVGZPOScEmAtjuARFoTp6gp6H32lP4ZcjP3waXAXuuitLvOpN00ENpNJ3UP');

    // Initialise les éléments du formulaire
    let elements = stripe.elements();

    // // Définit la redirection en cas de succès du paiement
    // let redirect = "/";
    //
    // // Récupère l'élément qui contiendra le nom du titulaire de la carte
    // let cardholderName = document.getElementById('cardholder-name');

    // Récupère l'élement button
    let cardButton = document.getElementById('card-button');

    // Récupère l'attribut data-secret du bouton
    let clientSecret = cardButton.dataset.secret;

    let style = {
        base: {
            color: "#32325d",
            fontFamily: 'Arial, sans-serif',
            fontSmoothing: "antialiased",
            fontSize: "16px",
            "::placeholder": {
                color: "#32325d"
            }
        },
        invalid: {
            fontFamily: 'Arial, sans-serif',
            color: "#fa755a",
            iconColor: "#fa755a"
        }
    };

    // Crée les éléments de carte et les stocke dans la variable card
    let card = elements.create("card", { style: style });

    card.mount("#card-element");

    card.on("change", function (event) {
        // Disable the Pay button if there are no card details in the Element
        document.querySelector("#card-button").disabled = event.empty;
        document.querySelector("#card-error").textContent = event.error ? event.error.message : "";
    });

    let form = document.getElementById("payment-form");
    form.addEventListener('submit', (e) => {
        e.preventDefault()
        // Complete payment when the submit button is clicked
        payWithCard(stripe, card, clientSecret);
    });

    // Calls stripe.confirmCardPayment
    // If the card requires authentication Stripe shows a pop-up modal to
    // prompt the user to enter authentication details without leaving your page.

    let payWithCard = function(stripe, card, clientSecret) {
        loading(true);
        stripe
            .confirmCardPayment(clientSecret, {
                payment_method: {
                    card: card
                }
            })
            .then(function(result) {
                if (result.error) {
                    // Show error to your customer
                    showError(result.error.message);
                } else {
                    // The payment succeeded!
                    orderComplete(result.paymentIntent.id);
                }
            });
    };

    // Shows a success message when the payment is complete
    let orderComplete = function(paymentIntentId) {
        loading(false);
        // document
        //     .querySelector(".result-message a")
        //     .setAttribute(
        //         "href",
        //         "https://dashboard.stripe.com/test/payments/" + paymentIntentId
        //     );
        document.querySelector(".result-message").classList.remove("hidden");
        document.querySelector("button").disabled = true;
        setTimeout(function() {
            document.location.href = '/liste';
        }, 4000);
    };



    // Show the customer the error from Stripe if their card fails to charge
    let showError = function(errorMsgText) {
        loading(false);
        let errorMsg = document.querySelector("#card-error");
        errorMsg.textContent = errorMsgText;
        setTimeout(function() {
            errorMsg.textContent = "";
        }, 4000);
    };

    // Show a spinner on payment submission
    let loading = function(isLoading) {
        if (isLoading) {
            // Disable the button and show a spinner
            document.querySelector("#card-button").disabled = true;
            document.querySelector("#spinner").classList.remove("hidden");
            document.querySelector("#button-text").classList.add("hidden");
        } else {
            document.querySelector("#card-button").disabled = false;
            document.querySelector("#spinner").classList.add("hidden");
            document.querySelector("#button-text").classList.remove("hidden");
        }
    };

}