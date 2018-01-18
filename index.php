<?php
include 'conf.class.php';
$affichage = Conf::init($_GET);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="UTF-8">
	<title>Créer un fichier de configuration</title>
	<link rel="stylesheet" href="style.css" />
</head>
<body>
	<h1>Créez votre configuration</h1>
	<div id="interface"><?php echo $affichage; ?></div>
</body>
</html>
