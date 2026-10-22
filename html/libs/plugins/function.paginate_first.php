<?php

function smarty_function_paginate_first($params, $template)
{
    $text = isset($params['text']) ? $params['text'] : '<<';
    $pageParam = 'page';
    $href = '?'.$pageParam.'=1';
    return '<a href="'.$href.'">'.$text.'</a>';
}


