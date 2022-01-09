<?php
class JSONRecordSet extends RecordSet
{
    function getJSONRecordSet($sql, $params = null)
    {
        $queryResult = $this->getRecordSet($sql, $params);
        $recordSet = $queryResult->fetchAll(PDO::FETCH_ASSOC);
        $nRecords = count($recordSet);
        if ($nRecords == 0) {
            $status = 200;
            $message = array("text" => "No records found");
            $result = '[]';
        } else {
            $status = 200;
            $message = array("text" => "Successful Query");
            $result = $recordSet;
        }
        return json_encode(array('status' => $status, 'message' => $message,
            'data' => array("RowCount" => $nRecords, "Result" => $result)), JSON_PRETTY_PRINT);
    }
} ?>