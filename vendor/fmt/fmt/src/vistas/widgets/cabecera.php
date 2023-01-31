<?php
$links = [];
$incluye_cerrar_sesion = true;
$activo = false;
$dev = false;
$logo = 'https://www.transporte.gob.ar/_img/logo_ministerio_grande_blanco.png';

if(isset($conf)){
	if(isset($conf['links']) && is_array($conf['links'])){
		$links = $conf['links'];
	}
	if(isset($conf['incluye_cerrar_sesion'])){
		$incluye_cerrar_sesion = (bool) $conf['incluye_cerrar_sesion'];
	}
	if(isset($conf['activo'])){
		$activo = $conf['activo'];
	}
	if(isset($conf['logo'])){
		$logo = $conf['logo'];
	}
	if(isset($conf['dev'])){
		$dev = (bool) $conf['dev'];
	}
}

if($incluye_cerrar_sesion){
	$links['/panel/logout.php'] = 'Cerrar Sesión';
}

?>
<header>
<nav class="navbar navbar-default" style="min-height:72px">
<?php 
// Warning para proyectos en Desarrollo.
if ($dev == true) {
	echo '<div style="position: absolute;width: 100%;text-align: center;font-size: 48px;font-weight: bold;color: rgba(255,0,255,.4);">VERSION DE DESARROLLO</div>';
}
 ?>
	<div class="container">
		<div class="navbar-header">
			<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse-header" aria-expanded="false">
				<span class="sr-only">Toggle navigation</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<?php 			
			if ($logo){
				echo '<a class="navbar-brand" href="#">';
				echo '<img src="'.$logo.'" height="50" alt=""></a>';
			}
			?>
		</div>
		<div class="collapse navbar-collapse" id="navbar-collapse-header">
			<ul class="nav navbar-nav navbar-right">
<?php
foreach($links as $link=>$name) {

	if(!is_array($name)){
		echo '<li';
		if($activo && $activo === $link){
			echo ' class="active"';
		}
		echo '><a href="'.$link.'">'.$name.'</a></li>';
	}else {
		$line = '';
		$subactivo = false;
		$subline = '';
		foreach ($name as $sublink=>$subname)
		{
			$subline .='<li';
			if($activo && $activo === $sublink){
				$subline .= ' class="active"';
				$subactivo = true;
			}
			$subline .= '><a href="'.$sublink.'">'.$subname.'</a></li>';
		}

		$line =  '<li';
		if($subactivo){
			$line .= ' class="active"';
		}
		$line .='><a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">'.
				 '<i class="fa fa-folder-open-o" aria-hidden="true"></i>'.$link.'<span class="caret"></span></a><ul class="dropdown-menu">'.$subline;

		$line .='</ul></li>';
		echo $line;
	}
      
}     	
?>
			</ul>
		</div>
	</div>
</nav>
</header>