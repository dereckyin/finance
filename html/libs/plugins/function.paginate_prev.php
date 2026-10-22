<?php

function smarty_function_paginate_prev($params, $template)
{
    $text = isset($params['text']) ? $params['text'] : '<';
    $pageParam = 'page';
    $current = method_exists('SmartyPaginate','getCurrentPage') ? SmartyPaginate::getCurrentPage() : 1;
    $prev = max(1, $current - 1);
    $href = '?'.$pageParam.'='.$prev;
    return '<a href="'.$href.'">'.$text.'</a>';
}


