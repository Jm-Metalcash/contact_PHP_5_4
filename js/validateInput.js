// Function to validate international phone number format
const validateInternationalPhoneNumberFormat = phone => /^(?:\+{1}[\d]{1,3})?([\s])?((\([\d]{2,3}\)[\s]?)|([\d]{2,3}[\s]?))?\d[\-.\s]?\d[\-.\s]?\d[\-.\s]?\d[\-.\s]?\d[\-.\s]?\d?[\-.\s]?\d?[\-.\s]?\d?[\-.\s]?\d?$/.test(phone);

// Function to validate email address format
const validateEmailFormat = email => /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(email);

// Function to validate IBAN format (for all countries)
const validateIBAN = iban => {
    iban = iban.replace(/\s+/g, '').toUpperCase();
    let ibanRegex = /^[A-Z]{2}\d{2}[A-Z0-9]{1,30}$/;
    if (!ibanRegex.test(iban)) return false;

    let rearrangedIBAN = iban.slice(4) + iban.slice(0, 4);
    let numericIBAN = rearrangedIBAN.replace(/[A-Z]/g, char => char.charCodeAt(0) - 55);
    let modResult = BigInt(numericIBAN) % 97n;
    return modResult === 1n;
};

// Get the form and input fields
const phoneNumberInput = document.getElementById("phone");
const emailInput = document.getElementById("email");
const ibanInput = document.getElementById("iban");
const phoneError = document.getElementById("phoneError");
const emailError = document.getElementById("emailError");
const ibanError = document.getElementById("ibanError");
const formError = document.getElementById("formError");
const form = document.querySelector("form");

// Helper function to handle error display
const handleErrorDisplay = (input, errorElement, isValid) => {
    if (input.value.trim() === "") {
        errorElement.classList.remove("show"); // Ne pas afficher si vide
    } else if (!isValid) {
        errorElement.classList.add("show"); // Afficher erreur si invalide
    } else {
        errorElement.classList.remove("show"); // Masquer si valide
    }
};

// Helper function to validate a field on blur
const handleBlurEvent = (input, errorElement, validationFunction) => {
    input.addEventListener("blur", () => {
        const value = input.value;
        const isValid = validationFunction(value);
        handleErrorDisplay(input, errorElement, isValid);
    });
};

// Apply validation on blur for phone number, email, and IBAN
handleBlurEvent(phoneNumberInput, phoneError, validateInternationalPhoneNumberFormat);
handleBlurEvent(emailInput, emailError, validateEmailFormat);
handleBlurEvent(ibanInput, ibanError, validateIBAN);

// Hide error message on input
const handleInputEvent = (input, errorElement) => {
    input.addEventListener("input", () => {
        errorElement.classList.remove("show"); // Masquer l'erreur lors de la saisie
        formError.classList.remove("show"); // Masquer le message général avec animation
    });
};

handleInputEvent(phoneNumberInput, phoneError);
handleInputEvent(emailInput, emailError);
handleInputEvent(ibanInput, ibanError);

// Prevent form submission if phone number, email, or IBAN are invalid
form.addEventListener("submit", event => {
    const phoneNumber = phoneNumberInput.value;
    const email = emailInput.value;
    const iban = ibanInput.value;

    const isPhoneValid = validateInternationalPhoneNumberFormat(phoneNumber);
    const isEmailValid = validateEmailFormat(email);
    const isIBANValid = validateIBAN(iban);

    // Validate all fields before submitting the form
    if ((!isPhoneValid && phoneNumber.trim() !== "") || (!isEmailValid && email.trim() !== "") || (!isIBANValid && iban.trim() !== "")) {
        event.preventDefault(); // Prevent form submission

        handleErrorDisplay(phoneNumberInput, phoneError, isPhoneValid);
        handleErrorDisplay(emailInput, emailError, isEmailValid);
        handleErrorDisplay(ibanInput, ibanError, isIBANValid);

        // Show general form error message
        formError.textContent = "Veuillez corriger les champs nécessaires avant de poursuivre.";
        formError.classList.add("show");
    }
});
