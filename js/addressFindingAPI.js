let autocomplete;
let address1Field;
let postalField;
let localityField;
let countryField;

function initAutocomplete() {
  address1Field = document.querySelector("#address");
  postalField = document.querySelector("#postalCode");
  localityField = document.querySelector("#locality");
  countryField = document.querySelector("#country");

  // Initialise l'autocomplete avec Google Places API
  autocomplete = new google.maps.places.Autocomplete(address1Field, {
    componentRestrictions: { country: [] }, // Autoriser tous les pays, peut être modifié
    fields: ["address_components", "geometry"],
    types: ["address"],
  });

  // Ajoute un écouteur pour détecter quand une adresse est sélectionnée
  autocomplete.addListener("place_changed", fillInAddress);

  // Désactive temporairement les animations lors de l'input pour Google Places suggestions
  address1Field.addEventListener("keydown", disableAnimationsTemporarily);
  address1Field.addEventListener("blur", reenableAnimations); // Réactive les animations quand on quitte l'input
  
  // Ajoute la classe .has-value si l'utilisateur commence à taper
  addInputListeners([address1Field, postalField, localityField, countryField]);
}

function fillInAddress() {
  const place = autocomplete.getPlace();
  let address1 = "";
  let postcode = "";

  // Parcourt les composants de l'adresse pour remplir les champs
  for (const component of place.address_components) {
    const componentType = component.types[0];

    switch (componentType) {
      case "street_number":
        address1 = `${component.long_name} ${address1}`;
        break;
      case "route":
        address1 += component.short_name;
        break;
      case "postal_code":
        postcode = `${component.long_name}${postcode}`;
        break;
      case "locality":
        localityField.value = component.long_name;
        break;
      case "country":
        countryField.value = component.long_name;
        break;
    }
  }

  // Appliquer les valeurs aux champs
  address1Field.value = address1;
  postalField.value = postcode;

  // Ajoute la classe has-value à tous les champs remplis
  [address1Field, postalField, localityField, countryField].forEach(field => {
    if (field.value) {
      field.classList.add('has-value');
    }
  });

  // Réactive les animations après que l'adresse soit sélectionnée
  reenableAnimations();
}

// Fonction pour désactiver temporairement les animations sur le champ d'adresse
function disableAnimationsTemporarily() {
  address1Field.classList.add('no-animation'); // Ajoute la classe pour désactiver l'animation
}

// Fonction pour réactiver les animations
function reenableAnimations() {
  address1Field.classList.remove('no-animation'); // Enlève la classe qui désactive l'animation
}

// Ajoute des écouteurs d'événement pour chaque input pour la gestion de l'état has-value
function addInputListeners(fields) {
  fields.forEach(field => {
    field.addEventListener('input', function() {
      if (this.value) {
        this.classList.add('has-value');
      } else {
        this.classList.remove('has-value');
      }
    });
  });
}

window.initAutocomplete = initAutocomplete;
