const inputs = document.querySelectorAll('.formbold-form-input');

// Écouter l'événement blur (quand l'utilisateur quitte l'input)
inputs.forEach(input => {
  input.addEventListener('blur', function() {
    if (input.value) {
      input.classList.add('has-value'); 
    } else {
      input.classList.remove('has-value'); 
    }
  });

  // Si l'utilisateur commence à taper, enlever l'effet has-value
  input.addEventListener('input', function() {
    if (!input.value) {
      input.classList.remove('has-value');
    }
  });
});






