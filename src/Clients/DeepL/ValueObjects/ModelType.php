<?php

declare(strict_types = 1);

namespace EugeneErg\Translate\Clients\DeepL\ValueObjects;

enum ModelType: string
{
    case LatencyOptimized = 'latency_optimized';
    case QualityOptimized = 'quality_optimized';
    case PreferQualityOptimized = 'prefer_quality_optimized';
}