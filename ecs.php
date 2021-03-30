<?php
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\EasyCodingStandard\ValueObject\Option;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->parameters()->set(Option::PATHS, [
        'src', 'tests'
    ]);

    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::SETS, [SetList::CLEAN_CODE, SetList::PSR_12]);
};
