<?php


namespace Cmfcmf\Module\MediaModule\Listener;

use Cmfcmf\Module\MediaModule\Exception\PasswordRequiredException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;

class PasswordRequiredListener implements EventSubscriberInterface
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 32]
        ];
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        if (!$exception instanceof PasswordRequiredException) {
            return null;
        }

        $event->setResponse(
            new RedirectResponse(
                $this->router->generate(
                    'cmfcmfmediamodule_collection_password',
                    [
                        'id' => $exception->getCollection()->getId(),
                        'permLevel' => $exception->getPermissionLevel()
                    ],
                    RouterInterface::ABSOLUTE_URL
                )
            )
        );
    }
}
