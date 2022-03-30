<?php

namespace Vtex\VtexMagento\Api\Data;

interface ImportInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ID = 'id';
    const TYPE = 'type';
    const STATUS = 'status';
    const TOTAL = 'total';
    const PROGRESS = 'progress';
    const ERRORS = 'errors';
    const FILENAME = 'filename';
    const DATE = 'date';
    /**#@-*/


    /**
     * @return mixed
     */
    public function getId();

    /**
     * @return mixed
     */
    public function getType();

    /**
     * @return mixed
     */
    public function getStatus();

    /**
     * @return mixed
     */
    public function getTotal();

    /**
     * @return mixed
     */
    public function getProgress();

    /**
     * @return mixed
     */
    public function getErrors();

    /**
     * @return mixed
     */
    public function getFilename();

    /**
     * @return mixed
     */
    public function getDate();

    /**
     * @param $id
     * @return mixed
     */
    public function setId($id);


    /**
     * @param $type
     * @return mixed
     */
    public function setType($type);

    /**
     * @param $status
     * @return mixed
     */
    public function setStatus($status);

    /**
     * @param $total
     * @return mixed
     */
    public function setTotal($total);

    /**
     * @param $progress
     * @return mixed
     */
    public function setProgress($progress);

    /**
     * @param $errors
     * @return mixed
     */
    public function setErrors($errors);

    /**
     * @param $filename
     * @return mixed
     */
    public function setFilename($filename);

    /**
     * @param $date
     * @return mixed
     */
    public function setDate($date);
}
