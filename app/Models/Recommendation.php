belongsTo(Report::class);
    }

    public static function typeLabel(string $type): string
    {
        return match ($type) {
            'working'        => "What's Working",
            'not_working'    => "What's Not Working",
            'at_risk'        => 'At Risk',
            'needs_scaling'  => 'Needs Scaling',
            'recommendations' => 'Recommendations',
            default          => ucfirst($type),
        };
    }
}