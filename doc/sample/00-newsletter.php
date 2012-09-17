<?php
use Doctrine\Common\Annotations\AnnotationRegistry;
use Ray\Di\Di\Inject;
use Ray\Di\Di\Named;
use Ray\Di\AbstractModule;
use Ray\Di\Injector;

$loader = require dirname(dirname(__DIR__)) . '/vendor/autoload.php';
AnnotationRegistry::registerLoader([$loader, "loadClass"]);

abstract class MailerInterface
{
    private $transport;

    /**
     * @Inject
     * @Named("transport_type")
     */
    public function __construct($transport)
    {
        $this->transport = $transport;
    }
}

class Mailer extends MailerInterface {}

class NewsletterManager
{
    public $mailer;

    /**
     * @Inject
     */
    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }
}

class NewsletterModule extends AbstractModule
{
    protected function configure()
    {
        $this->bind()->annotatedWith('transport_type')->toInstance('sendmail');
        $this->bind('MailerInterface')->to('Mailer');
    }
}

$useApcCache = false;
$di = Injector::create([new NewsletterModule], $useApcCache);

$newsletterManager = $di->getInstance('NewsletterManager');
var_dump($newsletterManager);

// display result
$works = ($newsletterManager->mailer instanceof MailerInterface);
echo (($works) ? 'It works!' : 'It DOES NOT work!');
echo "\n";
