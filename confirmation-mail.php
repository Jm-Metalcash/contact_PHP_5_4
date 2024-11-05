<?php
include 'lang/lang.fr_be.php';
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email confirmation - Metalcash</title>
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
            <h1><?= NEW_SUCCESS_EMAIL_TITLE ?></h1>
            <p><?= NEW_SUCCESS_EMAIL_DESC ?></p>
            <a href="index.php"><?= NEW_SUCCESS_EMAIL_BTN ?></a>
        </div>
    </div>
</body>

</html>