<?php

namespace Webkul\Tenant\Contracts;

interface Tenant
{
    public function getId(): int;

    public function getName(): string;

    public function getSlug(): string;

    public function getDomain(): ?string;

    public function getTheme(): string;

    public function getTemplate(): string;

    public function getLocale(): string;

    public function isActive(): bool;
}
