<?php

namespace App\HTML;

class Pagination
{
	private $text;
	private $db;
	private $url;
	private $end;

	function __construct($params)
	{
		$this->text = $params['text'];
		$this->db 	= $params['database'];
		$this->url 	= $params['url'];
		
		$this->end 	= floor($this->db->getTotal() / $this->db->getMaxrows()) + 1;
	}

	public function render()
	{
		$p = $this->db->getPage();
		ob_start();
		?>

		<span><?= explode('[]', $this->text)[0] ?></span>
		<div class="pagination" style="display:inline-block">
			<a class="page-link dropdown-toggle" href="#<?= $this->tab ?>" role="button" id="maxrows" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    			<?= $this->db->getMaxrows() ?>
  			</a>
			<div class="dropdown-menu" aria-labelledby="maxrows">
				<a class="dropdown-item" href="<?= $this->changeMax(20) ?>">20</a>
				<a class="dropdown-item" href="<?= $this->changeMax(50) ?>">50</a>
				<a class="dropdown-item" href="<?= $this->changeMax(100) ?>">100</a>
				<a class="dropdown-item" href="<?= $this->changeMax(300) ?>">300</a>
				<a class="dropdown-item" href="<?= $this->changeMax(1000) ?>">1000</a>
			</div>
		</div>
		<span><?= sprintf(explode('[]', $this->text)[1], $this->db->getTotal()) ?></span>
		<ul class="pagination fa-pull-right">
			<li class="page-item <?php if ($p == 1) echo 'disabled'; ?>">
				<a class="page-link" href="<?= $this->goToPage(1) ?>">
					<span aria-hidden="true"><i class="fas fa-angle-double-left"></i></span>
				</a>
			</li>
			<li class="page-item <?php if (!$this->valid($p-1)) echo 'disabled'; ?>">
				<a class="page-link" href="<?= $this->goToPage($p-1) ?>">
					<span aria-hidden="true"><i class="fas fa-angle-left"></i></span>
				</a>
			</li>
			<?php if ($this->valid($p-3)): ?>
				<li class="page-item disabled"><a class="page-link">...</a></li>
			<?php endif; ?>

			<?php for ($i = -2; $i <= 2; $i++): ?>
				<?php if ($this->valid($p+$i)): ?>
					<li class="page-item <?php if (!$i) echo 'active'; ?>"><a class="page-link" href="<?= $this->goToPage($p+$i) ?>"><?= $p+$i ?></a></li>
				<?php endif; ?>
			<?php endfor; ?>

			<?php if ($this->valid($p+3)): ?>
				<li class="page-item disabled"><a class="page-link">...</a></li>
			<?php endif; ?>
			<li class="page-item <?php if (!$this->valid($p+1)) echo 'disabled'; ?>">
				<a class="page-link" href="<?= $this->goToPage($p+1) ?>">
					<span aria-hidden="true"><i class="fas fa-angle-right"></i></span>
				</a>
			</li>
			<li class="page-item <?php if ($this->end == $p) echo 'disabled'; ?>">
				<a class="page-link" href="<?= $this->goToPage($this->end) ?>">
					<span aria-hidden="true"><i class="fas fa-angle-double-right"></i></span>
				</a>
			</li>
		</ul>

		<?php
		return ob_get_clean();
	}

	private function changeMax($max)
	{
		$href = '?m='.$max;
		foreach ($this->url as $key => $url)
		{
			$url = trim($url);
			if (!empty($url)) $href .= '&'.$key.'='.$url;
		}
		$href .= '&p=1';

		return $href;
	}

	private function goToPage($page)
	{
		$href = '?p='.$page;
		foreach ($this->url as $key => $url)
		{
			$url = trim($url);
			if (!empty($url)) $href .= '&'.$key.'='.$url;
		}

		return $href;
	}

	private function valid($page)
	{
		return ($page > 0 && $page <= $this->end);
	}
}
