<?php

declare(strict_types=1);

namespace Company;

use Company\Form\Company as CompanyForm;
use Company\Form\Job as JobForm;
use Company\Form\JobCategory as JobCategoryForm;
use Company\Form\JobLabel as JobLabelForm;
use Company\Form\JobsTransfer as JobsTransferForm;
use Company\Form\Package as PackageForm;
use Company\Mapper\BannerPackage as BannerPackageMapper;
use Company\Mapper\Category as CategoryMapper;
use Company\Mapper\Company as CompanyMapper;
use Company\Mapper\FeaturedPackage as FeaturedPackageMapper;
use Company\Mapper\Job as JobMapper;
use Company\Mapper\JobUpdate as JobUpdateMapper;
use Company\Mapper\Label as LabelMapper;
use Company\Mapper\Package as PackageMapper;
use Company\Service\AclService;
use Company\Service\Company as CompanyService;
use Company\Service\CompanyQuery as CompanyQueryService;
use Company\Service\Factory\CompanyFactory as CompanyServiceFactory;
use Company\Service\Factory\CompanyQueryFactory;
use Laminas\Mvc\I18n\Translator as MvcTranslator;
use Psr\Container\ContainerInterface;
use User\Authorization\AclServiceFactory;

class Module
{
    /**
     * Get the configuration for this module.
     *
     * @return array Module configuration
     */
    public function getConfig(): array
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    /**
     * Get service configuration.
     *
     * @return array Service configuration
     */
    public function getServiceConfig(): array
    {
        return [
            'factories' => [
                // Services
                AclService::class => AclServiceFactory::class,
                CompanyService::class => CompanyServiceFactory::class,
                CompanyQueryService::class => CompanyQueryFactory::class,
                // Mappers
                'company_mapper_company' => static function (ContainerInterface $container) {
                    return new CompanyMapper(
                        $container->get('doctrine.entitymanager.orm_default'),
                    );
                },
                'company_mapper_job' => static function (ContainerInterface $container) {
                    return new JobMapper(
                        $container->get('doctrine.entitymanager.orm_default'),
                    );
                },
                'company_mapper_job_update' => static function (ContainerInterface $container) {
                    return new JobUpdateMapper(
                        $container->get('doctrine.entitymanager.orm_default'),
                    );
                },
                'company_mapper_package' => static function (ContainerInterface $container) {
                    return new PackageMapper(
                        $container->get('doctrine.entitymanager.orm_default'),
                    );
                },
                'company_mapper_featuredpackage' => static function (ContainerInterface $container) {
                    return new FeaturedPackageMapper(
                        $container->get('doctrine.entitymanager.orm_default'),
                    );
                },
                'company_mapper_jobcategory' => static function (ContainerInterface $container) {
                    return new CategoryMapper(
                        $container->get('doctrine.entitymanager.orm_default'),
                    );
                },
                'company_mapper_joblabel' => static function (ContainerInterface $container) {
                    return new LabelMapper(
                        $container->get('doctrine.entitymanager.orm_default'),
                    );
                },
                'company_mapper_bannerpackage' => static function (ContainerInterface $container) {
                    return new BannerPackageMapper(
                        $container->get('doctrine.entitymanager.orm_default'),
                    );
                },
                // Forms
                'company_admin_package_form' => static function (ContainerInterface $container) {
                    return new PackageForm(
                        $container->get(MvcTranslator::class),
                        'job',
                    );
                },
                'company_admin_featuredpackage_form' => static function (ContainerInterface $container) {
                    return new PackageForm(
                        $container->get(MvcTranslator::class),
                        'featured',
                    );
                },
                'company_admin_jobcategory_form' => static function (ContainerInterface $container) {
                    return new JobCategoryForm(
                        $container->get('company_mapper_jobcategory'),
                        $container->get(MvcTranslator::class),
                    );
                },
                'company_admin_joblabel_form' => static function (ContainerInterface $container) {
                    return new JobLabelForm(
                        $container->get(MvcTranslator::class),
                    );
                },
                'company_admin_bannerpackage_form' => static function (ContainerInterface $container) {
                    return new PackageForm(
                        $container->get(MvcTranslator::class),
                        'banner',
                    );
                },
                'company_admin_company_form' => static function (ContainerInterface $container) {
                    return new CompanyForm(
                        $container->get('company_mapper_company'),
                        $container->get(MvcTranslator::class),
                    );
                },
                'company_admin_job_form' => static function (ContainerInterface $container) {
                    return new JobForm(
                        $container->get('company_mapper_job'),
                        $container->get(MvcTranslator::class),
                        $container->get('company_mapper_jobcategory')->findAll(),
                        $container->get('company_mapper_joblabel')->findAll(),
                    );
                },
                'company_admin_jobsTransfer_form' => static function (ContainerInterface $container) {
                    return new JobsTransferForm($container->get(MvcTranslator::class));
                },
                // Commands
            ],
        ];
    }
}
