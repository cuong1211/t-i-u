<?php

declare(strict_types=1);

namespace App\Helper;

use InvalidArgumentException;

class Ulogin
{
    protected $data;

    public function __construct(string $token, string $host)
    {
        $request = file_get_contents('http://ulogin.ru/token.php?token='.$token.'&host='.$host);
        $response = json_decode($request);

        $this->data = $response;

        if (isset($response->error)) {
            throw new InvalidArgumentException('Invalid input parameter');
        }

        $this->data->token = $token;
    }

    public function getName()
    {
        return sprintf('%s %s',
            $this->data->first_name,
            $this->data->last_name
        );
    }

    public function getId()
    {
        return $this->data->uid;
    }

    public function getPhoto()
    {
        return $this->data->photo_big;
    }

    public function getToken()
    {
        return $this->data->token;
    }
}
