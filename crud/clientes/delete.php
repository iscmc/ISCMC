<?php
require_once 'functions.php';

if (isset($_GET['id'])) {
	delete($_GET['id']);
} else {
	die("Erro: ID não definido");
}
?>