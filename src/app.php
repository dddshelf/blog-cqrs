<?php

use CQRSBlog\BlogEngine\Command\CommandBus;
use CQRSBlog\BlogEngine\Command\Handler\CreatePostHandler;
use CQRSBlog\BlogEngine\Infrastructure\Persistence\EventStore\MongoDbEventStore;
use CQRSBlog\BlogEngine\Infrastructure\Persistence\EventStore\PostRepository;
use CQRSBlog\BlogEngine\Infrastructure\Serialization\SymfonySerializer\Normalizer\PostIdNormalizer;
use CQRSBlog\BlogEngine\Query\Handler\PostQueryHandler;
use CQRSBlog\BlogEngine\Query\QueryBus;
use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;

$app = new Application();
$app->register(new UrlGeneratorServiceProvider());
$app->register(new ValidatorServiceProvider());
$app->register(new ServiceControllerServiceProvider());
$app->register(new Silex\Provider\SerializerServiceProvider());
$app->register(new TwigServiceProvider());
$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
    // add custom globals, filters, tags, ...

    return $twig;
}));

$app['serializer.normalizers'] = $app->share($app->extend('serializer.normalizers', function(array $normalizers, $app) {
    $normalizers[] = new PostIdNormalizer();

    return $normalizers;
}));

$app['mongodb'] = $app->share(function($app) {
    return new MongoClient();
});

$app['mongodb.posts_projection'] = $app->share(function($app) {
    return new \CQRSBlog\BlogEngine\Infrastructure\Projection\MongoDb\PostsProjection(
        $app['mongodb']->blog_cqrs->posts
    );
});

$app['event_store'] = $app->share(function($app) {
    return new MongoDbEventStore(
        $app['mongodb']->blog_cqrs->events,
        $app['serializer'],
        $app['mongodb.posts_projection']
    );
});

$app['event_store.post_repository'] = $app->share(function($app) {
    return new PostRepository($app['event_store']);
});

$app['mongodb.post_repository'] = $app->share(function($app) {
    return new \CQRSBlog\BlogEngine\Infrastructure\Persistence\MongoDb\PostRepository(
        $app['mongodb']->blog_cqrs->posts
    );
});

$app['command_bus'] = $app->share(function($app) {
    $commandBus = new CommandBus();
    $commandBus->register(new CreatePostHandler($app['event_store.post_repository']));
    return $commandBus;
});

$app['query_bus'] = $app->share(function($app) {
    $commandBus = new QueryBus();
    $commandBus->register(new PostQueryHandler($app['mongodb.post_repository']));
    return $commandBus;
});

return $app;
