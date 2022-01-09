<?php
abstract class RecordSet
{
    protected $conn;
    protected $queryResult;

    function __construct()
    {
        $this->conn = pdoDB::getConnection();
    }
    function getRecordSet($sql, $params = null)
    {
        if (is_array($params)) {
            $this->queryResult = $this->conn->prepare($sql);
            $this->queryResult->execute($params);
        } else {
            $this->queryResult = $this->conn->query($sql);
        }
        return $this->queryResult;
    }
} ?>