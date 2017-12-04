<?php

namespace AppBundle\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ApiResource
 * @ORM\Entity
 */
class Role
{

    /**
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @var string $description  deskripsi role
     * @ORM\Column(length=255)
     */
    private $description;

    /**
     * @ORM\ManyToMany(targetEntity="Group", mappedBy="roles")
     */
    private $groups;

    function __construct()
    {
    	$this->groups = new ArrayCollection();
    }

}
