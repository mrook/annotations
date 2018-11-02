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
use Prooph\Annotation\AnnotatedCommandTargetResolver;
use Prooph\Common\Messaging\Command;
use Prooph\EventStore\Exception\InvalidArgumentException;

class AnnotatedCommandTargetResolverTest extends TestCase
{
    const TARGET_ID = 'targetId';

    public function testShouldResolveMethod()
    {
        $resolver = new AnnotatedCommandTargetResolver();

        static::assertEquals(static::TARGET_ID, $resolver->resolveTarget(new class() extends Command {
            /**
             * @Prooph\Annotation\TargetAggregateIdentifier
             */
            public function getId()
            {
                return AnnotatedCommandTargetResolverTest::TARGET_ID;
            }

            public function payload(): array
            {
                return [];
            }

            protected function setPayload(array $payload): void
            {
            }
        }));
    }

    public function testShouldResolveProperty()
    {
        $resolver = new AnnotatedCommandTargetResolver();

        static::assertEquals(static::TARGET_ID, $resolver->resolveTarget(new class() extends Command {
            /**
             * @Prooph\Annotation\TargetAggregateIdentifier
             */
            public $id = AnnotatedCommandTargetResolverTest::TARGET_ID;

            public function payload(): array
            {
                return [];
            }

            protected function setPayload(array $payload): void
            {
            }
        }));
    }

    public function testCommandWithoutAnnotationsShouldNotResolve()
    {
        $resolver = new AnnotatedCommandTargetResolver();

        static::expectException(InvalidArgumentException::class);

        static::assertEquals(static::TARGET_ID, $resolver->resolveTarget(new class() extends Command {
            public function payload(): array
            {
                return [];
            }

            protected function setPayload(array $payload): void
            {
            }
        }));
    }
}
