<?php

namespace PhpProjects\SocialDev\Model\Url;

use Doctrine\ORM\EntityRepository;

/**
 * Repository for url entities
 */
class UrlCommentEntityRepository extends EntityRepository
{
    /**
     * Gets the most recent comments for $url after $comment was posted (if provided.
     *
     * @param UrlEntity $url
     * @param UrlCommentEntity $comment
     * @return array
     */
    public function getCommentsForUrlAfter(UrlEntity $url, UrlCommentEntity $comment = null)
    {
        $builder = $this->createQueryBuilder('comment');


        $andX = $builder->expr()->andX(
            $builder->expr()->eq('comment.url', ':url')
        );
        $builder->setParameter('url', $url);
        
        if (!empty($comment))
        {
            $andX->add($builder->expr()->andX(
                $builder->expr()->gte('comment.timestamp', ':timestamp'),
                $builder->expr()->orX(
                    $builder->expr()->gt('comment.timestamp', ':timestamp'),
                    $builder->expr()->gt('comment.urlCommentId', ':commentId')
                )
            ));
            $builder->setParameter('timestamp', $comment->getTimestamp());
            $builder->setParameter('commentId', $comment->getUrlCommentId());
        }
        $builder->where($andX);

        $builder->addOrderBy('comment.timestamp', 'ASC')
            ->addOrderBy('comment.urlCommentId', 'ASC');

        return $builder->getQuery()->getResult();
    }
}