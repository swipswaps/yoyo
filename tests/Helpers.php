<?php

namespace Tests;

use Clickfwd\Yoyo\Services\Response;
use Clickfwd\Yoyo\View;
use Clickfwd\Yoyo\ViewProviders\YoyoViewProvider;
use Clickfwd\Yoyo\Yoyo;
use Clickfwd\Yoyo\YoyoCompiler;
use Clickfwd\Yoyo\YoyoHelpers;

function initYoyo($components = [])
{
    foreach ($components as $component) {
        require_once __DIR__."/app/Yoyo/{$component}.php";
    }

    $yoyo = new Yoyo();

    $yoyo->configure([
        'namespace' => 'Tests\\App\\Yoyo\\',
    ]);

    $yoyo->registerViewProvider(function () {
        return new YoyoViewProvider(new View(__DIR__.'/app/resources/views/yoyo'));
    });

    return $yoyo;
}

function compile_html($name, $html, $spinning = false)
{
    $yoyo = new Yoyo();

    return $yoyo->mount($name)->compile('anonymous', $html, $spinning);
}

function compile_html_with_vars($name, $html, $vars, $spinning = false)
{
    $yoyo = new Yoyo();

    return $yoyo->mount($name, $vars)->compile('anonymous', $html, $spinning);
}

function render($name, $variables = [], $attributes = [])
{
    $yoyo = new Yoyo();

    return $yoyo->mount($name, $variables, $attributes)->render();
}

function update($name, $action = 'render', $variables = [], $attributes = [])
{
    $yoyo = new Yoyo();

    return $yoyo->mount($name, $variables, $attributes, $action)->refresh();
}

function yoyo_update()
{
    return (new Yoyo())->update();
}

function mockYoyoGetRequest($url, $component, $target = '', $parameters = [])
{
    $request = array_merge([
        'component' => $component,
    ], $parameters);

    $server = [
        'REQUEST_METHOD' => 'GET',
        'HTTP_HX_REQUEST' => true,
        'HTTP_HX_CURRENT_URL' => $url,
        'HTTP_HX_TARGET' => $target,
    ];

    $requestService = Yoyo::request()->mock($request, $server);

    return $requestService;
}

function mockYoyoPostRequest($url, $component, $target = '', $parameters = [])
{
    $request = array_merge([
        'component' => $component,
    ], $parameters);

    $server = [
        'REQUEST_METHOD' => 'POST',
        'HTTP_HX_REQUEST' => true,
        'HTTP_HX_CURRENT_URL' => $url,
        'HTTP_HX_TARGET' => $target,
    ];

    $requestService = Yoyo::request()->mock($request, $server);

    return $requestService;
}

function resetYoyoRequest()
{
    Yoyo::request()->reset();
}

function headers()
{
    return (Response::getInstance())->getHeaders();
}

function hxattr($name, $value = '')
{
    return YoyoCompiler::hxprefix($name).addValue($value);
}

function yoattr($name, $value = '')
{
    return YoyoCompiler::yoprefix($name).addValue($value);
}

function yoprefix_value($value)
{
    return YoyoCompiler::yoprefix_value($value);
}

function encode_vals($vars)
{
    return YoyoHelpers::encode_vals($vars);
}

function addValue($value = '')
{
    if (! $value) {
        return '';
    }

    if (YoyoHelpers::test_json($value)) {
        return "='".$value."'";
    }

    return '="'.$value.'"';
}

function response($filename)
{
    $output = file_get_contents(__DIR__."/responses/$filename.html");

    return htmlformat($output);
}

function htmlformat($html)
{
    $html = preg_replace('!\s+!', ' ', $html);
    $html = preg_replace('/\>\s+\</m', '><', $html);

    return $html;
}
