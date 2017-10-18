<?php

namespace ApartmentBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Apartment
 *
 * @ORM\Table(name="apartment")
 * @ORM\Entity(repositoryClass="ApartmentBundle\Repository\ApartmentRepository")
 */
class Apartment
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"apartment"})
     */
    private $id;

    /**
     * @var \DateTime
     * @Assert\NotBlank()
     * @Assert\Date()
     * @Groups({"apartment"})
     * @ORM\Column(name="move_in_date", type="date")
     */
    private $moveInDate;

    /**
     * @var string
     * @Assert\NotBlank()
     * @Groups({"apartment"})
     * @ORM\Column(name="street", type="string", length=255)
     */
    private $street;

    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Regex(
     *     pattern="/\d{3,10}/",
     *     match=true,
     *     message="Postal code should contain from 3 to 10 numbers. See https://en.wikipedia.org/wiki/Postal_code"
     * )
     * @Groups({"apartment"})
     * @ORM\Column(name="post_code", type="string", length=10)
     */
    private $postCode;

    /**
     * @var string
     * @Assert\NotBlank()
     * @Groups({"apartment"})
     * @ORM\Column(name="town", type="string", length=255)
     */
    private $town;

    /**
     * @var string
     * @Assert\NotBlank()
     * @Groups({"apartment"})
     * @ORM\Column(name="country", type="string", length=255)
     */
    private $country;

    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Email()
     * @Groups({"apartment"})
     * @ORM\Column(name="email", type="string", length=255)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="security_token", type="string", length=32)
     */
    private $securityToken;

    /**
     * @var \DateTime
     * @Groups({"apartment"})
     * @ORM\Column(name="posted_at", type="datetime")
     */
    private $postedAt;

    /**
     * @var \DateTime
     * @Groups({"apartment"})
     * @ORM\Column(name="edited_at", type="datetime")
     */
    private $editedAt;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set moveInDate
     *
     * @param \DateTime $moveInDate
     *
     * @return Apartment
     */
    public function setMoveInDate($moveInDate)
    {
        $this->moveInDate = $moveInDate;

        return $this;
    }

    /**
     * Get moveInDate
     *
     * @return \DateTime
     */
    public function getMoveInDate()
    {
        return $this->moveInDate;
    }

    /**
     * Set street
     *
     * @param string $street
     *
     * @return Apartment
     */
    public function setStreet($street)
    {
        $this->street = $street;

        return $this;
    }

    /**
     * Get street
     *
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * Set postCode
     *
     * @param string $postCode
     *
     * @return Apartment
     */
    public function setPostCode($postCode)
    {
        $this->postCode = $postCode;

        return $this;
    }

    /**
     * Get postCode
     *
     * @return string
     */
    public function getPostCode()
    {
        return $this->postCode;
    }

    /**
     * Set town
     *
     * @param string $town
     *
     * @return Apartment
     */
    public function setTown($town)
    {
        $this->town = $town;

        return $this;
    }

    /**
     * Get town
     *
     * @return string
     */
    public function getTown()
    {
        return $this->town;
    }

    /**
     * Set country
     *
     * @param string $country
     *
     * @return Apartment
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Apartment
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set securityToken
     *
     * @param string $securityToken
     *
     * @return Apartment
     */
    public function setSecurityToken($securityToken)
    {
        $this->securityToken = $securityToken;

        return $this;
    }

    /**
     * Get securityToken
     *
     * @return string
     */
    public function getSecurityToken()
    {
        return $this->securityToken;
    }

    /**
     * Set postedAt
     *
     * @param \DateTime $postedAt
     *
     * @return Apartment
     */
    public function setPostedAt($postedAt)
    {
        $this->postedAt = $postedAt;

        return $this;
    }

    /**
     * Get postedAt
     *
     * @return \DateTime
     */
    public function getPostedAt()
    {
        return $this->postedAt;
    }

    /**
     * Set editedAt
     *
     * @param \DateTime $editedAt
     *
     * @return Apartment
     */
    public function setEditedAt($editedAt)
    {
        $this->editedAt = $editedAt;

        return $this;
    }

    /**
     * Get editedAt
     *
     * @return \DateTime
     */
    public function getEditedAt()
    {
        return $this->editedAt;
    }

    /**
     * @param string $salt
     * @return string
     */
    public function generateToken(string $salt = '') : string
    {
        $data = [
            $this->getStreet(),
            $this->getTown(),
            $this->getCountry(),
            uniqid($salt)
        ];
        return md5(join('.', $data));
    }
}

