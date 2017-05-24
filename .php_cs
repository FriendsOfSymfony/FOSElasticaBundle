<?php

$header = <<<EOF
This file is part of the FOSElasticaBundle package.

(c) FriendsOfSymfony <http://friendsofsymfony.github.com/>

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
EOF;

Symfony\CS\Fixer\Contrib\HeaderCommentFixer::setHeader($header);

$finder = Symfony\CS\Finder\DefaultFinder::create()
    ->in(array(__DIR__))
;

return Symfony\CS\Config\Config::create()
    ->level(Symfony\CS\FixerInterface::SYMFONY_LEVEL)
    ->fixers(array(
        'combine_consecutive_unsets',
        'header_comment',
        'short_array_syntax',
        'newline_after_open_tag',
        'no_php4_constructor',
        'no_useless_else',
        'ordered_class_elements',
        'ordered_use',
        'php_unit_construct',
        '-phpdoc_no_empty_return',
    ))
    ->setUsingCache(true)
    ->finder($finder)
;
