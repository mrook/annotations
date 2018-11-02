<?php

/**
 * This file is part of prooph/annotations.
 * (c) 2017-2018 Michiel Rook <mrook@php.net>
 * (c) 2017-2018 prooph software GmbH <contact@prooph.de>
 * (c) 2017-2018 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ProophTest\Annotation;

use PHPUnit\Framework\TestCase;
use Prooph\Annotation\AnnotatedEventRouter;
use Prooph\Annotation\AnnotatedHandlerInvoker;
use Prooph\Common\Event\DefaultActionEvent;
use Prooph\Common\Event\ListenerHandler;
use Prooph\Common\Messaging\Message;
use Prooph\ServiceBus\EventBus;
use Prooph\ServiceBus\MessageBus;

class AnnotatedEventRouterTest extends TestCase
{
    public function testShouldFindHandlerForEventMessage()
    {
        $projector = new class() {
            /**
             * @Prooph\Annotation\EventHandler
             * @param Message $message
             */
            public function onEvent(Message $message)
            {
            }
        };

        $eventRouter = new AnnotatedEventRouter($projector);

        $event = new DefaultActionEvent('');
        $event->setParam(MessageBus::EVENT_PARAM_MESSAGE_NAME, Message::class);
        $eventRouter->onRouteMessage($event);
        static::assertInstanceOf(AnnotatedHandlerInvoker::class, $event->getParam(EventBus::EVENT_PARAM_EVENT_LISTENERS)[0]);
    }

    public function testShouldNotFindHandlerForUnknownEventMessage()
    {
        $eventRouter = new AnnotatedEventRouter(null);

        $event = new DefaultActionEvent('');
        $event->setParam(MessageBus::EVENT_PARAM_MESSAGE_NAME, Message::class);
        $eventRouter->onRouteMessage($event);
        static::assertEmpty($event->getParam(EventBus::EVENT_PARAM_EVENT_LISTENERS));
    }

    public function testShouldNotFindHandlerForEmptyEventMessage()
    {
        $eventRouter = new AnnotatedEventRouter(null);

        $event = new DefaultActionEvent('');
        $event->setParam(MessageBus::EVENT_PARAM_MESSAGE_NAME, '');
        $eventRouter->onRouteMessage($event);
        static::assertEmpty($event->getParam(EventBus::EVENT_PARAM_EVENT_LISTENERS));
    }

    public function testShouldAttachToEmitter()
    {
        $emitter = $this->getMockBuilder(MessageBus::class)->getMock();

        $emitter->expects(static::once())
            ->method('attach')
            ->willReturn($this->getMockBuilder(ListenerHandler::class)->getMock());

        $eventRouter = new AnnotatedEventRouter(null);

        $eventRouter->attachToMessageBus($emitter);
    }
}
