<?php

class UserVoPg extends \Simplon\Postgres\Crud\PgSqlCrudVo
{
    protected $crudIgnoreVariables = ['undefined'];
    protected $id;
    protected $name;
    protected $email;
    protected $createdAt;
    protected $updatedAt;
    protected $undefined;

//    /**
//     * @return array
//     */
//    public function crudColumns()
//    {
//        return [
//            'id'        => 'id',
//            'name'      => 'name',
//            'email'     => 'email',
//            'createdAt' => 'created_at',
//            'updatedAt' => 'updated_at',
//        ];
//    }
//
    /**
     * @param bool $isCreateEvent
     *
     * @return bool
     */
    public function crudBeforeSave($isCreateEvent)
    {
        if ($isCreateEvent === true)
        {
            $this->setCreatedAt(time());
        }

        $this->setUpdatedAt(time());

        return true;
    }

    /**
     * @return int
     */
    public function getCreatedAt()
    {
        return (int)$this->createdAt;
    }

    /**
     * @param int $createdAt
     *
     * @return UserVoPg
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return (string)$this->email;
    }

    /**
     * @param string $email
     *
     * @return UserVoPg
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return (int)$this->id;
    }

    /**
     * @param int $id
     *
     * @return UserVoPg
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return (string)$this->name;
    }

    /**
     * @param string $name
     *
     * @return UserVoPg
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return int
     */
    public function getUpdatedAt()
    {
        return (int)$this->updatedAt;
    }

    /**
     * @param int $updatedAt
     *
     * @return UserVoPg
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
} 