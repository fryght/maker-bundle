<?php

/*
 * This file is part of the Symfony MakerBundle package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\MakerBundle\Doctrine;

use function strtolower;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\Util\ClassNameDetails;

/**
 * @internal
 */
final class EntityClassGenerator
{
    private $generator;

    public function __construct(Generator $generator)
    {
        $this->generator = $generator;
    }

    public function generateEntityClass(ClassNameDetails $entityClassDetails, bool $apiResource, bool $skipGeneratingRepository = false, bool $withPasswordUpgrade = false): string
    {
        if (false === $skipGeneratingRepository) {
            $this->generateRepositoryClass($entityClassDetails, $withPasswordUpgrade);
            $repoClassDetails = $this->getRepoClassNameDetails($entityClassDetails);
        }
        $entityPath = $this->generator->generateClass(
            $entityClassDetails->getFullName(),
            'doctrine/Entity.tpl.php',
            [
                'generate_repository' => ! $skipGeneratingRepository,
                'repository_full_class_name' => isset($repoClassDetails) ? $repoClassDetails->getFullName() : null,
                'api_resource' => $apiResource,
            ]
        );

        return $entityPath;
    }

    public function generateRepositoryClass(ClassNameDetails $entityClassDetails, bool $withPasswordUpgrade = false): string
    {
        $repoClassDetails = $this->getRepoClassNameDetails($entityClassDetails);

        $entityAlias = strtolower($entityClassDetails->getShortName()[0]);
        $repositoryPath = $this->generator->generateClass(
            $repoClassDetails->getFullName(),
            'doctrine/Repository.tpl.php',
            [
                'entity_full_class_name' => $entityClassDetails->getFullName(),
                'entity_class_name' => $entityClassDetails->getShortName(),
                'entity_alias' => $entityAlias,
                'with_password_upgrade' => $withPasswordUpgrade,
            ]
        );

        return $repositoryPath;
    }

    private function getRepoClassNameDetails(ClassNameDetails $entityClassDetails): ClassNameDetails
    {
        $repoClassDetails = $this->generator->createClassNameDetails(
            $entityClassDetails->getRelativeName(),
            'Repository\\',
            'Repository'
        );

        return $repoClassDetails;
    }
}
