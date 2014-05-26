<?php

use CQRSBlog\BlogEngine\Command\CreatePostCommand;
use CQRSBlog\BlogEngine\DomainModel\PostId;
use CQRSBlog\BlogEngine\Query\PostQuery;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

//Request::setTrustedProxies(array('127.0.0.1'));

$app->get('/', function () use ($app) {
    return $app['twig']->render('index.html', array());
})
->bind('homepage')
;

$app->get(
    '/post/create',
    function () use ($app) {
        $aPostCommand = new CreatePostCommand(
            'This is a test3',
            'Test3'
        );

        $app['command_bus']->handle($aPostCommand);

        return Response::create('Post created!');
    }
);

$app->get(
    '/post/show/{id}',
    function ($id) use ($app) {
        $postQuery = new PostQuery();
        $postQuery->id = $id;
        $post = $app['query_bus']->handle($postQuery);

        return $app['twig']->render('post.html.twig', ['post' => $post]);
    }
);

$app->error(function (\Exception $e, $code) use ($app) {
    if ($app['debug']) {
        return;
    }

    // 404.html, or 40x.html, or 4xx.html, or error.html
    $templates = array(
        'errors/'.$code.'.html',
        'errors/'.substr($code, 0, 2).'x.html',
        'errors/'.substr($code, 0, 1).'xx.html',
        'errors/default.html',
    );

    return new Response($app['twig']->resolveTemplate($templates)->render(array('code' => $code)), $code);
});
