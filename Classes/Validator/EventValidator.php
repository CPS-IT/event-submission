<?php declare(strict_types=1);

namespace Cpsit\EventSubmission\Validator;

use DateTimeZone;

class EventValidator
{
    private const requiredFields = [
        'language',
        'email',
        'event_mode'
    ];
    private const optionalFields = [
        'external_reference',
        'title',
        'teaser',
        'bodytext',
        'location_title',
        'location_description',
        'location_short_title',
        'timezone',
        'datetime',
        'is_cop_event',
        'event_end',
        'organizer_simple',
        'registration_link',
        'streaming_link',
        'website_link'
    ];

    public function validate(array $data): array
    {
        $errors = [];
        foreach (self::requiredFields as $fieldName) {
            $value = $data[$fieldName] ?? null;
            if (!array_key_exists($fieldName, $data) || $value === null) {
                $errors[] = 'Field "' . $fieldName . '" is required';
            }
        }

        // do validation by trigger all validator for the given data
        return $errors;
    }
}
