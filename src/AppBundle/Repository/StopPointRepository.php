<?php

namespace AppBundle\Repository;

use AppBundle\Entity\StopPoint;

/**
 * StopPointRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class StopPointRepository extends \Doctrine\ORM\EntityRepository
{
    public function findOrCreateByCode($code) {
        $stopPoint = $this->findOneByCode($code);

        return is_null($stopPoint) ? new StopPoint() : $stopPoint;
    }
}
