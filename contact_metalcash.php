<?php

$contact_subject = array(MAIL_SUBJECT_1, MAIL_SUBJECT_2, MAIL_SUBJECT_3, MAIL_SUBJECT_4, MAIL_SUBJECT_5);

?>

<link href="/css/modal.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="/js/modal.js"></script>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<script type="text/javascript">
	$(document).ready(function() {
		$('a[rel*=modalbox]').leanModal({
			top: 50,
			closeButton: ".modal_close"
		});
	});
</script>

<?
/* Send message to database (user with privilege 100) */
if ($_POST['contact']) {
	/* Verification submit form */
	$ERRORTAG = '<div class="errorbox-bad">';
	$ERRORMSG = '<div class="errormsg">' . MANDATORY_FIELD . '</div></div>';

	// Check Recaptcha
	if (isset($_POST['g-recaptcha-response']) && !empty($_POST['g-recaptcha-response'])) {
		$secret = '6Lf7n5MiAAAAAPs93j4uyXCmg1YUv33-f-OIhpKS';
		$verifyResponse = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $secret . '&response=' . $_POST['g-recaptcha-response']);
		$responseData = json_decode($verifyResponse);

		if ($responseData->success) {
			$SuccessMsg = "OK";
		} else {
			$error = "1";
			$errorcapcha1 = $ERRORTAG;
			$errorcapcha2 = '<div class="errormsg">Hahaha - Go Away</div></div>';
		}
	} else {
		$error = "1";
		$errorcapcha1 = $ERRORTAG;
		$errorcapcha2 = '<div class="errormsg"></div></div>';
	}

	/* Verifier nom et email uniquement si non authentifi� */
	if (empty($_POST['name'])) {
		$errorname1 = $ERRORTAG;
		$errorname2 = $ERRORMSG;
		$error = 1;
	}

	/* Verification de l'email */
	if (!empty($_POST['email'])) {
		list($alias, $domain) = split("@", $_POST['email']);
		$postedemail = $_POST['email'];
	}
	if (empty($_POST['email'])) {
		$erroremail1 = $ERRORTAG;
		$erroremail2 = $ERRORMSG;
		$error = 1;
	} elseif (!eregi("^([_a-z0-9-]+)(\.[_a-z0-9-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,4})$", $_POST['email']) || !checkdnsrr($domain, "MX")) {
		$erroremail1 = $ERRORTAG;
		$erroremail2 = '<div class="errormsg">' . INVALID_EMAIL . '</div></div>';
		$error = 1;
	}

	/* Verification T�l�phone si param�tr� */
	if ($_POST['phone'] != "") {
		if (!ereg("^[0-9]{9,}$", $_POST['phone'])) {
			$errorphone1 = $ERRORTAG;
			$errorphone2 = '<div class="errormsg">' . INVALID_PHONE . '</div></div>';
			$error = 1;
		}
	}

	/* Empty message */
	if (empty($_POST['question']) || preg_match("/http/", $_POST['question'])) {
		$errorquestion1 = $ERRORTAG;
		$errorquestion2 = $ERRORMSG;
		$error = 1;
	}

	if (strlen($_POST['question']) < 20 || preg_match("/^[0-9\s]+$/", $_POST['question'])) {
		$errorquestion1 = $ERRORTAG;
		$errorquestion2 = $ERRORMSG;
		$error = 1;
	}
}

?>
<?php /**/ ?>
<h1><?= H1_CONTACT_TITLE ?></h1>

<div align="center">
	<div class="bubble green" style="font-size: 34px;"><img src="/imgs/static/call.png" width="21" height="30" alt="<?= ALT_PHONE ?> " align="absmiddle"><?= PHONE_NUMBER ?></div>
	<i><?= H1_CONTACT_TITLE_TEXT ?></i>
	<p>
</div>

<div align="left" style="font-size: 16px;">



</div>
<? /**/ ?>
<? /* ?><div class="tipbox" align="center"><img src="/imgs/icons/info.png" alt="!" align="absmiddle" border="0" width="13" height="13"> <b><? printf(CLOSED_TEXT)?></b></div><? */ ?>

<p>&nbsp;</p>


<h2><?= H2_CONTACT_TITLE ?></h2>

<div>
	<? if ($_POST['contact'] && !isset($error) && !isset($_SESSION['mailsent'])) {
		if ($_POST['phone'] != "") $addphone = "T�l�phone: " . $_POST['phone'] . "\n";
		$mailmsg = $_POST['name'] . " a �crit:\n\n" . $_POST['question'] . "\n\nEmail: " . $_POST['email'] . "\n" . $addphone . "Site: " . $_SERVER['HTTP_HOST'] . "\n_____________________________________\nIP: " . $_SERVER['REMOTE_ADDR'] . " | Sent: " . date("d-m-Y H:i:s");
		mail("contact@metalcash.be", $_POST['name'] . ": " . $_POST['subject'], $mailmsg, "From: Metalcash Support <no-reply@metalcash.be>\n" . "Reply-To: " . $_POST['email'] . "\nContent-Type: text/plain; charset=\"ISO-8859-1\"\n\n");
		$_SESSION['mailsent'] = 1;
	?>

		<p>&nbsp;</p>
		<p>&nbsp;</p>
		<div class="infobox" style="margin-left: -35px; font-size: 24px;" align="center">
			<img src="/imgs/icons/success.png" width="15" height="15" align="absmiddle"> <? printf(EMAIL_SENT, $_POST['name']); ?>
		</div>
		<p>
		<div class="tipbox" align="center" style="margin-right: 35px;"><img src="/imgs/icons/info.png" align="absmiddle" width="15" height="15" alt="i"> <?= EMAIL_SENT_TIPS ?></div>
		<p>&nbsp;</p>

	<? } else {
		unset($_SESSION['mailsent']);
	?>

		<form id="contact" action="<?= $_SERVER['REDIRECT_URL'] ?>" method="POST">

			<table>
				<? if (!isset($_SESSION['authID'])) { ?>
					<tr>
						<td><?= FIELD_NAME ?>:</td>
						<td><?= $errorname1 ?><input onclick="this.style.color='#000';" type="text" name="name" value="<?= $_POST['name'] ?>" style="width: 220px;"><?= $errorname2 ?></td>
					</tr>
					<tr>
						<td><?= FIELD_EMAIL ?>:</td>
						<td><?= $erroremail1 ?><input onclick="this.style.color='#000';" type="text" name="email" value="<?= $_POST['email'] ?>" style="width: 220px;"><?= $erroremail2 ?></td>
					</tr>
				<? } else { ?>

					<input type="hidden" name="name" value="<?= $_SESSION['authname'] ?>">
					<input type="hidden" name="email" value="<?= $_SESSION['authemail'] ?>">

				<? } ?>
				<tr>
					<td><?= FIELD_PHONE ?>:</td>
					<td><?= $errorphone1 ?><input onclick="this.style.color='#000';" type="text" name="phone" value="<?= $_POST['phone'] ?>" style="width: 220px;"><?= $errorphone2 ?></td>
				</tr>
				<tr>
					<td><?= FIELD_OBJECT ?>:</td>
					<td><select name="subject" style="width: 225px;"><? foreach ($contact_subject as $v) {
																			if ($_POST['subject'] == $v) $selected = " selected";
																			else unset($selected); ?><option value="<?= $v ?>" <?= $selected ?>><?= $v ?></option><? } ?></select></td>
				</tr>
				<tr>
					<td colspan="2"><?= FIELD_MSG ?>:<br><?= $errorquestion1 ?><textarea name="question" cols="55" rows="11"><?= $_POST['question'] ?></textarea><?= $errorquestion2 ?></td>
				</tr>
				<tr>
					<td colspan="2" align="center"><?= $errorcapcha1 ?><div class="g-recaptcha" data-sitekey="6Lf7n5MiAAAAAP7_TlZ0Uw2-5onMv5NbnUUSxkkX"></div><?= $errorcapcha2 ?></td>
				</tr>
			</table>
			<p>
			<div align="center"><input class="submit bold" type="submit" name="contact" value="  <?= SEND_QUESTION ?> &raquo  "></div>
		</form>
	<? } ?>
</div>


<p>&nbsp;</p>
<h2><?= FAQ ?></h2>
<img src="/imgs/icons/arrow.png" alt="->" align="absmiddle" border="0" width="20" height="20"> <a href="<?= LINK_SELL ?>" style="font-size: 16px;"><?= CLICK_HOW_TO_SELL ?></a>
<p>
	<img src="/imgs/icons/arrow.png" alt="->" align="absmiddle" border="0" width="20" height="20"> <a href="<?= LINK_FAQ ?>" style="font-size: 16px;"><?= CLICK_FAQ ?></a>
<p>

<div id="map" class="products" style="width: 520px;">
	<div class="products-header">
		<h3><?= ALT_MAP ?></h3>
		<a class="modal_close" href="javascript:void(0)"></a>
	</div>
	<div class="products-main">
		<p>
			<img src="imgs/pics/carte.jpg" alt="<?= ALT_MAP ?>">
	</div>
</div>