<?php

namespace Report\Service;

use Doctrine\ORM\EntityManager;
use Laminas\ProgressBar\Adapter\Console;
use Laminas\ProgressBar\ProgressBar;
use LogicException;
use ReflectionProperty;
use Report\Model\Organ as ReportOrganModel;
use Report\Model\OrganMember;
use Report\Model\SubDecision\Abrogation as ReportAbrogationModel;
use Report\Model\SubDecision\Discharge as ReportDischargeModel;
use Report\Model\SubDecision\Foundation as ReportFoundationModel;
use Report\Model\SubDecision\FoundationReference as ReportFoundationReferenceModel;
use Report\Model\SubDecision\Installation;
use Report\Model\SubDecision\Installation as ReportInstallationModel;

class Organ
{
    public function __construct(private readonly EntityManager $emReport)
    {
    }

    /**
     * Export organ info.
     */
    public function generate()
    {
        $foundationRepo = $this->emReport->getRepository(ReportFoundationModel::class);

        /** @var array<array-key, ReportFoundationModel> $foundations */
        $foundations = $foundationRepo->findBy([], [
            'meeting_type' => 'DESC',
            'meeting_number' => 'ASC',
            'decision_point' => 'ASC',
            'decision_number' => 'ASC',
            'number' => 'ASC',
        ]);

        $adapter = new Console();
        $progress = new ProgressBar($adapter, 0, count($foundations));

        $num = 0;
        foreach ($foundations as $foundation) {
            // see if there already is an organ
            $repOrgan = $this->generateFoundation($foundation);

            /**
             * Also find all related subdecisions.
             *
             * Types of subdecisions that can be related to an organ:
             * - foundation
             * - abrogation
             * - installation
             * - discharge
             */
            $repOrgan->addSubdecision($foundation);

            // get the abrogation date and find organ members
            /** @var ReportFoundationReferenceModel $ref */
            foreach ($foundation->getReferences() as $ref) {
                // first add as related subdecision
                $repOrgan->addSubdecision($ref);

                if ($ref instanceof ReportAbrogationModel) {
                    $this->generateAbrogation($ref);
                }

                if ($ref instanceof ReportInstallationModel) {
                    $this->generateInstallation($ref);
                }
            }

            $this->emReport->persist($repOrgan);
            $this->emReport->flush();
            $progress->update(++$num);
        }

        $this->emReport->flush();
        $progress->finish();
    }

    public function generateFoundation(ReportFoundationModel $foundation): ReportOrganModel
    {
        // see if there already is an organ (with a slight hack)
        $rp = new ReflectionProperty(ReportFoundationModel::class, 'organ');
        if ($rp->isInitialized($foundation)) {
            $repOrgan = $foundation->getOrgan();
        } else {
            $repOrgan = null;
        }

        if (null === $repOrgan) {
            $repOrgan = new ReportOrganModel();
            $repOrgan->setFoundation($foundation);
        }

        $repOrgan->setAbbr($foundation->getAbbr());
        $repOrgan->setName($foundation->getName());
        $repOrgan->setType($foundation->getOrganType());
        $repOrgan->setFoundationDate($foundation->getDecision()->getMeeting()->getDate());
        $this->emReport->persist($repOrgan);
        $this->emReport->flush();

        return $repOrgan;
    }

    public function generateAbrogation(ReportAbrogationModel $ref)
    {
        $rp = new ReflectionProperty(ReportFoundationModel::class, 'organ');
        if ($rp->isInitialized($ref->getFoundation())) {
            $repOrgan = $ref->getFoundation()->getOrgan();
        } else {
            $repOrgan = null;
        }

        if ($repOrgan === null) {
            // Grabbing the organ from the foundation doesn't work when it has not been saved yet
            $repo = $this->emReport->getRepository(ReportOrganModel::class);
            $repOrgan = $repo->findOneBy([
                'foundation' => $ref->getFoundation(),
            ]);

            if ($repOrgan === null) {
                throw new LogicException('Abrogation without Organ');
            }
        }

        $repOrgan->setAbrogationDate($ref->getDecision()->getMeeting()->getDate());
    }

    public function generateInstallation(ReportInstallationModel $ref)
    {
        $repo = $this->emReport->getRepository(ReportOrganModel::class);
        // get full reference
        $rp = new ReflectionProperty(ReportInstallationModel::class, 'organMember');
        if ($rp->isInitialized($ref)) {
            $organMember = $ref->getOrganMember();
        } else {
            $organMember = null;
        }

        $rp = new ReflectionProperty(ReportFoundationModel::class, 'organ');
        if ($rp->isInitialized($ref->getFoundation())) {
            $repOrgan = $ref->getFoundation()->getOrgan();
        } else {
            $repOrgan = null;
        }

        if ($repOrgan === null) {
            // Grabbing the organ from the foundation doesn't work when it has not been saved yet
            $repOrgan = $repo->findOneBy([
                'foundation' => $ref->getFoundation(),
            ]);

            if ($repOrgan === null) {
                throw new LogicException('Installation without Organ');
            }
        }

        if (null === $organMember) {
            $organMember = new OrganMember();
            // set the ID stuff
            $organMember->setOrgan($repOrgan);
            $organMember->setMember($ref->getMember());
            $function = $ref->getFunction();

            if (null === $function) {
                $function = 'Lid';
            }

            $organMember->setFunction($function);
            $organMember->setInstallDate($ref->getDecision()->getMeeting()->getDate());
        }

        $organMember->setInstallation($ref);
        $discharge = $ref->getDischarge();

        if (null !== $discharge) {
            $organMember->setDischargeDate($discharge->getDecision()->getMeeting()->getDate());

            // also add discharge as related
            $repOrgan->addSubdecision($discharge);
        }

        if ($repOrgan->getAbrogationDate() !== null && $organMember->getDischargeDate() === null) {
            $organMember->setDischargeDate($repOrgan->getAbrogationDate());
        }

        $this->emReport->persist($organMember);
    }

    public function generateDischarge(ReportDischargeModel $ref)
    {
        $rp = new ReflectionProperty(ReportInstallationModel::class, 'organMember');
        if ($rp->isInitialized($ref->getInstallation())) {
            $organMember = $ref->getInstallation()->getOrganMember();
        } else {
            $organMember = null;
        }

        if ($organMember === null) {
            throw new LogicException('Discharge without OrganMember');
        }

        $organMember->setDischargeDate($ref->getDecision()->getMeeting()->getDate());
        $this->emReport->persist($organMember);
    }
}