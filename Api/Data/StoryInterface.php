<?php
declare(strict_types=1);

namespace Magelearn\Story\Api\Data;

interface StoryInterface
{
    public const ID = 'id';
    public const NAME = 'name';
    public const STATUS = 'status';
    public const POSITION = 'position';
    public const DESCRIPTION = 'description';
    public const PHOTO = 'photo';
    public const STORES = 'stores';
    public const URL_KEY = 'url_key';
    public const META_TITLE = 'meta_title';
    public const META_DESCRIPTION = 'meta_description';
    public const META_ROBOTS = 'meta_robots';
    public const CANONICAL_URL = 'canonical_url';
    
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';
    
    public const STATUS_ENABLED = 1;
    public const STATUS_DISABLED = 0;

    /**
     * Get id
     * @return string|null
     */
    public function getId();

    /**
     * @param int $id
     * @return $this
     */
    public function setId($id);

    /**
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * @param string|null $name
     * @return void
     */
    public function setName(?string $name): void;

    /**
     * @return Int|null
     */
    public function getStatus(): ?Int;

    /**
     * @param Int|null $status
     * @return void
     */
    public function setStatus(?Int $status): void;
    
    /**
     * @return string|null
     */
    public function getPosition(): ?string;
    
    /**
     * @param string|null $position
     * @return void
     */
    public function setPosition(?string $position): void;

    /**
     * Get photo
     * @return string|null
     */
    public function getPhoto(): ?string;

    /**
     * @param string|null $photo
     * @return void
     */
    public function setPhoto(?string $photo): void;

    /**
     * @return string|null
     */
    public function getDescription(): ?string;

    /**
     * @param string|null $description
     * @return void
     */
    public function setDescription(?string $description): void;
    
    /**
     * @return string|null
     */
    public function getStores(): ?string;
    
    /**
     * @param string|null $stores
     * @return void
     */
    public function setStores(?string $stores): void;
    
    /**
     * @return string|null
     */
    public function getUrlKey(): ?string;
    
    /**
     * @param string|null $urlKey
     * @return void
     */
    public function setUrlKey(?string $urlKey): void;
    
    /**
     * @return string|null
     */
    public function getMetaTitle(): ?string;
    
    /**
     * @param string|null $metaTitle
     * @return void
     */
    public function setMetaTitle(?string $metaTitle): void;
    
    /**
     * @return string|null
     */
    public function getMetaDescription(): ?string;
    
    /**
     * @param string|null $metaDescription
     * @return void
     */
    public function setMetaDescription(?string $metaDescription): void;
    
    /**
     * @return string|null
     */
    public function getMetaRobots(): ?string;
    
    /**
     * @param string|null $metaRobots
     * @return void
     */
    public function setMetaRobots(?string $metaRobots): void;
    
    /**
     * @return string|null
     */
    public function getCanonicalUrl(): ?string;
    
    /**
     * @param string|null $canonicalUrl
     * @return void
     */
    public function setCanonicalUrl(?string $canonicalUrl): void;

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt(): ?string;

    /**
     * Set created_at
     * @param string $createdAt
     * @return void
     */
    public function setCreatedAt(?string $createdAt): void;

    /**
     * Get updated_at
     * @return string|null
     */
    public function getUpdatedAt(): ?string;

    /**
     * Set updated_at
     * @param string $updatedAt
     * @return void
     */
    public function setUpdatedAt(?string $updatedAt): void;
}

