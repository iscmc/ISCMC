<?php
//teste criado como exemplo do tutorial no site Web Dev Academy
//https://staging.webdevacademy.com.br/tutoriais/crud-bootstrap-php-mysql-parte1/

if (!isset($_SESSION)) {
//Verificar se a sessão não já está aberta.
	session_start();
}
require_once 'config.inc.php';
require_once DBAPI;

include HEADER_TEMPLATE;

$db = open_database();
?>

<h1>Dashboard</h1>
<hr>

<?php if ($db): ?>
<div class="row">
	<div class="col-xs-6 col-sm-3 col-md-2">
		<a href="clientes/add.php" class="btn btn-primary">
			<div class="row">
				<div class="col-xs-12 text-center">
					<i class="fa fa-plus fa-5x"></i>
				</div>
				<div class="col-xs-12 text-center">
					<p>Novo Cliente</p>
				</div>
			</div>
		</a>
	</div>

	<div class="col-xs-6 col-sm-3 col-md-2">
		<a href="clientes" class="btn btn-default">
			<div class="row">
				<div class="col-xs-12 text-center">
					<i class="fa fa-user fa-5x"></i>
				</div>
				<div class="col-xs-12 text-center">
					<p>Clientes</p>
				</div>
			</div>
		</a>
	</div>
</div>

<?php else: ?>
	<div class="alert alert-danger" role="alert">
		<p><strong>ERRO:</strong> Não foi possível Conectar ao Banco de Dados!</p>
	</div>
<?php endif;

include FOOTER_TEMPLATE;
?>