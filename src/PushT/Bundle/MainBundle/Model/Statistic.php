<?php
/**
 * User: mberberoglu
 * Date: 29/07/14
 * Time: 12:18
 */

namespace PushT\Bundle\MainBundle\Model;

use PushT\Bundle\MainBundle\Document\Push;

class Statistic
{
    public static function pushes(array $pushes)
    {
        $data = array(
            'total' => sizeof($pushes),
            'count' => array(0, 0, 0, 0, 0, 0, 0)
        );

        /** @var Push $push */
        foreach ($pushes as $push) {
            $data['count'][$push->getStatus()]++;

            switch ($push->getStatus()) {
                case 6:
                    $data['count'][1]++;
                    break;
                case 4:
                    $data['count'][0]++;
                    break;
                default:
                    break;
            }
        }

        $data['sentRate'] = floatval(sprintf('%.02F', ($data['count'][1] / $data['total']) * 100));
        $data['openRate'] = floatval(sprintf('%.02F', ($data['count'][6] / $data['count'][1]) * 100));

        foreach ($data['count'] as $statusId => $count) {
            $data['count'][Push::$statusTexts[$statusId]] = $count;
            unset($data['count'][$statusId]);
        }

        return $data;
    }
}
