<?php

namespace Performance\LazyCache;

use Doctrine\DBAL\Connection;

interface CacheRecordMapperInterface {
//    public function create(CacheRecord $cacheRecord);
//
//    public function update($id, CacheRecor $cacheRecord);
//
//    public function delete($id);
}

class CacheRecordSQLMapper implements CacheRecordMapperInterface {

    /** @var Connection  */
    private $db;
    private $entityTable;

    /**
     * 
     * @param Connection $adapter
     */
    function __construct(Connection $adapter) {
        $this->db = $adapter;
        $this->entityTable = "lazycache";
    }

    public function create(CacheRecord $cacheRecord) {
        $this->db->insert($this->entityTable, $cacheRecord->toArray());

        if ($this->db->lastInsertId() <= 0) {
            throw new InvalidArgumentException("The insert failed.");
        }
        return $this->db->lastInsertId();
    }

    public function update($id, CacheRecord $cacheRecord) {
        $count = $this->db->update($this->entityTable, $cacheRecord->toArray(), array("id" => $id));
        if ($count <= 0) {
            throw new InvalidArgumentException("The update failed.");
        }
    }

    public function delete($id) {
        $count = $this->db->delete($this->entityTable, array("id" => $id));
        if ($count <= 0) {
            throw new InvalidArgumentException(
            "The delete failed.");
        }
    }

    public function createEntity(array $row) {
        return new CacheRecord($row);
    }

    public function findByUri($uri) {
        $sql = "SELECT * FROM {$this->entityTable} WHERE uri = ?";
        $row = $this->db->fetchAssoc($sql, array((string) $uri));
        if ($row) {
            return $this->createEntity($row);
        }
    }

    public function findByUriValid($uri) {
        $sql = "SELECT * FROM {$this->entityTable} WHERE uri = ? AND compromised=0";
        $row = $this->db->fetchAssoc($sql, array((string) $uri));
        if ($row) {
            return $this->createEntity($row);
        }
    }

    public function compromiseAll() {
//        $sql = "UPDATE {$this->entityTable} SET compromised=1";
//        $count = $this->db->query($sql);
        $count = $this->db->executeUpdate("UPDATE {$this->entityTable} SET compromised=?", array(1));
//        \Symfony\Component\VarDumper\VarDumper::dump($count,11,1);
//        $count = $this->db->update($this->entityTable, array("compromised" => 1));
        return $count;
    }

    public function deleteAll() {
        $count = $this->db->executeUpdate("DELETE FROM {$this->entityTable}");
        return $count;
    }

    public function createTable() {
        $schema = $this->db->getSchemaManager();
        if ($schema->tablesExist($this->entityTable)) {
            $schema->dropTable($this->entityTable);
        }
        if (!$schema->tablesExist($this->entityTable)) {
            $table = new \Doctrine\DBAL\Schema\Table($this->entityTable);
            $id = $table->addColumn('id', 'integer', array(
                'unsigned' => true,
                'autoincrement' => true,
                'notnull' => false,
                'customSchemaOptions' => array(
                    'unique' => true)
            ));
            $id->setAutoincrement(true);
            $id->setNotnull(false);

            $table->setPrimaryKey(array('id'));

            $table->addColumn('uri', 'string', array('length' => 255, 'customSchemaOptions' => array('unique' => true)));
            $table->addColumn('hash', 'string', array('length' => 255));
            $table->addColumn('lmt', 'string', array('length' => 32));
            $table->addColumn('compromised', 'boolean', array('notnull' => false, 'default' => 0));
//            $table->addColumn('rand' . rand(), 'boolean');
//            \Symfony\Component\VarDumper\VarDumper::dump($table, 11, 1);

            $schema->createTable($table);
        }
    }

}
