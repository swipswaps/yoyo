<?php

use Clickfwd\Yoyo\ComponentManager;
use Clickfwd\Yoyo\Exceptions\ComponentMethodNotFound;
use Clickfwd\Yoyo\Exceptions\ComponentNotFound;
use function Tests\encode_vals;
use function Tests\htmlformat;
use function Tests\hxattr;
use function Tests\initYoyo;
use function Tests\mockYoyoGetRequest;
use function Tests\mockYoyoPostRequest;
use function Tests\render;
use function Tests\resetYoyoRequest;
use function Tests\response;
use function Tests\update;
use function Tests\yoprefix_value;
use function Tests\yoyo_update;

uses()->group('unit-dynamic');

beforeAll(function () {
    $yoyo = initYoyo(['Counter', 'ComputedProperty', 'ComputedPropertyCache']);
});

test('component class not found', function () {
    render('random');
})->throws(ComponentNotFound::class);

test('counter component render', function () {
    $vars = encode_vals([
        yoprefix_value('id') => 'counter',
        'count' => 0,
    ]);

    expect(render('counter'))->toContain(hxattr('vals', $vars));
});

test('counter component with default value', function () {
    $vars = encode_vals([yoprefix_value('id')=>'counter', 'count'=>3]);

    expect(render('counter', ['count'=>3]))->toContain(hxattr('vals', $vars));
});

test('counter component public method', function () {
    expect(update('counter', 'increment'))->toContain('The count is now 1');
});

test('counter component increment', function () {
    $vars = encode_vals([yoprefix_value('id')=>'counter', 'count'=>1]);

    expect(update('counter', 'increment'))->toContain(hxattr('vals', $vars));
});

test('counter component decrement', function () {
    $vars = encode_vals([yoprefix_value('id')=>'counter', 'count'=>-1]);

    expect(update('counter', 'decrement'))->toContain(hxattr('vals', $vars));
});

test('component method not found', function () {
    update('counter', 'random');
})->throws(ComponentMethodNotFound::class);

test('component computed property', function () {
    $output = render('computed-property');
    expect(htmlformat($output))->toEqual(response('computed-property'));
});

test('component computed property cache', function () {
    $output = render('computed-property-cache');
    expect(htmlformat($output))->toEqual(response('computed-property-cache'));
});

test('can set component view data', function () {
    require_once __DIR__.'/../app/Yoyo/SetViewData.php';
    expect(render('set-view-data'))->toContain('bar-baz');
});


test('action parameters passed to component method arguments', function () {
    require_once __DIR__.'/../app/Yoyo/ActionArguments.php';

    mockYoyoGetRequest('http://example.com/', 'action-arguments/someAction', '', [
        'actionArgs' => [1,'foo'],
    ]);

    $output = yoyo_update();

    resetYoyoRequest();

    expect(htmlformat($output))->toEqual(response('action-arguments'));
});

test('Null properties not added as vars to component root', function () {
    require_once __DIR__.'/../app/Yoyo/PostRequestVars.php';

    $vars = encode_vals([yoprefix_value('id') => 'post-request-vars']);

    expect(render('post-request-vars'))->toContain(hxattr('vals', $vars));
})->group('component-root-vars');

test('posted vars are not added to component root', function () {
    require_once __DIR__.'/../app/Yoyo/PostRequestVars.php';

    $vars = encode_vals([yoprefix_value('id') => 'post-request-vars']);

    mockYoyoPostRequest('http://example.com/', 'post-request-vars/save', '', [
        'foo' => 'bar',
    ]);

    expect(yoyo_update())->toContain(hxattr('vals', $vars));

    resetYoyoRequest();
})->group('component-root-vars');

test('registered dynamic component is loaded', function () {
    require_once __DIR__."/../app/Yoyo/registered.php";
    ComponentManager::registerComponent('registered-test', \Tests\App\Yoyo\Registered::class);
    expect(render('registered-test'))->toContain('id="registered"');
});
