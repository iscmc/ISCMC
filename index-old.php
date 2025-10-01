<?php
/**
 * Servidor de contingência ISCMC Off frid
 *
 * Este arquivo faz parte do framework MVC Projeto Contingenciamento.
 *
 * @category Framework
 * @package  Servidor de contingência ISCMC
 * @author   Sergio Figueroa <sergio.figueroa@iscmc.com.br>
 * @license  MIT, Apache
 * @link     http://10.132.16.43/ISCMC
 * @version  1.0.0
 * @since    2025-04-01
 * @maindev  Sergio Figueroa
 */

 ?>
<!DOCTYPE HTML>
<html lang="pt-BR">
	<head>
		<!-- título -->
		<title>Projeto Contingenciamento - ISCMS</title>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no" />
		<link rel="stylesheet" href="assets/css/style.css" />
	</head>
	<body class="is-preload">
		<!-- Cabecalho -->
			<header id="header">
				<h1>Projeto Contingenciamento de Prescrições</h1>
				<p>Sejam bem-vindos ao Hospital da Santa Casa de Misericórdia de Curitiba.<br>
				Caso precisar de mais informações entre em contato com a gente.</p>
			</header>

		<!-- Formulário -->
			<form id="signup-form" method="post" action="#">
				<input type="email" name="email" id="email" placeholder="O seu endereço de e-mail" required />
				<input type="submit" value="Quero mais informações" />
			</form>

		<!-- Rodapé -->
			<footer id="footer">
				<ul class="icons">
					<li><a href="#" class="icon brands fa-facebook"><span class="label">Facebook</span></a></li>
					<li><a href="#" class="icon brands fa-instagram"><span class="label">Instagram</span></a></li>
					
					<li><a href="#" class="icon fa-envelope"><span class="label">Email</span></a></li>
				</ul>
				<ul class="copyright">
					<li>&copy; <?php echo date('Y'); ?> Hospital Santa Casa da Misericórdia de Curitiba.</li><li>Uma iniciativa da <a href="https://www.icsmc.com.br" target="_blank">ISCMC</a></li>
				</ul>
			</footer>

		<!-- Scripts -->
			<script src="assets/js/script.js"></script>

	</body>
</html>