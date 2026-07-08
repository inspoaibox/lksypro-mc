<?php

namespace App\Http\Middleware;

use Illuminate\Contracts\Encryption\Encrypter as EncrypterContract;
use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;

class EncryptCookies extends Middleware
{
    /**
     * The names of the cookies that should not be encrypted.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];

    public function __construct(EncrypterContract $encrypter)
    {
        parent::__construct($encrypter);

        if (! config('session.encrypt_cookie', true)) {
            $this->except[] = config('session.cookie');
        }
    }
}
