<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Inclure PHPMailer
require '.\vendor\phpmailer\phpmailer\src\Exception.php';
require '.\vendor/phpmailer/phpmailer/src/PHPMailer.php';
require '.\vendor\phpmailer\phpmailer\src\SMTP.php';

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les données du formulaire
    $firstname = htmlspecialchars(trim($_POST['firstname']));
    $lastname = htmlspecialchars(trim($_POST['lastname']));
    $email = htmlspecialchars(trim($_POST['email']));
    $phone = htmlspecialchars(trim($_POST['phone']));
    $subject = htmlspecialchars(trim($_POST['subject']));
    $message = nl2br(htmlspecialchars(trim($_POST['question']))); 

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
                <h1 style='font-size: 16px; color: #005893;'>Metalcash</h1>
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

    // Créer une instance de PHPMailer
    $mail = new PHPMailer(true);

    try {
        // Configuration du serveur SMTP de Gmail
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'itmetalcash@gmail.com';
        $mail->Password = 'zqlsdmutmhkguhvh';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

         // Définir la langue sur français
         $mail->setLanguage('fr', '.\vendor\phpmailer\phpmailer\language');
         $mail->CharSet = 'UTF-8';
         $mail->Encoding = 'base64';

        // Configuration de l'e-mail
        $mail->setFrom($email, "$firstname $lastname"); 
        $mail->addAddress('jm@metalcash.be');

        // Contenu de l'e-mail
        $mail->isHTML(true);
        $mail->Subject = $mail_subject;
        $mail->Body    = $mail_message;

        // Envoyer l'e-mail
        $mail->send();

        // Rediriger vers la page de confirmation
        header('Location: confirmation-mail.php');
        exit;
    } catch (Exception $e) {
        echo "Erreur lors de l'envoi de l'e-mail : {$mail->ErrorInfo}";
    }
} else {
    echo "Méthode non autorisée.";
}
