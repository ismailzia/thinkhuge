<?php
namespace App\core;

use PDO;
use Exception;

class Installer
{
    private PDO $pdo;
    private string $installFlag;
    private string $sqlFile;
    private int $userId = 1;

    public function __construct(PDO $pdo, string $installFlag, string $sqlFile)
    {
        $this->pdo = $pdo;
        $this->installFlag = $installFlag;
        $this->sqlFile = $sqlFile;
    }

    public function install(): void
    {
        $this->runSchema();
        $this->insertSampleClientsAndTransactions();
        $this->createInstallFlag();
    }

    private function runSchema(): void
    {
        $sql = file_get_contents($this->sqlFile);
        if ($sql === false) {
            throw new Exception("Failed to read SQL file: {$this->sqlFile}");
        }
        // Execute each statement separately
        foreach (explode(';', $sql) as $query) {
            $query = trim($query);
            if ($query) {
                $this->pdo->exec($query);
            }
        }
    }

    private function insertSampleClientsAndTransactions(): void
    {
        // Sample clients data
        $clients = [
            ['John Smith', 'john.smith@example.com', '+44 7911 123456'],
            ['Marie Dupont', 'marie.dupont@example.fr', '+33 612 345 678'],
            ['Lukas Müller', 'lukas.mueller@example.de', '+49 171 234 5678'],
            ['Anna Rossi', 'anna.rossi@example.it', '+39 320 123 4567'],
            ['Carlos Silva', 'carlos.silva@example.pt', '+351 912 345 678'],
            ['Sofia Novak', 'sofia.novak@example.cz', '+420 777 123 456'],
            ['Ivan Petrov', 'ivan.petrov@example.ru', '+7 912 345 6789'],
            ['Emma Johnson', 'emma.johnson@example.co.uk', '+44 7700 900123'],
            ['Marta Kowalska', 'marta.kowalska@example.pl', '+48 600 123 456'],
            ['Jean Dupuis', 'jean.dupuis@example.be', '+32 478 12 34 56'],
            ['Laura Garcia', 'laura.garcia@example.es', '+34 612 345 678'],
            ['Niels Jensen', 'niels.jensen@example.dk', '+45 4012 3456'],
            ['Eva Novak', 'eva.novak@example.sk', '+421 905 123 456'],
            ['Peter Hansen', 'peter.hansen@example.no', '+47 912 34 567'],
            ['Maria Silva', 'maria.silva@example.se', '+46 70 123 45 67'],
            ['David Brown', 'david.brown@example.ie', '+353 85 123 4567'],
        ];

        // Insert clients
        $stmtInsertClient = $this->pdo->prepare("
            INSERT INTO clients (name, email, phone, user_id, created_at) 
            VALUES (:name, :email, :phone, :user_id, :created_at)
        ");

        foreach ($clients as $client) {
            [$name, $email, $phone] = $client;
            // Random created_at within last 20 days (including random hour/minute)
            $daysAgo = rand(0, 20);
            $hour = rand(0, 23);
            $minute = rand(0, 59);
            $createdAt = (new \DateTime())->modify("-{$daysAgo} days")->setTime($hour, $minute)->format('Y-m-d H:i:s');

            $stmtInsertClient->execute([
                ':name' => $name,
                ':email' => $email,
                ':phone' => $phone,
                ':user_id' => $this->userId,
                ':created_at' => $createdAt,
            ]);
        }

        // Get inserted clients IDs
        $clientIds = $this->pdo->query("SELECT id FROM clients WHERE user_id = {$this->userId}")->fetchAll(PDO::FETCH_COLUMN);

        // Prepare transaction insert statement
        $stmtInsertTxn = $this->pdo->prepare("
            INSERT INTO transactions (client_id, user_id, amount, type, description, date, created_at, updated_at) 
            VALUES (:client_id, :user_id, :amount, :type, :description, :date, NOW(), NOW())
        ");

        foreach ($clientIds as $clientId) {
            $txnCount = rand(2, 20);
            for ($i = 1; $i <= $txnCount; $i++) {
                $amount = round(30 + mt_rand() / mt_getrandmax() * (10000 - 30), 2);
                $type = (mt_rand(0, 1) === 1) ? 'income' : 'expense';
                $description = "Transaction {$i} for client {$clientId}";
                $txnDaysAgo = rand(0, 20);
                $date = (new \DateTime())->modify("-{$txnDaysAgo} days")->format('Y-m-d');

                $stmtInsertTxn->execute([
                    ':client_id' => $clientId,
                    ':user_id' => $this->userId,
                    ':amount' => $amount,
                    ':type' => $type,
                    ':description' => $description,
                    ':date' => $date,
                ]);
            }
        }
    }

    private function createInstallFlag(): void
    {
        $flagDir = dirname($this->installFlag);
        if (!is_dir($flagDir)) {
            mkdir($flagDir, 0777, true);
        }
        file_put_contents($this->installFlag, "Installed at " . date('Y-m-d H:i:s'));
    }
}
