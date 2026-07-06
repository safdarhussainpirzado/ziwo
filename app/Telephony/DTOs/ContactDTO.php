<?php

namespace App\Telephony\DTOs;

class ContactDTO
{
    public function __construct(
        public string $name,
        public string $phoneNumber,
        public string $category, // beat, sector, zone, emergency, custom
        public ?int $createdBy = null,
        public bool $isFavorite = false,
        public array $metadata = []
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            phoneNumber: $data['phone_number'],
            category: $data['category'] ?? 'custom',
            createdBy: $data['created_by'] ?? null,
            isFavorite: (bool)($data['is_favorite'] ?? false),
            metadata: $data['metadata'] ?? []
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'phone_number' => $this->phoneNumber,
            'category' => $this->category,
            'created_by' => $this->createdBy,
            'is_favorite' => $this->isFavorite,
            'metadata' => $this->metadata,
        ];
    }
}
