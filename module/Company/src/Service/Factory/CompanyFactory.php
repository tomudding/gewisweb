<?php

declare(strict_types=1);

namespace Company\Service\Factory;

use Application\Service\FileStorage as FileStorageService;
use Company\Form\Job as JobForm;
use Company\Form\JobCategory as JobCategoryForm;
use Company\Form\JobLabel as JobLabelForm;
use Company\Mapper\Category as JobCategoryMapper;
use Company\Mapper\Company as CompanyMapper;
use Company\Mapper\Job as JobMapper;
use Company\Mapper\JobUpdate as JobUpdateMapper;
use Company\Mapper\Label as JobLabelMapper;
use Company\Service\AclService;
use Company\Service\Company as CompanyService;
use Laminas\Mvc\I18n\Translator as MvcTranslator;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use User\Service\User as UserService;

class CompanyFactory implements FactoryInterface
{
    /**
     * @param string $requestedName
     */
    public function __invoke(
        ContainerInterface $container,
        $requestedName,
        ?array $options = null,
    ): CompanyService {
        return new CompanyService(
            $container->get(AclService::class),
            $container->get(MvcTranslator::class),
            $container->get(FileStorageService::class),
            $container->get(CompanyMapper::class),
            $container->get('company_mapper_package'),
            $container->get('company_mapper_bannerpackage'),
            $container->get('company_mapper_featuredpackage'),
            $container->get(JobMapper::class),
            $container->get(JobUpdateMapper::class),
            $container->get(JobCategoryMapper::class),
            $container->get(JobLabelMapper::class),
            $container->get('company_admin_company_form'),
            $container->get('company_admin_package_form'),
            $container->get('company_admin_bannerpackage_form'),
            $container->get('company_admin_featuredpackage_form'),
            $container->get(JobForm::class),
            $container->get(JobCategoryForm::class),
            $container->get(JobLabelForm::class),
            $container->get(UserService::class),
            $container->get('config')['storage']
        );
    }
}
