<?php
require '../inc/inits.php';
ob_start(); // Page content
?>

<!-- Tableau des chaînes non traduites -->
<table class="table table-hover table-bordered table-sm">
	<thead class="thead-dark">
		<tr>
			<th scope="col"><i class="fas fa-hashtag"></i></th>
			<th scope="col">Fichier</th>
			<th scope="col">Chaînes non traduites</th>
		</tr>
	</thead>
	<tbody>
	<?php
	$dirs = array(
		'../',
		'../app/',
		'../app/HTML/',
		'../inc/',
		'../lang/'
	);
	$cntRows = 1;
	foreach ($dirs as $dir)
	{
		$folder = new DirectoryIterator($dir);
		foreach ($folder as $file)
		{
			if (strpos(($filePath = $file->getPathname()), '.php') !== false &&
				strpos(($filePath = $file->getPathname()), 'notinstalled.php') === false)
			{
				$content = file_get_contents($filePath);
				$matches = array();
				if (preg_match_all("/(([a-z]*Title|[a-z]*Content)[\s]*=[\s]*\"|>)[a-zA-Z]{1,}[a-zA-Z_ 'âêîôûàéèù0-9]{1,}(<|\")/", $content, $matches) > 0)
		    	{
		    		?>
		    		<tr>
						<th scope="row"><?= $cntRows++ ?></th>
						<td><?= str_replace('../', '', $filePath) ?></td>
						<td>
							<?php foreach ($matches[0] as $match): ?>
								<span class="text-muted aslink"><?= str_replace(array('>', '<'), '', $match) ?></span><br>
							<?php endforeach; ?>
						</td>
					</tr>
					<?php
		    	}
			}
		}
	}
	?>
	</tbody>
</table>

<!-- Tableau des chaînes de traductions -->
<table class="table table-hover table-bordered table-sm">
	<thead class="thead-dark">
		<tr>
			<th scope="col"><i class="fas fa-hashtag"></i></th>
			<th scope="col"><?= $l['Key'] ?></th>
			<th scope="col">Valeur</th>
			<th scope="col">Nombre d'occurrences</th>
		</tr>
	</thead>
	<tbody>
	<?php
	$occurrences = array();
	$cntRows = 2;
	foreach ($l as $key => $trad)
	{
		$occurrences[$key] = array();
		$string = "\$l['".str_replace('\'', '\\\'', $key)."']";
		$dirs = array(
			'../',
			'../app/',
			'../app/HTML/',
			'../inc/',
			'../lang/',
		);
		foreach ($dirs as $dir)
		{
			$folder = new DirectoryIterator($dir);
			foreach ($folder as $file)
			{
				if (strpos(($filePath = $file->getPathname()), '.php') !== false)
				{
					$content = file_get_contents($filePath);
					if (($countOccurs = substr_count($content, $string)) > 0)
			    		$occurrences[$key][$filePath] = $countOccurs;
				}
			}
		}
	?>
		<tr>
			<th scope="row"><?= $cntRows++ ?></th>
			<td><?= $key ?></td>
			<td><?= $trad ?></td>
			<td>
				<?php foreach ($occurrences[$key] as $path => $occur): ?>
					<span class="text-muted"><b><?= $occur ?></b> <?= $l['in'] ?> <span class="aslink"><?= str_replace('../', '', $path) ?></span></span><br>
				<?php endforeach; ?>
			</td>
		</tr>
	<?php
	}
	?>
	</tbody>
</table>

<?php
$pageContent = ob_get_clean();
$pageTitle = "Traductions";
require '../inc/default.php';
