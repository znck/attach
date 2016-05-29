<?php namespace Znck\Attach\Contracts;

interface TokenGenerator
{
    public function make(string $id);

    public function verify(string $hash, string $id);
}
