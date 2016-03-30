<?php
/**
 * Created by Caleb Milligan on 3/29/2016.
 */
if (isset($_GET["name"]) || isset($_GET["action"])) {
	include_once "MyPDO.php";
	$name = null;
	if (isset($_GET["name"])) {
		$name = $_GET["name"];
	}
	$db = new MyPDO();
	if (isset($_GET["action"])) {
		$action = strtolower($_GET["action"]);
		if ($action == "create") {
			if (!isset($_GET["url"])) {
				http_response_code(400);
				exit(1);
			}
			$url = $_GET["url"];
			$id = -1;
			if ($name) {
				if (!isValidAlias($name)) {
					exit("[1, null]");
				}
				$statement = $db->prepare("SELECT `url` FROM `links` WHERE `name`=:link_name OR `id`=:link_id");
				$statement->bindParam(":link_name", $name, PDO::PARAM_STR);
				$statement->bindParam(":link_id", $id, PDO::PARAM_INT);
				$success = $statement->execute();
				if ($statement->rowCount() > 0) {
					$statement->closeCursor();
					exit("[2, null]");
				}
			}
			$statement = $db->prepare("INSERT INTO `links` (`url`, `name`) VALUES (:link_url, :link_name)");
			$statement->bindParam(":link_url", $url, PDO::PARAM_STR);
			$statement->bindParam(":link_name", $name, PDO::PARAM_STR);
			$success = $statement->execute();
			if (!$name) {
				$name = $db->lastInsertId();
			}
			http_response_code(200);
			$new_url = isApache() ? $_SERVER["HTTP_HOST"] . "/linkener/$name" : $_SERVER["HTTP_HOST"] . "/linkener/?name=$name";
			exit("[0, \"$new_url\"]");
		}
	}
	
	$statement = $db->prepare("SELECT `url` FROM `links` WHERE `name`=:link_name");
	$statement->bindParam(":link_name", $name, PDO::PARAM_STR);
	$success = $statement->execute();
	$url = "";
	if ($statement->rowCount() > 0) {
		$url = $statement->fetchColumn();
		$statement->closeCursor();
		header("Location: $url");
		exit;
	}
	$statement = $db->prepare("SELECT `url` FROM `links` WHERE `id`=:link_id");
	$statement->bindParam(":link_id", $name, PDO::PARAM_INT);
	$success = $statement->execute();
	if ($statement->rowCount() > 0) {
		$url = $statement->fetchColumn();
		$statement->closeCursor();
		header("Location: $url");
		exit;
	}
	echo "URL not found";
}

function isValidAlias($var) {
	return !preg_match("/^([+-]?\\d\\d*)$/", $var) && preg_match("/^[a-zA-Z_0-9-]*$/", $var);
}

function isApache() {
	return strpos($_SERVER['SERVER_SOFTWARE'], 'Apache') !== false;
}

?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta name="author" content="Caleb Milligan">
		<title>Linkener URL Shortener</title>
		<link rel="stylesheet" type="text/css" href="css/bootstrap.css">
		<link rel="stylesheet" type="text/css" href="css/linkener.css">
		<script type="application/javascript" src="js/jquery-1.11.3.min.js"></script>
		<script type="application/javascript" src="js/bootstrap.js"></script>
		<script type="application/javascript" src="js/linkener.js"></script>
	</head>
	<body>
		<form class="smallest" onsubmit="createLink();return false">
			<input id="input_url" title="URL" placeholder="URL to shorten" type="url" required="required">
			<input id="submit_link" class="btn-sm" type="submit" value="Create Link">
			<br>
			<hr>
			<span id="custom_span"><?php echo $_SERVER["HTTP_HOST"] . "/linkener/" ?></span>
			<input id="input_name" title="Name"
				   placeholder="Custom alias (optional)"
				   type="text"
				   pattern="^(?=.*[0-9]*)(?=.*[a-zA-Z_-])([a-zA-Z0-9_-]+)$">
		</form>
		<br>
		<a id="link_output"></a>
	</body>
</html>
