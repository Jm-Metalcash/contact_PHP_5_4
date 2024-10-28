<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation</title>
    <link rel="stylesheet" href="css/email-confirm.css" <?= rand() ?>">
</head>

<body>
    <div class="content-email-confirm">
        <div class="success-animation">
            <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                <circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none" />
                <path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8" />
            </svg>
        </div>
        <div class="message-content">
            <h1>Merci pour votre message !</h1>
            <p>Votre message a été envoyé avec succès. Nous vous répondrons dans les plus brefs délais.</p>
            <a href="index.php">Retour à la page d'accueil</a>
        </div>
    </div>
</body>

</html>