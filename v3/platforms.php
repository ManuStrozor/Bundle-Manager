<?php
require 'inc/inits.php';

$platforms = $db->fetchAll("SELECT * FROM ".PLATFORMS_TABLE);

function getIcon($name)
{
	$name = strtolower($name);

	if (strpos($name, 'ps') !== false || strpos($name, 'play') !== false || strpos($name, 'station') !== false)
	{
		echo '<i class="fab fa-playstation"></i>';
	}
	else if (strpos($name, 'x') !== false || strpos($name, 'box') !== false)
	{
		echo '<i class="fab fa-xbox"></i>';
	}
	else if (strpos($name, 'steam') !== false)
	{
		echo '<i class="fab fa-steam"></i>';
	}
}

ob_start();
?>

<ul class="nav nav-tabs" id="myTab" role="tablist">
    <?php foreach ($platforms as $key => $platform): ?>
    <li class="nav-item">
        <a class="nav-link <?php echo (!$key) ? 'active' : ''; ?>" id="<?= strtolower($platform['name']) ?>-tab" data-toggle="tab" href="#<?= strtolower($platform['name']) ?>" role="tab" aria-controls="<?= strtolower($platform['name']) ?>" aria-selected="<?php echo (!$key) ? 'true' : 'false'; ?>"><?= getIcon($platform['name'])." ".$platform['name'] ?></a>
    </li>
    <?php endforeach; ?>
</ul>
<div class="tab-content" id="myTabContent">
    <?php foreach ($platforms as $key => $platform): ?>
    <?php
    $games = $db->fetchAll("SELECT DISTINCT name FROM ".GAMES_TABLE." g INNER JOIN ".KEYS_TABLE." WHERE platform_id = {$platform['id']} AND g.id = game_id AND boxed = 0 ORDER BY name ASC");
    ?>
    <div class="tab-pane fade <?php echo (!$key) ? 'show active' : ''; ?>" id="<?= strtolower($platform['name']) ?>" role="tabpanel" aria-labelledby="<?= strtolower($platform['name']) ?>-tab">
        <canvas id="chart_<?= strtolower($platform['name']) ?>" width="400" height="400"></canvas>
    </div>
    <?php endforeach; ?>
</div>

<?php
$pageContent = ob_get_clean();
ob_start(); // Foot scripts
?>

<script>
<?php foreach ($platforms as $key => $platform): ?>
<?php
$games = $db->fetchAll("SELECT DISTINCT name FROM ".GAMES_TABLE." g INNER JOIN ".KEYS_TABLE." WHERE platform_id = {$platform['id']} AND g.id = game_id AND boxed = 0 ORDER BY name ASC");
?>
var ctx_<?= $platform['id'] ?> = document.getElementById("chart_<?= strtolower($platform['name']) ?>");
var myChart_<?= $platform['id'] ?> = new Chart(ctx_<?= $platform['id'] ?>, {
    type: 'horizontalBar',
    data: {
        labels: [
        	<?php foreach ($games as $key => $game): ?>
        	"<?= $game['name'] ?>"<?php if ($games[$key+1]) echo ",\n"; ?>
        	<?php endforeach; ?>
        ],
        datasets: [{
            label: '<?= $l['Keys'] ?>',
            data: [
            	<?php
            	foreach ($games as $key => $game) {
					$num = $db->fetchAll("SELECT * FROM ".GAMES_TABLE." g INNER JOIN ".KEYS_TABLE." WHERE name = \"{$game['name']}\" AND platform_id = {$platform['id']} AND g.id = game_id AND boxed = 0");
					echo ($games[$key+1]) ? count($num).",\n" : count($num);
        		}
        		?>
            ],
            backgroundColor: [
            	<?php
            	foreach ($games as $key => $game) {
            		echo ($games[$key+1]) ? "'rgba(0, 123, 255, 1)',\n" : "'rgba(0, 123, 255, 1)'";
        		}
        		?>
            ],
            borderWidth: 0
        }]
    },
    options: {
    	legend: {
    		display: false
    	},
        scales: {
            xAxes: [{
                ticks: {
                    beginAtZero:true
                }
            }]
        }
    }
});
<?php endforeach; ?>
</script>

<?php
$footScript = ob_get_clean();
ob_start(); // Head scripts
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.bundle.min.js"></script>

<?php
$headScript = ob_get_clean();
$pageTitle = $l['Platforms'];
require 'inc/default.php';
