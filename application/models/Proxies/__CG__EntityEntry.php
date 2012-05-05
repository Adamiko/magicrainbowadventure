<?php

namespace Entity\Proxy\__CG__\Entity;

/**
 * THIS CLASS WAS GENERATED BY THE DOCTRINE ORM. DO NOT EDIT THIS FILE.
 */
class Entry extends \Entity\Entry implements \Doctrine\ORM\Proxy\Proxy
{
    private $_entityPersister;
    private $_identifier;
    public $__isInitialized__ = false;
    public function __construct($entityPersister, $identifier)
    {
        $this->_entityPersister = $entityPersister;
        $this->_identifier = $identifier;
    }
    /** @private */
    public function __load()
    {
        if (!$this->__isInitialized__ && $this->_entityPersister) {
            $this->__isInitialized__ = true;

            if (method_exists($this, "__wakeup")) {
                // call this after __isInitialized__to avoid infinite recursion
                // but before loading to emulate what ClassMetadata::newInstance()
                // provides.
                $this->__wakeup();
            }

            if ($this->_entityPersister->load($this->_identifier, $this) === null) {
                throw new \Doctrine\ORM\EntityNotFoundException();
            }
            unset($this->_entityPersister, $this->_identifier);
        }
    }

    /** @private */
    public function __isInitialized()
    {
        return $this->__isInitialized__;
    }

    
    public function uploadFile($file, $extension)
    {
        $this->__load();
        return parent::uploadFile($file, $extension);
    }

    public function serialize()
    {
        $this->__load();
        return parent::serialize();
    }

    public function unserialize($data)
    {
        $this->__load();
        return parent::unserialize($data);
    }

    public function getDropboxThumbnail($size = 'small')
    {
        $this->__load();
        return parent::getDropboxThumbnail($size);
    }

    public function getId()
    {
        if ($this->__isInitialized__ === false) {
            return (int) $this->_identifier["id"];
        }
        $this->__load();
        return parent::getId();
    }

    public function setTitle($title)
    {
        $this->__load();
        return parent::setTitle($title);
    }

    public function getTitle()
    {
        $this->__load();
        return parent::getTitle();
    }

    public function setHash($hash)
    {
        $this->__load();
        return parent::setHash($hash);
    }

    public function getHash()
    {
        $this->__load();
        return parent::getHash();
    }

    public function setUrlTitle($urlTitle)
    {
        $this->__load();
        return parent::setUrlTitle($urlTitle);
    }

    public function getUrlTitle()
    {
        $this->__load();
        return parent::getUrlTitle();
    }

    public function setFilePath($filePath)
    {
        $this->__load();
        return parent::setFilePath($filePath);
    }

    public function getFilePath()
    {
        $this->__load();
        return parent::getFilePath();
    }

    public function setDescription($description)
    {
        $this->__load();
        return parent::setDescription($description);
    }

    public function getDescription()
    {
        $this->__load();
        return parent::getDescription();
    }

    public function setType($type)
    {
        $this->__load();
        return parent::setType($type);
    }

    public function getType()
    {
        $this->__load();
        return parent::getType();
    }

    public function addRating(\Entity\EntryRating $ratings)
    {
        $this->__load();
        return parent::addRating($ratings);
    }

    public function getRatings()
    {
        $this->__load();
        return parent::getRatings();
    }

    public function setUser(\Entity\User $user)
    {
        $this->__load();
        return parent::setUser($user);
    }

    public function getUser()
    {
        $this->__load();
        return parent::getUser();
    }

    public function setApproved($approved)
    {
        $this->__load();
        return parent::setApproved($approved);
    }

    public function getApproved()
    {
        $this->__load();
        return parent::getApproved();
    }

    public function isApproved()
    {
        $this->__load();
        return parent::isApproved();
    }

    public function setModeratedBy(\Entity\User $user)
    {
        $this->__load();
        return parent::setModeratedBy($user);
    }

    public function getModeratedBy()
    {
        $this->__load();
        return parent::getModeratedBy();
    }

    public function setCreatedDate()
    {
        $this->__load();
        return parent::setCreatedDate();
    }

    public function getCreatedDate()
    {
        $this->__load();
        return parent::getCreatedDate();
    }

    public function setModifiedDate()
    {
        $this->__load();
        return parent::setModifiedDate();
    }

    public function getModifiedDate()
    {
        $this->__load();
        return parent::getModifiedDate();
    }

    public function toArray()
    {
        $this->__load();
        return parent::toArray();
    }


    public function __sleep()
    {
        return array('__isInitialized__', 'id', 'title', 'url_title', 'file_path', 'hash', 'description', 'type', 'approved', 'created_date', 'modified_date', 'ratings', 'user', 'moderated_by');
    }

    public function __clone()
    {
        if (!$this->__isInitialized__ && $this->_entityPersister) {
            $this->__isInitialized__ = true;
            $class = $this->_entityPersister->getClassMetadata();
            $original = $this->_entityPersister->load($this->_identifier);
            if ($original === null) {
                throw new \Doctrine\ORM\EntityNotFoundException();
            }
            foreach ($class->reflFields AS $field => $reflProperty) {
                $reflProperty->setValue($this, $reflProperty->getValue($original));
            }
            unset($this->_entityPersister, $this->_identifier);
        }
        
    }
}