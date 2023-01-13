<?php

declare(strict_types=1);

namespace K911\Swoole\Bridge\Symfony\HttpFoundation\Session;

use K911\Swoole\Server\Session\StorageInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;

/**
 * Sets the session in the request.
 */
final class SetSessionCookieEventListener implements EventSubscriberInterface
{
    use SessionCookieEventListenerTrait;

    private SessionStorageInterface $sessionStorage;

    public function __construct(
        RequestStack $requestStack,
        SessionStorageInterface $sessionStorage,
        StorageInterface $swooleStorage,
        array $sessionOptions = []
    ) {
        $this->requestStack = $requestStack;
        $this->sessionStorage = $sessionStorage;
        $this->swooleStorage = $swooleStorage;
        $this->sessionCookieParameters = $this->mergeCookieParams($sessionOptions);
    }

    public function onFinishRequest(FinishRequestEvent $event): void
    {
        if (!$event->isMainRequest() || !$this->isSessionRelated($event)) {
            return;
        }

        if ($this->sessionStorage instanceof SwooleSessionStorage && $this->sessionStorage->isStarted()) {
            $this->sessionStorage->reset();
        }

        $this->swooleStorage->garbageCollect();
    }
}
