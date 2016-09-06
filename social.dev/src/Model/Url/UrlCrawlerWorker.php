<?php

namespace  PhpProjects\SocialDev\Model\Url;

use Doctrine\ORM\EntityManager;
use Pheanstalk\Pheanstalk;
use Pheanstalk\PheanstalkInterface;

/**
 * Manages interactions of our url crawler with our job queue system
 */
class UrlCrawlerWorker
{
    const BEANSTALKD_TUBE = 'urlcrawler';

    /**
     * @var HttpUrlService
     */
    private $httpUrlService;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var UrlEntityRepository
     */
    private $er;

    /**
     * @var Pheanstalk
     */
    private $queue;

    /**
     * UrlCrawlerWorker constructor.
     * @param HttpUrlService $httpUrlService
     * @param EntityManager $em
     * @param Pheanstalk $queue
     */
    public function __construct(HttpUrlService $httpUrlService, EntityManager $em, Pheanstalk $queue)
    {
        $this->httpUrlService = $httpUrlService;
        $this->em = $em;
        $this->er = $em->getRepository(UrlEntity::class);
        $this->queue = $queue;
    }


    /**
     * Processes queued jobs for this worker
     */
    public function processJobs()
    {
        $this->queue->watch(self::BEANSTALKD_TUBE);

        while (true)
        {
            if ($job = $this->queue->reserve(10))
            {
                $received = $job->getData();

                $urlEntity = $this->er->getOneByUrl($received);

                if (empty($urlEntity))
                {
                    echo "Error: Could not find {$received} url\n";
                    $this->queue->release($job, 1, 60);
                }
                else
                {
                    $this->httpUrlService->getUrlEntityFromUrl($urlEntity);

                    $this->queue->delete($job);

                    $this->em->flush();
                    $this->em->clear();
                }
            }
        }
    }

    /**
     * Queues a url for processing.
     * 
     * @param string $url
     */
    public function queueUrl(string $url)
    {
        $this->queue->putInTube(self::BEANSTALKD_TUBE, $url, PheanstalkInterface::DEFAULT_PRIORITY, 1);
    }
}