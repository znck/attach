<?php namespace Znck\Attach\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphOneOrMany;

interface AttachmentContract
{
    /**
     * Get attachment identifier value.
     *
     * @return string
     */
    public function getAttachmentKey(): string;

    /**
     * Get attachment identifier key name.
     *
     * @return string
     */
    public function getAttachmentKeyName(): string;

    /**
     * Get path.
     *
     * @param string $variation
     *
     * @return string
     */
    public function getPath(string $variation = null): string;

    /**
     * Resource uploaded by the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function owner();

    /**
     * Related models (attached to).
     *
     * @return MorphOneOrMany
     */
    public function related();
}
