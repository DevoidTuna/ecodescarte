<?php

namespace App\Domain\CollectionPoint;

/**
 * Where a point stands in the moderation pipeline. Only approved points reach
 * the public map.
 */
enum ModerationStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
}
