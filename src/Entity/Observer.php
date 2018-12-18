<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ObserverRepository")
 */
class Observer
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Product", mappedBy="observer", orphanRemoval=true)
     */
    private $products;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Error", mappedBy="observer", orphanRemoval=true)
     */
    private $errors;

    public function __construct()
    {
        $this->products = new ArrayCollection();
        $this->errors = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Collection|Product[]
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Product $product): self
    {
        if (!$this->products->contains($product)) {
            $this->products[] = $product;
            $product->setObserver($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): self
    {
        if ($this->products->contains($product)) {
            $this->products->removeElement($product);
            // set the owning side to null (unless already changed)
            if ($product->getObserver() === $this) {
                $product->setObserver(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Error[]
     */
    public function getErrors(): Collection
    {
        return $this->errors;
    }

    public function addError(Error $error): self
    {
        if (!$this->errors->contains($error)) {
            $this->errors[] = $error;
            $error->setObserver($this);
        }

        return $this;
    }

    public function removeError(Error $error): self
    {
        if ($this->errors->contains($error)) {
            $this->errors->removeElement($error);
            // set the owning side to null (unless already changed)
            if ($error->getObserver() === $this) {
                $error->setObserver(null);
            }
        }

        return $this;
    }
}
