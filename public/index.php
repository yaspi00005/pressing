<?php

use App\Kernel;

// Symfony 6.1 n'est pas officiellement compatible avec PHP 8.4 : on masque les avis de dépréciation
// provenant de vendor/ (sans effet sur le fonctionnement) pour qu'ils ne s'affichent plus.
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

require_once dirname(__DIR__).'/vendor/autoload_runtime.php';

return function (array $context) {
    return new Kernel($context['APP_ENV'], (bool) $context['APP_DEBUG']);
};
