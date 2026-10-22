<?php

function smarty_function_paginate_last($params, $template)
{
    $text = isset($params['text']) ? $params['text'] : '>>';
    $pageParam = 'page';
    $total = method_exists('SmartyPaginate','getTotalPages') ? SmartyPaginate::getTotalPages() : 1;
    $href = '?'.$pageParam.'='.$total;
    return '<a href="'.$href.'">'.$text.'</a>';
}


