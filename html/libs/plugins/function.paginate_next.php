<?php

function smarty_function_paginate_next($params, $template)
{
    $text = isset($params['text']) ? $params['text'] : '>';
    $pageParam = 'page';
    $current = method_exists('SmartyPaginate','getCurrentPage') ? SmartyPaginate::getCurrentPage() : 1;
    $total = method_exists('SmartyPaginate','getTotalPages') ? SmartyPaginate::getTotalPages() : $current;
    $next = min($total, $current + 1);
    $href = '?'.$pageParam.'='.$next;
    return '<a href="'.$href.'">'.$text.'</a>';
}


