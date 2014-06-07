<?php

use Braincrafted\Bundle\BootstrapBundle\Twig\BootstrapBadgeExtension;
use Braincrafted\Bundle\BootstrapBundle\Twig\BootstrapFormExtension;
use Braincrafted\Bundle\BootstrapBundle\Twig\BootstrapIconExtension;
use Braincrafted\Bundle\BootstrapBundle\Twig\BootstrapLabelExtension;
use CQRSBlog\BlogEngine\Query\AllPostsQueryHandler;
use CQRSBlog\Common\ServiceBus\CommandBus;
use CQRSBlog\BlogEngine\Command\CommentHandler;
use CQRSBlog\BlogEngine\Command\CreatePostHandler;
use CQRSBlog\BlogEngine\Command\PublishPostHandler;
use CQRSBlog\BlogEngine\Command\UpdatePostHandler;
use CQRSBlog\BlogEngine\Infrastructure\Persistence\EventStore\PostRepository;
use CQRSBlog\BlogEngine\Infrastructure\Persistence\EventStore\RedisEventStore;
use CQRSBlog\BlogEngine\Infrastructure\Persistence\Redis\PostViewRepository;
use CQRSBlog\BlogEngine\Infrastructure\Projection\Redis\PostProjection;
use CQRSBlog\BlogEngine\Query\PostQueryHandler;
use CQRSBlog\Common\ServiceBus\QueryBus;

use Silex\Application;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;

$app = new Application();
$app->register(new UrlGeneratorServiceProvider());
$app->register(new ValidatorServiceProvider());
$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new ServiceControllerServiceProvider());
$app->register(new FormServiceProvider());
$app->register(new Silex\Provider\TranslationServiceProvider(), ['locale_fallbacks' => array('en')]);
$app->register(new Silex\Provider\TwigServiceProvider(), ['twig.form.templates' => ['bootstrap.html.twig']]);
$app->register(new Predis\Silex\PredisServiceProvider(), [
    'predis.parameters' => 'tcp://127.0.0.1:6379',
    'predis.options'    => ['profile' => '2.2'],
]);

$app['twig'] = $app->share($app->extend('twig', function($twig) {
    $twig->addExtension(new BootstrapIconExtension);
    $twig->addExtension(new BootstrapLabelExtension);
    $twig->addExtension(new BootstrapBadgeExtension);
    $twig->addExtension(new BootstrapFormExtension);

    return $twig;
}));

$app['serializer'] = $app->share(function($app) {
    return
        JMS\Serializer\SerializerBuilder::create()
            ->setCacheDir(__DIR__ . '/../var/cache/serializer')
            ->setDebug($app['debug'])
            ->addMetadataDir(__DIR__ . '/../var/mapping/serializer')
        ->build()
    ;
});

$app['post_projection'] = $app->share(function($app) {
    return new PostProjection($app['predis']);
});

$app['event_store'] = $app->share(function($app) {
    return new RedisEventStore($app['predis'], $app['serializer']);
});

$app['post_repository'] = $app->share(function($app) {
    return new PostRepository($app['event_store'], $app['post_projection']);
});

$app['post_view_repository'] = $app->share(function($app) {
    return new PostViewRepository($app['predis']);
});

$app['command_bus'] = $app->share(function($app) {
    $commandBus = new CommandBus();
    $commandBus->register(new CreatePostHandler($app['post_repository']));
    $commandBus->register(new PublishPostHandler($app['post_repository']));
    $commandBus->register(new UpdatePostHandler($app['post_repository']));
    $commandBus->register(new CommentHandler($app['post_repository']));
    return $commandBus;
});

$app['query_bus'] = $app->share(function($app) {
    $commandBus = new QueryBus();
    $commandBus->register(new PostQueryHandler($app['post_view_repository']));
    $commandBus->register(new AllPostsQueryHandler($app['post_view_repository']));
    return $commandBus;
});

return $app;
