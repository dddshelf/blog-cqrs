<?php

use CQRSBlog\BlogEngine\Command\CommentCommand;
use CQRSBlog\BlogEngine\Command\CreatePostCommand;
use CQRSBlog\BlogEngine\Command\PublishPostCommand;
use CQRSBlog\BlogEngine\Command\UpdatePostCommand;
use CQRSBlog\BlogEngine\Query\AllPostsQuery;
use CQRSBlog\BlogEngine\Query\PostQuery;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app
    ->get(
        '/',
        function () use ($app)
        {
            $allPostsQuery = new AllPostsQuery();
            $posts = $app['query_bus']->handle($allPostsQuery);

            return $app['twig']->render('index.html.twig', array('posts' => $posts));
        }
    )
    ->bind('homepage')
;

$app
    ->get(
        '/post/new',
        function () use ($app)
        {
            $aPostCommand = new CreatePostCommand(
                'Write a blog post',
                'The Post title'
            );

            $form = $app['form.factory']->createBuilder('form', $aPostCommand)
                ->add('title', 'text')
                ->add('content', 'textarea')
                ->add('save', 'submit')
                ->getForm()
            ;

            return $app['twig']->render('new_post.html.twig', ['form' => $form->createView()]);
        }
    )
    ->bind('new_post')
;

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
                ->add('content', 'textarea')
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

                $app['session']->getFlashBag()->add('notices', 'Post was created!');
                return $app->redirect($app['url_generator']->generate('homepage'));
            }

            return $app['twig']->render('new_post.html.twig', ['form' => $form->createView()]);
        }
    )
    ->bind('create_post')
;

$app
    ->get(
        '/post/show/{id}',
        function ($id) use ($app)
        {
            $postQuery = new PostQuery($id);
            $post = $app['query_bus']->handle($postQuery);

            $form = $app['form.factory']->createBuilder('form', ['comment' => 'Write a comment'])
                ->add('comment', 'textarea')
                ->add('save', 'submit')
                ->getForm()
            ;

            return $app['twig']->render('post.html.twig', ['post' => $post, 'form' => $form->createView()]);
        }
    )
    ->bind('post')
;

$app
    ->get(
        '/post/publish/{id}',
        function ($id) use ($app)
        {
            $app['command_bus']->handle(new PublishPostCommand($id));

            $app['session']->getFlashBag()->add('notices', 'Post was published!');
            return $app->redirect($app['url_generator']->generate('post', ['id' => $id]));
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

            $app['session']->getFlashBag()->add('notices', 'Post was updated!');
            return $app->redirect($app['url_generator']->generate('post', ['id' => $id]));
        }
    )
;

$app
    ->post(
        '/post/{id}/comment',
        function ($id, Request $request) use ($app)
        {
            $data = [
                'comment' => 'Write a comment'
            ];

            $form = $app['form.factory']->createBuilder('form', $data)
                ->add('comment', 'textarea')
                ->add('save', 'submit')
                ->getForm()
            ;

            $form->handleRequest($request);

            if ($form->isValid()) {
                $data = $form->getData();

                $aCommentCommand = new CommentCommand(
                    $id,
                    $data['comment']
                );

                $app['command_bus']->handle($aCommentCommand);

                $app['session']->getFlashBag()->add('notices', 'Comment was added!');
                return $app->redirect($app['url_generator']->generate('post', ['id' => $id]));
            }

            $postQuery = new PostQuery($id);
            $post = $app['query_bus']->handle($postQuery);

            return $app['twig']->render('post.html.twig', ['post' => $post, 'form' => $form->createView()]);
        }
    )
    ->bind('comment')
;

$app->error(function (Exception $e, $code) use ($app) {
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
