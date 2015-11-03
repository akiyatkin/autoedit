<?php

$conf = infra_config();

$dirs = infra_dirs();

if ($conf['infra']['cache'] == 'fs') {
	@mkdir($dirs['cache']);
	@mkdir($dirs['cache'].'admin_takefiles/');
}
