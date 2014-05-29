<?php

use CQRSBlog\BlogEngine\Command\CreatePostCommand;
use CQRSBlog\BlogEngine\Command\PublishPostCommand;
use CQRSBlog\BlogEngine\Command\UpdatePostCommand;
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
    '/post/new',
    function () use ($app)
    {
        $aPostCommand = new CreatePostCommand(
            'Write a blog post',
            'The Post title'
        );

        $form = $app['form.factory']->createBuilder('form', $aPostCommand)
            ->add('title', 'text')
            ->add('content', 'text')
            ->add('save', 'submit')
            ->getForm()
        ;

        return $app['twig']->render('new_post.html.twig', ['form' => $form->createView()]);
    }
);

$app
    ->post(
        '/post/create',
        function (Request $request) use ($app)
        {
            $data = [
                'title'     => 'The Post title',
                'content'   => 'Write a blog post'
            ];

            $form = $app['form.factory']->createBuilder('form', $data)
                ->add('title', 'text')
                ->add('content', 'text')
                ->add('save', 'submit')
                ->getForm()
            ;

            $form->handleRequest($request);

            if ($form->isValid()) {
                $data = $form->getData();

                $aPostCommand = new CreatePostCommand(
                    $data['content'],
                    $data['title']
                );

                $app['command_bus']->handle($aPostCommand);

                return Response::create('Post created!');
            }

            return $app['twig']->render('new_post.html.twig', ['form' => $form->createView()]);
        }
    )
    ->bind('create_post')
;

$app->get(
    '/post/show/{id}',
    function ($id) use ($app)
    {
        $postQuery = new PostQuery($id);
        $post = $app['query_bus']->handle($postQuery);

        return $app['twig']->render('post.html.twig', ['post' => $post]);
    }
);

$app
    ->get(
        '/post/publish/{id}',
        function ($id) use ($app)
        {
            $app['command_bus']->handle(new PublishPostCommand($id));
            return Response::create('Post published!');
        }
    )
;

$app
    ->get(
        '/post/update/{id}',
        function ($id) use ($app)
        {
            $title = 'This is an update test for the title';
            $content = 'This is an update test for the content';

            $app['command_bus']->handle(new UpdatePostCommand($id, $content, $title));
            return Response::create('Post was updated!');
        }
    )
;

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
