<?php

namespace Bigfoot\Bundle\ImportBundle\Manager;

use Bigfoot\Bundle\ImportBundle\Translation\DataTranslationQueue;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Stopwatch\StopwatchEvent;
use Symfony\Component\Validator\Validator;

/**
 * Class ImportedDataManager
 */
class ImportedDataManager
{
    /** @var int */
    protected $batchSize = 50;

    /** @var int */
    protected $iteration = 0;

    /** @var \Doctrine\ORM\EntityManager */
    protected $entityManager;

    /** @var \Symfony\Component\Validator\Validator */
    protected $validator;

    /** @var \Symfony\Component\PropertyAccess\PropertyAccessor */
    protected $propertyAccessor;

    /** @var DataTranslationQueue */
    protected $translationQueue = array();

    /** @var \Doctrine\Common\Annotations\FileCacheReader */
    protected $annotationReader;

    /** @var \Bigfoot\Bundle\CoreBundle\Entity\TranslationRepository */
    protected $bigfootTransRepo;

    /** @var array */
    protected $importedEntities = array();

    /** @var string */
    protected $importedIdentifier;

    /** @var  TransversalDataManager */
    protected $transversalDataManager;

    /** @var  OutputInterface */
    protected $output;

    /** Verbose Level */
    protected $verbose;

    /** @var  Stopwatch */
    protected $timer;

    const QUIET = 0;
    const VERBOSE = 1;
    const DEBUG = 2;

    /**
     * @param \Doctrine\ORM\EntityManager $entityManager
     * @param \Symfony\Component\Validator\Validator $validator
     * @param \Symfony\Component\PropertyAccess\PropertyAccessor $propertyAccessor
     * @param \Bigfoot\Bundle\ImportBundle\Translation\DataTranslationQueue $translationQueue
     * @param \Doctrine\Common\Annotations\FileCacheReader $annotationReader
     * @param \Bigfoot\Bundle\CoreBundle\Entity\TranslationRepository $bigfootTransRepo
     * @param transversalDataManager $transversalDataManager
     */
    public function __construct(
        $entityManager,
        $validator,
        $propertyAccessor,
        $translationQueue,
        $annotationReader,
        $bigfootTransRepo,
        $transversalDataManager
    ) {
        $this->entityManager          = $entityManager;
        $this->validator              = $validator;
        $this->propertyAccessor       = $propertyAccessor;
        $this->translationQueue       = $translationQueue;
        $this->annotationReader       = $annotationReader;
        $this->bigfootTransRepo       = $bigfootTransRepo;
        $this->transversalDataManager = $transversalDataManager;
        $this->verbose                = self::QUIET;
        $this->timer                  = new Stopwatch();
    }

    /**
     * @param int $batchSize
     * @return $this
     */
    public function setBatchSize($batchSize)
    {
        $this->batchSize = $batchSize;

        return $this;
    }

    /**
     * @param $level
     * @return $this
     * @throws \Exception
     */
    public function setVerbose($level)
    {
        if ($level != self::QUIET && $level != self::VERBOSE && $level != self::DEBUG) {
            throw new \Exception('To set the verbosity level, you must use ImportedDataManager::[QUIET|VERBOSE|DEBUG] for this data manager.');
        }

        $this->verbose = $level;

        return $this;
    }

    /**
     * @param OutputInterface $output
     * @return ImportedDataManager
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
        return $this;
    }

    /**
     * @return array
     */
    public function getManagedEntity()
    {
        $this->entityManager->getUnitOfWork()->computeChangeSets();
        return [
            'insert' => $this->entityManager->getUnitOfWork()->getScheduledEntityInsertions(),
            'update'    => $this->entityManager->getUnitOfWork()->getScheduledEntityUpdates(),
            'delete'  => $this->entityManager->getUnitOfWork()->getScheduledEntityDeletions(),
        ];
    }

    /**
     * @param $entity
     * @return bool
     * @throws \Exception
     */
    public function load($entity)
    {
        if (!$this->importedIdentifier) {
            throw new \Exception('You must declare a property identifier for this data manager. The property identifier must be a accessible property in your entities.');
        }

        if (!$this->validator->validate($entity)) {
            return false;
        }

        $propertyAccessor = $this->propertyAccessor;
        $entityClass      = ltrim(get_class($entity), '\\');
        $property         = $this->getImportedIdentifier($entityClass);

        try {
            $importedId = $propertyAccessor->getValue($entity, $property);
        } catch (\Exception $e) {
            $importedId = spl_object_hash($entity);
        }

        if (!isset($this->importedEntities[$entityClass])) {
            $this->importedEntities[$entityClass] = array();
        }

        $this->importedEntities[$entityClass][$importedId] = $entity;

        $em = $this->entityManager;
        $em->persist($entity);

        return true;
    }

    /**
     *
     */
    public function batch()
    {
        if (++$this->iteration % $this->batchSize == 0) {
            $this->flush();
        }
    }

    public function terminate()
    {
        $this->flush();
        $this->iteration = 0;
        $this->transversalDataManager->clear();
    }

    /**
     *
     */
    public function flush()
    {
        if ($this->verbose > 0) {
            $this->preFlushVerbose($this->timer->start('flushOp'));
        }

        $this->processTranslations();
        $em = $this->entityManager;
        $em->flush();
        $em->clear();
        $this->importedEntities = array();
        $this->translationQueue->clear();
        $this->transversalDataManager->rebuildReferences();

        if ($this->verbose > 0) {
            $this->postFlushVerbose($this->timer->stop('flushOp'));
        }

        gc_collect_cycles();
    }

    /**
     * @param $e
     */
    public function persist($e)
    {
        $this->entityManager->persist($e);
    }

    /**
     * @param $e
     */
    public function merge($e)
    {
        $this->entityManager->merge($e);
    }

    /**
     * @param $e
     */
    public function remove($e)
    {
        $this->entityManager->remove($e);
    }

    /**
     * @throws \Exception
     */
    protected function preFlushVerbose(StopwatchEvent $event)
    {
        if ($this->output === null) {
            throw new \Exception('You must give outputInterface for use this data manager in verbose mode. The outputInterface must be defined by setOutput().');
        }

        $managed = $this->getManagedEntity();
        $time = new \DateTime('now');

        $this->output->writeln("\n");
        $this->output->writeln(sprintf("\t<info>#######></info> FLUSH OPERATION"));
        $this->output->writeln(sprintf("\t<info>#</info> > Start Time: <comment>%s</comment>", $time->format('H:i:s')));
        $this->output->writeln(sprintf("\t<info>#</info> > Entity to insert: <comment>%s</comment>", count($managed['insert'])));
        $this->output->writeln(sprintf("\t<info>#</info> > Entity to update: <comment>%s</comment>", count($managed['update'])));
        $this->output->writeln(sprintf("\t<info>#</info> > Entity to delete: <comment>%s</comment>", count($managed['delete'])));

        if ($this->verbose == self::DEBUG) {
            foreach ($managed as $key => $data) {
                $this->output->writeln(sprintf("\t<info>#</info> > %s list (<comment>%s</comment>): ", ucfirst($key), count($data)));
                $objects = array();

                foreach ($data as $e) {
                    $oName = get_class($e);

                    if (isset($objects[$oName])) {
                        $cpt = $objects[$oName];
                        $objects[$oName] = ++$cpt;
                    } else {
                        $objects[$oName] = 1;
                    }
                }

                foreach ($objects as $o => $nb) {
                    $this->output->writeln(sprintf("\t<info>#</info>      <comment>%5s</comment> x <comment>%s</comment>", $nb, $o));
                }
            }
        }
    }

    protected function postFlushVerbose(StopwatchEvent $event)
    {
        if ($this->output === null) {
            throw new \Exception('You must give outputInterface for use this data manager in verbose mode. The outputInterface must be defined by setOutput().');
        }

        $time = new \DateTime('now');

        $this->output->writeln(sprintf("\t<info>#</info> > End Time: <comment>%s</comment>", $time->format('H:i:s')));
        $this->output->writeln(sprintf("\t<info>#</info> > Duration: <comment>%s</comment>", $this->getHumanTime($event->getDuration())));
        $this->output->writeln(sprintf("\t<info>#</info> > Memory: <comment>%s</comment>", $this->formatSizeUnits($event->getMemory())));
        $this->output->writeln(sprintf("\t<info>#######></info>"));
        $this->output->writeln("\n");
    }

    private function getHumanTime($ms)
    {
        $message = "Less than a second.";

        if ($ms >= 1000){
            $seconds = (int) ($ms / 1000) % 60;
            $minutes = (int) (($ms / (1000 * 60)) % 60);
            $hours   = (int) (($ms / (1000 * 60 * 60)) % 24);

            if(($hours == 0) && ($minutes != 0)){
                $message = sprintf("%dmin %ds", $minutes, $seconds);
            }elseif(($hours == 0) && ($minutes == 0)){
                $message = sprintf("%ds", $seconds);
            }else{
                $message = sprintf("%dhours %dmin %ds", $hours, $minutes, $seconds);
            }
        }

        return $message;
    }

    private function formatSizeUnits($bytes)
    {
        if ($bytes >= 1073741824)
        {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        }
        elseif ($bytes >= 1048576)
        {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        }
        elseif ($bytes >= 1024)
        {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        }
        elseif ($bytes > 1)
        {
            $bytes = $bytes . ' bytes';
        }
        elseif ($bytes == 1)
        {
            $bytes = $bytes . ' byte';
        }
        else
        {
            $bytes = '0 bytes';
        }

        return $bytes;
    }

    /**
     * @param $class
     * @param $key
     * @param string $repoMethod
     * @return null
     * @throws \Exception
     */
    public function findExistingEntity($class, $key, $repoMethod = 'findOneBy')
    {
        if (!$this->importedIdentifier) {
            throw new \Exception('You must declare a property identifier for this data manager. The property identifier must be a accessible property in your entities.');
        }

        $property    = $this->getImportedIdentifier($class);
        $entityClass = ltrim($class, '\\');

        $entity = null;

        if (isset($this->importedEntities[$entityClass]) && isset($this->importedEntities[$entityClass][$key])) {
            $entity = $this->importedEntities[$entityClass][$key];
        }

        if (!$entity) {
            $repo   = $this->entityManager->getRepository($class);
            $entity = $repo->$repoMethod(array($property => $key));
        }

        if (!$entity && isset($this->importedEntities[$entityClass]) && isset($this->importedEntities[$entityClass][$key])) {
            $entity = $this->importedEntities[$entityClass][$key];
        }

        if (!$entity) {
            $entity = new $class();
        }

        return $entity;
    }

    /**
     * @param string $importedIdentifier
     * @return $this
     */
    public function setImportedIdentifier($importedIdentifier)
    {
        $this->importedIdentifier = $importedIdentifier;
        return $this;
    }

    /**
     * @return string
     */
    public function getImportedIdentifier()
    {
        return $this->importedIdentifier;
    }

    /**
     * @param $entity
     * @return mixed
     */
    protected function getImportedId($entity)
    {
        $propertyAccessor = $this->propertyAccessor;
        $entityClass      = get_class($entity);
        $property         = $this->getImportedIdentifier($entityClass);

        return $propertyAccessor->getValue($entity, $property);
    }

    protected function processTranslations()
    {
        $em               = $this->entityManager;
        $bigfootTransRepo = $this->bigfootTransRepo;

        foreach ($this->translationQueue->getQueue() as $class => $entities) {
            foreach ($entities as $locales) {
                $reflectionClass  = new \ReflectionClass($class);
                $gedmoAnnotations = $this->annotationReader->getClassAnnotation($reflectionClass, 'Gedmo\\Mapping\\Annotation\\TranslationEntity');

                if ($gedmoAnnotations !== null && $gedmoAnnotations->class != '') {
                    $translationRepository = $bigfootTransRepo;
                } else {
                    $translationRepository = $em->getRepository('Gedmo\\Translatable\\Entity\\Translation');
                }

                foreach ($locales as $locale => $properties) {
                    foreach ($properties as $property => $values) {
                        $entity  = $values['entity'];
                        $content = $values['content'];

                        $translationRepository->translate($entity, $property, $locale, $content);
                    }
                }
            }
        }
    }
}
