<?php

use CQRSBlog\BlogEngine\Infrastructure\Persistence\EventStore\MongoDbEventStore;
use CQRSBlog\BlogEngine\Infrastructure\Persistence\EventStore\PDOEventStore;
use CQRSBlog\BlogEngine\Query\AllPostsQueryHandler;
use CQRSBlog\Common\ServiceBus\CommandBus;
use CQRSBlog\BlogEngine\Command\CommentHandler;
use CQRSBlog\BlogEngine\Command\CreatePostHandler;
use CQRSBlog\BlogEngine\Command\PublishPostHandler;
use CQRSBlog\BlogEngine\Command\UpdatePostHandler;
use CQRSBlog\BlogEngine\Infrastructure\Persistence\EventStore\PostRepository;
use CQRSBlog\BlogEngine\Infrastructure\Persistence\EventStore\RedisEventStore;
use CQRSBlog\BlogEngine\Infrastructure\Persistence\Redis\PostViewRepository as RedisPostViewRepository;
use CQRSBlog\BlogEngine\Infrastructure\Persistence\MongoDb\PostViewRepository as MongoDbPostViewRepository;
use CQRSBlog\BlogEngine\Infrastructure\Persistence\PDO\PostViewRepository as PDOPostViewRepository;
use CQRSBlog\BlogEngine\Infrastructure\Projection\Redis\PostProjection as RedisPostProjection;
use CQRSBlog\BlogEngine\Infrastructure\Projection\MongoDb\PostProjection as MongoDbPostProjection;
use CQRSBlog\BlogEngine\Infrastructure\Projection\PDO\PostProjection as PDOPostProjection;
use CQRSBlog\BlogEngine\Query\PostQueryHandler;
use CQRSBlog\Common\ServiceBus\QueryBus;

use Silex\Application;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Braincrafted\Bundle\BootstrapBundle\Twig\BootstrapBadgeExtension;
use Braincrafted\Bundle\BootstrapBundle\Twig\BootstrapFormExtension;
use Braincrafted\Bundle\BootstrapBundle\Twig\BootstrapIconExtension;
use Braincrafted\Bundle\BootstrapBundle\Twig\BootstrapLabelExtension;
use CSanquer\Silex\PdoServiceProvider\Provider\PDOServiceProvider;
use Twig\Environment;

$app = new Application();
$app->register(new UrlGeneratorServiceProvider());
$app->register(new ValidatorServiceProvider());
$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new ServiceControllerServiceProvider());
$app->register(new FormServiceProvider());
$app->register(new Silex\Provider\TranslationServiceProvider(), ['locale_fallbacks' => array('en')]);
$app->register(new Silex\Provider\TwigServiceProvider(), ['twig.form.templates' => ['bootstrap.html.twig']]);

$app->register(new Predis\Silex\PredisServiceProvider(), [
    'predis.parameters' => $dependedConf['redis.connect'],
    'predis.options'    => ['profile' => '2.2'],
]);

$app->register(new PdoServiceProvider('pdo'), [
    'pdo.server'   => [
        'driver'   => 'mysql',
        'host'     => $dependedConf['mysql.host'],
        'dbname'   => 'mydddblog',
        'port'     => 3306,
        'user'     => 'mydddblog',
        'password' => 'myDddbl0g',
    ],
    'pdo.options' => [
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'"
    ],
    'pdo.attributes' => array(
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ),
]);

$app['mongo'] = $app::share(static function() {
    return new MongoClient();
});

$app['twig'] = $app::share($app->extend('twig', static function(Environment $twig) {
    $twig->addExtension(new BootstrapIconExtension(''));
    $twig->addExtension(new BootstrapLabelExtension);
    $twig->addExtension(new BootstrapBadgeExtension);
    $twig->addExtension(new BootstrapFormExtension);

    return $twig;
}));

$app['serializer'] = $app::share(static function($app) {
    return
        JMS\Serializer\SerializerBuilder::create()
            ->setCacheDir(__DIR__ . '/../var/cache/serializer')
            ->setDebug($app['debug'])
            ->addMetadataDir(__DIR__ . '/../resources/mapping/serializer')
        ->build()
    ;
});


/*******************************************
 * EVENT STORE CONFIGURATION
 *******************************************/

$app['event_store.redis'] = $app::share(static function($app) {
    return new RedisEventStore($app['predis'], $app['serializer']);
});

$app['event_store.mongodb'] = $app::share(static function($app) {
    return new MongoDbEventStore($app['mongo']->dddblog->events, $app['serializer']);
});

$app['event_store.pdo'] = $app::share(static function($app) {
    return new PDOEventStore($app['pdo'], $app['serializer']);
});

$app['event_store'] = $app::share(static function($app) {
    return $app['event_store.redis'];
});

/*******************************************
 * EVENT STORE REPOSITORIES
 *******************************************/

$app['post_repository'] = $app::share(static function($app) {
    return new PostRepository($app['event_store'], $app['post_projection']);
});

/*******************************************
 * PERSISTENCE CONFIGURATION
 *******************************************/

$app['post_view_repository.redis'] = $app::share(static function($app) {
    return new RedisPostViewRepository($app['predis'], $app['serializer']);
});

$app['post_view_repository.mongodb'] = $app::share(static function($app) {
    return new MongoDbPostViewRepository($app['mongo']->dddblog->posts);
});

$app['post_view_repository.pdo'] = $app::share(static function($app) {
    return new PDOPostViewRepository($app['pdo']);
});

$app['post_view_repository'] = $app::share(static function($app) {
    return $app['post_view_repository.pdo'];
});

/*******************************************
 * READ MODEL PROJECTIONS
 *******************************************/

$app['post_projection.redis'] = $app::share(static function($app) {
    return new RedisPostProjection($app['predis'], $app['serializer']);
});

$app['post_projection.mongodb'] = $app::share(static function($app) {
    return new MongoDbPostProjection($app['mongo']->dddblog->posts);
});

$app['post_projection.pdo'] = $app::share(static function($app) {
    return new PDOPostProjection($app['pdo']);
});

$app['post_projection'] = $app::share(static function($app) {
    return $app['post_projection.pdo'];
});

/*******************************************
 * COMMAND BUS
 *******************************************/

$app['command_bus'] = $app::share(static function($app) {
    $commandBus = new CommandBus();
    $commandBus->register(new CreatePostHandler($app['post_repository']));
    $commandBus->register(new PublishPostHandler($app['post_repository']));
    $commandBus->register(new UpdatePostHandler($app['post_repository']));
    $commandBus->register(new CommentHandler($app['post_repository']));

    return $commandBus;
});

/*******************************************
 * QUERY BUS
 *******************************************/

$app['query_bus'] = $app::share(static function($app) {
    $commandBus = new QueryBus();
    $commandBus->register(new PostQueryHandler($app['post_view_repository']));
    $commandBus->register(new AllPostsQueryHandler($app['post_view_repository']));

    return $commandBus;
});

return $app;
