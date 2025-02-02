<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory;

use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * FormLoginFactory creates services for form login authentication.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 *
 * @internal
 */
class FormLoginFactory extends AbstractFactory
{
    public const PRIORITY = -30;
    protected string $baseAuthenticatorId = 'security.authenticator.form_login';
    protected string $authenticatorId = '';

    public function __construct()
    {
        $this->addOption('username_parameter', '_username');
        $this->addOption('password_parameter', '_password');
        $this->addOption('csrf_parameter', '_csrf_token');
        $this->addOption('csrf_token_id', 'authenticate');
        $this->addOption('enable_csrf', false);
        $this->addOption('post_only', true);
        $this->addOption('form_only', false);
    }

    public function getPriority(): int
    {
        return self::PRIORITY;
    }

    public function getKey(): string
    {
        return 'form-login';
    }

    public function createAuthenticator(ContainerBuilder $container, string $firewallName, array $config, string $userProviderId): string
    {
        $this->setAuthenticatorId($firewallName);
        $options = array_intersect_key($config, $this->options);
        $authenticator = $container
            ->setDefinition($this->authenticatorId, new ChildDefinition($this->baseAuthenticatorId))
            ->replaceArgument(1, new Reference($userProviderId))
            ->replaceArgument(2, new Reference($this->createAuthenticationSuccessHandler($container, $firewallName, $config)))
            ->replaceArgument(3, new Reference($this->createAuthenticationFailureHandler($container, $firewallName, $config)))
            ->replaceArgument(4, $options);

        if ($options['use_forward'] ?? false) {
            $authenticator->addMethodCall('setHttpKernel', [new Reference('http_kernel')]);
        }

        return $this->authenticatorId;
    }

    protected function setAuthenticatorId(string $firewallName): void
    {
        $this->authenticatorId = $this->baseAuthenticatorId.'.'.$firewallName;
    }
}
