<?php
require '../inc/inits.php';
ob_start(); // Page content
?>

<!-- CHAINES NON TRADUITES -->
<div class="table-container box-shadow mb-3">
	<h3 class="h5 mb-3">_Missing translation strings_</h3>
	<table class="table table-hover table-bordered table-sm">
		<thead>
			<tr>
				<th scope="col"><i class="fas fa-hashtag"></i></th>
				<th scope="col">_Fichier_</th>
				<th scope="col">_Chaînes à traduire_</th>
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
					if (preg_match_all("/[^\w]_[a-zÀ-ÿ0-9\s'().!?]+_[^\w]/i", $content, $matches) > 0)
			    	{
			    		?>
			    		<tr>
							<th scope="row"><?= $cntRows++ ?></th>
							<td><?= str_replace('../', '', $filePath) ?></td>
							<td>
								<?php
								foreach ($matches[0] as $match)
								{
									$match = trim(preg_replace("/[<>\"_]/", '', $match));
									$folder = new DirectoryIterator('../lang/');
									$cnt = 0;
									foreach ($folder as $file)
									{
										if (strpos(($filePath = $file->getPathname()), 'index.php') === false)
										{
											$content = file_get_contents($filePath);
											if (strpos($content, "'$match'") !== false)
												$cnt++;
										}
									}
								?>
									<span class="aslink"><?= $match ?></span>
									<small class="dark-text float-right" style="<?= ($cnt > 0) ? 'color:#2ecc71' : '' ?>"><?= $cnt.' '.$l['available translation(s)'] ?></small>
									<br>
								<?php
								}
								?>
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
</div>
<!-- /CHAINES NON TRADUITES -->

<!-- TRADUCTIONS -->
<div class="table-container box-shadow mb-3">
	<h3 class="h5 mb-3">_Available translation strings_</h3>
	<table class="table table-hover table-bordered table-sm">
		<thead>
			<tr>
				<th scope="col"><i class="fas fa-hashtag"></i></th>
				<th scope="col"><?= $l['Key'] ?></th>
				<th scope="col">_Valeur_</th>
				<th scope="col">_Occurrences_</th>
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
			<tr style="<?= (empty($occurrences[$key])) ? 'background-color:#ff6b81;color:white' : ''; ?>">
				<th scope="row"><?= $cntRows++ ?></th>
				<td><?= $key ?></td>
				<td><?= $trad ?></td>
				<td>
					<?php foreach ($occurrences[$key] as $path => $occur): ?>
						<span class="dark-text"><b><?= $occur ?></b> <?= $l['in'] ?> <span class="aslink"><?= str_replace('../', '', $path) ?></span></span><br>
					<?php endforeach; ?>
				</td>
			</tr>
		<?php
		}
		?>
		</tbody>
	</table>
</div>
<!-- /TRADUCTIONS -->

<?php
$pageContent = ob_get_clean();
$pageTitle = $l['Translations'];
$arianList = array(
	$l['Dashboard'] => array('href' => '../', 'icon' => '<i class="fas fa-tachometer-alt"></i>'),
	$l['Settings'] => array('href' => '../settings.php')
);
require '../inc/default.php';
