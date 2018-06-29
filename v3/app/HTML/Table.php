<?php

namespace App\HTML;

class Table
{
	private $params;
	private $columns;

	function __construct($params)
	{
		$this->params = $params;
	}

	public function moreColumns($columns)
	{
		$this->columns = $columns;
	}

	public function render()
	{
		foreach ($this->params['th'] as $k => $t) break;
		$id_field = $k;

		ob_start();
		?>

		<div class="table-container mb-3">
			<?= (!empty($this->params['pagination'])) ? $this->params['pagination']->render() : '' ?>
			<table class="table table-hover table-bordered">
				<thead>
					<tr>
						<th scope="col"><i class="fas fa-hashtag"></i></th>
						<?php foreach ($this->params['th'] as $key => $th): ?>
							<?php
							if (!isset($_GET['o']) ||
								strpos($_GET['o'], 'desc') !== false ||
								strpos($_GET['o'], $key) === false)
								$order = "asc";
							else
								$order = "desc";

							if (strpos($_GET['o'], $key) === false)
								$sort = "fa-sort";
							else if (strpos($_GET['o'], 'asc') !== false)
								$sort = "fa-sort-up";
							else
								$sort = "fa-sort-down";
							?>
							<th scope="col">
								<a class="myth" href="?s=<?= trim($_GET['s']) ?>&o=<?= $key ?>+<?= $order ?>">
									<?= $th ?>
									<i class="float-right fas <?= $sort ?>"></i>
								</a>
							</th>
						<?php endforeach; ?>
						<!-- More Columns -->
						<?php foreach ($this->columns['th'] as $key => $th): ?>
							<th scope="col"><?= $th ?></th>
						<?php endforeach; ?>
					</tr>
				</thead>
				<tbody>
				<?php foreach ($this->params['data'] as $key => $data): ?>
				<tr>
					<th scope="row"><?= $this->params['ignore']+$key+1 ?></th>
					<?php foreach ($this->params['th'] as $kth => $th): ?>
						<td>
							<?php

							if (!empty($data[$kth]))
							{
								if (!empty($this->params['td'][$kth]))
								{
									$php = $this->params['td'][$kth]['php'];
									$str = $this->params['td'][$kth]['str'];

									$tmp = $data[$kth];
									if (!empty($php))
									{
										eval('$code = '.sprintf($php, $tmp).';');
										$tmp = $code;
									}

									if (!empty($str))
									{
										$tmp = sprintf($str, $tmp, $data[$id_field]);
									}

									echo $tmp;
								}
								else
								{
									echo $data[$kth];
								}
							}
							else
							{
								echo '--';
							}

							?>
						</td>
					<?php endforeach; ?>
					<!-- More Columns -->
					<?php foreach ($this->columns['th'] as $kth => $th): ?>
						<td>
							<?php

							if (!empty($this->columns['td'][$kth]))
							{
								$php = $this->columns['td'][$kth]['php'];
								$str = $this->columns['td'][$kth]['str'];

								$tmp = $data[$kth];
								if (!empty($php))
								{
									eval('$code = '.sprintf($php, $tmp).';');
									$tmp = $code;
								}

								if (!empty($str))
								{
									$tmp = sprintf($str, $tmp, $data[$id_field]);
								}

								echo $tmp;
							}
							else
							{
								echo $data[$kth];
							}

							?>
						</td>
					<?php endforeach; ?>
				</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<?= (!empty($this->params['pagination'])) ? $this->params['pagination']->render() : '' ?>
		</div>

		<?php
		return ob_get_clean();
	}
}
