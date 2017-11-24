<?php
error_reporting(-1);
ini_set('display_errors', 'On');
function dataHora($dtime){
	$data = date("d/m/Y", strtotime($dtime));
	$hora = date("H:i:s", strtotime($dtime));
	return $data.' '.$hora;
}
?>
<!doctype html>
<html>
	<head>
		<meta charset="utf-8">
		<title>Filtro e-SUS CDS</title>
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<link rel="stylesheet" type="text/css" href="bootstrap/dist/css/bootstrap.min.css">
		<!--<link rel="stylesheet" type="text/css" href="bootstrap/dist/css/bootstrap-reboot.min.css">-->
		<link rel="stylesheet" type="text/css" href="bootstrap/dist/css/bootstrap-grid.min.css">
  		<link rel="stylesheet" type="text/css" href="js/lib/jquery-ui/jquery-ui.css">
  		<link rel="stylesheet" type="text/css" href="css/main.css">
	</head>
	<body>
		<header>
			<nav class="navbar navbar-light bg-light">
				<a class="navbar-brand" href="#">
					<img src="img/logo_e-sus.ico" width="30" height="30" class="d-inline-block align-top" alt="logo">
					Filtro e-SUS CDS
				</a>
			</nav>
		</header>
		<main>

		<section class="container pesquisa">
			<form action="index.php">
				<div class="form-group">
				<fieldset>
					<legend id="title-form">Pesquisar</legend>
					<fieldset class="row">
						<legend>Periodo das fichas</legend>
						<label class="col-6">Inicial:
							<input id="dtFichaInicial" class="form-control" placeholder="yyyy-mm-dd" type="text" name="dtInicialFic" />
						</label>
						<label class="col-6">Final:
							<input id="dtFichaFinal" class="form-control" placeholder="yyyy-mm-dd" type="text" name="dtFinalFic" />
						</label>
					</fieldset>

					<fieldset class="row">
						<legend>Periodo das digitações</legend>
						<label class="col-6">Inicial:
							<input id="dtDigitaInicial" class="form-control" placeholder="yyyy-mm-dd" type="text" name="dtInicialDig" />
						</label>
						<label class="col-6">Final: 
							<input id="dtDigitaFinal" class="form-control" placeholder="yyyy-mm-dd" type="text" name="dtFinalDig" />
						</label>
					</fieldset>
					
					<fieldset class="row">
						<legend>Profissional responsável</legend>
						<div class="form-group col-12">
							<label for="profResponsavel">Pela visita:</label>
							<input type="text" class="form-control" name="profResponsavel"  id="profResponsavel" aria-describedby="profResponsavel" placeholder="Nome do profissional" value="<?=(isset($_GET['profResponsavel']) && strlen($_GET['profResponsavel']))?htmlentities($_GET['profResponsavel']):''?>">
							<small id="profResponsavel" class="form-text text-muted">Nome do profissional que fez a visita.</small>
						</div>

						<div class="form-group col-12">
							<label for="profDigitador">Por digitar a Ficha:</label>
							<input type="text" class="form-control" name="profDigitador"  id="profDigitador" aria-describedby="profDigitador" placeholder="Nome do profissional" value="<?=(isset($_GET['profDigitador']) && strlen($_GET['profDigitador']))?htmlentities($_GET['profDigitador']):''?>">
							<small id="profDigitador" class="form-text text-muted">Nome do profissional que digitou a ficha.</small>
						</div>
					</fieldset>
					<fieldset class='row'>
						<legend>Unidade</legend>
						<div class='col-12'>
							<select name="unidadeCnes" class="form-control" required>
								<option value="" disabled>Escolha a unidade</option>
								<option value="2027143">PSF VILA BOM JESUS</option>
								<option value="2027178">PSF VILA CAMARGO</option>
								<option value="2027208">PSF JARDIM VIRGINIA</option>
								<option value="2027216">PSF JARDIM GRAJAU</option>
								<option value="2034301">UBS JARDIM MARINGA</option>
								<option value="2045443">PSF VILA MARIANA</option>
								<option value="2048493">PSF VILA SAO CAMILO</option>
								<option value="2048884">UBS VILA SANTA MARIA</option>
								<option value="2051273">EACS AGROVILAS</option>
								<option value="2051559">UBS PARQUE SAO JORGE</option>
								<option value="2053071">PSF VILA SAO MIGUEL</option>
								<option value="2056259">PSF VILA DOM BOSCO CIMENTOLANDIA</option>
								<option value="2058219">PSF ALTO DA BRANCAL</option>
								<option value="2059134">PSF JARDIM IMPERADOR</option>
								<option value="2059142">UBS CSIs</option>
								<option value="2065436">PSF VILA TAQUARI</option>
								<option value="2070979">PSF JARDIM BELA VISTA</option>
								<option value="2070995">UBS VILA APARECIDA</option>
								<option value="2096390">PSF VILA SAO BENEDITO</option>
								<option value="6985890">UNIDADE DE SAUDE CAPUTERA AMARELA VELHA</option>
								<option value="7323859">UNIDADE DE SAUDE SAO ROQUE AREIA BRANCA</option>
								<option value="2047446">UNIDADE PSF GUARIZINHO JAO</option>
								<option value="2048833">UNIDADE PSF PACOVA</option>
							</select>
						</div>
					</fieldset>

					<fieldset disabled="disabled">
						<legend>Tipo de ficha</legend>
						<div class="form-check">
							<label class="form-check-label">
								<input class="form-check-input" type="checkbox" name="cadDomiciliar" id="cadDomiciliar" checked>
								Cadastro Domiciliar
							</label>
						</div>
						<div class="form-check">
							<label class="form-check-label">
								<input class="form-check-input" type="checkbox" name="cadIndividual" id="cadIndividual" checked>
								Cadastro Individual
							</label>
						</div>
						<div class="form-check">
							<label class="form-check-label">
								<input class="form-check-input" type="checkbox" name="visitaDomiciliar" id="visitaDomiciliar" checked>
								Visita Domiciliar
							</label>
						</div>
					</fieldset>
				</fieldset>
				<button class="btn btn-primary btn-lg btn-block" type="submit" name="envio">Pesquisar</button>
				</div>
			</form>
		</section>
		
		<section class="tabela">
			<?php
				if(isset($_GET['envio'])) {
					try {
					require 'BDesus.php';
					$esusBD = new BDesus();
					$fichas = $esusBD->consultar($_GET);		
					} catch (PDOException  $e) {
					print $e->getMessage();
				}
					?>
					<table class="table table-striped">
					<caption>Cadastros de fichas no e-sus</caption>
					<thead>
						<tr>
							<th>Código</th>
							<th>Data da Digitaçao</th>
							<th>Data da Ficha</th>
							<th>Profissional</th>
							<th>Digitador</th>
							<th>Unidade CNES</th>					
							<th>Tipo da Ficha</th>
						</tr>
					</thead>

					<tbody>
					<?php
					foreach($fichas as $ficha) {
					?>
					<tr <?=($ficha['prof_nome']!=$ficha['digitador_nome'])?'bgcolor=yellow':''?>>
						<td><?=$ficha['cod_ficha']?></td>
						<td><?=dataHora($ficha['dt_revisao'])?></td>
						<td><?=dataHora($ficha['dt_ficha'])?></td>
						<td><?=$ficha['prof_nome']?></td>
						<td><?=$ficha['digitador_nome']?></td>
						<td><?=$ficha['nu_cnes']?></td>					
						<td><?=$ficha['tipo']?></td>
					</tr>
					<?php
					}
				}
			?>		
					</tbody>
					<tfoot>
						<tr>
							<td colspan="7">Foi encontrado <?=count($fichas)?> registro(s).</td>
						</tr>
						
					</tfoot>
			</table>
		</section>
		
	</main>

  	<footer class="container">
		<p>Criado por &copy; 2015 Edgar de Jesus Endo Junior</p>
		<p>Modificado por &copy; 2017 Rafael Pereira de Lacerda Lopes</p>
	</footer>
	<script src="js/lib/jquery.js"></script>	
	<script src="js/lib/jquery-ui/jquery-ui.js"></script>
	<script>
		$(function() {
			$( "#dtFichaInicial").datepicker({ dateFormat:'yy-mm-dd'});
		});
		$(function() {
			$( "#dtFichaFinal").datepicker({ dateFormat:'yy-mm-dd'});
		});
		$(function() {
			$( "#dtDigitaInicial").datepicker({ dateFormat:'yy-mm-dd'});
		});
		$(function() {
			$( "#dtDigitaFinal").datepicker({ dateFormat:'yy-mm-dd'});
		});				
	</script>
	</body>
</html>
