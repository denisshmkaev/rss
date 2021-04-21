<?php

namespace App\Entity;

use App\Repository\LogsRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=LogsRepository::class)
 */
class Logs
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $request_method;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $request_url;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $response_code;

    /**
     * @ORM\Column(type="text")
     */
    private $response_body;


    public function __construct($object = null)
    {
        if (!$object) {
            return $this;
        }
        foreach ($object as $name => $value) {
            $name = str_replace('_', ' ', $name);
            $name = ucwords($name);
            $name = str_replace(' ', '', $name);
            $name = 'set' . $name;
            if (method_exists($this, $name)) {
                $this->$name($value);
            }
        }
        return $this;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getRequestMethod(): ?string
    {
        return $this->request_method;
    }

    public function setRequestMethod(string $request_method): self
    {
        $this->request_method = $request_method;

        return $this;
    }

    public function getRequestUrl(): ?string
    {
        return $this->request_url;
    }

    public function setRequestUrl(string $request_url): self
    {
        $this->request_url = $request_url;

        return $this;
    }

    public function getResponseCode(): ?string
    {
        return $this->response_code;
    }

    public function setResponseCode(string $response_code): self
    {
        $this->response_code = $response_code;

        return $this;
    }

    public function getResponseBody(): ?string
    {
        return $this->response_body;
    }

    public function setResponseBody(string $response_body): self
    {
        $this->response_body = $response_body;

        return $this;
    }

}
