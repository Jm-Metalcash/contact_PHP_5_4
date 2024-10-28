// Sélectionne les deux formulaires séparément
const contactForm = document.getElementById("contactForm");
const bordereauForm = document.getElementById("bordereauForm");

// Function to validate international phone number format
const validateInternationalPhoneNumberFormat = (phone) =>
  /^(?:\+{1}[\d]{1,3})?([\s])?((\([\d]{2,3}\)[\s]?)|([\d]{2,3}[\s]?))?\d[\-.\s]?\d[\-.\s]?\d[\-.\s]?\d[\-.\s]?\d[\-.\s]?\d?[\-.\s]?\d?[\-.\s]?\d?[\-.\s]?\d?$/.test(
    phone
  );

// Function to validate email address format
const validateEmailFormat = (email) =>
  /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(email);

// Function to validate IBAN format (for all countries)
const validateIBAN = (iban) => {
  iban = iban.replace(/\s+/g, "").toUpperCase();
  let ibanRegex = /^[A-Z]{2}\d{2}[A-Z0-9]{1,30}$/;
  if (!ibanRegex.test(iban)) return false;

  let rearrangedIBAN = iban.slice(4) + iban.slice(0, 4);
  let numericIBAN = rearrangedIBAN.replace(
    /[A-Z]/g,
    (char) => char.charCodeAt(0) - 55
  );
  let modResult = BigInt(numericIBAN) % 97n;
  return modResult === 1n;
};

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

// Hide error message on input
const handleInputEvent = (input, errorElement) => {
  input.addEventListener("input", () => {
    errorElement.classList.remove("show"); // Masquer l'erreur lors de la saisie
    const formError = input.closest("form").querySelector("#formError");
    formError.classList.remove("show"); // Masquer le message général avec animation
  });
};

// Fonction pour appliquer les validations à un formulaire spécifique
function setupValidation(form) {
  const phoneNumberInput = form.querySelector("#phone");
  const emailInput = form.querySelector("#email");
  const ibanInput = form.querySelector("#iban");
  const phoneError = form.querySelector("#phoneError");
  const emailError = form.querySelector("#emailError");
  const ibanError = form.querySelector("#ibanError");
  const formError = form.querySelector("#formError");

  // Appliquer la validation sur les champs spécifiques de chaque formulaire
  if (phoneNumberInput) {
    handleBlurEvent(
      phoneNumberInput,
      phoneError,
      validateInternationalPhoneNumberFormat
    );
    handleInputEvent(phoneNumberInput, phoneError);
  }

  if (emailInput) {
    handleBlurEvent(emailInput, emailError, validateEmailFormat);
    handleInputEvent(emailInput, emailError);
  }

  if (ibanInput) {
    handleBlurEvent(ibanInput, ibanError, validateIBAN);
    handleInputEvent(ibanInput, ibanError);
  }

  // Empêcher la soumission du formulaire s'il y a des erreurs
  form.addEventListener("submit", (event) => {
    const phoneNumber = phoneNumberInput ? phoneNumberInput.value : "";
    const email = emailInput ? emailInput.value : "";
    const iban = ibanInput ? ibanInput.value : "";

    const isPhoneValid = phoneNumberInput
      ? validateInternationalPhoneNumberFormat(phoneNumber)
      : true;
    const isEmailValid = emailInput ? validateEmailFormat(email) : true;
    const isIBANValid = ibanInput ? validateIBAN(iban) : true;

    // Si un champ est invalide, afficher les erreurs correspondantes
    if (
      (!isPhoneValid && phoneNumber.trim() !== "") ||
      (!isEmailValid && email.trim() !== "") ||
      (!isIBANValid && iban.trim() !== "")
    ) {
      event.preventDefault(); // Empêcher la soumission du formulaire

      if (phoneNumberInput) {
        handleErrorDisplay(phoneNumberInput, phoneError, isPhoneValid);
      }
      if (emailInput) {
        handleErrorDisplay(emailInput, emailError, isEmailValid);
      }
      if (ibanInput) {
        handleErrorDisplay(ibanInput, ibanError, isIBANValid);
      }

      // Afficher le message d'erreur général
      formError.textContent =
        "Veuillez corriger les champs nécessaires avant de poursuivre.";
      formError.classList.add("show");
    }
  });
}

// Appliquer la validation pour chaque formulaire
setupValidation(contactForm);
setupValidation(bordereauForm);

// Fonction format date pour l'expiration de la carte d'identité
document.getElementById("expiryDate").addEventListener("input", function (e) {
  const input = e.target;
  const value = input.value.replace(/[^\d]/g, ""); // Supprimer tout sauf les chiffres
  let formattedValue = "";

  // Insère les points de séparation automatiquement sans espacement autour des points
  if (value.length >= 1) {
    formattedValue += value.substring(0, 2); // jj
  }
  if (value.length >= 3) {
    formattedValue += "." + value.substring(2, 4); // mm
  }
  if (value.length >= 5) {
    formattedValue += "." + value.substring(4, 8); // aaaa
  }

  // Mettre à jour la valeur de l'input
  input.value = formattedValue;

  // Ne pas permettre plus de 10 caractères
  if (input.value.length > 10) {
    input.value = input.value.slice(0, 10);
  }

  // Ajouter la classe filled si le champ est complètement rempli
  if (input.value.length === 10) {
    input.blur();
    input.classList.add("formated-expiryDate");
  } else {
    input.classList.remove("filled");
    input.classList.remove("formated-expiryDate");
  }
});

document.getElementById("expiryDate").addEventListener("focus", function (e) {
  const input = e.target;
  input.classList.remove("formated-expiryDate", "has-value");
});

// Fonction pour valider et comparer la date d'expiration
document.getElementById("expiryDate").addEventListener("blur", function () {
  validateExpiryDate(); // Valider la date lorsqu'on quitte l'input
});

function validateExpiryDate() {
  const expiryDateInput = document.getElementById("expiryDate").value;
  const expiryError = document.getElementById("expiryError");

  // Ne pas afficher le message d'erreur si le champ est vide
  if (expiryDateInput === "") {
    expiryError.classList.remove("show");
    return true;
  }

  // Expression régulière pour accepter les formats JJ.MM.YYYY
  const dateRegex = /^(0[1-9]|[12][0-9]|3[01])\.(0[1-9]|1[0-2])\.(\d{4})$/;

  // Vérification du format de la date
  if (!dateRegex.test(expiryDateInput)) {
    expiryError.textContent =
      "La date saisie n'est pas valide. Veuillez vérifier le jour et le mois.";
    expiryError.classList.add("show");
    return false;
  }

  const [day, month, year] = expiryDateInput.split(".");
  const expiryDate = new Date(year, month - 1, day);
  const today = new Date();

  // Comparer la date de la carte d'identité avec la date actuelle
  if (expiryDate < today) {
    expiryError.textContent = "Votre carte d'identité est expirée.";
    expiryError.classList.add("show");
    return false;
  } else {
    expiryError.classList.remove("show");
    return true;
  }
}
