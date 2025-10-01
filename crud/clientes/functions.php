<?php

require_once '../config.inc.php';
require_once DBAPI;

$customers = null; //o conjunto de todos os clientes
$customer = null; //um único cliente

//listagem de clientes
function index() {
	global $customers;
	$customers = find_all('customers');
}

//cadastro de clientes
function add() {
	if (!empty($_POST['customer'])) {
		$today = date_create('now', new DateTimeZone('America/Sao_Paulo'));

		$customer = $_POST['customer'];
		$customer['modified'] = $customer['created'] = $today->format("Y-m-d H:i:s");

		save('customers', $customer);
		header('location: index.php');
	}
}

//Atualizacao/Edicao de Cliente
function edit() {
	$now = date_create('now', new DateTimeZone('America/Sao_Paulo'));

	if (isset($_GET['id'])) {
		$id = $_GET['id'];

		if (isset($_POST['customer'])) {
			$customer = $_POST['customer'];
			$customer['modified'] = $now->format("Y-m-d H:i:s");

			update('customers', $id, $customer);
			header('location: index.php');
		} else {
			global $customer;
			$customer = find('customers', $id);
		}
	} else {
		header('location: index.php');
	}
}

// visualização de um cliente
function view($id = null) {
	global $customer;
	$customer = find('customers', $id);
}

// exclusão de um cliente
function delete($id = null) {
	global $customer;
	$customer = remove('customers', $id);
	header('location:index.php');
}

function clear_messages() {
	unset($_SESSION['message']);
	unset($_SESSION['type']);
}