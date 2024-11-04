<?php
// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les données du formulaire
    $firstname = htmlspecialchars(trim($_POST['firstname']));
    $lastname = htmlspecialchars(trim($_POST['lastname']));
    $email = htmlspecialchars(trim($_POST['email']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    $subject = htmlspecialchars(trim($_POST['subject']));
    $message = nl2br(htmlspecialchars(trim($_POST['question']))); // Convertir les sauts de ligne en <br>

    // Vérifier que les champs obligatoires ne sont pas vides
    if (empty($email) || empty($message)) {
        echo "Veuillez remplir tous les champs obligatoires.";
        exit;
    }

    // Valider le format de l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Adresse e-mail invalide.";
        exit;
    }

    // Préparer le message de l'e-mail en HTML
    $mail_subject = "[Metalcash - Contact] Nouveau message de $firstname $lastname";
    $mail_message = "
<html>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>$mail_subject</title>
</head>
<body style='font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;'>
    <div style='max-width: 900px; margin: auto; background: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0px 4px 10px rgba(0,0,0,0.1);'>
        <div style='text-align: center; padding-bottom: 20px; border-bottom: 1px solid #eaeaea;'>
            <img src='./images/logo-HD.webp' alt='Metalcash Logo' style='max-width: 150px; margin-bottom: 10px;' />
        </div>
        <div style='padding: 20px;'>
            <h2 style='color: #333; font-size: 24px; margin-bottom: 20px;'>Nouveau message de $firstname $lastname</h2>
            <p style='color: #555; font-size: 16px; margin-bottom: 10px;'><strong>Nom complet :</strong> $firstname $lastname</p>
            <p style='color: #555; font-size: 16px; margin-bottom: 10px;'><strong>E-mail :</strong> <a href='mailto:$email' style='color: #1d72b8; text-decoration: none;'>$email</a></p>
            <p style='color: #555; font-size: 16px; margin-bottom: 10px;'><strong>Téléphone :</strong> $phone</p>
            <p style='color: #555; font-size: 16px; margin-bottom: 10px;'><strong>Sujet :</strong> $subject</p>
            <h3 style='color: #333; font-size: 20px; margin-top: 20px; border-top: 1px solid #eaeaea; padding-top: 20px;'>Message :</h3>
            <p style='line-height: 1.6; color: #333; font-size: 15px;'>$message</p>
        </div>
        <div style='text-align: center; padding-top: 20px; border-top: 1px solid #eaeaea; margin-top: 20px;'>
            <p style='font-size: 12px; color: #888;'>Cet e-mail a été envoyé depuis le formulaire de contact de Metalcash.</p>
        </div>
    </div>
</body>
</html>
";



    // Configurer les en-têtes de l'e-mail pour le format HTML
    $mail_headers = "From: $email\r\n";
    $mail_headers .= "Reply-To: $email\r\n";
    $mail_headers .= "MIME-Version: 1.0\r\n";
    $mail_headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    // Adresse e-mail de destination
    $to = "jm@metalcash.be";

    // Envoyer l'e-mail
    if (mail($to, $mail_subject, $mail_message, $mail_headers)) {
        // Rediriger vers la page de confirmation
        header('Location: confirmation-mail.php');
        exit;
    } else {
        echo "Erreur lors de l'envoi de votre message. Veuillez réessayer plus tard.";
    }
} else {
    echo "Méthode non autorisée.";
}
