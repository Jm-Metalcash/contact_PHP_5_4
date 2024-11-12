document.addEventListener('DOMContentLoaded', function() {
    const contactForm = document.getElementById('contactForm');
    const bordereauForm = document.getElementById('bordereauForm');

    // Function to check reCAPTCHA and handle form submission
    function handleRecaptchaSubmission(event, form) {
        const recaptchaResponse = form.querySelector('.g-recaptcha-response').value;
        if (recaptchaResponse === '') {
            event.preventDefault(); // Prevent form submission if reCAPTCHA is not completed
            const formError = form.querySelector('#formError');
            formError.textContent = "Veuillez compléter le reCAPTCHA pour soumettre le formulaire.";
            formError.classList.add('show');
        }
    }

    // Attach event listeners to both forms
    if (contactForm) {
        contactForm.addEventListener('submit', function(event) {
            handleRecaptchaSubmission(event, contactForm);
        });
    }

    if (bordereauForm) {
        bordereauForm.addEventListener('submit', function(event) {
            handleRecaptchaSubmission(event, bordereauForm);
        });
    }
});
